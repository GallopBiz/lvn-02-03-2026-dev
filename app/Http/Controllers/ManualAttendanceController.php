<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeAttendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManualAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $employees = HrmsEmployee::orderBy('first_name')->get();
		$employees = HrmsEmployee::with('biometricDetail')->orderBy('first_name')->get();
        $selectedEmployee = $request->input('employee_id');
        $month = $request->input('month', now()->format('m'));
        $year = $request->input('year', now()->format('Y'));

        $attendanceData = [];
        $startDate = $endDate = null;

        if ($selectedEmployee) {
            $startDate = Carbon::createFromFormat('Y-m', "$year-$month")->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', "$year-$month")->endOfMonth();

            $attendanceData = HrmsEmployeeAttendance::where('employee_id', $selectedEmployee)
                ->whereBetween('log_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get()
                ->keyBy('log_date');
        }

        return view('backend.HRMS.manual_attendance', compact(
            'employees', 'selectedEmployee', 'attendanceData', 'month', 'year', 'startDate', 'endDate'
        ));
    }

    public function update(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $month = $request->input('month');
        $year = $request->input('year');
        $dates = $request->input('dates', []);

        // ✅ Fetch ess_emp_code from biometric details
        $essEmpCode = DB::table('hrms_employee_biometric_details')
            ->where('employee_id', $employeeId)
            ->value('ess_emp_code');

        foreach ($dates as $date => $info) {
            $status = $info['status'] ?? null;
            $inTime = $info['in_time'] ?? null;
            $outTime = $info['out_time'] ?? null;

            // ✅ Skip if nothing is filled
            if (empty($status) && empty($inTime) && empty($outTime)) {
                continue;
            }

            $existing = HrmsEmployeeAttendance::where('employee_id', $employeeId)
                ->whereDate('log_date', $date)
                ->first();

            if ($existing) {
                if ($existing->entry_type === 'biometric') {
                    continue;
                }

                $existing->status = $status;
                $existing->in_time = $inTime;
                $existing->out_time = $outTime;
                $existing->ess_emp_code = $essEmpCode;
                $existing->save();
            } else {
                HrmsEmployeeAttendance::create([
                    'employee_id' => $employeeId,
                    'log_date' => $date,
                    'status' => $status,
                    'in_time' => $inTime,
                    'out_time' => $outTime,
                    'entry_type' => 'manual',
                    'ess_emp_code' => $essEmpCode,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Attendance updated successfully!');
    }
}
