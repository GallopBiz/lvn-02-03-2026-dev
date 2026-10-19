<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- 2026_2027 inquiry_registration summary ---\n";
$rows = DB::table('2026_2027.inquiry_registration')->get();
echo "Total count: " . count($rows) . "\n";

$formNumbers = $rows->pluck('form_number')->toArray();
echo "Form numbers min: " . min($formNumbers) . ", max: " . max($formNumbers) . "\n";
echo "First 5 form numbers: " . implode(', ', array_slice($formNumbers, 0, 5)) . "\n";
echo "Last 5 form numbers: " . implode(', ', array_slice($formNumbers, -5)) . "\n";

echo "\n--- 2025_2026 inquiry_registration next_year=1 ---\n";
$nyRows = DB::table('2025_2026.inquiry_registration')->where('next_year', 1)->get();
foreach ($nyRows as $r) {
    echo "ID: {$r->id}, form: {$r->form_number}, name: {$r->student_name}, session: {$r->session_name}, created: {$r->created_at}\n";
}
