<?php

namespace App\Http\Controllers;

use App\Models\Grades;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradesController extends Controller
{
    // public function index(){
    //     return view('backend.AcademicsModules.stream');
    // }

    public function index(){
        // $stream = Grades::orderBy('id','desc')->get();

        $stream = DB::table('grades')->where('is_delete', 0)->orderBy('id','desc')->get();

        return view('backend.AcademicsModules.grade', compact('stream'));
    }

    public function create(Request $request){
        // Grades::create($request->post());
        Grades::create($request->post());

        return redirect()->route('grade')->with('success','Grade has been created successfully.');
    }

    public function view($id){
        $stream_master = Grades::whereId($id)->get();
        // $stream = Grades::orderBy('id','desc')->paginate(5);

        $stream = DB::table('grades')->where('is_delete', 0)->orderBy('id','desc')->paginate(5);

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
        return redirect()->route('grade')->with('success','Grade has been updated successfully.');
    }

    public function grade_delete($id)
    {
        $a = explode('-',$id);
        $gradeId = $a[1] ?? $id;

        if (Grades::whereId($gradeId)->update(['is_delete' => 1])) {
            return redirect()->back()->with('success', 'Record successfully removed');
        }

        return redirect()->back()->with('error', 'Record not removed');
    }

    public function delete($id){
        $stream = Grades::findOrFail($id);
        $stream->delete();
        return redirect()->route('grade')->with('success','Deleted successfully.');
    }

}
