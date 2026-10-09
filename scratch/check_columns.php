<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "academic_students_marks columns:\n";
try {
    print_r(Schema::connection('dynamic')->getColumnListing('academic_students_marks'));
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "marks columns:\n";
try {
    print_r(Schema::connection('dynamic')->getColumnListing('marks'));
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
