<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\CommunityRankResolver;
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
    public function show(User $user, CommunityRankResolver $rankResolver, ?string $alias = null): View
    {
        $this->authorize('viewProfile', $user);

        $user->load([
            'communityRank',
            'badges' => fn ($query) => $query->active()->ordered(),
        ]);

        $user->loadCount([
            'comments' => fn ($query) => $query->where('is_approved', true),
            'followers' => fn ($query) => $query->where('users.is_active', true),
            'following' => fn ($query) => $query->where('users.is_active', true),
            'favoriteSeries' => fn ($query) => $query
                ->where('moderation_status', 'approved')
                ->whereNotNull('published_at'),
            'forumQuestions',
            'forumAnswers',
            'acceptedForumAnswers',
        ]);

        $viewer = auth()->user();
        $isOwner = $viewer?->is($user) ?? false;
        $viewerIsStaff = $viewer?->shouldEnterAdminPanel() ?? false;
        $canViewFavorites = $user->show_favorites || $isOwner || $viewerIsStaff;
        $canViewActivity = $user->show_activity || $isOwner || $viewerIsStaff;
        $favoriteSeries = $canViewFavorites
            ? $user->favoriteSeries()
                ->with('genre')
                ->where('moderation_status', 'approved')
                ->whereNotNull('published_at')
                ->orderByPivot('created_at', 'desc')
                ->take(12)
                ->get()
            : collect();
        $recentActivity = $canViewActivity
            ? $user->comments()
                ->with('commentable')
                ->where('is_approved', true)
                ->latest()
                ->take(6)
                ->get()
            : collect();
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
            'isOwner' => $isOwner,
            'isFollowing' => $isFollowing,
            'interactionBlocked' => $interactionBlocked,
            'viewerHasBlocked' => $viewerHasBlocked,
            'favoriteSeries' => $favoriteSeries,
            'recentActivity' => $recentActivity,
            'canViewFavorites' => $canViewFavorites,
            'canViewActivity' => $canViewActivity,
            'communityRank' => $rankResolver->resolve($user),
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
        $this->authorize('viewFavorites', $user);

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

        foreach (['location', 'website', 'occupation', 'interests'] as $field) {
            if ($request->exists($field)) {
                $user->{$field} = $validated[$field] ?? null;
            }
        }

        if ($request->exists('signature_text') && ! $user->signatureIsSuspended()) {
            $user->signature_text = $this->sanitizeSignatureText($validated['signature_text'] ?? null);
        }

        foreach (['profile_visibility', 'show_last_seen', 'show_join_date', 'show_favorites', 'show_activity', 'signature_enabled', 'show_signatures'] as $field) {
            if ($request->exists($field)) {
                $user->{$field} = str_starts_with($field, 'show_')
                    ? $request->boolean($field)
                    : $validated[$field];
            }
        }

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

        if ($request->boolean('signature_remove')) {
            $this->deleteSignatureImage($user);
            $user->signature_image = null;
        }

        if ($request->hasFile('signature_image')) {
            $this->deleteSignatureImage($user);
            $user->signature_image = $request->file('signature_image')->store('profile-signatures', 'public');
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
        $this->deleteSignatureImage($user);
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

    private function deleteSignatureImage(User $user): void
    {
        if ($user->signature_image) {
            Storage::disk('public')->delete($user->signature_image);
        }
    }

    private function sanitizeSignatureText(?string $signature): ?string
    {
        if ($signature === null) {
            return null;
        }

        $signature = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $signature) ?? '';
        $signature = strip_tags($signature);
        $signature = preg_replace('/[^\P{C}\n\t]/u', '', $signature) ?? '';
        $signature = trim($signature);

        return $signature === '' ? null : $signature;
    }

    private function connectionsView(User $user, string $type): View
    {
        $this->authorize('viewProfile', $user);

        $connections = $user->{$type}()
            ->visibleToProfileViewer(auth()->user())
            ->orderByPivot('created_at', 'desc')
            ->paginate(24);

        return view('profile.connections', [
            'profileUser' => $user,
            'connections' => $connections,
            'type' => $type,
        ]);
    }
}
