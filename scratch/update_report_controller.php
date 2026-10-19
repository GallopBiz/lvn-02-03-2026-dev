<?php
$file = __DIR__ . '/../app/Http/Controllers/backend/TransportReportController.php';
$content = file_get_contents($file);

$target = <<<'PHP'
        return $registrations->map(function ($registration) use ($driverBusMap, $driverDetails, $feeRecords) {
            $studentData = json_decode($registration->json_str, true) ?: [];
            if (empty($studentData['required_school_transport'])) {
                return null;
            }

            $driver = trim((string) ($studentData['driver_name'] ?? ''));
            $fee = $this->busFee($feeRecords->get($registration->id));
            $busAssignments = $driverBusMap[$driver] ?? [];
            $driverMobileNo = $this->uniqueValues(collect($busAssignments)->pluck('driver_mobile_no')->all())
                ?: $this->driverMobileByName($driver, $driverDetails);

            return [
                'student_name' => $registration->student_name,
                'scholar_no' => $registration->scholar_no,
                'class_name' => $registration->class_name,
                'section_name' => $studentData['sectionname'] ?? $studentData['section_name'] ?? '',
                'driver_name' => $driver,
                'driver_mobile_no' => $driverMobileNo,
                'bus_no' => $this->uniqueValues(collect($busAssignments)->pluck('bus_no')->all()),
                'vehicle_no' => $this->uniqueValues(collect($busAssignments)->pluck('vehicle_no')->all()),
                'bus_fee' => $fee,
                'start_date' => $studentData['bus_facility_start_date'] ?? '',
                'stop_date' => $studentData['bus_facility_end_date'] ?? '',
            ];
        })->filter()->values();
PHP;

$replacement = <<<'PHP'
        return $registrations->map(function ($registration) use ($driverBusMap, $driverDetails, $feeRecords) {
            $studentData = json_decode($registration->json_str, true) ?: [];

            // Exclude if bus transport is not requested or explicitly disabled
            $transport = $studentData['required_school_transport'] ?? null;
            if (empty($transport) || in_array($transport, ['0', 0, false, 'off', 'false', ''], true)) {
                return null;
            }

            // Exclude if bus facility has been stopped
            $startDate = null;
            $stopDate = null;

            if (!empty($studentData['bus_facility_start_date'])) {
                try {
                    $startDate = Carbon::parse($studentData['bus_facility_start_date'])->startOfDay();
                } catch (\Throwable $e) {}
            }

            if (!empty($studentData['bus_facility_end_date'])) {
                try {
                    $stopDate = Carbon::parse($studentData['bus_facility_end_date'])->startOfDay();
                } catch (\Throwable $e) {}
            }

            if ($stopDate) {
                // If stop date is on or after start date (or no start date), and stop date is reached (today or past)
                $isCurrentStop = !$startDate || $stopDate->greaterThanOrEqualTo($startDate);
                if ($isCurrentStop && ($stopDate->isPast() || $stopDate->isToday())) {
                    return null;
                }
            }

            $driver = trim((string) ($studentData['driver_name'] ?? ''));
            $fee = $this->busFee($feeRecords->get($registration->id));
            $busAssignments = $driverBusMap[$driver] ?? [];
            $driverMobileNo = $this->uniqueValues(collect($busAssignments)->pluck('driver_mobile_no')->all())
                ?: $this->driverMobileByName($driver, $driverDetails);

            $displayStopDate = '';
            if ($stopDate && (!$startDate || $stopDate->greaterThanOrEqualTo($startDate))) {
                $displayStopDate = $studentData['bus_facility_end_date'] ?? '';
            }

            return [
                'student_name' => $registration->student_name,
                'scholar_no' => $registration->scholar_no,
                'class_name' => $registration->class_name,
                'section_name' => $studentData['sectionname'] ?? $studentData['section_name'] ?? '',
                'driver_name' => $driver,
                'driver_mobile_no' => $driverMobileNo,
                'bus_no' => $this->uniqueValues(collect($busAssignments)->pluck('bus_no')->all()),
                'vehicle_no' => $this->uniqueValues(collect($busAssignments)->pluck('vehicle_no')->all()),
                'bus_fee' => $fee,
                'start_date' => $studentData['bus_facility_start_date'] ?? '',
                'stop_date' => $displayStopDate,
            ];
        })->filter()->values();
PHP;

// Normalize line endings for replacement
$normalizedContent = str_replace("\r\n", "\n", $content);
$normalizedTarget = str_replace("\r\n", "\n", $target);
$normalizedReplacement = str_replace("\r\n", "\n", $replacement);

if (strpos($normalizedContent, $normalizedTarget) !== false) {
    $newContent = str_replace($normalizedTarget, $normalizedReplacement, $normalizedContent);
    // preserve CRLF if original had it
    if (strpos($content, "\r\n") !== false) {
        $newContent = str_replace("\n", "\r\n", $newContent);
    }
    file_put_contents($file, $newContent);
    echo "SUCCESS: TransportReportController updated successfully!\n";
} else {
    echo "ERROR: Target string not found in TransportReportController!\n";
}
