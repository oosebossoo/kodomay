<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\MailTemplate;

class TemplateController extends Controller
{
    public function addTemplate(Request $request)
    {
        if(!isset($request))
        {
            return "podaj następujące parametry: tmp_name, tmp_body, user_id, np. /add_template?tmp_name=test&tmp_body=<p>test<p>&user_id=1";
        }

        $template = new MailTemplate();
        $template->template_name = $request->tmp_name;
        $template->template = $request->tmp_body;
        $template->user_id = $request->user_id;
        $template->save();

        if(MailTemplate::where('template_name', $request->tmp_name)->exists())
        {
            return ['status' => 0, 'desc' => 'you added new template... :)'];
        }
        return ['status' => 1, 'desc' => 'somethink goes wrong... :('];
    }

    public function getTemplates(Request $request)
    {
        if(!isset($request))
        {
            return "podaj następujące parametry: type=list lub type=edit,user_id, np. /get_template?type=edit&user_id=1";
        }
        if(isset($request->type))
        {
            if($request->type == "list")
            {
                return MailTemplate::select('name_template')->where('seller_id', $request->user_id)->get();
            }
            if($request->type == "edit")
            {
                return MailTemplate::select('template')->where('seller_id', $request->user_id)->where('id', $request->template_id)->first();
            }
        }
        else
        {
            return MailTemplate::where('seller_id', $request->user_id)->get();
        }
    }

    public function deleteTemplate(Request $request)
    {
        if(!isset($request))
        {
            return "podaj następujące parametry: id, np. /delete_template?id=1";
        }
        return MailTemplate::where('id', $request->id)->delete();
    }
}
