<?php

namespace App\Services;

use App\Mail\EpisodeAvailableMail;
use App\Models\Episode;
use App\Models\EpisodeEmailNotification;
use App\Models\EpisodeUserNotification;
use App\Models\User;
use App\Notifications\EpisodeAvailableNotification;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EpisodeAvailabilityNotifier
{
    public function sendFor(Episode $episode): int
    {
        if (! $episode->notify_subscribers || $episode->moderation_status !== 'approved' || ! $episode->published_at) {
            return 0;
        }

        $episode->loadMissing('series');
        $sent = 0;

        foreach ($this->recipients() as $recipient) {
            $notification = EpisodeEmailNotification::query()->firstOrCreate([
                'episode_id' => $episode->id,
                'email' => $recipient['email'],
            ], [
                'user_id' => $recipient['user_id'],
            ]);

            if ($notification->sent_at) {
                continue;
            }

            try {
                Mail::to($recipient['email'])->send(new EpisodeAvailableMail($episode));

                $notification->update([
                    'sent_at' => now(),
                    'error_message' => null,
                ]);
                $sent++;
            } catch (Throwable $exception) {
                report($exception);

                $notification->update([
                    'error_message' => mb_strimwidth($exception->getMessage(), 0, 1000),
                ]);
            }
        }

        $this->notifyUsers($episode);

        return $sent;
    }

    /** @return iterable<array{email: string, user_id: int|null}> */
    private function recipients(): iterable
    {
        if (config('episode_notifications.mode') === 'all') {
            return User::query()
                ->where('is_active', true)
                ->where('episode_email_notifications_enabled', true)
                ->whereNotNull('email')
                ->orderBy('id')
                ->get(['id', 'email'])
                ->map(fn (User $user) => ['email' => $user->email, 'user_id' => $user->id]);
        }

        $email = trim((string) config('episode_notifications.test_recipient'));

        if ($email === '') {
            return [];
        }

        $user = User::query()->where('email', $email)->first(['id', 'episode_email_notifications_enabled']);

        if ($user && ! $user->episode_email_notifications_enabled) {
            return [];
        }

        return [['email' => $email, 'user_id' => $user?->id]];
    }

    private function notifyUsers(Episode $episode): int
    {
        $sent = 0;

        foreach ($this->userRecipients() as $user) {
            $delivery = EpisodeUserNotification::query()->firstOrCreate([
                'episode_id' => $episode->id,
                'user_id' => $user->id,
            ]);

            if ($delivery->notified_at) {
                continue;
            }

            try {
                $user->notify(new EpisodeAvailableNotification($episode));

                $delivery->update([
                    'notified_at' => now(),
                    'error_message' => null,
                ]);
                $sent++;
            } catch (Throwable $exception) {
                report($exception);

                $delivery->update([
                    'error_message' => mb_strimwidth($exception->getMessage(), 0, 1000),
                ]);
            }
        }

        return $sent;
    }

    /** @return iterable<User> */
    private function userRecipients(): iterable
    {
        if (config('episode_notifications.mode') === 'all') {
            return User::query()
                ->where('is_active', true)
                ->where(function ($query): void {
                    $query
                        ->where('episode_email_notifications_enabled', true)
                        ->orWhere('push_notifications_enabled', true);
                })
                ->orderBy('id')
                ->get();
        }

        $email = trim((string) config('episode_notifications.test_recipient'));

        if ($email === '') {
            return [];
        }

        $user = User::query()->where('email', $email)->first();

        return $user ? [$user] : [];
    }
}
