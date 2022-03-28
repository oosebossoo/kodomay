<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use App\Http\Controllers\MailController;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $notiSettings = Notification::where('user_id', $request->user_id)->first();

        return $notiSettings;
    }

    public static function empty_code($offer_id, $user_id)
    {
        $noti = Notification::where('user_id', $user_id)->first();

        if($noti->empty_dbs)
        {
            $user = User::where('id', $user_id)->first();
            MailController::sendNotification($user, "empty_code");
            return 1;
        }
        return 0;
    }

    public static function last_codes($offer_id, $user_id)
    {
        $noti = Notification::where('user_id', $user_id)->first();

        if($noti->last_codes)
        {
            $user = User::where('id', $user_id)->first();
            MailController::sendNotification($user, "last_codes");
            return 1;
        }
        return 0;
    }
}
