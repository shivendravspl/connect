<?php

namespace App\Http\Controllers\LegalStatus;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LegalStatus\LegalStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LegalStatusController extends Controller
{
    public function index()
    {
        $legal_status_list = LegalStatus::all();
        return view('legal_status.legal_status_list', compact('legal_status_list'));
    }

    public function create()
    {
        $data = new LegalStatus();
        return view('legal_status.legal_status_form', compact('data'));
    }

    public function store(Request $request)
    {
      LegalStatus::create($request->all()); 
     return redirect()->route('legal_status.index')->with('toast_success', 'LegalStatus Created Successfully!');
    }

    public function show($id)
    {
        // Your show logic here
    }

    public function edit($id)
    {
        $data = LegalStatus::findOrFail($id);
        return view('legal_status.legal_status_form', compact('data'));
    }

    public function update(Request $request, LegalStatus $legal_status)
    {
        $legal_status->update($request->all());
     return redirect()->route('legal_status.index')->with('toast_success', 'LegalStatus Updated Successfully!');
    }

    public function destroy(LegalStatus $legal_status)
    {
        $legal_status->delete();
        return redirect()->route('legal_status.index')->with('toast_success', 'LegalStatus Deleted Successfully!');
    }
}
