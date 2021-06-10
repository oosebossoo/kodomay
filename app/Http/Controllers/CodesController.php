<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Response;
use Auth;

use App\Models\Code;

class CodesController extends Controller
{
    public function magreCodesToOffer(Request $request)
    {
        Code::where('db_id', $request->db_id)->update( ['offer_id' => $request->offer_id] );
    }

    public function addCodes(Request $request) 
    {
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
            $db_id = Hash::make($dbName)."".Hash::make($user_id)."".Hash::make($offerId);
            foreach ($request->codes as $code)
            {
                $cddb = new Code();
                $cddb->db_id = $db_id;
                $cddb->db_type = $dbType;
                $cddb->db_name = $dbName;
                $cddb->code = $code;
                $cddb->seller_id = $user_id;
                $cddb->save();
            }
        }
        elseif($request->db_type == 1)
        {
            // baza rek.
            $dbName = $request->db_name;
            $dbType = $request->db_type;
            $offerId = $request->offer_id;
            $db_id = Hash::make($dbName)."".Hash::make($user_id)."".Hash::make($offerId);
            foreach ($request->codes as $code)
            {
                $cddb = new Code();
                $cddb->db_id = $db_id;
                $cddb->db_type = $dbType;
                $cddb->db_name = $dbName;
                $cddb->code = $code;
                $cddb->seller_id = $user_id;
                $cddb->save();
            }
        }
        else
        {
            return ['status' => 'choose type of db... im not a clairvoyant ^-^'];
        }

        return ['status' => 'neeew codes, i like it ^-^'];
    }

    public function getAllCode(Request $request)
    {
        $limit = 100;
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        if(isset($request->limit))
        {
            $limit = $request->limit;
        }

        if(isset($request->db_name))
        {
            return Code::where('seller_id', $user_id)->where('db_name', $request->db_name)->limit($limit)->get();
        }
        if(isset($request->offer_id))
        {
            return Code::where('seller_id', $user_id)->where('offer_id', $request->offer_id)->limit($limit)->get();
        }

        return Code::where('seller_id', $user_id)->limit($limit)->get();
    }

    public static function getSellableCode(Request $request)
    {
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        $result = Code::where('status', 1)->where('user_id', $user_id)->first();
        return response()->json($result);
    }

    public static function getSellableCodes(Request $request)
    {
        $limit = 100;
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        if(isset($request->limit))
        {
            $limit = $request->limit;
        }

        $result = Code::where('status', 1)->where('user_id', $user_id)->limit($limit)->get();
        return response()->json($result);
    }

    public function getSoldCodes(Request $request)
    {
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        if(isset($request->count))
        {
            Code::where('status', 0)->where('user_id', $user_id)->count();
        }

        $result = Code::where('status', 0)->where('user_id', $user_id)->get();
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
