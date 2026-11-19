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

            $studentQuery = Student_registration::on('dynamic')->withoutGlobalScope('not_next_year')
                ->where('class_name', $request->class_id)
                ->whereNotIn('scholar_no', $transferredScholarNos);
            $this->applyActiveStudentFilters($studentQuery);

            $students = $studentQuery->get();
		
            $students = $students->map(function ($student) {
                $jsonData = json_decode($student->json_str, true) ?: [];
                $student->section_name = $student->section_name ?: ($jsonData['section_name'] ?? 'N/A');
                $student->next_year = (int)($student->next_year ?? ($jsonData['next_year'] ?? 0));
                $student->next_year_section = $student->next_year_section ?? ($jsonData['next_year_section'] ?? null);
                $student->next_year_stream = $student->next_year_stream ?? ($jsonData['next_year_stream'] ?? null);
                $student->next_year_subject_combination = $student->next_year_subject_combination ?? ($jsonData['next_year_subject_combination'] ?? null);
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

        $studentQuery = Student_registration::on('dynamic')->withoutGlobalScope('not_next_year')->whereIn('id', $selectedStudents);
        $this->applyActiveStudentFilters($studentQuery);

        $students = $studentQuery->get();

        // Step 1: Fetch inquiry data for fallback if needed
        $formNumbers = $students->pluck('form_number')->toArray();
        $inquiryRecords = Inquiry_registration::on('dynamic')->get(); // Consider filtering if performance is a concern

        $inquiryMap = [];

        foreach ($inquiryRecords as $inq) {
            $json = json_decode($inq->json_str, true);
            if (!empty($json['formno'])) {
                $inquiryMap[$json['formno']] = $inq->next_year;
            }
        }

        $promotedTargetClasses = [];

        foreach ($students as $student) {
            $formNumber = $student->form_number;
            $data = json_decode($student->json_str, true) ?: [];

            // Check if student is Next Year admission
            $isNextYear = (!empty($student->next_year) && ($student->next_year == 1 || $student->next_year === '1'));
            if (!$isNextYear) {
                if (!empty($data['next_year']) && ($data['next_year'] == 1 || $data['next_year'] === '1')) {
                    $isNextYear = true;
                } elseif (!empty($inquiryMap[$formNumber]) && ($inquiryMap[$formNumber] == 1 || $inquiryMap[$formNumber] === '1')) {
                    $isNextYear = true;
                }
            }

            if ($isNextYear) {
                // Category B: Next Year student - DO NOT PROMOTE CLASS
                // Class remains unchanged because student was registered directly for their upcoming class
                $newClassName = $student->class_name;
                $promotedSection = $student->next_year_section ?: ($student->section_name ?: ($data['section_name'] ?? 'D'));
                $targetStreamId = $student->next_year_stream ?: ($data['stream_id'] ?? null);
                $targetCombinationId = $student->next_year_subject_combination ?: ($data['combination_id'] ?? null);
            } else {
                // Category A: Regular existing student - PROMOTE CLASS
                $newClassName = $this->getNextClassName($student->class_name);
                $promotedSection = $student->section_name ?: ($data['section_name'] ?? 'Aryabhatta');
                $targetStreamId = $data['stream_id'] ?? null;
                $targetCombinationId = $data['combination_id'] ?? null;
            }

            if ($newClassName === null) {
                continue;
            }

            $promotedTargetClasses[] = $newClassName;

            $isPromotedSenior = in_array(trim((string)$newClassName), ['11', '12'], true);

            if ($isPromotedSenior) {
                $streamId = $targetStreamId;
                if (!empty($streamId)) {
                    $streamRecord = DB::connection('dynamic')->table('streams')->where('id', $streamId)->first();
                    $streamName = $streamRecord ? strtoupper(trim($streamRecord->streams)) : '';
                    if ($streamName === 'SCIENCE' || (int)$streamId === 3) {
                        $promotedSection = 'Kautilya';
                    } elseif (in_array($streamName, ['COMMERCE', 'HUMANITIES'], true) || in_array((int)$streamId, [1, 2], true)) {
                        $promotedSection = 'Ramanujan';
                    }
                }
            }

            // Find class_id in next_session_db
            $targetClassRecord = DB::connection('next_session_db')->table('classes')
                ->where('class_name', $newClassName)
                ->where('section_name', $promotedSection)
                ->where('is_delete', 0)
                ->first();
            $targetClassId = $targetClassRecord ? $targetClassRecord->id : null;
            if (!$targetClassId) {
                $firstClass = DB::connection('next_session_db')->table('classes')
                    ->where('class_name', $newClassName)
                    ->where('is_delete', 0)
                    ->first();
                $targetClassId = $firstClass ? $firstClass->id : null;
            }

            $nextJson = $data;
            $nextJson['classname'] = $newClassName;
            $nextJson['section_name'] = $promotedSection;
            $nextJson['class_id'] = $targetClassId;
            $nextJson['session_name'] = $nextSession;
            $nextJson['batch'] = $nextSession;
            $nextJson['next_year'] = 0;
            $nextJson['next_year_section'] = null;
            $nextJson['next_year_stream'] = null;
            $nextJson['next_year_subject_combination'] = null;
            if (!empty($targetStreamId)) {
                $nextJson['stream_id'] = $targetStreamId;
            }
            if (!empty($targetCombinationId)) {
                $nextJson['combination_id'] = $targetCombinationId;
            }

            // Transfer student to target session DB
            $targetStudent = NextYearStudent::updateOrCreate(
                ['scholar_no' => $student->scholar_no],
                [
                    'application_for' => $student->application_for,
                    'form_number'     => $formNumber,
                    'scholar_no'      => $student->scholar_no,
                    'date_of_birth'   => $student->date_of_birth,
                    'class_name'      => $newClassName,
                    'section_name'    => $promotedSection,
                    'class_id'        => $targetClassId,
                    'student_name'    => $student->student_name,
                    'session_name'    => $nextSession,
                    'json_str'        => json_encode($nextJson),
                    'staff_name'      => $student->staff_name,
                    'phone_number'    => $student->phone_number,
                    'mobile_number'   => $student->mobile_number,
                    'inq_mode'        => $student->inq_mode,
                    'driver'          => $student->driver,
                    'status'          => $student->status,
                    'type'            => $student->type,
                    'password'        => $student->password,
                    'next_year'       => 0,
                    'next_year_section' => null,
                    'next_year_stream'  => null,
                    'next_year_subject_combination' => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );

            // If senior class combination exists, assign in next session DB
            if ($isPromotedSenior && !empty($targetCombinationId) && Schema::connection('next_session_db')->hasTable('subject_assign_student')) {
                DB::connection('next_session_db')->table('subject_assign_student')->updateOrInsert(
                    ['students_details' => $targetStudent->id],
                    [
                        'class_name' => $newClassName,
                        'section_name' => $promotedSection,
                        'assign_this_combtoall' => $targetCombinationId,
                        'is_delete' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Task 7: Cleanup in current session DB - only runs after successful transfer
            $data['section_name'] = $student->section_name ?: ($data['section_name'] ?? 'Aryabhatta');
            $student->json_str = json_encode($data);
            if ($isNextYear) {
                $student->next_year = 0;
                $student->next_year_section = null;
                $student->next_year_stream = null;
                $student->next_year_subject_combination = null;
            }
            if (Schema::connection('dynamic')->hasColumn('student_registration', 'transfer_status')) {
                $student->transfer_status = 'transferred';
                $student->transferred_at = now();
            }
            $student->save();
        }

        // Automatic Round Robin distribution: merge Section D new admissions into promoted strength
        $promotedTargetClasses = array_values(array_unique($promotedTargetClasses));
        if (!empty($promotedTargetClasses)) {
            $this->allocateSectionsForSession($nextSession, $promotedTargetClasses);
        }

        return redirect()->back()->with('success', 'Students transferred successfully. Section distribution applied.');
    }

    return redirect()->back()->with('error', 'No students selected!');
}

    /**
     * Web endpoint to trigger automatic section allocation and balancing for the next academic session.
     */
    public function allocateSections(Request $request)
    {
        $this->applySessionDatabaseBindings($request);

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

        $targetSession = null;
        if ($this->nextSessionStudentTableExists()) {
            $targetSession = $nextSession;
        } elseif (Schema::connection('dynamic')->hasTable('student_registration')) {
            $targetSession = $currentSession;
        }

        if (!$targetSession) {
            return redirect()->back()->with('error', 'Target session database is not available.');
        }

        $selectedClass = $request->input('class_name') ? [$request->input('class_name')] : null;

        $results = $this->allocateSectionsForSession($targetSession, $selectedClass);

        return redirect()->back()->with('success', 'Sections automatically balanced for session ' . $targetSession . ' using Round Robin distribution.');
    }

    /**
     * Automatically assign sections to students in target academic session.
     *
     * Rules:
     * 1. Classes Nursery to 10:
     *    - Regular sections: ['Aryabhatta', 'Kautilya', 'Ramanujan']
     *    - Promoted students maintain their regular sections and form base strength.
     *    - All students in temporary Section D (new admissions) are merged and allocated
     *      using balanced Round Robin (lowest count first, tie-break Aryabhatta -> Kautilya -> Ramanujan).
     * 2. Classes 11 and 12:
     *    - Science -> 'Kautilya'
     *    - Humanities / Commerce -> 'Ramanujan'
     *
     * @param string $targetSession Target database session (e.g. '2026_2027')
     * @param array|null $targetClasses Optional classes filter
     * @return array Allocation report
     */
    public function allocateSectionsForSession(string $targetSession, ?array $targetClasses = null): array
    {
        $currentNextDb = Config::get('database.connections.next_session_db.database');
        if ($currentNextDb !== $targetSession) {
            Config::set('database.connections.next_session_db.database', $targetSession);
            DB::purge('next_session_db');
            DB::reconnect('next_session_db');
        }

        if (!Schema::connection('next_session_db')->hasTable('student_registration')) {
            return ['error' => "Table student_registration not found in session {$targetSession}"];
        }

        if (Schema::connection('next_session_db')->hasColumn('student_registration', 'next_year')) {
            DB::connection('next_session_db')->table('student_registration')
                ->where('next_year', 1)
                ->update([
                    'next_year' => 0,
                    'next_year_section' => null,
                    'next_year_stream' => null,
                    'next_year_subject_combination' => null,
                ]);
        }

        $regularSections = ['Aryabhatta', 'Kautilya', 'Ramanujan'];
        $nurseryTo10 = [
            'Nursery', 'KG1', 'KG2',
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10',
            '1', '2', '3', '4', '5', '6', '7', '8', '9'
        ];

        if (empty($targetClasses)) {
            $targetClasses = DB::connection('next_session_db')->table('student_registration')
                ->where(function ($q) {
                    $q->where('is_archived', 0)
                        ->orWhereNull('is_archived');
                })
                ->where(function ($q) {
                    $q->whereNull('status')->orWhereNotIn('status', ['t', 'archived']);
                })
                ->distinct()
                ->pluck('class_name')
                ->toArray();
        }

        $allocationSummary = [];

        foreach ($targetClasses as $className) {
            $cleanClass = trim((string)$className);
            $isSenior = in_array($cleanClass, ['11', '12'], true);
            $isJunior = in_array(strtolower($cleanClass), array_map('strtolower', $nurseryTo10), true);

            if ($isSenior) {
                // Task 3: Stream-based mapping for Classes 11 and 12
                $students = DB::connection('next_session_db')->table('student_registration')
                    ->where('class_name', $cleanClass)
                    ->where(function ($q) {
                        $q->where('is_archived', 0)
                            ->orWhereNull('is_archived');
                    })
                    ->where(function ($q) {
                        $q->whereNull('status')->orWhereNotIn('status', ['t', 'archived']);
                    })
                    ->get();

                $allocatedSeniorCount = 0;
                foreach ($students as $stu) {
                    $json = json_decode($stu->json_str, true) ?: [];
                    $streamId = $json['stream_id'] ?? null;
                    $streamName = '';
                    if (!empty($streamId)) {
                        $streamRow = DB::connection('next_session_db')->table('streams')->where('id', $streamId)->first();
                        if (!$streamRow && Schema::connection('dynamic')->hasTable('streams')) {
                            $streamRow = DB::connection('dynamic')->table('streams')->where('id', $streamId)->first();
                        }
                        $streamName = $streamRow ? strtoupper(trim($streamRow->streams)) : '';
                    }

                    $targetSection = 'Kautilya';
                    if ($streamName === 'SCIENCE' || (int)$streamId === 3) {
                        $targetSection = 'Kautilya';
                    } elseif (in_array($streamName, ['COMMERCE', 'HUMANITIES'], true) || in_array((int)$streamId, [1, 2], true)) {
                        $targetSection = 'Ramanujan';
                    } elseif (!empty($stu->section_name) && in_array($stu->section_name, ['Kautilya', 'Ramanujan'], true)) {
                        $targetSection = $stu->section_name;
                    }

                    $matchedClass = DB::connection('next_session_db')->table('classes')
                        ->where('class_name', $cleanClass)
                        ->where('section_name', $targetSection)
                        ->where('is_delete', 0)
                        ->first();
                    $classId = $matchedClass ? $matchedClass->id : $stu->class_id;

                    if ($stu->section_name !== $targetSection || $stu->class_id != $classId) {
                        $json['section_name'] = $targetSection;
                        $json['class_id'] = $classId;
                        DB::connection('next_session_db')->table('student_registration')
                            ->where('id', $stu->id)
                            ->update([
                                'section_name' => $targetSection,
                                'class_id'     => $classId,
                                'json_str'     => json_encode($json),
                            ]);
                        $allocatedSeniorCount++;
                    }
                }

                $allocationSummary[$cleanClass] = [
                    'type' => 'senior_stream_mapping',
                    'updated_students' => $allocatedSeniorCount,
                ];
            } elseif ($isJunior) {
                // Tasks 2 & 4: Round Robin distribution for Nursery to Class 10
                $students = DB::connection('next_session_db')->table('student_registration')
                    ->where('class_name', $cleanClass)
                    ->where(function ($q) {
                        $q->where('is_archived', 0)
                            ->orWhereNull('is_archived');
                    })
                    ->where(function ($q) {
                        $q->whereNull('status')->orWhereNotIn('status', ['t', 'archived']);
                    })
                    ->orderBy('scholar_no', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                $sectionCounts = [
                    'Aryabhatta' => 0,
                    'Kautilya'   => 0,
                    'Ramanujan'  => 0,
                ];

                $pendingStudents = [];

                foreach ($students as $stu) {
                    $sec = trim((string)$stu->section_name);
                    $matchedRegular = null;
                    foreach ($regularSections as $reg) {
                        if (strcasecmp($sec, $reg) === 0) {
                            $matchedRegular = $reg;
                            break;
                        }
                    }

                    if ($matchedRegular !== null) {
                        // Existing student in regular section (Aryabhatta, Kautilya, Ramanujan):
                        // Do not change their section, keep unchanged and count towards section strength
                        $sectionCounts[$matchedRegular]++;
                    } else {
                        // New admissions / Section D / unassigned students: redistribute via Round Robin
                        $pendingStudents[] = $stu;
                    }
                }

                $assignedCount = 0;
                foreach ($pendingStudents as $stu) {
                    $bestSection = 'Aryabhatta';
                    $minCount = $sectionCounts['Aryabhatta'];

                    foreach ($regularSections as $candidate) {
                        if ($sectionCounts[$candidate] < $minCount) {
                            $minCount = $sectionCounts[$candidate];
                            $bestSection = $candidate;
                        }
                    }

                    $sectionCounts[$bestSection]++;

                    $matchedClass = DB::connection('next_session_db')->table('classes')
                        ->where('class_name', $cleanClass)
                        ->where('section_name', $bestSection)
                        ->where('is_delete', 0)
                        ->first();
                    $classId = $matchedClass ? $matchedClass->id : $stu->class_id;

                    $json = json_decode($stu->json_str, true) ?: [];
                    $json['section_name'] = $bestSection;
                    $json['class_id'] = $classId;

                    DB::connection('next_session_db')->table('student_registration')
                        ->where('id', $stu->id)
                        ->update([
                            'section_name' => $bestSection,
                            'class_id'     => $classId,
                            'json_str'     => json_encode($json),
                        ]);

                    $assignedCount++;
                }

                $allocationSummary[$cleanClass] = [
                    'type' => 'round_robin_balanced',
                    'allocated_from_section_d' => $assignedCount,
                    'final_section_counts' => $sectionCounts,
                ];
            }
        }

        return $allocationSummary;
    }

	
	private function getNextClassName($currentClass)
	{
		// Mapping for text-based classes
		$classPromotionMap = [
			'Nursery' => 'KG1',
			'KG1' => 'KG2',
			'Kg1' => 'KG2',
			'KG2' => '01', // Switch to numeric classes
			'Kg2' => '01',
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
