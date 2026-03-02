<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\HrmsStaffType;
use App\Models\HrmsShiftType;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
 
class StafftypeController extends Controller
{
    

    public function index(){
        $stream = HrmsStaffType::all();
        $shiftTypes = HrmsShiftType::all();
        return view('backend.HRMS.stafftype', compact('stream', 'shiftTypes'));
    }



    // public function up()
    // {
    //     Schema::table('hrms_staff_type', function (Blueprint $table) {
    //         $table->string('shift')->nullable(); // Add this line
    //     });
    // }
    
    // public function down()
    // {
    //     Schema::table('hrms_staff_type', function (Blueprint $table) {
    //         $table->dropColumn('shift');
    //     });
    // }
    


    public function create(Request $request){
         
        $request->validate([
            'staff_type_name' => 'required|string|max:255',
            'shift_type_id' => 'required',
        ]);

        HrmsStaffType::create($request->all());

        return redirect()->route('stafftype')->with('success', 'Staff Type has been created successfully.');
    }

    public function view($id){
        $stream_master = HrmsStaffType::where('id', $id)->firstOrFail();
        $shiftTypes = HrmsShiftType::all();

        $stream = HrmsStaffType::all();
        return view('backend.HRMS.stafftype', compact('stream_master', 'stream' , 'shiftTypes'));
    }

    // public function store(Request $request)
    // {
    //     $data = [
    //         'Type' => $request->Type,
    //         // 'shift' => $request->shift,

    //     ];
    
    //     // Directly update the record
    //     HrmsStaffType::where('Staff_Type_ID', $request->id)->update($data);
    
    //     return redirect()->route('stafftype')->with('success', 'Stafftype has been updated successfully.');
    // }
    

    public function store(Request $request)
{
    $data = [
        'staff_type_name' => $request->staff_type_name,
        'shift_type_id' => $request->shift_type_id, // Ensure the shift value is saved
    ];

    // Directly update the record
    HrmsStaffType::where('id', $request->id)->update($data);

    return redirect()->route('stafftype')->with('success', 'Stafftype has been updated successfully.');
}

    

    public function stafftype_delete($id){
        try {
            // Find the department by ID
            $staff = HrmsStaffType::findOrFail($id);
            if ($staff && $staff->employees) {
                return redirect()->back()->with('error', 'This staff Type is assigned to an employee and cannot be deleted.');
            }
            // Perform soft delete
            $staff->delete();
            
            // Redirect with success message
            return redirect()->back()->with('success', 'Record successfully removed');
        } catch (\Exception $e) {
            // Handle exceptions (e.g., department not found)
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete($id){
        $stream = HrmsStaffType::findOrFail($id);
        $stream->delete();
        return redirect()->route('stafftype')->with('success', 'Deleted successfully.');
    }

}
