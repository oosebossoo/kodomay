<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use AsocialMedia\AllegroApi\AllegroRestApi;

use App\Models\UserData;

use Auth;

class AllegroController extends Controller
{
    // Auth::user()->token;
    // protected $token;
    const SANDBOX_URL = 'https://api.allegro.pl.allegrosandbox.pl';

    protected $clientId = '1842f4e03d1347d4812246f7439baaa1';
    protected $clientSecret = 'JvRLfxOdGmLBNRooPqnxQJOKFnwZ7XW1bW5m7tPCNb1LPaw5ttje2g7Fcz0OS6ri';

    public function getAuth()
    {

        $authUrl = "https://allegro.pl.allegrosandbox.pl/auth/oauth/authorize?"
            ."response_type=code&"
            ."client_id=$this->clientId&"
            ."redirect_uri=https://kodomat.herokuapp.com/get_token";

        return redirect($authUrl);
    }

    public function getToken(Request $request)
    {
        // $response = $restApi->get('/sale/user-ratings?user.id=' . $yourUserId)
        // post($resource, $data, array $headers = array(), $json = true)
        // sendRequest($resource, 'POST', $data, $headers, $json);
        // sendRequest($resource, $method,  $data = array(),    array $headers = array(), $json = true)
        $json = true;

        $resource = "https://www.allegro.pl.allegrosandbox.pl/auth/oauth/token?"
            ."grant_type=authorization_code&"
            ."code=$request->code&"
            ."redirect_uri=https://kodomat.herokuapp.com/get_token";

        $headers = array();
        $data = array();

        $options = array(
            'http' => array(
                'method'  => strtoupper('POST'),
                'header'  => $this->parseHeaders($requestHeaders = array_replace(array(
                    'User-Agent'      => 'AsocialMedia/AllegroApi/v3.1.0 (+https://asocial.media)',
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
            stream_context_create($options)
        ));
        dd($response);

        // $userData = new UserData();
        // $userData->user_id = Auth::user()->id;
        // $userData->access_token = $response->access_token;
        // $userData->token_type = $response->token_type;
        // $userData->refresh_token = $response->refresh_token;
        // $userData->expires_in = $response->expires_in;
        // $userData->scope = $response->scope;
        // $userData->allegro_api = $response->allegro_api;
        // $userData->jti = $response->jti;
        // $userData->save();

        return $response;
    }

    public function me()
    {
        $userData = UserData::where('user_id', Auth::user()->id)->get();
        $restApi = new AllegroRestApi($userData->access_token);
        $response = $restApi->get('/me');
    }

    public function parseHeaders(array $headers)
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

    public function getUrl()
    {
        // Returning correct URL depending on sandbox setting
        return $this->getSandbox() 
            ? AllegroRestApi::SANDBOX_URL 
            : AllegroRestApi::URL;
    }
}
