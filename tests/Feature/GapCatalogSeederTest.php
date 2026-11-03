<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Duo;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Database\Seeders\UpsertGapTheSeriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GapCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_gap_catalog_is_idempotent_and_uses_existing_people_and_correct_hls_paths(): void
    {
        User::factory()->create(['role' => 'admin']);
        Genre::create(['name' => 'Romance', 'slug' => 'romance']);
        $freen = Artist::create(['name' => 'Freen Sarocha Chankimha', 'slug' => 'freen-sarocha-chankimha', 'biography' => 'Perfil existente']);
        Artist::create(['name' => 'Becky Armstrong', 'slug' => 'becky-armstrong']);
        Duo::create(['name' => 'FreenBecky', 'slug' => 'freenbecky']);

        $this->seed(UpsertGapTheSeriesSeeder::class);
        $this->seed(UpsertGapTheSeriesSeeder::class);
        $series = Series::where('slug', 'gap-the-series')->firstOrFail();
        $this->assertSame(12, $series->episodes()->count());
        $this->assertSame(12, $series->episodes()->where('moderation_status', 'approved')->count());
        $this->assertSame(2, $series->artists()->count());
        $this->assertSame(1, $series->duos()->count());
        $this->assertSame('Perfil existente', $freen->fresh()->biography);
        $this->assertSame('2023-01-07', $series->episodes()->where('episode_number', 7)->firstOrFail()->release_date->format('Y-m-d'));
        $this->assertSame('2023-02-11', $series->episodes()->where('episode_number', 12)->firstOrFail()->release_date->format('Y-m-d'));
        foreach (range(1, 12) as $n) {
            $episode = $series->episodes()->where('episode_number', $n)->firstOrFail();
            $case = in_array($n, [4, 5, 6], true) ? 'CAP' : 'Cap';
            $this->assertSame("https://video.mundoyuri.com/gap/{$case}%20{$n}.HLS/master.m3u8", $episode->sources->sole()->video_url);
        }
        $this->get(route('catalog.series.show', $series))->assertOk()->assertSee('GAP: The Series')->assertSee('Becky');
        $this->get(route('public.episodes.show', 'gap-the-series-s1e4'))->assertOk();
    }
}
