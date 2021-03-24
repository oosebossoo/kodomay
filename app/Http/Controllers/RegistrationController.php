<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;

use App\Models\User;

use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    public function create() 
    {
        return view('register');
    }

    public function store(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required'
        ]);

        $activateCode= Hash::make($request->email);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'activate_code' => $activateCode
        ]);

        auth()->login($user);
        return redirect('/register/activate');
    }
}
