<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\EpisodeSource;
use App\Models\EpisodeWatchProgress;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EpisodeWatchProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_save_cloudflare_progress_and_continue_from_home_on_video_host(): void
    {
        config()->set('watch_progress.hosts', ['video.mundoyuri.com']);

        $user = User::factory()->create();
        [$episode, $source] = $this->episodeWithSource('cloudflare_hls', 5);

        $this->withServerVariables(['HTTP_HOST' => 'video.mundoyuri.com'])
            ->actingAs($user)
            ->postJson("http://video.mundoyuri.com/episodios/{$episode->id}/progreso", [
                'episode_source_id' => $source->id,
                'position_seconds' => 2100,
                'duration_seconds' => 3600,
            ])
            ->assertOk()
            ->assertJsonPath('saved', true);

        $this->assertDatabaseHas('episode_watch_progress', [
            'user_id' => $user->id,
            'episode_id' => $episode->id,
            'episode_source_id' => $source->id,
            'provider' => 'cloudflare_hls',
            'position_seconds' => 2100,
            'completed' => false,
        ]);

        $html = $this->withServerVariables(['HTTP_HOST' => 'video.mundoyuri.com'])
            ->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Continuar mirando')
            ->assertSee('Serie de prueba')
            ->getContent();

        $this->assertStringContainsString('t=2100', $html);
        $this->assertStringContainsString('source='.$source->id, $html);
        $this->assertStringContainsString('T1 · E5 · 35:00', $html);
    }

    public function test_progress_is_not_available_outside_video_host(): void
    {
        config()->set('watch_progress.hosts', ['video.mundoyuri.com', 'mundoyuri.com']);

        $user = User::factory()->create();
        [$episode, $source] = $this->episodeWithSource('backblaze_b2');

        $this->withServerVariables(['HTTP_HOST' => 'example.com'])
            ->actingAs($user)
            ->postJson("http://example.com/episodios/{$episode->id}/progreso", [
                'episode_source_id' => $source->id,
                'position_seconds' => 60,
                'duration_seconds' => 1200,
            ])
            ->assertNotFound();
    }

    public function test_member_can_save_progress_from_main_mundoyuri_host(): void
    {
        config()->set('watch_progress.hosts', ['video.mundoyuri.com', 'mundoyuri.com']);

        $user = User::factory()->create();
        [$episode, $source] = $this->episodeWithSource('backblaze_b2');

        $this->withServerVariables(['HTTP_HOST' => 'mundoyuri.com'])
            ->actingAs($user)
            ->postJson("http://mundoyuri.com/episodios/{$episode->id}/progreso", [
                'episode_source_id' => $source->id,
                'position_seconds' => 40,
                'duration_seconds' => 1200,
            ])
            ->assertOk();

        $this->assertDatabaseHas('episode_watch_progress', [
            'user_id' => $user->id,
            'episode_id' => $episode->id,
            'provider' => 'backblaze_b2',
            'position_seconds' => 40,
        ]);
    }

    public function test_progress_rejects_non_video_providers(): void
    {
        config()->set('watch_progress.hosts', ['video.mundoyuri.com']);

        $user = User::factory()->create();
        [$episode, $source] = $this->episodeWithSource('youtube');

        $this->withServerVariables(['HTTP_HOST' => 'video.mundoyuri.com'])
            ->actingAs($user)
            ->postJson("http://video.mundoyuri.com/episodios/{$episode->id}/progreso", [
                'episode_source_id' => $source->id,
                'position_seconds' => 60,
                'duration_seconds' => 1200,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('episode_source_id');
    }

    public function test_only_latest_ten_progress_records_are_kept_per_user(): void
    {
        config()->set('watch_progress.hosts', ['video.mundoyuri.com']);

        $user = User::factory()->create();
        $oldestEpisode = null;

        foreach (range(1, 11) as $number) {
            [$episode, $source] = $this->episodeWithSource('backblaze_b2', $number, "serie-{$number}");
            $oldestEpisode ??= $episode;

            $this->withServerVariables(['HTTP_HOST' => 'video.mundoyuri.com'])
                ->actingAs($user)
                ->postJson("http://video.mundoyuri.com/episodios/{$episode->id}/progreso", [
                    'episode_source_id' => $source->id,
                    'position_seconds' => 45 + $number,
                    'duration_seconds' => 1200,
                ])
                ->assertOk();
        }

        $this->assertSame(10, EpisodeWatchProgress::query()->where('user_id', $user->id)->count());
        $this->assertDatabaseMissing('episode_watch_progress', [
            'user_id' => $user->id,
            'episode_id' => $oldestEpisode->id,
        ]);
    }

    public function test_completed_episode_recommends_the_next_published_backblaze_episode(): void
    {
        config()->set('watch_progress.hosts', ['video.mundoyuri.com']);

        $user = User::factory()->create();
        [$episodeFive, $sourceFive] = $this->episodeWithSource('backblaze_b2', 5);

        $this->withServerVariables(['HTTP_HOST' => 'video.mundoyuri.com'])
            ->actingAs($user)
            ->postJson("http://video.mundoyuri.com/episodios/{$episodeFive->id}/progreso", [
                'episode_source_id' => $sourceFive->id,
                'position_seconds' => 1800,
                'duration_seconds' => 1800,
                'completed' => true,
            ])
            ->assertOk();

        $episodeSix = Episode::query()->create([
            'series_id' => $episodeFive->series_id,
            'title' => 'Capitulo 6',
            'slug' => 'serie-de-prueba-s1e6',
            'season_number' => 1,
            'episode_number' => 6,
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
        $sourceSix = EpisodeSource::query()->create([
            'episode_id' => $episodeSix->id,
            'provider' => 'backblaze_b2',
            'source_type' => 'full',
            'label' => 'Principal',
            'sort_order' => 0,
            'video_url' => 'https://video.mundoyuri.com/episode-6.mp4',
            'is_primary' => true,
        ]);

        $html = $this->withServerVariables(['HTTP_HOST' => 'video.mundoyuri.com'])
            ->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Continuar mirando')
            ->assertSee('Continuar con T1 · E6')
            ->assertSee('Capitulo 6')
            ->getContent();

        $this->assertStringContainsString('serie-de-prueba-s1e6?source='.$sourceSix->id, $html);
        $this->assertStringNotContainsString('t=1800', $html);
    }

    /** @return array{Episode, EpisodeSource} */
    private function episodeWithSource(string $provider, int $episodeNumber = 1, string $seriesSlug = 'serie-de-prueba'): array
    {
        $genre = Genre::query()->firstOrCreate(
            ['slug' => 'romance'],
            ['name' => 'Romance', 'is_active' => true]
        );

        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'title' => 'Serie de prueba',
            'slug' => $seriesSlug,
            'content_type' => 'series',
            'catalog_section' => 'series-gl',
            'status' => 'ongoing',
            'description' => 'Serie para pruebas de progreso.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'title' => "Capitulo {$episodeNumber}",
            'slug' => "{$seriesSlug}-s1e{$episodeNumber}",
            'season_number' => 1,
            'episode_number' => $episodeNumber,
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        $source = EpisodeSource::query()->create([
            'episode_id' => $episode->id,
            'provider' => $provider,
            'source_type' => 'full',
            'label' => 'Principal',
            'sort_order' => 0,
            'video_url' => $provider === 'cloudflare_hls'
                ? 'https://video.mundoyuri.com/stream/playlist.m3u8'
                : 'https://video.mundoyuri.com/video.mp4',
            'is_primary' => true,
        ]);

        return [$episode, $source];
    }
}
