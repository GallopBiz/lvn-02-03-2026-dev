<?php

namespace App\Http\Controllers;

use App\Models\Leavealocate;
use App\Models\Stafftype;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;

class LeaveAllocation extends Controller
{
    public function index()
    {
        $leaveAllocations = Leavealocate::where('is_delete', 0)->get();
        $staffTypes = Stafftype::where('is_delete', 0)->get();
        $positions = Position::where('is_delete', 0)->get();
        $departments = Department::where('is_delete', 0)->get();

        return view('backend.HRMS.leavealocate', compact('leaveAllocations', 'staffTypes', 'positions', 'departments'));
    }

    // public function create(Request $request)
    // {
    //     // Validation rules
    //     $request->validate([
    //         'Staff_Type_ID' => 'required|integer|in:1,2,3',
    //         'PositionName' => 'required|string|max:255',
    //         'DepartmentName' => 'required|string|max:255',
    //         'leave_name' => 'required|string|max:255',
    //         'short_name' => 'required|string|max:255',
    //         'CL_Allocation' => 'required|numeric|min:0',
    //         'ML_Allocation' => 'required|numeric|min:0',
    //         'EL_Allocation' => 'required|numeric|min:0',
    //         'Max_CL_At_Time' => 'required|numeric|min:0',
    //         'Max_EL_At_Time' => 'required|numeric|min:0',
    //         'Max_ML_At_Time' => 'required|numeric|min:0',
    //         'is_paid' => 'required|boolean',
    //         'is_vacation' => 'nullable|string|in:yes,no', // Optional for create
    //     ]);

    //     $data = $request->all();
    //     $staffType = $request->Staff_Type_ID;
    //     $isVacation = $request->is_vacation === 'yes';

    //     // Apply rules based on staff type and vacation status
    //     if ($staffType == 1) { // Permanent Staff
    //         $this->validatePermanentStaff($data, $isVacation);
    //     } elseif ($staffType == 2) { // Temporary Staff
    //         $this->validateTemporaryStaff($data);
    //     } elseif ($staffType == 3) { // Guest Faculty
    //         $this->validateGuestFaculty($data);
    //     }

    //     Leavealocate::create($data);

    //     return redirect()->route('leave_allocation')->with('success', 'Leave Allocation created successfully.');
    // }






    public function create(Request $request)
    {
        $request->validate([
            'Staff_Type_ID' => 'required|integer',
            'PositionName' => 'required|string|max:255',
            'DepartmentName' => 'required|string|max:255',
            'leave_name' => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
            'CL_Allocation' => 'required|numeric|min:0|max:500',
            'ML_Allocation' => 'required|numeric|min:0|max:500',
            'EL_Allocation' => 'required|numeric|min:0|max:500',
            'Max_CL_At_Time' => 'required|numeric|min:0|max:10',
            'Max_EL_At_Time' => 'required|numeric|min:0|max:10',
            'Max_ML_At_Time' => 'required|numeric|min:0|max:10',
            'is_paid' => 'required|boolean',
            'is_vacation' => 'required|string|in:yes,no',
        ]);
    
        $staffType = $request->Staff_Type_ID;
        $isVacation = $request->is_vacation === 'yes';

        $errors = [];
    
        // Permanent Staff Validation
        if ($staffType == 1) { 
            if ($request->CL_Allocation != 13) {
                $errors[] = 'Permanent staff should have 13 CL Allocation.';
            }
            if ($request->ML_Allocation != 10) {
                $errors[] = 'Permanent staff should have 10 ML Allocation.';
            }
            if ($request->Max_CL_At_Time != 5) {
                $errors[] = 'Permanent staff should be able to take a maximum of 5 CL at a time.';
            }
            if ($isVacation && $request->EL_Allocation != 30) {
                $errors[] = 'Vacation staff should have 30 EL Allocation.';
            }
        }
        // Temporary Staff Validation
        elseif ($staffType == 2) { 
            if ($request->CL_Allocation != 1) {
                $errors[] = 'Temporary staff should have 1 CL Allocation per month.';
            }
            if ($request->Max_CL_At_Time != 3) {
                $errors[] = 'Temporary staff should be able to take a maximum of 3 CL at a time.';
            }
        }
        // Guest Faculty Validation
        elseif ($staffType == 3) { 
            if ($request->CL_Allocation != 0 || $request->ML_Allocation != 0 || $request->EL_Allocation != 0) {
                $errors[] = 'Guest Faculty should have no CL, ML, or EL Allocation.';
            }
        }
    
        if (!empty($errors)) {
            return redirect()->back()->withErrors($errors)->withInput();
        }
    
        // Save the data if validation passes
        $data = $request->all();
        Leavealocate::create($data);
    
        return redirect()->route('leave_allocation')->with('success', 'Leave Allocation created successfully.');
    }

    
    

    public function view($id)
    {
        $leaveAllocation = Leavealocate::findOrFail($id);
        $staffTypes = Stafftype::where('is_delete', 0)->get();
        $positions = Position::where('is_delete', 0)->get();
        $departments = Department::where('is_delete', 0)->get();

        return view('backend.HRMS.leavealocate', compact('leaveAllocation', 'staffTypes', 'positions', 'departments'));
    }

    // public function store(Request $request)
    // {
    //     // Validation rules
    //     $request->validate([
    //         'Staff_Type_ID' => 'required|integer|in:1,2,3',
    //         'PositionName' => 'required|string|max:255',
    //         'DepartmentName' => 'required|string|max:255',
    //         'leave_name' => 'required|string|max:255',
    //         'short_name' => 'required|string|max:255',
    //         'CL_Allocation' => 'required|numeric|min:0',
    //         'ML_Allocation' => 'required|numeric|min:0',
    //         'EL_Allocation' => 'required|numeric|min:0',
    //         'Max_CL_At_Time' => 'required|numeric|min:0',
    //         'Max_EL_At_Time' => 'required|numeric|min:0',
    //         'Max_ML_At_Time' => 'required|numeric|min:0',
    //         'is_paid' => 'required|boolean',
    //         'is_vacation' => 'nullable|string|in:yes,no', // Optional for update
    //     ]);

    //     $leaveAllocation = Leavealocate::findOrFail($request->id);
    //     $data = $request->except('is_vacation');
    //     $data['is_vacation'] = ($request->input('is_vacation') === 'yes');

    //     $staffType = $request->Staff_Type_ID;
    //     $isVacation = $request->is_vacation === 'yes';

    //     // Apply rules based on staff type and vacation status
    //     if ($staffType == 1) { // Permanent Staff
    //         $this->validatePermanentStaff($data, $isVacation);
    //     } elseif ($staffType == 2) { // Temporary Staff
    //         $this->validateTemporaryStaff($data);
    //     } elseif ($staffType == 3) { // Guest Faculty
    //         $this->validateGuestFaculty($data);
    //     }

    //     $leaveAllocation->update($data);

    //     return redirect()->route('leave_allocation')->with('success', 'Leave Allocation updated successfully.');
    // }


    public function store(Request $request)
    {
        $request->validate([
            'Staff_Type_ID' => 'required|integer',
            'PositionName' => 'required|string|max:255',
            'DepartmentName' => 'required|string|max:255',
            'leave_name' => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
            'CL_Allocation' => 'required|numeric|min:0',
            'ML_Allocation' => 'required|numeric|min:0',
            'EL_Allocation' => 'required|numeric|min:0',
            'Max_CL_At_Time' => 'required|numeric|min:0',
            'Max_EL_At_Time' => 'required|numeric|min:0',
            'Max_ML_At_Time' => 'required|numeric|min:0',
            'is_paid' => 'required|boolean',
            'is_vacation' => 'required|string|in:yes,no',
        ]);
    
        $leaveAllocation = Leavealocate::findOrFail($request->id);
        $staffType = $request->Staff_Type_ID;
        $isVacation = $request->is_vacation === 'yes';
    
        // Rules validation
        $errors = [];
    
        if ($staffType == 1) { // Permanent Staff
            if ($request->CL_Allocation != 13) {
                $errors[] = 'Permanent staff should have 13 CL Allocation.';
            }
            if ($request->ML_Allocation != 10) {
                $errors[] = 'Permanent staff should have 10 ML Allocation.';
            }
            if ($request->Max_CL_At_Time != 5) {
                $errors[] = 'Permanent staff should be able to take a maximum of 5 CL at a time.';
            }
            if ($isVacation && $request->EL_Allocation != 30) {
                $errors[] = 'Vacation staff should have 30 EL Allocation.';
            }
        } elseif ($staffType == 2) { // Temporary Staff
            if ($request->CL_Allocation != 1) {
                $errors[] = 'Temporary staff should have 1 CL Allocation per month.';
            }
            if ($request->Max_CL_At_Time != 3) {
                $errors[] = 'Temporary staff should be able to take a maximum of 3 CL at a time.';
            }
        } elseif ($staffType == 3) { // Guest Faculty
            if ($request->CL_Allocation != 0 || $request->ML_Allocation != 0 || $request->EL_Allocation != 0) {
                $errors[] = 'Guest Faculty should have no CL, ML, or EL Allocation.';
            }
        }
    
        if (!empty($errors)) {
            return redirect()->back()->withErrors($errors)->withInput();
        }
    
        $data = $request->except('is_vacation');
        $data['is_vacation'] = $isVacation;
    
        $leaveAllocation->update($data);
    
        return redirect()->route('leave_allocation')->with('success', 'Leave Allocation updated successfully.');
    }
    




    public function leave_allocation_delete($id)
    {
        $leaveAllocation = Leavealocate::findOrFail($id);
        $leaveAllocation->update(['is_delete' => 1]);

        return redirect()->route('leave_allocation')->with('success', 'Leave Allocation marked as deleted.');
    }

    public function delete($id)
    {
        $leaveAllocation = Leavealocate::findOrFail($id);
        $leaveAllocation->delete();

        return redirect()->route('leave_allocation')->with('success', 'Leave Allocation permanently deleted.');
    }

    private function validatePermanentStaff(&$data, $isVacation)
    {
        if ($data['CL_Allocation'] != 13) {
            abort(400, 'CL Allocation for Permanent Staff should be 13.');
        }
        if ($data['ML_Allocation'] != 10) {
            abort(400, 'ML Allocation for Permanent Staff should be 10.');
        }
        if ($data['Max_CL_At_Time'] != 5) {
            abort(400, 'Max CL at a time for Permanent Staff should be 5.');
        }
        $data['EL_Allocation'] = $isVacation ? 30 : 0;
    }

    private function validateTemporaryStaff(&$data)
    {
        if ($data['CL_Allocation'] != 1) {
            abort(400, 'CL Allocation for Temporary Staff should be 1 per month.');
        }
        if ($data['Max_CL_At_Time'] != 3) {
            abort(400, 'Max CL at a time for Temporary Staff should be 3.');
        }
    }

    private function validateGuestFaculty(&$data)
    {
        $data['CL_Allocation'] = 0;
        $data['ML_Allocation'] = 0;
        $data['EL_Allocation'] = 0;
        $data['Max_CL_At_Time'] = 0;
        $data['Max_EL_At_Time'] = 0;
        $data['Max_ML_At_Time'] = 0;
    }
}