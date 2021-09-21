<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\Models\MailTemplate;
use App\Models\Offers;

use Validator;
use JWTAuth;

class TemplateController extends Controller
{
    protected $user;
    
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function listTemplates(Request $request)
    {
        if(isset($this->user))
        {
            $user_id = $this->user->id;

            $templates = MailTemplate::where('user_id', $user_id)->get();

            foreach($templates as $template)
            {
                $templates[] = $template;
            }

            return response()->json([
                $templates
            ], 200);
        }

        return response()->json(403);
        
    }

    public function getTemplate(Request $request)
    {
        if(isset($request->id))
        {
            return MailTemplate::where('id', $request->id)->first();
        }
        return [ "message" => "you should set id of template to display that one... ;)" ];
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
        $user_id = $this->user->id;

        if(isset($request->new))
        {
            $validator = Validator::make($request->all(), [
                'template_name' => 'required|unique:mail_template',
            ]);
    
            if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
            }

            $template = new MailTemplate();
            $template->template_name = $request->template_name;
            $template->template_subject = $request->subject;
            $template->replay_email = $request->replay_email;
            $template->template = $request->body;
            $template->user_id = $user_id;
            $template->save();

            return response()->json(['message' => 'added'], 201);
        }

        if(MailTemplate::where('template_name', $request->template_name)->update(["template_name" => $request->template_name, "template_subject" => $request->subject, "template" => $request->body]))
        {
            return response()->json(['message' => 'updated'], 201);
        }

        return response()->json([
            "message" => "please check all parametrs" 
        ], 400);
    }
}
