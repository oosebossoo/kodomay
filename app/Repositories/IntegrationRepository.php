<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Models\UserData;

use App\Http\Controllers\TimeController;

use Carbon\Carbon;

use App\Repositories\AllegroAccountRepository;

class IntegrationRepository
{
    protected $clientId = 'e27c3091a67a4edd8015191d4a26c66f';
    protected $clientSecret = '3JuWoxfQmMLK9da7BvS40sCMACFCjbGXPCepOnD3R4V4k87whYLy3KPLBle9UMro';

    static function add($clientId, $user_id)
    {
        $authUrl = "https://allegro.pl/auth/oauth/authorize?"
            ."response_type=code&"
            ."client_id=$clientId&"
            ."redirect_uri=https://api.cybersent.net/$user_id/get_token";

        return response()->json(['url' => $authUrl], 200);
    }

    static function refreshToken($refresh_token, $clientId, $clientSecret, $user_id = null)
    {
        $response = Http::withHeaders([
            'User-Agent'      => 'Kodomat',
            'Authorization'   => 'Basic ' . base64_encode($clientId.":".$clientSecret),
            'Content-Type'    => 'application/vnd.allegro.public.v1+json',
            'Accept'          => 'application/vnd.allegro.public.v1+json',
            'Accept-Language' => 'pl-PL'
        ])->post("http://allegro.pl/auth/oauth/token?grant_type=refresh_token&refresh_token=$refresh_token&redirect_uri=https://api.cybersent.net/get_token");

        if(!isset($response['error'])) {
            UserData::where('refresh_token', $refresh_token)->update([
                'ordinal_id' => UserData::where('user_id', $user_id)->count(),
                'access_token' => $response['access_token'], 
                'refresh_token' => $response['refresh_token'],
                'jti' => $response['jti'],
                'refresh' => 0
            ]);
            AllegroAccountRepository::updateOffers($user_id);
            return json_decode($response);
        } else {
            return json_decode($response);
        }
    }

    static function getToken($request, $clientId, $clientSecret, $user_id)
    {
        $response = Http::withHeaders([
            'User-Agent'      => 'Kodomat',
            'Authorization'   => 'Basic ' . base64_encode($clientId.":".$clientSecret),
            'Content-Type'    => 'application/vnd.allegro.public.v1+json',
            'Accept'          => 'application/vnd.allegro.public.v1+json',
            'Accept-Language' => 'pl-PL'
        ])->post("http://allegro.pl/auth/oauth/token?grant_type=authorization_code&code=$request->code&redirect_uri=https://api.cybersent.net/$user_id/get_token"); 

        if(!isset($response["error"])) {
            $user_datas = UserData::where('user_id', $user_id)->get();
            foreach ($user_datas as $user_data)
            {
                $old = Http::withHeaders([
                    "Accept" => "application/vnd.allegro.public.v1+json",
                    "Authorization" => "Bearer $user_data->access_token"
                ])->get("https://api.allegro.pl/me");
                $old = json_decode($old);
                $new = Http::withHeaders([
                    "Accept" => "application/vnd.allegro.public.v1+json",
                    "Authorization" => "Bearer ".$response['access_token']
                ])->get("https://api.allegro.pl/me"); 
                $new = json_decode($new);
                if ($old->id == $new->id)
                {
                    return redirect()->away('http://cybersent.net/#/integrations/allegro');
                }
            }
            $userData = new UserData();
            $userData->ordinal_id = UserData::where('user_id', $user_id)->count() + 1;
            $userData->user_id = $user_id;
            $userData->access_token = $response['access_token'];
            $userData->token_type = $response['token_type'];
            $userData->refresh_token = $response['refresh_token'];
            $userData->expires_in = $response['expires_in'];
            $userData->scope = $response['scope'];
            $userData->allegro_api = $response['allegro_api'];
            $userData->jti = $response['jti'];
            $userData->refresh = 0;
            $userData->last_event_b = self::lastEvent($response["access_token"]);
            $userData->last_event_rtp = self::lastEvent($response["access_token"]);
            $userData->save();

            self::addMail($response['access_token']);
            AllegroAccountRepository::updateOffers($user_id);

            return redirect()->away('http://cybersent.net/#/integrations/allegro');
        }

        return response()->json([
            'message' => 'try later'
        ], 500);
    }

    static function deleteAllegroUser($request)
    {
        if(UserData::where('id', $request->id)->delete()) {
            return response()->json([
                'message' => "Account deleted"
            ], 200);
        } else {
            return response()->json([
                'message' => "Can't delete account"
            ], 500);
        }
    }

    static function list($user_id)
    {
        $userDatas = UserData::where('user_id', $user_id)->get();

        if(!$userDatas->isEmpty()) {
            foreach ($userDatas as $userData)
            {
                $response = Http::withHeaders([
                    "Accept" => "application/vnd.allegro.public.v1+json",
                    'Accept-Language' => 'pl-PL',
                    "Authorization" => "Bearer $userData->access_token",
                    'Content-Type'    => 'application/vnd.allegro.public.v1+json',
                ])->get("https://api.allegro.pl/me"); 
                if(isset($response["error"])) {   
                    $refreshed = self::refreshToken(UserData::where('user_id', $user_id)->select('refresh_token')->first()['refresh_token'], 'e27c3091a67a4edd8015191d4a26c66f', '3JuWoxfQmMLK9da7BvS40sCMACFCjbGXPCepOnD3R4V4k87whYLy3KPLBle9UMro', $user_id);
                    $new = Http::withHeaders([
                        "Accept" => "application/vnd.allegro.public.v1+json",
                        'Accept-Language' => 'pl-PL',
                        "Authorization" => "Bearer $refreshed->access_token",
                        'Content-Type'    => 'application/vnd.allegro.public.v1+json',
                    ])->get("https://api.allegro.pl/me");
                    $new = json_decode($new);
                    $res[] = [
                        'id' => $userData->id,
                        'ordinal_id' => $userData->ordinal_id,
                        'login' => $new->login,
                        // Carbon::parse($userData->updated_at)->addHour(TimeController::repairTime());
                        'created_at' => Carbon::parse($userData->created_at)->addHour(TimeController::repairTime()),
                        'updated_at' => Carbon::parse($userData->updated_at)->addHour(TimeController::repairTime())
                    ];
                } else {
                    $response = json_decode($response);
                    $res[] = [
                        'id' => $userData->id,
                        'ordinal_id' => $userData->ordinal_id,
                        'login' => $response->login,
                        'created_at' => Carbon::parse($userData->created_at)->addHour(TimeController::repairTime()),
                        'updated_at' => Carbon::parse($userData->updated_at)->addHour(TimeController::repairTime())
                    ];
                }  
            }
            return response()->json($res, 200);
        } else {
            return response()->json([], 200);
        }
        return response()->json([], 200);
    }

    static function lastEvent($user_id)
    {
        $userDatas = UserData::where('user_id', $user_id)->get();
        
        foreach ($userDatas as $userData)
        {
            $response = Http::withHeaders([
                "Accept" => "application/vnd.allegro.public.v1+json",
                "Authorization" => "Bearer ".$userData["access_token"]
            ])->get("https://api.allegro.pl/order/event-stats");
            $response = json_decode($response);

            if($response->latestEvent != null)
            {
                $userData->last_event_b = $response->latestEvent->id;
                $userData->last_event_rtp = $response->latestEvent->id;
                $userData->save();
            }
        }
        return 0;
    }

    static function addMail($access_token, $email = 'office@accounts4life.com')
    {
        $response = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
            'Accept-Language' => 'pl-PL',
            "Authorization" => "Bearer $access_token",
            'Content-Type'    => 'application/vnd.allegro.public.v1+json',
            ])
        // ->withBody('{ "email" : "cybersent.noreply@gmail.com" }', 'vnd')
        ->post("https://api.allegro.pl/account/additional-emails", ['email' => $email]);

        //dd(json_decode($response));

        if($response->successful())
        {
            return 0;
        }

        return 1;
    }
}