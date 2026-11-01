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
        $reportAccess = app(\App\Services\StaffReportAccessService::class);
        $alreadyShown = Student_registration::pluck('class_name')->toArray();
        $uniqueNames = array_values(array_unique(array_filter($alreadyShown, fn ($className) => $reportAccess->canAccess($className))));
        // print_r($uniqueNames);
        // exit;

        $classlist = $reportAccess->filterClassOptions(Classname::get());
        return view('backend.AcademicsModules.StudentAttandenc',compact('uniqueNames','classlist'));
        // return view('backend.AcademicsModules.StudentAttandenc',compact('uniqueNames'));
    }
    public function class_student(Request $request){
        $id = $request->route('id') ?? $request->id;
        $reportAccess = app(\App\Services\StaffReportAccessService::class);
        abort_unless($reportAccess->canAccess($id), 403, 'You are not allowed to view attendance for this class.');
        $listclassstudent = Student_registration::where('class_name', $id)->get()
            ->filter(fn ($student) => !$reportAccess->isStaffUser()
                || (!empty($student->section_name)
                    && $reportAccess->canAccess($student->class_id ?: $student->class_name, $student->section_name)))
            ->values();

        //  print_r($listclassstudent);
        // exit;
        return json_encode($listclassstudent,1);
    }
    public function filter_class_attandence(Request $request){
        // print_r($request->all());exit;
        $a = explode('-',$request['Student']);
        $b = $request['inq_form_selection'];
        $class = $request['classname'];
        $selectedStudent = Student_registration::findOrFail($b);
        $reportAccess = app(\App\Services\StaffReportAccessService::class);
        abort_unless(
            (!$reportAccess->isStaffUser() || !empty($selectedStudent->section_name))
                && $reportAccess->canAccess($class, $selectedStudent->section_name)
                && $reportAccess->canAccess($selectedStudent->class_id ?: $selectedStudent->class_name, $selectedStudent->section_name),
            403,
            'You are not allowed to view attendance for this class/section.'
        );
        $stuname = collect([(object) ['student_name' => $selectedStudent->student_name]]);
        $startDate = $request['fromdate'];
        $endDate = $request['todate'];
        // print_r($stuname[0]->student_name);exit;

        $startDate = Carbon::parse($startDate);

$attendanceQuery = dailyattandence::whereDate('created_at', '>=', $startDate)
    ->whereDate('created_at', '<=', $endDate)
    ->where('class_name', $class);
$allowedSections = $reportAccess->allowedSections($class);
if (is_array($allowedSections)) {
    abort_if(empty($allowedSections), 403, 'You are not allowed to view attendance for this class/section.');
    $attendanceQuery->whereIn('section_name', $allowedSections);
}
$stuattand = $attendanceQuery->get();
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
        $uniqueNames = array_values(array_unique(array_filter($alreadyShown, fn ($className) => $reportAccess->canAccess($className))));
        $classlist = $reportAccess->filterClassOptions(DB::table('classes')->select('class_name')->distinct()->get());
        // print_r($uniqueNames);
        // exit;
        return view('backend.AcademicsModules.StudentAttandenc',compact('uniqueNames','finalAttandence','classlist'));
        // exit;
    }
}
