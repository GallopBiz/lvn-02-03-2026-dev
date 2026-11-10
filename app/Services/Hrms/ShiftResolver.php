<?php

namespace App\Services\Hrms;

use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeShiftHistory;
use App\Models\HrmsShift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ShiftResolver
{
    public static function getApplicableShift($employee, $attendanceDate): ?HrmsShift
    {
        if (!$employee instanceof HrmsEmployee) {
            $employee = HrmsEmployee::find($employee);
        }

        if (!$employee) {
            return null;
        }

        $date = Carbon::parse($attendanceDate)->toDateString();
        $connection = $employee->getConnectionName() ?: config('database.default');

        // Employees belong to the selected academic-session database. Resolve
        // both their assigned shift and any dated override on that same
        // connection; using the default connection can show another shift.
        if (Schema::connection($connection)->hasTable('hrms_employee_shift_history')) {
            $history = HrmsEmployeeShiftHistory::on($connection)
                ->where('employee_id', $employee->id)
                ->whereDate('effective_from', '<=', $date)
                ->where(function ($query) use ($date) {
                    $query->whereNull('effective_to')
                        ->orWhereDate('effective_to', '>=', $date);
                })
                ->orderByDesc('effective_from')
                ->orderByDesc('id')
                ->first();

            if ($history) {
                return HrmsShift::on($connection)->find($history->shift_id);
            }
        }

        return HrmsShift::on($connection)->find($employee->shift_id);
    }
}
