<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$databases = DB::select("SHOW DATABASES");
foreach ($databases as $db) {
    $vals = array_values((array)$db);
    $dbname = $vals[0];
    $hasTable = DB::select("SHOW TABLES FROM `$dbname` LIKE 'inquiry_registration'");
    if (!empty($hasTable)) {
        Config::set('database.connections.check_db', Config::get('database.connections.dynamic'));
        Config::set('database.connections.check_db.database', $dbname);
        DB::purge('check_db');

        $rows = DB::connection('check_db')->table('inquiry_registration')
            ->where('form_number', '1')
            ->orWhere('form_number', 1)
            ->get();
        echo "Database $dbname has " . $rows->count() . " records with form_number = 1:\n";
        foreach ($rows as $r) {
            echo "  ID: {$r->id}, Name: {$r->student_name}, Session: {$r->session_name}, NextYear: {$r->next_year}, Created: {$r->created_at}\n";
        }
    }
}
