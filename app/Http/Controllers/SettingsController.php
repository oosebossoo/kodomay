<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;

use JWTAuth;

use App\Models\PersonalData;

class SettingsController extends Controller
{
    protected $user;
 
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function getNotification(Request $request)
    {
        return Notification::where('user_id', $this->user->id)->first();
    }

    public function saveNotifications(Request $request)
    {
        if(Notification::where('user_id', $this->user->id)->exists())
        {
            $data = Notification::where('user_id', $this->user->id)->first();

            if(isset($request->send_email_copy_code))
            {
                $data->send_email_copy_code = $request->send_email_copy_code;
            }
            if(isset($request->send_info_new_adv))
            {
                $data->send_info_new_adv = $request->send_info_new_adv;
            }
            if(isset($request->send_info_end_of_credit))
            {
                $data->send_info_end_of_credit = $request->send_info_end_of_credit;
            }
            if(isset($request->send_info_zero_credit))
            {
                $data->send_info_zero_credit = $request->adrsend_info_zero_creditess;
            }
            if(isset($request->send_info_end_of_code))
            {
                $data->send_info_end_of_code = $request->send_info_end_of_code;
            }

            $data->save();
        }
        else
        {
            $data = new Notification();
            $data->user_id = $this->user->id;
            $data->send_email_copy_code = $request->send_email_copy_code;
            $data->send_info_new_adv = $request->send_info_new_adv;
            $data->send_info_end_of_credit = $request->send_info_end_of_credit;
            $data->send_info_zero_credit = $request->send_info_zero_credit;
            $data->send_info_end_of_code = $request->send_info_end_of_code;
            $data->save();
        }
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
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['message' => 'Login i/lub hasło są nieprawidłowe'], 401);
        }

        $user = User::where('email', $this->user->email)->update(['new_password' => bcrypt($request->password)]);

        return "test";
    }

    public function setSessionTime()
    {

    }
}
