<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdmissionReceiptController extends Controller
{
    public function show(Request $request, $scholar_no)
    {
        $resolved = $this->resolveStudentAndConnection($scholar_no);
        $student = $resolved['student'] ?? null;
        $connection = $resolved['connection'] ?? DB::connection();

        if (!$student) {
            return redirect()->back()->with('error', 'Student not found');
        }

        $json = json_decode($student->json_str, true) ?: [];

        // Check for specific receipt or latest receipt
        $receiptNo = $request->query('receipt');
        if (!$receiptNo) {
            $latestReceipt = $connection->table('totalnextyear')
                ->where('scholar_no', $scholar_no)
                ->where(function ($q) {
                    $q->where('is_delete', 0)->orWhereNull('is_delete');
                })
                ->orderByDesc('id')
                ->value('receipt_number');
            if ($latestReceipt) {
                $receiptNo = $latestReceipt;
            }
        }

        // Get fee heads from totalnextyear
        $query = $connection->table('totalnextyear')
            ->where('scholar_no', $scholar_no)
            ->where(function ($q) {
                $q->where('is_delete', 0)->orWhereNull('is_delete');
            });

        if ($receiptNo) {
            $query->where('receipt_number', $receiptNo);
        }

        $feeHeads = $query->orderBy('id')->get();

        // If fee heads not found in the student's database, check other academic databases
        if ($feeHeads->isEmpty() && $receiptNo) {
            $candidateDbs = array_unique(array_filter([
                session('db_names'),
                request()->cookie('selectedYear'),
                '2026_2027',
                '2025_2026',
                '2027_2028',
            ]));

            foreach ($candidateDbs as $dbName) {
                try {
                    config(['database.connections.fee_receipt_fallback' => array_merge(config('database.connections.mysql'), ['database' => $dbName])]);
                    DB::purge('fee_receipt_fallback');
                    $foundFees = DB::connection('fee_receipt_fallback')->table('totalnextyear')
                        ->where('scholar_no', $scholar_no)
                        ->where('receipt_number', $receiptNo)
                        ->where(function ($q) {
                            $q->where('is_delete', 0)->orWhereNull('is_delete');
                        })
                        ->orderBy('id')
                        ->get();
                    if ($foundFees->isNotEmpty()) {
                        $feeHeads = $foundFees;
                        break;
                    }
                } catch (\Throwable $e) {}
            }
        }

        return view('backend.admission-receipt', [
            'student' => $student,
            'json' => $json,
            'feeHeads' => $feeHeads,
        ]);
    }

    private function resolveStudentAndConnection($scholar_no)
    {
        // 1. Try dynamic connection
        try {
            $student = DB::connection('dynamic')->table('student_registration')
                ->where('scholar_no', $scholar_no)
                ->first();
            if ($student) {
                return ['student' => $student, 'connection' => DB::connection('dynamic')];
            }
        } catch (\Throwable $e) {}

        // 2. Try default connection
        try {
            $student = DB::table('student_registration')
                ->where('scholar_no', $scholar_no)
                ->first();
            if ($student) {
                return ['student' => $student, 'connection' => DB::connection()];
            }
        } catch (\Throwable $e) {}

        // 3. Try candidate academic databases
        $candidateDbs = array_unique(array_filter([
            session('db_names'),
            request()->cookie('selectedYear'),
            '2026_2027',
            '2025_2026',
            '2027_2028',
        ]));

        foreach ($candidateDbs as $dbName) {
            try {
                config(['database.connections.student_receipt_lookup' => array_merge(config('database.connections.mysql'), ['database' => $dbName])]);
                DB::purge('student_receipt_lookup');
                $student = DB::connection('student_receipt_lookup')->table('student_registration')
                    ->where('scholar_no', $scholar_no)
                    ->first();
                if ($student) {
                    return ['student' => $student, 'connection' => DB::connection('student_receipt_lookup')];
                }
            } catch (\Throwable $e) {}
        }

        return ['student' => null, 'connection' => DB::connection()];
    }
}
