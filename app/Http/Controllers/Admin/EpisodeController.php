<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Series;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EpisodeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request): View
    {
        $query = Episode::query()->with(['series', 'sources']);

        if ($request->filled('series_id')) {
            $query->where('series_id', $request->integer('series_id'));
        }

        if ($request->filled('moderation_status')) {
            $query->where('moderation_status', $request->string('moderation_status'));
        }

        $episodes = $query
            ->orderByDesc('release_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $seriesOptions = Series::query()->orderBy('title')->get();

        return view('admin.episodes.index', compact('episodes', 'seriesOptions'));
    }

    public function create(): View
    {
        $seriesOptions = Series::query()->orderBy('title')->get();

        return view('admin.episodes.create', compact('seriesOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEpisode($request);

        $episode = Episode::create([
            ...$validated,
            'slug' => $this->resolveUniqueSlug($validated['slug'] ?? $this->buildDefaultSlug($validated)),
            'created_by' => auth()->id(),
        ]);

        $this->syncSources($episode, $request);
        $this->syncModeration($episode, $validated['moderation_status'] ?? 'pending', $validated['moderation_notes'] ?? null);

        return redirect()->route('admin.episodes.index')->with('success', 'Episodio guardado.');
    }

    public function show(Episode $episode): View
    {
        $episode->load(['series', 'sources', 'creator', 'approver']);

        return view('admin.episodes.show', compact('episode'));
    }

    public function edit(Episode $episode): View
    {
        $seriesOptions = Series::query()->orderBy('title')->get();
        $episode->load('sources');

        return view('admin.episodes.edit', compact('episode', 'seriesOptions'));
    }

    public function update(Request $request, Episode $episode): RedirectResponse
    {
        $validated = $this->validateEpisode($request, $episode->id);

        $episode->update([
            ...$validated,
            'slug' => $this->resolveUniqueSlug($validated['slug'] ?? $this->buildDefaultSlug($validated), $episode->id),
        ]);

        $this->syncSources($episode, $request);
        $this->syncModeration($episode, $validated['moderation_status'] ?? 'pending', $validated['moderation_notes'] ?? null);

        return redirect()->route('admin.episodes.index')->with('success', 'Episodio actualizado.');
    }

    public function destroy(Episode $episode): RedirectResponse
    {
        $episode->delete();

        return redirect()->route('admin.episodes.index')->with('success', 'Episodio eliminado.');
    }

    private function validateEpisode(Request $request, ?int $exceptId = null): array
    {
        return $request->validate([
            'series_id' => ['required', 'exists:series,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:episodes,slug,'.$exceptId],
            'season_number' => ['required', 'integer', 'min:1', 'max:999'],
            'episode_number' => [
                'required',
                'integer',
                'min:1',
                'max:99999',
                Rule::unique('episodes')
                    ->where(fn ($query) => $query->where('series_id', $request->input('series_id'))->where('season_number', $request->input('season_number')))
                    ->ignore($exceptId),
            ],
            'release_date' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'thumbnail_image' => ['nullable', 'url'],
            'description' => ['nullable', 'string'],
            'moderation_status' => ['required', 'in:pending,approved,rejected'],
            'moderation_notes' => ['nullable', 'string'],
            'source_provider.*' => ['nullable', 'in:youtube,vimeo,byse,voe,ok,netu'],
            'source_url.*' => ['nullable', 'url'],
            'source_label.*' => ['nullable', 'string', 'max:120'],
            'source_primary' => ['nullable', 'integer'],
        ]);
    }

    private function syncSources(Episode $episode, Request $request): void
    {
        $providers = $request->input('source_provider', []);
        $urls = $request->input('source_url', []);
        $labels = $request->input('source_label', []);
        $primaryIndex = (int) $request->input('source_primary', 0);

        $episode->sources()->delete();

        foreach ($providers as $index => $provider) {
            $url = $urls[$index] ?? null;

            if (empty($provider) || empty($url)) {
                continue;
            }

            $episode->sources()->create([
                'provider' => $provider,
                'video_url' => $url,
                'label' => $labels[$index] ?? null,
                'is_primary' => $index === $primaryIndex,
            ]);
        }
    }

    private function resolveUniqueSlug(string $slug, ?int $exceptId = null): string
    {
        $baseSlug = Str::slug($slug);
        $finalSlug = $baseSlug;
        $index = 1;

        while (
            Episode::query()
                ->where('slug', $finalSlug)
                ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $finalSlug = "{$baseSlug}-{$index}";
            $index++;
        }

        return $finalSlug;
    }

    private function buildDefaultSlug(array $validated): string
    {
        $series = Series::query()->find($validated['series_id']);

        return Str::slug(
            $series?->title.'-s'.$validated['season_number'].'e'.$validated['episode_number']
        );
    }

    private function syncModeration(Episode $episode, string $status, ?string $notes): void
    {
        if ($status === 'approved') {
            $episode->forceFill([
                'moderation_status' => 'approved',
                'moderation_notes' => $notes,
                'approved_by' => auth()->id(),
                'published_at' => $episode->published_at ?: now(),
            ])->save();

            return;
        }

        if ($status === 'rejected') {
            $episode->forceFill([
                'moderation_status' => 'rejected',
                'moderation_notes' => $notes,
                'approved_by' => auth()->id(),
                'published_at' => null,
            ])->save();

            return;
        }

        $episode->forceFill([
            'moderation_status' => 'pending',
            'moderation_notes' => $notes,
            'approved_by' => null,
            'published_at' => null,
        ])->save();
    }
}
