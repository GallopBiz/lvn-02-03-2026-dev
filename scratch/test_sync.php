<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$currentDb = '2026_2027';
$prevDb = '2025_2026';

echo "Testing sync from $prevDb to $currentDb...\n";

$prevRecords = DB::table("{$prevDb}.inquiry_registration")
    ->where(function($q) {
        $q->where('next_year', 1)->orWhere('next_year', '1');
    })
    ->where(function($q) {
        $q->where('is_delete', 0)->orWhereNull('is_delete');
    })
    ->whereRaw('CAST(form_number AS UNSIGNED) < 1000')
    ->get();

echo "Found " . count($prevRecords) . " records in $prevDb:\n";
foreach ($prevRecords as $r) {
    echo "  Form: {$r->form_number}, Name: {$r->student_name}, Mobile: {$r->mobile_number}\n";
}

foreach ($prevRecords as $record) {
    $exists = DB::table("{$currentDb}.inquiry_registration")
        ->where('form_number', $record->form_number)
        ->where(function($q) use ($record) {
            $q->where('student_name', $record->student_name)
              ->orWhere('mobile_number', $record->mobile_number);
        })
        ->exists();

    echo "Record {$record->form_number} exists in $currentDb: " . ($exists ? 'YES' : 'NO') . "\n";
    if (!$exists) {
        $data = (array)$record;
        unset($data['id']);
        $data['session_name'] = $currentDb;
        $data['next_year'] = 0;
        DB::table("{$currentDb}.inquiry_registration")->insert($data);
        echo "  -> Synced to $currentDb successfully!\n";
    }
}

// Verify count in 2026_2027 < 1000 now
$synced = DB::table("{$currentDb}.inquiry_registration")
    ->whereRaw('CAST(form_number AS UNSIGNED) < 1000')
    ->get(['id', 'form_number', 'student_name', 'session_name', 'next_year']);
echo "Current records in $currentDb < 1000:\n";
foreach ($synced as $s) {
    echo "  ID: {$s->id}, Form: {$s->form_number}, Name: {$s->student_name}, Session: {$s->session_name}, next_year: {$s->next_year}\n";
}
