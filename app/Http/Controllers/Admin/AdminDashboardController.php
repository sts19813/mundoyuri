<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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

        return view('admin.dashboard', compact('stats', 'siteVisitStats', 'siteVisitChart', 'mostViewedEpisodes', 'mostViewedSeries'));
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
}
