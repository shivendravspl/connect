<?php

namespace App\Http\Controllers\Vendor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Vendor\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function index()
    {
        $vendor_list = Vendor::all();
        return view('vendor.vendor_list', compact('vendor_list'));
    }

    public function create()
    {
        $data = new Vendor();
        $business_nature_list = DB::table('business_nature')->get();
        $gender_list = DB::table('gender')->get();
        return view('vendor.vendor_form', compact('data', 'business_nature_list', 'gender_list'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'company_name' => 'required',
            'nauture_of_business' => 'required',
            'purpose_of_transaction_with_company' => 'required',
            'companys_address' => 'required',
            'pincode' => 'required',
            'vendor_email_id' => 'required',
            'contact_person_name' => 'required',
            'contact_no' => 'required',
            'payment_terms' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

      Vendor::create($request->all()); 
     return redirect()->route('vendor.index')->with('toast_success', 'Vendor Created Successfully!');
    }

    public function show($id)
    {
        // Your show logic here
    }

    public function edit($id)
    {
        $data = Vendor::findOrFail($id);
        $business_nature_list = DB::table('business_nature')->get();
        $gender_list = DB::table('gender')->get();
        return view('vendor.vendor_form', compact('data', 'business_nature_list', 'gender_list'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'company_name' => 'required',
            'nauture_of_business' => 'required',
            'purpose_of_transaction_with_company' => 'required',
            'companys_address' => 'required',
            'pincode' => 'required',
            'vendor_email_id' => 'required',
            'contact_person_name' => 'required',
            'contact_no' => 'required',
            'payment_terms' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vendor->update($request->all());
     return redirect()->route('vendor.index')->with('toast_success', 'Vendor Updated Successfully!');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('vendor.index')->with('toast_success', 'Vendor Deleted Successfully!');
    }
}
