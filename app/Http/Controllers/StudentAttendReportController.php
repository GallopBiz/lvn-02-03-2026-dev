<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Models\Classname;
use Illuminate\Http\Request;
use App\Models\dailyattandence;
use App\Http\Controllers\Controller;
use App\Models\Student_registration;



class StudentAttendReportController extends Controller
{
    public function index(){
        $alreadyShown = Student_registration::pluck('class_name')->toArray();
        $uniqueNames = array_unique($alreadyShown);
        // print_r($uniqueNames);
        // exit;

        $classlist = Classname::get();
        return view('backend.AcademicsModules.StudentAttandenc',compact('uniqueNames','classlist'));
        // return view('backend.AcademicsModules.StudentAttandenc',compact('uniqueNames'));
    }
    public function class_student(Request $request){
        $id = $request->id;
       $listclassstudent = Student_registration::where('class_name',$id)->get();

        //  print_r($listclassstudent);
        // exit;
        return json_encode($listclassstudent,1);
    }
    public function filter_class_attandence(Request $request){
        // print_r($request->all());exit;
        $a = explode('-',$request['Student']);
        $b = $request['inq_form_selection'];
        $class = $request['classname'];
        $stuname = DB::table('student_registration')->select('student_name')->where('id',$b)->get();
        $startDate = $request['fromdate'];
        $endDate = $request['todate'];
        // print_r($stuname[0]->student_name);exit;

        $startDate = Carbon::parse($startDate);

$stuattand = dailyattandence::where('created_at', '>=', $startDate)
->get();
// echo "<pre>"; print_r($stuattand);die();

    //    $stuattand = dailyattandence::where('create_date', '>=', $startDate)
                                // ->where('create_date', '<=', $endDate)
                                // ->where('class_name', $class)
                                // ->get();
        $classheld =  $stuattand->count();
        $students = [];
        foreach ($stuattand as $item) {
            $countP = 0;
            $students = json_decode($item->json_str, true);
        //   echo "<pre>"; print_r($students);die();


            if (!is_null($students) && (is_array($students) || is_object($students))) {
                foreach ($students as $student) {
                    $name = $student['student_name'];
                    $value = $student['value'];
                    // echo"<pre>";print_r($value);exit;
                    if ($name == $stuname[0]->student_name && $value == 'p') {
                        $countP++;
                    }
                }



            // foreach ($students as $student) {
            //     $name = $student['student_name'];
            //     $value = $student['value'];

            //     if ($name == $stuname && $value == 'p') {
            //         $countP++;
            //     }
            // }
            }

            $finaldata[] = [
                'countP' => $countP
            ];
        }
        $countPCount = 0;
        $finaldata = [];
        foreach ($finaldata as $item) {
            if (isset($item['countP'])) {
                $countPCount++;
            }
        }
        $finalAttandence = [
                                $classheld,
                                $countPCount,
                                $stuname,
                                $class
                            ];

        // echo"<pre>";
        // print_r($finalAttandence);
        // exit;
        $alreadyShown = Student_registration::pluck('class_name')->toArray();
        $uniqueNames = array_unique($alreadyShown);
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        // print_r($uniqueNames);
        // exit;
        return view('backend.AcademicsModules.StudentAttandenc',compact('uniqueNames','finalAttandence','classlist'));
        // exit;
    }
}
