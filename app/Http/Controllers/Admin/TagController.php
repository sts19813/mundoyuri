<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:edit series']);
    }

    public function index(): View
    {
        $tags = Tag::query()->withCount('series')->orderBy('category')->orderBy('name')->paginate(40);

        return view('admin.tags.index', compact('tags'));
    }

    public function create(): View
    {
        return view('admin.tags.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Tag::query()->create($this->validatedAttributes($request));

        return redirect()->route('admin.tags.index')->with('success', 'Etiqueta creada.');
    }

    public function edit(Tag $tag): View
    {
        return view('admin.tags.edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $tag->update($this->validatedAttributes($request, $tag));

        return redirect()->route('admin.tags.index')->with('success', 'Etiqueta actualizada.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('admin.tags.index')->with('success', 'Etiqueta eliminada.');
    }

    private function validatedAttributes(Request $request, ?Tag $tag = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:tags,name,'.($tag?->id)],
            'slug' => ['nullable', 'string', 'max:120', 'unique:tags,slug,'.($tag?->id)],
            'category' => ['required', 'string', 'max:40'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'name' => $validated['name'],
            'slug' => $this->resolveUniqueSlug($validated['slug'] ?: $validated['name'], $tag?->id),
            'category' => Str::slug($validated['category']),
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function resolveUniqueSlug(string $value, ?int $exceptId = null): string
    {
        $base = Str::slug($value) ?: 'etiqueta';
        $slug = $base;
        $suffix = 2;

        while (Tag::query()->where('slug', $slug)->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
