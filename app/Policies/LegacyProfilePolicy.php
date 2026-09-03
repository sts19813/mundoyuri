<?php

namespace App\Policies;

use App\Models\LegacyProfile;
use App\Models\User;

class LegacyProfilePolicy
{
    public function manage(User $user, ?LegacyProfile $legacyProfile = null): bool
    {
        return $user->isAdmin();
    }
}
