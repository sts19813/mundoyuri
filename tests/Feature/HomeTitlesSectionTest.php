<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTitlesSectionTest extends TestCase
{
    use RefreshDatabase;

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

        $titlesHeaderPosition = strpos($html, '<h2 class="section-title">Títulos</h2>');
        $this->assertNotFalse($titlesHeaderPosition);

        $titlesGridPosition = strpos($html, '<div class="row g-3">', $titlesHeaderPosition);
        $this->assertNotFalse($titlesGridPosition);

        $titlesHeader = substr($html, $titlesHeaderPosition, $titlesGridPosition - $titlesHeaderPosition);
        $this->assertStringNotContainsString('section-link', $titlesHeader);
        $this->assertStringNotContainsString('Ver todo', $titlesHeader);
    }
}
