<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserBadgeRequest;
use App\Models\Badge;
use App\Models\User;
use App\Services\BadgeAutomationService;
use Illuminate\Http\RedirectResponse;

class UserBadgeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function store(StoreUserBadgeRequest $request, User $user, BadgeAutomationService $automation): RedirectResponse
    {
        $badge = Badge::query()->findOrFail($request->integer('badge_id'));
        $this->authorize('assign', $badge);

        if ($user->badges()->whereKey($badge->id)->exists()) {
            return back()->with('info', 'La persona ya tiene esta insignia.');
        }

        $user->badges()->attach($badge->id, [
            'awarded_by' => $request->user()->id,
            'awarded_at' => now(),
            'note' => $request->validated('note'),
            'assignment_source' => 'manual',
        ]);
        $automation->recordManual($user, $badge, $request->user(), 'assigned', $request->validated('note'));

        return back()->with('success', 'Insignia asignada correctamente.');
    }

    public function destroy(User $user, Badge $badge, BadgeAutomationService $automation): RedirectResponse
    {
        $this->authorize('revoke', $badge);
        if ($badge->slug === 'fundadora') {
            return back()->with('info', 'La insignia de Fundadora está protegida y no puede retirarse desde este panel.');
        }
        $award = $user->badges()->whereKey($badge->id)->first();
        $user->badges()->detach($badge->id);
        if ($award) {
            $automation->recordManual($user, $badge, request()->user(), 'revoked', $award->pivot->note);
        }

        return back()->with('success', 'Insignia retirada correctamente.');
    }
}
