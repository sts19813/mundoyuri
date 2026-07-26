<?php

namespace App\Notifications;

use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewDirectMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public DirectMessage $directMessage,
        public User $sender,
    ) {}

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
        $name = $this->sender->alias ?: $this->sender->name;

        return [
            'kind' => 'direct_message',
            'title' => 'Nuevo mensaje de '.$name,
            'message' => Str::limit($this->directMessage->body, 100),
            'actor_id' => $this->sender->id,
            'actor_name' => $name,
            'actor_avatar' => $this->sender->avatarUrl(),
            'direct_message_id' => $this->directMessage->id,
            'url' => route('messages.show', $this->sender),
        ];
    }
}
