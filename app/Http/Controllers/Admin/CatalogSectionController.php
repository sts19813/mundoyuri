<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CatalogSectionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(): RedirectResponse
    {
        return redirect()->route('admin.catalog-sections.edit', $this->portalSection());
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.catalog-sections.index');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.catalog-sections.index');
    }

    public function edit(CatalogSection $catalogSection): View|RedirectResponse
    {
        $portalSection = $this->portalSection();

        if ($catalogSection->isNot($portalSection)) {
            return redirect()->route('admin.catalog-sections.edit', $portalSection);
        }

        return view('admin.catalog-sections.edit', compact('portalSection'));
    }

    public function update(Request $request, CatalogSection $catalogSection): RedirectResponse
    {
        $portalSection = $this->portalSection();
        $validated = $this->validated($request);
        $settings = Arr::except($validated, [
            'hero_desktop_image',
            'hero_mobile_image',
            'remove_hero_desktop_image',
            'remove_hero_mobile_image',
        ]);

        $settings['hero_video_on_mobile'] = $request->boolean('hero_video_on_mobile');
        $settings['hero_primary_enabled'] = $request->boolean('hero_primary_enabled');
        $settings['hero_secondary_enabled'] = $request->boolean('hero_secondary_enabled');
        $settings['hero_desktop_image'] = $this->syncHeroImage($request, 'hero_desktop_image', $portalSection->hero_desktop_image);
        $settings['hero_mobile_image'] = $this->syncHeroImage($request, 'hero_mobile_image', $portalSection->hero_mobile_image);

        $portalSection->update($settings);

        return redirect()->route('admin.catalog-sections.edit', $portalSection)->with('success', 'Portada de Mundo Yuri actualizada.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'hero_eyebrow' => ['nullable', 'string', 'max:160'],
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string', 'max:2000'],
            'hero_video_url' => ['nullable', 'url', 'max:2048'],
            'hero_video_on_mobile' => ['nullable', 'boolean'],
            'hero_desktop_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'hero_mobile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_hero_desktop_image' => ['nullable', 'boolean'],
            'remove_hero_mobile_image' => ['nullable', 'boolean'],
            'hero_primary_enabled' => ['nullable', 'boolean'],
            'hero_primary_label' => ['nullable', 'string', 'max:80'],
            'hero_primary_url' => ['nullable', 'string', 'max:2048'],
            'hero_secondary_enabled' => ['nullable', 'boolean'],
            'hero_secondary_label' => ['nullable', 'string', 'max:80'],
            'hero_secondary_url' => ['nullable', 'string', 'max:2048'],
        ]);
    }

    private function portalSection(): CatalogSection
    {
        return CatalogSection::query()->where('slug', 'series-gl')->firstOrFail();
    }

    private function syncHeroImage(Request $request, string $field, ?string $currentPath): ?string
    {
        if ($request->boolean('remove_'.$field)) {
            $this->deleteHeroImage($currentPath);

            return null;
        }

        if (! $request->hasFile($field)) {
            return $currentPath;
        }

        $this->deleteHeroImage($currentPath);

        return $request->file($field)->store('portal-hero-images', 'public');
    }

    private function deleteHeroImage(?string $path): void
    {
        if (filled($path) && str_starts_with($path, 'portal-hero-images/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
