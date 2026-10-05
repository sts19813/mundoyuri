<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\NewFollowerNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NotificationChannels\WebPush\WebPushChannel;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_subscribe_and_unsubscribe_current_browser_for_push_notifications(): void
    {
        config()->set('webpush.vapid.public_key', 'public-key');
        config()->set('webpush.vapid.private_key', 'private-key');
        config()->set('webpush.vapid.subject', 'https://mundoyuri.com');

        $user = User::factory()->create(['push_notifications_enabled' => false]);

        $payload = [
            'endpoint' => 'https://push.example.test/subscription/abc',
            'keys' => [
                'p256dh' => 'browser-public-key',
                'auth' => 'browser-auth-token',
            ],
            'contentEncoding' => 'aes128gcm',
        ];

        $this->actingAs($user)
            ->postJson(route('push-subscriptions.store'), $payload)
            ->assertOk()
            ->assertJsonPath('enabled', true);

        $this->assertTrue($user->fresh()->push_notifications_enabled);
        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_id' => $user->id,
            'endpoint' => $payload['endpoint'],
            'public_key' => 'browser-public-key',
            'auth_token' => 'browser-auth-token',
        ]);

        $this->actingAs($user)
            ->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => $payload['endpoint']])
            ->assertOk()
            ->assertJsonPath('enabled', false);

        $this->assertFalse($user->fresh()->push_notifications_enabled);
        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => $payload['endpoint'],
        ]);
    }

    public function test_database_notifications_gain_webpush_channel_when_user_enabled_push(): void
    {
        config()->set('webpush.vapid.public_key', 'public-key');
        config()->set('webpush.vapid.private_key', 'private-key');

        $recipient = User::factory()->create(['push_notifications_enabled' => true]);
        $follower = User::factory()->create();

        $recipient->updatePushSubscription(
            'https://push.example.test/subscription/abc',
            'browser-public-key',
            'browser-auth-token',
            'aes128gcm'
        );

        $channels = (new NewFollowerNotification($follower))->via($recipient);

        $this->assertContains('database', $channels);
        $this->assertContains(WebPushChannel::class, $channels);
    }
}
