<?php

namespace App\Services;

use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use App\Notifications\ForumReplyNotification;
use Illuminate\Support\Facades\DB;

class ForumPostService
{
    public function __construct(
        private readonly ForumCounterService $counters,
        private readonly ForumMentionService $mentions,
        private readonly QuestionService $questions,
    ) {}

    public function reply(ForumThread $thread, User $author, string $body): ForumPost
    {
        return DB::transaction(function () use ($thread, $author, $body): ForumPost {
            $post = ForumPost::query()->create([
                'forum_thread_id' => $thread->id,
                'user_id' => $author->id,
                'author_name_snapshot' => $author->displayName(),
                'body' => $body,
            ]);

            $post->load(['author', 'thread']);
            $mentionedUserIds = $this->mentions->record($post);
            $this->notifySubscribers($thread, $post, $author, $mentionedUserIds);
            $this->counters->synchronizeThread($thread);
            $this->counters->synchronizeUser($author);

            return $post;
        });
    }

    public function update(ForumPost $post, string $body): void
    {
        DB::transaction(function () use ($post, $body): void {
            $post->update(['body' => $body, 'edited_at' => now()]);
            $post->load('author');
            $this->mentions->record($post);
        });
    }

    public function hide(ForumPost $post): void
    {
        DB::transaction(function () use ($post): void {
            if ($post->is_hidden) {
                return;
            }

            $this->questions->removeAcceptanceFor($post);
            $post->update(['is_hidden' => true]);
            $this->counters->synchronizeThread($post->thread);
            $this->counters->synchronizeUser($post->author);
        });
    }

    public function delete(ForumPost $post): void
    {
        DB::transaction(function () use ($post): void {
            $thread = $post->thread;
            $author = $post->author;

            if ($post->is_initial) {
                $acceptedAnswer = $thread->acceptedAnswer;
                if ($acceptedAnswer) {
                    $this->questions->removeAcceptanceFor($acceptedAnswer);
                }
                $authors = $thread->visiblePosts()->with('author')->get()->pluck('author')->filter()->unique('id');
                $thread->posts()->delete();
                $thread->delete();
                foreach ($authors as $affectedUser) {
                    $this->counters->synchronizeUser($affectedUser);
                }

                return;
            }

            $this->questions->removeAcceptanceFor($post);
            $post->delete();
            $this->counters->synchronizeThread($thread);
            if ($author) {
                $this->counters->synchronizeUser($author);
            }
        });
    }

    /** @param array<int, int> $mentionedUserIds */
    private function notifySubscribers(ForumThread $thread, ForumPost $post, User $author, array $mentionedUserIds): void
    {
        $thread->subscribers()
            ->where('users.id', '!=', $author->id)
            ->get()
            ->each(function (User $subscriber) use ($post, $author, $mentionedUserIds): void {
                if (in_array($subscriber->id, $mentionedUserIds, true) || $author->cannotInteractWith($subscriber)) {
                    return;
                }

                $subscriber->notify(new ForumReplyNotification($post, $author));
            });
    }
}
