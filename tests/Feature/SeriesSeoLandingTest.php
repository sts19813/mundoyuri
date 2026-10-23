<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeriesSeoLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_series_page_exposes_search_friendly_content_and_structured_data(): void
    {
        $genre = $this->genre();
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'title' => 'Pls.Love',
            'slug' => 'plslove',
            'content_type' => 'series',
            'catalog_section' => 'series-gl',
            'status' => 'ongoing',
            'description' => 'Dos jóvenes descubren que el amor puede aparecer cuando menos lo esperan.',
            'country_of_origin' => 'Tailandia',
            'release_year' => 2026,
            'total_seasons' => 1,
            'banner_image' => 'https://cdn.example.com/pls-love-banner.webp',
            'cover_image' => 'https://cdn.example.com/pls-love-cover.webp',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
        Episode::query()->create([
            'series_id' => $series->id,
            'title' => 'El comienzo',
            'slug' => 'pls-love-s1e1',
            'season_number' => 1,
            'episode_number' => 1,
            'release_date' => '2026-10-20',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        $response = $this->get(route('catalog.series.show', $series->slug));

        $response->assertOk()
            ->assertSee('<title>Pls.Love: serie GL subtitulada en español | Mundo Yuri</title>', false)
            ->assertSee('Descubre y mira Pls.Love, serie GL subtitulada en español.', false)
            ->assertSee('<link rel="canonical" href="'.route('catalog.series.show', $series->slug).'">', false)
            ->assertSee('Sinopsis de Pls.Love')
            ->assertSee('También conocida como:')
            ->assertSee('Pls Love')
            ->assertSee('PlsLove')
            ->assertSee('Episodios de Pls.Love')
            ->assertSee(route('public.episodes.show', 'pls-love-s1e1'), false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('"@type":"TVSeries"', false)
            ->assertSee('"alternateName":["Pls Love","PlsLove"]', false)
            ->assertSee('https://cdn.example.com/pls-love-banner.webp', false)
            ->assertSee('https://cdn.example.com/pls-love-cover.webp', false);
    }

    public function test_custom_aliases_and_seo_copy_are_used_without_duplicates(): void
    {
        $series = Series::query()->create([
            'genre_id' => $this->genre()->id,
            'title' => 'Pls.Love',
            'alternate_titles' => ['Please Love', 'PLS LOVE', 'Please Love'],
            'seo_title' => 'Pls Love: dónde ver la serie GL',
            'seo_description' => 'Mira Pls Love en español y descubre su historia, reparto y episodios disponibles.',
            'slug' => 'pls-love-seo',
            'content_type' => 'series',
            'catalog_section' => 'series-gl',
            'status' => 'ongoing',
            'description' => 'Sinopsis de prueba.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        $this->get(route('catalog.series.show', $series->slug))
            ->assertOk()
            ->assertSee('<title>Pls Love: dónde ver la serie GL | Mundo Yuri</title>', false)
            ->assertSee('Mira Pls Love en español y descubre su historia, reparto y episodios disponibles.', false)
            ->assertSee('"alternateName":["Please Love","PLS LOVE","PlsLove"]', false);
    }

    public function test_sitemap_includes_series_images(): void
    {
        $series = Series::query()->create([
            'genre_id' => $this->genre()->id,
            'title' => 'Historia indexable',
            'slug' => 'historia-indexable',
            'content_type' => 'series',
            'catalog_section' => 'series-gl',
            'status' => 'completed',
            'description' => 'Una historia preparada para Google Imágenes.',
            'banner_image' => 'https://cdn.example.com/historia-banner.webp',
            'cover_image' => 'https://cdn.example.com/historia-cover.webp',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=3600, public')
            ->assertSee('xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"', false)
            ->assertSee(route('catalog.series.show', $series->slug), false)
            ->assertSee('<image:loc>https://cdn.example.com/historia-banner.webp</image:loc>', false)
            ->assertSee('<image:loc>https://cdn.example.com/historia-cover.webp</image:loc>', false)
            ->assertSee('<image:title>Historia indexable</image:title>', false);
    }

    private function genre(): Genre
    {
        return Genre::query()->firstOrCreate(
            ['slug' => 'romance'],
            ['name' => 'Romance', 'is_active' => true]
        );
    }
}
