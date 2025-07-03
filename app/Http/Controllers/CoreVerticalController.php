<?php

namespace App\Http\Controllers;

use App\Models\CoreVertical;
use Illuminate\Http\Request;
use App\Exports\CoreVerticalsExport;
use Maatwebsite\Excel\Facades\Excel;

class CoreVerticalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $verticals = CoreVertical::select(
            'id',
            'vertical_name',
            'vertical_code',
            'effective_date',
            'is_active'
        )->paginate(10);

        return view('core-verticals.index', compact('verticals'));
    }

    /**
     * Export verticals to Excel.
     */
    public function export()
    {
        return Excel::download(new CoreVerticalsExport, 'verticals.xlsx');
    }
}