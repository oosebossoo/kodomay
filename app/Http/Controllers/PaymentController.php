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
 
    // public function __construct()
    // {
    //     try {
    //         $this->user = JWTAuth::parseToken()->authenticate();
    //     } catch (TokenInvalidException $e) {
    //         dd('token_invalid');
    //     } catch (TokenExpiredException $e) {
    //         dd ('token_expired');
    //     } catch (JWTException $e){
    //         dd('token_invalid ws');
    //     }
    // }

    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required',
            'payment_service' => 'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        return response()->json(['url' => "https://www.google.com", 'crd' => $request->credits, 'payment_svc'=> $request->payment_service], 200);

        // return response()->json(['url' => "dajmi.hajs/szybko
        //     ?payment_svc=$request->payment_service
        //     &credits=$request->credits
        // "], 200);
    }

    public function add(Request $request)
    {
        $payment = new Payment();
        $payment->ordinal_id = Payment::where('user_id', $this->user->id)->count() + 1;
        $payment->user_id = $this->user->id;
        $payment->payment_key = rand(1000000, 9999999);
        $payment->date = $request->date;
        $payment->amount = $request->amount;
        $payment->credits = $request->credits;
        $payment->status = $request->status;
        
        return response()->json('', 200);
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
}
