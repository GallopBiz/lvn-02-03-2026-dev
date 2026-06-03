<?php

namespace App\Http\Controllers;

use App\Models\HrmsDepartment;
use App\Models\HrmsStaffType;
use App\Models\PayrollAttendanceConfiguration;
use App\Models\PayrollDepartmentAttendanceConfiguration;
use App\Models\PayrollStaffAttendanceConfiguration;
use Illuminate\Http\Request;

class PayrollAttendanceConfigurationController extends Controller
{
    public function index()
    {
        $monthConfigurations = PayrollAttendanceConfiguration::all()->keyBy('month_number');
        $departments = HrmsDepartment::orderBy('department_name')->get();
        $departmentConfigurations = PayrollDepartmentAttendanceConfiguration::all()->keyBy('department_id');
        $staffTypes = HrmsStaffType::orderBy('staff_type_name')->get();
        $staffConfigurations = PayrollStaffAttendanceConfiguration::all()->keyBy('staff_type_id');

        return view('backend.HRMS.payroll_attendance_configuration', compact(
            'departmentConfigurations',
            'departments',
            'monthConfigurations',
            'staffTypes',
            'staffConfigurations'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'months' => ['nullable', 'array'],
            'months.*' => ['nullable', 'boolean'],
            'departments' => ['nullable', 'array'],
            'departments.*' => ['nullable', 'boolean'],
            'staff_types' => ['nullable', 'array'],
            'staff_types.*' => ['nullable', 'boolean'],
        ]);

        for ($month = 1; $month <= 12; $month++) {
            PayrollAttendanceConfiguration::updateOrCreate(
                ['month_number' => $month],
                ['biometric_required' => (bool) data_get($validated, "months.$month", false)]
            );
        }

        foreach (HrmsStaffType::pluck('id') as $staffTypeId) {
            PayrollStaffAttendanceConfiguration::updateOrCreate(
                ['staff_type_id' => $staffTypeId],
                ['biometric_required' => (bool) data_get($validated, "staff_types.$staffTypeId", false)]
            );
        }

        foreach (HrmsDepartment::pluck('id') as $departmentId) {
            PayrollDepartmentAttendanceConfiguration::updateOrCreate(
                ['department_id' => $departmentId],
                ['biometric_required' => (bool) data_get($validated, "departments.$departmentId", false)]
            );
        }

        return redirect()
            ->route('payroll.attendance.configuration')
            ->with('success', 'Payroll attendance configuration updated successfully.');
    }
}
