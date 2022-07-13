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
use App\Models\Offers;
use App\Models\Orders;
use App\Models\SentMail;
use App\Models\UserData;

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

        foreach ($dbsUnique as $dbUnique)
        {
            $created_at = strtok($dbUnique->created_at, 'T');

            if($dbUnique->db_type == 0) {
                $db_type = "Zwykła";
            } else {
                $db_type = "Rekurencyjna";
            }

            $quantity = Code::where("db_id", $dbUnique->db_id)->count();
            if(Code::where("db_id", $dbUnique->db_id)->where("status", 1)->count() == 0) {
                $offers = Offers::where('codes_id', $dbUnique->db_id)->get();
                $available = 0;
                if($offers == null) {
                    $available = 0;
                } else {
                    foreach ($offers as $offer)
                    {
                        $available = $available - SentMail::where('offer_id', $offer->offer_id)->where('resend', 1)->count();
                    }
                }
                
            } else {
                $available = Code::where("db_id", $dbUnique->db_id)->where("status", 1)->count();
            }
            $sold = Code::where("db_id", $dbUnique->db_id)->where("status", 0)->count();

            $response[] = [ 
                'id' => $dbUnique->db_id, 
                'name' => $dbUnique->db_name, 
                'date' => $created_at,
                'quantity' => $quantity,
                'available' => $available,
                'sold' => $sold,
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
        $user_id = $this->user->id;

        $validator = Validator::make($request->all(), [
            'db_name' => 'required|unique:code',
            'db_type' => 'required',
            'codes' => 'required_without_all:codes_txt', //_without_all:codes_txt
            'codes_txt' => 'required_without_all:codes',
        ]);
        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        if($request->has('codes'))
        {
            $codes = $request->codes;
        } else {
            $codes = $request->codes_txt;
        }

        foreach($codes as $code)
        {
            $parts = preg_split('/\s+/', $code);
            $result = "";
            foreach ($parts as $part)
            {
                $result = sprintf("%s %s", $result, $part);
            }
            $pregCodes[] = $result;
        }

        $codes_unique = array_unique($pregCodes);
        $dupes = array_diff_key( $codes, $codes_unique );
        $count_dupes = array_count_values($dupes);
        if(count($count_dupes) > 0)
        {
            return response()->json([
                "error" => "duplicates", 
                "desc" => $count_dupes
            ], 400);
        }

        

        if($request->db_type == 0) {
            // baza zwykła
            $dbName = $request->db_name;
            $dbType = $request->db_type;
            $offerId = $request->offer_id;
            $db_id = Hash::make($dbName)."".Hash::make($user_id)."".Hash::make($offerId);
            $db_id = Hash::make($db_id);
            $db_id = substr($db_id, -8);
            $db_id = str_replace('/', 1, $db_id);
            foreach ($pregCodes as $code)
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
            $db_id = Hash::make($db_id);
            $db_id = substr($db_id, -8);
            foreach ($pregCodes as $code)
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
        if(null !== $request->code) {
            $user_id = $this->user->id;

            $codes = Code::where('db_id', $request->db_id)->get();
            
            foreach($codes as $code)
            {
                $pregCode = '';
                $parts = preg_split('/\s+/', $code->code);
                foreach ($parts as $part)
                {
                    $pregCode .= $part.'   ';
                }

                if(isset($pregCode))
                {
                    foreach($request->code as $rcode)
                    {
                        if($pregCode == $rcode) {
                            return response()->json([
                                "error" => "duplicat",
                                "code" => $rcode
                            ], 400);
                        }
                    }
                } else {
                    foreach($request->code as $rcode)
                    {
                        if($code->code == $rcode) {
                            return response()->json([
                                "error" => "duplicat",
                                "code" => $rcode
                            ], 400);
                        }
                    }
                }
            }

            $db = Code::where('db_id', $request->db_id)->first();
            $dbName = $db->db_name;
            $dbType = $db->db_type;
            $db_id = $request->db_id;

            foreach($request->code as $code)
            {
                if($dbType == 0) {
                    // baza zwykła
                    $cddb = new Code();
                    $cddb->db_id = $db_id;
                    $cddb->db_type = $dbType;
                    $cddb->db_name = $dbName;
                    $cddb->code = $code;
                    $cddb->seller_id = $user_id;
                    $cddb->status = 1;
                    $cddb->save();
                } elseif($db_type == 1) {
                    // baza rek
                    $cddb = new Code();
                    $cddb->db_id = $db_id;
                    $cddb->db_type = $dbType;
                    $cddb->db_name = $dbName;
                    $cddb->code = $code;
                    $cddb->seller_id = $user_id;
                    $cddb->status = 1;
                    $cddb->save();
                } else {
                    return response()->json([
                        'message' => 'something goes wrong',
                        'db_id' => $request->db_id
                    ], 200);
                }
            }

            $oldOrders = SentMail::where('resend', 0)->orderBy('id','asc')->get();

            if($oldOrders != [])
            {
                foreach ($oldOrders as $oldOrder)
                {
                    if(Offers::where('offer_id', $oldOrder->offer_id)->where('codes_id', $db_id)->exists() && Code::where('db_id', $db_id)->where('status', 1)->exists())
                    {
                        $order = Orders::where('order_id', $oldOrder->order_id)->first();
                        $code = Code::where('db_id', Offers::where('offer_id', $oldOrder->offer_id)->first()['codes_id'])->where('status', 1)->first();
                        MailController::sendOldMail($oldOrder->order_id, $oldOrder->id, $code, UserData::where('id', $order->allegro_user_id)->first()['access_token']);
                    }
                }
            }

            return response()->json([
                'message' => 'new key added'
            ], 201);
        }
        return response()->json([
            'message' => 'db_id = null'
        ], 400);
    }

    static function unused($db_id)
    {
        $codes = Code::where('db_id', $db_id)->where('status', 1)->get();

        if(!$codes->isEmpty()) {
            foreach ($codes as $code) 
            {
                $res[] = [
                    'id' => $code->id,
                    'key' => $code->code,
                ];
            }
            return $res;
        }
        return [];
    }

    static function used($db_id)
    {
        $codes = Code::where('db_id', $db_id)->where('status', 0)->get();

        if(!$codes->isEmpty()) {
            foreach ($codes as $code) 
            {
                $res[] = [
                    'id' => $code->id,
                    'key' => $code->code,
                ];
            }
            return $res;
        }
        return [];
    }

    public function delete_codes(Request $request)
    {
        $user_id = $this->user->id;

        if(null !== $request->code_id) {
            if(!Code::where('seller_id', $user_id)->where('id', $request->code_id)->delete()) {
                return response()->json(['message' => "Can't delete code from database"], 500);
            } else {
                return response()->json(['message' => "Codes deleted from database"], 200);
            }
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
                'unused' => self::unused($db->db_id),
                'used' => self::used($db->db_id)
            ];
        } else {
            $res = [
                'db_id' => $db->db_id,
                'db_name' => $db->db_name,
                'db_type' => "Rekurencyjna",
                'unused' => self::unused($db->db_id),
                'used' => self::used($db->db_id)
            ];
        }
        return response()->json($res, 200);
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'db_name' => 'required',
        ]);
        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user_id = $this->user->id;

        if(!Code::where('seller_id', $user_id)->where('db_id', $request->db_id)->update(["db_name" => $request->db_name]))
        {
            return response()->json("error", 500);
        }
        return response()->json([], 200);
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
