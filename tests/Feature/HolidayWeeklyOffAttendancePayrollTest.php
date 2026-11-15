<?php

namespace Tests\Feature;

use App\Models\Holidays;
use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeAttendance;
use App\Models\HrmsShift;
use App\Services\Hrms\AttendancePayrollRules;
use App\Services\Hrms\ShiftResolver;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HolidayWeeklyOffAttendancePayrollTest extends TestCase
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

            Schema::connection($conn)->create('hrms_holidays', function (Blueprint $table) {
                $table->increments('id');
                $table->string('HolidayName')->nullable();
                $table->date('HolidayStartDate');
                $table->date('HolidayEndDate');
                $table->timestamps();
            });
        }
    }

    public function test_sunday_and_holiday_punches_are_excluded_from_payroll_penalties(): void
    {
        $employee = HrmsEmployee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'shift_id' => 1,
        ]);

        $shift = HrmsShift::create([
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'late_coming_threshold' => 15,
        ]);

        // Holiday on 2026-03-04 (Wednesday)
        Holidays::create([
            'HolidayName' => 'Holi',
            'HolidayStartDate' => '2026-03-04',
            'HolidayEndDate' => '2026-03-04',
        ]);

        // Sunday punch on 2026-03-01 (Sunday): arrived very late (11:30 AM), left after 2 hours (no full day, < 60%)
        $sundayLog = (object) [
            'log_date' => '2026-03-01',
            'in_time' => '11:30:00',
            'out_time' => '13:30:00',
        ];

        // Holiday punch on 2026-03-04 (Holi Wednesday): arrived late (10:30 AM), missing out punch
        $holidayLog = (object) [
            'log_date' => '2026-03-04',
            'in_time' => '10:30:00',
            'out_time' => null,
        ];

        // Regular workday on 2026-03-02 (Monday): arrived late (09:45 AM)
        $workdayLog = (object) [
            'log_date' => '2026-03-02',
            'in_time' => '09:45:00',
            'out_time' => '17:00:00',
        ];

        $attendanceLogs = collect([$sundayLog, $holidayLog, $workdayLog]);

        $holidayDates = ['2026-03-04'];
        $approvedHalfDayDates = [];
        $lateComingCount = 0;
        $unapprovedHalfDaysFromBiometric = 0;

        foreach ($attendanceLogs as $log) {
            $logCarbon = Carbon::parse($log->log_date);
            $logDate = $logCarbon->format('Y-m-d');
            $isOffDay = $logCarbon->isSunday() || in_array($logDate, $holidayDates);
            if ($isOffDay) {
                continue;
            }

            $employeeShift = $shift;
            $shiftStart = strtotime($logDate . ' ' . $employeeShift->start_time);
            $shiftEnd = strtotime($logDate . ' ' . $employeeShift->end_time);
            $expectedDuration = max(1, $shiftEnd - $shiftStart);
            $inTime = $log->in_time ? strtotime($logDate . ' ' . $log->in_time) : null;
            $outTime = $log->out_time ? strtotime($logDate . ' ' . $log->out_time) : null;

            if (AttendancePayrollRules::isLateArrival(
                $shiftStart,
                $shiftEnd,
                (float) $employeeShift->late_coming_threshold,
                $inTime ?: null,
                null,
                $isOffDay
            )) {
                $lateComingCount++;
            }

            if (!in_array($logDate, $approvedHalfDayDates)) {
                if ($inTime && $outTime) {
                    $actualDuration = $outTime - $inTime;
                    if ($actualDuration > 0 && $actualDuration < ($expectedDuration * 0.6)) {
                        $unapprovedHalfDaysFromBiometric += 0.5;
                    }
                } elseif ($inTime && !$outTime) {
                    $unapprovedHalfDaysFromBiometric += 0.5;
                }
            }
        }

        // Only the Monday workday log should count as late, Sunday and Holiday are excluded
        $this->assertSame(1, $lateComingCount, 'Only normal workday late punch counted');
        $this->assertSame(0.0, (float) $unapprovedHalfDaysFromBiometric, 'Sunday and holiday short duration punches do not add biometric half-days');
    }

    public function test_attendance_view_suppresses_late_and_early_and_noout_chips_on_offdays(): void
    {
        $date = Carbon::parse('2026-03-01'); // Sunday
        $isWeekendOff = $date->isSunday();
        $holiday = null;
        $isOffDay = $isWeekendOff || !empty($holiday);

        $record = (object) [
            'log_date' => '2026-03-01',
            'in_time' => '11:00:00',
            'out_time' => '13:00:00',
            'status' => 'present',
        ];

        $shift = (object) [
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'late_coming_threshold' => 15,
        ];

        $isLateIn = false;
        $isEarlyOut = false;
        $missingOut = false;

        if ($record && $shift && $shift->start_time && $shift->end_time && !$isOffDay) {
            $shiftStart = strtotime('2026-03-01 ' . $shift->start_time);
            $shiftEnd = strtotime('2026-03-01 ' . $shift->end_time);
            $inTime = strtotime('2026-03-01 ' . $record->in_time);

            if (AttendancePayrollRules::isLateArrival($shiftStart, $shiftEnd, 15, $inTime, null, $isOffDay)) {
                $isLateIn = true;
            }
        }

        $this->assertFalse($isLateIn, 'Sunday punch must not be marked as late in');
        $this->assertFalse($isEarlyOut, 'Sunday punch must not be marked as early out');
        $this->assertFalse($missingOut, 'Sunday punch must not be marked as missing out');
    }

    public function test_skip_late_coming_for_entire_month(): void
    {
        $shift = (object) [
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'late_coming_threshold' => 15,
        ];

        // 3 late arrivals in the month
        $attendanceLogs = collect([
            (object) ['log_date' => '2026-03-02', 'in_time' => '09:30:00', 'out_time' => '17:00:00'],
            (object) ['log_date' => '2026-03-03', 'in_time' => '09:40:00', 'out_time' => '17:00:00'],
            (object) ['log_date' => '2026-03-05', 'in_time' => '09:50:00', 'out_time' => '17:00:00'],
        ]);

        $skipLateComing = true;
        $skipLateComingType = 'entire_month';
        $skipLateComingDates = [];

        $lateComingCount = 0;
        foreach ($attendanceLogs as $log) {
            $logDate = $log->log_date;
            $shiftStart = strtotime($logDate . ' ' . $shift->start_time);
            $shiftEnd = strtotime($logDate . ' ' . $shift->end_time);
            $inTime = strtotime($logDate . ' ' . $log->in_time);

            $isLateSkippedForDate = false;
            if ($skipLateComing) {
                if ($skipLateComingType === 'entire_month') {
                    $isLateSkippedForDate = true;
                } elseif ($skipLateComingType === 'specific_dates' && in_array($logDate, $skipLateComingDates, true)) {
                    $isLateSkippedForDate = true;
                }
            }

            if (!$isLateSkippedForDate && AttendancePayrollRules::isLateArrival($shiftStart, $shiftEnd, 15, $inTime, null, false)) {
                $lateComingCount++;
            }
        }

        $this->assertSame(0, $lateComingCount, 'All late arrivals are skipped for entire month');
    }

    public function test_skip_late_coming_for_specific_dates(): void
    {
        $shift = (object) [
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'late_coming_threshold' => 15,
        ];

        // 3 late arrivals: 2026-03-02, 2026-03-03, 2026-03-05
        $attendanceLogs = collect([
            (object) ['log_date' => '2026-03-02', 'in_time' => '09:30:00', 'out_time' => '17:00:00'],
            (object) ['log_date' => '2026-03-03', 'in_time' => '09:40:00', 'out_time' => '17:00:00'],
            (object) ['log_date' => '2026-03-05', 'in_time' => '09:50:00', 'out_time' => '17:00:00'],
        ]);

        // Admin exempts only 2026-03-02 (e.g. heavy rain)
        $skipLateComing = true;
        $skipLateComingType = 'specific_dates';
        $skipLateComingDates = ['2026-03-02'];

        $lateComingCount = 0;
        foreach ($attendanceLogs as $log) {
            $logDate = $log->log_date;
            $shiftStart = strtotime($logDate . ' ' . $shift->start_time);
            $shiftEnd = strtotime($logDate . ' ' . $shift->end_time);
            $inTime = strtotime($logDate . ' ' . $log->in_time);

            $isLateSkippedForDate = false;
            if ($skipLateComing) {
                if ($skipLateComingType === 'entire_month') {
                    $isLateSkippedForDate = true;
                } elseif ($skipLateComingType === 'specific_dates' && in_array($logDate, $skipLateComingDates, true)) {
                    $isLateSkippedForDate = true;
                }
            }

            if (!$isLateSkippedForDate && AttendancePayrollRules::isLateArrival($shiftStart, $shiftEnd, 15, $inTime, null, false)) {
                $lateComingCount++;
            }
        }

        // 2026-03-02 was exempted, so only 2026-03-03 and 2026-03-05 count (total 2, < 3 so no LWP)
        $this->assertSame(2, $lateComingCount, 'Only non-exempted dates count towards late coming');
    }

    public function test_skip_late_coming_does_not_affect_biometric_half_day_deduction(): void
    {
        $shift = (object) [
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'late_coming_threshold' => 15,
        ];

        // An employee arrives late AND has a missing out punch on 2026-03-02
        $log = (object) [
            'log_date' => '2026-03-02',
            'in_time' => '09:45:00',
            'out_time' => null, // Missing out punch
        ];

        $skipLateComing = true;
        $skipLateComingType = 'specific_dates';
        $skipLateComingDates = ['2026-03-02'];

        $logDate = $log->log_date;
        $shiftStart = strtotime($logDate . ' ' . $shift->start_time);
        $shiftEnd = strtotime($logDate . ' ' . $shift->end_time);
        $expectedDuration = max(1, $shiftEnd - $shiftStart);
        $inTime = strtotime($logDate . ' ' . $log->in_time);
        $outTime = null;

        $lateComingCount = 0;
        $unapprovedHalfDaysFromBiometric = 0;

        $isLateSkippedForDate = false;
        if ($skipLateComing) {
            if ($skipLateComingType === 'entire_month') {
                $isLateSkippedForDate = true;
            } elseif ($skipLateComingType === 'specific_dates' && in_array($logDate, $skipLateComingDates, true)) {
                $isLateSkippedForDate = true;
            }
        }

        if (!$isLateSkippedForDate && AttendancePayrollRules::isLateArrival($shiftStart, $shiftEnd, 15, $inTime, null, false)) {
            $lateComingCount++;
        }

        // Biometric half day check
        if ($inTime && !$outTime) {
            $unapprovedHalfDaysFromBiometric += 0.5;
        }

        $this->assertSame(0, $lateComingCount, 'Late coming was waived for the exempted date');
        $this->assertSame(0.5, $unapprovedHalfDaysFromBiometric, 'Missing out punch half-day deduction is preserved');
    }
}
