<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\busattandence;
use App\Models\CommanModel;
use App\Models\Student_registration;



class MobileController extends Controller
{
    public function index(){
        // $students = CommanModel::fetchDataArr('student_registration');
        $students = student_registration::all();
        // echo"<pre>";
        // print_r($students);
        // exit;
        // return view('backend.student_registrations.index',compact('all_inquiry'));
        return view('backend.mobileview',compact('students'));
    }
    public function Attendance(Request $request){
        // echo"<pre>";
        // print_r($request->all());
        $student_name = $request->Student_name;
        $class_name = $request->Class_name;
        $address = $request->Address;
        $student_id = $request->student_id;
        $Attendance = $request->attendance;
        // print_r($student_id);
        // print_r($student_name);
        // exit;
        
        $data = [];
        for ($i = 0; $i < count($student_name); $i++) {
            $data[] = [
                'name' => $student_name[$i],
                'class' => $class_name[$i],
                'address' => $address[$i],  
                'student_id' => $student_id[$i],
                'Attendance' => $Attendance[$i]
            ];
            
        }
        

        $insertArr  = [
            'DC_name'=>$request->DC_name,
            'Bus_no'=>$request->Bus_no,
            'json_str'=>json_encode($data)
        ];
        echo"<pre>";
        print_r($insertArr);
        exit;
        // busattandence::create($insertArr->post());
        CommanModel::insertData('busattandence',$insertArr);
        // echo "done";
        return redirect()->back()->with('success', 'Student successfully registred'); 
        exit;
    }

    public function list_busAttendence(){
        $listbusattandence= busattandence::where('is_delete',0)->orderBy('created_at', 'desc')->get();
        // echo"<pre>";
        // print_r($students);
        // exit;
        return view('backend.Transport.busattendance',compact('listbusattandence'));

    }
    public function filter_Attendance(Request $request){
        // print_r($request->all());
        $postData = $request->all();
        $fromdate = $request->post('fromdate');
        $todate = $request->post('todate');
        // print_r($postData);
        // exit;
        $filterlistbusattandence= busattandence::whereDate('created_at', '>=', $fromdate)
        ->whereDate('created_at', '<=', $todate)
        ->get();
        return view('backend.Transport.filter_busattendance',compact('filterlistbusattandence'));
        exit;
    }
    public function bus_student(Request $request){
        $id = $request->id;
        // print_r($request->all());
        // exit;
       $listbusstudent = busattandence::whereId($id)->get('json_str');
       
    //    echo"<pre>";
    //     print_r($listbusstudent);
    //     exit;
        return json_encode($listbusstudent,1);                      

    }
}
