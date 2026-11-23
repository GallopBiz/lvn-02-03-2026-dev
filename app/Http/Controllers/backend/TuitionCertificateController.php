<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TuitionCertificateController extends Controller
{
    public function generate(Request $request)
		{
			// Unpack the merged data from hidden field safely
			$rawMergedData = $request->input('merged_data');
			$merged_data = (!empty($rawMergedData) && is_string($rawMergedData)) ? @unserialize(base64_decode($rawMergedData)) : [];
			if (!is_array($merged_data)) {
				$merged_data = [];
			}

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
			$mergedData = array_merge($merged_data, $studentData, [
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
        $number = (float) str_replace(',', '', (string) $number);
        if ($number <= 0) {
            return 'Zero';
        }

        if (class_exists('\NumberFormatter')) {
            try {
                $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
                return ucwords($f->format($number), " \t\r\n\f\v-");
            } catch (\Throwable $e) {
                // Fallback to pure PHP converter below
            }
        }

        return ucwords($this->numberToWordsINR($number), " \t\r\n\f\v-");
    }

    protected function numberToWordsINR($num)
    {
        $num = (int) round($num);
        if ($num === 0) {
            return 'Zero';
        }

        $ones = [
            1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
            6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten',
            11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
            16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen'
        ];
        $tens = [
            2 => 'twenty', 3 => 'thirty', 4 => 'forty', 5 => 'fifty',
            6 => 'sixty', 7 => 'seventy', 8 => 'eighty', 9 => 'ninety'
        ];

        $words = [];

        if ($num >= 10000000) {
            $crore = (int) floor($num / 10000000);
            $words[] = $this->numberToWordsINR($crore) . ' crore';
            $num %= 10000000;
        }

        if ($num >= 100000) {
            $lakh = (int) floor($num / 100000);
            $words[] = $this->numberToWordsINR($lakh) . ' lakh';
            $num %= 100000;
        }

        if ($num >= 1000) {
            $thousand = (int) floor($num / 1000);
            $words[] = $this->numberToWordsINR($thousand) . ' thousand';
            $num %= 1000;
        }

        if ($num >= 100) {
            $hundred = (int) floor($num / 100);
            $words[] = $this->numberToWordsINR($hundred) . ' hundred';
            $num %= 100;
        }

        if ($num > 0) {
            if ($num < 20) {
                $words[] = $ones[$num];
            } else {
                $words[] = $tens[(int) floor($num / 10)] . ($num % 10 ? '-' . $ones[$num % 10] : '');
            }
        }

        return implode(' ', $words);
    }
}
