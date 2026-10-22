<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\EpisodeSource;
use App\Models\Genre;
use App\Models\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EpisodeSharingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_episode_focuses_on_watching_and_navigation_instead_of_sharing(): void
    {
        $genre = Genre::query()->create([
            'name' => 'Romance',
            'slug' => 'romance',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'title' => 'Historia compartida',
            'slug' => 'historia-compartida',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Una historia Girls Love para validar las opciones de compartir.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'title' => 'El encuentro',
            'slug' => 'el-encuentro',
            'season_number' => 1,
            'episode_number' => 2,
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        $response = $this->get(route('public.episodes.show', $episode->slug));

        $response->assertOk()
            ->assertDontSee('data-episode-share', false)
            ->assertDontSee('data-share-network', false)
            ->assertSee('Favorito')
            ->assertSee('class="player-favorite-action"', false)
            ->assertSee('data-active-source-label', false)
            ->assertSee('activeSourceLabel', false)
            ->assertSee('class="ep-number-grid"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('data-auth-open="register"', false)
            ->assertDontSee('name="alias"', false)
            ->assertDontSee('Comentarás como')
            ->assertDontSee('Guarda mi nombre y correo');
    }

    public function test_public_episode_uses_friendly_video_source_labels(): void
    {
        $genre = Genre::query()->create([
            'name' => 'Romance',
            'slug' => 'romance',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'title' => 'Historia con fuentes',
            'slug' => 'historia-con-fuentes',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Una historia Girls Love para validar las fuentes publicas.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'title' => 'Las fuentes',
            'slug' => 'las-fuentes',
            'season_number' => 1,
            'episode_number' => 3,
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        foreach ([
            [
                'episode_id' => $episode->id,
                'provider' => 'cloudflare_hls',
                'source_type' => 'full',
                'label' => 'Cloudflare HLS',
                'sort_order' => 1,
                'video_url' => 'https://video.mundoyuri.com/stream/playlist.m3u8',
                'is_primary' => true,
            ],
            [
                'episode_id' => $episode->id,
                'provider' => 'byse',
                'source_type' => 'full',
                'label' => 'BYSE',
                'sort_order' => 2,
                'video_url' => 'https://example.com/embed/byse',
                'is_primary' => false,
            ],
            [
                'episode_id' => $episode->id,
                'provider' => 'backblaze_b2',
                'source_type' => 'full',
                'label' => 'Backblaze B2',
                'sort_order' => 3,
                'video_url' => 'https://f000.backblazeb2.com/file/mundoyuri/video.mp4',
                'is_primary' => false,
            ],
        ] as $source) {
            EpisodeSource::query()->create($source);
        }

        $response = $this->get(route('public.episodes.show', $episode->slug));

        $response->assertOk()
            ->assertSee('video.mundoyuri')
            ->assertSee('Premium 👑')
            ->assertSee('Con publicidad')
            ->assertSee('Siempre ayuda a la página')
            ->assertSee('mundoyuri.com premium')
            ->assertDontSee('Cloudflare HLS')
            ->assertDontSee('Backblaze B2')
            ->assertDontSee('BYSE');
    }

    public function test_public_episode_hides_the_source_picker_when_there_is_only_one_source(): void
    {
        $genre = Genre::query()->create([
            'name' => 'Romance',
            'slug' => 'romance',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'title' => 'Historia con una fuente',
            'slug' => 'historia-con-una-fuente',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Una historia Girls Love para validar una fuente de video.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'title' => 'Una fuente',
            'slug' => 'una-fuente',
            'season_number' => 1,
            'episode_number' => 1,
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
        EpisodeSource::query()->create([
            'episode_id' => $episode->id,
            'provider' => 'cloudflare_hls',
            'source_type' => 'full',
            'label' => 'Cloudflare HLS',
            'sort_order' => 1,
            'video_url' => 'https://video.mundoyuri.com/stream/playlist.m3u8',
            'is_primary' => true,
        ]);

        $response = $this->get(route('public.episodes.show', $episode->slug));

        $response->assertOk()
            ->assertSee('episode-page-body', false)
            ->assertSee('episodeRemotePlaybackButton', false)
            ->assertSee('activeCastUrl =', false)
            ->assertSee('cast_sender.js', false)
            ->assertSee('webkitShowPlaybackTargetPicker', false)
            ->assertDontSee('Fuentes de vídeo');
    }
}
