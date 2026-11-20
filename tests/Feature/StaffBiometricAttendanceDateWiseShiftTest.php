<?php

namespace Tests\Feature;

use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeAttendance;
use App\Models\HrmsEmployeeShiftHistory;
use App\Models\HrmsShift;
use App\Services\Hrms\AttendancePayrollRules;
use App\Services\Hrms\ShiftResolver;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StaffBiometricAttendanceDateWiseShiftTest extends TestCase
{
    protected string $tempDbFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDbFile = tempnam(sys_get_temp_dir(), 'test_hrms_');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $this->tempDbFile,
            'database.connections.sqlite.prefix' => '',
            'database.connections.dynamic' => [
                'driver' => 'sqlite',
                'database' => $this->tempDbFile,
                'prefix' => '',
            ],
        ]);
        DB::purge('sqlite');
        DB::purge('dynamic');
        DB::setDefaultConnection('sqlite');

        \Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag());

        foreach (['sqlite'] as $conn) {
            Schema::connection($conn)->create('hrms_employees', function (Blueprint $table) {
                $table->increments('id');
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->unsignedInteger('shift_id')->nullable();
                $table->string('employee_status')->default('active');
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection($conn)->create('hrms_shift_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('shift_type_name')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection($conn)->create('hrms_shifts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('start_time')->default('09:00:00');
                $table->string('end_time')->default('17:00:00');
                $table->integer('late_coming_threshold')->default(15);
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection($conn)->create('hrms_employee_shift_history', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('employee_id');
                $table->unsignedInteger('shift_id');
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->string('remarks')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamps();
            });

            Schema::connection($conn)->create('hrms_employee_attendance', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('employee_id')->nullable();
                $table->string('ess_emp_code')->nullable();
                $table->date('log_date');
                $table->time('in_time')->nullable();
                $table->time('out_time')->nullable();
                $table->string('status')->default('Present');
                $table->timestamps();
            });

            Schema::connection($conn)->create('hrms_holidays', function (Blueprint $table) {
                $table->increments('id');
                $table->string('HolidayName')->nullable();
                $table->date('HolidayStartDate');
                $table->date('HolidayEndDate');
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection($conn)->create('hrms_leave_requests', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('employee_id');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('is_half_day')->nullable();
                $table->string('half_day_type')->nullable();
                $table->string('status')->default('approved');
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        DB::disconnect('dynamic');
        if (isset($this->tempDbFile) && file_exists($this->tempDbFile)) {
            @unlink($this->tempDbFile);
        }
        parent::tearDown();
    }

    public function test_default_shift_used_when_no_shift_history_exists(): void
    {
        $defaultShift = HrmsShift::create([
            'id' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'late_coming_threshold' => 15,
        ]);

        $employee = HrmsEmployee::create([
            'id' => 101,
            'first_name' => 'Alice',
            'last_name' => 'Sharma',
            'shift_id' => $defaultShift->id,
        ]);

        $resolvedShift = ShiftResolver::getApplicableShift($employee, '2026-10-02');
        $this->assertNotNull($resolvedShift);
        $this->assertEquals(1, $resolvedShift->id);
        $this->assertEquals('09:00:00', $resolvedShift->start_time);
        $this->assertEquals('17:00:00', $resolvedShift->end_time);

        // Employee default shift must remain unchanged
        $this->assertEquals(1, $employee->fresh()->shift_id);
    }

    public function test_temporary_shift_history_takes_priority_only_during_effective_dates(): void
    {
        $defaultShift = HrmsShift::create([
            'id' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'late_coming_threshold' => 15,
        ]);

        $tempShift = HrmsShift::create([
            'id' => 2,
            'start_time' => '07:00:00',
            'end_time' => '14:00:00',
            'late_coming_threshold' => 10,
        ]);

        $employee = HrmsEmployee::create([
            'id' => 102,
            'first_name' => 'Bob',
            'last_name' => 'Verma',
            'shift_id' => $defaultShift->id,
        ]);

        // Temporary shift assigned from 2026-10-01 to 2026-10-05
        HrmsEmployeeShiftHistory::create([
            'employee_id' => $employee->id,
            'shift_id' => $tempShift->id,
            'effective_from' => '2026-10-01',
            'effective_to' => '2026-10-05',
        ]);

        // 1. Before effective date (2026-09-30): Should use default shift (Shift 1)
        $shiftBefore = ShiftResolver::getApplicableShift($employee, '2026-09-30');
        $this->assertEquals(1, $shiftBefore->id);
        $this->assertEquals('09:00:00', $shiftBefore->start_time);

        // 2. During temporary period (2026-10-02): Should use temporary shift (Shift 2)
        $shiftDuring = ShiftResolver::getApplicableShift($employee, '2026-10-02');
        $this->assertEquals(2, $shiftDuring->id);
        $this->assertEquals('07:00:00', $shiftDuring->start_time);
        $this->assertEquals('14:00:00', $shiftDuring->end_time);

        // 3. After temporary period (2026-10-06): Should revert to default shift (Shift 1)
        $shiftAfter = ShiftResolver::getApplicableShift($employee, '2026-10-06');
        $this->assertEquals(1, $shiftAfter->id);
        $this->assertEquals('09:00:00', $shiftAfter->start_time);

        // Employee's default shift in hrms_employees must NOT be modified
        $this->assertEquals(1, $employee->fresh()->shift_id);
    }

    public function test_bulk_shift_resolution_for_range(): void
    {
        $shiftA = HrmsShift::create([
            'id' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $shiftB = HrmsShift::create([
            'id' => 2,
            'start_time' => '13:00:00',
            'end_time' => '21:00:00',
        ]);

        $emp1 = HrmsEmployee::create(['id' => 1, 'shift_id' => $shiftA->id]);
        $emp2 = HrmsEmployee::create(['id' => 2, 'shift_id' => $shiftB->id]);

        // emp1 has temporary shift B on 2026-10-02 only
        HrmsEmployeeShiftHistory::create([
            'employee_id' => 1,
            'shift_id' => $shiftB->id,
            'effective_from' => '2026-10-02',
            'effective_to' => '2026-10-02',
        ]);

        $rangeMatrix = ShiftResolver::getApplicableShiftsForRange(
            collect([$emp1, $emp2]),
            '2026-10-01',
            '2026-10-03'
        );

        // emp1: 2026-10-01 should be shiftA (1), 2026-10-02 should be shiftB (2), 2026-10-03 should be shiftA (1)
        $this->assertEquals(1, $rangeMatrix[1]['2026-10-01']->id);
        $this->assertEquals(2, $rangeMatrix[1]['2026-10-02']->id);
        $this->assertEquals(1, $rangeMatrix[1]['2026-10-03']->id);

        // emp2: All dates should be default shiftB (2)
        $this->assertEquals(2, $rangeMatrix[2]['2026-10-01']->id);
        $this->assertEquals(2, $rangeMatrix[2]['2026-10-02']->id);
        $this->assertEquals(2, $rangeMatrix[2]['2026-10-03']->id);
    }

    public function test_biometric_late_arrival_evaluated_against_applicable_date_shift(): void
    {
        $defaultShift = HrmsShift::create([
            'id' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'late_coming_threshold' => 15,
        ]);

        $earlyShift = HrmsShift::create([
            'id' => 2,
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'late_coming_threshold' => 15,
        ]);

        $employee = HrmsEmployee::create([
            'id' => 103,
            'first_name' => 'Charlie',
            'shift_id' => $defaultShift->id,
        ]);

        // Assign early shift for 2026-10-05 (Monday)
        HrmsEmployeeShiftHistory::create([
            'employee_id' => $employee->id,
            'shift_id' => $earlyShift->id,
            'effective_from' => '2026-10-05',
            'effective_to' => '2026-10-05',
        ]);

        // Punch at 07:30 AM on 2026-10-05:
        // Under Early Shift (07:00 + 15 min threshold = 07:15): 07:30 is LATE.
        // Under Default Shift (09:00): 07:30 would NOT be late.
        $dateStr = '2026-10-05';
        $applicableShift = ShiftResolver::getApplicableShift($employee, $dateStr);
        $this->assertEquals(2, $applicableShift->id);

        $shiftStart = strtotime($dateStr . ' ' . $applicableShift->start_time);
        $shiftEnd = strtotime($dateStr . ' ' . $applicableShift->end_time);
        $threshold = (float) $applicableShift->late_coming_threshold;
        $inTime = strtotime($dateStr . ' 07:30:00');

        $isLate = AttendancePayrollRules::isLateArrival($shiftStart, $shiftEnd, $threshold, $inTime, null, false);
        $this->assertTrue($isLate, 'Employee must be marked late based on the temporary shift timings');

        // On 2026-10-06 (reverted to default 09:00 shift), punch at 08:50 AM:
        $nextDateStr = '2026-10-06';
        $nextShift = ShiftResolver::getApplicableShift($employee, $nextDateStr);
        $this->assertEquals(1, $nextShift->id);

        $nextShiftStart = strtotime($nextDateStr . ' ' . $nextShift->start_time);
        $nextShiftEnd = strtotime($nextDateStr . ' ' . $nextShift->end_time);
        $nextThreshold = (float) $nextShift->late_coming_threshold;
        $nextInTime = strtotime($nextDateStr . ' 08:50:00');

        $isNextLate = AttendancePayrollRules::isLateArrival($nextShiftStart, $nextShiftEnd, $nextThreshold, $nextInTime, null, false);
        $this->assertFalse($isNextLate, 'Employee must be on time based on the default shift timings');
    }

    public function test_view_renders_date_wise_shift_in_tooltip_and_default_shift_in_sidebar(): void
    {
        $defaultShift = HrmsShift::create([
            'id' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'late_coming_threshold' => 15,
        ]);

        $tempShift = HrmsShift::create([
            'id' => 2,
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'late_coming_threshold' => 15,
        ]);

        $employee = HrmsEmployee::create([
            'id' => 104,
            'first_name' => 'Deepak',
            'last_name' => 'Patel',
            'shift_id' => $defaultShift->id,
            'employee_status' => 'active',
        ]);

        HrmsEmployeeShiftHistory::create([
            'employee_id' => $employee->id,
            'shift_id' => $tempShift->id,
            'effective_from' => '2026-10-01',
            'effective_to' => '2026-10-01',
        ]);

        $att1 = HrmsEmployeeAttendance::create([
            'employee_id' => $employee->id,
            'ess_emp_code' => 'EMP104',
            'log_date' => '2026-10-01',
            'in_time' => '07:05:00',
            'out_time' => '15:05:00',
            'status' => 'Present',
        ]);

        $att2 = HrmsEmployeeAttendance::create([
            'employee_id' => $employee->id,
            'ess_emp_code' => 'EMP104',
            'log_date' => '2026-10-02',
            'in_time' => '08:55:00',
            'out_time' => '17:05:00',
            'status' => 'Present',
        ]);

        $employees = collect([$employee]);
        $employeeShifts = collect([$employee->id => $defaultShift]);
        $startDate = '2026-10-01';
        $endDate = '2026-10-02';

        $dateWiseShifts = ShiftResolver::getApplicableShiftsForRange($employees, $startDate, $endDate);
        $attendanceData = collect([$employee->id => collect([$att1, $att2])]);
        $leaveRequests = collect();

        $rendered = view('backend.HRMS.employeeattendance', compact(
            'attendanceData',
            'employees',
            'employeeShifts',
            'dateWiseShifts',
            'startDate',
            'endDate',
            'leaveRequests'
        ))->render();

        // 1. Sidebar column shows default shift: 09:00 AM - 05:00 PM
        $this->assertStringContainsString('Shift: 09:00 AM - 05:00 PM', $rendered);

        // 2. Oct 1 tooltip shows temporary shift: 07:00 AM - 03:00 PM
        $this->assertStringContainsString('Shift: 07:00 AM - 03:00 PM', $rendered);
    }
}
