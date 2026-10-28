<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DiscoveryCatalogCurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_curation_corrects_legacy_metadata_and_makes_duo_aliases_searchable(): void
    {
        $genre = Genre::query()->create([
            'name' => 'Romance',
            'slug' => 'romance',
            'is_active' => true,
        ]);

        $anime = Series::query()->create([
            'genre_id' => $genre->id,
            'title' => 'Adachi to Shimamura',
            'slug' => 'adachi-to-shimamura',
            'catalog_section' => 'anime',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Dos estudiantes descubren sus sentimientos.',
            'release_year' => 2020,
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
        Series::query()->create([
            'genre_id' => $genre->id,
            'title' => 'Hometown Romance',
            'slug' => 'hometown-romance',
            'catalog_section' => 'series-gl',
            'content_type' => 'series',
            'status' => 'completed',
            'description' => 'Dos mejores amigas vuelven a encontrarse.',
            'release_year' => 2026,
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
        $fire = Series::query()->create([
            'genre_id' => $genre->id,
            'title' => 'El Fuego: Los Cuatro Elementos',
            'slug' => 'el-fuego-los-cuatro-elementos',
            'catalog_section' => 'series-gl',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Rivales que vuelven a encontrarse.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        Artisan::call('catalog:curate-discovery');

        $anime->refresh();
        $this->assertSame('Japón', $anime->country_of_origin);
        $this->assertSame('completed', $anime->status);
        $this->assertTrue($anime->artists()->exists());

        $this->assertDatabaseHas('duos', [
            'slug' => 'lmsy',
            'aliases' => 'LMSY, LookmheeSonya, Lookmhee & Sonya, Lookmhee Sonya',
        ]);
        $fire->refresh();
        $this->assertSame('completed', $fire->status);
        $this->assertSame('Tailandia', $fire->country_of_origin);
        $this->assertTrue($fire->duos()->where('slug', 'namnoey')->exists());

        $this->get(route('catalog.series.index'))
            ->assertOk()
            ->assertSee('LookmheeSonya')
            ->assertSee('Formato')
            ->assertSee('Identidad');
    }
}
