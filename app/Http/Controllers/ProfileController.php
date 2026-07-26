<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display a user's public profile.
     */
    public function show(User $user, ?string $alias = null): View
    {
        abort_unless($user->is_active, 404);

        $user->loadCount([
            'comments' => fn ($query) => $query->where('is_approved', true),
            'followers' => fn ($query) => $query->where('users.is_active', true),
            'following' => fn ($query) => $query->where('users.is_active', true),
            'favoriteSeries' => fn ($query) => $query
                ->where('moderation_status', 'approved')
                ->whereNotNull('published_at'),
        ]);

        $favoriteSeries = $user->favoriteSeries()
            ->with('genre')
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->orderByPivot('created_at', 'desc')
            ->take(12)
            ->get();
        $viewer = auth()->user();
        $viewerHasBlocked = $viewer !== null
            && ! $viewer->is($user)
            && $viewer->hasBlocked($user);
        $viewerIsBlocked = $viewer !== null
            && ! $viewer->is($user)
            && $viewer->isBlockedBy($user);
        $interactionBlocked = $viewerHasBlocked || $viewerIsBlocked;
        $isFollowing = $viewer !== null
            && ! $viewer->is($user)
            && ! $interactionBlocked
            && $viewer->following()->whereKey($user->id)->exists();

        return view('profile.show', [
            'profileUser' => $user,
            'isOwner' => auth()->id() === $user->id,
            'isFollowing' => $isFollowing,
            'interactionBlocked' => $interactionBlocked,
            'viewerHasBlocked' => $viewerHasBlocked,
            'favoriteSeries' => $favoriteSeries,
        ]);
    }

    public function followers(User $user): View
    {
        return $this->connectionsView($user, 'followers');
    }

    public function following(User $user): View
    {
        return $this->connectionsView($user, 'following');
    }

    public function favorites(User $user): View
    {
        abort_unless($user->is_active, 404);

        $favorites = $user->favoriteSeries()
            ->with('genre')
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->orderByPivot('created_at', 'desc')
            ->paginate(24);

        return view('profile.favorites', [
            'profileUser' => $user,
            'favorites' => $favorites,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'alias' => $validated['alias'] ?? null,
            'email' => $validated['email'],
            'biography' => $validated['biography'] ?? null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->boolean('avatar_remove')) {
            $this->deleteProfileImage($user);
            $user->profile_image = null;
        }

        if ($request->hasFile('profile_image')) {
            $this->deleteProfileImage($user);
            $user->profile_image = $request->file('profile_image')->store('profile-images', 'public');
        }

        if ($request->boolean('cover_remove')) {
            $this->deleteCoverImage($user);
            $user->cover_image = null;
        }

        if ($request->hasFile('cover_image')) {
            $this->deleteCoverImage($user);
            $user->cover_image = $request->file('cover_image')->store('profile-covers', 'public');
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Tu perfil se actualizó correctamente.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $this->deleteProfileImage($user);
        $this->deleteCoverImage($user);
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function deleteProfileImage(User $user): void
    {
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }
    }

    private function deleteCoverImage(User $user): void
    {
        if ($user->cover_image) {
            Storage::disk('public')->delete($user->cover_image);
        }
    }

    private function connectionsView(User $user, string $type): View
    {
        abort_unless($user->is_active, 404);

        $connections = $user->{$type}()
            ->where('users.is_active', true)
            ->orderByPivot('created_at', 'desc')
            ->paginate(24);

        return view('profile.connections', [
            'profileUser' => $user,
            'connections' => $connections,
            'type' => $type,
        ]);
    }
}
