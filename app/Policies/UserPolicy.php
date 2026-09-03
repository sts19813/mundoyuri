<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewProfile(?User $viewer, User $profileUser): Response
    {
        if (! $profileUser->is_active) {
            return Response::denyAsNotFound();
        }

        if ($viewer?->is($profileUser) || $viewer?->shouldEnterAdminPanel()) {
            return Response::allow();
        }

        return match ($profileUser->profile_visibility) {
            'public' => Response::allow(),
            'members' => $viewer
                ? Response::allow()
                : Response::denyAsNotFound(),
            default => Response::denyAsNotFound(),
        };
    }

    public function viewFavorites(?User $viewer, User $profileUser): Response
    {
        $profileAccess = $this->viewProfile($viewer, $profileUser);

        if ($profileAccess->denied()) {
            return $profileAccess;
        }

        if ($profileUser->show_favorites || $viewer?->is($profileUser) || $viewer?->shouldEnterAdminPanel()) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    public function manageSignature(User $user, User $profileUser): bool
    {
        return $user->isAdmin();
    }
}
