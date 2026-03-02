<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Student_registration;
use App\Models\Classname;
use App\Models\NextYearStudent;
use App\Models\Inquiry_registration;


class StudentTransferController extends Controller
{
    public function index()
    {
        $classes = Classname::all(); // Fetch all classes
        return view('backend.student_transfer.index', compact('classes'));
    }

    public function fetchStudents(Request $request)
    {
		$transferredScholarNos = NextYearStudent::pluck('scholar_no')->toArray();

        $students = Student_registration::where('class_name', $request->class_id)
		->whereNotIn('scholar_no', $transferredScholarNos)
		->get();
			//echo $students->toSql();die;
		
		$students = $students->map(function ($student) {
        $jsonData = json_decode($student->json_str, true);
        $student->section_name = $jsonData['section_name'] ?? 'N/A'; // Default if not found
        return $student;
    })->sortBy('section_name')->values(); // Sorting by section name

        return response()->json($students);
    }

   public function promote(Request $request)
{
    $selectedStudents = $request->selected_students;

    if ($selectedStudents) {
        $currentSession = Student_registration::first()->session_name;
        [$startYear, $endYear] = explode('_', $currentSession);
        $nextSession = ($startYear + 1) . '_' . ($endYear + 1);

        $students = Student_registration::whereIn('id', $selectedStudents)->get();

        // Step 1: Fetch inquiry data for all selected students
        $formNumbers = $students->pluck('form_number')->toArray();
        $inquiryRecords = Inquiry_registration::all(); // Consider filtering if performance is a concern

        $inquiryMap = [];

        foreach ($inquiryRecords as $inq) {
            $json = json_decode($inq->json_str, true);
            if (!empty($json['formno'])) {
                $inquiryMap[$json['formno']] = $inq->next_year;
            }
        }

        foreach ($students as $student) {
            $formNumber = $student->form_number;
            $nextYearValue = $inquiryMap[$formNumber] ?? 0;

            // If next_year == 1, DO NOT promote class
            $newClassName = ($nextYearValue == 1)
                ? $student->class_name
                : $this->getNextClassName($student->class_name);

            $data = json_decode($student->json_str, true);
            if (empty($data['section_name'])) {
                $data['section_name'] = 'Aryabhatta';
            }

            $student->json_str = json_encode($data);
            $student->save();

            NextYearStudent::create([
                'id'              => $student->id,
                'application_for' => $student->application_for,
                'form_number'     => $formNumber,
                'scholar_no'      => $student->scholar_no,
                'date_of_birth'   => $student->date_of_birth,
                'class_name'      => $newClassName,
                'student_name'    => $student->student_name,
                'session_name'    => $nextSession,
                'json_str'        => $student->json_str,
                'staff_name'      => $student->staff_name,
                'phone_number'    => $student->phone_number,
                'mobile_number'   => $student->mobile_number,
                'inq_mode'        => $student->inq_mode,
                'driver'          => $student->driver,
                'status'          => $student->status,
                'type'            => $student->type,
                'password'        => $student->password,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Students transferred successfully. Class promotion skipped if next_year = 1.');
    }

    return redirect()->back()->with('error', 'No students selected!');
}

	
	private function getNextClassName($currentClass)
	{
		// Mapping for text-based classes
		$classPromotionMap = [
			'Nursery' => 'KG1',
			'KG1' => 'KG2',
			'KG2' => '01', // Switch to numeric classes
		];

		// Check if class exists in text-based mapping
		if (isset($classPromotionMap[$currentClass])) {
			return $classPromotionMap[$currentClass];
		}

		// If it's numeric (01 to 12), increment the number
		if (preg_match('/^\d+$/', $currentClass)) {
			$newClass = str_pad((int)$currentClass + 1, 2, '0', STR_PAD_LEFT); // Keep 2-digit format
			return $newClass <= 12 ? $newClass : 'Graduated'; // 12th class students graduate
		}

		return $currentClass; // Default: No change if class doesn't match
	}



}
