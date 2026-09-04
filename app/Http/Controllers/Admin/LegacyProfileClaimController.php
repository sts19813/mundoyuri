<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewLegacyProfileClaimRequest;
use App\Models\LegacyProfileClaim;
use App\Services\LegacyProfileClaimService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegacyProfileClaimController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LegacyProfileClaim::class);
        $status = $request->string('status')->toString();
        abort_unless($status === '' || in_array($status, LegacyProfileClaim::STATUSES, true), 404);

        $claims = LegacyProfileClaim::query()
            ->with(['legacyProfile', 'claimant', 'reviewer'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.legacy-profile-claims.index', compact('claims', 'status'));
    }

    public function update(ReviewLegacyProfileClaimRequest $request, LegacyProfileClaim $legacyProfileClaim, LegacyProfileClaimService $claims): RedirectResponse
    {
        $claims->review(
            $legacyProfileClaim,
            $request->user(),
            $request->validated('status'),
            $request->validated('admin_notes'),
        );

        return back()->with('success', 'Solicitud de reclamación actualizada.');
    }
}
