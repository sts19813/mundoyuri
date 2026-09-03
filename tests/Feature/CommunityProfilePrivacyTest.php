<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityProfilePrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_visibility_is_enforced_for_guests_members_owners_and_staff(): void
    {
        $public = User::factory()->create(['profile_visibility' => 'public']);
        $membersOnly = User::factory()->create(['profile_visibility' => 'members']);
        $private = User::factory()->create(['profile_visibility' => 'private']);
        $viewer = User::factory()->create();
        $moderator = User::factory()->create(['role' => 'moderator']);

        $this->get($public->publicProfileUrl())->assertOk();
        $this->get($membersOnly->publicProfileUrl())->assertNotFound();
        $this->get($private->publicProfileUrl())->assertNotFound();

        $this->actingAs($viewer)->get($membersOnly->publicProfileUrl())->assertOk();
        $this->actingAs($viewer)->get($private->publicProfileUrl())->assertNotFound();
        $this->actingAs($private)->get($private->publicProfileUrl())->assertOk();
        $this->actingAs($moderator)->get($private->publicProfileUrl())->assertOk();
    }

    public function test_private_favorites_are_not_exposed_through_the_dedicated_route(): void
    {
        $profileUser = User::factory()->create([
            'profile_visibility' => 'public',
            'show_favorites' => false,
        ]);
        $viewer = User::factory()->create();

        $this->get($profileUser->publicProfileUrl())->assertOk()->assertDontSee('Series favoritas');
        $this->get(route('profiles.favorites', $profileUser))->assertNotFound();
        $this->actingAs($viewer)->get(route('profiles.favorites', $profileUser))->assertNotFound();
        $this->actingAs($profileUser)->get(route('profiles.favorites', $profileUser))->assertOk();
    }

    public function test_owner_can_update_community_profile_and_privacy_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'alias' => 'onee-luna',
                'profile_visibility' => 'members',
                'show_last_seen' => '1',
                'show_join_date' => '0',
                'show_favorites' => '0',
                'show_activity' => '1',
                'location' => 'Mérida, Yucatán',
                'occupation' => 'Ilustradora',
                'website' => 'https://example.com/luna',
                'interests' => 'Manga, anime y arte digital',
                'signature_text' => 'Nos vemos en el foro.',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('members', $user->profile_visibility);
        $this->assertTrue($user->show_last_seen);
        $this->assertFalse($user->show_join_date);
        $this->assertFalse($user->show_favorites);
        $this->assertTrue($user->show_activity);
        $this->assertSame('Mérida, Yucatán', $user->location);
        $this->assertSame('Nos vemos en el foro.', $user->signature_text);
    }

    public function test_legacy_profile_displays_historical_membership_without_private_metadata(): void
    {
        $legacy = User::factory()->create([
            'name' => 'Akari Histórica',
            'alias' => 'akari-2007',
            'email' => 'legacy-private@example.com',
            'is_legacy' => true,
            'legacy_joined_at' => '2007-04-12 00:00:00',
            'legacy_source' => 'respaldo-foro-2007',
            'legacy_notes' => 'Nota privada que nunca debe salir.',
            'legacy_verified' => true,
        ]);

        $this->get($legacy->publicProfileUrl())
            ->assertOk()
            ->assertSee('Miembro histórico de Mundo Yuri')
            ->assertSee('Miembro desde 2007')
            ->assertDontSee('legacy-private@example.com')
            ->assertDontSee('respaldo-foro-2007')
            ->assertDontSee('Nota privada que nunca debe salir.');
    }
}
