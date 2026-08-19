<?php

namespace App\Http\Controllers;

use App\Models\CatalogSection;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class CatalogController extends Controller
{
    public function home(): View
    {
        if (! $this->catalogTablesReady()) {
            return view('catalog.home', [
                'featuredSeries' => collect(),
                'latestEpisodes' => collect(),
                'genres' => collect(),
            ]);
        }

        $featuredSeries = Series::query()
            ->with('genre')
            ->where('moderation_status', 'approved')
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(8)
            ->get();

        $latestEpisodes = Episode::query()
            ->with(['series.genre'])
            ->where('moderation_status', 'approved')
            ->orderByRaw('COALESCE(release_date, published_at, created_at) DESC')
            ->orderByDesc('id')
            ->get()
            ->unique('series_id')
            ->take(12)
            ->values();

        $genres = Genre::query()
            ->where('is_active', true)
            ->withCount(['series' => function ($query) {
                $query->where('moderation_status', 'approved');
            }])
            ->orderBy('name')
            ->get();

        return view('catalog.home', compact('featuredSeries', 'latestEpisodes', 'genres'));
    }

    public function series(Request $request): View
    {
        if (! $this->catalogTablesReady()) {
            return view('catalog.series.index', [
                'series' => new LengthAwarePaginator([], 0, 12),
                'genres' => collect(),
                'catalogSections' => collect(),
                'activeSection' => null,
            ]);
        }

        // The public catalogue is intentionally rendered as one complete list.
        // Its filters run client-side, so changing a field is immediate and does
        // not leave the visitor on a partial paginator result set.
        $series = Series::query()
            ->with('genre')
            ->where('moderation_status', 'approved')
            ->when($request->filled('section'), fn ($query) => $query->where('catalog_section', $request->string('section')))
            ->when($request->filled('type'), fn ($query) => $query->where('content_type', $request->string('type')))
            ->latest('published_at')
            ->get();
        $genres = Genre::query()->where('is_active', true)->orderBy('name')->get();
        $catalogSections = CatalogSection::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $activeSection = $request->filled('section')
            ? CatalogSection::query()->where('slug', $request->string('section'))->first()
            : null;

        return view('catalog.series.index', compact('series', 'genres', 'catalogSections', 'activeSection'));
    }

    public function genres(): View
    {
        if (! $this->catalogTablesReady()) {
            return view('catalog.genres.index', [
                'genres' => new LengthAwarePaginator([], 0, 24),
            ]);
        }

        $genres = Genre::query()
            ->where('is_active', true)
            ->withCount(['series' => function ($query) {
                $query->where('moderation_status', 'approved');
            }])
            ->orderBy('name')
            ->paginate(24);

        return view('catalog.genres.index', compact('genres'));
    }

    public function genre(Genre $genre): View
    {
        if (! $this->catalogTablesReady()) {
            abort(404);
        }

        abort_unless($genre->is_active, 404);

        $series = $genre->series()
            ->where('moderation_status', 'approved')
            ->with('genre')
            ->latest('published_at')
            ->paginate(12);

        return view('catalog.genres.show', compact('genre', 'series'));
    }

    public function showSeries(Series $series): View
    {
        if (! $this->catalogTablesReady()) {
            abort(404);
        }

        abort_unless($series->moderation_status === 'approved', 404);

        $series->load([
            'genre',
            'episodes' => fn ($query) => $query
                ->where('moderation_status', 'approved')
                ->whereNotNull('published_at')
                ->orderBy('season_number')
                ->orderBy('episode_number'),
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

        $recentEpisodes = Episode::query()
            ->with('series')
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->take(8)
            ->get();

        return view('catalog.series.show', compact('series', 'recentEpisodes'));
    }

    public function showEpisode(Series $series, Episode $episode): View
    {
        if (! $this->catalogTablesReady()) {
            abort(404);
        }

        abort_unless($series->moderation_status === 'approved', 404);
        abort_unless($episode->series_id === $series->id, 404);
        abort_unless($episode->moderation_status === 'approved', 404);

        $episode->recordView(auth()->user());

        $episode->load([
            'sources',
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

        $episodes = $series->episodes()
            ->where('moderation_status', 'approved')
            ->orderBy('season_number')
            ->orderBy('episode_number')
            ->get();

        return view('catalog.episodes.show', compact('series', 'episode', 'episodes'));
    }

    private function catalogTablesReady(): bool
    {
        return Schema::hasTable('genres')
            && Schema::hasTable('series')
            && Schema::hasTable('episodes');
    }
}
