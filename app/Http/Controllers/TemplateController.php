<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\MailTemplate;

class TemplateController extends Controller
{
    public function addTemplate(Request $request)
    {
        // tmp_name= tmp_body= user_id=
        $template = new MailTemplate();
        $template->template_name = $request->tmp_name;
        $template->template = $request->tmp_body;
        $template->user_id = $request->user_id;
        $template->save();
    }
}
