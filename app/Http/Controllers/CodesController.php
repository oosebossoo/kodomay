<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use Auth;

use App\Models\Code;

class CodesController extends Controller
{

    public function addCodesFormFile(Request $request) 
    {
        $codes = $request->file('file')->store('file');
        $fp = @fopen("C:/Users/Sebastian/Documents/Praca/allegro/account-module/storage/app/".$codes, 'r'); 
        if ($fp) {
            $array = explode("\n", fread($fp, filesize("C:/Users/Sebastian/Documents/Praca/allegro/account-module/storage/app/".$codes)));
        }

        foreach($array as $line)
        {
            if($this->isExistCode($line)) {
                $exist[] = $line;
            } else {
                $code = new Code();
                $code->code = $line; 
                $code->type = "";
                $code->status = 1;
                $code->user_id = Auth::user()->id;
                $code->save();
            }
        }
        return [
            'codes' => $exist, 
            'delete_status' => $this->deleteFile("C:/Users/Sebastian/Documents/Praca/allegro/account-module/storage/app/".$codes)
        ];
    }

    public function addCodesFormTextBox(Request $request) 
    {
        $codes = "\"".$request->codes."\""; 
        $array = explode ('\n', $codes);    
        foreach($array as $line)
        {
            $char = strpos($line, "\"");
            if($char === false) {
                if($this->isExistCode($line)) {
                    $exist[] = $line;
                } else {
                    $code = new Code();
                    $code->code = $line;
                    $code->type = "";
                    $code->status = 1;
                    $code->user_id = Auth::user()->id;
                    $code->save();
                }
            } else {
                $code = new Code();
                $code->code = str_replace("\"", "", $line);
                $code->type = "";
                $code->status = 1;
                $code->user_id = Auth::user()->id;
                $code->save();
            }
        }
        return $exist;
    }

    public function getCode(Request $request)
    {
        $result = Code::find($request->id);
        return $result;
    }

    public function getAllCode(Request $request)
    {
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }
        $result = Code::where('user_id', $user_id)->get();

        return $result;
    }

    public static function getSellableCode()
    {
        $result = Code::where('status', 1)->where('user_id', Auth::user()->id)->first();
        return response()->json($result);
    }

    public function getSoldCodes(Request $request)
    {
        $result = Code::where('status', 0)->where('user_id', Auth::user()->id)->get();
        return response()->json($result);
    }

    public static function changeStatusOfCode($id)
    {
        $code = Code::where('id', $id)->get();

        if($code[0]->status == 1)
        {
            Code::where('id', $id)->update(['status' => 0]);
        }
        return response()->json(Code::where('id', $id)->get());
    }

    public function isExistCode($code)
    {
        $result = (Code::select('id', 'code')->where('code', $code)->get());
        
        if(count($result) == 0) return false;
        return true;
    }

    public function deleteFile($file)
    { 
        if (!unlink($file)) {  
            return [
                'msg' => 'File cannot be deleted due to an error',
                'path' => $file
            ];  
        }  
    }
}
