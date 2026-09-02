<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Student_registration;
use App\Models\Classname;
use App\Models\NextYearStudent;
use App\Models\Inquiry_registration;


class StudentTransferController extends Controller
{
    public function index(Request $request)
    {
        $this->applySessionDatabaseBindings($request);

		$classes = Classname::on('dynamic')->get(); // Fetch all classes from selected session DB
        return view('backend.student_transfer.index', compact('classes'));
    }

    public function fetchStudents(Request $request)
    {
        try {
            $this->applySessionDatabaseBindings($request);

            $transferredScholarNos = [];
            if ($this->nextSessionStudentTableExists()) {
                $transferredScholarNos = NextYearStudent::pluck('scholar_no')->toArray();
            }

            $studentQuery = Student_registration::on('dynamic')->where('class_name', $request->class_id)
                ->whereNotIn('scholar_no', $transferredScholarNos);
            $this->applyActiveStudentFilters($studentQuery);

            $students = $studentQuery->get();
            //echo $students->toSql();die;
		
            $students = $students->map(function ($student) {
                $jsonData = json_decode($student->json_str, true);
                $student->section_name = $jsonData['section_name'] ?? 'N/A'; // Default if not found
                return $student;
            })->sortBy('section_name')->values(); // Sorting by section name

            return response()->json($students);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to fetch students for the selected class.',
            ], 500);
        }
    }

   public function promote(Request $request)
{
    $this->applySessionDatabaseBindings($request);

    $selectedStudents = $request->selected_students;

    if ($selectedStudents) {
        $currentSession = $this->resolveSelectedSessionYear($request);
        if (empty($currentSession)) {
			$registration = Student_registration::on('dynamic')->first();
            $currentSession = $registration->session_name ?? null;
        }

        if (empty($currentSession) || !preg_match('/^\d{4}_\d{4}$/', $currentSession)) {
            return redirect()->back()->with('error', 'Invalid session year selected.');
        }

        [$startYear, $endYear] = explode('_', $currentSession);
        $nextSession = ($startYear + 1) . '_' . ($endYear + 1);

        if (!$this->nextSessionStudentTableExists()) {
            return redirect()->back()->with('error', 'Next session database is not available.');
        }

        $studentQuery = Student_registration::on('dynamic')->whereIn('id', $selectedStudents);
        $this->applyActiveStudentFilters($studentQuery);

        $students = $studentQuery->get();

        // Step 1: Fetch inquiry data for all selected students
        $formNumbers = $students->pluck('form_number')->toArray();
        $inquiryRecords = Inquiry_registration::on('dynamic')->get(); // Consider filtering if performance is a concern

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

            if ($newClassName === null) {
                continue;
            }

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
			return $newClass <= 12 ? $newClass : null; // Class 12 students should not get a Class 13 enrollment.
		}

		return $currentClass; // Default: No change if class doesn't match
	}

    private function applyActiveStudentFilters($query): void
    {
        if (Schema::connection('dynamic')->hasColumn('student_registration', 'status')) {
            $query->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 't');
            });
        }

        if (Schema::connection('dynamic')->hasColumn('student_registration', 'transfer_status')) {
            $query->where(function ($q) {
                $q->whereNull('transfer_status')->orWhere('transfer_status', '!=', 'transferred');
            });
        }
    }

    private function applySessionDatabaseBindings(Request $request): void
    {
        $selectedYear = $this->resolveSelectedSessionYear($request);
        if (!$selectedYear || !preg_match('/^\d{4}_\d{4}$/', $selectedYear)) {
            return;
        }

        [$startYear, $endYear] = explode('_', $selectedYear);
        $nextSession = ($startYear + 1) . '_' . ($endYear + 1);

        Config::set('database.connections.dynamic.database', $selectedYear);
        Config::set('database.default', 'dynamic');
        DB::purge('dynamic');
        DB::reconnect('dynamic');
        DB::setDefaultConnection('dynamic');

        Config::set('database.connections.next_session_db.database', $nextSession);
        DB::purge('next_session_db');
        try {
            DB::reconnect('next_session_db');
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function nextSessionStudentTableExists(): bool
    {
        try {
            return Schema::connection('next_session_db')->hasTable('student_registration');
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }

    private function resolveSelectedSessionYear(Request $request): ?string
    {
        $selectedYear = $request->session()->get('selectedYear');
        if (empty($selectedYear)) {
            $selectedYear = $request->cookie('selectedYear');
        }
        if (empty($selectedYear)) {
            $selectedYear = $request->input('year');
        }

        return is_string($selectedYear) ? $selectedYear : null;
    }



}
