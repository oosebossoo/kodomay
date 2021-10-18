<?php

namespace App\Repositories;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\MailController;

// use App\Models\Customer;
use App\Models\UserData;
// use App\Models\Orders;
// use App\Models\OrdersTable;
// use App\Models\Offers;
// use App\Models\SentMail;
// use App\Models\Code;

class IntegrationRepository
{
    protected $clientId = 'e27c3091a67a4edd8015191d4a26c66f';
    protected $clientSecret = '3JuWoxfQmMLK9da7BvS40sCMACFCjbGXPCepOnD3R4V4k87whYLy3KPLBle9UMro';

    static function add($clientId)
    {
        $authUrl = "https://allegro.pl/auth/oauth/authorize?"
            ."response_type=code&"
            ."client_id=$clientId&"
            ."redirect_uri=https://kodomat.herokuapp.com/get_token";

        return redirect($authUrl);
    }

    static function refreshToken($refresh_token, $clientId, $clientSecret)
    {
        $response = Http::withHeaders([
            'User-Agent'      => 'Kodomat',
            'Authorization'   => 'Basic ' . base64_encode($clientId.":".$clientSecret),
            'Content-Type'    => 'application/vnd.allegro.public.v1+json',
            'Accept'          => 'application/vnd.allegro.public.v1+json',
            'Accept-Language' => 'pl-PL'
        ])->post("http://allegro.pl/auth/oauth/token?grant_type=refresh_token&refresh_token=$refresh_token&redirect_uri=https://kodomat.herokuapp.com/get_token");

        echo $response;

        echo '<h1>'.$response['refresh_token'].'</h1>';

        UserData::where('user_id', 40)->update([
            'access_token' => $response['access_token'], 
            'refresh_token' => $response['refresh_token'],
            'jti' => $response['jti'],
            'refresh' => 0
        ]);
    }

    static function getToken($request, $clientId, $clientSecret, $user_id)
    {
        if(!isset($request->code))
        {

        }

        $response = Http::withHeaders([
            'User-Agent'      => 'Kodomat',
            'Authorization'   => 'Basic ' . base64_encode($clientId.":".$clientSecret),
            'Content-Type'    => 'application/vnd.allegro.public.v1+json',
            'Accept'          => 'application/vnd.allegro.public.v1+json',
            'Accept-Language' => 'pl-PL'
        ])->post("http://allegro.pl/auth/oauth/token?grant_type=authorization_code&code=$request->code&redirect_uri=https://kodomat.herokuapp.com/get_token"); 

        $userData = new UserData();
        $userData->user_id = $user_id;
        $userData->access_token = $response['access_token'];
        $userData->token_type = $response['token_type'];
        $userData->refresh_token = $response['refresh_token'];
        $userData->expires_in = $response['expires_in'];
        $userData->scope = $response['scope'];
        $userData->allegro_api = $response['allegro_api'];
        $userData->jti = $response['jti'];
        $userData->refresh = 0;
        $userData->save();

        return response()->json([
            'message' => 'added new account'
        ], 200);
    }



    static function deleteAllegroUser($request)
    {
        if(isset($request->user_id))
        {
            $user_id = $request->user_id;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        if(UserData::where('user_id', $user_id)->where('id', $request->id)->delete())
        {
            return resposne()->json([
                'message' => "Can't delete account"
            ], 500);
        }
        else
        {
            return resposne()->json([
                'message' => "Can't delete account"
            ], 500);
        }
    }

    static function list($user_id)
    {
        // --- PRODUKCJA --- 
        $userDatas = UserData::where('user_id', $user_id)->get();

        $users = array();
        foreach ($userDatas as $userData)
        {
            $response = Http::withHeaders([
                "Accept" => "application/vnd.allegro.public.v1+json",
                "Authorization" => "Bearer $userData->access_token"
            ])->get("https://api.allegro.pl/me"); 
            if(!isset($response["error"])) {   
                $response = json_decode($response);
                $user[] = [
                    $response->login,
                    $response->firstName,
                    $response->lastName,
                ];
                return response()->json($user);
            }
            else {
                UserData::where('user_id', $user_id)->update([
                    'refresh' => 1
                ]);
                $this->refreshToken(UserData::where('user_id', $user_id)->select('refresh_token')['refresh_token']);
                return response()->json(['error' => $response['error']]);
            }  
        }
    }

    static function offers($user_id)
    {
        $limit = 100;
        if(isset($request->limit))
        {
            $limit = $request->limit;
        }

        $userDatas = UserData::where('user_id', $user_id)->get();
        foreach($userDatas as $userData)
        {

        $response = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
            "Authorization" => "Bearer $userData->access_token"
        ])->get("https://api.allegro.pl/sale/offers?limit=$limit");

        foreach($response['offers'] as $offer)
        {
            $ending[] = $offer;
            $existOffer = Offers::where('offer_id', $offer['id'])->get();
            if(!isset($existOffer[0]["id"]))
            {
                $offerDB = new Offers();
                $offerDB->seller_id = $user_id;
                $offerDB->offer_id = $offer['id'];
                $offerDB->offer_name = $offer['name'];
                $offerDB->stock_available = $offer["stock"]["available"];
                $offerDB->stock_sold = $offer['stock']['sold'];
                
                $d=strtotime("-1 Months");
                $date = date("Y-m-d h:i:s", $d);
                $soldInTrD = Orders::where('offer_id', $offer['id'])->where('created_at', '>', $date)->count();
                $offerDB->sold_last_30d = $soldInTrD;
                
                $offerDB->price_amount = $offer['sellingMode']['price']['amount'];
                $offerDB->price_currency = $offer['sellingMode']['price']['currency'];
                $offerDB->platform = "Allegro";
                $offerDB->status_platform = $offer['publication']['status'];
                $offerDB->startedAt = $offer['publication']['startedAt'];

                if(isset($offer['publication']['endingAt']))
                {
                    $offerDB->endingAt = $offer['publication']['endingAt'];
                }
                else
                {
                    $offerDB->endingAt = "Neverending offer... :)";
                }
                
                if(isset($offer['publication']['endedAt']))
                {
                    $offerDB->endingAt = $offer['publication']['endedAt'];
                }
                else
                {
                    $offerDB->endingAt = "Neverended offer... :)";
                }
                
                $offerDB->is_active = 'YES';
                $offerDB->save();
            }
        }
        }
        return Offers::where('seller_id', $user_id)->get();
    }

    // ----------------------
        // ----------------------
            // ----------------------
                // ----------------------
                    // ----------------------


    static function parseHeaders(array $headers)
    {
        // Creating variable for headers
        $stringHeaders = '';
        
        // Loop over each of header
        foreach ($headers as $header => $value) {
            
            // Adding header line
            $stringHeaders .= "$header: $value\r\n";
        }
        
        // Returning headers
        return $stringHeaders;
    }

    static function getUrl()
    {
        // Returning correct URL depending on sandbox setting
        return $this->getSandbox() 
            ? AllegroRestApi::SANDBOX_URL 
            : AllegroRestApi::URL;
    }
}