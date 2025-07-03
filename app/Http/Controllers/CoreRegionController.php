<?php

namespace App\Http\Controllers;

use App\Models\CoreRegion;
use App\Models\CoreVertical;
use App\Models\CoreBusinessType;
use Illuminate\Http\Request;
use App\Exports\CoreRegionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class CoreRegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $regions = CoreRegion::select(
            'core_region.id',
            'core_business_type.business_type',
            'core_vertical.vertical_name',
            'core_region.region_name',
            'core_region.region_code',
            'core_region.numeric_code',
            'core_region.effective_date',
            'core_region.is_active'
        )
            ->join('core_vertical', 'core_region.vertical_id', '=', 'core_vertical.id')
            ->join('core_business_type', 'core_region.business_type', '=', 'core_business_type.id')

            ->paginate(10);
        $verticals = CoreVertical::where('is_active', 1)->get(['id', 'vertical_name']);
        $business_types = CoreBusinessType::where('is_active', 1)->get(['id', 'business_type']);

        return view('core-regions.index', compact('regions', 'verticals', 'business_types'));
    }

    /**
     * Export regions to Excel.
     */
    public function export()
    {
        return Excel::download(new CoreRegionsExport, 'regions.xlsx');
    }

    /**
     * Fetch regions for DataTable with server-side processing.
     */
    public function getRegionList(Request $request)
    {
        $query = CoreRegion::select(
            'core_region.id',
            'core_business_type.business_type',
            'core_vertical.vertical_name',
            'core_region.region_name',
            'core_region.region_code',
            'core_region.numeric_code',
            'core_region.effective_date',
            'core_region.is_active'
        )
            ->leftjoin('core_vertical', 'core_region.vertical_id', '=', 'core_vertical.id')
            ->leftjoin('core_business_type', 'core_region.business_type', '=', 'core_business_type.id');

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status == '1') {
                $query->where('core_region.is_active', 1);
            } else {
                // Treat status = 0 or NULL as inactive
                $query->where(function ($q) {
                    $q->where('core_region.is_active', 0)
                        ->orWhereNull('core_region.is_active');
                });
            }
        }

        if ($request->filled('vertical_id')) {
            $query->where('core_region.vertical_id', $request->vertical_id);
        }

        if ($request->filled('business_type')) {
            $query->where('core_region.business_type', $request->business_type);
        }

        return DataTables::of($query)
            // ->editColumn('effective_date', function ($region) {
            //     return $region->effective_date ? \Carbon\Carbon::parse($region->effective_date)->format('Y-m-d') : '';
            // })
            ->editColumn('is_active', function ($region) {
                return $region->is_active ? 'Active' : 'Inactive';
            })
            ->make(true);
    }

      public function get_region_by_zone(Request $request)
    {
        $zone_id = $request->zone;
        $regionList = DB::table('core_zone_region_mapping')
            ->leftJoin('core_region', 'core_region.id', '=', 'core_zone_region_mapping.region_id')
            ->where('zone_id', $zone_id)->select(['core_region.id', 'core_region.region_name'])->get();
        return response()->json(array('regionList' => $regionList));
    }
}
