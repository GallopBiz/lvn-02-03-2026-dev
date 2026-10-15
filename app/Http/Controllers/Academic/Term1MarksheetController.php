<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Exam;
use App\Models\Academic\Marksheet;
use App\Models\Classes;
use App\Models\Classname;
use App\Models\Holidays;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Term1MarksheetController extends Controller
{
    public function index(Request $request)
    {
        $generatedSearch = trim((string) $request->query('generated_search', ''));
        $generatedClassName = $request->query('generated_class_name') ?: null;
        $generatedClassId = $request->integer('generated_class_id') ?: null;
        $generatedExamId = $request->integer('generated_exam_id') ?: null;

        $filterExamIds = [];
        if ($generatedExamId) {
            $filterExamIds = [$generatedExamId];
            $selExam = Exam::find($generatedExamId);
            if ($selExam && !empty($selExam->exam_group_id)) {
                $filterExamIds = Exam::where('exam_group_id', $selExam->exam_group_id)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }
        }

        $generatedMarksheets = Marksheet::query()
            ->where(function ($query) {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.marksheet_type')) = 'term_1'")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.print_data.marksheet_type')) = 'term_1'");
            })
            ->when($generatedClassId, fn ($query) => $query->where('class_id', $generatedClassId))
            ->when($generatedClassName, function ($query) use ($generatedClassName) {
                $like = '%' . $generatedClassName . '%';
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.class_name')) LIKE ?", [$like]);
            })
            ->when(!empty($filterExamIds), fn ($query) => $query->whereIn('exam_id', $filterExamIds))
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
            ->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.student_name')) ASC")
            ->paginate(20)
            ->appends($request->only('generated_search', 'generated_class_name', 'generated_class_id', 'generated_exam_id', 'class_name', 'section_name', 'exam_id'));

        // 1. Fetch actual available classes (master list for filters)
        $classes = Classname::where('is_delete', 0)
            ->whereNotIn('class_name', ['Test', 'testing', '12th out', 'xyz'])
            ->orderBy('id')
            ->get(['id', 'class_name']);

        // 2. Sections mapped by class
        $sectionsByClass = Classes::query()
            ->where(function ($q) {
                if (Schema::connection('dynamic')->hasColumn('classes', 'is_delete')) {
                    $q->where('is_delete', 0)->orWhereNull('is_delete');
                }
            })
            ->whereNotNull('section_name')
            ->where('section_name', '!=', '')
            ->whereNotIn('section_name', ['testing', 'test Section', '12th out section'])
            ->get()
            ->groupBy('class_name')
            ->map(function ($items) {
                return $items->pluck('section_name')->unique()->values();
            })
            ->toArray();

        // 3. Exams grouped so each exam appears ONLY ONCE
        $rawExams = Exam::with('classInfo')
            ->where(function ($q) {
                $q->whereIn('exam_type', ['PT 1', 'PT-1', 'Term 1', 'Term-1', 'Half Yearly', 'Half-Yearly'])
                    ->orWhere('exam_name', 'like', '%PT 1%')
                    ->orWhere('exam_name', 'like', '%Term 1%')
                    ->orWhere('exam_name', 'like', '%Half%');
            })
            ->latest('id')
            ->get();

        if ($rawExams->isEmpty()) {
            $rawExams = Exam::with('classInfo')->latest('id')->get();
        }

        $groupedExams = $rawExams->groupBy(function ($exam) {
            return $exam->exam_group_id ?: ($exam->exam_name . '|' . $exam->exam_type);
        })->map(function ($group) {
            $first = $group->first();

            $classesData = [];
            foreach ($group as $examItem) {
                $cName = optional($examItem->classInfo)->class_name;
                if ($cName) {
                    $classesData[] = [
                        'exam_id' => (int) $examItem->id,
                        'class_id' => (int) $examItem->class_id,
                        'class_name' => (string) $cName,
                        'display_name' => (string) $cName,
                    ];
                }
            }

            $uniqueClasses = collect($classesData)->unique('class_name')->values()->all();
            usort($uniqueClasses, function ($a, $b) {
                return strnatcasecmp($a['class_name'], $b['class_name']);
            });

            return [
                'id' => (int) $first->id,
                'group_key' => (string) ($first->exam_group_id ?: $first->id),
                'exam_name' => (string) $first->exam_name,
                'exam_title' => (string) ($first->exam_title ?: $first->exam_name),
                'exam_type' => (string) ($first->exam_type ?: ''),
                'display_name' => (string) ($first->exam_name . ($first->exam_type ? ' - ' . $first->exam_type : '')),
                'classes' => $uniqueClasses,
                'all_exam_ids' => $group->pluck('id')->map(fn ($id) => (int) $id)->all(),
            ];
        })->values();

        $selectedExamId = $request->integer('exam_id') ?: null;
        $selectedClassName = $request->input('class_name') ?: null;
        $selectedSectionName = $request->input('section_name') ?: null;
        $selectedStudentId = $request->integer('student_id') ?: null;

        $selectedGroup = null;
        if ($selectedExamId) {
            $selectedGroup = $groupedExams->first(function ($g) use ($selectedExamId) {
                return (int) $g['id'] === (int) $selectedExamId || in_array((int) $selectedExamId, $g['all_exam_ids'], true);
            });
            if ($selectedGroup) {
                $selectedExamId = $selectedGroup['id'];
            }
        }

        $availableClasses = $selectedGroup ? $selectedGroup['classes'] : [];

        // If a class was requested, verify it exists for the selected exam
        if ($selectedGroup && $selectedClassName) {
            $isValidClass = collect($availableClasses)->contains(function ($c) use ($selectedClassName) {
                return (string) $c['class_name'] === (string) $selectedClassName
                    || ltrim((string) $c['class_name'], '0') === ltrim((string) $selectedClassName, '0');
            });
            if (!$isValidClass) {
                $selectedClassName = null;
                $selectedSectionName = null;
            }
        }

        $sections = collect();
        if ($selectedClassName) {
            $sections = collect($sectionsByClass[$selectedClassName] ?? ($sectionsByClass[ltrim($selectedClassName, '0')] ?? []));
        }

        $students = $selectedClassName
            ? $this->studentsForClass($selectedClassName, $selectedSectionName)
            : collect();

        // Pass groupedExams as exams as well for backwards compatibility
        $exams = $groupedExams;

        return view('backend.AcademicsModules.term1_marksheet', compact(
            'generatedMarksheets',
            'generatedSearch',
            'generatedClassName',
            'generatedClassId',
            'generatedExamId',
            'classes',
            'availableClasses',
            'sections',
            'sectionsByClass',
            'exams',
            'groupedExams',
            'students',
            'selectedClassName',
            'selectedSectionName',
            'selectedExamId',
            'selectedStudentId'
        ));
    }

    public function getStudentsJson(Request $request)
    {
        $className = $request->input('class_name');
        $sectionName = $request->input('section_name');

        if (!$className) {
            return response()->json([]);
        }

        $students = $this->studentsForClass($className, $sectionName);

        $data = $students->map(function ($s) {
            return [
                'id' => $s->id,
                'student_name' => $s->student_name,
                'scholar_no' => $s->scholar_no,
                'section_name' => $s->section_name ?? '',
                'class_id' => $s->class_id,
            ];
        });

        return response()->json($data);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'class_name' => 'nullable|string',
            'section_name' => 'nullable|string',
            'class_id' => 'nullable|integer',
            'exam_id' => 'nullable|integer',
            'resolved_exam_id' => 'nullable|integer',
            'student_id' => 'nullable|integer',
            'remarks' => 'nullable|string|max:255',
        ]);

        $className = $request->input('class_name') ?: null;
        $sectionName = $request->input('section_name') ?: null;
        $classId = !empty($validated['class_id']) ? (int) $validated['class_id'] : null;
        $examId = !empty($validated['resolved_exam_id']) ? (int) $validated['resolved_exam_id'] : (!empty($validated['exam_id']) ? (int) $validated['exam_id'] : null);
        $studentId = !empty($validated['student_id']) ? (int) $validated['student_id'] : null;
        $template = (object) ['name' => 'LVN Term 1 Report Card'];

        if (!$examId) {
            return redirect()->back()->withInput()->with('error', 'Please select an Exam.');
        }

        if (!$className && !$studentId) {
            return redirect()->back()->withInput()->with('error', 'Please select a Class.');
        }

        if ($className) {
            $classExam = $this->resolveExamForClass($examId, $classId, $className);
            if (!$classExam) {
                $examObj = Exam::find($examId);
                $examName = $examObj ? $examObj->exam_name : 'The selected exam';
                return redirect()->back()->withInput()->with('error', "{$examName} is not available for {$className}.");
            }
        }

        $students = $studentId
            ? collect([$this->studentById($studentId)])->filter()
            : $this->studentsForClass($className, $sectionName, $classId);

        if ($students->isEmpty()) {
            return redirect()->route('academic.term1-marksheet.index', array_filter(['class_name' => $className, 'section_name' => $sectionName, 'exam_id' => $examId]))
                ->with('error', 'No students found for this class / section.');
        }

        $count = 0;
        foreach ($students as $student) {
            $studentClassId = (int) ($student->class_id ?: ($classId ?: $this->resolveClassIdForStudent($student)));
            $targetExam = $this->resolveExamForClass($examId, $studentClassId, $className ?: optional($student)->class_name);
            $studentExamId = $targetExam ? (int) $targetExam->id : $examId;

            $printData = $this->buildPrintData($studentClassId, $studentExamId, (int) $student->id, $request);
            $snapshotPrintData = $printData;
            $snapshotPrintData['student'] = null;
            $snapshotPrintData['class'] = ['class_name' => optional($printData['class'])->class_name];
            $snapshotPrintData['exam'] = ['exam_name' => optional($printData['exam'])->exam_name];

            Marksheet::query()->updateOrCreate(
                [
                    'student_id' => (int) $student->id,
                    'class_id' => $studentClassId,
                    'exam_id' => $studentExamId,
                    'session_year' => $printData['session_year'],
                ],
                [
                    'snapshot' => [
                        'template_name' => $template->name,
                        'student_name' => $printData['student_meta']['student_name'],
                        'scholar_no' => $printData['student_meta']['scholar_no'],
                        'class_name' => optional($printData['class'])->class_name,
                        'exam_name' => optional($printData['exam'])->exam_name,
                        'marksheet_type' => 'term_1',
                        'print_data' => $snapshotPrintData,
                    ],
                    'status' => 'generated',
                ]
            );
            $count++;
        }

        return redirect()->route('academic.term1-marksheet.index', array_filter(['class_name' => $className, 'section_name' => $sectionName, 'exam_id' => $examId]))
            ->with('success', "{$count} Term 1 Report Card(s) generated and saved successfully.");
    }

    public function print(Request $request)
    {
        $marksheetId = $request->integer('marksheet_id') ?: null;
        if ($marksheetId) {
            return $this->printGenerated(Marksheet::query()->findOrFail($marksheetId), $request);
        }

        $className = $request->input('class_name') ?: null;
        $sectionName = $request->input('section_name') ?: null;
        $classId = $request->integer('class_id') ?: null;
        $examId = $request->integer('resolved_exam_id') ?: ($request->integer('exam_id') ?: null);
        $studentId = $request->integer('student_id') ?: null;
        $template = (object) ['name' => 'Term 1 Report Card'];

        if (!$examId) {
            return redirect()->route('academic.term1-marksheet.index', array_filter(['class_name' => $className, 'section_name' => $sectionName]))
                ->with('error', 'Please select an Exam first.');
        }

        if (!$className && !$studentId && !$classId) {
            return redirect()->route('academic.term1-marksheet.index', array_filter(['exam_id' => $examId]))
                ->with('error', 'Please select a Class first.');
        }

        if ($className) {
            $classExam = $this->resolveExamForClass($examId, $classId, $className);
            if (!$classExam) {
                $examObj = Exam::find($examId);
                $examName = $examObj ? $examObj->exam_name : 'The selected exam';
                return redirect()->route('academic.term1-marksheet.index', array_filter(['exam_id' => $examId]))
                    ->with('error', "{$examName} is not available for {$className}.");
            }
        }

        if ($studentId) {
            $student = $this->studentById($studentId);
            $studentClassId = (int) ($student->class_id ?? ($classId ?: $this->resolveClassIdForStudent($student)));
            $targetExam = $this->resolveExamForClass($examId, $studentClassId, $className ?: optional($student)->class_name);
            $studentExamId = $targetExam ? (int) $targetExam->id : $examId;

            $printData = $this->buildPrintData($studentClassId, $studentExamId, $studentId, $request);
            $sheets = [compact('template', 'printData')];
        } elseif ($className || $classId) {
            $students = $this->studentsForClass($className, $sectionName, $classId);
            if ($students->isEmpty()) {
                return redirect()->route('academic.term1-marksheet.index', array_filter(['class_name' => $className, 'section_name' => $sectionName, 'exam_id' => $examId]))
                    ->with('error', 'No students found for this class / section.');
            }
            $sheets = $students->map(function ($student) use ($classId, $className, $examId, $request, $template) {
                $studentClassId = (int) ($student->class_id ?: ($classId ?: $this->resolveClassIdForStudent($student)));
                $targetExam = $this->resolveExamForClass($examId, $studentClassId, $className ?: optional($student)->class_name);
                $studentExamId = $targetExam ? (int) $targetExam->id : $examId;

                $printData = $this->buildPrintData($studentClassId, $studentExamId, (int) $student->id, $request);
                return compact('template', 'printData');
            })->all();
            $printData = $sheets[0]['printData'] ?? [];
        } else {
            $targetExam = $this->resolveExamForClass($examId, $classId, $className);
            $studentExamId = $targetExam ? (int) $targetExam->id : $examId;
            $printData = $this->buildPrintData($classId, $studentExamId, null, $request);
            $sheets = [compact('template', 'printData')];
        }

        return view('backend.AcademicsModules.term1_marksheet_print', compact('template', 'printData', 'sheets'));
    }

    public function printGenerated(Marksheet $marksheet, ?Request $request = null)
    {
        $request = $request ?: request();
        ['template' => $template, 'printData' => $printData] = $this->savedPrintSheet($marksheet, $request);
        $marksheet->forceFill(['printed_at' => now()])->save();
        $sheets = [compact('template', 'printData')];

        return view('backend.AcademicsModules.term1_marksheet_print', compact('template', 'printData', 'sheets'));
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
            return redirect()->route('academic.term1-marksheet.index')
                ->with('error', 'Please select at least one generated report card to print.');
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
            ->get()
            ->sortBy(function ($marksheet) {
                return strtolower($marksheet->snapshot['student_name'] ?? $marksheet->snapshot['print_data']['student_meta']['student_name'] ?? '');
            }, SORT_NATURAL)
            ->values();

        if ($marksheets->isEmpty()) {
            return redirect()->route('academic.term1-marksheet.index')
                ->with('error', 'Please select at least one generated report card to print.');
        }

        $sheets = $marksheets->map(fn ($marksheet) => $this->savedPrintSheet($marksheet, $request))->values();
        Marksheet::query()->whereIn('id', $marksheets->pluck('id'))->update(['printed_at' => now()]);
        $template = (object) ['name' => 'Bulk Term 1 Report Cards'];

        return view('backend.AcademicsModules.term1_marksheet_print', compact('template', 'sheets'));
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

        return redirect()->route('academic.term1-marksheet.index')
            ->with('success', "{$count} generated report card(s) deleted.");
    }

    public function deleteGenerated(Marksheet $marksheet)
    {
        $classId = $marksheet->class_id;
        $marksheet->delete();

        return redirect()->route('academic.term1-marksheet.index', ['class_id' => $classId])
            ->with('success', 'Generated Term 1 report card deleted.');
    }

    private function savedPrintSheet(Marksheet $marksheet, ?Request $request = null): array
    {
        $template = (object) ['name' => $marksheet->snapshot['template_name'] ?? 'Term 1 Report Card'];
        $printData = $marksheet->snapshot['print_data'] ?? [];

        if ($marksheet->student_id && $marksheet->class_id) {
            $request = $request ?: request();
            $snapshotAttendance = $printData['attendance'] ?? '';
            // If saved snapshot has placeholder '/' or empty attendance, don't pass it as override so buildPrintData recalculates live attendance
            $explicitAttendance = $request->input('attendance');
            if ($explicitAttendance === null) {
                $explicitAttendance = ($snapshotAttendance !== '/' && trim((string) $snapshotAttendance) !== '')
                    ? $snapshotAttendance
                    : null;
            }

            $snapshotRemarks = $printData['remarks'] ?? '';
            $snapshotDate = $printData['date'] ?? null;
            $liveRequest = $request->duplicate(
                array_merge($request->query(), array_filter([
                    'attendance' => $explicitAttendance,
                    'remarks' => $request->input('remarks', $snapshotRemarks),
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

            // If snapshot had placeholder '/' or empty attendance and we calculated actual attendance, update saved snapshot
            if (($snapshotAttendance === '/' || empty($snapshotAttendance)) && !empty($printData['attendance']) && $printData['attendance'] !== '/') {
                $snapshot = $marksheet->snapshot;
                if (is_array($snapshot)) {
                    $snapshot['print_data']['attendance'] = $printData['attendance'];
                    $marksheet->snapshot = $snapshot;
                    $marksheet->save();
                }
            }
        }

        $printData['marksheet_type'] = 'term_1';

        foreach (['student', 'class', 'exam'] as $key) {
            if (isset($printData[$key]) && is_array($printData[$key])) {
                $printData[$key] = (object) $printData[$key];
            }
        }

        return compact('template', 'printData');
    }

    public function buildPrintData(?int $classId, ?int $examId, ?int $studentId, Request $request): array
    {
        $grading = $this->defaultGradingConfig();
        $student = $studentId ? $this->studentById($studentId) : null;
        if (!$classId && $student) {
            $classId = $this->resolveClassIdForStudent($student);
        }
        $class = $classId ? Classes::query()->find($classId) : null;
        if (!$class && $student) {
            $class = (object) [
                'id' => $classId,
                'class_name' => $student->class_name,
                'section_name' => $student->section_name ?? '',
            ];
        }
        $exam = $examId ? Exam::query()->find($examId) : null;

        $subjects = $this->subjectsFor($classId, $studentId);
        $marks = $studentId ? $this->marksForStudent($studentId, $classId, $examId) : collect();
        $examMap = $this->marksheetExamMap($classId, $exam);
        $rollNo = $this->rollNoFor($studentId, $classId, $examId);

        $scholasticRows = [];
        $gradeOnlyRows = [];
        $grandTotal = 0.0;
        $maxTotal = 0.0;
        $hasFailingSubject = false;

        $termExam = $this->termExamForTerm1($examMap, $exam);
        $ptExam = $this->ptExamForTerm1($examMap);
        $termExamId = $termExam?->id;
        $ptExamId = $ptExam?->id;

        // Separate subjects for grade only list (e.g. Computer Science, Sanskrit or items marked)
        $separateSubjectIds = collect(Config::get('global.marksheet_separate_subject_ids', [14, 5]))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        foreach ($subjects as $subject) {
            $subjectId = (int) $subject->id;
            $subjectName = $this->marksheetSubjectName($subject->subject_name ?? $subject->name ?? '-');
            $isSeparateSubject = in_array($subjectId, $separateSubjectIds, true)
                || !empty($subject->do_not_print_in_main_scholastic_area)
                || in_array(Str::lower($subject->subject_type ?? ''), ['skilled', 'optional', 'non academic'], true)
                || Str::contains(Str::lower($subjectName), ['computer', 'skill']);

            $mark = $marks->first(function ($item) use ($subjectId, $termExamId) {
                return (int) ($item->subject_id ?? 0) === $subjectId
                    && (!$termExamId || (int) ($item->exam_id ?? 0) === $termExamId);
            });

            $ptMark = $marks->first(function ($item) use ($subjectId, $ptExamId) {
                return (int) ($item->subject_id ?? 0) === $subjectId
                    && $ptExamId
                    && (int) ($item->exam_id ?? 0) === (int) $ptExamId;
            });

            $termValues = $this->cbseTermValues($mark, $ptMark, $ptExam);
            $ptVal = (float) $termValues['pt'];
            $masVal = (float) $termValues['mas'];
            $nbVal = (float) $termValues['nb'];
            $seaVal = (float) $termValues['sea'];
            $theoryVal = (float) $termValues['theory'];
            $obtainedTotal = (float) $termValues['final_total'];

            $subjectGrade = $this->gradeFor($obtainedTotal, 100, $grading, $class->class_name ?? null, $subject->subject_type ?? null);
            $termFailed = $this->isBelowFailPercent($obtainedTotal, 100, $termExam);

            $row = [
                'subject_id' => $subjectId,
                'subject' => $subjectName,
                'pt' => $this->formatMarkValue($ptVal),
                'mas' => $this->formatMarkValue($masVal),
                'portfolio' => $this->formatMarkValue($nbVal),
                'sea' => $this->formatMarkValue($seaVal),
                'theory' => $this->formatMarkValue($theoryVal),
                'obtained' => $this->formatMarkValue($obtainedTotal),
                'grade' => $subjectGrade,
                'result' => $termFailed ? 'Fail' : 'Pass',
            ];

            // Exclude dummy test subjects like te4st or test
            if (Str::startsWith(Str::lower($subjectName), 'test') || Str::contains(Str::lower($subjectName), ['te4st', 'dummy'])) {
                continue;
            }

            // Separate subjects (e.g. Computer Science, Skilled, Optional) go to Grade Only table
            if ($isSeparateSubject && !Str::contains(Str::lower($subjectName), 'sanskrit')) {
                $gradeOnlyRows[] = [
                    'subject' => $subjectName,
                    'grade' => $subjectGrade ?: 'C2',
                ];
            } else {
                $scholasticRows[] = $row;
                $hasFailingSubject = $hasFailingSubject || $termFailed;
                $maxTotal += 100;
                $grandTotal += $obtainedTotal;

                if (in_array($subjectId, [5]) || Str::contains(Str::lower($subjectName), 'sanskrit')) {
                    // In the school's sample, Sanskrit grade is also reflected in the lower table
                    $gradeOnlyRows[] = [
                        'subject' => $subjectName,
                        'grade' => $subjectGrade ?: 'C2',
                    ];
                }
            }
        }

        // Standard CBSE order for scholastic subjects
        $orderMap = [
            'english' => 1,
            'hindi' => 2,
            'sanskrit' => 3,
            'mathematics' => 4,
            'mathamatics' => 4,
            'maths' => 4,
            'science' => 5,
            'social science' => 6,
            'evs' => 7,
            'environmental studies' => 7,
        ];

        usort($scholasticRows, function ($a, $b) use ($orderMap) {
            $aKey = Str::lower(trim($a['subject']));
            $bKey = Str::lower(trim($b['subject']));
            $aOrder = $orderMap[$aKey] ?? 99;
            $bOrder = $orderMap[$bKey] ?? 99;
            if ($aOrder === $bOrder) {
                return strcmp($aKey, $bKey);
            }
            return $aOrder <=> $bOrder;
        });

        // Sort gradeOnlyRows so Sanskrit and Computer Science appear first
        usort($gradeOnlyRows, function ($a, $b) {
            $order = ['sanskrit' => 1, 'computer science' => 2];
            $aO = $order[Str::lower(trim($a['subject']))] ?? 99;
            $bO = $order[Str::lower(trim($b['subject']))] ?? 99;
            if ($aO === $bO) {
                return strcmp($a['subject'], $b['subject']);
            }
            return $aO <=> $bO;
        });

        $percentageRaw = $maxTotal > 0 ? ($grandTotal / $maxTotal) * 100 : null;
        $percentage = $percentageRaw !== null ? round($percentageRaw, 2) : null;
        $result = $percentageRaw === null ? '' : ($hasFailingSubject ? 'Fail' : 'Pass');

        // Teacher's remark
        $studentRemarks = $studentId ? $this->teacherRemarkForStudent($studentId, $termExamId) : null;
        $defaultRemark = $request->input('remarks', $studentRemarks ?: 'Needs to develop a better sense of responsibility.');

        // Attendance
        $reqAttendance = $request->input('attendance');
        $attendance = (!empty($reqAttendance) && trim($reqAttendance) !== '' && trim($reqAttendance) !== '/')
            ? $reqAttendance
            : $this->calculateTerm1Attendance(
                $studentId,
                $classId,
                $termExamId ?: $examId,
                $this->resolveDynamicSessionYear($request),
                $class->section_name ?? null
            );

        // Co-scholastic & Discipline grades
        $coScholastic = [
            ['title' => 'Work Education', 'grade' => 'B'],
            ['title' => 'Art Education', 'grade' => 'A'],
            ['title' => 'Health And Physical Education', 'grade' => 'A'],
        ];

        $discipline = [
            ['title' => 'Discipline', 'grade' => 'B'],
        ];

        return [
            'marksheet_type' => 'term_1',
            'session_year' => $this->resolveDynamicSessionYear($request),
            'student' => $student,
            'student_meta' => $this->studentMeta($student),
            'class' => $class,
            'class_roman' => $this->romanClassName((string) optional($class)->class_name),
            'section_name' => $this->studentSectionName($student, $class),
            'exam' => $termExam ?: $exam,
            'roll_no' => $rollNo,
            'scholastic_rows' => $scholasticRows,
            'grade_only_rows' => $gradeOnlyRows,
            'co_scholastic' => $coScholastic,
            'discipline' => $discipline,
            'grand_total' => $grandTotal,
            'percentage' => $percentage,
            'result' => $result,
            'remarks' => $defaultRemark,
            'attendance' => $attendance ?: '/',
            'date' => $request->input('date', now()->format('d-M-Y')),
        ];
    }

    private function termExamForTerm1($examMap, $selectedExam)
    {
        if ($selectedExam && Str::contains($this->normalizeExamType($selectedExam->exam_type), ['term1', 'halfyearly'])) {
            return $selectedExam;
        }

        foreach (['term1', 'halfyearly'] as $type) {
            if ($examMap->has($type)) {
                return $examMap->get($type);
            }
        }

        return $selectedExam;
    }

    private function ptExamForTerm1($examMap)
    {
        return $examMap->get('pt1');
    }

    private function teacherRemarkForStudent(int $studentId, ?int $examId): ?string
    {
        if (!Schema::connection('dynamic')->hasTable('academic_student_remarks')) {
            return null;
        }

        $row = DB::connection('dynamic')
            ->table('academic_student_remarks as sr')
            ->where('sr.student_id', $studentId)
            ->when($examId, function ($q) use ($examId) {
                if (Schema::connection('dynamic')->hasTable('academic_teacher_remark_entries')) {
                    $q->join('academic_teacher_remark_entries as tr', 'tr.id', '=', 'sr.teacher_remark_entry_id')
                        ->where('tr.exam_id', $examId);
                }
            })
            ->latest('sr.id')
            ->select('sr.remark_text')
            ->first();

        return $row->remark_text ?? null;
    }

    public function calculateTerm1Attendance(?int $studentId, ?int $classId, ?int $examId, ?string $sessionYear, ?string $sectionName = null): string
    {
        if (!$studentId) {
            return '/';
        }

        // 1. Check collective attendance first if non-zero
        if (Schema::connection('dynamic')->hasTable('academic_attendance_collectives') && Schema::connection('dynamic')->hasTable('academic_attendance_collective_details')) {
            $collective = DB::connection('dynamic')
                ->table('academic_attendance_collective_details as acd')
                ->join('academic_attendance_collectives as ac', 'acd.collective_id', '=', 'ac.id')
                ->where('ac.is_delete', 0)
                ->where('acd.student_id', $studentId)
                ->when($examId, fn ($q) => $q->where('ac.exam_id', $examId))
                ->orderByDesc('ac.id')
                ->select('acd.present_days', 'acd.working_days')
                ->first();

            if (!$collective) {
                $collective = DB::connection('dynamic')
                    ->table('academic_attendance_collective_details as acd')
                    ->join('academic_attendance_collectives as ac', 'acd.collective_id', '=', 'ac.id')
                    ->where('ac.is_delete', 0)
                    ->where('acd.student_id', $studentId)
                    ->orderByDesc('ac.id')
                    ->select('acd.present_days', 'acd.working_days')
                    ->first();
            }

            if ($collective && (float) $collective->present_days > 0 && (int) $collective->working_days > 0) {
                $pres = ((int) $collective->present_days == $collective->present_days) ? (int) $collective->present_days : (float) $collective->present_days;
                return "{$pres} / {$collective->working_days}";
            }
        }

        // 2. Calculate days school is open (from session start to term 1 exam conduct)
        $startYear = null;
        if ($sessionYear && preg_match('/^(\d{4})/', (string) $sessionYear, $matches)) {
            $startYear = (int) $matches[1];
        }
        if (!$startYear) {
            $startYear = (int) now()->format('Y');
        }

        // Detect if attendance or exam belongs to subsequent calendar year
        $exam = null;
        if ($examId) {
            $exam = DB::connection('dynamic')->table('academic_exam')->where('id', $examId)->first()
                ?: DB::connection('dynamic')->table('exams')->where('id', $examId)->first();
        }

        $latestAttDate = null;
        if (Schema::connection('dynamic')->hasTable('academic_attendance')) {
            $latestAttDate = DB::connection('dynamic')->table('academic_attendance')
                ->where('is_delete', 0)
                ->latest('attendance_date')
                ->value('attendance_date');
        }

        $effectiveYear = $startYear;
        if ($latestAttDate && Carbon::parse($latestAttDate)->year > $startYear) {
            $effectiveYear = Carbon::parse($latestAttDate)->year;
        } elseif ($exam && !empty($exam->created_at) && Carbon::parse($exam->created_at)->year > $startYear) {
            $effectiveYear = Carbon::parse($exam->created_at)->year;
        }

        $sessionStartDate = Carbon::create($effectiveYear, 4, 1)->startOfDay();

        // Determine Term 1 exam conduct date
        $term1ExamDate = null;
        if ($exam && !empty($exam->created_at)) {
            $examCreated = Carbon::parse($exam->created_at)->startOfDay();
            if ($examCreated->year === $effectiveYear && $examCreated->month >= 8 && $examCreated->month <= 11) {
                $term1ExamDate = $examCreated;
            }
        }

        if (!$term1ExamDate) {
            $term1ExamDate = Carbon::create($effectiveYear, 9, 30)->startOfDay();
        }

        // Total days between session start and exam conduct date
        $totalDays = $sessionStartDate->diffInDays($term1ExamDate) + 1;

        // Non-working days (Sundays and holidays)
        $nonWorkingDays = [];
        for ($d = $sessionStartDate->copy(); $d->lte($term1ExamDate); $d->addDay()) {
            if ($d->isSunday()) {
                $nonWorkingDays[$d->toDateString()] = 'Sunday';
            }
        }

        if (Schema::connection('dynamic')->hasTable('hrms_holidays')) {
            $holidays = Holidays::whereDate('HolidayStartDate', '<=', $term1ExamDate)
                ->where(function ($q) use ($sessionStartDate) {
                    $q->whereDate('HolidayEndDate', '>=', $sessionStartDate)
                      ->orWhereNull('HolidayEndDate');
                })
                ->get();

            foreach ($holidays as $h) {
                $hStart = Carbon::parse($h->HolidayStartDate);
                if ($hStart->lt($sessionStartDate)) {
                    $hStart = $sessionStartDate->copy();
                }
                $hEnd = $h->HolidayEndDate ? Carbon::parse($h->HolidayEndDate) : Carbon::parse($h->HolidayStartDate);
                if ($hEnd->gt($term1ExamDate)) {
                    $hEnd = $term1ExamDate->copy();
                }

                for ($cur = $hStart->copy(); $cur->lte($hEnd); $cur->addDay()) {
                    $nonWorkingDays[$cur->toDateString()] = 'Holiday: ' . ($h->HolidayName ?? 'Holiday');
                }
            }
        }

        $workingDays = max(1, $totalDays - count($nonWorkingDays));

        // 3. Calculate how many days student was present
        $presentDays = 0;
        if (Schema::connection('dynamic')->hasTable('academic_attendance_details') && Schema::connection('dynamic')->hasTable('academic_attendance')) {
            $presentCount = DB::connection('dynamic')
                ->table('academic_attendance_details as aad')
                ->join('academic_attendance as aa', 'aad.attendance_id', '=', 'aa.id')
                ->where('aa.is_delete', 0)
                ->where('aad.student_id', $studentId)
                ->whereBetween('aa.attendance_date', [$sessionStartDate->toDateString(), $term1ExamDate->toDateString()])
                ->whereIn('aad.status', ['present', 'half_day'])
                ->selectRaw("SUM(CASE WHEN aad.status = 'present' THEN 1 WHEN aad.status = 'half_day' THEN 0.5 ELSE 0 END) as total")
                ->value('total');

            if ($presentCount !== null) {
                $presentDays = (float) $presentCount;
            }
        }

        $formattedPresent = ((int) $presentDays == $presentDays) ? (int) $presentDays : number_format($presentDays, 1);

        return "{$formattedPresent} / {$workingDays}";
    }

    private function collectiveAttendanceForMarksheet(?int $studentId, ?int $classId, ?int $examId, ?string $sessionYear, string $fallback = '/'): string
    {
        $calculated = $this->calculateTerm1Attendance($studentId, $classId, $examId, $sessionYear);
        return ($calculated !== '/' && !empty($calculated)) ? $calculated : $fallback;
    }

    private function sessionYearVariants(?string $sessionYear): array
    {
        if (!$sessionYear) {
            return [];
        }

        $normalizedDash = str_replace('_', '-', $sessionYear);
        $normalizedUnderscore = str_replace('-', '_', $sessionYear);
        $short = null;

        if (preg_match('/^(\d{4})[-_](\d{4})$/', $sessionYear, $matches)) {
            $short = $matches[1] . '-' . substr($matches[2], -2);
        }

        return collect([$sessionYear, $normalizedDash, $normalizedUnderscore, $short])
            ->filter()
            ->unique()
            ->values()
            ->all();
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

    private function studentsForClass(?string $className = null, ?string $sectionName = null, ?int $classId = null)
    {
        if (!Schema::connection('dynamic')->hasTable('student_registration')) {
            return collect();
        }

        $class = $classId ? Classes::query()->find($classId) : null;
        if (!$className && $class) {
            $className = $class->class_name;
            if (!$sectionName) {
                $sectionName = $class->section_name;
            }
        }

        return DB::connection('dynamic')
            ->table('student_registration')
            ->where(function ($q) {
                $q->where('is_archived', 0)->orWhereNull('is_archived');
            })
            ->when($className, function ($q) use ($className) {
                $q->where(function ($inner) use ($className) {
                    $inner->where('class_name', $className)
                        ->orWhere('class_name', ltrim($className, '0'))
                        ->orWhereRaw('LOWER(class_name) = ?', [strtolower($className)]);
                });
            })
            ->when($sectionName, function ($q) use ($sectionName) {
                $q->where('section_name', $sectionName);
            })
            ->when(!$className && $classId, function ($q) use ($classId) {
                $q->where('class_id', (string) $classId);
            })
            ->orderBy('student_name')
            ->get();
    }

    private function resolveClassIdForStudent($student): ?int
    {
        if (!$student) {
            return null;
        }
        if (!empty($student->class_id)) {
            return (int) $student->class_id;
        }
        $className = $student->class_name ?? null;
        $sectionName = $student->section_name ?? null;
        if ($className) {
            $query = Classes::where('class_name', $className);
            if ($sectionName) {
                $query->where('section_name', $sectionName);
            }
            $c = $query->first();
            if ($c) {
                return (int) $c->id;
            }
        }
        return null;
    }

    private function studentById(int $studentId)
    {
        if (!Schema::connection('dynamic')->hasTable('student_registration')) {
            return null;
        }

        return DB::connection('dynamic')
            ->table('student_registration')
            ->where(function ($q) {
                $q->where('is_archived', 0)->orWhereNull('is_archived');
            })
            ->where('id', $studentId)
            ->first();
    }

    private function subjectsFor(?int $classId, ?int $studentId)
    {
        $class = $classId ? Classes::query()->find($classId) : null;
        $subjectColumns = ['s.id', 's.subject_name', 's.subject_type'];
        $subjectColumns[] = Schema::connection('dynamic')->hasColumn('subjectmaster', 'do_not_print_in_main_scholastic_area')
            ? 's.do_not_print_in_main_scholastic_area'
            : DB::raw('0 as do_not_print_in_main_scholastic_area');

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

    private function marksForStudent(int $studentId, ?int $classId, ?int $examId)
    {
        if (!Schema::connection('dynamic')->hasTable('academic_students_marks') || !Schema::connection('dynamic')->hasTable('previosly_saved_marks_entry')) {
            return collect();
        }

        $examIds = [];
        if ($examId) {
            $examIds[] = $examId;
        }

        if ($classId && Schema::connection('dynamic')->hasTable('academic_exam')) {
            $examIds = array_merge($examIds, DB::connection('dynamic')
                ->table('academic_exam')
                ->where('class_id', $classId)
                ->whereIn('exam_type', ['PT 1', 'PT-1', 'Term 1', 'Term-1', 'Half Yearly', 'Half-Yearly'])
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

    private function marksheetExamMap(?int $classId, $selectedExam)
    {
        $query = Exam::query();
        $query->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->whereIn('exam_type', ['PT 1', 'PT-1', 'Term 1', 'Term-1', 'Half Yearly', 'Half-Yearly']);

        $exams = $query->get();
        if ($selectedExam && !$exams->contains('id', $selectedExam->id)) {
            $exams->push($selectedExam);
        }

        return $exams->keyBy(fn ($exam) => $this->normalizeExamType($exam->exam_type));
    }

    private function normalizeExamType(?string $type): string
    {
        return Str::lower(str_replace([' ', '-'], '', (string) $type));
    }

    private function resolveExamForClass(?int $examId, ?int $classId = null, ?string $className = null): ?Exam
    {
        if (!$examId) {
            return null;
        }

        $exam = Exam::with('classInfo')->find($examId);
        if (!$exam) {
            return null;
        }

        // 1. If exam directly matches class_id
        if ($classId && (int) $exam->class_id === (int) $classId) {
            return $exam;
        }

        // 2. If exam directly matches class_name
        $examClassName = optional($exam->classInfo)->class_name;
        if ($className && $examClassName && (
            (string) $examClassName === (string) $className ||
            ltrim((string) $examClassName, '0') === ltrim((string) $className, '0')
        )) {
            return $exam;
        }

        // 3. Search siblings in the same exam group or with identical exam_name and exam_type
        $siblingQuery = Exam::with('classInfo')->where('id', '!=', $examId);

        if (!empty($exam->exam_group_id)) {
            $siblingQuery->where('exam_group_id', $exam->exam_group_id);
        } else {
            $siblingQuery->where('exam_name', $exam->exam_name);
            if (!empty($exam->exam_type)) {
                $siblingQuery->where('exam_type', $exam->exam_type);
            }
        }

        $siblings = $siblingQuery->get();

        foreach ($siblings as $sibling) {
            if ($classId && (int) $sibling->class_id === (int) $classId) {
                return $sibling;
            }
            $sibClassName = optional($sibling->classInfo)->class_name;
            if ($className && $sibClassName && (
                (string) $sibClassName === (string) $className ||
                ltrim((string) $sibClassName, '0') === ltrim((string) $className, '0')
            )) {
                return $sibling;
            }
        }

        // 4. If neither classId nor className was passed, return the original exam
        if (!$classId && !$className) {
            return $exam;
        }

        return null;
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
        if (!$studentId) {
            return '';
        }

        $className = $classId ? Classes::query()->where('id', $classId)->value('class_name') : null;

        if (Schema::connection('dynamic')->hasTable('academic_student_roll_no')) {
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

            if ($row && !empty($row->roll_no)) {
                return (string) $row->roll_no;
            }
        }

        $regRoll = $this->studentRegistrationRollNo($studentId);
        if (!empty($regRoll)) {
            return (string) $regRoll;
        }

        // School standard calculated roll number: (ClassCode) + (SectionDigit) + (2-digit serial)
        $student = $this->studentById($studentId);
        if ($student) {
            $classObj = $classId ? Classes::query()->find($classId) : null;
            $cName = $classObj->class_name ?? ($student->class_name ?? '');
            $sName = $classObj->section_name ?? ($student->section_name ?? '');

            $digits = (int) preg_replace('/[^0-9]/', '', (string) $cName);
            $secUpper = strtoupper(trim((string) $sName));
            $secMap = [
                'ARYABHATTA' => '1',
                'KAUTILYA' => '2',
                'RAMANUJAN' => '1',
            ];
            $secDigit = $secMap[$secUpper] ?? '1';

            $peers = DB::connection('dynamic')->table('student_registration')
                ->where(fn ($q) => $q->where('is_archived', 0)->orWhereNull('is_archived'))
                ->where(function ($q) use ($classId, $cName, $sName) {
                    $q->where('class_id', (string) $classId);
                    if (!empty($cName)) {
                        $q->orWhere(function ($sub) use ($cName, $sName) {
                            $sub->where('class_name', $cName);
                            if (!empty($sName)) {
                                $sub->where('section_name', $sName);
                            }
                        });
                    }
                })
                ->orderBy('student_name')
                ->pluck('id')
                ->all();

            $serialIdx = array_search($studentId, $peers);
            $serialNum = $serialIdx !== false ? $serialIdx + 1 : 1;

            return ($digits > 0 ? (string) ($digits + 1) : '7') . $secDigit . sprintf('%02d', $serialNum);
        }

        return '';
    }

    private function studentRegistrationRollNo(?int $studentId): string
    {
        if (!$studentId || !Schema::connection('dynamic')->hasTable('student_registration')) {
            return '';
        }

        $student = DB::connection('dynamic')->table('student_registration')
            ->where(fn ($q) => $q->where('is_archived', 0)->orWhereNull('is_archived'))
            ->where('id', $studentId)
            ->first();

        return (string) ($student->roll_no ?? $student->roll_number ?? '');
    }

    private function studentSectionName($student, $class = null): string
    {
        if (!empty($student?->section_name)) {
            return (string) $student->section_name;
        }

        if (!empty($class?->section_name)) {
            return (string) $class->section_name;
        }

        if ($student) {
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

        if (preg_match('/^0*([1-9]|1[0-2])$/', $value, $m)) {
            return $romanMap[(int) $m[1]] ?? $value;
        }

        if (preg_match('/(^|\D)0*(1[0-2]|[1-9])(\D|$)/', $value, $match)) {
            $num = (int) $match[2];
            return $romanMap[$num] ?? $value;
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

        $dobRaw = $student->date_of_birth ?? ($json['student_dob'] ?? '');
        $dobFormatted = $this->formatDobDate($dobRaw);

        return [
            'student_name' => $student->student_name ?? ($json['studentname'] ?? ''),
            'mother_name' => $this->nameWithPrefix($json, ['mothername', 'mother_name', 'Mother Name', 'mother', 'student_mother_name'], ['mothername_prefix', 'mother_name_prefix']),
            'father_name' => $this->nameWithPrefix($json, ['fathername', 'father_name', 'Father Name', 'father', 'student_father_name'], ['fathername_prefix', 'father_name_prefix']),
            'scholar_no' => $student->scholar_no ?? ($json['scholar_no'] ?? ''),
            'date_of_birth' => $dobFormatted,
            'phone' => $student->phone_number ?? $student->mobile_number ?? '',
        ];
    }

    private function formatDobDate(?string $dob): string
    {
        if (empty($dob)) {
            return '';
        }

        // Clean year if 0015 -> 2015
        if (preg_match('/^(\d{1,2})[-.\/](\d{1,2})[-.\/](00)(\d{2})$/', $dob, $m)) {
            $dob = sprintf('%02d-%02d-20%02d', $m[1], $m[2], $m[4]);
        } elseif (preg_match('/^(00)(\d{2})[-.\/](\d{1,2})[-.\/](\d{1,2})$/', $dob, $m)) {
            $dob = sprintf('20%02d-%02d-%02d', $m[2], $m[3], $m[4]);
        }

        try {
            return Carbon::parse($dob)->format('d-M-Y');
        } catch (\Throwable $e) {
            return $dob;
        }
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
            'environmental studies' => 'EVS',
            'environmental study' => 'EVS',
            'environment studies' => 'EVS',
            'environment study' => 'EVS',
            'mathamatics' => 'Mathematics',
            'maths' => 'Mathematics',
            'computer' => 'Computer Science',
        ];

        return $aliases[$normalized] ?? $subjectName;
    }

    private function cbseTermValues($termMark, $ptMark, $ptExam): array
    {
        $theory = $this->numericMark($this->markValue($termMark, 'mark_theory'));
        $pt = $this->convertedPtMark($ptMark, $ptExam);
        $nb = $this->internalAssessmentValue($termMark, ['NB', 'Notebook', 'Portfolio', 'PF']);
        $mas = $this->internalAssessmentValue($termMark, ['MA/MAS', 'MAS', 'MA', 'Multiple Assessment', 'Multiple Ass.']);
        $sea = $this->internalAssessmentValue($termMark, ['SE', 'SEA', 'Subject Enrichment']);
        $internalTotal = min(20, $pt + $nb + $mas + $sea);

        return [
            'theory' => round($theory, 2),
            'pt' => $pt,
            'nb' => $nb,
            'mas' => $mas,
            'sea' => $sea,
            'internal_total' => round($internalTotal, 2),
            'final_total' => round(min(100, $theory + $internalTotal), 2),
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

        return round(min(5, ($obtained / $max) * 5), 2);
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
            $normalizedSource = Str::lower(str_replace([' ', '-', '_', '/'], '', $source));
            foreach ($internalMarks as $key => $value) {
                $normalizedKey = Str::lower(str_replace([' ', '-', '_', '/'], '', (string) $key));
                if ($normalizedKey === $normalizedSource && $value !== null && $value !== '') {
                    return (string) $value;
                }
            }
        }

        return '';
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
                return round(min(5, $this->numericMark($value)), 2);
            }
        }

        return 0.0;
    }

    private function numericMark($value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function formatMarkValue(float $value): string
    {
        return number_format($value, 2, '.', '');
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

    private function isBelowFailPercent(float $marks, float $max, $exam = null): bool
    {
        if ($max <= 0) {
            return false;
        }

        $failPercent = (float) ($exam->fail_percent ?? 0);
        $failPercent = $failPercent > 0 ? $failPercent : 33.0;

        return (($marks / $max) * 100) < $failPercent;
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
        $year = $request->hasSession() ? $request->session()->get('selectedYear') : null;
        $year = $year ?: $request->cookie('selectedYear');
        $year = $year ?: Config::get('database.connections.dynamic.database');
        if (is_string($year) && preg_match('/^\d{4}_\d{4}$/', $year)) {
            return $year;
        }

        return '2025_2026';
    }
}
