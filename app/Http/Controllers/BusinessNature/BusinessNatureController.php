<?php

namespace App\Http\Controllers\BusinessNature;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BusinessNature\BusinessNature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BusinessNatureController extends Controller
{
    public function index()
    {
        $business_nature_list = BusinessNature::all();
        return view('business_nature.business_nature_list', compact('business_nature_list'));
    }

    public function create()
    {
        $data = new BusinessNature();
        return view('business_nature.business_nature_form', compact('data'));
    }

    public function store(Request $request)
    {
      BusinessNature::create($request->all()); 
     return redirect()->route('business_nature.index')->with('toast_success', 'BusinessNature Created Successfully!');
    }

    public function show($id)
    {
        // Your show logic here
    }

    public function edit($id)
    {
        $data = BusinessNature::findOrFail($id);
        return view('business_nature.business_nature_form', compact('data'));
    }

    public function update(Request $request, BusinessNature $business_nature)
    {
        $business_nature->update($request->all());
     return redirect()->route('business_nature.index')->with('toast_success', 'BusinessNature Updated Successfully!');
    }

    public function destroy(BusinessNature $business_nature)
    {
        $business_nature->delete();
        return redirect()->route('business_nature.index')->with('toast_success', 'BusinessNature Deleted Successfully!');
    }
}
