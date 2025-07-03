<?php

namespace App\Http\Controllers;

use App\Models\CoreZone;
use App\Models\CoreVertical;
use App\Models\CoreBusinessType;
use Illuminate\Http\Request;
use App\Exports\CoreZonesExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class CoreZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch filter dropdown data
        $verticals = CoreVertical::where('is_active', 1)->get(['id', 'vertical_name']);
        $business_types = CoreBusinessType::where('is_active', 1)->get(['id', 'business_type']);

        return view('core-zones.index', compact('verticals', 'business_types'));
    }

    /**
     * Export zones to Excel.
     */
    public function export()
    {
        return Excel::download(new CoreZonesExport, 'zones.xlsx');
    }

    /**
     * Fetch zones for DataTable with server-side processing.
     */
    public function getZoneList(Request $request)
    {
        $query = CoreZone::select(
            'core_zone.id',
            'core_zone.zone_name',
            'core_zone.zone_code',
            'core_zone.numeric_code',
            'core_zone.effective_date',
            'core_zone.is_active',
            'core_vertical.vertical_name',
            'core_business_type.business_type'
        )
        ->join('core_vertical', 'core_zone.vertical_id', '=', 'core_vertical.id')
        ->join('core_business_type', 'core_zone.business_type', '=', 'core_business_type.id');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('core_zone.is_active', $request->status);
        }

        if ($request->filled('vertical_id')) {
            $query->where('core_zone.vertical_id', $request->vertical_id);
        }

        if ($request->filled('business_type')) {
            $query->where('core_zone.business_type', $request->business_type);
        }

        return DataTables::of($query)
            ->editColumn('effective_date', function ($zone) {
                return $zone->effective_date ? \Carbon\Carbon::parse($zone->effective_date)->format('Y-m-d') : '';
            })
            ->editColumn('is_active', function ($zone) {
                return $zone->is_active ? 'Active' : 'Inactive';
            })
            ->make(true);
    }

     public function get_zone_by_bu(Request $request)
    {
        $bu_id = $request->bu;
        $zoneList = DB::table('core_bu_zone_mapping')
        ->leftJoin('core_zone', 'core_zone.id', '=', 'core_bu_zone_mapping.zone_id')
        ->where('business_unit_id', $bu_id)
        ->where('core_bu_zone_mapping.effective_to', null)
        ->get();
        return response()->json(array('zoneList' => $zoneList));
    }
}