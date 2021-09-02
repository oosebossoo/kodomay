<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
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
            return response()->json(['error' => 'Unauthorized'], 401);
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

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $name = $request->name;
        $email = $request->email;
        $token = bcrypt($name.$email);

        $user = User::create(array_merge(
                    $validator->validated(), ['password' => bcrypt($request->password), 'activate_code' => $token]
                ));

        $this->sendActivationEmail($email, $name, $token);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user->id
        ], 201);
    }

    public function resetPassword(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        dd($validator->email);
        $user = User::where('email', $validator->email)(array_merge(
                    $validator->validated(), ['password' => bcrypt($request->password)]
                ));

        return response()->json([
            'message' => 'User successfully reset password',
            'user' => $user
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
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'expires_in' => auth("api")->factory()->getTTL() * 60,
            'user' => auth()->user()
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
