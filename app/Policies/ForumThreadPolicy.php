<?php

namespace App\Policies;

use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ForumThreadPolicy
{
    public function view(?User $user, ForumThread $thread): Response
    {
        if ($user?->shouldEnterAdminPanel()) {
            return Response::allow();
        }

        return ! $thread->is_hidden && ! $thread->trashed() && $thread->forum->category->is_active
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function reply(User $user, ForumThread $thread): bool
    {
        return ! $thread->is_locked
            && ! $thread->forum->is_locked
            && ! $thread->is_hidden
            && $thread->forum->acceptsRole($user);
    }

    public function update(User $user, ForumThread $thread): bool
    {
        return $user->is($thread->author) || $user->shouldEnterAdminPanel();
    }

    public function delete(User $user, ForumThread $thread): bool
    {
        return $user->is($thread->author) || $user->shouldEnterAdminPanel();
    }

    public function moderate(User $user): bool
    {
        return $user->shouldEnterAdminPanel();
    }
}
