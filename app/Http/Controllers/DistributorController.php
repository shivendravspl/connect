<?php

namespace App\Http\Controllers;

use App\Exports\DistributorsExport;
use App\Models\CoreDistributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Employee;
use App\Models\CoreState;

class DistributorController extends Controller
{
    public function index()
    {
        $employee_list = Employee::where('department', 15)->where('emp_status', 'A')->select(['id', 'emp_name', 'emp_code'])->get();
        $vc_employee_list = Employee::where('department', 15)
            ->where('emp_status', 'A')
            ->where('emp_vertical', 2)
            ->select(['id', 'emp_name', 'emp_code'])
            ->get();

        $fc_employee_list = Employee::where('department', 15)
            ->where('emp_status', 'A')
            ->where('emp_vertical', 1)
            ->select(['id', 'emp_name', 'emp_code'])
            ->get();

        $vc_territory_list = DB::table('core_territory')->where('territory_name', 'LIKE', 'VC-%')->get();
        $fc_territory_list = DB::table('core_territory')->where('territory_name', 'LIKE', 'FC-%')->get();
        $all_territory_list = DB::table('core_territory')->get();
        $state_list = CoreState::where('country_id', 1)->orderBy('state_name')->get();
        $business_type = DB::table('core_business_type')->where('is_active', '1')->get();
        return view('distributor.index', compact('employee_list', 'vc_territory_list', 'fc_territory_list', 'all_territory_list', 'state_list', 'vc_employee_list', 'fc_employee_list', 'business_type'));
    }
    public function getDistributorList(Request $request)
    {
        $userQuery = CoreDistributor::query();

          if ($request->filled('status')) {
            $userQuery->where('core_distributor.status', $request->status);
        }
        if ($request->filled('business_type')) {
            $userQuery->where('core_distributor.business_type', $request->business_type);
        }
        if ($request->filled('vc_territory')) {
            $userQuery->where('core_distributor.vc_territory', $request->vc_territory);
        }
        if ($request->filled('fc_territory')) {
            $userQuery->where('core_distributor.fc_territory', $request->fc_territory);
        }
         if ($request->filled('bulk_party')) {
            $userQuery->where('core_distributor.bulk_party', $request->bulk_party);
        }
        //dd($request->employee);
        if ($request->filled('employee')) {
            $userQuery->where(function ($q) use ($request) {
                $q->where('core_distributor.vc_emp', $request->employee)
                    ->orWhere('core_distributor.fc_emp', $request->employee);
            });
        }
        $query = $userQuery
            ->leftJoin('core_territory as vc', 'core_distributor.vc_territory', '=', 'vc.id')
            ->leftJoin('core_territory as fc', 'core_distributor.fc_territory', '=', 'fc.id')
            ->leftJoin('core_territory as bc', 'core_distributor.bulk_territory', '=', 'bc.id')
            ->leftJoin('core_employee as e1', 'core_distributor.vc_emp', '=', 'e1.id')
            ->leftJoin('core_employee as e2', 'core_distributor.fc_emp', '=', 'e2.id')
            ->leftJoin('core_employee as e3', 'core_distributor.bulk_emp', '=', 'e3.id')
            ->leftJoin('core_business_type as b', 'core_distributor.business_type', '=', 'b.id')
            ->select([
                'core_distributor.*',
                'vc.territory_name as vc_territory_name',
                'fc.territory_name as fc_territory_name',
                'bc.territory_name as bulk_territory_name',
                DB::raw("CONCAT(e1.emp_name, ' - VC') as vc_emp"),
                DB::raw("CONCAT(e2.emp_name, ' - FC') as fc_emp"),
                DB::raw("CONCAT(e3.emp_name, ' - Bulk') as bulk_emp"),
                'b.business_type',
            ]);

        return DataTables::of($query)->make(true);
    }

    public function export()
    {
        return Excel::download(new DistributorsExport, 'distributors.xlsx');
    }
}
