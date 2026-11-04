<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Duo;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Database\Seeders\UpsertTheLoyalPinSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TheLoyalPinCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_loyal_pin_with_only_verified_hls_episodes_published(): void
    {
        User::factory()->create(['role' => 'admin']);
        Genre::create(['name' => 'Romance', 'slug' => 'romance']);
        Artist::create(['name' => 'Freen Sarocha Chankimha', 'slug' => 'freen-sarocha-chankimha']);
        Artist::create(['name' => 'Becky Armstrong', 'slug' => 'becky-armstrong']);
        Duo::create(['name' => 'FreenBecky', 'slug' => 'freenbecky']);

        $this->seed(UpsertTheLoyalPinSeeder::class);
        $this->seed(UpsertTheLoyalPinSeeder::class);

        $series = Series::query()->where('slug', 'the-loyal-pin')->firstOrFail();
        $this->assertSame(16, $series->episodes()->count());
        $this->assertSame(14, $series->episodes()->where('moderation_status', 'approved')->count());
        $this->assertSame('2024-08-04', $series->episodes()->where('episode_number', 1)->firstOrFail()->release_date->format('Y-m-d'));
        $this->assertSame('2024-11-17', $series->episodes()->where('episode_number', 16)->firstOrFail()->release_date->format('Y-m-d'));
        $this->assertSame('pending', $series->episodes()->where('episode_number', 15)->firstOrFail()->moderation_status);
        $this->assertSame(
            'https://video.mundoyuri.com/The%20Loyal%20Pin/The.Loyal.Pin.S01e14.HLS/master.m3u8',
            $series->episodes()->where('episode_number', 14)->firstOrFail()->sources->sole()->video_url,
        );
        $this->get(route('catalog.series.show', $series))->assertOk()->assertSee('The Loyal Pin')->assertSee('Freen');
        $this->get(route('public.episodes.show', 'the-loyal-pin-s1e14'))->assertOk();
    }
}
