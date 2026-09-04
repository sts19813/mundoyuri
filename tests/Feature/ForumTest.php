<?php

namespace Tests\Feature;

use App\Models\Forum;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use App\Services\ForumPostService;
use App\Services\ForumThreadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForumTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_member_can_create_a_thread_and_a_reply_with_consistent_counts(): void
    {
        [$category, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Autora']);
        $replyAuthor = User::factory()->create(['alias' => 'Respuesta']);

        $this->actingAs($author)->post(route('forum.threads.store', $forum), [
            'title' => 'Un tema para conversar',
            'body' => 'Primer mensaje seguro.',
        ])->assertRedirect();

        $thread = ForumThread::query()->firstOrFail();
        $this->assertSame(0, $thread->replies_count);
        $this->assertSame(1, $author->fresh()->community_message_count);
        $this->assertDatabaseHas('forum_posts', ['forum_thread_id' => $thread->id, 'is_initial' => true]);

        $this->actingAs($replyAuthor)->post(route('forum.posts.store', $thread), [
            'body' => 'Una respuesta segura.',
        ])->assertRedirect(route('forum.threads.show', $thread).'#post-2');

        $this->assertSame(1, $thread->fresh()->replies_count);
        $this->assertSame(1, $replyAuthor->fresh()->community_message_count);
        $this->assertSame(2, ForumPost::query()->count());
    }

    public function test_hidden_or_deleted_forum_content_is_not_counted(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Autora']);
        $moderator = User::factory()->create(['role' => 'moderator']);
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema de prueba', 'Mensaje inicial.');
        $reply = app(ForumPostService::class)->reply($thread, $author, 'Mensaje que se ocultará.');

        $this->actingAs($moderator)->patch(route('forum.moderation.post.hide', $reply))->assertRedirect();

        $this->assertTrue($reply->fresh()->is_hidden);
        $this->assertSame(0, $thread->fresh()->replies_count);
        $this->assertSame(1, $author->fresh()->community_message_count);

        $this->actingAs($author)->delete(route('forum.posts.destroy', $reply))->assertRedirect();
        $this->assertSoftDeleted('forum_posts', ['id' => $reply->id]);
        $this->assertSame(1, $author->fresh()->community_message_count);
    }

    public function test_mentions_notify_once_and_subscriptions_do_not_duplicate_that_notification(): void
    {
        [, $forum] = $this->forum();
        $owner = User::factory()->create(['alias' => 'Miyu']);
        $replyAuthor = User::factory()->create(['alias' => 'Hana']);
        $thread = app(ForumThreadService::class)->create($forum, $owner, 'Tema con mención', 'Hola comunidad.');

        app(ForumPostService::class)->reply($thread, $replyAuthor, 'Hola @Miyu, gracias por abrirlo.');

        $this->assertDatabaseHas('forum_mentions', ['mentioned_user_id' => $owner->id, 'mentioner_user_id' => $replyAuthor->id]);
        $this->assertSame(1, $owner->notifications()->count());
        $this->assertSame('forum_mention', $owner->notifications()->first()->data['kind']);
    }

    public function test_forum_content_is_escaped_and_private_comments_are_not_reused(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Autora']);
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema seguro', '<script>alert(1)</script>');

        $this->get(route('forum.threads.show', $thread))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_regular_members_cannot_manage_structure_but_admins_can_create_categories_and_forums(): void
    {
        $member = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($member)->get(route('admin.forum-categories.index'))->assertRedirect('/');

        $this->actingAs($admin)->post(route('admin.forum-categories.store'), [
            'name' => 'Comunidad',
            'slug' => 'comunidad',
            'sort_order' => 0,
            'is_active' => true,
        ])->assertRedirect(route('admin.forum-categories.index'));

        $category = ForumCategory::query()->firstOrFail();
        $this->actingAs($admin)->post(route('admin.forums.store'), [
            'forum_category_id' => $category->id,
            'name' => 'General',
            'slug' => 'general',
            'sort_order' => 0,
            'is_locked' => false,
        ])->assertRedirect(route('admin.forums.index'));

        $this->assertDatabaseHas('forums', ['name' => 'General', 'forum_category_id' => $category->id]);
    }

    public function test_moderators_can_lock_pin_hide_and_move_threads_without_receiving_structure_access(): void
    {
        [$category, $forum] = $this->forum();
        $destination = Forum::query()->create([
            'forum_category_id' => $category->id,
            'name' => 'Recomendaciones',
            'slug' => 'recomendaciones',
            'sort_order' => 1,
        ]);
        $author = User::factory()->create(['alias' => 'Autora']);
        $moderator = User::factory()->create(['role' => 'moderator']);
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema moderable', 'Contenido inicial.');

        $this->actingAs($moderator)->patch(route('forum.moderation.thread.update', $thread), [
            'is_locked' => true,
            'is_pinned' => true,
            'is_hidden' => true,
            'forum_id' => $destination->id,
        ])->assertRedirect();

        $thread->refresh();
        $this->assertTrue($thread->is_locked);
        $this->assertTrue($thread->is_pinned);
        $this->assertTrue($thread->is_hidden);
        $this->assertSame($destination->id, $thread->forum_id);
        $this->assertSame(0, $author->fresh()->community_message_count);
        $this->actingAs(User::factory()->create())->get(route('forum.threads.show', $thread))->assertNotFound();
        $this->actingAs($moderator)->get(route('admin.forum-categories.index'))->assertRedirect('/');
    }

    public function test_recount_command_restores_message_and_reply_counters_from_visible_content(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Autora']);
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema para reconciliar', 'Inicial.');
        app(ForumPostService::class)->reply($thread, $author, 'Respuesta visible.');
        $author->update(['community_message_count' => 99]);
        $thread->update(['replies_count' => 99]);

        $this->artisan('community:recount-forums')
            ->expectsOutput('Contadores de foros reconciliados correctamente.')
            ->assertExitCode(0);

        $this->assertSame(2, $author->fresh()->community_message_count);
        $this->assertSame(1, $thread->fresh()->replies_count);
    }

    /** @return array{ForumCategory, Forum} */
    private function forum(): array
    {
        $category = ForumCategory::query()->create([
            'name' => 'Comunidad',
            'slug' => 'comunidad',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return [$category, Forum::query()->create([
            'forum_category_id' => $category->id,
            'name' => 'General',
            'slug' => 'general',
            'sort_order' => 0,
        ])];
    }
}
