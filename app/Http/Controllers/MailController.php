<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CodesController;
use App\Models\Code;
use App\Models\Orders;
use App\Models\Offers;
use App\Models\SentMail;

class MailController extends Controller
{
   public static function sendCode($order_id, $quantity, $email) 
   {
      $email = 'sebek.kasprzak.kodomat@gmail.com';

      $order = Orders::where('order_id', $order_id)->first();
      $offer = Offers::where('offer_id', $order->offer_id)->first();

      for ($i = 0; $i < $quantity; $i++)
      {
         $code = Code::where('offer_id', $offer->offer_id)->where('status', 1)->where('seller_id', $order->seller_id)->first();
         $data = array('code' => $code->code);
      }

      Mail::send('mail', $data, function($message) use ($order, $email) {
         $message->to($email, "Sebastian")->subject
            ("Order no. $order->offer_id");
         $message->from('noreplay@kodo.mat','Kodomat');   
      });

      for ($i = 0; $i < $order->quantity; $i++)
      {
         $sentMail = new SentMail();
         $sentMail->customer_id = $order->customer_id;
         $sentMail->order_id = $request->order_id;
         $sentMail->offer_id = $order->offer_id;
         $sentMail->code_id = $code->id;
         $sentMail->save();
      }
   }

   public static function sendEmail(Request $request) 
   {
      $email = 'sebek.kasprzak.kodomat@gmail.com';

      if(isset($request->email))
      {
         $email = $request->email;
      }

      $order = Orders::where('order_id', $request->order_id)->first();
      $offer = Offers::where('offer_id', $order->offer_id)->first();

      for ($i = 0; $i < $order->quantity; $i++)
      {
         $code = Code::where('offer_id', $offer->offer_id)->where('status', 1)->where('seller_id', $order->seller_id)->first();
         $data = array('code' => $code->code);
      }

      // dd($code);

      Mail::send('mail', $data, function($message) use ($order, $email) {
         $message->to($email, "Sebastian")->subject
            ("Order no. $order->offer_id");
         $message->from('noreplay@kodo.mat','Kodomat');   
      });

      for ($i = 0; $i < $order->quantity; $i++)
      {
         $sentMail = new SentMail();
         $sentMail->customer_id = $order->customer_id;
         $sentMail->order_id = $request->order_id;
         $sentMail->offer_id = $order->offer_id;
         $sentMail->code_id = $code->id;
         $sentMail->save();
      }
   }

   public function activate()
   {
      
      $data = array('url' => "localhost/activation?activate_code=".Auth::user()->activate_code);
      // dd($data);
      Mail::send(['text'=>'activate'], $data, function($message) {
         $message->to(Auth::user()->email, Auth::user()->name)->subject
            ('Welcome'.Auth::user()->name);
         $message->from('noreplay@kodo.mat','Kodomat');
      });
   }
}
