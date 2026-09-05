<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Forum;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use App\Services\ForumPostService;
use App\Services\ForumThreadService;
use App\Services\QuestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContextualReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_reply_to_a_reply_in_a_forum_and_notify_its_author_once(): void
    {
        $owner = User::factory()->create();
        $recipient = User::factory()->create(['alias' => 'Hana']);
        $writer = User::factory()->create();
        $category = ForumCategory::query()->create(['name' => 'Comunidad', 'slug' => 'comunidad', 'is_active' => true]);
        $forum = Forum::query()->create(['forum_category_id' => $category->id, 'name' => 'General', 'slug' => 'general']);
        $thread = app(ForumThreadService::class)->create($forum, $owner, 'Tema con conversación', 'Inicio');
        $reply = app(ForumPostService::class)->reply($thread, $recipient, 'Mi recomendación');
        $thread->subscribers()->syncWithoutDetaching([$recipient->id]);
        $response = $this->actingAs($writer)->postJson(route('forum.posts.store', $thread), [
            'body' => 'Gracias por la recomendación', 'reply_to_post_id' => $reply->id,
        ])->assertCreated()->assertJsonPath('replies_count', 2);
        $this->assertStringContainsString('forum-post-branch', $response->json('html'));
        $this->assertStringNotContainsString('message-reply-context', $response->json('html'));
        $this->assertDatabaseHas('forum_posts', ['user_id' => $writer->id, 'reply_to_post_id' => $reply->id]);
        $this->assertSame(1, $recipient->notifications()->count());
        $this->assertSame(1, $writer->fresh()->community_message_count);
        $this->get(route('forum.threads.show', $thread))->assertOk()
            ->assertSeeInOrder(['Mi recomendación', 'Gracias por la recomendación'])
            ->assertSee('forum-post-children', false);
    }

    public function test_question_answer_can_receive_a_reply_and_hidden_original_text_is_not_leaked(): void
    {
        $owner = User::factory()->create();
        $writer = User::factory()->create();
        $question = app(QuestionService::class)->create($owner, 'Busco una serie', '¿Cuál será?');
        $answer = app(ForumPostService::class)->reply($question, $owner, 'Texto que se ocultará');
        $this->actingAs($writer)->postJson(route('questions.answers.store', $question), [
            'body' => 'Respuesta específica', 'reply_to_post_id' => $answer->id,
        ])->assertCreated();
        $this->assertDatabaseHas('forum_posts', ['body' => 'Respuesta específica', 'reply_to_post_id' => $answer->id]);
        $answer->update(['is_hidden' => true]);
        $this->get(route('questions.show', $question))->assertOk()
            ->assertSee('Respuesta específica')->assertDontSee('Texto que se ocultará')
            ->assertDontSee('message-reply-context', false);
    }

    public function test_reply_targets_cannot_cross_threads_or_bypass_blocks_hidden_content_and_locks(): void
    {
        $owner = User::factory()->create();
        $writer = User::factory()->create();
        $question = app(QuestionService::class)->create($owner, 'Primera pregunta', 'Contexto');
        $other = app(QuestionService::class)->create($owner, 'Segunda pregunta', 'Contexto');
        $target = $question->posts()->first();
        $payload = ['body' => 'No permitida', 'reply_to_post_id' => $target->id];
        $this->actingAs($writer)->postJson(route('questions.answers.store', $other), $payload)->assertUnprocessable();
        $owner->blockedUsers()->attach($writer);
        $this->postJson(route('questions.answers.store', $question), $payload)->assertUnprocessable();
        $owner->blockedUsers()->detach($writer);
        $target->update(['is_hidden' => true]);
        $this->postJson(route('questions.answers.store', $question), $payload)->assertUnprocessable();
        $target->update(['is_hidden' => false]);
        $question->update(['is_locked' => true]);
        $this->postJson(route('questions.answers.store', $question), $payload)->assertForbidden();
        $this->assertSame(2, ForumPost::query()->count());
    }

    public function test_episode_replies_can_be_answered_without_disappearing_into_unrendered_depths(): void
    {
        $author = User::factory()->create();
        $series = $this->series($author);
        $episode = $this->episode($series, $author);
        $root = $episode->comments()->create(['user_id' => $author->id, 'body' => 'Raíz', 'is_approved' => true]);
        $reply = $episode->comments()->create(['parent_id' => $root->id, 'user_id' => $author->id, 'body' => 'Respuesta primera', 'is_approved' => true]);
        $this->actingAs($author)->post(route('comments.store'), [
            'target_type' => 'episode', 'target_id' => $episode->id, 'parent_id' => $reply->id, 'body' => 'Respuesta a la respuesta',
        ])->assertRedirect();
        $this->assertDatabaseHas('comments', ['body' => 'Respuesta a la respuesta', 'parent_id' => $reply->id, 'reply_to_comment_id' => $reply->id]);
        $this->get(route('public.episodes.show', $episode->slug))->assertOk()
            ->assertSee('Respuesta a la respuesta')->assertSee('comment-tree-children', false)
            ->assertSee('data-author-card', false)->assertSee('data-reaction-control', false);
    }

    public function test_catalog_reply_rejects_other_content_unapproved_parents_and_blocked_authors(): void
    {
        $author = User::factory()->create();
        $writer = User::factory()->create();
        $series = $this->series($author);
        $episode = $this->episode($series, $author);
        $comment = $series->comments()->create(['user_id' => $author->id, 'body' => 'Comentario', 'is_approved' => true]);
        $payload = ['target_type' => 'episode', 'target_id' => $episode->id, 'parent_id' => $comment->id, 'body' => 'Respuesta inválida'];
        $this->actingAs($writer)->postJson(route('comments.store'), $payload)->assertUnprocessable();
        $payload['target_type'] = 'series';
        $payload['target_id'] = $series->id;
        $author->blockedUsers()->attach($writer);
        $this->postJson(route('comments.store'), $payload)->assertUnprocessable();
        $comment->update(['is_approved' => false]);
        $this->postJson(route('comments.store'), $payload)->assertNotFound();
        $this->assertDatabaseCount('comments', 1);
    }

    public function test_catalog_reactions_have_real_anchors_and_respect_moderation_and_blocks(): void
    {
        $author = User::factory()->create();
        $writer = User::factory()->create();
        $series = $this->series($author);
        $comment = $series->comments()->create(['user_id' => $author->id, 'body' => 'Comentario', 'is_approved' => true]);
        $payload = ['target' => 'comment', 'target_id' => $comment->id, 'type' => 'love'];
        $this->actingAs($writer)->postJson(route('community.reactions.store'), $payload)->assertOk()->assertJsonPath('total', 1);
        $this->assertSame(route('catalog.series.show', $series).'#comment-'.$comment->id, $author->notifications()->first()->data['url']);
        $this->get(route('catalog.series.show', $series))->assertOk()->assertSee('Me encanta');
        $author->blockedUsers()->attach($writer);
        $this->postJson(route('community.reactions.store'), $payload)->assertForbidden();
        $series->update(['moderation_status' => 'pending']);
        $this->postJson(route('community.reactions.store'), $payload)->assertNotFound();
    }

    public function test_forum_images_are_validated_optimized_and_can_be_published_without_text(): void
    {
        Storage::fake('public');
        $category = ForumCategory::query()->create(['name' => 'Arte', 'slug' => 'arte', 'is_active' => true]);
        $forum = Forum::query()->create(['forum_category_id' => $category->id, 'name' => 'Fanart', 'slug' => 'fanart']);
        $author = User::factory()->create();
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Comparte arte', 'Primer mensaje');
        // A phone-sized source may exceed 4,000 px; the server must resize it instead of rejecting it.
        $image = UploadedFile::fake()->image('ilustracion.png', 4200, 3000);

        $this->actingAs($author)->postJson(route('forum.posts.store', $thread), [
            'body' => '', 'image' => $image,
        ])->assertCreated();

        $post = $thread->posts()->latest('id')->firstOrFail();
        $this->assertSame('', $post->body);
        $this->assertStringEndsWith('.webp', $post->image_path);
        Storage::disk('public')->assertExists($post->image_path);
        $this->assertLessThanOrEqual(800 * 1024, Storage::disk('public')->size($post->image_path));

        $this->postJson(route('forum.posts.store', $thread), [
            'body' => '', 'image' => UploadedFile::fake()->create('no-es-imagen.jpg', 10, 'text/plain'),
        ])->assertUnprocessable()->assertJsonValidationErrors('image');
    }

    public function test_full_conversations_only_offer_all_replies_after_one_hundred_messages(): void
    {
        $author = User::factory()->create();
        $category = ForumCategory::query()->create(['name' => 'Conversación', 'slug' => 'conversacion', 'is_active' => true]);
        $forum = Forum::query()->create(['forum_category_id' => $category->id, 'name' => 'General', 'slug' => 'general']);
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Hilo largo', 'Inicio');

        foreach (range(1, 101) as $number) {
            $thread->posts()->create(['user_id' => $author->id, 'body' => 'Respuesta '.$number]);
        }
        $thread->update(['replies_count' => 101]);

        $this->get(route('forum.threads.show', $thread))->assertOk()
            ->assertSee('Respuesta 100')->assertDontSee('Respuesta 101')
            ->assertSee('?all=1', false);
        $this->get(route('forum.threads.show', $thread).'?all=1')->assertOk()->assertSee('Respuesta 101');

        $question = app(QuestionService::class)->create($author, 'Pregunta larga', 'Inicio');
        foreach (range(1, 101) as $number) {
            $question->posts()->create(['user_id' => $author->id, 'body' => 'Aporte '.$number]);
        }
        $question->update(['replies_count' => 101]);

        $this->get(route('questions.show', $question))->assertOk()
            ->assertSee('Aporte 100')->assertDontSee('Aporte 101')
            ->assertSee('?all=1', false);
        $this->get(route('questions.show', $question).'?all=1')->assertOk()->assertSee('Aporte 101');
    }

    private function series(User $author): Series
    {
        $genre = Genre::query()->create(['name' => 'Drama', 'slug' => 'drama', 'is_active' => true]);

        return Series::query()->create([
            'genre_id' => $genre->id, 'created_by' => $author->id, 'title' => 'Serie de prueba', 'slug' => 'serie-prueba',
            'description' => 'Una serie para las pruebas.', 'content_type' => 'series', 'status' => 'ongoing',
            'moderation_status' => 'approved', 'published_at' => now(),
        ]);
    }

    private function episode(Series $series, User $author): Episode
    {
        return $series->episodes()->create([
            'created_by' => $author->id, 'title' => 'Episodio de prueba', 'slug' => 'episodio-prueba',
            'season_number' => 1, 'episode_number' => 1, 'description' => 'Episodio para pruebas.',
            'moderation_status' => 'approved', 'published_at' => now(),
        ]);
    }
}
