<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEpisodeSourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_episode_with_dynamic_sources(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::query()->create([
            'name' => 'Drama',
            'slug' => 'drama',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Serie demo',
            'slug' => 'serie-demo',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripcion suficientemente larga para la serie demo.',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.episodes.store'), [
                'series_id' => $series->id,
                'title' => 'Episodio piloto',
                'season_number' => 1,
                'episode_number' => 1,
                'moderation_status' => 'approved',
                'source_provider' => ['youtube_link', 'vimeo', 'voe', ''],
                'source_type' => ['part', 'full', 'part', 'full'],
                'source_url' => [
                    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'https://player.vimeo.com/video/123456',
                    'https://voe.sx/e/abcdef',
                    '',
                ],
                'source_label' => ['Principal', 'Mirror 1', 'Mirror 2', ''],
                'source_sort_order' => [1, 3, 2, 4],
                'source_primary' => 2,
            ]);

        $response->assertRedirect(route('admin.episodes.index'));

        $episode = Episode::query()->with('sources')->firstOrFail();

        $this->assertCount(3, $episode->sources);
        $this->assertSame('youtube', $episode->sources[0]->provider);
        $this->assertSame('part', $episode->sources[0]->source_type);
        $this->assertSame('voe', $episode->sources[1]->provider);
        $this->assertSame('part', $episode->sources[1]->source_type);
        $this->assertSame('vimeo', $episode->sources[2]->provider);
        $this->assertSame('full', $episode->sources[2]->source_type);
        $this->assertTrue($episode->sources[1]->is_primary);
    }

    public function test_admin_can_update_episode_and_reduce_sources(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::query()->create([
            'name' => 'Comedia',
            'slug' => 'comedia',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Serie update',
            'slug' => 'serie-update',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripcion suficientemente larga para la serie update.',
        ]);
        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'created_by' => $admin->id,
            'title' => 'Ep 1',
            'slug' => 'ep-1',
            'season_number' => 1,
            'episode_number' => 1,
            'moderation_status' => 'pending',
        ]);
        $episode->sources()->createMany([
            ['provider' => 'youtube', 'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'label' => 'Principal', 'is_primary' => true],
            ['provider' => 'vimeo', 'video_url' => 'https://player.vimeo.com/video/123456', 'label' => 'Backup', 'is_primary' => false],
        ]);

        $response = $this
            ->actingAs($admin)
            ->put(route('admin.episodes.update', $episode), [
                'series_id' => $series->id,
                'title' => 'Ep 1 editado',
                'season_number' => 1,
                'episode_number' => 1,
                'moderation_status' => 'approved',
                'source_provider' => ['netu'],
                'source_type' => ['part'],
                'source_url' => ['https://netu.ac/e/example'],
                'source_label' => ['Unica'],
                'source_sort_order' => [1],
                'source_primary' => 0,
            ]);

        $response->assertRedirect(route('admin.episodes.index'));

        $episode->refresh()->load('sources');

        $this->assertCount(1, $episode->sources);
        $this->assertSame('netu', $episode->sources[0]->provider);
        $this->assertSame('part', $episode->sources[0]->source_type);
        $this->assertTrue($episode->sources[0]->is_primary);
    }

    public function test_duplicate_episode_number_returns_json_validation_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::query()->create([
            'name' => 'Suspenso',
            'slug' => 'suspenso',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Serie duplicada',
            'slug' => 'serie-duplicada',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripcion suficientemente larga para el caso duplicado.',
        ]);

        Episode::query()->create([
            'series_id' => $series->id,
            'created_by' => $admin->id,
            'title' => 'Ep base',
            'slug' => 'ep-base',
            'season_number' => 1,
            'episode_number' => 1,
            'moderation_status' => 'approved',
        ]);

        $response = $this
            ->actingAs($admin)
            ->postJson(route('admin.episodes.store'), [
                'series_id' => $series->id,
                'title' => 'Ep repetido',
                'season_number' => 1,
                'episode_number' => 1,
                'moderation_status' => 'approved',
                'source_provider' => ['youtube_link'],
                'source_type' => ['full'],
                'source_url' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
                'source_label' => ['Principal'],
                'source_sort_order' => [1],
                'source_primary' => 0,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['episode_number']);
    }
}
