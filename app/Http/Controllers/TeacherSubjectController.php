<?php

namespace App\Http\Controllers;
use DB;
use App\Models\Stream;
use App\Models\Subject;
use App\Models\Teachers;
use App\Models\Classname;
use App\Models\CommanModel;
use App\Models\HrmsEmployee;
// use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\HrmsDepartment;
use App\Models\TeacherSubject;
use App\Http\Controllers\Controller;
use App\Models\Student_registration;
use Illuminate\Support\Facades\Auth;

class TeacherSubjectController extends Controller
{
    public function index(){
        //$studentclasses = Student_registration::select('class_name')->distinct()->get();
        //$studentsession = Student_registration::select('session_name')->distinct()->get();
        //$subjects = Subject::select('id','subject_name')->distinct('subject_name')->get();
        //$teacherlist = Teachers::select('teacher_name')->distinct()->get();
        //$session = Student_registration::select('id','session_name')->distinct('session_name')->get();
        $teachersubjects = TeacherSubject::where('is_delete', 0)->get();
        //$teachersession = Student_registration::all();
        //$pre_studentsessions = $studentsession;
        //$datas = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $classlist = Classname::select('id','class_name')->where('is_delete', 0)->distinct()->get();
        $streamlist = Stream::where('is_delete', 0)->get();
        $deparments = HrmsDepartment::all();
        //return view('backend.AcademicsModules.teachersubject', compact('deparments','streamlist','teacherlist','classlist','studentclasses', 'subjects', 'teachersubjects', 'session', 'studentsession', 'pre_studentsessions','teachersession'));
        return view('backend.AcademicsModules.teachersubject', compact('deparments','streamlist','classlist', 'teachersubjects'));
    }

    public function create(Request $request)
    {
        $class = Classname::where('id', $request->class_name)->first();
        $stream = Stream::where('id', $request->streams)->first();
        $subject = Subject::where('id', $request->subject_name)->first();
        $employee = HrmsEmployee::where('id', $request->teacher_name)->first();

        $check = TeacherSubject::where('class_id', $class->id)
            ->when(in_array($class->class_name, ['11', '12']), function ($query) use ($stream) {
                $query->where('stream_id', $stream ? $stream->id : null);
            })
            ->where('section_name', $request->section_name)
            ->where('subject_id', $subject->id)
            ->where('teacher_id', $employee->id)
            ->where('is_delete', 0)
            ->first();

        if ($check) {
            return redirect()->route('teachersubject')->with('error', 'Data already exists!');
        }
        $streamId = in_array($class->class_name, ['11', '12']) ? ($stream->id ?? null) : null;

       // dd($check);
        if ($request->section_name == "All") {
            if ($request->role == 'Class Teacher') {
                return redirect()->route('teachersubject')->with('error', 'One teacher cannot be assigned to all sections of a class as class teacher.');
            }
            $sections = ['Kautilya', 'Ramanujan', 'Aryabhatta'];
            foreach ($sections as $section) {
                $exists = TeacherSubject::where('class_id', $class->id)
                    ->when($streamId, fn($q) => $q->where('stream_id', $streamId))
                    ->where('section_name', $section)
                    ->where('subject_id', $subject->id)
                    ->where('teacher_id', $employee->id)
                    ->where('is_delete', 0)
                    ->exists();

                if (!$exists) {
                    TeacherSubject::create([
                        'class_id'     => $class->id,
                        'stream_id'    => $streamId,
                        'section_name' => $section,
                        'subject_id'   => $subject->id,
                        'teacher_id'   => $employee->id,
                        'role'         => $request->role,
                    ]);
                }
            }
        } else {
            TeacherSubject::create([
                'class_id' => $class->id,
                'stream_id' => $streamId,
                'section_name' => $request->section_name,
                'subject_id' => $subject->id,
                'teacher_id' => $employee->id,
                'role' => $request->role,
            ]);
        }

        return redirect()->route('teachersubject')->with('success', 'Teacher subject has been created successfully.');
    }


    public function teachersubject_copy(Request $request){
        $data = $request->all();
        // print_r($data['pre_session']);
        $pre_session = $data['pre_session'];
        $session_name = $data['session_name'];
        $TeacherSubject = TeacherSubject::where(['session_name' => $pre_session])->get();
        $newData = [];
        foreach ($TeacherSubject as $item) {
            $data = array(
                "class_name" => $item['class_name'],
                "section_name" => $item['section_name'],
                "subject_name" => $item['subject_name'],
                "teacher_name" => $item['teacher_name'],
                "session_name" => $session_name,
            );
            array_push($newData, $data);
        }

        TeacherSubject::insert($newData);

        // return redirect()->route('teachersubject')->with('success',' copied successfully.');
        return redirect()->route('teachersubject')->with('success',' has been copied successfully.');
    }


    public function view($id){
        $teacher_subject = TeacherSubject::whereId($id)->first();
        // $teachersubjects = TeacherSubject::orderBy('id','desc');
        //$teachersubjects = TeacherSubject::all();

        // if(!empty($teacher_subject)){
        //     $teacher_subject[count($teacher_subject)-1]->class_name = explode(',', $teacher_subject[count($teacher_subject)-1]->class_name);
        // }
        //$teacher_subject = $teacher_subject[0];

        //$studentclasses = Student_registration::select('class_name')->distinct()->get();
        //$classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        //$teacherlist = Teachers::select('teacher_name')->distinct()->get();
        $class_id = $teacher_subject->class_id;
        $stream_id = $teacher_subject->stream_id;
        $subjects = Subject::whereHas('Classname', function ($query) use ($class_id, $stream_id) {
            $query->where('academic_class_subject.class_id', $class_id);

            if (!empty($stream_id)) {
                $query->where('academic_class_subject.stream_id', $stream_id);
            }
        })->get();
        $classlist = Classname::select('id','class_name')->where('is_delete', 0)->distinct()->get();
        $streamlist = Stream::where('is_delete', 0)->get();
        $deparments = HrmsDepartment::all();
        $employee = HrmsEmployee::with('biometricDetails')->where('department_id', $teacher_subject->Teacher->department->id)->get();
        //dd($employee[0]->biometricDetails->ess_emp_code);
        return view('backend.AcademicsModules.teachersubject',compact('deparments','streamlist','teacher_subject','classlist', 'subjects', 'employee'));

        /* $sessions = Student_registration::whereId($id)->get();
        $teachersession = Student_registration::orderBy('id','desc');

        if(!empty($sessions)){
            $sessions[count($sessions)-1]->class_name = explode(',', $sessions[count($sessions)-1]->session_name);
        }
        $pre_studentsessions = $studentsession;

        $studentsession = Student_registration::select('session_name')->distinct()->get();

        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();

        return view('backend.AcademicsModules.teachersubject',compact('classlist','sessions','teachersession', 'studentsession', 'pre_studentsessions')); */

    }


    // public function getteachersclasses(Request $request){
    //     $teacher = $request->teacher;

    //     $data = DB::table('teacher_subjects')->where("teacher_name", $teacher)->distinct()->get();
    //     return $data;
    // }

    // public function getteacherssubject(Request $request){
    //     $teacher = $request->teacher;

    //     $data = DB::table('teacher_subjects')->where("teacher_name", $teacher)->distinct()->get();
    //     return $data;
    // }
    public function getteachersdata(Request $request){
        $teacher = Auth::guard('staff')->check() && !Auth::guard('web')->check()
            ? Auth::guard('staff')->user()->employee_id
            : $request->teacher;

        $classes = TeacherSubject::with('Class')->where("teacher_id", $teacher)->groupBy('class_id')->where('is_delete','=',0)->get();
        $subjects = TeacherSubject::with('Subject')->where("teacher_id", $teacher)->groupBy('subject_id')->where('is_delete','=',0)->get();
        $sections = TeacherSubject::where("teacher_id", $teacher)->where('is_delete','=',0)->distinct()->pluck('section_name');
        return ['classes' => $classes, 'subjects' => $subjects, 'sections' => $sections];
    }

    public function getteachersandsubject(Request $request){
        $teacher = Auth::guard('staff')->check() && !Auth::guard('web')->check()
            ? Auth::guard('staff')->user()->employee_id
            : $request->teacher;
        $class_name = $request->class_name;

        // $classes = DB::table('teacher_subjects')->where("teacher_name", $teacher)->distinct()->pluck('class_name');
        $subjects =TeacherSubject::with('Subject')->where("teacher_id", $teacher)->where("class_id", $class_name)->groupBy('subject_id')->get();

        return [ 'subjects' => $subjects];
    }

    public function store(Request $request){
        //   echo 'store';
            // print_r($request->all());

            $teachersubject = array(
                'class_id'     => $request->class_name,
                'stream_id'    => $request->streams ?? null,
                'section_name' => $request->section_name ?? null,
                'subject_id'   => $request->subject_name,
                'teacher_id'   => $request->teacher_name,
                'role'         => $request->role,


            );

            // $exammaster = Exammaster::create($exammaster);
            // return view($exammaster, "added successfully.");

            TeacherSubject::whereId($request->id)->update($teachersubject);
            return redirect()->route('teachersubject')->with('success',' Updated successfully.');

    }

    public function teachersubject_delete($id)
    {
        // print_r($request->all());
        // die();
        //$table = $request->table_name;
        //$delete_resp = CommanModel::soft_delete($table,['id'=>$request->delete_id]);
        TeacherSubject::whereId($id)->update(['is_delete' => 1]);
        return redirect()->back()->with('success', 'Record successfully removed');
        /* if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        } */
    }


    public function delete($id){
        $teachersubject = TeacherSubject::findOrFail($id);
        $teachersubject->delete();
        return redirect()->route('teachersubject')->with('success','Deleted successfully.');
    }
    // created by Nirmal
    public function fetchSubjectsForTeacher(Request $request)
    {
        $class_id = $request->class_id;
        $stream_id = $request->stream_id;

        $subjects = Subject::whereHas('Classname', function ($query) use ($class_id, $stream_id) {
            $query->where('academic_class_subject.class_id', $class_id); // Explicit table name

            if (!empty($stream_id)) {
                $query->where('academic_class_subject.stream_id', $stream_id); // Explicit pivot field
            }
        })->get();
        return $subjects;
    }
    // created by Nirmal
    public function fetchTeachers(Request $request)
    {
        $departmentId = $request->departmentId;
        $employee = HrmsEmployee::with('biometricDetails')->where('department_id', $departmentId)->get();
        return $employee;
    }

}
