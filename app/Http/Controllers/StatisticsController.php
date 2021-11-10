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

    public function ordersTodayCount(Request $request)
    {
        $user_id = 40;

        return Orders::where('seller_id', $user_id)->whereBetween('order_date', [date('Y-m-d')."T00:00:00.000Z", date('Y-m-d')."T23:59:59.999Z"])->count();
    }

    public function offersActiveCount(Request $request)
    {
        $user_id = 40;

        return Offers::where('seller_id', $user_id)->where('is_active', 'YES')->count();
    }

    public function getCredits()
    {
        
        return response()->json(['credits' => $this->user->credits]);
    }

    public function getCash()
    {
        $userData = UserData::where('user_id', $this->user->id)->get();
        dd($userData);
        $response = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
            "Authorization" => "Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE2MzU4ODUwOTgsInVzZXJfbmFtZSI6IjEwMTAyNTEwNyIsImp0aSI6IjJlNzMyZjc2LTgzNWQtNDVlYS1iZDBlLTgyMDc4ZWI0ZDM4NCIsImNsaWVudF9pZCI6ImUyN2MzMDkxYTY3YTRlZGQ4MDE1MTkxZDRhMjZjNjZmIiwic2NvcGUiOlsiYWxsZWdybzphcGk6b3JkZXJzOnJlYWQiLCJhbGxlZ3JvOmFwaTpwcm9maWxlOndyaXRlIiwiYWxsZWdybzphcGk6c2FsZTpvZmZlcnM6d3JpdGUiLCJhbGxlZ3JvOmFwaTpiaWxsaW5nOnJlYWQiLCJhbGxlZ3JvOmFwaTpjYW1wYWlnbnMiLCJhbGxlZ3JvOmFwaTpkaXNwdXRlcyIsImFsbGVncm86YXBpOnNhbGU6b2ZmZXJzOnJlYWQiLCJhbGxlZ3JvOmFwaTpiaWRzIiwiYWxsZWdybzphcGk6b3JkZXJzOndyaXRlIiwiYWxsZWdybzphcGk6YWRzIiwiYWxsZWdybzphcGk6cGF5bWVudHM6d3JpdGUiLCJhbGxlZ3JvOmFwaTpzYWxlOnNldHRpbmdzOndyaXRlIiwiYWxsZWdybzphcGk6cHJvZmlsZTpyZWFkIiwiYWxsZWdybzphcGk6cmF0aW5ncyIsImFsbGVncm86YXBpOnNhbGU6c2V0dGluZ3M6cmVhZCIsImFsbGVncm86YXBpOnBheW1lbnRzOnJlYWQiLCJhbGxlZ3JvOmFwaTptZXNzYWdpbmciXSwiYWxsZWdyb19hcGkiOnRydWV9.0LV_O4_Rw4u6vYiSNSpUCW9gQYjZ0C8zYoZAE7nl-y0Kqk6U9pSO9oXuMkKmBwYpKBu2ZgZDjY1gS8KcE0bPoust8LyefnTmsASg_IZvlorO9PJ96v5M7T-QTyfHa58GcrZnicAzoXkLdi-bl9KVWcIKfKBNkYZocyFSbrf1gFuoRIGGE5v-l_gy1KphPE71wZi0Uq0Je5YBO2s9YxEGbcrx4YUPrRveLZwUTUg-ZS1B6r9dKa1GwBWIjcCIki5n4-Fv3NxAGfodx3tCavgp1GyAgQNNqx26ueJMclyGGdDMT0YFRp1aKW3joY8mm3P9D51bjjtzXN_qmttEZobCuw"
        ])->get("https://api.allegro.pl/payments/payment-operations?occurredAt.gte=2021-10-30T11:06:50.935Z");

        $responseTwo = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
            "Authorization" => "Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE2MzU4ODUyMTgsInVzZXJfbmFtZSI6IjY3MjMyODc4IiwianRpIjoiZjZjMWEyZTktOWJhZC00NDYxLTg5MTgtYzllMmFhZWMxZDdmIiwiY2xpZW50X2lkIjoiZTI3YzMwOTFhNjdhNGVkZDgwMTUxOTFkNGEyNmM2NmYiLCJzY29wZSI6WyJhbGxlZ3JvOmFwaTpvcmRlcnM6cmVhZCIsImFsbGVncm86YXBpOnByb2ZpbGU6d3JpdGUiLCJhbGxlZ3JvOmFwaTpzYWxlOm9mZmVyczp3cml0ZSIsImFsbGVncm86YXBpOmJpbGxpbmc6cmVhZCIsImFsbGVncm86YXBpOmNhbXBhaWducyIsImFsbGVncm86YXBpOmRpc3B1dGVzIiwiYWxsZWdybzphcGk6YmlkcyIsImFsbGVncm86YXBpOnNhbGU6b2ZmZXJzOnJlYWQiLCJhbGxlZ3JvOmFwaTpvcmRlcnM6d3JpdGUiLCJhbGxlZ3JvOmFwaTphZHMiLCJhbGxlZ3JvOmFwaTpwYXltZW50czp3cml0ZSIsImFsbGVncm86YXBpOnNhbGU6c2V0dGluZ3M6d3JpdGUiLCJhbGxlZ3JvOmFwaTpwcm9maWxlOnJlYWQiLCJhbGxlZ3JvOmFwaTpyYXRpbmdzIiwiYWxsZWdybzphcGk6c2FsZTpzZXR0aW5nczpyZWFkIiwiYWxsZWdybzphcGk6cGF5bWVudHM6cmVhZCIsImFsbGVncm86YXBpOm1lc3NhZ2luZyJdLCJhbGxlZ3JvX2FwaSI6dHJ1ZX0.Dp3GusPimbVJiK6YeoFd3oR1uJ-pbrMmenLizYRuOYFNXIIyQFZ5WkVyCOAKZaFPozsNlS2HooGNQrIfZPCbAqikHzeFeOgTD4etP7bMGaVF1nT5eJvLSHuXDPI9iLGLLJwvUc6LJXEBi_06Gkt_ZZ8yRdzvb0xPC5iesSTmAVb6oJ1Redk3cNJ2gYIJguEairf31N4mVgK2iRb4dsmNeZ7YIcRZuKr2WU4XlB-i4Fj4HIj-ZFyV_rSZeF0EWtn4njnxgr995qd-44nwbufUf2OnlkCtPWWVBHEjimEMrgLbF8NhZL50UGwtVo2sOA4PN5R-dI_FdZYTPja2lfaOzg"
        ])->get("https://api.allegro.pl/payments/payment-operations?occurredAt.gte=2021-10-30T11:06:50.935Z");

        $vauleOne = $response['paymentOperations'][0]['wallet']['balance']['amount'];

        $valueTwo = $responseTwo['paymentOperations'][0]['wallet']['balance']['amount'];

        return response()->json(['cash' => $vauleOne + $valueTwo]);
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
