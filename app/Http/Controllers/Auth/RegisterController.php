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
            'email' => ['required:phone', 'string', 'email', 'max:255', 'unique:users,email', 'nullable'],
            'phone' => ['required:email', 'string', 'max:15', 'unique:users,phone', 'regex:/^[0-9]{10,15}$/', 'nullable'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'The email field is required.',
            'phone.required' => 'The phone number field is required.',
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
            'type'   => 'vendor',
            'status' => 'P',
        ]);
    }
}
