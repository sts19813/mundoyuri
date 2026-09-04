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
use Illuminate\Support\Facades\DB;
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

    public function test_forum_feed_shows_content_and_only_two_latest_visible_replies_per_topic(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create();
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema en el feed', 'Contenido original del feed');
        foreach (range(1, 4) as $number) {
            app(ForumPostService::class)->reply($thread, $author, 'Respuesta número '.$number);
        }
        app(ForumPostService::class)->reply($thread, $author, 'Respuesta oculta')->update(['is_hidden' => true]);
        $hidden = app(ForumThreadService::class)->create($forum, $author, 'Tema escondido', 'Texto secreto');
        $hidden->update(['is_hidden' => true]);

        $this->actingAs($author)->get(route('forums.show', $forum))->assertOk()
            ->assertSee('Contenido original del feed')->assertSee('Respuesta número 3')
            ->assertSee('Respuesta número 4')->assertDontSee('Respuesta número 1')
            ->assertDontSee('Respuesta oculta')->assertDontSee('Texto secreto')
            ->assertSee(route('forum.posts.store', $thread), false)
            ->assertViewHas('threads', fn ($threads) => $threads->first()->previewReplies->count() === 2);
    }

    public function test_forum_feed_paginates_fifty_topics_and_preserves_search(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create();
        foreach (range(1, 51) as $number) {
            $forum->threads()->create([
                'user_id' => $author->id, 'title' => 'Conversación '.$number,
                'slug' => 'conversacion-'.$number, 'type' => 'discussion', 'last_post_at' => now(),
            ]);
        }
        $this->get(route('forums.show', $forum).'?q=Conversaci')->assertOk()
            ->assertViewHas('threads', fn ($threads) => $threads->perPage() === 50 && $threads->count() === 50 && $threads->total() === 51)
            ->assertSee('q=Conversaci', false);
        $this->get(route('forums.show', $forum).'?q=Conversaci&page=2')->assertOk()
            ->assertViewHas('threads', fn ($threads) => $threads->count() === 1);
    }

    public function test_feed_reply_returns_safe_content_and_rejects_invalid_or_locked_submissions(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create();
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema con respuestas', 'Mensaje inicial');
        $member = User::factory()->create();

        $response = $this->actingAs($member)->postJson(route('forum.posts.store', $thread), [
            'body' => '<script>alert(123)</script>', 'from_feed' => true,
        ])->assertCreated()->assertJsonPath('replies_count', 1);
        $this->assertStringContainsString('&lt;script&gt;', $response->json('html'));
        $this->assertStringNotContainsString('<script>alert', $response->json('html'));
        $this->assertSame(1, $member->fresh()->community_message_count);
        $this->postJson(route('forum.posts.store', $thread), ['body' => ''])->assertUnprocessable();
        $thread->update(['is_locked' => true]);
        $this->postJson(route('forum.posts.store', $thread), ['body' => 'No permitida'])->assertForbidden();
        $this->assertSame(2, $thread->posts()->count());
    }

    public function test_feed_creation_and_plain_reply_return_to_the_forum(): void
    {
        [, $forum] = $this->forum();
        $member = User::factory()->create();
        $this->actingAs($member)->post(route('forum.threads.store', $forum), [
            'title' => 'Creación desde el feed', 'body' => 'Hola comunidad', 'from_feed' => true,
        ])->assertRedirect(route('forums.show', $forum).'#thread-'.ForumThread::query()->firstOrFail()->id);
        $thread = ForumThread::query()->firstOrFail();
        $this->post(route('forum.posts.store', $thread), ['body' => 'Respuesta sin JavaScript', 'from_feed' => true])
            ->assertRedirect(route('forums.show', $forum).'#thread-'.$thread->id);
        $this->get(route('forums.show', $forum))->assertSee('forum-topic-dialog');
        $forum->update(['is_locked' => true]);
        $this->get(route('forums.show', $forum))->assertDontSee('id="forum-topic-dialog"', false)
            ->assertDontSee('data-feed-reply', false);
    }

    public function test_expanding_feed_replies_is_paginated_and_keeps_hidden_content_private(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create();
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Conversación larga', 'Mensaje de apertura');
        foreach (range(1, 21) as $number) {
            $thread->posts()->create(['user_id' => $author->id, 'body' => 'Contenido de respuesta '.$number]);
        }
        $thread->posts()->create(['user_id' => $author->id, 'body' => 'Texto oculto', 'is_hidden' => true]);
        $response = $this->getJson(route('forum.threads.show', $thread))->assertOk();
        $this->assertSame(20, substr_count($response->json('html'), 'class="forum-post '));
        $this->assertStringNotContainsString('Texto oculto', $response->json('html'));
        $this->assertStringNotContainsString('Mensaje de apertura', $response->json('html'));
        $secondPage = $this->getJson($response->json('next_page_url'))->assertOk()->assertJsonPath('next_page_url', null);
        $this->assertStringContainsString('Contenido de respuesta 21', $secondPage->json('html'));
        $this->assertSame(0, $thread->fresh()->views_count);
        $thread->update(['is_hidden' => true]);
        $this->getJson(route('forum.threads.show', $thread))->assertNotFound();
    }

    public function test_feed_queries_do_not_grow_per_topic_and_each_topic_gets_its_own_preview(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create();
        $create = function ($number) use ($forum, $author): void {
            $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema número '.$number, 'Contenido inicial');
            foreach (range(1, 3) as $reply) {
                $thread->posts()->create(['user_id' => $author->id, 'body' => 'Tema '.$number.' respuesta '.$reply]);
            }
        };
        $create(1);
        $this->get(route('forums.show', $forum))->assertOk();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->get(route('forums.show', $forum))->assertOk();
        $singleQueries = count(DB::getQueryLog());
        foreach (range(2, 5) as $number) {
            $create($number);
        }
        DB::flushQueryLog();
        $this->get(route('forums.show', $forum))->assertOk()
            ->assertViewHas('threads', fn ($threads) => $threads->every(fn ($thread) => $thread->previewReplies->count() === 2))
            ->assertSee('Tema 1 respuesta 3')->assertSee('Tema 5 respuesta 3');
        $multipleQueries = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertLessThanOrEqual($singleQueries + 2, $multipleQueries);
    }

    public function test_author_card_respects_private_profiles_blocks_and_signature_preferences(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['signature_enabled' => true, 'signature_text' => 'Firma distintiva del autor', 'profile_visibility' => 'private']);
        app(ForumThreadService::class)->create($forum, $author, 'Privacidad del autor', 'Texto público del foro');
        $viewer = User::factory()->create(['show_signatures' => true]);
        $this->actingAs($viewer)->get(route('forums.show', $forum))->assertOk()
            ->assertSee('Texto público del foro')->assertDontSee('Firma distintiva del autor');
        $author->update(['profile_visibility' => 'public']);
        $this->get(route('forums.show', $forum))->assertSee('Firma distintiva del autor')
            ->assertSee(route('messages.show', $author), false);
        $viewer->update(['show_signatures' => false]);
        $this->get(route('forums.show', $forum))->assertDontSee('Firma distintiva del autor');
        $viewer->blockedUsers()->attach($author);
        $viewer->unsetRelation('blockedUsers');
        $this->get(route('forums.show', $forum))->assertDontSee(route('messages.show', $author), false);
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
