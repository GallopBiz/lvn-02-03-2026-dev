<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'lt.name as leave_type',
                'elb.balance'
            )
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
                    'employee_name' => $row->employee_name,
                    'staff_type_name' => $row->staff_type_name,
                    'leaves' => []
                ];
            }

            $employees[$row->employee_id]['leaves'][$row->leave_type] = $row->balance;
        }

        $employees = array_values($employees); // reindex array

        // ✅ Handle AJAX vs normal view
        if ($request->ajax()) {
            return view('backend.HRMS.employee_leave_balance_table', compact('employees', 'leaveTypes'))->render();
        }

        return view('backend.HRMS.employee_leave_balance', compact('employees', 'leaveTypes'));
    }
}
