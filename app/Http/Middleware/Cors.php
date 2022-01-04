<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request)
            ->header('Access-Control-Allow-Origin', 'https://kodomat-front-end.herokuapp.com/')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers',' Origin, Content-Type, Accept, Authorization, X-Request-With, sec-ch-ua, sec-ch-ua-mobile, sec-ch-ua-platform, User-Agent')
            ->header('Access-Control-Allow-Credentials',' true');
    }
}
