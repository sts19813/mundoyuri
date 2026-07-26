<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserFollowController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->is_active, 404);

        if ($request->user()->is($user)) {
            return back()->with('error', 'No puedes seguir tu propia cuenta.');
        }

        $request->user()->following()->syncWithoutDetaching([$user->id]);

        return back()->with('success', 'Ahora sigues a '.($user->alias ?: $user->name).'.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $request->user()->following()->detach($user->id);

        return back()->with('success', 'Dejaste de seguir a '.($user->alias ?: $user->name).'.');
    }
}
