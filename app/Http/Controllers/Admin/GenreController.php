<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GenreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view genres')->only(['index', 'show']);
        $this->middleware('can:create genres')->only(['create', 'store']);
        $this->middleware('can:edit genres')->only(['edit', 'update']);
        $this->middleware('can:delete genres')->only('destroy');
    }

    public function index(): View
    {
        $genres = Genre::query()->withCount('series')->orderBy('name')->paginate(20);

        return view('admin.genres.index', compact('genres'));
    }

    public function create(): View
    {
        return view('admin.genres.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:genres,name'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:genres,slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        Genre::create([
            'name' => $validated['name'],
            'slug' => $this->resolveUniqueSlug($slug),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.genres.index')->with('success', 'Genero creado.');
    }

    public function show(Genre $genre): View
    {
        return view('admin.genres.show', compact('genre'));
    }

    public function edit(Genre $genre): View
    {
        return view('admin.genres.edit', compact('genre'));
    }

    public function update(Request $request, Genre $genre): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:genres,name,'.$genre->id],
            'slug' => ['nullable', 'string', 'max:120', 'unique:genres,slug,'.$genre->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        $genre->update([
            'name' => $validated['name'],
            'slug' => $this->resolveUniqueSlug($slug, $genre->id),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.genres.index')->with('success', 'Genero actualizado.');
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->series()->exists()) {
            return back()->withErrors(['genre' => 'No puedes eliminar un genero que tiene series asociadas.']);
        }

        $genre->delete();

        return redirect()->route('admin.genres.index')->with('success', 'Genero eliminado.');
    }

    private function resolveUniqueSlug(string $slug, ?int $exceptId = null): string
    {
        $baseSlug = Str::slug($slug);
        $finalSlug = $baseSlug;
        $index = 1;

        while (
            Genre::query()
                ->where('slug', $finalSlug)
                ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $finalSlug = "{$baseSlug}-{$index}";
            $index++;
        }

        return $finalSlug;
    }
}
