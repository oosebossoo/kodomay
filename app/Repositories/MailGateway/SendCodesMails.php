<?php

namespace App\Repositories\MailGateway;

use Mail;

use Illuminate\Support\Facades\Http;

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

class SendCodesMails
{
    static function testMail($request)
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
        $html = "<h1>To tylko test<h1/>";

        $order = Orders::where('order_id', $request->order_id)->first();
        $offer = Offers::where('offer_id', $order->offer_id)->first();
        $customer = Customer::where('customer_id', $order->customer_id)->first();
        // $mail = MailTemplate::where('id', $offer->mail_template)->first();
        // $html = $mail->template;
        // $codes = array();

        // for ($i = 0; $i < $request->quantity; $i++)
        // {
        //    $code = Code::where('status', 1)->where('seller_id', $order->seller_id)->where('db_id', $offer->codes_id)->first();
        //    // if(Code::where('status', 1)->where('seller_id', $order->seller_id)->where('db_id', $offer->codes_id)->count() < 11) {
        //    //    NotificationController::last_codes($offer->offer_id, $order->seller_id);
        //    // }
        //    // if(Code::where('status', 1)->where('seller_id', $order->seller_id)->where('db_id', $offer->codes_id)->count() < 1) {
        //    //    $sentMail = new SentMail();
        //    //    $sentMail->customer_id = $order->customer_id;
        //    //    $sentMail->order_id = $order->order_id;
        //    //    $sentMail->offer_id = $order->offer_id;
        //    //    $sentMail->code_id = '';
        //    //    $sentMail->resend = 1;
        //    //    $sentMail->save();

        //    //    NotificationController::empty_code($offer->offer_id, $order->seller_id);

        //    //    return response()->json(['message' => 'baza danych pusta'], 200);
        //    // }
        //    $data .= $code->code."<br>";
        //    // $code->status = 0;
        //    // $code->save();
        //    array_push($codes, $code->id);
        // }

        // if (strpos($html,'(NAZWA_SPRZEDAJACEGO)') !== false) {
        //    $html = str_replace('(NAZWA_SPRZEDAJACEGO)', $user->login, $html);
        // }

        // if (strpos($html,'(ALLEGRO_LOGIN)') !== false) {
        //    $html = str_replace('(ALLEGRO_LOGIN)', $customer->login, $html);
        // }

        // if (strpos($html,'(KOD)') !== false) {
        //    $html = str_replace('(KOD)', $data, $html);
        // }

        // if (strpos($html,'(EMAIL)') !== false) {
        //    $email = Customer::select('email')->where('customer_id', $order->customer_id)->first();
        //    $html = str_replace('(EMAIL)', $email->email, $html);
        // }

        // if (strpos($html,'(NAZWA_AUKCJI)') !== false) {
        //    $email = Customer::select('email')->where('customer_id', $order->customer_id)->first();
        //    $html = str_replace('(NAZWA_AUKCJI)', $offer->offer_name, $html);
        // }

        // if (strpos($html,'(ILOSC)') !== false) {
        //    $email = Customer::select('email')->where('customer_id', $order->customer_id)->first();
        //    $html = str_replace('(ILOSC)', $order->quantity, $html);
        // }

        // \Mail::send([], [], function ($message) use ($html, $user) {
        //       $message->to($user->email)
        //       ->replyTo('sebek.kasprzak@gmail.com', 'kodomat')
        //       ->from('sebek.kasprzak.kodomat@gmail.com', 'kodomat')
        //       ->subject("TEST")
        //       ->setBody($html, 'text/html');
        // });

        try {
            Mail::send([], [], function ($message) use ($email, $html, $user, $customer) {
                $message->to($customer->email, $customer->login)
                ->replyTo($user->email, $user->login)
                ->from("office@accounts4life.com", $user->login)
                ->subject("TEST || ". '$mail->template_subject'." $order->order_id")
                ->setBody($html, 'text/html');
            });
        }
        catch(\Exception $e) {
            echo $e->getMessage();
            try {
                Mail::send([], [], function ($message) use ($order, $email, $html, $customer) {
                    $message->to($customer->email, $customer->login)
                    ->replyTo($user->email, $user->login)
                    ->from("office@accounts4life.com", $user->login)
                    ->subject("TEST || ". '$mail->template_subject'." $order->order_id")
                    ->setBody($html, 'text/html');
                });
            }
            catch(\Exception $e) {
                echo $e->getMessage();
                Mail::send([], [], function ($message) use ($order, $email, $html, $user, $customer) {
                    $message->to($customer->email, $customer->login)
                    ->replyTo($user->email, $user->login)
                    ->from("office@accounts4life.com", $user->login)
                    ->subject("TEST || ". '$mail->template_subject'." $order->order_id")
                    ->setBody($html, 'text/html');
                });
            }
        }

        if( count(Mail::failures()) > 0 ) {

            echo "There was one or more failures. They were: <br />";
        
            foreach(Mail::failures() as $email_address) {
                echo " - $email_address <br />";
            }
        
        } else {
            echo "No errors, all sent successfully!";
        }

        // foreach($codes as $code)
        // {
        //    $sentMail = new SentMail();
        //    $sentMail->customer_id = $order->customer_id;
        //    $sentMail->order_id = $order->order_id;
        //    $sentMail->offer_id = $order->offer_id;
        //    $sentMail->code_id = $code;
        //    $sentMail->resend = 0;
        //    $sentMail->save();
        // }
    }

    static function sendCode($order_id, $quantity, $access_token)
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

        \Mail::send([], [], function ($message) use ($order, $email, $html, $mail, $user, $customer) {
            $message->to('sebek.kasprzak.kodomat@gmail.com')
            ->replyTo($user->email, $user->login)
            ->from("office@accounts4life.com", $user->login)
            ->subject("$mail->template_subject")
            ->setBody($html, 'text/html');
        });

        // \Mail::send([], [], function ($message) use ($order, $email, $html, $mail, $user, $customer) {
        //     $message->to($customer->email)
        //     ->replyTo($user->email, $user->login)
        //     ->from("office@accounts4life.com", $user->login)
        //     ->subject("$mail->template_subject")
        //     ->setBody($html, 'text/html');
        // });

        // foreach($codes as $code)
        // {
        //     $sentMail = new SentMail();
        //     $sentMail->customer_id = $order->customer_id;
        //     $sentMail->order_id = $order->order_id;
        //     $sentMail->offer_id = $order->offer_id;
        //     $sentMail->code_id = $code;
        //     $sentMail->resend = 0;
        //     $sentMail->save();
        // }
    }
}