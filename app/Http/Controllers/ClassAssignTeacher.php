<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Student_registration;
use App\Models\CommanModel;
use App\Models\Teachers;
use App\Models\ClasseseAssignToTeacher;
use Illuminate\Http\Request;
use DB;

class ClassAssignTeacher extends Controller
{
    public function index(){    
        // echo"<pre>";  
        // filter class option 
        $alreadyShown = Student_registration::pluck('class_name')->toArray();
        $uniqueNames = array_unique($alreadyShown);
        $ClassTeacher = ClasseseAssignToTeacher::where('is_delete',0)->get();
        $teacherlist = Teachers::select('teacher_name')->distinct()->get();
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        // print_r($ClassTeacher);
        // exit;  
        return view('backend.AcademicsModules.ClassAssignTeacher',compact('teacherlist','classlist','uniqueNames','ClassTeacher'));
    }
    
    public function saveclassdata(Request $request){    
        // print_r($request->all());
        $insetArr  = [
            'Class'=>$request->class_name,
            'Section'=>$request->section_name,
            'teacher_name'=>$request->teacher_name,
            'teacher_namee'=>$request->Teacher_2
        ];
        // print_r($insertArr);
        CommanModel::insertData('classasigntoteacher',$insetArr);
        // echo "done";
        return redirect('calssese-assigne-to-teacher')
                        ->with('success','Record inserted successfully');
        exit;  
    }
    public function view($id){
        // print_r($id);
        $updateclassteacher = ClasseseAssignToTeacher::whereId($id)->get();
        $alreadyShown = Student_registration::pluck('class_name')->toArray();
        $uniqueNames = array_unique($alreadyShown);
        $ClassTeacher = ClasseseAssignToTeacher::where('is_delete',0)->get();
        $teacherlist = Teachers::select('teacher_name')->distinct()->get();
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        
        // echo"<pre>";
        // print_r($updateclassteacher);
        // exit;
        // return $updateclassteacher;
        return view('backend.AcademicsModules.ClassAssignTeacher',compact('teacherlist','classlist','updateclassteacher','uniqueNames','ClassTeacher'));
    }
    public function store(Request $request){
        // print_r($request->all());
        // exit;
        $data = [
            'Class' => $request->class_name,
            'Section' => $request->section_name,
            'teacher_name' => $request->teacher_name,
            'teacher_namee' => $request->teacher_namee,
        ];
        // print_r($data);
        
        ClasseseAssignToTeacher::whereId($request->id)->update($data);
        // echo"done";
        // exit;
        return redirect()->route('calssese-assigne-to-teacher')->with('success','data has been Updated successfully.');
    }
    public function  class_teacherdelete($id)
    {
        // echo $id;
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
}
