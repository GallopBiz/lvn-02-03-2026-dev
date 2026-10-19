<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$students = DB::connection('dynamic')->table('student_registration')
    ->select('id', 'student_name', 'scholar_no', 'json_str')
    ->get();

$endDates = [];
foreach ($students as $s) {
    $data = json_decode($s->json_str, true);
    if (!empty($data['bus_facility_end_date'])) {
        $end = $data['bus_facility_end_date'];
        $endDates[$end] = ($endDates[$end] ?? 0) + 1;
    }
}

echo "Distribution of bus_facility_end_date:\n";
print_r($endDates);
