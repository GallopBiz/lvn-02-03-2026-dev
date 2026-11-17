<?php

namespace Tests\Feature;

use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeAttendance;
use App\Models\HrmsLeaveRequest;
use App\Models\HrmsLeaveType;
use App\Models\HrmsShift;
use App\Services\Hrms\AttendancePayrollRules;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HalfDayAttendancePayrollRulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.dynamic' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
        ]);
        DB::purge('sqlite');
        DB::purge('dynamic');
        DB::setDefaultConnection('sqlite');

        foreach (['sqlite', 'dynamic'] as $conn) {
            Schema::connection($conn)->create('hrms_employees', function (Blueprint $table) {
                $table->increments('id');
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->unsignedInteger('shift_id')->nullable();
                $table->string('employee_status')->default('active');
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

            Schema::connection($conn)->create('hrms_leave_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->tinyInteger('is_paid')->default(1);
                $table->timestamps();
            });

            Schema::connection($conn)->create('hrms_leave_requests', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('employee_id');
                $table->unsignedInteger('leave_type_id')->nullable();
                $table->string('status')->default('Pending');
                $table->string('is_half_day')->default('1');
                $table->string('half_day_type')->default('first');
                $table->date('start_date');
                $table->date('end_date');
                $table->text('reason')->nullable();
                $table->string('db_name')->nullable();
                $table->timestamps();
            });

            Schema::connection($conn)->create('hrms_holidays', function (Blueprint $table) {
                $table->increments('id');
                $table->string('HolidayName')->nullable();
                $table->date('HolidayStartDate');
                $table->date('HolidayEndDate');
                $table->string('for_hrms')->default('0');
                $table->string('for_academic')->default('1');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function test_half_day_requests_prevent_late_coming_for_all_statuses(): void
    {
        $shiftStart = strtotime('2026-11-05 09:00:00');
        $shiftEnd = strtotime('2026-11-05 17:00:00');
        $lateIn = strtotime('2026-11-05 10:30:00');

        // With no half-day leave request -> isLateArrival is true
        $this->assertTrue(AttendancePayrollRules::isLateArrival($shiftStart, $shiftEnd, 15, $lateIn, null));

        // With Pending half-day request -> isLateArrival is false
        $this->assertFalse(AttendancePayrollRules::isLateArrival($shiftStart, $shiftEnd, 15, $lateIn, [
            'status' => 'pending',
            'half_day_type' => 'first',
        ]));
        $this->assertFalse(AttendancePayrollRules::isLateArrival($shiftStart, $shiftEnd, 15, $lateIn, [
            'status' => 'pending',
            'half_day_type' => 'second',
        ]));

        // With Approved half-day request -> isLateArrival is false
        $this->assertFalse(AttendancePayrollRules::isLateArrival($shiftStart, $shiftEnd, 15, $lateIn, [
            'status' => 'approved',
            'half_day_type' => 'first',
        ]));
        $this->assertFalse(AttendancePayrollRules::isLateArrival($shiftStart, $shiftEnd, 15, $lateIn, [
            'status' => 'approved',
            'half_day_type' => 'second',
        ]));

        // With Rejected half-day request -> isLateArrival is false
        $this->assertFalse(AttendancePayrollRules::isLateArrival($shiftStart, $shiftEnd, 15, $lateIn, [
            'status' => 'rejected',
            'half_day_type' => 'first',
        ]));
        $this->assertFalse(AttendancePayrollRules::isLateArrival($shiftStart, $shiftEnd, 15, $lateIn, [
            'status' => 'rejected',
            'half_day_type' => 'second',
        ]));
    }

    public function test_salary_deduction_rules_for_half_day_leave(): void
    {
        $shift = HrmsShift::create([
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'late_coming_threshold' => 15,
        ]);
        $shiftStart = strtotime('2026-11-05 ' . $shift->start_time);
        $shiftEnd = strtotime('2026-11-05 ' . $shift->end_time);
        $punchIn = strtotime('2026-11-05 13:00:00');
        $punchOut = strtotime('2026-11-05 17:00:00');

        // Approved half-day leave: 0 late events, 0.0 absence deduction
        $approvedResult = AttendancePayrollRules::evaluateDay(
            $shiftStart,
            $shiftEnd,
            15,
            $punchIn,
            $punchOut,
            ['status' => 'approved', 'half_day_type' => 'first'],
            false
        );
        $this->assertSame(0, $approvedResult['late_events']);
        $this->assertSame(0.0, $approvedResult['absence_days']);

        // Pending half-day leave: 0 late events, 0.5 absence deduction
        $pendingResult = AttendancePayrollRules::evaluateDay(
            $shiftStart,
            $shiftEnd,
            15,
            $punchIn,
            $punchOut,
            ['status' => 'pending', 'half_day_type' => 'first'],
            false
        );
        $this->assertSame(0, $pendingResult['late_events']);
        $this->assertSame(0.5, $pendingResult['absence_days']);

        // Rejected half-day leave: 0 late events, 0.5 absence deduction
        $rejectedResult = AttendancePayrollRules::evaluateDay(
            $shiftStart,
            $shiftEnd,
            15,
            $punchIn,
            $punchOut,
            ['status' => 'rejected', 'half_day_type' => 'first'],
            false
        );
        $this->assertSame(0, $rejectedResult['late_events']);
        $this->assertSame(0.5, $rejectedResult['absence_days']);
    }

    public function test_employee_attendance_view_suppresses_late_badge_when_half_day_request_exists(): void
    {
        $shift = HrmsShift::create([
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'late_coming_threshold' => 15,
        ]);

        $employee = HrmsEmployee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'shift_id' => $shift->id,
        ]);

        $date = Carbon::parse('2026-11-05'); // A Thursday

        // Biometric log showing employee arrived late (10:30 AM)
        $attendance = HrmsEmployeeAttendance::create([
            'employee_id' => $employee->id,
            'log_date' => '2026-11-05',
            'in_time' => '10:30:00',
            'out_time' => '17:00:00',
            'status' => 'Present',
        ]);

        // Pending half-day leave request
        $leaveRequest = HrmsLeaveRequest::create([
            'employee_id' => $employee->id,
            'status' => 'Pending',
            'is_half_day' => '1',
            'half_day_type' => 'first',
            'start_date' => '2026-11-05',
            'end_date' => '2026-11-05',
        ]);

        $rendered = view('backend.HRMS.employeeattendance', [
            'employees' => collect([$employee]),
            'employeeShifts' => collect([$employee->id => $shift]),
            'attendanceData' => collect([$employee->id => collect([$attendance])]),
            'leaveRequests' => collect([$leaveRequest]),
            'startDate' => '2026-11-01',
            'endDate' => '2026-11-30',
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();

        // Must display Half Day badge and must NOT display Late badge in the attendance cell
        $this->assertStringContainsString('title="Half Day Leave (Pending)"', $rendered);
        $this->assertStringContainsString('Half Day', $rendered);
        $this->assertStringNotContainsString('title="Late by', $rendered);
    }

    public function test_employee_attendance_view_displays_late_badge_when_no_half_day_request_exists(): void
    {
        $shift = HrmsShift::create([
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'late_coming_threshold' => 15,
        ]);

        $employee = HrmsEmployee::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'shift_id' => $shift->id,
        ]);

        $attendance = HrmsEmployeeAttendance::create([
            'employee_id' => $employee->id,
            'log_date' => '2026-11-05',
            'in_time' => '10:30:00',
            'out_time' => '17:00:00',
            'status' => 'Present',
        ]);

        $rendered = view('backend.HRMS.employeeattendance', [
            'employees' => collect([$employee]),
            'employeeShifts' => collect([$employee->id => $shift]),
            'attendanceData' => collect([$employee->id => collect([$attendance])]),
            'leaveRequests' => collect([]),
            'startDate' => '2026-11-01',
            'endDate' => '2026-11-30',
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();

        // Must display Late badge with title
        $this->assertStringContainsString('title="Late by', $rendered);
        $this->assertStringNotContainsString('title="Half Day Leave', $rendered);
    }
}
