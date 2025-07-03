<?php

namespace App\Http\Controllers;

use App\Models\CoreVariety;
use App\Models\CoreCrop;
use App\Models\CoreCategory;
use Illuminate\Http\Request;
use App\Exports\CoreVarietiesExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class CoreVarietyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch filter dropdown data
        $crops = CoreCrop::where('is_active', 1)->get(['id', 'crop_name']);
        $categories = CoreCategory::where('is_active', 1)->get(['id', 'category_name']);

        return view('core-varieties.index', compact('crops', 'categories'));
    }

    /**
     * Export varieties to Excel.
     */
    public function export()
    {
        return Excel::download(new CoreVarietiesExport, 'varieties.xlsx');
    }

    /**
     * Fetch varieties for DataTable with server-side processing.
     */
    public function getVarietyList(Request $request)
    {
        $query = CoreVariety::select(
            'core_variety.id',
            'core_crop.crop_name',
            'core_variety.variety_name',
            'core_variety.variety_code',
            'core_variety.numeric_code',
            'core_category.category_name',
            'core_variety.is_active'
        )
        ->join('core_crop', 'core_variety.crop_id', '=', 'core_crop.id')
        ->join('core_category', 'core_variety.category_id', '=', 'core_category.id');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('core_variety.is_active', $request->status);
        }

        if ($request->filled('crop_id')) {
            $query->where('core_variety.crop_id', $request->crop_id);
        }

        if ($request->filled('category_id')) {
            $query->where('core_variety.category_id', $request->category_id);
        }

        return DataTables::of($query)
            ->editColumn('is_active', function ($variety) {
                return $variety->is_active ? 'Active' : 'Inactive';
            })
            ->make(true);
    }
}