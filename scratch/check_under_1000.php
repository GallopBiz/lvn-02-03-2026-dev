<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = DB::table('2026_2027.inquiry_registration')
    ->whereRaw('CAST(form_number AS UNSIGNED) < 1000')
    ->get(['id', 'form_number', 'student_name']);
echo "2026_2027 records < 1000 count: " . count($r) . "\n";
foreach ($r as $x) {
    echo "  ID: {$x->id}, form: {$x->form_number}, name: {$x->student_name}\n";
}
