<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\Models\MailTemplate;
use App\Models\Offers;

use JWTAuth;

class TemplateController extends Controller
{
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function getTemplates(Request $request)
    {
        if(isset($request->user_id))
        {
            $user_id = $request->user_id;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        return MailTemplate::where('user_id', $user_id)->get();
    }

    public function getTemplate(Request $request)
    {
        if(isset($request->id))
        {
            return MailTemplate::where('id', $request->id)->first();
        }
        return [ "status" => 1, "desc" => "you should set id of template to display that one... ;)" ];
    }

    public function deleteTemplate(Request $request)
    {
        if(!isset($request->id))
        {
            return "please give me this parametrs: id, np. /delete_template?id=1";
        }
        return MailTemplate::where('id', $request->id)->delete();
    }

    public function saveTemplate(Request $request)
    {
        if(isset($request->user_id))
        {
            $user_id = $request->user_id;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        if (MailTemaplate::where('id', $request->template_id)->exists())
        {
            return MailTemplate::where('id', $request->template_id)->update(["template_name" => $request->name, "template_subject" => $request->subject, "replay_email" => $request->replay_email,"template" => $request->body]);
        }
        else
        {
            $template = new MailTemplate();
            $template->template_name = $request->name;
            $template->template_subject = $request->subject;
            $template->replay_email = $request->replay_email;
            $template->template = $request->body;
            $template->user_id = $user_id;
            $template->save();
        }
        return [ "status" => 1, "desc" => "please check all parametrs" ];
    }
}
