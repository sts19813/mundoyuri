<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserBlockController extends Controller
{
    public function index(Request $request): View
    {
        return view('profile.blocked-users', [
            'blockedUsers' => $request->user()
                ->blockedUsers()
                ->orderByPivot('created_at', 'desc')
                ->paginate(24),
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $viewer = $request->user();

        abort_if($viewer->is($user), 404);
        abort_unless($user->is_active, 404);

        DB::transaction(function () use ($viewer, $user): void {
            $viewer->blockedUsers()->syncWithoutDetaching([$user->id]);

            DB::table('user_follows')
                ->where(function ($query) use ($viewer, $user): void {
                    $query
                        ->where('follower_id', $viewer->id)
                        ->where('followed_id', $user->id);
                })
                ->orWhere(function ($query) use ($viewer, $user): void {
                    $query
                        ->where('follower_id', $user->id)
                        ->where('followed_id', $viewer->id);
                })
                ->delete();
        });

        return back()->with('success', 'Bloqueaste a '.($user->alias ?: $user->name).'.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $request->user()->blockedUsers()->detach($user->id);

        return back()->with('success', 'Desbloqueaste a '.($user->alias ?: $user->name).'.');
    }
}
