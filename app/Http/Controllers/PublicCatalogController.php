<?php

namespace App\Http\Controllers;

use App\Models\CatalogSection;
use App\Models\Episode;
use App\Models\EpisodeWatchProgress;
use App\Models\Series;
use App\Services\CommentConversationTree;
use App\Services\CommunityRankResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PublicCatalogController extends Controller
{
    public function home(): View
    {
        if (! $this->catalogTablesReady()) {
            return view('index', $this->emptyMixedHomeData());
        }

        return view('index', $this->mixedHomeData());
    }

    public function section(string $sectionSlug): View
    {
        abort_unless($this->catalogTablesReady(), 404);

        $section = $this->resolveSection($sectionSlug);

        abort_unless($section, 404);

        return view('index', $this->homeData($section));
    }

    /** @return array<string, mixed> */
    private function homeData(CatalogSection $section): array
    {
        $featuredSeries = Series::query()
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->where('catalog_section', $section->slug)
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
            ->whereHas('series', fn ($query) => $query->where('catalog_section', $section->slug))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get()
            ->unique('series_id')
            ->take(12)
            ->values();

        $seriesCount = Series::query()
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->where('catalog_section', $section->slug)
            ->count();
        $continueWatching = $this->continueWatchingEpisodes();

        return compact(
            'section',
            'featuredSeries',
            'latestEpisodes',
            'seriesCount',
            'continueWatching'
        );
    }

    /** @return array<string, mixed> */
    private function mixedHomeData(): array
    {
        $featuredGlSeries = $this->featuredSeriesForSection('series-gl');
        $featuredAnimeSeries = $this->featuredSeriesForSection('anime');
        $glSeries = $this->allTitlesForSection('series-gl');
        $animeSeries = $this->allTitlesForSection('anime');

        return [
            'section' => $this->resolveSection('series-gl') ?? $this->fallbackSection(),
            'isMixedHome' => true,
            'latestEpisodes' => $this->latestEpisodesForMixedHome(),
            'featuredSeries' => $this->interleave($featuredGlSeries, $featuredAnimeSeries),
            'mixedSeries' => $this->interleave($glSeries, $animeSeries),
            'continueWatching' => $this->continueWatchingEpisodes(),
        ];
    }

    private function interleave(Collection $first, Collection $second): Collection
    {
        $items = collect();
        $total = max($first->count(), $second->count());

        for ($index = 0; $index < $total; $index++) {
            if ($first->has($index)) {
                $items->push($first->get($index));
            }

            if ($second->has($index)) {
                $items->push($second->get($index));
            }
        }

        return $items;
    }

    private function latestEpisodesForMixedHome(): Collection
    {
        return Episode::query()
            ->with('series')
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->whereHas('series', fn ($query) => $query
                ->where('moderation_status', 'approved')
                ->whereNotNull('published_at')
                ->whereIn('catalog_section', ['series-gl', 'anime']))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get()
            ->unique('series_id')
            ->take(12)
            ->values();
    }

    private function latestEpisodesForSection(string $sectionSlug): Collection
    {
        return Episode::query()
            ->with('series')
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->whereHas('series', fn ($query) => $query->where('catalog_section', $sectionSlug))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get()
            ->unique('series_id')
            ->take(12)
            ->values();
    }

    private function featuredSeriesForSection(string $sectionSlug): Collection
    {
        return Series::query()
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->where('catalog_section', $sectionSlug)
            ->where('content_type', 'series')
            ->withSum([
                'episodes as total_episode_views' => fn ($query) => $query
                    ->where('moderation_status', 'approved')
                    ->whereNotNull('published_at'),
            ], 'views_count')
            ->orderByDesc('total_episode_views')
            ->orderByDesc('published_at')
            ->take(12)
            ->get();
    }

    private function allTitlesForSection(string $sectionSlug): Collection
    {
        return Series::query()
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->where('catalog_section', $sectionSlug)
            ->orderByDesc('published_at')
            ->get();
    }

    public function episodes(CommunityRankResolver $rankResolver, CommentConversationTree $commentTree, ?Episode $episode = null): View
    {
        if (! $this->catalogTablesReady()) {
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

        if (! $episode) {
            return view('episodios', [
                'episode' => null,
                'series' => null,
                'seriesEpisodes' => collect(),
                'recentEpisodes' => collect(),
                'previousEpisode' => null,
                'nextEpisode' => null,
            ]);
        }

        $episode->recordView(auth()->user());

        $episode->load([
            'sources',
            'series',
        ]);
        $episode->setRelation('comments', $commentTree->build($episode->comments()
            ->where('is_approved', true)
            ->with([
                'user.communityRank',
                'user.badges' => fn ($badgeQuery) => $badgeQuery->active()->ordered(),
            ])
            ->oldest()
            ->get()));

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

        $watchProgressEnabled = $this->watchProgressEnabledForRequest(request()) && auth()->check();
        $episodeProgress = $watchProgressEnabled
            ? EpisodeWatchProgress::query()
                ->where('user_id', auth()->id())
                ->where('episode_id', $episode->id)
                ->first()
            : null;

        return view('episodios', compact(
            'episode',
            'series',
            'seriesEpisodes',
            'recentEpisodes',
            'previousEpisode',
            'nextEpisode',
            'rankResolver',
            'watchProgressEnabled',
            'episodeProgress'
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
        abort_unless(! is_null($episode->published_at), 404);
    }

    private function catalogTablesReady(): bool
    {
        return Schema::hasTable('genres')
            && Schema::hasTable('series')
            && Schema::hasColumn('series', 'catalog_section')
            && Schema::hasTable('catalog_sections')
            && Schema::hasTable('episodes')
            && Schema::hasTable('comments')
            && Schema::hasTable('episode_sources');
    }

    private function continueWatchingEpisodes(): Collection
    {
        if (! $this->watchProgressEnabledForRequest(request()) || ! auth()->check()) {
            return collect();
        }

        if (! Schema::hasTable('episode_watch_progress')) {
            return collect();
        }

        $items = collect();
        $seenEpisodeIds = [];

        EpisodeWatchProgress::query()
            ->with(['episode.series', 'source'])
            ->where('user_id', auth()->id())
            ->whereIn('provider', config('watch_progress.providers', []))
            ->whereHas('episode', fn ($query) => $query
                ->where('moderation_status', 'approved')
                ->whereNotNull('published_at')
                ->whereHas('series', fn ($seriesQuery) => $seriesQuery
                    ->where('moderation_status', 'approved')
                    ->whereNotNull('published_at')))
            ->whereHas('source', fn ($query) => $query->whereIn('provider', config('watch_progress.providers', [])))
            ->latest('last_watched_at')
            ->latest('id')
            ->limit(30)
            ->get()
            ->each(function (EpisodeWatchProgress $progress) use ($items, &$seenEpisodeIds): void {
                if (! $progress->episode || ! $progress->episode->series) {
                    return;
                }

                if ($progress->completed) {
                    $nextItem = $this->nextEpisodeContinueItem($progress);

                    if (! $nextItem || in_array($nextItem->episode->id, $seenEpisodeIds, true)) {
                        return;
                    }

                    $seenEpisodeIds[] = $nextItem->episode->id;
                    $items->push($nextItem);

                    return;
                }

                if ($progress->position_seconds < (int) config('watch_progress.minimum_seconds', 15)) {
                    return;
                }

                if (in_array($progress->episode_id, $seenEpisodeIds, true)) {
                    return;
                }

                $progress->is_next_episode = false;
                $seenEpisodeIds[] = $progress->episode_id;
                $items->push($progress);
            });

        return $items->take((int) config('watch_progress.keep_per_user', 10))->values();
    }

    private function watchProgressEnabledForRequest(Request $request): bool
    {
        $host = $request->getHost();

        if (in_array($host, config('watch_progress.hosts', []), true)) {
            return true;
        }

        return app()->environment('local')
            && in_array($host, config('watch_progress.local_hosts', []), true);
    }

    private function nextEpisodeContinueItem(EpisodeWatchProgress $progress): ?object
    {
        $episode = $progress->episode;

        if (! $episode) {
            return null;
        }

        $nextEpisode = Episode::query()
            ->with(['series', 'sources' => fn ($query) => $query
                ->whereIn('provider', config('watch_progress.providers', []))
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->orderBy('id')])
            ->where('series_id', $episode->series_id)
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->where(function ($query) use ($episode) {
                $query
                    ->where('season_number', '>', $episode->season_number)
                    ->orWhere(function ($sameSeasonQuery) use ($episode) {
                        $sameSeasonQuery
                            ->where('season_number', $episode->season_number)
                            ->where('episode_number', '>', $episode->episode_number);
                    });
            })
            ->whereHas('sources', fn ($query) => $query->whereIn('provider', config('watch_progress.providers', [])))
            ->orderBy('season_number')
            ->orderBy('episode_number')
            ->orderBy('id')
            ->first();

        if (! $nextEpisode) {
            return null;
        }

        $alreadyStarted = EpisodeWatchProgress::query()
            ->where('user_id', $progress->user_id)
            ->where('episode_id', $nextEpisode->id)
            ->exists();

        if ($alreadyStarted) {
            return null;
        }

        $source = $nextEpisode->sources->first();

        if (! $source) {
            return null;
        }

        return (object) [
            'episode' => $nextEpisode,
            'source' => $source,
            'episode_source_id' => $source->id,
            'provider' => $source->provider,
            'position_seconds' => 0,
            'duration_seconds' => null,
            'progress_percent' => 0,
            'completed' => false,
            'last_watched_at' => $progress->last_watched_at,
            'is_next_episode' => true,
        ];
    }

    private function resolveSection(string $slug): ?CatalogSection
    {
        if (! Schema::hasTable('catalog_sections')) {
            return null;
        }

        return CatalogSection::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    private function fallbackSection(): CatalogSection
    {
        return CatalogSection::make([
            'slug' => 'series-gl',
            'name' => 'Series GL',
            'label' => 'Serie GL',
            'hero_eyebrow' => 'Contenido GL · Actualizado diario',
            'hero_title' => 'Historias Girls’ Love para descubrir, sentir y compartir',
            'hero_description' => 'Mira series, doramas y películas GL de todo el mundo, subtituladas en español y con nuevos episodios cada semana.',
            'hero_video_url' => null,
            'hero_primary_label' => 'Explorar series GL',
            'hero_secondary_label' => 'Ver novedades',
        ]);
    }

    /** @return array<string, mixed> */
    private function emptyHomeData(CatalogSection $section): array
    {
        return [
            'section' => $section,
            'featuredSeries' => collect(),
            'latestEpisodes' => collect(),
            'seriesCount' => 0,
            'continueWatching' => collect(),
        ];
    }

    /** @return array<string, mixed> */
    private function emptyMixedHomeData(): array
    {
        return [
            'section' => $this->fallbackSection(),
            'isMixedHome' => true,
            'latestEpisodes' => collect(),
            'featuredSeries' => collect(),
            'mixedSeries' => collect(),
            'continueWatching' => collect(),
        ];
    }
}
