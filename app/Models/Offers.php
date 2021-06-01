<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offers extends Model
{
    use HasFactory;
    protected $table = "offers";

    protected $fillable = [
        'offer_id',
        'offer_name',
        'stock',
        'price',
        'publication',
        'is_active',
    ];
}
