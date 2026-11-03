<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Database\Seeders\UpsertUsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_us_without_publishing_unavailable_videos_or_overwriting_artists(): void
    {
        User::factory()->create(['role' => 'admin']);
        Genre::query()->create(['name' => 'Romance', 'slug' => 'romance']);
        $emi = Artist::query()->create(['name' => 'Emi existente', 'slug' => 'emi-thasorn-klinnium', 'biography' => 'Biografía existente']);

        $this->seed(UpsertUsSeeder::class);
        $this->seed(UpsertUsSeeder::class);

        $series = Series::query()->where('slug', 'us')->firstOrFail();
        $this->assertSame(12, $series->episodes()->count());
        $this->assertSame(9, $series->episodes()->where('moderation_status', 'approved')->count());
        $this->assertSame([6, 7, 12], $series->episodes()->where('moderation_status', 'pending')->orderBy('episode_number')->pluck('episode_number')->map(fn ($n) => (int) $n)->all());
        $this->assertSame('Biografía existente', $emi->fresh()->biography);
        $this->assertSame(2, $series->artists()->count());
        $this->assertSame('2025-04-05', $series->episodes()->where('episode_number', 12)->firstOrFail()->release_date->format('Y-m-d'));
        $this->assertSame('https://video.mundoyuri.com/us/US%2010.HLS/master.m3u8', $series->episodes()->where('episode_number', 10)->firstOrFail()->sources->first()->video_url);

        $this->get(route('catalog.series.show', $series))->assertOk()->assertSee('Us')->assertSee('Emi');
        $this->get(route('public.episodes.show', 'us-s1e6'))->assertNotFound();
    }
}
