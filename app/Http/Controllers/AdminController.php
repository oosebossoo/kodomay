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
        $user_id = 40;

        if(isset($request->m) == 1)
        {
            $m = $request->m;
        }
        else
        {
            $m = (int)date("m");
        }

        for($i = 0; $i < $this->days_in_month($m, (int)date("Y")); $i++)
        {
            if(isset($request->m) == 1)
            {
                $m = $request->m;
            }
            else
            {
                $m = (int)date("m");
            }

            $j = $i;
            $d = $j + 1;
            if($d < 10)
            {
                $d = "0".$d;
            }

            if($m < 10)
            {
                $m = "0".$m;
            }
            
            $date = (int)date("Y")."-".$m."-".$d;
            $data[$date] = Orders::where('seller_id', $user_id)->whereBetween('order_date', [$date."T00:00:00.000Z", $date."T23:59:59.999Z"])->count();

        }
        return $data;
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
