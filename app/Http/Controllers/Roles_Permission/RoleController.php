<?php

namespace App\Http\Controllers\Roles_Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

    public function index()
    {
        $roles = Role::where('id', '!=', 1)->orderBy('id', 'DESC')->get();
        // Define custom module order
        $moduleOrder = ['Common', 'Budget', 'COGS', 'Sales'];
        // Retrieve, organize, and sort permissions
        $permissions = Permission::orderBy('group_name')
            ->get()
            ->groupBy('module')
            ->sortBy(
                fn($_, $module) =>
                array_search($module, $moduleOrder) !== false ? array_search($module, $moduleOrder) : PHP_INT_MAX
            )
            ->map(
                fn($moduleItems) =>
                $moduleItems->groupBy('group_name')->map(
                    fn($groupItems) =>
                    $groupItems->map(fn($item) => [
                        'name' => $item->name,
                        'id' => $item->id,
                    ])->toArray()
                )->toArray()
            )->toArray();
            //dd($roles);
        return view('roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'role_name' => 'required|unique:roles,name',
            'permission' => 'required',
        ]);

        $query = Role::create(['name' => $request->input('role_name'), 'status' => 'active']);
        $id = $query->id;
        $role = Role::find($id);
        $role->syncPermissions($request->input('permission'));

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully');
    }

    public function edit($id)
    {
        $role = Role::find($id);
        $results = Permission::orderBy('group_name')->get();
        $grouped_results = $results->mapToGroups(function ($item, $key) {
            return [$item->group_name => ['name' => $item->name, 'id' => $item->id]];
        });
        $permissions = $grouped_results->toArray();

        $rolePermissions = DB::table('role_has_permissions')->where('role_has_permissions.role_id', $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();

        return view('roles.edit', compact('role', 'rolePermissions', 'permissions'));
    }


    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'role_name' => 'required|unique:roles,name,' . $id,
            'permission' => 'required',
        ]);

        $role = Role::find($id);
        $role->name = $request->input('role_name');
        $role->save();
        $role->syncPermissions($request->input('permission'));

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully');
    }


    public function destroy($id)
    {
        $role = Role::find($id);
        $role->delete();

        return response()->json(['status' => 200, 'message' => 'Data Deleted Successfully.']);
    }
}
