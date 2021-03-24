<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Exchange;

class ExchangeController extends Controller
{
    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;
    }
    public function exchangeToPln(Request $request)
    {
        // --- przykładowy url ---
        // --- 10zł na dolary  ---
        // localhost:8000/exchange_to_pln?price=10&currency=USD

        return $this->exchange->exchangeToPln($request->price, $request->currency);
    }
}
