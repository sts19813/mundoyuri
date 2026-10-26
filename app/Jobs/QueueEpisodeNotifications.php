<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\User;
use App\Services\EpisodeAvailabilityNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class QueueEpisodeNotifications implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 45;

    public array $backoff = [60, 300];

    public function __construct(public int $episodeId)
    {
        $this->onQueue('episode-notifications');
    }

    public function handle(EpisodeAvailabilityNotifier $notifier): void
    {
        $episode = Episode::query()->find($this->episodeId);

        if (! $episode || ! $notifier->canSendFor($episode)) {
            return;
        }

        if (config('episode_notifications.mode') !== 'all') {
            $email = trim((string) config('episode_notifications.test_recipient'));

            if ($email === '') {
                return;
            }

            $user = User::query()->where('email', $email)->first();

            if (! $user || $user->episode_email_notifications_enabled) {
                DeliverEpisodeEmail::dispatch($episode->id, $email, $user?->id);
            }

            if ($user) {
                DeliverEpisodeUserNotification::dispatch($episode->id, $user->id);
            }

            return;
        }

        User::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->where('episode_email_notifications_enabled', true)
                    ->orWhere('push_notifications_enabled', true);
            })
            ->chunkById(100, function ($users) use ($episode): void {
                foreach ($users as $user) {
                    if ($user->episode_email_notifications_enabled && filled($user->email)) {
                        DeliverEpisodeEmail::dispatch($episode->id, $user->email, $user->id);
                    }

                    DeliverEpisodeUserNotification::dispatch($episode->id, $user->id);
                }
            });
    }
}
