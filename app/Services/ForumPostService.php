<?php

namespace App\Services;

use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use App\Notifications\ForumReplyNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ForumPostService
{
    public function __construct(
        private readonly ForumCounterService $counters,
        private readonly MentionService $mentions,
        private readonly QuestionService $questions,
        private readonly CommunityPostImageService $images,
    ) {}

    public function reply(ForumThread $thread, User $author, string $body, ?int $replyToId = null, ?string $imagePath = null): ForumPost
    {
        return DB::transaction(function () use ($thread, $author, $body, $replyToId, $imagePath): ForumPost {
            $replyTo = $replyToId ? $thread->posts()->whereKey($replyToId)->where('is_hidden', false)->lockForUpdate()->first() : null;
            if ($replyToId && (! $replyTo || ($replyTo->author && $author->cannotInteractWith($replyTo->author)))) {
                throw ValidationException::withMessages(['reply_to_post_id' => 'No puedes responder a ese mensaje.']);
            }
            $post = ForumPost::query()->create([
                'forum_thread_id' => $thread->id,
                'user_id' => $author->id,
                'author_name_snapshot' => $author->displayName(),
                'body' => $body,
                'image_path' => $imagePath,
                'reply_to_post_id' => $replyTo?->id,
            ]);

            $post->load(['author', 'thread']);
            $mentionedUserIds = $this->mentions->record($post);
            $this->notifySubscribers($thread, $post, $author, $mentionedUserIds, $replyTo?->author);
            $this->counters->synchronizeThread($thread);
            $this->counters->synchronizeUser($author);

            return $post;
        });
    }

    public function update(ForumPost $post, string $body, ?string $imagePath = null, bool $replaceImage = false): void
    {
        if (! $replaceImage) {
            $imagePath = $post->image_path;
        }

        DB::transaction(function () use ($post, $body, $imagePath): void {
            $post->update(['body' => $body, 'image_path' => $imagePath, 'edited_at' => now()]);
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
            if ($post->author) {
                $this->counters->synchronizeUser($post->author);
            }
        });
    }

    public function delete(ForumPost $post): void
    {
        $imagePaths = DB::transaction(function () use ($post): array {
            $thread = $post->thread;
            $author = $post->author;

            if ($post->is_initial) {
                $imagePaths = $thread->posts()->withTrashed()->whereNotNull('image_path')->pluck('image_path')->all();
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

                return $imagePaths;
            }

            $imagePaths = array_filter([$post->image_path]);
            $this->questions->removeAcceptanceFor($post);
            $post->delete();
            $this->counters->synchronizeThread($thread);
            if ($author) {
                $this->counters->synchronizeUser($author);
            }

            return $imagePaths;
        });

        foreach ($imagePaths as $imagePath) {
            $this->images->delete($imagePath);
        }
    }

    /** @param array<int, int> $mentionedUserIds */
    private function notifySubscribers(ForumThread $thread, ForumPost $post, User $author, array $mentionedUserIds, ?User $replyRecipient = null): void
    {
        $recipients = $thread->subscribers()
            ->where('users.id', '!=', $author->id)
            ->get();
        if ($replyRecipient) {
            $recipients->push($replyRecipient);
        }
        $recipients->unique('id')->each(function (User $subscriber) use ($post, $author, $mentionedUserIds): void {
            if ($author->is($subscriber) || in_array($subscriber->id, $mentionedUserIds, true) || $author->cannotInteractWith($subscriber)) {
                return;
            }

            $subscriber->notify(new ForumReplyNotification($post, $author));
        });
    }
}
