<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TuitionFeeCertificateController extends Controller
{
    // Show the form for searching
    public function index()
    {
        return view('backend.tuition_fees_certificate');
    }

    // Search for the student by Scholar No and return the results
    public function searchStudent(Request $request)
    {
        $scholar_no = $request->input('scholar_no');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        // Fetch students matching the scholar number and decode json_str to show required fields
		$students = DB::connection('dynamic')->table('student_registration')
		->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(json_str, '$.scholar_no')) = ?", [$scholar_no])
		->get()
		->map(function ($student) {
			// Decode the json_str to get the individual student data
			$student->data = json_decode($student->json_str);
			return $student;
		});

        // Filter fees based on the selected date range
        if ($start_date && $end_date) {
            $students->map(function ($student) use ($start_date, $end_date) {
                $studentData = $student->data;
                
                // Get the fees details from the 'feesreceiptchallan' table with the date range filter
                $fees = DB::connection('dynamic')->table('feesreceiptchallan')
                    ->where('student_id', $studentData->id)
                    ->whereBetween(DB::raw('DATE(created_at)'), [$start_date, $end_date])
                    ->get();

                $student->fees = $fees; // Attach the filtered fees to the student object
                return $student;
            });
        }

        return view('backend.tuition_fees_certificate', compact('students', 'start_date', 'end_date'));
    }

    // Generate the PDF certificate for the student
    public function generatePDF($id)
    {
        // Fetch the student data by ID
        $student = DB::connection('dynamic')->table('student_registration')
            ->where('id', $id)
            ->first();

        if (!$student) {
            return redirect()->route('fees.certificate.form')->with('error', 'Student not found!');
        }

        // Decode json_str to pass required data to PDF view
        $studentData = json_decode($student->json_str);

        // Get the payment details from the 'student_online_fee_payments' table
        $paymentDetails = DB::connection('dynamic')->table('student_online_fee_payments')
            ->where('transaction_id', $studentData->transaction_id)
            ->first();

        // Get the formatted payment date
        $paymentDateFormatted = $paymentDetails ? Carbon::parse($paymentDetails->created_at)->format('d-m-Y') : 'N/A';

        // Prepare other student details
        $scholarNo = $studentData->name_scholarno ?? 'N/A';
        $studentName = $studentData->name_student ?? 'N/A';
        $classSection = $studentData->name_classsection ?? 'N/A';

        // Get the related fees data from the 'fees_data' table
        $feesData = DB::connection('dynamic')->table('fees_data')
            ->where('student_id', $studentData->student_id) // Assuming student_id is available
            ->get();

        // Pass the data to the PDF view and generate the PDF
        $pdf = Pdf::loadView('backend.tuition_fee_certificate_pdf', compact(
            'studentData',
            'paymentDateFormatted',
            'scholarNo',
            'studentName',
            'classSection',
            'feesData'
        ));

        return $pdf->download('tuition_fee_certificate_' . $studentData->name_scholarno . '.pdf');
    }
}
