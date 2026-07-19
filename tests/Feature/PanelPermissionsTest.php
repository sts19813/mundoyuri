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

class PanelPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_regular_user_uses_shared_content_views_with_creation_permissions(): void
    {
        $user = $this->userWithRole('user');
        $genre = $this->genre();
        $series = $this->series($user, $genre, 'approved');

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('admin.series.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.series.create'))->assertOk();
        $this->actingAs($user)->get(route('admin.episodes.create'))->assertOk();
        $this->actingAs($user)->get(route('admin.genres.index'))->assertOk();

        $this->actingAs($user)->get(route('admin.series.edit', $series))->assertForbidden();
        $this->actingAs($user)->get(route('admin.moderation.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.settings.backblaze-b2.edit'))->assertRedirect('/');
        $this->actingAs($user)->get(route('admin.users.index'))->assertRedirect('/');
    }

    public function test_regular_user_submission_is_always_pending_even_with_forged_moderation_fields(): void
    {
        $user = $this->userWithRole('user');
        $genre = $this->genre();

        $response = $this->actingAs($user)->post(route('admin.series.store'), [
            'genre_id' => $genre->id,
            'title' => 'Aporte de usuario',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripción suficientemente completa para el aporte del usuario.',
            'moderation_status' => 'approved',
            'moderation_notes' => 'Intento de aprobación manual',
            'is_featured' => '1',
        ]);

        $response->assertRedirect(route('admin.series.index'));

        $series = Series::query()->where('title', 'Aporte de usuario')->firstOrFail();
        $this->assertSame('pending', $series->moderation_status);
        $this->assertNull($series->approved_by);
        $this->assertNull($series->published_at);
        $this->assertFalse($series->is_featured);
    }

    public function test_regular_user_can_add_a_pending_episode_to_published_content(): void
    {
        $user = $this->userWithRole('user');
        $admin = $this->userWithRole('admin');
        $genre = $this->genre();
        $series = $this->series($admin, $genre, 'approved');

        $response = $this->actingAs($user)->postJson(route('admin.episodes.store'), [
            'series_id' => $series->id,
            'title' => 'Episodio aportado',
            'season_number' => 1,
            'episode_number' => 3,
            'moderation_status' => 'approved',
            'source_provider' => [],
            'source_type' => [],
            'source_url' => [],
            'source_label' => [],
            'source_sort_order' => [],
        ]);

        $episode = Episode::query()->where('title', 'Episodio aportado')->firstOrFail();
        $response->assertOk()->assertJsonPath('redirect', route('admin.episodes.show', $episode));

        $this->assertSame($user->id, $episode->created_by);
        $this->assertSame('pending', $episode->moderation_status);
        $this->assertNull($episode->published_at);
    }

    public function test_moderator_can_edit_and_moderate_content_but_not_administer_system(): void
    {
        $moderator = $this->userWithRole('moderator');
        $contributor = $this->userWithRole('user');
        $genre = $this->genre();
        $series = $this->series($contributor, $genre, 'pending');

        $this->actingAs($moderator)->get(route('admin.series.edit', $series))->assertOk();
        $this->actingAs($moderator)->get(route('admin.moderation.index'))->assertOk();
        $this->actingAs($moderator)->get(route('admin.users.index'))->assertRedirect('/');
        $this->actingAs($moderator)->get(route('admin.settings.backblaze-b2.edit'))->assertRedirect('/');

        $this->actingAs($moderator)
            ->post(route('admin.moderation.series.approve', $series))
            ->assertRedirect();

        $this->assertSame('approved', $series->fresh()->moderation_status);
    }

    public function test_sidebar_uses_one_permission_aware_content_navigation(): void
    {
        $user = $this->userWithRole('user');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Series y películas')
            ->assertSee('Episodios')
            ->assertSee('Géneros')
            ->assertSee('Episodios con más vistas')
            ->assertSee('Series y películas con más vistas')
            ->assertDontSee('Inicio portal')
            ->assertDontSee('Dashboard admin')
            ->assertDontSee('Usuarios y permisos')
            ->assertDontSee('Backblaze B2');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['role' => $role]);
        $user->assignRole($role);

        return $user;
    }

    private function genre(): Genre
    {
        return Genre::query()->create([
            'name' => 'Drama',
            'slug' => 'drama',
            'is_active' => true,
        ]);
    }

    private function series(User $creator, Genre $genre, string $moderationStatus): Series
    {
        return Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $creator->id,
            'approved_by' => $moderationStatus === 'approved' ? $creator->id : null,
            'title' => 'Serie de prueba '.$moderationStatus,
            'slug' => 'serie-de-prueba-'.$moderationStatus,
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripción suficientemente extensa para la serie de prueba.',
            'moderation_status' => $moderationStatus,
            'published_at' => $moderationStatus === 'approved' ? now() : null,
        ]);
    }
}
