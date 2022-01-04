<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\User;
use Validator;
use Mail;

class AuthController extends Controller
{
    protected $name, $email;

    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['message' => 'Login i/lub hasło są nieprawidłowe'], 401);
        }

        return $this->createNewToken($token);
    }

    public function register(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => [
                'required', 
                'min:6',              // musi zawierać co najmniej 6 znaków
                'regex:/[a-z]/',      // musi zawierać jedną małą litere
                'regex:/[A-Z]/',      // musi zawierać jedną dużą litere
                'regex:/[0-9]/',      // musi zawierać jedną cyfre
                'confirmed',
            ],
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $name = $request->name;
        $email = $request->email;

        $token = bcrypt($name.$email);
        $char = array('/', '.');
        $token = str_replace($char, "", $token);

        $user = User::create(array_merge(
                    $validator->validated(), ['password' => bcrypt($request->password), 'activate_code' => $token]
                ));

        $this->sendActivationEmail($email, $name, $token);

        Notification::create(['user_id' => $user->id]);

        return response()->json([
            'message' => 'User successfully registered',
        ], 201);
    }

    public function logout() 
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    public function refresh() 
    {
        return $this->createNewToken(auth()->refresh());
    }

    public function userProfile() 
    {
        return response()->json(auth()->user());
    }

    protected function createNewToken($token)
    {
        $user = auth()->user();
        $fullname = explode(" ", $user->name);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'expires_in' => auth("api")->factory()->getTTL() * 60,
            'id' => $user->id,
            'first_name' => $fullname[0],
            // 'last_name' => $fullname[1],
            'email'=> $user->email,
        ]);
    }

    protected function sendActivationEmail($email, $name, $token)
    {
        $data = array(
            'url' => "http://localhost:3000/activation:".$token,
            'email' => $email
        );

        Mail::send(['html'=>'activate'], $data, function($message) use ($email, $name) {
            $message->to($email, $name)->subject('Welcome '.$name);
            $message->from('noreplay@kodo.mat','Kodomat');
        });
    }
}
