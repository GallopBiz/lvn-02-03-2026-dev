<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

Config::set('database.connections.dynamic.database', '2026_2027');
DB::purge('dynamic');

$records = DB::connection('dynamic')->table('inquiry_registration')
    ->select('id', 'form_number', 'student_name', 'session_name', 'next_year', 'created_at')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();

echo "Last 5 records in 2026_2027:\n";
print_r($records);
