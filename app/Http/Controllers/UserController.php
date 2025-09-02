<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Permission;




class UserController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $user = Auth::user();
        $employeeId = $user->emp_id;

        return view('users.index', compact('roles'));
    }

    /**
     * Get business units filtered by user permissions
     */
    protected function getFilteredBusinessUnits($user, $employeeId)
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management'])) {
            return DB::table('core_business_unit')
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('business_unit_name', 'id')
                ->prepend('All BU', 'All')
                ->toArray();
        }

        $buId = DB::table('core_employee')
            ->where('employee_id', $employeeId)
            ->where('emp_zone', 0)
            ->value('emp_bu');

        if ($buId > 0) {
            return DB::table('core_business_unit')
                ->where('id', $buId)
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('business_unit_name', 'id')
                ->prepend('Select BU', '')
                ->toArray();
        }

        return [];
    }

    /**
     * Get zones filtered by user permissions
     */
    protected function getFilteredZones($user, $employeeId)
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management'])) {
            return DB::table('core_zone')
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('zone_name', 'id')
                ->prepend('All Zone', 'All')
                ->toArray();
        }

        $zoneId = DB::table('core_employee')
            ->where('employee_id', $employeeId)
            ->where('emp_region', 0)
            ->value('emp_zone');

        if ($zoneId > 0) {
            return DB::table('core_zone')
                ->where('id', $zoneId)
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('zone_name', 'id')
                ->prepend('Select Zone', '')
                ->toArray();
        }

        return [];
    }

    /**
     * Get regions filtered by user permissions
     */
    protected function getFilteredRegions($user, $employeeId)
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management'])) {
            return DB::table('core_region')
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('region_name', 'id')
                ->prepend('All Region', 'All')
                ->toArray();
        }

        $regionId = DB::table('core_employee')
            ->where('employee_id', $employeeId)
            ->where('emp_territory', 0)
            ->value('emp_region');

        if ($regionId > 0) {
            return DB::table('core_region')
                ->where('id', $regionId)
                ->pluck('region_name', 'id')
                ->prepend('Select Region', '')
                ->toArray();
        }

        return [];
    }

    /**
     * Get territories filtered by user permissions
     */
    protected function getFilteredTerritories($user, $employeeId)
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management'])) {
            return DB::table('core_territory')
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('territory_name', 'id')
                ->prepend('All Territory', 'All')
                ->toArray();
        }

        $territoryId = DB::table('core_employee')
            ->where('employee_id', $employeeId)
            ->value('emp_territory');

        if ($territoryId > 0) {
            return DB::table('core_territory')
                ->where('id', $territoryId)
                ->pluck('territory_name', 'id')
                ->toArray();
        }

        return [];
    }

    public function getUserList(Request $request)
    {
        try {
            $users = User::select([
                'users.id',
                'users.name',
                'users.email',
                'users.status',
                'users.phone',
                'users.created_at',
                'users.emp_id',
                DB::raw('GROUP_CONCAT(roles.name) AS roles')
            ])
                ->leftJoin('core_employee', 'users.emp_id', '=', 'core_employee.employee_id')
                ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->groupBy(
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.status',
                    'users.phone',
                    'users.created_at',
                    'users.emp_id',
                );

            return DataTables::of($users)
                ->addColumn('roles', function ($user) {
                    return $user->roles ? explode(',', $user->roles) : [];
                })
                ->addColumn('action', function ($user) {
                    return '
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-info edit-user" data-id="' . $user->id . '" title="Edit">
                            <i class="bx bx-pencil fs-14"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-user" data-id="' . $user->id . '" title="Delete">
                            <i class="bx bx-trash fs-14"></i>
                        </button>
                    </div>
                ';
                })
                ->editColumn('created_at', function ($user) {
                    return $user->created_at ? $user->created_at->format('Y-m-d') : '-';
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $search = strtolower($request->input('search.value'));
                        $query->where(function ($q) use ($search) {
                            $q->whereRaw('LOWER(users.id) LIKE ?', ["%$search%"])
                                ->orWhereRaw('LOWER(users.name) LIKE ?', ["%$search%"])
                                ->orWhereRaw('LOWER(users.email) LIKE ?', ["%$search%"])
                                ->orWhereRaw('LOWER(users.phone) LIKE ?', ["%$search%"])
                                ->orWhereRaw('LOWER(users.created_at) LIKE ?', ["%$search%"]);
                        });
                    }
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in getUserList: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required_without:phone|email|max:255|unique:users,email|nullable',
                'phone' => 'required_without:email|string|max:15|unique:users,phone|regex:/^[0-9]{10,15}$/|nullable',
                'user_status' => 'required|in:A,P,D',
                'password' => 'required|string|min:8|confirmed',
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id',
            ], [
                'email.required_without' => 'Either email or phone is required.',
                'phone.required_without' => 'Either phone or email is required.',
                'phone.regex' => 'The phone number must be a valid number (10-15 digits).',
                'user_status.in' => 'Status must be either Active , Pending or Disabled.',
                'roles.*' => 'One or more selected roles are invalid.',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->user_status,
                'password' => Hash::make($request->password),
            ]);

            // Sync roles using IDs
            $roles = Role::findMany($request->roles);
            $user->syncRoles($roles);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating user.',
            ], 500);
        }
    }

    public function edit(Request $request, User $user)
    {
        $userRoles = $user->roles()->whereIn('id', Role::pluck('id'))->pluck('id')->toArray();
        return response()->json([
            'success' => true,
            'user' => $user,
            'userRoles' => $userRoles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        //dd($request->all());
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required_without:phone|email|max:255|unique:users,email,' . $user->id . '|nullable',
                'phone' => 'required_without:email|string|max:15|unique:users,phone,' . $user->id . '|regex:/^[0-9]{10,15}$/|nullable',
                'user_status' => 'required|in:A,P,D',
                'password' => 'nullable|string|min:8|confirmed',
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id',
            ], [
                'email.required_without' => 'Either email or phone is required.',
                'phone.required_without' => 'Either phone or email is required.',
                'phone.regex' => 'The phone number must be a valid number (10-15 digits).',
                'user_status.in' => 'Status must be either Active , Pending or Disabled.',
                'roles.*' => 'One or more selected roles are invalid.',
            ]);

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->user_status,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);
            // Sync roles using IDs
            $roles = Role::findMany($request->roles);
            $user->syncRoles($roles);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating user.',
            ], 500);
        }
    }

    // Add this method to your UserController
    public function changePassword(Request $request, User $user)
    {
        try {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating password.',
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user.',
            ], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new UsersExport($request), 'UserList.xlsx');
        } catch (\Exception $e) {
            \Log::error('Export error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to export users'], 500);
        }
    }

     public function give_permission($id)
    {
        $permission_list = Permission::orderBy('group_name')->get();
        $grouped_results = $permission_list->mapToGroups(function ($item, $key) {
            return [$item->group_name => ['name' => $item->name, 'id' => $item->id]];
        });
        $permissions = $grouped_results->toArray();

        $userPermissions = DB::table('model_has_permissions')->where('model_id', $id)->pluck('permission_id', 'permission_id')->all();
        $user = User::find($id);
        return view('users.give_permission', compact('permissions', 'userPermissions', 'user'));
    }

     public function set_user_permission(Request $request, $id)
    {
        $user = User::find($id);
        $user->syncPermissions($request->input('permission'));
        return redirect()->back()->with('success', 'Permission Added Successfully.');
    }

}
