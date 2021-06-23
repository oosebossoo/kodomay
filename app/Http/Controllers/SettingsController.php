<?php

namespace App\Http\Controllers;

use Auth;

use Illuminate\Http\Request;

use App\Models\PersonalData;
use App\Models\Notification;

class SettingsController extends Controller
{
    public function getNotification(Request $request)
    {
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        return Notification::where('user_id', $user_id)->first();
    }

    public function saveNotifications(Request $request)
    {
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        if(Notification::where('user_id', $user_id)->exists())
        {
            $data = Notification::where('user_id', $user_id)->first();

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
            $data->user_id = $user_id;
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
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        return PersonalData::where('user_id', $user_id)->first();
    }
    public function savePersonalData(Request $request)
    {
        
        if(isset($request->dev))
        {
            $user_id = 14;
        }
        else
        {
            $user_id = Auth::user()->id;
        }

        if(PersonalData::where('user_id', $user_id)->exists())
        {
            $data = PersonalData::where('user_id', $user_id)->first();

            if(isset($request->type))
            {
                $data->type = $request->type;
            }
            if(isset($request->full_name))
            {
                $data->full_name = $request->full_name;
            }
            if(isset($request->full_office_name))
            {
                $data->full_office_name = $request->full_office_name;
            }
            if(isset($request->adress))
            {
                $data->adress = $request->adress;
            }
            if(isset($request->post_code))
            {
                $data->post_code = $request->post_code;
            }
            if(isset($request->city))
            {
                $data->city = $request->city;
            }
            if(isset($request->NIP))
            {
                $data->NIP = $request->NIP;
            }
            if(isset($request->phone_number))
            {
                $data->phone_number = $request->phone_number;
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
                $data->user_id = $user_id;
                $data->type = $request->type;
                $data->full_name = $request->full_name;
                $data->full_office_name = "";
                $data->adress = $request->adress;
                $data->post_code = $request->post_code;
                $data->city = $request->city;
                $data->NIP = "";
                $data->phone_number = $request->phone_number;
                $data->country = $request->country;
            }
            if($request->type == "business")
            {
                $data->user_id = $user_id;
                $data->type = $request->type;
                $data->full_name = $request->full_name;
                $data->full_office_name = $request->full_office_name;
                $data->adress = $request->adress;
                $data->post_code = $request->post_code;
                $data->city = $request->city;
                $data->NIP = $request->NIP;
                $data->phone_number = $request->phone_number;
                $data->country = $request->country;
            }
            $data->save();
        }
        
    }
}
