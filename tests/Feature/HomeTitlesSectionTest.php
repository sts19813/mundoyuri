<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Episode;
use App\Models\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTitlesSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_catalog_orders_all_public_titles_by_first_episode_and_exposes_view_counts(): void
    {
        $genre = Genre::query()->create(['name' => 'Romance', 'slug' => 'romance', 'is_active' => true]);
        $makeTitle = function (string $title, string $slug, string $section, string $type, string $status = 'approved') use ($genre): Series {
            return Series::query()->create([
                'genre_id' => $genre->id, 'title' => $title, 'slug' => $slug, 'catalog_section' => $section,
                'content_type' => $type, 'status' => 'completed', 'description' => 'Una historia.',
                'moderation_status' => $status, 'published_at' => now(),
            ]);
        };
        $oldMovie = $makeTitle('Película clásica', 'pelicula-clasica', 'series-gl', 'movie');
        $anime = $makeTitle('Anime nuevo', 'anime-nuevo', 'anime', 'series');
        $other = $makeTitle('Otro título', 'otro-titulo', 'otra-seccion', 'series');
        $hidden = $makeTitle('Pendiente', 'pendiente', 'series-gl', 'series', 'pending');

        foreach ([[$oldMovie, 1, '2020-01-01', 20, 'approved'], [$anime, 1, '2024-01-01', 10, 'approved'], [$anime, 2, '2024-01-08', 15, 'approved'], [$anime, 3, '2024-01-15', 1000, 'pending'], [$hidden, 1, '2025-01-01', 2, 'approved']] as [$series, $number, $date, $views, $status]) {
            Episode::query()->create([
                'series_id' => $series->id, 'title' => 'Episodio '.$number,
                'slug' => $series->slug.'-'.$number, 'season_number' => 1, 'episode_number' => $number,
                'release_date' => $date, 'views_count' => $views, 'moderation_status' => $status, 'published_at' => now(),
            ]);
        }

        $response = $this->get(route('home'))->assertOk()
            ->assertSee('Estreno del primer episodio')
            ->assertSee('Más vistas')
            ->assertSee('Nombre A–Z')
            ->assertDontSee('Pendiente');

        $titles = $response->viewData('mixedSeries');
        $this->assertSame([$anime->id, $oldMovie->id, $other->id], $titles->pluck('id')->all());
        $this->assertEquals(25, $titles->first()->total_episode_views);
        $this->assertStringStartsWith('2024-01-01', (string) $titles->first()->first_episode_date);
    }

    public function test_home_titles_section_lists_all_anime_and_gl_titles_without_view_all_link(): void
    {
        $genre = Genre::query()->create([
            'name' => 'Romance',
            'slug' => 'romance',
            'is_active' => true,
        ]);

        foreach (range(1, 13) as $number) {
            Series::query()->create([
                'genre_id' => $genre->id,
                'title' => "Serie GL {$number}",
                'slug' => "serie-gl-{$number}",
                'content_type' => 'series',
                'catalog_section' => 'series-gl',
                'status' => 'ongoing',
                'description' => "Descripción de la serie GL {$number}",
                'moderation_status' => 'approved',
                'published_at' => now()->subMinutes($number),
            ]);
        }

        Series::query()->create([
            'genre_id' => $genre->id,
            'title' => 'Anime Película',
            'slug' => 'anime-pelicula',
            'content_type' => 'movie',
            'catalog_section' => 'anime',
            'status' => 'completed',
            'description' => 'Una película de anime.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        $html = $this->get(route('home'))
            ->assertOk()
            ->assertSee('Serie GL 13')
            ->assertSee('Anime Película')
            ->assertSee('Anime · Película')
            ->getContent();

        $this->assertStringContainsString('js-lazy-media', $html);
        $this->assertStringContainsString('data-src="https://picsum.photos/300/420?series-', $html);
        $this->assertStringContainsString('src="data:image/gif;base64', $html);

        $titlesHeaderPosition = strpos($html, '<h2 class="section-title">Títulos</h2>');
        $this->assertNotFalse($titlesHeaderPosition);

        $titlesGridPosition = strpos($html, '<div class="row g-3">', $titlesHeaderPosition);
        $this->assertNotFalse($titlesGridPosition);

        $titlesHeader = substr($html, $titlesHeaderPosition, $titlesGridPosition - $titlesHeaderPosition);
        $this->assertStringNotContainsString('section-link', $titlesHeader);
        $this->assertStringNotContainsString('Ver todo', $titlesHeader);
    }
}
