<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Sample academic_students_marks:\n";
try {
    print_r(DB::connection('dynamic')->table('academic_students_marks')->latest('id')->limit(5)->get()->toArray());
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
