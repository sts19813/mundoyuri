<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Series;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PublicCatalogController extends Controller
{
    public function home(): View
    {
        if (!$this->catalogTablesReady()) {
            return view('index', [
                'featuredSeries' => collect(),
                'latestEpisodes' => collect(),
                'seriesCount' => 0,
            ]);
        }

        $featuredSeries = Series::query()
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->withSum([
                'episodes as total_episode_views' => fn ($query) => $query
                    ->where('moderation_status', 'approved')
                    ->whereNotNull('published_at'),
            ], 'views_count')
            ->orderByDesc('total_episode_views')
            ->orderByDesc('published_at')
            ->take(12)
            ->get();

        $latestEpisodes = Episode::query()
            ->with('series')
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get()
            ->unique('series_id')
            ->take(12)
            ->values();

        $seriesCount = Series::query()
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->count();

        return view('index', compact(
            'featuredSeries',
            'latestEpisodes',
            'seriesCount'
        ));
    }

    public function episodes(?Episode $episode = null): View
    {
        if (!$this->catalogTablesReady()) {
            return view('episodios', [
                'episode' => null,
                'series' => null,
                'seriesEpisodes' => collect(),
                'recentEpisodes' => collect(),
                'previousEpisode' => null,
                'nextEpisode' => null,
            ]);
        }

        if ($episode !== null) {
            $this->ensureApprovedEpisode($episode);
            $episode->loadMissing('series');
        } else {
            $episode = Episode::query()
                ->with('series')
                ->where('moderation_status', 'approved')
                ->whereNotNull('published_at')
                ->latest('published_at')
                ->first();
        }

        if (!$episode) {
            return view('episodios', [
                'episode' => null,
                'series' => null,
                'seriesEpisodes' => collect(),
                'recentEpisodes' => collect(),
                'previousEpisode' => null,
                'nextEpisode' => null,
            ]);
        }

        $episode->recordView();

        $episode->load([
            'sources',
            'series',
            'comments' => fn ($query) => $query
                ->where('is_approved', true)
                ->whereNull('parent_id')
                ->latest()
                ->with([
                    'user',
                    'replies' => fn ($replyQuery) => $replyQuery
                        ->where('is_approved', true)
                        ->oldest()
                        ->with('user'),
                ]),
        ]);

        $series = $episode->series;

        $seriesEpisodes = Episode::query()
            ->where('series_id', $series->id)
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->orderBy('season_number')
            ->orderBy('episode_number')
            ->get();

        [$previousEpisode, $nextEpisode] = $this->resolvePrevAndNext($seriesEpisodes, $episode->id);

        $recentEpisodes = Episode::query()
            ->with('series')
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->take(8)
            ->get();

        return view('episodios', compact(
            'episode',
            'series',
            'seriesEpisodes',
            'recentEpisodes',
            'previousEpisode',
            'nextEpisode'
        ));
    }

    private function resolvePrevAndNext(Collection $episodes, int $currentEpisodeId): array
    {
        $index = $episodes->search(fn (Episode $item) => $item->id === $currentEpisodeId);

        if ($index === false) {
            return [null, null];
        }

        return [
            $episodes->get($index - 1),
            $episodes->get($index + 1),
        ];
    }

    private function ensureApprovedEpisode(Episode $episode): void
    {
        abort_unless($episode->moderation_status === 'approved', 404);
        abort_unless(!is_null($episode->published_at), 404);
    }

    private function catalogTablesReady(): bool
    {
        return Schema::hasTable('genres')
            && Schema::hasTable('series')
            && Schema::hasTable('episodes')
            && Schema::hasTable('comments')
            && Schema::hasTable('episode_sources');
    }
}
