<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\CommunityReaction;
use App\Models\Forum;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use App\Notifications\CommunityReactionNotification;
use App\Services\ForumPostService;
use App\Services\ForumThreadService;
use App\Services\QuestionService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityReactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_add_change_and_remove_their_single_reaction_to_a_forum_post(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Autora']);
        $member = User::factory()->create(['alias' => 'Hana']);
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema para reaccionar', 'Contenido.');
        $post = $thread->posts()->firstOrFail();

        $this->actingAs($member)->post(route('community.reactions.store'), [
            'target' => 'post', 'target_id' => $post->id, 'type' => 'love',
        ])->assertSessionHas('success', 'Reacción guardada.');

        $this->assertDatabaseHas('community_reactions', [
            'user_id' => $member->id,
            'reactable_type' => ForumPost::class,
            'reactable_id' => $post->id,
            'type' => 'love',
        ]);

        $this->actingAs($member)->post(route('community.reactions.store'), [
            'target' => 'post', 'target_id' => $post->id, 'type' => 'yuri',
        ])->assertSessionHas('success', 'Reacción guardada.');

        $this->assertSame(1, CommunityReaction::query()->count());
        $this->assertDatabaseHas('community_reactions', ['reactable_id' => $post->id, 'type' => 'yuri']);

        $this->actingAs($member)->post(route('community.reactions.store'), [
            'target' => 'post', 'target_id' => $post->id, 'type' => 'yuri',
        ])->assertSessionHas('success', 'Reacción retirada.');

        $this->assertDatabaseCount('community_reactions', 0);
    }

    public function test_question_and_answers_are_valid_reaction_targets_and_summaries_render_without_n_plus_one_relations(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Autora']);
        $answerer = User::factory()->create(['alias' => 'Respuesta']);
        $member = User::factory()->create(['alias' => 'Hana']);
        $question = app(QuestionService::class)->create($author, '¿Pregunta con reacciones?', 'Contenido.');
        $answer = app(ForumPostService::class)->reply($question, $answerer, 'Una respuesta.');

        $this->actingAs($member)->post(route('community.reactions.store'), [
            'target' => 'thread', 'target_id' => $question->id, 'type' => 'like',
        ])->assertSessionHas('success', 'Reacción guardada.');
        $this->actingAs($member)->post(route('community.reactions.store'), [
            'target' => 'post', 'target_id' => $answer->id, 'type' => 'love',
        ])->assertSessionHas('success', 'Reacción guardada.');

        $this->get(route('questions.show', $question))
            ->assertOk()
            ->assertSee('👍')
            ->assertSee('❤️');
    }

    public function test_reactions_cannot_target_hidden_forum_content_and_validation_rejects_unknown_types(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create();
        $member = User::factory()->create();
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema oculto', 'Contenido.');
        $thread->update(['is_hidden' => true]);

        $this->actingAs($member)->post(route('community.reactions.store'), [
            'target' => 'thread', 'target_id' => $thread->id, 'type' => 'love',
        ])->assertForbidden();

        $visible = app(ForumThreadService::class)->create($forum, $author, 'Tema visible', 'Contenido.');
        $this->actingAs($member)->from(route('forum.threads.show', $visible))->post(route('community.reactions.store'), [
            'target' => 'thread', 'target_id' => $visible->id, 'type' => 'unknown',
        ])->assertRedirect(route('forum.threads.show', $visible))
            ->assertSessionHasErrors('type');
    }

    public function test_reaction_notifications_are_grouped_per_unread_content_and_not_sent_for_self_reactions(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Autora']);
        $firstMember = User::factory()->create(['alias' => 'Hana']);
        $secondMember = User::factory()->create(['alias' => 'Miyu']);
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema popular', 'Contenido.');
        $post = $thread->posts()->firstOrFail();

        $this->actingAs($firstMember)->post(route('community.reactions.store'), [
            'target' => 'post', 'target_id' => $post->id, 'type' => 'love',
        ]);
        $this->actingAs($secondMember)->post(route('community.reactions.store'), [
            'target' => 'post', 'target_id' => $post->id, 'type' => 'yuri',
        ]);
        $this->actingAs($author)->post(route('community.reactions.store'), [
            'target' => 'post', 'target_id' => $post->id, 'type' => 'like',
        ]);

        $this->assertSame(1, $author->notifications()->where('type', CommunityReactionNotification::class)->count());
        $notification = $author->notifications()->firstOrFail();
        $this->assertSame(2, $notification->data['actor_count']);
        $this->assertSame([$firstMember->id, $secondMember->id], $notification->data['actor_ids']);
    }

    public function test_database_constraint_prevents_duplicate_active_reactions_and_comments_are_ready_as_targets(): void
    {
        $author = User::factory()->create();
        $member = User::factory()->create();
        $comment = Comment::query()->create([
            'user_id' => $author->id,
            'alias' => 'Autora',
            'body' => 'Comentario público.',
            'is_approved' => true,
            'commentable_type' => ForumThread::class,
            'commentable_id' => 999,
        ]);

        $this->actingAs($member)->post(route('community.reactions.store'), [
            'target' => 'comment', 'target_id' => $comment->id, 'type' => 'laugh',
        ])->assertSessionHas('success', 'Reacción guardada.');

        $this->expectException(QueryException::class);
        CommunityReaction::query()->create([
            'user_id' => $member->id,
            'reactable_type' => Comment::class,
            'reactable_id' => $comment->id,
            'type' => 'cry',
        ]);
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
            'is_active' => true,
        ])];
    }
}
