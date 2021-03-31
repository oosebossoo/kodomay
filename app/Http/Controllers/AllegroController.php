<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use AsocialMedia\AllegroApi\AllegroRestApi;
use App\Repos\AllegroRepo;

use App\Models\UserData;

use Auth;

class AllegroController extends Controller
{
    // Auth::user()->token;
    // protected $token;
    const SANDBOX_URL = 'https://api.allegro.pl.allegrosandbox.pl';

    protected $clientId = '1842f4e03d1347d4812246f7439baaa1';
    protected $clientSecret = 'JvRLfxOdGmLBNRooPqnxQJOKFnwZ7XW1bW5m7tPCNb1LPaw5ttje2g7Fcz0OS6ri';

    // public function __construct(AllegroReop $allegroRepo)
    // {
    //     $this->allegroRepo = $allegroRepo;
    // }

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

        $resource = "https://allegro.pl.allegrosandbox.pl/auth/oauth/token?"
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
            stream_context_create($options),
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

    public function getOrderEvents()
    {
        $json = true;
        $token = "token";

        $resource = "https://allegro.pl.allegrosandbox.pl/order/events";

        $headers = array();
        $data = array();

        $options = array(
            'http' => array(
                'method'  => strtoupper('POST'),
                'header'  => $this->parseHeaders($requestHeaders = array_replace(array(
                    'Authorization'   => 'Bearer  ' . $token,
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
        // $userData = UserData::where('user_id', Auth::user()->id)->get();
        $restApi = new AllegroRestApi(/*$userData->access_token*/ "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE2MTcyMzMzNjMsInVzZXJfbmFtZSI6IjkzOTc5MDc2IiwianRpIjoiYjkxYjVkYjItYjhhNS00NDEzLTkxZjItMTZkNDgzZTc4MTM4IiwiY2xpZW50X2lkIjoiMTg0MmY0ZTAzZDEzNDdkNDgxMjI0NmY3NDM5YmFhYTEiLCJzY29wZSI6WyJhbGxlZ3JvOmFwaTpvcmRlcnM6cmVhZCIsImFsbGVncm86YXBpOnByb2ZpbGU6d3JpdGUiLCJhbGxlZ3JvOmFwaTpzYWxlOm9mZmVyczp3cml0ZSIsImFsbGVncm86YXBpOmJpbGxpbmc6cmVhZCIsImFsbGVncm86YXBpOmNhbXBhaWducyIsImFsbGVncm86YXBpOmRpc3B1dGVzIiwiYWxsZWdybzphcGk6c2FsZTpvZmZlcnM6cmVhZCIsImFsbGVncm86YXBpOmJpZHMiLCJhbGxlZ3JvOmFwaTpvcmRlcnM6d3JpdGUiLCJhbGxlZ3JvOmFwaTphZHMiLCJhbGxlZ3JvOmFwaTpwYXltZW50czp3cml0ZSIsImFsbGVncm86YXBpOnNhbGU6c2V0dGluZ3M6d3JpdGUiLCJhbGxlZ3JvOmFwaTpwcm9maWxlOnJlYWQiLCJhbGxlZ3JvOmFwaTpyYXRpbmdzIiwiYWxsZWdybzphcGk6c2FsZTpzZXR0aW5nczpyZWFkIiwiYWxsZWdybzphcGk6cGF5bWVudHM6cmVhZCJdLCJhbGxlZ3JvX2FwaSI6dHJ1ZX0.n_CmfF7pk9kdjbk2SEhFMcYm1cr7Nrzr9WZxZZ3QfmGEITWBf8VL6joN55hBz8ztlvDcud_IAVlAMKDcvFgj10q5O9FkqTORPIGOlZ9sMtzpNNuc6uO3ZKIP2oeP7WfBBMdWJ6kp5HiI2swlqAI28J1keo0GKLpHSni3-g8jJ0MmC0pTvvNAqWOo0gaqNDk5UoJZbjz-KM__CX9fVOe2pzFN_LIzku-ja2TWHEMKyhk5NlYKYV7J-OQAGQu4iAG_--sweC6KvDJ4N-TZDkq0YydMY-E68dNWXRogsH3_alRxWfURjOzdJVRq1cr48lC4PTIpPSBFHo_0dI0F-R2L6B9gmm9CVksZF_W_DU_XO8zfFq5yT6efxBu3PRICjTFnfFuNq6foTYQjcBcbXvU7d7R1ruhgE0GjkOQpVX3qoxQBuBRKoIOJv_zU8_RoKyPZ-m3znh9szOzb6cINj6WDoo46Zru3uB7r8VFsAvYhO3yChVhHOqyA1BNlbe75TXuRCxOVUbo98ZNcd5sXDHw6PWSXE1e0bipuLV9TqO7y8AliDrciRy_TzuLqzdfAZlvNdFiZlGIFrrk0HRW4w4Klu7RqNcax3PUa5WuYYPWMxXdfIX9Mb40owmdmo1BQn4Is2NxOceUoJjP5g2HGHVH3mg_EzJDungyKMMM_MzQLoGo");
        $response = $restApi->get('/me');
        dd($response);
    }

    // --- ---
    // --- ---
    // --- ---
    // --- ---
    // --- ---

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
