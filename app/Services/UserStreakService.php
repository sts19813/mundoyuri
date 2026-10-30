<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class UserStreakService
{
    /**
     * Register one eligible day of activity. Multiple page loads, devices, or
     * heartbeats on the same date never increment a streak more than once.
     */
    public function record(User $user, ?CarbonInterface $occurredAt = null): User
    {
        $activityDate = ($occurredAt ?: now())
            ->copy()
            ->setTimezone(config('app.timezone'))
            ->startOfDay();

        $trackedUser = DB::transaction(function () use ($user, $activityDate): User {
            /** @var User $trackedUser */
            $trackedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $lastDate = $trackedUser->last_streak_date?->copy()->startOfDay();

            if ($lastDate?->isSameDay($activityDate)) {
                return $trackedUser;
            }

            $continuesYesterday = $lastDate?->isSameDay($activityDate->copy()->subDay()) ?? false;
            $currentStreak = $continuesYesterday
                ? max(1, $trackedUser->current_streak_days) + 1
                : 1;

            $trackedUser->forceFill([
                'current_streak_days' => $currentStreak,
                'longest_streak_days' => max($currentStreak, $trackedUser->longest_streak_days),
                'last_streak_date' => $activityDate,
            ])->saveQuietly();

            return $trackedUser;
        }, 3);

        // Keep the authenticated instance in sync as well. The navigation and
        // profile UI can be rendered during this same request.
        $user->setAttribute('current_streak_days', $trackedUser->current_streak_days);
        $user->setAttribute('longest_streak_days', $trackedUser->longest_streak_days);
        $user->setAttribute('last_streak_date', $trackedUser->last_streak_date);

        return $trackedUser;
    }

    /**
     * A streak remains current through the day after the last activity, so a
     * member has all of today to return before the consecutive run is broken.
     */
    public function isCurrent(User $user, ?CarbonInterface $at = null): bool
    {
        return $user->activeStreakDays($at) > 0;
    }
}
