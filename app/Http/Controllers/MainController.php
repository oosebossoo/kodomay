<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SoapClient;

use AsocialMedia\AllegroApi\AllegroApi;

class MainController extends Controller
{
    public function csrf_token()
    {   
        $token = $request->session()->token();

        $token = csrf_token();

        return $token;
    }
}