<?php
// --- tabela z kursami (np. /C/), Format json - "?format=json"
// http://api.nbp.pl/api/exchangerates/tables/{table}/

// --- wybrany kurs (np. /C/USD/)
// http://api.nbp.pl/api/exchangerates/rates/{table}/{code}/
namespace App\Services;

class Exchange
{

    private $price;
    private $currency;

    public function exchangeToPln($price, $currency)
    {   
        $query = "http://api.nbp.pl/api/exchangerates/rates/C/$currency/?format=json";

        $ch = curl_init($query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($result === false || $resultCode !== 200) {
            return response()->json(['result' => $result, 'resultCode' => $resultCode]);
            
        } else {
            curl_close($ch);

            $result = json_decode($result);

            $code = $result->code;
            $newPrice = $price * $result->rates[0]->bid;

            return response()->json([
                'newPrice' => ['code' => $code, 'price' => $newPrice], 
                'oldPrice' => ['code' => 'PLN', 'price' => $price]
            ]);
        }
    }
}