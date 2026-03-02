<?php

namespace App\Http\Controllers;

use App\Models\HrmsLeaveType;
use App\Models\Leavealocate;
use App\Models\Stafftype;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class LeaveTypeMaster extends Controller
{
    public function index()
    {
        $leaveTypes = HrmsLeaveType::all();
        return view('backend.HRMS.leavetypes', compact('leaveTypes'));
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'reset_month' => 'required|numeric|min:1|max:12',
            'annual_entitlement' => 'required|numeric|min:0',
            'apply_before_days' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
           
        // Save the data if validation passes
        $data = $request->all();
        HrmsLeaveType::create($data);
    
        return redirect()->route('leave_Types')->with('success', 'Leave Type created successfully.');
    }

    
    

    public function view($id)
    {
        $leaveType = HrmsLeaveType::findOrFail($id);
        $leaveTypes = HrmsLeaveType::all();
        return view('backend.HRMS.leavetypes', compact('leaveType','leaveTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'reset_month' => 'required|numeric|min:1|max:12',
            'annual_entitlement' => 'required|numeric|min:0',
            'apply_before_days' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $leaveType = HrmsLeaveType::findOrFail($request->id);    
        $data = $request->all();
    
        $leaveType->update($data);
    
        return redirect()->route('leave_Types')->with('success', 'Leave Type updated successfully.');
    }
    

    public function delete($id)
    {
        $leaveType = HrmsLeaveType::findOrFail($id);
        $leaveType->delete();

        return redirect()->route('leave_Types')->with('success', 'Leave Type permanently deleted.');
    }

}