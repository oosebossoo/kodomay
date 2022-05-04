<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TimeController extends Controller
{
    public static function repairTime()
    {
        $year = date('Y');

        if(date('Y-m-d') >= date_format(date_create("04/01/".$year), "Y-m-d") 
        && date("Y-m-d") <= date_format(date_create("11/01/".$year), "Y-m-d"))
        {
            return 2;
        } else {
            return 1;
        }
    }
}
