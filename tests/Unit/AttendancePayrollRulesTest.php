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

    public function test_approved_first_half_leave_exempts_late_arrival(): void
    {
        [$start, $end] = $this->shiftTimes();
        $isLate = AttendancePayrollRules::isLateArrival(
            $start,
            $end,
            15,
            strtotime('2026-03-02 09:30:00'),
            ['status' => 'approved', 'half_day_type' => 'first']
        );

        $this->assertFalse($isLate);
    }

    public function test_approved_first_half_leave_arriving_during_second_half_is_not_late(): void
    {
        [$start, $end] = $this->shiftTimes();

        $isLate = AttendancePayrollRules::isLateArrival(
            $start,
            $end,
            15,
            strtotime('2026-03-02 13:30:00'),
            ['status' => 'approved', 'half_day_type' => 'first']
        );

        $this->assertFalse($isLate);
    }

    public function test_approved_second_half_leave_checks_late_arrival_during_first_half(): void
    {
        [$start, $end] = $this->shiftTimes();

        $isLate = AttendancePayrollRules::isLateArrival(
            $start,
            $end,
            15,
            strtotime('2026-03-02 09:30:00'),
            ['status' => 'approved', 'half_day_type' => 'second']
        );

        $this->assertTrue($isLate);
    }

    public function test_approved_second_half_leave_does_not_count_late_arrival_during_second_half(): void
    {
        [$start, $end] = $this->shiftTimes();

        $isLate = AttendancePayrollRules::isLateArrival(
            $start,
            $end,
            15,
            strtotime('2026-03-02 13:30:00'),
            ['status' => 'approved', 'half_day_type' => 'second']
        );

        $this->assertFalse($isLate);
    }

    public function test_approved_second_half_leave_does_not_count_on_time_first_half_arrival_as_late(): void
    {
        [$start, $end] = $this->shiftTimes();

        $isLate = AttendancePayrollRules::isLateArrival(
            $start,
            $end,
            15,
            strtotime('2026-03-02 09:15:00'),
            ['status' => 'approved', 'half_day_type' => 'second']
        );

        $this->assertFalse($isLate);
    }

    /**
     * @dataProvider nonApprovedHalfDayRequests
     */
    public function test_pending_or_rejected_half_day_request_keeps_normal_late_calculation(string $status, string $halfDayType): void
    {
        [$start, $end] = $this->shiftTimes();

        $isLate = AttendancePayrollRules::isLateArrival(
            $start,
            $end,
            15,
            strtotime('2026-03-02 09:30:00'),
            ['status' => $status, 'half_day_type' => $halfDayType]
        );

        $this->assertTrue($isLate);
    }

    public static function nonApprovedHalfDayRequests(): array
    {
        return [
            'pending first half' => ['pending', 'first'],
            'rejected first half' => ['rejected', 'first'],
            'pending second half' => ['pending', 'second'],
            'rejected second half' => ['rejected', 'second'],
        ];
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
