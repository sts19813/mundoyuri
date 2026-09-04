<?php

namespace App\Policies;

use App\Models\ForumCategory;
use App\Models\User;

class ForumCategoryPolicy
{
    public function view(?User $user, ForumCategory $category): bool
    {
        return $category->is_active || $user?->isAdmin() === true;
    }

    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
