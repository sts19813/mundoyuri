<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            ->with(['roles', 'permissions'])
            ->latest()
            ->get();
        $roles = Role::query()->with('permissions')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'usersPayload' => $users->map(fn (User $user) => $this->userPayload($user))->values(),
            'rolesPayload' => $roles->map(fn (Role $role) => $this->rolePayload($role))->values(),
            'permissionsPayload' => $permissions->map(fn (Permission $permission) => $this->permissionPayload($permission))->values(),
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
        $user->syncPermissions($validated['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user->load(['roles', 'permissions']);

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
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::query()->orderBy('name')->pluck('name');
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'role' => ['required', Rule::exists('roles', 'name')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
            'is_active' => 'nullable|boolean',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);
        $user->syncRoles($validated['role']);
        $user->syncPermissions($validated['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user->load(['roles', 'permissions']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Usuario actualizado exitosamente.',
                'user' => $this->userPayload($user),
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado exitosamente');
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
            'created_at' => optional($user->created_at)->format('d/m/Y'),
            'avatar_url' => $user->avatarUrl(),
            'initials' => $user->initials(),
        ];
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
