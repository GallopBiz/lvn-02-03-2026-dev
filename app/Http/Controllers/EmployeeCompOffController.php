<?php
namespace App\Http\Controllers;

use App\Models\HrmsCompOffRequest;
use App\Models\HrmsEmployee; 
use App\Models\HrmsLeaveType;
use App\Models\HrmsEmployeeLeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeCompOffController extends Controller
{
    // Show all comp off requests
    public function index()
    {
        $requests = HrmsCompOffRequest::with(['employee', 'approver', 'leaveType'])->orderBy('start_date', 'desc')->get();
        $employees = HrmsEmployee::all();
        $leaveTypes = HrmsLeaveType::all();

        return view('backend.HRMS.employee_comp_off_requests', compact('requests', 'employees', 'leaveTypes'));
    }

    // Store a new comp off request
    public function store(Request $request)
    {
        $request->validate([
            'employee_id'   => 'required|exists:hrms_employees,id',
            'leave_type_id' => 'required|exists:hrms_leave_types,id',
            'start_date'    => 'required|date',
            'reason'        => 'nullable|string|max:255',
        ]);

        HrmsCompOffRequest::create([
            'employee_id'   => $request->employee_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date'    => $request->start_date,
            'reason'        => $request->reason,
            'status'        => 'pending',
            'approved_by'   => null,
        ]);

        return redirect()->route('compoff.index')->with('success', 'Comp Off request submitted successfully.');
    }

    // Update comp off status (approve/reject)
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $compOff = HrmsCompOffRequest::with('leaveType')->findOrFail($id);

        // Skip if already approved
        if ($compOff->status === 'approved') {
            return redirect()->route('compoff.index')->with('info', 'Comp Off request already approved.');
        }

        $compOff->status = $request->status;
        $compOff->approved_by = Auth::id() ?? 1;
        $compOff->save();

        if ($request->status === 'approved') {
            $employeeId = $compOff->employee_id;
            $leaveTypeId = $compOff->leave_type_id;
            $requestedDate = $compOff->start_date;

            // ✅ Check if a Comp Off has already been approved on the same date for this employee
            $existingCompOff = HrmsCompOffRequest::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveTypeId)
                ->where('start_date', $requestedDate)
                ->where('status', 'approved')
                ->where('id', '!=', $compOff->id)
                ->exists();

            if (!$existingCompOff) {
                $balanceRecord = HrmsEmployeeLeaveBalance::where('employee_id', $employeeId)
                    ->where('leave_type_id', $leaveTypeId)
                    ->first();

                if ($balanceRecord) {
                    $balanceRecord->balance += 1;
                    $balanceRecord->save();
                } else {
                    HrmsEmployeeLeaveBalance::create([
                        'employee_id'   => $employeeId,
                        'leave_type_id' => $leaveTypeId,
                        'balance'       => 1,
                    ]);
                }
            }
        }

        return redirect()->route('compoff.index')->with('success', 'Comp Off request status updated.');
    }

    // Delete a comp off request
    public function destroy($id)
    {
        $compOff = HrmsCompOffRequest::findOrFail($id);
        $compOff->delete();

        return redirect()->route('compoff.index')->with('success', 'Comp Off request deleted.');
    }
	// Show comp off requests for admin approval
	public function employeeCompOffApprovals()
	{
		$stream = HrmsCompOffRequest::with(['employee', 'leaveType'])->orderBy('start_date', 'desc')->get();
		return view('backend.HRMS.employee_comp_off_approve', compact('stream'));
	}

    // Staff: Show only logged-in staff's comp off requests
    public function staffIndex(Request $request)
    {
        $user = auth()->guard('staff')->user();
        $employeeId = $user->employee_id;
        $employees = HrmsEmployee::where('id', $employeeId)->get();
        $leaveTypes = HrmsLeaveType::all();
        $stream_master = null;
        $stream = HrmsCompOffRequest::where('employee_id', $employeeId)
            ->orderByDesc('id')
            ->paginate(20);
        return view('backend.HRMS.compoffrequests_staff', compact('stream', 'employees', 'leaveTypes', 'stream_master'));
    }

    // Staff: Store comp off request
    public function staffStore(Request $request)
    {
        $user = auth()->guard('staff')->user();
        $employeeId = $user->employee_id;
        $request->validate([
            'start_date'    => 'required|date',
            'leave_type_id' => 'required|integer|exists:hrms_leave_types,id',
            'reason'        => 'nullable|string|max:255',
        ]);

        HrmsCompOffRequest::create([
            'employee_id'   => $employeeId,
            'leave_type_id' => $request->input('leave_type_id'),
            'start_date'    => $request->input('start_date'),
            'reason'        => $request->input('reason'),
            'status'        => 'Pending',
            'approved_by'   => null,
        ]);

        return redirect()->route('compoffrequests.staff')->with('success', 'Comp Off request has been created successfully.');
    }
}
