<?php

namespace App\Notifications;

use App\Models\Episode;
use App\Notifications\Concerns\SendsWebPushNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EpisodeAvailableNotification extends Notification
{
    use Queueable, SendsWebPushNotifications;

    public function __construct(public Episode $episode) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->databaseAndWebPushChannels($notifiable);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->episode->loadMissing('series');

        return [
            'kind' => 'episode_available',
            'title' => 'Nuevo episodio disponible',
            'message' => $this->episode->series->title.' · '.$this->episode->title,
            'episode_id' => $this->episode->id,
            'series_id' => $this->episode->series_id,
            'url' => route('public.episodes.show', $this->episode->slug),
        ];
    }
}
