<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$students = DB::connection('dynamic')->table('student_registration')
    ->select('id', 'student_name', 'scholar_no', 'json_str')
    ->where(function ($query) {
        $query->where('is_archived', 0)->orWhereNull('is_archived');
    })
    ->get();

$feeRecords = DB::connection('dynamic')->table('generate_duechartstatus')
    ->select('student_id', 'json_str')
    ->whereIn('student_id', $students->pluck('id'))
    ->get()
    ->keyBy('student_id');

function getFee($feeRecord) {
    if (!$feeRecord) return 0;
    $outer = json_decode($feeRecord->json_str, true);
    $inner = isset($outer[0]['json_str']) ? json_decode($outer[0]['json_str'], true) : null;
    if (!is_array($inner)) return 0;
    foreach ($inner['account_name'] ?? [] as $index => $accountName) {
        if ($accountName === 'BUS FEES') {
            return (float) ($inner['orig_fees'][$index] ?? $inner['fees'][$index] ?? 0);
        }
    }
    return 0;
}

$activeCount = 0;
$hasEndDateCount = 0;
$zeroFeeCount = 0;
$hasEndDateAndZeroFee = 0;
$hasEndDateAndPositiveFee = 0;

foreach ($students as $s) {
    $data = json_decode($s->json_str, true);
    if (empty($data['required_school_transport'])) continue;
    $activeCount++;
    $fee = getFee($feeRecords->get($s->id));
    $hasEnd = !empty($data['bus_facility_end_date']);
    if ($hasEnd) {
        $hasEndDateCount++;
        if ($fee > 0) $hasEndDateAndPositiveFee++;
        else $hasEndDateAndZeroFee++;
    }
    if ($fee == 0) $zeroFeeCount++;
}

echo "Total with required_school_transport: $activeCount\n";
echo "With end_date: $hasEndDateCount (Zero fee: $hasEndDateAndZeroFee, Positive fee: $hasEndDateAndPositiveFee)\n";
echo "Total with zero fee: $zeroFeeCount\n";
