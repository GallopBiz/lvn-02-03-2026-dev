<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Roles: " . json_encode(DB::table('roles')->pluck('name')) . "\n";
} catch (\Exception $e) {
    echo "Roles error: " . $e->getMessage() . "\n";
}

try {
    $staffSample = App\Models\Staff::take(5)->get();
    echo "Staff sample: " . json_encode($staffSample) . "\n";
} catch (\Exception $e) {
    echo "Staff sample error: " . $e->getMessage() . "\n";
}
