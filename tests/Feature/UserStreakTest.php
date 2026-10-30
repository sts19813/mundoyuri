<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserStreakService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStreakTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_streak_counts_only_one_activity_per_day_and_preserves_the_best_run(): void
    {
        $user = User::factory()->create();
        $service = app(UserStreakService::class);

        $service->record($user, Carbon::parse('2026-10-25 08:00:00', config('app.timezone')));
        $service->record($user, Carbon::parse('2026-10-25 22:30:00', config('app.timezone')));
        $service->record($user, Carbon::parse('2026-10-26 09:00:00', config('app.timezone')));

        $user->refresh();

        $this->assertSame(2, $user->current_streak_days);
        $this->assertSame(2, $user->longest_streak_days);
        $this->assertSame('2026-10-26', $user->last_streak_date->toDateString());

        $service->record($user, Carbon::parse('2026-10-28 09:00:00', config('app.timezone')));

        $user->refresh();

        $this->assertSame(1, $user->current_streak_days);
        $this->assertSame(2, $user->longest_streak_days);
    }

    public function test_admin_can_view_the_streaks_table(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'current_streak_days' => 6,
            'longest_streak_days' => 12,
            'last_streak_date' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['tab' => 'streaks']))
            ->assertOk()
            ->assertSeeText('Rachas de actividad')
            ->assertSeeText($member->displayName())
            ->assertSeeText('6 días');
    }
}
