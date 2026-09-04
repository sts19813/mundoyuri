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
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(Request $request, CommunityRankResolver $rankResolver): View
    {
        $memberRelations = [
            'communityRank',
            'badges' => fn ($query) => $query->active()->ordered(),
        ];

        $members = User::query()->visibleInCommunityDirectory();
        $threads = $this->publicThreads();

        return view('community.home', [
            'activeMembers' => (clone $members)
                ->with($memberRelations)
                ->orderByDesc('last_login_at')
                ->orderByDesc('community_message_count')
                ->limit(6)
                ->get(),
            'newMembers' => (clone $members)
                ->with($memberRelations)
                ->latest()
                ->limit(6)
                ->get(),
            'legacyMembers' => LegacyProfile::query()
                ->published()
                ->orderBy('legacy_joined_at')
                ->orderBy('nickname')
                ->limit(4)
                ->get(),
            'recentThreads' => (clone $threads)
                ->where('type', 'discussion')
                ->with(['forum.category', 'author'])
                ->orderByDesc('last_post_at')
                ->limit(5)
                ->get(),
            'popularThreads' => (clone $threads)
                ->where('type', 'discussion')
                ->with(['forum.category', 'author'])
                ->orderByDesc('replies_count')
                ->orderByDesc('views_count')
                ->orderByDesc('last_post_at')
                ->limit(5)
                ->get(),
            'recentQuestions' => (clone $threads)
                ->questions()
                ->with(['author'])
                ->latest()
                ->limit(5)
                ->get(),
            'unresolvedQuestions' => (clone $threads)
                ->questions()
                ->whereNull('accepted_answer_post_id')
                ->with(['author'])
                ->orderByDesc('last_post_at')
                ->limit(5)
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
                ->limit(7)
                ->get(),
            'stats' => $this->statistics(),
            'rankResolver' => $rankResolver,
        ]);
    }

    public function members(MemberDirectoryRequest $request, CommunityRankResolver $rankResolver): View
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

    private function publicThreads(): Builder
    {
        return ForumThread::query()
            ->where('is_hidden', false)
            ->where(function (Builder $query): void {
                $query->questions()
                    ->orWhereHas('forum', fn (Builder $forum) => $forum->active()->whereHas('category', fn (Builder $category) => $category->active()));
            });
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

    /** @return array{members: int, threads: int, messages: int, questions: int, answers: int} */
    private function statistics(): array
    {
        return Cache::remember('community.home.statistics.v2', now()->addMinutes(10), function (): array {
            $threads = $this->publicThreads();
            $posts = ForumPost::query()
                ->where('is_hidden', false)
                ->whereHas('thread', fn (Builder $query) => $this->constrainPublicThreads($query));

            return [
                'members' => User::query()->visibleInCommunityDirectory()->count(),
                'threads' => (clone $threads)->where('type', 'discussion')->count(),
                'messages' => (clone $posts)->count(),
                'questions' => (clone $threads)->questions()->count(),
                'answers' => (clone $posts)
                    ->where('is_initial', false)
                    ->whereHas('thread', fn (Builder $query) => $query->where('type', 'question'))
                    ->count(),
            ];
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
