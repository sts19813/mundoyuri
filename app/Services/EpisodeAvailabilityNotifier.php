<?php

namespace App\Services;

use App\Mail\EpisodeAvailableMail;
use App\Models\Episode;
use App\Models\EpisodeEmailNotification;
use App\Models\EpisodeUserNotification;
use App\Models\User;
use App\Notifications\EpisodeAvailableNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Throwable;

class EpisodeAvailabilityNotifier
{
    public function sendFor(Episode $episode): int
    {
        if (! $this->canSendFor($episode)) {
            return 0;
        }

        $episode->loadMissing('series');
        $sent = 0;

        foreach ($this->recipients() as $recipient) {
            try {
                $sent += (int) $this->sendEmailTo($episode, $recipient['email'], $recipient['user_id']);
            } catch (Throwable) {
                // Keep delivering to the remaining recipients when invoked directly.
            }
        }

        $this->notifyUsers($episode);

        return $sent;
    }

    public function canSendFor(Episode $episode): bool
    {
        return $episode->notify_subscribers
            && $episode->moderation_status === 'approved'
            && $episode->published_at !== null;
    }

    public function sendEmailTo(Episode $episode, string $email, ?int $userId = null): bool
    {
        if (! $this->canSendFor($episode)) {
            return false;
        }

        $delivery = EpisodeEmailNotification::query()->firstOrCreate([
            'episode_id' => $episode->id,
            'email' => $email,
        ], ['user_id' => $userId]);

        if ($delivery->sent_at) {
            return false;
        }

        try {
            $episode->loadMissing('series');
            Mail::to($email)->send(new EpisodeAvailableMail($episode));
            $delivery->update(['sent_at' => now(), 'error_message' => null]);

            return true;
        } catch (Throwable $exception) {
            report($exception);
            $delivery->update(['error_message' => mb_strimwidth($exception->getMessage(), 0, 1000)]);

            throw $exception;
        }
    }

    public function notifyUser(Episode $episode, User $user): bool
    {
        if (! $this->canSendFor($episode)) {
            return false;
        }

        $delivery = EpisodeUserNotification::query()->firstOrCreate([
            'episode_id' => $episode->id,
            'user_id' => $user->id,
        ]);

        if ($delivery->notified_at) {
            return false;
        }

        try {
            $notification = new EpisodeAvailableNotification($episode);
            $alreadyInInbox = $user->notifications()
                ->where('type', EpisodeAvailableNotification::class)
                ->where('data->episode_id', $episode->id)
                ->exists();

            if ($alreadyInInbox) {
                $pushChannels = array_values(array_filter(
                    $notification->via($user),
                    fn (string $channel): bool => $channel !== 'database'
                ));

                if ($pushChannels !== []) {
                    Notification::sendNow($user, $notification, $pushChannels);
                }
            } else {
                $user->notify($notification);
            }

            $delivery->update(['notified_at' => now(), 'error_message' => null]);

            return true;
        } catch (Throwable $exception) {
            report($exception);
            $delivery->update(['error_message' => mb_strimwidth($exception->getMessage(), 0, 1000)]);

            throw $exception;
        }
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
            try {
                $sent += (int) $this->notifyUser($episode, $user);
            } catch (Throwable) {
                // Keep delivering to the remaining recipients when invoked directly.
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
