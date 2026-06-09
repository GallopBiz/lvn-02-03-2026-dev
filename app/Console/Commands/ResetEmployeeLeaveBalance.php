<?php

namespace App\Console\Commands;

use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeLeaveBalance;
use App\Models\HrmsLeaveStaffAllocation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetEmployeeLeaveBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset employee leave balance and carry forward eligible leaves';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $employees = HrmsEmployee::with('leaveBalances.leaveType')
                ->where(fn($query) => $query->where('employee_status', 'active')->orWhereNull('employee_status'))
                ->get();

            $updatedBalances = 0;

            foreach ($employees as $employee) {
                $carryForwardBalances = [];

                foreach ($employee->leaveBalances as $leaveBalance) {
                    if (optional($leaveBalance->leaveType)->is_carry_forward) {
                        $carryForwardBalances[$leaveBalance->leave_type_id] = $leaveBalance->balance;
                    }
                }

                $updatedBalances += $this->initializeLeaveBalances(
                    $employee->id,
                    $employee->staff_type_id,
                    $carryForwardBalances
                );
            }

            DB::commit();

            $this->info("Employee leave balances have been reset successfully. Updated {$updatedBalances} balance(s) for {$employees->count()} employee(s).");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error resetting leave balances: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function initializeLeaveBalances($employeeId, $employeeStaffId, $carryForwardBalances)
    {
        if (!$employeeStaffId) {
            return 0;
        }

        // Load employee for eligibility checks and allocations with related leave type metadata
        $employee = \App\Models\HrmsEmployee::with(['department', 'staffType'])->find($employeeId);

        // Helper to check Earn Leave eligibility: staff type 'Permanent' OR department 'Office Staff'
        $isEarnLeaveEligible = false;
        if ($employee) {
            $staffName = optional($employee->staffType)->staff_type_name;
            $deptName = optional($employee->department)->department_name;
            // Require BOTH Permanent staff type AND Office Staff department
            $isEarnLeaveEligible = (
                is_string($staffName) && strcasecmp(trim($staffName), 'Permanent') === 0
            ) && (
                is_string($deptName) && strcasecmp(trim($deptName), 'Office Staff') === 0
            );
        }

        // Load allocations with related leave type metadata
        $leaveAllocated = HrmsLeaveStaffAllocation::where('hrms_staff_type_id', $employeeStaffId)
            ->with('leaveType')
            ->get();

        $updatedBalances = 0;

        foreach ($leaveAllocated as $leave) {
            $leaveType = $leave->leaveType;
            $leaveTypeId = $leave->leave_type_id;
            $leaveName = optional($leaveType)->name;
            $normalizedLeaveName = strtolower(preg_replace('/[^a-z]/', '', $leaveName ?? ''));
            $isEarnLeaveType = in_array($normalizedLeaveName, ['earnleaveadmin', 'eearnleaveadmin'], true);
            $isCompoffType = $normalizedLeaveName === 'compoff';

            // Special handling for Earn Leave - Admin
            if ($isEarnLeaveType) {
                if ($isEarnLeaveEligible) {
                    // Per request: assign 30 EL to eligible employees (carry-forward applies if allowed)
                    $allocated = 30.0;
                    $carry = 0;
                    if (optional($leaveType)->is_carry_forward) {
                        $carry = $carryForwardBalances[$leaveTypeId] ?? 0;
                    }

                    $totalBalance = $allocated + $carry;

                    HrmsEmployeeLeaveBalance::updateOrCreate(
                        ['employee_id' => $employeeId, 'leave_type_id' => $leaveTypeId],
                        ['balance' => $totalBalance]
                    );
                    $updatedBalances++;
                } else {
                    // Not eligible -> force zero
                    HrmsEmployeeLeaveBalance::updateOrCreate(
                        ['employee_id' => $employeeId, 'leave_type_id' => $leaveTypeId],
                        ['balance' => 0]
                    );
                    $updatedBalances++;
                }

                continue;
            }

            // Special handling for Compoff: always reset to 0
            if ($isCompoffType) {
                HrmsEmployeeLeaveBalance::updateOrCreate(
                    ['employee_id' => $employeeId, 'leave_type_id' => $leaveTypeId],
                    ['balance' => 0]
                );
                $updatedBalances++;
                continue;
            }

            // Default allocated amount
            $allocated = (float) ($leave->max_allowed ?? 0);

            // Carry forward only if the leave type allows it. Otherwise ignore previous balance.
            $carry = 0;
            if (optional($leaveType)->is_carry_forward) {
                $carry = $carryForwardBalances[$leaveTypeId] ?? 0;
            }

            $totalBalance = $allocated + $carry;

            HrmsEmployeeLeaveBalance::updateOrCreate(
                ['employee_id' => $employeeId, 'leave_type_id' => $leaveTypeId],
                ['balance' => $totalBalance]
            );

            $updatedBalances++;
        }

        // Ensure Compoff is reset to 0 even if there is no allocation for this staff type
        $compoffType = \App\Models\HrmsLeaveType::get()->first(function ($lt) {
            return strtolower(preg_replace('/[^a-z]/', '', optional($lt)->name ?? '')) === 'compoff';
        });
        if ($compoffType && !$leaveAllocated->contains('leave_type_id', $compoffType->id)) {
            HrmsEmployeeLeaveBalance::updateOrCreate(
                ['employee_id' => $employeeId, 'leave_type_id' => $compoffType->id],
                ['balance' => 0]
            );
            $updatedBalances++;
        }

        // If Earn Leave - Admin is not allocated for this staff type and employee is NOT eligible,
        // ensure the balance is zero. If employee is eligible but no allocation exists, do nothing.
        $elType = \App\Models\HrmsLeaveType::get()->first(function ($lt) {
            $normalized = strtolower(preg_replace('/[^a-z]/', '', optional($lt)->name ?? ''));
            return in_array($normalized, ['earnleaveadmin', 'eearnleaveadmin'], true);
        });
        if ($elType && !$leaveAllocated->contains('leave_type_id', $elType->id) && !$isEarnLeaveEligible) {
            HrmsEmployeeLeaveBalance::updateOrCreate(
                ['employee_id' => $employeeId, 'leave_type_id' => $elType->id],
                ['balance' => 0]
            );
            $updatedBalances++;
        }

        return $updatedBalances;
    }
}
