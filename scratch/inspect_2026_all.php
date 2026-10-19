<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

Config::set('database.connections.dynamic.database', '2026_2027');
DB::purge('dynamic');

$records = DB::connection('dynamic')->table('inquiry_registration')->get();
echo "Total records in 2026_2027: " . $records->count() . "\n";
echo "Min form_number: " . $records->min('form_number') . "\n";
echo "Max form_number: " . $records->max('form_number') . "\n";
echo "Created dates: " . $records->min('created_at') . " to " . $records->max('created_at') . "\n";

foreach ($records->take(5) as $r) {
    echo "ID: {$r->id}, Form: {$r->form_number}, Name: {$r->student_name}, NextYear: {$r->next_year}, Session: {$r->session_name}, Date: {$r->created_at}\n";
}
foreach ($records->slice(-5) as $r) {
    echo "ID: {$r->id}, Form: {$r->form_number}, Name: {$r->student_name}, NextYear: {$r->next_year}, Session: {$r->session_name}, Date: {$r->created_at}\n";
}
