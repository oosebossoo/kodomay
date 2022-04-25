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

use App\Models\Code;
use App\Models\Orders;
use App\Models\Customer;
use App\Models\Offers;
use App\Models\SentMail;
use App\Models\MailTemplate;
use App\Models\UserData;

class MailController extends Controller
{
   public function testSend(Request $request)
   {
      self::sendCode($request->order_id, $request->quantity, $request->access_token);
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

      for ($i = 0; $i < $quantity; $i++)
      {
         $code = Code::where('status', 1)->where('seller_id', $order->seller_id)->where('db_id', $offer->codes_id)->first();
         if(Code::where('status', 1)->where('seller_id', $order->seller_id)->where('db_id', $offer->codes_id)->count() < 11)
         {
            NotificationController::last_codes($offer->offer_id, $order->seller_id);
         }
         if(Code::where('status', 1)->where('seller_id', $order->seller_id)->where('db_id', $offer->codes_id)->count() < 1)
         {
            $sentMail = new SentMail();
            $sentMail->customer_id = $order->customer_id;
            $sentMail->order_id = $order->order_id;
            $sentMail->offer_id = $order->offer_id;
            $sentMail->code_id = '';
            $sentMail->resend = 1;
            $sentMail->save();

            NotificationController::empty_code($order->offer_id, $order->seller_id);

            return 1;
         }
         $data .= $code->code." ";
         $code->status = 0;
         $code->save();
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

      \Mail::send([], [], function ($message) use ($order, $email, $html, $mail, $user, $customer) {
         $message->to('sebek.kasprzak.kodomat@gmail.com')
         ->replyTo($user->email, $user->login)
         ->from($user->email, $user->login)
         ->subject("$mail->template_subject")
         ->setBody($html, 'text/html');
      });

      // \Mail::send([], [], function ($message) use ($order, $email, $html, $mail, $user, $customer) {
      //    $message->to($customer->email)
      //    ->replyTo($user->email, $user->login)
      //    ->from($user->email, $user->login)
      //    ->subject("$mail->template_subject")
      //    ->setBody($html, 'text/html');
      // });

      for ($i = 0; $i < $order->quantity; $i++)
      {
         $sentMail = new SentMail();
         $sentMail->customer_id = $order->customer_id;
         $sentMail->order_id = $order->order_id;
         $sentMail->offer_id = $order->offer_id;
         $sentMail->code_id = $code->id;
         $sentMail->resend = 0;
         $sentMail->save();
      }
   }

   public static function sendOldMail($order_id, $mail_id, $code_id, $access_token) 
   {
      $code = Code::where('id', $code_id)->first();

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
      $sent->resend = 0;
      $sent->code_id = $code_id;
      $sent->save();

      // \Mail::send([], [], function ($message) use ($order, $email, $html, $mail, $user, $customer) {
      //    $message->to($customer->email)
      //    ->replyTo($user->email, $user->login)
      //    ->from($user->email, $user->login)
      //    ->subject("$mail->template_subject")
      //    ->setBody($html, 'text/html');
      // });

   }

   public function testMail(Request $request)
   {
      $response = Http::withHeaders([
         "Accept" => "application/vnd.allegro.public.v1+json",
         "Authorization" => "Bearer $request->access_token"
      ])->get("https://api.allegro.pl/me"); 
      if(!isset($response["error"])) {   
         $user = json_decode($response);
      } else {
         $user = '';
      }

      $data = "";
      $email = 'sebek.kasprzak.kodomat@gmail.com';

      $order = Orders::where('order_id', $request->order_id)->first();
      $offer = Offers::where('offer_id', $order->offer_id)->first();
      $customer = Customer::where('customer_id', $order->customer_id)->first();
      $mail = MailTemplate::where('id', $offer->mail_template)->first();
      $html = $mail->template;

      for ($i = 0; $i < $request->quantity; $i++)
      {
         $code = Code::where('status', 1)->where('seller_id', $order->seller_id)->where('db_id', $offer->codes_id)->first();
         if(Code::where('status', 1)->where('seller_id', $order->seller_id)->where('db_id', $offer->codes_id)->count() < 11)
         {
            NotificationController::last_codes($offer->offer_id, $order->seller_id);
         }
         if(Code::where('status', 1)->where('seller_id', $order->seller_id)->where('db_id', $offer->codes_id)->count() < 1)
         {
            $sentMail = new SentMail();
            $sentMail->customer_id = $order->customer_id;
            $sentMail->order_id = $order->order_id;
            $sentMail->offer_id = $order->offer_id;
            $sentMail->code_id = '';
            $sentMail->resend = 1;
            $sentMail->save();

            NotificationController::empty_code($offer->offer_id, $order->seller_id);

            return response()->json(['message' => 'baza danych pusta'], 200);
         }
         $data .= $code->code." ";
         // $code->status = 0;
         $code->save();
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

      \Mail::send([], [], function ($message) use ($order, $email, $html, $mail, $user, $customer) {
            $message->to('sebek.kasprzak.kodomat@gmail.com')
            ->replyTo($user->email, $user->login)
            ->from($user->email, $user->login)
            ->subject("TEST || $mail->template_subject $order->order_id")
            ->setBody($html, 'text/html');
      });

      // for ($i = 0; $i < $order->quantity; $i++)
      // {
      //    $sentMail = new SentMail();
      //    $sentMail->customer_id = $order->customer_id;
      //    $sentMail->order_id = $order->order_id;
      //    $sentMail->offer_id = $order->offer_id;
      //    $sentMail->code_id = '$code->id';
      //    $sentMail->resend = 0;
      //    $sentMail->save();
      // }
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
