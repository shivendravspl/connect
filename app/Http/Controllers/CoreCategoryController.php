<?php

namespace App\Http\Controllers;

use App\Models\CoreCategory;
use Illuminate\Http\Request;
use App\Exports\CoreCategoriesExport;
use Maatwebsite\Excel\Facades\Excel;

class CoreCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = CoreCategory::select(
            'id',
            'category_name',
            'category_code',
            'numeric_code',
            'effective_date',
            'is_active'
        )->paginate(10);

        return view('core-categories.index', compact('categories'));
    }

    /**
     * Export categories to Excel.
     */
    public function export()
    {
        return Excel::download(new CoreCategoriesExport, 'categories.xlsx');
    }
}