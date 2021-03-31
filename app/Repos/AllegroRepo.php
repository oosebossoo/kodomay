<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use AsocialMedia\AllegroApi\AllegroRestApi;

use App\Models\UserData;

use Auth;

class AllegroRepo
{
    // new_user_auth()

    // get_token()

    // get_user_info()

    // new_offer()

    // edit_offer()

    // get_offer()
    // -> /sale/offer?sort={value}

    // get_status_of_ofert(id) 
    // -> /order/event
    // -> /sale/offer-evets

    public function getOrderEvents()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.allegro.pl/order/events",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/vnd.allegro.beta.v1+json",
                "Authorization: Bearer TutajWklejBearerToken",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Content-Type: application/vnd.allegro.beta.v1+json",
                "Host: api.allegro.pl",
                "accept-encoding: gzip, deflate",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
        echo $response;
        }
    }
}
