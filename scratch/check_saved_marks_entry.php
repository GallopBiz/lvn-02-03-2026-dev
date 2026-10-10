<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "previosly_saved_marks_entry columns:\n";
try {
    print_r(Schema::connection('dynamic')->getColumnListing('previosly_saved_marks_entry'));
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Sample previosly_saved_marks_entry data:\n";
try {
    print_r(DB::connection('dynamic')->table('previosly_saved_marks_entry')->latest('id')->limit(5)->get()->toArray());
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
