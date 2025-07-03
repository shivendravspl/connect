<?php

namespace App\Http\Controllers\UserRegistration;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserRegistration\UserRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserRegistrationController extends Controller
{
    public function index()
    {
        $user_registration_list = UserRegistration::all();
        return view('user_registration.user_registration_list', compact('user_registration_list'));
    }

    public function create()
    {
        $data = new UserRegistration();
        return view('user_registration.user_registration_form', compact('data'));
    }

    public function store(Request $request)
    {
      UserRegistration::create($request->all()); 
     return redirect()->route('user_registration.index')->with('toast_success', 'UserRegistration Created Successfully!');
    }

    public function show($id)
    {
        // Your show logic here
    }

    public function edit($id)
    {
        $data = UserRegistration::findOrFail($id);
        return view('user_registration.user_registration_form', compact('data'));
    }

    public function update(Request $request, UserRegistration $user_registration)
    {
        $user_registration->update($request->all());
     return redirect()->route('user_registration.index')->with('toast_success', 'UserRegistration Updated Successfully!');
    }

    public function destroy(UserRegistration $user_registration)
    {
        $user_registration->delete();
        return redirect()->route('user_registration.index')->with('toast_success', 'UserRegistration Deleted Successfully!');
    }
}
