<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MainController;

use App\Models\User;

use Validator;
use Auth;
use Mail;

class AccountController extends Controller
{
    // public function create()
    // {
    //     if(Auth::check()) {
    //         return redirect('/home');
    //     }
    //     return view('login');
    // }

    // public function store(Request $request)
    // {
    //     if(Auth::check()) {
    //         return redirect('/home');
    //     }
    //     $this->validate($request, [
    //         'email'   => 'required|email',
    //         'password'  => 'required'
    //     ]);

    //     $user_data = array(
    //         'email'  => $request->get('email'),
    //         'password' => $request->get('password')
    //     );

    //     if(Auth::attempt($user_data)) {
    //         return redirect('/home');
    //         if (Auth::check()) {
    //             return ["name" => Auth::user()->name, "email" => Auth::user()->email];
    //         }
    //         return ["status" => "logout", "desc" => "please login"];
    //     } else {
    //         return 'Zle informacje';
    //     }
    // }

    // public function destroy()
    // {
    //     Auth::logout();
    //     return 'Wylogowano';
    // }

    public function activation(Request $request)
    {
        // $name = User::where('activate_code', $request->activate_code)->first();
        // if(isset($name->name))
        // {
            User::where('activate_code', $request->activate_code)->update(['activate' => 1,'activate_code' => ""]);

            return response()->json([
                'message' => 'User successfully activated',
            ], 201);
        // }
        // else
        // {
        //     return response()->json([
        //         'message' => 'Something goes wrong'
        //     ], 404);
        // }
    }

    public function resetPasswordMail(Request $request)
    {
        // return response()->json(['dziala'], 200);
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $email = $request->email;

        User::where('email', $request->email)->update(['remember_token' => bcrypt($request->email.time())]);

        $data = array(
            'url' => "http://localhost:3000/reset:".User::where('email', $request->email)->first()->remember_token,
            'email' => $request->email
        );
        Mail::send(['html'=>'reset'], $data, function($message) use ($email) {
            $message->to($email)->subject
            ('Welcome '.$email);
            $message->from('noreplay@kodo.mat','Kodomat');
        });
    }

    public function resetPassword(Request $request)
    {
        $user = User::where('remember_token', $request->id)->update(['password' => bcrypt($request->password), 'remember_token' => ""]);

        return response()->json([
            'message' => 'User successfully reset password',
            'user' => $user
        ], 201);
    }
}
