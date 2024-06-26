<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Models\Customer;
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
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (TokenInvalidException $e) {
            header("Location: /unauthorized"); 
            die;
        } catch (TokenExpiredException $e) {
            header("Location: /unauthorized"); 
            die;
        } catch (JWTException $e) {
            header("Location: /unauthorized"); 
            die;
        }
    }

    public function getDashboard(Request $request)
    {
        return response()->json([
            'orders_today' => StatisticsController::ordersTodayCount(),
            'active_offers' => StatisticsController::offersActiveCount(),
            'credits'=> StatisticsController::getCredits(),
            'cash' => StatisticsController::getCash(),
            'transaction_in_month' => StatisticsController::getTransactionInMonth($request),
            'transaction_value' => StatisticsController::transactionValue($request),
            'avg_send_time' => StatisticsController::avgSendTime(),
            'income_today' => StatisticsController::incomeToday(),
            'income_this_mounth' => StatisticsController::incomeThisMounth(),
            'send_codes_today' => StatisticsController::sendCodesToday(),
            'send_codes_this_mounth' => StatisticsController::sendCodesThisMounth(),
            'customers_quantity' => StatisticsController::customersQuantity(),
        ]);
    }

    public function ordersTodayCount()
    {
        $user_id = $this->user->id;

        return Orders::where('seller_id', $user_id)->whereBetween('created_at', [date('Y-m-d')."T00:00:00.000Z", date('Y-m-d')."T23:59:59.999Z"])->count();
        return response()->json(Orders::where('seller_id', $user_id)->whereBetween('order_date', [date('Y-m-d')."T00:00:00.000Z", date('Y-m-d')."T23:59:59.999Z"])->count());
    }

    public function offersActiveCount()
    {
        $user_id = $this->user->id;

        return Offers::where('seller_id', $user_id)->where('is_active', 'YES')->count();
        return response()->json(Offers::where('seller_id', $user_id)->where('is_active', 'YES')->count());
    }

    public function getCredits()
    {
        return $this->user->credits;
        return response()->json($this->user->credits);
    }

    public function getCash()
    {
        $userDatas = UserData::where('user_id', $this->user->id)->get();
        $value = 0;
        $oneMonthBack = date( \DateTime::ISO8601, strtotime('-1 month' ));
        return $oneMonthBack;

        foreach ($userDatas as $userData)
        {
            $response = Http::withHeaders([
                "Accept" => "application/vnd.allegro.public.v1+json",
                "Authorization" => "Bearer $userData->access_token"
            ])->get('https://api.allegro.pl/payments/payment-operations?occurredAt.gte='.$oneMonthBack);
            return $response['errors'];

            if(isset($response['paymentOperations'][0]['wallet']['balance']['amount'])) {
                $value += $response['paymentOperations'][0]['wallet']['balance']['amount'];
            }
        }

        return $value;
        return response()->json($value);
    }

    public function getTransactionInMonth(Request $request)
    {
        $user_id = $this->user->id;

        if(isset($request->m) == 1) {
            $m = $request->m;
        } else {
            $m = (int)date("m");
        }

        for($i = 0; $i < $this->days_in_month($m, (int)date("Y")); $i++)
        {
            if(isset($request->m) == 1) {
                $m = $request->m;
            } else {
                $m = (int)date("m");
            }

            $j = $i;
            $d = $j + 1;
            if($d < 10) {
                $d = "0".$d;
            }

            if($m < 10) {
                $m = "0".$m;
            }
            
            $date = (int)date("Y")."-".$m."-".$d;
            $data[$date] = Orders::where('seller_id', $user_id)->whereBetween('order_date', [$date."T00:00:00.000Z", $date."T23:59:59.999Z"])->count();
        }
        return $data;
        return response()->json($data);
    }

    public function transactionValue(Request $request)
    {
        $user_id = $this->user->id;

        if(isset($request->m)) {
            $m = $request->m;
        } else {
            $m = (int)date("m");
        }
        for($i = 0; $i < $this->days_in_month($m, (int)date("Y")); $i++)
        {
            $j = $i;
            $d = $j + 1;
            if($d < 10) {
                $d = "0".$d;
            }

            if($m < 10) {
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
        return response()->json($data);
    }

    // ---------------------------------------------------------

    public function avgSendTime()
    {
        return 0;
        return response()->json(0);
    }

    public function incomeToday()
    {
        return Orders::where('seller_id', $this->user->id)->whereBetween('order_date', [date('Y-m-d')."T00:00:00.000Z", date('Y-m-d')."T23:59:59.999Z"])->sum('order_price');
        return response()->json(Orders::where('seller_id', $this->user->id)->whereBetween('order_date', [date('Y-m-d')."T00:00:00.000Z", date('Y-m-d')."T23:59:59.999Z"])->sum('order_price'));
    }

    public function incomeThisMounth()
    {
        return Orders::where('seller_id', $this->user->id)->whereBetween('order_date', [date('Y-m')."-01T00:00:00.000Z", date('Y-m')."-31T23:59:59.999Z"])->sum('order_price');
        return response()->json(Orders::where('seller_id', $this->user->id)->whereBetween('order_date', [date('Y-m')."-01T00:00:00.000Z", date('Y-m')."-31T23:59:59.999Z"])->sum('order_price'));
    }

    public function sendCodesToday()
    {
        return Orders::where('seller_id', $this->user->id)->whereBetween('order_date', [date('Y-m-d')."T00:00:00.000Z", date('Y-m-d')."T23:59:59.999Z"])->sum('quantity');
        return response()->json(Orders::where('seller_id', $this->user->id)->whereBetween('order_date', [date('Y-m-d')."T00:00:00.000Z", date('Y-m-d')."T23:59:59.999Z"])->sum('quantity'));
    }

    public function sendCodesThisMounth()
    {
        return Orders::where('seller_id', $this->user->id)->whereBetween('order_date', [date('Y-m')."-01T00:00:00.000Z", date('Y-m')."-31T23:59:59.999Z"])->sum('quantity');
        return response()->json(Orders::where('seller_id', $this->user->id)->whereBetween('order_date', [date('Y-m')."-01T00:00:00.000Z", date('Y-m')."-31T23:59:59.999Z"])->sum('quantity'));
    }

    public function customersQuantity()
    {
        return Customer::where('seller_id', $this->user->id)->whereBetween('updated_at', [date('Y-m')."-01T00:00:00.000Z", date('Y-m')."-31T23:59:59.999Z"])->count();
        return response()->json(Customer::where('seller_id', $this->user->id)->whereBetween('updated_at', [date('Y-m')."-01T00:00:00.000Z", date('Y-m')."-31T23:59:59.999Z"])->count());
    }

    function days_in_month($month, $year){
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    } 
}
