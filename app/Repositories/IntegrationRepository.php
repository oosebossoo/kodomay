<?php

namespace App\Repositories;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;

use App\Models\UserData;

class IntegrationRepository
{
    protected $clientId = 'e27c3091a67a4edd8015191d4a26c66f';
    protected $clientSecret = '3JuWoxfQmMLK9da7BvS40sCMACFCjbGXPCepOnD3R4V4k87whYLy3KPLBle9UMro';

    static function add($clientId, $user_id)
    {
        $authUrl = "https://allegro.pl/auth/oauth/authorize?"
            ."response_type=code&"
            ."client_id=$clientId&"
            ."redirect_uri=https://kodomat.herokuapp.com/$user_id/get_token";

        return response()->json(['url' => $authUrl], 200);
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

        if(!isset($response['error'])) {
            UserData::where('refresh_token', $refresh_token)->update([
                'access_token' => $response['access_token'], 
                'refresh_token' => $response['refresh_token'],
                'jti' => $response['jti'],
                'refresh' => 0
            ]);

            return response()->json([
                'message' => 'updated',
                $response
            ], 200);
        } else {
            return response()->json([
                'message' => 'error',
                $response
            ], 200);
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
        ])->post("http://allegro.pl/auth/oauth/token?grant_type=authorization_code&code=$request->code&redirect_uri=https://kodomat.herokuapp.com/$user_id/get_token"); 

        if(!isset($response["error"])) {
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
            $userData->save();

            return redirect()->away('http://localhost:3000/integrations/allegro');
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
                    "Authorization" => "Bearer $userData->access_token"
                ])->get("https://api.allegro.pl/me"); 
                if(!isset($response["error"])) {   
                    $response = json_decode($response);
                    $res[] = [
                        'id' => $userData->id,
                        'ordinal_id' => $userData->ordinal_id,
                        'login' => $response->login,
                        'created_at' => $userData->created_at,
                        'updated_at' => $userData->updated_at
                    ];
                } else {
                    UserData::where('user_id', $user_id)->update([
                        'refresh' => 1
                    ]);
                    self::refreshToken(UserData::where('user_id', $user_id)->select('refresh_token')->first()['refresh_token'], 'e27c3091a67a4edd8015191d4a26c66f', '3JuWoxfQmMLK9da7BvS40sCMACFCjbGXPCepOnD3R4V4k87whYLy3KPLBle9UMro');
                    $res = [
                        'error' => $response['error']
                    ];
                }  
            }
            return response()->json($res, 200);
        } else {
            return response()->json([], 200);
        }
        return response()->json([], 200);
    }
}