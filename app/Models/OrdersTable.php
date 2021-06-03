<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersTable extends Model
{
    use HasFactory;

    protected $table = "orders_table";

    protected $fillable = [
        'offer_id',
        'seller_id',
        'customer_id',
        'offer_link',
        'count',
    ];
}
