<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        return redirect()->route('admin.users.index', ['tab' => 'roles']);
    }

    public function create(): View
    {
        $permissions = Permission::query()->orderBy('name')->get();

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($validated['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role->load('permissions');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Rol creado correctamente.',
                'role' => $this->rolePayload($role),
            ], 201);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Rol creado correctamente.');
    }

    public function show(Role $role): View
    {
        $role->load('permissions', 'users');

        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::query()->orderBy('name')->get();
        $role->load('permissions');

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $previousName = $role->name;
        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($previousName !== $role->name) {
            User::query()
                ->where('role', $previousName)
                ->update(['role' => $role->name]);
        }

        $role->load('permissions');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Rol actualizado.',
                'role' => $this->rolePayload($role),
            ]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado.');
    }

    public function destroy(Request $request, Role $role)
    {
        if ($role->name === 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No puedes eliminar el rol admin.'], 422);
            }

            return back()->withErrors(['role' => 'No puedes eliminar el rol admin.']);
        }

        if ($role->users()->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No puedes eliminar un rol asignado a usuarios.'], 422);
            }

            return back()->withErrors(['role' => 'No puedes eliminar un rol asignado a usuarios.']);
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Rol eliminado.']);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado.');
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
}
