<?php

namespace App\Repositories;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\MailController;

use App\Models\Customer;
use App\Models\UserData;
use App\Models\User;
use App\Models\Orders;
use App\Models\OrdersTable;
use App\Models\Offers;
use App\Models\SentMail;
// use App\Models\Code;

class AllegroMainFunction
{
    protected static $clientId = 'e27c3091a67a4edd8015191d4a26c66f';
    protected static $clientSecret = '3JuWoxfQmMLK9da7BvS40sCMACFCjbGXPCepOnD3R4V4k87whYLy3KPLBle9UMro';

    static function checkOut($token, $checkOutFormId)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.allegro.public.v1+json',
            'Authorization' => "Bearer $token"
        ])->get("https://api.allegro.pl/order/checkout-forms/$checkOutFormId");
        return json_decode($response);
    }

    static function changeStatus($token, $checkOutFormId, $status = 'SENT')
    {
        // $response = Http::withHeaders([
        //     'Authorization' => "Bearer $token",
        //     'Accept'          => 'application/vnd.allegro.public.v1+json',
        //     'Content-Type'    => 'application/vnd.allegro.public.v1+json',
        // ])->put("https://api.allegro.pl/order/checkout-forms/$checkoutFormId/fulfillment", ['status' => $status]);
        
        // $response = json_decode($response);
        // if(isset($response['error']))
        // {
        //     return $response;
        // }
        // return 0;
    }

    static function mainFunction($user_id)
    {
        $userDatas = UserData::where('user_id', $user_id)->get();
        // $userDatas = UserData::where('user_id', $user_id)->where('active', 1)->get();

        $user = User::where('id', $user_id)->first();
        if($user['credits'] == 10) {
            return response()->json('Credits are empty');
        }

        if(!isset($userDatas)) {
            return [ 
                'status' => 1,
                'desc' => 'any allegro account does not exist in database' 
            ];
        }

        $log[] = 'User: '.$user_id;

        foreach ($userDatas as $userData)
        {
            $response = Http::withHeaders([
                "Accept" => "application/vnd.allegro.public.v1+json",
                "Authorization" => "Bearer $userData->access_token"
            ])->get("https://api.allegro.pl/order/events?type=READY_FOR_PROCESSING&from=$userData->last_event");

            if($response->failed() || $response->clientError()) {
                UserData::where('user_id', $user_id)->update([
                    'refresh' => true
                ]);
                return IntegrationRepository::refreshToken(UserData::where('id', $userData->id)->select('refresh_token')->first()['refresh_token'], self::$clientId, self::$clientSecret);
            }

            if($response["events"] != []) {
                $res = $response["events"];
                $lastEvent = $res[0]["id"];

                if($res[0]["id"] != $userData->last_event) {
                    $log[] = "user: $userData->id new events: ".$res[0]["id"];

                    // $userData->last_event = $res[0]["id"];
                    // $userData->save();

                    foreach ($res as $order) 
                    {
                        $userData->last_event = $order["id"];
                        $userData->save();
                        $existOrder = Orders::where('order_id', $order["id"])->get();

                        $detailsInfo = self::checkOut($userData->access_token, $order["order"]["checkoutForm"]["id"]);
                        
                        $isActive = Offers::where('offer_id', $detailsInfo->lineItems[0]->offer->id)->first()['is_active'];

                        if(!isset($existOrder[0]) && $isActive == "YES") {
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
                            $orderModel->seller_id = $user_id;
                            $orderModel->order_date = $detailsInfo->lineItems[0]->boughtAt;
                            $orderModel->save();

                            if(Customer::where('customer_id', $buyer["id"])->exists()) {
                                Customer::where('customer_id', $buyer["id"])->update(['orders' => Orders::where('customer_id', $buyer["id"])->count()]);

                                if(OrdersTable::where('offer_id', $detailsInfo->lineItems[0]->offer->id)->where('customer_id',  $buyer["id"])->exists()) {
                                    OrdersTable::where('customer_id', $buyer["id"])
                                        ->where('offer_id', $detailsInfo->lineItems[0]->offer->id)
                                        ->update([
                                            'count' => Orders::where('customer_id', $buyer["id"])->where('offer_id', $detailsInfo->lineItems[0]->offer->id)->count()
                                    ]);
                                } else {
                                    $order_table = new OrdersTable;
                                    $order_table->seller_id = $user_id;
                                    $order_table->customer_id = $buyer["id"];
                                    $order_table->offer_id = $detailsInfo->lineItems[0]->offer->id;
                                    $order_table->offer_link = "https://www.allegro.pl/oferta/".$detailsInfo->lineItems[0]->offer->id;
                                    $order_table->count = 1;
                                    $order_table->save();
                                }
                            } else {
                                if(OrdersTable::where('offer_id', $detailsInfo->lineItems[0]->offer->id)->where('customer_id',  $buyer["id"])->exists()) {
                                    $tests[] = "jeśli ordersTable istnieje";
                                    OrdersTable::where('customer_id', $buyer["id"])
                                        ->where('offer_id', $detailsInfo->lineItems[0]->offer->id)
                                        ->update([
                                            'count' => Orders::where('customer_id', $buyer["id"])->where('offer_id', $detailsInfo->lineItems[0]->offer->id)->count()
                                    ]);
                                } else {
                                    $order_table = new OrdersTable;
                                    $order_table->seller_id = $user_id;
                                    $order_table->customer_id = $buyer["id"];
                                    $order_table->offer_id = $detailsInfo->lineItems[0]->offer->id;
                                    $order_table->offer_link = "https://www.allegro.pl/oferta/".$detailsInfo->lineItems[0]->offer->id;
                                    $order_table->count = 1;
                                    $order_table->save();
                                }
                                $customer = new Customer;
                                $customer->customer_id = $buyer["id"];
                                $customer->seller_id = $user_id;
                                $customer->login = $buyer["login"];
                                $customer->email = $buyer["email"];
                                $customer->first_name = $detailsInfo->buyer->firstName;
                                $customer->last_name = $detailsInfo->buyer->lastName;
                                $customer->adress = $detailsInfo->buyer->address->street;
                                $customer->city = $detailsInfo->buyer->address->city;
                                $customer->post_code = $detailsInfo->buyer->address->postCode;
                                $customer->no_tel = $detailsInfo->buyer->phoneNumber;
                                $customer->office = $detailsInfo->buyer->companyName;
                                $customer->guest = $buyer["guest"];
                                $customer->orders = 1;
                                $customer->save();
                            }
                            // wyślij maila
                            MailController::sendCode($order["id"], $detailsInfo->lineItems[0]->quantity, $userData->access_token);
                            
                            $user['credits'] -= $detailsInfo->lineItems[0]->quantity;
                            $user->save();

                            // zmień status zamówienia !!!!
                            $lastEvent = $order["id"];
                            if(SentMail::where('order_id', $order["id"])->where('resend', 0)->count() == $detailsInfo->lineItems[0]->quantity) {
                                $changeStatus = changeStatus($userData->access_token, $order["order"]["checkoutForm"]["id"]);
                                if($changeStatus != 0) {
                                    $log[] = $changeStatus["error_description"];
                                }
                            }
                        } else {
                            $lastEvent = $order["id"];
                            $log[] = "old: ".$order["id"];
                        }
                    }
                } else {
                    $log[] = "last: ".$lastEvent;
                }
                // zmiana w badzie danych ostatniego eventu DO ZMIANY 
                // $userData->last_event = $lastEvent;
                // $userData->save();
            } else {
                $log[] = "user: $userData->id waiting";
            }     
            unset($res);
        }
        return $log;
    }
}