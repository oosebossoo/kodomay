<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'http://localhost:8000/add_template',
        'http://localhost:8000/save_template',
        'http://localhost:8000/delete_template',
        'http://localhost:8000/save_presonal_data',
        'http://localhost:8000/save_notifications',
        'http://localhost:8000/reset_password',
        'http://localhost:8000/reset_password_mail',
        '/register',
        'http://kodomat.herokuapp.com/',
        'http://kodomat.herokuapp.com/*',
        'http://kodomat.herokuapp.com/login',
        'http://kodomat.herokuapp.com/add_template',
        'http://kodomat.herokuapp.com/save_template',
        'http://kodomat.herokuapp.com/delete_template',
        'http://kodomat.herokuapp.com/save_presonal_data',
        'http://kodomat.herokuapp.com/save_notifications',
    ];
}
