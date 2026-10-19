<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = DB::connection('dynamic')->table('student_registration')->where('id', 10466)->first();
$data = json_decode($row->json_str, true);
$keys = ['required_school_transport', 'driver_name', 'bus_facility_start_date', 'bus_facility_end_date', 'bus_facility_date', 'stop_date', 'status'];
foreach ($keys as $k) {
    echo "$k: " . var_export($data[$k] ?? null, true) . "\n";
}
