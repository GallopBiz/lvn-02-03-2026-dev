<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdmissionReceiptController extends Controller
{
    public function show(Request $request, $scholar_no)
    {
        // Get student data
        $student = DB::table('student_registration')
            ->where('scholar_no', $scholar_no)
            ->first();

        if (!$student) {
            return redirect()->back()->with('error', 'Student not found');
        }

        $json = json_decode($student->json_str, true) ?: [];

        // Check for specific receipt or latest receipt
        $receiptNo = $request->query('receipt');
        if (!$receiptNo) {
            $latestReceipt = DB::table('totalnextyear')
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
        $query = DB::table('totalnextyear')
            ->where('scholar_no', $scholar_no)
            ->where(function ($q) {
                $q->where('is_delete', 0)->orWhereNull('is_delete');
            });

        if ($receiptNo) {
            $query->where('receipt_number', $receiptNo);
        }

        $feeHeads = $query->orderBy('id')->get();

        return view('backend.admission-receipt', [
            'student' => $student,
            'json' => $json,
            'feeHeads' => $feeHeads,
        ]);
    }
}
