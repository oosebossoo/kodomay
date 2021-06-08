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

    public function getTransactionInMonth()
    {
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        for($i = 0; $i < cal_days_in_month(CAL_GREGORIAN, (int)date("m"), (int)date("Y")); $i++)
        {
            $j = $i;
            if($j <10)
            {

                $d = "0".$j+1;
            }

            if((int)date("m") < 10)
            {
                $m = "0".(int)date("m");
            }
            $date = (int)date("Y")."-".$m."-".$d;
            $data[] = Orders::where('seller_id', $user_id)->whereBetween('order_date', [$date."T00:00:00.000Z", $date."T23:59:59.999Z"])->count();
        }
        return $data;
    }

    public function transactionValue()
    {
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }
        for($i = 0; $i < cal_days_in_month(CAL_GREGORIAN, (int)date("m"), (int)date("Y")); $i++)
        {
            $j = $i;
            if($j <10)
            {
                $d = "0".$j+1;
            }

            if((int)date("m") < 10)
            {
                $m = "0".(int)date("m");
                $m = $m - 1;
            }
            $date = (int)date("Y")."-".$m."-".$d;
            $orders = Orders::select('order_price')->where('seller_id', $user_id)->whereBetween('order_date', [$date."T00:00:00.000Z", $date."T23:59:59.999Z"])->get();
            $value = 0;
            foreach($orders as $order) 
            {
                $value = $value + (float)$order->order_price;
            }
            $data[] = round($value, 2);
        }
        return $data;
    }


}
