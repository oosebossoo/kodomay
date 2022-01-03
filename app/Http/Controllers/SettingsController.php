<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;

use JWTAuth;

use App\Models\Notification;
use App\Models\PersonalData;

class SettingsController extends Controller
{
    protected $user;
 
    public function __construct()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (TokenInvalidException $e) {
            header("Location: /unauthorized"); 
            dd('token_invalid');
        } catch (TokenExpiredException $e) {
            header("Location: /unauthorized"); 
            dd('token_expired');
        } catch (JWTException $e) {
            header("Location: /unauthorized"); 
            dd('token_invalid ws');
        }
    }

    public function getNotifications(Request $request)
    {
        $not = Notification::where('user_id', $this->user->id)->first();

        return response()->json([
            'copy_email' => boolval($not->copy_email) ? true : false,
            'email' => $not->email,
            'end_of_credit' => boolval($not->end_of_credit) ? true : false,
            'empty_credit' => boolval($not->empty_credit) ? true : false,
            'empty_dbs' => boolval($not->empty_dbs) ? true : false,
        ], 200);
    }

    public function saveNotifications(Request $request)
    {
        if(Notification::where('user_id', $this->user->id)->exists())
        {
            $data = Notification::where('user_id', $this->user->id)->first();

            if(isset($request->copy_email))
            {
                $data->copy_email = $request->copy_email;
            }
            if(isset($request->email))
            {
                $data->email = $request->email;
            }
            if(isset($request->new_adv))
            {
                $data->new_adv = $request->new_adv;
            }
            if(isset($request->end_of_credit))
            {
                $data->end_of_credit = $request->end_of_credit;
            }
            if(isset($request->empty_credit))
            {
                $data->empty_credit = $request->empty_credit;
            }
            if(isset($request->empty_dbs))
            {
                $data->empty_dbs = $request->empty_dbs;
            }

            $data->save();
        }
        else
        {
            $data = new Notification();
            $data->user_id = $this->user->id;
            $data->copy_email = $request->copy_email;
            $data->email = $request->email;
            $data->new_complaint = $request->new_complaint;
            $data->end_of_credit = $request->end_of_credit;
            $data->empty_credit = $request->empty_credit;
            $data->empty_dbs = $request->empty_dbs;
            $data->save();
        }

        return response()->json([], 200);
    }

    public function getPersonalData(Request $request)
    {
        $data = PersonalData::where('user_id', $this->user->id)->first(); 

        $res = [
            'accountType' => $data->type,
            'firstLastName' => $data->full_name,
            'companyName' => $data->full_office_name,
            'address' => $data->adress,
            'zipCode' => $data->post_code,
            'city' => $data->city,
            'NIP' => $data->NIP,
            'phoneNumber' => $data->phone_number,
            'country' => $data->country
        ];
        return response()->json($res);
    }

    public function getDataEmail(Request $request)
    {
        return response()->json($this->user->email);
    }
    
    public function savePersonalData(Request $request)
    {
        
        if(PersonalData::where('user_id', $this->user->id)->exists())
        {
            $data = PersonalData::where('user_id', $this->user->id)->first();

            if(isset($request->accountType))
            {
                $data->type = $request->accountType;
            }
            if(isset($request->firstLastName))
            {
                $data->full_name = $request->firstLastName;
            }
            if(isset($request->companyName))
            {
                $data->full_office_name = $request->companyName;
            }
            if(isset($request->address))
            {
                $data->adress = $request->address;
            }
            if(isset($request->zipCode))
            {
                $data->post_code = $request->zipCode;
            }
            if(isset($request->city))
            {
                $data->city = $request->city;
            }
            if(isset($request->NIP))
            {
                $data->NIP = $request->NIP;
            }
            if(isset($request->phoneNumber))
            {
                $data->phone_number = $request->phoneNumber;
            }
            if(isset($request->country))
            {
                $data->country = $request->country;
            }

            if($request->type == "private")
            {
                $data->full_office_name = "";
                $data->NIP = "";
            }
            $data->save();
        }
        else
        {
            $data = new PersonalData();
            if($request->accountType == "private")
            {
                $data->user_id = $this->user->id;
                $data->type = $request->accountType;
                $data->full_name = $request->firstLastName;
                $data->full_office_name = "";
                $data->adress = $request->address;
                $data->post_code = $request->zipCode;
                $data->city = $request->city;
                $data->NIP = "";
                $data->phone_number = $request->phoneNumber;
                $data->country = $request->country;
            }
            if($request->accountType == "company")
            {
                $data->user_id = $this->user->id;
                $data->type = $request->accountType;
                $data->full_name = $request->firstLastName;
                $data->full_office_name = $request->companyName;
                $data->adress = $request->address;
                $data->post_code = $request->zipCode;
                $data->city = $request->city;
                $data->NIP = $request->NIP;
                $data->phone_number = $request->phoneNumber;
                $data->country = $request->country;
            }
            if($data->save())
            {
                return response()->json(['message' => 'Data saved']);
            } else {
                return response()->json(['message' => "Can't save"]);
            }
        }
    }

    public function setPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'new_password' => [
                'required', 
                'min:6',              // musi zawierać co najmniej 6 znaków
                'regex:/[a-z]/',      // musi zawierać jedną małą litere
                'regex:/[A-Z]/',      // musi zawierać jedną dużą litere
                'regex:/[0-9]/',      // musi zawierać jedną cyfre
                'confirmed',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['message' => 'Stare hasło jest nieprawidłowe'], 401);
        }

        $user = User::where('email', $this->user->email)->update(['password' => bcrypt($request->new_password)]);

        return response()->json(['message' => 'Hasło zmienione'], 200);
    }
}
