<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommanModel;
use App\Models\SubjectAssignStudent;
use App\Models\SubjectCombination;
use App\Models\Student_registration;
use App\Models\Classes;
use DB;
class AssignSubjectController extends Controller
{
    public function index() {
        $stream = SubjectAssignStudent::where('is_delete', 0)->get();
        $subjectsassign = SubjectAssignStudent::with('Student','combination')->where('is_delete', 0)->get();
        $classlist = DB::table('class_name')->select('class_name')->get();
        $datas = DB::table('classes')->select('class_name')->distinct()->get();
        $comlist = SubjectCombination::select('id','combination_name')->where('is_delete', 0)->distinct()->get(); // Change the select column here
        $classes = Classes::where('is_delete', 0)->get();
        $classNames = $classes->where('is_delete', 0)->unique('class_name')->values();
        // Initialize $c_stream with a default value, or fetch it from your data
        $c_stream = ''; // You can set it to the default value or fetch it from your data
        //dd($subjectsassign);

        return view('backend.AcademicsModules.assignsubject', compact('classes', 'classNames','classlist', 'stream', 'datas', 'subjectsassign', 'comlist', 'c_stream'));
    }

    public function create(Request $request){

        dd($request);
        // $students_details = $request->input('students_details');
        $students_details = "";

        if($request->section_name == "All"){

            $data = $request->all();
            $sections = ['A', 'B'];
            foreach($sections as $section){
                 $data = $section;
                //  TeacherSubject::create($data);

                //   print_r($data);
                //  echo 'shivani';

            }

        }else{

            $newSubjectAssignStudent = new SubjectAssignStudent([
                'class_name' => $request->input('class_name'),
                'section_name' => $request->input('section_name'),
                'assign_this_combtoall' => $request->input('combination_name_dropdown'),
                // 'students_details' => $students_details
            ]);

            $newSubjectAssignStudent->save();

            // SubjectAssignStudent::create($request->post());
        }
        // SubjectCombination::create($request->post());
        $comlist = SubjectCombination::select('combination_name')->distinct()->get();
        return redirect()->route('AssignSubject')->with('success','  AssignSubject has been created successfully.');
    }


    public function view($id){
        $stream_master = SubjectAssignStudent::with('Student','combination')->whereId($id)->first();
        $stream = SubjectAssignStudent::orderBy('id','desc')->paginate(5);
        $datas = DB::table('classes')->select('class_name')->distinct()->get();
        $classlist = DB::table('class_name')->select('class_name')->distinct()->get();
        $subjectsassign = SubjectAssignStudent::all();
        $comlist = SubjectCombination::select('id','combination_name')->where('is_delete', 0)->distinct()->get();
        $classes = Classes::where('is_delete', 0)->get();
        return view('backend.AcademicsModules.assignsubject', compact('classes','stream_master','stream','classlist','subjectsassign', 'comlist'));

        // return view('backend.AcademicsModules.teachersubject',compact('classlist','sessions','teachersession', 'studentsession', 'pre_studentsessions'));

    }

    public function student_combination_data(Request $request){
        $class_name = $request->class_name;
        $section_name = $request->section_name;
        //$classes = Classes::where('class_name',$request->class_name)->first();
        // $data = DB::table('student_registration')->select('id', 'class_name', 'student_name')->where(['class_name','=',$class_name], ['section_name','=',$section_name])->distinct()->get();
        //$data = DB::table('student_registration')->where('class_name','=',$class_name)  ->get();
        if (empty($class_name)) {
            return response()->json([]);
        }

        $assignedStudentIds = [];
        if($class_name && $section_name){
            $assignedStudentIds = SubjectAssignStudent::where('class_name',  $request->class_name)->where('section_name',  $request->section_name)->where('is_delete', 0)->pluck('students_details')->toArray();
        }else{
            $assignedStudentIds = SubjectAssignStudent::where('class_name',  $request->class_name)->where('is_delete', 0)->pluck('students_details')->toArray();
        }

        $assignedStudentIds = collect($assignedStudentIds)
            ->flatMap(function ($studentId) {
                $decoded = is_string($studentId) ? json_decode($studentId, true) : null;

                return is_array($decoded) ? $decoded : [$studentId];
            })
            ->filter(fn ($studentId) => $studentId !== null && $studentId !== '')
            ->map(fn ($studentId) => (int) $studentId)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $students = Student_registration::where('class_name', $class_name)
            ->when($section_name, function ($query) use ($section_name) {
                $query->where(function ($sectionQuery) use ($section_name) {
                    $sectionQuery->where('section_name', $section_name)
                        ->orWhereJsonContains('json_str->section_name', $section_name);
                });
            })
            ->when(!empty($assignedStudentIds), fn ($query) => $query->whereNotIn('id', $assignedStudentIds))
            ->orderBy('student_name')
            ->get(['id', 'class_name', 'section_name', 'student_name']);


        return response()->json($students);

    }


    public function store(Request $request){
        //dd($request);
        if ($request->has('bulk_assign')) {
            foreach ($request->assignments as $assign) {
                // Example: insert or update logic
                SubjectAssignStudent::updateOrCreate(
                    ['students_details' => $assign['student_id']],
                    [
                        'class_name' => $assign['class_name'],
                        'section_name' => $assign['section_name'] ?? null,
                        'assign_this_combtoall' => $assign['combination_name'],
                        'is_delete' => 0,
                    ]
                );
                /* DB::table('subject_assign_student')->updateOrInsert(
                    [
                        'class_name' => $assign['class_name'],
                        'students_details' => $assign['student_id'],
                    ],
                    [
                        'assign_this_combtoall' => $assign['combination_name'],
                        'is_delete' => 0,
                        'updated_at' => now()
                    ]
                ); */
            }

            return response()->json(['status' => 'success']);
        }else{
            $validated = $request->validate([
                'studentclass' => 'required',
                'student_id' => 'required|exists:student_registration,id',
                'combination_name' => 'required|exists:subject_combinations,id',
            ]);
            DB::table('subject_assign_student')->updateOrInsert(
                [
                    'class_name' => $validated['studentclass'],
                    'students_details' => $validated['student_id'],
                ],
                [
                    'section_name' => $request->section_name,
                    'assign_this_combtoall' => $validated['combination_name'],
                    'is_delete' => 0,
                    'updated_at' => now()
                ]
            );
            return redirect()->route('AssignSubject')->with('success','AssignSubject has been Updated successfully.');
        }

    }




    public function fetchStudentData(Request $request)
    {
        // Fetch student data based on the selected class and section
        $selectedClass = $request->input('class_name');
        $selectedSection = $request->input('section_name');

        // You should implement the logic to fetch student data based on class and section here
        $studentData = response[i].studentData; // Replace with your logic to get student data

        return response()->json($studentData);
    }

    public function AssignSubject_delete($id)
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
        $stream = SubjectAssignStudent::findOrFail($id);
        $stream->delete();
        return redirect()->route('AssignSubject')->with('success','Deleted successfully.');
    }












}
