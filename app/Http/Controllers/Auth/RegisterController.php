<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required_without:phone', 'string', 'email', 'max:255', 'unique:users,email', 'nullable'],
            'phone' => ['required_without:email', 'string', 'max:15', 'unique:users,phone', 'regex:/^[0-9]{10,15}$/', 'nullable'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required_without' => 'Either email or phone is required.',
            'phone.required_without' => 'Either phone or email is required.',
            'phone.regex' => 'The phone number must be a valid number (10-15 digits).',
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
        ]);
    }
}