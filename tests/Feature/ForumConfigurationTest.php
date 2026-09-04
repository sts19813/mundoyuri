<?php

namespace Tests\Feature;

use App\Models\Forum;
use App\Models\ForumCategory;
use App\Models\User;
use Database\Seeders\CommunityForumSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForumConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_forum_structure_is_idempotent_and_publicly_available(): void
    {
        $this->seed(CommunityForumSeeder::class);
        $this->seed(CommunityForumSeeder::class);

        $this->assertDatabaseCount('forum_categories', 5);
        $this->assertDatabaseCount('forums', 10);
        $this->assertDatabaseHas('forums', [
            'slug' => 'presentaciones',
            'description' => 'Preséntate y conoce a las demás personas de la comunidad.',
            'is_active' => true,
        ]);

        $forum = Forum::query()->where('slug', 'presentaciones')->firstOrFail();
        $member = User::factory()->create();

        $this->get(route('forums.index'))->assertOk()->assertSee('Bienvenidas')->assertSee('Presentaciones');
        $this->actingAs($member)->get(route('forums.show', $forum))->assertOk();
        $this->actingAs($member)->get(route('questions.create'))
            ->assertOk()
            ->assertSee('Título')
            ->assertSee('Descripción')
            ->assertDontSee('Presentaciones');
        $this->actingAs($member)->post(route('forum.threads.store', $forum), [
            'title' => 'Mi primer tema en la comunidad',
            'body' => 'Hola a todas las personas de Mundo Yuri.',
        ])->assertRedirect();
    }

    public function test_inactive_and_locked_forums_are_not_available_for_public_participation(): void
    {
        $this->seed(CommunityForumSeeder::class);
        $member = User::factory()->create();
        $inactive = Forum::query()->where('slug', 'musica')->firstOrFail();
        $locked = Forum::query()->where('slug', 'juegos')->firstOrFail();
        $inactive->update(['is_active' => false]);
        $locked->update(['is_locked' => true]);

        $this->get(route('forums.index'))->assertOk()->assertDontSee('Música')->assertSee('Juegos');
        $this->actingAs($member)->get(route('forums.show', $inactive))->assertForbidden();
        $this->actingAs($member)->post(route('forum.threads.store', $locked), [
            'title' => 'No debe poder publicarse aquí',
            'body' => 'Este foro está bloqueado.',
        ])->assertForbidden();
        $this->actingAs($member)->get(route('questions.create'))
            ->assertOk()
            ->assertSee('Haz una pregunta')
            ->assertDontSee('Música')
            ->assertDontSee('Juegos');
    }

    public function test_admin_can_manage_structure_and_only_remove_empty_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user']);
        $category = ForumCategory::query()->create([
            'name' => 'Temporal',
            'slug' => 'temporal',
            'sort_order' => 90,
            'is_active' => true,
        ]);
        $forum = Forum::query()->create([
            'forum_category_id' => $category->id,
            'name' => 'Temporal',
            'slug' => 'foro-temporal',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($member)->get(route('admin.forum-categories.index'))->assertRedirect('/');
        $this->actingAs($admin)->get(route('admin.forum-categories.index'))->assertOk();
        $this->actingAs($member)->get(route('admin.forum-topics.index'))->assertRedirect('/');
        $this->actingAs($admin)->get(route('admin.forum-topics.index'))->assertOk();
        $this->actingAs($admin)->patch(route('admin.forums.update', $forum), [
            'forum_category_id' => $category->id,
            'name' => 'Temporal',
            'slug' => 'foro-temporal',
            'sort_order' => 20,
            'is_active' => false,
            'is_locked' => true,
        ])->assertRedirect(route('admin.forums.index'));
        $this->assertFalse($forum->fresh()->is_active);
        $this->assertTrue($forum->fresh()->is_locked);

        $this->actingAs($admin)->delete(route('admin.forum-categories.destroy', $category))
            ->assertRedirect(route('admin.forum-categories.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('forum_categories', ['id' => $category->id]);

        $empty = ForumCategory::query()->create([
            'name' => 'Vacía',
            'slug' => 'vacia',
            'sort_order' => 100,
            'is_active' => false,
        ]);
        $this->actingAs($admin)->delete(route('admin.forum-categories.destroy', $empty))
            ->assertRedirect(route('admin.forum-categories.index'));
        $this->assertDatabaseMissing('forum_categories', ['id' => $empty->id]);
    }
}
