<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use Validator;
use Auth;

class AccountController extends Controller
{
    public function create()
    {
        return view('login');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'email'   => 'required|email',
            'password'  => 'required|alphaNum'
        ]);

        $user_data = array(
            'email'  => $request->get('email'),
            'password' => $request->get('password')
        );

        if(Auth::attempt($user_data)) {
            return 'Zalogowano';
        } else {
            return 'Zle informacje';
        }
    }

    public function destroy()
    {
        Auth::logout();
        return 'Wylogowano';
    }

    public function activation(Request $request)
    {
        //dd($request->activate_code);
        User::where('activate_code', $request->activate_code)->update(['activate' => 1,'activate_code' => ""]);

        return redirect('/activation_success');
    }
}
