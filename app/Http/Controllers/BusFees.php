<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BusFees extends Controller
{
    public function index(){
        return view('backend.Fees-module.Bus_Fees_Type');
    }
}
