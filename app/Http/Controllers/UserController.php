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


class UserController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $user = Auth::user();
        $employeeId = $user->emp_id;

        // Get crop vertical based on employee
        $employeeVertical = Employee::where('id', $employeeId)->value('emp_vertical');
        $crop_type = [];

        if ($employeeVertical == '2') {
            $crop_type = ['2' => 'Veg Crop'];
        } elseif ($employeeVertical == '1') {
            $crop_type = ['1' => 'Field Crop'];
        }

        // Get filtered lists based on user role
        $bu_list = $this->getFilteredBusinessUnits($user, $employeeId);
        $zone_list = $this->getFilteredZones($user, $employeeId);
        $region_list = $this->getFilteredRegions($user, $employeeId);
        $territory_list = $this->getFilteredTerritories($user, $employeeId);

        return view('users.index', compact(
            'roles',
            'territory_list',
            'region_list',
            'zone_list',
            'crop_type',
            'bu_list'
        ));
    }

    // Helper methods moved to controller (or better, to a dedicated service class)

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
            ->where('id', $employeeId)
            ->where('zone', 0)
            ->value('bu');

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
            ->where('id', $employeeId)
            ->where('region', 0)
            ->value('zone');

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
            ->where('id', $employeeId)
            ->where('territory', 0)
            ->value('region');

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
            ->where('id', $employeeId)
            ->value('territory');

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
                'users.phone',
                'users.created_at',
                'users.emp_id',
                DB::raw('COALESCE(core_territory.territory_name, "-") AS territory_name'),
                DB::raw('COALESCE(core_region.region_name, "-") AS region_name'),
                DB::raw('COALESCE(core_zone.zone_name, "-") AS zone_name'),
                DB::raw('CASE 
                    WHEN core_employee.emp_vertical = 1 THEN "Field Crop" 
                    WHEN core_employee.emp_vertical = 2 THEN "Veg Crop" 
                    ELSE "-" 
                END AS crop_vertical_name'),
                DB::raw('GROUP_CONCAT(roles.name) AS roles')
            ])
                ->leftJoin('core_employee', 'users.emp_id', '=', 'core_employee.id')
                ->leftJoin('core_territory', 'core_employee.territory', '=', 'core_territory.id')
                ->leftJoin('core_region', 'core_employee.region', '=', 'core_region.id')
                ->leftJoin('core_zone', 'core_employee.zone', '=', 'core_zone.id')
                ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->groupBy(
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.phone',
                    'users.created_at',
                    'users.emp_id',
                    'core_territory.territory_name',
                    'core_region.region_name',
                    'core_zone.zone_name',
                    'core_employee.emp_vertical'
                );

            // Apply filters
            if ($request->has('bu_id') && $request->bu_id && $request->bu_id !== 'All') {
                $users->where('core_employee.bu', $request->bu_id);
            }

            if ($request->has('territory_id') && $request->territory_id) {
                $users->where('core_employee.territory', $request->territory_id);
            }

            if ($request->has('region_id') && $request->region_id) {
                $users->where('core_employee.region', $request->region_id);
            }

            if ($request->has('zone_id') && $request->zone_id) {
                $users->where('core_employee.zone', $request->zone_id);
            }

            if ($request->has('crop_vertical') && $request->crop_vertical) {
                $users->where('core_employee.emp_vertical', $request->crop_vertical);
            }

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
                                ->orWhereRaw('LOWER(users.created_at) LIKE ?', ["%$search%"])
                                ->orWhereRaw('LOWER(core_territory.territory_name) LIKE ?', ["%$search%"])
                                ->orWhereRaw('LOWER(core_region.region_name) LIKE ?', ["%$search%"])
                                ->orWhereRaw('LOWER(core_zone.zone_name) LIKE ?', ["%$search%"])
                                ->orWhereRaw('core_employee.emp_vertical IN (1, 2) AND (
                              (core_employee.emp_vertical = 1 AND LOWER(?) LIKE ?) OR 
                              (core_employee.emp_vertical = 2 AND LOWER(?) LIKE ?)
                          )', ['Field Crop', "%$search%", 'Veg Crop', "%$search%"]);
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
                'password' => 'required|string|min:8|confirmed',
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id',
            ], [
                'email.required_without' => 'Either email or phone is required.',
                'phone.required_without' => 'Either phone or email is required.',
                'phone.regex' => 'The phone number must be a valid number (10-15 digits).',
                'roles.*' => 'One or more selected roles are invalid.',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
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
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required_without:phone|email|max:255|unique:users,email,' . $user->id . '|nullable',
                'phone' => 'required_without:email|string|max:15|unique:users,phone,' . $user->id . '|regex:/^[0-9]{10,15}$/|nullable',
                'password' => 'nullable|string|min:8|confirmed',
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id',
            ], [
                'email.required_without' => 'Either email or phone is required.',
                'phone.required_without' => 'Either phone or email is required.',
                'phone.regex' => 'The phone number must be a valid number (10-15 digits).',
                'roles.*' => 'One or more selected roles are invalid.',
            ]);

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
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
}
