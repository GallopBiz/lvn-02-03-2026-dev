<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student_registration;
use App\Models\Course_fees_head_master;
use App\Models\CommanModel;

class feesregisterController extends Controller
{
    public function index(){
        $alreadyShown = Student_registration::pluck('class_name')->toArray();
        $uniqueNames = array_unique($alreadyShown);
        $session = Student_registration::pluck('session_name')->toArray();
        $uniquesession = array_unique($session);
        $shownheadname = Course_fees_head_master::pluck('ac_head_name')->toArray();
        $uniqueheadnameNames = array_unique($shownheadname);
        $shownheadname = Course_fees_head_master::pluck('ac_head_name')->toArray();
        $uniqueheadnameNames = array_unique($shownheadname);
        $feestypemaster = CommanModel::fetchDataWhere('fees_types_master',['is_delete'=>0]);
        $alltype = CommanModel::fetchDataWhere('feestypes',['is_delete'=>0]);
        $bustypefees = CommanModel::fetchDataWhere('busfees',['is_delete'=>0]);
        // print_r($uniquesession);exit;
        return view('backend.Fees-module.feesregister',compact('uniqueNames','uniqueheadnameNames','feestypemaster','alltype','bustypefees','uniquesession'));
    }
    public function feesregisterdata(Request $requset){
        
        if ($requset->has('All_class')) {
            $all_class = $requset->input('All_class');
            echo"hai bhai ";
            print_r($requset->all());
        } else {
            $all_class = '';
            echo"nahi hai bhai ";
            print_r($requset->all());
        }
        exit;
    }
}
