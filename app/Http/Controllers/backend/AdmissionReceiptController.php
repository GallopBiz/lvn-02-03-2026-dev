<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdmissionReceiptController extends Controller
{
    public function show($scholar_no)
    {
        // Get student data
        $student = DB::table('student_registration')
            ->where('scholar_no', $scholar_no)
            ->first();

        if (!$student) {
            return redirect()->back()->with('error', 'Student not found');
        }

        $json = json_decode($student->json_str, true);

        // Get fee heads from totalnextyear
        $feeHeads = DB::table('totalnextyear')
            ->where('scholar_no', $scholar_no)
            ->select('account_name', 'fees', 'created_at')
            ->get();

        return view('backend.admission-receipt', [
            'student' => $student,
            'json' => $json,
            'feeHeads' => $feeHeads,
        ]);
    }
}
