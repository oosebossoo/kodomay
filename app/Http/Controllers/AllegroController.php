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
        // $restApi = new AllegroRestApi('eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE2MTcyMzQ2MjgsInVzZXJfbmFtZSI6IjkzOTc5MDc2IiwianRpIjoiYTJkYTkzNDgtNGQ0MC00OTk1LTk2ZDQtYzEzYjU0OWUwZTBmIiwiY2xpZW50X2lkIjoiMTg0MmY0ZTAzZDEzNDdkNDgxMjI0NmY3NDM5YmFhYTEiLCJzY29wZSI6WyJhbGxlZ3JvOmFwaTpvcmRlcnM6cmVhZCIsImFsbGVncm86YXBpOnByb2ZpbGU6d3JpdGUiLCJhbGxlZ3JvOmFwaTpzYWxlOm9mZmVyczp3cml0ZSIsImFsbGVncm86YXBpOmJpbGxpbmc6cmVhZCIsImFsbGVncm86YXBpOmNhbXBhaWducyIsImFsbGVncm86YXBpOmRpc3B1dGVzIiwiYWxsZWdybzphcGk6c2FsZTpvZmZlcnM6cmVhZCIsImFsbGVncm86YXBpOmJpZHMiLCJhbGxlZ3JvOmFwaTpvcmRlcnM6d3JpdGUiLCJhbGxlZ3JvOmFwaTphZHMiLCJhbGxlZ3JvOmFwaTpwYXltZW50czp3cml0ZSIsImFsbGVncm86YXBpOnNhbGU6c2V0dGluZ3M6d3JpdGUiLCJhbGxlZ3JvOmFwaTpwcm9maWxlOnJlYWQiLCJhbGxlZ3JvOmFwaTpyYXRpbmdzIiwiYWxsZWdybzphcGk6c2FsZTpzZXR0aW5nczpyZWFkIiwiYWxsZWdybzphcGk6cGF5bWVudHM6cmVhZCJdLCJhbGxlZ3JvX2FwaSI6dHJ1ZX0.pyMO0rdgYi91RIUMHl5220ctFVtZQnOHPGPgOfoKS7_L9rnCRC2guxHYQfyowGJv-GXD3Z6OZLvxNFx8dEpqfISR0rXX7Ptms5jMnzYi4PR48TBEDSmv7QYnE9hU0s3WNNswbX1nAUQZjRRkvkc7gwm0scad8zro7spgsKKG2SIh6-T0CuJZ-4YDJvNjzmt8KAuamxP9VPNar3mxwMKUg9dr-xTW-U4Q3hTucmBOcWWq52bayo7aA8mn2inyFrwPFnT_AYRqv-G-viBZypS-om_btFj4Z9lwgzaS-cMRfmUA0A7sWLPp1KaryDZPiOkjIYalRSGesEfc6h9Glg_MizfxUfvsoVXj3zU7Qasxs8HFIgAVhCq0mE-r4CaMjBkOmge4OvnPyp9_s2oOVtO1UT3Al5OQrEVJs7DiWCjf4CPauYHoIu_H_7KqPPiTTXc2eR1xwA-ZYGci8qF9CxEZY1XZhZHbin_P09VM1D3ZdgPKO66QwBL9a1ciqH_5kdigfVTws_awyOa6lk-HH5Srg-Lf_3zPfvXSbmJYl7Kqpe4S5yzpC1uSkuguI4ZE-1T46D_Qas2bmZztrKX8r17gCZEUD4nRhdwGzltPBXRgNPkEXhkzSPCuRM6jtlVWh20wXzKEy1XYM_kHU21T8m-XMPBvuWZjCCe8W_vH-lpc5zc');
        // $restApi->get('/me');

        $resource = "https://allegro.pl.allegrosandbox.pl/me";

        // Setting request options
        $options = array(
            'http' => array(
                'method'  => strtoupper($method),
                'header'  => $this->parseHeaders($requestHeaders = array_replace(array(
                    'User-Agent'      => 'Kodomat',
                    'Authorization'   => 'Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE2MTcyMzQ2MjgsInVzZXJfbmFtZSI6IjkzOTc5MDc2IiwianRpIjoiYTJkYTkzNDgtNGQ0MC00OTk1LTk2ZDQtYzEzYjU0OWUwZTBmIiwiY2xpZW50X2lkIjoiMTg0MmY0ZTAzZDEzNDdkNDgxMjI0NmY3NDM5YmFhYTEiLCJzY29wZSI6WyJhbGxlZ3JvOmFwaTpvcmRlcnM6cmVhZCIsImFsbGVncm86YXBpOnByb2ZpbGU6d3JpdGUiLCJhbGxlZ3JvOmFwaTpzYWxlOm9mZmVyczp3cml0ZSIsImFsbGVncm86YXBpOmJpbGxpbmc6cmVhZCIsImFsbGVncm86YXBpOmNhbXBhaWducyIsImFsbGVncm86YXBpOmRpc3B1dGVzIiwiYWxsZWdybzphcGk6c2FsZTpvZmZlcnM6cmVhZCIsImFsbGVncm86YXBpOmJpZHMiLCJhbGxlZ3JvOmFwaTpvcmRlcnM6d3JpdGUiLCJhbGxlZ3JvOmFwaTphZHMiLCJhbGxlZ3JvOmFwaTpwYXltZW50czp3cml0ZSIsImFsbGVncm86YXBpOnNhbGU6c2V0dGluZ3M6d3JpdGUiLCJhbGxlZ3JvOmFwaTpwcm9maWxlOnJlYWQiLCJhbGxlZ3JvOmFwaTpyYXRpbmdzIiwiYWxsZWdybzphcGk6c2FsZTpzZXR0aW5nczpyZWFkIiwiYWxsZWdybzphcGk6cGF5bWVudHM6cmVhZCJdLCJhbGxlZ3JvX2FwaSI6dHJ1ZX0.pyMO0rdgYi91RIUMHl5220ctFVtZQnOHPGPgOfoKS7_L9rnCRC2guxHYQfyowGJv-GXD3Z6OZLvxNFx8dEpqfISR0rXX7Ptms5jMnzYi4PR48TBEDSmv7QYnE9hU0s3WNNswbX1nAUQZjRRkvkc7gwm0scad8zro7spgsKKG2SIh6-T0CuJZ-4YDJvNjzmt8KAuamxP9VPNar3mxwMKUg9dr-xTW-U4Q3hTucmBOcWWq52bayo7aA8mn2inyFrwPFnT_AYRqv-G-viBZypS-om_btFj4Z9lwgzaS-cMRfmUA0A7sWLPp1KaryDZPiOkjIYalRSGesEfc6h9Glg_MizfxUfvsoVXj3zU7Qasxs8HFIgAVhCq0mE-r4CaMjBkOmge4OvnPyp9_s2oOVtO1UT3Al5OQrEVJs7DiWCjf4CPauYHoIu_H_7KqPPiTTXc2eR1xwA-ZYGci8qF9CxEZY1XZhZHbin_P09VM1D3ZdgPKO66QwBL9a1ciqH_5kdigfVTws_awyOa6lk-HH5Srg-Lf_3zPfvXSbmJYl7Kqpe4S5yzpC1uSkuguI4ZE-1T46D_Qas2bmZztrKX8r17gCZEUD4nRhdwGzltPBXRgNPkEXhkzSPCuRM6jtlVWh20wXzKEy1XYM_kHU21T8m-XMPBvuWZjCCe8W_vH-lpc5zc',
                    'Content-Type'    => 'application/vnd.allegro.public.v1+json',
                    'Accept'          => 'application/vnd.allegro.public.v1+json',
                    'Accept-Language' => 'pl-PL'
                ), $headers)),
                'content' => ($json ? json_encode($data) : $data),
                'ignore_errors' => true
            )
        );

        // Getting result from API
        $response = json_decode(file_get_contents(
            (stristr($resource, 'http') !== false 
                ? $resource 
                : $this->getUrl() . '/' . ltrim($resource, '/')
            ), 
            false, 
            stream_context_create($options)
        ));
        
        // We have found an error in response
        if (isset($response->errors) || isset($response->error_description)) {

            // Throwing an exception
            throw new RuntimeException(
                'An error has occurred: ' . print_r($response, true),
                $this->getResponseCode($http_response_header)
            );
        }
        
        // Checking if our response is a valid object
        if (!is_object($response)) {
            
            // Creating an instance of stdClass
            $response = new \stdClass();
        }
        
        // Saving response and request headers
        $response->request_headers  = $requestHeaders;
        $response->response_headers = $http_response_header;
        
        // Returning response
        return $response;
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
