<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Duo;
use App\Models\Series;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DiscoveryPeopleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:edit series']);
    }

    public function artistsIndex(): View
    {
        $artists = Artist::query()->withCount('series')->orderBy('name')->paginate(30);

        return view('admin.discovery.artists.index', compact('artists'));
    }

    public function artistsCreate(): View
    {
        return view('admin.discovery.artists.create', ['series' => $this->seriesOptions()]);
    }

    public function artistsStore(Request $request): RedirectResponse
    {
        $validated = $this->validateArtist($request);
        $artist = Artist::query()->create($this->artistAttributes($validated));
        $artist->series()->sync($validated['series_ids'] ?? []);

        return redirect()->route('admin.artists.index')->with('success', 'Artista creada.');
    }

    public function artistsEdit(Artist $artist): View
    {
        $artist->load('series');

        return view('admin.discovery.artists.edit', ['artist' => $artist, 'series' => $this->seriesOptions()]);
    }

    public function artistsUpdate(Request $request, Artist $artist): RedirectResponse
    {
        $validated = $this->validateArtist($request, $artist);
        $artist->update($this->artistAttributes($validated, $artist));
        $artist->series()->sync($validated['series_ids'] ?? []);

        return redirect()->route('admin.artists.index')->with('success', 'Artista actualizada.');
    }

    public function artistsDestroy(Artist $artist): RedirectResponse
    {
        $artist->delete();

        return redirect()->route('admin.artists.index')->with('success', 'Artista eliminada.');
    }

    public function duosIndex(): View
    {
        $duos = Duo::query()->withCount('series')->orderBy('name')->paginate(30);

        return view('admin.discovery.duos.index', compact('duos'));
    }

    public function duosCreate(): View
    {
        return view('admin.discovery.duos.create', ['series' => $this->seriesOptions()]);
    }

    public function duosStore(Request $request): RedirectResponse
    {
        $validated = $this->validateDuo($request);
        $duo = Duo::query()->create($this->duoAttributes($validated));
        $duo->series()->sync($validated['series_ids'] ?? []);

        return redirect()->route('admin.duos.index')->with('success', 'Dupla creada.');
    }

    public function duosEdit(Duo $duo): View
    {
        $duo->load('series');

        return view('admin.discovery.duos.edit', ['duo' => $duo, 'series' => $this->seriesOptions()]);
    }

    public function duosUpdate(Request $request, Duo $duo): RedirectResponse
    {
        $validated = $this->validateDuo($request, $duo);
        $duo->update($this->duoAttributes($validated, $duo));
        $duo->series()->sync($validated['series_ids'] ?? []);

        return redirect()->route('admin.duos.index')->with('success', 'Dupla actualizada.');
    }

    public function duosDestroy(Duo $duo): RedirectResponse
    {
        $duo->delete();

        return redirect()->route('admin.duos.index')->with('success', 'Dupla eliminada.');
    }

    private function seriesOptions()
    {
        return Series::query()->orderBy('title')->get(['id', 'title', 'release_year']);
    }

    private function validateArtist(Request $request, ?Artist $artist = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160', 'unique:artists,name,'.($artist?->id)],
            'slug' => ['nullable', 'string', 'max:160'],
            'aliases' => ['nullable', 'string'],
            'country_of_origin' => ['nullable', 'string', 'max:120'],
            'biography' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'series_ids' => ['nullable', 'array'],
            'series_ids.*' => ['integer', 'exists:series,id'],
        ]);
    }

    private function validateDuo(Request $request, ?Duo $duo = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160', 'unique:duos,name,'.($duo?->id)],
            'slug' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'series_ids' => ['nullable', 'array'],
            'series_ids.*' => ['integer', 'exists:series,id'],
        ]);
    }

    private function artistAttributes(array $validated, ?Artist $artist = null): array
    {
        return [
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug(Artist::class, $validated['slug'] ?: $validated['name'], $artist?->id),
            'aliases' => $validated['aliases'] ?? null,
            'country_of_origin' => $validated['country_of_origin'] ?? null,
            'biography' => $validated['biography'] ?? null,
            'is_active' => request()->boolean('is_active', true),
        ];
    }

    private function duoAttributes(array $validated, ?Duo $duo = null): array
    {
        return [
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug(Duo::class, $validated['slug'] ?: $validated['name'], $duo?->id),
            'description' => $validated['description'] ?? null,
            'is_active' => request()->boolean('is_active', true),
        ];
    }

    private function uniqueSlug(string $model, string $value, ?int $exceptId = null): string
    {
        $base = Str::slug($value) ?: 'perfil';
        $slug = $base;
        $suffix = 2;

        while ($model::query()->where('slug', $slug)->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
