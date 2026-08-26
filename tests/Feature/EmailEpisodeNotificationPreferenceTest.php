<?php

namespace Tests\Feature;

use App\Mail\EpisodeAvailableMail;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use App\Services\EpisodeAvailabilityNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailEpisodeNotificationPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_pause_and_restore_episode_emails(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->episode_email_notifications_enabled);

        $this->actingAs($user)
            ->patch(route('email-episode-notifications.update'), ['enabled' => false])
            ->assertRedirect();

        $this->assertFalse($user->refresh()->episode_email_notifications_enabled);

        $this->actingAs($user)
            ->patch(route('email-episode-notifications.update'), ['enabled' => true])
            ->assertRedirect();

        $this->assertTrue($user->refresh()->episode_email_notifications_enabled);
    }

    public function test_episode_mail_is_only_sent_to_users_who_have_it_enabled(): void
    {
        Mail::fake();
        config()->set('episode_notifications.mode', 'all');

        $subscribed = User::factory()->create([
            'email' => 'suscrita@example.com',
            'episode_email_notifications_enabled' => true,
        ]);
        User::factory()->create([
            'email' => 'pausada@example.com',
            'episode_email_notifications_enabled' => false,
        ]);
        $genre = Genre::query()->create(['name' => 'Romance', 'slug' => 'romance', 'is_active' => true]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $subscribed->id,
            'title' => 'Entre nosotras',
            'slug' => 'entre-nosotras',
            'description' => 'Serie de prueba.',
            'moderation_status' => 'approved',
        ]);
        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'created_by' => $subscribed->id,
            'title' => 'Episodio 2',
            'slug' => 'entre-nosotras-episodio-2',
            'season_number' => 1,
            'episode_number' => 2,
            'moderation_status' => 'approved',
            'published_at' => now(),
            'notify_subscribers' => true,
        ]);

        $sent = app(EpisodeAvailabilityNotifier::class)->sendFor($episode);

        $this->assertSame(1, $sent);
        Mail::assertSent(EpisodeAvailableMail::class, 1);
        Mail::assertSent(EpisodeAvailableMail::class, fn (EpisodeAvailableMail $mail): bool => $mail->hasTo('suscrita@example.com'));
        Mail::assertNotSent(EpisodeAvailableMail::class, fn (EpisodeAvailableMail $mail): bool => $mail->hasTo('pausada@example.com'));
    }
}
