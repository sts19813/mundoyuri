<?php

namespace App\Services;

use App\Models\CommunityRank;
use App\Models\User;
use Illuminate\Support\Collection;

class CommunityRankResolver
{
    /** @var Collection<int, CommunityRank>|null */
    private ?Collection $automaticRanks = null;

    public function resolve(User $user): ?CommunityRank
    {
        $assignedRank = $user->relationLoaded('communityRank')
            ? $user->communityRank
            : $user->communityRank()->first();

        if ($assignedRank?->is_active) {
            return $assignedRank;
        }

        return $this->automaticRanks()
            ->first(fn (CommunityRank $rank): bool => $user->community_message_count >= $rank->minimum_messages);
    }

    /** @return Collection<int, CommunityRank> */
    public function automaticRanks(): Collection
    {
        return $this->automaticRanks ??= CommunityRank::query()
            ->active()
            ->automatic()
            ->orderByDesc('minimum_messages')
            ->get();
    }
}
