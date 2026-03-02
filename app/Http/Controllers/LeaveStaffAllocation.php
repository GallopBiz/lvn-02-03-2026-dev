<?php

namespace App\Http\Controllers;

use App\Models\HrmsDepartment;
use App\Models\HrmsLeaveStaffAllocation;
use App\Models\HrmsLeaveType;
use App\Models\HrmsPosition;
use App\Models\HrmsStaffType;
use App\Models\Leavealocate;
use App\Models\Stafftype;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class LeaveStaffAllocation extends Controller
{
    public function index()
    {
        $leaveAllocations = HrmsLeaveStaffAllocation::all();
        $staffTypes = HrmsStaffType::all();
        $leaveTypes = HrmsLeaveType::all();

        return view('backend.HRMS.leavestaffallocation', compact('leaveAllocations', 'staffTypes', 'leaveTypes'));
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hrms_staff_type_id' => [
                'required',
                'exists:hrms_staff_type,id'
            ],
            'leave_type_id' => [
                'required',
                'exists:hrms_leave_types,id',
                Rule::unique('hrms_staff_leave_allocation', 'leave_type_id')
                    ->where(function ($query) use ($request) {
                        $query->where('hrms_staff_type_id', $request->input('hrms_staff_type_id'));
                    })
            ],
            'max_allowed' => 'required|numeric|min:0',
            'max_per_instance' => 'required|numeric|min:0',
        ], [
            // Custom error messages
            'leave_type_id.unique' => 'This staff member already has this leave type assigned.',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
    
        // Save the data if validation passes
        $data = $request->all();
        HrmsLeaveStaffAllocation::create($data);
    
        return redirect()->route('leave_staff_allocation')->with('success', 'Leave Allocation created successfully.');
    }

    
    

    public function view($id)
    {
        $leaveAllocation = HrmsLeaveStaffAllocation::findOrFail($id);
        $leaveAllocations=HrmsLeaveStaffAllocation::all();
        $staffTypes = HrmsStaffType::all();
        $leaveTypes = HrmsLeaveType::all();

        return view('backend.HRMS.leavestaffallocation', compact('leaveAllocations','leaveAllocation', 'staffTypes', 'leaveTypes'));
    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hrms_staff_type_id' => [
                'required',
                'exists:hrms_staff_type,id'
            ],
            'leave_type_id' => [
                'required',
                'exists:hrms_leave_types,id',
                Rule::unique('hrms_staff_leave_allocation', 'leave_type_id')
                    ->where(function ($query) use ($request) {
                        $query->where('hrms_staff_type_id', $request->input('hrms_staff_type_id'));
                    })
                    ->ignore($request->id), // Ignore the current record
            ],
            'max_allowed' => 'required|numeric|min:0',
            'max_per_instance' => 'required|numeric|min:0',
        ], [
            // Custom error messages
            'leave_type_id.unique' => 'This staff member already has this leave type assigned.',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        $leaveAllocation = HrmsLeaveStaffAllocation::findOrFail($request->id);    
        $data = $request->all();
    
        $leaveAllocation->update($data);
    
        return redirect()->route('leave_staff_allocation')->with('success', 'Leave Allocation updated successfully.');
    }
    




    public function delete($id)
    {
        $leaveAllocation = HrmsLeaveStaffAllocation::findOrFail($id);
        $leaveAllocation->delete();

        return redirect()->route('leave_staff_allocation')->with('success', 'Leave Allocation permanently deleted.');
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