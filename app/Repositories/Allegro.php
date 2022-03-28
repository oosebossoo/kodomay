<?php

namespace App\Repositories;

class Allegro
{
    static function getCredenctial($ENV)
    {
        if($ENV == "PROD")
        {
            return [
                'client' => "prod123", 
                'secret' => "prod456"
            ];
        } elseif($ENV == "DEV") {
            return [
                'client' => "dev123", 
                'secret' => "dev456"
            ];
        } else {
            return response()->json([], 500);
        }
    }
}