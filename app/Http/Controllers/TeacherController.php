<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\Teachers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    // public function index(){
    //     return view('backend.AcademicsModules.stream');
    // }

    public function index(){
        $stream = Teachers::where('is_delete',0)->get();        
        return view('backend.AcademicsModules.teacher', compact('stream'));
    }

    
    public function create(Request $request){
        Teachers::create($request->post());
        return redirect()->route('teachers')->with('success','  teacher has been created successfully.');
    }

    public function view($id){
        $stream_master = Teachers::whereId($id)->get();
        // $stream = Teachers::orderBy('id','desc')->paginate(5);
        $stream = Teachers::where('is_delete',0)->get();     
        return view('backend.AcademicsModules.teacher', compact('stream_master','stream'));
    }

    public function store(Request $request){
        $data = [
            'teacher_name' => $request->teacher_name,
            
        ];
        Teachers::whereId($request->id)->update($data);
        return redirect()->route('teachers')->with('success','teacher has been Updated successfully.');
    }


    public function delete($id){
        $teachersubject = Teachers::findOrFail($id);
        $teachersubject->delete();
        return redirect()->route('teachers')->with('success','Deleted successfully.');
    }

    
    public function teaches_delete($id)
    {        
        $stream = Teachers::findOrFail($id);
        $stream->delete();
        return redirect()->back()->with('success', 'Record successfully removed');
    }

}
