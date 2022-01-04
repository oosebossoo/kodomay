<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['/api*', '/stat/orders/today/count', '/stat'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_origins' => ['https://kodomat-front-end.herokuapp.com'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => [
        'Origin', 
        'Content-Type', 
        'Accept', 
        'Authorization', 
        'X-Request-With', 
        'sec-ch-ua', 
        'sec-ch-ua-mobile', 
        'sec-ch-ua-platform', 
        'User-Agent'
    ],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
