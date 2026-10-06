<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPresenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_requests_refresh_last_seen_at(): void
    {
        $user = User::factory()->create([
            'last_seen_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk();

        $this->assertTrue($user->fresh()->last_seen_at->greaterThan(now()->subMinutes(5)));
    }

    public function test_public_profile_marks_recently_seen_user_as_online_for_two_hours(): void
    {
        $online = User::factory()->create([
            'last_seen_at' => now()->subMinutes(119),
        ]);
        $offline = User::factory()->create([
            'last_seen_at' => now()->subMinutes(121),
        ]);

        $this->get($online->publicProfileUrl())
            ->assertOk()
            ->assertSee('profile-avatar-wrap is-online', false)
            ->assertSee('Conectada ahora');

        $this->get($offline->publicProfileUrl())
            ->assertOk()
            ->assertDontSee('profile-avatar-wrap is-online', false)
            ->assertDontSee('Conectada ahora');
    }

    public function test_messenger_presence_uses_one_hour_window(): void
    {
        $viewer = User::factory()->create();
        $online = User::factory()->create([
            'name' => 'Luna Activa',
            'last_seen_at' => now()->subMinutes(59),
        ]);
        $offline = User::factory()->create([
            'name' => 'Mio Ausente',
            'last_seen_at' => now()->subMinutes(61),
        ]);

        $this->actingAs($viewer)
            ->get(route('messages.show', $online))
            ->assertOk()
            ->assertSee('Conectada ahora');

        $this->actingAs($viewer)
            ->get(route('messages.show', $offline))
            ->assertOk()
            ->assertDontSee('Conectada ahora');
    }
}
