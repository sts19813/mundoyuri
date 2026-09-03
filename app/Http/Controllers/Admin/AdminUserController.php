<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityBadge;
use App\Models\CommunityRank;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            ->with(['roles', 'permissions', 'communityRank', 'communityBadges'])
            ->latest()
            ->get();
        $roles = Role::query()->with('permissions')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();
        $communityRanks = $this->assignableCommunityRanks();
        $communityBadges = $this->assignableCommunityBadges();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'usersPayload' => $users->map(fn (User $user) => $this->userPayload($user))->values(),
            'rolesPayload' => $roles->map(fn (Role $role) => $this->rolePayload($role))->values(),
            'permissionsPayload' => $permissions->map(fn (Permission $permission) => $this->permissionPayload($permission))->values(),
            'communityRanks' => $communityRanks,
            'communityBadges' => $communityBadges,
        ]);
    }

    public function create()
    {
        $roles = Role::query()->orderBy('name')->pluck('name');

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
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

        $user->load(['roles', 'permissions', 'communityRank', 'communityBadges']);

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
        $user->load(['communityRank', 'communityBadges']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::query()->orderBy('name')->pluck('name');
        $communityRanks = $this->assignableCommunityRanks();
        $communityBadges = $this->assignableCommunityBadges();

        $user->load('communityBadges');

        return view('admin.users.edit', compact('user', 'roles', 'communityRanks', 'communityBadges'));
    }

    public function update(Request $request, User $user)
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
                Rule::exists('community_badges', 'id')->where('is_active', true),
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
            $changes = $user->communityBadges()->sync($badgeIds);

            foreach ($changes['attached'] as $badgeId) {
                $user->communityBadges()->updateExistingPivot($badgeId, [
                    'awarded_by' => $request->user()->id,
                    'awarded_at' => now(),
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user->load(['roles', 'permissions', 'communityRank', 'communityBadges']);

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
            'last_login_at' => $user->last_login_at?->format('d/m/Y H:i'),
            'created_at' => optional($user->created_at)->format('d/m/Y'),
            'avatar_url' => $user->avatarUrl(),
            'initials' => $user->initials(),
            'profile_visibility' => $user->profile_visibility,
            'community_rank_id' => $user->community_rank_id,
            'community_rank' => $user->communityRank?->name,
            'community_badges' => $user->communityBadges->pluck('id')->values(),
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
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function assignableCommunityBadges()
    {
        return CommunityBadge::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
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
