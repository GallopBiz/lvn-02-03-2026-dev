<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Exam;
use App\Models\Academic\Marksheet;
use App\Models\Classes;
use App\Models\Classname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MarksheetController extends Controller
{
    public function index(Request $request)
    {
        $generatedSearch = trim((string) $request->query('generated_search', ''));
        $generatedClassId = $request->integer('generated_class_id') ?: null;
        $generatedExamId = $request->integer('generated_exam_id') ?: null;
        $generatedMarksheets = Marksheet::query()
            ->when($generatedClassId, fn ($query) => $query->where('class_id', $generatedClassId))
            ->when($generatedExamId, fn ($query) => $query->where('exam_id', $generatedExamId))
            ->when($generatedSearch !== '', function ($query) use ($generatedSearch) {
                $like = '%' . str_replace(' ', '%', $generatedSearch) . '%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('session_year', 'like', $like)
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.student_name')) LIKE ?", [$like])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.scholar_no')) LIKE ?", [$like])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.print_data.student_meta.scholar_no')) LIKE ?", [$like])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.class_name')) LIKE ?", [$like])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.exam_name')) LIKE ?", [$like]);
                });
            })
            ->latest('id')
            ->paginate(20)
            ->appends($request->only('generated_search', 'generated_class_id', 'generated_exam_id', 'class_id'));
        $classes = Classes::query()
            ->orderBy('class_name')
            ->get()
            ->unique('class_name')
            ->values();
        $exams = Exam::with('classInfo')->latest('id')->get();
        $students = $this->studentsForClass($request->integer('class_id'));
        $selectedClassId = $request->integer('class_id') ?: null;

        return view('backend.AcademicsModules.marksheet', compact(
            'generatedMarksheets',
            'generatedSearch',
            'generatedClassId',
            'generatedExamId',
            'classes',
            'exams',
            'students',
            'selectedClassId'
        ));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|integer',
            'exam_id' => 'nullable|integer',
            'student_id' => 'nullable|integer',
            'attendance' => 'nullable|string|max:50',
        ]);

        $classId = (int) $validated['class_id'];
        $examId = !empty($validated['exam_id']) ? (int) $validated['exam_id'] : null;
        $studentId = !empty($validated['student_id']) ? (int) $validated['student_id'] : null;
        $template = $this->fixedTemplate();

        $students = $studentId
            ? collect([$this->studentById($studentId)])->filter()
            : $this->studentsForClass($classId);

        if ($students->isEmpty()) {
            return redirect()->route('marksheet', ['class_id' => $classId])->with('error', 'No students found for this class.');
        }

        $count = 0;
        foreach ($students as $student) {
            $printData = $this->buildPrintData($classId, $examId, (int) $student->id, $request);
            $snapshotPrintData = $printData;
            $snapshotPrintData['student'] = null;
            $snapshotPrintData['class'] = ['class_name' => optional($printData['class'])->class_name];
            $snapshotPrintData['exam'] = ['exam_name' => optional($printData['exam'])->exam_name];
            Marksheet::query()->updateOrCreate(
                [
                    'student_id' => (int) $student->id,
                    'class_id' => $classId,
                    'exam_id' => $examId,
                    'session_year' => $printData['session_year'],
                ],
                [
                    'snapshot' => [
                        'template_name' => $template->name,
                        'student_name' => $printData['student_meta']['student_name'],
                        'scholar_no' => $printData['student_meta']['scholar_no'],
                        'class_name' => optional($printData['class'])->class_name,
                        'exam_name' => optional($printData['exam'])->exam_name,
                        'print_data' => $snapshotPrintData,
                    ],
                    'status' => 'generated',
                ]
            );
            $count++;
        }

        return redirect()->route('marksheet', ['class_id' => $classId])->with('success', "{$count} marksheet(s) generated and saved.");
    }

    public function print(Request $request)
    {
        $marksheetId = $request->integer('marksheet_id') ?: null;
        if ($marksheetId) {
            return $this->printGenerated(Marksheet::query()->findOrFail($marksheetId), $request);
        }

        $classId = $request->integer('class_id') ?: null;
        $examId = $request->integer('exam_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;
        $template = $this->fixedTemplate();
        $printData = $this->buildPrintData($classId, $examId, $studentId, $request);
        $sheets = [compact('template', 'printData')];

        return view('backend.AcademicsModules.marksheet_print', compact('template', 'printData', 'sheets'));
    }

    public function printGenerated(Marksheet $marksheet, Request $request)
    {
        ['template' => $template, 'printData' => $printData] = $this->savedPrintSheet($marksheet, $request);
        $marksheet->forceFill(['printed_at' => now()])->save();
        $sheets = [compact('template', 'printData')];

        return view('backend.AcademicsModules.marksheet_print', compact('template', 'printData', 'sheets'));
    }

    public function bulkPrintGeneratedPage(string $marksheets, Request $request)
    {
        $ids = collect(explode(',', $marksheets))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return redirect()->route('marksheet')->with('error', 'Please select at least one generated marksheet to print.');
        }

        return $this->renderGeneratedBulkPrint($ids, $request);
    }

    public function bulkPrintGenerated(Request $request)
    {
        $validated = $request->validate([
            'marksheet_ids' => 'required|array|min:1',
            'marksheet_ids.*' => 'integer',
        ]);

        return $this->renderGeneratedBulkPrint($validated['marksheet_ids'], $request);
    }

    private function renderGeneratedBulkPrint(array $marksheetIds, Request $request)
    {
        $marksheets = Marksheet::query()
            ->whereIn('id', $marksheetIds)
            ->latest('id')
            ->get();

        if ($marksheets->isEmpty()) {
            return redirect()->route('marksheet')->with('error', 'Please select at least one generated marksheet to print.');
        }

        $sheets = $marksheets->map(fn ($marksheet) => $this->savedPrintSheet($marksheet, $request))->values();
        Marksheet::query()->whereIn('id', $marksheets->pluck('id'))->update(['printed_at' => now()]);
        $template = $this->fixedTemplate('Bulk Generated Marksheets');

        return view('backend.AcademicsModules.marksheet_print', compact('template', 'sheets'));
    }

    public function bulkDeleteGenerated(Request $request)
    {
        $validated = $request->validate([
            'marksheet_ids' => 'required|array|min:1',
            'marksheet_ids.*' => 'integer',
        ]);

        $count = Marksheet::query()
            ->whereIn('id', $validated['marksheet_ids'])
            ->delete();

        return redirect()->route('marksheet')->with('success', "{$count} generated marksheet(s) deleted.");
    }

    public function deleteGenerated(Marksheet $marksheet)
    {
        $classId = $marksheet->class_id;
        $marksheet->delete();

        return redirect()->route('marksheet', ['class_id' => $classId])->with('success', 'Generated marksheet deleted.');
    }

    private function savedPrintSheet(Marksheet $marksheet, ?Request $request = null): array
    {
        $template = $this->fixedTemplate($marksheet->snapshot['template_name'] ?? 'Generated Marksheet');
        $printData = $marksheet->snapshot['print_data'] ?? [];

        if ($marksheet->student_id && $marksheet->class_id) {
            $request = $request ?: request();
            $snapshotAttendance = $printData['attendance'] ?? '';
            $snapshotDate = $printData['date'] ?? null;
            $liveRequest = $request->duplicate(
                array_merge($request->query(), array_filter([
                    'attendance' => $request->input('attendance', $snapshotAttendance),
                    'date' => $request->input('date', $snapshotDate),
                ], fn ($value) => $value !== null)),
                $request->request->all()
            );
            $printData = $this->buildPrintData(
                (int) $marksheet->class_id,
                $marksheet->exam_id ? (int) $marksheet->exam_id : null,
                (int) $marksheet->student_id,
                $liveRequest
            );
        }

        foreach (['student', 'class', 'exam'] as $key) {
            if (isset($printData[$key]) && is_array($printData[$key])) {
                $printData[$key] = (object) $printData[$key];
            }
        }

        return compact('template', 'printData');
    }

    private function buildPrintData(?int $classId, ?int $examId, ?int $studentId, Request $request): array
    {
        $layout = $this->defaultLayoutConfig();
        $grading = $this->defaultGradingConfig();
        $logic = $this->defaultResultLogic();
        $student = $studentId ? $this->studentById($studentId) : null;
        $class = $classId ? Classes::query()->find($classId) : null;
        $exam = $examId ? Exam::query()->find($examId) : null;
        $subjects = $this->subjectsFor($classId, $studentId);
        $marks = $studentId ? $this->marksForStudent($studentId, $classId, $examId, $layout) : collect();
        $examMap = $this->marksheetExamMap($classId, $layout, $exam);
        $rollNo = $this->rollNoFor($studentId, $classId, $examId);

        $rows = [];
        $grandTotal = 0.0;
        $maxTotal = 0.0;
        $hasFailingSubject = false;

        foreach ($subjects as $subject) {
            $row = [
                'subject_id' => (int) $subject->id,
                'subject' => $this->marksheetSubjectName($subject->subject_name ?? $subject->name ?? '-'),
                'special_subject_formatting' => !empty($subject->do_not_print_in_main_scholastic_area),
                'terms' => [],
                'grand_total' => 0,
                'grade' => '',
                'result' => '',
            ];

            foreach ($layout['scholastic_terms'] as $termIndex => $term) {
                $termExam = $this->examForTerm($term, $termIndex, $examMap, $exam);
                $ptExam = $this->ptExamForTerm($term, $termIndex, $examMap);
                $termExamId = $termExam?->id;
                $ptExamId = $ptExam?->id;
                $mark = $marks->first(function ($item) use ($subject, $termExamId) {
                    return (int) ($item->subject_id ?? 0) === (int) $subject->id
                        && (!$termExamId || (int) ($item->exam_id ?? 0) === $termExamId);
                });
                $ptMark = $marks->first(function ($item) use ($subject, $ptExamId) {
                    return (int) ($item->subject_id ?? 0) === (int) $subject->id
                        && $ptExamId
                        && (int) ($item->exam_id ?? 0) === (int) $ptExamId;
                });

                $termTotal = 0.0;
                $termValues = $this->cbseTermValues($mark, $ptMark, $ptExam);
                $cells = [];
                foreach (($term['columns'] ?? []) as $column) {
                    $value = $this->termColumnValue($column['source'] ?? null, $mark, $termValues);
                    $cells[] = $value;
                    if (($column['counts_in_total'] ?? false) && is_numeric($value)) {
                        $termTotal += (float) $value;
                    }
                }
                if ($termTotal === 0.0) {
                    $termTotal = (float) $termValues['final_total'];
                }
                $termMax = $this->termMax($term);
                $termFailed = $this->isBelowFailPercent($termTotal, $termMax, $termExam);
                if (empty($row['special_subject_formatting'])) {
                    $hasFailingSubject = $hasFailingSubject || $termFailed;
                    $maxTotal += $termMax;
                    $grandTotal += $termTotal;
                }
                $row['terms'][$termIndex] = [
                    'cells' => $cells,
                    'total' => $termTotal,
                    'grade' => $this->gradeFor($termTotal, $termMax, $grading, $class->class_name ?? null, $subject->subject_type ?? null),
                    'result' => $termFailed ? 'Fail' : 'Pass',
                ];
                $row['grand_total'] += $termTotal;
            }

            $rowMax = max(1, $this->rowMax($layout));
            $rowFailed = $this->isBelowFailPercent($row['grand_total'], $rowMax, $exam);
            if (empty($row['special_subject_formatting'])) {
                $hasFailingSubject = $hasFailingSubject || $rowFailed;
            }
            $row['grade'] = $this->gradeFor($row['grand_total'], $rowMax, $grading, $class->class_name ?? null, $subject->subject_type ?? null);
            $row['result'] = $rowFailed ? 'Fail' : 'Pass';
            $rows[] = $row;
        }

        $percentageRaw = $maxTotal > 0 ? ($grandTotal / $maxTotal) * 100 : null;
        $percentage = $percentageRaw !== null ? $this->roundPercentage($percentageRaw) : null;
        $result = $percentageRaw === null
            ? ''
            : ($hasFailingSubject || $this->isBelowFailPercent($grandTotal, $maxTotal, $exam) ? 'Fail' : 'Pass');
        $remarks = $result === 'Fail'
            ? 'Needs Improvement'
            : ($logic['default_remark'] ?? 'Passed and promoted to next class.');

        return [
            'layout' => $layout,
            'grading' => $grading,
            'student' => $student,
            'student_meta' => $this->studentMeta($student),
            'class' => $class,
            'class_section' => $this->classSectionText($class, $student),
            'exam' => $exam,
            'roll_no' => $rollNo,
            'rows' => $rows,
            'grand_total' => $grandTotal,
            'percentage' => $percentage,
            'result' => $result,
            'remarks' => $remarks,
            'attendance' => $request->input('attendance', ''),
            'date' => $request->input('date', now()->format('F d, Y')),
            'session_year' => $this->resolveDynamicSessionYear($request),
        ];
    }

    private function defaultLayoutConfig(): array
    {
        return [
            'paper' => 'a4-landscape',
            'title' => 'Term - II (Annual) Examination Report Card',
            'student_fields' => ['student_name', 'mother_name', 'father_name', 'scholar_no', 'roll_no', 'date_of_birth', 'class_section'],
            'optional_fields' => ['percentage', 'attendance'],
            'separate_subject_ids' => collect(Config::get('global.marksheet_separate_subject_ids', [14, 5]))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values()
                ->all(),
            'scholastic_terms' => [
                [
                    'key' => 'term_1',
                    'label' => 'Term I',
                    'exam_type' => 'Term 1',
                    'pt_exam_type' => 'PT 1',
                    'columns' => [
                        ['label' => 'PT', 'source' => 'pt', 'max' => 5],
                        ['label' => 'NB', 'source' => 'nb', 'max' => 5],
                        ['label' => 'MAS', 'source' => 'mas', 'max' => 5],
                        ['label' => 'SEA', 'source' => 'sea', 'max' => 5],
                        ['label' => 'Theory (80)', 'source' => 'mark_theory', 'max' => 80],
                        ['label' => 'Marks obtained (100)', 'source' => 'final_total', 'max' => 100, 'counts_in_total' => true],
                    ],
                ],
                [
                    'key' => 'term_2',
                    'label' => 'Term II',
                    'exam_type' => 'Term 2',
                    'pt_exam_type' => 'PT 2',
                    'columns' => [
                        ['label' => 'PT', 'source' => 'pt', 'max' => 5],
                        ['label' => 'NB', 'source' => 'nb', 'max' => 5],
                        ['label' => 'MAS', 'source' => 'mas', 'max' => 5],
                        ['label' => 'SEA', 'source' => 'sea', 'max' => 5],
                        ['label' => 'Theory (80)', 'source' => 'mark_theory', 'max' => 80],
                        ['label' => 'Marks obtained (100)', 'source' => 'final_total', 'max' => 100, 'counts_in_total' => true],
                    ],
                ],
            ],
            'co_scholastic_sections' => [
                ['title' => 'Co-Scholastic Areas: Term-I [on a 3-point (A-C) grading scale]', 'items' => ['Discipline', 'Work Education', 'Art Education', 'Health And Physical Education']],
                ['title' => 'Co-Scholastic Areas: Term-II [on a 3-point (A-C) grading scale]', 'items' => ['Discipline', 'Work Education', 'Art Education', 'Health And Physical Education']],
            ],
            'abbreviations' => 'PT-Periodic Test, NB-Notebook, MAS-Multiple Assessment Strategy, SEA-Subject Enrichment Activity',
            'signature_labels' => [
                'class_teacher' => 'Signature of Class Teacher',
                'principal' => 'Signature of Principal',
                'authorized_signatory' => 'Authorized Signatory',
            ],
        ];
    }

    private function defaultGradingConfig(): array
    {
        return [
            'scale' => [
                ['grade' => 'A1', 'min' => 91, 'max' => 100],
                ['grade' => 'A2', 'min' => 81, 'max' => 90],
                ['grade' => 'B1', 'min' => 71, 'max' => 80],
                ['grade' => 'B2', 'min' => 61, 'max' => 70],
                ['grade' => 'C1', 'min' => 51, 'max' => 60],
                ['grade' => 'C2', 'min' => 41, 'max' => 50],
                ['grade' => 'D', 'min' => 33, 'max' => 40],
                ['grade' => 'E', 'min' => 0, 'max' => 32],
            ],
            'co_scholastic_default_grade' => 'A',
        ];
    }

    private function defaultResultLogic(): array
    {
        return [
            'pass_percentage' => 33,
            'default_remark' => 'Passed and promoted to next class.',
        ];
    }

    private function fixedTemplate(string $name = 'LVN Annual Report Card'): object
    {
        return (object) ['name' => $name];
    }

    private function studentsForClass(?int $classId)
    {
        if (!Schema::connection('dynamic')->hasTable('student_registration')) {
            return collect();
        }

        $class = $classId ? Classes::query()->find($classId) : null;

        return DB::connection('dynamic')
            ->table('student_registration')
            ->when($classId, function ($q) use ($classId, $class) {
                $q->where(function ($inner) use ($classId, $class) {
                    $inner->where('class_id', (string) $classId);
                    if ($class && !empty($class->class_name)) {
                        $inner->orWhere('class_name', $class->class_name);
                    }
                });
            })
            ->orderBy('student_name')
            ->limit(300)
            ->get();
    }

    private function studentById(int $studentId)
    {
        if (!Schema::connection('dynamic')->hasTable('student_registration')) {
            return null;
        }

        return DB::connection('dynamic')->table('student_registration')->where('id', $studentId)->first();
    }

    private function subjectsFor(?int $classId, ?int $studentId)
    {
        $class = $classId ? Classes::query()->find($classId) : null;
        $subjectColumns = $this->marksheetSubjectColumns();

        if ($studentId && Schema::connection('dynamic')->hasTable('subject_assign_student') && Schema::connection('dynamic')->hasTable('combination_subject')) {
            $assigned = DB::connection('dynamic')
                ->table('subject_assign_student as sas')
                ->join('combination_subject as cs', 'cs.subject_combination_id', '=', 'sas.assign_this_combtoall')
                ->join('subjectmaster as s', 's.id', '=', 'cs.subject_id')
                ->where(function ($q) use ($studentId) {
                    $q->where('sas.students_details', (string) $studentId)
                        ->orWhere('sas.students_details', $studentId)
                        ->orWhereJsonContains('sas.students_details', (string) $studentId)
                        ->orWhereJsonContains('sas.students_details', $studentId);
                })
                ->when($class && !empty($class->class_name), fn ($q) => $q->where('sas.class_name', $class->class_name))
                ->where(function ($q) {
                    $q->where('sas.is_delete', 0)->orWhereNull('sas.is_delete');
                })
                ->select($subjectColumns)
                ->distinct()
                ->orderBy('cs.subject_order')
                ->orderBy('s.subject_name')
                ->get();

            if ($assigned->count() > 0) {
                return $assigned;
            }
        }

        if ($classId && Schema::connection('dynamic')->hasTable('academic_class_subject')) {
            $subjects = DB::connection('dynamic')
                ->table('academic_class_subject as acs')
                ->join('subjectmaster as s', 's.id', '=', 'acs.subject_id')
                ->whereIn('acs.class_id', $this->marksClassIdsFor($classId))
                ->select($subjectColumns)
                ->orderBy('s.subject_name')
                ->get();
            if ($subjects->count() > 0) {
                return $subjects;
            }
        }

        if (Schema::connection('dynamic')->hasTable('subjectmaster')) {
            return DB::connection('dynamic')
                ->table('subjectmaster')
                ->where(function ($q) {
                    if (Schema::connection('dynamic')->hasColumn('subjectmaster', 'is_delete')) {
                        $q->where('is_delete', 0)->orWhereNull('is_delete');
                    }
                })
                ->select(
                    'id',
                    'subject_name',
                    'subject_type',
                    Schema::connection('dynamic')->hasColumn('subjectmaster', 'do_not_print_in_main_scholastic_area')
                        ? 'do_not_print_in_main_scholastic_area'
                        : DB::raw('0 as do_not_print_in_main_scholastic_area')
                )
                ->orderBy('subject_name')
                ->get();
        }

        return collect();
    }

    private function marksheetSubjectColumns(): array
    {
        $columns = ['s.id', 's.subject_name', 's.subject_type'];

        $columns[] = Schema::connection('dynamic')->hasColumn('subjectmaster', 'do_not_print_in_main_scholastic_area')
            ? 's.do_not_print_in_main_scholastic_area'
            : DB::raw('0 as do_not_print_in_main_scholastic_area');

        return $columns;
    }

    private function marksForStudent(int $studentId, ?int $classId, ?int $examId, array $layout)
    {
        if (!Schema::connection('dynamic')->hasTable('academic_students_marks') || !Schema::connection('dynamic')->hasTable('previosly_saved_marks_entry')) {
            return collect();
        }

        $examIds = collect($layout['scholastic_terms'] ?? [])
            ->pluck('exam_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        if ($examId) {
            $examIds[] = $examId;
        }
        if ($classId && Schema::connection('dynamic')->hasTable('academic_exam')) {
            $examIds = array_merge($examIds, DB::connection('dynamic')
                ->table('academic_exam')
                ->where('class_id', $classId)
                ->whereIn('exam_type', ['PT 1', 'PT-1', 'PT 2', 'PT-2', 'Term 1', 'Term-1', 'Term 2', 'Term-2'])
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all());
        }
        $examIds = array_values(array_unique(array_filter($examIds)));

        $query = DB::connection('dynamic')
            ->table('academic_students_marks as sm')
            ->join('previosly_saved_marks_entry as me', 'me.id', '=', 'sm.marks_id')
            ->where('sm.student_id', (string) $studentId)
            ->when($classId, fn ($q) => $q->whereIn('me.class_id', array_map('strval', $this->marksClassIdsFor($classId))))
            ->when(count($examIds) > 0, fn ($q) => $q->whereIn('me.exam_id', array_map('strval', $examIds)));

        if (Schema::connection('dynamic')->hasColumn('academic_students_marks', 'is_delete')) {
            $query->where(function ($q) {
                $q->where('sm.is_delete', 0)->orWhereNull('sm.is_delete');
            });
        }

        if (Schema::connection('dynamic')->hasColumn('previosly_saved_marks_entry', 'is_delete')) {
            $query->where(function ($q) {
                $q->where('me.is_delete', 0)->orWhereNull('me.is_delete');
            });
        }

        return $query->select('sm.*', 'me.exam_id', 'me.subject_id', 'me.class_id')->get();
    }

    private function marksheetExamMap(?int $classId, array $layout, $selectedExam)
    {
        $examIds = collect($layout['scholastic_terms'] ?? [])
            ->flatMap(fn ($term) => array_filter([$term['exam_id'] ?? null, $term['pt_exam_id'] ?? null]))
            ->map(fn ($id) => (int) $id)
            ->values();

        $query = Exam::query();
        if ($examIds->isNotEmpty()) {
            $query->whereIn('id', $examIds);
        } else {
            $query->when($classId, fn ($q) => $q->where('class_id', $classId))
                ->whereIn('exam_type', ['PT 1', 'PT-1', 'PT 2', 'PT-2', 'Term 1', 'Term-1', 'Term 2', 'Term-2']);
        }

        $exams = $query->get();
        if ($selectedExam && !$exams->contains('id', $selectedExam->id)) {
            $exams->push($selectedExam);
        }

        return $exams->keyBy(fn ($exam) => $this->normalizeExamType($exam->exam_type));
    }

    private function examForTerm(array $term, int $termIndex, $examMap, $selectedExam)
    {
        if (!empty($term['exam_id'])) {
            return Exam::query()->find((int) $term['exam_id']);
        }

        $termType = $term['exam_type'] ?? ($termIndex === 0 ? 'Term 1' : 'Term 2');
        $normalizedTermType = $this->normalizeExamType($termType);
        if ($selectedExam && $this->normalizeExamType($selectedExam->exam_type) === $normalizedTermType) {
            return $selectedExam;
        }

        return $examMap->get($normalizedTermType);
    }

    private function ptExamForTerm(array $term, int $termIndex, $examMap)
    {
        if (!empty($term['pt_exam_id'])) {
            return Exam::query()->find((int) $term['pt_exam_id']);
        }

        $ptType = $term['pt_exam_type'] ?? ($termIndex === 0 ? 'PT 1' : 'PT 2');

        return $examMap->get($this->normalizeExamType($ptType));
    }

    private function normalizeExamType(?string $type): string
    {
        return Str::lower(str_replace([' ', '-'], '', (string) $type));
    }

    private function marksClassIdsFor(?int $classId): array
    {
        if (!$classId) {
            return [];
        }

        $ids = [$classId];
        $className = Classes::query()->where('id', $classId)->value('class_name');
        if ($className && Schema::connection('dynamic')->hasTable('class_name')) {
            $marksClassId = Classname::query()->where('class_name', $className)->value('id');
            if ($marksClassId) {
                $ids[] = (int) $marksClassId;
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    private function rollNoFor(?int $studentId, ?int $classId, ?int $examId): string
    {
        if (!$studentId || !Schema::connection('dynamic')->hasTable('academic_student_roll_no')) {
            return $this->studentRegistrationRollNo($studentId);
        }

        $className = $classId ? Classes::query()->where('id', $classId)->value('class_name') : null;

        $row = DB::connection('dynamic')
            ->table('academic_student_roll_no')
            ->where('student_id', (string) $studentId)
            ->when($classId || $className, function ($q) use ($classId, $className) {
                $q->where(function ($inner) use ($classId, $className) {
                    if ($classId) {
                        $inner->where('class_id', (string) $classId);
                    }
                    if ($className) {
                        $inner->orWhere('class_id', $className);
                    }
                });
            })
            ->when($examId, function ($q) use ($examId) {
                $q->where(function ($inner) use ($examId) {
                    $inner->where('exam_id', (string) $examId)
                        ->orWhereNull('exam_id')
                        ->orWhere('exam_id', '');
                });
            })
            ->latest('id')
            ->first();

        return (string) ($row->roll_no ?? $this->studentRegistrationRollNo($studentId));
    }

    private function studentRegistrationRollNo(?int $studentId): string
    {
        if (!$studentId || !Schema::connection('dynamic')->hasTable('student_registration')) {
            return '';
        }

        $student = DB::connection('dynamic')->table('student_registration')->where('id', $studentId)->first();

        return (string) ($student->roll_no ?? $student->roll_number ?? '');
    }

    private function classSectionText($class, $student): string
    {
        $className = $this->romanClassName((string) ($class->class_name ?? $student->class_name ?? ''));
        $sectionName = $this->studentSectionName($student);

        return trim($className . ($sectionName !== '' ? ' - ' . $sectionName : ''));
    }

    private function studentSectionName($student): string
    {
        if (!$student) {
            return '';
        }

        if (!empty($student->section_name)) {
            return (string) $student->section_name;
        }

        foreach (['json_str', 'jsondata'] as $field) {
            if (!empty($student->{$field})) {
                $decoded = json_decode($student->{$field}, true);
                if (is_array($decoded) && !empty($decoded['section_name'])) {
                    return is_array($decoded['section_name'])
                        ? (string) reset($decoded['section_name'])
                        : (string) $decoded['section_name'];
                }
            }
        }

        return '';
    }

    private function romanClassName(string $className): string
    {
        $value = trim($className);
        if ($value === '') {
            return '';
        }

        $romanMap = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        if (preg_match('/(^|\D)(1[0-2]|[1-9])(\D|$)/', $value, $match)) {
            return trim(str_replace($match[2], $romanMap[(int) $match[2]] ?? $match[2], $value));
        }

        return $value;
    }

    private function studentMeta($student): array
    {
        if (!$student) {
            return [
                'student_name' => '',
                'mother_name' => '',
                'father_name' => '',
                'scholar_no' => '',
                'date_of_birth' => '',
                'phone' => '',
            ];
        }

        $json = [];
        foreach (['json_str', 'jsondata'] as $field) {
            if (!empty($student->{$field})) {
                $decoded = json_decode($student->{$field}, true);
                if (is_array($decoded)) {
                    $json = array_merge($json, $decoded);
                }
            }
        }

        return [
            'student_name' => $student->student_name ?? '',
            'mother_name' => $this->nameWithPrefix($json, ['mothername', 'mother_name', 'Mother Name', 'mother', 'student_mother_name'], ['mothername_prefix', 'mother_name_prefix']),
            'father_name' => $this->nameWithPrefix($json, ['fathername', 'father_name', 'Father Name', 'father', 'student_father_name'], ['fathername_prefix', 'father_name_prefix']),
            'scholar_no' => $student->scholar_no ?? '',
            'date_of_birth' => $student->date_of_birth ?? '',
            'phone' => $student->phone_number ?? $student->mobile_number ?? '',
        ];
    }

    private function nameWithPrefix(array $data, array $nameKeys, array $prefixKeys): string
    {
        $prefix = '';
        foreach ($prefixKeys as $key) {
            if (!empty($data[$key])) {
                $prefix = trim((string) $data[$key]);
                break;
            }
        }

        $name = '';
        foreach ($nameKeys as $key) {
            if (!empty($data[$key])) {
                $name = trim((string) $data[$key]);
                break;
            }
        }

        return trim($prefix . ' ' . $name);
    }

    private function marksheetSubjectName(string $subjectName): string
    {
        $normalized = Str::lower(preg_replace('/\s+/', ' ', trim($subjectName)));
        $aliases = [
            'environmental studies',
            'environmental study',
            'environment studies',
            'environment study',
        ];

        return in_array($normalized, $aliases, true) ? 'EVS' : $subjectName;
    }

    private function markValue($mark, ?string $source): string
    {
        if (!$mark || !$source) {
            return '';
        }

        $aliases = [
            'total_marks' => ['total_marks', 'subject_marks'],
            'mark_theory' => ['mark_theory', 'subject_marks'],
            'mark_practical' => ['mark_practical'],
            'grade' => ['grade'],
        ];

        foreach ($aliases[$source] ?? [$source] as $field) {
            if (isset($mark->{$field}) && $mark->{$field} !== '') {
                return (string) $mark->{$field};
            }
        }

        $internalMarks = $this->internalAssessmentMarks($mark);
        if ($source && $internalMarks !== []) {
            $normalizedSource = Str::lower(str_replace([' ', '-', '_'], '', $source));
            foreach ($internalMarks as $key => $value) {
                $normalizedKey = Str::lower(str_replace([' ', '-', '_'], '', (string) $key));
                if ($normalizedKey === $normalizedSource && $value !== null && $value !== '') {
                    return (string) $value;
                }
            }
        }

        return '';
    }

    private function termColumnValue(?string $source, $mark, array $termValues): string
    {
        $source = Str::lower((string) $source);
        $map = [
            'pt' => 'pt',
            'converted_pt' => 'pt',
            'nb' => 'nb',
            'notebook' => 'nb',
            'mas' => 'mas',
            'ma/mas' => 'mas',
            'pf' => 'nb',
            'portfolio' => 'nb',
            'sea' => 'sea',
            'se' => 'sea',
            'internal_total' => 'internal_total',
            'final_total' => 'final_total',
            'total_marks' => 'final_total',
            'mark_theory' => 'theory',
            'theory' => 'theory',
        ];

        if (isset($map[$source])) {
            return $this->formatMarkValue($termValues[$map[$source]] ?? 0);
        }

        return $this->markValue($mark, $source);
    }

    private function cbseTermValues($termMark, $ptMark, $ptExam): array
    {
        $theory = $this->numericMark($this->markValue($termMark, 'mark_theory'));
        $pt = $this->convertedPtMark($ptMark, $ptExam);
        $nb = $this->internalAssessmentValue($termMark, ['NB', 'Notebook']);
        $mas = $this->internalAssessmentValue($termMark, ['MA/MAS', 'MAS', 'MA', 'Multiple Assessment']);
        $sea = $this->internalAssessmentValue($termMark, ['SE', 'SEA', 'Subject Enrichment']);
        $internalTotal = min(20, $pt + $nb + $mas + $sea);

        return [
            'theory' => $this->roundMark($theory),
            'pt' => $pt,
            'nb' => $nb,
            'mas' => $mas,
            'sea' => $sea,
            'internal_total' => $this->roundMark($internalTotal),
            'final_total' => $this->roundMark(min(100, $theory + $internalTotal)),
        ];
    }

    private function convertedPtMark($ptMark, $ptExam): float
    {
        if (!$ptMark) {
            return 0.0;
        }

        $obtained = $this->numericMark($this->markValue($ptMark, 'mark_theory'));
        if ($obtained <= 0) {
            $obtained = $this->numericMark($this->markValue($ptMark, 'total_marks'));
        }

        $max = (float) ($ptExam->max_marks_theory ?? 0);
        if ($max <= 0) {
            $max = (float) ($ptExam->max_marks ?? 0);
        }
        if ($max <= 0) {
            $max = max(1, $obtained);
        }

        return $this->roundPtMark(min(5, ($obtained / $max) * 5));
    }

    private function internalAssessmentValue($mark, array $keys): float
    {
        $internalMarks = $this->internalAssessmentMarks($mark);
        if ($internalMarks === []) {
            return 0.0;
        }

        $normalizedKeys = collect($keys)
            ->map(fn ($key) => Str::lower(str_replace([' ', '-', '_', '/'], '', (string) $key)))
            ->all();

        foreach ($internalMarks as $key => $value) {
            $normalizedKey = Str::lower(str_replace([' ', '-', '_', '/'], '', (string) $key));
            if (in_array($normalizedKey, $normalizedKeys, true)) {
                return $this->roundMark(min(5, $this->numericMark($value)));
            }
        }

        return 0.0;
    }

    private function numericMark($value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function roundMark(float $value): float
    {
        return round($value, 0, PHP_ROUND_HALF_UP);
    }

    private function roundPtMark(float $value): float
    {
        return $this->roundMark($value);
    }

    private function roundPercentage(float $value): float
    {
        return round($value, 2, PHP_ROUND_HALF_UP);
    }

    private function formatPtMarkValue(float $value): string
    {
        return $this->formatMarkValue($value);
    }

    private function formatMarkValue(float $value): string
    {
        return number_format($this->roundMark($value), 2, '.', '');
    }

    private function internalAssessmentMarks($mark): array
    {
        if (!$mark || empty($mark->internal_assessment_marks)) {
            return [];
        }

        if (is_array($mark->internal_assessment_marks)) {
            return $mark->internal_assessment_marks;
        }

        $decoded = json_decode((string) $mark->internal_assessment_marks, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function termMax(array $term): float
    {
        $totalColumns = collect($term['columns'] ?? [])->filter(fn ($column) => !empty($column['counts_in_total']));
        if ($totalColumns->isNotEmpty()) {
            return $totalColumns->sum(fn ($column) => (float) ($column['max'] ?? 0));
        }

        return collect($term['columns'] ?? [])->sum(fn ($column) => (float) ($column['max'] ?? 0));
    }

    private function rowMax(array $layout): float
    {
        return collect($layout['scholastic_terms'] ?? [])->sum(fn ($term) => $this->termMax($term));
    }

    private function isBelowFailPercent(float $marks, float $max, $exam = null): bool
    {
        if ($max <= 0) {
            return false;
        }

        return (($marks / $max) * 100) < $this->failPercentFor($exam);
    }

    private function failPercentFor($exam = null): float
    {
        $failPercent = (float) ($exam->fail_percent ?? 0);

        return $failPercent > 0 ? $failPercent : 33.0;
    }

    private function gradeFor(float $marks, float $max, array $grading, ?string $className = null, ?string $subjectType = null): string
    {
        if ($max <= 0) {
            return '';
        }

        $percentage = ($marks / $max) * 100;
        $dynamicGrade = $this->gradeFromMaster($percentage, $className, $subjectType);
        if ($dynamicGrade !== '') {
            return $dynamicGrade;
        }

        foreach (($grading['scale'] ?? []) as $range) {
            if ($percentage >= (float) ($range['min'] ?? 0) && $percentage <= (float) ($range['max'] ?? 100)) {
                return (string) ($range['grade'] ?? '');
            }
        }

        return '';
    }

    private function gradeFromMaster(float $marks, ?string $className, ?string $subjectType): string
    {
        if (!Schema::connection('dynamic')->hasTable('grademaster')) {
            return '';
        }

        $subjectType = trim((string) $subjectType);
        if ($subjectType === '') {
            return '';
        }

        $range = DB::connection('dynamic')->table('grademaster')
            ->where('is_delete', 0)
            ->whereRaw('LOWER(TRIM(subject_type)) = ?', [strtolower($subjectType)])
            ->whereRaw('CAST(min_per AS DECIMAL(10,2)) <= ?', [$marks])
            ->whereRaw('CAST(max_per AS DECIMAL(10,2)) >= ?', [$marks])
            ->orderByRaw('CAST(min_per AS DECIMAL(10,2)) DESC')
            ->get()
            ->first(function ($item) use ($className) {
                $classes = collect(json_decode($item->groups ?? '[]', true) ?: []);
                return $classes->isEmpty() || $classes->contains($className);
            });

        return (string) ($range->grade ?? '');
    }

    private function resolveDynamicSessionYear(Request $request): ?string
    {
        $year = $request->session()->get('selectedYear') ?: $request->cookie('selectedYear');
        $year = $year ?: Config::get('database.connections.dynamic.database');
        if (is_string($year) && preg_match('/^\d{4}_\d{4}$/', $year)) {
            return $year;
        }

        return null;
    }
}
