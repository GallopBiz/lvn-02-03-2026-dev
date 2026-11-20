<?php

namespace App\Services\Hrms;

use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeShiftHistory;
use App\Models\HrmsShift;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class ShiftResolver
{
    /**
     * Resolve the applicable shift for a single employee on a given date.
     *
     * Priority:
     * 1. Check hrms_employee_shift_history for the employee and date (effective_from <= date and (effective_to is null or >= date))
     * 2. Default shift_id from hrms_employees
     */
    public static function getApplicableShift($employee, $attendanceDate, ?string $connection = null): ?HrmsShift
    {
        $conn = $connection 
            ?: ($employee instanceof HrmsEmployee && $employee->getConnectionName() ? $employee->getConnectionName() : null)
            ?: (Config::get('database.connections.dynamic.database') ? 'dynamic' : config('database.default'));

        if (!$employee instanceof HrmsEmployee) {
            $employee = HrmsEmployee::on($conn)->find($employee);
        }

        if (!$employee) {
            return null;
        }

        $date = Carbon::parse($attendanceDate)->toDateString();

        $withRelations = Schema::connection($conn)->hasTable('hrms_shift_types') ? ['shiftType'] : [];

        // 1. Check hrms_employee_shift_history for the employee and date
        if (Schema::connection($conn)->hasTable('hrms_employee_shift_history')) {
            $history = HrmsEmployeeShiftHistory::on($conn)
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
                $shift = HrmsShift::on($conn)->with($withRelations)->find($history->shift_id);
                if ($shift) {
                    return $shift;
                }
            }
        }

        // 2. Fall back to the default shift_id from hrms_employees
        return HrmsShift::on($conn)->with($withRelations)->find($employee->shift_id);
    }

    /**
     * Bulk resolve shifts for employees over a date range.
     *
     * Priority:
     * 1. Check hrms_employee_shift_history for the employee and selected date
     * 2. Default shift_id from hrms_employees
     *
     * @param mixed $employees Collection or array of HrmsEmployee or employee IDs
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @param string|null $connection
     * @return array Matrix of [employee_id => [dateString => HrmsShift|null]]
     */
    public static function getApplicableShiftsForRange($employees, $startDate, $endDate, ?string $connection = null): array
    {
        if (empty($employees)) {
            return [];
        }

        $startDateStr = Carbon::parse($startDate)->toDateString();
        $endDateStr = Carbon::parse($endDate)->toDateString();

        if (!($employees instanceof \Illuminate\Support\Collection)) {
            $employees = collect($employees);
        }

        $firstEmp = $employees->first();
        $conn = $connection 
            ?: ($firstEmp instanceof HrmsEmployee && $firstEmp->getConnectionName() ? $firstEmp->getConnectionName() : null)
            ?: (Config::get('database.connections.dynamic.database') ? 'dynamic' : config('database.default'));

        if (!$firstEmp instanceof HrmsEmployee) {
            $employees = HrmsEmployee::on($conn)->whereIn('id', $employees)->get();
        }

        $employeeIds = $employees->pluck('id')->filter()->unique()->values()->all();
        if (empty($employeeIds)) {
            return [];
        }

        // Fetch shift history records covering the date range
        $hasHistoryTable = Schema::connection($conn)->hasTable('hrms_employee_shift_history');
        $histories = collect();
        if ($hasHistoryTable) {
            $histories = HrmsEmployeeShiftHistory::on($conn)
                ->whereIn('employee_id', $employeeIds)
                ->whereDate('effective_from', '<=', $endDateStr)
                ->where(function ($q) use ($startDateStr) {
                    $q->whereNull('effective_to')
                      ->orWhereDate('effective_to', '>=', $startDateStr);
                })
                ->orderByDesc('effective_from')
                ->orderByDesc('id')
                ->get();
        }

        // Collect all shift IDs (both default and history)
        $allShiftIds = $employees->pluck('shift_id')
            ->merge($histories->pluck('shift_id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $shiftsById = collect();
        if (!empty($allShiftIds) && Schema::connection($conn)->hasTable('hrms_shifts')) {
            $withRelations = Schema::connection($conn)->hasTable('hrms_shift_types') ? ['shiftType'] : [];
            $shiftsById = HrmsShift::on($conn)
                ->with($withRelations)
                ->whereIn('id', $allShiftIds)
                ->get()
                ->keyBy('id');
        }

        $historiesByEmp = $histories->groupBy('employee_id');

        $period = CarbonPeriod::create($startDateStr, $endDateStr);
        $dateStrings = [];
        foreach ($period as $dt) {
            $dateStrings[] = $dt->toDateString();
        }

        $result = [];
        foreach ($employees as $employee) {
            $empId = $employee->id;
            $defaultShift = $shiftsById->get($employee->shift_id);
            $empHistories = $historiesByEmp->get($empId);

            foreach ($dateStrings as $dateStr) {
                $matchedShift = null;
                if ($empHistories && $empHistories->isNotEmpty()) {
                    $matchedHistory = $empHistories->first(function ($h) use ($dateStr) {
                        $from = $h->effective_from instanceof Carbon 
                            ? $h->effective_from->toDateString() 
                            : substr((string)$h->effective_from, 0, 10);
                        $to = $h->effective_to 
                            ? ($h->effective_to instanceof Carbon ? $h->effective_to->toDateString() : substr((string)$h->effective_to, 0, 10)) 
                            : null;

                        return $from <= $dateStr && ($to === null || $to >= $dateStr);
                    });

                    if ($matchedHistory) {
                        $matchedShift = $shiftsById->get($matchedHistory->shift_id);
                    }
                }

                $result[$empId][$dateStr] = $matchedShift ?: $defaultShift;
            }
        }

        return $result;
    }
}
