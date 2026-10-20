<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\NewFollowerNotification;
use App\Services\BadgeAutomationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserFollowController extends Controller
{
    public function store(Request $request, User $user, BadgeAutomationService $badges): RedirectResponse
    {
        abort_unless($user->is_active, 404);

        if ($request->user()->is($user)) {
            return back()->with('error', 'No puedes seguir tu propia cuenta.');
        }

        if ($request->user()->cannotInteractWith($user)) {
            return back()->with('error', 'No es posible seguir esta cuenta.');
        }

        $changes = $request->user()->following()->syncWithoutDetaching([$user->id]);

        if ($changes['attached'] !== []) {
            $user->notify(new NewFollowerNotification($request->user()));
        }
        $badges->sync($request->user());
        $badges->sync($user);

        return back()->with('success', 'Ahora sigues a '.($user->alias ?: $user->name).'.');
    }

    public function destroy(Request $request, User $user, BadgeAutomationService $badges): RedirectResponse
    {
        $request->user()->following()->detach($user->id);
        $badges->sync($request->user());
        $badges->sync($user);

        return back()->with('success', 'Dejaste de seguir a '.($user->alias ?: $user->name).'.');
    }
}
