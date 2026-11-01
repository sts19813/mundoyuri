<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserPresenceSession;
use App\Services\BadgeAutomationService;
use Database\Seeders\CommunityBadgeCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HalloweenBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_presence_awards_once_during_the_local_event_day_and_keeps_the_badge(): void
    {
        $this->seed(CommunityBadgeCatalogSeeder::class);
        $user = User::factory()->create(['id' => 2]);
        $badge = Badge::where('slug', 'halloween-yuri-2026')->firstOrFail();

        Carbon::setTestNow(Carbon::parse('2026-10-30 23:59:59', 'America/Merida'));
        $this->actingAs($user)->get(route('home'))->assertOk();
        $this->assertDatabaseMissing('badge_user', ['user_id' => $user->id, 'badge_id' => $badge->id]);

        Carbon::setTestNow(Carbon::parse('2026-10-31 00:00:00', 'America/Merida'));
        $this->actingAs($user)->get(route('home'))->assertOk();
        $this->actingAs($user)->get(route('home'))->assertOk();
        $this->assertDatabaseHas('badge_user', ['user_id' => $user->id, 'badge_id' => $badge->id, 'automation_rule' => 'halloween-presencia-2026']);
        $this->assertSame(1, $user->badges()->whereKey($badge->id)->count());
        $this->assertSame(1, \App\Models\BadgeAwardLog::where('user_id', $user->id)->where('badge_id', $badge->id)->count());

        Carbon::setTestNow(Carbon::parse('2026-11-01 00:00:00', 'America/Merida'));
        app(BadgeAutomationService::class)->sync($user->fresh());
        $this->assertDatabaseHas('badge_user', ['user_id' => $user->id, 'badge_id' => $badge->id]);
        $lateUser = User::factory()->create(['id' => 3]);
        $this->actingAs($lateUser)->get(route('home'))->assertOk();
        $this->assertDatabaseMissing('badge_user', ['user_id' => $lateUser->id, 'badge_id' => $badge->id]);
    }

    public function test_backfill_finds_today_activity_without_awarding_inactive_users(): void
    {
        $badge = Badge::where('slug', 'halloween-yuri-2026')->firstOrFail();
        Carbon::setTestNow(Carbon::parse('2026-10-31 20:00:00', 'America/Merida'));
        $seen = User::factory()->create(['id' => 2, 'last_seen_at' => now()->subHour()]);
        $login = User::factory()->create(['id' => 3, 'last_login_at' => now()->subHours(2)]);
        $session = User::factory()->create(['id' => 4, 'last_seen_at' => null, 'last_login_at' => null]);
        $absent = User::factory()->create(['id' => 5, 'last_seen_at' => now()->subDays(2), 'last_login_at' => null]);
        UserPresenceSession::create([
            'user_id' => $session->id, 'session_id' => '11111111-1111-4111-8111-111111111111',
            'started_at' => now()->subHours(3), 'last_seen_at' => now()->subHours(2),
            'total_seconds' => 1, 'heartbeat_count' => 1, 'current_path' => '/',
        ]);

        $this->artisan('badges:award-halloween-2026')->assertSuccessful();
        $this->artisan('badges:award-halloween-2026')->assertSuccessful();
        foreach ([$seen, $login, $session] as $user) {
            $this->assertDatabaseHas('badge_user', ['user_id' => $user->id, 'badge_id' => $badge->id]);
        }
        $this->assertDatabaseMissing('badge_user', ['user_id' => $absent->id, 'badge_id' => $badge->id]);
        $this->assertDatabaseCount('badge_award_logs', 3);
    }
}
