<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = DB::table('2025_2026.inquiry_registration')->whereIn('id', [232, 827, 828])->get();
foreach ($rows as $r) {
    echo "ID: {$r->id}, form: {$r->form_number}, name: {$r->student_name}, next_year: {$r->next_year}, date: {$r->created_at}\n";
}
