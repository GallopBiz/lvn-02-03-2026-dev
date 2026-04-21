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
        $selectedYear = $_COOKIE['selectedYear'] ?? null;
        $db_name = $request->input('session') ?? $request->input('session_name') ?? $selectedYear ?? "2023_2024";
        $this->currentSession = $db_name;

        $dynamicConfig = Config::get("database.connections.dynamic");
        $dynamicConfig['database'] = $db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');
    }

    public function defaulterList(Request $request)
    {
        $today = Carbon::now();
        $transportFilter = $request->get('transport_filter');
        $advanceFilter = $request->get('advance_filter');
        $classFilter = $request->get('class_filter');
        $batchFilter = $request->get('batch_filter');

        $students = DB::connection('dynamic')->table('student_registration')
            ->select('id', 'student_name', 'scholar_no', 'json_str', 'class_name')
            ->get()
            ->filter(function ($student) {
                $json = json_decode($student->json_str, true);
                $batch = trim((string) ($json['batch'] ?? ''));

                return ($json['application_for'] ?? '') !== 'RTE'
                    && $batch !== ''
                    && $batch === $this->currentSession;
            })
            ->keyBy('id');

        $allClasses = $students->map(function ($student) {
            $json = json_decode($student->json_str, true);
            return $json['classname'] ?? null;
        })->filter()->unique()->sort()->values();

        $allBatches = $students->map(function ($student) {
            $json = json_decode($student->json_str, true);
            return $json['batch'] ?? null;
        })->filter()->unique()->sort()->values();

        $dueData = DB::connection('dynamic')->table('generate_duechartstatus')->get();
        $defaulters = [];

        foreach ($dueData as $dueRecord) {

            $studentId = $dueRecord->student_id;
            if (!isset($students[$studentId])) continue;

            $student = $students[$studentId];
            $json = json_decode($student->json_str, true);

            $father_mobile = $json['father_mobile'] ?? 'N/A';
            $classname = $student->class_name;
            $batch = $json['batch'] ?? null;

            if ($classFilter && strtolower($classname) !== strtolower($classFilter)) continue;
            if ($batchFilter && strtolower((string) $batch) !== strtolower($batchFilter)) continue;

            $outerJson = json_decode($dueRecord->json_str, true);
            if (!isset($outerJson[0]['json_str'])) continue;

            $fees = json_decode($outerJson[0]['json_str'], true);

            if (
                !$fees ||
                !isset($fees['due_date']) ||
                !is_array($fees['due_date'])
            ) continue;

            $hasTransport = isset($fees['account_name']) &&
                collect($fees['account_name'])->contains(fn($name) => stripos($name, 'bus') !== false);

            $hasAdvance = isset($fees['account_name']) &&
                collect($fees['account_name'])->contains(fn($name) => stripos($name, 'advance') !== false);

            if ($transportFilter === 'with' && !$hasTransport) continue;
            if ($transportFilter === 'without' && $hasTransport) continue;
            if ($advanceFilter === 'with' && !$hasAdvance) continue;
            if ($advanceFilter === 'without' && $hasAdvance) continue;

            $paidReceipts = DB::connection('dynamic')->table('feesreceiptchallan')
                ->where('student_id', $studentId)
                ->get()
                ->map(fn($r) => json_decode($r->str_json, true))
                ->filter()
                ->toArray();

            $dues = [];
            $onlyBusInTerm1 = true;

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

                if ($dueDate->greaterThan($today)) continue;

                $accountHead = strtoupper(trim($fees['account_name'][$i]));
                $term = strtoupper(trim($fees['term'][$i]));
                $dueAmount = floatval($fees['fees'][$i]);
                $paidAmount = 0;

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

                        if (
                            $head === $fees['account_name'][$i] &&
                            $termStr === $fees['term'][$i]
                        ) {
                            $paidAmount += floatval($amt);
                        }
                    }
                }

                $remaining = $dueAmount - $paidAmount;

                if ($remaining > 1) {

                    if (in_array($accountHead, ['TUITION FEES', 'LUNCH FEES'])) {

                        $onlyBusInTerm1 = false;

                        $dues[] = [
                            'account_name' => $fees['account_name'][$i],
                            'term' => $fees['term'][$i],
                            'due_date' => $fees['due_date'][$i],
                            'amount' => $remaining,
                        ];
                    } elseif (stripos($accountHead, 'bus') !== false && $term === 'TERM 1') {
                        // only bus case
                    } else {
                        $onlyBusInTerm1 = false;
                    }
                }
            }

            if (!empty($dues) && !$onlyBusInTerm1) {
                $defaulters[$studentId]['info'] = [
                    'name' => $student->student_name,
                    'scholar_no' => $student->scholar_no,
                    'father_mobile' => $father_mobile,
                    'classname' => $classname,
                    'batch' => $batch,
                ];

                $defaulters[$studentId]['dues'] = $dues;
            }
        }

        return view('backend.defaulters.list', compact('defaulters', 'allClasses', 'allBatches'));
    }

    public function exportDefaultersCSV(Request $request)
    {
        $today = Carbon::now();
        $transportFilter = $request->get('transport_filter');
        $advanceFilter = $request->get('advance_filter');
        $classFilter = $request->get('class_filter');
        $batchFilter = $request->get('batch_filter');

        $students = DB::connection('dynamic')->table('student_registration')
            ->select('id', 'student_name', 'scholar_no', 'json_str', 'class_name')
            ->get()
            ->filter(function ($student) {
                $json = json_decode($student->json_str, true);
                $batch = trim((string) ($json['batch'] ?? ''));

                return strtoupper($json['application_for'] ?? '') !== 'RTE'
                    && $batch !== ''
                    && $batch === $this->currentSession;
            })
            ->keyBy('id');

        $dueData = DB::connection('dynamic')->table('generate_duechartstatus')->get();
        $csvRows = [];

        foreach ($dueData as $dueRecord) {
            $studentId = $dueRecord->student_id;
            if (!isset($students[$studentId])) {
                continue;
            }

            $student = $students[$studentId];
            $json = json_decode($student->json_str, true);
            $classname = $student->class_name;
            $batch = $json['batch'] ?? null;

            if ($classFilter && strtolower($classname) !== strtolower($classFilter)) {
                continue;
            }

            if ($batchFilter && strtolower((string) $batch) !== strtolower($batchFilter)) {
                continue;
            }

            $outerJson = json_decode($dueRecord->json_str, true);
            if (!isset($outerJson[0]['json_str'])) {
                continue;
            }

            $fees = json_decode($outerJson[0]['json_str'], true);
            if (!$fees || !isset($fees['due_date']) || !is_array($fees['due_date'])) {
                continue;
            }

            $hasTransport = isset($fees['account_name']) &&
                collect($fees['account_name'])->contains(fn($name) => stripos($name, 'bus') !== false);

            $hasAdvance = isset($fees['account_name']) &&
                collect($fees['account_name'])->contains(fn($name) => stripos($name, 'advance') !== false);

            if ($transportFilter === 'with' && !$hasTransport) {
                continue;
            }
            if ($transportFilter === 'without' && $hasTransport) {
                continue;
            }
            if ($advanceFilter === 'with' && !$hasAdvance) {
                continue;
            }
            if ($advanceFilter === 'without' && $hasAdvance) {
                continue;
            }

            $paidReceipts = DB::connection('dynamic')->table('feesreceiptchallan')
                ->where('student_id', $studentId)
                ->get()
                ->map(fn($row) => json_decode($row->str_json, true))
                ->filter()
                ->toArray();

            $totalDue = 0;
            $onlyBusInTerm1 = true;
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

                if ($dueDate->greaterThan($today)) {
                    continue;
                }

                $accountHead = strtoupper(trim($fees['account_name'][$i]));
                $term = strtoupper(trim($fees['term'][$i]));
                $dueAmount = (float) $fees['fees'][$i];
                $paidAmount = 0;

                foreach ($paidReceipts as $payment) {
                    if (
                        !isset($payment['head_name']) ||
                        !isset($payment['term_str']) ||
                        !isset($payment['head_rec_ammount'])
                    ) {
                        continue;
                    }

                    $heads = explode(',', $payment['head_name']);
                    $terms = explode(',', $payment['term_str']);
                    $amounts = explode(',', $payment['head_rec_ammount']);

                    foreach ($amounts as $j => $amount) {
                        $head = $heads[$j] ?? null;
                        $termStr = $terms[$j] ?? null;

                        if ($head === null || $termStr === null) {
                            continue;
                        }

                        if ($head === $fees['account_name'][$i] && $termStr === $fees['term'][$i]) {
                            $paidAmount += (float) $amount;
                        }
                    }
                }

                $remaining = $dueAmount - $paidAmount;

                if ($remaining > 1) {
                    if (in_array($accountHead, ['TUITION FEES', 'LUNCH FEES'])) {
                        $onlyBusInTerm1 = false;
                        $totalDue += $remaining;
                    } elseif (stripos($accountHead, 'bus') === false || $term !== 'TERM 1') {
                        $onlyBusInTerm1 = false;
                    }
                }
            }

            if ($totalDue > 1 && !$onlyBusInTerm1) {
                $csvRows[] = [
                    'Sr No' => count($csvRows) + 1,
                    'Name' => $student->student_name,
                    'Scholar No' => $student->scholar_no,
                    'Class' => $classname,
                    'Batch' => $batch,
                    'Due Amount' => number_format($totalDue, 2),
                ];
            }
        }

        $filename = 'defaulters_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($csvRows) {
            $file = fopen('php://output', 'w');

            if (!empty($csvRows)) {
                fputcsv($file, array_keys($csvRows[0]));
                foreach ($csvRows as $row) {
                    fputcsv($file, array_values($row));
                }
            } else {
                fputcsv($file, ['No defaulters found']);
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
