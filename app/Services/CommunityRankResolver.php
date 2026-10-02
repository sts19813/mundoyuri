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

        if ($assignedRank?->is_active && $assignedRank->is_special) {
            return $assignedRank;
        }

        $postRank = $this->automaticRanks()
            ->first(fn (CommunityRank $rank): bool => $user->community_message_count >= $rank->minimum_posts);

        return $this->highestRank($postRank, $this->tenureRank($user));
    }

    /** @return Collection<int, CommunityRank> */
    public function automaticRanks(): Collection
    {
        return $this->automaticRanks ??= CommunityRank::query()
            ->active()
            ->automatic()
            ->orderByDesc('minimum_posts')
            ->orderByDesc('priority')
            ->get();
    }

    private function tenureRank(User $user): ?CommunityRank
    {
        $joinedAt = $user->communityJoinDate();

        if (! $joinedAt) {
            return null;
        }

        $slug = match (true) {
            $joinedAt->lte(now()->subYears(5)) => 'onee-sama',
            $joinedAt->lte(now()->subYear()) => 'yuri-senpai',
            $joinedAt->lte(now()->subMonths(6)) => 'yuri-fan',
            $joinedAt->lte(now()->subMonth()) => 'kohai',
            default => 'nuevo-miembro',
        };

        return $this->automaticRanks()->firstWhere('slug', $slug);
    }

    private function highestRank(?CommunityRank ...$ranks): ?CommunityRank
    {
        $highest = null;

        foreach (array_filter($ranks) as $rank) {
            if (
                ! $highest
                || $rank->minimum_posts > $highest->minimum_posts
                || ($rank->minimum_posts === $highest->minimum_posts && $rank->priority > $highest->priority)
            ) {
                $highest = $rank;
            }
        }

        return $highest;
    }
}
