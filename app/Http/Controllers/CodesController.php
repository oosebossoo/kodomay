<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Response;
use Auth;

use App\Models\Code;

class CodesController extends Controller
{
    public function addCodes(Request $request) 
    {
        // dd($request->db_type[0]);
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        if($request->db_type == 0)
        {
            // baza zwykła
            $dbName = $request->db_name;
            $dbType = $request->db_type;
            $offerId = $request->offer_id;
            foreach ($request->codes as $code)
            {
                // dd(Hash::make($dbName)."".Hash::make($user_id)."".Hash::make($offerId));
                $cddb = new Code();
                $cddb->db_id = Hash::make($dbName)."".Hash::make($user_id)."".Hash::make($offerId);
                $cddb->db_type = $dbType;
                $cddb->db_name = $dbName;
                $cddb->code = $code;
                $cddb->seller_id = $user_id;
                $cddb->offer_id = $offerId;
                $cddb->save();
            }
        }
        elseif($request->db_type == 1)
        {
            // baza rek.
            $dbName = $request->db_name;
            $dbType = $request->db_type;
            $offerId = $request->offer_id;
            foreach ($request->codes as $code)
            {
                // dd(Hash::make($dbName)."".Hash::make($user_id)."".Hash::make($offerId));
                $cddb = new Code();
                $cddb->db_id = Hash::make($dbName)."".Hash::make($user_id)."".Hash::make($offerId);
                $cddb->db_type = $dbType;
                $cddb->db_name = $dbName;
                $cddb->code = $code;
                $cddb->seller_id = $user_id;
                $cddb->offer_id = $offerId;
                $cddb->save();
            }
        }
        else
        {
            return ['status' => 'choose type of db... im not a clairvoyant ^-^'];
        }

        return ['status' => 'neeew codes, i like it ^-^'];
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

    public static function getSellableCodes()
    {
        $result = Code::where('status', 1)->where('user_id', Auth::user()->id)->get();
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
