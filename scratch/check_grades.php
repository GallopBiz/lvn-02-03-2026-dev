<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "GRADES:\n";
try {
    print_r(DB::connection('dynamic')->table('grades')->get()->toArray());
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "GRADEMASTER:\n";
try {
    print_r(DB::connection('dynamic')->table('grademaster')->get()->toArray());
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
