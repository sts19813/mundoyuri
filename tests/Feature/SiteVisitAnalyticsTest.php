<?php

namespace Tests\Feature;

use App\Models\SiteVisit;
use App\Models\User;
use App\Models\VisitorPresenceSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteVisitAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_public_html_pages_record_an_anonymous_visit(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertCookie('mundo_yuri_visitor_id');

        $this->assertDatabaseCount('site_visits', 1);
        $this->assertDatabaseHas('site_visits', [
            'user_id' => null,
            'path' => '/',
        ]);
    }

    public function test_repeated_registered_page_reload_is_deduplicated_for_a_short_window(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('home'))->assertOk();
        $this->actingAs($user)->get(route('home'))->assertOk();
        $this->actingAs($user)->get(route('catalog.series.index'))->assertOk();

        $this->assertDatabaseCount('site_visits', 2);
        $this->assertDatabaseHas('site_visits', [
            'user_id' => $user->id,
            'visitor_id' => 'user:'.$user->id,
            'path' => '/',
        ]);
        $this->assertDatabaseHas('site_visits', [
            'user_id' => $user->id,
            'visitor_id' => 'user:'.$user->id,
            'path' => '/series',
        ]);
    }

    public function test_admin_dashboard_requests_are_not_counted_as_site_visits(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->assertDatabaseCount('site_visits', 0);
    }

    public function test_admin_dashboard_shows_site_visit_metrics_and_chart_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'created_at' => now()->subDays(2)]);
        $olderUser = User::factory()->create(['created_at' => now()->subDays(45)]);

        SiteVisit::query()->create([
            'user_id' => null,
            'visitor_id' => 'anonymous-one',
            'visited_on' => now()->toDateString(),
            'visited_at' => now(),
            'path' => '/',
            'path_hash' => sha1('/'),
        ]);
        SiteVisit::query()->create([
            'user_id' => $olderUser->id,
            'visitor_id' => 'user:'.$olderUser->id,
            'visited_on' => now()->subDays(6)->toDateString(),
            'visited_at' => now()->subDays(6),
            'path' => '/series',
            'path_hash' => sha1('/series'),
        ]);
        SiteVisit::query()->create([
            'user_id' => null,
            'visitor_id' => 'anonymous-old',
            'visited_on' => now()->subDays(40)->toDateString(),
            'visited_at' => now()->subDays(40),
            'path' => '/anime',
            'path_hash' => sha1('/anime'),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Visitantes únicos')
            ->assertSee('Páginas vistas hoy')
            ->assertSee('Tráfico de los últimos 30 días')
            ->assertSee('anónimos')
            ->assertSee('Personas en el sitio')
            ->assertSee('Páginas que atraen más visitas')
            ->assertSee('site-visits-30-days-chart', false)
            ->assertSee('site-visits-today-chart', false);
    }

    public function test_anonymous_visitor_presence_is_recorded_without_personal_data(): void
    {
        $sessionId = '22222222-2222-4222-8222-222222222222';
        $this->withCredentials()
            ->withCookie('mundo_yuri_visitor_id', '11111111-1111-4111-8111-111111111111')
            ->postJson(route('visitor-presence.heartbeat'), [
                'session_id' => $sessionId,
                'path' => '/series',
                'title' => 'Series GL',
            ])
            ->assertOk()
            ->assertJsonPath('saved', true);

        $this->assertDatabaseHas('visitor_presence_sessions', [
            'session_id' => $sessionId,
            'current_path' => '/series',
            'current_title' => 'Series GL',
        ]);

        $this->assertSame(1, VisitorPresenceSession::count());
    }
}
