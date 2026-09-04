<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLegacyProfileRequest;
use App\Http\Requests\Admin\UpdateLegacyProfileRequest;
use App\Models\Badge;
use App\Models\LegacyProfile;
use App\Models\User;
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
            ->with(['claimedBy', 'badges'])
            ->latest()
            ->paginate(20);

        return view('admin.legacy-profiles.index', compact('legacyProfiles'));
    }

    public function create(): View
    {
        $this->authorize('manage', LegacyProfile::class);

        return view('admin.legacy-profiles.create', ['badges' => $this->availableBadges()]);
    }

    public function store(StoreLegacyProfileRequest $request): RedirectResponse
    {
        $legacyProfile = LegacyProfile::query()->create([
            ...$request->safe()->except('legacy_avatar'),
            'is_legacy' => true,
        ]);

        if ($request->hasFile('legacy_avatar')) {
            $legacyProfile->update([
                'legacy_avatar_path' => $request->file('legacy_avatar')->store('legacy-avatars', 'public'),
            ]);
        }

        $this->syncBadges($legacyProfile, $request->validated('badge_ids', []), $request->user());

        return redirect()->route('admin.legacy-profiles.index')->with('success', 'Perfil histórico creado correctamente.');
    }

    public function edit(LegacyProfile $legacyProfile): View
    {
        $this->authorize('manage', $legacyProfile);

        $legacyProfile->load('badges');

        return view('admin.legacy-profiles.edit', ['legacyProfile' => $legacyProfile, 'badges' => $this->availableBadges()]);
    }

    public function update(UpdateLegacyProfileRequest $request, LegacyProfile $legacyProfile): RedirectResponse
    {
        $this->authorize('manage', $legacyProfile);

        $legacyProfile->update([
            ...$request->safe()->except(['legacy_avatar', 'legacy_avatar_remove']),
            'is_legacy' => true,
        ]);

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

        $this->syncBadges($legacyProfile, $request->validated('badge_ids', []), $request->user());

        return redirect()->route('admin.legacy-profiles.index')->with('success', 'Perfil histórico actualizado correctamente.');
    }

    private function deleteAvatar(LegacyProfile $legacyProfile): void
    {
        if ($legacyProfile->legacy_avatar_path) {
            Storage::disk('public')->delete($legacyProfile->legacy_avatar_path);
        }
    }

    private function availableBadges()
    {
        return Badge::query()->active()->ordered()->get();
    }

    /** @param array<int, int|string> $badgeIds */
    private function syncBadges(LegacyProfile $legacyProfile, array $badgeIds, User $admin): void
    {
        $badgeIds = array_map('intval', $badgeIds);
        $currentIds = $legacyProfile->badges()->pluck('badges.id')->all();

        $legacyProfile->badges()->detach(array_diff($currentIds, $badgeIds));
        $legacyProfile->badges()->attach(array_diff($badgeIds, $currentIds), [
            'awarded_by' => $admin->id,
            'awarded_at' => now(),
        ]);
    }
}
