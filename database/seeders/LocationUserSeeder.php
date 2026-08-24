<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LocationUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define and create all required permissions using Spatie models
        $permissionNames = [
            'view settings',
            'edit settings',
            'create settings',
            'delete settings',
            'auth location'
        ];

        foreach ($permissionNames as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // 2. Define permissions to assign to default roles (excluding 'delete settings' if needed)
        $rolePermissions = ['view settings', 'edit settings', 'create settings','auth location'];

        // 3. Create roles and assign permissions
        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::findOrCreate($roleEnum->value, 'web');
            $role->syncPermissions($rolePermissions);
        }

        // 4. Create or update user
        $user = User::updateOrCreate(
            ['email' => 'locationuser@example.com'],
            [
                'name' => 'GHL Location User',
                'password' => bcrypt('password123'),
                'location_id' => 'loc_ghl_987654321',
            ]
        );

        // 5. Assign Role and Direct Permission
        $user->assignRole(RoleEnum::LOCATION->value);
        $user->givePermissionTo('delete settings');
    }
}