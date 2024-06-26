<?php

namespace App\Repositories;

use App\Repositories\Allegro;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\MailController;

use App\Repositories\IntegrationRepository;

// use App\Models\Customer;
use App\Models\UserData;
use App\Models\Orders;
// use App\Models\OrdersTable;
use App\Models\Offers;
use App\Models\SentMail;
use App\Models\Code;

class AllegroAccountRepository
{
    static function testAllegro($ENV)
    {
        return Allegro::getCredenctial($ENV);
    }

    static function updateOffers($user_id)
    {
        $limit = 100;
        if(isset($request->limit)) {
            $limit = $request->limit;
        }

        $userDatas = UserData::where('user_id', $user_id)->get();
        Offers::where('seller_id', $user_id)->where('is_active', 'NO')->delete();

        foreach($userDatas as $userData)
        {
            $response = Http::withHeaders([
                "Accept" => "application/vnd.allegro.public.v1+json",
                "Authorization" => "Bearer $userData->access_token"
            ])->get("https://api.allegro.pl/sale/offers?limit=$limit");

            if(isset($response['offers'])) {
                foreach($response['offers'] as $offer)
                {
                    $ending[] = $offer;
                    $existOffer = Offers::where('offer_id', $offer['id'])->get();
                    if(!isset($existOffer[0]["id"])) {
                        $offerDB = new Offers();
                        $offerDB->ordinal_id = Offers::where('seller_id', $user_id)->count() + 1;
                        $offerDB->seller_id = $user_id;
                        $offerDB->offer_id = $offer['id'];
                        $offerDB->offer_name = $offer['name'];
                        $offerDB->stock_available = $offer["stock"]["available"];
                        $offerDB->stock_sold = $offer['stock']['sold'];
                        
                        $d=strtotime("-1 Months");
                        $date = date("Y-m-d h:i:s", $d);
                        $soldInTrD = Orders::where('offer_id', $offer['id'])->where('created_at', '>', $date)->count();
                        $offerDB->sold_last_30d = $soldInTrD;

                        
                        if(isset($offer['sellingMode']['price']['amount'])) {
                            $offerDB->price_amount = $offer['sellingMode']['price']['amount'];
                        } else {
                            $offerDB->price_amount = "null";
                        }

                        if(isset($offer['sellingMode']['price']['currency'])) {
                            $offerDB->price_currency = $offer['sellingMode']['price']['currency'];
                        } else {
                            $offerDB->price_currency = "null";
                        }

                        $offerDB->platform = "Allegro";

                        if(isset($offer['publication']['status'])) {
                            $offerDB->status_platform = $offer['publication']['status'];
                        } else {
                            $offerDB->status_platform = "null";
                        }

                        if(isset($offer['publication']['startedAt'])) {
                            $offerDB->startedAt = $offer['publication']['startedAt'];
                        } else {
                            $offerDB->startedAt = "null";
                        }

                        if(isset($offer['publication']['endingAt'])) {
                            $offerDB->endingAt = $offer['publication']['endingAt'];
                        } else {
                            $offerDB->endingAt = "Do wyczerpania zapasów";
                        }
                        
                        if(isset($offer['publication']['endedAt'])) {
                            $offerDB->endedAt = $offer['publication']['endedAt'];
                        } else {
                            $offerDB->endedAt = "Do wyczerpania zapasów";
                        }
                        
                        $offerDB->is_active = 'NO';
                        $offerDB->save();
                    }
                }
            }
        }
    }

    static function offers($user_id, $update = NULL)
    {
        if($update != NULL)
        {
            self::updateOffers($user_id);
        }
        return response()->json(Offers::where('seller_id', $user_id)->where('is_active', 'YES')->get());
    }

    static function offersOff($user_id)
    {
        return response()->json(Offers::where('seller_id', $user_id)->where('is_active', 'NO')->get(), 200);
    }

    static function offer($offer_id)
    {
        $offer = Offers::where('id', $offer_id)->first();

        return response()->json($offer);
    }

    static function setMonitoring($offer_id, $templ_id = null, $db_id = null)
    {
        if($templ_id != null || $db_id !=null) {
            if(
                Offers::where('offer_id', $offer_id)->update(['mail_template' => $templ_id, 'is_active' => 'YES', 'codes_id' => $db_id]) && SentMail::where('offer_id', $offer_id)->update(['resend' => 0])
            ) {
                $offer = Offers::where('offer_id', $offer_id)->first();
                $last_event_update_status = IntegrationRepository::lastEvent($offer->seller_id);
                return response()->json(['message' => 'set', 'last_event_update_status' => $last_event_update_status], 200);
            }
            return response()->json(['message' => "Can't set, check offer id"], 400);
        } else {
            return response()->json(['message' => "Can't set, missing values"], 400);
        }
    }

    static function offMonitoring($offer_id)
    {
        $offer = Offers::where('offer_id', $offer_id)->first();

        if(!empty($offer)) {
            Offers::where('offer_id', $offer_id)->update([ 'is_active' => 'NO', 'mail_template' => '', 'codes_id' => '']);
            return response()->json(['message' => 'set'], 200);
        } else {
            return response()->json(['message' => "Can't set, check offer id"], 400);
        }
    }

    static function getMonitoring($set)
    {
        $offers = Offers::where('is_active', $set)->get();

        return response()->json($offers);
    }
}