<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = DB::connection('dynamic')->table('student_registration')->where('id', 10156)->first();
$data = json_decode($row->json_str, true);
echo "Student 10156 (Anaadi Shinde):\n";
echo "Transport: " . var_export($data['required_school_transport'] ?? null, true) . "\n";
echo "Driver: " . var_export($data['driver_name'] ?? null, true) . "\n";
echo "Start: " . var_export($data['bus_facility_start_date'] ?? null, true) . "\n";
echo "End: " . var_export($data['bus_facility_end_date'] ?? null, true) . "\n";

$chart = DB::connection('dynamic')->table('generate_duechartstatus')->where('student_id', 10156)->first();
if ($chart) {
    $outer = json_decode($chart->json_str, true);
    $inner = isset($outer[0]['json_str']) ? json_decode($outer[0]['json_str'], true) : null;
    echo "Due chart account names:\n";
    print_r($inner['account_name'] ?? []);
    echo "Due chart fees:\n";
    print_r($inner['fees'] ?? []);
}
