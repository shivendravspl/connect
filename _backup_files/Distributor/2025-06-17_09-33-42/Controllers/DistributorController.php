<?php

namespace App\Http\Controllers;

use App\Exports\DistributorsExport;
use App\Models\CoreDistributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class DistributorController extends Controller
{
   public function index(Request $request)
    {
        $distributors = CoreDistributor::leftJoin('core_territory as vc', 'core_distributor.vc_territory', '=', 'vc.id')
            ->leftJoin('core_territory as fc', 'core_distributor.fc_territory', '=', 'fc.id')
            ->leftJoin('core_territory as bulk', 'core_distributor.bulk_territory', '=', 'bulk.id')
            ->select(
                'core_distributor.*',
                'vc.territory_name as vc_territory_name',
                'fc.territory_name as fc_territory_name',
                'bulk.territory_name as bulk_territory_name'
            )
            ->paginate(10);
        $vc_territory_list = DB::table('core_territory')->where('territory_name', 'LIKE', 'VC-%')->get();
        $fc_territory_list = DB::table('core_territory')->where('territory_name', 'LIKE', 'FC-%')->get();
        $all_territory_list = DB::table('core_territory')->get();
        $business_type = DB::table('core_business_type')->where('is_active', '1')->get();
            
        return view('distributor.index', compact('distributors','business_type','vc_territory_list','fc_territory_list'));
    }

    public function getDistributorList(Request $request)
    {
        $query = CoreDistributor::query()
            ->leftJoin('core_territory as vc', 'core_distributor.vc_territory', '=', 'vc.id')
            ->leftJoin('core_territory as fc', 'core_distributor.fc_territory', '=', 'fc.id')
            ->leftJoin('core_territory as bc', 'core_distributor.bulk_territory', '=', 'bc.id')
            ->select([
                'core_distributor.*',
                'vc.territory_name as vc_territory_name',
                'fc.territory_name as fc_territory_name',
                'bc.territory_name as bulk_territory_name'
            ]);

        if ($request->filled('status')) {
            $query->where('core_distributor.status', $request->status);
        }
        if ($request->filled('business_type')) {
            $query->where('core_distributor.business_type', $request->business_type);
        }
        if ($request->filled('vc_territory')) {
            $query->where('core_distributor.vc_territory', $request->vc_territory);
        }
        if ($request->filled('fc_territory')) {
            $query->where('core_distributor.fc_territory', $request->fc_territory);
        }

        return DataTables::of($query)->make(true);
    }

    public function export()
    {
        return Excel::download(new DistributorsExport, 'distributors.xlsx');
    }
}