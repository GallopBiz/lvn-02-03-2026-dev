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

        $isApprovedHalfDay = $halfDayRequest
            && strtolower(trim((string) ($halfDayRequest['status'] ?? ''))) === 'approved'
            && in_array(
                strtolower(trim((string) ($halfDayRequest['half_day_type'] ?? ''))),
                ['first', 'second'],
                true
            );

        if ($isApprovedHalfDay) {
            $halfDayType = strtolower(trim((string) $halfDayRequest['half_day_type']));
            if (
                $halfDayType === 'first'
                || ($halfDayType === 'second' && $inTime >= $shiftStart + (int) floor(($shiftEnd - $shiftStart) / 2))
            ) {
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

        if ($halfDayRequest) {
            if (
                $halfDayRequest['status'] === 'approved'
                && $halfDayRequest['half_day_type'] === 'second'
            ) {
                return ['late_events' => 0, 'absence_days' => 0.0];
            }

            $lateMinutes = $inTime ? ($inTime - $shiftStart) / 60 : 0;
            $lateEvents = $lateMinutes > 0
                && $lateMinutes <= 60
                && $lateMinutes > $lateComingThreshold
                ? 1
                : 0;

            return ['late_events' => $lateEvents, 'absence_days' => 0.0];
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
