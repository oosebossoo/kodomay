<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Orders;

class AdminController extends Controller
{
    public function test()
    {
        return User::all();
    }

    public function dash()
    {
        return Orders::where('seller_id', 40)->whereBetween('order_date', [date('Y-m-d')."T00:00:00.000Z", date('Y-m-d')."T23:59:59.999Z"])->count();
    }
    public function showUsers()
    {
        return response()->json(User::select('login', 'email', 'credits', 'activate as activated', 'created_at')->get(), 200);
    }

    public function editUser()
    {

    }
    
    public function deleteUser(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        dd($user->delete());
        if($user->delete())
        {
            return response()->json([
                'message' => 'User successfully deleted',
            ], 201);
        }
        return response()->json([
            'message' => 'User not found',
        ], 400);
    }

    public function sendPDF()
    {

    }

    public function getExcel()
    {
        
    }

}
