<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DefaulterController extends Controller
{
    private string $currentSession;

    public function __construct(Request $request)
    {
        $selectedYear = $request->input('session')
            ?? $request->input('session_name')
            ?? $request->cookie('selectedYear')
            ?? ($_COOKIE['selectedYear'] ?? null)
            ?? session('db_names')
            ?? Config::get("database.connections.dynamic.database")
            ?? env('DB_DATABASE', '2025_2026');

        $this->currentSession = (string) $selectedYear;

        // Ensure database exists, fallback to available database if needed
        $availableDbs = ['2025_2026', '2026_2027', '2027_2028'];
        if (!in_array($this->currentSession, $availableDbs)) {
            $fallback = in_array(session('db_names'), $availableDbs)
                ? session('db_names')
                : (in_array(env('DB_DATABASE'), $availableDbs) ? env('DB_DATABASE') : '2025_2026');
            $this->currentSession = $fallback;
        }

        $dynamicConfig = Config::get("database.connections.dynamic");
        $dynamicConfig['database'] = $this->currentSession;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');
    }

    /**
     * Compute defaulters data preserving exact qualification logic,
     * with optional Bus Fees, Late Fees, and Without-Bus filtering.
     */
    private function getDefaultersData(Request $request): array
    {
        $today = Carbon::now();
        $transportFilter = $request->get('transport_filter'); // 'with', 'without', or ''
        $classFilter = $request->get('class_filter');
        $batchFilter = $request->get('batch_filter');
        $feeMode = $request->get('fee_mode', 'all'); // 'all', 'without_bus', 'with_bus'
        $includeLate = $request->get('include_late', '1') !== '0'; // default true
        $search = trim((string) $request->get('search'));

        // Load late fees master configuration
        $lateFeesMaster = DB::connection('dynamic')->table('late_fees_master')->where('id', 1)->first();

        // 1. Fetch Students
        $studentsQuery = DB::connection('dynamic')->table('student_registration')
            ->select('id', 'student_name', 'scholar_no', 'json_str', 'class_name', 'section_name');

        $students = $studentsQuery->get()
            ->filter(function ($student) use ($batchFilter) {
                $json = json_decode($student->json_str, true);
                $batch = trim((string) ($json['batch'] ?? ''));

                // Exclude RTE students (preserved logic)
                if (strtoupper($json['application_for'] ?? '') === 'RTE') {
                    return false;
                }

                // If explicit batch filtered, match it
                if ($batchFilter) {
                    return strtolower($batch) === strtolower($batchFilter);
                }

                // Match active session or normalized session
                return $batch !== '' && (
                    $batch === $this->currentSession ||
                    str_replace('-', '_', $batch) === str_replace('-', '_', $this->currentSession)
                );
            })
            ->keyBy('id');

        $allClasses = $students->map(function ($student) {
            $json = json_decode($student->json_str, true);
            return $student->class_name ?: ($json['classname'] ?? null);
        })->filter()->unique()->sort()->values();

        $allBatches = $students->map(function ($student) {
            $json = json_decode($student->json_str, true);
            return $json['batch'] ?? null;
        })->filter()->unique()->sort()->values();

        // 2. Fetch Due Records
        $dueData = DB::connection('dynamic')->table('generate_duechartstatus')->get();
        $defaulters = [];

        $totalAcademicDueSum = 0;
        $totalBusDueSum = 0;
        $totalLateFeeSum = 0;
        $grandTotalDueSum = 0;

        foreach ($dueData as $dueRecord) {
            $studentId = $dueRecord->student_id;
            if (!isset($students[$studentId])) continue;

            $student = $students[$studentId];
            $json = json_decode($student->json_str, true);

            $father_mobile = $json['father_mobile'] ?? $json['mobile'] ?? 'N/A';
            $father_name = $json['father_name'] ?? $json['name_father'] ?? 'N/A';
            $classname = $student->class_name ?: ($json['classname'] ?? 'N/A');
            $section = $student->section_name ?: ($json['section_name'] ?? ($json['section'] ?? ($dueRecord->sectionname ?? '-')));
            $batch = $json['batch'] ?? $this->currentSession;

            // Class filter
            if ($classFilter && strtolower($classname) !== strtolower($classFilter)) continue;

            // Text search (Student Name, Scholar No, Father Name, Mobile, Section)
            if ($search) {
                $searchLower = strtolower($search);
                $matchName = str_contains(strtolower($student->student_name ?? ''), $searchLower);
                $matchScholar = str_contains(strtolower((string) $student->scholar_no), $searchLower);
                $matchMobile = str_contains(strtolower((string) $father_mobile), $searchLower);
                $matchFather = str_contains(strtolower((string) $father_name), $searchLower);
                $matchSection = str_contains(strtolower((string) $section), $searchLower);
                if (!$matchName && !$matchScholar && !$matchMobile && !$matchFather && !$matchSection) {
                    continue;
                }
            }

            $outerJson = json_decode($dueRecord->json_str, true);
            if (!isset($outerJson[0]['json_str'])) continue;

            $fees = json_decode($outerJson[0]['json_str'], true);
            if (!$fees || !isset($fees['due_date']) || !is_array($fees['due_date'])) continue;

            // Detect bus transport attachment
            $hasTransport = isset($fees['account_name']) &&
                collect($fees['account_name'])->contains(fn($name) => stripos($name, 'bus') !== false);

            if ($transportFilter === 'with' && !$hasTransport) continue;
            if ($transportFilter === 'without' && $hasTransport) continue;

            // Load payments made by this student
            $paidReceipts = DB::connection('dynamic')->table('feesreceiptchallan')
                ->where('student_id', $studentId)
                ->get()
                ->map(fn($r) => json_decode($r->str_json, true))
                ->filter()
                ->toArray();

            $dues = [];
            $hasAcademicDefaulter = false;
            $onlyBusInTerm1 = true;
            $tuitionDue = 0;
            $lunchDue = 0;
            $busDue = 0;
            $studentLateFee = 0;
            $oldestDueDate = null;
            $maxDaysOverdue = 0;

            $totalItems = count($fees['due_date']);

            for ($i = 0; $i < $totalItems; $i++) {
                if (
                    !isset($fees['due_date'][$i]) ||
                    !isset($fees['account_name'][$i]) ||
                    !isset($fees['term'][$i]) ||
                    !isset($fees['fees'][$i])
                ) {
                    continue;
                }

                try {
                    $dueDate = Carbon::parse(trim($fees['due_date'][$i]));
                } catch (\Exception $e) {
                    continue;
                }

                // If due date is in the future, it is not defaulted yet
                if ($dueDate->greaterThan($today)) continue;

                $accountHead = strtoupper(trim($fees['account_name'][$i]));
                $term = strtoupper(trim($fees['term'][$i]));
                $dueAmount = floatval($fees['fees'][$i]);
                $paidAmount = 0;

                // Match against payments made
                foreach ($paidReceipts as $payment) {
                    if (
                        !isset($payment['head_name']) ||
                        !isset($payment['term_str']) ||
                        !isset($payment['head_rec_ammount'])
                    ) continue;

                    $heads = explode(",", $payment['head_name']);
                    $terms = explode(",", $payment['term_str']);
                    $amounts = explode(",", $payment['head_rec_ammount']);

                    foreach ($amounts as $j => $amt) {
                        $head = $heads[$j] ?? null;
                        $termStr = $terms[$j] ?? null;

                        if ($head === null || $termStr === null) continue;

                        if ($head === $fees['account_name'][$i] && $termStr === $fees['term'][$i]) {
                            $paidAmount += floatval($amt);
                        }
                    }
                }

                $remaining = $dueAmount - $paidAmount;

                if ($remaining > 1) {
                    $daysOverdue = max(0, (int) $dueDate->diffInDays($today, false));
                    if ($daysOverdue > $maxDaysOverdue) {
                        $maxDaysOverdue = $daysOverdue;
                    }
                    if (!$oldestDueDate || $dueDate->lt($oldestDueDate)) {
                        $oldestDueDate = $dueDate->copy();
                    }

                    // Calculate late fee based on late_fees_master
                    $itemLateFee = 0;
                    if ($includeLate && $lateFeesMaster && $daysOverdue > 0 && $remaining >= floatval($lateFeesMaster->from_amount ?? 0)) {
                        $dailyRate = floatval($lateFeesMaster->late_fees_amount ?? 5);
                        $cap = floatval($lateFeesMaster->upto ?? 250);
                        $itemLateFee = min($cap, $daysOverdue * $dailyRate);
                    }
                    // Respect any admin late fee adjustments / waivers
                    try {
                        $itemLateFee = \App\Models\StudentLateFeeAdjustment::resolveLateFee($student->id, $fees['term'][$i], $itemLateFee);
                    } catch (\Throwable $e) {
                        // Keep itemLateFee as fallback
                    }
                    $studentLateFee += $itemLateFee;

                    // Academic Dues (Tuition / Lunch)
                    if (in_array($accountHead, ['TUITION FEES', 'LUNCH FEES'])) {
                        $hasAcademicDefaulter = true;
                        $onlyBusInTerm1 = false;

                        if ($accountHead === 'TUITION FEES') {
                            $tuitionDue += $remaining;
                        } else {
                            $lunchDue += $remaining;
                        }

                        $dues[] = [
                            'account_name' => $fees['account_name'][$i],
                            'term' => $fees['term'][$i],
                            'due_date' => $fees['due_date'][$i],
                            'days_overdue' => $daysOverdue,
                            'due_amount' => $dueAmount,
                            'paid_amount' => $paidAmount,
                            'amount' => $remaining,
                            'late_fee' => $itemLateFee,
                            'type' => 'academic',
                        ];
                    } elseif (stripos($accountHead, 'bus') !== false) {
                        if ($term !== 'TERM 1') {
                            $onlyBusInTerm1 = false;
                        }
                        $busDue += $remaining;

                        $dues[] = [
                            'account_name' => $fees['account_name'][$i],
                            'term' => $fees['term'][$i],
                            'due_date' => $fees['due_date'][$i],
                            'days_overdue' => $daysOverdue,
                            'due_amount' => $dueAmount,
                            'paid_amount' => $paidAmount,
                            'amount' => $remaining,
                            'late_fee' => $itemLateFee,
                            'type' => 'bus',
                        ];
                    } else {
                        $onlyBusInTerm1 = false;
                    }
                }
            }

            // EXACT PRESERVED DEFAULTER RULE:
            // Student must have overdue academic fees (Tuition / Lunch) and not be exclusively bus in Term 1
            if ($hasAcademicDefaulter && !$onlyBusInTerm1 && !empty($dues)) {
                $academicDue = $tuitionDue + $lunchDue;
                $effectiveBusDue = ($feeMode === 'without_bus') ? 0 : $busDue;
                $effectiveLateFee = $includeLate ? $studentLateFee : 0;
                $totalStudentDue = $academicDue + $effectiveBusDue + $effectiveLateFee;

                $defaulters[$studentId] = [
                    'student_id' => $studentId,
                    'info' => [
                        'name' => $student->student_name,
                        'scholar_no' => $student->scholar_no,
                        'father_name' => $father_name,
                        'father_mobile' => $father_mobile,
                        'classname' => $classname,
                        'class_roman' => \App\Academic\AcademicHelper::toRoman($classname) ?: $classname,
                        'section' => $section,
                        'batch' => $batch,
                        'has_transport' => $hasTransport,
                    ],
                    'dues' => $dues,
                    'tuition_due' => $tuitionDue,
                    'lunch_due' => $lunchDue,
                    'academic_due' => $academicDue,
                    'bus_due' => $busDue,
                    'late_fee' => $studentLateFee,
                    'effective_late_fee' => $effectiveLateFee,
                    'total_due' => $totalStudentDue,
                    'total_without_bus' => $academicDue + $effectiveLateFee,
                    'oldest_due_date' => $oldestDueDate ? $oldestDueDate->format('d-m-Y') : 'N/A',
                    'max_days_overdue' => $maxDaysOverdue,
                ];

                $totalAcademicDueSum += $academicDue;
                $totalBusDueSum += $busDue;
                $totalLateFeeSum += $effectiveLateFee;
                $grandTotalDueSum += $totalStudentDue;
            }
        }

        return [
            'defaulters' => $defaulters,
            'allClasses' => $allClasses,
            'allBatches' => $allBatches,
            'totals' => [
                'count' => count($defaulters),
                'academic_due' => $totalAcademicDueSum,
                'bus_due' => $totalBusDueSum,
                'late_fees' => $totalLateFeeSum,
                'grand_total' => $grandTotalDueSum,
            ],
            'filters' => [
                'transport_filter' => $transportFilter,
                'class_filter' => $classFilter,
                'batch_filter' => $batchFilter,
                'fee_mode' => $feeMode,
                'include_late' => $includeLate,
                'search' => $search,
            ],
            'currentSession' => $this->currentSession,
        ];
    }

    public function defaulterList(Request $request)
    {
        $data = $this->getDefaultersData($request);

        return view('backend.defaulters.list', [
            'defaulters' => $data['defaulters'],
            'allClasses' => $data['allClasses'],
            'allBatches' => $data['allBatches'],
            'totals' => $data['totals'],
            'filters' => $data['filters'],
            'currentSession' => $data['currentSession'],
        ]);
    }

    public function exportDefaultersCSV(Request $request)
    {
        $data = $this->getDefaultersData($request);
        $defaulters = $data['defaulters'];
        $feeMode = $data['filters']['fee_mode'] ?? 'all';

        $csvRows = [];
        $i = 1;

        foreach ($defaulters as $studentId => $row) {
            $info = $row['info'];

            // Detailed dues summary string
            $headsSummary = [];
            foreach ($row['dues'] as $due) {
                if ($feeMode === 'without_bus' && $due['type'] === 'bus') {
                    continue;
                }
                $headsSummary[] = "{$due['account_name']} ({$due['term']}): ₹" . number_format($due['amount'], 2) . " [Due: {$due['due_date']}]";
            }

            $csvRows[] = [
                'Sr No' => $i++,
                'Scholar No' => $info['scholar_no'] ?: '-',
                'Student Name' => $info['name'],
                'Class' => $info['classname'],
                'Section' => $info['section'] ?: '-',
                'Batch' => $info['batch'],
                'Father Name' => $info['father_name'],
                'Transport' => $info['has_transport'] ? 'With Bus' : 'No Bus',
                'Tuition Due (₹)' => number_format($row['tuition_due'], 2, '.', ''),
                'Lunch Due (₹)' => number_format($row['lunch_due'], 2, '.', ''),
                'Academic Due (₹)' => number_format($row['academic_due'], 2, '.', ''),
                'Bus Due (₹)' => number_format($row['bus_due'], 2, '.', ''),
                'Late Fees (₹)' => number_format($row['effective_late_fee'], 2, '.', ''),
                'Total Outstanding (₹)' => number_format($row['total_due'], 2, '.', ''),
                'Oldest Due Date' => $row['oldest_due_date'],
                'Max Days Overdue' => $row['max_days_overdue'],
                'Fee Heads Breakdown' => implode(' | ', $headsSummary),
            ];
        }

        $filename = 'fee_defaulters_' . ($data['currentSession'] ?? 'report') . '_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($csvRows) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens symbols and rupees correctly
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if (!empty($csvRows)) {
                fputcsv($file, array_keys($csvRows[0]));
                foreach ($csvRows as $row) {
                    fputcsv($file, array_values($row));
                }
            } else {
                fputcsv($file, ['No matching defaulters found for the selected criteria.']);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function list(Request $request)
    {
        return $this->defaulterList($request);
    }
}
