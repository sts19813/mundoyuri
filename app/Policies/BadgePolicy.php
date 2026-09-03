<?php

namespace App\Policies;

use App\Models\Badge;
use App\Models\User;

class BadgePolicy
{
    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }

    public function assign(User $user, Badge $badge): bool
    {
        return $user->isAdmin() && $badge->is_active;
    }

    public function revoke(User $user, Badge $badge): bool
    {
        return $user->isAdmin();
    }
}
