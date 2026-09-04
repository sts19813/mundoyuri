<?php

namespace App\Services;

use App\Models\CommunityModerationLog;
use App\Models\CommunityReport;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommunityModerationService
{
    public function submit(User $reporter, Model $reportable, string $reason, ?string $details): CommunityReport
    {
        return DB::transaction(function () use ($reporter, $reportable, $reason, $details): CommunityReport {
            $existing = CommunityReport::query()
                ->where('reporter_id', $reporter->id)
                ->where('reportable_type', $reportable->getMorphClass())
                ->where('reportable_id', $reportable->getKey())
                ->whereIn('status', ['pending', 'reviewing'])
                ->first();

            if ($existing) {
                return $existing;
            }

            return $reportable->reports()->create([
                'reporter_id' => $reporter->id,
                'reason' => $reason,
                'details' => $details,
            ]);
        });
    }

    public function review(CommunityReport $report, User $moderator, string $status, ?string $resolution): void
    {
        DB::transaction(function () use ($report, $moderator, $status, $resolution): void {
            $report->update([
                'status' => $status,
                'reviewed_by' => $moderator->id,
                'reviewed_at' => now(),
                'resolution' => $resolution,
            ]);
            $this->log($report, $moderator, 'report_'.$status);
        });
    }

    public function act(CommunityReport $report, User $moderator, string $action): void
    {
        DB::transaction(function () use ($report, $moderator, $action): void {
            $report->loadMissing('reportable');
            $target = $report->reportable;

            if (! $target instanceof Model) {
                throw ValidationException::withMessages(['report' => 'El contenido reportado ya no está disponible.']);
            }

            match ($action) {
                'hide' => $this->hide($report, $target, $moderator),
                'restore' => $this->restore($report, $target, $moderator),
                'lock_thread' => $this->lockThread($report, $target, $moderator),
            };
        });
    }

    private function hide(CommunityReport $report, Model $target, User $moderator): void
    {
        if ($target instanceof ForumThread) {
            $target->update(['is_hidden' => true]);
            $this->synchronizeThreadAndAuthors($target);
            $this->log($report, $moderator, 'thread_hidden');

            return;
        }

        if ($target instanceof ForumPost) {
            app(ForumPostService::class)->hide($target);
            $this->log($report, $moderator, 'post_hidden');

            return;
        }

        if ($target instanceof User) {
            $previousVisibility = $target->profile_visibility;
            $target->update(['profile_visibility' => 'private']);
            $this->log($report, $moderator, 'profile_hidden', ['previous_visibility' => $previousVisibility]);

            return;
        }

        throw ValidationException::withMessages(['report' => 'Este contenido no puede ocultarse.']);
    }

    private function restore(CommunityReport $report, Model $target, User $moderator): void
    {
        if ($target instanceof ForumThread) {
            $target->update(['is_hidden' => false]);
            $this->synchronizeThreadAndAuthors($target);
            $this->log($report, $moderator, 'thread_restored');

            return;
        }

        if ($target instanceof ForumPost) {
            $target->update(['is_hidden' => false]);
            $target->loadMissing('thread', 'author');
            app(ForumCounterService::class)->synchronizeThread($target->thread);
            $target->author && app(ForumCounterService::class)->synchronizeUser($target->author);
            $this->log($report, $moderator, 'post_restored');

            return;
        }

        if ($target instanceof User) {
            $lastHide = CommunityModerationLog::query()
                ->where('moderatable_type', $target->getMorphClass())
                ->where('moderatable_id', $target->id)
                ->where('action', 'profile_hidden')
                ->latest()
                ->first();
            $previousVisibility = ($lastHide?->metadata ?? [])['previous_visibility'] ?? null;
            $target->update(['profile_visibility' => in_array($previousVisibility, ['public', 'members', 'private'], true) ? $previousVisibility : 'public']);
            $this->log($report, $moderator, 'profile_restored');

            return;
        }

        throw ValidationException::withMessages(['report' => 'Este contenido no puede restaurarse.']);
    }

    private function lockThread(CommunityReport $report, Model $target, User $moderator): void
    {
        $thread = match (true) {
            $target instanceof ForumThread => $target,
            $target instanceof ForumPost => $target->thread,
            default => null,
        };

        if (! $thread) {
            throw ValidationException::withMessages(['report' => 'Solo los temas y sus mensajes pueden cerrarse.']);
        }

        $thread->update(['is_locked' => true]);
        $this->log($report, $moderator, 'thread_locked', ['forum_thread_id' => $thread->id]);
    }

    private function synchronizeThreadAndAuthors(ForumThread $thread): void
    {
        $counters = app(ForumCounterService::class);
        $counters->synchronizeThread($thread);
        $thread->posts()->with('author')->get()->pluck('author')->filter()->unique('id')
            ->each(fn (User $author) => $counters->synchronizeUser($author));
    }

    /** @param array<string, mixed>|null $metadata */
    private function log(CommunityReport $report, User $moderator, string $action, ?array $metadata = null): void
    {
        $target = $report->reportable;

        if (! $target instanceof Model) {
            return;
        }

        $target->moderationLogs()->create([
            'actor_id' => $moderator->id,
            'community_report_id' => $report->id,
            'action' => $action,
            'metadata' => $metadata,
        ]);
    }
}
