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

    public function list(Request $request)
    {
        if(isset($this->user))
        {
            $user_id = $this->user->id;

            $templates = MailTemplate::where('user_id', $user_id)->get();

            foreach($templates as $template)
            {
                $res[] = [
                    'id' => $template->id, 
                    'template_name' => $template->template_name
                ];
            }

            return response()->json($res, 200);
        }

        return response()->json(403);
    }

    public function get(Request $request)
    {
        if(isset($request->id))
        {
            $template = MailTemplate::where('id', $request->id)->first();
            $res = [
                'id' => $template->id, 
                'template_name' => $template->template_name,
                'subject' =>$template->template_subject,
                'body' => $template->template,
                'email' => $template->replay_email
            ];

            return response()->json($res, 200);
        }
        return response()->json([ 
            "message" => "Template id is null" 
        ], 200);
    }

    public function delete(Request $request)
    {
        if(!isset($request->id))
        {
            return response()->json([
                "message" => "Template id is null"
            ], 400);
        }
        if(MailTemplate::where('id', $request->id)->delete())
        {
            return response()->json([
                "message" => "Template deleted"
            ], 200);
        }
        else
        {
            return response()->json([
                "message" => "Can't delete"
            ], 500);
        }
    }

    public function save(Request $request)
    {
        $user_id = $this->user->id;

        if(isset($request->new))
        {
            $validator = Validator::make($request->all(), [
                'template_name' => 'required|unique:mail_template',
                'body' => 'required',
            ]);
    
            if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
            }

            $template = new MailTemplate();
            $template->template_name = $request->template_name;
            $template->template_subject = $request->subject;
            $template->replay_email = $request->email;
            $template->template = $request->body;
            $template->user_id = $user_id;
            $template->save();

            return response()->json([
                'message' => 'Template added'
            ], 201);
        }
        else
        {
            if(MailTemplate::where('template_name', $request->template_name)
                ->update([
                    "template_name" => $request->template_name, 
                    "template_subject" => $request->subject, 
                    "template" => $request->body,
                ]))
            {
                return response()->json([
                    'message' => 'updated'
                ], 201);
            }
        }

        return response()->json([
            "message" => "please check all parametrs" 
        ], 400);
    }
}
