<?php

namespace Tests\Feature;

use App\Models\BackblazeB2Setting;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BackblazeB2IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_backblaze_settings_with_an_encrypted_key(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->put(route('admin.settings.backblaze-b2.update'), [
            'enabled' => '1',
            'key_id' => 'example-key-id',
            'application_key' => 'super-secret-application-key',
            'bucket_name' => 'mundoyuri',
            'token_ttl_seconds' => 3600,
        ]);

        $response->assertRedirect();

        $settings = BackblazeB2Setting::current();

        $this->assertTrue($settings->enabled);
        $this->assertSame('super-secret-application-key', $settings->application_key);
        $this->assertNotSame(
            'super-secret-application-key',
            DB::table('backblaze_b2_settings')->value('application_key')
        );
    }

    public function test_admin_can_verify_connection_and_resolve_bucket_metadata(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        BackblazeB2Setting::query()->create([
            'enabled' => false,
            'key_id' => 'example-key-id',
            'application_key' => 'example-application-key',
            'bucket_name' => 'mundoyuri',
            'token_ttl_seconds' => 3600,
        ]);

        Http::fake([
            'api.backblazeb2.com/*' => Http::response($this->authorizationPayload(), 200),
            'api005.backblazeb2.com/*' => Http::response([
                'buckets' => [[
                    'bucketId' => 'bucket-id-123',
                    'bucketName' => 'mundoyuri',
                    'bucketType' => 'allPrivate',
                ]],
            ], 200),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.settings.backblaze-b2.verify'));

        $response->assertRedirect()->assertSessionHas('success');

        $settings = BackblazeB2Setting::current();
        $this->assertSame('bucket-id-123', $settings->bucket_id);
        $this->assertSame('https://f005.backblazeb2.com', $settings->download_url);
        $this->assertNotNull($settings->last_verified_at);
    }

    public function test_backblaze_source_redirects_video_to_a_temporary_private_url(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $source = $this->createBackblazeSource($admin);

        BackblazeB2Setting::query()->create([
            'enabled' => true,
            'key_id' => 'example-key-id',
            'application_key' => 'example-application-key',
            'bucket_name' => 'mundoyuri',
            'bucket_id' => 'bucket-id-123',
            'token_ttl_seconds' => 3600,
        ]);

        Http::fake([
            'api.backblazeb2.com/*' => Http::response($this->authorizationPayload(), 200),
            'api005.backblazeb2.com/*' => Http::response([
                'authorizationToken' => 'temporary-download-token',
                'bucketId' => 'bucket-id-123',
                'fileNamePrefix' => 'shows/Fulfill S01E01.mp4',
            ], 200),
        ]);

        $response = $this->get(route('episode-sources.player', $source));

        $response->assertRedirect(
            'https://f005.backblazeb2.com/file/mundoyuri/shows/Fulfill%20S01E01.mp4?Authorization=temporary-download-token'
        );
        $this->assertSame('max-age=0, no-store, private', $response->headers->get('Cache-Control'));

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/b2api/v4/b2_get_download_authorization')
            && $request->hasHeader('Authorization', 'account-authorization-token')
            && $request['bucketId'] === 'bucket-id-123'
            && $request['fileNamePrefix'] === 'shows/Fulfill S01E01.mp4'
            && $request['validDurationInSeconds'] === 3600);
    }

    public function test_admin_can_register_a_backblaze_url_as_movie_source(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::query()->create([
            'name' => 'Drama B2',
            'slug' => 'drama-b2',
            'is_active' => true,
        ]);
        $movie = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Pelicula B2',
            'slug' => 'pelicula-b2',
            'content_type' => 'movie',
            'status' => 'completed',
            'description' => 'Descripcion suficientemente larga para una pelicula de Backblaze.',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.episodes.store'), [
            'series_id' => $movie->id,
            'title' => 'Pelicula completa',
            'season_number' => 1,
            'episode_number' => 1,
            'moderation_status' => 'approved',
            'source_provider' => ['backblaze_b2'],
            'source_type' => ['full'],
            'source_url' => ['https://mundoyuri.s3.us-east-005.backblazeb2.com/movies/Pelicula.mp4?Authorization=must-not-be-stored'],
            'source_label' => ['B2 HD'],
            'source_sort_order' => [1],
            'source_primary' => 0,
        ]);

        $response->assertRedirect(route('admin.episodes.index'));

        $source = Episode::query()->with('sources')->firstOrFail()->sources->firstOrFail();
        $this->assertSame('backblaze_b2', $source->provider);
        $this->assertSame('https://mundoyuri.s3.us-east-005.backblazeb2.com/movies/Pelicula.mp4', $source->video_url);
        $this->assertSame('video', $source->player_type);
    }

    private function createBackblazeSource(User $admin): mixed
    {
        $genre = Genre::query()->create([
            'name' => 'B2',
            'slug' => 'b2',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'title' => 'Serie B2',
            'slug' => 'serie-b2',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripcion suficientemente larga para la serie de prueba B2.',
        ]);
        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'created_by' => $admin->id,
            'title' => 'Episodio B2',
            'slug' => 'episodio-b2',
            'season_number' => 1,
            'episode_number' => 1,
            'moderation_status' => 'approved',
        ]);

        return $episode->sources()->create([
            'provider' => 'backblaze_b2',
            'source_type' => 'full',
            'video_url' => 'https://f005.backblazeb2.com/file/mundoyuri/shows/Fulfill%20S01E01.mp4',
            'label' => 'B2 HD',
            'sort_order' => 1,
            'is_primary' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizationPayload(): array
    {
        return [
            'accountId' => 'account-id-123',
            'authorizationToken' => 'account-authorization-token',
            'apiInfo' => [
                'storageApi' => [
                    'apiUrl' => 'https://api005.backblazeb2.com',
                    'downloadUrl' => 'https://f005.backblazeb2.com',
                    'allowed' => [
                        'buckets' => null,
                        'capabilities' => ['listBuckets', 'readFiles', 'shareFiles'],
                        'namePrefix' => null,
                    ],
                ],
            ],
        ];
    }
}
