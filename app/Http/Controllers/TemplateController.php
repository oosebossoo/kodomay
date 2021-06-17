<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\MailTemplate;

class TemplateController extends Controller
{
    public function addTemplate(Request $request)
    {
        if(!isset($request->tmp_name) || !isset($request->tmp_body) || !isset($request->user_id))
        {
            return "please give me this parametrs: tmp_name, tmp_body, user_id";
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
        if(isset($request->type))
        {
            if($request->type == "list")
            {
                return MailTemplate::select('id', 'template_name')->where('user_id', $request->user_id)->get();
            }
            elseif($request->type == "edit")
            {
                return MailTemplate::select('id', 'template')->where('user_id', $request->user_id)->where('id', $request->template_id)->first();
            }
            else
            {
                return "please give me this parametrs: for type=list, user_id or for type=edit, user_id ,template_id, np. /get_template?type=edit&user_id=1&template_id=2";
            }
        }
        else
        {
            return MailTemplate::where('user_id', $request->user_id)->get();
        }
    }

    public function deleteTemplate(Request $request)
    {
        if(!isset($request->id))
        {
            return "please give me this parametrs: id, np. /delete_template?id=1";
        }
        return MailTemplate::where('id', $request->id)->delete();
    }

    public function editTemplate(Request $request)
    {
        if(!isset($request->template_id))
        {
            return "please give me this parametrs: template_id, template";
        }
        return MailTemplate::where('id', $request->template_id)->update(["template" => $request->template]);
    }
}
