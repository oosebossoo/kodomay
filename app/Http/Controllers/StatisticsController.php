<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Orders;
use App\Models\Offers;

use Auth;

class StatisticsController extends Controller
{
    public function ordersTodayCount()
    {
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        return Orders::where('seller_id', $user_id)->whereBetween('order_date', [date('Y-m-d')."T00:00:00.000Z", date('Y-m-d')."T23:59:59.999Z"])->count();
    }

    public function offersActiveCount()
    {
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        return Offers::where('seller_id', $user_id)->where('is_active', 'YES')->count();
    }

    public function cashAllegro()
    {
        return 0;
    }

    public function getTransactionInMonth(Request $request)
    {
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

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

    public function transactionValue(Request $request)
    {
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        if(isset($request->m))
        {
            $m = $request->m;
        }
        else
        {
            $m = (int)date("m");
        }
        // dd(cal_days_in_month(CAL_GREGORIAN, (int)date("m") - 1, (int)date("Y")));
        for($i = 0; $i < $this->days_in_month($m, (int)date("Y")); $i++)
        {
            $j = $i;
            $d = $j + 1;
            if($d < 10)
            {
                $d = "0".$d;
            }

            if($m < 10)
            {
                $m = (int)date("m");
                $m = "0".$m;
            }

            $date = (int)date("Y")."-".$m."-".$d;
            $orders = Orders::select('order_price')->where('seller_id', $user_id)->whereBetween('order_date', [$date."T00:00:00.000Z", $date."T23:59:59.999Z"])->get();
            // $data[] = [$date => $orders];
            // $orders = Orders::select('order_price')->where('seller_id', $user_id)->whereBetween('order_date', ["2021-06-08T00:00:00.000Z", "2021-06-08T23:59:59.999Z"])->get();
            $value = 0;
            foreach($orders as $order) 
            {
                $value = $value + (float)$order->order_price;
            }
            $data[$date] = round($value, 2);
            // $data[$i] = [$d, $m];
        }
        return $data;
    }

    function days_in_month($month, $year){
        // calculate number of days in a month
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    } 


}
