<?php

namespace App\Policies;

use App\Models\LegacyProfileClaim;
use App\Models\User;

class LegacyProfileClaimPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function review(User $user, LegacyProfileClaim $claim): bool
    {
        return $user->isAdmin();
    }
}
