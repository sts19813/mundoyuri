<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogSectionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(): View
    {
        $sections = CatalogSection::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.catalog-sections.index', compact('sections'));
    }

    public function create(): View
    {
        return view('admin.catalog-sections.create');
    }

    public function store(Request $request): RedirectResponse
    {
        CatalogSection::create($this->validated($request));

        return redirect()->route('admin.catalog-sections.index')->with('success', 'Sección creada.');
    }

    public function edit(CatalogSection $catalogSection): View
    {
        return view('admin.catalog-sections.edit', compact('catalogSection'));
    }

    public function update(Request $request, CatalogSection $catalogSection): RedirectResponse
    {
        $catalogSection->update($this->validated($request, $catalogSection));

        return redirect()->route('admin.catalog-sections.index')->with('success', 'Sección actualizada.');
    }

    private function validated(Request $request, ?CatalogSection $catalogSection = null): array
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('catalog_sections', 'slug')->ignore($catalogSection)],
            'name' => ['required', 'string', 'max:120'],
            'label' => ['nullable', 'string', 'max:100'],
            'hero_eyebrow' => ['nullable', 'string', 'max:160'],
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string', 'max:2000'],
            'hero_video_url' => ['nullable', 'url', 'max:2048'],
            'hero_primary_label' => ['nullable', 'string', 'max:80'],
            'hero_secondary_label' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        return $validated;
    }
}
