<?php

namespace App\Notifications;

use App\Notifications\Concerns\SendsWebPushNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BadgeAwardedNotification extends Notification
{
    use Queueable, SendsWebPushNotifications;

    public function __construct(public int $badgeId, public string $badgeName, public string $profileUrl, public int $awardLogId) {}

    public function via(object $notifiable): array
    {
        return $this->databaseAndWebPushChannels($notifiable);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'badge_awarded',
            'title' => '¡Desbloqueaste una insignia!',
            'message' => 'Has obtenido la insignia '.$this->badgeName.'.',
            'badge_id' => $this->badgeId,
            'award_log_id' => $this->awardLogId,
            'url' => $this->profileUrl,
        ];
    }
}
