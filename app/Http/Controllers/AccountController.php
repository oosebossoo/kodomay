<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MainController;

use App\Models\User;

use Validator;
use Auth;

class AccountController extends Controller
{
    public function create()
    {
        if(Auth::check()) {
            return redirect('/home');
        }
        return view('login');
    }

    public function store(Request $request)
    {
        if(Auth::check()) {
            return redirect('/home');
        }
        $this->validate($request, [
            'email'   => 'required|email',
            'password'  => 'required'
        ]);

        $user_data = array(
            'email'  => $request->get('email'),
            'password' => $request->get('password')
        );

        if(Auth::attempt($user_data)) {
            return redirect('/home');
            if (Auth::check()) {
                return ["name" => Auth::user()->name, "email" => Auth::user()->email];
            }
            return ["status" => "logout", "desc" => "please login"];
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
        User::where('activate_code', $request->activate_code)->update(['activate' => 1,'activate_code' => ""]);

        return redirect('/activation_success');
    }
}
