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
            // Fetch all employees
            $employees = HrmsEmployee::all();
            foreach ($employees as $employee) {
                // Fetch leave balances for the employee
                $leaveBalances = HrmsEmployeeLeaveBalance::where('employee_id', $employee->id)->get();
                $employeeStaffId = $employee->staff_type_id; 
                $carryForwardBalances = [];
                $leaveBalances = HrmsEmployeeLeaveBalance::where('employee_id', $employee->id)->get();

                foreach ($leaveBalances as $leave) {
                    if ($leave->leaveType->is_carry_forward) {
                        $carryForwardBalances[$leave->leave_type_id] = $leave->balance;
                    } else {
                        $carryForwardBalances[$leave->leave_type_id] = 0;
                    }
                }
                $this->initializeLeaveBalances($employee->id, $employeeStaffId, $employee->is_vacation, $carryForwardBalances);

            }

            // Commit transaction
            DB::commit();
            
            $this->info('Employee leave balances have been reset successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error resetting leave balances: ' . $e->getMessage());
        }
        return Command::SUCCESS;
    }

    private function initializeLeaveBalances($employeeId, $employeeStaffId, $isVacationType, $carryForwardBalances)
    {
        $leaveAllocated = HrmsLeaveStaffAllocation::where('hrms_staff_type_id', $employeeStaffId)->get();

        foreach ($leaveAllocated as $leave) {
            $newBalance = $leave->max_allowed;
            $carryForward = $carryForwardBalances[$leave->leave_type_id] ?? 0;
            $totalBalance = $newBalance + $carryForward; // Add carry forward balance
            HrmsEmployeeLeaveBalance::updateOrCreate(
                ['employee_id' => $employeeId, 'leave_type_id' => $leave->leave_type_id],
                ['balance' => $totalBalance]
            );
        }

        if ($isVacationType) {
            HrmsEmployeeLeaveBalance::updateOrCreate(
                ['employee_id' => $employeeId, 'leave_type_id' => 1],
                ['remaining_balance' => 0]
            );
        }
    }
}
