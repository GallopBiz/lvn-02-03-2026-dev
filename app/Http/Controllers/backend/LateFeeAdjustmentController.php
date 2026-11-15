<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicController;
use App\Models\StudentLateFeeAdjustment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LateFeeAdjustmentController extends Controller
{
    /**
     * Display list of late fee adjustments with filter/search and stats.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $statusFilter = trim($request->input('status', ''));

        $query = StudentLateFeeAdjustment::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('scholar_no', 'like', "%{$search}%")
                  ->orWhere('student_name', 'like', "%{$search}%")
                  ->orWhere('class_name', 'like', "%{$search}%");
            });
        }

        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        $adjustments = $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString();

        // Calculate summary metrics
        $allAdjustments = StudentLateFeeAdjustment::all();
        $stats = [
            'total_count' => $allAdjustments->count(),
            'waived_count' => $allAdjustments->where('status', 'waived')->count(),
            'adjusted_count' => $allAdjustments->where('status', 'adjusted')->count(),
            'total_saved' => $allAdjustments->sum(function ($item) {
                return max(0, floatval($item->original_late_fee) - floatval($item->adjusted_late_fee));
            }),
        ];

        return view('backend.late_fees.adjustments', compact('adjustments', 'stats', 'search', 'statusFilter'));
    }

    /**
     * Export late fee adjustments ledger to CSV (Excel compatible).
     */
    public function exportCSV(Request $request)
    {
        $search = trim($request->input('search', ''));
        $statusFilter = trim($request->input('status', ''));

        $query = StudentLateFeeAdjustment::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('scholar_no', 'like', "%{$search}%")
                  ->orWhere('student_name', 'like', "%{$search}%")
                  ->orWhere('class_name', 'like', "%{$search}%");
            });
        }

        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        $records = $query->orderBy('updated_at', 'desc')->get();

        $csvRows = [];
        $i = 1;
        foreach ($records as $item) {
            $csvRows[] = [
                'Sr No' => $i++,
                'Scholar No' => $item->scholar_no ?: '-',
                'Student Name' => $item->student_name ?: '-',
                'Class' => $item->class_name ?: '-',
                'Term' => $item->term === 'all' ? 'All Terms' : "Term {$item->term}",
                'Original Late Fee' => number_format((float) $item->original_late_fee, 2, '.', ''),
                'Adjusted Late Fee' => number_format((float) $item->adjusted_late_fee, 2, '.', ''),
                'Relief / Discount' => number_format(max(0, (float) $item->original_late_fee - (float) $item->adjusted_late_fee), 2, '.', ''),
                'Status' => ucfirst($item->status),
                'Reason / Remarks' => $item->reason ?: '-',
                'Adjusted By' => $item->admin_name ?: 'Admin',
                'Date' => $item->updated_at ? $item->updated_at->format('d-M-Y h:i A') : '-',
            ];
        }

        $filename = 'late_fee_adjustments_ledger_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($csvRows) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            if (!empty($csvRows)) {
                fputcsv($file, array_keys($csvRows[0]));
                foreach ($csvRows as $row) {
                    fputcsv($file, array_values($row));
                }
            } else {
                fputcsv($file, ['No adjustment records found for selected criteria.']);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * AJAX endpoint to fetch student fee & overdue term breakdown.
     */
    public function getStudentFeeInfo(Request $request)
    {
        $scholarNo = trim($request->input('scholar_no', ''));
        if (empty($scholarNo)) {
            return response()->json(['status' => 'error', 'message' => 'Scholar number is required.'], 422);
        }

        $student = DB::connection('dynamic')->table('student_registration')
            ->where('scholar_no', $scholarNo)
            ->first();

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => "Student not found with scholar number: {$scholarNo}",
            ], 404);
        }

        // Fetch due chart
        $dueChart = DB::connection('dynamic')->table('generate_duechartstatus')
            ->where('student_id', $student->id)
            ->first();

        if (!$dueChart) {
            return response()->json([
                'status' => 'error',
                'message' => "Fee structure / Due chart not generated for student {$student->student_name} (Scholar: {$scholarNo}).",
            ], 404);
        }

        $dueJson = json_decode($dueChart->json_str, true);
        $subJson = isset($dueJson[0]['json_str']) ? json_decode($dueJson[0]['json_str'], true) : [];

        // Fetch past receipts
        $feeReceipts = DB::connection('dynamic')->table('feesreceiptchallan')
            ->where('student_id', $student->id)
            ->select('str_json')
            ->get();

        $allocatedByHeadTerm = [];
        foreach ($feeReceipts as $rec) {
            $jsonData = json_decode($rec->str_json, true) ?: [];
            $headNames = explode(',', $jsonData['head_name'] ?? '');
            $headRecAmount = explode(',', $jsonData['head_rec_ammount'] ?? '');
            $termStr = explode(',', $jsonData['term_str'] ?? '');

            foreach ($headNames as $idx => $hName) {
                $termVal = trim($termStr[$idx] ?? '');
                $amount = floatval($headRecAmount[$idx] ?? 0);
                $key = trim($hName) . '_' . $termVal;
                $allocatedByHeadTerm[$key] = ($allocatedByHeadTerm[$key] ?? 0) + $amount;
            }
        }

        // Fetch existing adjustments for this student
        $existingAdjustments = StudentLateFeeAdjustment::where('student_id', $student->id)
            ->get()
            ->keyBy(function ($item) {
                return strtolower(trim((string)$item->term));
            });

        $publicCtrl = new PublicController($request);
        $today = Carbon::today();
        $termsData = [];
        $accounts = $subJson['account_name'] ?? [];

        foreach ($accounts as $idx => $accountName) {
            if ($accountName !== 'TUITION FEES') {
                continue;
            }

            $rawTerm = $subJson['term'][$idx] ?? '';
            $termDigits = preg_replace('/[^0-9]/', '', (string)$rawTerm);
            $normalizedTerm = !empty($termDigits) ? $termDigits : strtolower(trim((string)$rawTerm));
            $baseFee = floatval($subJson['fees'][$idx] ?? 0);
            $dueDateStr = $subJson['due_date'][$idx] ?? '';

            // Calculate paid amount
            $headTermKey1 = "TUITION FEES_{$rawTerm}";
            $headTermKey2 = "TUITION FEES_{$normalizedTerm}";
            $paidAmount = $allocatedByHeadTerm[$headTermKey1] ?? ($allocatedByHeadTerm[$headTermKey2] ?? 0);
            $dueAmount = max(0, $baseFee - $paidAmount);
            $isPaid = $dueAmount <= 0;

            // Calculate overdue days
            $daysOverdue = 0;
            $isOverdue = false;
            if (!empty($dueDateStr)) {
                try {
                    $dueDate = Carbon::parse($dueDateStr);
                    if ($today->gt($dueDate)) {
                        $daysOverdue = $dueDate->diffInDays($today);
                        $isOverdue = true;
                    }
                } catch (\Throwable $e) {
                    $isOverdue = false;
                }
            }

            // Calculate auto late fee matching PublicController logic:
            // In PublicController, late fee is ONLY charged if no payment has been allocated for this term and it is past due date.
            $autoLateFee = 0;
            if ($paidAmount <= 0 && $isOverdue) {
                $autoLateFee = $publicCtrl->calculateLateFee($dueDateStr);
            }

            // Check if student has an adjustment for this term or 'all'
            $adj = $existingAdjustments->get($normalizedTerm) ?: $existingAdjustments->get('all');
            $isAdjusted = $adj !== null;
            $isWaived = $isAdjusted && floatval($adj->adjusted_late_fee) == 0;
            $effectiveLateFee = $isAdjusted ? floatval($adj->adjusted_late_fee) : floatval($autoLateFee);

            $termsData[] = [
                'term_label' => $rawTerm ?: "Term {$normalizedTerm}",
                'term_code' => $normalizedTerm,
                'due_date' => $dueDateStr,
                'days_overdue' => $daysOverdue,
                'is_overdue' => $isOverdue,
                'base_fee' => $baseFee,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'is_paid' => $dueAmount <= 0,
                'auto_late_fee' => $autoLateFee,
                'effective_late_fee' => $effectiveLateFee,
                'is_adjusted' => $isAdjusted,
                'is_waived' => $isWaived,
                'adjusted_late_fee' => $isAdjusted ? floatval($adj->adjusted_late_fee) : null,
                'reason' => $isAdjusted ? $adj->reason : '',
                'adjustment_id' => $isAdjusted ? $adj->id : null,
                'updated_at' => $isAdjusted && $adj->updated_at ? $adj->updated_at->format('d-M-Y h:i A') : '',
            ];
        }

        return response()->json([
            'status' => 'success',
            'student' => [
                'id' => $student->id,
                'scholar_no' => $student->scholar_no,
                'student_name' => $student->student_name,
                'class_name' => $student->class_name,
                'section_name' => $student->section_name ?? '',
                'father_name' => $student->father_name ?? '',
            ],
            'terms' => $termsData,
            'global_adjustment' => $existingAdjustments->get('all'),
        ]);
    }

    /**
     * Save, update, waive or reset student late fee adjustment.
     */
    public function saveAdjustment(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|numeric',
            'scholar_no' => 'required|string',
            'term' => 'required|string',
            'action_type' => 'required|in:waive,adjust,reset',
            'adjusted_late_fee' => 'nullable|numeric|min:0',
            'original_late_fee' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $studentId = $validated['student_id'];
        $scholarNo = $validated['scholar_no'];
        $rawTerm = $validated['term'];
        $actionType = $validated['action_type'];
        $reason = trim($validated['reason'] ?? '');

        $normalizedTerm = StudentLateFeeAdjustment::normalizeTerm($rawTerm);

        // RESET ACTION: delete adjustment and restore automatic calculation
        if ($actionType === 'reset') {
            if ($normalizedTerm === 'all') {
                StudentLateFeeAdjustment::where('student_id', $studentId)->delete();
            } else {
                StudentLateFeeAdjustment::where('student_id', $studentId)
                    ->where(function ($q) use ($normalizedTerm) {
                        $q->where('term', $normalizedTerm)->orWhere('term', 'all');
                    })
                    ->delete();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Late fee adjustment removed. Reverted back to automatic calculation.',
            ]);
        }

        // WAIVE OR ADJUST ACTION
        $student = DB::connection('dynamic')->table('student_registration')
            ->where('id', $studentId)
            ->first();

        $studentName = $student->student_name ?? '';
        $className = $student->class_name ?? '';

        if ($actionType === 'waive') {
            $adjustedAmount = 0.00;
            $status = 'waived';
            $defaultReason = 'Waived by Admin';
        } else {
            $adjustedAmount = floatval($validated['adjusted_late_fee'] ?? 0);
            $status = 'adjusted';
            $defaultReason = 'Adjusted by Admin';
        }

        $adminUser = Auth::user();
        $adminName = $adminUser ? ($adminUser->name ?? $adminUser->username ?? 'Admin') : 'Admin';
        $adminId = Auth::id() ?: null;
        $sessionName = session('selectedYear') ?: session('db_names') ?: '2025_2026';

        $adjustment = StudentLateFeeAdjustment::updateOrCreate(
            [
                'student_id' => $studentId,
                'term' => $normalizedTerm,
            ],
            [
                'scholar_no' => $scholarNo,
                'student_name' => $studentName,
                'class_name' => $className,
                'original_late_fee' => floatval($validated['original_late_fee'] ?? 0),
                'adjusted_late_fee' => $adjustedAmount,
                'status' => $status,
                'reason' => !empty($reason) ? $reason : $defaultReason,
                'admin_id' => $adminId,
                'admin_name' => $adminName,
                'session_name' => $sessionName,
            ]
        );

        $termLabel = $normalizedTerm === 'all' ? 'All Terms' : "Term {$normalizedTerm}";
        $successMsg = $status === 'waived'
            ? "Late fee for {$termLabel} successfully WAIVED (₹0.00) for {$studentName} ({$scholarNo})."
            : "Late fee for {$termLabel} successfully adjusted to ₹" . number_format($adjustedAmount, 2) . " for {$studentName} ({$scholarNo}).";

        return response()->json([
            'status' => 'success',
            'message' => $successMsg,
            'adjustment' => $adjustment,
        ]);
    }

    /**
     * Delete an adjustment by ID, restoring automatic late fee calculation.
     */
    public function resetAdjustment($id)
    {
        $adjustment = StudentLateFeeAdjustment::find($id);
        if (!$adjustment) {
            return response()->json(['status' => 'error', 'message' => 'Adjustment record not found.'], 404);
        }

        $studentName = $adjustment->student_name ?: $adjustment->scholar_no;
        $termLabel = $adjustment->term === 'all' ? 'All Terms' : "Term {$adjustment->term}";
        $adjustment->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Adjustment for {$studentName} ({$termLabel}) removed. Reverted back to automatic calculation.",
        ]);
    }
}

