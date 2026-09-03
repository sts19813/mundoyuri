<?php

namespace App\Http\Controllers;

use App\Models\LegacyProfile;
use Illuminate\View\View;

class LegacyProfileController extends Controller
{
    public function index(): View
    {
        $legacyProfiles = LegacyProfile::query()
            ->published()
            ->orderBy('legacy_joined_at')
            ->orderBy('nickname')
            ->paginate(24);

        return view('legacy-profiles.index', compact('legacyProfiles'));
    }

    public function show(LegacyProfile $legacyProfile): View
    {
        abort_unless($legacyProfile->is_published || auth()->user()?->isAdmin(), 404);

        return view('legacy-profiles.show', compact('legacyProfile'));
    }
}
