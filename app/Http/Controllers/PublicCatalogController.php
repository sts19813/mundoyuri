<?php

namespace App\Http\Controllers;

use App\Models\CatalogSection;
use App\Models\Episode;
use App\Models\Series;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PublicCatalogController extends Controller
{
    public function home(): View
    {
        if (! $this->catalogTablesReady()) {
            return view('index', $this->emptyHomeData($this->fallbackSection()));
        }

        $section = $this->resolveSection('series-gl') ?? $this->fallbackSection();

        return view('index', $this->homeData($section));
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

        return compact(
            'section',
            'featuredSeries',
            'latestEpisodes',
            'seriesCount'
        );
    }

    public function episodes(?Episode $episode = null): View
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
        ];
    }
}
