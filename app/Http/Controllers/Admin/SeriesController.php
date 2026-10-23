<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogSection;
use App\Models\Artist;
use App\Models\Duo;
use App\Models\Genre;
use App\Models\Series;
use App\Models\Tag;
use App\Support\SeriesMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SeriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view series')->only(['index', 'show']);
        $this->middleware('can:create series')->only(['create', 'store']);
        $this->middleware('can:edit series')->only(['edit', 'update']);
        $this->middleware('can:delete series')->only('destroy');
    }

    public function index(Request $request): View
    {
        $query = Series::query()->with(['genre', 'creator']);

        if ($request->filled('catalog_section')) {
            $query->where('catalog_section', $request->string('catalog_section'));
        }

        if (! $request->user()->can('moderate content')) {
            $query->where(function ($scope) use ($request): void {
                $scope->where('moderation_status', 'approved')
                    ->orWhere('created_by', $request->user()->id);
            });
        }

        if ($request->filled('moderation_status')) {
            $query->where('moderation_status', $request->string('moderation_status'));
        }

        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->string('q').'%');
        }

        $series = $query->latest()->paginate(20)->withQueryString();

        $catalogSections = CatalogSection::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.series.index', compact('series', 'catalogSections'));
    }

    public function create(): View
    {
        $genres = Genre::query()->where('is_active', true)->orderBy('name')->get();
        $catalogSections = CatalogSection::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $tags = Tag::query()->where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $artists = Artist::query()->where('is_active', true)->orderBy('name')->get();
        $duos = Duo::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.series.create', compact('genres', 'catalogSections', 'tags', 'artists', 'duos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSeries($request);
        $seriesData = Arr::except($validated, ['banner_image', 'cover_image', 'tag_ids', 'artist_ids', 'duo_ids']);
        $seriesData['alternate_titles'] = $this->normalizeAlternateTitles($validated['alternate_titles'] ?? null);

        if (! $request->user()->can('moderate content')) {
            $seriesData['is_featured'] = false;
            $seriesData['moderation_status'] = 'pending';
            $seriesData['moderation_notes'] = null;
        }

        $series = Series::create([
            ...$seriesData,
            'banner_image' => SeriesMedia::syncUploadedField($request, 'banner_image'),
            'cover_image' => SeriesMedia::syncUploadedField($request, 'cover_image'),
            'slug' => $this->resolveUniqueSlug($validated['slug'] ?? $validated['title']),
            'created_by' => auth()->id(),
        ]);

        $this->syncModeration($series, $validated['moderation_status'] ?? 'pending', $validated['moderation_notes'] ?? null);
        $this->syncDiscoveryRelations($series, $validated);

        return redirect()->route('admin.series.index')->with('success', 'Serie guardada.');
    }

    public function show(Series $series): View
    {
        $this->authorizeVisible($series);
        $series->load([
            'genre',
            'creator',
            'approver',
            'episodes' => function ($query): void {
                if (! auth()->user()->can('moderate content')) {
                    $query->where(function ($scope): void {
                        $scope->where('moderation_status', 'approved')
                            ->orWhere('created_by', auth()->id());
                    });
                }

                $query->with('sources');
            },
        ]);

        return view('admin.series.show', compact('series'));
    }

    public function edit(Series $series): View
    {
        $genres = Genre::query()->where('is_active', true)->orderBy('name')->get();
        $catalogSections = CatalogSection::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $tags = Tag::query()->where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $artists = Artist::query()->where('is_active', true)->orderBy('name')->get();
        $duos = Duo::query()->where('is_active', true)->orderBy('name')->get();
        $series->loadMissing(['tags', 'artists', 'duos']);

        return view('admin.series.edit', compact('series', 'genres', 'catalogSections', 'tags', 'artists', 'duos'));
    }

    public function update(Request $request, Series $series): RedirectResponse
    {
        $validated = $this->validateSeries($request, $series->id);
        $seriesData = Arr::except($validated, ['banner_image', 'cover_image', 'tag_ids', 'artist_ids', 'duo_ids']);
        $seriesData['alternate_titles'] = $this->normalizeAlternateTitles($validated['alternate_titles'] ?? null);

        $series->update([
            ...$seriesData,
            'banner_image' => SeriesMedia::syncUploadedField($request, 'banner_image', $series->banner_image),
            'cover_image' => SeriesMedia::syncUploadedField($request, 'cover_image', $series->cover_image),
            'slug' => $this->resolveUniqueSlug($validated['slug'] ?? $validated['title'], $series->id),
        ]);

        $this->syncModeration($series, $validated['moderation_status'] ?? 'pending', $validated['moderation_notes'] ?? null);
        $this->syncDiscoveryRelations($series, $validated);

        return redirect()->route('admin.series.index')->with('success', 'Serie actualizada.');
    }

    public function destroy(Series $series): RedirectResponse
    {
        SeriesMedia::deleteIfStored($series->banner_image);
        SeriesMedia::deleteIfStored($series->cover_image);
        $series->delete();

        return redirect()->route('admin.series.index')->with('success', 'Serie eliminada.');
    }

    private function validateSeries(Request $request, ?int $exceptId = null): array
    {
        return $request->validate([
            'genre_id' => ['required', 'exists:genres,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:series,slug,'.$exceptId],
            'content_type' => ['required', 'in:series,movie'],
            'catalog_section' => ['required', 'exists:catalog_sections,slug'],
            'status' => ['required', 'in:ongoing,completed,upcoming'],
            'description' => ['required', 'string'],
            'alternate_titles' => ['nullable', 'string', 'max:2000'],
            'seo_title' => ['nullable', 'string', 'max:120'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'country_of_origin' => ['nullable', 'string', 'max:120'],
            'release_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'total_seasons' => ['nullable', 'integer', 'min:1', 'max:100'],
            'total_episodes' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'banner_image' => SeriesMedia::validationRules(),
            'cover_image' => SeriesMedia::validationRules(),
            'trailer_url' => ['nullable', 'url'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'artist_ids' => ['nullable', 'array'],
            'artist_ids.*' => ['integer', 'exists:artists,id'],
            'duo_ids' => ['nullable', 'array'],
            'duo_ids.*' => ['integer', 'exists:duos,id'],
            'is_featured' => ['nullable', 'boolean'],
            'moderation_status' => [Rule::requiredIf($request->user()->can('moderate content')), 'nullable', 'in:pending,approved,rejected'],
            'moderation_notes' => ['nullable', 'string'],
        ]);
    }

    /** @return array<int, string> */
    private function normalizeAlternateTitles(?string $titles): array
    {
        return collect(preg_split('/[\r\n;]+/', (string) $titles) ?: [])
            ->map(fn (string $title): string => Str::squish(strip_tags($title)))
            ->filter()
            ->unique(fn (string $title): string => Str::lower($title))
            ->take(20)
            ->values()
            ->all();
    }

    private function resolveUniqueSlug(string $slug, ?int $exceptId = null): string
    {
        $baseSlug = Str::slug($slug);
        $finalSlug = $baseSlug;
        $index = 1;

        while (
            Series::query()
                ->where('slug', $finalSlug)
                ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $finalSlug = "{$baseSlug}-{$index}";
            $index++;
        }

        return $finalSlug;
    }

    private function syncModeration(Series $series, string $status, ?string $notes): void
    {
        if (! auth()->user()->can('moderate content')) {
            $status = 'pending';
            $notes = null;
        }

        if ($status === 'approved') {
            $series->forceFill([
                'moderation_status' => 'approved',
                'moderation_notes' => $notes,
                'approved_by' => auth()->id(),
                'published_at' => $series->published_at ?: now(),
            ])->save();

            return;
        }

        if ($status === 'rejected') {
            $series->forceFill([
                'moderation_status' => 'rejected',
                'moderation_notes' => $notes,
                'approved_by' => auth()->id(),
                'published_at' => null,
            ])->save();

            return;
        }

        $series->forceFill([
            'moderation_status' => 'pending',
            'moderation_notes' => $notes,
            'approved_by' => null,
            'published_at' => null,
        ])->save();
    }

    private function syncDiscoveryRelations(Series $series, array $validated): void
    {
        $series->tags()->sync($validated['tag_ids'] ?? []);
        $series->artists()->sync($validated['artist_ids'] ?? []);
        $series->duos()->sync($validated['duo_ids'] ?? []);
    }

    private function authorizeVisible(Series $series): void
    {
        abort_unless(
            auth()->user()->can('moderate content')
                || $series->moderation_status === 'approved'
                || $series->created_by === auth()->id(),
            403
        );
    }
}
