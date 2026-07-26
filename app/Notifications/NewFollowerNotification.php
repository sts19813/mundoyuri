<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFollowerNotification extends Notification
{
    use Queueable;

    public function __construct(public User $follower) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $name = $this->follower->alias ?: $this->follower->name;

        return [
            'kind' => 'new_follower',
            'title' => 'Tienes una nueva persona siguiéndote',
            'message' => $name.' comenzó a seguirte.',
            'actor_id' => $this->follower->id,
            'actor_name' => $name,
            'actor_avatar' => $this->follower->avatarUrl(),
            'url' => $this->follower->publicProfileUrl(),
        ];
    }
}
