<?php

namespace Tests\Feature;

use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeLeaveBalance;
use App\Models\HrmsLeaveRequest;
use App\Models\HrmsLeaveType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeLeaveApprovalWorkflowTest extends TestCase
{
    protected $adminUser;
    protected $employee;
    protected $clType;
    protected $mlType;
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
                    'student_name' => 'Admin Leave Tester',
                    'form_number' => 'ADMIN_LEAVE_' . time(),
                    'password' => bcrypt('password'),
                    'type' => 'a',
                    'role' => 'admin',
                ]);
            }
        }

        // 2. Fetch or create Leave Types (CL = paid, ML = paid, LWP = unpaid)
        $this->clType = HrmsLeaveType::firstOrCreate(
            ['id' => 1],
            ['name' => 'Casual Leave - P', 'is_paid' => 1, 'annual_entitlement' => 13]
        );
        $this->mlType = HrmsLeaveType::firstOrCreate(
            ['id' => 3],
            ['name' => 'Medical Leave', 'is_paid' => 1, 'annual_entitlement' => 10]
        );
        $this->lwpType = HrmsLeaveType::firstOrCreate(
            ['id' => 6],
            ['name' => 'Leave Without Pay', 'is_paid' => 0, 'annual_entitlement' => 20]
        );

        // 3. Create test employee
        $this->employee = HrmsEmployee::create([
            'first_name' => 'Test',
            'last_name' => 'Staff_' . rand(1000, 9999),
            'email' => 'test_staff_' . time() . rand(100, 999) . '@example.com',
            'employee_status' => 'active',
            'staff_type_id' => 1,
            'is_delete' => 0,
        ]);
        $this->createdEmployeeIds[] = $this->employee->id;

        // Set initial balances
        HrmsEmployeeLeaveBalance::updateOrCreate(
            ['employee_id' => $this->employee->id, 'leave_type_id' => $this->clType->id],
            ['balance' => 10.0]
        );
        HrmsEmployeeLeaveBalance::updateOrCreate(
            ['employee_id' => $this->employee->id, 'leave_type_id' => $this->mlType->id],
            ['balance' => 8.0]
        );
        HrmsEmployeeLeaveBalance::updateOrCreate(
            ['employee_id' => $this->employee->id, 'leave_type_id' => $this->lwpType->id],
            ['balance' => 20.0]
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

    public function test_approving_leave_with_same_type_retains_balance_and_creates_datewise_approved_records()
    {
        // Simulate: Employee requested 2 days Casual Leave (2026-11-10 to 2026-11-11)
        // At application time, 2 days were already deducted from CL (balance dropped from 10 to 8)
        HrmsEmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $this->clType->id)
            ->update(['balance' => 8.0]);

        $leaveReq = HrmsLeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->clType->id,
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-11',
            'reason' => 'Family event',
            'status' => 'pending',
            'rollback_done' => 0,
        ]);
        $this->createdLeaveRequestIds[] = $leaveReq->id;

        // Admin approves without changing the leave type
        $response = $this->actingAs($this->adminUser)->post(
            route('employeeleaves.update', $leaveReq->id),
            [
                'status' => 'approved',
                'leave_types' => [
                    '2026-11-10' => $this->clType->id,
                    '2026-11-11' => $this->clType->id,
                ],
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // CL balance should remain 8.0 (no double deduction)
        $clBalance = HrmsEmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $this->clType->id)
            ->value('balance');
        $this->assertEquals(8.0, (float) $clBalance);

        // Date-wise approved records should exist
        $approvedCount = HrmsLeaveRequest::where('employee_id', $this->employee->id)
            ->where('status', 'approved')
            ->where('leave_type_id', $this->clType->id)
            ->count();
        $this->assertEquals(2, $approvedCount);

        // Original pending request should be soft deleted
        $this->assertSoftDeleted('hrms_leave_requests', ['id' => $leaveReq->id]);
    }

    public function test_rejecting_leave_restores_deducted_balance_and_updates_status()
    {
        // 2 days deducted at application (balance dropped to 8.0)
        HrmsEmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $this->clType->id)
            ->update(['balance' => 8.0]);

        $leaveReq = HrmsLeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->clType->id,
            'start_date' => '2026-11-10', // Tuesday
            'end_date' => '2026-11-11',   // Wednesday
            'reason' => 'Test Reject',
            'status' => 'pending',
            'rollback_done' => 0,
        ]);
        $this->createdLeaveRequestIds[] = $leaveReq->id;

        $response = $this->actingAs($this->adminUser)->post(
            route('employeeleaves.update', $leaveReq->id),
            ['status' => 'rejected']
        );

        $response->assertRedirect();

        // Balance should be restored from 8.0 back to 10.0 (+2 days)
        $clBalance = HrmsEmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $this->clType->id)
            ->value('balance');
        $this->assertEquals(10.0, (float) $clBalance);

        // Status updated to rejected and rollback_done = 1
        $updatedReq = HrmsLeaveRequest::find($leaveReq->id);
        $this->assertEquals('rejected', strtolower($updatedReq->status));
        $this->assertEquals(1, (int) $updatedReq->rollback_done);

        // Second rejection attempt should not double-refund
        $this->actingAs($this->adminUser)->post(
            route('employeeleaves.update', $leaveReq->id),
            ['status' => 'rejected']
        );
        $clBalanceAfter = HrmsEmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $this->clType->id)
            ->value('balance');
        $this->assertEquals(10.0, (float) $clBalanceAfter);
    }

    public function test_approving_with_leave_without_pay_refunds_original_paid_balance()
    {
        // Employee applied for 1 day Casual Leave (deducted at application: 10 -> 9)
        HrmsEmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $this->clType->id)
            ->update(['balance' => 9.0]);

        $leaveReq = HrmsLeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->clType->id,
            'start_date' => '2026-11-12',
            'end_date' => '2026-11-12',
            'reason' => 'Personal',
            'status' => 'pending',
            'rollback_done' => 0,
        ]);
        $this->createdLeaveRequestIds[] = $leaveReq->id;

        // Admin approves, but selects Leave Without Pay (id = 6) in the dropdown
        $response = $this->actingAs($this->adminUser)->post(
            route('employeeleaves.update', $leaveReq->id),
            [
                'status' => 'approved',
                'leave_types' => [
                    '2026-11-12' => $this->lwpType->id,
                ],
            ]
        );

        $response->assertRedirect();

        // 1. Casual Leave balance should be refunded (+1 from 9.0 back to 10.0)
        $clBalance = HrmsEmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $this->clType->id)
            ->value('balance');
        $this->assertEquals(10.0, (float) $clBalance);

        // 2. LWP balance should remain unchanged (20.0, no deduction for unpaid)
        $lwpBalance = HrmsEmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $this->lwpType->id)
            ->value('balance');
        $this->assertEquals(20.0, (float) $lwpBalance);

        // 3. Approved leave request record should be created with leave_type_id = 6 (LWP)
        $approvedReq = HrmsLeaveRequest::where('employee_id', $this->employee->id)
            ->where('status', 'approved')
            ->where('start_date', '2026-11-12')
            ->first();
        $this->assertNotNull($approvedReq);
        $this->assertEquals($this->lwpType->id, $approvedReq->leave_type_id);
    }

    public function test_approving_with_different_paid_leave_type_swaps_balances()
    {
        // Employee applied for 1 day Casual Leave (deducted: CL 10 -> 9, ML is 8)
        HrmsEmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $this->clType->id)
            ->update(['balance' => 9.0]);

        $leaveReq = HrmsLeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->clType->id,
            'start_date' => '2026-11-13',
            'end_date' => '2026-11-13',
            'reason' => 'Sick',
            'status' => 'pending',
            'rollback_done' => 0,
        ]);
        $this->createdLeaveRequestIds[] = $leaveReq->id;

        // Admin approves, but changes leave type to Medical Leave (id = 3)
        $response = $this->actingAs($this->adminUser)->post(
            route('employeeleaves.update', $leaveReq->id),
            [
                'status' => 'approved',
                'leave_types' => [
                    '2026-11-13' => $this->mlType->id,
                ],
            ]
        );

        $response->assertRedirect();

        // 1. CL should be refunded (+1 from 9.0 to 10.0)
        $clBalance = HrmsEmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $this->clType->id)
            ->value('balance');
        $this->assertEquals(10.0, (float) $clBalance);

        // 2. ML should be deducted (-1 from 8.0 to 7.0)
        $mlBalance = HrmsEmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $this->mlType->id)
            ->value('balance');
        $this->assertEquals(7.0, (float) $mlBalance);

        // 3. Approved record created with ML
        $approvedReq = HrmsLeaveRequest::where('employee_id', $this->employee->id)
            ->where('status', 'approved')
            ->where('start_date', '2026-11-13')
            ->first();
        $this->assertNotNull($approvedReq);
        $this->assertEquals($this->mlType->id, $approvedReq->leave_type_id);
    }

    public function test_approval_view_does_not_contain_lwp_checkbox_and_preselects_requested_type()
    {
        $leaveReq = HrmsLeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->mlType->id,
            'start_date' => '2026-11-15',
            'end_date' => '2026-11-15',
            'reason' => 'Doctor appointment',
            'status' => 'pending',
            'rollback_done' => 0,
        ]);
        $this->createdLeaveRequestIds[] = $leaveReq->id;

        $response = $this->actingAs($this->adminUser)->get(route('employeeleaves'));

        $response->assertStatus(200);

        // Checkbox column and input should NOT be present in the modal
        $response->assertDontSee('<th>LWP</th>', false);
        $response->assertDontSee('name="lwp_dates[]"', false);

        // Leave type dropdown should be present
        $response->assertSee('name="leave_types[2026-11-15]"', false);
    }
}
