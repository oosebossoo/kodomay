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
use App\Models\OrdersTable;
use App\Models\Offers;

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

    public function getAuth(Request $request)
    {
        if(isset($request->opt))
        {
            UserData::where('user_id', Auth::user()->id)->update([
                'refresh' => true
            ]);
        }
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

        if(UserData::select('refresh')->where('refresh', 1)->get())
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
            return ['status' => 'added new account'];
        }
    }

    

    public function refreshTokenRepo()
    {
        $clientId = 'e27c3091a67a4edd8015191d4a26c66f';
        $clientSecret = '3JuWoxfQmMLK9da7BvS40sCMACFCjbGXPCepOnD3R4V4k87whYLy3KPLBle9UMro';
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

        $curl = curl_init("https://allegro.pl/auth/oauth/token?grant_type=refresh_token&refresh_token=$token->refresh_token&redirect_uri=https://kodomat.herokuapp.com/get_token");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST,true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        $response = curl_exec($curl);   
        curl_close($curl);

        // ----------------------------------------------------------------------------
        // ----------------------------+------------------+----------------------------
        // ----------------------------| TEST HTTP CLIENT |----------------------------
        // ----------------------------+--------|---------+----------------------------
        // -------------------------------------V--------------------------------------

        // $response = Http::withHeaders([
        //     "Accept" => "application/vnd.allegro.public.v1+json",
        //     "Authorization" => "Basic " . base64_encode("$this->clientId:$$this->clientSecret")
        // ])->post("https://allegro.pl/auth/oauth/token?grant_type=refresh_token&refresh_token=$token->refresh_token&redirect_uri=https://kodomat.herokuapp.com/get_token");

        dd($response);
    }

    public function endOfGettingToken(Request $request)
    {
        return $request;
    }

    public function setOffer(Request $request)
    {
        $offer = Offers::where('offer_id', $request->offer_id)->first();

        if($offer->is_active == 'NO')
        {
            //dd(Offers::where('offer_id', $request->offer_id)->first());
            Offers::where('offer_id', $request->offer_id)->update([ 'is_active' => 'YES' ]);
            $status = ['is_active' => 'YES'];
        }

        if($offer->is_active == 'YES')
        {
            Offers::where('offer_id', $request->offer_id)->update([ 'is_active' => 'NO' ]);
            $status = ['is_active' => 'NO'];
        }

        if(isset($status))
        {
            return $status;
        }
        else
        {
            return ['some goes wrong... :('];
        }
    }

    public function getOffer(Request $request)
    {
        $limit = 100;
        if(isset($request->limit))
        {
            $limit = $request->limit;
        }
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }
        $userData = UserData::where('user_id', $user_id)->get()[0];

        // dd($request->refresh);
        if($request->refresh == "set")
        {
            $response = Http::withHeaders([
                "Accept" => "application/vnd.allegro.public.v1+json",
                "Authorization" => "Bearer $userData->access_token"
            ])->get("https://api.allegro.pl/sale/offers?limit=100");

            // return $response['offers'];
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
        else
        {
            return Offers::where('seller_id', $user_id)->get();
        }
        return Offers::where('seller_id', $user_id)->get();

    }

    public function getCustomers(Request $request)
    {
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        $oderBy = 'desc';
        $limit = 100;
        $customerId = ['sing' => '!=', 'id' => ''];

        if(isset($request->oderBy))
        {
            if($request->oderBy == 'desc')
            {
                $oderBy = 'desc';
            }
            elseif($request->oderBy == 'asc')
            {
                $oderBy = 'asc';
            }
        }

        if(isset($request->limit))
        {
            if(is_numeric($request->limit))
            {
                $limit = $request->limit;
            }
            else
            {
                return ['error' => 'wrong number... :('];
            }
        }

        if(isset($request->customer_id))
        {
            $customerId['sing'] = '=';
            $customerId['id'] = $request->customer_id;
        }

        if(isset($request->date))
        {
            
            $from = date($request->date . " 00:00:00");
            $to = date($request->date . " 23:59:59");
            $customers = Customer::where('seller_id', $user_id)->whereBetween('created_at', [$from, $to])->where('customer_id', $customerId['sing'], $customerId['id'])->limit($limit)->get();
        }

        $customers = Customer::where('seller_id', $user_id)->where('customer_id', $customerId['sing'], $customerId['id'])->limit($limit)->get();

        foreach($customers as $customer)
        {
            $response[] = [ 
                'customer' => $customer, 
                'customer_orders' => $this->getCustomerOrders($customer->customer_id, $request->dev)
            ];
        }

        return $response;
    }

    public function getCustomerOrders($customer_id, $dev)
    {
        if(isset($dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }
        $orders_table = OrdersTable::where('customer_id', $customer_id)->where('seller_id', $user_id)->get();
        foreach($orders_table as $order_table)
        {
            $response[] = ["name" => $order_table->offer_id, "link" => $order_table->offer_link  ,"count" => $order_table->count];
        }
        if(isset($response))
        {
            return $response;
        }
        return [
            'status' => 'no data in db... sorry :('
        ];
    }

    public static function createCustomerOffer(Request $request)
    {

    }

    public function cancelOrder(Request $request)
    {
        return Order::where('id', $request->id)->update(['status' => "canceled"]);
    }

    public function mainFunction(Request $request)
    {
        $details = array();

        $log[] = "";

        $userDatas = UserData::where('user_id', $request->user_id)->get();

        foreach ($userDatas as $userData)
        {
            $response = Http::withHeaders([
                "Accept" => "application/vnd.allegro.public.v1+json",
                "Authorization" => "Bearer $userData->access_token"
            ])->get("https://api.allegro.pl/order/events?type=READY_FOR_PROCESSING&from=$userData->last_event");

            if($response->failed() || $response->clientError())
            {
                UserData::where('user_id', $request->user_id)->update([
                    'refresh' => true
                ]);
                return $this->getAuthRepo();
            }
            if($response["events"] != []) {
                $res = $response["events"];
                $lastEvent = $res[0]["id"];
                if($res[0]["id"] != $userData->last_event) 
                {
                    $log[] = "new events: ".$res[0]["id"];
                    foreach ($res as $order) 
                    {
                        $existOrder = Orders::where('order_id', $order["id"])->get();
                        $detailsInfo = $this->checkOut($order["order"]["checkoutForm"]["id"], $userData->access_token);
                        
                        $isActive = Offers::where('offer_id', $detailsInfo->lineItems[0]->offer->id)->first();
                        // dd($existOrder);
                        // dd($detailsInfo);
                        // dd($isActive['is_active'], $detailsInfo->lineItems[0]->offer->id);
                        if(!isset($existOrder[0]["id"]) && $isActive['is_active'] == "YES") 
                        {
                            $log[] = "new order: ".$order["id"];
                            $buyer = $order["order"]["buyer"];
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
                            $orderModel->seller_id = $request->user_id;
                            $orderModel->order_date = $detailsInfo->lineItems[0]->boughtAt;
                            $orderModel->save();

                            if(Customer::where('customer_id', $buyer["id"])->exists())
                            {
                                Customer::where('customer_id', $buyer["id"])->update(['orders' => Orders::where('customer_id', $buyer["id"])->count()]);

                                if(OrdersTable::where('offer_id', $detailsInfo->lineItems[0]->offer->id)->where('customer_id',  $buyer["id"])->exists())
                                {
                                    OrdersTable::where('customer_id', $buyer["id"])
                                        ->where('offer_id', $detailsInfo->lineItems[0]->offer->id)
                                        ->update([
                                            'count' => Orders::where('customer_id', $buyer["id"])->where('offer_id', $detailsInfo->lineItems[0]->offer->id)->count()
                                    ]);
                                }
                                else
                                {
                                    $order_table = new OrdersTable;
                                    $order_table->seller_id = $request->user_id;
                                    $order_table->customer_id = $buyer["id"];
                                    $order_table->offer_id = $detailsInfo->lineItems[0]->offer->id;
                                    $order_table->offer_link = "https://www.allegro.pl/oferta/".$detailsInfo->lineItems[0]->offer->id;
                                    $order_table->count = 1;
                                    $order_table->save();
                                }
                            }
                            else 
                            {
                                if(OrdersTable::where('offer_id', $detailsInfo->lineItems[0]->offer->id)->where('customer_id',  $buyer["id"])->exists())
                                {
                                    $tests[] = "jeśli ordersTable istnieje";
                                    OrdersTable::where('customer_id', $buyer["id"])
                                        ->where('offer_id', $detailsInfo->lineItems[0]->offer->id)
                                        ->update([
                                            'count' => Orders::where('customer_id', $buyer["id"])->where('offer_id', $detailsInfo->lineItems[0]->offer->id)->count()
                                    ]);
                                }
                                else
                                {
                                    $order_table = new OrdersTable;
                                    $order_table->seller_id = $request->user_id;
                                    $order_table->customer_id = $buyer["id"];
                                    $order_table->offer_id = $detailsInfo->lineItems[0]->offer->id;
                                    $order_table->offer_link = "https://www.allegro.pl/oferta/".$detailsInfo->lineItems[0]->offer->id;
                                    $order_table->count = 1;
                                    $order_table->save();
                                }
                                $customer = new Customer;
                                $customer->customer_id = $buyer["id"];
                                $customer->seller_id = $request->user_id;
                                $customer->login = $buyer["login"];
                                $customer->email = $buyer["email"];
                                $customer->first_name = $detailsInfo->buyer->firstName;
                                $customer->last_name = $detailsInfo->buyer->lastName;
                                $customer->adress = $detailsInfo->buyer->address->street;
                                $customer->city = $detailsInfo->buyer->address->city;
                                $customer->no_tel = $detailsInfo->buyer->phoneNumber;
                                $customer->office = $detailsInfo->buyer->companyName;
                                $customer->guest = $buyer["guest"];
                                $customer->orders = 1;
                                $customer->save();
                            }

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
                            $lastEvent = $order["id"];
                            $details[] = $orderModel;
                        }
                        else {
                            $lastEvent = $order["id"];
                            $log[] = "old order: ".$order["id"];
                        }
                        // dd(['details' => $details, 'log' => $log, $detailsInfo->lineItems[0]->offer->id, $isActive['is_active']]);
                        // dd(['debug' => $tests]);
                    }
                    $status = 0;
                    $desc = "Oh yhee.. some new orders :) ";
                }
                else{
                    $log[] = "last order: ".$lastEvent;
                    $status = 0;
                    $desc = "Please... give me some orders :( ";
                }
                $userData->last_event = $lastEvent;
                $userData->save();
                // zmiana w badzie danych ostatniego eventu
            }
            else {
                $status = 0;
                $desc = "Please... give me some orders :( ";
            }     
            unset($res);
        }
        return [date("Y-m-d") .'/'. date("H:i:s") => ["status" => $status, "desc" => $desc, $log]];
    }

    // public function manualCheckOut(Request $request)
    // {
    //     $this->changeStatus($request->checkoutFormId, $request->access_token, "SENT");
    // }
    public static function getOfferLink($offerId, $token)
    {
        $response = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
            "Authorization" => "Bearer $token"
        ])->get("https://api.allegro.pl/sale/offers?offer.id=$offerId");
        return json_decode($response);
    }

    public function getLastEvent(Request $request)
    {
        $userData = UserData::where('user_id', $request->user_id)->get();
        return $this->getLastEventRepo($userData[0]["access_token"]);
    }

    public static function getLastEventRepo($userData)
    {
        $response = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
            "Authorization" => "Bearer $userData"
        ])->get("https://api.allegro.pl/order/event-stats");
        return $response["latestEvent"];
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
            $response = Http::withHeaders([
                "Accept" => "application/vnd.allegro.public.v1+json",
                "Authorization" => "Bearer $userData->access_token"
            ])->get("https://api.allegro.pl/me"); 
            if(!isset($response["error"])) {   
                $user[] = json_decode($response);
                return $user;
            }
            else {
                $user[] = json_decode($response);
            }  
        }
        return response()->json($user);
    }

    public function getOrders(Request $request)
    {
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        $oderBy = 'desc';
        $limit = 100;
        $offerId = ['sing' => '!=', 'id' => ''];
        $from = date('2000-01-01');
        $to = date('3000-01-01');

        if(isset($request->oderBy))
        {
            if($request->oderBy == 'desc')
            {
                $oderBy = 'desc';
            }
            elseif($request->oderBy == 'asc')
            {
                $oderBy = 'asc';
            }
        }

        if(isset($request->limit))
        {
            if(is_numeric($request->limit))
            {
                $limit = $request->limit;
            }
            else
            {
                return ['error' => 'wrong number... :('];
            }
        }

        if(isset($request->offer_id))
        {
            $offerId['sing'] = '=';
            $offerId['id'] = $request->offer_id;
        }

        if(isset($request->from))
        {
            $from = date($request->from);
        }

        if(isset($request->to))
        {
            $to = date($request->to);
        }

        $orders = Orders::where('seller_id', $user_id)->where('offer_id', $offerId['sing'], $offerId['id'])->whereBetween('order_date', [$from, $to])->orderBy('order_date', $oderBy)->limit($limit)->get();

        return $orders;
    }

    // --- ---
    // --- ---
    // --- ---
    // --- ---
    // --- ---

    function addCustomer()
    {

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
