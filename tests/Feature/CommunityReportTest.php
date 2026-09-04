<?php

namespace Tests\Feature;

use App\Models\Forum;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use App\Services\ForumPostService;
use App\Services\ForumThreadService;
use App\Services\QuestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_report_forum_content_and_duplicates_are_not_created(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create();
        $reporter = User::factory()->create();
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema reportable', 'Contenido.');

        $payload = [
            'target' => 'thread',
            'target_id' => $thread->id,
            'reason' => 'spam',
            'details' => 'Parece publicidad no solicitada.',
        ];

        $this->actingAs($reporter)->from(route('forum.threads.show', $thread))->post(route('community.reports.store'), $payload)
            ->assertRedirect(route('forum.threads.show', $thread));

        $this->assertDatabaseHas('community_reports', [
            'reporter_id' => $reporter->id,
            'reportable_type' => ForumThread::class,
            'reportable_id' => $thread->id,
            'reason' => 'spam',
            'status' => 'pending',
        ]);

        $this->actingAs($reporter)->from(route('forum.threads.show', $thread))->post(route('community.reports.store'), $payload)
            ->assertRedirect(route('forum.threads.show', $thread));

        $this->assertDatabaseCount('community_reports', 1);
    }

    public function test_questions_and_answers_use_existing_forum_models_as_report_targets(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create();
        $answerer = User::factory()->create();
        $reporter = User::factory()->create();
        $question = app(QuestionService::class)->create($forum, $author, 'Pregunta reportable', 'Contexto.', []);
        $answer = app(ForumPostService::class)->reply($question, $answerer, 'Respuesta reportable.');

        $this->actingAs($reporter)->post(route('community.reports.store'), [
            'target' => 'thread',
            'target_id' => $question->id,
            'reason' => 'unmarked_spoiler',
        ])->assertRedirect();

        $this->actingAs($reporter)->post(route('community.reports.store'), [
            'target' => 'post',
            'target_id' => $answer->id,
            'reason' => 'harassment',
        ])->assertRedirect();

        $this->assertDatabaseHas('community_reports', ['reportable_type' => ForumThread::class, 'reportable_id' => $question->id]);
        $this->assertDatabaseHas('community_reports', ['reportable_type' => ForumPost::class, 'reportable_id' => $answer->id]);
    }

    public function test_member_can_report_another_visible_profile_but_not_their_own(): void
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create(['profile_visibility' => 'public']);

        $this->actingAs($reporter)->post(route('community.reports.store'), [
            'target' => 'user',
            'target_id' => $target->id,
            'reason' => 'personal_information',
        ])->assertRedirect();

        $this->assertDatabaseHas('community_reports', ['reportable_type' => User::class, 'reportable_id' => $target->id]);

        $this->actingAs($reporter)->from('/')->post(route('community.reports.store'), [
            'target' => 'user',
            'target_id' => $reporter->id,
            'reason' => 'other',
        ])->assertSessionHasErrors('target');
    }

    public function test_only_moderators_and_administrators_can_access_report_queue(): void
    {
        $member = User::factory()->create(['role' => 'user']);
        $moderator = User::factory()->create(['role' => 'moderator']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($member)->get(route('admin.community-reports.index'))->assertRedirect('/');
        $this->actingAs($moderator)->get(route('admin.community-reports.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.community-reports.index'))->assertOk();
    }

    public function test_moderator_can_hide_restore_lock_and_resolve_without_deleting_content(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['profile_visibility' => 'members']);
        $reporter = User::factory()->create();
        $moderator = User::factory()->create(['role' => 'moderator']);
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema moderable', 'Contenido inicial.');
        $post = app(ForumPostService::class)->reply($thread, $author, 'Respuesta moderable.');
        $threadReport = $thread->reports()->create(['reporter_id' => $reporter->id, 'reason' => 'inappropriate_content']);
        $postReport = $post->reports()->create(['reporter_id' => $reporter->id, 'reason' => 'spam']);
        $profileReport = $author->reports()->create(['reporter_id' => $reporter->id, 'reason' => 'harassment']);

        $this->actingAs($moderator)->post(route('admin.community-reports.action', $threadReport), ['action' => 'hide'])->assertRedirect();
        $this->assertTrue($thread->fresh()->is_hidden);
        $this->assertNotSoftDeleted('forum_threads', ['id' => $thread->id]);
        $this->assertDatabaseHas('community_moderation_logs', ['community_report_id' => $threadReport->id, 'action' => 'thread_hidden']);

        $this->actingAs($moderator)->post(route('admin.community-reports.action', $threadReport), ['action' => 'restore'])->assertRedirect();
        $this->assertFalse($thread->fresh()->is_hidden);

        $this->actingAs($moderator)->post(route('admin.community-reports.action', $postReport), ['action' => 'hide'])->assertRedirect();
        $this->assertTrue($post->fresh()->is_hidden);
        $this->assertNotSoftDeleted('forum_posts', ['id' => $post->id]);

        $this->actingAs($moderator)->post(route('admin.community-reports.action', $postReport), ['action' => 'restore'])->assertRedirect();
        $this->assertFalse($post->fresh()->is_hidden);

        $this->actingAs($moderator)->post(route('admin.community-reports.action', $threadReport), ['action' => 'lock_thread'])->assertRedirect();
        $this->assertTrue($thread->fresh()->is_locked);

        $this->actingAs($moderator)->post(route('admin.community-reports.action', $profileReport), ['action' => 'hide'])->assertRedirect();
        $this->assertSame('private', $author->fresh()->profile_visibility);
        $this->actingAs($moderator)->post(route('admin.community-reports.action', $profileReport), ['action' => 'restore'])->assertRedirect();
        $this->assertSame('members', $author->fresh()->profile_visibility);

        $this->actingAs($moderator)->patch(route('admin.community-reports.update', $threadReport), [
            'status' => 'resolved',
            'resolution' => 'Contenido revisado y tema cerrado.',
        ])->assertRedirect();

        $this->assertDatabaseHas('community_reports', [
            'id' => $threadReport->id,
            'status' => 'resolved',
            'reviewed_by' => $moderator->id,
            'resolution' => 'Contenido revisado y tema cerrado.',
        ]);
    }

    public function test_regular_members_cannot_moderate_reports(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create();
        $reporter = User::factory()->create();
        $member = User::factory()->create(['role' => 'user']);
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Tema', 'Contenido.');
        $report = $thread->reports()->create(['reporter_id' => $reporter->id, 'reason' => 'spam']);

        $this->actingAs($member)->patch(route('admin.community-reports.update', $report), ['status' => 'dismissed'])->assertRedirect('/');
        $this->actingAs($member)->post(route('admin.community-reports.action', $report), ['action' => 'hide'])->assertRedirect('/');
        $this->assertFalse($thread->fresh()->is_hidden);
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
