<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\User;
use App\Services\EpisodeAvailabilityNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeliverEpisodeUserNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 45;

    public array $backoff = [60, 300];

    public function __construct(public int $episodeId, public int $userId)
    {
        $this->onQueue('episode-notifications');
    }

    public function handle(EpisodeAvailabilityNotifier $notifier): void
    {
        $episode = Episode::query()->find($this->episodeId);
        $user = User::query()->find($this->userId);

        if (! $episode || ! $user || ! $notifier->canSendFor($episode)) {
            return;
        }

        if (config('episode_notifications.mode') === 'all') {
            if (! $user->is_active || (! $user->episode_email_notifications_enabled && ! $user->push_notifications_enabled)) {
                return;
            }
        } elseif ($user->email !== trim((string) config('episode_notifications.test_recipient'))) {
            return;
        }

        $notifier->notifyUser($episode, $user);
    }
}
