<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLegacyProfileClaimRequest;
use App\Models\LegacyProfile;
use App\Services\LegacyProfileClaimService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegacyProfileClaimController extends Controller
{
    public function create(Request $request): View
    {
        $selectedProfile = LegacyProfile::query()
            ->published()
            ->whereIn('claim_status', ['unclaimed', 'rejected'])
            ->find($request->integer('profile'));

        $legacyProfiles = LegacyProfile::query()
            ->published()
            ->whereIn('claim_status', ['unclaimed', 'rejected'])
            ->orderBy('nickname')
            ->get(['id', 'nickname', 'legacy_joined_at']);

        return view('legacy-profile-claims.create', compact('legacyProfiles', 'selectedProfile'));
    }

    public function store(StoreLegacyProfileClaimRequest $request, LegacyProfileClaimService $claims): RedirectResponse
    {
        $legacyProfile = LegacyProfile::query()->findOrFail($request->integer('legacy_profile_id'));

        $claims->submit(
            $legacyProfile,
            $request->user(),
            $request->validated('message'),
            $request->validated('evidence'),
        );

        return redirect()->route('legacy-profiles.show', $legacyProfile)
            ->with('success', 'Tu solicitud fue enviada. El equipo la revisará manualmente.');
    }
}
