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

        $leaveAllocated = HrmsLeaveStaffAllocation::where('hrms_staff_type_id', $employeeStaffId)->get();
        $updatedBalances = 0;

        foreach ($leaveAllocated as $leave) {
            $newBalance = (float) ($leave->max_allowed ?? 0);
            $carryForward = $carryForwardBalances[$leave->leave_type_id] ?? 0;
            $totalBalance = $newBalance + $carryForward;

            HrmsEmployeeLeaveBalance::updateOrCreate(
                ['employee_id' => $employeeId, 'leave_type_id' => $leave->leave_type_id],
                ['balance' => $totalBalance]
            );

            $updatedBalances++;
        }

        return $updatedBalances;
    }
}
