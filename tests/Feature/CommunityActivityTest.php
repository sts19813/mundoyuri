<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Forum;
use App\Models\ForumCategory;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use App\Services\CommunityActivityService;
use App\Services\ForumPostService;
use App\Services\ForumThreadService;
use App\Services\QuestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_activity_uses_only_public_community_events_and_respects_favorite_preference(): void
    {
        [, $forum] = $this->forum();
        $member = User::factory()->create(['show_activity' => true, 'show_favorites' => true]);
        $other = User::factory()->create();
        $threads = app(ForumThreadService::class);
        $posts = app(ForumPostService::class);
        $questions = app(QuestionService::class);

        $threads->create($forum, $member, 'Tema propio', 'Primer tema.');
        $topic = $threads->create($forum, $other, 'Tema para responder', 'Contexto.');
        $posts->reply($topic, $member, 'Mi respuesta al tema.');
        $questions->create($member, 'Pregunta propia', 'Necesito ayuda.');
        $question = $questions->create($other, 'Pregunta para responder', 'Contexto.');
        $answer = $posts->reply($question, $member, 'Respuesta que será aceptada.');
        $questions->acceptAnswer($question, $answer, $other);

        $series = $this->publishedSeries($member, 'Serie favorita');
        $member->favoriteSeries()->attach($series->id);
        $badge = Badge::query()->create([
            'name' => 'Colaboradora', 'slug' => 'colaboradora', 'type' => 'achievement', 'priority' => 1, 'is_active' => true,
        ]);
        $member->badges()->attach($badge->id, ['awarded_at' => now()]);

        $activities = app(CommunityActivityService::class)->forUser($member, true, 12);
        $this->assertEqualsCanonicalizing([
            'created_thread', 'replied_thread', 'created_question', 'answered_question',
            'accepted_answer', 'favorite_series', 'badge_awarded',
        ], $activities->pluck('activity_type')->all());

        $member->update(['show_favorites' => false]);
        $this->get($member->publicProfileUrl())
            ->assertOk()
            ->assertDontSee('Serie favorita')
            ->assertDontSee('Marcó como favorita');
    }

    public function test_activity_is_hidden_when_disabled_or_between_blocked_members_and_owner_retains_access(): void
    {
        [, $forum] = $this->forum();
        $member = User::factory()->create(['show_activity' => false, 'show_favorites' => true]);
        $viewer = User::factory()->create();
        app(ForumThreadService::class)->create($forum, $member, 'Actividad privada', 'Contenido.');

        $this->get($member->publicProfileUrl())
            ->assertOk()
            ->assertDontSee('Actividad reciente')
            ->assertDontSee('Actividad privada');

        $this->actingAs($member)->get($member->publicProfileUrl())
            ->assertOk()
            ->assertSee('Actividad reciente')
            ->assertSee('Actividad privada');

        $member->update(['show_activity' => true]);
        $member->blockedUsers()->attach($viewer->id);

        $this->actingAs($viewer)->get($member->publicProfileUrl())
            ->assertOk()
            ->assertDontSee('Actividad reciente')
            ->assertDontSee('Actividad privada');
    }

    public function test_activity_shows_only_the_five_most_recent_visible_events(): void
    {
        [, $forum] = $this->forum();
        $member = User::factory()->create(['show_activity' => true]);
        $threads = app(ForumThreadService::class);

        $hidden = $threads->create($forum, $member, 'Tema oculto', 'No debe aparecer.');
        $hidden->update(['is_hidden' => true]);
        $deleted = $threads->create($forum, $member, 'Tema eliminado', 'No debe aparecer.');
        $deleted->delete();

        foreach (range(1, 13) as $number) {
            $threads->create($forum, $member, 'Tema paginado '.$number, 'Contenido.');
        }

        foreach (['', '?activity_page=2'] as $query) {
            $response = $this->get($member->publicProfileUrl().$query)
                ->assertOk()
                ->assertDontSee('Tema oculto')
                ->assertDontSee('Tema eliminado')
                ->assertDontSee('community-activity-pagination', false)
                ->assertDontSee('Tema paginado 8');

            foreach (range(9, 13) as $number) {
                $response->assertSee('Tema paginado '.$number);
            }
        }
    }

    /** @return array{ForumCategory, Forum} */
    private function forum(): array
    {
        $category = ForumCategory::query()->create([
            'name' => 'Comunidad', 'slug' => 'comunidad', 'sort_order' => 0, 'is_active' => true,
        ]);

        return [$category, Forum::query()->create([
            'forum_category_id' => $category->id, 'name' => 'General', 'slug' => 'general', 'sort_order' => 0, 'is_active' => true,
        ])];
    }

    private function publishedSeries(User $author, string $title): Series
    {
        $genre = Genre::query()->create(['name' => 'Girls Love', 'slug' => 'girls-love', 'is_active' => true]);

        return Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $author->id,
            'approved_by' => $author->id,
            'title' => $title,
            'slug' => 'serie-favorita',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Serie publicada para comprobar la actividad comunitaria.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
    }
}
