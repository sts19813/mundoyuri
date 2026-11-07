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
use App\Models\VisitorPresenceSession;
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
        $todayTrafficChart = null;
        $topVisitedPages = collect();
        $contentMomentum = null;
        $anonymousPages = collect();
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
            $todayTrafficChart = $this->todayTrafficChart();
            $topVisitedPages = $this->topVisitedPages();
            $contentMomentum = $this->contentMomentum();
            $anonymousPages = $this->anonymousPages();
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
            'todayTrafficChart',
            'topVisitedPages',
            'contentMomentum',
            'anonymousPages',
            'mostViewedEpisodes',
            'mostViewedSeries',
            'activePresenceSessions',
            'topTimeUsers',
            'recentUserVisits',
            'currentWatchingEpisodes',
            'recentWatchProgress',
        ));
    }

    /** @return array<string, int|float> */
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

        $recentVisitors = SiteVisit::query()
            ->where('visited_on', '>=', $thirtyDaysAgo)
            ->where('visited_on', '<', now()->addDay()->toDateString())
            ->selectRaw('COUNT(DISTINCT visitor_id) as unique_visitors')
            ->selectRaw('COUNT(DISTINCT CASE WHEN user_id IS NULL THEN visitor_id END) as anonymous_visitors')
            ->selectRaw('COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id END) as registered_visitors')
            ->first();

        $activeSince = now()->subMinutes(15);
        $activeRegistered = UserPresenceSession::query()
            ->where('last_seen_at', '>=', $activeSince)
            ->distinct('user_id')
            ->count('user_id');
        $activeAnonymous = VisitorPresenceSession::query()
            ->where('last_seen_at', '>=', $activeSince)
            ->distinct('visitor_id')
            ->count('visitor_id');

        $todayVisits = (int) ($row->visits_today ?? 0);
        $yesterdayVisits = SiteVisit::query()
            ->where('visited_on', now()->subDay()->toDateString())
            ->count();

        return [
            'total_visits' => (int) ($row->total_visits ?? 0),
            'visits_today' => (int) ($row->visits_today ?? 0),
            'visits_7_days' => (int) ($row->visits_7_days ?? 0),
            'visits_30_days' => (int) ($row->visits_30_days ?? 0),
            'unique_visitors' => (int) ($row->unique_visitors ?? 0),
            'anonymous_visitors' => (int) ($row->anonymous_visitors ?? 0),
            'registered_visitors' => (int) ($row->registered_visitors ?? 0),
            'new_users_30_days' => User::query()->where('created_at', '>=', now()->subDays(29)->startOfDay())->count(),
            'active_registered' => $activeRegistered,
            'active_anonymous' => $activeAnonymous,
            'active_now' => $activeRegistered + $activeAnonymous,
            'tracked_hours' => (int) floor(UserPresenceSession::query()->sum('total_seconds') / 3600),
            'unique_visitors_30_days' => (int) ($recentVisitors->unique_visitors ?? 0),
            'anonymous_visitors_30_days' => (int) ($recentVisitors->anonymous_visitors ?? 0),
            'registered_visitors_30_days' => (int) ($recentVisitors->registered_visitors ?? 0),
            'yesterday_visits' => $yesterdayVisits,
        ];
    }

    /** @return array{labels: Collection<int, string>, visits: Collection<int, int>, visitors: Collection<int, int>, anonymous: Collection<int, int>} */
    private function siteVisitChart(): array
    {
        $startDate = now()->subDays(29)->startOfDay();
        $visitsByDay = SiteVisit::query()
            ->where('visited_on', '>=', $startDate->toDateString())
            ->selectRaw('visited_on, COUNT(*) as visits, COUNT(DISTINCT visitor_id) as visitors, COUNT(DISTINCT CASE WHEN user_id IS NULL THEN visitor_id END) as anonymous')
            ->groupBy('visited_on')
            ->get()
            ->keyBy(fn (SiteVisit $visit) => $visit->visited_on->toDateString());

        $labels = collect();
        $visits = collect();
        $visitors = collect();
        $anonymous = collect();

        for ($day = $startDate->copy(); $day->lte(now()); $day->addDay()) {
            $date = $day->toDateString();
            $labels->push(Carbon::parse($date)->format('d/m'));
            $row = $visitsByDay[$date] ?? null;
            $visits->push((int) ($row->visits ?? 0));
            $visitors->push((int) ($row->visitors ?? 0));
            $anonymous->push((int) ($row->anonymous ?? 0));
        }

        return [
            'labels' => $labels->all(),
            'visits' => $visits->all(),
            'visitors' => $visitors->all(),
            'anonymous' => $anonymous->all(),
        ];
    }

    /** @return array{labels: Collection<int, string>, values: Collection<int, int>} */
    private function todayTrafficChart(): array
    {
        $start = now()->copy()->startOfDay();
        $rows = SiteVisit::query()
            ->where('visited_at', '>=', $start)
            ->get(['visited_at'])
            ->groupBy(fn (SiteVisit $visit) => $visit->visited_at->format('H'))
            ->map->count();

        $labels = collect();
        $values = collect();
        $lastHour = (int) now()->format('G');

        for ($hour = 0; $hour <= $lastHour; $hour++) {
            $key = str_pad((string) $hour, 2, '0', STR_PAD_LEFT);
            $labels->push($key.':00');
            $values->push((int) ($rows[$key] ?? 0));
        }

        return ['labels' => $labels->all(), 'values' => $values->all()];
    }

    private function anonymousPages(): Collection
    {
        // Count each anonymous browser once, on its most recently active tab.
        $latest = VisitorPresenceSession::query()
            ->where('last_seen_at', '>=', now()->subMinutes(15))
            ->orderByDesc('last_seen_at')
            ->orderByDesc('id')
            ->get(['visitor_id', 'current_path', 'last_seen_at'])
            ->unique('visitor_id');

        return $latest->groupBy('current_path')
            ->map(fn (Collection $sessions, string $path) => (object) ['path' => $path, 'visitors' => $sessions->count()])
            ->sortByDesc('visitors')
            ->take(8)
            ->values();
    }

    private function topVisitedPages(): Collection
    {
        return SiteVisit::query()
            ->where('visited_at', '>=', now()->subDays(30))
            ->select('path')
            ->selectRaw('COUNT(*) as visits')
            ->selectRaw('COUNT(DISTINCT visitor_id) as visitors')
            ->groupBy('path')
            ->orderByDesc('visits')
            ->take(8)
            ->get();
    }

    /** @return array<string, int> */
    private function contentMomentum(): array
    {
        $since = now()->subDays(30);

        return [
            'episode_starts' => EpisodeWatchProgress::query()->where('last_watched_at', '>=', $since)->count(),
            'episode_completions' => EpisodeWatchProgress::query()->where('last_watched_at', '>=', $since)->where('completed', true)->count(),
            'comments' => Comment::query()->where('created_at', '>=', $since)->count(),
            'new_users' => User::query()->where('created_at', '>=', $since)->count(),
        ];
    }

    private function activePresenceSessions(): Collection
    {
        return UserPresenceSession::query()
            ->with('user:id,name,alias,email')
            ->where('last_seen_at', '>=', now()->subMinutes(15))
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
            ->where('last_watched_at', '>=', now()->subMinutes(15))
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
