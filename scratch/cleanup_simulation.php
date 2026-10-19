<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::table('2026_2027.inquiry_registration')->where('student_name', 'Rahul Sharma NextYear')->delete();
DB::table('2027_2028.inquiry_registration')->where('student_name', 'Rahul Sharma NextYear')->delete();

echo "Cleaned up simulation test records.\n";
