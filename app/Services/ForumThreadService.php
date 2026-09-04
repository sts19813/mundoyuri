<?php

namespace App\Services;

use App\Models\Forum;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ForumThreadService
{
    public function __construct(
        private readonly ForumCounterService $counters,
        private readonly MentionService $mentions,
    ) {}

    public function create(Forum $forum, User $author, string $title, string $body, string $type = 'discussion'): ForumThread
    {
        return DB::transaction(function () use ($forum, $author, $title, $body, $type): ForumThread {
            $thread = ForumThread::query()->create([
                'forum_id' => $forum->id,
                'user_id' => $author->id,
                'author_name_snapshot' => $author->displayName(),
                'title' => $title,
                'slug' => $this->uniqueSlug($forum, $title),
                'type' => $type,
                'last_post_at' => now(),
            ]);

            $post = ForumPost::query()->create([
                'forum_thread_id' => $thread->id,
                'user_id' => $author->id,
                'author_name_snapshot' => $author->displayName(),
                'body' => $body,
                'is_initial' => true,
            ]);

            $thread->subscribers()->syncWithoutDetaching([$author->id]);
            $post->load('author');
            $this->mentions->record($post);
            $this->counters->synchronizeThread($thread);
            $this->counters->synchronizeUser($author);

            return $thread;
        });
    }

    private function uniqueSlug(Forum $forum, string $title): string
    {
        $base = Str::limit(Str::slug($title) ?: 'tema', 160, '');
        $slug = $base;
        $number = 2;

        while (ForumThread::query()->where('slug', $slug)->exists()) {
            $slug = Str::limit($base, 180 - strlen((string) $number), '').'-'.$number;
            $number++;
        }

        return $slug;
    }
}
