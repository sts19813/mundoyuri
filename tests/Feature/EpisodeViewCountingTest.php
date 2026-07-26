<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EpisodeViewCountingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_profile_does_not_increment_views_on_episode_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$series, $episode] = $this->publishedEpisode($admin);

        $this->actingAs($admin)
            ->get(route('catalog.episodes.show', [$series, $episode]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('public.episodes.show', $episode))
            ->assertOk();

        $this->assertSame(0, $episode->fresh()->views_count);
    }

    public function test_assigned_admin_role_does_not_increment_views(): void
    {
        $admin = User::factory()->create(['role' => 'user']);
        $admin->assignRole('admin');
        [$series, $episode] = $this->publishedEpisode($admin);

        $this->actingAs($admin)
            ->get(route('catalog.episodes.show', [$series, $episode]))
            ->assertOk();

        $this->assertSame(0, $episode->fresh()->views_count);
    }

    public function test_guest_and_regular_user_views_are_still_counted(): void
    {
        $creator = User::factory()->create(['role' => 'admin']);
        $viewer = User::factory()->create(['role' => 'user']);
        [$series, $episode] = $this->publishedEpisode($creator);

        $this->get(route('catalog.episodes.show', [$series, $episode]))
            ->assertOk();

        $this->actingAs($viewer)
            ->get(route('public.episodes.show', $episode))
            ->assertOk();

        $this->assertSame(2, $episode->fresh()->views_count);
    }

    /**
     * @return array{Series, Episode}
     */
    private function publishedEpisode(User $creator): array
    {
        $genre = Genre::query()->create([
            'name' => 'Drama',
            'slug' => 'drama',
            'is_active' => true,
        ]);

        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $creator->id,
            'approved_by' => $creator->id,
            'title' => 'Serie publicada',
            'slug' => 'serie-publicada',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripción suficientemente extensa para la serie publicada.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'created_by' => $creator->id,
            'approved_by' => $creator->id,
            'title' => 'Episodio publicado',
            'slug' => 'episodio-publicado',
            'season_number' => 1,
            'episode_number' => 1,
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        return [$series, $episode];
    }
}
