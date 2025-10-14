<?php

namespace App\Http\Controllers;

use App\Models\CoreCrop;
use App\Models\CoreVertical;
use Illuminate\Http\Request;
use App\Exports\CoreCropsExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class CoreCropController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch filter dropdown data
        $verticals = CoreVertical::where('is_active', 1)->get(['id', 'vertical_name']);

        return view('core-crops.index', compact('verticals'));
    }

    /**
     * Export crops to Excel.
     */
    public function export()
    {
        return Excel::download(new CoreCropsExport, 'crops.xlsx');
    }

    /**
     * Fetch crops for DataTable with server-side processing.
     */
    public function getCropList(Request $request)
{
    $query = CoreCrop::select(
        'core_crop.id',
        'core_vertical.vertical_name',
        'core_crop.crop_name',
        'core_crop.crop_code',
        'core_crop.numeric_code',
        'core_crop.effective_date',
        'core_crop.is_active'
    )
    ->join('core_vertical', 'core_crop.vertical_id', '=', 'core_vertical.id');

    // Apply filters
    if ($request->filled('status')) {
        $query->where('core_crop.is_active', $request->status);
    }

    if ($request->filled('vertical_id')) {
        $query->where('core_crop.vertical_id', $request->vertical_id);
    }

    return DataTables::of($query)
        ->editColumn('effective_date', function ($crop) {
            return $crop->effective_date ? \Carbon\Carbon::parse($crop->effective_date)->format('Y-m-d') : '';
        })
        ->editColumn('is_active', function ($crop) {
            return $crop->is_active ? 'Active' : 'Inactive';
        })
        ->make(true);
}

public function getCropsByVertical($vertical)
{
    try {
        $cropsQuery = CoreCrop::where('is_active', 1)
            ->select('id', 'crop_name')
            ->orderBy('crop_name');

        // If vertical is NOT common (5), filter by vertical_id
        if ($vertical != 5) {
            $cropsQuery->where('vertical_id', $vertical);
        }

        $crops = $cropsQuery->get();

        return response()->json([
            'success' => true,
            'crops' => $crops
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to load crops'
        ], 500);
    }
}
}