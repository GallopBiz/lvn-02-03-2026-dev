<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols25 = DB::select("DESCRIBE `2025_2026`.`inquiry_registration`");
$cols26 = DB::select("DESCRIBE `2026_2027`.`inquiry_registration`");
$cols27 = DB::select("DESCRIBE `2027_2028`.`inquiry_registration`");

$names25 = array_map(fn($c) => $c->Field, $cols25);
$names26 = array_map(fn($c) => $c->Field, $cols26);
$names27 = array_map(fn($c) => $c->Field, $cols27);

echo "Diff 25 vs 26: " . json_encode(array_diff($names25, $names26)) . "\n";
echo "Diff 26 vs 27: " . json_encode(array_diff($names26, $names27)) . "\n";
