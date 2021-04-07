<?php

// https://allegro.pl.allegrosandbox.pl/oferta/uun2-8gb-srebrny-usb2-0-7680166142

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

    public function refreshToken()
    {
        //$token = UserData::where('user_id', 7)->get()[0];
        $refresh_token = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX25hbWUiOiI5Mzk3OTA3NiIsInNjb3BlIjpbImFsbGVncm86YXBpOm9yZGVyczpyZWFkIiwiYWxsZWdybzphcGk6cHJvZmlsZTp3cml0ZSIsImFsbGVncm86YXBpOnNhbGU6b2ZmZXJzOndyaXRlIiwiYWxsZWdybzphcGk6YmlsbGluZzpyZWFkIiwiYWxsZWdybzphcGk6Y2FtcGFpZ25zIiwiYWxsZWdybzphcGk6ZGlzcHV0ZXMiLCJhbGxlZ3JvOmFwaTpiaWRzIiwiYWxsZWdybzphcGk6c2FsZTpvZmZlcnM6cmVhZCIsImFsbGVncm86YXBpOm9yZGVyczp3cml0ZSIsImFsbGVncm86YXBpOmFkcyIsImFsbGVncm86YXBpOnBheW1lbnRzOndyaXRlIiwiYWxsZWdybzphcGk6c2FsZTpzZXR0aW5nczp3cml0ZSIsImFsbGVncm86YXBpOnByb2ZpbGU6cmVhZCIsImFsbGVncm86YXBpOnJhdGluZ3MiLCJhbGxlZ3JvOmFwaTpzYWxlOnNldHRpbmdzOnJlYWQiLCJhbGxlZ3JvOmFwaTpwYXltZW50czpyZWFkIl0sImFsbGVncm9fYXBpIjp0cnVlLCJhdGkiOiIxMjUyNjIxNy1iZDI4LTQ2MTAtOTg5YS0xZWRhODZjMDc2NzMiLCJleHAiOjE2MjU1NjIwOTQsImp0aSI6IjVjN2ViNzE3LTQ3YmUtNDAwYS04ZjhiLTM3YzZiMjU4MmY2YyIsImNsaWVudF9pZCI6IjE4NDJmNGUwM2QxMzQ3ZDQ4MTIyNDZmNzQzOWJhYWExIn0.TJah9nT8ZXLDRoUGyovgUM9suoldaNDeJAqjMfa6yZOzWrHPRfWaTluhTC0---XUh48_B6YC7mpXerqEnwcBoNa__LiastBu5rhzI3Jw_UwQF_BFYH7VD4QLIBrc2YWYe4wdyuIcRkq4wg2LumThAvJgsma0kAs518TJ5UCbsFpUYc3UcnNtrIdMgtVRAY0xxe-slMzDWhFIZ5It_JcAm_aRCZ31NlLXdFnx9UpP4ZgbKnYHo19kT0fGmNw4RDucVhKRwG19WpdKmbbM-Lr8958-48mYpCV0B1IAXnoNGQTL7MI8MKf-DK1I7hHhB_5NZdwuUzE3jOPyjPkqonai5xiC5b-QOySFYgibSohtlBxNp7bDaCq8tqt-8dpvfaVuB-rdW73L9pfrwwwpOEW7P7sIbmT9EPoivPFVgKVAWHXLPA2INk5o7wSKvpdazYCxpVZf7sBDVDhZOk5I9mwI1S6tfkBj0wVZz-gzaJuOYChjCqlFfT4ZJA2tPaH25jN-YpnlabcJYE_w-3T__9IeFixVc4GoIAjKUGYK2USULREiPNRu7d_HI4Uy-Odnpg8pdHyFzBWSbnSrBFdkYDGNemKa6Df9SN3Q4tEli9_aEkOZL7Xj15amz2W4nrGl8J74TgNwYxm-Uhtn73zYeuMMx_YulxX5m7kuup64uip4Kec';
        // $token = UserData::where('user_id', Auth::user()->id)->get()[0];

        $headers = [ 
            "Accept" => "application/vnd.allegro.public.v1+json", 
            "Authorization" => "Basic " . base64_encode("$this->clientId:$this->clientSecret")
        ];

        $post = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'redirect_uri' => 'https://kodomat.herokuapp.com/get_token'
        ];

        $curl = curl_init("https://allegro.pl/auth/oauth/token");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        $response = curl_exec($curl);   
        curl_close($curl);
        return response()->json($response);
    }

    public function getOrderEvents()
    {
        $token = UserData::where('user_id', 7)->get()[0];
        // $token = UserData::where('user_id', Auth::user()->id)->get()[0];

        $headers = [ 
            "Accept: application/vnd.allegro.public.v1+json", 
            "Authorization: Bearer $token->access_token"
        ];

        $curl = curl_init("https://api.allegro.pl.allegrosandbox.pl/order/events");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);   
        curl_close($curl);
        return response()->json($response);
    }

    public function checkoutForms()
    {
        $token = UserData::where('user_id', 7)->get()[0];
        // $token = UserData::where('user_id', Auth::user()->id)->get()[0];

        $headers = [ 
            "Accept: application/vnd.allegro.public.v1+json", 
            "Authorization: Bearer $token->access_token"
        ];

        $curl = curl_init("https://api.allegro.pl.allegrosandbox.pl/order/checkout-forms/81818531-93a8-11eb-b68b-59dfcd1e885f");

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl); 

        curl_close($curl);

        return response()->json($response);
    }

    public function me()
    {
        $token = UserData::where('user_id', 7)->get()[0];
        // $token = UserData::where('user_id', Auth::user()->id)->get()[0];

        $headers = [ 
            "Accept: application/vnd.allegro.public.v1+json", 
            "Authorization: Bearer $token->access_token"
        ];

        $curl = curl_init("https://api.allegro.pl.allegrosandbox.pl/me");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);   
        curl_close($curl);
        return response()->json($response);
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
