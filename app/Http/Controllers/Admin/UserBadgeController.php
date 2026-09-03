<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserBadgeRequest;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserBadgeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function store(StoreUserBadgeRequest $request, User $user): RedirectResponse
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
        ]);

        return back()->with('success', 'Insignia asignada correctamente.');
    }

    public function destroy(User $user, Badge $badge): RedirectResponse
    {
        $this->authorize('revoke', $badge);
        $user->badges()->detach($badge->id);

        return back()->with('success', 'Insignia retirada correctamente.');
    }
}
