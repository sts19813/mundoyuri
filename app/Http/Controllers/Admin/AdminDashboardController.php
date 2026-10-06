<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Episode;
use App\Models\EpisodeWatchProgress;
use App\Models\Genre;
use App\Models\Series;
use App\Models\SiteVisit;
use App\Models\User;
use App\Models\UserPresenceSession;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view dashboard');
    }

    public function index(): View
    {
        $user = auth()->user();
        $canModerate = $user->can('moderate content');
        $siteVisitStats = null;
        $siteVisitChart = null;
        $activePresenceSessions = collect();
        $topTimeUsers = collect();
        $recentUserVisits = collect();
        $currentWatchingEpisodes = collect();
        $recentWatchProgress = collect();

        $stats = [
            'users' => $user->can('manage users') ? User::count() : null,
            'genres' => Genre::count(),
            'series' => $canModerate ? Series::count() : $user->submittedSeries()->count(),
            'episodes' => $canModerate ? Episode::count() : $user->submittedEpisodes()->count(),
            'pending_series' => $canModerate
                ? Series::where('moderation_status', 'pending')->count()
                : $user->submittedSeries()->where('moderation_status', 'pending')->count(),
            'pending_episodes' => $canModerate
                ? Episode::where('moderation_status', 'pending')->count()
                : $user->submittedEpisodes()->where('moderation_status', 'pending')->count(),
            'comments' => $user->can('manage users') ? Comment::count() : $user->comments()->count(),
        ];

        if ($canModerate) {
            $siteVisitStats = $this->siteVisitStats();
            $siteVisitChart = $this->siteVisitChart();
            $activePresenceSessions = $this->activePresenceSessions();
            $topTimeUsers = $this->topTimeUsers();
            $recentUserVisits = $this->recentUserVisits();
            $currentWatchingEpisodes = $this->currentWatchingEpisodes();
            $recentWatchProgress = $this->recentWatchProgress();
        }

        $mostViewedEpisodes = Episode::query()
            ->with('series:id,title')
            ->when(! $canModerate, fn ($query) => $query->where('created_by', $user->id))
            ->orderByDesc('views_count')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        $mostViewedSeries = Series::query()
            ->withSum('episodes as total_views', 'views_count')
            ->when(! $canModerate, fn ($query) => $query->where('created_by', $user->id))
            ->orderByDesc('total_views')
            ->orderBy('title')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'siteVisitStats',
            'siteVisitChart',
            'mostViewedEpisodes',
            'mostViewedSeries',
            'activePresenceSessions',
            'topTimeUsers',
            'recentUserVisits',
            'currentWatchingEpisodes',
            'recentWatchProgress',
        ));
    }

    /** @return array<string, int> */
    private function siteVisitStats(): array
    {
        $today = now()->toDateString();
        $sevenDaysAgo = now()->subDays(6)->toDateString();
        $thirtyDaysAgo = now()->subDays(29)->toDateString();

        $row = SiteVisit::query()
            ->selectRaw('COUNT(*) as total_visits')
            ->selectRaw('SUM(CASE WHEN visited_on = ? THEN 1 ELSE 0 END) as visits_today', [$today])
            ->selectRaw('SUM(CASE WHEN visited_on >= ? THEN 1 ELSE 0 END) as visits_7_days', [$sevenDaysAgo])
            ->selectRaw('SUM(CASE WHEN visited_on >= ? THEN 1 ELSE 0 END) as visits_30_days', [$thirtyDaysAgo])
            ->selectRaw('COUNT(DISTINCT visitor_id) as unique_visitors')
            ->selectRaw('COUNT(DISTINCT CASE WHEN user_id IS NULL THEN visitor_id END) as anonymous_visitors')
            ->selectRaw('COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id END) as registered_visitors')
            ->first();

        return [
            'total_visits' => (int) ($row->total_visits ?? 0),
            'visits_today' => (int) ($row->visits_today ?? 0),
            'visits_7_days' => (int) ($row->visits_7_days ?? 0),
            'visits_30_days' => (int) ($row->visits_30_days ?? 0),
            'unique_visitors' => (int) ($row->unique_visitors ?? 0),
            'anonymous_visitors' => (int) ($row->anonymous_visitors ?? 0),
            'registered_visitors' => (int) ($row->registered_visitors ?? 0),
            'new_users_30_days' => User::query()->where('created_at', '>=', now()->subDays(29)->startOfDay())->count(),
            'active_now' => UserPresenceSession::query()->where('last_seen_at', '>=', now()->subMinutes(2))->distinct('user_id')->count('user_id'),
            'tracked_hours' => (int) floor(UserPresenceSession::query()->sum('total_seconds') / 3600),
        ];
    }

    /** @return array{labels: Collection<int, string>, values: Collection<int, int>} */
    private function siteVisitChart(): array
    {
        $startDate = now()->subDays(29)->startOfDay();
        $visitsByDay = SiteVisit::query()
            ->where('visited_on', '>=', $startDate->toDateString())
            ->selectRaw('visited_on, COUNT(*) as visits')
            ->groupBy('visited_on')
            ->pluck('visits', 'visited_on');

        $labels = collect();
        $values = collect();

        for ($day = $startDate->copy(); $day->lte(now()); $day->addDay()) {
            $date = $day->toDateString();
            $labels->push(Carbon::parse($date)->format('d/m'));
            $values->push((int) ($visitsByDay[$date] ?? 0));
        }

        return compact('labels', 'values');
    }

    private function activePresenceSessions(): Collection
    {
        return UserPresenceSession::query()
            ->with('user:id,name,alias,email')
            ->where('last_seen_at', '>=', now()->subMinutes(2))
            ->latest('last_seen_at')
            ->take(30)
            ->get()
            ->unique('user_id')
            ->take(10)
            ->values();
    }

    private function topTimeUsers(): Collection
    {
        $totals = UserPresenceSession::query()
            ->select('user_id')
            ->selectRaw('SUM(total_seconds) as total_presence_seconds')
            ->selectRaw('MAX(last_seen_at) as last_presence_at')
            ->groupBy('user_id');

        return User::query()
            ->joinSub($totals, 'presence_totals', fn ($join) => $join->on('users.id', '=', 'presence_totals.user_id'))
            ->select('users.id', 'users.name', 'users.alias', 'users.email')
            ->addSelect([
                'total_presence_seconds' => DB::raw('presence_totals.total_presence_seconds'),
                'last_presence_at' => DB::raw('presence_totals.last_presence_at'),
            ])
            ->orderByDesc('presence_totals.total_presence_seconds')
            ->take(10)
            ->get();
    }

    private function recentUserVisits(): Collection
    {
        return SiteVisit::query()
            ->with('user:id,name,alias,email')
            ->whereNotNull('user_id')
            ->latest('visited_at')
            ->take(10)
            ->get();
    }

    private function currentWatchingEpisodes(): Collection
    {
        return EpisodeWatchProgress::query()
            ->with(['user:id,name,alias,email', 'episode.series:id,title'])
            ->where('last_watched_at', '>=', now()->subMinutes(2))
            ->latest('last_watched_at')
            ->take(10)
            ->get();
    }

    private function recentWatchProgress(): Collection
    {
        return EpisodeWatchProgress::query()
            ->with(['user:id,name,alias,email', 'episode.series:id,title'])
            ->latest('last_watched_at')
            ->take(10)
            ->get();
    }
}
