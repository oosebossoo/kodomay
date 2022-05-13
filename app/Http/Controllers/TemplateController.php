<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\Models\MailTemplate;
use App\Models\Offers;

use Validator;
use Auth;
use JWTAuth;

class TemplateController extends Controller
{
    protected $user;
    
    public function __construct()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (TokenInvalidException $e) {
            header("Location: /unauthorized"); 
            die;
        } catch (TokenExpiredException $e) {
            header("Location: /unauthorized"); 
            die;
        } catch (JWTException $e) {
            header("Location: /unauthorized"); 
            die;
        }
    }

    public function list(Request $request)
    {
        $user_id = $this->user->id;

        $templates = MailTemplate::where('user_id', $user_id)->get();

        foreach($templates as $template)
        {
            $res[] = [
                'id' => $template->id, 
                'template_name' => $template->template_name,
            ];
        }
        if(isset($res)) {
            return response()->json($res, 200);
        }
        return response()->json([], 200);
    }

    public function get(Request $request)
    {
        if(isset($request->id)) {
            $template = MailTemplate::where('id', $request->id)->first();
            $res = [
                'id' => $template->id, 
                'template_name' => $template->template_name,
                'subject' =>$template->template_subject,
                'body' => $template->template,
                'email' => $template->replay_email,
            ];
            return response()->json($res, 200);
        }
        return response()->json([ 
            "message" => "Template id is null" 
        ], 200);
    }

    public function delete(Request $request)
    {
        if(!isset($request->id)) {
            return response()->json([
                "message" => "Template id is null"
            ], 400);
        }
        if(MailTemplate::where('id', $request->id)->delete()) {
            return response()->json([
                "message" => "Template deleted"
            ], 200);
        } else {
            return response()->json([
                "message" => "Can't delete"
            ], 500);
        }
    }

    public function save(Request $request)
    {
        $user_id = $this->user->id;

        if(isset($request->new)) {
            $validator = Validator::make($request->all(), [
                'template_name' => 'required|unique:mail_template,template_name,NULL,id,user_id,'.$user_id,
                'template_subject' => 'required',
                'replay_email' => 'required',
                'template' => 'required',
            ]);
    
            if($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $template = new MailTemplate();
            $template->template_name = $request->template_name;
            $template->template_subject = $request->template_subject;
            $template->replay_email = $request->replay_email;
            $template->template = $request->template;
            $template->user_id = $user_id;
            $template->save();

            return response()->json([
                'message' => 'Template added'
            ], 201);
        } else {
            if(MailTemplate::where('id', $request->id)
                ->update([
                    "template_name" => $request->template_name, 
                    "template_subject" => $request->subject, 
                    "template" => $request->body,
                    "replay_email" => $request->email
                ])) {
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
