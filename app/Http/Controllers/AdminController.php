<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function showUsers()
    {
        return User::all();
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
