<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AdminPermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        return redirect()->route('admin.users.index', ['tab' => 'permissions']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:permissions,name'],
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Permiso creado correctamente.',
                'permission' => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ],
            ], 201);
        }

        return redirect()
            ->route('admin.users.index', ['tab' => 'permissions'])
            ->with('success', 'Permiso creado correctamente.');
    }

    public function destroy(Request $request, Permission $permission)
    {
        $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Permiso eliminado.']);
        }

        return redirect()
            ->route('admin.users.index', ['tab' => 'permissions'])
            ->with('success', 'Permiso eliminado.');
    }
}
