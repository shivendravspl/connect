<?php

namespace App\Http\Controllers;

use App\Models\CoreBusinessUnit;
use App\Models\CoreVertical;
use Illuminate\Http\Request;
use App\Exports\CoreBusinessUnitsExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class CoreBusinessUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch filter dropdown data
        $verticals = CoreVertical::where('is_active', 1)->get(['id', 'vertical_name']);

        return view('core-business-units.index', compact('verticals'));
    }

    /**
     * Export business units to Excel.
     */
    public function export()
    {
        return Excel::download(new CoreBusinessUnitsExport, 'business_units.xlsx');
    }

    /**
     * Fetch business units for DataTable with server-side processing.
     */
    public function getBusinessUnitList(Request $request)
    {
        $query = CoreBusinessUnit::select(
            'core_business_unit.id',
            'core_business_unit.business_unit_name',
            'core_business_unit.business_unit_code',
            'core_business_unit.numeric_code',
            'core_business_unit.effective_date',
            'core_business_unit.is_active',
            'core_vertical.vertical_name'
        )
        ->join('core_vertical', 'core_business_unit.vertical_id', '=', 'core_vertical.id');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('core_business_unit.is_active', $request->status);
        }

        if ($request->filled('vertical_id')) {
            $query->where('core_business_unit.vertical_id', $request->vertical_id);
        }

        return DataTables::of($query)
            ->editColumn('effective_date', function ($unit) {
                return $unit->effective_date ? \Carbon\Carbon::parse($unit->effective_date)->format('Y-m-d') : '';
            })
            ->editColumn('is_active', function ($unit) {
                return $unit->is_active ? 'Active' : 'Inactive';
            })
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CoreBusinessUnit $coreBusinessUnit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CoreBusinessUnit $coreBusinessUnit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CoreBusinessUnit $coreBusinessUnit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoreBusinessUnit $coreBusinessUnit)
    {
        //
    }
}