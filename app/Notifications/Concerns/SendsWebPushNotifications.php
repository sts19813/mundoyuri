<?php

namespace App\Notifications\Concerns;

use Illuminate\Support\Str;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

trait SendsWebPushNotifications
{
    /**
     * @return array<int, string>
     */
    protected function databaseAndWebPushChannels(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->shouldSendWebPush($notifiable)) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        $data = $this->toArray($notifiable);
        $url = $this->absoluteUrl($data['url'] ?? route('notifications.index'));

        return (new WebPushMessage)
            ->title((string) ($data['title'] ?? 'Mundo Yuri'))
            ->body((string) ($data['message'] ?? 'Tienes una nueva notificación.'))
            ->icon(asset('assets/img/pwa/icon-192.png'))
            ->badge(asset('assets/img/pwa/badge-96.png'))
            ->tag((string) ($data['kind'] ?? 'mundo-yuri-notification'))
            ->data([
                'url' => $url,
                'notification_id' => $notification->id ?? null,
                'kind' => $data['kind'] ?? null,
            ])
            ->vibrate([80, 40, 80])
            ->options(['TTL' => 3600]);
    }

    protected function shouldSendWebPush(object $notifiable): bool
    {
        if (! ($notifiable->push_notifications_enabled ?? false)) {
            return false;
        }

        if (! filled(config('webpush.vapid.public_key')) || ! filled(config('webpush.vapid.private_key'))) {
            return false;
        }

        if (! method_exists($notifiable, 'pushSubscriptions')) {
            return false;
        }

        return $notifiable->pushSubscriptions()->exists();
    }

    protected function absoluteUrl(mixed $url): string
    {
        $url = is_string($url) && $url !== '' ? $url : route('notifications.index');

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return url($url);
    }
}
