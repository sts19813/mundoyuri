<?php

namespace App\Services;

use App\Models\BadgeAwardLog;
use App\Notifications\BadgeAwardedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Throwable;

class BadgeNotificationService
{
    public function notifyAward(BadgeAwardLog $award): bool
    {
        return DB::transaction(function () use ($award): bool {
            $log = BadgeAwardLog::query()->with('user')->lockForUpdate()->findOrFail($award->id);
            if ($log->action !== 'assigned' || $log->notified_at || ! $log->user || ! $log->badge_id) {
                return false;
            }

            $user = $log->user;
            $notification = new BadgeAwardedNotification($log->badge_id, $log->badge_name, $user->publicProfileUrl(), $log->id);
            Notification::sendNow($user, $notification, ['database']);
            $log->forceFill(['notified_at' => now()])->save();

            DB::afterCommit(function () use ($user, $notification): void {
                try {
                    $pushChannels = array_values(array_diff($notification->via($user), ['database']));
                    if ($pushChannels) {
                        Notification::sendNow($user, $notification, $pushChannels);
                    }
                } catch (Throwable $exception) {
                    report($exception);
                }
            });

            return true;
        });
    }
}
