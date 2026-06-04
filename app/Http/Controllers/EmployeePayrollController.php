<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\Holidays;
use App\Models\HrmsAttendanceLock;
use App\Models\HrmsBasicDeduction;
use App\Models\HrmsEmployeeDeduction;
use App\Models\HrmsEmployeeDeductionHistory;
use App\Models\HrmsEmployeeFeeEMI;
use App\Models\HrmsLeaveRequest;
use App\Models\HrmsLeaveType;
use App\Models\HrmsPayroll;
use App\Models\HrmsSalary;
use App\Models\HrmsShift;
use App\Models\Salaries;
use App\Models\HrmsEmployee;
use App\Models\Student_registration;
use App\Models\HrmsEmployeeChild;
use App\Models\HrmsEmployeeLoanEmi;
use App\Models\HrmsEmployeeLoan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use function GuzzleHttp\json_decode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\HrmsSalaryReport;
use App\Models\HrmsBankDetail;
use App\Models\HrmsStatutoryInformation;
use App\Models\HrmsPosition;
use App\Models\HrmsEmployeeLeaveBalance;
use App\Models\PayrollAttendanceConfiguration;
use App\Models\PayrollDepartmentAttendanceConfiguration;
use App\Models\PayrollStaffAttendanceConfiguration;
use App\Services\Hrms\ShiftResolver;




class EmployeePayrollController extends Controller
{
	private function isBiometricRequiredForPayrollMonth($month, $staffTypeId = null, $departmentId = null)
	{
		$monthConfiguration = PayrollAttendanceConfiguration::where('month_number', (int) $month)->first();

		if (!$monthConfiguration) {
			return true;
		}

		if ($monthConfiguration->biometric_required) {
			return true;
		}

		if ($staffTypeId) {
			$staffConfiguration = PayrollStaffAttendanceConfiguration::where('staff_type_id', $staffTypeId)->first();
			if ($staffConfiguration && $staffConfiguration->biometric_required) {
				return true;
			}
		}

		if ($departmentId) {
			$departmentConfiguration = PayrollDepartmentAttendanceConfiguration::where('department_id', $departmentId)->first();
			if ($departmentConfiguration && $departmentConfiguration->biometric_required) {
				return true;
			}
		}

		return false;
	}

	private function selectedEmployeesRequireBiometric($employeeIds, $month)
	{
		if (empty($employeeIds)) {
			return true;
		}

		$employees = HrmsEmployee::whereIn('id', $employeeIds)->get(['staff_type_id', 'department_id']);

		foreach ($employees as $employee) {
			if ($this->isBiometricRequiredForPayrollMonth($month, $employee->staff_type_id, $employee->department_id)) {
				return true;
			}
		}

		return false;
	}

	public function GenerateJson(Request $request)
	{
		try {
			$employeeIds = $request->input('employee_ids');
			$month = $request->input('month');
			$year = $request->input('year');
			$skipLateComing = $request->boolean('skip_late_coming');

			$attendanceLockRequired = $this->selectedEmployeesRequireBiometric($employeeIds, $month);

			// Check if attendance is locked when biometric attendance is required.
			if ($attendanceLockRequired && !HrmsAttendanceLock::isLocked($year, $month)) {
				$monthName = Carbon::createFromFormat('m', $month)->format('F');
				return response()->json(['error' => "Attendance must be locked before processing payroll for {$monthName} {$year}."], 403);
			}

			// Check for pending leave requests
			$pendingLeaves = HrmsLeaveRequest::whereIn('employee_id', $employeeIds)
				->where('status', 'pending')
				->with('employee')
				->get();

			if ($pendingLeaves->isNotEmpty()) {
				$employeeDetails = $pendingLeaves->groupBy('employee_id')->map(function ($leaves, $empId) {
					$employee = $leaves->first()->employee;
					return $employee->first_name . ' ' . $employee->last_name . ' (ID: ' . $empId . ')';
				})->implode(', ');

				return response()->json([
					'error' => 'Please approve pending leave requests for the following employees before generating payroll: ' . $employeeDetails
				], 422);
			}

			$sno = 1;

			// Date range for the payroll
			$startDate = Carbon::create($year, $month, 1)->startOfMonth();
			$endDate = Carbon::create($year, $month, 1)->endOfMonth();
			$monthDays = $startDate->daysInMonth;

			// Fetch selected employees with necessary relationships
			$employees = HrmsEmployee::with([
				'salaries' => fn($q) => $q->where('is_active', 1),
				'employeeDeductions' => fn($q) => $q->whereHas('salary', fn($sq) => $sq->where('is_active', 1)),
				'children' => fn($q) => $q->whereHas('salary', fn($sq) => $sq->where('is_active', 1)),
				'loans' => fn($q) => $q->where('is_active', 1),
				'leaveRequest',
				'biometricDetails',
				'staffType',
				'leaveBalances'
			])->whereIn('id', $employeeIds)
			  ->where(fn($q) => $q->where('employee_status', 'active')->orWhereNull('employee_status'))
			  ->get();

			$payrollResults = [];
			foreach ($employees as $employee) {
				try {
					$biometricRequired = $this->isBiometricRequiredForPayrollMonth($month, $employee->staff_type_id, $employee->department_id);
					// ...existing payroll calculation logic from Generate...
					$activeSalary = $employee->salaries->first();
					$basicSalary = $activeSalary->basic_salary * 0.5;
					$da = $basicSalary * (config('payroll.da_percentage', 60) / 100);
					$hra = $basicSalary * (config('payroll.hra_percentage', 40) / 100);
					$grossSalary = $basicSalary + $da + $hra;
					$esicgrossSalary = $basicSalary + $da;
					$PFSalary = round($basicSalary + $da, 2);
					$totalDeductions = 0;
					$deductionArray = [];
					foreach ($employee->employeeDeductions as $deduction) {
						$history = HrmsEmployeeDeductionHistory::where('employee_id', $employee->id)
							->where('employee_salary_id', $activeSalary->id)
							->where('basic_deduction_id', $deduction->basicDeduction->id)
							->where('month', $month)
							->where('year', $year)
							->first();
						$amount = (float) ($history->amount ?? ($deduction->manual_amount ?? $deduction->basicDeduction->amount));
						$totalDeductions += $amount;
						$deductionArray[] = [
							'name' => $deduction->basicDeduction->name,
							'amount' => $amount
						];
					}
					$lwp = 0;
					$EmpAttandanceLog = collect();
					if ($biometricRequired && optional($employee->biometricDetails)->ess_emp_code) {
						$EmpAttandanceLog = DB::table('hrms_employee_attendance')
							->where('ess_emp_code', $employee->biometricDetails->ess_emp_code)
							->whereBetween('log_date', [$startDate, $endDate])
							->get();
					}
					$holidays = Holidays::where(function ($q) use ($startDate, $endDate) {
						$q->whereBetween('HolidayStartDate', [$startDate, $endDate])
							->orWhereBetween('HolidayEndDate', [$startDate, $endDate])
							->orWhere(fn($sq) => $sq->where('HolidayStartDate', '<=', $startDate)
								->where('HolidayEndDate', '>=', $endDate));
					})->get();
					$holidayDates = [];
					foreach ($holidays as $holiday) {
						$period = Carbon::parse($holiday->HolidayStartDate)->toPeriod(Carbon::parse($holiday->HolidayEndDate));
						foreach ($period as $day) {
							$holidayDates[] = $day->format('Y-m-d');
						}
					}
					$lateComingCount = 0;
					if ($biometricRequired) {
						foreach ($EmpAttandanceLog as $log) {
							$logDate = Carbon::parse($log->log_date)->format('Y-m-d');
							if (in_array($logDate, $holidayDates)) continue;
							$employeeShift = ShiftResolver::getApplicableShift($employee, $logDate);
							if (!$employeeShift) continue;
							$shiftStart = strtotime($logDate . ' ' . $employeeShift->start_time);
							$inTime = strtotime($logDate . ' ' . $log->in_time);
							if ($inTime > $shiftStart) {
								$lateMinutes = round(($inTime - $shiftStart) / 60, 2);
								if ($lateMinutes > $employeeShift->late_coming_threshold) {
									$lateComingCount++;
								}
							}
						}
					}
					if (!$skipLateComing && $lateComingCount >= 3) {
						$lwp += floor($lateComingCount / 3);
					}
					$unApplyLeave = 0;
					$workingDays = [];
					$currentDate = $startDate->copy();
					while ($currentDate->lte($endDate)) {
						if ($currentDate->dayOfWeek !== Carbon::SUNDAY && !in_array($currentDate->format('Y-m-d'), $holidayDates)) {
							$workingDays[] = $currentDate->format('Y-m-d');
						}
						$currentDate->addDay();
					}
					$attendanceDates = $biometricRequired
						? $EmpAttandanceLog->pluck('log_date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray()
						: $workingDays;
					$leaveDates = [];
					foreach ($employee->leaveRequest as $lr) {
						if ($lr->status === 'Approved') {
							$period = Carbon::parse($lr->start_date)->toPeriod(Carbon::parse($lr->end_date));
							foreach ($period as $d) {
								$leaveDates[$d->format('Y-m-d')] = true;
							}
						}
					}
					$sandwichCoveredDays = 0;
					$isTempStaff = false;
					if (isset($employee->staffType->staff_type_name)) {
						$isTempStaff = stripos($employee->staffType->staff_type_name, 'temp') !== false;
					}
					$clBalance = 0;
					$clLeaveTypeId = 1;
					if ($isTempStaff) {
						$clLeaveTypeId = 5;
					}
					$clLeaveBalance = $employee->leaveBalances->where('leave_type_id', $clLeaveTypeId)->first();
					if ($clLeaveBalance) {
						$clBalance = (int)$clLeaveBalance->balance;
					}
					$allDates = [];
					$dateCursor = $startDate->copy();
					while ($dateCursor->lte($endDate)) {
						$dateStr = $dateCursor->format('Y-m-d');
						if (in_array($dateStr, $attendanceDates)) {
							$allDates[$dateStr] = 'present';
						} elseif (in_array($dateStr, $holidayDates) || $dateCursor->dayOfWeek === Carbon::SUNDAY) {
							$allDates[$dateStr] = 'holiday';
						} elseif (isset($leaveDates[$dateStr])) {
							$allDates[$dateStr] = 'leave';
						} else {
							$allDates[$dateStr] = 'absent';
						}
						$dateCursor->addDay();
					}
					$blocks = [];
					$currentBlock = [];
					foreach ($allDates as $date => $status) {
						if ($status !== 'present') {
							$currentBlock[$date] = $status;
						} else {
							if (count($currentBlock) > 0) {
								$blocks[] = $currentBlock;
								$currentBlock = [];
							}
						}
					}
					if (count($currentBlock) > 0) {
						$blocks[] = $currentBlock;
					}
					foreach ($blocks as $block) {
						$blockDates = array_keys($block);
						$statuses = array_values($block);
						$startStatus = $block[$blockDates[0]];
						$endStatus = $block[$blockDates[count($blockDates)-1]];
						$sandwichDays = count($blockDates);
						$holidayAdjacent = false;
						for ($i = 0; $i < count($statuses); $i++) {
							if ($statuses[$i] === 'holiday') {
								$prev = $statuses[$i - 1] ?? null;
								$next = $statuses[$i + 1] ?? null;
								if ($prev === 'leave' && $next === 'leave') {
									$holidayAdjacent = true;
									break;
								}
							}
						}
						if ($sandwichDays > 2 && $startStatus === 'leave' && $endStatus === 'leave' && $holidayAdjacent) {
							if ($isTempStaff) {
								if ($clBalance >= $sandwichDays) {
									$clBalance -= $sandwichDays;
									$sandwichCoveredDays += $sandwichDays;
								} else {
									$unApplyLeave += $sandwichDays;
								}
							}
						} else {
							foreach ($block as $d => $status) {
								if ($status === 'absent') {
									$unApplyLeave++;
								}
							}
						}
					}
					$lwp += $unApplyLeave;
					$workingDaysRatio = ($monthDays - $lwp) / $monthDays;
					$esicSalary = round($esicgrossSalary * $workingDaysRatio, 2);
					$earnBase = $basicSalary + $da + $hra;
					$earnSalary = round($earnBase * $workingDaysRatio, 2);
					if ($esicgrossSalary > 20999) {
						$esic = 0;
					} else {
						$esic = round($esicSalary * (config('payroll.esic_percentage', 0.75) / 100), 2);
					}
					$efpaPercentage = config('payroll.efpa_percentage', 12);
					$efpa = round($PFSalary * ($workingDaysRatio * ($efpaPercentage / 100)), 2);
					if ($efpa > 1800) {
						$efpa = 1800;
					}
					$totalEmiDeduction = 0;
					foreach ($employee->loans as $loan) {
						$emi = HrmsEmployeeLoanEmi::where('loan_id', $loan->id)
							->whereMonth('emi_date', $month)
							->whereYear('emi_date', $year)
							->where('status', 'pending')
							->sum('emi_amount');
						$totalEmiDeduction += $emi;
						HrmsEmployeeLoanEmi::where('loan_id', $loan->id)
							->whereMonth('emi_date', $month)
							->whereYear('emi_date', $year)
							->where('status', 'pending')
							->update(['status' => 'paid']);
					}
					$totalLeaveDeduction = 0;
					$unApplyLeaveDeductionAmount = round($unApplyLeave * ($grossSalary / $monthDays), 2);
					$lateDeductionAmount = 0;
					if (!$skipLateComing) {
						$lateDeductionAmount = round(
							floor($lateComingCount / 3) * ($grossSalary / $monthDays),
							2
						);
					}
					$netSalary = $grossSalary - $totalDeductions - $totalEmiDeduction - $efpa - $esic - $totalLeaveDeduction - $lateDeductionAmount - $unApplyLeaveDeductionAmount;
					$deductionDetails = [
						'UN Apply Leave' => $unApplyLeave,
						'EFPA' => $efpa,
						'ESIC' => $esic,
						'Total Deductions' => $totalDeductions,
						'Total EMI Deductions' => $totalEmiDeduction,
						'Late Deduction Amount' => $lateDeductionAmount,
						'UnApply Leave Deduction Amount'=> $unApplyLeaveDeductionAmount
					];
					$sandwichApplied = ($sandwichCoveredDays > 0) ? 'Yes' : 'No';
					$payrollResults[] = [
						'employee_id' => $employee->id,
						'employee_name' => $employee->first_name . ' ' . $employee->last_name,
						'employee_code' => optional($employee->biometricDetails)->ess_emp_code,
						'staff_type' => optional($employee->staffType)->staff_type_name,
						'lwp' => $lwp,
						'sandwich_days' => $sandwichCoveredDays ?? 0,
						'sandwich_applied' => $sandwichApplied,
						'gross_salary' => $grossSalary,
						'basic_salary' => $basicSalary,
						'da' => $da,
						'hra' => $hra,
						'earn_salary' => $earnSalary,
						'deductions' => $deductionDetails,
						'net_salary' => $netSalary,
						'month' => $month,
						'year' => $year
					];
				} catch (\Exception $e) {
					\Log::error("Error processing Employee ID {$employee->id}: {$e->getMessage()}", ['stack' => $e->getTraceAsString()]);
				}
			}
			return response()->json([
				'success' => true,
				'payroll' => $payrollResults,
				'month' => $month,
				'year' => $year
			]);
		} catch (\Exception $e) {
			\Log::error('Error generating payroll JSON: ' . $e->getMessage());
			return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
		}
	}

    public function index()
    {
        // $stream = HrmsEmployee::with('salaries', 'deductions', 'loans', 'children')->get();
        $stream = HrmsEmployee::with([
            'salaries' => function ($query) {
                $query->where('is_active', 1); // Fetch only active salaries
            },
            'employeeDeductions' => function ($query) {
                $query->whereHas('salary', function ($subQuery) {
                    $subQuery->where('is_active', 1); // Filter deductions linked to active salaries
                });
            },
            'children' => function ($query) {
                $query->whereHas('salary', function ($subQuery) {
                    $subQuery->where('is_active', 1); // Filter children linked to active salaries
                });
            },
            'loans' => function ($query) {
                $query->where('is_active', 1); // Fetch only active loans
            },
        ])
		->where('employee_status', 'active') // Only fetch active employees
		->get();
        return view('backend.HRMS.payroll', compact('stream'));
    }

    public function Generate(Request $request)
	{
		try {
			$employeeIds = $request->input('employee_ids');
			$month = $request->input('month');
			$year = $request->input('year');
			$skipLateComing = $request->boolean('skip_late_coming');


			$attendanceLockRequired = $this->selectedEmployeesRequireBiometric($employeeIds, $month);

			// Check if attendance is locked when biometric attendance is required.
			if ($attendanceLockRequired && !HrmsAttendanceLock::isLocked($year, $month)) {
				$monthName = Carbon::createFromFormat('m', $month)->format('F');
				return response()->json(['error' => "Attendance must be locked before processing payroll for {$monthName} {$year}."], 403);
			}

			// Check for pending leave requests
			$pendingLeaves = HrmsLeaveRequest::whereIn('employee_id', $employeeIds)
				->where('status', 'pending')
				->with('employee')
				->get();

			if ($pendingLeaves->isNotEmpty()) {
				$employeeDetails = $pendingLeaves->groupBy('employee_id')->map(function ($leaves, $empId) {
					$employee = $leaves->first()->employee;
					return $employee->first_name . ' ' . $employee->last_name . ' (ID: ' . $empId . ')';
				})->implode(', ');

				return response()->json([
					'error' => 'Please approve pending leave requests for the following employees before generating payroll: ' . $employeeDetails
				], 422);
			}

			$sno = 1;
			$csvData = [];

			// Date range for the payroll
			$startDate = Carbon::create($year, $month, 1)->startOfMonth();
			$endDate = Carbon::create($year, $month, 1)->endOfMonth();
			$monthDays = $startDate->daysInMonth;

			// Fetch selected employees with necessary relationships
			$employees = HrmsEmployee::with([
				'salaries' => fn($q) => $q->where('is_active', 1),
				'employeeDeductions' => fn($q) => $q->whereHas('salary', fn($sq) => $sq->where('is_active', 1)),
				'children' => fn($q) => $q->whereHas('salary', fn($sq) => $sq->where('is_active', 1)),
				'loans' => fn($q) => $q->where('is_active', 1),
				'leaveRequest',
				'biometricDetails',
			'staffType',
			'leaveBalances'
			])->whereIn('id', $employeeIds)
			  ->where(fn($q) => $q->where('employee_status', 'active')->orWhereNull('employee_status'))
			  ->get();

			foreach ($employees as $employee) {
				try {
					$biometricRequired = $this->isBiometricRequiredForPayrollMonth($month, $employee->staff_type_id, $employee->department_id);
					\Log::info("Processing Employee ID: {$employee->id}, Name: {$employee->first_name} {$employee->last_name}");

					// --- Gross Salary Calculation ---
					$activeSalary = $employee->salaries->first();
					$basicSalary = $activeSalary->basic_salary * 0.5;
					$da = $basicSalary * (config('payroll.da_percentage', 60) / 100);
					$hra = $basicSalary * (config('payroll.hra_percentage', 40) / 100);
					$grossSalary = $basicSalary + $da + $hra;
					$esicgrossSalary = $basicSalary + $da;
					$PFSalary = round($basicSalary + $da, 2);
					\Log::info("Gross Salary: $grossSalary, Basic: $basicSalary, DA: $da, HRA: $hra, PF Salary: $PFSalary");

					// --- Deductions Calculation ---
					$totalDeductions = 0;
					$deductionArray = [];
					foreach ($employee->employeeDeductions as $deduction) {
						$history = HrmsEmployeeDeductionHistory::where('employee_id', $employee->id)
							->where('employee_salary_id', $activeSalary->id)
							->where('basic_deduction_id', $deduction->basicDeduction->id)
							->where('month', $month)
							->where('year', $year)
							->first();

					$amount = (float) ($history->amount ?? ($deduction->manual_amount ?? $deduction->basicDeduction->amount));
					$totalDeductions += $amount;
					$deductionArray[] = [
						'name' => $deduction->basicDeduction->name,
						'amount' => $amount
					];
					
					
					\Log::info("Deduction: {$deduction->basicDeduction->name}, Amount: $amount");

					}
					\Log::info("Total Deductions: $totalDeductions");

					// --- Attendance and LWP Calculation ---
					$lwp = 0;
					$EmpAttandanceLog = collect();
					if ($biometricRequired && optional($employee->biometricDetails)->ess_emp_code) {
						$EmpAttandanceLog = DB::table('hrms_employee_attendance')
							->where('ess_emp_code', $employee->biometricDetails->ess_emp_code)
							->whereBetween('log_date', [$startDate, $endDate])
							->get();
					}

					$holidays = Holidays::where(function ($q) use ($startDate, $endDate) {
						$q->whereBetween('HolidayStartDate', [$startDate, $endDate])
							->orWhereBetween('HolidayEndDate', [$startDate, $endDate])
							->orWhere(fn($sq) => $sq->where('HolidayStartDate', '<=', $startDate)
								->where('HolidayEndDate', '>=', $endDate));
					})->get();

					$holidayDates = [];
					foreach ($holidays as $holiday) {
						$period = Carbon::parse($holiday->HolidayStartDate)->toPeriod(Carbon::parse($holiday->HolidayEndDate));
						foreach ($period as $day) {
							$holidayDates[] = $day->format('Y-m-d');
						}
					}

					$lateComingCount = 0;

					if ($biometricRequired) {
						foreach ($EmpAttandanceLog as $log) {
							$logDate = Carbon::parse($log->log_date)->format('Y-m-d');
							if (in_array($logDate, $holidayDates)) continue;
							$employeeShift = ShiftResolver::getApplicableShift($employee, $logDate);
							if (!$employeeShift) continue;

							$shiftStart = strtotime($logDate . ' ' . $employeeShift->start_time);
							$inTime = strtotime($logDate . ' ' . $log->in_time);
							if ($inTime > $shiftStart) {
								$lateMinutes = round(($inTime - $shiftStart) / 60, 2);
								if ($lateMinutes > $employeeShift->late_coming_threshold) {
									$lateComingCount++;
									\Log::info("Late Entry on $logDate: $lateMinutes minutes.");
								}
							}
						}
					}

					if (!$skipLateComing && $lateComingCount >= 3) {
						\Log::info("Late Coming Count: $lateComingCount, LWP added: " . floor($lateComingCount / 3));
						$lwp += floor($lateComingCount / 3);
					}


					// Remove approve/reject logic. Only collect all leave dates (regardless of approval status)
					$unApplyLeave = 0;
					$workingDays = [];
					$currentDate = $startDate->copy();
					while ($currentDate->lte($endDate)) {
						if ($currentDate->dayOfWeek !== Carbon::SUNDAY && !in_array($currentDate->format('Y-m-d'), $holidayDates)) {
							$workingDays[] = $currentDate->format('Y-m-d');
						}
						$currentDate->addDay();
					}
					\Log::info('Working Days: ' . implode(', ', $workingDays));

					$attendanceDates = $biometricRequired
						? $EmpAttandanceLog->pluck('log_date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray()
						: $workingDays;
					\Log::info('Attendance Dates: ' . implode(', ', $attendanceDates));
					$missingDays = array_diff($workingDays, $attendanceDates);
					\Log::info('Missing Days: ' . implode(', ', $missingDays));

					$leaveDates = [];
					foreach ($employee->leaveRequest as $lr) {
						if ($lr->status === 'Approved') {
							$period = Carbon::parse($lr->start_date)->toPeriod(Carbon::parse($lr->end_date));
							foreach ($period as $d) {
								$leaveDates[$d->format('Y-m-d')] = true;
							}
						}
					}
					\Log::info('All Leave Dates: ' . implode(', ', array_keys($leaveDates)));

					// --- Sandwich Rule: scan for continuous blocks of absence (including holidays/weekends in between) ---
					$sandwichCoveredDays = 0;
					$sandwichApplied = false;
					$isTempStaff = false;
					if (isset($employee->staffType->staff_type_name)) {

						$isTempStaff = stripos($employee->staffType->staff_type_name, 'temp') !== false;
					}
					\Log::info('Staff Type: ' . ($isTempStaff ? 'Temporary' : 'Permanent'));
				// Initialize CL balance (fetch from leave balance relationship)
				$clBalance = 0;
				$clLeaveTypeId = 1; // Default to permanent staff
				if ($isTempStaff) {
					$clLeaveTypeId = 5; // Temporary staff
				}
				$clLeaveBalance = $employee->leaveBalances->where('leave_type_id', $clLeaveTypeId)->first();
				if ($clLeaveBalance) {
					$clBalance = (int)$clLeaveBalance->balance;
				}
					\Log::info("CL Balance for Employee ID {$employee->id}: $clBalance");

					// Build a day-by-day status array for the month
					$allDates = [];
					$dateCursor = $startDate->copy();
					while ($dateCursor->lte($endDate)) {
						$dateStr = $dateCursor->format('Y-m-d');
						if (in_array($dateStr, $attendanceDates)) {
							$allDates[$dateStr] = 'present';
						} elseif (in_array($dateStr, $holidayDates) || $dateCursor->dayOfWeek === Carbon::SUNDAY) {
							$allDates[$dateStr] = 'holiday';
						} elseif (isset($leaveDates[$dateStr])) {
							$allDates[$dateStr] = 'leave';
						} else {
							$allDates[$dateStr] = 'absent';
						}
						$dateCursor->addDay();
					}

					// Find all blocks of consecutive non-present days
					$blocks = [];
					$currentBlock = [];
					foreach ($allDates as $date => $status) {
						if ($status !== 'present') {
							$currentBlock[$date] = $status;
						} else {
							if (count($currentBlock) > 0) {
								$blocks[] = $currentBlock;
								$currentBlock = [];
							}
						}
					}
					if (count($currentBlock) > 0) {
						$blocks[] = $currentBlock;
					}
					foreach ($blocks as $block) {
						$blockDates = array_keys($block);
						$statuses = array_values($block);
						$blockStr = implode(', ', $blockDates);
						$startStatus = $block[$blockDates[0]];
						$endStatus = $block[$blockDates[count($blockDates)-1]];
						$sandwichDays = count($blockDates);
						\Log::info("Checking block: $blockStr | Statuses: " . implode(', ', $statuses));
						// Sandwich rule: require a holiday/weekend inside the block that is directly flanked
						// by leave (previous and next day both 'leave'). Only then apply sandwich.
						$holidayAdjacent = false;
						for ($i = 0; $i < count($statuses); $i++) {
							if ($statuses[$i] === 'holiday') {
								$prev = $statuses[$i - 1] ?? null;
								$next = $statuses[$i + 1] ?? null;
								if ($prev === 'leave' && $next === 'leave') {
									$holidayAdjacent = true;
									break;
								}
							}
						}
						if ($sandwichDays > 2 && $startStatus === 'leave' && $endStatus === 'leave' && $holidayAdjacent) {
							\Log::info("Sandwich Rule Triggered: Block $blockStr (Days: $sandwichDays). Holiday inside directly flanked by leaves.");
							// All days in block (leave, holiday, absent) are treated as leave
							$leaveCount = 0; $holidayCount = 0; $absentCount = 0;
							foreach ($block as $d => $status) {
								if ($status === 'leave') $leaveCount++;
								if ($status === 'holiday') $holidayCount++;
								if ($status === 'absent') $absentCount++;
							}
							\Log::info("Block breakdown: Leaves=$leaveCount, Holidays/Weekends=$holidayCount, Absents=$absentCount");
							// Create approved leave request for sandwich-covered days (only for temporary employees)
							if ($isTempStaff) {
								// First check if all sandwich days are already approved
								$blockDates = array_keys($block);
								$allDaysApproved = true;
								foreach ($blockDates as $bd) {
									$date = Carbon::parse($bd)->startOfDay();
									$isApproved = HrmsLeaveRequest::where('employee_id', $employee->id)
										->where('leave_type_id', 5)
										->where('status', 'approved')
										->whereDate('start_date', '<=', $date)
										->whereDate('end_date', '>=', $date)
										->exists();
									if (!$isApproved) {
										$allDaysApproved = false;
										break;
									}
								}

								if ($allDaysApproved) {
									\Log::info("All sandwich days ($sandwichDays) are already approved for employee {$employee->id}. No CL deduction needed.");
									$sandwichCoveredDays += $sandwichDays;
									$sandwichApplied = true;
								} elseif ($clBalance >= $sandwichDays) {
									\Log::info("CL balance ($clBalance) is sufficient for sandwich days ($sandwichDays). Deducting from leave balance.");
									$clBalance -= $sandwichDays;
									$sandwichCoveredDays += $sandwichDays;
                                    $sandwichApplied = true;
									$blockDates = array_keys($block);
									$clStartDate = Carbon::parse($blockDates[0]);
									$clEndDate = Carbon::parse($blockDates[count($blockDates)-1]);
                                    
									$existingApprovedLeave = HrmsLeaveRequest::where('employee_id', $employee->id)
										->where('leave_type_id', 5)
										->where('start_date', $clStartDate)
										->where('end_date', $clEndDate)
										->where('status', 'approved')
										->first();
                                    
									if (!$existingApprovedLeave) {
										HrmsLeaveRequest::create([
											'employee_id' => $employee->id,
											'leave_type_id' => 5, // CL (Casual Leave)
											'start_date' => $clStartDate,
											'end_date' => $clEndDate,
											'status' => 'approved',
											'reason' => 'Sandwich Rule Applied - Auto Approved'
										]);
										\Log::info("Created approved leave request for CL from {$clStartDate->format('Y-m-d')} to {$clEndDate->format('Y-m-d')} (Temporary Staff)");
									} else {
										\Log::info("Approved leave request already exists for CL from {$clStartDate->format('Y-m-d')} to {$clEndDate->format('Y-m-d')}. Skipping creation.");
									}
                                
								} else {
									$sandwichApplied = true;
									\Log::info("CL balance ($clBalance) is INSUFFICIENT for sandwich days ($sandwichDays). All become LWP.");
									$unApplyLeave += $sandwichDays;
								}
							}else{
								\Log::info("No sandwich application for permanent staff. Block $blockStr treated as unapproved leave if absent, but no automatic approval given.");
							}
							foreach ($block as $d => $status) {
								\Log::info("Sandwich day: $d | Status: $status");
							}
						} else {
							// Normal unapproved leave counting for absents not covered by sandwich
							foreach ($block as $d => $status) {
								if ($status === 'absent') {
									\Log::info("Unapproved leave on $d");
									$unApplyLeave++;
								}
							}
						}
					}


					$lwp += $unApplyLeave;
					\Log::info("LWP: $lwp (Late: $lateComingCount, Unapproved Leave: $unApplyLeave)");

					// --- ESIC and EFPA ---
					// --- ESIC and EFPA ---
					$workingDaysRatio = ($monthDays - $lwp) / $monthDays;
					$esicSalary = round($esicgrossSalary * $workingDaysRatio, 2);
					
					// Earn salary (new, same logic)
					$earnBase = $basicSalary + $da + $hra;
					$earnSalary = round($earnBase * $workingDaysRatio, 2);

					// ESIC logic based on ESIC Gross Salary
					if ($esicgrossSalary > 20999) {
						$esic = 0;
					} else {
						$esic = round($esicSalary * (config('payroll.esic_percentage', 0.75) / 100), 2);
					}

					$efpaPercentage = config('payroll.efpa_percentage', 12);
					$efpa = round($PFSalary * ($workingDaysRatio * ($efpaPercentage / 100)), 2);
					if ($efpa > 1800) {
						$efpa = 1800;
					}

					\Log::info("ESIC: $esic, EFPA: $efpa");


					$efpaPercentage = config('payroll.efpa_percentage', 12);
					$efpa = round($PFSalary * ($workingDaysRatio * ($efpaPercentage / 100)), 2);
					if ($efpa > 1800) $efpa = 1800;

					\Log::info("ESIC: $esic, EFPA: $efpa");

					// --- Loans / Child Deductions ---
					$totalEmiDeduction = 0;
					foreach ($employee->loans as $loan) {
						$emi = HrmsEmployeeLoanEmi::where('loan_id', $loan->id)
							->whereMonth('emi_date', $month)
							->whereYear('emi_date', $year)
							->where('status', 'pending')
							->sum('emi_amount');
						$totalEmiDeduction += $emi;
						HrmsEmployeeLoanEmi::where('loan_id', $loan->id)
							->whereMonth('emi_date', $month)
							->whereYear('emi_date', $year)
							->where('status', 'pending')
							->update(['status' => 'paid']);
					}
					// No leave type deduction, handled by sandwich logic
					$totalLeaveDeduction = 0;

					$unApplyLeaveDeductionAmount = round($unApplyLeave * ($grossSalary / $monthDays), 2);
					$lateDeductionAmount = 0;

					if (!$skipLateComing) {
						$lateDeductionAmount = round(
							floor($lateComingCount / 3) * ($grossSalary / $monthDays),
							2
						);
					}


					// --- Net Salary ---
					$netSalary = $grossSalary - $totalDeductions - $totalEmiDeduction - $efpa - $esic - $totalLeaveDeduction - $lateDeductionAmount - $unApplyLeaveDeductionAmount;

					// Save payroll record
					HrmsPayroll::create([
						'employee_id' => $employee->id,
						'gross_salary' => $grossSalary,
						'basic_salary' => $basicSalary,
						'earn_salary' => $earnSalary,
						'deductions' => $deductionArray,
						'net_salary' => $netSalary,
						'hra' => $hra,     // added
						'da' => $da,       // added
						'epfa' => $efpa,
						'esic' => $esic,
						'late_deduction_amount' => $lateDeductionAmount,
						
// 🔥 Leave Records (includes sandwich-covered days)
							'approved_leaves' => $sandwichCoveredDays,
						'sandwich_leaves' => $sandwichCoveredDays,
						'unapproved_leaves' => $unApplyLeave,
						'lwp_days' => $lwp,
						
						'month' => $month,
						'year' => $year,
						'month_days' => $monthDays,
						'generated_at' => now()
					]);

					// Prepare CSV
					$deductionDetails = [
						'UN Apply Leave' => $unApplyLeave,
						'EFPA' => $efpa,
						'ESIC' => $esic,
						'Total Deductions' => $totalDeductions,
						'Total EMI Deductions' => $totalEmiDeduction,
						'Late Deduction Amount' => $lateDeductionAmount,
						'UnApply Leave Deduction Amount'=> $unApplyLeaveDeductionAmount
					];

					$headerRow = array_merge(
						['S.No', 'Employee ID', 'Employee Name', 'Employee Code', 'Staff Type', 'LWP', 'Sandwich Days', 'Sandwich Applied', 'Gross Salary', 'Basic Salary', 'DA', 'HRA', 'Earn Salary'],
						array_keys($deductionDetails),
						['Net Salary']
					);

					$sandwichAppliedText = $sandwichApplied ? 'Yes' : 'No';
					$csvData[] = array_merge(
						[$sno++, $employee->id, $employee->first_name . ' ' . $employee->last_name, optional($employee->biometricDetails)->ess_emp_code, optional($employee->staffType)->staff_type_name, $lwp, ($sandwichCoveredDays ?? 0), $sandwichAppliedText, $grossSalary, $basicSalary, $da, $hra, $earnSalary],
						array_values($deductionDetails),
						[$netSalary]
					);

				} catch (\Exception $e) {
					\Log::error("Error processing Employee ID {$employee->id}: {$e->getMessage()}", ['stack' => $e->getTraceAsString()]);
				}
			}

			$filename = "payroll_{$month}_{$year}.csv";
			return response()->streamDownload(function () use ($headerRow, $csvData) {
				$file = fopen('php://output', 'w');
				fputcsv($file, $headerRow);
				foreach ($csvData as $row) fputcsv($file, $row);
				fclose($file);
			}, $filename, ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""]);

		} catch (\Exception $e) {
			\Log::error('Error generating payroll CSV: ' . $e->getMessage());
			return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
		}
	}

    public function delete($id)
    {
        $stream = HrmsSalary::findOrFail($id);
        $stream->employeeChilds()->delete();
        $stream->employeeDeductions()->delete();
        $stream->delete();
        return redirect()->route('salaries')->with('success', 'Deleted successfully.');
    }

    public function check(Request $request)
    {
        try {
            $att = $this->getEmployeeAttandance('2024-11-01', '2024-11-30');

            return redirect()->route('employeeloan')->with('success', 'Deleted successfully.');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->route('employeeloan')->with('success', 'Deleted successfully.');
        }
    }
    private function getEmployeeAttandance($fromDate, $toDate)
    {
        $startDate = Carbon::parse($fromDate)->setTime(0, 0, 0);
        $endDate = Carbon::parse($toDate)->setTime(0, 0, 0);
        $serialNumber = 'AEXY211460054';
        $userName = 'dev';
        $userPassword = 'Test@123';
        $strDataList = 'Blank';
        $url = 'http://45.248.190.2:8083/iclock/WebAPIService.asmx?op=GetTransactionsLog';
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema"
            xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
                <GetTransactionsLog xmlns="http://tempuri.org/">
                    <FromDateTime>' . $startDate . '</FromDateTime>
                    <ToDateTime>' . $endDate . '</ToDateTime>
                    <SerialNumber>' . $serialNumber . '</SerialNumber>
                    <UserName>' . $userName . '</UserName>
                    <UserPassword>' . $userPassword . '</UserPassword>
                    <strDataList>' . $strDataList . '</strDataList>
                </GetTransactionsLog>
            </soap:Body>
        </soap:Envelope>';

        $response = Http::withHeaders([
            'Content-Type' => 'text/xml',
        ])->send('POST', $url, [
                    'body' => $xml,
                ]);
        if ($response->failed()) {
            return response()->json(['error' => 'SOAP request failed.'], 500);
        }

        // Parse the response XML
        $xml = simplexml_load_string($response->body());
        $namespaces = $xml->getNamespaces(true);
        $soapBody = $xml->children($namespaces['soap'])->Body;
        $getTransactionsLogResponse = $soapBody->children($namespaces[''])->GetTransactionsLogResponse;
        $getTransactionsLogResult = (string) $getTransactionsLogResponse->GetTransactionsLogResult;
        $strDataList = (string) $getTransactionsLogResponse->strDataList;


        // Split strDataList into individual logs
        $logs = explode("\n", trim($strDataList));
        //empty table first
        DB::table('employee_thumb_attendance')->truncate();
        foreach ($logs as $log) {
            list($userId, $timestamp) = explode("\t", trim($log));
            $logDate = date('Y-m-d', strtotime($timestamp));



            // Check if there's already an entry for this user and date
            $existingLog = DB::table('employee_thumb_attendance')
                ->where('ess_emp_code', $userId)
                ->where('log_date', $logDate)
                ->first();

            if ($existingLog) {
                // Update the existing entry
                //$inTime = min($existingLog->in_time, $timestamp);
                // $outTime = max($existingLog->out_time, $timestamp);
                DB::table('employee_thumb_attendance')
                    ->where('id', $existingLog->id)
                    ->update([
                        'out_time' => $timestamp,
                    ]);
            } else {
                // Insert a new entry
                //$inTime = min($existingLog->in_time, $timestamp);
                // $outTime = max($existingLog->out_time, $timestamp);
                DB::table('employee_thumb_attendance')->insert([
                    'ess_emp_code' => $userId,
                    'log_date' => $logDate,
                    'in_time' => $timestamp,
                    'out_time' => $logDate,
                ]);
            }
        }
    }
	
	public function employeeSalaryReport(Request $request)
	{
		// Convert filters to proper types
		$month = $request->filled('month') ? (int) $request->month : null;
		$year = $request->filled('year') ? (int) $request->year : null;

		// Step 1: Get latest payroll record per employee for each month-year
		$subQuery = HrmsSalaryReport::select(DB::raw('MAX(id) as latest_id'))
			->when($month, fn($q) => $q->where('month', $month))
			->when($year, fn($q) => $q->where('year', $year))
			->groupBy('employee_id', 'month', 'year');

		$latestIds = $subQuery->pluck('latest_id');

		$salaryReports = collect();
		$avgNetSalaries = collect();

		// Step 2: Only fetch records if IDs exist
		if ($latestIds->isNotEmpty()) {
			// Fetch latest salary records
			$query = HrmsSalaryReport::with('employee.biometricDetail')
				->whereIn('id', $latestIds)
				->orderBy('year', 'desc')
				->orderBy('month', 'desc')
				->orderBy('employee_id', 'asc');

			// Step 3: Apply search filter (by name or ESS code)
			if ($request->filled('search')) {
				$query->whereHas('employee', function ($q) use ($request) {
					$q->where('first_name', 'like', '%' . $request->search . '%')
						->orWhere('last_name', 'like', '%' . $request->search . '%')
						->orWhereHas('biometricDetail', function ($q2) use ($request) {
							$q2->where('ess_emp_code', 'like', '%' . $request->search . '%');
						});
				});
			}

			$salaryReports = $query->get();

			// Step 4: Calculate average net salary per employee
			$avgNetSalaries = HrmsSalaryReport::whereIn('id', $latestIds)
				->select('employee_id', DB::raw('AVG(net_salary) as avg_net_salary'))
				->groupBy('employee_id')
				->pluck('avg_net_salary', 'employee_id');
		}

		return view('backend.HRMS.employee_salary_report', compact('salaryReports', 'avgNetSalaries'));
	}




	public function consolidatedSalaryReport(Request $request)
	{
		$query = HrmsSalaryReport::select(
				'month',
				'year',
				DB::raw('SUM(gross_salary) as total_gross'),
				DB::raw('SUM(net_salary) as total_net'),
				DB::raw('MAX(bank_transferred_date) as bank_transferred_date'),
				DB::raw('MAX(disbursement_date) as disbursement_date')
			)
			->groupBy('month', 'year')
			->orderBy('year', 'desc')
			->orderBy('month', 'desc');

		if ($request->filled('month')) {
			$query->where('month', (int) $request->month);
		}

		if ($request->filled('year')) {
			$query->where('year', (int) $request->year);
		}

		$consolidatedReports = $query->get();

		return view('backend.HRMS.consolidated_salary_report', compact('consolidatedReports'));
	}
	
	public function printSalarySlip($id)
	{
			$report = HrmsSalaryReport::with([
			 'employee.position', 
			'employee.biometricDetail',
			'employee.bankDetails',
			'employee.statutoryInformation' // ✅ correct name
		])->findOrFail($id);


		$additionsRaw = $report->additions ?? '{}';
		$deductionsRaw = $report->deductions ?? '{}';

		$additions = is_array($additionsRaw) ? $additionsRaw : json_decode($additionsRaw, true) ?? [];
		$deductions = is_array($deductionsRaw) ? $deductionsRaw : json_decode($deductionsRaw, true) ?? [];

		$monthName = date('F', mktime(0, 0, 0, $report->month, 1));

		return view('backend.HRMS.salary_slip_print', compact('report', 'deductions', 'additions', 'monthName'));
	}


}
