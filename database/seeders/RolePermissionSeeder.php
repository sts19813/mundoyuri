<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            'view dashboard',
            'manage users',
            'manage roles',
            'manage permissions',
            'view series',
            'create series',
            'edit series',
            'delete series',
            'view genres',
            'create genres',
            'edit genres',
            'delete genres',
            'view episodes',
            'create episodes',
            'edit episodes',
            'delete episodes',
            'moderate content',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $moderatorRole = Role::firstOrCreate(['name' => 'moderator']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Asignar permisos a roles
        $adminRole->syncPermissions(Permission::all());
        $moderatorRole->syncPermissions([
            'view dashboard',
            'view series',
            'view genres',
            'create genres',
            'edit genres',
            'create series',
            'edit series',
            'view episodes',
            'create episodes',
            'edit episodes',
            'moderate content',
        ]);
        $userRole->syncPermissions([
            'view dashboard',
            'view series',
            'create series',
            'view genres',
            'view episodes',
            'create episodes',
        ]);

        User::query()
            ->whereIn('role', ['admin', 'moderator', 'user'])
            ->each(function (User $user): void {
                if (! $user->hasRole($user->role)) {
                    $user->syncRoles([$user->role]);
                }
            });
    }
}
