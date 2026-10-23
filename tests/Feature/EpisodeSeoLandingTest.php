<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\EpisodeSource;
use App\Models\Genre;
use App\Models\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EpisodeSeoLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_episode_page_targets_free_episode_searches_with_structured_video_data(): void
    {
        $genre = Genre::query()->create([
            'name' => 'Romance',
            'slug' => 'romance',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'title' => 'Pls.Love',
            'slug' => 'plslove',
            'content_type' => 'series',
            'catalog_section' => 'series-gl',
            'status' => 'ongoing',
            'description' => 'Una historia romántica sobre dos jóvenes que aprenden a confiar.',
            'banner_image' => 'https://cdn.example.com/pls-love-banner.webp',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'title' => 'Una nueva oportunidad',
            'slug' => 'pls-love-s1e7',
            'season_number' => 1,
            'episode_number' => 7,
            'release_date' => '2026-10-23',
            'duration_minutes' => 48,
            'description' => 'Las protagonistas deben decidir si están listas para avanzar juntas.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
        EpisodeSource::query()->create([
            'episode_id' => $episode->id,
            'provider' => 'cloudflare_hls',
            'source_type' => 'full',
            'label' => 'Principal',
            'sort_order' => 1,
            'video_url' => 'https://video.mundoyuri.com/pls-love/e07/master.m3u8',
            'is_primary' => true,
        ]);

        $response = $this->get(route('public.episodes.show', $episode->slug));

        $response->assertOk()
            ->assertSee('<title>Pls.Love episodio 7 gratis sub español | Mundo Yuri</title>', false)
            ->assertSee('Mira Pls.Love episodio 7 gratis y online, subtitulado en español.', false)
            ->assertSee('<h1>Pls.Love episodio 7</h1>', false)
            ->assertSee('<link rel="canonical" href="'.route('public.episodes.show', $episode->slug).'">', false)
            ->assertSee('Más episodios de Pls.Love')
            ->assertSee('"@type":"TVEpisode"', false)
            ->assertSee('"@type":"VideoObject"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('"alternateName":["Pls.Love episodio 7","Pls.Love capítulo 7","Pls.Love capitulo 7","Pls Love episodio 7"', false)
            ->assertSee('"contentUrl":"https://video.mundoyuri.com/pls-love/e07/master.m3u8"', false)
            ->assertSee('"isAccessibleForFree":true', false);
    }
}
