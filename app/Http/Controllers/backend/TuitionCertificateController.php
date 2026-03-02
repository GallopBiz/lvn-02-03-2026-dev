<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TuitionCertificateController extends Controller
{
    public function generate(Request $request)
		{
			// Unpack the merged data from hidden field
			$merged_data = unserialize(base64_decode($request->input('merged_data')));

			// Get student details directly from the form inputs
			$studentData = [
				'student_name' => $request->input('studentname', 'N/A'),
				'father_name' => $request->input('fathername', 'N/A'),
				'dob' => $request->input('student_dob', 'N/A'),
				'class' => $request->input('classname', 'N/A'),
				'section' => $request->input('section_name', 'N/A'),
				'batch' => $request->input('batch', 'N/A'),
				'scholar_no' => $request->input('scholar_no', 'N/A'),
				'total_credited' => $request->input('total_credited', 'N/A'),
			];
			
			// Get gender from the request (hidden field in the form)
			$gender = $request->input('gender', '');

			// Set the gender-related label (s/o or d/o)
			$genderPrefix = '';
			if ($gender === 'Female') {
				$genderPrefix = 'D/O';  // Daughter of
			} elseif ($gender === 'Male') {
				$genderPrefix = 'S/O';  // Son of
			}

			// Format current date
			$current_date = now()->format('d-M-Y');

			// Use session from request or fallback default
			$session = $request->input('session', '2025–2026');

			// Merge final data for PDF
			// Merge final data for PDF
			$mergedData = array_merge($studentData, [
				'fees_paid' => $request->input('total_credited', 0),
				'fees_paid_words' => $this->convertNumberToWords($request->input('total_credited', 0)),
				'permanent_address' => $request->input('permanent_address', ''),
				'local_address' => $request->input('present_address', ''),
				'session' => $session,
				'gender_prefix' => $genderPrefix, // Pass the gender prefix
			]);


			// Load the PDF view
			return view('backend.tuition_fee_certificate_pdf', [
				'merged_data' => $mergedData,
				'current_date' => $current_date,
				'session' => $session,
			]);
		}


    // Function to convert a number to words (example: 25000 => 'Twenty-Five Thousand')
    protected function convertNumberToWords($number)
    {
        $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        return ucwords($f->format($number));
    }
}
