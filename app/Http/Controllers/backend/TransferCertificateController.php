<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Classname;
use App\Models\TransferCertificate;
use App\Services\TransferCertificate\TransferCertificateService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class TransferCertificateController extends Controller
{
    public function __construct(private TransferCertificateService $service)
    {
    }

    public function index(Request $request)
    {
        $this->applySessionDatabaseBindings($request);

        $query = TransferCertificate::with(['latestIssue', 'issues'])
            ->orderByDesc('issue_date')
            ->orderByDesc('id');

        if ($request->filled('session_name')) {
            $query->where('session_name', $request->session_name);
        }

        if ($request->filled('class_name')) {
            $query->where('class_name', $request->class_name);
        }

        if ($request->filled('student_search')) {
            $search = $request->student_search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('scholar_no', 'like', "%{$search}%")
                    ->orWhere('certificate_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('issue_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('issue_date', '<=', $request->to_date);
        }

        if ($request->boolean('duplicates')) {
            $query->whereHas('issues', fn ($q) => $q->where('is_duplicate', true));
        }

        if ($request->get('export') === 'csv') {
            return $this->exportCsv($query->get());
        }

        $certificates = $query->paginate(25)->withQueryString();
        $classes = Schema::connection('dynamic')->hasTable('class_name')
            ? Classname::on('dynamic')->orderBy('class_name')->get()
            : collect();
        $classIdsByName = Schema::connection('dynamic')->hasTable('classes')
            ? DB::connection('dynamic')->table('classes')
                ->select('id', 'class_name')
                ->orderBy('id')
                ->get()
                ->unique('class_name')
                ->pluck('id', 'class_name')
            : collect();
        $studentIdsByScholarNo = Schema::connection('dynamic')->hasTable('student_registration')
            ? DB::connection('dynamic')->table('student_registration')
                ->whereIn('scholar_no', $certificates->getCollection()->pluck('scholar_no')->filter()->unique()->values())
                ->pluck('id', 'scholar_no')
            : collect();

        return view('backend.TransferCertificate.tc-index', compact('certificates', 'classes', 'classIdsByName', 'studentIdsByScholarNo'));
    }

    public function create(Request $request)
    {
        $this->applySessionDatabaseBindings($request);

        $sessionName = $this->resolveSelectedSessionYear($request);

        $students = DB::connection('dynamic')
            ->table('student_registration')
            ->when(Schema::connection('dynamic')->hasColumn('student_registration', 'status'), function ($query) {
                $query->where('status', 'r');
            })
            ->when(Schema::connection('dynamic')->hasColumn('student_registration', 'next_year'), function ($query) {
                $query->where(function ($q) {
                    $q->where('next_year', 0)->orWhereNull('next_year');
                });
            })
            ->when(Schema::connection('dynamic')->hasColumn('student_registration', 'transfer_status'), function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('transfer_status')->orWhere('transfer_status', '!=', 'transferred');
                });
            })
            ->orderBy('student_name')
            ->select('id', 'student_name', 'scholar_no', 'class_name', 'session_name', 'json_str', 'date_of_birth', 'registration_date')
            ->limit(500)
            ->get();

        $suggestedTcNo = $this->nextAvailableCertificateNo($sessionName);
        $classes = $this->classesForForm();

        return view('backend.TransferCertificate.tc-form', [
            'students' => $students,
            'certificate' => null,
            'suggestedTcNo' => $suggestedTcNo,
            'classes' => $classes,
            'sessionName' => $sessionName,
        ]);
    }

    public function store(Request $request)
    {
        $this->applySessionDatabaseBindings($request);

        $validated = $request->validate([
            'student_id' => ['required', 'integer'],
            'certificate_no' => ['required', 'string', 'max:60', Rule::unique('tc_certificates', 'certificate_no')],
            'session_name' => ['nullable', 'string', 'max:50'],
            'issue_date' => ['required', 'date'],
            'leaving_date' => ['nullable', 'date'],
            'reason_for_leaving' => ['nullable', 'string', 'max:500'],
            'conduct' => ['nullable', 'string', 'max:200'],
            'remarks' => ['nullable', 'string'],
            'reissue_reason' => ['nullable', 'string', 'max:500'],
            'tc_form_data' => ['nullable', 'array'],
        ]);

        $certificate = $this->service->issue($validated, $request);

        return redirect()
            ->route('transfercertificate.print', $certificate->id)
            ->with('success', 'Transfer Certificate generated successfully.');
    }

    public function edit(Request $request, int $id)
    {
        $this->applySessionDatabaseBindings($request);

        $certificate = TransferCertificate::findOrFail($id);
        $students = collect();
        $classes = $this->classesForForm();
        $sessionName = $certificate->session_name;

        return view('backend.TransferCertificate.tc-form', compact('certificate', 'students', 'classes', 'sessionName'));
    }

    public function update(Request $request, int $id)
    {
        $this->applySessionDatabaseBindings($request);

        $certificate = TransferCertificate::findOrFail($id);
        $oldValues = $certificate->toArray();

        $validated = $request->validate([
            'certificate_no' => ['required', 'string', 'max:60', Rule::unique('tc_certificates', 'certificate_no')->ignore($certificate->id)],
            'issue_date' => ['required', 'date'],
            'leaving_date' => ['nullable', 'date'],
            'reason_for_leaving' => ['nullable', 'string', 'max:500'],
            'conduct' => ['nullable', 'string', 'max:200'],
            'remarks' => ['nullable', 'string'],
            'tc_form_data' => ['nullable', 'array'],
        ]);

        $certificate->update($validated);
        $this->service->writeAudit($certificate, null, 'updated', $request, $oldValues, $certificate->fresh()->toArray());

        return redirect()->route('transfercertificate.index')->with('success', 'Transfer Certificate updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->applySessionDatabaseBindings($request);

        $certificate = TransferCertificate::findOrFail($id);
        $oldValues = $certificate->toArray();

        DB::connection('dynamic')->transaction(function () use ($certificate, $oldValues, $request) {
            $statusRecord = DB::connection('dynamic')
                ->table('tc_student_session_statuses')
                ->where('certificate_id', $certificate->id)
                ->first();

            $this->restoreStudentAfterTcDelete($certificate);
            $this->restoreNextSessionStudentAfterTcDelete($certificate, $statusRecord?->inactive_from_session);

            DB::connection('dynamic')
                ->table('tc_student_session_statuses')
                ->where('certificate_id', $certificate->id)
                ->delete();

            $this->service->writeAudit($certificate, null, 'deleted_and_student_restored', $request, $oldValues, null);

            $certificate->delete();
        });

        return redirect()->route('transfercertificate.index')->with('success', 'Transfer Certificate deleted and student restored as regular.');
    }

    public function print(Request $request, int $id)
    {
        $this->applySessionDatabaseBindings($request);

        $certificate = TransferCertificate::with(['latestIssue', 'issues'])->findOrFail($id);

        if ($request->get('download') === 'pdf') {
            $isPdf = true;
            $pdf = Pdf::loadView('backend.TransferCertificate.pdf.default', compact('certificate', 'isPdf'))->setPaper('A4');

            return $pdf->download($certificate->certificate_no . '.pdf');
        }

        $isPdf = false;

        return view('backend.TransferCertificate.tc-print', compact('certificate', 'isPdf'));
    }

    public function duplicate(Request $request, int $id)
    {
        $this->applySessionDatabaseBindings($request);

        $certificate = TransferCertificate::findOrFail($id);

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'reissue_reason' => ['required', 'string', 'max:500'],
            ]);

            $data = array_merge($data, [
                'student_id' => $certificate->student_id,
                'certificate_no' => $certificate->certificate_no,
                'session_name' => $certificate->session_name,
                'issue_date' => now()->toDateString(),
                'leaving_date' => optional($certificate->leaving_date)->toDateString(),
                'reason_for_leaving' => $certificate->reason_for_leaving,
                'conduct' => $certificate->conduct,
                'remarks' => $certificate->remarks,
            ]);

            $this->service->issue($data, $request);

            return redirect()->route('transfercertificate.print', $certificate->id)->with('success', 'Duplicate Transfer Certificate generated.');
        }

        return view('backend.TransferCertificate.tc-duplicate', compact('certificate'));
    }

    public function searchStudent(Request $request)
    {
        $this->applySessionDatabaseBindings($request);

        $search = $request->get('q');
        $students = DB::connection('dynamic')
            ->table('student_registration')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('student_name', 'like', "%{$search}%")
                        ->orWhere('scholar_no', 'like', "%{$search}%");
                });
            })
            ->when(Schema::connection('dynamic')->hasColumn('student_registration', 'next_year'), function ($query) {
                $query->where(function ($q) {
                    $q->where('next_year', 0)->orWhereNull('next_year');
                });
            })
            ->orderBy('student_name')
            ->limit(20)
            ->get(['id', 'student_name', 'scholar_no', 'class_name', 'session_name', 'json_str']);

        return response()->json($students->map(function ($student) {
            $json = json_decode($student->json_str ?? '{}', true) ?: [];
            $section = $this->resolveStudentSection($student, $student->session_name);
            $rollNo = $this->rollNoForStudent((int) $student->id, $student->scholar_no, $student->class_name, $student->session_name);

            return [
                'id' => $student->id,
                'text' => trim($student->student_name . ' - ' . $student->scholar_no),
                'student_name' => $student->student_name,
                'scholar_no' => $student->scholar_no,
                'class_name' => $student->class_name,
                'section_name' => $section,
                'roll_no' => $rollNo,
                'session_name' => $student->session_name,
            ];
        }));
    }

    public function studentDetails(Request $request, int $id)
    {
        $this->applySessionDatabaseBindings($request);

        $student = DB::connection('dynamic')
            ->table('student_registration')
            ->where('id', $id)
            ->first();

        abort_if(!$student, 404);

        $json = json_decode($student->json_str ?? '{}', true) ?: [];
        $sessionName = $student->session_name ?: $this->resolveSelectedSessionYear($request);
        $section = $this->resolveStudentSection($student, $sessionName);
        $rollNo = $this->rollNoForStudent((int) $student->id, $student->scholar_no, $student->class_name, $sessionName);
        $subjectClassName = $request->input('class_name', $student->class_name);
        $subjects = $this->subjectsForStudent((int) $student->id, $subjectClassName);
        $attendance = $this->attendanceForStudent((int) $student->id, $sessionName, $student->class_name, $student->scholar_no);
        $dateOfBirth = $this->dateForInput($student->date_of_birth ?: ($json['student_dob'] ?? null));
        $categoryAndCaste = $this->resolveStudentCategoryAndCaste($student, $sessionName);

        return response()->json([
            'id' => $student->id,
            'name' => $this->displayValue($student->student_name ?: ($json['studentname'] ?? null)),
            'roll_no' => $rollNo,
            'scholar_no' => $this->displayValue($student->scholar_no),
            'scholar_serial_no' => $this->displayValue($student->scholar_no),
            'class_name' => $this->displayValue($student->class_name),
            'section_name' => $section,
            'session_name' => $this->displayValue($sessionName),
            'mother_name' => $this->displayValue($json['mother_name'] ?? $json['mothername'] ?? null),
            'father_name' => $this->displayValue($json['student_father_name'] ?? $json['fathername'] ?? $json['father_name'] ?? null),
            'caste' => $this->displayValue($categoryAndCaste),
            'nationality' => $this->displayValue($json['nationality'] ?? 'Indian'),
            'date_of_admitted' => $this->dateForInput($json['admission_date'] ?? $student->registration_date ?? null),
            'class_to_which_admitted' => $this->displayValue($json['admitted_class'] ?? $json['class_to_which_admitted'] ?? $json['classname'] ?? $student->application_for ?? null),
            'admission_session' => $this->displayValue($json['admission_session'] ?? $sessionName),
            'date_of_birth' => $dateOfBirth,
            'date_of_birth_words' => $this->dateInWords($dateOfBirth),
            'subjects_studied' => $this->displayValue($subjects->implode(', ')),
            'pen_apaar_id' => $this->displayValue(
                $json['pen_apaar_id']
                ?? $json['pen_no_apaar_id']
                ?? $json['pen_no']
                ?? $json['pen_number']
                ?? $json['pen']
                ?? $json['apaar_id']
                ?? $json['apaar']
                ?? $json['APAAR']
                ?? null
            ),
            'working_days_present' => $attendance['present_days'],
            'total_working_days' => $attendance['working_days'],
        ]);
    }

    private function exportCsv($certificates)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transfer-certificates.csv"',
        ];

        $callback = function () use ($certificates) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Certificate No', 'Scholar No', 'Student Name', 'Session', 'Class', 'Issue Date', 'Duplicate Issues']);

            foreach ($certificates as $certificate) {
                fputcsv($handle, [
                    $certificate->certificate_no,
                    $certificate->scholar_no,
                    $certificate->student_name,
                    $certificate->session_name,
                    $certificate->class_name,
                    optional($certificate->issue_date)->format('Y-m-d'),
                    $certificate->issues->where('is_duplicate', true)->count(),
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function classesForForm()
    {
        if (!Schema::connection('dynamic')->hasTable('class_name')) {
            return collect();
        }

        return DB::connection('dynamic')
            ->table('class_name')
            ->where(function ($query) {
                $query->where('is_delete', 0)->orWhereNull('is_delete');
            })
            ->orderBy('class_name')
            ->get(['class_name']);
    }

    private function subjectsForStudent(int $studentId, ?string $className)
    {
        if (
            !Schema::connection('dynamic')->hasTable('subject_assign_student')
            || !Schema::connection('dynamic')->hasTable('combination_subject')
            || !Schema::connection('dynamic')->hasTable('subjectmaster')
        ) {
            return collect();
        }

        return DB::connection('dynamic')
            ->table('subject_assign_student as sas')
            ->join('combination_subject as cs', 'cs.subject_combination_id', '=', 'sas.assign_this_combtoall')
            ->join('subjectmaster as s', 's.id', '=', 'cs.subject_id')
            ->where(function ($q) use ($studentId) {
                $q->where('sas.students_details', (string) $studentId)
                    ->orWhere('sas.students_details', $studentId)
                    ->orWhereJsonContains('sas.students_details', (string) $studentId)
                    ->orWhereJsonContains('sas.students_details', $studentId);
            })
            ->when($className, fn ($q) => $q->where('sas.class_name', $className))
            ->where(function ($q) {
                $q->where('sas.is_delete', 0)->orWhereNull('sas.is_delete');
            })
            ->where(function ($q) {
                $q->where('s.is_delete', 0)->orWhereNull('s.is_delete');
            })
            ->orderBy('cs.subject_order')
            ->orderBy('s.subject_name')
            ->distinct()
            ->pluck('s.subject_name');
    }

    /**
     * Resolve attendance (both collective and daily) for student across sessions
     */
    private function attendanceForStudent(int $studentId, ?string $sessionName, ?string $className = null, ?string $scholarNo = null): array
    {
        $sessions = $this->getAvailableAcademicDatabases();

        foreach ($sessions as $db) {
            $currStudentId = $studentId;

            if ($db) {
                if (!$this->tableExists($db, 'student_registration')) {
                    continue;
                }
                $st = DB::table("{$db}.student_registration")->where('id', $studentId)->first();
                if (!$st && $scholarNo) {
                    $st = DB::table("{$db}.student_registration")->where('scholar_no', $scholarNo)->first();
                }
                if ($st) {
                    $currStudentId = $st->id;
                }
            }

            // 1. Try collective attendance (first with annual exam filter, then latest collective record)
            if ($this->tableExists($db, 'academic_attendance_collective_details') && $this->tableExists($db, 'academic_attendance_collectives')) {
                $tableCollectives = $db ? "{$db}.academic_attendance_collectives" : 'academic_attendance_collectives';
                $tableCollectiveDetails = $db ? "{$db}.academic_attendance_collective_details" : 'academic_attendance_collective_details';
                $conn = $db ? null : 'dynamic';

                $builder = $conn ? DB::connection($conn)->table("{$tableCollectiveDetails} as d") : DB::table("{$tableCollectiveDetails} as d");

                $record = (clone $builder)
                    ->join("{$tableCollectives} as c", 'c.id', '=', 'd.collective_id')
                    ->where('d.student_id', $currStudentId)
                    ->where(function ($q) {
                        $q->where('c.is_delete', 0)->orWhereNull('c.is_delete');
                    })
                    ->when($className, fn ($q) => $q->where('c.class_name', $className))
                    ->tap(fn ($q) => $this->applyAnnualExamFilter($q, $sessionName, 'c'))
                    ->orderByDesc('c.end_date')
                    ->orderByDesc('d.id')
                    ->first(['d.present_days', 'd.working_days']);

                if (!$record || (float) ($record->working_days ?? 0) <= 0) {
                    $record = (clone $builder)
                        ->join("{$tableCollectives} as c", 'c.id', '=', 'd.collective_id')
                        ->where('d.student_id', $currStudentId)
                        ->where(function ($q) {
                            $q->where('c.is_delete', 0)->orWhereNull('c.is_delete');
                        })
                        ->when($className, fn ($q) => $q->where('c.class_name', $className))
                        ->orderByDesc('c.end_date')
                        ->orderByDesc('d.id')
                        ->first(['d.present_days', 'd.working_days']);
                }

                if ($record && (float) ($record->working_days ?? 0) > 0) {
                    $p = (float) $record->present_days;
                    $w = (float) $record->working_days;
                    return [
                        'present_days' => ((int) $p == $p) ? (int) $p : round($p, 2),
                        'working_days' => ((int) $w == $w) ? (int) $w : round($w, 2),
                    ];
                }
            }

            // 2. Try daily attendance (academic_attendance + academic_attendance_details)
            if ($this->tableExists($db, 'academic_attendance_details') && $this->tableExists($db, 'academic_attendance')) {
                $tableAtt = $db ? "{$db}.academic_attendance" : 'academic_attendance';
                $tableAttDetails = $db ? "{$db}.academic_attendance_details" : 'academic_attendance_details';
                $conn = $db ? null : 'dynamic';

                $dailyBuilder = $conn ? DB::connection($conn)->table("{$tableAttDetails} as aad") : DB::table("{$tableAttDetails} as aad");

                $dailyQuery = $dailyBuilder
                    ->join("{$tableAtt} as aa", 'aad.attendance_id', '=', 'aa.id')
                    ->where('aad.student_id', $currStudentId)
                    ->where(function ($q) {
                        $q->where('aa.is_delete', 0)->orWhereNull('aa.is_delete');
                    });

                $totalDays = (clone $dailyQuery)->distinct('aa.attendance_date')->count('aa.attendance_date');
                if ($totalDays > 0) {
                    $presentCount = (clone $dailyQuery)
                        ->selectRaw("SUM(CASE 
                            WHEN LOWER(aad.status) IN ('present', 'p', 'pl') THEN 1 
                            WHEN LOWER(aad.status) IN ('half_day', 'half-day', 'hd') THEN 0.5 
                            ELSE 0 
                        END) as total")
                        ->value('total');

                    $presentDays = (float) ($presentCount ?? 0);
                    return [
                        'present_days' => ((int) $presentDays == $presentDays) ? (int) $presentDays : round($presentDays, 2),
                        'working_days' => $totalDays,
                    ];
                }
            }
        }

        return [
            'present_days' => '',
            'working_days' => '',
        ];
    }

    /**
     * Resolve latest Roll No from academic/roll-no-tools (academic_student_roll_no)
     */
    private function rollNoForStudent(int $studentId, ?string $scholarNo = null, ?string $className = null, ?string $sessionName = null): string
    {
        $sessions = $this->getAvailableAcademicDatabases();

        foreach ($sessions as $db) {
            $currStudentId = $studentId;

            if ($db) {
                if (!$this->tableExists($db, 'student_registration')) {
                    continue;
                }
                $st = DB::table("{$db}.student_registration")->where('id', $studentId)->first();
                if (!$st && $scholarNo) {
                    $st = DB::table("{$db}.student_registration")->where('scholar_no', $scholarNo)->first();
                }
                if ($st) {
                    $currStudentId = $st->id;
                }
            }

            if ($this->tableExists($db, 'academic_student_roll_no')) {
                $tableRoll = $db ? "{$db}.academic_student_roll_no" : 'academic_student_roll_no';
                $conn = $db ? null : 'dynamic';
                $builder = $conn ? DB::connection($conn)->table($tableRoll) : DB::table($tableRoll);

                $query = $builder
                    ->where(function ($q) use ($currStudentId) {
                        $q->where('student_id', (string) $currStudentId)
                          ->orWhere('student_id', $currStudentId);
                    })
                    ->whereNotNull('roll_no')
                    ->where('roll_no', '!=', '');

                if ($sessionName) {
                    $sessionVariants = array_unique(array_filter([
                        $sessionName,
                        str_replace('_', '-', $sessionName),
                        str_replace('-', '_', $sessionName),
                    ]));
                    $query->where(function ($q) use ($sessionVariants) {
                        $q->whereIn('session_id', $sessionVariants)
                          ->orWhereNull('session_id')
                          ->orWhere('session_id', '');
                    });
                }

                $rollNo = (string) $query->orderByDesc('id')->value('roll_no');
                if (!empty($rollNo) && trim($rollNo) !== '') {
                    return trim($rollNo);
                }
            }

            // Fallback to student_registration table / json_str
            $tableStudent = $db ? "{$db}.student_registration" : 'student_registration';
            $conn = $db ? null : 'dynamic';
            $stBuilder = $conn ? DB::connection($conn)->table($tableStudent) : DB::table($tableStudent);

            $stRecord = $stBuilder->where('id', $currStudentId)->first();
            if ($stRecord) {
                $json = json_decode($stRecord->json_str ?? '{}', true) ?: [];
                $rollCandidate = $stRecord->roll_no ?? ($json['roll_no'] ?? ($json['rollno'] ?? null));
                if (!empty($rollCandidate) && trim((string) $rollCandidate) !== '') {
                    return trim((string) $rollCandidate);
                }
            }
        }

        return '';
    }

    /**
     * Resolve student assigned section across columns, roll tools, and sessions
     */
    private function resolveStudentSection($student, ?string $sessionName = null): string
    {
        if (!$student) {
            return '';
        }

        // 1. Direct section_name on student
        if (!empty($student->section_name) && trim((string) $student->section_name) !== '' && strtoupper(trim((string) $student->section_name)) !== 'N/A') {
            return trim((string) $student->section_name);
        }

        // 2. Section in JSON str
        $json = json_decode($student->json_str ?? '{}', true) ?: [];
        if (!empty($json['section_name']) && trim((string) $json['section_name']) !== '' && strtoupper(trim((string) $json['section_name'])) !== 'N/A') {
            return trim((string) $json['section_name']);
        }
        if (!empty($json['section']) && trim((string) $json['section']) !== '' && strtoupper(trim((string) $json['section'])) !== 'N/A') {
            return trim((string) $json['section']);
        }

        $scholarNo = $student->scholar_no ?? ($json['scholar_no'] ?? null);
        $studentId = $student->id;
        $sessions = $this->getAvailableAcademicDatabases();

        foreach ($sessions as $db) {
            $currStudentId = $studentId;

            if ($db) {
                if (!$this->tableExists($db, 'student_registration')) {
                    continue;
                }
                $st = DB::table("{$db}.student_registration")->where('id', $studentId)->first();
                if (!$st && $scholarNo) {
                    $st = DB::table("{$db}.student_registration")->where('scholar_no', $scholarNo)->first();
                }
                if ($st) {
                    $currStudentId = $st->id;
                    if (!empty($st->section_name) && trim((string) $st->section_name) !== '' && strtoupper(trim((string) $st->section_name)) !== 'N/A') {
                        return trim((string) $st->section_name);
                    }
                    $stJson = json_decode($st->json_str ?? '{}', true) ?: [];
                    if (!empty($stJson['section_name']) && trim((string) $stJson['section_name']) !== '' && strtoupper(trim((string) $stJson['section_name'])) !== 'N/A') {
                        return trim((string) $stJson['section_name']);
                    }
                }
            }

            // 3. From academic_student_roll_no.section_id (stores section names like 'Aryabhatta', 'Kautilya')
            if ($this->tableExists($db, 'academic_student_roll_no')) {
                $tableRoll = $db ? "{$db}.academic_student_roll_no" : 'academic_student_roll_no';
                $conn = $db ? null : 'dynamic';
                $builder = $conn ? DB::connection($conn)->table($tableRoll) : DB::table($tableRoll);

                $rollSec = $builder
                    ->where(function ($q) use ($currStudentId) {
                        $q->where('student_id', (string) $currStudentId)
                          ->orWhere('student_id', $currStudentId);
                    })
                    ->whereNotNull('section_id')
                    ->where('section_id', '!=', '')
                    ->where('section_id', '!=', '0')
                    ->orderByDesc('id')
                    ->value('section_id');

                if (!empty($rollSec) && trim((string) $rollSec) !== '') {
                    return trim((string) $rollSec);
                }
            }

            // 4. From classes table if class_id is present
            if (!empty($student->class_id) && $this->tableExists($db, 'classes')) {
                $tableClasses = $db ? "{$db}.classes" : 'classes';
                $conn = $db ? null : 'dynamic';
                $builder = $conn ? DB::connection($conn)->table($tableClasses) : DB::table($tableClasses);

                $clsSec = $builder->where('id', $student->class_id)->value('section_name');
                if (!empty($clsSec) && trim((string) $clsSec) !== '') {
                    return trim((string) $clsSec);
                }
            }

            // 5. Next year section
            if (!empty($student->next_year_section) && trim((string) $student->next_year_section) !== '') {
                return trim((string) $student->next_year_section);
            }
            if (!empty($json['next_year_section']) && trim((string) $json['next_year_section']) !== '') {
                return trim((string) $json['next_year_section']);
            }
        }

        return '';
    }

    /**
     * Resolve student Category and Caste inline across registration columns, json_str, and sessions
     */
    private function resolveStudentCategoryAndCaste($student, ?string $sessionName = null): string
    {
        if (!$student) {
            return '';
        }

        $json = json_decode($student->json_str ?? '{}', true) ?: [];
        $rawCategory = $json['category'] ?? ($json['student_category'] ?? ($student->category ?? null));
        $rawCaste = $json['student_caste'] ?? ($json['caste'] ?? ($json['caste_name'] ?? ($json['cast'] ?? ($student->caste ?? null))));

        $scholarNo = $student->scholar_no ?? ($json['scholar_no'] ?? null);
        $studentId = $student->id;

        // If either category or caste is missing, check other session databases
        if (empty($rawCategory) || empty($rawCaste) || in_array(strtolower(trim((string) $rawCaste)), ['0', 'select', '-', '--', 'null', 'none', 'na', 'n/a'])) {
            $sessions = $this->getAvailableAcademicDatabases();

            foreach ($sessions as $db) {
                if (!$db || !$this->tableExists($db, 'student_registration')) {
                    continue;
                }

                $st = DB::table("{$db}.student_registration")->where('id', $studentId)->first();
                if (!$st && $scholarNo) {
                    $st = DB::table("{$db}.student_registration")->where('scholar_no', $scholarNo)->first();
                }

                if ($st) {
                    $sJson = json_decode($st->json_str ?? '{}', true) ?: [];
                    if (empty($rawCategory)) {
                        $rawCategory = $sJson['category'] ?? ($sJson['student_category'] ?? ($st->category ?? null));
                    }
                    if (empty($rawCaste) || in_array(strtolower(trim((string) $rawCaste)), ['0', 'select', '-', '--', 'null', 'none', 'na', 'n/a'])) {
                        $candidateCaste = $sJson['student_caste'] ?? ($sJson['caste'] ?? ($sJson['caste_name'] ?? ($sJson['cast'] ?? ($st->caste ?? null))));
                        if (!empty($candidateCaste) && !in_array(strtolower(trim((string) $candidateCaste)), ['0', 'select', '-', '--', 'null', 'none', 'na', 'n/a'])) {
                            $rawCaste = $candidateCaste;
                        }
                    }

                    if (!empty($rawCategory) && !empty($rawCaste) && !in_array(strtolower(trim((string) $rawCaste)), ['0', 'select', '-', '--', 'null', 'none', 'na', 'n/a'])) {
                        break;
                    }
                }
            }
        }

        // Clean & normalize Category
        $rawCategory = is_string($rawCategory) ? trim($rawCategory) : '';
        if (in_array(strtolower($rawCategory), ['', '0', 'select', '-', '--', 'null', 'none', 'na', 'n/a'])) {
            $rawCategory = null;
        }

        $categoryDisplay = null;
        if ($rawCategory) {
            $upperCat = strtoupper($rawCategory);
            if (in_array($upperCat, ['GEN', 'GEN.', 'GENERAL', 'UR', 'UNRESERVED', 'OPEN'])) {
                $categoryDisplay = 'General';
            } elseif (in_array($upperCat, ['OBC', 'SC', 'ST'])) {
                $categoryDisplay = $upperCat;
            } else {
                $categoryDisplay = ucfirst(trim($rawCategory));
            }
        }

        // Clean & resolve Caste
        $rawCaste = is_string($rawCaste) ? trim($rawCaste) : '';
        if (is_numeric($rawCaste) && (int) $rawCaste > 0) {
            $casteName = DB::connection('dynamic')->table('caste_name')->where('id', (int) $rawCaste)->value('caste_name');
            if ($casteName) {
                $rawCaste = trim((string) $casteName);
            }
        }

        if (in_array(strtolower($rawCaste), ['', '0', 'select', '-', '--', 'null', 'none', 'na', 'n/a'])) {
            $rawCaste = null;
        }

        // Remove redundancy if caste merely duplicates category
        if ($categoryDisplay && $rawCaste) {
            if (strcasecmp($categoryDisplay, $rawCaste) === 0) {
                $rawCaste = null;
            } elseif ($categoryDisplay === 'General' && in_array(strtoupper($rawCaste), ['GEN', 'GEN.', 'GENERAL', 'UR', 'OPEN'])) {
                $rawCaste = null;
            }
        }

        if ($categoryDisplay && $rawCaste) {
            $casteFormatted = (strtoupper($rawCaste) === $rawCaste || strtolower($rawCaste) === $rawCaste)
                ? ucwords(strtolower($rawCaste))
                : $rawCaste;
            return "{$categoryDisplay} (Category) - {$casteFormatted} (Caste)";
        } elseif ($categoryDisplay) {
            return $categoryDisplay;
        } elseif ($rawCaste) {
            $casteFormatted = (strtoupper($rawCaste) === $rawCaste || strtolower($rawCaste) === $rawCaste)
                ? ucwords(strtolower($rawCaste))
                : $rawCaste;
            return "{$casteFormatted} (Caste)";
        }

        return '';
    }

    /**
     * Get available academic session database names in chronological order
     */
    private function getAvailableAcademicDatabases(?Request $request = null): array
    {
        $currentSession = $this->resolveSelectedSessionYear($request ?: request()) ?: (Config::get('database.connections.dynamic.database') ?: '2026_2027');
        $currStart = 2026;
        if (preg_match('/^(\d{4})[_-](\d{4})$/', (string) $currentSession, $m)) {
            $currStart = (int) $m[1];
        }
        $prevSession = ($currStart - 1) . '_' . ($currStart);
        $currSession = $currStart . '_' . ($currStart + 1);

        $isMysql = (Config::get('database.connections.dynamic.driver') === 'mysql' || Config::get('database.default') === 'mysql');
        $sessionsToCheck = [];

        if ($isMysql) {
            try {
                $availableDbs = array_map(function ($d) {
                    return array_values((array) $d)[0];
                }, DB::select("SHOW DATABASES"));

                $matchingDbs = [];
                foreach ($availableDbs as $dName) {
                    if (preg_match('/^(\d{4})[_-](\d{4})$/', $dName, $dm)) {
                        $matchingDbs[$dName] = (int) $dm[1];
                    }
                }
                asort($matchingDbs);

                foreach ($matchingDbs as $dName => $sYear) {
                    if ($sYear <= $currStart || $dName === $currentSession) {
                        $sessionsToCheck[] = $dName;
                    }
                }
                if (in_array($prevSession, $availableDbs) && !in_array($prevSession, $sessionsToCheck)) {
                    $sessionsToCheck[] = $prevSession;
                }
                if (in_array($currSession, $availableDbs) && !in_array($currSession, $sessionsToCheck)) {
                    $sessionsToCheck[] = $currSession;
                }
                if (in_array($currentSession, $availableDbs) && !in_array($currentSession, $sessionsToCheck)) {
                    $sessionsToCheck[] = $currentSession;
                }
            } catch (\Throwable $e) {}
        }

        if (empty($sessionsToCheck)) {
            $sessionsToCheck[] = null; // null uses active dynamic connection
        } else {
            // Put current session first for faster lookups
            if (in_array($currentSession, $sessionsToCheck)) {
                $sessionsToCheck = array_diff($sessionsToCheck, [$currentSession]);
                array_unshift($sessionsToCheck, $currentSession);
            }
        }

        return array_values(array_unique($sessionsToCheck));
    }

    /**
     * Check if a table exists in dynamic connection or specific database
     */
    private function tableExists(?string $db, string $table): bool
    {
        if (!$db) {
            return Schema::connection('dynamic')->hasTable($table);
        }

        try {
            $res = DB::select("SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1", [$db, $table]);
            return !empty($res);
        } catch (\Throwable $e) {
            try {
                return Schema::connection('dynamic')->hasTable($table);
            } catch (\Throwable $e2) {
                return false;
            }
        }
    }

    private function dateForInput($value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }

    private function dateInWords(?string $date): string
    {
        if (!$date) {
            return '--';
        }

        try {
            $carbon = \Carbon\Carbon::parse($date);
        } catch (\Throwable) {
            return '--';
        }

        return trim($this->ordinalWord((int) $carbon->format('j')) . ' ' . $carbon->format('F') . ' ' . $this->numberToWords((int) $carbon->format('Y')));
    }

    private function ordinalWord(int $number): string
    {
        $ordinals = [
            1 => 'First', 2 => 'Second', 3 => 'Third', 4 => 'Fourth', 5 => 'Fifth',
            6 => 'Sixth', 7 => 'Seventh', 8 => 'Eighth', 9 => 'Ninth', 10 => 'Tenth',
            11 => 'Eleventh', 12 => 'Twelfth', 13 => 'Thirteenth', 14 => 'Fourteenth',
            15 => 'Fifteenth', 16 => 'Sixteenth', 17 => 'Seventeenth', 18 => 'Eighteenth',
            19 => 'Nineteenth', 20 => 'Twentieth', 21 => 'Twenty First', 22 => 'Twenty Second',
            23 => 'Twenty Third', 24 => 'Twenty Fourth', 25 => 'Twenty Fifth',
            26 => 'Twenty Sixth', 27 => 'Twenty Seventh', 28 => 'Twenty Eighth',
            29 => 'Twenty Ninth', 30 => 'Thirtieth', 31 => 'Thirty First',
        ];

        return $ordinals[$number] ?? (string) $number;
    }

    private function numberToWords(int $number): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        if ($number < 20) {
            return $ones[$number];
        }

        if ($number < 100) {
            return trim($tens[intdiv($number, 10)] . ' ' . $ones[$number % 10]);
        }

        if ($number < 1000) {
            return trim($ones[intdiv($number, 100)] . ' Hundred ' . $this->numberToWords($number % 100));
        }

        if ($number < 100000) {
            return trim($this->numberToWords(intdiv($number, 1000)) . ' Thousand ' . $this->numberToWords($number % 1000));
        }

        return (string) $number;
    }

    private function applyAnnualExamFilter($query, ?string $sessionName, string $alias)
    {
        $sessionWithDash = $sessionName ? str_replace('_', '-', $sessionName) : null;

        return $query
            ->where(function ($q) use ($alias) {
                $q->whereRaw("LOWER(COALESCE({$alias}.exam_name, '')) LIKE ?", ['%annual%'])
                    ->orWhereRaw("LOWER(COALESCE({$alias}.exam_name, '')) LIKE ?", ['%term 2%'])
                    ->orWhereRaw("LOWER(COALESCE({$alias}.exam_name, '')) LIKE ?", ['%term-2%'])
                    ->orWhereRaw("LOWER(COALESCE({$alias}.exam_name, '')) LIKE ?", ['%term_2%']);
            })
            ->when($sessionName, function ($q) use ($alias, $sessionName, $sessionWithDash) {
                $q->where(function ($inner) use ($alias, $sessionName, $sessionWithDash) {
                    $inner->where("{$alias}.academic_session", $sessionName)
                        ->orWhere("{$alias}.academic_session", $sessionWithDash)
                        ->orWhere("{$alias}.exam_name", 'like', '%' . $sessionName . '%')
                        ->orWhere("{$alias}.exam_name", 'like', '%' . $sessionWithDash . '%');
                });
            });
    }

    private function displayValue($value): string
    {
        $value = is_string($value) ? trim($value) : $value;

        return empty($value) ? '--' : (string) $value;
    }

    private function nextAvailableCertificateNo(?string $sessionName): string
    {
        $sessionPart = $sessionName ?: now()->format('Y');
        $prefix = 'TC/' . str_replace('_', '-', $sessionPart) . '/';

        if (!Schema::connection('dynamic')->hasTable('tc_certificates') && !Schema::hasTable('tc_certificates')) {
            return $prefix . '00001';
        }

        try {
            $nextNumber = TransferCertificate::where('session_name', $sessionName)->count() + 1;

            do {
                $certificateNo = $prefix . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
                $nextNumber++;
            } while (TransferCertificate::where('certificate_no', $certificateNo)->exists());

            return $certificateNo;
        } catch (\Throwable $e) {
            return $prefix . '00001';
        }
    }

    private function restoreStudentAfterTcDelete(TransferCertificate $certificate): void
    {
        if (!Schema::connection('dynamic')->hasTable('student_registration')) {
            return;
        }

        $updates = ['updated_at' => now()];

        if (Schema::connection('dynamic')->hasColumn('student_registration', 'status')) {
            $updates['status'] = 'r';
        }
        if (Schema::connection('dynamic')->hasColumn('student_registration', 'transfer_status')) {
            $updates['transfer_status'] = 'active';
        }
        if (Schema::connection('dynamic')->hasColumn('student_registration', 'transferred_at')) {
            $updates['transferred_at'] = null;
        }
        if (Schema::connection('dynamic')->hasColumn('student_registration', 'tc_certificate_id')) {
            $updates['tc_certificate_id'] = null;
        }

        DB::connection('dynamic')
            ->table('student_registration')
            ->where('id', $certificate->student_id)
            ->orWhere('scholar_no', $certificate->scholar_no)
            ->update($updates);
    }

    private function restoreNextSessionStudentAfterTcDelete(TransferCertificate $certificate, ?string $nextSession): void
    {
        if (!$nextSession || !preg_match('/^\d{4}_\d{4}$/', $nextSession)) {
            return;
        }

        Config::set('database.connections.next_session_db.database', $nextSession);
        DB::purge('next_session_db');
        DB::reconnect('next_session_db');

        if (!Schema::connection('next_session_db')->hasTable('student_registration')) {
            return;
        }

        $updates = ['updated_at' => now()];

        if (Schema::connection('next_session_db')->hasColumn('student_registration', 'status')) {
            $updates['status'] = 'r';
        }
        if (Schema::connection('next_session_db')->hasColumn('student_registration', 'transfer_status')) {
            $updates['transfer_status'] = 'active';
        }
        if (Schema::connection('next_session_db')->hasColumn('student_registration', 'transferred_at')) {
            $updates['transferred_at'] = null;
        }
        if (Schema::connection('next_session_db')->hasColumn('student_registration', 'tc_certificate_id')) {
            $updates['tc_certificate_id'] = null;
        }

        $query = DB::connection('next_session_db')
            ->table('student_registration')
            ->where('scholar_no', $certificate->scholar_no);

        if (Schema::connection('next_session_db')->hasColumn('student_registration', 'tc_certificate_id')) {
            $query->where(function ($q) use ($certificate) {
                $q->where('tc_certificate_id', $certificate->id)
                    ->orWhereNull('tc_certificate_id');
            });
        }

        $query->update($updates);
    }

    private function applySessionDatabaseBindings(Request $request): void
    {
        $selectedYear = $this->resolveSelectedSessionYear($request);
        if (!$selectedYear || !preg_match('/^\d{4}_\d{4}$/', $selectedYear)) {
            return;
        }

        Config::set('database.connections.dynamic.database', $selectedYear);
        Config::set('database.default', 'dynamic');
        DB::purge('dynamic');
        DB::reconnect('dynamic');
        DB::setDefaultConnection('dynamic');
    }

    private function resolveSelectedSessionYear(?Request $request = null): ?string
    {
        $req = $request ?: request();
        $sessionVal = ($req && $req->hasSession()) ? $req->session()->get('selectedYear') : null;
        $selectedYear = $sessionVal ?: ($req ? ($req->cookie('selectedYear') ?: $req->input('year')) : null);

        return is_string($selectedYear) ? $selectedYear : null;
    }
}
