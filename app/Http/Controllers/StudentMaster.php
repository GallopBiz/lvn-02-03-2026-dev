<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentM;
class StudentMaster extends Controller
{
    public function index(){
        return view('backend.Student-Master-info.Student-master');
    }
}
