<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HrmsBiometricDetail;
use App\Models\HrmsEmployeeAttendance;

$logs = HrmsEmployeeAttendance::whereNull('employee_id')
    ->orWhere('employee_id', 0)
    ->get();

echo "Found " . count($logs) . " unmapped attendance records.\n";

$updated = 0;
foreach ($logs as $log) {
    if (!$log->ess_emp_code) continue;
    $empId = HrmsBiometricDetail::where('ess_emp_code', $log->ess_emp_code)->value('employee_id');

    if ($empId) {
        $log->update(['employee_id' => $empId]);
        $updated++;
    }
}

echo "Successfully mapped and updated " . $updated . " attendance records with employee_id.\n";

$remaining = HrmsEmployeeAttendance::whereNull('employee_id')
    ->orWhere('employee_id', 0)
    ->count();

echo "Remaining unmapped records (biometric device code not linked to any employee): " . $remaining . "\n";
