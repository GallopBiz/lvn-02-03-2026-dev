<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$tables = DB::select("SHOW TABLES FROM `2027_2028`");
echo "Tables in 2027_2028 count: " . count($tables) . "\n";
print_r($tables);
