<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\StudentRollNo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SeatingArrangementController extends Controller
{
    public function __construct(Request $request)
    {
        $dbName = $request->session
            ?? $request->session_name
            ?? ($_COOKIE['selectedYear'] ?? session('db_names') ?? '2025_2026');

        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        $dynamicConfig['database'] = $dbName;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');
    }

    public function index(Request $request)
    {
        $className = $request->query('class_name');
        $sectionName = $request->query('section_name');
        $sessionName = $this->resolveActiveSessionName();
        $selectedExamId = $request->query('exam_id');

        $classList = DB::connection('dynamic')->table('class_name')
            ->whereNotNull('class_name')
            ->select('class_name')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name')
            ->values();

        $sectionList = collect();
        if (!empty($className)) {
            $sectionList = DB::connection('dynamic')->table('classes')
                ->where('class_name', $className)
                ->whereNotNull('section_name')
                ->select('section_name')
                ->distinct()
                ->orderBy('section_name')
                ->pluck('section_name');
        }

        $students = collect();
        $rollMap = collect();
        $roomMap = collect();
        $hasGeneratedRolls = false;
        $exams = collect();
        if (!empty($className)) {
            $examsQuery = DB::connection('dynamic')->table('academic_exam as ae')
                ->join('classes as c', 'c.id', '=', 'ae.class_id')
                ->where('c.class_name', $className)
                ->when(!empty($sessionName), fn ($query) => $query->where('ae.session_year', $sessionName))
                ->select('ae.id', 'ae.exam_name', 'ae.exam_type')
                ->orderBy('ae.exam_name')
                ->orderBy('ae.exam_type');

            if (Schema::connection('dynamic')->hasColumn('academic_exam', 'deleted_at')) {
                $examsQuery->whereNull('ae.deleted_at');
            }

            $exams = $examsQuery->get();
        }

        if (!empty($selectedExamId) && !$exams->contains('id', (int) $selectedExamId)) {
            $selectedExamId = null;
        }

        if (!empty($className) && !empty($sectionName) && !empty($sessionName)) {
            $students = $this->getClassSectionStudents($className, $sectionName, $sessionName);

            if (!empty($selectedExamId)) {
                $rollMap = StudentRollNo::query()
                    ->where('class_id', $className)
                    ->where('section_id', $sectionName)
                    ->where('session_id', $sessionName)
                    ->where('exam_id', $selectedExamId)
                    ->pluck('roll_no', 'student_id');

                if ($this->hasRoomNoColumn()) {
                    $roomMap = StudentRollNo::query()
                        ->where('class_id', $className)
                        ->where('section_id', $sectionName)
                        ->where('session_id', $sessionName)
                        ->where('exam_id', $selectedExamId)
                        ->pluck('room_no', 'student_id');
                }

                $hasGeneratedRolls = $rollMap->isNotEmpty();
            }

            if ($hasGeneratedRolls) {
                $students = $students->sortBy(function ($student) use ($rollMap) {
                    $roll = $rollMap->get($student->id);
                    if (empty($roll)) {
                        return 'ZZZZZZ';
                    }

                    return $roll;
                })->values();
            }
        }

        return view('backend.AcademicsModules.roll_no_tools', compact(
            'classList',
            'sectionList',
            'students',
            'rollMap',
            'className',
            'sectionName',
            'sessionName',
            'hasGeneratedRolls',
            'exams',
            'selectedExamId',
            'roomMap'
        ));
    }

    // Generate roll numbers for a class/section/session (bulk for one class/section)
    public function generateRollNumbers(Request $request, $className, $sectionName, $sessionName)
    {
        $sessionName = $this->resolveActiveSessionName();
        $examId = $request->query('exam_id');

        if (empty($examId)) {
            return $this->redirectToTools($className, $sectionName, $sessionName, $examId, 'error', 'Please select an exam first.');
        }

        $students = $this->getClassSectionStudents($className, $sectionName, $sessionName);

        if ($students->isEmpty()) {
            return $this->redirectToTools($className, $sectionName, $sessionName, $examId, 'error', 'No students found for selected class/section/session.');
        }

        $this->assignRollNumbers($students, $className, $sectionName, $sessionName, (int) $examId);

        return $this->redirectToTools($className, $sectionName, $sessionName, $examId, 'success', 'Roll numbers generated successfully.');
    }

    // Generate roll number for a single student
    public function generateRollNumberForStudent(Request $request, $studentId, $sessionName)
    {
        $sessionName = $this->resolveActiveSessionName();
        $examId = $request->query('exam_id');

        if (empty($examId)) {
            return redirect()->route('academic.roll-no-tools')->with('error', 'Please select an exam first.');
        }

        $student = DB::connection('dynamic')->table('student_registration as sr')
            ->join('classes as c', 'c.id', '=', 'sr.class_id')
            ->where('sr.id', $studentId)
            ->where(function ($query) {
                $query->where('sr.is_archived', 0)->orWhereNull('sr.is_archived');
            })
            ->select('sr.*', 'c.class_name as mapped_class_name', 'c.section_name as mapped_section_name')
            ->first();

        if (!$student) {
            return redirect()->route('academic.roll-no-tools')->with('error', 'Student not found!');
        }

        $className = $student->mapped_class_name ?? $student->class_name;
        $sectionName = $student->mapped_section_name ?? $student->section_name;

        $students = $this->getClassSectionStudents($className, $sectionName, $sessionName);
        if ($students->isEmpty()) {
            return $this->redirectToTools($className, $sectionName, $sessionName, $examId, 'error', 'No students found in the same class/section/session.');
        }

        $serial = 0;
        foreach ($students as $idx => $s) {
            if ((string) $s->id === (string) $studentId) {
                $serial = $idx + 1;
                break;
            }
        }

        if ($serial <= 0) {
            return $this->redirectToTools($className, $sectionName, $sessionName, $examId, 'error', 'Student not found in selected class/section list.');
        }

        $rollNo = $this->resolveClassCode($className) . $this->resolveSectionDigit($sectionName) . sprintf('%02d', $serial);
        StudentRollNo::updateOrCreate(
            [
                'student_id' => $studentId,
                'class_id' => $className,
                'section_id' => $sectionName,
                'session_id' => $sessionName,
                'exam_id' => (int) $examId,
            ],
            ['roll_no' => $rollNo]
        );

        return $this->redirectToTools($className, $sectionName, $sessionName, $examId, 'success', 'Roll number generated/updated for student.');
    }

    public function generateRollNumbersForSelected(Request $request)
    {
        $sessionName = $this->resolveActiveSessionName();
        $className = $request->input('class_name');
        $sectionName = $request->input('section_name');
        $examId = $request->input('exam_id');
        $studentIds = array_values(array_filter((array) $request->input('student_ids', [])));

        if (empty($examId)) {
            return $this->redirectToTools((string) $className, (string) $sectionName, $sessionName, $examId, 'error', 'Please select an exam first.');
        }

        if (empty($className) || empty($sectionName) || empty($studentIds)) {
            return $this->redirectToTools((string) $className, (string) $sectionName, $sessionName, $examId, 'error', 'Please select at least one student.');
        }

        $students = $this->getClassSectionStudents($className, $sectionName, $sessionName, $studentIds);

        if ($students->isEmpty()) {
            return $this->redirectToTools($className, $sectionName, $sessionName, $examId, 'error', 'No selected students found.');
        }

        // Keep sequence correct globally for class/section by regenerating on full class set.
        $allClassStudents = $this->getClassSectionStudents($className, $sectionName, $sessionName);
        $this->assignRollNumbers($allClassStudents, $className, $sectionName, $sessionName, (int) $examId);

        return $this->redirectToTools($className, $sectionName, $sessionName, $examId, 'success', 'Roll numbers generated for selected students.');
    }

    public function saveRoomNumbers(Request $request)
    {
        $sessionName = $this->resolveActiveSessionName();
        $className = $request->input('class_name');
        $sectionName = $request->input('section_name');
        $examId = $request->input('exam_id');

        if (empty($examId)) {
            return $this->redirectToTools((string) $className, (string) $sectionName, $sessionName, $examId, 'error', 'Please select an exam first.');
        }

        if (!$this->hasRoomNoColumn()) {
            return $this->redirectToTools((string) $className, (string) $sectionName, $sessionName, $examId, 'error', 'Room number column is missing. Please run migrations first.');
        }

        $validated = $request->validate([
            'class_name' => 'required|string',
            'section_name' => 'required|string',
            'room_numbers' => 'nullable|array',
            'room_numbers.*' => 'nullable|string|max:50',
        ]);

        foreach (($validated['room_numbers'] ?? []) as $studentId => $roomNo) {
            $roomNo = trim((string) $roomNo);

            $record = StudentRollNo::firstOrNew([
                'student_id' => (int) $studentId,
                'class_id' => $validated['class_name'],
                'section_id' => $validated['section_name'],
                'session_id' => $sessionName,
                'exam_id' => (int) $examId,
            ]);

            if (!$record->exists && empty($record->roll_no)) {
                $record->roll_no = '';
            }

            $record->room_no = $roomNo !== '' ? $roomNo : null;
            $record->save();
        }

        return $this->redirectToTools($validated['class_name'], $validated['section_name'], $sessionName, $examId, 'success', 'Room numbers saved successfully.');
    }

    // Bulk generate roll numbers for all classes/sections in a session
    public function generateRollNumbersBulk($sessionName)
    {
        $sessionName = $this->resolveActiveSessionName();
        return redirect()->route('academic.roll-no-tools', ['session_name' => $sessionName])
            ->with('error', 'Please select class, section, and exam before generating roll numbers.');
    }

    // Print admit cards (two per A4 page)
    public function printAdmitCards(Request $request, $className, $sectionName, $sessionName)
    {
        $sessionName = $this->resolveActiveSessionName();
        $examId = $request->query('exam_id');
        $exam = null;

        if (empty($examId)) {
            return $this->redirectToTools($className, $sectionName, $sessionName, $examId, 'error', 'Please select an exam first.');
        }

        if (!empty($examId)) {
            $exam = DB::connection('dynamic')->table('academic_exam')
                ->where('id', $examId)
                ->select('id', 'exam_name', 'exam_title', 'exam_type', 'session_year')
                ->first();
        }

        $students = $this->getClassSectionStudents($className, $sectionName, $sessionName);

        $this->applyRollAndRoomNumbers($students, $className, $sectionName, $sessionName, (int) $examId);

        if ($students->contains(fn ($s) => !empty($s->roll_no))) {
            $students = $students->sortBy(function ($s) {
                return !empty($s->roll_no) ? $s->roll_no : 'ZZZZZZ';
            })->values();
        }

        return view('backend.AcademicsModules.admit_card_print', compact('students', 'className', 'sectionName', 'sessionName', 'exam'));
    }

    public function printAdmitCardForStudent(Request $request, $studentId, $sessionName)
    {
        $sessionName = $this->resolveActiveSessionName();
        $examId = $request->query('exam_id');
        $exam = null;

        if (empty($examId)) {
            return redirect()->route('academic.roll-no-tools')->with('error', 'Please select an exam first.');
        }

        if (!empty($examId)) {
            $exam = DB::connection('dynamic')->table('academic_exam')
                ->where('id', $examId)
                ->select('id', 'exam_name', 'exam_title', 'exam_type', 'session_year')
                ->first();
        }

        $student = DB::connection('dynamic')->table('student_registration as sr')
            ->join('classes as c', 'c.id', '=', 'sr.class_id')
            ->where('sr.id', $studentId)
            ->where(function ($query) {
                $query->where('sr.is_archived', 0)->orWhereNull('sr.is_archived');
            })
            ->select('sr.*', 'c.class_name as class_name', 'c.section_name as section_name')
            ->first();

        if (!$student) {
            return redirect()->route('academic.roll-no-tools')->with('error', 'Student not found!');
        }

        $students = collect([$student]);
        $this->applyRollAndRoomNumbers($students, $student->class_name, $student->section_name, $sessionName, (int) $examId);

        $data = [
            'students' => $students,
            'className' => $student->class_name,
            'sectionName' => $student->section_name,
            'sessionName' => $sessionName,
            'exam' => $exam,
        ];

        return view('backend.AcademicsModules.admit_card_print', $data);
    }

    public function printAdmitCardsForSelected(Request $request)
    {
        $sessionName = $this->resolveActiveSessionName();
        $className = $request->input('class_name');
        $sectionName = $request->input('section_name');
        $examId = $request->input('exam_id');
        $studentIds = array_values(array_filter((array) $request->input('student_ids', [])));

        if (empty($examId)) {
            return $this->redirectToTools((string) $className, (string) $sectionName, $sessionName, $examId, 'error', 'Please select an exam first.');
        }

        if (empty($className) || empty($sectionName) || empty($studentIds)) {
            return $this->redirectToTools((string) $className, (string) $sectionName, $sessionName, $examId, 'error', 'Please select at least one student.');
        }

        $exam = null;
        if (!empty($examId)) {
            $exam = DB::connection('dynamic')->table('academic_exam')
                ->where('id', $examId)
                ->select('id', 'exam_name', 'exam_title', 'exam_type', 'session_year')
                ->first();
        }

        $students = $this->getClassSectionStudents($className, $sectionName, $sessionName, $studentIds);

        $this->applyRollAndRoomNumbers($students, $className, $sectionName, $sessionName, (int) $examId);

        if ($students->contains(fn ($s) => !empty($s->roll_no))) {
            $students = $students->sortBy(function ($s) {
                return !empty($s->roll_no) ? $s->roll_no : 'ZZZZZZ';
            })->values();
        }

        return view('backend.AcademicsModules.admit_card_print', compact('students', 'className', 'sectionName', 'sessionName', 'exam'));
    }

    public function downloadRollNumbers(Request $request, $className, $sectionName, $sessionName): StreamedResponse
    {
        $sessionName = $this->resolveActiveSessionName();
        $examId = $request->query('exam_id');

        $students = $this->getClassSectionStudents($className, $sectionName, $sessionName);

        $rollMap = StudentRollNo::query()
            ->where('class_id', $className)
            ->where('section_id', $sectionName)
            ->where('session_id', $sessionName)
            ->where('exam_id', $examId)
            ->pluck('roll_no', 'student_id');

        // sort by roll number (sequence) if present
        if ($rollMap->isNotEmpty()) {
            $students = $students->sortBy(function ($student) use ($rollMap) {
                return $rollMap->get($student->id, 'ZZZZZZ');
            })->values();
        }

        $filename = 'roll_numbers_' . $className . '_' . $sectionName . '_' . str_replace(['/', '\\', ' '], '-', $sessionName) . '.csv';

        return response()->streamDownload(function () use ($students, $rollMap, $className, $sectionName) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Sr. No.', 'Student Name', 'Scholar No.', 'Roll No.', 'Class', 'Section']);

            $srNo = 1;
            foreach ($students as $student) {
                fputcsv($handle, [
                    $srNo++,
                    $student->student_name,
                    $student->scholar_no ?? '',
                    $rollMap->get($student->id, ''),
                    $className,
                    $sectionName,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function printRollNumbersPdf(Request $request, $className, $sectionName, $sessionName)
    {
        $sessionName = $this->resolveActiveSessionName();
        $examId = $request->query('exam_id');
        $exam = null;

        if (!empty($examId)) {
            $exam = DB::connection('dynamic')->table('academic_exam')
                ->where('id', $examId)
                ->select('id', 'exam_name', 'exam_type', 'session_year')
                ->first();
        }

        $students = $this->getClassSectionStudents($className, $sectionName, $sessionName);

        $rollMap = StudentRollNo::query()
            ->where('class_id', $className)
            ->where('section_id', $sectionName)
            ->where('session_id', $sessionName)
            ->where('exam_id', $examId)
            ->pluck('roll_no', 'student_id');

        // sort by roll number (sequence) if present
        if ($rollMap->isNotEmpty()) {
            $students = $students->sortBy(function ($student) use ($rollMap) {
                return $rollMap->get($student->id, 'ZZZZZZ');
            })->values();
        }

        $pdf = Pdf::loadView('backend.AcademicsModules.roll_no_pdf', compact(
            'students',
            'rollMap',
            'className',
            'sectionName',
            'sessionName',
            'exam'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('roll_numbers_' . $className . '_' . $sectionName . '.pdf');
    }

    private function assignRollNumbers(Collection $students, string $className, string $sectionName, string $sessionName, int $examId): void
    {
        if ($this->isSeniorClass($className)) {
            $students = $this->orderSeniorStudentsByCombination($students, $className, $sectionName);
        }

        $classCode = $this->resolveClassCode($className);
        $sectionCode = $this->resolveSectionDigit($sectionName);
        $serial = 1;

        foreach ($students as $student) {
            $rollNo = $classCode . $sectionCode . sprintf('%02d', $serial);

            StudentRollNo::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'class_id' => $className,
                    'section_id' => $sectionName,
                    'session_id' => $sessionName,
                    'exam_id' => $examId,
                ],
                ['roll_no' => $rollNo]
            );

            $serial++;
        }
    }

    private function applyRollAndRoomNumbers(Collection $students, string $className, string $sectionName, string $sessionName, int $examId): void
    {
        $query = StudentRollNo::query()
            ->where('class_id', $className)
            ->where('section_id', $sectionName)
            ->where('session_id', $sessionName)
            ->where('exam_id', $examId);

        $records = $query->get()->keyBy('student_id');

        foreach ($students as $student) {
            $record = $records->get($student->id);
            $student->roll_no = $record->roll_no ?? null;
            $student->room_no = $this->hasRoomNoColumn() ? ($record->room_no ?? null) : null;
        }
    }

    private function hasRoomNoColumn(): bool
    {
        return Schema::connection('dynamic')->hasColumn('academic_student_roll_no', 'room_no');
    }

    private function redirectToTools(string $className, string $sectionName, string $sessionName, ?string $examId, string $flashType, string $flashMessage)
    {
        $params = [
            'class_name' => $className,
            'section_name' => $sectionName,
        ];

        if (!empty($examId)) {
            $params['exam_id'] = $examId;
        }

        return redirect()->route('academic.roll-no-tools', $params)
            ->with($flashType, $flashMessage);
    }

    private function resolveActiveSessionName(): ?string
    {
        $activeDb = $_COOKIE['selectedYear'] ?? session('db_names');
        if (!empty($activeDb)) {
            return $activeDb;
        }

        return DB::connection('dynamic')->table('student_registration')
            ->where(function ($query) {
                $query->where('is_archived', 0)->orWhereNull('is_archived');
            })
            ->whereNotNull('session_name')
            ->orderByDesc('session_name')
            ->value('session_name');
    }

    private function resolveClassCode(string $className): string
    {
        $digits = preg_replace('/[^0-9]/', '', $className);
        if ($digits === '') {
            return '0';
        }

        $value = (int) $digits;
        if ($value < 1) {
            return '0';
        }

        if ($value > 12) {
            $value = 12;
        }

        return (string) $value;
    }

    private function resolveSectionDigit(string $sectionName): string
    {
        $normalized = strtoupper(trim($sectionName));
        $map = [
            'ARYABHATTA' => '1',
            'KAUTILYA' => '2',
            'RAMANUJAN' => '3',
        ];

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        return '9';
    }

    private function getClassSectionStudents(string $className, string $sectionName, string $sessionName, array $studentIds = []): Collection
    {
        $query = DB::connection('dynamic')
            ->table('student_registration as sr')
            ->leftJoin('classes as c', 'c.id', '=', 'sr.class_id')
            ->where(function ($query) {
                $query->where('sr.is_archived', 0)->orWhereNull('sr.is_archived');
            })
            ->where(function ($q) use ($className, $sectionName) {
                // Match class/section either via joined classes table or direct columns/json fields
                $q->where(function ($q2) use ($className, $sectionName) {
                    $q2->where('c.class_name', $className)
                       ->where('c.section_name', $sectionName);
                })
                ->orWhere(function ($q2) use ($className, $sectionName) {
                    $q2->where('sr.class_name', $className)
                       ->where(function ($q3) use ($sectionName) {
                           $q3->where('sr.section_name', $sectionName)
                              ->orWhereJsonContains('sr.json_str->section_name', $sectionName);
                       });
                });
            })
            ->select('sr.*')
            ->orderBy('sr.student_name');

        if (!empty($studentIds)) {
            $query->whereIn('sr.id', $studentIds);
        }

        $students = $query->get();

        if ($this->isSeniorClass($className)) {
            return $this->orderSeniorStudentsByCombination($students, $className, $sectionName);
        }

        return $students;
    }

    private function isSeniorClass(?string $className): bool
    {
        if (empty($className)) {
            return false;
        }

        if (preg_match('/(out|test)/i', $className)) {
            return false;
        }

        $seniorNames = ['11', '12', 'CLASS 11', 'CLASS 12', 'XI', 'XII', 'CLASS XI', 'CLASS XII', '011', '012'];
        $upper = strtoupper(trim($className));
        if (in_array($upper, $seniorNames, true)) {
            return true;
        }

        $digits = preg_replace('/[^0-9]/', '', $className);
        return $digits === '11' || $digits === '12';
    }

    private function orderSeniorStudentsByCombination(Collection $students, string $className, string $sectionName): Collection
    {
        if ($students->isEmpty()) {
            return $students;
        }

        $combMap = $this->getStudentCombinationMap();

        foreach ($students as $student) {
            $combInfo = $combMap[$student->id] ?? null;
            $combName = $combInfo['combination_name'] ?? null;
            $combId = $combInfo['combination_id'] ?? null;
            $streamName = $combInfo['stream_name'] ?? null;

            if (empty($combName) && !empty($student->subject_combination)) {
                $combName = $student->subject_combination;
            }

            if (empty($combName) && !empty($student->json_str)) {
                $json = is_array($student->json_str) ? $student->json_str : json_decode((string) $student->json_str, true);
                if (is_array($json)) {
                    if (!empty($json['subject_combination'])) {
                        $combName = $json['subject_combination'];
                    } elseif (!empty($json['combination'])) {
                        $combName = $json['combination'];
                    }
                }
            }

            $student->combination_id = $combId;
            $student->combination_name = !empty($combName) ? trim((string) $combName) : '';
            $student->stream_name = !empty($streamName) ? trim((string) $streamName) : '';
        }

        return $students->sort(function ($a, $b) {
            // 1. Group by category: PCM first, PCB second, other combinations third, unassigned last
            $pA = $this->getCombinationCategoryPriority($a->combination_name);
            $pB = $this->getCombinationCategoryPriority($b->combination_name);
            if ($pA !== $pB) {
                return $pA <=> $pB;
            }

            // 2. Alphabetical order based on subject combination
            $normA = $this->normalizeCombinationForSort($a->combination_name);
            $normB = $this->normalizeCombinationForSort($b->combination_name);
            $normCmp = strnatcasecmp($normA, $normB);
            if ($normCmp !== 0) {
                return $normCmp;
            }

            // Raw combination name comparison to ensure identical combinations group strictly together
            $rawCmp = strnatcasecmp(trim((string) $a->combination_name), trim((string) $b->combination_name));
            if ($rawCmp !== 0) {
                return $rawCmp;
            }

            // 3. Within each combination group, sort students alphabetically by name
            $nameA = trim((string) ($a->student_name ?? ''));
            $nameB = trim((string) ($b->student_name ?? ''));
            $nameCmp = strnatcasecmp($nameA, $nameB);
            if ($nameCmp !== 0) {
                return $nameCmp;
            }

            // Tie-breaker on scholar_no or ID
            $schA = trim((string) ($a->scholar_no ?? ''));
            $schB = trim((string) ($b->scholar_no ?? ''));
            if ($schA !== '' && $schB !== '' && $schA !== $schB) {
                return strnatcasecmp($schA, $schB);
            }

            return ((int) ($a->id ?? 0)) <=> ((int) ($b->id ?? 0));
        })->values();
    }

    private function getStudentCombinationMap(): array
    {
        if (!Schema::connection('dynamic')->hasTable('subject_assign_student')) {
            return [];
        }

        $hasIsDelete = Schema::connection('dynamic')->hasColumn('subject_assign_student', 'is_delete');

        $query = DB::connection('dynamic')
            ->table('subject_assign_student as sas')
            ->leftJoin('subject_combinations as sc', 'sc.id', '=', 'sas.assign_this_combtoall')
            ->leftJoin('streams as str', 'str.id', '=', 'sc.streams')
            ->select(
                'sas.id',
                'sas.students_details',
                'sas.class_name',
                'sas.section_name',
                'sc.id as combination_id',
                'sc.combination_name',
                'str.id as stream_id',
                'str.streams as stream_name'
            );

        if ($hasIsDelete) {
            $query->addSelect('sas.is_delete');
        }

        $assignments = $query->orderBy('sas.id', 'asc')->get();

        $activeMap = [];
        $fallbackMap = [];
        foreach ($assignments as $a) {
            $raw = $a->students_details;
            $ids = [];
            if (is_numeric($raw)) {
                $ids[] = (int) $raw;
            } elseif (is_string($raw) && str_starts_with(trim($raw), '[')) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $d) {
                        if (is_numeric($d)) $ids[] = (int) $d;
                    }
                }
            } elseif (is_string($raw) && str_contains($raw, ',')) {
                foreach (explode(',', $raw) as $d) {
                    if (is_numeric(trim($d))) $ids[] = (int) trim($d);
                }
            } elseif (!empty($raw) && is_numeric(trim($raw))) {
                $ids[] = (int) trim($raw);
            }

            $combInfo = [
                'combination_id' => $a->combination_id,
                'combination_name' => $a->combination_name,
                'stream_id' => $a->stream_id,
                'stream_name' => $a->stream_name,
            ];

            $isDeleted = $hasIsDelete && !empty($a->is_delete) && (int) $a->is_delete === 1;

            foreach ($ids as $sId) {
                if (!$isDeleted) {
                    $activeMap[$sId] = $combInfo;
                } else {
                    $fallbackMap[$sId] = $combInfo;
                }
            }
        }

        // Active assignments take priority over fallback assignments
        return $activeMap + $fallbackMap;
    }

    private function getCombinationCategoryPriority(?string $combName): int
    {
        if (empty($combName) || trim($combName) === '' || strcasecmp(trim($combName), 'General Combination') === 0) {
            return 99; // Unassigned / fallback at the end
        }

        $c = strtoupper(trim($combName));

        // 1. PCM combinations (e.g. PCM, PCM 11+ PE, PCM+CS, but not PCMB)
        if (preg_match('/\bPCM\b/i', $c) && !preg_match('/\bPCMB\b/i', $c)) {
            return 1;
        }

        // 2. PCB / PCMB combinations
        if (preg_match('/\bPCB\b/i', $c) || preg_match('/\bPCMB\b/i', $c)) {
            return 2;
        }

        // 3. Other combinations (Commerce, Humanities, etc.)
        return 3;
    }

    private function normalizeCombinationForSort(?string $combName): string
    {
        if (empty($combName)) {
            return '';
        }

        // Strip class numbers like 11, 12, XI, XII to compare subjects cleanly (e.g. "PCM 11+ PE" vs "PCM+CS")
        $s = preg_replace('/\b(11|12|XI|XII)\b/i', '', $combName);
        $s = preg_replace('/[\s+]+/', ' ', trim($s));

        return strtoupper($s);
    }
}
