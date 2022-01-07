<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Http;

use App\Models\Payment;

use JWTAuth;

class PaymentController extends Controller
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

    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required',
            'payment_service' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        
        $date = getdate();
        $data = [
            'date' => $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'],
            'amount' => self::getAmount($request->quantity),
            'credits' => $request->quantity,
            'status' => 'WAITING_FOR_PAYMENT'
        ];
        $payment = self::add($data);
        if($payment[0])
        {
            $text = array($payment[1]["payment_key"],147909,1000,"PLN","e60802a4a646c6df");
            // dd(hash("sha384", serialize($text)));
            $response = Http::withBasicAuth('147909', '27c0faf8dafc7c1324d9adad921ffb3a')
                ->post('https://sandbox.przelewy24.pl/api/v1/transaction/register', [
                    "merchantId" =>  147909,
                    "posId"=> 147909,
                    "sessionId" => $payment[1]["payment_key"],
                    "amount" => 1000,
                    "currency" => "PLN",
                    "description" => "testowa tranzakcja",
                    "email" => "sebek.kasprzak@gmail.com",
                    "client" => "Sebastian Kasprzak",
                    "country" => "PL",
                    "language" => "pl",
                    "urlReturn" => "https://kodomat.herokuapp.com/api/payment/pay-return",
                    "urlStatus" => "string",
                    "waitForResult" => false,
                    "sign" => hash("sha384", serialize($text)),
                ]);
            return response()->json(json_decode($response));
        }

        $response = Http::withBasicAuth('147909', '27c0faf8dafc7c1324d9adad921ffb3a')->get('https://sandbox.przelewy24.pl/api/v1/testAccess');
        return response()->json(json_decode($response));

        return response()->json(['url' => "https://www.google.com", 'crd' => $request->credits, 'payment_svc'=> $request->payment_service], 200);
    }

    public function payReturn($token)
    {
        return response()->json($token);
    }

    public function update_status(Request $request)
    {
        Payment::where('payment_key', $request->payment_key)->update(['status', $request->status]);
        
        return response()->json('', 200);
    }

    public function history()
    {
        $res = Payment::select('ordinal_id', 'date', 'amount', 'credits', 'status', 'info')->where('user_id', $this->user->id)->get();
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
        $payment->ordinal_id = Payment::where('user_id', $this->user->id)->count() + 1;
        $payment->user_id = $this->user->id;
        $payment->payment_key = rand(1000000, 9999999);
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
        return $quantity * 2.99;
    }
}
