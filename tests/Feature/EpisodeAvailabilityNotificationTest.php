<?php

namespace Tests\Feature;

use App\Jobs\DeliverEpisodeEmail;
use App\Jobs\DeliverEpisodeUserNotification;
use App\Jobs\QueueEpisodeNotifications;
use App\Mail\EpisodeAvailableMail;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use App\Services\EpisodeAvailabilityNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EpisodeAvailabilityNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_approved_episode_sends_email_when_explicitly_confirmed_for_today(): void
    {
        Mail::fake();
        Queue::fake();
        config()->set('episode_notifications.mode', 'test');
        config()->set('episode_notifications.test_recipient', 'sts19813@gmail.com');

        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::query()->create(['name' => 'Drama', 'slug' => 'drama', 'is_active' => true]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Serie de prueba',
            'slug' => 'serie-de-prueba',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripción para probar el correo de nuevo episodio.',
            'cover_image' => 'https://cdn.example.com/serie-de-prueba-portada.jpg',
            'moderation_status' => 'approved',
        ]);

        $this->actingAs($admin)->post(route('admin.episodes.store'), [
            'series_id' => $series->id,
            'season_number' => 1,
            'episode_number' => 4,
            'release_date' => now()->toDateString(),
            'published_at' => now()->format('Y-m-d\\TH:i'),
            'notify_subscribers' => true,
            'source_provider' => ['backblaze_b2'],
            'source_type' => ['full'],
            'source_url' => ['https://f000.backblazeb2.com/file/mundoyuri/serie-de-prueba-e4.mp4'],
            'source_label' => ['Backblaze B2'],
            'source_sort_order' => [1],
            'source_primary' => 0,
        ])->assertRedirect(route('admin.episodes.index'));

        $episode = Episode::query()->firstOrFail();

        $this->assertSame('Episodio 4', $episode->title);
        Mail::assertNothingSent();
        Queue::assertPushedOn('episode-notifications', QueueEpisodeNotifications::class);
        Queue::pushed(QueueEpisodeNotifications::class)->first()
            ->handle(app(EpisodeAvailabilityNotifier::class));
        Queue::assertPushedOn('episode-notifications', DeliverEpisodeEmail::class);
        Queue::pushed(DeliverEpisodeEmail::class)->first()
            ->handle(app(EpisodeAvailabilityNotifier::class));

        Mail::assertSent(EpisodeAvailableMail::class, function (EpisodeAvailableMail $mail) use ($episode): bool {
            return $mail->hasTo('sts19813@gmail.com')
                && $mail->episode->is($episode)
                && str_contains($mail->render(), route('public.episodes.show', $episode->slug))
                && str_contains($mail->render(), 'https://cdn.example.com/serie-de-prueba-portada.jpg');
        });
        $this->assertDatabaseHas('episode_email_notifications', [
            'episode_id' => $episode->id,
            'email' => 'sts19813@gmail.com',
        ]);
    }

    public function test_new_episode_does_not_notify_without_the_explicit_confirmation(): void
    {
        Mail::fake();
        Queue::fake();
        config()->set('episode_notifications.mode', 'test');
        config()->set('episode_notifications.test_recipient', 'sts19813@gmail.com');

        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::query()->create(['name' => 'Drama', 'slug' => 'drama', 'is_active' => true]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Serie sin aviso',
            'slug' => 'serie-sin-aviso',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripción para probar que no se envía correo.',
            'moderation_status' => 'approved',
        ]);

        $this->actingAs($admin)->post(route('admin.episodes.store'), [
            'series_id' => $series->id,
            'season_number' => 1,
            'episode_number' => 1,
            'published_at' => now()->format('Y-m-d\\TH:i'),
            'source_provider' => ['backblaze_b2'],
            'source_type' => ['full'],
            'source_url' => ['https://f000.backblazeb2.com/file/mundoyuri/serie-sin-aviso-e1.mp4'],
            'source_label' => ['Backblaze B2'],
            'source_sort_order' => [1],
            'source_primary' => 0,
        ])->assertRedirect(route('admin.episodes.index'));

        Mail::assertNothingSent();
        Queue::assertNotPushed(QueueEpisodeNotifications::class);
        $this->assertDatabaseMissing('episode_email_notifications', [
            'episode_id' => Episode::query()->value('id'),
        ]);
    }

    public function test_create_form_keeps_email_notification_switch_enabled_when_publication_date_is_not_today(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::query()->create(['name' => 'Drama', 'slug' => 'drama', 'is_active' => true]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Serie con aviso manual',
            'slug' => 'serie-con-aviso-manual',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripción para probar el formulario.',
            'moderation_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.episodes.create', ['series_id' => $series->id]));

        $response->assertOk();
        preg_match('/<input[^>]*id="episode-notify-subscribers"[^>]*>/i', $response->getContent(), $matches);

        $this->assertNotEmpty($matches);
        $this->assertStringNotContainsString('disabled', $matches[0]);
        $this->assertStringNotContainsString('checked', $matches[0]);
    }

    public function test_new_approved_episode_can_send_email_when_publication_date_is_not_today(): void
    {
        Mail::fake();
        Queue::fake();
        config()->set('episode_notifications.mode', 'test');
        config()->set('episode_notifications.test_recipient', 'sts19813@gmail.com');

        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::query()->create(['name' => 'Drama', 'slug' => 'drama', 'is_active' => true]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Pelicula de prueba',
            'slug' => 'pelicula-de-prueba',
            'content_type' => 'movie',
            'status' => 'completed',
            'description' => 'Descripción para probar el correo con fecha historica.',
            'moderation_status' => 'approved',
        ]);

        $this->actingAs($admin)->post(route('admin.episodes.store'), [
            'series_id' => $series->id,
            'season_number' => 1,
            'episode_number' => 0,
            'release_date' => now()->subYears(3)->toDateString(),
            'published_at' => now()->subMonth()->format('Y-m-d\\TH:i'),
            'notify_subscribers' => true,
            'source_provider' => ['backblaze_b2'],
            'source_type' => ['full'],
            'source_url' => ['https://f000.backblazeb2.com/file/mundoyuri/pelicula-de-prueba.mp4'],
            'source_label' => ['Backblaze B2'],
            'source_sort_order' => [1],
            'source_primary' => 0,
        ])->assertRedirect(route('admin.episodes.index'));

        $episode = Episode::query()->firstOrFail();

        Mail::assertNothingSent();
        Queue::pushed(QueueEpisodeNotifications::class)->first()
            ->handle(app(EpisodeAvailabilityNotifier::class));
        Queue::pushed(DeliverEpisodeEmail::class)->first()
            ->handle(app(EpisodeAvailabilityNotifier::class));

        Mail::assertSent(EpisodeAvailableMail::class, function (EpisodeAvailableMail $mail) use ($episode): bool {
            return $mail->hasTo('sts19813@gmail.com')
                && $mail->episode->is($episode);
        });
        $this->assertDatabaseHas('episode_email_notifications', [
            'episode_id' => $episode->id,
            'email' => 'sts19813@gmail.com',
        ]);
    }

    public function test_queued_email_and_system_notifications_are_independent_and_idempotent(): void
    {
        Mail::fake();
        Queue::fake();
        config()->set('episode_notifications.mode', 'all');

        $admin = User::factory()->create(['role' => 'admin', 'episode_email_notifications_enabled' => false]);
        $emailUser = User::factory()->create(['episode_email_notifications_enabled' => true]);
        $pushUser = User::factory()->create([
            'episode_email_notifications_enabled' => false,
            'push_notifications_enabled' => true,
        ]);
        User::factory()->create([
            'episode_email_notifications_enabled' => false,
            'push_notifications_enabled' => false,
        ]);
        $genre = Genre::query()->create(['name' => 'Drama', 'slug' => 'drama', 'is_active' => true]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Serie con cola',
            'slug' => 'serie-con-cola',
            'description' => 'Notificaciones en segundo plano.',
            'moderation_status' => 'approved',
        ]);
        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'created_by' => $admin->id,
            'title' => 'Episodio 1',
            'slug' => 'serie-con-cola-s1e1',
            'season_number' => 1,
            'episode_number' => 1,
            'moderation_status' => 'approved',
            'published_at' => now(),
            'notify_subscribers' => true,
        ]);

        QueueEpisodeNotifications::dispatch($episode->id);
        Queue::pushed(QueueEpisodeNotifications::class)->first()
            ->handle(app(EpisodeAvailabilityNotifier::class));

        Queue::assertPushed(DeliverEpisodeEmail::class, 1);
        Queue::assertPushed(DeliverEpisodeUserNotification::class, 2);

        $emailJob = Queue::pushed(DeliverEpisodeEmail::class)->first();
        $emailJob->handle(app(EpisodeAvailabilityNotifier::class));
        $emailJob->handle(app(EpisodeAvailabilityNotifier::class));

        foreach (Queue::pushed(DeliverEpisodeUserNotification::class) as $job) {
            $job->handle(app(EpisodeAvailabilityNotifier::class));
            $job->handle(app(EpisodeAvailabilityNotifier::class));
        }

        Mail::assertSent(EpisodeAvailableMail::class, 1);
        Mail::assertSent(EpisodeAvailableMail::class, fn (EpisodeAvailableMail $mail): bool => $mail->hasTo($emailUser->email));
        $this->assertDatabaseCount('episode_user_notifications', 2);
        $this->assertSame(1, $emailUser->notifications()->count());
        $this->assertSame(1, $pushUser->notifications()->count());
    }

    public function test_database_worker_delivers_episode_emails_and_system_notifications(): void
    {
        Mail::fake();
        config()->set('queue.default', 'database');
        config()->set('episode_notifications.mode', 'all');

        $user = User::factory()->create(['episode_email_notifications_enabled' => true]);
        $genre = Genre::query()->create(['name' => 'Drama', 'slug' => 'drama', 'is_active' => true]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $user->id,
            'title' => 'Serie en cola real',
            'slug' => 'serie-cola-real',
            'description' => 'Prueba del trabajador de base de datos.',
            'moderation_status' => 'approved',
        ]);
        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'created_by' => $user->id,
            'title' => 'Episodio 1',
            'slug' => 'serie-cola-real-s1e1',
            'season_number' => 1,
            'episode_number' => 1,
            'moderation_status' => 'approved',
            'published_at' => now(),
            'notify_subscribers' => true,
        ]);

        QueueEpisodeNotifications::dispatch($episode->id);
        Mail::assertNothingSent();
        $this->assertDatabaseCount('jobs', 1);

        Artisan::call('queue:work', [
            'connection' => 'database',
            '--queue' => 'episode-notifications',
            '--stop-when-empty' => true,
            '--tries' => 3,
        ]);

        Mail::assertSent(EpisodeAvailableMail::class, 1);
        $this->assertSame(1, $user->notifications()->count());
        $this->assertDatabaseCount('jobs', 0);
    }
}
