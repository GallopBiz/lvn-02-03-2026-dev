<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Teachers;
use App\Models\CommanModel;
use Illuminate\Http\Request;
use App\Models\TeacherSubject;
use App\Models\dailyattandence;
use App\Models\StudentAttendance;
use App\Http\Controllers\Controller;
use App\Models\Student_registration;

class DailyAttandanceController extends Controller
{
    public function index(){
        $Classes = Student_registration::all();
        // echo"<pre>";
        // print_r($Classes);
        // exit;
        $dailyreport = dailyattandence::all();
         // Step 2: Retrieve the data that has already been shown and store it in an array or collection
    $alreadyShown = Student_registration::pluck('class_name')->toArray();

    // Step 3: Query the database to select the data that has not been shown yet
    // $nextData = Student_registration::whereNotIn('class_name', $alreadyShown)->first();
    $uniqueNames = array_unique($alreadyShown);

        // $records = Student_registration::select('class_name')->get();

        // echo"<pre>";
        // print_r($dailyreport);
        // exit;
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        $teacherlist = TeacherSubject::where('is_delete', 0) ->groupBy('teacher_id')->get();
        return view('backend.AcademicsModules.dailyattandence', compact('teacherlist','classlist','Classes','dailyreport','uniqueNames','alreadyShown'));
    }


    public function filter(Request $request){
        // $id=;
        $class_id = $request->class_id;
        $section_name = $request->section_name;
        $classwiseAttendance = Student_registration::where('class_name', $class_id)->whereJsonContains('json_str->section_name', $section_name)->get();
        // echo"<pre>";
        // print_r($classwiseAttendance);
        // exit;
        //dd($classwiseAttendance);
        return json_encode($classwiseAttendance,1);

    }

    public function Attendance(Request $request){
        // Check if attendance already exists
        $exists = dailyattandence::where('Teacher_id', $request->teacher_name)
            ->where('class_id', $request->class_name)
            ->where('section_name', $request->section_name)
            ->where('Attandence_date', $request->Attandence_Name)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Attendance already recorded for this date.');
        }

        // Begin DB transaction for safety
        DB::beginTransaction();

        try {
            // Create main attendance session
            $attendanceSession = dailyattandence::create([
                'Teacher_id' => $request->teacher_name,
                'class_id' => $request->class_name,
                'section_name' => $request->section_name,
                'Attandence_date' => $request->Attandence_Name,
            ]);

            // Build student attendance records
            $studentData = [];

            foreach ($request->attendance as $studentId => $status) {
                $studentData[] = [
                    'attendance_id' => $attendanceSession->id,
                    'student_id' => $studentId,
                    'status' => $status
                ];
            }

            // Bulk insert into student_attendance table
            StudentAttendance::insert($studentData);

            DB::commit();
            return redirect()->back()->with('success', 'Attendance saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save attendance: ' . $e->getMessage());
        }
    }

    public function dailyattandenceUpdate($id){
        $dailyreportMarster = dailyattandence::where('id', $id)->first();
        $teacherlist = TeacherSubject::where('is_delete', 0) ->groupBy('teacher_id')->get();
        $studentAttendances = StudentAttendance::where('attendance_id', $id)->get();
        return view('backend.AcademicsModules.dailyattandence', compact('studentAttendances','teacherlist','dailyreportMarster'));
    }

    public function dailyattandenceUpdateInfo(Request $request)
    {
        DB::beginTransaction();

        try {

            $studentData = [];

            foreach ($request->attendance as $studentId => $status) {
                StudentAttendance::updateOrCreate(
                    [
                        'attendance_id' => $request->attendance_id,
                        'student_id' => $studentId
                    ],
                    [
                        'status' => $status,
                    ]
                );
            }

            DB::commit();

            return redirect()->back()->with('success', 'Attendance has been saved/updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save/update attendance: ' . $e->getMessage());
        }

    }

}
