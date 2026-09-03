<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLegacyProfileRequest;
use App\Http\Requests\Admin\UpdateLegacyProfileRequest;
use App\Models\LegacyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LegacyProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(): View
    {
        $this->authorize('manage', LegacyProfile::class);

        $legacyProfiles = LegacyProfile::query()
            ->with('claimedBy')
            ->latest()
            ->paginate(20);

        return view('admin.legacy-profiles.index', compact('legacyProfiles'));
    }

    public function create(): View
    {
        $this->authorize('manage', LegacyProfile::class);

        return view('admin.legacy-profiles.create');
    }

    public function store(StoreLegacyProfileRequest $request): RedirectResponse
    {
        $legacyProfile = LegacyProfile::query()->create($request->safe()->except('legacy_avatar'));

        if ($request->hasFile('legacy_avatar')) {
            $legacyProfile->update([
                'legacy_avatar_path' => $request->file('legacy_avatar')->store('legacy-avatars', 'public'),
            ]);
        }

        return redirect()->route('admin.legacy-profiles.index')->with('success', 'Perfil histórico creado correctamente.');
    }

    public function edit(LegacyProfile $legacyProfile): View
    {
        $this->authorize('manage', $legacyProfile);

        return view('admin.legacy-profiles.edit', compact('legacyProfile'));
    }

    public function update(UpdateLegacyProfileRequest $request, LegacyProfile $legacyProfile): RedirectResponse
    {
        $this->authorize('manage', $legacyProfile);

        $legacyProfile->update($request->safe()->except(['legacy_avatar', 'legacy_avatar_remove']));

        if ($request->boolean('legacy_avatar_remove')) {
            $this->deleteAvatar($legacyProfile);
            $legacyProfile->update(['legacy_avatar_path' => null]);
        }

        if ($request->hasFile('legacy_avatar')) {
            $this->deleteAvatar($legacyProfile);
            $legacyProfile->update([
                'legacy_avatar_path' => $request->file('legacy_avatar')->store('legacy-avatars', 'public'),
            ]);
        }

        return redirect()->route('admin.legacy-profiles.index')->with('success', 'Perfil histórico actualizado correctamente.');
    }

    private function deleteAvatar(LegacyProfile $legacyProfile): void
    {
        if ($legacyProfile->legacy_avatar_path) {
            Storage::disk('public')->delete($legacyProfile->legacy_avatar_path);
        }
    }
}
