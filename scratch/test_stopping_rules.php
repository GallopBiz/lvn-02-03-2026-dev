<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new App\Http\Controllers\backend\TransportReportController(request());
$reflector = new ReflectionClass($controller);
$busFeeMethod = $reflector->getMethod('busFee');
$busFeeMethod->setAccessible(true);

$students = DB::connection('dynamic')->table('student_registration')
    ->select('id', 'student_name', 'scholar_no', 'json_str')
    ->where(function ($query) {
        $query->where('is_archived', 0)->orWhereNull('is_archived');
    })
    ->get();

$feeRecords = DB::connection('dynamic')->table('generate_duechartstatus')
    ->select('student_id', 'json_str')
    ->whereIn('student_id', $students->pluck('id'))
    ->get()
    ->keyBy('student_id');

$total = 0;
$rule1_stopped = 0; // required_school_transport empty / 0
$rule2_stopped = 0; // has end_date >= start_date and <= today
$rule3_stopped = 0; // fee == 0 with end date
$rule4_all_end_dates = 0;

$removedStudents = [];

foreach ($students as $s) {
    $data = json_decode($s->json_str, true);
    $transport = $data['required_school_transport'] ?? null;
    $fee = $busFeeMethod->invoke($controller, $feeRecords->get($s->id));
    
    // Check if transport stopped
    $isStopped = false;
    $reason = '';
    
    if (empty($transport) || in_array($transport, ['0', 0, false, 'off'], true)) {
        $isStopped = true;
        $reason = 'required_school_transport is empty/0';
        $rule1_stopped++;
    } else {
        $startStr = $data['bus_facility_start_date'] ?? null;
        $endStr = $data['bus_facility_end_date'] ?? null;
        
        if (!empty($endStr)) {
            $startDate = null;
            $endDate = null;
            try { if ($startStr) $startDate = \Carbon\Carbon::parse($startStr); } catch (\Exception $e) {}
            try { if ($endStr) $endDate = \Carbon\Carbon::parse($endStr); } catch (\Exception $e) {}
            
            // If end date is on or after start date (or no start date)
            if ($endDate && (!$startDate || $endDate->greaterThanOrEqualTo($startDate))) {
                if ($endDate->startOfDay()->isPast() || $endDate->isToday()) {
                    $isStopped = true;
                    $reason = "end_date ($endStr) >= start_date ($startStr)";
                    $rule2_stopped++;
                }
            }
        }
    }
    
    if ($isStopped) {
        $removedStudents[] = [
            'id' => $s->id,
            'name' => $s->student_name,
            'fee' => $fee,
            'reason' => $reason
        ];
    }
}

echo "Total students processed: " . $students->count() . "\n";
echo "Rule 1 (transport empty/0): $rule1_stopped\n";
echo "Rule 2 (end date >= start date & past/today): $rule2_stopped\n";
echo "Total removed as stopped: " . count($removedStudents) . "\n";
echo "Sample removed students:\n";
print_r(array_slice($removedStudents, 0, 15));
