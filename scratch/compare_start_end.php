<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$students = DB::connection('dynamic')->table('student_registration')
    ->select('id', 'student_name', 'scholar_no', 'json_str')
    ->get();

$endBeforeStart = 0;
$endAfterOrEqualToStart = 0;
$noStartWithEnd = 0;

foreach ($students as $s) {
    $data = json_decode($s->json_str, true);
    if (!empty($data['required_school_transport']) && !empty($data['bus_facility_end_date'])) {
        $startStr = $data['bus_facility_start_date'] ?? null;
        $endStr = $data['bus_facility_end_date'];
        
        $startDate = null;
        $endDate = null;
        try {
            if ($startStr) $startDate = \Carbon\Carbon::parse($startStr);
        } catch (\Exception $e) {}
        try {
            if ($endStr) $endDate = \Carbon\Carbon::parse($endStr);
        } catch (\Exception $e) {}

        if (!$startDate) {
            $noStartWithEnd++;
        } elseif ($endDate && $endDate < $startDate) {
            $endBeforeStart++;
            if ($endBeforeStart <= 5) {
                echo "End < Start: ID {$s->id}, Name: {$s->student_name}, Start: $startStr, End: $endStr\n";
            }
        } else {
            $endAfterOrEqualToStart++;
            if ($endAfterOrEqualToStart <= 5) {
                echo "End >= Start: ID {$s->id}, Name: {$s->student_name}, Start: $startStr, End: $endStr\n";
            }
        }
    }
}

echo "\nSummary:\n";
echo "End < Start: $endBeforeStart\n";
echo "End >= Start: $endAfterOrEqualToStart\n";
echo "No Start: $noStartWithEnd\n";
