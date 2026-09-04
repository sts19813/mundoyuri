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
}
