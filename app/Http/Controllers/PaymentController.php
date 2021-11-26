<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use JWTAuth;

class PaymentController extends Controller
{
    protected $user;
 
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
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
}
