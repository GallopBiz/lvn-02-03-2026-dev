<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommanModel;
use App\Models\Grade;
use DB;


class GreadingMasterController extends Controller
{
    public function index(){
        $stream = Grade::where('is_delete',0)->get();
        $studentclasses = DB::table('classes')->select('class_name')->get();
        // return view('backend.AcademicsModules.greadingmaster');
        return view('backend.AcademicsModules.greadingmaster', compact('stream', 'studentclasses'));
    }


    // public function create(Request $request){
    //     Grade::create($request->post());
    //     return redirect()->route('greadingmaster')->with('success','  greading has been created successfully.');
    // }


    public function create(Request $request)
    {
        $selectedOptions = [];
        
        foreach (['op1', 'op2', 'op3'] as $option) {
            if ($request->has($option)) {
                $selectedOptions[] = $option;
            }
        }
    
        $applicableTo = implode(',', $selectedOptions);
        
        // Create a new record in the database
        $data = $request->post();
        $data['applicable'] = $applicableTo;
        Grade::create($data);
    
        return redirect()->route('greadingmaster')->with('success', 'greading has been created successfully.');
    }
    








    public function view($id){
        $stream_master = Grade::whereId($id)->get();
        $stream = Grade::orderBy('id','desc')->paginate(5);
        // return $stream_master;
        return view('backend.AcademicsModules.greadingmaster', compact('stream_master','stream'));
    }

    public function store(Request $request)
    {
        $selectedOptions = [];
        
        foreach (['op1', 'op2', 'op3'] as $option) {
            if ($request->has($option)) {
                $selectedOptions[] = $option;
            }
        }
        
        $applicableTo = implode(',', $selectedOptions);
        
        $isElective = $request->has('applicable') ? 'Yes' : 'No';
    
        $data = [
            'grading_name' => $request->grading_name,
            'applicable' => $applicableTo,
            'min_per' => $request->min_per,
            'max_per' => $request->max_per,
            'grade' => $request->grade,
            'groups' => $request->groups,
        ];
    
        if ($request->has('id')) {
            Grade::whereId($request->id)->update($data);
        } else {
            Grade::create($data);
        }
    
        return redirect()->route('greadingmaster')->with('success', 'greading has been Updated successfully.');
    }
    
    
    public function grade_master_delete($id)
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
        $stream = Grade::findOrFail($id);
        $stream->delete();
        return redirect()->route('greadingmaster')->with('success','Deleted successfully.');
    }






}