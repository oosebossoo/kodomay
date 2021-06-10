<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentMail extends Model
{
    use HasFactory;

    protected $table = "sent_mail";

    protected $fillable = [
        'customer_id',
        'code_id'
    ];
}
