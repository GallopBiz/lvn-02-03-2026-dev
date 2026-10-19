<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "2025_2026 next_year=1:\n";
$r25 = DB::table('2025_2026.inquiry_registration')->where('next_year', 1)->where('is_delete', 0)->get(['id', 'form_number', 'student_name']);
foreach ($r25 as $r) echo "  ID: {$r->id}, form: {$r->form_number}, name: {$r->student_name}\n";

echo "2026_2027 next_year=1:\n";
$r26 = DB::table('2026_2027.inquiry_registration')->where('next_year', 1)->where('is_delete', 0)->get(['id', 'form_number', 'student_name']);
foreach ($r26 as $r) echo "  ID: {$r->id}, form: {$r->form_number}, name: {$r->student_name}\n";

echo "2027_2028 next_year=1:\n";
$r27 = DB::table('2027_2028.inquiry_registration')->where('next_year', 1)->where('is_delete', 0)->get(['id', 'form_number', 'student_name']);
foreach ($r27 as $r) echo "  ID: {$r->id}, form: {$r->form_number}, name: {$r->student_name}\n";
