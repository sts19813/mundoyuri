<?php

namespace Tests\Feature;

use App\Models\CommunityBadge;
use App\Models\CommunityRank;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommunityProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_manage_historical_profile_visibility_rank_and_badges(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $specialRank = CommunityRank::query()->create([
            'name' => 'Fundadora',
            'slug' => 'fundadora',
            'is_special' => true,
            'is_active' => true,
        ]);
        $legacyBadge = CommunityBadge::query()->where('slug', 'miembro-historico')->firstOrFail();

        $this->actingAs($admin)
            ->putJson(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'user',
                'is_active' => true,
                'profile_visibility' => 'private',
                'community_rank_id' => $specialRank->id,
                'community_badges_present' => true,
                'community_badges' => [$legacyBadge->id],
                'is_legacy' => true,
                'legacy_joined_at' => '2007-06-15',
                'legacy_source' => 'exportacion-foro-clasico',
                'legacy_notes' => 'Dato interno de moderación.',
                'legacy_verified' => true,
                'profile_claimed_at' => '2026-08-30',
            ])
            ->assertOk()
            ->assertJsonPath('user.profile_visibility', 'private')
            ->assertJsonPath('user.is_legacy', true);

        $user->refresh();

        $this->assertSame($specialRank->id, $user->community_rank_id);
        $this->assertTrue($user->is_legacy);
        $this->assertTrue($user->legacy_verified);
        $this->assertSame('2007-06-15', $user->legacy_joined_at?->format('Y-m-d'));
        $this->assertSame('private', $user->profile_visibility);
        $this->assertDatabaseHas('community_badge_user', [
            'user_id' => $user->id,
            'community_badge_id' => $legacyBadge->id,
            'awarded_by' => $admin->id,
        ]);
    }

    public function test_regular_user_cannot_change_community_administration_fields(): void
    {
        $regularUser = User::factory()->create(['role' => 'user']);
        $target = User::factory()->create(['profile_visibility' => 'public']);

        $this->actingAs($regularUser)
            ->put(route('admin.users.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'role' => 'user',
                'is_active' => true,
                'profile_visibility' => 'private',
                'is_legacy' => true,
            ])
            ->assertRedirect('/');

        $this->assertSame('public', $target->refresh()->profile_visibility);
        $this->assertFalse($target->is_legacy);
    }
}
