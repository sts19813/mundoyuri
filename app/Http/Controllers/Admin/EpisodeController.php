<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Series;
use App\Support\SeriesMedia;
use App\Support\VideoSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EpisodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view episodes')->only(['index', 'show']);
        $this->middleware('can:create episodes')->only(['create', 'store']);
        $this->middleware('can:edit episodes')->only(['edit', 'update']);
        $this->middleware('can:delete episodes')->only('destroy');
    }

    public function index(Request $request): View
    {
        $query = Episode::query()->with(['series', 'sources']);

        if (! $request->user()->can('moderate content')) {
            $query->where(function ($scope) use ($request): void {
                $scope->where('moderation_status', 'approved')
                    ->orWhere('created_by', $request->user()->id);
            });
        }

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

        $seriesOptions = $this->availableSeries()->get();

        return view('admin.episodes.index', compact('episodes', 'seriesOptions'));
    }

    public function create(): View
    {
        $seriesOptions = $this->availableSeries()->get();
        $sourceProviders = $this->sourceProviders();

        return view('admin.episodes.create', compact('seriesOptions', 'sourceProviders'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $this->validateEpisode($request);
        $episodeData = $this->episodeData($validated);

        $episode = Episode::create([
            ...$episodeData,
            'thumbnail_image' => SeriesMedia::syncUploadedField($request, 'thumbnail_image'),
            'slug' => $this->resolveUniqueSlug($validated['slug'] ?? $this->buildDefaultSlug($validated)),
            'created_by' => auth()->id(),
        ]);

        $this->syncSources($episode, $request);
        $this->syncModeration($episode, $validated['moderation_status'] ?? 'pending', $validated['moderation_notes'] ?? null);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Episodio guardado.',
                'redirect' => $request->user()->can('edit episodes')
                    ? route('admin.episodes.edit', $episode)
                    : route('admin.episodes.show', $episode),
            ]);
        }

        return redirect()->route('admin.episodes.index')->with('success', 'Episodio guardado.');
    }

    public function show(Episode $episode): View
    {
        $this->authorizeVisible($episode);
        $episode->load(['series', 'sources', 'creator', 'approver']);

        return view('admin.episodes.show', compact('episode'));
    }

    public function edit(Episode $episode): View
    {
        $seriesOptions = $this->availableSeries()->get();
        $sourceProviders = $this->sourceProviders();
        $episode->load('sources');

        return view('admin.episodes.edit', compact('episode', 'seriesOptions', 'sourceProviders'));
    }

    public function update(Request $request, Episode $episode): JsonResponse|RedirectResponse
    {
        $validated = $this->validateEpisode($request, $episode->id);
        $episodeData = $this->episodeData($validated);

        $episode->update([
            ...$episodeData,
            'thumbnail_image' => SeriesMedia::syncUploadedField($request, 'thumbnail_image', $episode->thumbnail_image),
            'slug' => $this->resolveUniqueSlug($validated['slug'] ?? $this->buildDefaultSlug($validated), $episode->id),
        ]);

        $this->syncSources($episode, $request);
        $this->syncModeration($episode, $validated['moderation_status'] ?? 'pending', $validated['moderation_notes'] ?? null);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Episodio actualizado.',
                'redirect' => route('admin.episodes.edit', $episode),
            ]);
        }

        return redirect()->route('admin.episodes.index')->with('success', 'Episodio actualizado.');
    }

    public function destroy(Episode $episode): RedirectResponse
    {
        SeriesMedia::deleteIfStored($episode->thumbnail_image);
        $episode->delete();

        return redirect()->route('admin.episodes.index')->with('success', 'Episodio eliminado.');
    }

    private function validateEpisode(Request $request, ?int $exceptId = null): array
    {
        $seriesExists = Rule::exists('series', 'id');

        if (! $request->user()->can('moderate content')) {
            $seriesExists->where(function ($query) use ($request): void {
                $query->where('moderation_status', 'approved')
                    ->orWhere('created_by', $request->user()->id);
            });
        }

        return $request->validate([
            'series_id' => ['required', $seriesExists],
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
            'thumbnail_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'description' => ['nullable', 'string'],
            'moderation_status' => [Rule::requiredIf($request->user()->can('moderate content')), 'nullable', 'in:pending,approved,rejected'],
            'moderation_notes' => ['nullable', 'string'],
            'source_provider' => ['nullable', 'array'],
            'source_provider.*' => ['nullable', Rule::in(array_keys($this->sourceProviders()))],
            'source_type' => ['nullable', 'array'],
            'source_type.*' => ['nullable', Rule::in(['full', 'part'])],
            'source_url' => ['nullable', 'array'],
            'source_url.*' => [
                'nullable',
                'string',
                'max:5000',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $index = (int) Str::afterLast($attribute, '.');
                    $provider = trim((string) Arr::get($request->input('source_provider', []), $index, ''));
                    $rawUrl = trim((string) $value);

                    if ($provider === '' && $rawUrl === '') {
                        return;
                    }

                    if ($provider !== '' && $rawUrl === '') {
                        $fail('Debes indicar la URL o iframe de la fuente seleccionada.');

                        return;
                    }

                    if ($provider === '' && $rawUrl !== '') {
                        $fail('Selecciona un proveedor para la fuente indicada.');

                        return;
                    }

                    $normalizedProvider = VideoSource::normalizeProvider($provider);
                    $normalizedUrl = VideoSource::normalizeUrl($normalizedProvider, $rawUrl);

                    if (! $normalizedUrl) {
                        $fail('La fuente no es válida. Usa una URL pública o un iframe con src válido.');

                        return;
                    }

                    $remoteValidationMessage = VideoSource::validateRemote($normalizedProvider, $normalizedUrl);

                    if ($remoteValidationMessage) {
                        $fail($remoteValidationMessage);
                    }
                },
            ],
            'source_label' => ['nullable', 'array'],
            'source_label.*' => ['nullable', 'string', 'max:120'],
            'source_sort_order' => ['nullable', 'array'],
            'source_sort_order.*' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'source_primary' => ['nullable', 'integer'],
        ]);
    }

    private function episodeData(array $validated): array
    {
        return Arr::except($validated, [
            'thumbnail_image',
            'source_provider',
            'source_type',
            'source_url',
            'source_label',
            'source_sort_order',
            'source_primary',
        ]);
    }

    private function syncSources(Episode $episode, Request $request): void
    {
        $providers = $request->input('source_provider', []);
        $types = $request->input('source_type', []);
        $urls = $request->input('source_url', []);
        $labels = $request->input('source_label', []);
        $sortOrders = $request->input('source_sort_order', []);
        $primaryIndex = (int) $request->input('source_primary', 0);
        $validSources = [];

        $episode->sources()->delete();

        foreach ($providers as $index => $provider) {
            $provider = trim((string) $provider);
            $url = $urls[$index] ?? null;

            if (empty($provider) || empty($url)) {
                continue;
            }

            $provider = VideoSource::normalizeProvider($provider);
            $normalizedUrl = VideoSource::normalizeUrl($provider, (string) $url);

            if (! $normalizedUrl) {
                continue;
            }

            $sourceType = in_array(($types[$index] ?? 'full'), ['full', 'part'], true)
                ? $types[$index]
                : 'full';

            $validSources[] = [
                'provider' => $provider,
                'source_type' => $sourceType,
                'video_url' => $normalizedUrl,
                'label' => $labels[$index] ?? null,
                'sort_order' => (int) ($sortOrders[$index] ?? ($index + 1)),
                'is_primary' => $index === $primaryIndex,
            ];
        }

        usort($validSources, fn (array $left, array $right) => [($left['source_type'] === 'part' ? 0 : 1), $left['sort_order']] <=> [($right['source_type'] === 'part' ? 0 : 1), $right['sort_order']]);

        $hasPrimary = collect($validSources)->contains(fn (array $source) => $source['is_primary'] === true);

        foreach ($validSources as $index => $source) {
            $episode->sources()->create([
                ...$source,
                'is_primary' => $source['is_primary'] || (! $hasPrimary && $index === 0),
            ]);
        }
    }

    private function normalizeProvider(string $provider): string
    {
        $provider = strtolower(trim($provider));
        $providerConfig = $this->sourceProviders();

        return $providerConfig[$provider]['stores_as'] ?? $provider;
    }

    private function normalizeSourceUrl(string $provider, string $rawValue): ?string
    {
        $rawValue = trim($rawValue);

        if ($rawValue === '') {
            return null;
        }

        $url = $this->extractIframeSrc($rawValue) ?? $rawValue;
        $url = trim($url);

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        if ($provider === 'youtube') {
            return $this->normalizeYouTubeEmbedUrl($url);
        }

        if ($provider === 'pixeldrain_cdn') {
            return $this->normalizePixeldrainUrl($url);
        }

        return $url;
    }

    private function extractIframeSrc(string $rawValue): ?string
    {
        if (! str_contains(strtolower($rawValue), '<iframe')) {
            return null;
        }

        if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $rawValue, $matches) === 1) {
            return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return null;
    }

    private function normalizeYouTubeEmbedUrl(string $url): ?string
    {
        $parts = parse_url($url);

        if (! $parts || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $path = $parts['path'] ?? '';
        $query = $parts['query'] ?? '';
        $queryParams = [];
        parse_str($query, $queryParams);

        $videoId = null;

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $videoId = trim($path, '/');
        }

        if (! $videoId && isset($queryParams['v'])) {
            $videoId = (string) $queryParams['v'];
        }

        if (! $videoId && preg_match('~/embed/([^/?&]+)~', $path, $matches) === 1) {
            $videoId = $matches[1];
        }

        if (! $videoId && preg_match('~/shorts/([^/?&]+)~', $path, $matches) === 1) {
            $videoId = $matches[1];
        }

        if (! $videoId && preg_match('~/v/([^/?&]+)~', $path, $matches) === 1) {
            $videoId = $matches[1];
        }

        if (! $videoId && preg_match('~/live/([^/?&]+)~', $path, $matches) === 1) {
            $videoId = $matches[1];
        }

        if (! $videoId) {
            return null;
        }

        $videoId = preg_replace('/[^a-zA-Z0-9_-]/', '', $videoId);

        if (! $videoId) {
            return null;
        }

        $embedParams = [];
        $allowedParams = ['start', 'list', 'index', 'si', 'rel', 'autoplay'];

        foreach ($allowedParams as $param) {
            if (isset($queryParams[$param]) && $queryParams[$param] !== '') {
                $embedParams[$param] = $queryParams[$param];
            }
        }

        if (! isset($embedParams['start']) && isset($queryParams['t'])) {
            $seconds = $this->parseYouTubeTimeToSeconds((string) $queryParams['t']);

            if ($seconds > 0) {
                $embedParams['start'] = $seconds;
            }
        }

        $embedUrl = 'https://www.youtube.com/embed/'.$videoId;

        if (! empty($embedParams)) {
            $embedUrl .= '?'.http_build_query($embedParams);
        }

        return $embedUrl;
    }

    private function normalizePixeldrainUrl(string $url): ?string
    {
        $parts = parse_url($url);

        if (! $parts || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $path = trim($parts['path'] ?? '', '/');

        if (! str_contains($host, 'pixeldrain')) {
            return null;
        }

        $fileId = null;

        if (preg_match('~^api/file/([^/?#]+)$~', $path, $matches) === 1) {
            $fileId = $matches[1];
        } elseif (preg_match('~^u/([^/?#]+)$~', $path, $matches) === 1) {
            $fileId = $matches[1];
        } elseif (preg_match('~^([^/?#]+)$~', $path, $matches) === 1) {
            $fileId = $matches[1];
        }

        if (! $fileId) {
            return null;
        }

        $fileId = preg_replace('/[^a-zA-Z0-9_-]/', '', $fileId);

        if (! $fileId) {
            return null;
        }

        return 'https://pixeldrain.com/api/file/'.$fileId;
    }

    private function parseYouTubeTimeToSeconds(string $value): int
    {
        $value = strtolower(trim($value));

        if ($value === '') {
            return 0;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        $hours = 0;
        $minutes = 0;
        $seconds = 0;

        if (preg_match('/(\d+)h/', $value, $match) === 1) {
            $hours = (int) $match[1];
        }

        if (preg_match('/(\d+)m/', $value, $match) === 1) {
            $minutes = (int) $match[1];
        }

        if (preg_match('/(\d+)s/', $value, $match) === 1) {
            $seconds = (int) $match[1];
        }

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    private function sourceProviders(): array
    {
        return config('episode_sources.providers', []);
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
        if (! auth()->user()->can('moderate content')) {
            $status = 'pending';
            $notes = null;
        }

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

    private function availableSeries(): Builder
    {
        return Series::query()
            ->when(! auth()->user()->can('moderate content'), function ($query): void {
                $query->where(function ($scope): void {
                    $scope->where('moderation_status', 'approved')
                        ->orWhere('created_by', auth()->id());
                });
            })
            ->orderBy('title');
    }

    private function authorizeVisible(Episode $episode): void
    {
        abort_unless(
            auth()->user()->can('moderate content')
                || $episode->moderation_status === 'approved'
                || $episode->created_by === auth()->id(),
            403
        );
    }
}
