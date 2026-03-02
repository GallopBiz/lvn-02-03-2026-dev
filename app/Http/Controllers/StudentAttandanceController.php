<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentAttandanceController extends Controller
{
    public function index(){
        return view('backend.AcademicsModules.studentattandence');
    }
}
