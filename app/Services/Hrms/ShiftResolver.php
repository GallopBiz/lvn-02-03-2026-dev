<?php

namespace App\Services\Hrms;

use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeShiftHistory;
use App\Models\HrmsShift;
use Carbon\Carbon;

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

        $history = HrmsEmployeeShiftHistory::with('shift')
            ->where('employee_id', $employee->id)
            ->whereDate('effective_from', '<=', $date)
            ->whereDate('effective_to', '>=', $date)
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();

        if ($history && $history->shift) {
            return $history->shift;
        }

        return HrmsShift::find($employee->shift_id);
    }
}
