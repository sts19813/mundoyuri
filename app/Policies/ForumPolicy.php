<?php

namespace App\Policies;

use App\Models\Forum;
use App\Models\User;

class ForumPolicy
{
    public function view(?User $user, Forum $forum): bool
    {
        return $forum->category->is_active || $user?->isAdmin() === true;
    }

    public function createTopic(User $user, Forum $forum): bool
    {
        return ! $forum->is_locked
            && $forum->category->is_active
            && $forum->acceptsRole($user);
    }

    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
