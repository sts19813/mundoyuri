<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminCommentModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_can_list_hide_restore_and_delete_catalog_comments(): void
    {
        $admin = $this->userWithRole('admin');
        $author = $this->userWithRole('user');
        $episode = $this->episode($author);
        $comment = $episode->comments()->create([
            'user_id' => $author->id,
            'body' => 'Comentario que necesita revisión',
            'is_approved' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.comments.index'))
            ->assertOk()
            ->assertSee('Comentario que necesita revisión')
            ->assertSee('Abrir página');

        $this->actingAs($admin)
            ->patch(route('admin.comments.update', $comment), ['is_approved' => false])
            ->assertRedirect();

        $this->assertFalse($comment->fresh()->is_approved);

        $this->actingAs($admin)
            ->patch(route('admin.comments.update', $comment), ['is_approved' => true])
            ->assertRedirect();

        $this->assertTrue($comment->fresh()->is_approved);

        $this->actingAs($admin)
            ->delete(route('admin.comments.destroy', $comment))
            ->assertRedirect();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_regular_user_cannot_access_comment_moderation(): void
    {
        $user = $this->userWithRole('user');
        $comment = Comment::query()->create([
            'commentable_type' => Series::class,
            'commentable_id' => $this->series($user)->id,
            'body' => 'Comentario visible',
            'is_approved' => true,
        ]);

        $this->actingAs($user)->get(route('admin.comments.index'))->assertRedirect('/');
        $this->actingAs($user)->patch(route('admin.comments.update', $comment), ['is_approved' => false])->assertRedirect('/');
        $this->actingAs($user)->delete(route('admin.comments.destroy', $comment))->assertRedirect('/');
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

    private function series(User $creator): Series
    {
        return Series::query()->create([
            'genre_id' => $this->genre()->id,
            'created_by' => $creator->id,
            'approved_by' => $creator->id,
            'title' => 'Serie con comentarios',
            'slug' => 'serie-con-comentarios',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripción suficientemente extensa para una serie con comentarios.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
    }

    private function episode(User $creator): Episode
    {
        $series = $this->series($creator);

        return Episode::query()->create([
            'series_id' => $series->id,
            'created_by' => $creator->id,
            'approved_by' => $creator->id,
            'title' => 'Episodio con comentarios',
            'slug' => 'episodio-con-comentarios',
            'season_number' => 1,
            'episode_number' => 1,
            'description' => 'Descripción suficientemente extensa para un episodio.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
    }
}
