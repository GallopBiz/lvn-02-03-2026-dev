<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\Employees;
use App\Models\Position;
use App\Models\Inquiry_registration;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
class EnquiryListdataController extends Controller
{
    // public function index(){
    //     return view('backend.AcademicsModules.stream');
    // }

    public function index(){
        // $stream = Employees::where('is_delete',0)->get(); 
        
    
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        // Fetch only specific columns
        $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')->select('session_name', 'student_name', 'class_name', 'json_str')->where('status','=','i')->get();
        //Inquiry_registration::select('session_name', 'student_name', 'class_name', 'json_str',)->get();

        return view('backend.enquiry_listdata', compact('all_inquiry','classlist'));
    
        // return view('backend.enquiry_listdata', compact('deparments'));
    }

    public function filter_enquiryList(Request $request)
{
    $session_name = $request->post('session_name');
    $class_name = $request->post('classname');
    $studentname = $request->post('student_name');
    $fromdate = $request->post('fromdate');
    $todate = $request->post('todate');
    // die();
    // $Userid = $data['Userid'];
    // $states = $data['states'];
    // $classlist2 = DB::table('class_name')->select('class_name')->distinct()->get();
    $jsonArr = [
        'session_name'=>$request->post('session_name'),
         'student_name'=>$request->post('student_name'),
        'fromdate'=> $request->post('fromdate'),
        'todate'=> $request->post('todate'),
        'class'=>$request->post('class'),
        
    ];

    $studentclasses = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();

    $EnqSecdata[] = [
        'session_name' => $session_name,
        'class_name' => $class_name,
        'studentname' => $studentname,
        'fromdate' => $fromdate,
        'todate' => $todate
    ];
    $records1 = DB::connection('dynamic')->table('inquiry_registration')->where(['status' => 'i']);

    if (!empty($session_name)) {
        $records1->where('session_name', $session_name);
    }
    
    if (!empty($class_name)) {
        $records1->where('class_name', $class_name);
    }
    
    if (!empty($studentname)) {
        $records1->where('student_name', 'LIKE', '%' . $studentname . '%');
    }
    
    if (!empty($fromdate)) {
        $records1->whereBetween('created_at', [$fromdate, $todate]);
    }
    
    $EnqSecdata = $records1->get();
    
    
   $all_inquiry = $records1->get();


    return view('backend.filter_enquryListdata',compact('all_inquiry','jsonArr','studentclasses','EnqSecdata'));

    
}

    


}