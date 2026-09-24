<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransportReportController extends Controller
{
    public function __construct(Request $request)
    {
        $database = $request->cookie('selectedYear')
            ?: $request->input('session')
            ?: $request->input('session_name')
            ?: config('database.connections.dynamic.database');

        $dynamicConfig = Config::get('database.connections.dynamic');
        $dynamicConfig['database'] = $database;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');
    }

    public function index(Request $request)
    {
        $connection = DB::connection('dynamic');
        $students = $this->reportStudents($connection);

        $drivers = $students->pluck('driver_name')->filter()->unique()->sort()->values();
        $buses = $students->pluck('vehicle_no')
            ->flatMap(fn ($vehicleNumbers) => explode(',', $vehicleNumbers))
            ->map(fn ($vehicleNumber) => trim($vehicleNumber))
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $driver = trim((string) $request->input('driver'));
        $bus = trim((string) $request->input('bus'));

        if ($driver !== '') {
            $students = $students->where('driver_name', $driver)->values();
        }
        if ($bus !== '') {
            $students = $this->filterByVehicleNo($students, $bus);
        }

        $studentsTotal = $students->count();
        $busFeesTotal = $students->sum('bus_fee');
        $summarySearch = trim((string) $request->input('summary_search'));
        $studentsSearch = trim((string) $request->input('students_search'));
        $studentDriver = trim((string) $request->input('student_driver'));
        $studentBusNo = trim((string) $request->input('student_bus_no'));
        $studentBusAmount = trim((string) $request->input('student_bus_amount'));

        $summary = $this->summaryRows($students, $summarySearch);

        $summaryTotal = $summary->count();
        $summaryPage = max(1, (int) $request->input('summary_page', 1));
        $summary = new LengthAwarePaginator(
            $summary->forPage($summaryPage, 20)->values(),
            $summaryTotal,
            20,
            $summaryPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'summary_page',
            ]
        );

        $studentDrivers = $students->pluck('driver_name')->filter()->unique()->sort()->values();
        $studentBusNos = $students->pluck('bus_no')
            ->flatMap(fn ($busNumbers) => explode(',', $busNumbers))
            ->map(fn ($busNumber) => trim($busNumber))
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $studentBusAmounts = $students->pluck('bus_fee')
            ->unique()
            ->sort()
            ->values();

        $students = $this->studentRows($students, $studentsSearch, $studentDriver, $studentBusNo, $studentBusAmount);

        $studentsPage = max(1, (int) $request->input('students_page', 1));
        $students = new LengthAwarePaginator(
            $students->forPage($studentsPage, 20)->values(),
            $students->count(),
            20,
            $studentsPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'students_page',
            ]
        );

        return view('backend.Transport.report', compact('students', 'studentsTotal', 'busFeesTotal', 'summary', 'summaryTotal', 'summarySearch', 'studentsSearch', 'studentDriver', 'studentBusNo', 'studentBusAmount', 'studentDrivers', 'studentBusNos', 'studentBusAmounts', 'drivers', 'buses', 'driver', 'bus'));
    }

    public function exportSummaryCsv(Request $request)
    {
        $connection = DB::connection('dynamic');
        $students = $this->reportStudents($connection);

        $driver = trim((string) $request->input('driver'));
        $bus = trim((string) $request->input('bus'));
        $summarySearch = trim((string) $request->input('summary_search'));

        if ($driver !== '') {
            $students = $students->where('driver_name', $driver)->values();
        }
        if ($bus !== '') {
            $students = $this->filterByVehicleNo($students, $bus);
        }

        $summary = $this->summaryRows($students, $summarySearch);
        $filename = 'driver-bus-strength-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($summary) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['S.No.', 'Driver', 'Driver Mobile No.', 'Bus No.', 'Students', 'Bus Fees Total']);

            foreach ($summary as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row['driver_name'],
                    $row['driver_mobile_no'] ?: '-',
                    $row['bus_no'],
                    $row['student_count'],
                    number_format($row['total_bus_fees'], 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportStudentsCsv(Request $request)
    {
        $connection = DB::connection('dynamic');
        $students = $this->reportStudents($connection);

        $driver = trim((string) $request->input('driver'));
        $bus = trim((string) $request->input('bus'));
        $studentsSearch = trim((string) $request->input('students_search'));
        $studentDriver = trim((string) $request->input('student_driver'));
        $studentBusNo = trim((string) $request->input('student_bus_no'));
        $studentBusAmount = trim((string) $request->input('student_bus_amount'));

        if ($driver !== '') {
            $students = $students->where('driver_name', $driver)->values();
        }
        if ($bus !== '') {
            $students = $this->filterByVehicleNo($students, $bus);
        }

        $students = $this->studentRows($students, $studentsSearch, $studentDriver, $studentBusNo, $studentBusAmount);
        $filename = 'student-fee-master-assignments-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($students) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['S.No.', 'Student', 'Scholar No.', 'Class', 'Driver', 'Driver Contact No.', 'Bus No.', 'Bus Amount', 'Start Date', 'Stop Date']);

            foreach ($students as $index => $student) {
                fputcsv($handle, [
                    $index + 1,
                    $student['student_name'],
                    $student['scholar_no'],
                    trim($student['class_name'] . ($student['section_name'] ? ' / ' . $student['section_name'] : '')),
                    $student['driver_name'] ?: 'Unassigned',
                    $student['driver_mobile_no'] ?: '-',
                    $student['bus_no'] ?: 'Unassigned',
                    number_format($student['bus_fee'], 2, '.', ''),
                    $student['start_date'] ?: '-',
                    $student['stop_date'] ?: '-',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function reportStudents($connection)
    {
        $driverBusMap = $this->driverBusMap($connection);
        $driverDetails = $this->driverDetailsByName($connection);

        return $this->transportStudents($connection, $driverBusMap, $driverDetails);
    }

    private function filterByVehicleNo($students, string $vehicleNo)
    {
        return $students->filter(function ($student) use ($vehicleNo) {
            return collect(explode(',', $student['vehicle_no']))
                ->map(fn ($number) => trim($number))
                ->contains($vehicleNo);
        })->values();
    }

    private function summaryRows($students, string $summarySearch)
    {
        $summary = $students->groupBy(function ($student) {
            return ($student['driver_name'] ?: 'Unassigned driver') . '|' . ($student['bus_no'] ?: 'Unassigned bus');
        })->map(function ($group) {
            return [
                'driver_name' => $group->first()['driver_name'] ?: 'Unassigned driver',
                'bus_no' => $group->first()['bus_no'] ?: 'Unassigned bus',
                'driver_mobile_no' => $this->uniqueValues($group->pluck('driver_mobile_no')->all()),
                'student_count' => $group->count(),
                'total_bus_fees' => $group->sum('bus_fee'),
            ];
        })->values();

        if ($summarySearch === '') {
            return $summary;
        }

        return $summary->filter(function ($row) use ($summarySearch) {
            $searchText = strtolower(implode(' ', [
                $row['driver_name'],
                $row['bus_no'],
                $row['driver_mobile_no'],
                $row['student_count'],
                $row['total_bus_fees'],
            ]));

            return str_contains($searchText, strtolower($summarySearch));
        })->values();
    }

    private function studentRows($students, string $studentsSearch, string $studentDriver = '', string $studentBusNo = '', string $studentBusAmount = '')
    {
        if ($studentDriver !== '') {
            $students = $students->where('driver_name', $studentDriver)->values();
        }

        if ($studentBusNo !== '') {
            $students = $students->filter(function ($student) use ($studentBusNo) {
                return collect(explode(',', $student['bus_no']))
                    ->map(fn ($number) => trim($number))
                    ->contains($studentBusNo);
            })->values();
        }

        if ($studentBusAmount !== '') {
            $students = $students->filter(function ($student) use ($studentBusAmount) {
                return number_format((float) $student['bus_fee'], 2, '.', '') === number_format((float) $studentBusAmount, 2, '.', '');
            })->values();
        }

        if ($studentsSearch === '') {
            return $students;
        }

        return $students->filter(function ($student) use ($studentsSearch) {
            $searchText = strtolower(implode(' ', [
                $student['student_name'],
                $student['scholar_no'],
                $student['class_name'],
                $student['section_name'],
                $student['driver_name'],
                $student['driver_mobile_no'],
                $student['bus_no'],
                $student['bus_fee'],
                $student['start_date'],
                $student['stop_date'],
            ]));

            return str_contains($searchText, strtolower($studentsSearch));
        })->values();
    }

    private function driverBusMap($connection)
    {
        $map = [];
        $vehicleDetails = $this->vehicleDetailsByNumber($connection);
        $schedules = $connection->table('schedulemasterall')->select('schedule_name', 'schedule_check_one')->get();

        foreach ($schedules as $schedule) {
            $assignments = json_decode($schedule->schedule_check_one, true);
            if (!is_array($assignments)) {
                continue;
            }

            foreach ($assignments as $assignment) {
                $driver = trim((string) ($assignment['driver_name'] ?? ''));
                $vehicleNumber = trim((string) ($assignment['vehicelno'] ?? $assignment['vehicle_no'] ?? $assignment['callno'] ?? ''));
                $vehicle = $vehicleDetails[$this->normalizeKey($vehicleNumber)] ?? null;
                $driver = $driver ?: ($vehicle['driver_name'] ?? '');
                $bus = $vehicle['bus_no'] ?? trim((string) ($assignment['callno'] ?? $vehicleNumber));
                if ($driver === '' || $bus === '') {
                    continue;
                }
                $map[$driver][] = [
                    'bus_no' => $bus,
                    'vehicle_no' => $vehicle['vehicle_no'] ?? $vehicleNumber,
                    'driver_mobile_no' => $vehicle['driver_mobile_no'] ?? '',
                ];
            }
        }

        return collect($map)->map(function ($buses) {
            return collect($buses)
                ->unique(fn ($bus) => $this->normalizeKey($bus['bus_no'].'|'.$bus['vehicle_no']))
                ->values()
                ->all();
        })->all();
    }

    private function transportStudents($connection, array $driverBusMap, array $driverDetails)
    {
        $registrations = $connection->table('student_registration')
            ->select('id', 'student_name', 'class_name', 'scholar_no', 'json_str')
            ->where(function ($query) {
                $query->where('is_archived', 0)->orWhereNull('is_archived');
            })
            ->get();

        $feeRecords = $connection->table('generate_duechartstatus')
            ->select('student_id', 'json_str')
            ->whereIn('student_id', $registrations->pluck('id'))
            ->get()
            ->keyBy('student_id');

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
    }

    private function vehicleDetailsByNumber($connection): array
    {
        if (!Schema::connection('dynamic')->hasTable('vehicel')) {
            return [];
        }

        $hasDriverId = Schema::connection('dynamic')->hasColumn('vehicel', 'driver_id');
        $hasHrmsEmployees = Schema::connection('dynamic')->hasTable('hrms_employees');

        $query = $connection->table('vehicel')
            ->where(function ($query) {
                $query->where('vehicel.is_delete', 0)->orWhereNull('vehicel.is_delete');
            })
            ->select('vehicel.callno', 'vehicel.vehicelno');

        if ($hasDriverId && $hasHrmsEmployees) {
            $query->leftJoin('hrms_employees', 'hrms_employees.id', '=', 'vehicel.driver_id')
                ->addSelect(
                    'hrms_employees.contact_number',
                    'hrms_employees.first_name',
                    'hrms_employees.last_name'
                );
        }

        $details = [];
        foreach ($query->get() as $vehicle) {
            $driverName = trim((string) (($vehicle->first_name ?? '').' '.($vehicle->last_name ?? '')));
            $detail = [
                'bus_no' => trim((string) $vehicle->callno),
                'vehicle_no' => trim((string) $vehicle->vehicelno),
                'driver_name' => $driverName,
                'driver_mobile_no' => trim((string) ($vehicle->contact_number ?? '')),
            ];

            foreach ([$detail['bus_no'], $detail['vehicle_no']] as $number) {
                if ($number !== '') {
                    $details[$this->normalizeKey($number)] = $detail;
                }
            }
        }

        return $details;
    }

    private function driverDetailsByName($connection): array
    {
        if (!Schema::connection('dynamic')->hasTable('hrms_employees')) {
            return [];
        }

        $drivers = [];
        $employees = $connection->table('hrms_employees')
            ->select('first_name', 'last_name', 'contact_number')
            ->get();

        foreach ($employees as $employee) {
            $driverName = trim((string) (($employee->first_name ?? '').' '.($employee->last_name ?? '')));
            $driverMobileNo = trim((string) ($employee->contact_number ?? ''));
            if ($driverName === '') {
                continue;
            }

            $drivers[$this->normalizeKey($driverName)] = [
                'driver_mobile_no' => $driverMobileNo,
            ];
            $drivers[$this->normalizeDriverName($driverName)] = [
                'driver_mobile_no' => $driverMobileNo,
            ];
            $drivers['__drivers'][] = [
                'name' => $this->normalizeDriverName($driverName),
                'driver_mobile_no' => $driverMobileNo,
            ];
        }

        return $drivers;
    }

    private function driverMobileByName(string $driverName, array $driverDetails): string
    {
        $normalizedName = $this->normalizeKey($driverName);
        $normalizedDriverName = $this->normalizeDriverName($driverName);

        $driverMobileNo = $driverDetails[$normalizedName]['driver_mobile_no']
            ?? $driverDetails[$normalizedDriverName]['driver_mobile_no']
            ?? '';

        if ($driverMobileNo !== '') {
            return $driverMobileNo;
        }

        return $this->closestDriverMobile($normalizedDriverName, $driverDetails['__drivers'] ?? []);
    }

    private function closestDriverMobile(string $driverName, array $drivers): string
    {
        if ($driverName === '') {
            return '';
        }

        $bestDistance = null;
        $bestMobileNo = '';
        $matchedDrivers = 0;
        $maximumDistance = max(2, (int) floor(strlen($driverName) * 0.15));

        foreach ($drivers as $driver) {
            $hrmsDriverName = $driver['name'] ?? '';
            $driverMobileNo = $driver['driver_mobile_no'] ?? '';

            if ($hrmsDriverName === '' || $driverMobileNo === '') {
                continue;
            }

            $distance = levenshtein($driverName, $hrmsDriverName);
            if ($distance > $maximumDistance) {
                continue;
            }

            if ($bestDistance === null || $distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMobileNo = $driverMobileNo;
                $matchedDrivers = 1;
                continue;
            }

            if ($distance === $bestDistance) {
                $matchedDrivers++;
            }
        }

        return $matchedDrivers === 1 ? $bestMobileNo : '';
    }

    private function uniqueValues(array $values): string
    {
        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->implode(', ');
    }

    private function normalizeKey(string $value): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }

    private function normalizeDriverName(string $value): string
    {
        $value = preg_replace('/\b(mr|mrs|ms|miss|shri|smt|dr)\.?\b/i', ' ', $value);
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value);

        return $this->normalizeKey($value);
    }

    private function busFee($feeRecord)
    {
        if (!$feeRecord) {
            return 0;
        }

        $outer = json_decode($feeRecord->json_str, true);
        $inner = isset($outer[0]['json_str']) ? json_decode($outer[0]['json_str'], true) : null;
        if (!is_array($inner)) {
            return 0;
        }

        foreach ($inner['account_name'] ?? [] as $index => $accountName) {
            if ($accountName === 'BUS FEES') {
                return (float) ($inner['orig_fees'][$index] ?? $inner['fees'][$index] ?? 0);
            }
        }

        return 0;
    }
}
