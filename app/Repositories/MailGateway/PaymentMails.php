<?php

namespace App\Repositories\MailGateway;

use Mail;

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\MailController;

class PaymentMails
{
    static function paymentConfirm($data)
    {
        $user = $data['user'];

        \Mail::send([], [], function ($message) use ($order, $email, $html, $user, $customer) {
            $message->to($customer->email, $customer->login)
            ->replyTo("office@accounts4life.com", "Cybersent")
            ->from("office@accounts4life.com", "Cybersent")
            ->subject("Payment Confirmation - $order->order_id")
            ->setBody($html, 'text/html');
         });
    }

    static function paymentBegin($data)
    {

    }
}


// $data = [
//     'user' => [
//         'email' => 'email@com.com', (string)
//         'login' => 'login', (string)
//     ],
//     'orderId' => '123qwe456RTY', (string)
//     'amount' => 20.99, (int)
//     'quantity' => 100 (int)
// ];