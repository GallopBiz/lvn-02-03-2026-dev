<?php

namespace Tests\Unit;

use App\Services\Hrms\AttendancePayrollRules;
use PHPUnit\Framework\TestCase;

class AttendancePayrollRulesTest extends TestCase
{
    private function shiftTimes(): array
    {
        return [
            strtotime('2026-03-02 09:00:00'),
            strtotime('2026-03-02 17:00:00'),
        ];
    }

    public function test_late_arrival_within_one_hour_uses_existing_threshold(): void
    {
        [$start, $end] = $this->shiftTimes();

        $result = AttendancePayrollRules::evaluateDay(
            $start,
            $end,
            15,
            strtotime('2026-03-02 09:30:00'),
            strtotime('2026-03-02 17:00:00'),
            null,
            false
        );

        $this->assertSame(['late_events' => 1, 'absence_days' => 0.0], $result);
    }

    public function test_arrival_after_one_hour_is_half_day_absence(): void
    {
        [$start, $end] = $this->shiftTimes();

        $result = AttendancePayrollRules::evaluateDay(
            $start,
            $end,
            60,
            strtotime('2026-03-02 10:01:00'),
            strtotime('2026-03-02 17:00:00'),
            null,
            false
        );

        $this->assertSame(['late_events' => 0, 'absence_days' => 0.5], $result);
    }

    public function test_missing_punch_out_counts_as_a_full_absence(): void
    {
        [$start, $end] = $this->shiftTimes();

        $result = AttendancePayrollRules::evaluateDay(
            $start,
            $end,
            60,
            strtotime('2026-03-02 09:00:00'),
            null,
            null,
            false
        );

        $this->assertSame(['late_events' => 0, 'absence_days' => 1.0], $result);
    }

    public function test_first_half_leave_uses_second_half_reporting_time_and_grace(): void
    {
        [$start, $end] = $this->shiftTimes();
        $firstHalfRequest = ['status' => 'approved', 'half_day_type' => 'first'];

        $result = AttendancePayrollRules::evaluateDay(
            $start,
            $end,
            60,
            strtotime('2026-03-02 14:01:00'),
            strtotime('2026-03-02 17:00:00'),
            $firstHalfRequest,
            false
        );

        $this->assertSame(['late_events' => 1, 'absence_days' => 0.0], $result);
    }

    public function test_first_half_leave_arrival_within_grace_is_not_late(): void
    {
        [$start, $end] = $this->shiftTimes();
        $firstHalfRequest = ['status' => 'approved', 'half_day_type' => 'first'];

        $result = AttendancePayrollRules::evaluateDay(
            $start,
            $end,
            60,
            strtotime('2026-03-02 14:00:00'),
            strtotime('2026-03-02 17:00:00'),
            $firstHalfRequest,
            false
        );

        $this->assertSame(['late_events' => 0, 'absence_days' => 0.0], $result);
    }

    public function test_approved_second_half_leave_does_not_count_early_leaving(): void
    {
        [$start, $end] = $this->shiftTimes();
        $secondHalfRequest = ['status' => 'approved', 'half_day_type' => 'second'];

        $result = AttendancePayrollRules::evaluateDay(
            $start,
            $end,
            30,
            strtotime('2026-03-02 09:00:00'),
            strtotime('2026-03-02 13:00:00'),
            $secondHalfRequest,
            false
        );

        $this->assertSame(['late_events' => 0, 'absence_days' => 0.0], $result);
    }

    public function test_unrequested_early_departure_uses_shift_threshold(): void
    {
        [$start, $end] = $this->shiftTimes();

        $result = AttendancePayrollRules::evaluateDay(
            $start,
            $end,
            30,
            strtotime('2026-03-02 09:00:00'),
            strtotime('2026-03-02 16:29:00'),
            null,
            false
        );

        $this->assertSame(['late_events' => 1, 'absence_days' => 0.0], $result);
    }

    public function test_holiday_and_weekly_off_suppress_late_and_early_events(): void
    {
        [$start, $end] = $this->shiftTimes();

        $result = AttendancePayrollRules::evaluateDay(
            $start,
            $end,
            15,
            strtotime('2026-03-02 10:00:00'),
            strtotime('2026-03-02 16:00:00'),
            null,
            true
        );

        $this->assertSame(['late_events' => 0, 'absence_days' => 0.0], $result);
    }
}
