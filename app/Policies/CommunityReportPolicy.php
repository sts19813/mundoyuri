<?php

namespace App\Policies;

use App\Models\CommunityReport;
use App\Models\User;

class CommunityReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->shouldEnterAdminPanel();
    }

    public function update(User $user, CommunityReport $report): bool
    {
        return $user->shouldEnterAdminPanel();
    }
}
