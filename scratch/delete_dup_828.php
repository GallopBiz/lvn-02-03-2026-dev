<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::table('2025_2026.inquiry_registration')->where('id', 828)->delete();
echo "Deleted duplicate test ID 828 from 2025_2026\n";
