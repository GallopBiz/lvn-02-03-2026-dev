<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$tables = DB::select("SHOW TABLES FROM `2026_2027`");
echo "Total tables in 2026_2027: " . count($tables) . "\n";
foreach ($tables as $t) {
    $tname = array_values((array)$t)[0];
    echo "  $tname\n";
}
