<?php

namespace App\Http\Controllers;

use App\Models\CoreOrgFunction;
use Illuminate\Http\Request;
use App\Exports\CoreOrgFunctionsExport;
use Maatwebsite\Excel\Facades\Excel;

class CoreOrgFunctionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $functions = CoreOrgFunction::paginate(10);
        return view('core-org-functions.index', compact('functions'));
    }

    public function export()
    {
        return Excel::download(new CoreOrgFunctionsExport, 'org_functions.xlsx');
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
