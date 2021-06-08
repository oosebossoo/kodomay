<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CodesController;
use App\Models\Code;

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

   public function sendEmail(Request $request) 
   {
      if(isset($request->order_id))
      {
         $order = Orders::where('order_id', $request->order_id)->first();
         $code = Code::where('id', $order->code_id);
      }
      Orders::where('order_id', $request->order_id)->first();
      $data = array('name'=> $request->customerName, 'code' => $request->code);
    
      Mail::send(['text'=>'mail'], $data, function($message) {
         $message->to("vyjpq3e2u1+1ee2043b8@user.allegrogroup.pl", "Sebastian")->subject
            ('Order no. 1234567890');
         $message->from('noreplay@kodo.mat','Kodomat');
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
