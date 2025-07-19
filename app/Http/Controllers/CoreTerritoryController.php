<?php

namespace App\Http\Controllers;

use App\Models\CoreTerritory;
use App\Models\CoreBusinessType;
use Illuminate\Http\Request;
use App\Exports\CoreTerritoriesExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class CoreTerritoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch filter dropdown data
        $business_types = CoreBusinessType::where('is_active', 1)->get(['id', 'business_type']);

        return view('core-territories.index', compact('business_types'));
    }

    /**
     * Export territories to Excel.
     */
    public function export()
    {
        return Excel::download(new CoreTerritoriesExport, 'territories.xlsx');
    }

    /**
     * Fetch territories for DataTable with server-side processing.
     */
    public function getTerritoryList(Request $request)
    {
        $query = CoreTerritory::select(
            'core_territory.id',
            'core_territory.territory_name',
            'core_territory.territory_code',
            'core_territory.numeric_code',
            'core_territory.effective_date',
            'core_territory.is_active',
            'core_business_type.business_type'
        )
        ->join('core_business_type', 'core_territory.business_type', '=', 'core_business_type.id');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('core_territory.is_active', $request->status);
        }

        if ($request->filled('business_type')) {
            $query->where('core_territory.business_type', $request->business_type);
        }

        return DataTables::of($query)
            ->editColumn('effective_date', function ($territory) {
                return $territory->effective_date ? \Carbon\Carbon::parse($territory->effective_date)->format('Y-m-d') : '';
            })
            ->editColumn('is_active', function ($territory) {
                return $territory->is_active ? 'Active' : 'Inactive';
            })
            ->make(true);
    }

       public function getMappingData(Request $request)
    {
        $territoryId = $request->input('territory_id');
        
        $result = DB::select("
            SELECT 
                rtm.region_id,
                r.region_name,
                zrm.zone_id,
                z.zone_name,
                bzm.business_unit_id,
                b.business_unit_name
            FROM 
                core_region_territory_mapping rtm
            JOIN 
                core_region r ON rtm.region_id = r.id
            LEFT JOIN 
                core_zone_region_mapping zrm ON r.id = zrm.region_id
            LEFT JOIN 
                core_zone z ON zrm.zone_id = z.id
            LEFT JOIN 
                core_bu_zone_mapping bzm ON z.id = bzm.zone_id
            LEFT JOIN 
                core_business_unit b ON bzm.business_unit_id = b.id
            WHERE 
                rtm.territory_id = ?
        ", [$territoryId]);

        $data = [
            'regions' => [],
            'zones' => [],
            'businessUnits' => []
        ];

        foreach ($result as $row) {
            if ($row->region_id && !array_key_exists($row->region_id, $data['regions'])) {
                $data['regions'][$row->region_id] = $row->region_name;
            }
            if ($row->zone_id && !array_key_exists($row->zone_id, $data['zones'])) {
                $data['zones'][$row->zone_id] = $row->zone_name;
            }
             if ($row->business_unit_id && !array_key_exists($row->business_unit_id, $data['businessUnits'])) {
                $data['businessUnits'][$row->business_unit_id] = $row->business_unit_name;
            }
        }

        return response()->json($data);
    }

      public function get_territory_by_region(Request $request)
    {
        $region_id = $request->region;
        $territoryList = DB::table('core_region_territory_mapping')
            ->leftJoin('core_territory', 'core_territory.id', '=', 'core_region_territory_mapping.territory_id')
            ->where('region_id', $region_id)->select(['core_territory.id', 'core_territory.territory_name'])
            ->where('core_region_territory_mapping.effective_to', '=', null) // Condition added
            ->get();
        return response()->json(array('territoryList' => $territoryList));
    }
    
}