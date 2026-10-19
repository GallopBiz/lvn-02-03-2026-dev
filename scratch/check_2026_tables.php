<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables2026 = DB::select('SHOW TABLES FROM `2026_2027`');
echo "Tables in 2026_2027: " . count($tables2026) . "\n";
