<?php

namespace Tests\Feature;

use App\Models\Forum;
use App\Models\ForumCategory;
use App\Models\ForumPostVote;
use App\Models\ForumThread;
use App\Models\ForumThreadVote;
use App\Models\User;
use App\Notifications\QuestionAnswerAcceptedNotification;
use App\Services\ForumPostService;
use App\Services\QuestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_create_a_question_with_tags_and_it_reuses_forum_counters(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Autora']);

        $this->actingAs($author)->post(route('questions.store'), [
            'forum_id' => $forum->id,
            'title' => '¿Dónde puedo encontrar una recomendación?',
            'body' => 'Busco una serie con una historia tranquila.',
            'tags' => ['recomendaciones', 'series'],
        ])->assertRedirect();

        $question = ForumThread::query()->firstOrFail();
        $this->assertSame('question', $question->type);
        $this->assertSame(1, $author->fresh()->community_message_count);
        $this->assertSame(['recomendaciones', 'series'], $question->questionTags()->orderBy('name')->pluck('name')->all());
        $this->get(route('questions.index'))->assertOk()->assertSee($question->title);
    }

    public function test_votes_are_unique_and_members_cannot_vote_for_themselves(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Autora']);
        $voter = User::factory()->create(['alias' => 'Votante']);
        $question = app(QuestionService::class)->create($forum, $author, 'Pregunta para votar', 'Necesito ayuda.', []);

        $this->actingAs($author)->post(route('questions.votes.store', $question))->assertForbidden();
        $this->actingAs($voter)->post(route('questions.votes.store', $question))->assertRedirect();
        $this->actingAs($voter)->post(route('questions.votes.store', $question))->assertSessionHas('error');

        $this->assertSame(1, $question->fresh()->upvotes_count);
        $this->assertSame(1, $author->fresh()->community_reputation);
        $this->assertSame(1, ForumThreadVote::query()->count());
    }

    public function test_answer_acceptance_requires_question_owner_or_moderator_and_awards_reputation(): void
    {
        Notification::fake();
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Autora']);
        $answerer = User::factory()->create(['alias' => 'Ayuda']);
        $outsider = User::factory()->create(['alias' => 'Ajena']);
        $question = app(QuestionService::class)->create($forum, $author, 'Pregunta con solución', 'Aquí están los detalles.', []);
        $answer = app(ForumPostService::class)->reply($question, $answerer, 'Esta es una solución comprobada.');

        $this->actingAs($outsider)->post(route('questions.answers.accept', [$question, $answer]))->assertForbidden();
        $this->actingAs($author)->post(route('questions.answers.accept', [$question, $answer]))->assertRedirect();

        $this->assertSame($answer->id, $question->fresh()->accepted_answer_post_id);
        $this->assertSame(5, $answerer->fresh()->community_reputation);
        Notification::assertSentTo($answerer, QuestionAnswerAcceptedNotification::class);
        $this->assertSame(1, $answerer->acceptedForumAnswers()->count());

        $moderator = User::factory()->create(['role' => 'moderator']);
        $moderatedQuestion = app(QuestionService::class)->create($forum, $author, 'Pregunta moderada', 'Más contexto.', []);
        $moderatedAnswer = app(ForumPostService::class)->reply($moderatedQuestion, $outsider, 'Una solución revisada.');
        $this->actingAs($moderator)->post(route('questions.answers.accept', [$moderatedQuestion, $moderatedAnswer]))->assertRedirect();
        $this->assertSame($moderatedAnswer->id, $moderatedQuestion->fresh()->accepted_answer_post_id);
    }

    public function test_answer_votes_are_unique_and_profile_question_relationships_are_available(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Autora']);
        $answerer = User::factory()->create(['alias' => 'Respuesta']);
        $voter = User::factory()->create(['alias' => 'Votante']);
        $question = app(QuestionService::class)->create($forum, $author, 'Pregunta de relaciones', 'Contexto.', []);
        $answer = app(ForumPostService::class)->reply($question, $answerer, 'Respuesta útil.');

        $this->actingAs($answerer)->post(route('questions.answers.votes.store', $answer))->assertForbidden();
        $this->actingAs($voter)->post(route('questions.answers.votes.store', $answer))->assertRedirect();
        $this->actingAs($voter)->post(route('questions.answers.votes.store', $answer))->assertSessionHas('error');

        $this->assertSame(1, $answer->fresh()->upvotes_count);
        $this->assertSame(1, $answerer->fresh()->community_reputation);
        $this->assertSame(1, ForumPostVote::query()->count());
        $this->assertSame(1, $author->forumQuestions()->count());
        $this->assertSame(1, $answerer->forumAnswers()->count());
    }

    public function test_question_filters_support_unanswered_and_popular_ordering(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Autora']);
        $answered = app(QuestionService::class)->create($forum, $author, 'Pregunta respondida', 'Contexto uno.', []);
        $open = app(QuestionService::class)->create($forum, $author, 'Pregunta popular', 'Contexto dos.', []);
        app(ForumPostService::class)->reply($answered, User::factory()->create(), 'Una respuesta.');
        $open->update(['upvotes_count' => 12, 'views_count' => 50]);

        $this->get(route('questions.index', ['sort' => 'unanswered']))
            ->assertOk()
            ->assertSee('Pregunta popular')
            ->assertDontSee('Pregunta respondida');
        $this->get(route('questions.index', ['sort' => 'popular']))
            ->assertOk()
            ->assertSeeInOrder(['Pregunta popular', 'Pregunta respondida']);
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
