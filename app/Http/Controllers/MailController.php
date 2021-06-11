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
   public static function sendCode($request) 
   {
      $mail = $request["mail"];
      $customerName = $request["customerName"];

      $code = CodesController::getSellableCode();
      $codeId = json_decode($code->original->id);

      $data = array('name'=> $request["customerName"], 'code' => $code->original->code, 'email' => $request["mail"]);

      CodesController::changeStatusOfCode($codeId);

      Mail::send(['text'=>'mail'], $data, function($message) use ($request) {
         $message->to($request["mail"], $request["customerName"])->subject
            ('Order no. '.$request["subject"]);
         $message->from('noreplay@kodo.mat','Kodomat');
      });
      return true;
   }

   public static function sendEmail($request) 
   {
      $email = 'sebek.kasprzak.kodomat@gmail.com';

      if(isset($request->email))
      {
         $email = $request->email;
      }

      $order = Orders::where('order_id', $request->order_id)->first();
      $offer = Offers::where('offer_id', $order->offer_id)->first();

      $message['order_id'] = $order->order_id;

      for ($i = 0; $i < $order->quantity; $i++)
      {
         $code = Code::where('offer_id', $offer->offer_id)->where('status', 1)->where('seller_id', $order->seller_id)->first();
         $data = array('code' => $code->code);
      }

      Mail::send(['text'=>'mail'], $data, function($message) use ($order) {
         $message->to('sebek.kasprzak.kodomat@gmail.com', "Sebastian")->subject
            ("Order no. $order->offer_id");
         $message->from('noreplay@kodo.mat','Kodomat');   
      });

      // for ($i = 0; $i < $order->quantity; $i++)
      // {
      //    $sentMail = new SentMail();
      //    $sentMail->customer_id = $order->customer_id;
      //    $sentMail->offer_id = $order->offer_id;
      //    $sentMail->code_id = $code->code_id;
      //    $sentMail->save();
      // }
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
