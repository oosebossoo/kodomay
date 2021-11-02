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
        'stock_available',
        'stock_sold',
        'price_amount',
        'price_currency',
        'status_allegro',
        'startedAt',
        'endingAt',
        'is_active',
    ];
}