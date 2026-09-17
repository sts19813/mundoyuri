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

    public function test_episode_index_prioritizes_missing_episodes_and_shows_episode_views(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $completeSeries = $this->createSeries($admin, [
            'title' => 'Serie completa',
            'slug' => 'serie-completa',
            'total_episodes' => 1,
        ]);
        $incompleteSeries = $this->createSeries($admin, [
            'title' => 'Serie incompleta',
            'slug' => 'serie-incompleta',
            'total_episodes' => 4,
        ]);

        Episode::query()->create([
            'series_id' => $completeSeries->id,
            'created_by' => $admin->id,
            'title' => 'Episodio completo',
            'slug' => 'episodio-completo',
            'season_number' => 1,
            'episode_number' => 1,
            'views_count' => 10,
            'moderation_status' => 'approved',
        ]);
        Episode::query()->create([
            'series_id' => $incompleteSeries->id,
            'created_by' => $admin->id,
            'title' => 'Episodio visto',
            'slug' => 'episodio-visto',
            'season_number' => 1,
            'episode_number' => 1,
            'views_count' => 1234,
            'moderation_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.episodes.index'));

        $response->assertOk()
            ->assertSeeInOrder(['Serie incompleta', 'Serie completa'])
            ->assertSee('Faltan 3 de 4')
            ->assertSee('1,234');
    }

    public function test_episode_index_can_sort_by_series_and_episode_views(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $highSeriesViews = $this->createSeries($admin, ['title' => 'Serie con más vistas', 'slug' => 'serie-con-mas-vistas']);
        $highEpisodeViews = $this->createSeries($admin, ['title' => 'Serie con episodio top', 'slug' => 'serie-con-episodio-top']);
        $lowViews = $this->createSeries($admin, ['title' => 'Serie con pocas vistas', 'slug' => 'serie-con-pocas-vistas']);

        foreach ([[1, 60], [2, 60]] as [$episodeNumber, $views]) {
            Episode::query()->create([
                'series_id' => $highSeriesViews->id,
                'created_by' => $admin->id,
                'title' => 'Episodio '.$episodeNumber,
                'slug' => 'serie-vistas-episodio-'.$episodeNumber,
                'season_number' => 1,
                'episode_number' => $episodeNumber,
                'views_count' => $views,
                'moderation_status' => 'approved',
            ]);
        }

        Episode::query()->create([
            'series_id' => $highEpisodeViews->id,
            'created_by' => $admin->id,
            'title' => 'Episodio top',
            'slug' => 'episodio-top',
            'season_number' => 1,
            'episode_number' => 1,
            'views_count' => 100,
            'moderation_status' => 'approved',
        ]);
        Episode::query()->create([
            'series_id' => $lowViews->id,
            'created_by' => $admin->id,
            'title' => 'Episodio bajo',
            'slug' => 'episodio-bajo',
            'season_number' => 1,
            'episode_number' => 1,
            'views_count' => 1,
            'moderation_status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.episodes.index', ['sort' => 'series_views']))
            ->assertOk()
            ->assertSeeInOrder(['Serie con más vistas', 'Serie con episodio top', 'Serie con pocas vistas']);

        $this->actingAs($admin)
            ->get(route('admin.episodes.index', ['sort' => 'episode_views']))
            ->assertOk()
            ->assertSeeInOrder(['Serie con episodio top', 'Serie con más vistas', 'Serie con pocas vistas']);
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
            ->assertSee('name="series_id" id="episode-series-id" value="'.$series->id.'"', false)
            ->assertSee('id="episode-number" value="4"', false)
            ->assertSee('id="episode-title" value="Episodio 4"', false)
            ->assertSee('backblaze_b2" selected', false)
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
