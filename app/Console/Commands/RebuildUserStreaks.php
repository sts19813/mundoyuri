<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserPresenceSession;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RebuildUserStreaks extends Command
{
    protected $signature = 'users:rebuild-streaks {--user= : Recalcular sólo un usuario}';

    protected $description = 'Reconstruye rachas a partir de los latidos y accesos registrados';

    public function handle(): int
    {
        $query = User::query()->orderBy('id');

        if ($userId = $this->option('user')) {
            $query->whereKey($userId);
        }

        $today = now(config('app.timezone'))->startOfDay();
        $processed = 0;

        $query->eachById(function (User $user) use ($today, &$processed): void {
            $dates = UserPresenceSession::query()
                ->where('user_id', $user->id)
                ->whereNotNull('last_seen_at')
                ->orderBy('last_seen_at')
                ->pluck('last_seen_at')
                ->map(fn ($date) => Carbon::parse($date)->setTimezone(config('app.timezone'))->toDateString());

            foreach ([$user->last_login_at, $user->last_seen_at] as $date) {
                if ($date) {
                    $dates->push($date->copy()->setTimezone(config('app.timezone'))->toDateString());
                }
            }

            $dates = $dates->unique()->sort()->values();
            $current = 0;
            $longest = 0;
            $previous = null;

            foreach ($dates as $date) {
                $day = Carbon::parse($date, config('app.timezone'))->startOfDay();
                $current = $previous?->isSameDay($day->copy()->subDay()) ? $current + 1 : 1;
                $longest = max($longest, $current);
                $previous = $day;
            }

            if (! $previous || $previous->lessThan($today->copy()->subDay())) {
                $current = 0;
            }

            $user->forceFill([
                'current_streak_days' => $current,
                'longest_streak_days' => $longest,
                'last_streak_date' => $previous,
            ])->saveQuietly();
            $processed++;
        });

        $this->info("Rachas recalculadas: {$processed}.");

        return self::SUCCESS;
    }
}
