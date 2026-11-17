<?php

namespace App\Services\Hrms;

class AttendancePayrollRules
{
    public static function isLateArrival(
        int $shiftStart,
        int $shiftEnd,
        float $lateComingThreshold,
        ?int $inTime,
        ?array $halfDayRequest,
        bool $isOffDay = false
    ): bool {
        if ($isOffDay || !$inTime || $shiftEnd <= $shiftStart) {
            return false;
        }

        // Requirement: If a staff member has submitted a half-day leave request for that date
        // (Approved, Pending, or Rejected; First or Second Half), do NOT apply the late-coming rule.
        if (!empty($halfDayRequest)) {
            $status = strtolower(trim((string) ($halfDayRequest['status'] ?? '')));
            if (in_array($status, ['approved', 'pending', 'rejected'], true)) {
                return false;
            }
        }

        return $inTime > ($shiftStart + ($lateComingThreshold * 60));
    }

    public static function evaluateDay(
        int $shiftStart,
        int $shiftEnd,
        float $lateComingThreshold,
        ?int $inTime,
        ?int $outTime,
        ?array $halfDayRequest,
        bool $isOffDay
    ): array {
        if ($isOffDay || $shiftEnd <= $shiftStart) {
            return ['late_events' => 0, 'absence_days' => 0.0];
        }

        if (!empty($halfDayRequest)) {
            $status = strtolower(trim((string) ($halfDayRequest['status'] ?? '')));
            if (in_array($status, ['approved', 'pending', 'rejected'], true)) {
                $absenceDays = ($status === 'approved') ? 0.0 : 0.5;
                return ['late_events' => 0, 'absence_days' => $absenceDays];
            }
        }

        $lateEvents = 0;
        $absenceDays = 0.0;
        if ($inTime) {
            $lateMinutes = ($inTime - $shiftStart) / 60;
            if ($lateMinutes > 60) {
                $absenceDays = 0.5;
            } elseif ($lateMinutes > 0 && $lateMinutes > $lateComingThreshold) {
                $lateEvents++;
            }
        }

        if (
            $inTime &&
            $outTime &&
            $outTime < $shiftEnd &&
            (($shiftEnd - $outTime) / 60) > $lateComingThreshold
        ) {
            $lateEvents++;
        }

        if (!$inTime || !$outTime) {
            $absenceDays = 1.0;
        } elseif (
            $outTime > $inTime &&
            ($outTime - $inTime) < (($shiftEnd - $shiftStart) * 0.6)
        ) {
            $absenceDays = max($absenceDays, 0.5);
        }

        return [
            'late_events' => $lateEvents,
            'absence_days' => round($absenceDays, 1),
        ];
    }
}
