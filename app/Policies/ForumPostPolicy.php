<?php

namespace App\Policies;

use App\Models\ForumPost;
use App\Models\User;

class ForumPostPolicy
{
    public function update(User $user, ForumPost $post): bool
    {
        return ! $post->is_hidden && ($user->is($post->author) || $user->shouldEnterAdminPanel());
    }

    public function delete(User $user, ForumPost $post): bool
    {
        return $user->is($post->author) || $user->shouldEnterAdminPanel();
    }

    public function moderate(User $user): bool
    {
        return $user->shouldEnterAdminPanel();
    }

    public function vote(User $user, ForumPost $post): bool
    {
        return ! $post->is_initial
            && ! $post->is_hidden
            && ! $post->trashed()
            && $post->author !== null
            && $post->thread->isQuestion()
            && ! $user->is($post->author);
    }

    public function react(User $user, ForumPost $post): bool
    {
        $thread = $post->thread;

        if ($thread->isQuestion()) {
            return ! $post->is_hidden
                && ! $post->trashed()
                && ! $thread->is_hidden
                && ! $thread->trashed();
        }

        return ! $post->is_hidden
            && ! $post->trashed()
            && ! $thread->is_hidden
            && ! $thread->trashed()
            && $thread->forum?->is_active
            && $thread->forum?->category?->is_active;
    }
}
