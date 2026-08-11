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
            ->orderBy('student_name')
            ->limit(20)
            ->get(['id', 'student_name', 'scholar_no', 'class_name', 'session_name', 'json_str']);

        return response()->json($students->map(function ($student) {
            $json = json_decode($student->json_str ?? '{}', true) ?: [];

            return [
                'id' => $student->id,
                'text' => trim($student->student_name . ' - ' . $student->scholar_no),
                'student_name' => $student->student_name,
                'scholar_no' => $student->scholar_no,
                'class_name' => $student->class_name,
                'section_name' => $json['section_name'] ?? null,
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
        $section = $json['section_name'] ?? $student->section_name ?? null;
        $sessionName = $student->session_name ?: $this->resolveSelectedSessionYear($request);
        $subjectClassName = $request->input('class_name', $student->class_name);
        $subjects = $this->subjectsForStudent((int) $student->id, $subjectClassName);
        $attendance = $this->attendanceForStudent((int) $student->id, $sessionName, $student->class_name);
        $dateOfBirth = $this->dateForInput($student->date_of_birth ?: ($json['student_dob'] ?? null));

        return response()->json([
            'id' => $student->id,
            'name' => $this->displayValue($student->student_name ?: ($json['studentname'] ?? null)),
            'scholar_no' => $this->displayValue($student->scholar_no),
            'scholar_serial_no' => $this->displayValue($student->scholar_no),
            'class_name' => $this->displayValue($student->class_name),
            'section_name' => $this->displayValue($section),
            'session_name' => $this->displayValue($sessionName),
            'mother_name' => $this->displayValue($json['mother_name'] ?? $json['mothername'] ?? null),
            'father_name' => $this->displayValue($json['student_father_name'] ?? $json['fathername'] ?? $json['father_name'] ?? null),
            'caste' => $this->displayValue($json['student_caste'] ?? $json['caste'] ?? $json['caste_name'] ?? $json['cast'] ?? null),
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

    private function attendanceForStudent(int $studentId, ?string $sessionName, ?string $className = null): array
    {
        if (
            !Schema::connection('dynamic')->hasTable('academic_attendance_collective_details')
            || !Schema::connection('dynamic')->hasTable('academic_attendance_collectives')
        ) {
            return ['present_days' => '', 'working_days' => ''];
        }

        $sessionWithDash = $sessionName ? str_replace('_', '-', $sessionName) : null;

        $record = DB::connection('dynamic')
            ->table('academic_attendance_collective_details as d')
            ->join('academic_attendance_collectives as c', 'c.id', '=', 'd.collective_id')
            ->where('d.student_id', $studentId)
            ->when($className, fn ($q) => $q->where('c.class_name', $className))
            ->tap(fn ($q) => $this->applyAnnualExamFilter($q, $sessionName, 'c'))
            ->orderByDesc('c.end_date')
            ->orderByDesc('d.id')
            ->first(['d.present_days', 'd.working_days']);

        return [
            'present_days' => $record->present_days ?? '',
            'working_days' => $record->working_days ?? '',
        ];
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
        $nextNumber = TransferCertificate::where('session_name', $sessionName)->count() + 1;

        do {
            $certificateNo = $prefix . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (TransferCertificate::where('certificate_no', $certificateNo)->exists());

        return $certificateNo;
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

    private function resolveSelectedSessionYear(Request $request): ?string
    {
        $selectedYear = $request->session()->get('selectedYear') ?: $request->cookie('selectedYear') ?: $request->input('year');

        return is_string($selectedYear) ? $selectedYear : null;
    }
}
