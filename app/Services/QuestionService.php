<?php

namespace App\Services;

use App\Models\Forum;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\QuestionTag;
use App\Models\User;
use App\Notifications\QuestionAnswerAcceptedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QuestionService
{
    public function __construct(private readonly ForumThreadService $threads) {}

    /** @param array<int, string> $tags */
    public function create(Forum $forum, User $author, string $title, string $body, array $tags): ForumThread
    {
        return DB::transaction(function () use ($forum, $author, $title, $body, $tags): ForumThread {
            $question = $this->threads->create($forum, $author, $title, $body, 'question');
            $question->questionTags()->sync($this->resolveTags($tags)->pluck('id'));

            return $question->load('questionTags');
        });
    }

    public function acceptAnswer(ForumThread $question, ForumPost $answer, User $actor): void
    {
        DB::transaction(function () use ($question, $answer, $actor): void {
            $question = ForumThread::query()->lockForUpdate()->findOrFail($question->id);
            $answer = ForumPost::query()->with('author')->lockForUpdate()->findOrFail($answer->id);

            if (! $question->isQuestion() || $answer->forum_thread_id !== $question->id || $answer->is_initial || $answer->is_hidden || $answer->trashed()) {
                throw ValidationException::withMessages(['answer' => 'La respuesta elegida no puede aceptarse.']);
            }

            if ($question->accepted_answer_post_id === $answer->id) {
                return;
            }

            $previous = $question->accepted_answer_post_id
                ? ForumPost::query()->with('author')->find($question->accepted_answer_post_id)
                : null;

            if ($previous?->author) {
                $previous->author->decrement('community_reputation', 5);
            }

            $question->update([
                'accepted_answer_post_id' => $answer->id,
                'accepted_answer_at' => now(),
            ]);

            if ($answer->author) {
                $answer->author->increment('community_reputation', 5);
                if (! $answer->author->is($actor)) {
                    $answer->author->notify(new QuestionAnswerAcceptedNotification($question, $answer, $actor));
                }
            }
        });
    }

    public function removeAcceptanceFor(ForumPost $answer): void
    {
        $question = ForumThread::query()
            ->where('accepted_answer_post_id', $answer->id)
            ->lockForUpdate()
            ->first();

        if (! $question) {
            return;
        }

        $answer->loadMissing('author');
        $answer->author?->decrement('community_reputation', 5);
        $question->update(['accepted_answer_post_id' => null, 'accepted_answer_at' => null]);
    }

    /** @param array<int, string> $tags */
    private function resolveTags(array $tags): Collection
    {
        return collect($tags)
            ->map(fn (string $tag): string => trim(preg_replace('/\s+/', ' ', $tag) ?? ''))
            ->filter()
            ->unique(fn (string $tag): string => Str::lower($tag))
            ->map(function (string $tag): QuestionTag {
                $slug = Str::slug($tag);

                return QuestionTag::query()->firstOrCreate(['slug' => $slug], ['name' => $tag]);
            })
            ->values();
    }
}
