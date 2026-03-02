<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\HrmsShiftType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShifttypetypeController extends Controller
{
    

    public function index(){
        $stream = HrmsShiftType::all();
        return view('backend.HRMS.shifttype', compact('stream'));
    }


    
    public function create(Request $request){
        
        $validator = Validator::make($request->all(), [
            'shift_type_name' => 'required|string|max:255|unique:hrms_shift_types,shift_type_name',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        HrmsShiftType::create($request->all());

        return redirect()->route('shifttype')->with('success', 'Shift Type has been created successfully.');
    
    }


    // public function create(Request $request)
    // {
    //     $request->validate([
    //         'Type' => 'required|string|max:255',
    //     ]);
    
    //     // Insert only the necessary fields
    //     HrmsShiftType::create([
    //         'Type' => $request->Type,
    //         'is_delete' => 0, // Optional: Default value
    //     ]);
    
    //     return redirect()->route('shifttype')->with('success', 'Shift Type has been created successfully.');
    // }

    


    public function view($id){
        $stream_master = HrmsShiftType::where('id', $id)->firstOrFail();
        $stream = HrmsShiftType::get();
        return view('backend.HRMS.shifttype', compact('stream_master', 'stream'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shift_type_name' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {        
                    // Check for duplicate shift_type_name excluding the current record
                    $exists = HrmsShiftType::where('shift_type_name', $value)
                        ->where('id', '<>', $request->id) // Exclude current record
                        ->exists();        
                    if ($exists) {
                        $fail('A shift with this name already exists.');
                    }
                }
            ],
        ]);
        
        if ($validator->fails()) {        
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $data = [
            'shift_type_name' => $request->shift_type_name,
        ];
    
        // Directly update the record
        HrmsShiftType::where('id', $request->id)->update($data);
    
        return redirect()->route('shifttype')->with('success', 'Shifttype has been updated successfully.');
    }
    
    

    public function shifttype_delete($id){
        try {
            // Find the department by ID
            $shifyType = HrmsShiftType::findOrFail($id);
            
            // Perform soft delete
            $shifyType->delete();
            
            // Redirect with success message
            return redirect()->back()->with('success', 'Record successfully removed');
        } catch (\Exception $e) {
            // Handle exceptions (e.g., department not found)
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete($id){
        $stream = HrmsShiftType::findOrFail($id);
        $stream->delete();
        return redirect()->route('shifttype')->with('success', 'Deleted successfully.');
    }

}
