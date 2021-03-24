<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class MailController extends Controller
{
   public function sendEmail(Request $request) 
   {
      $data = array('name'=> $request->customerName, 'code' => $request->code);
    
      Mail::send(['text'=>'mail'], $data, function($message) {
         $message->to($request->mail, $request->customerName)->subject
            ('Order no. '.$request->subject);
         $message->from('answerEmail@gmail.com','answerEmail');
      });
        
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
