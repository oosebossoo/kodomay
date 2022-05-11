<?php
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
use App\Http\Controllers\TimeController;

use App\Models\Customer;
use App\Models\UserData;
use App\Models\Orders;
use App\Models\OrdersTable;
use App\Models\Offers;
use App\Models\SentMail;
use App\Models\Code;
use App\Models\User;

use Auth;
use JWTAuth;
use Carbon\Carbon;

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

    // public function testAllegro()
    // {
    //     $credenctial = $this->allegroAccountRepo->testAllegro("PROD");
    //     return $credenctial['secret'];
    // }
    
    public function getLastEvent()
    {
        return $this->integrationRepo::lastEvent(40);
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
        // $user_id = $request->user_id;
        $user_id = 40;
        return $this->integrationRepo::refreshToken(UserData::where('user_id', $user_id)->select('refresh_token')->first()['refresh_token'], $this->clientId, $this->clientSecret);
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
        // $user_id = $request->user_id;
        $user_id = 40;
        if(isset($request->update))
        {
            return $this->allegroAccountRepo::offers($user_id, $request->update);
        }
        return $this->allegroAccountRepo::offers($user_id);
    }

    public function offersOff(Request $request)
    {
        // $user_id = $request->user_id;
        $user_id = 40;
        return $this->allegroAccountRepo::offersOff($user_id);
    }

    public function offer(Request $request)
    {
        // $user_id = $request->user_id;
        $user_id = 40;
        return $this->allegroAccountRepo::offer($request->id);
    }

    public function setListening(Request $request)
    {
        return $this->allegroAccountRepo::setListening($request->id);
    }

    public static function monitoringOnAuto($user_id)
    {
        // $user = User::where('id', $user_id)->first();
        $response = Http::withHeaders([
            "Authorization" => "{jwtAuth::getToken()}"
            ])
            ->withBody(json_encode([
                'user_id' => $user_id
            ]), 'json')
            ->post("http://localhost:3000/listening", );
        return $response;
    }

    public function monitoringOn(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $response = Http::asForm()->post('http://localhost:3000/listening', [
            'user_id' => $user->id,
            'opt' => 'start',
            'interval_id' => $user->interval_id
        ]);
        return $response;
    }

    public function monitoringOff(Request $request)
    {
        $response = Http::withHeaders([
                "Authorization" => "{jwtAuth::getToken()}"
            ])->withBody(json_encode([
                // 'user_id' => $user->id,
                'opt' => 'end'
            ]), 'json')
            ->post("http://localhost:3000/listening");
    }

    public function mainFunction(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        
        // $user_id = $this->user->id;
        return $this->allegroMainFunction::mainFunction($request->user_id);
    }

    public function setMonitoring(Request $request)
    {
        return $this->allegroAccountRepo::setMonitoring($request->offer_id, $request->template, $request->codeBase);
    }

    public function offMonitoring(Request $request)
    {
        return $this->allegroAccountRepo::offMonitoring($request->offer_id);
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
        // $user_id = $request->user_id;
        $user_id = 40;

        $oderBy = 'desc';
        $limit = 50;
        $customerId = ['sign' => '!=', 'id' => ''];
        $canceled = ['sign' => '=', 'desc' => ''];

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
            $response[] = ['customer' => [
                'customer_id' => $customer->customer_id,
                'name' => $customer->login,
                'fullname' => $customer->first_name." ".$customer->last_name,
                'city' => $customer->city,
                'email' => $customer->email
            ]];
        }

        return response()->json($response, 200);
    }

    public function getCustomer(Request $request)
    {
        // $user_id = $request->user_id;
        $user_id = 40;
        $customer = Customer::where('seller_id', $user_id)->where('customer_id', $request->customer_id)->get();
        $total_amount = Orders::where('seller_id', $user_id)->where('customer_id', $request->customer_id)->count('order_price');
        $first_order_date = Orders::where('seller_id', $user_id)->where('customer_id', $request->customer_id)->orderBy('order_date', 'asc')->first();
        $first_order_date = $first_order_date->order_date;
        $last_order = Orders::where('seller_id', $user_id)->where('customer_id', $request->customer_id)->orderBy('order_date', 'desc')->first();
        $last_order_date = $last_order->order_date;

        $response = [ 
            'customer' => $customer,
            'total_amount' => $total_amount,
            'first_purchase_date' => $first_order_date,
            'last_purchase' => $last_order->offer_name,
            'last_purchase_date' => $last_order_date,
            // 'customer_orders' => $this->getCustomerOrders($request->customer_id)
        ];

        return response()->json($response, 200);
    }

    public function getCustomerOrders($customer_id)
    {
        // $user_id = $request->user_id;
        $user_id = 40;

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
     
    public static function getOfferLink($offerId, $token)
    {
        $response = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
            "Authorization" => "Bearer $token"
        ])->get("https://api.allegro.pl/sale/offers?offer.id=$offerId");
        return json_decode($response);
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
        $limit = 200;
        $offerId = ['sing' => '!=', 'id' => ''];
        $canceled = 0;
        $from = date('2000-01-01');
        $to = date('2023-01-01');

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

        // if(isset($request->offer_id))
        // {
        //     $offerId['sing'] = '=';
        //     $offerId['id'] = $request->offer_id;
        // }

        // if(isset($request->from))
        // {
        //     $from = date($request->from);
        // }

        // if(isset($request->to))
        // {
        //     $to = date($request->to);
        // }

        if(isset($request->canceled))
        {
            $orders = Orders::where('seller_id', $user_id)
            ->where('offer_id', $offerId['sing'], $offerId['id'])
            ->where('isCanceled', 1)
            ->whereBetween('order_date', [$from, $to])
            ->orderBy('order_date', $oderBy)
            ->limit($limit)
            ->get();
        } else {
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
                $codes = array();
                $order->order_date = Carbon::parse($order->order_date)->addHour();

                $sentMails = SentMail::where('order_id', $order->order_id)->get();
                if (!isset($sentMails[0]))
                {
                    $send_status = 'Sending';
                    $sent_date = 'Sending';
                    array_push($codes, "Sending");
                    // $codes[] = 'Sending';
                } else {
                    foreach ($sentMails as $sentMail)
                    {
                        $send_status = 'Sent';
                        $sent_date = explode("T", $sentMail->created_at);
                        $sent_date[0] = Carbon::parse($sent_date[0])->addHour(TimeController::repairTime());
                        $code = Code::where('id', $sentMail->code_id)->first();
                        
                        if(!isset($code->code)){
                            array_push($codes, "brak informacji");
                            // $codes[] = "brak informacji";
                        } else {
                            array_push($codes, $code->code);
                            // $codes[] = $code->code;
                        }
                    }
                }

                $customer = Customer::where('customer_id', $order->customer_id)->first();
                if($customer == null)
                {
                    $res[] = [
                        'order' => [ 
                            $order, 
                            'link' => "https://allegro.pl/oferta/$order->offer_id",
                            'platform' => 'Allegro',
                            'send_status' => $send_status,
                            'ended' => 'null',
                            'date_PayU' => 'rrrr-mm-dd hh:mm:ss', 
                            'sent_date' => $sent_date[0], 
                            'codes' => $codes
                        ], 
                        'customer' => [ 
                            // 'name' => 'name_test', 
                            // 'login' => 'login_test',
                            // 'email' => 'email_test',
                            'name' => "", 
                            'login' => "",
                            'email' => "",
                        ]
                    ];
                } else {
                    $res[] = [
                        'order' => [ 
                            $order, 
                            'link' => "https://allegro.pl/oferta/$order->offer_id",
                            'platform' => 'Allegro',
                            'send_status' => $send_status,
                            'ended' => 'null',
                            'date_PayU' => 'rrrr-mm-dd hh:mm:ss', 
                            'sent_date' => $sent_date[0], 
                            'codes' => $codes
                        ], 
                        'customer' => [ 
                            // 'name' => 'name_test', 
                            // 'login' => 'login_test',
                            // 'email' => 'email_test',
                            'name' => $customer->first_name." ".$customer->last_name." ".$customer->login, 
                            'login' => $customer->login,
                            'email' => $customer->email,
                        ]
                    ];
                }

                unset($codes);
            }
            return response()->json($res, 200);
        }
        $res = [];
        return response()->json([], 200);
    }
}
