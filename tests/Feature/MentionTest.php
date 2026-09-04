<?php

namespace Tests\Feature;

use App\Models\Forum;
use App\Models\ForumCategory;
use App\Models\ForumMention;
use App\Models\User;
use App\Services\ForumPostService;
use App\Services\ForumThreadService;
use App\Services\QuestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentions_use_unique_aliases_ignore_unknown_users_and_render_safe_profile_links(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Hana']);
        $mentioned = User::factory()->create(['alias' => 'Miyu']);

        $thread = app(ForumThreadService::class)->create(
            $forum,
            $author,
            'Tema con mención',
            'Hola @Miyu y otra vez @Miyu. @Inexistente <script>alert("xss")</script>',
        );

        $post = $thread->posts()->firstOrFail();
        $this->assertSame(1, ForumMention::query()->where('forum_post_id', $post->id)->count());
        $this->assertSame(1, $mentioned->notifications()->count());

        $this->get(route('forum.threads.show', $thread))
            ->assertOk()
            ->assertSee('<a class="forum-mention" href="'.$mentioned->publicProfileUrl().'">@Miyu</a>', false)
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertSee('@Inexistente');
    }

    public function test_unchanged_mentions_on_edit_do_not_notify_again_but_new_mentions_do(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Hana']);
        $firstMentioned = User::factory()->create(['alias' => 'Miyu']);
        $secondMentioned = User::factory()->create(['alias' => 'Yuri']);
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema editable', 'Hola @Miyu.');
        $post = $thread->posts()->firstOrFail();

        app(ForumPostService::class)->update($post, 'Hola @Miyu, con una corrección.');
        $this->assertSame(1, $firstMentioned->notifications()->count());
        $this->assertSame(1, ForumMention::query()->where('forum_post_id', $post->id)->count());

        app(ForumPostService::class)->update($post, 'Hola @Miyu y @Yuri.');
        $this->assertSame(1, $firstMentioned->notifications()->count());
        $this->assertDatabaseHas('forum_mentions', ['forum_post_id' => $post->id, 'mentioned_user_id' => $secondMentioned->id]);
        $this->assertSame(1, $secondMentioned->notifications()->count());
        $this->assertSame(2, ForumMention::query()->where('forum_post_id', $post->id)->count());
    }

    public function test_mentions_respect_existing_blocks_and_are_available_for_questions_and_answers(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['alias' => 'Hana']);
        $blocked = User::factory()->create(['alias' => 'Miyu']);
        $answerer = User::factory()->create(['alias' => 'Yuri']);
        $author->blockedUsers()->attach($blocked);

        $question = app(QuestionService::class)->create($author, 'Pregunta de menciones', '¿Puede verlo @Miyu?');
        $questionPost = $question->posts()->firstOrFail();
        $this->assertDatabaseMissing('forum_mentions', ['forum_post_id' => $questionPost->id, 'mentioned_user_id' => $blocked->id]);
        $this->assertSame(0, $blocked->notifications()->count());

        $answer = app(ForumPostService::class)->reply($question, $answerer, 'Sí, @Hana, aquí tienes una respuesta.');
        $this->assertDatabaseHas('forum_mentions', ['forum_post_id' => $answer->id, 'mentioned_user_id' => $author->id]);
        $this->assertSame(1, $author->notifications()->count());
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
