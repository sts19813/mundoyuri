<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPresenceSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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

    public function test_presence_heartbeat_accumulates_user_session_time(): void
    {
        $user = User::factory()->create();
        $sessionId = '11111111-1111-4111-8111-111111111111';
        $startedAt = now();

        Carbon::setTestNow($startedAt);

        $this->actingAs($user)
            ->postJson(route('presence.heartbeat'), [
                'session_id' => $sessionId,
                'path' => '/series',
                'title' => 'Series',
            ])
            ->assertOk()
            ->assertJsonPath('saved', true)
            ->assertJsonPath('total_seconds', 0);

        Carbon::setTestNow($startedAt->copy()->addSeconds(35));

        $this->actingAs($user)
            ->postJson(route('presence.heartbeat'), [
                'session_id' => $sessionId,
                'path' => '/series/gl',
                'title' => 'Series GL',
                'ending' => true,
            ])
            ->assertOk()
            ->assertJsonPath('total_seconds', 35);

        $this->assertDatabaseHas('user_presence_sessions', [
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'current_path' => '/series/gl',
            'current_title' => 'Series GL',
            'total_seconds' => 35,
            'heartbeat_count' => 2,
        ]);

        $this->assertNotNull(UserPresenceSession::query()->firstWhere('session_id', $sessionId)?->ended_at);

        Carbon::setTestNow();
    }

    public function test_admin_dashboard_shows_presence_sections(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Luna Tiempo',
            'alias' => 'Luna',
        ]);

        UserPresenceSession::query()->create([
            'user_id' => $member->id,
            'session_id' => '22222222-2222-4222-8222-222222222222',
            'started_at' => now()->subHours(2),
            'last_seen_at' => now()->subSeconds(30),
            'total_seconds' => 7200,
            'heartbeat_count' => 8,
            'current_path' => '/series',
            'current_title' => 'Series',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Usuarios activos ahora')
            ->assertSee('Horas acumuladas')
            ->assertSee('Usuarios viendo ahora')
            ->assertSee('Usuarios con más tiempo')
            ->assertSee('Luna')
            ->assertSee('/series');
    }
}
