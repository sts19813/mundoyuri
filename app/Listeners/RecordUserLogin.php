<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\UserStreakService;
use Illuminate\Auth\Events\Login;

class RecordUserLogin
{
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $seenAt = now();

        $event->user->forceFill([
            'last_login_at' => $seenAt,
            'last_seen_at' => $seenAt,
        ])->saveQuietly();

        app(UserStreakService::class)->record($event->user, $seenAt);
    }
}
