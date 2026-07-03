<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\EpisodeSource;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminEpisodeSourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_dailymotion_playback_to_a_movie(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::query()->create([
            'name' => 'Romance',
            'slug' => 'romance',
            'is_active' => true,
        ]);
        $movie = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Pelicula Dailymotion',
            'slug' => 'pelicula-dailymotion',
            'content_type' => 'movie',
            'status' => 'completed',
            'description' => 'Descripcion suficientemente larga para la pelicula Dailymotion.',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.episodes.store'), [
                'series_id' => $movie->id,
                'title' => 'Pelicula completa',
                'season_number' => 1,
                'episode_number' => 1,
                'moderation_status' => 'approved',
                'source_provider' => ['dailymotion'],
                'source_type' => ['full'],
                'source_url' => ['https://dai.ly/kMoruavr3wFz7EHkyr0'],
                'source_label' => ['Dailymotion'],
                'source_sort_order' => [1],
                'source_primary' => 0,
            ]);

        $response->assertRedirect(route('admin.episodes.index'));

        $source = Episode::query()->with('sources')->firstOrFail()->sources->firstOrFail();

        $this->assertSame('dailymotion', $source->provider);
        $this->assertSame(
            'https://geo.dailymotion.com/player.html?video=kMoruavr3wFz7EHkyr0',
            $source->video_url
        );
        $this->assertSame($source->video_url, $source->playable_url);
        $this->assertSame('iframe', $source->player_type);
        $this->assertTrue($source->is_primary);
    }

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

    public function test_admin_can_create_episode_with_pixeldrain_cdn_source(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::query()->create([
            'name' => 'Accion',
            'slug' => 'accion',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Serie pixeldrain',
            'slug' => 'serie-pixeldrain',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripcion suficientemente larga para validar Pixeldrain CDN.',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.episodes.store'), [
                'series_id' => $series->id,
                'title' => 'Episodio Pixeldrain',
                'season_number' => 1,
                'episode_number' => 2,
                'moderation_status' => 'approved',
                'source_provider' => ['pixeldrain_cdn'],
                'source_type' => ['full'],
                'source_url' => ['https://cdn.pixeldrain.eu.cc/LTRmJYYs'],
                'source_label' => ['Pixeldrain CDN'],
                'source_sort_order' => [1],
                'source_primary' => 0,
            ]);

        $response->assertRedirect(route('admin.episodes.index'));

        $episode = Episode::query()->with('sources')->where('title', 'Episodio Pixeldrain')->firstOrFail();

        $this->assertCount(1, $episode->sources);
        $this->assertSame('pixeldrain_cdn', $episode->sources[0]->provider);
        $this->assertSame('iframe', $episode->sources[0]->player_type);
        $this->assertSame(route('episode-sources.player', $episode->sources[0]), $episode->sources[0]->playable_url);
        $this->assertSame('https://pixeldrain.com/api/file/LTRmJYYs', $episode->sources[0]->video_url);
        $this->assertSame('https://pixeldrain.com/api/file/LTRmJYYs', $episode->sources[0]->direct_video_url);
        $this->assertTrue($episode->sources[0]->is_primary);
    }

    public function test_admin_can_create_episode_with_bunny_stream_source_using_video_id(): void
    {
        config()->set('services.bunny.library_id', '987654');
        config()->set('services.bunny.api_key', 'test-bunny-key');
        config()->set('services.bunny.token_key', null);

        Http::fake([
            'https://video.bunnycdn.com/library/987654/videos/*' => Http::response([
                'guid' => '550e8400-e29b-41d4-a716-446655440000',
                'isPublic' => true,
                'status' => 3,
            ], 200),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::query()->create([
            'name' => 'SciFi',
            'slug' => 'scifi',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Serie bunny',
            'slug' => 'serie-bunny',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripcion suficientemente larga para validar Bunny Stream.',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.episodes.store'), [
                'series_id' => $series->id,
                'title' => 'Episodio Bunny',
                'season_number' => 1,
                'episode_number' => 3,
                'moderation_status' => 'approved',
                'source_provider' => ['bunny_stream'],
                'source_type' => ['full'],
                'source_url' => ['550e8400-e29b-41d4-a716-446655440000'],
                'source_label' => ['Bunny Principal'],
                'source_sort_order' => [1],
                'source_primary' => 0,
            ]);

        $response->assertRedirect(route('admin.episodes.index'));

        $episode = Episode::query()->with('sources')->where('title', 'Episodio Bunny')->firstOrFail();

        $this->assertCount(1, $episode->sources);
        $this->assertSame('bunny_stream', $episode->sources[0]->provider);
        $this->assertSame('iframe', $episode->sources[0]->player_type);
        $this->assertSame(
            'https://player.mediadelivery.net/embed/987654/550e8400-e29b-41d4-a716-446655440000',
            $episode->sources[0]->video_url
        );
        $this->assertSame($episode->sources[0]->video_url, $episode->sources[0]->playable_url);

        Http::assertSent(fn ($request) => $request->url() === 'https://video.bunnycdn.com/library/987654/videos/550e8400-e29b-41d4-a716-446655440000');
    }

    public function test_private_bunny_video_is_allowed_when_token_key_is_configured(): void
    {
        Carbon::setTestNow('2026-06-03 12:00:00');

        config()->set('services.bunny.library_id', '987654');
        config()->set('services.bunny.api_key', 'test-bunny-key');
        config()->set('services.bunny.token_key', 'secret-token-key');
        config()->set('services.bunny.token_ttl', 120);

        Http::fake([
            'https://video.bunnycdn.com/library/987654/videos/*' => Http::response([
                'guid' => '550e8400-e29b-41d4-a716-446655440000',
                'isPublic' => false,
                'status' => 3,
            ], 200),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::query()->create([
            'name' => 'Drama privado',
            'slug' => 'drama-privado',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Serie bunny privada',
            'slug' => 'serie-bunny-privada',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripcion suficientemente larga para validar Bunny privado.',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.episodes.store'), [
                'series_id' => $series->id,
                'title' => 'Episodio Bunny Privado',
                'season_number' => 1,
                'episode_number' => 4,
                'moderation_status' => 'approved',
                'source_provider' => ['bunny_stream'],
                'source_type' => ['full'],
                'source_url' => ['550e8400-e29b-41d4-a716-446655440000'],
                'source_label' => ['Bunny Privado'],
                'source_sort_order' => [1],
                'source_primary' => 0,
            ]);

        $response->assertRedirect(route('admin.episodes.index'));

        $episode = Episode::query()->with('sources')->where('title', 'Episodio Bunny Privado')->firstOrFail();
        $playableUrl = $episode->sources[0]->playable_url;
        $expectedExpires = Carbon::now()->addMinutes(120)->timestamp;
        $expectedToken = hash('sha256', 'secret-token-key'.'550e8400-e29b-41d4-a716-446655440000'.$expectedExpires);

        $this->assertStringStartsWith(
            'https://player.mediadelivery.net/embed/987654/550e8400-e29b-41d4-a716-446655440000?',
            $playableUrl
        );
        $this->assertStringContainsString('token='.$expectedToken, $playableUrl);
        $this->assertStringContainsString('expires='.$expectedExpires, $playableUrl);

        Carbon::setTestNow();
    }

    public function test_pixeldrain_source_uses_lightweight_player_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::query()->create([
            'name' => 'Fantasia',
            'slug' => 'fantasia',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Serie stream',
            'slug' => 'serie-stream',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripcion suficientemente larga para probar el stream.',
        ]);
        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'created_by' => $admin->id,
            'title' => 'Ep stream',
            'slug' => 'ep-stream',
            'season_number' => 1,
            'episode_number' => 1,
            'moderation_status' => 'approved',
        ]);

        $source = EpisodeSource::query()->create([
            'episode_id' => $episode->id,
            'provider' => 'pixeldrain_cdn',
            'source_type' => 'full',
            'label' => 'Pixeldrain',
            'sort_order' => 1,
            'video_url' => 'https://cdn.pixeldrain.eu.cc/LTRmJYYs',
            'is_primary' => true,
        ]);

        $response = $this->get(route('episode-sources.player', $source));

        $response->assertOk();
        $response->assertHeader('Referrer-Policy', 'no-referrer');
        $response->assertSee('https://pixeldrain.com/api/file/LTRmJYYs', false);
        $response->assertSee('<video controls playsinline preload="metadata">', false);
    }
}
