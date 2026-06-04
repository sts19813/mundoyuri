<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use App\Support\SeriesMedia;
use App\Support\VideoSource;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ContentSubmissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(): View
    {
        $genres = Genre::query()->where('is_active', true)->orderBy('name')->get();
        $sourceProviders = config('episode_sources.providers', []);

        return view('catalog.submissions.create', compact('genres', 'sourceProviders'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'genre_id' => ['required', 'exists:genres,id'],
            'title' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'in:series,movie'],
            'status' => ['required', 'in:ongoing,completed,upcoming'],
            'description' => ['required', 'string', 'min:30'],
            'country_of_origin' => ['nullable', 'string', 'max:120'],
            'release_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'banner_image' => SeriesMedia::validationRules(),
            'cover_image' => SeriesMedia::validationRules(),
            'episode_title' => ['nullable', 'string', 'max:255'],
            'episode_number' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'season_number' => ['nullable', 'integer', 'min:1', 'max:999'],
            'episode_release_date' => ['nullable', 'date'],
            'episode_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'episode_description' => ['nullable', 'string'],
            'source_provider' => ['nullable', Rule::in(array_keys(config('episode_sources.providers', [])))],
            'source_url' => [
                'nullable',
                'string',
                'max:5000',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $provider = trim((string) $request->input('source_provider', ''));
                    $rawUrl = trim((string) $value);

                    if ($provider === '' && $rawUrl === '') {
                        return;
                    }

                    if ($provider !== '' && $rawUrl === '') {
                        $fail('Debes indicar la URL o identificador de la fuente seleccionada.');

                        return;
                    }

                    if ($provider === '' && $rawUrl !== '') {
                        $fail('Selecciona un proveedor para la fuente indicada.');

                        return;
                    }

                    $normalizedProvider = VideoSource::normalizeProvider($provider);
                    $normalizedUrl = VideoSource::normalizeUrl($normalizedProvider, $rawUrl);

                    if (! $normalizedUrl) {
                        $fail('La fuente no es válida. Usa una URL pública, iframe o ID compatible.');

                        return;
                    }

                    $remoteValidationMessage = VideoSource::validateRemote($normalizedProvider, $normalizedUrl);

                    if ($remoteValidationMessage) {
                        $fail($remoteValidationMessage);
                    }
                },
            ],
            'source_label' => ['nullable', 'string', 'max:120'],
        ]);

        $user = auth()->user();
        $isAdmin = $user?->role === 'admin' || ($user && method_exists($user, 'hasRole') && $user->hasRole('admin'));
        $moderationStatus = $isAdmin ? 'approved' : 'pending';
        $seriesData = Arr::except($validated, ['banner_image', 'cover_image']);

        $series = Series::create([
            'genre_id' => $seriesData['genre_id'],
            'created_by' => auth()->id(),
            'approved_by' => $isAdmin ? auth()->id() : null,
            'title' => $seriesData['title'],
            'slug' => $this->resolveUniqueSlug($seriesData['title']),
            'content_type' => $seriesData['content_type'],
            'status' => $seriesData['status'],
            'description' => $seriesData['description'],
            'country_of_origin' => $seriesData['country_of_origin'] ?? null,
            'release_year' => $seriesData['release_year'] ?? null,
            'duration_minutes' => $seriesData['duration_minutes'] ?? null,
            'banner_image' => SeriesMedia::syncUploadedField($request, 'banner_image'),
            'cover_image' => SeriesMedia::syncUploadedField($request, 'cover_image'),
            'moderation_status' => $moderationStatus,
            'published_at' => $isAdmin ? now() : null,
        ]);

        if (! empty($validated['episode_title'])) {
            $episode = $series->episodes()->create([
                'created_by' => auth()->id(),
                'approved_by' => $isAdmin ? auth()->id() : null,
                'title' => $validated['episode_title'],
                'slug' => $this->resolveUniqueEpisodeSlug($series->title, $validated['episode_number'] ?? 1),
                'season_number' => $validated['season_number'] ?? 1,
                'episode_number' => $validated['episode_number'] ?? 1,
                'release_date' => $validated['episode_release_date'] ?? null,
                'duration_minutes' => $validated['episode_duration_minutes'] ?? null,
                'description' => $validated['episode_description'] ?? null,
                'moderation_status' => $moderationStatus,
                'published_at' => $isAdmin ? now() : null,
            ]);

            if (! empty($validated['source_provider']) && ! empty($validated['source_url'])) {
                $providerConfig = config('episode_sources.providers.'.$validated['source_provider'], []);
                $provider = $providerConfig['stores_as'] ?? $validated['source_provider'];
                $normalizedUrl = VideoSource::normalizeUrl($provider, $validated['source_url']);

                if ($normalizedUrl) {
                    $episode->sources()->create([
                        'provider' => $provider,
                        'label' => $validated['source_label'] ?? 'Fuente principal',
                        'video_url' => $normalizedUrl,
                        'is_primary' => true,
                    ]);
                }
            }
        }

        $message = $isAdmin
            ? 'Contenido publicado correctamente.'
            : 'Contenido enviado. Quedo en revision para validacion.';

        return redirect()->route('submissions.create')->with('success', $message);
    }

    private function resolveUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $index = 1;

        while (Series::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$index}";
            $index++;
        }

        return $slug;
    }

    private function resolveUniqueEpisodeSlug(string $seriesTitle, int $episodeNumber): string
    {
        $baseSlug = Str::slug($seriesTitle.'-episodio-'.$episodeNumber);
        $slug = $baseSlug;
        $index = 1;

        while (Episode::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$index}";
            $index++;
        }

        return $slug;
    }
}
