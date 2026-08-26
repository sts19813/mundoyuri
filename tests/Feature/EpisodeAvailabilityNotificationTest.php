<?php

namespace Tests\Feature;

use App\Mail\EpisodeAvailableMail;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EpisodeAvailabilityNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_approved_episode_sends_email_when_explicitly_confirmed_for_today(): void
    {
        Mail::fake();
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
        $this->assertDatabaseMissing('episode_email_notifications', [
            'episode_id' => Episode::query()->value('id'),
        ]);
    }
}
