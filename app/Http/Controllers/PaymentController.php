<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Http;

use App\Models\DebugInfo;
use App\Models\Payment;
use App\Models\PaymentP24;
use App\Models\User;

use JWTAuth;

class PaymentController extends Controller
{
    protected $user;
 
    public function __construct()
    {
        try {
            // $this->user = JWTAuth::parseToken()->authenticate();
            $this->user = 40;
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

    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required',
            'payment_service' => 'required',
        ]);

        $sessionId = 0;

        $date = getdate();

        $chars = str_split($date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds']."test_login");

        foreach ($chars as $char)
        {
            $char = dechex(ord(strtolower($char)));

            $sessionId .= strval($char);
        }

        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = [
            'date' => $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'],
            'amount' => self::getAmount($request->quantity),
            'credits' => $request->quantity,
            'status' => 'WAITING_FOR_PAYMENT'
        ];

        $payment = self::add($data);
        if($payment[0])
        {
            $merchantId = 147909;
            $amount = $data['amount'];
            $currency = "PLN";
            $crc = "d60b4bc1690aad81";

            $data = [
                'sessionId' => $sessionId,
                'merchantId' => $merchantId,
                'amount' => $amount,
                'currency' => $currency,
                'crc' => $crc
            ];

            $sign = hash('sha384', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $response = Http::withBasicAuth(147909, "27c0faf8dafc7c1324d9adad921ffb3a")
                ->post('https://sandbox.przelewy24.pl/api/v1/transaction/register', [
                    "merchantId" => $merchantId,
                    "posId"=> $merchantId,
                    "sessionId" => $sessionId,
                    "amount" => $amount,
                    "currency" => $currency,
                    "description" => "testowa tranzakcja",
                    "email" => "sebek.kasprzak@gmail.com",
                    "client" => "Sebastian Kasprzak",
                    "country" => "PL",
                    "language" => "pl",
                    "urlReturn" => "https://api.cybersent.net/api/payment/pay-return",
                    "urlStatus" => "https://api.cybersent.net/api/payment/pay-status/".$payment[1]['payment_key'],
                    "waitForResult" => false,
                    "sign" => $sign,
                ]);

            $response = json_decode($response);

            $token = $response->data->token;
            return redirect("https://sandbox.przelewy24.pl/trnRequest/$token");
        }

        return response()->json(['url' => "https://www.google.com", 'crd' => $request->credits, 'payment_svc'=> $request->payment_service], 200);
    }

    public function payReturn(Request $request)
    {
        return redirect('https://cybersent.net/#/payments/history');
    }

    public function payStatus(Request $request, $payment_key)
    {
        $tmp = new DebugInfo();
        $tmp->data = json_decode($request);
        $tmp->save();

        $p24ver = new PaymentP24();
        $p24ver->payment_key = $payment_key;
        $p24ver->merchantId = $request->merchantId;
        $p24ver->posId = $request->posId;
        $p24ver->sessionId = $request->sessionId;
        $p24ver->amount = $request->amount;
        $p24ver->originAmount = $request->originAmount;
        $p24ver->currency = $request->currency; 
        $p24ver->orderId = $request->orderId;
        $p24ver->methodId = $request->methodId;
        $p24ver->statement = $request->statement;
        $p24ver->sign = $request->sign;
        $p24ver->save();

        $sessionId = $request->sessionId;
        $merchantId = $request->merchantId;
        $amount = $request->amount;
        $currency = $request->currency;
        $crc = "d60b4bc1690aad81";

        $data = [
            'sessionId' => $request->sessionId,
            "orderId" => $request->orderId,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'crc' => $crc
        ];

        $sign = hash('sha384', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $response = Http::withBasicAuth($request->merchantId, "27c0faf8dafc7c1324d9adad921ffb3a")
            ->put('https://sandbox.przelewy24.pl/api/v1/transaction/verify', [
                "merchantId" => $request->merchantId,
                "posId"=> $request->posId,
                "sessionId" => $request->sessionId,
                "amount" => $request->amount,
                "currency" => $request->currency,
                "orderId" => $request->orderId,
                "sign" => $sign,
            ]);

        if($response['data']['status'] == "success")
        {
            $payment = Payment::where('payment_key', $payment_key)->first();
            return self::addCredits($payment->credits);
        }
        return 2;
    }

    public function update_status(Request $request)
    {
        Payment::where('payment_key', $request->payment_key)->update(['status', $request->status]);
        
        return response()->json('', 200);
    }

    public function history()
    {
        $res = Payment::select('ordinal_id', 'date', 'amount', 'credits', 'status', 'info')->where('user_id', $this->user)->get();
        return response()->json($res, 200);
    }

    public function testAPI()
    {
        $response = Http::withBasicAuth('147909', '27c0faf8dafc7c1324d9adad921ffb3a')->get('https://sandbox.przelewy24.pl/api/v1/testAccess');
        return response()->json(json_decode($response));
    }

    function add($data)
    {
        $payment = new Payment();
        $payment->ordinal_id = Payment::where('user_id', $this->user)->count() + 1;
        $payment->user_id = $this->user;
        $payment->payment_key = rand(10000000, 99999999);
        $payment->date = $data['date'];
        $payment->amount = $data['amount'];
        $payment->credits = $data['credits'];
        $payment->status = $data['status'];

        if($payment->save())
        {
            return [1, $payment];
        } else {
            return [0, null];
        }
    }

    function getAmount($quantity)
    {
        return $quantity * 299;
    }

    function addCredits($credits)
    {
        if(User::where('id', $this->user)->increment('credits', $credits))
        {
            return 0;
        }
        return 1;
    }
}
