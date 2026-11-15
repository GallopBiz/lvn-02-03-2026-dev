<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\HrmsDepartment;
use App\Models\HrmsEmployee;
use App\Models\HrmsLeaveRequest;
use App\Models\HrmsLeaveType;
use App\Models\HrmsStaffType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdvanceLeaveReportController extends Controller
{
    /**
     * Display the Advance Leave Report with support for:
     * 1. Detailed Leave Report (individual records)
     * 2. Employee-Wise Monthly Analysis
     * 3. All Employees Monthly Summary
     */
    public function index(Request $request)
    {
        $reportType = $request->input('report_type', 'detailed');
        if (!in_array($reportType, ['detailed', 'employee_monthly', 'all_monthly'], true)) {
            $reportType = 'detailed';
        }

        // Shared dropdown data (Active employees only)
        $employees = HrmsEmployee::with(['biometricDetail', 'staffType', 'department'])
            ->whereNull('deleted_at')
            ->where('employee_status', 'active')
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();

        $staffTypes = HrmsStaffType::whereNull('deleted_at')
            ->orderBy('staff_type_name', 'asc')
            ->get();

        $leaveTypes = HrmsLeaveType::whereNull('deleted_at')
            ->orderBy('name', 'asc')
            ->get();

        $departments = HrmsDepartment::whereNull('deleted_at')
            ->orderBy('department_name', 'asc')
            ->get();

        $sessionYear = $this->resolveSessionYear($request);
        $sessionMonths = $this->resolveSessionMonths($request);

        $isPrint = $request->boolean('print') || $request->get('print') === '1';

        // Filters array for view state retention
        $filters = [
            'report_type' => $reportType,
            'session_year' => $sessionYear,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'employee_id' => $request->employee_id,
            'staff_type_id' => $request->staff_type_id,
            'leave_type_id' => $request->leave_type_id,
            'status' => $request->status,
            'request_date' => $request->request_date,
            'request_from_date' => $request->request_from_date,
            'request_to_date' => $request->request_to_date,
            'search' => $request->search,
            'per_page' => $request->input('per_page', 25),
        ];

        // Dispatch to appropriate report handler
        if ($reportType === 'employee_monthly') {
            return $this->handleEmployeeMonthlyReport($request, $employees, $staffTypes, $leaveTypes, $sessionMonths, $filters, $isPrint);
        }

        if ($reportType === 'all_monthly') {
            return $this->handleAllMonthlyReport($request, $employees, $staffTypes, $leaveTypes, $sessionMonths, $filters, $isPrint);
        }

        // Default: Detailed Leave Report
        return $this->handleDetailedReport($request, $employees, $staffTypes, $leaveTypes, $departments, $sessionMonths, $filters, $isPrint);
    }

    /**
     * Handler for Report Type 1: Detailed Leave Report
     */
    protected function handleDetailedReport(Request $request, $employees, $staffTypes, $leaveTypes, $departments, array $sessionMonths, array $filters, bool $isPrint)
    {
        $query = $this->buildDetailedQuery($request);

        // Calculate summary metrics across all filtered records
        $totalRequests = (clone $query)->count();

        $statsByStatus = (clone $query)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $approvedCount = $statsByStatus['Approved'] ?? 0;
        $pendingCount = $statsByStatus['Pending'] ?? 0;
        $rejectedCount = $statsByStatus['Rejected'] ?? 0;

        $allFilteredLeaves = (clone $query)->select('id', 'start_date', 'end_date', 'is_half_day', 'cl_days_deducted')->get();
        $totalDays = 0;
        foreach ($allFilteredLeaves as $leave) {
            $totalDays += $this->calculateDays($leave);
        }

        $stats = [
            'total_requests' => $totalRequests,
            'approved' => $approvedCount,
            'pending' => $pendingCount,
            'rejected' => $rejectedCount,
            'total_days' => $totalDays,
        ];

        $perPage = $filters['per_page'];
        if ($isPrint || $perPage === 'all') {
            $leaveRequests = $query->orderBy('id', 'desc')->get();
        } else {
            $perPage = in_array((int)$perPage, [10, 25, 50, 100], true) ? (int)$perPage : 25;
            $leaveRequests = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
        }

        foreach ($leaveRequests as $req) {
            $req->total_days = $this->calculateDays($req);
        }

        if ($isPrint) {
            return view('backend.HRMS.advance_leave_report_print', compact(
                'leaveRequests',
                'stats',
                'sessionMonths',
                'filters'
            ));
        }

        return view('backend.HRMS.advance_leave_report', compact(
            'leaveRequests',
            'employees',
            'staffTypes',
            'leaveTypes',
            'departments',
            'sessionMonths',
            'stats',
            'filters'
        ));
    }

    /**
     * Handler for Report Type 2: Employee-Wise Monthly Analysis
     */
    protected function handleEmployeeMonthlyReport(Request $request, $employees, $staffTypes, $leaveTypes, array $sessionMonths, array $filters, bool $isPrint)
    {
        // Select employee (default to first active employee if not specified)
        $selectedEmpId = $request->employee_id ?: optional($employees->first())->id;
        $selectedEmployee = $employees->firstWhere('id', (int)$selectedEmpId);

        $monthlyData = [];
        $typeTotals = [];
        foreach ($leaveTypes as $lt) {
            $typeTotals[$lt->id] = 0;
        }

        $sessionTotalDays = 0;
        $totalApprovedDays = 0;
        $totalPendingDays = 0;
        $totalRejectedDays = 0;
        $totalRequestsCount = 0;

        $employeeLeaves = collect();

        if ($selectedEmployee) {
            $firstMonth = reset($sessionMonths);
            $lastMonth = end($sessionMonths);

            $leavesQuery = HrmsLeaveRequest::with('leaveType')
                ->where('employee_id', $selectedEmployee->id)
                ->whereDate('start_date', '<=', $lastMonth['end'])
                ->whereDate('end_date', '>=', $firstMonth['start']);

            if ($request->filled('leave_type_id')) {
                $leavesQuery->where('leave_type_id', $request->leave_type_id);
            }

            if ($request->filled('status')) {
                $leavesQuery->where('status', $request->status);
            }

            $employeeLeaves = $leavesQuery->orderBy('start_date', 'asc')->get();

            foreach ($sessionMonths as $ym => $monthInfo) {
                $mStart = $monthInfo['start'];
                $mEnd = $monthInfo['end'];

                $monthDays = 0;
                $monthApproved = 0;
                $monthPending = 0;
                $monthRejected = 0;
                $monthRequests = 0;

                $byType = [];
                foreach ($leaveTypes as $lt) {
                    $byType[$lt->id] = 0;
                }

                $overlappingLeaves = [];

                foreach ($employeeLeaves as $leave) {
                    $daysInMonth = $this->calculateDaysInMonth($leave, $mStart, $mEnd);
                    if ($daysInMonth <= 0) {
                        continue;
                    }

                    $monthDays += $daysInMonth;
                    $monthRequests++;
                    $overlappingLeaves[] = $leave;

                    $statusLower = strtolower($leave->status ?: 'pending');
                    if ($statusLower === 'approved') {
                        $monthApproved += $daysInMonth;
                    } elseif ($statusLower === 'rejected') {
                        $monthRejected += $daysInMonth;
                    } else {
                        $monthPending += $daysInMonth;
                    }

                    if (isset($byType[$leave->leave_type_id])) {
                        $byType[$leave->leave_type_id] += $daysInMonth;
                        $typeTotals[$leave->leave_type_id] += $daysInMonth;
                    }
                }

                $monthlyData[$ym] = [
                    'label' => $monthInfo['label'],
                    'short_label' => $monthInfo['short_label'],
                    'start' => $mStart,
                    'end' => $mEnd,
                    'total_days' => $monthDays,
                    'approved_days' => $monthApproved,
                    'pending_days' => $monthPending,
                    'rejected_days' => $monthRejected,
                    'requests_count' => $monthRequests,
                    'by_type' => $byType,
                    'leaves' => $overlappingLeaves,
                ];

                $sessionTotalDays += $monthDays;
                $totalApprovedDays += $monthApproved;
                $totalPendingDays += $monthPending;
                $totalRejectedDays += $monthRejected;
                $totalRequestsCount += $monthRequests;
            }
        }

        $empStats = [
            'total_days' => $sessionTotalDays,
            'approved_days' => $totalApprovedDays,
            'pending_days' => $totalPendingDays,
            'rejected_days' => $totalRejectedDays,
            'requests_count' => $totalRequestsCount,
            'type_totals' => $typeTotals,
        ];

        if ($isPrint) {
            return view('backend.HRMS.advance_leave_report_print', compact(
                'selectedEmployee',
                'monthlyData',
                'empStats',
                'leaveTypes',
                'sessionMonths',
                'employeeLeaves',
                'filters'
            ));
        }

        return view('backend.HRMS.advance_leave_report', compact(
            'employees',
            'staffTypes',
            'leaveTypes',
            'sessionMonths',
            'selectedEmployee',
            'monthlyData',
            'empStats',
            'employeeLeaves',
            'filters'
        ));
    }

    /**
     * Handler for Report Type 3: All Employees Monthly Summary
     */
    protected function handleAllMonthlyReport(Request $request, $employees, $staffTypes, $leaveTypes, array $sessionMonths, array $filters, bool $isPrint)
    {
        $firstMonth = reset($sessionMonths);
        $lastMonth = end($sessionMonths);

        // Filter employee list if staff_type or search applied
        $filteredEmployees = $employees;

        if ($request->filled('staff_type_id')) {
            $filteredEmployees = $filteredEmployees->where('staff_type_id', (int)$request->staff_type_id);
        }

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $filteredEmployees = $filteredEmployees->filter(function ($emp) use ($search) {
                $fullName = strtolower($emp->first_name . ' ' . $emp->last_name);
                $code = strtolower(optional($emp->biometricDetail)->ess_emp_code ?: '');
                return str_contains($fullName, $search) || str_contains($code, $search);
            });
        }

        $empIds = $filteredEmployees->pluck('id')->toArray();

        // Fetch all relevant leaves for these employees in this session window
        $leavesQuery = HrmsLeaveRequest::with('leaveType')
            ->whereIn('employee_id', $empIds)
            ->whereDate('start_date', '<=', $lastMonth['end'])
            ->whereDate('end_date', '>=', $firstMonth['start']);

        if ($request->filled('leave_type_id')) {
            $leavesQuery->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('status')) {
            $leavesQuery->where('status', $request->status);
        }

        $allLeaves = $leavesQuery->get()->groupBy('employee_id');

        $matrix = [];
        $monthTotals = array_fill_keys(array_keys($sessionMonths), 0);
        $grandTotalDays = 0;
        $monthEmployeeBreakdown = [];

        foreach ($sessionMonths as $ym => $m) {
            $monthEmployeeBreakdown[$ym] = [];
        }

        foreach ($filteredEmployees as $emp) {
            $empLeaves = $allLeaves->get($emp->id, collect());
            $rowMonths = [];
            $empTotal = 0;

            foreach ($sessionMonths as $ym => $monthInfo) {
                $monthDays = 0;
                $mStart = $monthInfo['start'];
                $mEnd = $monthInfo['end'];

                foreach ($empLeaves as $leave) {
                    $days = $this->calculateDaysInMonth($leave, $mStart, $mEnd);
                    if ($days > 0) {
                        $monthDays += $days;
                    }
                }

                $rowMonths[$ym] = $monthDays;
                $empTotal += $monthDays;
                $monthTotals[$ym] += $monthDays;
                $grandTotalDays += $monthDays;

                if ($monthDays > 0) {
                    $monthEmployeeBreakdown[$ym][] = [
                        'employee' => $emp,
                        'days' => $monthDays,
                    ];
                }
            }

            $matrix[] = [
                'employee' => $emp,
                'months' => $rowMonths,
                'total' => $empTotal,
            ];
        }

        $allStats = [
            'total_employees' => count($matrix),
            'employees_with_leaves' => count(array_filter($matrix, fn($r) => $r['total'] > 0)),
            'month_totals' => $monthTotals,
            'grand_total_days' => $grandTotalDays,
        ];

        if ($isPrint) {
            return view('backend.HRMS.advance_leave_report_print', compact(
                'matrix',
                'sessionMonths',
                'allStats',
                'monthEmployeeBreakdown',
                'filters'
            ));
        }

        // Paginate employee matrix for web display
        $perPage = $filters['per_page'];
        $totalEmployees = count($matrix);

        if ($perPage === 'all') {
            $paginatedMatrix = new LengthAwarePaginator(
                $matrix,
                $totalEmployees,
                $totalEmployees > 0 ? $totalEmployees : 25,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $perPageInt = in_array((int)$perPage, [10, 25, 50, 100], true) ? (int)$perPage : 25;
            $currentPage = max(1, (int)$request->input('page', 1));
            $currentSlice = array_slice($matrix, ($currentPage - 1) * $perPageInt, $perPageInt);

            $paginatedMatrix = new LengthAwarePaginator(
                $currentSlice,
                $totalEmployees,
                $perPageInt,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        return view('backend.HRMS.advance_leave_report', [
            'employees' => $employees,
            'staffTypes' => $staffTypes,
            'leaveTypes' => $leaveTypes,
            'sessionMonths' => $sessionMonths,
            'matrix' => $paginatedMatrix,
            'allStats' => $allStats,
            'monthEmployeeBreakdown' => $monthEmployeeBreakdown,
            'filters' => $filters,
        ]);
    }

    /**
     * Export CSV according to selected report type and filters.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $reportType = $request->input('report_type', 'detailed');

        if ($reportType === 'employee_monthly') {
            return $this->exportEmployeeMonthlyCsv($request);
        }

        if ($reportType === 'all_monthly') {
            return $this->exportAllMonthlyCsv($request);
        }

        return $this->exportDetailedCsv($request);
    }

    /**
     * CSV Export for Detailed Leave Report
     */
    protected function exportDetailedCsv(Request $request): StreamedResponse
    {
        $query = $this->buildDetailedQuery($request);
        $records = $query->orderBy('id', 'desc')->get();

        $fileName = 'advance_leave_detailed_report_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($records) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                '#',
                'Staff Name',
                'ESS / Biometric Code',
                'Staff Type',
                'Department',
                'Leave Type',
                'From Date',
                'To Date',
                'Total Leave Days',
                'Half Day Details',
                'Leave Request Date',
                'Approval Status',
                'Reason',
                'Approved By',
            ]);

            $index = 1;
            foreach ($records as $req) {
                $emp = $req->employee;
                $empName = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : 'N/A';
                $essCode = optional(optional($emp)->biometricDetail)->ess_emp_code ?: 'N/A';
                $staffType = optional(optional($emp)->staffType)->staff_type_name ?: 'N/A';
                $dept = optional(optional($emp)->department)->department_name ?: 'N/A';
                $leaveType = optional($req->leaveType)->name ?: 'N/A';
                $fromDate = !empty($req->start_date) ? Carbon::parse($req->start_date)->format('d-m-Y') : 'N/A';
                $toDate = !empty($req->end_date) ? Carbon::parse($req->end_date)->format('d-m-Y') : 'N/A';
                $days = $this->calculateDays($req);

                $halfDayInfo = 'Full Day';
                if (!empty($req->is_half_day) && ($req->is_half_day == 1 || $req->is_half_day === '1')) {
                    $halfDayInfo = 'Half Day' . (!empty($req->half_day_type) ? ' (' . ucfirst($req->half_day_type) . ')' : '');
                }

                $reqDate = $req->created_at ? $req->created_at->format('d-m-Y H:i') : 'N/A';
                $status = ucfirst($req->status ?: 'Pending');
                $reason = $req->reason ?: '-';
                $approver = $req->approver ? trim($req->approver->first_name . ' ' . $req->approver->last_name) : '-';

                fputcsv($handle, [
                    $index++,
                    $empName,
                    $essCode,
                    $staffType,
                    $dept,
                    $leaveType,
                    $fromDate,
                    $toDate,
                    $days,
                    $halfDayInfo,
                    $reqDate,
                    $status,
                    $reason,
                    $approver,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * CSV Export for Employee-Wise Monthly Analysis
     */
    protected function exportEmployeeMonthlyCsv(Request $request): StreamedResponse
    {
        $sessionMonths = $this->resolveSessionMonths($request);
        $leaveTypes = HrmsLeaveType::whereNull('deleted_at')->orderBy('name')->get();

        $empId = $request->employee_id ?: optional(HrmsEmployee::whereNull('deleted_at')->where('employee_status', 'active')->orderBy('first_name')->first())->id;
        $employee = HrmsEmployee::with(['biometricDetail', 'staffType', 'department'])->find($empId);

        $empName = $employee ? trim($employee->first_name . ' ' . $employee->last_name) : 'Staff';
        $safeName = preg_replace('/[^A-Za-z0-9_]/', '_', $empName);
        $fileName = "employee_monthly_leave_{$safeName}_" . now()->format('Y-m-d') . ".csv";

        $firstMonth = reset($sessionMonths);
        $lastMonth = end($sessionMonths);

        $leavesQuery = HrmsLeaveRequest::with('leaveType')
            ->where('employee_id', $empId)
            ->whereDate('start_date', '<=', $lastMonth['end'])
            ->whereDate('end_date', '>=', $firstMonth['start']);

        if ($request->filled('leave_type_id')) {
            $leavesQuery->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('status')) {
            $leavesQuery->where('status', $request->status);
        }

        $leaves = $leavesQuery->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($employee, $sessionMonths, $leaveTypes, $leaves) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Metadata info rows
            fputcsv($handle, ['Employee-Wise Monthly Leave Report']);
            fputcsv($handle, ['Staff Name:', optional($employee)->first_name . ' ' . optional($employee)->last_name]);
            fputcsv($handle, ['ESS / Biometric Code:', optional(optional($employee)->biometricDetail)->ess_emp_code ?: 'N/A']);
            fputcsv($handle, ['Staff Type:', optional(optional($employee)->staffType)->staff_type_name ?: 'N/A']);
            fputcsv($handle, ['Department:', optional(optional($employee)->department)->department_name ?: 'N/A']);
            fputcsv($handle, ['Generated On:', now()->format('d-m-Y H:i')]);
            fputcsv($handle, []);

            // Table Header
            $headerRow = ['Month', 'Date Range'];
            foreach ($leaveTypes as $lt) {
                $headerRow[] = $lt->name;
            }
            $headerRow[] = 'Approved Days';
            $headerRow[] = 'Pending Days';
            $headerRow[] = 'Rejected Days';
            $headerRow[] = 'Total Leave Days';
            $headerRow[] = 'Requests Count';

            fputcsv($handle, $headerRow);

            $typeTotals = array_fill_keys($leaveTypes->pluck('id')->toArray(), 0);
            $totalApproved = 0;
            $totalPending = 0;
            $totalRejected = 0;
            $grandTotalDays = 0;
            $totalRequests = 0;

            foreach ($sessionMonths as $m) {
                $mDays = 0;
                $mApproved = 0;
                $mPending = 0;
                $mRejected = 0;
                $mRequests = 0;
                $mTypes = array_fill_keys($leaveTypes->pluck('id')->toArray(), 0);

                foreach ($leaves as $l) {
                    $d = $this->calculateDaysInMonth($l, $m['start'], $m['end']);
                    if ($d <= 0) continue;

                    $mDays += $d;
                    $mRequests++;
                    $st = strtolower($l->status ?: 'pending');
                    if ($st === 'approved') $mApproved += $d;
                    elseif ($st === 'rejected') $mRejected += $d;
                    else $mPending += $d;

                    if (isset($mTypes[$l->leave_type_id])) {
                        $mTypes[$l->leave_type_id] += $d;
                        $typeTotals[$l->leave_type_id] += $d;
                    }
                }

                $totalApproved += $mApproved;
                $totalPending += $mPending;
                $totalRejected += $mRejected;
                $grandTotalDays += $mDays;
                $totalRequests += $mRequests;

                $row = [
                    $m['label'],
                    $m['start']->format('d-m-Y') . ' to ' . $m['end']->format('d-m-Y'),
                ];
                foreach ($leaveTypes as $lt) {
                    $row[] = $mTypes[$lt->id] > 0 ? $mTypes[$lt->id] : 0;
                }
                $row[] = $mApproved;
                $row[] = $mPending;
                $row[] = $mRejected;
                $row[] = $mDays;
                $row[] = $mRequests;

                fputcsv($handle, $row);
            }

            // Summary row
            $summaryRow = ['SESSION TOTAL', '-'];
            foreach ($leaveTypes as $lt) {
                $summaryRow[] = $typeTotals[$lt->id];
            }
            $summaryRow[] = $totalApproved;
            $summaryRow[] = $totalPending;
            $summaryRow[] = $totalRejected;
            $summaryRow[] = $grandTotalDays;
            $summaryRow[] = $totalRequests;

            fputcsv($handle, $summaryRow);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * CSV Export for All Employees Monthly Summary Matrix
     */
    protected function exportAllMonthlyCsv(Request $request): StreamedResponse
    {
        $sessionMonths = $this->resolveSessionMonths($request);

        $employeesQuery = HrmsEmployee::with(['biometricDetail', 'staffType', 'department'])
            ->whereNull('deleted_at')
            ->where('employee_status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($request->filled('staff_type_id')) {
            $employeesQuery->where('staff_type_id', (int)$request->staff_type_id);
        }

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $employeesQuery->where(function ($q) use ($search) {
                $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                  ->orWhereHas('biometricDetail', function ($bq) use ($search) {
                      $bq->where('ess_emp_code', 'like', "%{$search}%");
                  });
            });
        }

        $employees = $employeesQuery->get();
        $empIds = $employees->pluck('id')->toArray();

        $firstMonth = reset($sessionMonths);
        $lastMonth = end($sessionMonths);

        $leavesQuery = HrmsLeaveRequest::whereIn('employee_id', $empIds)
            ->whereDate('start_date', '<=', $lastMonth['end'])
            ->whereDate('end_date', '>=', $firstMonth['start']);

        if ($request->filled('leave_type_id')) {
            $leavesQuery->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('status')) {
            $leavesQuery->where('status', $request->status);
        }

        $allLeaves = $leavesQuery->get()->groupBy('employee_id');

        $fileName = 'all_employees_monthly_leave_summary_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($employees, $sessionMonths, $allLeaves) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header Row
            $headerRow = ['#', 'Staff Name', 'ESS Code', 'Staff Type', 'Department'];
            foreach ($sessionMonths as $m) {
                $headerRow[] = $m['label'];
            }
            $headerRow[] = 'Total Days';

            fputcsv($handle, $headerRow);

            $monthTotals = array_fill_keys(array_keys($sessionMonths), 0);
            $grandTotal = 0;
            $index = 1;

            foreach ($employees as $emp) {
                $empLeaves = $allLeaves->get($emp->id, collect());
                $rowTotal = 0;

                $empName = trim($emp->first_name . ' ' . $emp->last_name);
                $essCode = optional(optional($emp)->biometricDetail)->ess_emp_code ?: 'N/A';
                $staffType = optional(optional($emp)->staffType)->staff_type_name ?: 'N/A';
                $dept = optional(optional($emp)->department)->department_name ?: 'N/A';

                $row = [$index++, $empName, $essCode, $staffType, $dept];

                foreach ($sessionMonths as $ym => $m) {
                    $monthDays = 0;
                    foreach ($empLeaves as $l) {
                        $d = $this->calculateDaysInMonth($l, $m['start'], $m['end']);
                        if ($d > 0) $monthDays += $d;
                    }
                    $row[] = $monthDays;
                    $rowTotal += $monthDays;
                    $monthTotals[$ym] += $monthDays;
                    $grandTotal += $monthDays;
                }

                $row[] = $rowTotal;
                fputcsv($handle, $row);
            }

            // Summary Row
            $footerRow = ['', 'ORGANIZATION TOTAL', '', '', ''];
            foreach ($sessionMonths as $ym => $m) {
                $footerRow[] = $monthTotals[$ym];
            }
            $footerRow[] = $grandTotal;

            fputcsv($handle, $footerRow);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Build the query for detailed leave records.
     */
    protected function buildDetailedQuery(Request $request)
    {
        $query = HrmsLeaveRequest::with([
            'employee' => function ($q) {
                $q->select('id', 'first_name', 'last_name', 'staff_type_id', 'department_id');
            },
            'employee.staffType:id,staff_type_name',
            'employee.department:id,department_name',
            'employee.biometricDetail:id,employee_id,ess_emp_code',
            'leaveType:id,name',
            'approver:id,first_name,last_name',
        ])
        ->whereHas('employee', function ($q) {
            $q->whereNull('deleted_at')->where('employee_status', 'active');
        });

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $toDate = Carbon::parse($request->to_date)->endOfDay();
            $query->where(function ($q) use ($fromDate, $toDate) {
                $q->whereDate('start_date', '<=', $toDate)
                  ->whereDate('end_date', '>=', $fromDate);
            });
        } elseif ($request->filled('from_date')) {
            $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $query->whereDate('end_date', '>=', $fromDate);
        } elseif ($request->filled('to_date')) {
            $toDate = Carbon::parse($request->to_date)->endOfDay();
            $query->whereDate('start_date', '<=', $toDate);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('staff_type_id')) {
            $staffTypeId = $request->staff_type_id;
            $query->whereHas('employee', function ($q) use ($staffTypeId) {
                $q->where('staff_type_id', $staffTypeId);
            });
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('request_date')) {
            $query->whereDate('created_at', $request->request_date);
        }

        if ($request->filled('request_from_date') && $request->filled('request_to_date')) {
            $reqFrom = Carbon::parse($request->request_from_date)->startOfDay();
            $reqTo = Carbon::parse($request->request_to_date)->endOfDay();
            $query->whereBetween('created_at', [$reqFrom, $reqTo]);
        } elseif ($request->filled('request_from_date')) {
            $query->whereDate('created_at', '>=', $request->request_from_date);
        } elseif ($request->filled('request_to_date')) {
            $query->whereDate('created_at', '<=', $request->request_to_date);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($qq) use ($search) {
                    $qq->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhereHas('biometricDetail', function ($bq) use ($search) {
                           $bq->where('ess_emp_code', 'like', "%{$search}%");
                       });
                })
                ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Compute total days for a single leave request.
     */
    protected function calculateDays($leave): float
    {
        if (!empty($leave->is_half_day) && ($leave->is_half_day == 1 || $leave->is_half_day === '1')) {
            return 0.5;
        }

        if (!empty($leave->start_date) && !empty($leave->end_date)) {
            try {
                $start = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);
                if ($end->gte($start)) {
                    return (float)($start->diffInDays($end) + 1);
                }
            } catch (\Exception $e) {
                // fall through
            }
        }

        if (!empty($leave->cl_days_deducted) && $leave->cl_days_deducted > 0) {
            return (float)$leave->cl_days_deducted;
        }

        return 1.0;
    }

    /**
     * Compute days of a leave falling strictly inside a specific month window.
     */
    protected function calculateDaysInMonth($leave, Carbon $monthStart, Carbon $monthEnd): float
    {
        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);

        if ($start->gt($monthEnd) || $end->lt($monthStart)) {
            return 0.0;
        }

        if (!empty($leave->is_half_day) && ($leave->is_half_day == 1 || $leave->is_half_day === '1')) {
            return 0.5;
        }

        $overlapStart = $start->copy()->max($monthStart);
        $overlapEnd = $end->copy()->min($monthEnd);

        if ($overlapEnd->gte($overlapStart)) {
            return (float)($overlapStart->diffInDays($overlapEnd) + 1);
        }

        return 0.0;
    }

    /**
     * Resolve the active academic session year string (e.g. 2025_2026).
     */
    protected function resolveSessionYear(Request $request): string
    {
        if ($request->filled('session_year')) {
            return str_replace('-', '_', $request->session_year);
        }
        if ($request->hasSession() && $request->session()->has('db_names') && !empty($request->session()->get('db_names'))) {
            return str_replace('-', '_', $request->session()->get('db_names'));
        }
        if (isset($_COOKIE['selectedYear']) && !empty($_COOKIE['selectedYear'])) {
            return str_replace('-', '_', $_COOKIE['selectedYear']);
        }
        if (session()->has('selectedYear')) {
            return str_replace('-', '_', session('selectedYear'));
        }
        return '2025_2026';
    }

    /**
     * Generate session months in chronological sequence (June of start year to May of end year).
     * Follows the HRMS session cycle (June to May).
     */
    protected function resolveSessionMonths(Request $request): array
    {
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $start = Carbon::parse($request->from_date)->startOfMonth();
            $end = Carbon::parse($request->to_date)->endOfMonth();
        } else {
            $session = $this->resolveSessionYear($request);
            $parts = explode('_', $session);
            $startYear = isset($parts[0]) && is_numeric($parts[0]) ? (int)$parts[0] : (int)date('Y');
            $endYear = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : ($startYear + 1);

            $start = Carbon::create($startYear, 6, 1)->startOfMonth();
            $end = Carbon::create($endYear, 5, 31)->endOfMonth();
        }

        $months = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $ym = $cursor->format('Y-m');
            $months[$ym] = [
                'key' => $ym,
                'label' => $cursor->format('M Y'),
                'short_label' => $cursor->format('M y'),
                'month_name' => $cursor->format('F'),
                'year' => (int)$cursor->format('Y'),
                'month_num' => (int)$cursor->format('m'),
                'start' => $cursor->copy()->startOfMonth(),
                'end' => $cursor->copy()->endOfMonth(),
            ];
            $cursor->addMonth();
        }

        return $months;
    }
}
