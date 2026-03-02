<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\ExamType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExamtypeController extends Controller
{
    // public function index(){
    //     return view('backend.AcademicsModules.stream');
    // }

    public function index(){
        $stream = ExamType::where('is_delete',0)->get();
        
        return view('backend.AcademicsModules.examtype', compact('stream'));
    }

    
    public function create(Request $request){
        ExamType::create($request->post());
        return redirect()->route('examtype')->with('success','  examtype has been created successfully.');
    }

    public function view($id){
        $stream_master = ExamType::whereId($id)->get();
        $stream = ExamType::orderBy('id','desc')->paginate(5);
        return view('backend.AcademicsModules.examtype', compact('stream_master','stream'));
    }

    public function store(Request $request){
        $data = [
            'examtype' => $request->examtype,
            
        ];
        ExamType::whereId($request->id)->update($data);
        return redirect()->route('examtype')->with('success','examtype has been Updated successfully.');
    }

    
    public function examtype_delete ($id)
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
        $stream = ExamType::findOrFail($id);
        $stream->delete();
        return redirect()->route('examtype')->with('success','Deleted successfully.');
    }

}
