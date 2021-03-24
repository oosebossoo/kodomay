<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserData extends Model
{
    use HasFactory;

    protected $table = "user-data";

    protected $fillable = [
        'access_token',
        'token_type',
        'refresch_token',
        'expires_in',
        'scope',
        'allegro_api',
        'jti'
    ];
}
