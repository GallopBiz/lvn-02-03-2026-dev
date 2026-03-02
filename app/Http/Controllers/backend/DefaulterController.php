<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DefaulterController extends Controller
{
    public function __construct(Request $request)
    {
        $selectedYear = $_COOKIE['selectedYear'] ?? null;
        $db_name = $request->session ?? $request->session_name ?? $selectedYear ?? "2023_2024";

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

        $students = DB::connection('dynamic')->table('student_registration')->select('id', 'student_name', 'scholar_no', 'json_str', 'class_name')->get()
        ->filter(function ($student) {
            $json = json_decode($student->json_str, true);
            return ($json['application_for'] ?? '') !== 'RTE';
        })
        ->keyBy('id');
        $allClasses = $students->map(function ($student) {
            $json = json_decode($student->json_str, true);
            return $json['classname'] ?? null;
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

            if ($classFilter && strtolower($classname) !== strtolower($classFilter)) {
                continue;
            }

            $outerJson = json_decode($dueRecord->json_str, true);
            if (!isset($outerJson[0]['json_str'])) continue;

            $fees = json_decode($outerJson[0]['json_str'], true);
            if (!$fees || !isset($fees['due_date'])) continue;

            $hasTransport = collect($fees['account_name'])->contains(fn($name) => stripos($name, 'bus') !== false);
            $hasAdvance = collect($fees['account_name'])->contains(fn($name) => stripos($name, 'advance') !== false);

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

            for ($i = 0; $i < count($fees['due_date']); $i++) {
                try {
                    $dueDate = Carbon::parse(trim($fees['due_date'][$i]));
                } catch (\Exception $e) {
                    continue;
                }

                if ($dueDate->greaterThan($today)) continue;

                $accountHead = strtoupper(trim($fees['account_name'][$i] ?? ''));
                $term = strtoupper(trim($fees['term'][$i] ?? ''));
                $dueAmount = floatval($fees['fees'][$i]);
                $paidAmount = 0;

                foreach ($paidReceipts as $payment) {
                    $heads = explode(",", $payment['head_name']);
                    $terms = explode(",", $payment['term_str']);
                    $amounts = explode(",", $payment['head_rec_ammount']);

                    foreach ($amounts as $j => $amt) {
                        if (
                            ($heads[$j] ?? '') === $fees['account_name'][$i] &&
                            ($terms[$j] ?? '') === $fees['term'][$i]
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
                        // still count as only bus in term 1
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
                ];
                $defaulters[$studentId]['dues'] = $dues;
            }
        }

        return view('backend.defaulters.list', compact('defaulters', 'allClasses'));
    }

    public function exportDefaultersCSV(Request $request)
{
    $transportFilter = $request->get('transport_filter');
    $advanceFilter = $request->get('advance_filter');
    $classFilter = $request->get('class_filter');
    $today = Carbon::now();

    $students = DB::connection('dynamic')->table('student_registration')
        ->select('id', 'student_name', 'scholar_no', 'json_str', 'class_name')
        ->get()
        ->filter(function ($student) {
            $json = json_decode($student->json_str, true);
            return strtoupper($json['application_for'] ?? '') !== 'RTE';
        })
        ->keyBy('id');

    $dueData = DB::connection('dynamic')->table('generate_duechartstatus')->get();
    $csvRows = [];

    foreach ($dueData as $dueRecord) {
        $studentId = $dueRecord->student_id;
        if (!isset($students[$studentId])) continue;

        $student = $students[$studentId];
        $json = json_decode($student->json_str, true);
        $classname = $student->class_name;

        // Apply class filter
        if ($classFilter && strtolower($classname) !== strtolower($classFilter)) {
            continue;
        }

        $outerJson = json_decode($dueRecord->json_str, true);
        if (!isset($outerJson[0]['json_str'])) continue;

        $fees = json_decode($outerJson[0]['json_str'], true);
        if (!$fees || !isset($fees['due_date'])) continue;

        $hasTransport = collect($fees['account_name'])->contains(fn($name) => stripos($name, 'bus') !== false);
        $hasAdvance = collect($fees['account_name'])->contains(fn($name) => stripos($name, 'advance') !== false);

        // Apply transport & advance filters
        if ($transportFilter === 'with' && !$hasTransport) continue;
        if ($transportFilter === 'without' && $hasTransport) continue;
        if ($advanceFilter === 'with' && !$hasAdvance) continue;
        if ($advanceFilter === 'without' && $hasAdvance) continue;

        // Fetch paid receipts
        $paidReceipts = DB::connection('dynamic')->table('feesreceiptchallan')
            ->where('student_id', $studentId)
            ->get()
            ->map(fn($r) => json_decode($r->str_json, true))
            ->filter()
            ->toArray();

        $totalDue = 0;
        $onlyBusInTerm1 = true;

        for ($i = 0; $i < count($fees['due_date']); $i++) {
            try {
                $dueDate = Carbon::parse(trim($fees['due_date'][$i]));
            } catch (\Exception $e) {
                continue;
            }

            if ($dueDate->greaterThan($today)) continue;

            $accountHead = strtoupper(trim($fees['account_name'][$i] ?? ''));
            $term = strtoupper(trim($fees['term'][$i] ?? ''));
            $dueAmount = floatval($fees['fees'][$i]);
            $paidAmount = 0;

            foreach ($paidReceipts as $payment) {
                $heads = explode(",", $payment['head_name']);
                $terms = explode(",", $payment['term_str']);
                $amounts = explode(",", $payment['head_rec_ammount']);

                foreach ($amounts as $j => $amt) {
                    if (
                        ($heads[$j] ?? '') === $fees['account_name'][$i] &&
                        ($terms[$j] ?? '') === $fees['term'][$i]
                    ) {
                        $paidAmount += floatval($amt);
                    }
                }
            }

            $remaining = $dueAmount - $paidAmount;

            if ($remaining > 1) {
                if (in_array($accountHead, ['TUITION FEES', 'LUNCH FEES'])) {
                    $onlyBusInTerm1 = false;
                    $totalDue += $remaining;
                } elseif (stripos($accountHead, 'bus') !== false && $term === 'TERM 1') {
                    // only bus in term 1
                } else {
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
                'Due Amount' => number_format($totalDue, 2),
            ];
        }
    }

    $filename = "defaulters_" . now()->format('Ymd_His') . ".csv";
    $headers = [
        "Content-Type" => "text/csv",
        "Content-Disposition" => "attachment; filename=\"$filename\"",
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


}
