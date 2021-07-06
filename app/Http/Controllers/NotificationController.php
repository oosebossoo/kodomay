<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $notiSettings = Notification::where('user_id', $request->user_id)->first();

        return $notiSettings;
    }
}
