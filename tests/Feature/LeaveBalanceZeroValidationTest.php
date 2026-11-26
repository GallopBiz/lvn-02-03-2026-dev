<?php

namespace Tests\Feature;

use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeLeaveBalance;
use App\Models\HrmsLeaveRequest;
use App\Models\HrmsLeaveStaffAllocation;
use App\Models\HrmsLeaveType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LeaveBalanceZeroValidationTest extends TestCase
{
    protected $adminUser;
    protected $employee;
    protected $clType;
    protected $lwpType;
    protected $createdLeaveRequestIds = [];
    protected $createdEmployeeIds = [];

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
                    'student_name' => 'Admin Leave Test',
                    'form_number' => 'ADMIN_LEAVE_' . time(),
                    'password' => bcrypt('password'),
                    'type' => 'a',
                    'role' => 'admin',
                ]);
            }
        }

        // 2. Leave Types
        $this->clType = HrmsLeaveType::firstOrCreate(
            ['id' => 1],
            ['name' => 'Casual Leave - P', 'is_paid' => 1, 'annual_entitlement' => 13]
        );
        $this->lwpType = HrmsLeaveType::find(6);
        if (!$this->lwpType) {
            $this->lwpType = HrmsLeaveType::create([
                'id' => 6,
                'name' => 'Leave Application',
                'is_paid' => 0,
                'annual_entitlement' => 20,
            ]);
        }

        // 3. Create test employee
        $this->employee = HrmsEmployee::create([
            'first_name' => 'ZeroBal',
            'last_name' => 'Staff_' . rand(1000, 9999),
            'email' => 'zerobal_' . time() . rand(100, 999) . '@example.com',
            'employee_status' => 'active',
            'staff_type_id' => 1,
            'is_delete' => 0,
        ]);
        $this->createdEmployeeIds[] = $this->employee->id;

        // Staff leave allocation
        HrmsLeaveStaffAllocation::firstOrCreate(
            ['hrms_staff_type_id' => 1, 'leave_type_id' => 1]
        );
        HrmsLeaveStaffAllocation::firstOrCreate(
            ['hrms_staff_type_id' => 1, 'leave_type_id' => 6]
        );
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdLeaveRequestIds)) {
            HrmsLeaveRequest::whereIn('id', $this->createdLeaveRequestIds)->forceDelete();
        }

        if (!empty($this->createdEmployeeIds)) {
            HrmsEmployeeLeaveBalance::whereIn('employee_id', $this->createdEmployeeIds)->delete();
            HrmsLeaveRequest::whereIn('employee_id', $this->createdEmployeeIds)->forceDelete();
            HrmsEmployee::whereIn('id', $this->createdEmployeeIds)->delete();
        }

        parent::tearDown();
    }

    public function test_zero_balance_prevents_submitting_full_day_paid_leave(): void
    {
        // Explicitly set CL balance to 0
        HrmsEmployeeLeaveBalance::updateOrCreate(
            ['employee_id' => $this->employee->id, 'leave_type_id' => $this->clType->id],
            ['balance' => 0.0]
        );

        $response = $this->actingAs($this->adminUser)->post('/save-leaverequests', [
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->clType->id,
            'start_date' => '2026-11-27',
            'end_date' => '2026-11-27',
            'reason' => 'Testing zero balance full day',
        ]);

        $response->assertSessionHas('error', 'You do not have any available leave balance for this leave type. Please submit a Leave Application instead.');

        $this->assertDatabaseMissing('hrms_leave_requests', [
            'employee_id' => $this->employee->id,
            'start_date' => '2026-11-27',
            'leave_type_id' => $this->clType->id,
        ]);
    }

    public function test_zero_balance_prevents_submitting_half_day_paid_leave(): void
    {
        // Explicitly set CL balance to 0
        HrmsEmployeeLeaveBalance::updateOrCreate(
            ['employee_id' => $this->employee->id, 'leave_type_id' => $this->clType->id],
            ['balance' => 0.0]
        );

        $response = $this->actingAs($this->adminUser)->post('/save-leaverequests', [
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->clType->id,
            'start_date' => '2026-11-27',
            'end_date' => '2026-11-27',
            'is_half_day' => 'on',
            'half_day_type' => 'first',
            'reason' => 'Testing zero balance half day',
        ]);

        $response->assertSessionHas('error', 'You do not have any available leave balance for this leave type. Please submit a Leave Application instead.');

        $this->assertDatabaseMissing('hrms_leave_requests', [
            'employee_id' => $this->employee->id,
            'start_date' => '2026-11-27',
            'leave_type_id' => $this->clType->id,
        ]);
    }

    public function test_leave_application_can_be_submitted_when_regular_balance_is_zero(): void
    {
        // Set regular CL balance to 0
        HrmsEmployeeLeaveBalance::updateOrCreate(
            ['employee_id' => $this->employee->id, 'leave_type_id' => $this->clType->id],
            ['balance' => 0.0]
        );

        $response = $this->actingAs($this->adminUser)->post('/save-leaverequests', [
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->lwpType->id, // Leave Application
            'start_date' => '2026-11-27',
            'end_date' => '2026-11-27',
            'reason' => 'Submitting Leave Application with zero balance',
        ]);

        $response->assertSessionHas('success');

        $created = HrmsLeaveRequest::where('employee_id', $this->employee->id)
            ->where('start_date', '2026-11-27')
            ->where('leave_type_id', $this->lwpType->id)
            ->first();

        $this->assertNotNull($created, 'Leave Application must be created successfully');
        $this->createdLeaveRequestIds[] = $created->id;

        // Ensure CL balance was NOT touched
        $clBalance = HrmsEmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $this->clType->id)
            ->first();
        $this->assertEquals(0.0, (float) $clBalance->balance);
    }

    public function test_half_day_leave_application_can_be_submitted_with_zero_balance(): void
    {
        // Set regular CL balance to 0
        HrmsEmployeeLeaveBalance::updateOrCreate(
            ['employee_id' => $this->employee->id, 'leave_type_id' => $this->clType->id],
            ['balance' => 0.0]
        );

        $response = $this->actingAs($this->adminUser)->post('/save-leaverequests', [
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->lwpType->id, // Leave Application
            'start_date' => '2026-11-27',
            'end_date' => '2026-11-27',
            'is_half_day' => 'on',
            'half_day_type' => 'first',
            'reason' => 'Submitting half-day Leave Application with zero balance',
        ]);

        $response->assertSessionHas('success');

        $created = HrmsLeaveRequest::where('employee_id', $this->employee->id)
            ->where('start_date', '2026-11-27')
            ->where('leave_type_id', $this->lwpType->id)
            ->first();

        $this->assertNotNull($created);
        $this->createdLeaveRequestIds[] = $created->id;
    }

    public function test_positive_balance_allows_submitting_paid_leave(): void
    {
        // Set CL balance to 5.0
        HrmsEmployeeLeaveBalance::updateOrCreate(
            ['employee_id' => $this->employee->id, 'leave_type_id' => $this->clType->id],
            ['balance' => 5.0]
        );

        $response = $this->actingAs($this->adminUser)->post('/save-leaverequests', [
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->clType->id,
            'start_date' => '2026-11-27',
            'end_date' => '2026-11-27',
            'reason' => 'Testing positive balance full day',
        ]);

        $response->assertSessionHas('success');

        $created = HrmsLeaveRequest::where('employee_id', $this->employee->id)
            ->where('start_date', '2026-11-27')
            ->where('leave_type_id', $this->clType->id)
            ->first();

        $this->assertNotNull($created);
        $this->createdLeaveRequestIds[] = $created->id;

        // Balance must be deducted by 1.0 (from 5.0 to 4.0)
        $clBalance = HrmsEmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $this->clType->id)
            ->first();
        $this->assertEquals(4.0, (float) $clBalance->balance);
    }

    public function test_leave_type_dropdown_displays_leave_application(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/get-leave-types', [
            'employee_id' => $this->employee->id,
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $lwpOption = collect($data)->firstWhere('id', 6);
        $this->assertNotNull($lwpOption, 'Leave type 6 must be returned');
        $this->assertEquals('Leave Application', $lwpOption['name'], 'Leave type 6 must be named Leave Application');
    }

    public function test_leave_application_is_prevented_when_casual_leave_balance_is_available(): void
    {
        // Set CL balance to 3.0 (positive available balance)
        HrmsEmployeeLeaveBalance::updateOrCreate(
            ['employee_id' => $this->employee->id, 'leave_type_id' => $this->clType->id],
            ['balance' => 3.0]
        );

        $response = $this->actingAs($this->adminUser)->post('/save-leaverequests', [
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->lwpType->id, // Leave Application
            'start_date' => '2026-11-28',
            'end_date' => '2026-11-28',
            'reason' => 'Testing Leave Application with positive CL balance',
        ]);

        $response->assertSessionHas(
            'error',
            'You still have available leave balance (CL-T / Casual Leave). Please use your available leave balance before applying for Leave Application.'
        );

        $this->assertDatabaseMissing('hrms_leave_requests', [
            'employee_id' => $this->employee->id,
            'start_date' => '2026-11-28',
            'leave_type_id' => $this->lwpType->id,
        ]);
    }

    public function test_leave_application_is_prevented_when_cl_t_balance_is_available(): void
    {
        // Create teaching staff with CL - T (id 5) allocation
        $cltType = HrmsLeaveType::firstOrCreate(
            ['id' => 5],
            ['name' => 'CL - T', 'is_paid' => 1, 'annual_entitlement' => 11]
        );

        $teachingEmployee = HrmsEmployee::create([
            'first_name' => 'Teaching',
            'last_name' => 'Staff_' . rand(1000, 9999),
            'email' => 'teaching_' . time() . rand(100, 999) . '@example.com',
            'employee_status' => 'active',
            'staff_type_id' => 2,
            'is_delete' => 0,
        ]);
        $this->createdEmployeeIds[] = $teachingEmployee->id;

        HrmsLeaveStaffAllocation::firstOrCreate(
            ['hrms_staff_type_id' => 2, 'leave_type_id' => 5]
        );
        HrmsLeaveStaffAllocation::firstOrCreate(
            ['hrms_staff_type_id' => 2, 'leave_type_id' => 6]
        );

        // Set CL - T balance to 2.0
        HrmsEmployeeLeaveBalance::updateOrCreate(
            ['employee_id' => $teachingEmployee->id, 'leave_type_id' => 5],
            ['balance' => 2.0]
        );

        $response = $this->actingAs($this->adminUser)->post('/save-leaverequests', [
            'employee_id' => $teachingEmployee->id,
            'leave_type_id' => $this->lwpType->id, // Leave Application
            'start_date' => '2026-11-28',
            'end_date' => '2026-11-28',
            'reason' => 'Testing Leave Application with positive CL-T balance',
        ]);

        $response->assertSessionHas(
            'error',
            'You still have available leave balance (CL-T / Casual Leave). Please use your available leave balance before applying for Leave Application.'
        );

        $this->assertDatabaseMissing('hrms_leave_requests', [
            'employee_id' => $teachingEmployee->id,
            'start_date' => '2026-11-28',
            'leave_type_id' => $this->lwpType->id,
        ]);
    }
}
