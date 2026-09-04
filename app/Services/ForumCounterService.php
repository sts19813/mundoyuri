<?php

namespace App\Services;

use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ForumCounterService
{
    public function synchronizeThread(ForumThread $thread): void
    {
        $posts = ForumPost::query()
            ->where('forum_thread_id', $thread->id)
            ->where('is_hidden', false);

        $thread->forceFill([
            'replies_count' => (clone $posts)->where('is_initial', false)->count(),
            'last_post_at' => (clone $posts)->max('created_at'),
        ])->save();
    }

    public function synchronizeUser(User $user): void
    {
        DB::table('users')->where('id', $user->id)->lockForUpdate()->first();

        $count = ForumPost::query()
            ->where('user_id', $user->id)
            ->where('is_hidden', false)
            ->whereHas('thread', fn ($query) => $query->where('is_hidden', false))
            ->count();

        $user->forceFill(['community_message_count' => $count])->save();
    }

    public function synchronizeAll(): void
    {
        ForumThread::query()->each(fn (ForumThread $thread) => $this->synchronizeThread($thread));
        User::query()->each(fn (User $user) => $this->synchronizeUser($user));
    }
}
