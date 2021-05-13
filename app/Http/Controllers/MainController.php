<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SoapClient;

use AsocialMedia\AllegroApi\AllegroApi;

class MainController extends Controller
{
    public static function csrfToken(Request $request)
    {   
        $token = $request->session()->token();

        $token = csrf_token();

        return $token;
    }
}