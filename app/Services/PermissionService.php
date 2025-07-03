<?php

namespace App\Services;

//use Spatie\Permission\Models\Permission;
use App\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionService
{
    /**
     * Create a permission and assign it to the Admin role.
     *
     * @param string $permissionName
     * @return void
     */
    public function createAndAssignPermission(string $permissionName, string $groupName): void
    {
        // Create the permission with the group name if it doesn't already exist
        $permission = Permission::firstOrCreate(
            ['name' => $permissionName],
            ['group_name' => $groupName]
        );

        // Get the Admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Assign the permission to the Admin role
        if (!$adminRole->hasPermissionTo($permissionName)) {
            $adminRole->givePermissionTo($permissionName);
        }
    }
}
