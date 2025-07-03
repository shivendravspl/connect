<?php

namespace App\Http\Controllers\Gender;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Gender\Gender;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GenderController extends Controller
{
    public function index()
    {
        $gender_list = Gender::all();
        return view('gender.gender_list', compact('gender_list'));
    }

    public function create()
    {
        $data = new Gender();
        return view('gender.gender_form', compact('data'));
    }

    public function store(Request $request)
    {
      Gender::create($request->all()); 
     return redirect()->route('gender.index')->with('toast_success', 'Gender Created Successfully!');
    }

    public function show($id)
    {
        // Your show logic here
    }

    public function edit($id)
    {
        $data = Gender::findOrFail($id);
        return view('gender.gender_form', compact('data'));
    }

    public function update(Request $request, Gender $gender)
    {
        $gender->update($request->all());
     return redirect()->route('gender.index')->with('toast_success', 'Gender Updated Successfully!');
    }

    public function destroy(Gender $gender)
    {
        $gender->delete();
        return redirect()->route('gender.index')->with('toast_success', 'Gender Deleted Successfully!');
    }
}
