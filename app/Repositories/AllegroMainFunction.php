<?php

namespace App\Repositories;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\MailController;

use App\Models\Customer;
use App\Models\UserData;
use App\Models\Orders;
use App\Models\OrdersTable;
use App\Models\Offers;
// use App\Models\SentMail;
// use App\Models\Code;

class AllegroMainFunction
{
    static function checkOut($checkOutFormId, $token)
    {
        $response = Http::withHeaders([
            "Accept" => "application/vnd.allegro.public.v1+json",
            "Authorization" => "Bearer $token"
        ])->get("https://api.allegro.pl/order/checkout-forms/$checkOutFormId");
        return json_decode($response);
    }

    static function mainFunction($request)
    {
        $details = array();

        $log[] = "";
        echo "test";

        $userDatas = UserData::where('user_id', $request->user_id)->get();

        if(!isset($userDatas))
        {
            return [ 
                'status' => 1,
                'desc' => 'any allegro account does not exist in database' 
            ];
        }

        foreach ($userDatas as $userData)
        {
            return $userData->access_token;
            $response = Http::withHeaders([
                "Accept" => "application/vnd.allegro.public.v1+json",
                "Authorization" => "Bearer $userData->access_token"
            ])->get("https://api.allegro.pl/order/events?type=READY_FOR_PROCESSING&from=$userData->last_event");

            if($response->failed() || $response->clientError())
            {
                UserData::where('user_id', $request->user_id)->update([
                    'refresh' => true
                ]);
                return "refresh_token";
            }
            return $response;

            if($response["events"] != []) {
                return $response["events"];
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

                            // wyślij maila
                            MailController::sendCode($order["id"], $detailsInfo->lineItems[0]->quantity, $buyer["email"]);

                            // zmień status zamówienia !!!!
                            $lastEvent = $order["id"];
                            $details[] = $orderModel;
                        }
                        else {
                            $lastEvent = $order["id"];
                            $log[] = "old order: ".$order["id"];
                        }
                    }
                    echo "zakończone dodawanie<br>";
                    $status = 0;
                    $desc = "Oh yhee.. some new orders :) ";
                }
                else {
                    echo "nie było co dodawać<br>";
                    $log[] = "last order: ".$lastEvent;
                    $status = 0;
                    $desc = "Please... give me some orders :( ";
                }
                // zmiana w badzie danych ostatniego eventu
                $userData->last_event = $lastEvent;
                $userData->save();
            }
            else {
                echo "nie pobrano niczego z allegro<br>";
                $status = 0;
                $desc = "Please... give me some orders :( ";
            }     
            unset($res);
        }
        return [date("Y-m-d") .'/'. date("H:i:s") => ["status" => $status, "desc" => $desc, $log]];
    }
}