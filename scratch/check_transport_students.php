<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$db = config('database.connections.dynamic.database') ?: '2025_2026';
echo "Checking database: $db\n";

$students = DB::connection('dynamic')->table('student_registration')
    ->select('id', 'student_name', 'scholar_no', 'json_str')
    ->get();

$countTransport = 0;
$countWithEndDate = 0;
$countStopped = 0;

foreach ($students as $s) {
    $data = json_decode($s->json_str, true);
    if (!empty($data['required_school_transport'])) {
        $countTransport++;
        if (!empty($data['bus_facility_end_date'])) {
            $countWithEndDate++;
            echo "Student ID: {$s->id}, Name: {$s->student_name}, Transport: {$data['required_school_transport']}, Start: " . ($data['bus_facility_start_date'] ?? 'N/A') . ", End: {$data['bus_facility_end_date']}\n";
        }
    } else {
        if (!empty($data['bus_facility_end_date'])) {
            $countStopped++;
            echo "STOPPED (transport empty): Student ID: {$s->id}, Name: {$s->student_name}, Transport: " . var_export($data['required_school_transport'] ?? null, true) . ", End: {$data['bus_facility_end_date']}\n";
        }
    }
}

echo "Total students: " . $students->count() . "\n";
echo "Total with required_school_transport: $countTransport\n";
echo "With bus_facility_end_date & required_school_transport: $countWithEndDate\n";
echo "With bus_facility_end_date & transport empty: $countStopped\n";
