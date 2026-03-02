<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLeave;
use Illuminate\Http\Request;

class EmployeeLeaves extends Controller
{
    // Display all Employee Leaves
    public function index()
    {
        $employeeLeaves = EmployeeLeave::where('is_delete', 0)->get();
        return view('backend.HRMS.employeeleave', compact('employeeLeaves'));
    }

    // Create a new Employee Leave
    public function create(Request $request)
    {
        $request->validate([
            'CL_Balance' => 'required|numeric|min:0',
            'ML_Balance' => 'required|numeric|min:0',
            'EL_Balance' => 'required|numeric|min:0',
        ]);

        EmployeeLeave::create($request->all());

        return redirect()->route('employeeleave')->with('success', 'Employee Leave has been created successfully.');
    }

    // View and Edit a specific Employee Leave
    public function view($id)
    {
        $employeeLeave = EmployeeLeave::findOrFail($id);
        $employeeLeaves = EmployeeLeave::where('is_delete', 0)->get();

        return view('backend.HRMS.employeeleave', compact('employeeLeave', 'employeeLeaves'));
    }

    // Update a specific Employee Leave
    public function store(Request $request)
    {
        $request->validate([
            'CL_Balance' => 'required|numeric|min:0',
            'ML_Balance' => 'required|numeric|min:0',
            'EL_Balance' => 'required|numeric|min:0',
        ]);

        $employeeLeave = EmployeeLeave::findOrFail($request->id);
        $employeeLeave->update($request->all());

        return redirect()->route('employeeleave')->with('success', 'Employee Leave has been updated successfully.');
    }


    
    // Soft delete a specific Employee Leave
    public function employeeleave_delete($id)
    {
        $employeeLeave = EmployeeLeave::findOrFail($id);
        $employeeLeave->update(['is_delete' => 1]);

        return redirect()->back()->with('success', 'Employee Leave has been deleted successfully.');
    }

    // Hard delete a specific Employee Leave
    public function delete($id)
    {
        $employeeLeave = EmployeeLeave::findOrFail($id);
        $employeeLeave->delete();

        return redirect()->route('employeeleave')->with('success', 'Employee Leave has been permanently deleted.');
    }
}
