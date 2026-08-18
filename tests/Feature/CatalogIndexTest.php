<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogue_renders_every_approved_title_without_pagination(): void
    {
        $genre = Genre::query()->create([
            'name' => 'Romance',
            'slug' => 'romance',
            'is_active' => true,
        ]);

        foreach (range(1, 13) as $number) {
            Series::query()->create([
                'genre_id' => $genre->id,
                'title' => "Serie {$number}",
                'slug' => "serie-{$number}",
                'content_type' => 'series',
                'status' => 'ongoing',
                'description' => "Descripción de la serie {$number}",
                'moderation_status' => 'approved',
                'published_at' => now()->subMinutes($number),
            ]);
        }

        $this->get(route('catalog.series.index', ['type' => 'series']))
            ->assertOk()
            ->assertSee('Serie 1')
            ->assertSee('Serie 13')
            ->assertSee('data-catalog-item', false)
            ->assertSee('data-catalog-filters', false)
            ->assertDontSee('pagination');
    }
}
