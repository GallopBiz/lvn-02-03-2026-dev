<?php

namespace App\Http\Controllers;

use App\Models\Stafftype; // For 'staff_type' table
use App\Models\InitialLeave;
use App\Models\Leavealocate; // For 'leave_allocation' table
use Illuminate\Http\Request;

class Empiniticialleave extends Controller
{
    public function index()
    {
        // Fetch staff types and leave allocations
        $staffTypes = Stafftype::where('is_delete', 0)->get(); // Correct table name
        $leaveAllocations = Leavealocate::where('is_delete', 0)->get(); // Correct table name
        $initialLeaves = InitialLeave::where('is_delete', 0)->get();
    
        return view('backend.HRMS.empinicial', compact('staffTypes', 'leaveAllocations', 'initialLeaves'));
    }
    
    public function create(Request $request)
    {
        // Validate input
        $request->validate([
            'staff_name' => 'required|exists:staff_type,Staff_Type_ID', // Correct table name
            'leave_name' => 'required|exists:leave_allocation,Leave_Allocation_ID', // Correct table name
            'total_allotted' => 'required|integer|min:0',
            'assigned_to_employee' => 'required|integer|min:0'
        ]);
    
        // Store the data into the InitialLeave table
        InitialLeave::create([
            'staff_name' => $request->staff_name,
            'leave_name' => $request->leave_name,
            'total_allotted' => $request->total_allotted,
            'assigned_to_employee' => $request->assigned_to_employee,
            'is_delete' => 0
        ]);
    
        return redirect()->route('InitialLeave')->with('success', 'Initial Leave successfully created.');
    }

    public function view($id)
    {
        // Fetch the specific initial leave entry by ID
        $initialLeave = InitialLeave::findOrFail($id);
        $staffTypes = Stafftype::where('is_delete', 0)->get(); // Correct table name
        $leaveNames = Leavealocate::where('is_delete', 0)->get(); // Correct table name
    
        return view('backend.HRMS.empinicial', compact('initialLeave', 'staffTypes', 'leaveNames'));
    }

    public function store(Request $request)
    {
        // Validate the input
        $request->validate([
            'staff_name' => 'required|exists:staff_type,Staff_Type_ID', // Correct table name
            'leave_name' => 'required|exists:leave_allocation,Leave_Allocation_ID', // Correct table name
            'total_allotted' => 'required|integer|min:0',
            'assigned_to_employee' => 'required|integer|min:0'
        ]);
    
        // Store the data
        InitialLeave::create([
            'staff_name' => $request->staff_name,
            'leave_name' => $request->leave_name,
            'total_allotted' => $request->total_allotted,
            'assigned_to_employee' => $request->assigned_to_employee,
            'is_delete' => 0
        ]);
    
        return redirect()->route('InitialLeave')->with('success', 'Leave assigned successfully.');
    }

    public function filterLeaves(Request $request)
    {
        // Validate input
        $request->validate([
            'staff_name' => 'required|exists:staff_type,Staff_Type_ID',
            'leave_name' => 'required|exists:leave_allocation,Leave_Allocation_ID'
        ]);
    
        // Fetch staff types and leave allocations for the form
        $staffTypes = Stafftype::where('is_delete', 0)->get();
        $leaveAllocations = Leavealocate::where('is_delete', 0)->get();
    
        // Fetch filtered leave allocations based on selected staff type and leave name
        $filteredLeaveAllocations = Leavealocate::where('Staff_Type_ID', $request->staff_name)
            ->where('Leave_Allocation_ID', $request->leave_name)
            ->where('is_delete', 0)
            ->get();
    
        // Pass the data to the view
        return view('backend.HRMS.empinicial', compact('staffTypes', 'leaveAllocations', 'filteredLeaveAllocations'))
            ->with('staff_name', $request->staff_name)
            ->with('leave_name', $request->leave_name);
    }
    
    public function saveAssignedLeave(Request $request)
    {
        // Validate input
        $request->validate([
            'leave_id' => 'required|exists:leave_allocation,Leave_Allocation_ID', // Correct table name
            'assigned_leave' => 'required|integer|min:0'
        ]);
    
        $leaveAllocation = Leavealocate::findOrFail($request->leave_id);
    
        // Store assigned leave into InitialLeave table
        InitialLeave::create([
            'staff_name' => $leaveAllocation->Staff_Type_ID,
            'leave_name' => $leaveAllocation->Leave_Allocation_ID,
            'assigned_to_employee' => $request->assigned_leave,
            'total_allotted' => $leaveAllocation->total_leave, // Ensure this column exists
            'is_delete' => 0
        ]);
    
        return response()->json(['success' => true]);
    }
}
