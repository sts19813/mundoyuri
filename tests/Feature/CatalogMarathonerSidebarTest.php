<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use App\Models\UserPresenceSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogMarathonerSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_episode_sidebar_defaults_to_marathoners_and_excludes_admins(): void
    {
        [$series, $episode] = $this->catalogEntry();

        $viewer = User::factory()->create(['name' => 'Visitante']);
        $leader = User::factory()->create([
            'name' => 'Luna Maratonista',
            'alias' => 'Luna',
            'last_seen_at' => now(),
            'signature_text' => 'Ver hasta el amanecer',
        ]);
        $runnerUp = User::factory()->create(['name' => 'Mio Constante', 'alias' => 'Mio']);
        $admin = User::factory()->create(['name' => 'Admin del ranking', 'role' => 'admin']);

        $this->presence($leader, 7200, '11111111-1111-4111-8111-111111111111');
        $this->presence($runnerUp, 3600, '22222222-2222-4222-8222-222222222222');
        $this->presence($admin, 14400, '33333333-3333-4333-8333-333333333333');

        $this->actingAs($viewer)
            ->get(route('public.episodes.show', $episode->slug))
            ->assertOk()
            ->assertSee('Maratonistas del GL')
            ->assertSee('Lo más nuevo')
            ->assertSee('data-sidebar-tab="marathoners"', false)
            ->assertSee('aria-selected="true"', false)
            ->assertSeeInOrder(['Luna', 'Mio'])
            ->assertDontSee('Admin del ranking')
            ->assertSee('forum-author-popover', false)
            ->assertSee('forum-post-avatar is-online', false)
            ->assertSee('Ver hasta el amanecer')
            ->assertSee(route('messages.show', $leader), false)
            ->assertSee('Escribir');
    }

    public function test_series_detail_uses_the_same_discovery_sidebar(): void
    {
        [$series] = $this->catalogEntry();
        $member = User::factory()->create(['name' => 'Yuri Espectadora', 'alias' => 'Yuri']);

        $this->presence($member, 1800, '44444444-4444-4444-8444-444444444444');

        $this->get(route('catalog.series.show', $series->slug))
            ->assertOk()
            ->assertSee('data-sidebar-tabs', false)
            ->assertSee('Yuri')
            ->assertSee('Lo más nuevo');
    }

    /** @return array{Series, Episode} */
    private function catalogEntry(): array
    {
        $genre = Genre::query()->create([
            'name' => 'Romance',
            'slug' => 'romance',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'title' => 'Fairway de prueba',
            'slug' => 'fairway-de-prueba',
            'content_type' => 'series',
            'catalog_section' => 'series-gl',
            'status' => 'ongoing',
            'description' => 'Serie para validar el módulo lateral.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
        $episode = Episode::query()->create([
            'series_id' => $series->id,
            'title' => 'El gran maratón',
            'slug' => 'el-gran-maraton',
            'season_number' => 1,
            'episode_number' => 1,
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        return [$series, $episode];
    }

    private function presence(User $user, int $seconds, string $sessionId): void
    {
        UserPresenceSession::query()->create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'started_at' => now()->subSeconds($seconds),
            'last_seen_at' => now()->subMinute(),
            'total_seconds' => $seconds,
            'heartbeat_count' => 2,
            'current_path' => '/series',
            'current_title' => 'Series',
        ]);
    }
}
