<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$adminUsers = DB::table('student_registration')->where('type', 'a')->take(10)->get(['id', 'student_name', 'form_number', 'type']);
echo "Admin Users in student_registration (type='a'): " . json_encode($adminUsers) . "\n";

$studentUsers = DB::table('student_registration')->where('type', 's')->take(5)->get(['id', 'student_name', 'scholar_no', 'type']);
echo "Student Users in student_registration (type='s'): " . json_encode($studentUsers) . "\n";

$usersTableCount = DB::table('users')->count();
echo "Users table total count: {$usersTableCount}\n";
$usersTableSample = DB::table('users')->take(5)->get(['id', 'name', 'username', 'role']);
echo "Users table sample: " . json_encode($usersTableSample) . "\n";
