<?php

namespace App\Services;

use App\Mail\EpisodeAvailableMail;
use App\Models\Episode;
use App\Models\EpisodeEmailNotification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EpisodeAvailabilityNotifier
{
    public function sendFor(Episode $episode): int
    {
        if (! $episode->notify_subscribers || $episode->moderation_status !== 'approved' || ! $episode->published_at || ! $episode->published_at->isToday()) {
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

        return $sent;
    }

    /** @return iterable<array{email: string, user_id: int|null}> */
    private function recipients(): iterable
    {
        if (config('episode_notifications.mode') === 'all') {
            return User::query()
                ->where('is_active', true)
                ->whereNotNull('email')
                ->orderBy('id')
                ->get(['id', 'email'])
                ->map(fn (User $user) => ['email' => $user->email, 'user_id' => $user->id]);
        }

        $email = trim((string) config('episode_notifications.test_recipient'));

        if ($email === '') {
            return [];
        }

        return [[
            'email' => $email,
            'user_id' => User::query()->where('email', $email)->value('id'),
        ]];
    }
}
