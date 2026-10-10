<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "teacher_subjects columns:\n";
try {
    print_r(Schema::connection('dynamic')->getColumnListing('teacher_subjects'));
} catch (\Exception $e) {
    echo "teacher_subjects error: " . $e->getMessage() . "\n";
}

echo "Sample teacher_subjects data:\n";
try {
    print_r(DB::connection('dynamic')->table('teacher_subjects')->latest('id')->limit(3)->get()->toArray());
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
