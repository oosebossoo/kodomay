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
    public function activation(Request $request)
    {
        $isActive = User::where('activate_code', $request->activate_code)->update(['activate' => 1,'activate_code' => ""]);
        
        if($isActive)
            return response()->json([
                'message' => 'User successfully activated',
            ], 201);
        else
        {
            return response()->json([
                'message' => 'Something goes wrong ;-)'
            ], 404);
        }
    }

    public function resetPasswordMail(Request $request)
    {
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
            $message->to($email)->subject('Welcome '.$email);
            $message->from('noreplay@kodo.mat','Kodomat');
        });
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => [
                'required', 
                'min:6',              // musi zawierać co najmniej 6 znaków
                'regex:/[a-z]/',      // musi zawierać jedną małą litere
                'regex:/[A-Z]/',      // musi zawierać jedną dużą litere
                'regex:/[0-9]/',      // musi zawierać jedną cyfre
                'confirmed',
            ],
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        
        $user = User::where('remember_token', $request->token)->update(['password' => bcrypt($request->password), 'remember_token' => ""]);

        return response()->json([
            'message' => 'User successfully reset password',
            'user' => $user
        ], 201);
    }
}
