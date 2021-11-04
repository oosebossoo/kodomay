<?php
// --- [SANDBOX] PRODUKT DO TESTÓW
// https://allegro.pl.allegrosandbox.pl/oferta/uun2-8gb-srebrny-usb2-0-7680166142

// db_password: e8khH0BUfi
// pass: DCF268D263E2DE84

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use AsocialMedia\AllegroApi\AllegroRestApi;

use App\Repositories\IntegrationRepository;
use App\Repositories\AllegroAccountRepository;
use App\Repositories\AllegroMainFunction;

use App\Http\Controllers\MailController;

use App\Models\Customer;
use App\Models\UserData;
use App\Models\Orders;
use App\Models\OrdersTable;
use App\Models\Offers;
use App\Models\SentMail;
use App\Models\Code;

use Auth;
use JWTAuth;

class AllegroController extends Controller
{
    // --- PROD ---
    protected $id;
    const SANDBOX_URL = 'https://api.allegro.pl.';
    protected $clientId = 'e27c3091a67a4edd8015191d4a26c66f';
    protected $clientSecret = '3JuWoxfQmMLK9da7BvS40sCMACFCjbGXPCepOnD3R4V4k87whYLy3KPLBle9UMro';

    public function __construct(IntegrationRepository $integrationRepo, AllegroAccountRepository $allegroAccountRepo, AllegroMainFunction $allegroMainFunction, JWTAuth $jwtAuth)
    {
        // $this->user = $jwtAuth::parseToken()->authenticate();
        // $this->jwtAuth = $jwtAuth;
        // $id = $this->user->id;

        $this->integrationRepo = $integrationRepo;
        $this->allegroAccountRepo = $allegroAccountRepo;
        $this->allegroMainFunction = $allegroMainFunction;
    }
    
    public function test()
    {
        $response = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
            "Authorization" => "Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE2MzU4ODUwOTgsInVzZXJfbmFtZSI6IjEwMTAyNTEwNyIsImp0aSI6IjJlNzMyZjc2LTgzNWQtNDVlYS1iZDBlLTgyMDc4ZWI0ZDM4NCIsImNsaWVudF9pZCI6ImUyN2MzMDkxYTY3YTRlZGQ4MDE1MTkxZDRhMjZjNjZmIiwic2NvcGUiOlsiYWxsZWdybzphcGk6b3JkZXJzOnJlYWQiLCJhbGxlZ3JvOmFwaTpwcm9maWxlOndyaXRlIiwiYWxsZWdybzphcGk6c2FsZTpvZmZlcnM6d3JpdGUiLCJhbGxlZ3JvOmFwaTpiaWxsaW5nOnJlYWQiLCJhbGxlZ3JvOmFwaTpjYW1wYWlnbnMiLCJhbGxlZ3JvOmFwaTpkaXNwdXRlcyIsImFsbGVncm86YXBpOnNhbGU6b2ZmZXJzOnJlYWQiLCJhbGxlZ3JvOmFwaTpiaWRzIiwiYWxsZWdybzphcGk6b3JkZXJzOndyaXRlIiwiYWxsZWdybzphcGk6YWRzIiwiYWxsZWdybzphcGk6cGF5bWVudHM6d3JpdGUiLCJhbGxlZ3JvOmFwaTpzYWxlOnNldHRpbmdzOndyaXRlIiwiYWxsZWdybzphcGk6cHJvZmlsZTpyZWFkIiwiYWxsZWdybzphcGk6cmF0aW5ncyIsImFsbGVncm86YXBpOnNhbGU6c2V0dGluZ3M6cmVhZCIsImFsbGVncm86YXBpOnBheW1lbnRzOnJlYWQiLCJhbGxlZ3JvOmFwaTptZXNzYWdpbmciXSwiYWxsZWdyb19hcGkiOnRydWV9.0LV_O4_Rw4u6vYiSNSpUCW9gQYjZ0C8zYoZAE7nl-y0Kqk6U9pSO9oXuMkKmBwYpKBu2ZgZDjY1gS8KcE0bPoust8LyefnTmsASg_IZvlorO9PJ96v5M7T-QTyfHa58GcrZnicAzoXkLdi-bl9KVWcIKfKBNkYZocyFSbrf1gFuoRIGGE5v-l_gy1KphPE71wZi0Uq0Je5YBO2s9YxEGbcrx4YUPrRveLZwUTUg-ZS1B6r9dKa1GwBWIjcCIki5n4-Fv3NxAGfodx3tCavgp1GyAgQNNqx26ueJMclyGGdDMT0YFRp1aKW3joY8mm3P9D51bjjtzXN_qmttEZobCuw"
        ])->get("https://api.allegro.pl/payments/payment-operations?occurredAt.gte=2021-10-30T11:06:50.935Z");

        $responseTwo = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
            "Authorization" => "Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE2MzU4ODUyMTgsInVzZXJfbmFtZSI6IjY3MjMyODc4IiwianRpIjoiZjZjMWEyZTktOWJhZC00NDYxLTg5MTgtYzllMmFhZWMxZDdmIiwiY2xpZW50X2lkIjoiZTI3YzMwOTFhNjdhNGVkZDgwMTUxOTFkNGEyNmM2NmYiLCJzY29wZSI6WyJhbGxlZ3JvOmFwaTpvcmRlcnM6cmVhZCIsImFsbGVncm86YXBpOnByb2ZpbGU6d3JpdGUiLCJhbGxlZ3JvOmFwaTpzYWxlOm9mZmVyczp3cml0ZSIsImFsbGVncm86YXBpOmJpbGxpbmc6cmVhZCIsImFsbGVncm86YXBpOmNhbXBhaWducyIsImFsbGVncm86YXBpOmRpc3B1dGVzIiwiYWxsZWdybzphcGk6YmlkcyIsImFsbGVncm86YXBpOnNhbGU6b2ZmZXJzOnJlYWQiLCJhbGxlZ3JvOmFwaTpvcmRlcnM6d3JpdGUiLCJhbGxlZ3JvOmFwaTphZHMiLCJhbGxlZ3JvOmFwaTpwYXltZW50czp3cml0ZSIsImFsbGVncm86YXBpOnNhbGU6c2V0dGluZ3M6d3JpdGUiLCJhbGxlZ3JvOmFwaTpwcm9maWxlOnJlYWQiLCJhbGxlZ3JvOmFwaTpyYXRpbmdzIiwiYWxsZWdybzphcGk6c2FsZTpzZXR0aW5nczpyZWFkIiwiYWxsZWdybzphcGk6cGF5bWVudHM6cmVhZCIsImFsbGVncm86YXBpOm1lc3NhZ2luZyJdLCJhbGxlZ3JvX2FwaSI6dHJ1ZX0.Dp3GusPimbVJiK6YeoFd3oR1uJ-pbrMmenLizYRuOYFNXIIyQFZ5WkVyCOAKZaFPozsNlS2HooGNQrIfZPCbAqikHzeFeOgTD4etP7bMGaVF1nT5eJvLSHuXDPI9iLGLLJwvUc6LJXEBi_06Gkt_ZZ8yRdzvb0xPC5iesSTmAVb6oJ1Redk3cNJ2gYIJguEairf31N4mVgK2iRb4dsmNeZ7YIcRZuKr2WU4XlB-i4Fj4HIj-ZFyV_rSZeF0EWtn4njnxgr995qd-44nwbufUf2OnlkCtPWWVBHEjimEMrgLbF8NhZL50UGwtVo2sOA4PN5R-dI_FdZYTPja2lfaOzg"
        ])->get("https://api.allegro.pl/payments/payment-operations?occurredAt.gte=2021-10-30T11:06:50.935Z");

        $vauleOne = $response['paymentOperations'][0]['wallet']['balance']['amount'];

        $valueTwo = $responseTwo['paymentOperations'][0]['wallet']['balance']['amount'];

        return $vauleOne + $valueTwo;
    }

    public function add(Request $request, $user_id)
    {   
        return $this->integrationRepo::add($this->clientId, $user_id);
    }

    public function getToken(Request $request, $user_id)
    {
        return $this->integrationRepo::getToken($request, $this->clientId, $this->clientSecret, $user_id);
    }

    public function refreshToken(Request $request)
    {
        return $this->integrationRepo::refreshToken(UserData::where('user_id', 40)->select('refresh_token')->first()['refresh_token'], $this->clientId, $this->clientSecret);
    }

    public function deleteAllegroUser(Request $request)
    {
        return $this->integrationRepo::deleteAllegroUser($request);
    }

    public function list($user_id)
    {
        return $this->integrationRepo::list($user_id);
    }

    public function offers(Request $request)
    {
        return $this->allegroAccountRepo::offers(40);
    }

    public function offer(Request $request)
    {
        return $this->allegroAccountRepo::offer($request->id);
    }

    public function setListening(Request $request)
    {
        return $this->allegroAccountRepo::setListening($request->id);
    }

    public function monitoringOn(Request $request)
    {
        $response = Http::withHeaders([
            "Authorization" => "{jwtAuth::getToken()}"
            ])
            ->withBody(json_encode([
                'user_id' => 40
            ]), 'json')
            ->post("http://localhost:3000/listening", );
        return 1;
    }

    public function monitoringOff(Request $request)
    {
        $response = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
        ])->get("http://localhost:3000/listening/off?user_id=$user_id");
    }

    public function mainFunction(Request $request)
    {
        // $user_id = $this->user->id;
        return $this->allegroMainFunction::mainFunction($request->user_id);
    }

    // ================
    // ---- OFERTY ----
    // ================

    public function setMonitoring(Request $request)
    {
        if(isset($request->templ_id)&& isset($request->db_id))
        {
            return $this->allegroAccountRepo::setMonitoring($request->offer_id, $request->template, $request->codeBase);
        }
        return $this->allegroAccountRepo::setMonitoring($request->offer_id);
    }

    public function getMonitoring($set)
    {
        return $this->allegroAccountRepo::getMonitoring($set);
    }

    public function getOffer(Request $request)
    {
        return $this->allegroAccountRepo::offers(40, $request);
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
        $limit = 50;
        $customerId = ['sign' => '!=', 'id' => ''];
        $canceled = [ 'sign' => '=', 'desc' => ''];

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
            $customerId['sign'] = '=';
            $customerId['id'] = $request->customer_id;
        }

        if(isset($request->canceled))
        {
            $customerId['sign'] = '=';
            $customerId['desc'] = 'CANCELED';
        }

        if(isset($request->date))
        {
            
            $from = date($request->date . " 00:00:00");
            $to = date($request->date . " 23:59:59");
            $customers = Customer::where('seller_id', $user_id)
                ->whereBetween('created_at', [$from, $to])
                ->where('customer_id', $customerId['sign'], $customerId['id'])
                ->where('status', $canceled['sign'], $canceled['desc'])
                ->limit($limit)
                ->get();
        }

        $customers = Customer::where('seller_id', $user_id)->where('customer_id', $customerId['sign'], $customerId['id'])->limit($limit)->get();

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

    public function cancelOrder(Request $request)
    {
        // 3990
        // 1623157840976792
        return Orders::where('order_id', $request->order_id)->update(['isCanceled' => 1]);
    }

    public function tst(Request $request)
    {
        // 1621513352164979
        $userDatas = UserData::where('user_id', $request->user_id)->get();

        foreach ($userDatas as $userData)
        {
            if($request->func == "event")
            {
                $response = Http::withHeaders([
                    "Accept" => "application/vnd.allegro.public.v1+json",
                    "Authorization" => "Bearer $userData->access_token"
                ])->get("https://api.allegro.pl/order/events?type=READY_FOR_PROCESSING&from=$userData->last_event");

                return $response;
            }

            if($request->func == "chechout")
            {
                $response = Http::withHeaders([
                    "Accept" => "application/vnd.allegro.public.v1+json",
                    "Authorization" => "Bearer $userData->access_token"
                ])->get("https://api.allegro.pl/order/checkout-forms/$checkOutFormId");
                return json_decode($response);
            }
        }
    }

    // public function mainFunction(Request $request)
    // {
    //     $details = array();

    //     $log[] = "";

    //     $userDatas = UserData::where('user_id', $request->user_id)->get();

    //     if(!isset($userDatas))
    //     {
    //         return [ 
    //             'status' => 1,
    //             'desc' => 'any allegro account does not exist in database' 
    //         ];
    //     }

    //     foreach ($userDatas as $userData)
    //     {
    //         $response = Http::withHeaders([
    //             "Accept" => "application/vnd.allegro.public.v1+json",
    //             "Authorization" => "Bearer $userData->access_token"
    //         ])->get("https://api.allegro.pl/order/events?type=READY_FOR_PROCESSING&from=$userData->last_event");

    //         // return $response;

    //         if($response->failed() || $response->clientError())
    //         {
    //             UserData::where('user_id', $request->user_id)->update([
    //                 'refresh' => true
    //             ]);
    //             return $this->addAllegroUserRepo();
    //         }
    //         if($response["events"] != []) {
    //             $res = $response["events"];
    //             $lastEvent = $res[0]["id"];
    //             if($res[0]["id"] != $userData->last_event) 
    //             {
    //                 $log[] = "new events: ".$res[0]["id"];
    //                 foreach ($res as $order) 
    //                 {
    //                     $existOrder = Orders::where('order_id', $order["id"])->get();
    //                     $detailsInfo = $this->checkOut($order["order"]["checkoutForm"]["id"], $userData->access_token);
                        
    //                     $isActive = Offers::where('offer_id', $detailsInfo->lineItems[0]->offer->id)->first();
    //                     if(!isset($existOrder[0]["id"]) && $isActive['is_active'] == "YES") 
    //                     {
    //                         $log[] = "new order: ".$order["id"];
    //                         $buyer = $order["order"]["buyer"];
    //                         $orderModel = new Orders;
    //                         $orderModel->offer_id = $detailsInfo->lineItems[0]->offer->id;
    //                         $orderModel->order_id = $order["id"];
    //                         $orderModel->offer_name = $detailsInfo->lineItems[0]->offer->name;
    //                         $orderModel->offer_price = $detailsInfo->lineItems[0]->originalPrice->amount;
    //                         $orderModel->offer_currency = $detailsInfo->lineItems[0]->originalPrice->currency;
    //                         $orderModel->quantity = $detailsInfo->lineItems[0]->quantity;
    //                         $orderModel->order_price = $detailsInfo->lineItems[0]->price->amount;
    //                         $orderModel->order_currency = $detailsInfo->lineItems[0]->price->currency;
    //                         $orderModel->customer_id = $buyer["id"];
    //                         $orderModel->seller_id = $request->user_id;
    //                         $orderModel->order_date = $detailsInfo->lineItems[0]->boughtAt;
    //                         $orderModel->save();

    //                         if(Customer::where('customer_id', $buyer["id"])->exists())
    //                         {
    //                             Customer::where('customer_id', $buyer["id"])->update(['orders' => Orders::where('customer_id', $buyer["id"])->count()]);

    //                             if(OrdersTable::where('offer_id', $detailsInfo->lineItems[0]->offer->id)->where('customer_id',  $buyer["id"])->exists())
    //                             {
    //                                 OrdersTable::where('customer_id', $buyer["id"])
    //                                     ->where('offer_id', $detailsInfo->lineItems[0]->offer->id)
    //                                     ->update([
    //                                         'count' => Orders::where('customer_id', $buyer["id"])->where('offer_id', $detailsInfo->lineItems[0]->offer->id)->count()
    //                                 ]);
    //                             } else {
    //                                 $order_table = new OrdersTable;
    //                                 $order_table->seller_id = $request->user_id;
    //                                 $order_table->customer_id = $buyer["id"];
    //                                 $order_table->offer_id = $detailsInfo->lineItems[0]->offer->id;
    //                                 $order_table->offer_link = "https://www.allegro.pl/oferta/".$detailsInfo->lineItems[0]->offer->id;
    //                                 $order_table->count = 1;
    //                                 $order_table->save();
    //                             }
    //                         } else {
    //                             if(OrdersTable::where('offer_id', $detailsInfo->lineItems[0]->offer->id)->where('customer_id',  $buyer["id"])->exists())
    //                             {
    //                                 $tests[] = "jeśli ordersTable istnieje";
    //                                 OrdersTable::where('customer_id', $buyer["id"])
    //                                     ->where('offer_id', $detailsInfo->lineItems[0]->offer->id)
    //                                     ->update([
    //                                         'count' => Orders::where('customer_id', $buyer["id"])->where('offer_id', $detailsInfo->lineItems[0]->offer->id)->count()
    //                                 ]);
    //                             } else {
    //                                 $order_table = new OrdersTable;
    //                                 $order_table->seller_id = $request->user_id;
    //                                 $order_table->customer_id = $buyer["id"];
    //                                 $order_table->offer_id = $detailsInfo->lineItems[0]->offer->id;
    //                                 $order_table->offer_link = "https://www.allegro.pl/oferta/".$detailsInfo->lineItems[0]->offer->id;
    //                                 $order_table->count = 1;
    //                                 $order_table->save();
    //                             }
    //                             $customer = new Customer;
    //                             $customer->customer_id = $buyer["id"];
    //                             $customer->seller_id = $request->user_id;
    //                             $customer->login = $buyer["login"];
    //                             $customer->email = $buyer["email"];
    //                             $customer->first_name = $detailsInfo->buyer->firstName;
    //                             $customer->last_name = $detailsInfo->buyer->lastName;
    //                             $customer->adress = $detailsInfo->buyer->address->street;
    //                             $customer->city = $detailsInfo->buyer->address->city;
    //                             $customer->no_tel = $detailsInfo->buyer->phoneNumber;
    //                             $customer->office = $detailsInfo->buyer->companyName;
    //                             $customer->guest = $buyer["guest"];
    //                             $customer->orders = 1;
    //                             $customer->save();
    //                         }

    //                         // $this->changeStatus($order["order"]["checkoutForm"]["id"], $userData->access_token, "PROCESSING");
    //                         // $this->changeStatus("66b231c0-9789-11eb-80ab-8b7eefbb1428", "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX25hbWUiOiI5Mzk3OTA3NiIsInNjb3BlIjpbImFsbGVncm86YXBpOm9yZGVyczpyZWFkIiwiYWxsZWdybzphcGk6cHJvZmlsZTp3cml0ZSIsImFsbGVncm86YXBpOnNhbGU6b2ZmZXJzOndyaXRlIiwiYWxsZWdybzphcGk6YmlsbGluZzpyZWFkIiwiYWxsZWdybzphcGk6Y2FtcGFpZ25zIiwiYWxsZWdybzphcGk6ZGlzcHV0ZXMiLCJhbGxlZ3JvOmFwaTpiaWRzIiwiYWxsZWdybzphcGk6c2FsZTpvZmZlcnM6cmVhZCIsImFsbGVncm86YXBpOm9yZGVyczp3cml0ZSIsImFsbGVncm86YXBpOmFkcyIsImFsbGVncm86YXBpOnBheW1lbnRzOndyaXRlIiwiYWxsZWdybzphcGk6c2FsZTpzZXR0aW5nczp3cml0ZSIsImFsbGVncm86YXBpOnByb2ZpbGU6cmVhZCIsImFsbGVncm86YXBpOnJhdGluZ3MiLCJhbGxlZ3JvOmFwaTpzYWxlOnNldHRpbmdzOnJlYWQiLCJhbGxlZ3JvOmFwaTpwYXltZW50czpyZWFkIl0sImFsbGVncm9fYXBpIjp0cnVlLCJhdGkiOiJjZDZkZDg1Yi1jYjA3LTQ5ODgtYjA2Zi00ODZjZGU4ZDFiOGEiLCJleHAiOjE2Mjg0MDMwMTcsImp0aSI6IjQ3ODg4YjcyLWFlMzgtNDQxMy1hMjU5LWM2NTdmMjRhNTEyZiIsImNsaWVudF9pZCI6IjE4NDJmNGUwM2QxMzQ3ZDQ4MTIyNDZmNzQzOWJhYWExIn0.dGV6yg4BWAzWy65q4j-Q_Zkzt3d7aviCBCGvzY5HJEu_Vdmn22Dg8ZeGPK895HRQDjS5DAy8CQVmVqPz4b8lFIMQy_69hAaHO3-JEyPNk8IleGAUn9tYGJLJ7giUjnFZaBWfARMgirG1jgCjW1Dc32_5B2wtu_TddABlkrE1qRw4pC0lLoQpPB1tOq777wZMXr7VEnWrK_Rsqq6bQv99WnacJvedQ2OPePluYmyjJUEOqn-MuEqw6AWmJGej7s4b0tQARw5WkXPYUWsH2XoUYIaCa_zPdFVMLiPtXhJf3eZDLWG3ZK7vqLNjrioOB37SXBTuz5OQe-vJATNLXhWmtjEytRzbwiijcGCzZ-IdzxlMM7ZpMfbYMzTyiu88QgnW8L0lcm7exkvRelFQY1f8-VFsq26M-9ETiALN-V8w_Jcu5yXGH2kIhRv1ss6UboBFH_LZ6A90etAqI_BDIjHJh96cNfn8coNrRLb_Wt49PA209r6ChzQPIrtyrZdtjdamkTGq-PPPbdN6sTlRONoeI5jhb4c3NJsM7saULZnDPE73CzkoIVOhHvpcO13MNo5V_YxWpkOvfDulClFqi9iokJgTutmx3pOHOD5UR6dxYZH_Md9Fti8hega-WQuIR255WAb55kseYKguIQy3nhax7zfd8XS3XVxe4k224xBwFC0", "PROCESSING");
    //                         MailController::sendCode($order["id"], $detailsInfo->lineItems[0]->quantity, $buyer["email"]);
    //                         // zmień status zamówienia !!!!
    //                         // $temp = $this->checkOut("66b231c0-9789-11eb-80ab-8b7eefbb1428", "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX25hbWUiOiI5Mzk3OTA3NiIsInNjb3BlIjpbImFsbGVncm86YXBpOm9yZGVyczpyZWFkIiwiYWxsZWdybzphcGk6cHJvZmlsZTp3cml0ZSIsImFsbGVncm86YXBpOnNhbGU6b2ZmZXJzOndyaXRlIiwiYWxsZWdybzphcGk6YmlsbGluZzpyZWFkIiwiYWxsZWdybzphcGk6Y2FtcGFpZ25zIiwiYWxsZWdybzphcGk6ZGlzcHV0ZXMiLCJhbGxlZ3JvOmFwaTpiaWRzIiwiYWxsZWdybzphcGk6c2FsZTpvZmZlcnM6cmVhZCIsImFsbGVncm86YXBpOm9yZGVyczp3cml0ZSIsImFsbGVncm86YXBpOmFkcyIsImFsbGVncm86YXBpOnBheW1lbnRzOndyaXRlIiwiYWxsZWdybzphcGk6c2FsZTpzZXR0aW5nczp3cml0ZSIsImFsbGVncm86YXBpOnByb2ZpbGU6cmVhZCIsImFsbGVncm86YXBpOnJhdGluZ3MiLCJhbGxlZ3JvOmFwaTpzYWxlOnNldHRpbmdzOnJlYWQiLCJhbGxlZ3JvOmFwaTpwYXltZW50czpyZWFkIl0sImFsbGVncm9fYXBpIjp0cnVlLCJhdGkiOiJjZDZkZDg1Yi1jYjA3LTQ5ODgtYjA2Zi00ODZjZGU4ZDFiOGEiLCJleHAiOjE2Mjg0MDMwMTcsImp0aSI6IjQ3ODg4YjcyLWFlMzgtNDQxMy1hMjU5LWM2NTdmMjRhNTEyZiIsImNsaWVudF9pZCI6IjE4NDJmNGUwM2QxMzQ3ZDQ4MTIyNDZmNzQzOWJhYWExIn0.dGV6yg4BWAzWy65q4j-Q_Zkzt3d7aviCBCGvzY5HJEu_Vdmn22Dg8ZeGPK895HRQDjS5DAy8CQVmVqPz4b8lFIMQy_69hAaHO3-JEyPNk8IleGAUn9tYGJLJ7giUjnFZaBWfARMgirG1jgCjW1Dc32_5B2wtu_TddABlkrE1qRw4pC0lLoQpPB1tOq777wZMXr7VEnWrK_Rsqq6bQv99WnacJvedQ2OPePluYmyjJUEOqn-MuEqw6AWmJGej7s4b0tQARw5WkXPYUWsH2XoUYIaCa_zPdFVMLiPtXhJf3eZDLWG3ZK7vqLNjrioOB37SXBTuz5OQe-vJATNLXhWmtjEytRzbwiijcGCzZ-IdzxlMM7ZpMfbYMzTyiu88QgnW8L0lcm7exkvRelFQY1f8-VFsq26M-9ETiALN-V8w_Jcu5yXGH2kIhRv1ss6UboBFH_LZ6A90etAqI_BDIjHJh96cNfn8coNrRLb_Wt49PA209r6ChzQPIrtyrZdtjdamkTGq-PPPbdN6sTlRONoeI5jhb4c3NJsM7saULZnDPE73CzkoIVOhHvpcO13MNo5V_YxWpkOvfDulClFqi9iokJgTutmx3pOHOD5UR6dxYZH_Md9Fti8hega-WQuIR255WAb55kseYKguIQy3nhax7zfd8XS3XVxe4k224xBwFC0");
    //                         // return $temp;
    //                         // $this->changeStatus($order["order"]["checkoutForm"]["id"], $userData->access_token, "SENT");
    //                         // $this->checkOut($order["order"]["checkoutForm"]["id"], $userData->access_token);
    //                         $lastEvent = $order["id"];
    //                         $details[] = $orderModel;
    //                     } else {
    //                         $lastEvent = $order["id"];
    //                         $log[] = "old order: ".$order["id"];
    //                     }
    //                     // dd(['details' => $details, 'log' => $log, $detailsInfo->lineItems[0]->offer->id, $isActive['is_active']]);
    //                     // dd(['debug' => $tests]);
    //                 }
    //                 $status = 0;
    //                 $desc = "Oh yhee.. some new orders :) ";
    //             } else {
    //                 $log[] = "last order: ".$lastEvent;
    //                 $status = 0;
    //                 $desc = "Please... give me some orders :( ";
    //             }
    //             $userData->last_event = $lastEvent;
    //             $userData->save();
    //             // zmiana w badzie danych ostatniego eventu
    //         } else {
    //             $status = 0;
    //             $desc = "Please... give me some orders :( ";
    //         }     
    //         unset($res);
    //     }
    //     return [date("Y-m-d") .'/'. date("H:i:s") => ["status" => $status, "desc" => $desc, $log]];
    // }

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
        $userData = UserData::where('id', $request->id)->get();
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

    

    public function getOrders(Request $request)
    {
        $user_id = $request->user_id;

        $oderBy = 'desc';
        $limit = 50;
        $offerId = ['sing' => '!=', 'id' => ''];
        $canceled = 0;
        $from = date('2000-01-01');
        $to = date('2022-01-01');

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

        if(isset($request->canceled))
        {
            $orders = Orders::where('seller_id', $user_id)
            ->where('offer_id', $offerId['sing'], $offerId['id'])
            ->where('isCanceled', 1)
            ->whereBetween('order_date', [$from, $to])
            ->orderBy('order_date', $oderBy)
            ->limit($limit)
            ->get();
        }
        else
        {
            $orders = Orders::where('seller_id', $user_id)
            ->where('offer_id', $offerId['sing'], $offerId['id'])
            ->whereBetween('order_date', [$from, $to])
            ->orderBy('order_date', $oderBy)
            ->limit($limit)
            ->get();
        }

        if(!$orders->isEmpty())
        {
            foreach($orders as $order)
            {
                $sentMails = SentMail::where('order_id', $order->order_id)->get();
                if (!isset($sentMails[0]))
                {
                    $send_status = 'Sending';
                    $sent_date = 'Sending';
                    $codes[] = 'Sending';
                }
                else
                {
                    foreach ($sentMails as $sentMail)
                    {
                        $send_status = 'Sent';
                        $sent_date = $sentMail->created_at;
                        $code = Code::where('id', $sentMail->code_id)->first();
                        $codes[] = $code->code;
                    }
                }

                // $customer = Customer::where('customer_id', $order->customer_id)->first();
                $res[] = [
                    'order' => [ 
                        $order, 
                        'link' => "https://allegro.pl/oferta/$order->offer_id",
                        'platform' => 'Allegro',
                        'send_status' => $send_status,
                        'ended' => 'null',
                        'date_PayU' => 'rrrr-mm-dd hh:mm:ss', 
                        'sent_date' => $sent_date, 
                        'codes' => $codes
                    ], 
                    'customer' => [ 
                        'name' => 'name_test', 
                        'login' => 'login_test',
                        'email' => 'email_test',
                        // 'name' => $customer->first_name." ".$customer->last_name, 
                        // 'login' => $customer->login,
                        // 'email' => $customer->email,
                    ]
                ];

                if($codes != 'Sending')
                {
                    \array_splice($codes, 0, 1);
                }
            }
            return response()->json($res, 200);
        }
        $res = [];
        return response()->json($res, 200);
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
