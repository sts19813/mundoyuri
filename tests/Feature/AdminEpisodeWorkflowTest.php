<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEpisodeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_episode_index_groups_records_by_series_and_shows_sources_and_web_publication_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $series = $this->createSeries($admin, ['title' => 'Serie agrupada', 'slug' => 'serie-agrupada']);
        $otherSeries = $this->createSeries($admin, ['title' => 'Serie sin episodios', 'slug' => 'serie-sin-episodios']);

        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'created_by' => $admin->id,
            'approved_by' => $admin->id,
            'title' => 'El tercer episodio',
            'slug' => 'el-tercer-episodio',
            'season_number' => 1,
            'episode_number' => 3,
            'moderation_status' => 'approved',
            'published_at' => '2026-07-21 18:30:00',
        ]);
        $episode->sources()->create([
            'provider' => 'vimeo',
            'video_url' => 'https://player.vimeo.com/video/123456',
            'is_primary' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.episodes.index'));

        $response->assertOk()
            ->assertSee('Serie agrupada')
            ->assertSee('Serie sin episodios')
            ->assertSee('T1 · E3')
            ->assertSee('https://player.vimeo.com/video/123456', false)
            ->assertSee('21/07/2026 18:30')
            ->assertSee(route('admin.episodes.create', ['series_id' => $otherSeries->id]), false);
    }

    public function test_create_form_selects_series_and_suggests_the_next_episode_number(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $series = $this->createSeries($admin);

        foreach ([1, 2, 3] as $number) {
            Episode::query()->create([
                'series_id' => $series->id,
                'created_by' => $admin->id,
                'title' => 'Episodio '.$number,
                'slug' => 'episodio-'.$number,
                'season_number' => 1,
                'episode_number' => $number,
                'moderation_status' => 'approved',
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.episodes.create', ['series_id' => $series->id]));

        $response->assertOk()
            ->assertSee('value="'.$series->id.'" selected', false)
            ->assertSee('id="episode-number" value="4"', false)
            ->assertSee('siguiente número disponible');
    }

    public function test_an_admin_episode_is_always_approved_even_when_another_status_is_submitted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $series = $this->createSeries($admin);

        $response = $this->actingAs($admin)->postJson(route('admin.episodes.store'), [
            'series_id' => $series->id,
            'title' => 'Episodio automático',
            'season_number' => 1,
            'episode_number' => 1,
            'moderation_status' => 'rejected',
        ]);

        $response->assertOk()->assertJsonPath(
            'redirect',
            route('admin.episodes.index', ['series_id' => $series->id])
        );

        $episode = Episode::query()->firstOrFail();
        $this->assertSame('approved', $episode->moderation_status);
        $this->assertSame($admin->id, $episode->approved_by);
        $this->assertNotNull($episode->published_at);
    }

    private function createSeries(User $admin, array $overrides = []): Series
    {
        $genre = Genre::query()->firstOrCreate(
            ['slug' => 'drama'],
            ['name' => 'Drama', 'is_active' => true]
        );

        return Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Serie consecutiva',
            'slug' => 'serie-consecutiva',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripción de la serie para probar el flujo de episodios.',
            'moderation_status' => 'approved',
            ...$overrides,
        ]);
    }
}
