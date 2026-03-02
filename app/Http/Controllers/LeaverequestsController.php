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
use App\Models\HrmsPayroll;
use App\Http\Controllers\Controller;
use App\Models\HrmsEmployeeAttendance;
use App\Models\HrmsHoliday;
use Illuminate\Http\Request;
use App\Models\HrmsShift;
use App\Models\Holidays;
use App\Models\HrmsAttendanceLock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SendLeaveNotificationJob;
use App\Mail\LeaveAppliedNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class LeaverequestsController extends Controller
{
    public function index(){
        $stream = HrmsLeaveRequest::all();
        $employees = HrmsEmployee::all();
        $leaveTypes = HrmsLeaveType::all();
        return view('backend.HRMS.leaverequests', compact('stream','employees','leaveTypes'));
    }

    public function getLeaveTypesByEmployee(Request $request)
    {
        $employeeId = $request->employee_id;
        $employee = HrmsEmployee::where('id', $employeeId)->first();
        $leaveAllocated = HrmsLeaveStaffAllocation::where('hrms_staff_type_id', $employee->staff_type_id)->get();
        $leaveAllocatedIds = $leaveAllocated->pluck('leave_type_id')->toArray();
        $leaveTypes = HrmsLeaveType::whereIn('id', $leaveAllocatedIds)->get();

        foreach ($leaveTypes as $leaveType) {
            $employeeLeaveBalance = HrmsEmployeeLeaveBalance::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveType->id)
                ->first();

            if (!is_null($employeeLeaveBalance)) {
                $balance = rtrim(rtrim(number_format($employeeLeaveBalance->balance, 1, '.', ''), '0'), '.');
                $leaveType->name = $leaveType->name . ' (' . $balance . ' available)';
            }
        }

        return response()->json($leaveTypes);
    }

    public function create(Request $request)
    {
        Log::info('Leave request initiated.', $request->all());

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'leave_type_id' => 'required|integer|exists:hrms_leave_types,id',
            'employee_id' => 'required|integer|exists:hrms_employees,id',
            'reason' => 'required|string',
            'half_day_type' => 'nullable|in:first,second',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed.', $validator->errors()->toArray());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $employeeId = $request->input('employee_id');
        $leaveTypeId = $request->input('leave_type_id');

        Carbon::setWeekendDays([Carbon::SUNDAY]);
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        $employee = HrmsEmployee::find($employeeId);

        // Confirmation date restriction ONLY for Earn Leave (id 4) and Medical Leave (id 3)
        if ($employee && $employee->confirmation_date && in_array($leaveTypeId, [3, 4])) {
            $confirmationDate = Carbon::parse($employee->confirmation_date);
            if ($startDate->lte($confirmationDate)) {
                Log::warning('Leave start date is before or on confirmation date.', [
                    'confirmation_date' => $confirmationDate->toDateString(),
                    'start_date' => $startDate->toDateString(),
                    'leave_type_id' => $leaveTypeId
                ]);
                return back()->with('error', 'You can only apply for this leave after your confirmation date: ' . $confirmationDate->format('d-m-Y'));
            }
        }

        $isHalfDay = $request->has('is_half_day') ? 1 : 0;

        Log::info('Parsed dates.', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'is_half_day' => $isHalfDay,
            'half_day_type' => $request->input('half_day_type')
        ]);

        if ($isHalfDay && !$startDate->equalTo($endDate)) {
            Log::error('Half-day leave requested for multiple days.');
            return back()->with('error', 'Half day leave is only allowed for a single day.');
        }

        $daysRequested = $isHalfDay ? 0.5 : ($startDate->diffInWeekdays($endDate) + 1);
        Log::info('Calculated days requested.', ['days_requested' => $daysRequested]);

        $leaveBalance = HrmsEmployeeLeaveBalance::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->first();

        if (!$leaveBalance || $leaveBalance->balance < $daysRequested) {
            Log::warning('Insufficient leave balance.', [
                'available_balance' => optional($leaveBalance)->balance,
                'required' => $daysRequested
            ]);
            return back()->with('error', 'Insufficient leave balance.');
        }

        $today = now();
        $leaveType = HrmsLeaveType::find($leaveTypeId);

        if ($leaveType->apply_before_days) {
            $minApplyDate = $today->copy()->addDays($leaveType->apply_before_days);
            if ($startDate->lt($minApplyDate)) {
                Log::warning('Leave applied too late.', [
                    'required_days_before' => $leaveType->apply_before_days,
                    'min_apply_date' => $minApplyDate->toDateString()
                ]);
                return redirect()->route('leaverequests')->with('error', "You must apply for this leave at least {$leaveType->apply_before_days} days in advance.");
            }
        }

        if (!$leaveType->allow_backdate && $startDate->lt($today)) {
            Log::warning('Backdated leave application not allowed.');
            return redirect()->route('leaverequests')->with('error', "Backdate leave applications are not allowed for {$leaveType->name}.");
        }

        // Deduct balance
        $leaveBalance->balance -= $daysRequested;
        $leaveBalance->save();
        Log::info('Leave balance updated.', ['new_balance' => $leaveBalance->balance]);

        // Save leave request
        HrmsLeaveRequest::create([
            'employee_id' => $employeeId,
            'leave_type_id' => $leaveTypeId,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'reason' => $request->input('reason'),
            'is_half_day' => $isHalfDay,
            'half_day_type' => $request->input('half_day_type'),
        ]);

        Log::info('Leave request created successfully.');

        $emp = HrmsEmployee::find($employeeId);
        $leaveDetails = [
            'employee_name' => $emp->first_name . ' ' . $emp->last_name,
            'employee_email' => $emp->email,
            'leave_type' => $leaveType->name,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'reason' => $request->input('reason'),
            'action' => 'Created',
        ];

        Log::info('Sending leave notification email.', $leaveDetails);
        // Mail::to($leaveDetails['employee_email'])->send(new LeaveAppliedNotification($leaveDetails));

        return redirect()->route('leaverequests')->with('success', 'Leave request has been created successfully.');
    }

    public function view($id){
        $stream_master = HrmsLeaveRequest::where('id',$id)->first();
        $stream = HrmsLeaveRequest::orderBy('id','desc')->paginate(5); 
        $employees = HrmsEmployee::all();

        $employee=HrmsEmployee::where('id', $stream_master->employee_id)->first();
        $leaveAllocated=HrmsLeaveStaffAllocation::where('hrms_staff_type_id', $employee->staff_type_id)->get();
        $leaveAllocatedIds = $leaveAllocated->pluck('leave_type_id')->toArray();
        $leaveTypes = HrmsLeaveType::whereIn('id', $leaveAllocatedIds)->get();
        foreach ($leaveTypes as $leaveType) {
            $employeeLeaveBalance=HrmsEmployeeLeaveBalance::where('employee_id', $stream_master->employee_id)->where('leave_type_id', $leaveType->id)->first();
            if(!is_null($employeeLeaveBalance)){
                $leaveType->name = $leaveType->name.'('.$employeeLeaveBalance->balance.' available)';
            }
        }
        return view('backend.HRMS.leaverequests', compact('stream_master','stream','leaveTypes','employees'));
    }

    public function store(Request $request){
        $leaveRequest = HrmsLeaveRequest::findOrFail($request->id);

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'leave_type_id' => 'required|integer|exists:hrms_leave_types,id',
            'reason' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $employeeId = $leaveRequest->employee_id;
        $leaveTypeId = $request->input('leave_type_id');

        Carbon::setWeekendDays([Carbon::SUNDAY]);
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        $employee = HrmsEmployee::find($employeeId);

        // Confirmation date restriction for update (Earn Leave and Medical Leave only)
        if ($employee && $employee->confirmation_date && in_array($leaveTypeId, [3, 4])) {
            $confirmationDate = Carbon::parse($employee->confirmation_date);
            if ($startDate->lte($confirmationDate)) {
                return back()->with('error', 'You can only apply for this leave after your confirmation date: ' . $confirmationDate->format('d-m-Y'));
            }
        }

        $isHalfDay = $request->has('is_half_day') ? 1 : 0;
        $daysRequested = $isHalfDay ? 0.5 : ($startDate->diffInWeekdays($endDate) + 1);

        $leaveBalance = HrmsEmployeeLeaveBalance::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->first();

        if (!$leaveBalance) {
            return back()->with('error', 'Leave balance not found.');
        }

        $originalDays = $leaveRequest->is_half_day ? 0.5 : (Carbon::parse($leaveRequest->start_date)
            ->diffInWeekdays(Carbon::parse($leaveRequest->end_date)) + 1);

        $adjustedBalance = $leaveBalance->balance + $originalDays - $daysRequested;

        if ($adjustedBalance < 0) {
            return back()->with('error', 'Insufficient leave balance.');
        }

        $leaveType = HrmsLeaveType::find($leaveTypeId);
        $today = now();

        if ($leaveType->apply_before_days) {
            $minApplyDate = $today->copy()->addDays($leaveType->apply_before_days);
            if ($startDate->lt($minApplyDate)) {
                return redirect()->route('leaverequests')->with('error', "You must apply for this leave at least {$leaveType->apply_before_days} days in advance.");
            }
        }

        if (!$leaveType->allow_backdate && $startDate->lt($today)) {
            return redirect()->route('leaverequests')->with('error', "Backdate leave applications are not allowed for {$leaveType->name}.");
        }

        $leaveBalance->balance = $adjustedBalance;
        $leaveBalance->save();

        $leaveRequest->update([
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'leave_type_id' => $leaveTypeId,
            'reason' => $request->input('reason'),
            'is_half_day' => $isHalfDay,
            'half_day_type' => $request->input('half_day_type'),
        ]);

        $emp=HrmsEmployee::find($leaveRequest->employee_id);
        $leaveDetails = [
            'employee_name' => $emp->first_name.' '.$emp->last_name,
            'employee_email' => $emp->email,
            'leave_type' => $leaveType->name,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'reason' => $request->input('reason'),
            'action' => 'updated',
        ];

        Mail::to($leaveDetails['employee_email'])->send(new LeaveAppliedNotification($leaveDetails));

        return redirect()->route('leaverequests')->with('success', 'Leave request updated successfully.');
    }

    public function leaverequests_delete($id)
    {       
        $delete_resp = CommanModel::soft_delete('leaverequests',['LeaveRequestID'=>$id]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function delete($id){
        $stream = HrmsLeaveRequest::findOrFail($id);
        $stream->delete();
        return redirect()->route('leaverequests')->with('success','Deleted successfully.');
    }
	
	public function leaveReport(Request $request)
	{
		/* ---------- Date Range ---------- */
		$fromDate = $request->filled('from_date')
			? Carbon::parse($request->from_date)->startOfMonth()
			: Carbon::now()->startOfYear();

		$toDate = $request->filled('to_date')
			? Carbon::parse($request->to_date)->endOfMonth()
			: Carbon::now()->endOfYear();

		$employeeFilter = $request->employee_id;

		/* ---------- Employees ---------- */
		$employees = HrmsEmployee::with('biometricDetail')
			->when($employeeFilter, fn ($q) => $q->where('id', $employeeFilter))
			->orderBy('first_name')
			->get();

		/* ---------- Keep existing blade compatible ---------- */
		$leaveTypes = HrmsLeaveType::orderBy('name')->get(); // 👈 IMPORTANT

		/* ---------- Month Headers ---------- */
		$months = [];
		$cursor = $fromDate->copy()->startOfMonth();

		while ($cursor <= $toDate) {
			$months[$cursor->format('Y-m')] = $cursor->format('M Y');
			$cursor->addMonth();
		}

		/* ---------- Payroll Data ---------- */
		$payrolls = HrmsPayroll::whereBetween(
				DB::raw("STR_TO_DATE(CONCAT(year,'-',month,'-01'), '%Y-%m-%d')"),
				[$fromDate, $toDate]
			)
			->when($employeeFilter, fn ($q) => $q->where('employee_id', $employeeFilter))
			->get();

		/* ---------- Matrix Init ---------- */
		$matrix = [];
		foreach ($employees as $emp) {
			foreach ($months as $ym => $label) {
				$matrix[$emp->id][$ym] = 0;
			}
		}

		/* ---------- Fill Matrix ---------- */
		foreach ($payrolls as $row) {
			$ym = sprintf('%04d-%02d', $row->year, $row->month);

			$matrix[$row->employee_id][$ym] =
				($row->approved_leaves ?? 0)
				+ ($row->unapproved_leaves ?? 0)
				+ ($row->lwp_days ?? 0);
		}

		return view('backend.HRMS.employee_leave_report', compact(
			'employees',
			'leaveTypes',   // 👈 keep blade happy
			'months',
			'matrix',
			'fromDate',
			'toDate',
			'employeeFilter'
		));
	}

	public function exportCsv(Request $request)
	{
		$fromDate = $request->filled('from_date')
			? Carbon::parse($request->from_date)->startOfMonth()
			: Carbon::now()->startOfYear();

		$toDate = $request->filled('to_date')
			? Carbon::parse($request->to_date)->endOfMonth()
			: Carbon::now()->endOfMonth();

		$employeeFilter  = $request->employee_id;
		$leaveTypeFilter = $request->leave_type_id;

		// Employees
		$employeesQuery = HrmsEmployee::with('biometricDetail')->orderBy('first_name');
		if ($employeeFilter) {
			$employeesQuery->where('id', $employeeFilter);
		}
		$employees = $employeesQuery->get();

		// Month headers
		$months = [];
		$cursor = $fromDate->copy()->startOfMonth();
		while ($cursor <= $toDate) {
			$months[$cursor->format('Y-m')] = $cursor->format('M Y');
			$cursor->addMonth();
		}

		// Leaves
		$leavesQuery = HrmsLeaveRequest::with('leaveType')
			->where('status', 'approved')
			->whereDate('start_date', '<=', $toDate)
			->whereDate('end_date', '>=', $fromDate);

		if ($employeeFilter) {
			$leavesQuery->where('employee_id', $employeeFilter);
		}
		if ($leaveTypeFilter) {
			$leavesQuery->where('leave_type_id', $leaveTypeFilter);
		}
		$leaves = $leavesQuery->get();

		// Initialize matrix
		$matrix = [];
		foreach ($employees as $emp) {
			foreach ($months as $ym => $label) {
				$matrix[$emp->id][$ym] = 0;
			}
		}

		// Calculate leaves month-wise
		foreach ($leaves as $leave) {
			$start = Carbon::parse($leave->start_date)->lt($fromDate)
				? $fromDate
				: Carbon::parse($leave->start_date);

			$end = Carbon::parse($leave->end_date)->gt($toDate)
				? $toDate
				: Carbon::parse($leave->end_date);

			$period = $start->daysUntil($end->copy()->addDay());

			foreach ($period as $day) {
				$ym = $day->format('Y-m');
				if (isset($matrix[$leave->employee_id][$ym])) {
					$matrix[$leave->employee_id][$ym] += $leave->is_half_day ? 0.5 : 1;
				}
			}
		}

		$fileName = 'employee_leave_report_' . now()->format('Y_m_d_H_i_s') . '.csv';
		$headers = [
			'Content-Type' => 'text/csv',
			'Content-Disposition' => "attachment; filename={$fileName}",
		];

		$callback = function() use ($employees, $months, $matrix) {
			$file = fopen('php://output', 'w');

			// Header row
			$header = array_merge(['Employee', 'ESS Code'], array_values($months));
			fputcsv($file, $header);

			// Data rows
			foreach ($employees as $emp) {
				$row = [
					$emp->first_name . ' ' . $emp->last_name,
					optional($emp->biometricDetail)->ess_emp_code ?? '-'
				];
				foreach ($months as $ym => $label) {
					$row[] = $matrix[$emp->id][$ym] ?? 0;
				}
				fputcsv($file, $row);
			}

			fclose($file);
		};

		return response()->stream($callback, 200, $headers);
	}

	public function employeeLeaveReportDateWise(Request $request)
{
    $fromDate = $request->filled('from_date')
        ? Carbon::parse($request->from_date)->startOfDay()
        : Carbon::now()->startOfMonth();

    $toDate = $request->filled('to_date')
        ? Carbon::parse($request->to_date)->endOfDay()
        : Carbon::now()->endOfMonth();

    $employeeId = $request->employee_id;

    // Get locked months
    $lockedMonths = HrmsAttendanceLock::where('is_locked', 1)
        ->select('year', 'month')
        ->get()
        ->map(fn($l) => sprintf('%04d-%02d', $l->year, $l->month))
        ->toArray();

    // Employees
    $employees = HrmsEmployee::with('biometricDetails')
        ->orderBy('first_name')
        ->when($employeeId, fn($q) => $q->where('id', $employeeId))
        ->get();

    // Holidays
    $holidayDates = [];
    $holidays = Holidays::whereDate('HolidayStartDate', '<=', $toDate)
        ->whereDate('HolidayEndDate', '>=', $fromDate)
        ->get();
    foreach ($holidays as $holiday) {
        foreach (Carbon::parse($holiday->HolidayStartDate)->toPeriod(Carbon::parse($holiday->HolidayEndDate)) as $day) {
            $holidayDates[] = $day->format('Y-m-d');
        }
    }

    $rows = [];

    foreach ($employees as $emp) {
        $essCode = optional($emp->biometricDetails)->ess_emp_code;
        if (!$essCode) continue;

        // Fetch leaves and attendance per employee to avoid loading unnecessary data
        $leaveRequests = HrmsLeaveRequest::with('leaveType')
            ->where('employee_id', $emp->id)
            ->where('status', 'Approved')
            ->whereDate('start_date', '<=', $toDate)
            ->whereDate('end_date', '>=', $fromDate)
            ->get();

        $attendanceLogs = HrmsEmployeeAttendance::where('ess_emp_code', $essCode)
            ->whereBetween('log_date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])
            ->get()
            ->keyBy(fn($a) => $a->log_date);

        $date = $fromDate->copy();
        while ($date->lte($toDate)) {
            $dateStr = $date->format('Y-m-d');

            // Skip Sunday & Holiday
            if ($date->isSunday() || in_array($dateStr, $holidayDates)) {
                $date->addDay();
                continue;
            }

            // Skip unlocked months
            if (!in_array($date->format('Y-m'), $lockedMonths)) {
                $date->addDay();
                continue;
            }

            $attendance = $attendanceLogs->get($dateStr);

            if ($attendance && ($attendance->in_time || $attendance->out_time)) {
                $date->addDay();
                continue;
            }

            // Check approved leave
            $leave = $leaveRequests->first(fn($l) => $dateStr >= $l->start_date && $dateStr <= $l->end_date);

            if ($leave) {
                $rows[] = [
                    'date'       => $dateStr,
                    'employee'   => $emp->first_name.' '.$emp->last_name,
                    'leave_type' => optional($leave->leaveType)->name ?? 'Leave',
                    'day_value'  => $leave->is_half_day ? 0.5 : 1,
                    'status'     => 'Approved'
                ];
            } else {
                $rows[] = [
                    'date'       => $dateStr,
                    'employee'   => $emp->first_name.' '.$emp->last_name,
                    'leave_type' => 'LWP',
                    'day_value'  => 1,
                    'status'     => 'Unapproved'
                ];
            }

            $date->addDay();
        }
    }

    return view(
        'backend.HRMS.employee_date_wise_leave_report',
        compact('rows', 'employees', 'fromDate', 'toDate', 'employeeId')
    );
}


public function exportDateWiseLeaveCsv(Request $request)
{
    $fromDate = $request->filled('from_date')
        ? Carbon::parse($request->from_date)->startOfDay()
        : Carbon::now()->startOfMonth();

    $toDate = $request->filled('to_date')
        ? Carbon::parse($request->to_date)->endOfDay()
        : Carbon::now()->endOfMonth();

    $employeeFilter = $request->employee_id;

    /* 🔒 Locked Months */
    $lockedMonths = HrmsAttendanceLock::where('is_locked', 1)
        ->select('year', 'month')
        ->get()
        ->map(fn ($l) => sprintf('%04d-%02d', $l->year, $l->month))
        ->toArray();

    /* Employees */
    $employeesQuery = HrmsEmployee::with('biometricDetails')->orderBy('first_name');
    if ($employeeFilter) {
        $employeesQuery->where('id', $employeeFilter);
    }
    $employees = $employeesQuery->get();

    /* Holidays */
    $holidayDates = [];
    $holidays = Holidays::whereDate('HolidayStartDate', '<=', $toDate)
        ->whereDate('HolidayEndDate', '>=', $fromDate)
        ->get();

    foreach ($holidays as $holiday) {
        foreach (
            Carbon::parse($holiday->HolidayStartDate)
                ->toPeriod(Carbon::parse($holiday->HolidayEndDate))
            as $day
        ) {
            $holidayDates[] = $day->format('Y-m-d');
        }
    }

    /* Approved Leaves */
    $leaves = HrmsLeaveRequest::with('leaveType')
        ->where('status', 'Approved')
        ->whereDate('start_date', '<=', $toDate)
        ->whereDate('end_date', '>=', $fromDate)
        ->when($employeeFilter, fn ($q) => $q->where('employee_id', $employeeFilter))
        ->get();

    /* Attendance Logs */
    $attendanceLogs = HrmsEmployeeAttendance::whereBetween(
            'log_date',
            [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')]
        )
        ->get()
        ->groupBy(fn ($a) => $a->ess_emp_code . '_' . $a->log_date);

    /* Prepare Rows */
    $rows = [];

    foreach ($employees as $emp) {

        $essCode = optional($emp->biometricDetails)->ess_emp_code;
        if (!$essCode) {
            continue;
        }

        $date = $fromDate->copy();

        while ($date->lte($toDate)) {

            $dateStr   = $date->format('Y-m-d');
            $yearMonth = $date->format('Y-m');

            // Sunday / Holiday
            if ($date->isSunday() || in_array($dateStr, $holidayDates)) {
                $date->addDay();
                continue;
            }

            // 🔒 Only locked months
            if (!in_array($yearMonth, $lockedMonths)) {
                $date->addDay();
                continue;
            }

            // Attendance present
            $attendanceKey = $essCode . '_' . $dateStr;
            $attendance = $attendanceLogs->get($attendanceKey)?->first();

            if ($attendance && ($attendance->in_time || $attendance->out_time)) {
                $date->addDay();
                continue;
            }

            // Approved Leave
            $leave = $leaves->first(function ($l) use ($emp, $dateStr) {
                return $l->employee_id == $emp->id
                    && $dateStr >= $l->start_date
                    && $dateStr <= $l->end_date;
            });

            if ($leave) {
                $rows[] = [
                    'date'       => $dateStr,
                    'employee'   => $emp->first_name . ' ' . $emp->last_name,
                    'ess_code'   => $essCode,
                    'leave_type' => optional($leave->leaveType)->name ?? 'Leave',
                    'day_value'  => $leave->is_half_day ? 0.5 : 1,
                    'status'     => 'Approved',
                ];
            } else {
                $rows[] = [
                    'date'       => $dateStr,
                    'employee'   => $emp->first_name . ' ' . $emp->last_name,
                    'ess_code'   => $essCode,
                    'leave_type' => 'LWP',
                    'day_value'  => 1,
                    'status'     => 'Unapproved',
                ];
            }

            $date->addDay();
        }
    }

    /* CSV Output */
    $fileName = 'employee_datewise_leave_report_' . now()->format('Y_m_d_H_i_s') . '.csv';

    $headers = [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => "attachment; filename={$fileName}",
    ];

    $callback = function () use ($rows) {
        $file = fopen('php://output', 'w');

        // Header
        fputcsv($file, [
            'Date',
            'Employee Name',
            'ESS Code',
            'Leave Type',
            'Day Value',
            'Status',
        ]);

        foreach ($rows as $row) {
            fputcsv($file, [
                $row['date'],
                $row['employee'],
                $row['ess_code'],
                $row['leave_type'],
                $row['day_value'],
                $row['status'],
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}





}
