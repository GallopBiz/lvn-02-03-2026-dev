<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\HrmsDepartment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    // public function index(){
    //     return view('backend.AcademicsModules.stream');
    // }

    public function index(){
        $stream = HrmsDepartment::all();
        return view('backend.HRMS.department', compact('stream'));
    }

    
    public function create(Request $request){
        HrmsDepartment::create($request->post());
        return redirect()->route('department')->with('success','  Department has been created successfully.');
    }

    public function view($id){
        // $stream_master = HrmsDepartment::whereId($id)->get();        
        $stream_master = HrmsDepartment::where('id',$id)->get();
        // $stream = HrmsDepartment::orderBy('DepartmentID','desc')->paginate(5);
        $stream = HrmsDepartment::get();
        return view('backend.HRMS.department', compact('stream_master','stream'));
    }

    public function store(Request $request){
        $data = [
            'department_name' => $request->department_name
            
        ];
        // HrmsDepartment::whereId($request->id)->update($data);
        HrmsDepartment::where('id',$request->id)->update($data);
        return redirect()->route('department')->with('success','department has been Updated successfully.');
    }

    public function department_delete($id)
    {
        try {
            // Find the department by ID
            $department = HrmsDepartment::findOrFail($id);
            
            // Perform soft delete
            $department->delete();
            
            // Redirect with success message
            return redirect()->back()->with('success', 'Record successfully removed');
        } catch (\Exception $e) {
            // Handle exceptions (e.g., department not found)
            return redirect()->back()->with('error', 'Record not removed');
        }
        // $a = explode('-',$id);
        // $b = $a[1];        
        // $c = $a[0];
        // $delete_resp = CommanModel::soft_delete($c,['DepartmentID'=>$b]);
        // if($delete_resp=='TRUE'){
        //     return redirect()->back()->with('success', 'Record successfully removed');
        // }elseif($delete_resp=='FALSE'){
        //     return redirect()->back()->with('error', 'Record not removed');
        // }
    }

    public function delete($id){
        $stream = HrmsDepartment::findOrFail($id);
        $stream->delete();
        return redirect()->route('department')->with('success','Deleted successfully.');
    }

}
