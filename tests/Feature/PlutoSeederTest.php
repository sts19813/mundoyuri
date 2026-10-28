<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Database\Seeders\UpsertPlutoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlutoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_pluto_fiche_and_only_exposes_available_hls_episodes(): void
    {
        User::factory()->create(['role' => 'admin']);
        Genre::query()->create(['name' => 'Romance', 'slug' => 'romance', 'is_active' => true]);

        $this->seed(UpsertPlutoSeeder::class);

        $this->assertDatabaseHas('series', [
            'slug' => 'pluto',
            'country_of_origin' => 'Tailandia',
            'release_year' => 2024,
            'total_episodes' => 12,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('episodes', ['slug' => 'pluto-s1e11', 'moderation_status' => 'approved']);
        $this->assertDatabaseHas('episodes', ['slug' => 'pluto-s1e12', 'moderation_status' => 'pending']);
        $this->assertDatabaseHas('episode_sources', [
            'video_url' => 'https://video.mundoyuri.com/Pluto/Pluto.S01e01.Iq.HLS/master.m3u8',
            'provider' => 'cloudflare_hls',
        ]);
        $this->assertDatabaseHas('duos', ['slug' => 'namtanfilm']);
    }
}
