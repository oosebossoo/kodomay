<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Orders;
use App\Models\SentMail;
use App\Models\ServicesStatistics;

class ServicesStatisticsController extends Controller
{
    public function getOrderTimeToSentMail(Request $request)
    {
        return ServicesStatisctics::all();
    }

    public function makeOrderTimeToSentMail(Request $request)
    {
        $order = Orders::where('order_id', $request->order_id)->first();
        $sent_mail = SentMail::orderBy('id', 'desc')->where('order_id', $request->order_id)->where('resend', 0)->first();
        if($sent_mail->resend == 0) {
            $start = \Carbon\Carbon::parse($order->order_date);
            $end = \Carbon\Carbon::parse($sent_mail->created_at);
            $time = $end->diffAsCarbonInterval($start)->total('milliseconds');
            $stat = new ServicesStatistics();
            $stat->order_id = $request->order_id;
            $stat->order_time_to_sent_mail = $time;
            $stat->save();
        }
        return $time;
    }
}
