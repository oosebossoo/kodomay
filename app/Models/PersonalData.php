<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalData extends Model
{
    use HasFactory;
    protected $table = "personal_data";

    protected $fillable = [
        'user_id',
        'type',
        'full_name',
        'full_office_name	',
        'adress',
        'post_code',
        'city',
        'phone_number',
        'country',
    ];
}
