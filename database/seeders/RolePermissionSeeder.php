<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
            'view episodes',
            'create episodes',
            'edit episodes',
            'delete episodes',
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
            'create series',
            'edit series',
            'view episodes',
            'create episodes',
            'edit episodes',
        ]);
        $userRole->syncPermissions([
            'view series',
            'view episodes',
        ]);
    }
}

