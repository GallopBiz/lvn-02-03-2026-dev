<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\HrmsPosition;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    

    public function index(){
        $stream = HrmsPosition::all();
        return view('backend.HRMS.position', compact('stream'));
    }

    
    public function create(Request $request){
        HrmsPosition::create($request->post());
        return redirect()->route('position')->with('success','  position has been created successfully.');
    }

    public function view($id){        
        // $stream = HrmsPosition::orderBy('PositionID','desc')->paginate(5);
        $stream_master = HrmsPosition::where('id',$id)->get();        
        $stream = HrmsPosition::get();
        return view('backend.HRMS.position', compact('stream_master','stream'));
    }

    public function store(Request $request){
        $data = [
            'position_name' => $request->position_name
            
        ];
        // HrmsPosition::whereId($request->id)->update($data);
        HrmsPosition::where('id',$request->id)->update($data);

        return redirect()->route('position')->with('success','position has been Updated successfully.');
    }

    public function position_delete($id)
    {
        try {
            // Find the department by ID
            $position = HrmsPosition::findOrFail($id);
            
            // Perform soft delete
            $position->delete();
            
            // Redirect with success message
            return redirect()->back()->with('success', 'Record successfully removed');
        } catch (\Exception $e) {
            // Handle exceptions (e.g., department not found)
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function delete($id){
        $stream = HrmsPosition::findOrFail($id);
        $stream->delete();
        return redirect()->route('position')->with('success','Deleted successfully.');
    }

}
