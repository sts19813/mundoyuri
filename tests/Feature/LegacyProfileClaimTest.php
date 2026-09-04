<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\LegacyProfile;
use App\Models\LegacyProfileClaim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyProfileClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_unclaimed_historical_profile_offers_a_manual_claim_request(): void
    {
        $profile = $this->legacyProfile();

        $this->get(route('legacy-profiles.show', $profile))
            ->assertOk()
            ->assertSee('¿Este perfil era tuyo?')
            ->assertSee('Solicitar reclamación');

        $this->actingAs(User::factory()->create())
            ->get(route('legacy-profile-claims.create', ['profile' => $profile->id]))
            ->assertOk()
            ->assertSee($profile->nickname);
    }

    public function test_authenticated_user_can_submit_one_manual_claim_without_linking_any_account(): void
    {
        $profile = $this->legacyProfile();
        $claimant = User::factory()->create([
            'is_legacy' => false,
            'profile_claimed_at' => null,
        ]);

        $this->actingAs($claimant)->post(route('legacy-profile-claims.store'), [
            'legacy_profile_id' => $profile->id,
            'message' => 'Reconozco el alias, la ciudad archivada y varios mensajes de esa época.',
            'evidence' => 'Puedo aportar una captura personal y referencias archivadas.',
        ])->assertRedirect(route('legacy-profiles.show', $profile));

        $this->assertDatabaseHas('legacy_profile_claims', [
            'legacy_profile_id' => $profile->id,
            'claimant_user_id' => $claimant->id,
            'status' => 'pending',
        ]);
        $this->assertSame('pending', $profile->fresh()->claim_status);
        $this->assertNull($profile->fresh()->claimed_by_user_id);
        $this->assertFalse($claimant->fresh()->is_legacy);
        $this->assertNull($claimant->fresh()->profile_claimed_at);

        $this->actingAs($claimant)->from(route('legacy-profile-claims.create'))->post(route('legacy-profile-claims.store'), [
            'legacy_profile_id' => $profile->id,
            'message' => 'Esta segunda solicitud debe ser rechazada por duplicada.',
        ])->assertSessionHasErrors('legacy_profile_id');

        $this->assertDatabaseCount('legacy_profile_claims', 1);
    }

    public function test_claim_form_rejects_unpublished_claimed_and_pending_profiles(): void
    {
        $claimant = User::factory()->create();
        $unpublished = $this->legacyProfile(['legacy_external_key' => 'foro-2007:privado', 'slug' => 'privado', 'is_published' => false]);
        $pending = $this->legacyProfile(['legacy_external_key' => 'foro-2007:pendiente', 'slug' => 'pendiente', 'claim_status' => 'pending']);
        $claimed = $this->legacyProfile(['legacy_external_key' => 'foro-2007:reclamado', 'slug' => 'reclamado', 'claim_status' => 'claimed']);

        foreach ([$unpublished, $pending, $claimed] as $profile) {
            $this->actingAs($claimant)->from(route('legacy-profile-claims.create'))->post(route('legacy-profile-claims.store'), [
                'legacy_profile_id' => $profile->id,
                'message' => 'Tengo información suficiente para intentar reclamar este perfil histórico.',
            ])->assertSessionHasErrors('legacy_profile_id');
        }

        $this->assertDatabaseCount('legacy_profile_claims', 0);
    }

    public function test_admin_approval_links_the_profile_preserves_credentials_and_records_audit_data(): void
    {
        $profile = $this->legacyProfile(['legacy_verified' => true]);
        $claimant = User::factory()->create(['email' => 'actual@example.test']);
        $password = $claimant->getRawOriginal('password');
        $badge = Badge::query()->firstOrFail();
        $claimant->badges()->attach($badge->id, ['awarded_at' => now()]);
        $claim = LegacyProfileClaim::query()->create([
            'legacy_profile_id' => $profile->id,
            'claimant_user_id' => $claimant->id,
            'message' => 'El alias, los intereses y la fecha de registro coinciden con mi cuenta antigua.',
        ]);
        $profile->update(['claim_status' => 'pending']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->patch(route('admin.legacy-profile-claims.update', $claim), [
            'status' => 'approved',
            'admin_notes' => 'Evidencia contrastada con el archivo.',
        ])->assertRedirect();

        $this->assertDatabaseHas('legacy_profile_claims', [
            'id' => $claim->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'admin_notes' => 'Evidencia contrastada con el archivo.',
        ]);
        $this->assertDatabaseHas('legacy_profiles', [
            'id' => $profile->id,
            'claim_status' => 'claimed',
            'claimed_by_user_id' => $claimant->id,
        ]);
        $this->assertDatabaseHas('community_moderation_logs', [
            'moderatable_type' => LegacyProfile::class,
            'moderatable_id' => $profile->id,
            'actor_id' => $admin->id,
            'action' => 'legacy_profile_claim_approved',
        ]);

        $claimant->refresh();
        $this->assertTrue($claimant->is_legacy);
        $this->assertSame('2007-04-12', $claimant->legacy_joined_at?->format('Y-m-d'));
        $this->assertSame('captura-archivada-2007', $claimant->legacy_source);
        $this->assertTrue($claimant->legacy_verified);
        $this->assertNotNull($claimant->profile_claimed_at);
        $this->assertSame('actual@example.test', $claimant->email);
        $this->assertSame($password, $claimant->getRawOriginal('password'));
        $this->assertTrue($claimant->badges()->whereKey($badge->id)->exists());
    }

    public function test_admin_can_reject_without_linking_the_profile_or_account(): void
    {
        $profile = $this->legacyProfile();
        $claimant = User::factory()->create();
        $claim = LegacyProfileClaim::query()->create([
            'legacy_profile_id' => $profile->id,
            'claimant_user_id' => $claimant->id,
            'message' => 'Creo reconocer este perfil, pero mi evidencia no será suficiente.',
        ]);
        $profile->update(['claim_status' => 'pending']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->patch(route('admin.legacy-profile-claims.update', $claim), [
            'status' => 'rejected',
            'admin_notes' => 'No hay evidencia verificable.',
        ])->assertRedirect();

        $this->assertDatabaseHas('legacy_profile_claims', ['id' => $claim->id, 'status' => 'rejected', 'reviewed_by' => $admin->id]);
        $this->assertSame('rejected', $profile->fresh()->claim_status);
        $this->assertNull($profile->fresh()->claimed_by_user_id);
        $this->assertFalse($claimant->fresh()->is_legacy);
        $this->assertDatabaseHas('community_moderation_logs', ['moderatable_id' => $profile->id, 'action' => 'legacy_profile_claim_rejected']);
    }

    public function test_regular_members_cannot_review_or_access_claim_administration(): void
    {
        $profile = $this->legacyProfile();
        $claim = LegacyProfileClaim::query()->create([
            'legacy_profile_id' => $profile->id,
            'claimant_user_id' => User::factory()->create()->id,
            'message' => 'La cuenta está vinculada a recuerdos y referencias comprobables.',
        ]);
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($member)->get(route('admin.legacy-profile-claims.index'))->assertRedirect('/');
        $this->actingAs($member)->patch(route('admin.legacy-profile-claims.update', $claim), ['status' => 'approved'])->assertRedirect('/');
        $this->assertSame('pending', $claim->fresh()->status);
    }

    private function legacyProfile(array $overrides = []): LegacyProfile
    {
        return LegacyProfile::query()->create([...[
            'legacy_external_key' => 'foro-2007:akari',
            'slug' => 'akari-historica',
            'nickname' => 'Akari',
            'legacy_joined_at' => '2007-04-12',
            'legacy_message_count' => 42,
            'source' => 'captura-archivada-2007',
            'legacy_verified' => false,
            'is_published' => true,
        ], ...$overrides]);
    }
}
