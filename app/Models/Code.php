<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    use HasFactory;

    protected $table = 'code';

    protected $fillable = [
        'offer_id',
        'db_id',
        'db_type',
        'db_name',
        'code',
        'seller_id',
        'status',
        'created_at',
        'updated_at',
    ];
}
