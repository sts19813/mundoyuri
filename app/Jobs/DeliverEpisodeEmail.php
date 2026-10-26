<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\User;
use App\Services\EpisodeAvailabilityNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeliverEpisodeEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 45;

    public array $backoff = [60, 300];

    public function __construct(public int $episodeId, public string $email, public ?int $userId = null)
    {
        $this->onQueue('episode-notifications');
    }

    public function handle(EpisodeAvailabilityNotifier $notifier): void
    {
        $episode = Episode::query()->find($this->episodeId);

        if (! $episode || ! $notifier->canSendFor($episode)) {
            return;
        }

        if ($this->userId !== null) {
            $user = User::query()->find($this->userId);

            if (! $user || ! $user->episode_email_notifications_enabled || $user->email !== $this->email
                || (config('episode_notifications.mode') === 'all' && ! $user->is_active)) {
                return;
            }
        } elseif (User::query()->where('email', $this->email)->where('episode_email_notifications_enabled', false)->exists()) {
            return;
        }

        $notifier->sendEmailTo($episode, $this->email, $this->userId);
    }
}
