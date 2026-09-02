<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Classname;
use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\CommanModel;
use App\Models\Student_registration;
use Illuminate\Support\Facades\DB;

class SectionController extends Controller
{
    public function index(){
        $section = Section::orderBy('id','desc')->paginate(5);
        return view('backend.AcademicsModules.section', compact('section'));
    }

    public function create(Request $request){
        Section::create($request->post());
        return redirect()->route('sectionmaster')->with('success',' has been created successfully.');
    }

    public function view($id){
        $section_master = Section::whereId($id)->get();
        $section = Section::orderBy('id','desc')->paginate(5);
        return view('backend.AcademicsModules.section', compact('section_master','section'));
    }

    public function store(Request $request){
        $data = [
            'section' => $request->section,
            'remark' => $request->remark,
        ];
        Section::whereId($request->id)->update($data);
        return redirect()->route('sectionmaster')->with('success','Section has been Updated successfully.');
    }


    public function section_master_delete($id)
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
        $section = Section::findOrFail($id);
        $section->delete();
        return redirect()->route('sectionmaster')->with('success','Deleted successfully.');
    }

    public function sectionAssign()
    {
        $classes = Classname::where('is_delete', 0)->get();
        //$students = Student_registration::with('section')->where('class_id', '!=', null)->get();
        $students1 = Student_registration::whereNotNull('class_id')
            ->whereNotNull('section_name')
            ->get();

        // Group by class_name and section_name
        $classSectionCounts = $students1
            ->groupBy(function ($student) {
                return strtoupper($student->class_name); // or strtolower()
            })
            ->map(function ($classGroup) {
                return $classGroup->groupBy(function ($student) {
                    return ($student->section_name); // normalize here too if needed
                })->map->count();
            });


        return view('backend.AcademicsModules.sectionAssign', compact('classes',  'classSectionCounts'));
    }

    public function fetch_section_student_data(Request $request)
    {
        $students = Student_registration::with('section')->where('class_name',  $request->class_name)->where('class_id', null)->where('section_name', null)->get();
        $students1 = Student_registration::with('section')
            ->whereHas('section', function ($query) use ($request) {
                $query->where('class_name', $request->class_name);
            })->get();

        $sectionCounts = $students1->groupBy(fn ($student) => $student->section->section_name ?? 'Unknown')
            ->map(fn ($group) => $group->count());

        return ['studentds' => $students, 'sectionCounts' => $sectionCounts];
    }

    public function updateStudentSectio(Request $request)
    {
        if ($request->has('bulk_assign')){
            foreach($request->assignments as $assign){
                $class = Classes::where('class_name', $assign['studentclass'])->where('section_name', $assign['section'])->first();
                if(empty($class)){
                    return response()->json(['message' => 'Class section not found.'], 404);
                }
                $student = Student_registration::where('class_name', $assign['studentclass'])->where('id',  $assign['student_id'])->first();
                if(empty($student)){
                    return response()->json(['message' => 'Student not found.'], 404);
                }
                $data = json_decode($student->json_str, true);
                if (!is_array($data)) {
                    $data = [];
                }
                $data['section_name'] = $assign['section'];
                $student->json_str = json_encode($data, JSON_UNESCAPED_UNICODE);
                $student->class_id = $class->id;
                $student->section_name = $assign['section'];
                $student->save();
            }
            return response()->json(['message' => 'Section assigned successfully']);
        }else{
            $class = Classes::where('class_name', $request->studentclass)->where('section_name', $request->section)->first();
            if(empty($class)){
                return response()->json(['message' => 'Class section not found.'], 404);
            }
            $student = Student_registration::where('class_name',  $request->studentclass)->where('id',  $request->student_id)->first();
            if(empty($student)){
                return response()->json(['message' => 'Student not found.'], 404);
            }
            $data = json_decode($student->json_str, true);
            if (!is_array($data)) {
                $data = [];
            }
            $data['section_name'] = $request->section;
            $student->json_str = json_encode($data, JSON_UNESCAPED_UNICODE);
            $student->class_id = $class->id;
            $student->section_name = $request->section;
            $student->save();
            return response()->json(['message' => 'Section assigned successfully']);
        }
    }
    public function viewAssignSection($id, Request $request)
    {
        $sectionName = $request->query('section');
        $classes = Classname::where('is_delete', 0)->get();
        $students = Student_registration::with('section')->where('class_name',  $id)->where('section_name', $sectionName)->get();
        //dd($students);
        return view('backend.AcademicsModules.sectionAssign', compact('classes', 'students'));
    }
}
