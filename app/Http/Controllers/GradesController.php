<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\Grades;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class GradesController extends Controller
{
    // public function index(){
    //     return view('backend.AcademicsModules.stream');
    // }

    public function index(){
        // $stream = Grades::orderBy('id','desc')->get();

        $stream = DB::table('grades')->orderBy('id','desc')->get();

        return view('backend.AcademicsModules.grade', compact('stream'));
    }

    public function create(Request $request){
        // Grades::create($request->post());
        Grades::create($request->post());

        return redirect()->route('gread')->with('success','  Stream has been created successfully.');
    }

    public function view($id){
        $stream_master = Grades::whereId($id)->get();
        // $stream = Grades::orderBy('id','desc')->paginate(5);

        $stream = DB::table('grades')->orderBy('id','desc')->paginate(5);

        return view('backend.AcademicsModules.grade', compact('stream_master','stream'));
    }

    public function store(Request $request){
        $data = [
            'termigradecoscholasticareas' => $request->termigradecoscholasticareas,
            'termiigradecoscholasticareas' => $request->termiigradecoscholasticareas,
            'termigradedicipline' => $request->termigradedicipline,
            'termiigradedicipline' => $request->termiigradedicipline,

        ];
        Grades::whereId($request->id)->update($data);
        return redirect()->route('gread')->with('success','Stream has been Updated successfully.');
    }

    public function grade_delete($id)
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
        $stream = Grades::findOrFail($id);
        $stream->delete();
        return redirect()->route('gread')->with('success','Deleted successfully.');
    }

}
