<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberDirectoryRequest;
use App\Models\CommunityRank;
use App\Models\User;
use App\Services\CommunityRankResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(MemberDirectoryRequest $request, CommunityRankResolver $rankResolver): View
    {
        $filters = $request->validated();
        $query = User::query()
            ->visibleInCommunityDirectory()
            ->with([
                'communityRank',
                'badges' => fn ($query) => $query->active()->ordered(),
            ]);

        if ($search = trim((string) ($filters['q'] ?? ''))) {
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('alias', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($rankId = $filters['rank'] ?? null) {
            $this->applyRankFilter($query, CommunityRank::query()->findOrFail($rankId));
        }

        $filter = $filters['filter'] ?? null;

        if ($filter === 'new') {
            $query->where('created_at', '>=', now()->subDays(30));
        } elseif ($filter === 'legacy') {
            $query->where('is_legacy', true);
        }

        $sort = $filters['sort'] ?? match ($filter) {
            'oldest' => 'joined',
            'active' => 'activity',
            default => 'joined',
        };
        $direction = $filters['direction'] ?? ($filter === 'oldest' ? 'asc' : 'desc');

        $this->applySort($query, $sort, $direction);

        return view('community.index', [
            'members' => $query->paginate(24)->withQueryString(),
            'ranks' => CommunityRank::query()->active()->orderBy('priority')->orderBy('name')->get(),
            'rankResolver' => $rankResolver,
            'filters' => $filters,
        ]);
    }

    private function applyRankFilter(Builder $query, CommunityRank $rank): void
    {
        if ($rank->is_special || $rank->minimum_posts === null) {
            $query->where('community_rank_id', $rank->id);

            return;
        }

        $nextMinimum = CommunityRank::query()
            ->active()
            ->automatic()
            ->where('minimum_posts', '>', $rank->minimum_posts)
            ->min('minimum_posts');

        $query->where(function (Builder $query) use ($rank, $nextMinimum): void {
            $query->where('community_rank_id', $rank->id)
                ->orWhere(function (Builder $query) use ($rank, $nextMinimum): void {
                    $query
                        ->whereNull('community_rank_id')
                        ->where('community_message_count', '>=', $rank->minimum_posts)
                        ->when($nextMinimum !== null, fn (Builder $query) => $query->where('community_message_count', '<', $nextMinimum));
                });
        });
    }

    private function applySort(Builder $query, string $sort, string $direction): void
    {
        match ($sort) {
            'name' => $query->orderByRaw("LOWER(COALESCE(alias, name)) {$direction}"),
            'messages' => $query->orderBy('community_message_count', $direction),
            'activity' => $query
                ->orderBy('community_message_count', $direction)
                ->orderBy('updated_at', $direction),
            default => $query->orderByRaw(
                "CASE WHEN is_legacy = 1 AND legacy_joined_at IS NOT NULL THEN legacy_joined_at ELSE created_at END {$direction}"
            ),
        };

        $query->orderBy('id');
    }
}
