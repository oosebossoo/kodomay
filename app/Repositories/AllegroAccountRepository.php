<?php

namespace App\Repositories;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\MailController;

// use App\Models\Customer;
use App\Models\UserData;
use App\Models\Orders;
// use App\Models\OrdersTable;
use App\Models\Offers;
// use App\Models\SentMail;
use App\Models\Code;

class AllegroAccountRepository
{
    // protected $templ_id = null;
    // protected $db_id = null;

    static function offers($user_id)
    {
        $limit = 100;
        if(isset($request->limit)) {
            $limit = $request->limit;
        }

        $userDatas = UserData::where('user_id', $user_id)->get();

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
                            $offerDB->endingAt = "Neverending offer... :)";
                        }
                        
                        if(isset($offer['publication']['endedAt'])) {
                            $offerDB->endingAt = $offer['publication']['endedAt'];
                        } else {
                            $offerDB->endingAt = "Neverended offer... :)";
                        }
                        
                        $offerDB->is_active = 'YES';
                        $offerDB->save();
                    }
                }
            }
        }
        // dd(response()->json(Offers::where('seller_id', $user_id)->get()));
    return response()->json(Offers::where('seller_id', $user_id)->get());
    }

    static function offer($offer_id)
    {
        $offer = Offers::where('id', $offer_id)->first();

        return response()->json($offer);
    }

    static function setMonitoring($offer_id, $templ_id = null, $db_id = null)
    {
        if($templ_id != null && $db_id !=null) {
            if(
                Code::where('db_id', $db_id)->update(['offer_id' => $offer_id]) &&
                Offers::where('offer_id', $offer_id)->update(['mail_template' => $templ_id, 'is_active' => 'YES'])
            ) {
                return response()->json(['message' => 'set'], 200);
            }
            return response()->json(['message' => "Can't set, check offer id"], 400);
        }

        $offer = Offers::where('offer_id', $offer_id)->first();

        if(!empty($offer)) {
            if($offer->is_active == 'NO') {
                Offers::where('offer_id', $offer_id)->update([ 'is_active' => 'YES' ]);
                return response()->json(['is_active' => 'YES'], 200);
            }

            if($offer->is_active == 'YES') {
                Offers::where('offer_id', $offer_id)->update([ 'is_active' => 'NO' ]);
                return response()->json(['is_active' => 'NO'], 200);
            }
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