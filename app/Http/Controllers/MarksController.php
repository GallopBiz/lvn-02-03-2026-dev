<?php

namespace App\Http\Controllers;
use DB;
use App\Models\Marks;
use App\Models\Subject;
use App\Models\Teachers;
use App\Models\Classname;
use App\Models\Exammaster;
use App\Models\Tablemarks;
use App\Models\CommanModel;
use Illuminate\Http\Request;
use App\Models\TeacherSubject;
use App\Models\SubjectCombination;
use App\Http\Controllers\Controller;
use App\Models\SubjectAssignStudent;

class MarksController extends Controller
{
    // public function index(){T
    //     return view('backend.AcademicsModules.stream');
    // }

    public function index(){
        $stream = Marks::where('is_delete','=',0)->get();//Marks::where('is_delete',0)->get();
        //$teacherlist = Teachers::select('teacher_name')->distinct()->get();
        $examslist = DB::table('exammasters')->where('is_delete','=',0)->get();//Exammaster::select('exam_name')->distinct()->get();
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        $subjectlist = Subject::select('subject_name')->distinct()->get();
        $teacherlist = TeacherSubject::where('is_delete', 0) ->groupBy('teacher_id')->get();
        return view('backend.AcademicsModules.marks', compact('classlist','stream','teacherlist','examslist','subjectlist'));
    }


    public function create(Request $request){
        //dd($request);
        $data = [
            "teacher_id" => $request->teacher_name,
            "class_id" => $request->class_name,
            "section_name" => $request->section_name,
            "exam_id" => $request->exam_name,
            "subject_id" => $request->subject_name,
            "Stream_id" => $request->stream_id ?? null,

        ];
        $mark = Marks::create($data);
        $id = $mark->id;

        $marksdata = $request->students;


        foreach ($marksdata as $mdata) {

            $newmdata = [
                "marks_id" => $id,
                "is_absent" => $mdata['is_absent'],
                "is_absent_pr" => $mdata['is_absent_pr'],
                "student_id" => $mdata['student_id'],
                "subject_marks" => $mdata['marks']
            ];
            DB::table("marks")->insert($newmdata);
        }
        $response = ["status" => "success"];
        return response()->json($response);


        // return "success";
        // Marks::create($request->post());
        // return redirect()->route('marks')->with('success','  marks has been created successfully.');
    }

    public function classstudentdata(Request $request){
        $section_name = $request->section_name;
        $subjectId = $request->subject_id;
        $classId = $request->class;

        $combinations = SubjectCombination::where('class_id', $classId)
            ->whereHas('subjects', function ($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })
            ->first();
        $studentsIds = SubjectAssignStudent::where('assign_this_combtoall',$combinations->id)->pluck('students_details');
        $data['students'] = DB::table('student_registration')
            ->whereIn('id', $studentsIds)
            //->where('class_id', $classId)
            ->whereJsonContains('json_str->section_name', $section_name)
            ->get();
        //dd($data);
        return $data;
    }

    public function grade_percentage(Request $request) {
        $percentage = $request->post('percentage');

        $arr = DB::table('grademaster')->where('is_delete','=',0)->get();
        $data = "";

        foreach ($arr as $range) {
            $min = $range->min_per;
            $max = $range->max_per;
            $grade = $range->grade;

            if ($percentage >= $min && $percentage <= $max) {
                $data = $grade;
                break;  // Break out of the loop once a match is found
            }
        }

        return json_encode($data);
    }


    public function view($id){
        $stream_master = Marks::where('id', $id)->where('is_delete',0)->first();//Marks::where('is_delete',0)->get();
        $stream = Marks::where('is_delete',0)->get();
        $teacherlist = TeacherSubject::where('is_delete', 0)->groupBy('teacher_id')->get();
        $examslist = Exammaster::select('exam_name', 'id')->distinct()->get();
        //$classlist = DB::table('classes')->select('class_name')->distinct()->get();
        $classlist = Classname::select('class_name','id')->where('id',$stream_master->class_id)->first();
        $subjectlist = Subject::select('subject_name')->distinct()->get();
        $marksData = DB::table('marks')
            ->join('student_registration', 'marks.student_id', '=', 'student_registration.id')
            ->where('marks.marks_id', $stream_master->id)
            ->select('marks.*', 'student_registration.*') // Add fields as needed
            ->get();
            //dd($marksData);
        // return $stream_master;
        //dd($classlist);
        return view('backend.AcademicsModules.marks', compact('marksData', 'classlist','stream_master','stream','teacherlist','examslist','subjectlist'));
    }

    public function store(Request $request){
        //dd($request);
        $marksId = $request->marks_id;
        $mainData = [
            "teacher_id" => $request->teacher_name,
            "class_id" => $request->class_name,
            "section_name" => $request->section_name,
            "exam_id" => $request->exam_name,
            "subject_id" => $request->subject_name,
            "Stream_id" => $request->stream_id ?? null,
        ];
    
        Marks::where('id', $marksId)->update($mainData);
    
        // 2. Handle student marks
        $marksdata = $request->students;
    
        foreach ($marksdata as $mdata) {
            $studentMark = [
                "marks_id" => $marksId,
                "student_id" => $mdata['student_id'],
                "is_absent" => $mdata['is_absent'],
                "is_absent_pr" => $mdata['is_absent_pr'],
                "subject_marks" => $mdata['marks'],
            ];
    
            // 3. Check if the mark exists for this student
            $existing = DB::table("marks")
                ->where("marks_id", $marksId)
                ->where("student_id", $mdata['student_id'])
                ->first();
    
            if ($existing) {
                // Update existing
                DB::table("marks")
                    ->where("id", $existing->id)
                    ->update($studentMark);
            } else {
                // Insert new
                DB::table("marks")->insert($studentMark);
            }
        }
    
        return response()->json(["status" => "success"]);
    }

    public function marks_delete($id)
    {
        // echo $id;
        // exit;
        $a = explode('-',$id);
        $b = $a[1];
        $c = $a[0];
        $delete_resp = CommanModel::soft_delete($c,['id'=>$b]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function delete($id){
        $stream = Marks::findOrFail($id);
        $stream->delete();
        return redirect()->route('marks')->with('success','Deleted successfully.');
    }

    public function showmarks(){
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        $examslist = DB::table('exammasters')->select('exam_name')->where('is_delete','=',0)->get();//Exammaster::select('exam_name')->distinct()->get();
        return view('backend.AcademicsModules.showmarks',compact('classlist','examslist'));
    }

    public function show_report_markss(Request $request){
        $section_name = $request->post('section_name');
        $class_name = $request->post('classname');
        $term_name = $request->post('term_name');
        $report_type = $request->post('report_type');
        $exam_title = $request->post('exam_title');
        $best_of_two = $request->post('best_of_two');
        $pdf_check = $request->post('pdf_check');
        if (!empty($pdf_check)){
            echo "yes";
        } else {
            echo "no";
        }
        die();
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        $examslist = DB::table('exammasters')->select('exam_name')->where('is_delete','=',0)->get();
        $studentmarkss = DB::connection('dynamic')
                ->table('previosly_saved_marks_entry')
                ->join('marks', 'previosly_saved_marks_entry.id', '=', 'marks.marks_id')
                ->where('previosly_saved_marks_entry.class_name', '=', $class_name)
                ->where('section_name', '=', $section_name)
                ->Where('exam_name', '=', $term_name)
                ->get();
        $class_teacher = DB::connection('dynamic')->table('teacher_subjects')->select('teacher_name')
        ->where('class_name','=',$class_name)
        ->where('role','=','Class Teacher')
        ->first();
        $studentmarks = [];
        $studentmarks_grade = [];
        $studentmarks_total = [];
        $subject = [];
        $total = 0;
        $sum_marks = 0;
        // echo '<pre>';
        $studentmarks_total_sum = [];
        $studentmarks_total_sum_max = [];
        $student_grade = [];
        foreach($studentmarkss as $key => $student){
            $subject[$student->subject_name] = $student->max_marks;
            $studentmarks[$student->student_name][$student->subject_name] = $student->total_marks;
            $studentmarks_grade[$student->student_name][$student->subject_name] = $student->grade;
            if($student->subject_name != 'Sanskrit' && $student->subject_name != 'Computer Science'){
                $studentmarks[$student->student_name]['total'][] = $student->total_marks;
                $studentmarks[$student->student_name]['max_total'][] = $student->max_marks;
            }
            $studentmarks[$student->student_name]['scholar_no'] = $student->scholar_no;
            $studentmarks_total_sum[$student->student_name] = array_sum($studentmarks[$student->student_name]['total']);
            $studentmarks_total_sum_max[$student->student_name] = array_sum($studentmarks[$student->student_name]['max_total']);
            $student_grade[$student->student_name] = $this->check_grade(($studentmarks_total_sum[$student->student_name] / $studentmarks_total_sum_max[$student->student_name]) * 100);
            $studentmarks_total[$student->student_name][$student->subject_name] = $student->max_marks;
        }
        // print_r($studentmarks);die();
        // print_r($studentmarks_total_sum_max);

        return view('backend.AcademicsModules.showmarks',compact('report_type','term_name','class_name','section_name','class_teacher','studentmarks_grade','student_grade','studentmarks_total_sum_max','subject','classlist','examslist','studentmarks'));
    }

    public function check_grade($number){
        $arr = DB::table('grademaster')->where('is_delete','=','0')->get();
        $data = "";

        foreach ($arr as $range) {
            $min = $range->min_per;
            $max = $range->max_per;
            $grade = $range->grade;

            if ($number >= $min && $number <= $max) {
                $data = $grade;
                break;  // Break out of the loop once a match is found
            }
        }
        return $data;
    }
}
