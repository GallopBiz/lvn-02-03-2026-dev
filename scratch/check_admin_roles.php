<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Staff role values: " . json_encode(App\Models\Staff::pluck('role')->unique()->values()) . "\n";
$adminsInStaff = App\Models\Staff::where('role', 'like', '%admin%')->get(['id', 'name', 'username', 'role']);
echo "Admins in Staff: " . json_encode($adminsInStaff) . "\n";

echo "User role values: " . json_encode(DB::table('student_registration')->pluck('type')->unique()->values()) . "\n";
