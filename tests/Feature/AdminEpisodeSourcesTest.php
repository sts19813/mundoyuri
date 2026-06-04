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
                'source_url' => [
                    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'https://player.vimeo.com/video/123456',
                    'https://voe.sx/e/abcdef',
                    '',
                ],
                'source_label' => ['Principal', 'Mirror 1', 'Mirror 2', ''],
                'source_primary' => 2,
            ]);

        $response->assertRedirect(route('admin.episodes.index'));

        $episode = Episode::query()->with('sources')->firstOrFail();

        $this->assertCount(3, $episode->sources);
        $this->assertSame('youtube', $episode->sources[0]->provider);
        $this->assertSame('vimeo', $episode->sources[1]->provider);
        $this->assertSame('voe', $episode->sources[2]->provider);
        $this->assertTrue($episode->sources[2]->is_primary);
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
                'source_url' => ['https://netu.ac/e/example'],
                'source_label' => ['Unica'],
                'source_primary' => 0,
            ]);

        $response->assertRedirect(route('admin.episodes.index'));

        $episode->refresh()->load('sources');

        $this->assertCount(1, $episode->sources);
        $this->assertSame('netu', $episode->sources[0]->provider);
        $this->assertTrue($episode->sources[0]->is_primary);
    }
}
