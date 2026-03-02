<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use DB;
use App\Models\CommanModel;
use App\Models\Inquiry_registration;
use App\Models\Inquiry;
use App\Models\Call;
use DataTables;
use App\Http\Requests\StoreFileRequest;



class MasterRegistrationController extends Controller
{
   
    /*List All Registration*/
    public function student_master()  
    {   
        $all_inquiry = CommanModel::fetchDataArr('student_registration');
        return view('backend.student-Master-info.Student-master',compact('all_inquiry'));
    }

}    