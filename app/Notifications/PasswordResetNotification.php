<?php

namespace App\Notifications;

use App\Mail\PasswordResetMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): PasswordResetMail
    {
        return (new PasswordResetMail($notifiable, $this->token))
            ->to($notifiable->routeNotificationFor('mail', $this));
    }
}
