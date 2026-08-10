<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeLeaveBalance;
use App\Models\HrmsLeaveResetHistory;
use App\Models\HrmsLeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class EmployeeLeaveBalanceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // ✅ Get only active leave types (exclude soft-deleted + LWP)
        $leaveTypes = DB::table('hrms_leave_types')
            ->whereNull('deleted_at')
            ->where('name', '!=', 'Leave Without Pay')
            ->get();

        // ✅ Build query with joins (exclude deleted balances + deleted leave types + LWP)
        $query = DB::table('hrms_employee_leave_balances as elb')
            ->join('hrms_employees as emp', 'elb.employee_id', '=', 'emp.id')
            ->join('hrms_staff_type as st', 'emp.staff_type_id', '=', 'st.id')
            ->join('hrms_leave_types as lt', function ($join) {
                $join->on('elb.leave_type_id', '=', 'lt.id')
                     ->whereNull('lt.deleted_at')
                     ->where('lt.name', '!=', 'Leave Without Pay'); // exclude LWP
            })
            ->select(
                'emp.id as employee_id',
                DB::raw("CONCAT(emp.first_name, ' ', emp.last_name) as employee_name"),
                'st.staff_type_name',
                'lt.id as leave_type_id',
                'lt.name as leave_type',
                'elb.balance'
            )
            ->whereNull('emp.deleted_at')
            ->where('emp.employee_status', 'active')
            ->whereNull('elb.deleted_at'); // exclude deleted leave balances

        // ✅ Apply search filter if provided
        if ($search) {
            $query->where(DB::raw("CONCAT(emp.first_name, ' ', emp.last_name)"), 'like', "%{$search}%");
        }

        $results = $query->get();

        // ✅ Structure results per employee
        $employees = [];

        foreach ($results as $row) {
            if (!isset($employees[$row->employee_id])) {
                $employees[$row->employee_id] = [
                    'employee_id' => $row->employee_id,
                    'employee_name' => $row->employee_name,
                    'staff_type_name' => $row->staff_type_name,
                    'leaves' => []
                ];
            }

            $employees[$row->employee_id]['leaves'][$row->leave_type_id] = $row->balance;
        }

        $employees = array_values($employees); // reindex array

        $resetHistories = HrmsLeaveResetHistory::orderByDesc('created_at')->limit(5)->get();

        // ✅ Handle AJAX vs normal view
        if ($request->ajax()) {
            return view('backend.HRMS.employee_leave_balance_table', compact('employees', 'leaveTypes'))->render();
        }

        return view('backend.HRMS.employee_leave_balance', compact('employees', 'leaveTypes', 'resetHistories'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'balances' => 'required|array',
            'balances.*' => 'array',
            'balances.*.*' => 'nullable|numeric|min:0',
        ]);

        $employeeIds = array_keys($validated['balances']);
        $leaveTypeIds = collect($validated['balances'])->flatMap(fn($balances) => array_keys($balances))->unique()->values();

        $validEmployeeIds = HrmsEmployee::whereIn('id', $employeeIds)->pluck('id')->map(fn($id) => (string) $id)->all();
        $validLeaveTypeIds = HrmsLeaveType::whereIn('id', $leaveTypeIds)
            ->where('name', '!=', 'Leave Without Pay')
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->all();

        $updated = 0;

        DB::transaction(function () use ($validated, $validEmployeeIds, $validLeaveTypeIds, &$updated) {
            foreach ($validated['balances'] as $employeeId => $balances) {
                if (!in_array((string) $employeeId, $validEmployeeIds, true)) {
                    continue;
                }

                foreach ($balances as $leaveTypeId => $balance) {
                    if ($balance === null || $balance === '' || !in_array((string) $leaveTypeId, $validLeaveTypeIds, true)) {
                        continue;
                    }

                    HrmsEmployeeLeaveBalance::updateOrCreate(
                        ['employee_id' => $employeeId, 'leave_type_id' => $leaveTypeId],
                        ['balance' => $balance]
                    );

                    $updated++;
                }
            }
        });

        $message = "{$updated} leave balance(s) updated successfully.";

        if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()
            ->route('employee.leave.balances', $request->only('search'))
            ->with('success', $message);
    }

    public function import(Request $request)
    {
        $request->validate([
            'leave_balance_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = fopen($request->file('leave_balance_file')->getRealPath(), 'r');
        $updated = 0;
        $skipped = 0;
        $rowNumber = 0;

        DB::transaction(function () use ($file, &$updated, &$skipped, &$rowNumber) {
            while (($row = fgetcsv($file)) !== false) {
                $rowNumber++;
                $row = array_map('trim', $row);

                if ($rowNumber === 1 && isset($row[0]) && !is_numeric($row[0])) {
                    continue;
                }

                if (count($row) >= 5) {
                    $employeeId = $row[0];
                    $leaveTypeId = $row[2];
                    $balance = $row[4];
                } else {
                    $employeeId = $row[0] ?? null;
                    $leaveTypeId = $row[1] ?? null;
                    $balance = $row[2] ?? null;
                }

                if (!is_numeric($employeeId) || !is_numeric($leaveTypeId) || !is_numeric($balance) || $balance < 0) {
                    $skipped++;
                    continue;
                }

                $employeeExists = HrmsEmployee::where('id', $employeeId)->exists();
                $leaveTypeExists = HrmsLeaveType::where('id', $leaveTypeId)
                    ->where('name', '!=', 'Leave Without Pay')
                    ->exists();

                if (!$employeeExists || !$leaveTypeExists) {
                    $skipped++;
                    continue;
                }

                HrmsEmployeeLeaveBalance::updateOrCreate(
                    ['employee_id' => $employeeId, 'leave_type_id' => $leaveTypeId],
                    ['balance' => $balance]
                );

                $updated++;
            }
        });

        fclose($file);

        return redirect()
            ->route('employee.leave.balances')
            ->with('success', "{$updated} leave balance(s) imported successfully. Skipped {$skipped} row(s).");
    }

    public function sample()
    {
        $employees = HrmsEmployee::with('leaveBalances')
            ->where(fn($query) => $query->where('employee_status', 'active')->orWhereNull('employee_status'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $leaveTypes = HrmsLeaveType::whereNull('deleted_at')
            ->where('name', '!=', 'Leave Without Pay')
            ->orderBy('name')
            ->get();

        $filename = 'employee_leave_balance_sample.csv';

        return response()->streamDownload(function () use ($employees, $leaveTypes) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['employee_id', 'employee_name', 'leave_type_id', 'leave_type_name', 'balance']);

            foreach ($employees as $employee) {
                foreach ($leaveTypes as $leaveType) {
                    $currentBalance = optional(
                        $employee->leaveBalances->firstWhere('leave_type_id', $leaveType->id)
                    )->balance ?? 0;

                    fputcsv($file, [
                        $employee->id,
                        trim($employee->first_name . ' ' . $employee->last_name),
                        $leaveType->id,
                        $leaveType->name,
                        $currentBalance,
                    ]);
                }
            }

            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function reset(Request $request)
    {
        try {
            Artisan::call('leave:reset');
            $output = trim(Artisan::output());

            HrmsLeaveResetHistory::create([
                'created_by' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'message' => $output ?: 'Employee leave balances reset successfully.',
            ]);

            return redirect()
                ->route('employee.leave.balances')
                ->with('success', $output ?: 'Employee leave balances reset successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
