<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberDirectoryRequest;
use App\Models\CommunityRank;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\LegacyProfile;
use App\Models\User;
use App\Services\CommunityRankResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(Request $request, CommunityRankResolver $rankResolver): View
    {
        $modernMembers = User::query()
            ->visibleInCommunityDirectory()
            ->with([
                'communityRank',
                'badges' => fn ($query) => $query->active()->ordered(),
            ])
            ->latest()
            ->limit(4)
            ->get();

        $historicalMembers = LegacyProfile::query()
            ->published()
            ->whereNull('claimed_by_user_id')
            ->with(['badges' => fn ($query) => $query->active()->ordered()])
            ->orderByDesc('legacy_joined_at')
            ->limit(2)
            ->get();

        return view('community.home', [
            'featuredMembers' => $this->mixMembers($modernMembers, $historicalMembers),
            'rankResolver' => $rankResolver,
            'recentThreads' => ForumThread::query()
                ->where('type', 'discussion')
                ->where('is_hidden', false)
                ->whereHas('forum', fn (Builder $forum) => $forum->active()->whereHas('category', fn (Builder $category) => $category->active()))
                ->with('forum')
                ->orderByDesc('last_post_at')
                ->limit(3)
                ->get(),
            'recentActivity' => ForumPost::query()
                ->where('is_hidden', false)
                ->whereHas('thread', fn (Builder $query) => $this->constrainPublicThreads($query))
                ->whereHas('author', function (Builder $query) use ($request): void {
                    $query->visibleInCommunityDirectory()
                        ->where('show_activity', true)
                        ->when($request->user(), function (Builder $query, User $viewer): void {
                            $query
                                ->whereDoesntHave('blockedUsers', fn (Builder $blocked) => $blocked->whereKey($viewer->id))
                                ->whereDoesntHave('blockedByUsers', fn (Builder $blockedBy) => $blockedBy->whereKey($viewer->id));
                        });
                })
                ->with(['thread.forum', 'author'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    /** @param Collection<int, User> $modernMembers @param Collection<int, LegacyProfile> $historicalMembers @return Collection<int, User|LegacyProfile> */
    private function mixMembers(Collection $modernMembers, Collection $historicalMembers): Collection
    {
        $members = collect();
        $length = max($modernMembers->count(), $historicalMembers->count());

        for ($index = 0; $index < $length; $index++) {
            if ($modernMembers->has($index)) {
                $members->push($modernMembers->get($index));
            }

            if ($historicalMembers->has($index)) {
                $members->push($historicalMembers->get($index));
            }
        }

        return $members->take(6);
    }

    public function members(MemberDirectoryRequest $request, CommunityRankResolver $rankResolver): View
    {
        $filters = $request->validated();
        $modernMembers = User::query()
            ->visibleInCommunityDirectory()
            ->with([
                'communityRank',
                'badges' => fn ($query) => $query->active()->ordered(),
            ]);

        if ($search = trim((string) ($filters['q'] ?? ''))) {
            $modernMembers->where(function (Builder $query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('alias', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($rankId = $filters['rank'] ?? null) {
            $this->applyRankFilter($modernMembers, CommunityRank::query()->findOrFail($rankId));
        }

        $filter = $filters['filter'] ?? null;

        if ($filter === 'new') {
            $modernMembers->where('created_at', '>=', now()->subDays(30));
        } elseif ($filter === 'legacy') {
            $modernMembers->where('is_legacy', true);
        }

        $sort = $filters['sort'] ?? match ($filter) {
            'oldest' => 'joined',
            'active' => 'activity',
            default => 'joined',
        };
        $direction = $filters['direction'] ?? ($filter === 'oldest' ? 'asc' : 'desc');

        $includeArchivedProfiles = $rankId === null && ! in_array($filter, ['new', 'active'], true);
        $legacyProfiles = collect();

        if ($includeArchivedProfiles) {
            $legacyProfiles = LegacyProfile::query()
                ->published()
                ->whereNull('claimed_by_user_id')
                ->with(['badges' => fn ($query) => $query->active()->ordered()])
                ->when($search, function (Builder $query) use ($search): void {
                    $query->where(function (Builder $query) use ($search): void {
                        $query
                            ->where('nickname', 'like', "%{$search}%")
                            ->orWhere('legacy_location', 'like', "%{$search}%");
                    });
                })
                ->get();
        }

        $members = $this->paginateMembers(
            $modernMembers->get()->concat($legacyProfiles),
            $sort,
            $direction,
            $request,
        );

        return view('community.index', [
            'members' => $members,
            'ranks' => CommunityRank::query()->active()->orderBy('priority')->orderBy('name')->get(),
            'rankResolver' => $rankResolver,
            'filters' => $filters,
        ]);
    }

    /** @param Collection<int, User|LegacyProfile> $members */
    private function paginateMembers(Collection $members, string $sort, string $direction, Request $request): LengthAwarePaginator
    {
        $members = $members
            ->sortBy(function (User|LegacyProfile $member) use ($sort): array|string|int {
                $isLegacyProfile = $member instanceof LegacyProfile;

                return match ($sort) {
                    'name' => mb_strtolower($isLegacyProfile ? $member->nickname : $member->displayName()),
                    'messages' => $isLegacyProfile ? ($member->legacy_message_count ?? 0) : $member->community_message_count,
                    'activity' => $isLegacyProfile ? 0 : $member->community_message_count,
                    default => ($isLegacyProfile ? $member->legacy_joined_at : $member->communityJoinDate())?->getTimestamp() ?? 0,
                };
            })
            ->values();

        if ($direction === 'desc') {
            $members = $members->reverse()->values();
        }

        $perPage = 24;
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $members->forPage($page, $perPage)->values(),
            $members->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );
    }

    private function constrainPublicThreads(Builder $query): Builder
    {
        return $query
            ->where('is_hidden', false)
            ->where(function (Builder $threads): void {
                $threads->questions()
                    ->orWhereHas('forum', fn (Builder $forum) => $forum->active()->whereHas('category', fn (Builder $category) => $category->active()));
            });
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
}
