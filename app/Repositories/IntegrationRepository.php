<?php

namespace App\Repositories;

use Illuminate\Http\Request;

class IntegrationRepository
{
    static function add($clientId)
    {
        $authUrl = "https://allegro.pl/auth/oauth/authorize?"
            ."response_type=code&"
            ."client_id=$clientId&"
            ."redirect_uri=https://kodomat.herokuapp.com/get_token";

        return redirect($authUrl);
    }

    static function getToken($request)
    {
        if(!isset($request->code))
        {
            return $this->endOfGettingToken($request);
        }

        $json = true;

        $resource = "https://allegro.pl/auth/oauth/token?"
            ."grant_type=authorization_code&"
            ."code=$request->code&"
            ."redirect_uri=https://kodomat.herokuapp.com/get_token";

        $headers = array();
        $data = array();

        $options = array(
            'http' => array(
                'method'  => strtoupper('POST'),
                'header'  => $this->parseHeaders($requestHeaders = array_replace(array(
                    'User-Agent'      => 'Kodomat',
                    'Authorization'   => 'Basic ' . base64_encode($this->clientId.":".$this->clientSecret),
                    'Content-Type'    => 'application/vnd.allegro.public.v1+json',
                    'Accept'          => 'application/vnd.allegro.public.v1+json',
                    'Accept-Language' => 'pl-PL'
                ))),
                'content' => ($json ? json_encode($data) : $data),
                'ignore_errors' => true
            )
        );

        $response = json_decode(file_get_contents(
            (stristr($resource, 'http') !== false 
                ? $resource 
                : $this->getUrl() . '/' . ltrim($resource, '/')
            ), 
            false, 
            stream_context_create($options),
        ));

        if(UserData::select('refresh')->where('refresh', 1)->exists())
        {
            $updates = UserData::where('refresh', 1)->get();
            $log[] = 'start updating';
            foreach($updates as $update)
            {
                UserData::where('user_id', $update->user_id)->update([
                    'access_token' => $response->access_token, 
                    'refresh_token' => $response->refresh_token,
                    'jti' => $response->jti,
                    'refresh' => 0
                ]);
                $log[] = ['id' => $update->user_id];
            }
            return [$log];
        }
        else
        {
            $userData = new UserData();
            $userData->user_id = Auth::user()->id;
            $userData->access_token = $response->access_token;
            $userData->token_type = $response->token_type;
            $userData->refresh_token = $response->refresh_token;
            $userData->expires_in = $response->expires_in;
            $userData->scope = $response->scope;
            $userData->allegro_api = $response->allegro_api;
            $userData->jti = $response->jti;
            $userData->refresh = 0;
            $userData->save();

            return [
                'status' => 0,
                'desc' => 'added new account'
            ];
        }
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
}