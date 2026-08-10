<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'manage taxonomy',
            'moderate comments',
            'manage users',
            'manage roles',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $author = Role::firstOrCreate(['name' => 'author']);
        $moderator = Role::firstOrCreate(['name' => 'moderator']);
        $admin = Role::firstOrCreate(['name' => 'admin']);

        $author->syncPermissions([]);
        $moderator->syncPermissions(['moderate comments']);
        $admin->syncPermissions($permissions);
    }
}
