<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    public function reset(Request $request)
    {
        $validatedData = $request->validate([
            'password' => 'required|confirmed|min:6',
        ]);
        $user = User::find(Auth::id());
        $user->password = Hash::make($validatedData['password']);
        $user->save();

        return redirect()->route('home')->with('success', 'Password updated successfully');
    }

    public function update_other_user_password(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'password' => 'required|confirmed|min:6',
            ],
            [
                'password.required' => 'Password is required',
                'password.confirmed' => 'Password does not match',
                'password.min' => 'Password must be at least 6 characters',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'error' => $validator->errors()->toArray()]);
        } else {
            $user = User::find($request->user_id);
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json(['status' => 200, 'message' => 'Password Updated Successfully.']);
        }
    }
}
