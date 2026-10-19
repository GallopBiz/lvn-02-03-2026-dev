<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$students = DB::connection('dynamic')->table('student_registration')
    ->select('id', 'student_name', 'scholar_no', 'json_str')
    ->whereIn('id', [10466, 10467, 10468, 10469, 10910, 11473, 11744])
    ->get();

$feeRecords = DB::connection('dynamic')->table('generate_duechartstatus')
    ->select('student_id', 'json_str')
    ->whereIn('student_id', $students->pluck('id'))
    ->get()
    ->keyBy('student_id');

foreach ($students as $s) {
    $data = json_decode($s->json_str, true);
    $fee = $feeRecords->get($s->id);
    $hasBusFee = false;
    $feeAmt = 0;
    if ($fee) {
        $outer = json_decode($fee->json_str, true);
        $inner = isset($outer[0]['json_str']) ? json_decode($outer[0]['json_str'], true) : null;
        if (is_array($inner)) {
            foreach ($inner['account_name'] ?? [] as $idx => $acc) {
                if ($acc === 'BUS FEES') {
                    $hasBusFee = true;
                    $feeAmt = $inner['fees'][$idx] ?? 0;
                }
            }
        }
    }
    echo "ID: {$s->id}, Name: {$s->student_name}, Transport: " . var_export($data['required_school_transport'] ?? null, true) . 
         ", Start: " . ($data['bus_facility_start_date'] ?? 'N/A') . 
         ", End: " . ($data['bus_facility_end_date'] ?? 'N/A') . 
         ", Has BUS FEES in due chart: " . ($hasBusFee ? "YES ($feeAmt)" : "NO") . "\n";
}
