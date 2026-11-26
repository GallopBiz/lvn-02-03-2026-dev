<?php

namespace Tests\Feature;

use App\Http\Controllers\EmployeePayrollController;
use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeLeaveBalance;
use App\Models\HrmsSalary;
use App\Models\HrmsLeaveRequest;
use App\Models\HrmsLeaveType;
use App\Models\PayrollAttendanceConfiguration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class LwpPayrollDeductionTest extends TestCase
{
    protected $adminUser;
    protected $clType;
    protected $lwpType;
    protected $employee;
    protected $createdLeaveRequestIds = [];
    protected $createdEmployeeIds = [];
    protected $createdSalaryIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Admin user
        $this->adminUser = User::where('type', 'a')->first();
        if (!$this->adminUser) {
            $this->adminUser = User::first();
            if ($this->adminUser) {
                $this->adminUser->type = 'a';
                $this->adminUser->role = 'admin';
                $this->adminUser->save();
            } else {
                $this->adminUser = User::create([
                    'student_name' => 'Admin Payroll Tester',
                    'form_number' => 'ADMIN_PAYROLL_' . time(),
                    'password' => bcrypt('password'),
                    'type' => 'a',
                    'role' => 'admin',
                ]);
            }
        }

        // 2. Fetch or create leave types
        $this->clType = HrmsLeaveType::firstOrCreate(
            ['id' => 1],
            ['name' => 'Casual Leave - P', 'is_paid' => 1, 'annual_entitlement' => 13]
        );
        $this->lwpType = HrmsLeaveType::firstOrCreate(
            ['id' => 6],
            ['name' => 'Leave Without Pay', 'is_paid' => 0, 'annual_entitlement' => 20]
        );

        // 3. Create test employee
        $this->employee = HrmsEmployee::create([
            'first_name' => 'LwpTest',
            'last_name' => 'Employee_' . rand(1000, 9999),
            'email' => 'lwptest_' . time() . rand(100, 999) . '@example.com',
            'employee_status' => 'active',
            'staff_type_id' => 1,
            'is_delete' => 0,
        ]);
        $this->createdEmployeeIds[] = $this->employee->id;

        // 4. Create active salary for employee (Rs. 20,000 basic)
        $salary = HrmsSalary::create([
            'employee_id' => $this->employee->id,
            'basic_salary' => 20000,
            'is_active' => 1,
            'effective_date' => '2026-01-01',
        ]);
        $this->createdSalaryIds[] = $salary->id;

        // 5. Initialize leave balances
        HrmsEmployeeLeaveBalance::updateOrCreate(
            ['employee_id' => $this->employee->id, 'leave_type_id' => $this->clType->id],
            ['balance' => 10.0]
        );
        HrmsEmployeeLeaveBalance::updateOrCreate(
            ['employee_id' => $this->employee->id, 'leave_type_id' => $this->lwpType->id],
            ['balance' => 20.0]
        );

        // Disable biometric for test month (November = month 11) so all working days are present by default
        PayrollAttendanceConfiguration::updateOrCreate(
            ['month_number' => 11],
            ['biometric_required' => 0]
        );
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdLeaveRequestIds)) {
            HrmsLeaveRequest::whereIn('id', $this->createdLeaveRequestIds)->forceDelete();
        }

        if (!empty($this->createdSalaryIds)) {
            HrmsSalary::whereIn('id', $this->createdSalaryIds)->forceDelete();
        }

        if (!empty($this->createdEmployeeIds)) {
            HrmsEmployeeLeaveBalance::whereIn('employee_id', $this->createdEmployeeIds)->delete();
            HrmsLeaveRequest::whereIn('employee_id', $this->createdEmployeeIds)->forceDelete();
            HrmsEmployee::whereIn('id', $this->createdEmployeeIds)->delete();
        }

        parent::tearDown();
    }

    /**
     * Helper to generate payroll JSON for the test employee in November 2026
     */
    protected function generatePayrollForEmployee(): array
    {
        $controller = app(EmployeePayrollController::class);
        $request = new Request([
            'employee_ids' => [$this->employee->id],
            'month' => 11,
            'year' => 2026,
        ]);

        $response = $controller->GenerateJson($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success'] ?? false, 'Payroll generation failed: ' . json_encode($data));
        $this->assertNotEmpty($data['payroll'], 'No payroll records returned');

        return $data['payroll'][0];
    }

    public function test_approved_full_day_lwp_is_deducted_from_salary(): void
    {
        // 2026-11-04 is Wednesday (working day)
        $leave = HrmsLeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->lwpType->id,
            'start_date' => '2026-11-04',
            'end_date' => '2026-11-04',
            'reason' => 'Approved LWP full day',
            'status' => 'approved',
            'is_half_day' => '0',
        ]);
        $this->createdLeaveRequestIds[] = $leave->id;

        $payroll = $this->generatePayrollForEmployee();

        // 1 full day of LWP must be deducted
        $this->assertEquals(1.0, (float) $payroll['lwp'], 'Approved full day LWP must result in 1.0 LWP day');
        $this->assertLessThan($payroll['gross_salary'], $payroll['earn_salary'], 'Earn salary must be less than gross salary due to LWP deduction');
    }

    public function test_rejected_full_day_lwp_is_deducted_from_salary(): void
    {
        // 2026-11-04 is Wednesday (working day)
        $leave = HrmsLeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->lwpType->id,
            'start_date' => '2026-11-04',
            'end_date' => '2026-11-04',
            'reason' => 'Rejected LWP full day',
            'status' => 'rejected',
            'is_half_day' => '0',
        ]);
        $this->createdLeaveRequestIds[] = $leave->id;

        $payroll = $this->generatePayrollForEmployee();

        // 1 full day of LWP must be deducted even if rejected
        $this->assertEquals(1.0, (float) $payroll['lwp'], 'Rejected full day LWP must result in 1.0 LWP day');
        $this->assertLessThan($payroll['gross_salary'], $payroll['earn_salary'], 'Earn salary must be less than gross salary due to LWP deduction');
    }

    public function test_approved_half_day_lwp_is_deducted_from_salary(): void
    {
        // 2026-11-04 is Wednesday (working day)
        $leave = HrmsLeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->lwpType->id,
            'start_date' => '2026-11-04',
            'end_date' => '2026-11-04',
            'reason' => 'Approved LWP half day',
            'status' => 'approved',
            'is_half_day' => '1',
            'half_day_type' => 'first',
        ]);
        $this->createdLeaveRequestIds[] = $leave->id;

        $payroll = $this->generatePayrollForEmployee();

        // 0.5 day of LWP must be deducted
        $this->assertEquals(0.5, (float) $payroll['lwp'], 'Approved half day LWP must result in 0.5 LWP day');
        $this->assertLessThan($payroll['gross_salary'], $payroll['earn_salary'], 'Earn salary must be less than gross salary due to 0.5 LWP deduction');
    }

    public function test_rejected_half_day_lwp_is_deducted_from_salary(): void
    {
        // 2026-11-04 is Wednesday (working day)
        $leave = HrmsLeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->lwpType->id,
            'start_date' => '2026-11-04',
            'end_date' => '2026-11-04',
            'reason' => 'Rejected LWP half day',
            'status' => 'rejected',
            'is_half_day' => '1',
            'half_day_type' => 'second',
        ]);
        $this->createdLeaveRequestIds[] = $leave->id;

        $payroll = $this->generatePayrollForEmployee();

        // 0.5 day of LWP must be deducted even if rejected
        $this->assertEquals(0.5, (float) $payroll['lwp'], 'Rejected half day LWP must result in 0.5 LWP day');
        $this->assertLessThan($payroll['gross_salary'], $payroll['earn_salary'], 'Earn salary must be less than gross salary due to 0.5 LWP deduction');
    }

    public function test_approved_full_day_casual_leave_is_not_deducted(): void
    {
        // 2026-11-04 is Wednesday (working day)
        $leave = HrmsLeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->clType->id,
            'start_date' => '2026-11-04',
            'end_date' => '2026-11-04',
            'reason' => 'Approved CL full day',
            'status' => 'approved',
            'is_half_day' => '0',
        ]);
        $this->createdLeaveRequestIds[] = $leave->id;

        $payroll = $this->generatePayrollForEmployee();

        // Paid leave must NOT be deducted
        $this->assertEquals(0.0, (float) $payroll['lwp'], 'Approved Casual Leave must not be deducted (LWP must be 0)');
        $this->assertEquals($payroll['gross_salary'], $payroll['earn_salary'], 'Earn salary must equal gross salary for paid leave');
    }

    public function test_approved_half_day_casual_leave_is_not_deducted(): void
    {
        // 2026-11-04 is Wednesday (working day)
        $leave = HrmsLeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->clType->id,
            'start_date' => '2026-11-04',
            'end_date' => '2026-11-04',
            'reason' => 'Approved CL half day',
            'status' => 'approved',
            'is_half_day' => '1',
            'half_day_type' => 'first',
        ]);
        $this->createdLeaveRequestIds[] = $leave->id;

        $payroll = $this->generatePayrollForEmployee();

        // Approved half-day CL must NOT be deducted
        $this->assertEquals(0.0, (float) $payroll['lwp'], 'Approved half day Casual Leave must not be deducted (LWP must be 0)');
        $this->assertEquals($payroll['gross_salary'], $payroll['earn_salary']);
    }

    public function test_multi_day_lwp_approved_deducts_all_working_days(): void
    {
        // 2026-11-04 (Wed) to 2026-11-05 (Thu) = 2 working days
        $leave = HrmsLeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->lwpType->id,
            'start_date' => '2026-11-04',
            'end_date' => '2026-11-05',
            'reason' => '2 days LWP',
            'status' => 'approved',
            'is_half_day' => '0',
        ]);
        $this->createdLeaveRequestIds[] = $leave->id;

        $payroll = $this->generatePayrollForEmployee();

        $this->assertEquals(2.0, (float) $payroll['lwp'], '2-day LWP must deduct 2.0 days');
    }
}
