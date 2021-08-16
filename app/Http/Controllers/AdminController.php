<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function showUsers()
    {
        return User::all();
    }

    public function editUser()
    {

    }

    public function deleteUser()
    {
        
    }

    public function sendPDF()
    {

    }

    public function getExcel()
    {
        
    }

}
