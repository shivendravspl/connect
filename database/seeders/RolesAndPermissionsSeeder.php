<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Define permissions
        $permissions = [
            // Role Permissions
            ['name' => 'add-role', 'group_name' => 'role', 'module' => 'Common'],
            ['name' => 'edit-role', 'group_name' => 'role', 'module' => 'Common'],
            ['name' => 'delete-role', 'group_name' => 'role', 'module' => 'Common'],
            ['name' => 'list-role', 'group_name' => 'role', 'module' => 'Common'],

            // User Access Permissions
            ['name' => 'add-user', 'group_name' => 'User Access', 'module' => 'Common'],
            ['name' => 'edit-user', 'group_name' => 'User Access', 'module' => 'Common'],
            ['name' => 'delete-user', 'group_name' => 'User Access', 'module' => 'Common'],
            ['name' => 'list-user', 'group_name' => 'User Access', 'module' => 'Common'],

            // Location Master Permissions
            ['name' => 'list-zone', 'group_name' => 'Location Master', 'module' => 'Common'],
            ['name' => 'list-region', 'group_name' => 'Location Master', 'module' => 'Common'],
            ['name' => 'list-territory', 'group_name' => 'Location Master', 'module' => 'Common'],

            // Product Master Permissions
            ['name' => 'list-category', 'group_name' => 'Product Master', 'module' => 'Common'],
            ['name' => 'list-crop', 'group_name' => 'Product Master', 'module' => 'Common'],
            ['name' => 'list-variety', 'group_name' => 'Product Master', 'module' => 'Common'],
            ['name' => 'list-vertical', 'group_name' => 'Product Master', 'module' => 'Common'],

            // Business Unit Permission
            ['name' => 'list-business-unit', 'group_name' => 'Business Unit', 'module' => 'Common'],
            // Organization Function Permission
            ['name' => 'list-org-function', 'group_name' => 'Organization Function', 'module' => 'Common'],
            // Companies Permission
            ['name' => 'list-company', 'group_name' => 'Companies', 'module' => 'Common'],
            // Core API Permission
            ['name' => 'list-core-api', 'group_name' => 'Core API', 'module' => 'Common'], // Changed to list-core-api
            
            ['name' => 'add-distributor', 'group_name' => 'Distributor Entry', 'module' => 'Common', 'guard_name' => 'web'],
            ['name' => 'edit-distributor', 'group_name' => 'Distributor Entry', 'module' => 'Common', 'guard_name' => 'web'],
            ['name' => 'delete-distributor', 'group_name' => 'Distributor Entry', 'module' => 'Common', 'guard_name' => 'web'],
            ['name' => 'list-distributor', 'group_name' => 'Distributor Entry', 'module' => 'Common', 'guard_name' => 'web'],
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['group_name' => $permission['group_name'], 'module' => $permission['module'], 'guard_name' => 'web']
            );
        }

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        // Assign all permissions to Super Admin
        $superAdminRole->syncPermissions(Permission::all());

        // Assign permissions to Admin
        $adminPermissions = [
            'add-role',
            'edit-role',
            'delete-role',
            'list-role',
            'add-user',
            'edit-user',
            'delete-user',
            'list-user',
            'list-distributor',
            'list-zone',
            'list-region',
            'list-territory',
            'list-category',
            'list-crop',
            'list-variety',
            'list-vertical',
            'list-business-unit',
            'list-org-function',
            'list-company',
            'list-core-api',
        ];
        $adminRole->syncPermissions($adminPermissions);
    }
}