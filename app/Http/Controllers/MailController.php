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
use App\Models\MailTemplate;

class MailController extends Controller
{
   public static function sendCode($order_id, $quantity, $email) 
   {
      // dd($order_id, $quantity, $email);
      $email = 'sebek.kasprzak.kodomat@gmail.com';

      $order = Orders::where('order_id', $order_id)->first();

      for ($i = 0; $i < $quantity; $i++)
      {
         // $code = Code::where('offer_id', $order->offer_id)->where('status', 1)->where('seller_id', $order->seller_id)->first();
         $code = Code::where('status', 1)->where('seller_id', $order->seller_id)->first();
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
         $sentMail->order_id = $order_id;
         $sentMail->offer_id = $order->offer_id;
         $sentMail->code_id = $code->id;
         $sentMail->resend = 0;
         $sentMail->save();
      }
   }

   public function testMail(Request $request)
   {
      $email = 'sebek.kasprzak.work@gmail.com';

      $data['code'] = '2345haejfdsj098124.faiushfo';
      Mail::send([], [], function($message) use ($email, $data) {

         $emailTemplate = MailTemplate::where('id', 1)->first();

         $message->to($email, "Sebastian");
         $message->subject("Testowanie templatek");
         $message->from('noreplay@kodo.mat','Kodomat');
         
         $message->setBody($emailTemplate->parse($data), 'text/html');
      });
   }

   public static function sendEmailAgain(Request $request) 
   {
      $email = 'sebek.kasprzak.kodomat@gmail.com';

      if(!isset($request->order_id))
      {
         return [
            'status' => 1 ,
            'desc' => 'please give me order id... :/'
         ];
      }

      if(isset($request->email))
      {
         $email = $request->email;
      }

      $order = Orders::where('order_id', $request->order_id)->first();

      if(!isset($order->id))
      {
         return [
            'status' => 1 ,
            'desc' => 'please check order id... i cant find any order :/'
         ];
      }

      for ($i = 0; $i < $order->quantity; $i++)
      {
         $code = Code::where('offer_id', $order->offer_id)->where('status', 1)->where('seller_id', $order->seller_id)->first();
         $data = array('code' => $code->code);
      }

      $mail = Mail::send('mail', $data, function($message) use ($order, $email) {
         $message->to($email, "Sebastian")->subject
            ("Send Again | Order no. $order->offer_id");
         $message->from('noreplay@kodo.mat','Kodomat');   
      });

      for ($i = 0; $i < $order->quantity; $i++)
      {
         $resend = SentMail::where('order_id', $order->order_id)->first();
         SentMail::where('order_id', $order->order_id)->update(['resend' => $resend->resend + 1]);
      }

      return [
         'status' => 0 ,
         'desc' => 'resend succesfull... :)'
      ];
   }

   public function activate()
   {
      
      $data = array('url' => "localhost/activation?activate_code=".Auth::user()->activate_code);
      // dd($data);
      Mail::send(['text'=>'activate'], $data, function($message) {
         $message->to('email', 'name')->subject
            ('Welcome'.'name');
         $message->from('noreplay@kodo.mat','Kodomat');
      });
   }
}
