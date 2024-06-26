<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Mail;
use Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CodesController;
use App\Http\Controllers\NotificationController;

use App\Repositories\MailGateway\PaymentMails;
use App\Repositories\MailGateway\SendCodesMails;

use App\Models\Code;
use App\Models\Customer;
use App\Models\DebugInfo;
use App\Models\MailTemplate;
use App\Models\Orders;
use App\Models\Offers;
use App\Models\SentMail;
use App\Models\UserData;

class MailController extends Controller
{
   public function __construct(PaymentMails $paymentMails, SendCodesMails $sendCodesMails)
   {
      $this->paymentMails = $paymentMails;
      $this->sendCodesMails = $sendCodesMails;
   }

   public function testSend(Request $request)
   {
      return $this->sendCodesMails::sendCode($request->order_id, $request->quantity, $request->access_token);
   }

   public static function sendCode($order_id, $quantity, $access_token) 
   {
      $response = Http::withHeaders([
         "Accept" => "application/vnd.allegro.public.v1+json",
         "Authorization" => "Bearer $access_token"
      ])->get("https://api.allegro.pl/me"); 
      if(!isset($response["error"])) {   
         $user = json_decode($response);
      } else {
         $user = '';
      }

      $data = "";
      $email = 'sebek.kasprzak.kodomat@gmail.com';

      $order = Orders::where('order_id', $order_id)->first();
      $offer = Offers::where('offer_id', $order->offer_id)->first();
      $customer = Customer::where('customer_id', $order->customer_id)->first();
      $mail = MailTemplate::where('id', $offer->mail_template)->first();
      $html = $mail->template;
      $codes = array();

      if(Code::where('status', 1)->where('seller_id', $order->seller_id)->where('db_id', $offer->codes_id)->count() < $request->quantity) {
         for($i = 0; $i < $request->quantity; $i++)
         {
            $sentMail = new SentMail();
            $sentMail->customer_id = $order->customer_id;
            $sentMail->order_id = $order->order_id;
            $sentMail->offer_id = $order->offer_id;
            $sentMail->code_id = "";
            $sentMail->send = 0;
            $sentMail->save();
         }
         return 1;
      }

      for ($i = 0; $i < $quantity; $i++)
      {
         $code = Code::where('status', 1)->where('seller_id', $order->seller_id)->where('db_id', $offer->codes_id)->first();
         if(Code::where('status', 1)->where('seller_id', $order->seller_id)->where('db_id', $offer->codes_id)->count() < 11)
         {
            NotificationController::last_codes($offer->offer_id, $order->seller_id);
         }
         $data .= $code->code."<br>";
         $code->status = 0;
         $code->save();
         array_push($codes, $code->id);
      }

      if (strpos($html,'(NAZWA_SPRZEDAJACEGO)') !== false) {
         $html = str_replace('(NAZWA_SPRZEDAJACEGO)', $user->login, $html);
      }

      if (strpos($html,'(ALLEGRO_LOGIN)') !== false) {
         $html = str_replace('(ALLEGRO_LOGIN)', $customer->login, $html);
      }

      if (strpos($html,'(KOD)') !== false) {
         $html = str_replace('(KOD)', $data, $html);
      }

      if (strpos($html,'(EMAIL)') !== false) {
         $email = Customer::select('email')->where('customer_id', $order->customer_id)->first();
         $html = str_replace('(EMAIL)', $email->email, $html);
      }

      if (strpos($html,'(NAZWA_AUKCJI)') !== false) {
         $email = Customer::select('email')->where('customer_id', $order->customer_id)->first();
         $html = str_replace('(NAZWA_AUKCJI)', $offer->offer_name, $html);
      }

      if (strpos($html,'(ILOSC)') !== false) {
         $email = Customer::select('email')->where('customer_id', $order->customer_id)->first();
         $html = str_replace('(ILOSC)', $order->quantity, $html);
      }

      // ------------

      // \Mail::send([], [], function ($message) use ($order, $email, $html, $mail, $user, $customer) {
      //    $message->to('sebek.kasprzak.kodomat@gmail.com')
      //    ->replyTo($user->email, $user->login)
      //    ->from("office@accounts4life.com", $user->login)
      //    ->subject("$mail->template_subject")
      //    ->setBody($html, 'text/html');
      // });

      try {
         \Mail::send([], [], function ($message) use ($order, $email, $html, $mail, $user, $customer) {
            $message->to($customer->email)
            ->replyTo($user->email, $user->login)
            ->from("office@accounts4life.com", $user->login)
            ->subject("$mail->template_subject")
            ->setBody($html, 'text/html');
         });
      }
      catch(\Exception $e) {
         $di = new DebugInfo();
         $di->data = $e->getMessage();
         $di->data1 = getdata();
         $di->save();
         try {
            \Mail::send([], [], function ($message) use ($order, $email, $html, $mail, $user, $customer) {
               $message->to($customer->email)
               ->replyTo($user->email, $user->login)
               ->from("office@accounts4life.com", $user->login)
               ->subject("$mail->template_subject")
               ->setBody($html, 'text/html');
            });
         }
         catch(\Exception $e) {
            $di = new DebugInfo();
            $di->data = $e->getMessage();
            $di->data1 = getdata();
            $di->save();
            \Mail::send([], [], function ($message) use ($order, $email, $html, $mail, $user, $customer) {
               $message->to($customer->email)
               ->replyTo($user->email, $user->login)
               ->from("office@accounts4life.com", $user->login)
               ->subject("$mail->template_subject")
               ->setBody($html, 'text/html');
            });
         }
      }

      if(count(Mail::failures()) > 0) {    
         // jest error
      } else {
         // nie ma erroru
      }

      

      $this->sendCodesMails::sendCode($order_id, $quantity, $access_token);
   }

   public static function sendOldMail($order_id, $mail_id, $code, $access_token) 
   {
      $response = Http::withHeaders([
         "Accept" => "application/vnd.allegro.public.v1+json",
         "Authorization" => "Bearer $access_token"
      ])->get("https://api.allegro.pl/me"); 
      if(!isset($response["error"])) {   
         $user = json_decode($response);
      } else {
         $user = '';
      }

      // $data = "";
      $email = 'sebek.kasprzak.kodomat@gmail.com';

      $order = Orders::where('order_id', $order_id)->first();
      $offer = Offers::where('offer_id', $order->offer_id)->first();
      $customer = Customer::where('customer_id', $order->customer_id)->first();
      $mail = MailTemplate::where('id', $offer->mail_template)->first();
      $html = $mail->template;

      // $data = $code->code;
      $code->status = 0;
      $code->save();

      if (strpos($html,'(NAZWA_SPRZEDAJACEGO)') !== false) {
         $html = str_replace('(NAZWA_SPRZEDAJACEGO)', $user->login, $html);
      }

      if (strpos($html,'(ALLEGRO_LOGIN)') !== false) {
         $html = str_replace('(ALLEGRO_LOGIN)', $customer->login, $html);
      }

      if (strpos($html,'(KOD)') !== false) {
         $html = str_replace('(KOD)', $code->code, $html);
      }

      if (strpos($html,'(EMAIL)') !== false) {
         $email = Customer::select('email')->where('customer_id', $order->customer_id)->first();
         $html = str_replace('(EMAIL)', $email->email, $html);
      }

      if (strpos($html,'(NAZWA_AUKCJI)') !== false) {
         $email = Customer::select('email')->where('customer_id', $order->customer_id)->first();
         $html = str_replace('(NAZWA_AUKCJI)', $offer->offer_name, $html);
      }

      if (strpos($html,'(ILOSC)') !== false) {
         $email = Customer::select('email')->where('customer_id', $order->customer_id)->first();
         $html = str_replace('(ILOSC)', $order->quantity, $html);
      }

      // ------------

      \Mail::send([], [], function ($message) use ($order, $email, $html, $mail, $user, $customer) {
         $message->to('sebek.kasprzak.kodomat@gmail.com')
         ->replyTo($user->email, $user->login)
         ->from($user->email, $user->login)
         ->subject("$mail->template_subject")
         ->setBody($html, 'text/html');
      });

      $sent = SentMail::where('id', $mail_id)->first();
      $sent->resend = 1;
      $sent->code_id = $code_id;
      $sent->save();

      \Mail::send([], [], function ($message) use ($order, $email, $html, $mail, $user, $customer) {
         $message->to($customer->email)
         ->replyTo($user->email, $user->login)
         ->from($user->email, $user->login)
         ->subject("$mail->template_subject")
         ->setBody($html, 'text/html');
      });

   }

   public function testMail(Request $request)
   {
      return $this->sendCodesMails::testMail($request);
   }

   public static function sendNotification($user, $noti_type)
   {
      if($noti_type == "empty_code")
      {
         $html = "empty_code";
         $subject = "Pusta baza kodów || ZATRZYMANIE AUTOMATU";
      }
      if($noti_type == "last_codes")
      {
         $html = "last_codes";
         $subject = "Zostały ostatnie koty w db";
      }
      Mail::send([], [], function ($message) use ($html, $user, $subject) {
         $message->to($user->email)
         ->from('cybersent.noreply@gmail.com',"Cybersent Notification")
         ->subject($subject)
         ->setBody($html, 'text/html');
      });
   }

   public static function sendEmailAgain(Request $request) 
   {
      $email = 'sebek.kasprzak.kodomat@gmail.com';

      if(!isset($request->order_id)) {
         return [
            'status' => 1 ,
            'desc' => 'please give me order id... :/'
         ];
      }

      if(isset($request->email)) {
         $email = $request->email;
      }

      $order = Orders::where('order_id', $request->order_id)->first();

      if(!isset($order->id)) {
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

   public static function payment($type, $data)
   {
      if($type == 'confirm')
      {
         PaymentMails::paymentConfirm($data);
      }

      if($type == 'begin')
      {
         PaymentMails::paymentBegin($data);
      }
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
