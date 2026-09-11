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

        $filename = 'roll_numbers_' . $className . '_' . $sectionName . '_' . str_replace(['/', '\\', ' '], '-', $sessionName) . '.csv';

        return response()->streamDownload(function () use ($students, $rollMap, $className, $sectionName, $sessionName) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Class', 'Section', 'Session', 'Student ID', 'Student Name', 'Roll No']);

            foreach ($students as $student) {
                fputcsv($handle, [
                    $className,
                    $sectionName,
                    $sessionName,
                    $student->id,
                    $student->student_name,
                    $rollMap->get($student->id, ''),
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

        return $query->get();
    }
}
