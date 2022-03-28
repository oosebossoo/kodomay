<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = "notification";

    protected $fillable = [
        'user_id',
        'send_email_copy_code',
        'email',
        'send_info_new_adv',
        'send_info_end_of_credit',
        'send_info_zero_credit',
        'send_info_end_of_code',
    ];
}
