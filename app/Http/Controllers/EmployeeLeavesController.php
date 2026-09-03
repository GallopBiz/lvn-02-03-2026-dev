<?php

namespace App\Http\Controllers;

use App\Models\CommanModel;
use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeLeaveBalance;
use App\Models\HrmsLeaveRequest;
use App\Models\HrmsLeaveStaffAllocation;
use App\Models\HrmsLeaveType;
use App\Models\Leaverequests;
use App\Models\Employees;
use App\Models\HrmsLeaveDatewiseEntry;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SendLeaveNotificationJob;
use App\Mail\LeaveAppliedNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;

class EmployeeLeavesController extends Controller
{
		public function index(Request $request)
	{
		$query = HrmsLeaveRequest::with(['employee', 'leaveType']);

		// Employee wise filter
		if ($request->filled('employee_id')) {
			$query->where('employee_id', $request->employee_id);
		}

		// Leave status wise filter
		if ($request->filled('status')) {
			$query->where('status', $request->status);
		}

		// From date
		if ($request->filled('from_date')) {
            $query->whereDate('start_date', '>=', $request->from_date);
		}

		// To date
		if ($request->filled('to_date')) {
			$query->whereDate('end_date', '<=', $request->to_date);
		}

		// Search (employee name / reason)
		if ($request->filled('search')) {
			$search = $request->search;
			$query->where(function ($q) use ($search) {
				$q->whereHas('employee', function ($qq) use ($search) {
					$qq->where('first_name', 'like', "%$search%")
					   ->orWhere('last_name', 'like', "%$search%");
				})
				->orWhere('reason', 'like', "%$search%");
			});
		}

		$results = $query->orderBy('id', 'desc')->get();
		/* Manual pagination (same pattern as Daily Collection) */
		$page = Paginator::resolveCurrentPage('page');
		$perPage = 20;
		$total = $results->count();

		$paginatedResults = $results
			->slice(($page - 1) * $perPage, $perPage)
			->values();

		$stream = new LengthAwarePaginator(
			$paginatedResults,
			$total,
			$perPage,
			$page,
			[
				'path' => Paginator::resolveCurrentPath(),
				'pageName' => 'page',
			]
		);

		$employees = HrmsEmployee::all();
		$leaveTypes = HrmsLeaveType::all();

		return view(
			'backend.HRMS.employeeleaverequests',
			compact('stream', 'employees', 'leaveTypes')
		);
	}

    public function export(Request $request)
    {
        $query = HrmsLeaveRequest::with(['employee', 'leaveType']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('start_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('end_date', '<=', $request->to_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($qq) use ($search) {
                    $qq->where('first_name', 'like', "%$search%")
                       ->orWhere('last_name', 'like', "%$search%");
                })
                ->orWhere('reason', 'like', "%$search%");
            });
        }

        $leaveRequests = $query->orderBy('id', 'desc')->get();
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=Employee_Leave_Requests.csv',
        ];

        return response()->stream(function () use ($leaveRequests) {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Sr.', 'Employee Name', 'Start Date', 'End Date', 'Total Leaves',
                'Leave Type', 'Half Day Type', 'Reason', 'Status',
            ]);

            foreach ($leaveRequests as $index => $leaveRequest) {
                $totalLeaves = $leaveRequest->is_half_day
                    ? 0.5
                    : Carbon::parse($leaveRequest->start_date)->diffInDays(Carbon::parse($leaveRequest->end_date)) + 1;
                $employeeName = trim(($leaveRequest->employee->first_name ?? '') . ' ' . ($leaveRequest->employee->last_name ?? ''));

                fputcsv($output, [
                    $index + 1,
                    $employeeName,
                    $leaveRequest->start_date,
                    $leaveRequest->end_date,
                    $totalLeaves,
                    $leaveRequest->leaveType->name ?? 'N/A',
                    $leaveRequest->is_half_day ? ucfirst($leaveRequest->half_day_type) . ' Half' : '-',
                    $leaveRequest->reason,
                    $leaveRequest->status,
                ]);
            }

            fclose($output);
        }, 200, $headers);
    }


    public function getLeaveTypesByEmployee(Request $request) {
        $employeeId = $request->employee_id;
        $employee = HrmsEmployee::where('id', $employeeId)->first();
        $leaveAllocated = HrmsLeaveStaffAllocation::where('hrms_staff_type_id', $employee->staff_type_id)->get();
        $leaveAllocatedIds = $leaveAllocated->pluck('leave_type_id')->toArray();
        $leaveTypes = HrmsLeaveType::whereIn('id', $leaveAllocatedIds)->get();
        foreach ($leaveTypes as $leaveType) {
            $employeeLeaveBalance = HrmsEmployeeLeaveBalance::where('employee_id', $employeeId)->where('leave_type_id', $leaveType->id)->first();
            $leaveType->name = $leaveType->name.'('.$employeeLeaveBalance->balance.' available)';
        }
        return response()->json($leaveTypes);
    }

    public function view($id) {
        $stream_master = HrmsLeaveRequest::where('id',$id)->first();
        $stream = HrmsLeaveRequest::orderBy('id','desc')->paginate(5); 
        $employees = HrmsEmployee::all();

        $employee = HrmsEmployee::where('id', $stream_master->employee_id)->first();
        $leaveAllocated = HrmsLeaveStaffAllocation::where('hrms_staff_type_id', $employee->staff_type_id)->get();
        $leaveAllocatedIds = $leaveAllocated->pluck('leave_type_id')->toArray();
        $leaveTypes = HrmsLeaveType::whereIn('id', $leaveAllocatedIds)->get();
        foreach ($leaveTypes as $leaveType) {
            $employeeLeaveBalance = HrmsEmployeeLeaveBalance::where('employee_id', $stream_master->employee_id)->where('leave_type_id', $leaveType->id)->first();
            $leaveType->name = $leaveType->name.'('.$employeeLeaveBalance->balance.' available)';
        }
        return view('backend.HRMS.leaverequests', compact('stream_master','stream','leaveTypes','employees'));
    }

	public function updateLeaveStatus(Request $request, $id)
{
    $mainRequest = HrmsLeaveRequest::findOrFail($id);
    $newStatus = $request->input('status');

    if (!in_array($newStatus, ['approved', 'rejected'])) {
        return back()->with('error', 'Invalid status.');
    }

    /*
    |--------------------------------------------------------------------------
    | LEAVE ROLLBACK – ONLY ONCE
    |--------------------------------------------------------------------------
    */
    if (
        $newStatus === 'rejected' &&
        $mainRequest->rollback_done == 0
    ) {
        $period = CarbonPeriod::create(
            Carbon::parse($mainRequest->start_date),
            Carbon::parse($mainRequest->end_date)
        );

        foreach ($period as $date) {
            $leaveBalance = HrmsEmployeeLeaveBalance::where('employee_id', $mainRequest->employee_id)
                ->where('leave_type_id', $mainRequest->leave_type_id)
                ->first();

            if ($leaveBalance) {
                $leaveBalance->balance += ($mainRequest->is_half_day ? 0.5 : 1);
                $leaveBalance->save();
            }
        }

        // mark rollback as completed
        $mainRequest->rollback_done = 1;
    }

    // update status
    $mainRequest->status = $newStatus;
    $mainRequest->save();

    /*
    |--------------------------------------------------------------------------
    | APPROVAL LOGIC – UNCHANGED
    |--------------------------------------------------------------------------
    */
    if ($newStatus === 'approved') {

        $leaveTypesByDate = $request->input('leave_types', []);
        $lwpDates = $request->input('lwp_dates', []);

        foreach ($leaveTypesByDate as $date => $leaveTypeId) {

            $isLwp = in_array($date, $lwpDates);

            HrmsLeaveRequest::create([
                'employee_id' => $mainRequest->employee_id,
                'leave_type_id' => $leaveTypeId,
                'start_date' => $date,
                'end_date' => $date,
                'reason' => $mainRequest->reason,
                'status' => 'approved',
                'is_half_day' => $mainRequest->is_half_day,
            ]);

            if (!$isLwp) {
                $leaveBalance = HrmsEmployeeLeaveBalance::where('employee_id', $mainRequest->employee_id)
                    ->where('leave_type_id', $leaveTypeId)
                    ->first();

                    // Leave deduction at approval commented out to prevent double deduction
                    /*
                    if ($leaveBalance && $leaveBalance->balance > 0) {
                        $leaveBalance->balance -= ($mainRequest->is_half_day ? 0.5 : 1);
                        $leaveBalance->save();
                    }
                    */
            }
        }

        $mainRequest->delete();
    }

    return back()->with('success', 'Leave request processed date-wise.');
}





    public function delete($id){
        $stream = Leaverequests::findOrFail($id);
        $stream->delete();
        return redirect()->route('leaverequests')->with('success','Deleted successfully.');
    }
}
