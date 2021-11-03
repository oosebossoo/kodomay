<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;

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
        return response()->json(PersonalData::where('user_id', $this->user->id)->first());
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
            if($request->type == "private")
            {
                dd($this->user->id);
                $data->user_id = $this->user->id;
                $data->type = $request->accountType;
                $data->full_name = $request->firstLastName;
                $data->full_office_name = "";
                $data->adress = $request->adress;
                $data->post_code = $request->zipCode;
                $data->city = $request->city;
                $data->NIP = "";
                $data->phone_number = $request->phoneNumber;
                $data->country = $request->country;
            }
            if($request->type == "company")
            {
                $data->user_id = $this->user->id;
                $data->type = $request->accountType;
                $data->full_name = $request->firstLastName;
                $data->full_office_name = $request->companyName;
                $data->adress = $request->adress;
                $data->post_code = $request->zipCode;
                $data->city = $request->city;
                $data->NIP = $request->NIP;
                $data->phone_number = $request->phoneNumber;
                $data->country = $request->country;
            }
            $data->save();
        }
        
    }

    public function setSessionTime()
    {

    }
}
