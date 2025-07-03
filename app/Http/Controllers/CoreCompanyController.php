<?php

namespace App\Http\Controllers;

use App\Models\CoreCompany;
use Illuminate\Http\Request;
use App\Exports\CoreCompaniesExport;
use Maatwebsite\Excel\Facades\Excel;

class CoreCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = CoreCompany::select('id','company_name','company_code','registration_number','tin_number','gst_number','legal_entity_type','website','email','groups_of_company')->paginate(10);
        return view('core-companies.index',compact('companies'));
    }

    /**
     * Export companies to Excel.
     */
    public function export()
    {
        return Excel::download(new CoreCompaniesExport, 'companies.xlsx');
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
