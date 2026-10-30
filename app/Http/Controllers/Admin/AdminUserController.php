<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\CommunityRank;
use App\Models\User;
use App\Services\BadgeAutomationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index()
    {
        $users = User::query()
            ->withMax('presenceSessions as last_heartbeat_at', 'last_seen_at')
            ->with(['roles', 'permissions', 'communityRank', 'badges'])
            ->latest()
            ->get();
        $roles = Role::query()->with('permissions')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();
        $communityRanks = $this->assignableCommunityRanks();
        $communityBadges = $this->assignableCommunityBadges();
        $streakUsers = User::query()
            ->orderByDesc('longest_streak_days')
            ->orderByDesc('last_streak_date')
            ->orderBy('name')
            ->get()
            ->each(fn (User $user) => $user->setAttribute('active_streak_days', $user->activeStreakDays()))
            ->sortByDesc('active_streak_days')
            ->values();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'usersPayload' => $users->map(fn (User $user) => $this->userPayload($user))->values(),
            'rolesPayload' => $roles->map(fn (Role $role) => $this->rolePayload($role))->values(),
            'permissionsPayload' => $permissions->map(fn (Permission $permission) => $this->permissionPayload($permission))->values(),
            'communityRanks' => $communityRanks,
            'communityBadges' => $communityBadges,
            'streakUsers' => $streakUsers,
        ]);
    }

    public function create()
    {
        $roles = Role::query()->orderBy('name')->pluck('name');

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request, BadgeAutomationService $automation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => ['required', Rule::exists('roles', 'name')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
            'permissions_present' => ['sometimes', 'boolean'],
            'is_active' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ]);
        $user->assignRole($validated['role']);
        if ($request->boolean('permissions_present') || $request->exists('permissions')) {
            $user->syncPermissions($validated['permissions'] ?? []);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $automation->sync($user);

        $user->load(['roles', 'permissions', 'communityRank', 'badges']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Usuario creado exitosamente.',
                'user' => $this->userPayload($user),
            ], 201);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente');
    }

    public function show(User $user)
    {
        $user->load([
            'communityRank',
            'badges' => fn ($query) => $query->ordered(),
        ]);
        $availableBadges = Badge::query()->active()->ordered()->get();
        $badgeAwarders = User::query()
            ->whereKey($user->badges->pluck('pivot.awarded_by')->filter()->unique())
            ->pluck('name', 'id');

        return view('admin.users.show', compact('user', 'availableBadges', 'badgeAwarders'));
    }

    public function edit(User $user)
    {
        $roles = Role::query()->orderBy('name')->pluck('name');
        $communityRanks = $this->assignableCommunityRanks();
        $communityBadges = $this->assignableCommunityBadges();

        $user->load('badges');

        return view('admin.users.edit', compact('user', 'roles', 'communityRanks', 'communityBadges'));
    }

    public function update(Request $request, User $user, BadgeAutomationService $automation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:8|confirmed',
            'role' => ['required', Rule::exists('roles', 'name')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
            'permissions_present' => ['sometimes', 'boolean'],
            'is_active' => 'nullable|boolean',
            'profile_visibility' => ['sometimes', Rule::in(['public', 'members', 'private'])],
            'community_rank_id' => [
                'nullable',
                'integer',
                Rule::exists('community_ranks', 'id')
                    ->where(fn ($query) => $query->where('is_active', true)->where('is_special', true)),
            ],
            'community_badges' => ['sometimes', 'array'],
            'community_badges.*' => [
                'integer',
                Rule::exists('badges', 'id')->where('is_active', true),
            ],
            'community_badges_present' => ['sometimes', 'boolean'],
            'is_legacy' => ['sometimes', 'boolean'],
            'legacy_joined_at' => ['nullable', 'date'],
            'legacy_source' => ['nullable', 'string', 'max:255'],
            'legacy_notes' => ['nullable', 'string', 'max:2000'],
            'legacy_verified' => ['sometimes', 'boolean'],
            'profile_claimed_at' => ['nullable', 'date'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        foreach (['profile_visibility', 'community_rank_id', 'legacy_joined_at', 'legacy_source', 'legacy_notes', 'profile_claimed_at'] as $field) {
            if ($request->exists($field)) {
                $userData[$field] = $validated[$field] ?? null;
            }
        }

        foreach (['is_legacy', 'legacy_verified'] as $field) {
            if ($request->exists($field)) {
                $userData[$field] = $request->boolean($field);
            }
        }

        $user->update($userData);
        $user->syncRoles($validated['role']);
        if ($request->boolean('permissions_present') || $request->exists('permissions')) {
            $user->syncPermissions($validated['permissions'] ?? []);
        }

        if ($request->boolean('community_badges_present')) {
            $badgeIds = collect($validated['community_badges'] ?? [])->map(fn (int|string $badgeId): int => (int) $badgeId);
            $currentBadgeIds = $user->badges()->pluck('badges.id');

            $badgesToRemove = $user->badges()
                ->whereIn('badges.id', $currentBadgeIds->diff($badgeIds))
                ->where('badges.slug', '!=', 'fundadora')
                ->get();
            $currentBadges = $badgesToRemove;
            $user->badges()->detach($badgesToRemove->pluck('id'));
            $currentBadges->each(fn (Badge $badge) => $automation->recordManual($user, $badge, $request->user(), 'revoked', $badge->pivot->note));

            foreach ($badgeIds->diff($currentBadgeIds) as $badgeId) {
                $badge = Badge::query()->findOrFail($badgeId);
                $user->badges()->attach($badge->id, [
                    'awarded_by' => $request->user()->id,
                    'awarded_at' => now(),
                    'assignment_source' => 'manual',
                ]);
                $automation->recordManual($user, $badge, $request->user(), 'assigned');
            }
        }

        $automation->sync($user);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user->load(['roles', 'permissions', 'communityRank', 'badges']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Usuario actualizado exitosamente.',
                'user' => $this->userPayload($user),
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado exitosamente');
    }

    public function updateEmailNotifications(Request $request, User $user)
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $enabled = (bool) $validated['enabled'];

        $user->update([
            'episode_email_notifications_enabled' => $enabled,
        ]);

        $message = $enabled
            ? 'Las notificaciones por correo quedaron habilitadas para este usuario.'
            : 'Las notificaciones por correo quedaron desactivadas para este usuario.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'email_notifications_enabled' => $enabled,
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroySignature(User $user): RedirectResponse
    {
        $this->authorize('manageSignature', $user);

        if ($user->signature_image) {
            Storage::disk('public')->delete($user->signature_image);
        }

        $user->update([
            'signature_text' => null,
            'signature_image' => null,
            'signature_enabled' => false,
        ]);

        return back()->with('success', 'La firma del usuario fue eliminada.');
    }

    public function updateSignatureSuspension(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manageSignature', $user);

        $validated = $request->validate([
            'signature_suspended_until' => ['nullable', 'date', 'after:now'],
        ]);

        $user->update([
            'signature_suspended_until' => $validated['signature_suspended_until'] ?? null,
        ]);

        return back()->with('success', filled($validated['signature_suspended_until'] ?? null)
            ? 'La firma fue suspendida temporalmente.'
            : 'La suspensión temporal de firma fue retirada.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'No puedes eliminar tu propia cuenta.'], 403);
            }

            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Usuario eliminado exitosamente.']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado exitosamente');
    }

    private function userPayload(User $user): array
    {
        $roleNames = $user->getRoleNames()->values();
        $roleName = $roleNames->first() ?: $user->role ?: 'user';

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $roleName,
            'roles' => $roleNames,
            'permissions' => $user->getDirectPermissions()->pluck('name')->values(),
            'is_active' => (bool) $user->is_active,
            'email_notifications_enabled' => (bool) $user->episode_email_notifications_enabled,
            'last_heartbeat_at' => $user->last_heartbeat_at?->toIso8601String(),
            'last_heartbeat_timestamp' => $user->last_heartbeat_at?->getTimestamp(),
            'created_at' => $user->created_at?->toIso8601String(),
            'created_at_timestamp' => $user->created_at?->getTimestamp(),
            'avatar_url' => $user->avatarUrl(),
            'initials' => $user->initials(),
            'profile_visibility' => $user->profile_visibility,
            'community_rank_id' => $user->community_rank_id,
            'community_rank' => $user->communityRank?->name,
            'community_badges' => $user->badges->pluck('id')->values(),
            'community_badge_labels' => $user->badges
                ->sortByDesc('priority')
                ->map(fn (Badge $badge) => trim(($badge->icon ?: '✦').' '.$badge->name))
                ->values(),
            'is_legacy' => (bool) $user->is_legacy,
            'legacy_joined_at' => $user->legacy_joined_at?->format('Y-m-d'),
            'legacy_source' => $user->legacy_source,
            'legacy_notes' => $user->legacy_notes,
            'legacy_verified' => (bool) $user->legacy_verified,
            'profile_claimed_at' => $user->profile_claimed_at?->format('Y-m-d'),
        ];
    }

    private function assignableCommunityRanks()
    {
        return CommunityRank::query()
            ->active()
            ->special()
            ->orderByDesc('priority')
            ->orderBy('name')
            ->get();
    }

    private function assignableCommunityBadges()
    {
        return Badge::query()
            ->active()
            ->ordered()
            ->get();
    }

    private function rolePayload(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->values(),
            'users_count' => $role->users()->count(),
        ];
    }

    private function permissionPayload(Permission $permission): array
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
        ];
    }
}
