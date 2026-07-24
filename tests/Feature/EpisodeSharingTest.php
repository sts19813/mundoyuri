<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EpisodeSharingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_episode_has_prominent_sharing_options(): void
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
            ->assertSee('aria-label="Compartir episodio"', false)
            ->assertSee('aria-label="Compartir por WhatsApp"', false)
            ->assertSee('aria-label="Compartir en Facebook"', false)
            ->assertSee('aria-label="Compartir en X"', false)
            ->assertSee('aria-label="Compartir en Instagram"', false)
            ->assertSee('aria-label="Copiar enlace del episodio"', false)
            ->assertSee('data-share-network="wa"', false)
            ->assertSee('data-share-network="fb"', false)
            ->assertSee('data-share-network="x"', false)
            ->assertSee('data-share-network="ig"', false)
            ->assertSee(route('public.episodes.show', $episode->slug), false);
    }
}
