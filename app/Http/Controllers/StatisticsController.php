<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Models\Orders;
use App\Models\Offers;
use App\Models\UserData;

use Auth;
use JWTAuth;

class StatisticsController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function getDashboard(Request $request)
    {
        return response()->json([
            // 'orders_today' => self::ordersTodayCount(),
            // 'active_offers' => self::offersActiveCount(),
            // 'credits'=> self::getCredits(),
            // 'cash' => self::getCash(),
        ]);
    }

    public function ordersTodayCount()
    {
        $user_id = $this->user->id;

        return ['todayCount' => Orders::where('seller_id', $user_id)->whereBetween('order_date', [date('Y-m-d')."T00:00:00.000Z", date('Y-m-d')."T23:59:59.999Z"])->count()];
    }

    public function offersActiveCount()
    {
        $user_id = $this->user->id;

        return ['activeCount' => Offers::where('seller_id', $user_id)->where('is_active', 'YES')->count()];
    }

    public function getCredits()
    {
        return ['credits' => $this->user->credits];
    }

    public function getCash()
    {
        $userDatas = UserData::where('user_id', $this->user->id)->get();
        $value = 0;

        foreach ($userDatas as $userData)
        {
            $response = Http::withHeaders([
                "Accept" => "application/vnd.allegro.public.v1+json",
                "Authorization" => "Bearer $userData->access_token"
            ])->get("https://api.allegro.pl/payments/payment-operations");

            if(isset($response['paymentOperations'][0]['wallet']['balance']['amount']))
            {
                $value += $response['paymentOperations'][0]['wallet']['balance']['amount'];
            }
        }

        return ['cash' => $value];
    }

    public function getTransactionInMonth(Request $request)
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

    public function transactionValue(Request $request)
    {
        $user_id = 40;

        if(isset($request->m))
        {
            $m = $request->m;
        }
        else
        {
            $m = (int)date("m");
        }
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
            $value = 0;
            foreach($orders as $order) 
            {
                $value = $value + (float)$order->order_price;
            }
            $data[$date] = round($value, 2);
        }
        return $data;
    }

    

    function days_in_month($month, $year){
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    } 
}
