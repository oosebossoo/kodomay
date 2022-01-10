<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;


use Response;
use JWTAuth;
use Exception;
use App\Models\Code;
use App\Models\SentMail;

class CodesController extends Controller
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
            //echo('token_expired');
            die;
        } catch (JWTException $e) {
            header("Location: /unauthorized"); 
            die;
        }
    }
    
    public function shortList(Request $request)
    {
        $codes = $this->user->codes()->get();
        $dbsUnique = $codes->unique('db_id');

        foreach ($dbsUnique as $dbUnique)
        {
            if(!isset($sold[$dbUnique->db_id])) {
                $sold[$dbUnique->db_id] = 0;
            }

            if(!isset($available[$dbUnique->db_id])) {
                $available[$dbUnique->db_id] = 0;
            }

            $created_at = strtok($dbUnique->created_at, 'T');

            if($dbUnique->db_type == 0) {
                $db_type = "Zwykła";
            } else {
                $db_type = "Rekurencyjna";
            }

            $response[] = [ 
                'id' => $dbUnique->db_id, 
                'name' => $dbUnique->db_name, 
                'db_type' => $db_type,
            ];
        }

        if(isset($response)) {
            return response()->json($response);
        } else {
            return response()->json([
                'message' => 'No data in database',
            ], 200);
        }
    }

    public function list(Request $request)
    {
        $codes = $this->user->codes()->get();
        $dbsUnique = $codes->unique('db_id');

        foreach ($codes as $code) 
        {
            if(isset($ids[$code->db_id])) {
                $ids[$code->db_id] = $ids[$code->db_id] + 1;

                if(isset($sold[$code->db_id])) {
                    if($code->status == 0) {
                        $sold[$code->db_id] ++;
                    } else {
                        $available[$code->db_id] ++;
                    }
                } else {
                    if($code->status == 0) {
                        $sold[$code->db_id] = 1;
                    } else {
                        $available[$code->db_id] = 1;
                    }
                }
            } else {
                $ids[$code->db_id] = 1;

                if(isset($sold[$code->db_id])) {
                    if($code->status == 0) {
                        $sold[$code->db_id] ++;
                    } else {
                        $available[$code->db_id] ++;
                    }
                } else {
                    if($code->status == 0) {
                        $sold[$code->db_id] = 1;
                    } else {
                        $available[$code->db_id] = 1;
                    }
                }
            }
        }
        foreach ($dbsUnique as $dbUnique)
        {
            if(!isset($sold[$dbUnique->db_id])) {
                $sold[$dbUnique->db_id] = 0;
            }

            if(!isset($available[$dbUnique->db_id])) {
                $available[$dbUnique->db_id] = 0;
            }
            // $dbUnique->created_at = substr($dbUnique->created_at, 0, strpos($dbUnique->created_at, "T"));
            // $created_at = explode('T',$dbUnique->created_at);

            $created_at = strtok($dbUnique->created_at, 'T');

            if($dbUnique->db_type == 0) {
                $db_type = "Zwykła";
            } else {
                $db_type = "Rekurencyjna";
            }

            $response[] = [ 
                'id' => $dbUnique->db_id, 
                'name' => $dbUnique->db_name, 
                'date' => $created_at,
                'quantity' => $ids[$dbUnique->db_id],
                'available' => $available[$dbUnique->db_id],
                'sold' => $sold[$dbUnique->db_id],
                'db_type' => $db_type,
            ];
        }

        if(isset($response)) {
            return response()->json($response);
        } else {
            return response()->json([], 200);
        }
    }

    public function add_db(Request $request) 
    {
        dd(['codes_txt' => $request->codes_txt, 'codes' => $request->codes]);
        
        $user_id = $this->user->id;

        $validator = Validator::make($request->all(), [
            'db_name' => 'required|unique:code',
            'db_type' => 'required',
            'codes' => 'required', //_without_all:codes_txt
            // 'codes_txt' => 'required_without_all:codes',
        ]);
        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $codes = $request->codes;

        if($request->db_type == 0) {
            // baza zwykła
            $dbName = $request->db_name;
            $dbType = $request->db_type;
            $offerId = $request->offer_id;
            $db_id = Hash::make($dbName)."".Hash::make($user_id)."".Hash::make($offerId);
            foreach ($codes as $code)
            {
                $cddb = new Code();
                $cddb->db_id = $db_id;
                $cddb->db_type = $dbType;
                $cddb->db_name = $dbName;
                $cddb->code = $code;
                $cddb->seller_id = $user_id;
                $cddb->status = 1;
                $cddb->save();
            }
        } elseif ($request->db_type == 1) {
            // baza rek.
            $dbName = $request->db_name;
            $dbType = $request->db_type;
            $offerId = $request->offer_id;
            $db_id = Hash::make($dbName)."".Hash::make($user_id)."".Hash::make($offerId);
            foreach ($codes as $code)
            {
                $cddb = new Code();
                $cddb->db_id = $db_id;
                $cddb->db_type = $dbType;
                $cddb->db_name = $dbName;
                $cddb->code = $code;
                $cddb->seller_id = $user_id;
                $cddb->status = 1;
                $cddb->save();
            }
        } else {
            return response()->json([
                'message' => 'choose type of db... im not a clairvoyant ^-^'
            ], 200);
        }

        return response()->json([
            'message' => 'yhy, neeew codes, i like it ^-^'
        ], 201);
    }

    public function delete_db(Request $request)
    {
        $user_id = $this->user->id;

        if(Code::where('db_id', $request->db_id)->delete()) {
            return response()->json(['status' => 'database deleted'], 200);
        }

        return response()->json(['status' => 'no codes database in database'], 200);
    }

    public function add_code(Request $request) 
    {
        if(null !== $request->db_id || null !== $request->code) {
            $user_id = $this->user->id;

            $db = Code::where('db_id', $request->db_id)->first();
            $dbName = $db->db_name;
            $dbType = $db->db_type;
            $offerId = $db->offer_id;
            $db_id = $request->db_id;

            if($dbType == 0) {
                // baza zwykła
                $cddb = new Code();
                $cddb->db_id = $db_id;
                $cddb->db_type = $dbType;
                $cddb->db_name = $dbName;
                $cddb->code = $request->code;
                $cddb->seller_id = $user_id;
                $cddb->status = 1;
                $cddb->save();
            } elseif($db_type == 1) {
                // baza rek
                $cddb = new Code();
                $cddb->db_id = $db_id;
                $cddb->db_type = $dbType;
                $cddb->db_name = $dbName;
                $cddb->code = $request->code;
                $cddb->seller_id = $user_id;
                $cddb->status = 1;
                $cddb->save();
            } else {
                return response()->json([
                    'message' => 'something goes wrong',
                    'db_id' => $request->db_id
                ], 200);
            }
            return response()->json([
                'message' => 'new key added'
            ], 201);
        }
        return response()->json([
            'message' => 'db_id = null'
        ], 400);
    }

    public function unused(Request $request)
    {
        $codes = Code::where('db_id', $request->db_id)->where('status', 1)->get();

        if(!$codes->isEmpty()) {
            foreach ($codes as $code) 
            {
                $res[] = [
                    'id' => $code->id,
                    'key' => $code->code,
                ];
            }
            return response()->json($res, 200);
        }
        return response()->json([], 200);
    }

    public function used(Request $request)
    {
        $codes = Code::where('db_id', $request->db_id)->where('status', 0)->get();

        if(!$codes->isEmpty()) {
            foreach ($codes as $code) 
            {
                $res[] = [
                    'key' => $code->code,
                ];
            }
            return response()->json($res, 200);
        }
        return response()->json([], 200);
    }

    public function delete_codes(Request $request)
    {
        $user_id = $this->user->id;
        //dd($request->code_ids);

        if(null !== $request->code_ids) {
            // foreach($request->code_ids as $id)
            // {
                if(!Code::where('seller_id', $user_id)->where('id', $request->code_ids)->delete()) {
                    return response()->json(['message' => "Can't delete code from database"], 500);
                } else {
                    return response()->json(['message' => "Codes deleted from database"], 200);
                }
            // }
        }

        return response()->json(['message' => 'wrong values'], 400);
    }

    public function info(Request $request)
    {
        $user_id = $this->user->id;
        $db = Code::where('seller_id', $user_id)->where('db_id', $request->db_id)->first();

        if($db->db_type == 0) {
            $res = [
                'db_id' => $db->db_id,
                'db_name' => $db->db_name,
                'db_type' => "Zwykła",
            ];
        } else {
            $res = [
                // 'db_id' => $db->db_id,
                'db_name' => $db->db_name,
                'db_type' => "Rekurencyjna",
            ];
        }
        return response()->json($res, 200);
    }

    public function find(Request $request)
    {
        $user_id = $this->user->id;
        $db = Code::where('seller_id', $user_id)->where('code', $request->code)->first();

        $res = [
            // 'db_id' => $db->db_id,
            'db_name' => $db->db_name,
        ];

        return response()->json($res, 200);
    }


    public function getCodesFromOrder(Request $request)
    {
        $codes_id = SentMail::select('code_id')->where('order_id', $request->orderId)->where('customer_id', $request->customerId)->get();
        foreach ($codes_id as $code_id)
        {   
            $code = Code::where('id', $code_id->code_id)->first();
            $codes[] = $code->code;
        }
        return $codes;
    }

    public function listOfCodesFromDB(Request $request)
    {
        $user_id = $this->user->id;

        if(null !== $request->limit && null !== $request->db_id) {
            if(null !== $request->id) {
                return response()->json(Code::where('seller_id', $user_id)
                    ->where('db_id', $request->db_id)
                    ->limit($limit)
                    ->get(), 200);
            } else {
                return response()->json(Code::where('seller_id', $user_id)
                    ->where('db_id', $request->db_id)
                    ->where('id', '>', $request->code_id)
                    ->limit($limit)
                    ->get(), 200);
            }
        }
        return response()->json(['message' => 'wrong values'], 400);
    }

    public static function changeStatusOfCode($id)
    {
        $code = Code::where('id', $id)->get();

        if($code[0]->status == 1) {
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
}
