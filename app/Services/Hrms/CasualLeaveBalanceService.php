<?php

namespace App\Services\Hrms;

use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeLeaveBalance;
use Illuminate\Support\Facades\DB;

class CasualLeaveBalanceService
{
    public function leaveTypeIdForEmployee(HrmsEmployee $employee): int
    {
        $employee->loadMissing('staffType');

        return stripos((string) optional($employee->staffType)->staff_type_name, 'temp') !== false ? 5 : 1;
    }

    public function deduct(HrmsEmployee $employee, float $days): float
    {
        return DB::transaction(function () use ($employee, $days) {
            $balance = HrmsEmployeeLeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $this->leaveTypeIdForEmployee($employee))
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                return 0.0;
            }

            $available = max(0.0, (float) $balance->balance);
            $deducted = min($available, max(0.0, $days));
            $balance->balance = round($available - $deducted, 1);
            $balance->save();

            return $deducted;
        });
    }

    public function refund(HrmsEmployee $employee, float $days): void
    {
        if ($days <= 0) {
            return;
        }

        DB::transaction(function () use ($employee, $days) {
            $balance = HrmsEmployeeLeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $this->leaveTypeIdForEmployee($employee))
                ->lockForUpdate()
                ->first();

            if ($balance) {
                $balance->balance = round(max(0.0, (float) $balance->balance) + $days, 1);
                $balance->save();
            }
        });
    }

    public static function allocate(float $available, float $requested): array
    {
        $deducted = min(max(0.0, $available), max(0.0, $requested));

        return [
            'cl_days' => round($deducted, 1),
            'lwp_days' => round(max(0.0, $requested - $deducted), 1),
        ];
    }
}
