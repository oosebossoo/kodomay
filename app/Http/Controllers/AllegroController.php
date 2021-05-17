<?php

// --- [SANDBOX] PRODUKT DO TESTÓW
// https://allegro.pl.allegrosandbox.pl/oferta/uun2-8gb-srebrny-usb2-0-7680166142

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use AsocialMedia\AllegroApi\AllegroRestApi;
use App\Repos\AllegroRepo;

use App\Http\Controllers\MailController;

use App\Models\Customer;
use App\Models\UserData;
use App\Models\Orders;

use Auth;

class AllegroController extends Controller
{
    // --- SANDBOX ---
    // const SANDBOX_URL = 'https://api.allegro.pl.allegrosandbox.pl';
    // protected $clientId = '1842f4e03d1347d4812246f7439baaa1';
    // protected $clientSecret = 'JvRLfxOdGmLBNRooPqnxQJOKFnwZ7XW1bW5m7tPCNb1LPaw5ttje2g7Fcz0OS6ri';


    // --- PROD ---
    const SANDBOX_URL = 'https://api.allegro.pl.';
    protected $clientId = 'e27c3091a67a4edd8015191d4a26c66f';
    protected $clientSecret = '3JuWoxfQmMLK9da7BvS40sCMACFCjbGXPCepOnD3R4V4k87whYLy3KPLBle9UMro';

    public function getAuth()
    {
        return $this->getAuthRepo();
    }

    public function getToken(Request $request)
    {
        return $this->getTokenRepo($request);
    }

    public function refreshToken(Request $request)
    {
        return $this->refreshTokenRepo($request);
    }

    public function getAuthRepo()
    {

        $authUrl = "https://allegro.pl/auth/oauth/authorize?"
            ."response_type=code&"
            ."client_id=$this->clientId&"
            ."redirect_uri=https://kodomat.herokuapp.com/get_token";

        return redirect($authUrl);
    }

    public function getTokenRepo($request)
    {
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

        $userData = new UserData();
        $userData->user_id = Auth::user()->id;
        $userData->access_token = $response->access_token;
        $userData->token_type = $response->token_type;
        $userData->refresh_token = $response->refresh_token;
        $userData->expires_in = $response->expires_in;
        $userData->scope = $response->scope;
        $userData->allegro_api = $response->allegro_api;
        $userData->jti = $response->jti;
        $userData->save();
        dd($response);
        return $response;
    }

    

    public function refreshTokenRepo()
    {
        $clientId = '1842f4e03d1347d4812246f7439baaa1';
        $clientSecret = 'JvRLfxOdGmLBNRooPqnxQJOKFnwZ7XW1bW5m7tPCNb1LPaw5ttje2g7Fcz0OS6ri';
        $token = UserData::where('user_id', 7)->get()[1];
        // $refresh_token = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX25hbWUiOiI5Mzk3OTA3NiIsInNjb3BlIjpbImFsbGVncm86YXBpOm9yZGVyczpyZWFkIiwiYWxsZWdybzphcGk6cHJvZmlsZTp3cml0ZSIsImFsbGVncm86YXBpOnNhbGU6b2ZmZXJzOndyaXRlIiwiYWxsZWdybzphcGk6YmlsbGluZzpyZWFkIiwiYWxsZWdybzphcGk6Y2FtcGFpZ25zIiwiYWxsZWdybzphcGk6ZGlzcHV0ZXMiLCJhbGxlZ3JvOmFwaTpiaWRzIiwiYWxsZWdybzphcGk6c2FsZTpvZmZlcnM6cmVhZCIsImFsbGVncm86YXBpOm9yZGVyczp3cml0ZSIsImFsbGVncm86YXBpOmFkcyIsImFsbGVncm86YXBpOnBheW1lbnRzOndyaXRlIiwiYWxsZWdybzphcGk6c2FsZTpzZXR0aW5nczp3cml0ZSIsImFsbGVncm86YXBpOnByb2ZpbGU6cmVhZCIsImFsbGVncm86YXBpOnJhdGluZ3MiLCJhbGxlZ3JvOmFwaTpzYWxlOnNldHRpbmdzOnJlYWQiLCJhbGxlZ3JvOmFwaTpwYXltZW50czpyZWFkIl0sImFsbGVncm9fYXBpIjp0cnVlLCJhdGkiOiIxMjUyNjIxNy1iZDI4LTQ2MTAtOTg5YS0xZWRhODZjMDc2NzMiLCJleHAiOjE2MjU1NjIwOTQsImp0aSI6IjVjN2ViNzE3LTQ3YmUtNDAwYS04ZjhiLTM3YzZiMjU4MmY2YyIsImNsaWVudF9pZCI6IjE4NDJmNGUwM2QxMzQ3ZDQ4MTIyNDZmNzQzOWJhYWExIn0.TJah9nT8ZXLDRoUGyovgUM9suoldaNDeJAqjMfa6yZOzWrHPRfWaTluhTC0---XUh48_B6YC7mpXerqEnwcBoNa__LiastBu5rhzI3Jw_UwQF_BFYH7VD4QLIBrc2YWYe4wdyuIcRkq4wg2LumThAvJgsma0kAs518TJ5UCbsFpUYc3UcnNtrIdMgtVRAY0xxe-slMzDWhFIZ5It_JcAm_aRCZ31NlLXdFnx9UpP4ZgbKnYHo19kT0fGmNw4RDucVhKRwG19WpdKmbbM-Lr8958-48mYpCV0B1IAXnoNGQTL7MI8MKf-DK1I7hHhB_5NZdwuUzE3jOPyjPkqonai5xiC5b-QOySFYgibSohtlBxNp7bDaCq8tqt-8dpvfaVuB-rdW73L9pfrwwwpOEW7P7sIbmT9EPoivPFVgKVAWHXLPA2INk5o7wSKvpdazYCxpVZf7sBDVDhZOk5I9mwI1S6tfkBj0wVZz-gzaJuOYChjCqlFfT4ZJA2tPaH25jN-YpnlabcJYE_w-3T__9IeFixVc4GoIAjKUGYK2USULREiPNRu7d_HI4Uy-Odnpg8pdHyFzBWSbnSrBFdkYDGNemKa6Df9SN3Q4tEli9_aEkOZL7Xj15amz2W4nrGl8J74TgNwYxm-Uhtn73zYeuMMx_YulxX5m7kuup64uip4Kec';
        $token = UserData::where('user_id', Auth::user()->id)->get()[0];
        $headers = [ 
            "Accept: application/vnd.allegro.public.v1+json", 
            "Authorization: Basic " . base64_encode("$this->clientId:$$this->clientSecret")
        ];

        $post = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->refresh_token,
            'redirect_uri' => 'https://kodomat.herokuapp.com/get_token'
        ];

        $curl = curl_init("https://allegro.pl/auth/oauth/token");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_POST,true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        $response = curl_exec($curl);   
        curl_close($curl);

        // ----------------------------------------------------------------------------
        // ----------------------------+------------------+----------------------------
        // ----------------------------| TEST HTTP CLIENT |----------------------------
        // ----------------------------+--------|---------+----------------------------
        // -------------------------------------V--------------------------------------

        return response()->json($response);
    }

    public function mainFunction(Request $request)
    {
        $details = array();

        $userDatas = UserData::where('user_id', $request->user_id)->get();

        foreach ($userDatas as $userData)
        {
            // --- PRODUKCJA ---
            $response = Http::withHeaders([
                "Accept" => "application/vnd.allegro.public.v1+json",
                "Authorization" => "Bearer $userData->access_token"
            ])->get("https://api.allegro.pl/order/events?type=READY_FOR_PROCESSING");

            if(isset($response["events"])) {
                $res = $response["events"];
                $lastEvent = $res[0]["id"];
                if($res[0]["id"] != $userData->last_event) 
                {
                    $log[] = "new events: ".$res[0]["id"];
                    foreach ($res as $order) 
                    {
                        $existOrder = Orders::where('order_id', $order["id"])->get();
                        if(!isset($existOrder[0]["id"])) 
                        {
                            $log[] = "new order: ".$order["id"];
                            $detailsInfo = $this->checkOut($order["order"]["checkoutForm"]["id"], $userData->access_token);
                            
                            $buyer = $order["order"]["buyer"];

                            // $customer = new Customer;
                            // ...

                            $orderModel = new Orders;
                            $orderModel->offer_id = $detailsInfo->lineItems[0]->offer->id;
                            $orderModel->order_id = $order["id"];
                            $orderModel->offer_name = $detailsInfo->lineItems[0]->offer->name;
                            $orderModel->offer_price = $detailsInfo->lineItems[0]->originalPrice->amount;
                            $orderModel->offer_currency = $detailsInfo->lineItems[0]->originalPrice->currency;
                            $orderModel->quantity = $detailsInfo->lineItems[0]->quantity;
                            $orderModel->order_price = $detailsInfo->lineItems[0]->price->amount;
                            $orderModel->order_currency = $detailsInfo->lineItems[0]->price->currency;
                            $orderModel->customer_id = $buyer["id"];
                            $orderModel->order_date = $detailsInfo->lineItems[0]->boughtAt;
                            $orderModel->save();

                            // $this->changeStatus($order["order"]["checkoutForm"]["id"], $userData->access_token, "PROCESSING");
                            // $this->changeStatus("66b231c0-9789-11eb-80ab-8b7eefbb1428", "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX25hbWUiOiI5Mzk3OTA3NiIsInNjb3BlIjpbImFsbGVncm86YXBpOm9yZGVyczpyZWFkIiwiYWxsZWdybzphcGk6cHJvZmlsZTp3cml0ZSIsImFsbGVncm86YXBpOnNhbGU6b2ZmZXJzOndyaXRlIiwiYWxsZWdybzphcGk6YmlsbGluZzpyZWFkIiwiYWxsZWdybzphcGk6Y2FtcGFpZ25zIiwiYWxsZWdybzphcGk6ZGlzcHV0ZXMiLCJhbGxlZ3JvOmFwaTpiaWRzIiwiYWxsZWdybzphcGk6c2FsZTpvZmZlcnM6cmVhZCIsImFsbGVncm86YXBpOm9yZGVyczp3cml0ZSIsImFsbGVncm86YXBpOmFkcyIsImFsbGVncm86YXBpOnBheW1lbnRzOndyaXRlIiwiYWxsZWdybzphcGk6c2FsZTpzZXR0aW5nczp3cml0ZSIsImFsbGVncm86YXBpOnByb2ZpbGU6cmVhZCIsImFsbGVncm86YXBpOnJhdGluZ3MiLCJhbGxlZ3JvOmFwaTpzYWxlOnNldHRpbmdzOnJlYWQiLCJhbGxlZ3JvOmFwaTpwYXltZW50czpyZWFkIl0sImFsbGVncm9fYXBpIjp0cnVlLCJhdGkiOiJjZDZkZDg1Yi1jYjA3LTQ5ODgtYjA2Zi00ODZjZGU4ZDFiOGEiLCJleHAiOjE2Mjg0MDMwMTcsImp0aSI6IjQ3ODg4YjcyLWFlMzgtNDQxMy1hMjU5LWM2NTdmMjRhNTEyZiIsImNsaWVudF9pZCI6IjE4NDJmNGUwM2QxMzQ3ZDQ4MTIyNDZmNzQzOWJhYWExIn0.dGV6yg4BWAzWy65q4j-Q_Zkzt3d7aviCBCGvzY5HJEu_Vdmn22Dg8ZeGPK895HRQDjS5DAy8CQVmVqPz4b8lFIMQy_69hAaHO3-JEyPNk8IleGAUn9tYGJLJ7giUjnFZaBWfARMgirG1jgCjW1Dc32_5B2wtu_TddABlkrE1qRw4pC0lLoQpPB1tOq777wZMXr7VEnWrK_Rsqq6bQv99WnacJvedQ2OPePluYmyjJUEOqn-MuEqw6AWmJGej7s4b0tQARw5WkXPYUWsH2XoUYIaCa_zPdFVMLiPtXhJf3eZDLWG3ZK7vqLNjrioOB37SXBTuz5OQe-vJATNLXhWmtjEytRzbwiijcGCzZ-IdzxlMM7ZpMfbYMzTyiu88QgnW8L0lcm7exkvRelFQY1f8-VFsq26M-9ETiALN-V8w_Jcu5yXGH2kIhRv1ss6UboBFH_LZ6A90etAqI_BDIjHJh96cNfn8coNrRLb_Wt49PA209r6ChzQPIrtyrZdtjdamkTGq-PPPbdN6sTlRONoeI5jhb4c3NJsM7saULZnDPE73CzkoIVOhHvpcO13MNo5V_YxWpkOvfDulClFqi9iokJgTutmx3pOHOD5UR6dxYZH_Md9Fti8hega-WQuIR255WAb55kseYKguIQy3nhax7zfd8XS3XVxe4k224xBwFC0", "PROCESSING");
                            // MailController::sendCode([
                            //     "customerName" => $buyer["login"],
                            //     "mail" => $buyer["email"],
                            //     "subject" => $order["id"]
                            // ]);
                            // zmień status zamówienia !!!!
                            // $temp = $this->checkOut("66b231c0-9789-11eb-80ab-8b7eefbb1428", "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX25hbWUiOiI5Mzk3OTA3NiIsInNjb3BlIjpbImFsbGVncm86YXBpOm9yZGVyczpyZWFkIiwiYWxsZWdybzphcGk6cHJvZmlsZTp3cml0ZSIsImFsbGVncm86YXBpOnNhbGU6b2ZmZXJzOndyaXRlIiwiYWxsZWdybzphcGk6YmlsbGluZzpyZWFkIiwiYWxsZWdybzphcGk6Y2FtcGFpZ25zIiwiYWxsZWdybzphcGk6ZGlzcHV0ZXMiLCJhbGxlZ3JvOmFwaTpiaWRzIiwiYWxsZWdybzphcGk6c2FsZTpvZmZlcnM6cmVhZCIsImFsbGVncm86YXBpOm9yZGVyczp3cml0ZSIsImFsbGVncm86YXBpOmFkcyIsImFsbGVncm86YXBpOnBheW1lbnRzOndyaXRlIiwiYWxsZWdybzphcGk6c2FsZTpzZXR0aW5nczp3cml0ZSIsImFsbGVncm86YXBpOnByb2ZpbGU6cmVhZCIsImFsbGVncm86YXBpOnJhdGluZ3MiLCJhbGxlZ3JvOmFwaTpzYWxlOnNldHRpbmdzOnJlYWQiLCJhbGxlZ3JvOmFwaTpwYXltZW50czpyZWFkIl0sImFsbGVncm9fYXBpIjp0cnVlLCJhdGkiOiJjZDZkZDg1Yi1jYjA3LTQ5ODgtYjA2Zi00ODZjZGU4ZDFiOGEiLCJleHAiOjE2Mjg0MDMwMTcsImp0aSI6IjQ3ODg4YjcyLWFlMzgtNDQxMy1hMjU5LWM2NTdmMjRhNTEyZiIsImNsaWVudF9pZCI6IjE4NDJmNGUwM2QxMzQ3ZDQ4MTIyNDZmNzQzOWJhYWExIn0.dGV6yg4BWAzWy65q4j-Q_Zkzt3d7aviCBCGvzY5HJEu_Vdmn22Dg8ZeGPK895HRQDjS5DAy8CQVmVqPz4b8lFIMQy_69hAaHO3-JEyPNk8IleGAUn9tYGJLJ7giUjnFZaBWfARMgirG1jgCjW1Dc32_5B2wtu_TddABlkrE1qRw4pC0lLoQpPB1tOq777wZMXr7VEnWrK_Rsqq6bQv99WnacJvedQ2OPePluYmyjJUEOqn-MuEqw6AWmJGej7s4b0tQARw5WkXPYUWsH2XoUYIaCa_zPdFVMLiPtXhJf3eZDLWG3ZK7vqLNjrioOB37SXBTuz5OQe-vJATNLXhWmtjEytRzbwiijcGCzZ-IdzxlMM7ZpMfbYMzTyiu88QgnW8L0lcm7exkvRelFQY1f8-VFsq26M-9ETiALN-V8w_Jcu5yXGH2kIhRv1ss6UboBFH_LZ6A90etAqI_BDIjHJh96cNfn8coNrRLb_Wt49PA209r6ChzQPIrtyrZdtjdamkTGq-PPPbdN6sTlRONoeI5jhb4c3NJsM7saULZnDPE73CzkoIVOhHvpcO13MNo5V_YxWpkOvfDulClFqi9iokJgTutmx3pOHOD5UR6dxYZH_Md9Fti8hega-WQuIR255WAb55kseYKguIQy3nhax7zfd8XS3XVxe4k224xBwFC0");
                            // return $temp;
                            // $this->changeStatus($order["order"]["checkoutForm"]["id"], $userData->access_token, "SENT");
                            // $this->checkOut($order["order"]["checkoutForm"]["id"], $userData->access_token);
                            
                            $details[] = $orderModel;
                        }
                        else {
                            $log[] = "old order: ".$order["id"];
                        }
                        // dd([$details, $log]);
                    }
                    $userData->last_event = $lastEvent;
                    $userData->save();
                    $status = 0;
                    $desc = "Oh yhee.. some new orders :) ";
                }
                else{
                    $log[] = "last order: ".$lastEvent;
                    $status = 0;
                    $desc = "Please... give me some orders :( ";
                }
                // zmiana w badzie danych ostatniego eventu
            }
            else {
                $status = 1;
                $desc = "Och no, i cant see any allegro account... :( ";
            }     
            unset($res);
        }
        return ["status" => $status, "desc" => $desc, $log];
    }

    public function getLastEvent(Request $request)
    {
        $userData = UserData::where('user_id', $request->user_id)->get();
        // dd($userData[0]["access_token"]);
        return $this->getLastEventRepo($userData[0]["access_token"]);
    }

    public static function getLastEventRepo($userData)
    {
        $response = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
            "Authorization" => "Bearer $userData"
        ])->get("https://api.allegro.pl/order/event-stats");
        return $response["latestEvent"]["id"];
    }

    public static function changeStatus($checkOutFormId, $token, $status)
    {
        $response = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
            "Content-Type" => "application/vnd.allegro.public.v1+json",
            "Authorization" => 'Bearer '.$token
        ])->put("https://api.allegro.pl/order/checkout-forms/$checkOutFormId/fulfillment", [
                "body" => ["status" => $status]
            ]);

        dd($response);
    }

    public static function checkOut($checkOutFormId, $token)
    {
        $response = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
            "Authorization" => "Bearer $token"
        ])->get("https://api.allegro.pl/order/checkout-forms/$checkOutFormId");
        return json_decode($response);
    }

    public function getAllegroUsers()
    {
        // --- PRODUKCJA --- 
        $userDatas = UserData::where('user_id',Auth::user()->id)->get();

        $users = array();
        foreach ($userDatas as $userData)
        {
            // dd($token);
            $response = Http::withHeaders([
                "Accept" => "application/vnd.allegro.public.v1+json",
                "Authorization" => "Bearer $userData->access_token"
            ])->get("https://api.allegro.pl/me"); 
            if(!isset($response["error"])) {   
                $user[] = json_decode($response);
            }
            else {
                $this->refreshTokenRepo($userData->refresh_token);
                $user[] = json_decode($response);
            }  
        }

        // --- DEV TEST ---
        // $tokens = UserData::where('user_id', 7)->get();
        // foreach ($tokens as $token)
        // {
        //     $response = Http::withHeaders([
        //         "Accept" => "application/vnd.allegro.public.v1+json",
        //         "Authorization" => "Bearer $token->access_token"
        //     ])->get("https://api.allegro.pl.allegrosandbox.pl/me");
        //     if(!isset($response["error"])) {   
        //         // refresh 
        //         $user[] = json_decode($response);
        //     }
        //     else {
        //         redirect("/get_auth?refresh_token=$token->refresh_token");
        //         $user[] = json_decode($response);
        //     }
        // }
        return response()->json($user);
    }

    public function getOrders()
    {
        $orders = Orders::select('id','offer_id', 'offer_name', 'order_price', 'order_currency', 'customer_id', 'order_date')->where('order_currency', "PLN")->orderBy('order_date', 'desc')->get();

        return $orders;
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
