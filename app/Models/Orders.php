<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $table = "orders";

    protected $fillable = [
        'offer_id',
        'offer_name',
        'offer_price',
        'offer_currency',
        'quantity',
        'order_price',
        'order_currency',
    ];
}
