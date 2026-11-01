<?php

namespace App\Http\Controllers\Academic;

use App\Academic\AcademicHelper;
use App\Http\Controllers\Controller;
use App\Models\Academic\Exam;
use App\Models\Academic\Marksheet;
use App\Models\Classes;
use App\Models\Classname;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SeniorMarksheetController extends Controller
{
    private array $seniorClassNames = ['11', '12', 'Class 11', 'Class 12', 'XI', 'XII', '011', '012'];
    private array $seniorClassIds = [14, 15, 41, 42, 43, 44];

    public function index(Request $request)
    {
        $reportAccess = app(\App\Services\StaffReportAccessService::class);
        $generatedSearch = trim((string) $request->query('generated_search', ''));
        $generatedClassId = $request->integer('generated_class_id') ?: null;
        $generatedExamId = $request->integer('generated_exam_id') ?: null;
        $selectedClassId = $request->integer('class_id') ?: null;
        $selectedSectionName = trim((string) $request->input('section_name', '')) ?: null;
        $selectedExamId = $request->integer('exam_id') ?: null;

        // Only Class 11 and 12
        $classes = $reportAccess->filterClassOptions(Classes::query()
            ->where(function ($q) {
                if (Schema::connection('dynamic')->hasColumn('classes', 'is_delete')) {
                    $q->where('is_delete', 0)->orWhereNull('is_delete');
                }
            })
            ->where(function ($q) {
                $q->whereIn('class_name', $this->seniorClassNames)
                    ->orWhereIn('id', $this->seniorClassIds);
            })
            ->whereNotIn('section_name', ['testing', 'test Section', '12th out section'])
            ->orderBy('class_name')
            ->get())
            ->unique('class_name')
            ->values();

        $sectionsByClass = $reportAccess->filterClassOptions(Classes::query()
            ->where(function ($q) {
                if (Schema::connection('dynamic')->hasColumn('classes', 'is_delete')) {
                    $q->where('is_delete', 0)->orWhereNull('is_delete');
                }
            })
            ->where(function ($q) {
                $q->whereIn('class_name', $this->seniorClassNames)
                    ->orWhereIn('id', $this->seniorClassIds);
            })
            ->whereNotNull('section_name')
            ->where('section_name', '!=', '')
            ->whereNotIn('section_name', ['testing', 'test Section', '12th out section'])
            ->orderBy('section_name')
            ->get())
            ->groupBy('class_name')
            ->map(function ($items) {
                return $items->pluck('section_name')->unique()->values();
            })
            ->toArray();

        // Augment sectionsByClass with Roman keys, stripped numbers, and class IDs
        $augmentedSections = [];
        foreach ($sectionsByClass as $cName => $secs) {
            $augmentedSections[$cName] = $secs;
            $stripped = ltrim((string) $cName, '0');
            $augmentedSections[$stripped] = $secs;
            $roman = AcademicHelper::toRoman((string) $cName);
            if ($roman !== '') {
                $augmentedSections[$roman] = $secs;
            }
        }
        $allSeniorClasses = Classes::query()
            ->whereIn('class_name', $this->seniorClassNames)
            ->orWhereIn('id', $this->seniorClassIds)
            ->get();
        foreach ($allSeniorClasses as $sc) {
            $cName = (string) $sc->class_name;
            $secs = $sectionsByClass[$cName] ?? ($sectionsByClass[ltrim($cName, '0')] ?? []);
            if (!empty($secs)) {
                $augmentedSections[(string) $sc->id] = $secs;
            }
        }
        $sectionsByClass = $augmentedSections;


        // Retrieve exams for Classes 11 and 12 (or all exams if shared)
        $rawExams = Exam::with('classInfo')
            ->where(function ($q) {
                $q->whereIn('class_id', $this->seniorClassIds)
                    ->orWhereNull('class_id')
                    ->orWhere('class_id', 0);
            })
            ->latest('id')
            ->get();

        if ($rawExams->isEmpty()) {
            $rawExams = Exam::with('classInfo')->latest('id')->get();
        }

        $groupedExams = $rawExams->groupBy(function ($exam) {
            return $exam->exam_group_id ?: ($exam->exam_name . '|' . $exam->exam_type);
        })->map(function ($group) use ($reportAccess) {
            $first = $group->first();

            $classesData = [];
            foreach ($group as $examItem) {
                $cName = optional($examItem->classInfo)->class_name;
                $cId = (int) $examItem->class_id;
                if (($cName || $cId) && $reportAccess->canAccess($cId ?: $cName)) {
                    $classesData[] = [
                        'exam_id' => (int) $examItem->id,
                        'class_id' => (int) $examItem->class_id,
                        'class_name' => (string) ($cName ?: ('Class ' . $cId)),
                        'display_name' => AcademicHelper::toRoman((string) ($cName ?: ('Class ' . $cId))),
                    ];
                }
            }

            $uniqueClasses = collect($classesData)->unique('class_id')->values()->all();
            usort($uniqueClasses, function ($a, $b) {
                return strnatcasecmp($a['class_name'], $b['class_name']);
            });

            $displayName = trim((string) $first->exam_name);
            $examType = trim((string) $first->exam_type);
            if ($examType !== '' && strcasecmp($displayName, $examType) !== 0) {
                $displayName .= ' - ' . $examType;
            }

            return [
                'id' => (int) $first->id,
                'group_key' => (string) ($first->exam_group_id ?: $first->id),
                'exam_name' => (string) $first->exam_name,
                'exam_title' => (string) ($first->exam_title ?: $first->exam_name),
                'exam_type' => (string) ($first->exam_type ?: ''),
                'display_name' => $displayName,
                'classes' => $uniqueClasses,
                'all_exam_ids' => $group->pluck('id')->map(fn ($id) => (int) $id)->all(),
            ];
        })->values();

        $selectedGroup = null;
        if ($selectedExamId) {
            $selectedGroup = $groupedExams->first(function ($g) use ($selectedExamId) {
                return (int) $g['id'] === (int) $selectedExamId || in_array((int) $selectedExamId, $g['all_exam_ids'], true);
            });
            if ($selectedGroup) {
                $selectedExamId = $selectedGroup['id'];
            }
        } elseif ($selectedClassId) {
            $selectedGroup = $groupedExams->first(function ($g) use ($selectedClassId) {
                return collect($g['classes'])->contains(fn ($c) => (int) $c['class_id'] === (int) $selectedClassId);
            });
            if ($selectedGroup) {
                $selectedExamId = $selectedGroup['id'];
            }
        }

        $availableClasses = $classes->map(fn ($c) => [
            'exam_id' => null,
            'class_id' => (int) $c->id,
            'class_name' => (string) $c->class_name,
            'display_name' => AcademicHelper::toRoman((string) $c->class_name),
        ])->all();

        $perPage = $request->integer('per_page', 20);
        $allowedPerPage = [10, 20, 50, 100, 200, 500];
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 20;
        }

        $filterExamIds = [];
        if ($generatedExamId) {
            $filterExamIds = [$generatedExamId];
            $matchedGenGroup = $groupedExams->first(function ($g) use ($generatedExamId) {
                return (int) $g['id'] === (int) $generatedExamId || in_array((int) $generatedExamId, $g['all_exam_ids'], true);
            });
            if ($matchedGenGroup) {
                $filterExamIds = $matchedGenGroup['all_exam_ids'];
            }
        }

        $latestMarksheetIds = $this->getLatestMarksheetIds($generatedClassId, $filterExamIds);

        $generatedMarksheets = Marksheet::query()
            ->whereIn('id', !empty($latestMarksheetIds) ? $latestMarksheetIds : [0])
            ->when($reportAccess->isStaffUser(), function ($query) use ($reportAccess) {
                $classIds = $reportAccess->filterClassOptions(Classes::query()->get())->pluck('id')->all();
                $query->whereIn('class_id', $classIds ?: [0]);
            })
            ->where(function ($query) {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.marksheet_type')) = 'senior_11_12'")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.print_data.marksheet_type')) = 'senior_11_12'");
            })
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
            ->paginate($perPage)
            ->appends($request->only('generated_search', 'generated_class_id', 'generated_exam_id', 'class_id', 'section_name', 'exam_id', 'per_page'));

        $selectedClassName = null;
        if ($selectedClassId) {
            $matchedClass = Classes::find($selectedClassId);
            if ($matchedClass) {
                $selectedClassName = $matchedClass->class_name;
            }
        }
        $availableSections = [];
        if ($selectedClassName) {
            $availableSections = $sectionsByClass[$selectedClassName] ?? ($sectionsByClass[ltrim($selectedClassName, '0')] ?? []);
        }

        $exams = $rawExams;
        $students = $this->studentsForClass($selectedClassId, $selectedClassName, $selectedSectionName);

        return view('backend.AcademicsModules.senior_marksheet', compact(
            'generatedMarksheets',
            'generatedSearch',
            'generatedClassId',
            'generatedExamId',
            'perPage',
            'classes',
            'sectionsByClass',
            'availableSections',
            'selectedSectionName',
            'exams',
            'groupedExams',
            'availableClasses',
            'students',
            'selectedClassId',
            'selectedExamId'
        ));
    }

    public function getStudentsJson(Request $request)
    {
        $classId = $request->integer('class_id') ?: null;
        $className = trim((string) $request->input('class_name', '')) ?: null;
        $sectionName = trim((string) $request->input('section_name', '')) ?: null;
        if (!$classId && !$className) {
            return response()->json([
                'students' => [],
                'has_marks' => false,
            ]);
        }

        $students = $this->studentsForClass($classId, $className, $sectionName);

        $data = $students->map(function ($s) {
            return [
                'id' => $s->id,
                'student_name' => $s->student_name,
                'scholar_no' => $s->scholar_no ?? '',
                'section_name' => $s->section_name ?? '',
                'class_id' => $s->class_id ?? '',
                'class_name' => $s->class_name ?? '',
            ];
        });

        return response()->json([
            'students' => $data,
            'has_marks' => true,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|integer',
            'section_name' => 'nullable|string|max:100',
            'exam_id' => 'nullable|integer',
            'student_id' => 'nullable|integer',
            'attendance' => 'nullable|string|max:50',
            'print_date' => 'nullable|string|max:50',
            'date' => 'nullable|string|max:50',
        ]);

        $classId = (int) $validated['class_id'];
        $sectionName = !empty($validated['section_name']) ? trim($validated['section_name']) : null;
        $examId = !empty($validated['exam_id']) ? (int) $validated['exam_id'] : null;
        $studentId = !empty($validated['student_id']) ? (int) $validated['student_id'] : null;

        $students = $studentId
            ? collect([$this->studentById($studentId)])->filter()
            : $this->studentsForClass($classId, null, $sectionName);

        if ($students->isEmpty()) {
            return redirect()->route('academic.senior-marksheets.index', array_filter(['class_id' => $classId, 'section_name' => $sectionName, 'exam_id' => $examId]))
                ->with('error', 'No students found for this class / section.');
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
                    'session_year' => $printData['session_year'],
                ],
                [
                    'exam_id' => $examId ?: 0,
                    'snapshot' => [
                        'marksheet_type' => 'senior_11_12',
                        'pt_scale' => 50,
                        'template_name' => 'Class 11-12 Report Card',
                        'student_name' => $printData['student_meta']['student_name'],
                        'scholar_no' => $printData['student_meta']['scholar_no'],
                        'class_name' => optional($printData['class'])->class_name,
                        'exam_name' => optional($printData['exam'])->exam_name ?: ((\App\Academic\AcademicHelper::normalizeClassIdentifier(optional($printData['class'])->class_name) === '12') ? 'Pre Board Examination' : 'Annual Examination'),
                        'print_date' => $printData['date'],
                        'print_data' => $snapshotPrintData,
                    ],
                    'status' => 'generated',
                ]
            );
            $count++;
        }

        return redirect()->route('academic.senior-marksheets.index', array_filter(['class_id' => $classId, 'section_name' => $sectionName, 'exam_id' => $examId]))
            ->with('success', "{$count} Class 11-12 marksheet(s) generated and saved successfully.");
    }

    public function print(Request $request)
    {
        $marksheetId = $request->integer('marksheet_id') ?: null;
        if ($marksheetId) {
            return $this->printGenerated(Marksheet::query()->findOrFail($marksheetId), $request);
        }

        $classId = $request->integer('class_id') ?: null;
        $sectionName = trim((string) $request->input('section_name', '')) ?: null;
        $examId = $request->integer('exam_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;

        if ($classId && !$studentId) {
            $students = $this->studentsForClass($classId, null, $sectionName);
            if ($students->isEmpty()) {
                $printData = $this->buildPrintData($classId, $examId, null, $request);
                $sheets = [compact('printData')];
            } else {
                $sheets = $students->map(function ($student) use ($classId, $examId, $request) {
                    $printData = $this->buildPrintData($classId, $examId, (int) $student->id, $request);
                    return compact('printData');
                })->all();
                $printData = $sheets[0]['printData'] ?? [];
            }
        } else {
            $printData = $this->buildPrintData($classId, $examId, $studentId, $request);
            $sheets = [compact('printData')];
        }

        return view('backend.AcademicsModules.senior_marksheet_print', compact('printData', 'sheets'));
    }

    public function printGenerated($marksheet, Request $request)
    {
        $marksheetModel = null;
        if ($marksheet instanceof Marksheet) {
            $marksheetModel = $marksheet;
        } elseif (is_numeric($marksheet)) {
            $marksheetModel = Marksheet::query()->find((int) $marksheet);
        }

        if ($marksheetModel) {
            $printData = $this->savedPrintSheet($marksheetModel, $request);
            $marksheetModel->forceFill(['printed_at' => now()])->save();
            $sheets = [compact('printData')];

            return view('backend.AcademicsModules.senior_marksheet_print', compact('printData', 'sheets'));
        }

        $id = is_numeric($marksheet) ? (int) $marksheet : null;
        $student = $id ? $this->studentById($id) : null;
        $classId = $request->integer('class_id') ?: ($student->class_id ?? 14);
        $examId = $request->integer('exam_id') ?: null;

        $printData = $this->buildPrintData($classId, $examId, $student ? (int) $student->id : null, $request);
        $sheets = [compact('printData')];

        return view('backend.AcademicsModules.senior_marksheet_print', compact('printData', 'sheets'));
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
            return redirect()->route('academic.senior-marksheets.index')->with('error', 'Please select at least one generated marksheet to print.');
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
            return redirect()->route('academic.senior-marksheets.index')->with('error', 'Please select at least one generated marksheet to print.');
        }

        $sheets = $marksheets->map(function ($marksheet) use ($request) {
            $printData = $this->savedPrintSheet($marksheet, $request);
            return compact('printData');
        })->values();

        Marksheet::query()->whereIn('id', $marksheets->pluck('id'))->update(['printed_at' => now()]);

        return view('backend.AcademicsModules.senior_marksheet_print', compact('sheets'));
    }

    public function bulkDeleteGenerated(Request $request)
    {
        $rawIds = $request->input('marksheet_ids');
        if (is_string($rawIds)) {
            $rawIds = explode(',', $rawIds);
        }
        $ids = collect((array) $rawIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Please select at least one generated marksheet to delete.');
        }

        Marksheet::query()->whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', 'Selected marksheets deleted successfully.');
    }

    public function deleteGenerated($marksheet, Request $request)
    {
        $id = $marksheet instanceof Marksheet ? $marksheet->id : (int) $marksheet;
        Marksheet::query()->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Marksheet deleted successfully.');
    }

    public function savedPrintSheet(Marksheet $marksheet, Request $request): array
    {
        $snapshot = $marksheet->snapshot ?? [];
        $printData = $snapshot['print_data'] ?? [];

        $studentId = (int) $marksheet->student_id;
        $classId = (int) $marksheet->class_id;
        $examId = $marksheet->exam_id ? (int) $marksheet->exam_id : null;

        if (empty($printData)) {
            return $this->buildPrintData($classId, $examId, $studentId, $request);
        }

        // Check if live marks exist when snapshot had no marks or missing annual marks
        if ($studentId && $classId) {
            $livePrintData = $this->buildPrintData($classId, $examId, $studentId, $request);
            if (app(\App\Services\StaffReportAccessService::class)->isStaffUser()) {
                $printData = $livePrintData;
            } elseif (!empty($livePrintData['has_marks']) && empty($printData['has_marks'])) {
                $printData = $livePrintData;
            } elseif (!empty($livePrintData['has_marks']) && !empty($printData['rows'])) {
                $snapshotHasAnnual = false;
                foreach ($printData['rows'] as $sr) {
                    if ((isset($sr['annual_obtained']) && $sr['annual_obtained'] !== '') ||
                        (isset($sr['annual_theory_obt']) && $sr['annual_theory_obt'] !== '') ||
                        (isset($sr['annual_practical_obt']) && $sr['annual_practical_obt'] !== '')) {
                        $snapshotHasAnnual = true;
                        break;
                    }
                }
                if (!$snapshotHasAnnual) {
                    $printData = $livePrintData;
                }
            }
        }

        // Re-hydrate dynamic student and class instances if needed
        $printData['student'] = $this->studentById($studentId);
        $printData['class'] = $classId ? Classes::find($classId) : null;

        // Ensure only subjects belonging to the student's assigned combination are displayed
        if ($studentId && $classId) {
            $combSubjects = $this->subjectsFor($classId, $studentId);
            if ($combSubjects->isNotEmpty()) {
                $allowedSubjectIds = $combSubjects->pluck('id')->map(fn ($id) => (int) $id)->all();
                $existingRowSubjectIds = array_map(fn ($r) => (int) ($r['subject_id'] ?? 0), $printData['rows'] ?? []);
                $hasExtraSubjects = !empty(array_diff($existingRowSubjectIds, $allowedSubjectIds));

                if ($hasExtraSubjects) {
                    $livePrintData = $this->buildPrintData($classId, $examId, $studentId, $request);
                    if (!empty($livePrintData['rows'])) {
                        $printData = $livePrintData;
                    } else {
                        $printData['rows'] = array_values(array_filter($printData['rows'] ?? [], function ($r) use ($allowedSubjectIds) {
                            $sid = isset($r['subject_id']) ? (int) $r['subject_id'] : null;
                            return $sid && in_array($sid, $allowedSubjectIds, true);
                        }));
                    }
                }
            }
        }

        if ($request->filled('print_date')) {
            $printData['date'] = $this->formatSeniorDate($request->input('print_date'));
        }

        if (!empty($printData['title'])) {
            $printData['title'] = preg_replace('/term\s*[-_]?\s*(ii|i|1|2)\s*(\(([^)]+)\))?\s*/i', '$3 ', $printData['title']);
            $printData['title'] = preg_replace('/\s+/', ' ', trim($printData['title']));
        }

        if ($request->filled('attendance')) {
            $printData['attendance'] = $request->input('attendance');
        } elseif (empty($printData['attendance']) || str_contains($printData['attendance'], '%')) {
            $liveAtt = $this->collectiveAttendanceForMarksheet(
                $studentId,
                $classId,
                $marksheet->exam_id ? (int) $marksheet->exam_id : null,
                $this->resolveDynamicSessionYear($request),
                $request->input('attendance', '')
            );
            if ($liveAtt !== '' && $liveAtt !== '/') {
                $printData['attendance'] = $liveAtt;
            }
        }

        // Ensure Term II percentage and total are calculated based exclusively on Term II marks and corresponding maximum marks
        $term2Calc = $this->calculateSeniorTerm2Percentage($printData);
        if ($term2Calc !== null) {
            $printData['annual_percentage'] = number_format($term2Calc['percentage'], 2, '.', '') . ' %';
            if (empty($printData['total_annual_obtained']) && $term2Calc['obtained'] > 0) {
                $printData['total_annual_obtained'] = number_format($term2Calc['obtained'], 2, '.', '');
            }
        }

        // Convert PT marks to scale of 50 if not already converted
        if (!empty($printData['rows'])) {
            $isAlreadyConverted = (!empty($snapshot['pt_scale']) && (int) $snapshot['pt_scale'] === 50)
                || (!empty($printData['pt_scale']) && (int) $printData['pt_scale'] === 50);

            if (!$isAlreadyConverted) {
                foreach ($printData['rows'] as &$sr) {
                    if (isset($sr['pt1']) && $sr['pt1'] !== '' && $sr['pt1'] !== 'AB' && is_numeric($sr['pt1'])) {
                        $sr['pt1'] = $this->convertSeniorPtMark($sr['pt1']);
                    }
                    if (isset($sr['pt2']) && $sr['pt2'] !== '' && $sr['pt2'] !== 'AB' && is_numeric($sr['pt2'])) {
                        $sr['pt2'] = $this->convertSeniorPtMark($sr['pt2']);
                    }
                    $p1 = is_numeric($sr['pt1'] ?? '') ? (float) $sr['pt1'] : 0.0;
                    $hy = is_numeric($sr['hy_obtained'] ?? '') ? (float) $sr['hy_obtained'] : 0.0;
                    if ((isset($sr['pt1']) && $sr['pt1'] !== '') || (isset($sr['hy_obtained']) && $sr['hy_obtained'] !== '')) {
                        $sr['hy_grand_total'] = (float) ($p1 + $hy);
                    }
                    $p2 = is_numeric($sr['pt2'] ?? '') ? (float) $sr['pt2'] : 0.0;
                    $an = is_numeric($sr['annual_obtained'] ?? '') ? (float) $sr['annual_obtained'] : 0.0;
                    if ((isset($sr['pt2']) && $sr['pt2'] !== '') || (isset($sr['annual_obtained']) && $sr['annual_obtained'] !== '')) {
                        $sr['annual_total'] = (float) ($p2 + $an);
                    }
                    if (isset($sr['hy_grand_total']) || isset($sr['annual_total'])) {
                        $g1 = is_numeric($sr['hy_grand_total'] ?? '') ? (float) $sr['hy_grand_total'] : 0.0;
                        $g2 = is_numeric($sr['annual_total'] ?? '') ? (float) $sr['annual_total'] : 0.0;
                        $sr['combined_grand_total'] = (float) ($g1 + $g2);
                    }
                }
                unset($sr);
                $printData['pt_scale'] = 50;
            }
        }

        return $printData;
    }

    public function calculateSeniorTerm2Percentage(array $printData): ?array
    {
        $rows = $printData['rows'] ?? [];
        if (empty($rows)) {
            return null;
        }

        $term2Obtained = 0.0;
        $term2Max = 0.0;
        $hasTerm2Marks = false;

        foreach ($rows as $row) {
            $obtained = $row['annual_obtained'] ?? '';
            if ($obtained === '' || !is_numeric($obtained)) {
                $th = $row['annual_theory_obt'] ?? '';
                $pr = $row['annual_practical_obt'] ?? '';
                if ((is_numeric($th) && $th !== '') || (is_numeric($pr) && $pr !== '')) {
                    $obtained = (float) (is_numeric($th) ? $th : 0) + (float) (is_numeric($pr) ? $pr : 0);
                }
            }

            if ($obtained !== '' && is_numeric($obtained)) {
                $term2Obtained += (float) $obtained;
                $thMax = isset($row['annual_theory_mm']) && is_numeric($row['annual_theory_mm']) ? (float) $row['annual_theory_mm'] : null;
                $prMax = isset($row['annual_practical_mm']) && is_numeric($row['annual_practical_mm']) ? (float) $row['annual_practical_mm'] : null;
                $subjMax = (($thMax !== null ? $thMax : 0.0) + ($prMax !== null ? $prMax : 0.0));
                if ($subjMax <= 0) {
                    $subjMax = 100.0;
                }
                $term2Max += $subjMax;
                $hasTerm2Marks = true;
            }
        }

        if (!$hasTerm2Marks || $term2Max <= 0) {
            return null;
        }

        $percentage = round(($term2Obtained / $term2Max) * 100, 2);

        return [
            'obtained' => $term2Obtained,
            'max' => $term2Max,
            'percentage' => $percentage,
        ];
    }

    public function buildPrintData(?int $classId, ?int $examId, ?int $studentId, Request $request): array
    {
        $student = $studentId ? $this->studentById($studentId) : null;
        $class = $classId ? Classes::find($classId) : null;
        $exam = $examId ? Exam::find($examId) : null;

        $sessionYear = $this->resolveDynamicSessionYear($request);
        $displaySession = str_replace('_', '-', $sessionYear);

        $subjects = $this->subjectsFor($classId, $studentId);
        $marks = $studentId ? $this->marksForStudent($studentId, $classId, $examId) : collect();
        $reportAccess = app(\App\Services\StaffReportAccessService::class);
        if ($reportAccess->isStaffUser() && $class) {
            $section = $student->section_name ?? $class->section_name ?? $request->input('section_name');
            $studentClass = $student->class_id ?? $student->class_name ?? $class->id;
            abort_unless(
                $reportAccess->canAccess($class->id, $section)
                    && $reportAccess->canAccess($studentClass, $section),
                403,
                'You are not authorized to access reports for this class, section, or subject.'
            );
            $subjects = $reportAccess->filterSubjectData($subjects, $class->id, $section);
            $marks = $reportAccess->filterSubjectData($marks, $class->id, $section, 'subject_id');
        }

        // Map exams for Class 11/12
        $examMap = $this->seniorExamMap($classId);

        $pt1Exam = $examMap->get('pt1');
        $hyExam = $examMap->get('term1');
        $pt2Exam = $examMap->get('pt2');
        $annualExam = $examMap->get('term2');

        $rows = [];
        $totalHYObtained = 0.0;
        $totalHYMax = 0.0;
        $hasHYMarks = false;

        $totalAnnualObtained = 0.0;
        $totalAnnualMax = 0.0;
        $hasAnnualMarks = false;

        $hasAnyMarks = false;
        $hasFailingSubject = false;

        $validSubjectCount = 0;

        foreach ($subjects as $subject) {
            $subjectId = (int) $subject->id;
            $subjectName = trim((string) ($subject->subject_name ?? $subject->name ?? ''));

            // Exclude test subjects
            if (Str::startsWith(Str::lower($subjectName), 'test') || Str::contains(Str::lower($subjectName), ['te4st', 'dummy'])) {
                continue;
            }

            // Exclude Non-Academic or Skill subjects if marked as do_not_print or purely non-academic
            $subType = strtolower(trim((string) ($subject->subject_type ?? '')));
            if (preg_match('/non[\s-]*academic/i', $subType) || AcademicHelper::isSpecialFormattingForClass($subject, $classId, $class?->class_name)) {
                // Per requirement 3: No Non-Academic / Skill Subjects table
                continue;
            }

            // Theory & Practical Max Marks
            $subjectMaxConfig = $this->getSeniorSubjectMaxMarks(
                $subjectName,
                $hyExam?->max_marks_theory,
                $hyExam?->max_marks_practical
            );
            $hyTheoryMax = $subjectMaxConfig['theory'];
            $hyPracticalMax = $subjectMaxConfig['practical'];

            $annualSubjectMaxConfig = $this->getSeniorSubjectMaxMarks(
                $subjectName,
                $annualExam?->max_marks_theory,
                $annualExam?->max_marks_practical
            );
            $annualTheoryMax = $annualSubjectMaxConfig['theory'];
            $annualPracticalMax = $annualSubjectMaxConfig['practical'];

            // Find Marks for PT1, HY, PT2, Annual
            $pt1Mark = $pt1Exam ? $this->findMark($marks, $subjectId, $pt1Exam->id) : null;
            $hyMark = $hyExam ? $this->findMark($marks, $subjectId, $hyExam->id) : null;
            $pt2Mark = $pt2Exam ? $this->findMark($marks, $subjectId, $pt2Exam->id) : null;
            $annualMark = $annualExam ? $this->findMark($marks, $subjectId, $annualExam->id) : null;

            // PT - 1 (entered out of 40, converted to scale of 50)
            $rawPt1 = $this->extractObtainedMark($pt1Mark);
            $pt1Val = $this->convertSeniorPtMark($rawPt1);
            if ($pt1Val !== '') $hasAnyMarks = true;

            // Half Yearly Exam
            $hyTheoryVal = $this->extractTheoryMark($hyMark);
            $hyPracticalVal = $this->extractPracticalMark($hyMark);
            if ($hyTheoryVal !== '' || $hyPracticalVal !== '') $hasAnyMarks = true;

            $hyMarksObtained = '';
            if (is_numeric($hyTheoryVal) || is_numeric($hyPracticalVal)) {
                $th = is_numeric($hyTheoryVal) ? (float) $hyTheoryVal : 0;
                $pr = is_numeric($hyPracticalVal) ? (float) $hyPracticalVal : 0;
                $hyMarksObtained = (float) ($th + $pr);
            }

            $hyGrandTotal = '';
            if (is_numeric($pt1Val) || is_numeric($hyMarksObtained)) {
                $p1 = is_numeric($pt1Val) ? (float) $pt1Val : 0;
                $hy = is_numeric($hyMarksObtained) ? (float) $hyMarksObtained : 0;
                $hyGrandTotal = (float) ($p1 + $hy);
            }

            // PT - II (entered out of 40, converted to scale of 50)
            $rawPt2 = $this->extractObtainedMark($pt2Mark);
            $pt2Val = $this->convertSeniorPtMark($rawPt2);
            if ($pt2Val !== '') $hasAnyMarks = true;

            // Annual Exam
            $annualTheoryVal = $this->extractTheoryMark($annualMark);
            $annualPracticalVal = $this->extractPracticalMark($annualMark);
            if ($annualTheoryVal !== '' || $annualPracticalVal !== '') $hasAnyMarks = true;

            $annualMarksObtained = '';
            if (is_numeric($annualTheoryVal) || is_numeric($annualPracticalVal)) {
                $ath = is_numeric($annualTheoryVal) ? (float) $annualTheoryVal : 0;
                $apr = is_numeric($annualPracticalVal) ? (float) $annualPracticalVal : 0;
                $annualMarksObtained = (float) ($ath + $apr);
            }

            $annualTotal = '';
            if (is_numeric($pt2Val) || is_numeric($annualMarksObtained)) {
                $p2 = is_numeric($pt2Val) ? (float) $pt2Val : 0;
                $an = is_numeric($annualMarksObtained) ? (float) $annualMarksObtained : 0;
                $annualTotal = (float) ($p2 + $an);
            }

            $combinedGrandTotal = '';
            if (is_numeric($hyGrandTotal) || is_numeric($annualTotal)) {
                $g1 = is_numeric($hyGrandTotal) ? (float) $hyGrandTotal : 0;
                $g2 = is_numeric($annualTotal) ? (float) $annualTotal : 0;
                $combinedGrandTotal = (float) ($g1 + $g2);
            }

            $athMm = is_numeric($annualTheoryMax) ? (float) $annualTheoryMax : 0.0;
            $aprMm = is_numeric($annualPracticalMax) ? (float) $annualPracticalMax : 0.0;
            $annualSubjMax = ($athMm + $aprMm) > 0 ? ($athMm + $aprMm) : 100.0;

            if ($annualMarksObtained !== '' && is_numeric($annualMarksObtained)) {
                $totalAnnualObtained += (float) $annualMarksObtained;
                $totalAnnualMax += $annualSubjMax;
                $hasAnnualMarks = true;
            }

            $hthMm = is_numeric($hyTheoryMax) ? (float) $hyTheoryMax : 0.0;
            $hprMm = is_numeric($hyPracticalMax) ? (float) $hyPracticalMax : 0.0;
            $hySubjMax = ($hthMm + $hprMm) > 0 ? ($hthMm + $hprMm) : 100.0;

            if ($hyMarksObtained !== '' && is_numeric($hyMarksObtained)) {
                $totalHYObtained += (float) $hyMarksObtained;
                $totalHYMax += $hySubjMax;
                $hasHYMarks = true;
            }

            $validSubjectCount++;

            $rows[] = [
                'subject_id' => $subjectId,
                'subject_name' => $subjectName,
                // Term I
                'pt1' => $pt1Val,
                'hy_theory_mm' => $hyTheoryMax,
                'hy_theory_obt' => $hyTheoryVal,
                'hy_practical_mm' => $hyPracticalMax,
                'hy_practical_obt' => $hyPracticalVal,
                'hy_obtained' => $hyMarksObtained,
                'hy_grand_total' => $hyGrandTotal,
                // Term II
                'pt2' => $pt2Val,
                'annual_theory_mm' => $annualTheoryMax,
                'annual_theory_obt' => $annualTheoryVal,
                'annual_practical_mm' => $annualPracticalMax,
                'annual_practical_obt' => $annualPracticalVal,
                'annual_obtained' => $annualMarksObtained,
                'annual_total' => $annualTotal,
                'combined_grand_total' => $combinedGrandTotal,
            ];
        }

        // Percentage calculations: Term II percentage calculated based exclusively on Term II marks and corresponding maximum marks
        $hyPercentage = null;
        if ($hasHYMarks && $totalHYMax > 0) {
            $hyPercentage = round(($totalHYObtained / $totalHYMax) * 100, 2);
        }

        $annualPercentage = null;
        if ($hasAnnualMarks && $totalAnnualMax > 0) {
            $annualPercentage = round(($totalAnnualObtained / $totalAnnualMax) * 100, 2);
        }

        // Attendance & Teacher Remarks
        $attendance = $request->input('attendance');
        if (empty($attendance)) {
            $attendance = $this->collectiveAttendanceForMarksheet($studentId, $classId, $examId, $sessionYear);
        }

        $teacherRemark = $this->teacherRemarkForStudent($studentId, $examId);
        if (empty($teacherRemark)) {
            $teacherRemark = $hasFailingSubject ? 'Needs Improvement' : 'Passed and promoted to next class.';
        }

        $rollNo = $this->rollNoFor($studentId, $classId, $examId);
        $classSection = $this->classSectionText($class, $student);

        return [
            'marksheet_type' => 'senior_11_12',
            'pt_scale' => 50,
            'has_marks' => $hasAnyMarks,
            'student' => $student,
            'student_meta' => $this->studentMeta($student),
            'class' => $class,
            'class_section' => $classSection,
            'exam' => $exam,
            'roll_no' => $rollNo,
            'rows' => $rows,
            'total_hy_obtained' => $totalHYObtained > 0 ? number_format($totalHYObtained, 2, '.', '') : '',
            'total_annual_obtained' => $totalAnnualObtained > 0 ? number_format($totalAnnualObtained, 2, '.', '') : '',
            'hy_percentage' => $hyPercentage !== null ? number_format($hyPercentage, 2, '.', '') . ' %' : '',
            'annual_percentage' => $annualPercentage !== null ? number_format($annualPercentage, 2, '.', '') . ' %' : '',
            'attendance' => $attendance,
            'remarks' => $teacherRemark,
            'date' => $this->formatSeniorDate($request->input('print_date', $request->input('date'))),
            'session_year' => $sessionYear,
            'display_session' => $displaySession,
            'title' => (\App\Academic\AcademicHelper::normalizeClassIdentifier(optional($class)->class_name) === '12') ? 'Pre Board Examination Report Card' : 'Annual Examination Report Card',
        ];
    }

    public function getSeniorSubjectMaxMarks(string $subjectName, ?int $examTheoryMax = null, ?int $examPracticalMax = null): array
    {
        if ($examTheoryMax !== null && $examPracticalMax !== null && ($examTheoryMax + $examPracticalMax) === 100) {
            return ['theory' => $examTheoryMax, 'practical' => $examPracticalMax];
        }

        $name = strtolower(trim($subjectName));

        // 30 Theory + 70 Practical
        if (preg_match('/\b(painting|graphics|sculpture|fine\s*art|applied\s*art|music|dance)\b/i', $name)) {
            return ['theory' => 30, 'practical' => 70];
        }

        // 60 Theory + 40 Practical
        if (preg_match('/\b(marketing|retail|tourism|web\s*app|typography)\b/i', $name)) {
            return ['theory' => 60, 'practical' => 40];
        }

        // 70 Theory + 30 Practical
        if (preg_match('/\b(physics|chemistry|biology|biotechnology|psychology|geography|computer|informatics|physical\s*education|pe)\b/i', $name)) {
            return ['theory' => 70, 'practical' => 30];
        }

        // 80 Theory + 20 Practical / Internal (Standard CBSE Default for English, Maths, Commerce, Humanities)
        return ['theory' => 80, 'practical' => 20];
    }

    private function findMark($marks, int $subjectId, int $examId)
    {
        return $marks->first(function ($m) use ($subjectId, $examId) {
            return (int) ($m->subject_id ?? 0) === $subjectId && (int) ($m->exam_id ?? 0) === $examId;
        });
    }

    private function extractObtainedMark($mark): string
    {
        if (!$mark) return '';
        if (!empty($mark->is_absent)) return 'AB';
        $val = $mark->total_marks ?? ($mark->mark_theory ?? ($mark->subject_marks ?? null));
        return $this->formatNumeric($val);
    }

    private function extractTheoryMark($mark): string
    {
        if (!$mark) return '';
        if (!empty($mark->is_absent)) return 'AB';
        $val = $mark->mark_theory ?? ($mark->subject_marks ?? null);
        return $this->formatNumeric($val);
    }

    private function extractPracticalMark($mark): string
    {
        if (!$mark) return '';
        if (!empty($mark->is_absent_pr)) return 'AB';
        $val = $mark->mark_practical ?? null;
        return $this->formatNumeric($val);
    }

    public function convertSeniorPtMark($val): string
    {
        if ($val === null || $val === '' || $val === '-') {
            return '';
        }
        if (is_string($val) && strtoupper(trim($val)) === 'AB') {
            return 'AB';
        }
        if (!is_numeric($val)) {
            return (string) $val;
        }

        $obtained = (float) $val;
        if ($obtained <= 0) {
            return '0';
        }

        // PT marks entered out of 40 converted to scale of 50
        $converted = ($obtained / 40.0) * 50.0;
        $rounded = round(min(50.0, max(0.0, $converted)), 0, PHP_ROUND_HALF_UP);

        return (string) (int) $rounded;
    }

    private function formatNumeric($val): string
    {
        if ($val === null || $val === '' || !is_numeric($val)) {
            return (string) ($val ?? '');
        }
        $f = (float) $val;
        return abs($f - round($f)) < 0.005 ? (string) (int) round($f) : (string) $f;
    }

    private function seniorExamMap(?int $classId)
    {
        $classIds = $this->marksClassIdsFor($classId);
        $exams = Exam::query()
            ->when(!empty($classIds), fn ($q) => $q->whereIn('class_id', $classIds))
            ->get();

        if ($exams->isEmpty()) {
            $exams = Exam::all();
        }

        $map = collect();
        foreach ($exams as $exam) {
            $canonical = $this->canonicalExamType($exam->exam_type, $exam->exam_name);
            if ($canonical !== '' && !$map->has($canonical)) {
                $map->put($canonical, $exam);
            }
        }

        return $map;
    }

    public function canonicalExamType(?string $type, ?string $name = null): string
    {
        $raw = strtolower(trim((string) ($type ?: $name)));
        $clean = preg_replace('/[^a-z0-9]/', '', $raw);

        if (is_numeric($raw) && !empty($name)) {
            return $this->canonicalExamType(null, $name);
        }

        // 1. PT 2
        if (
            preg_match('/\bpt[\s_-]*(2|ii)\b/i', $raw) ||
            preg_match('/periodic[\s_-]*test[\s_-]*(2|ii)\b/i', $raw) ||
            in_array($clean, ['pt2', 'ptii', 'periodictest2', 'periodictestii', 'pa2'], true) ||
            str_starts_with($clean, 'pt2') ||
            str_starts_with($clean, 'ptii')
        ) {
            return 'pt2';
        }

        // 2. PT 1
        if (
            preg_match('/\bpt[\s_-]*(1|i)\b/i', $raw) ||
            preg_match('/periodic[\s_-]*test[\s_-]*(1|i)\b/i', $raw) ||
            in_array($clean, ['pt1', 'pti', 'periodictest1', 'periodictesti', 'pa1'], true) ||
            str_starts_with($clean, 'pt1') ||
            str_starts_with($clean, 'pti')
        ) {
            return 'pt1';
        }

        // 3. Half Yearly / Term 1
        if (
            preg_match('/half[\s_-]*yearly/i', $raw) ||
            preg_match('/mid[\s_-]*term/i', $raw) ||
            in_array($clean, ['halfyearly', 'midterm', 'hy'], true) ||
            str_starts_with($clean, 'halfyearly') ||
            str_starts_with($clean, 'midterm')
        ) {
            return 'term1';
        }

        // 4. Annual / Term 2 / Pre Board
        if (
            preg_match('/\bterm[\s_-]*(2|ii)\b/i', $raw) ||
            preg_match('/annual/i', $raw) ||
            preg_match('/pre[\s_-]*board/i', $raw) ||
            preg_match('/(?<!half[\s_-])yearly/i', $raw) ||
            preg_match('/final/i', $raw) ||
            in_array($clean, ['term2', 'termii', 'annual', 'yearly', 'final', 'preboard'], true) ||
            str_starts_with($clean, 'term2') ||
            str_starts_with($clean, 'termii') ||
            str_starts_with($clean, 'annual') ||
            str_starts_with($clean, 'preboard')
        ) {
            return 'term2';
        }

        // 5. Term 1 / Term I fallback
        if (
            preg_match('/\bterm[\s_-]*(1|i)\b/i', $raw) ||
            in_array($clean, ['term1', 'termi'], true) ||
            str_starts_with($clean, 'term1') ||
            str_starts_with($clean, 'termi')
        ) {
            return 'term1';
        }

        if (!empty($name) && $raw !== strtolower(trim($name))) {
            return $this->canonicalExamType(null, $name);
        }

        return $clean;
    }

    private function marksForStudent(int $studentId, ?int $classId, ?int $examId)
    {
        $hasStudentMarks = Schema::connection('dynamic')->hasTable('academic_students_marks');
        $hasMarksTable = Schema::connection('dynamic')->hasTable('marks');
        $hasSavedEntry = Schema::connection('dynamic')->hasTable('previosly_saved_marks_entry');

        if (!$hasSavedEntry || (!$hasStudentMarks && !$hasMarksTable)) {
            return collect();
        }

        $marksTable = $hasStudentMarks ? 'academic_students_marks' : 'marks';

        $query = DB::connection('dynamic')
            ->table($marksTable . ' as sm')
            ->join('previosly_saved_marks_entry as me', 'me.id', '=', 'sm.marks_id')
            ->where('sm.student_id', (string) $studentId);

        if (Schema::connection('dynamic')->hasColumn($marksTable, 'is_delete')) {
            $query->where(function ($q) use ($marksTable) {
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

    private function subjectsFor(?int $classId, ?int $studentId)
    {
        $class = $classId ? Classes::find($classId) : null;
        $subjectColumns = ['id', 'subject_name', 'subject_type', 'evaluation', 'practical', 'do_not_print_in_main_scholastic_area', 'special_formatting_classes'];
        $actualColumns = [];
        foreach ($subjectColumns as $col) {
            if (Schema::connection('dynamic')->hasColumn('subjectmaster', $col)) {
                $actualColumns[] = 's.' . $col;
            }
        }
        if (empty($actualColumns)) {
            $actualColumns = ['s.id', 's.subject_name'];
        }

        $assigned = collect();

        // 1. Check student-specific assignment
        if ($studentId && Schema::connection('dynamic')->hasTable('subject_assign_student') && Schema::connection('dynamic')->hasTable('combination_subject')) {
            $hasIsDelete = Schema::connection('dynamic')->hasColumn('subject_assign_student', 'is_delete');
            $query = DB::connection('dynamic')->table('subject_assign_student as sas');
            if ($class && !empty($class->class_name)) {
                $norm = AcademicHelper::normalizeClassIdentifier($class->class_name);
                $query->where(function ($q) use ($class, $norm) {
                    $q->where('sas.class_name', $class->class_name)
                      ->orWhere('sas.class_name', $norm);
                });
            }
            if ($hasIsDelete) {
                $query->orderByRaw('CASE WHEN sas.is_delete = 0 OR sas.is_delete IS NULL THEN 0 ELSE 1 END ASC');
            }
            $assignments = $query->orderByDesc('sas.id')->get();

            $matchedCombId = null;
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

                if (in_array((int) $studentId, $ids, true)) {
                    $matchedCombId = (int) $a->assign_this_combtoall;
                    break;
                }
            }

            if ($matchedCombId) {
                $assigned = DB::connection('dynamic')
                    ->table('combination_subject as cs')
                    ->join('subjectmaster as s', 's.id', '=', 'cs.subject_id')
                    ->where('cs.subject_combination_id', $matchedCombId)
                    ->where(function ($q) {
                        if (Schema::connection('dynamic')->hasColumn('subjectmaster', 'is_delete')) {
                            $q->where('s.is_delete', 0)->orWhereNull('s.is_delete');
                        }
                    })
                    ->whereNotIn(DB::raw('LOWER(TRIM(s.subject_name))'), ['test', 'te4st', 'dummy'])
                    ->select($actualColumns)
                    ->orderBy('cs.subject_order')
                    ->orderBy('s.subject_name')
                    ->get()
                    ->unique('id')
                    ->values();
            }
        }

        // If combination subjects are found, return ONLY those assigned subjects (do not append extra subjects from mark entries or class master)
        if ($assigned->isNotEmpty()) {
            return $assigned;
        }

        // 2. Check class-level assigned subjects (fallback only when student has no assigned combination)
        if ($classId && Schema::connection('dynamic')->hasTable('academic_class_subject')) {
            $assigned = DB::connection('dynamic')
                ->table('academic_class_subject as acs')
                ->join('subjectmaster as s', 's.id', '=', 'acs.subject_id')
                ->whereIn('acs.class_id', $this->marksClassIdsFor($classId))
                ->where(function ($q) {
                    if (Schema::connection('dynamic')->hasColumn('subjectmaster', 'is_delete')) {
                        $q->where('s.is_delete', 0)->orWhereNull('s.is_delete');
                    }
                })
                ->whereNotIn(DB::raw('LOWER(TRIM(s.subject_name))'), ['test', 'te4st', 'dummy'])
                ->select($actualColumns)
                ->orderBy('s.subject_name')
                ->get()
                ->unique('id')
                ->values();
        }

        // 3. Include any subjects where marks are recorded for this student (fallback only when student has no assigned combination)
        if ($studentId && Schema::connection('dynamic')->hasTable('previosly_saved_marks_entry')) {
            $marksTable = Schema::connection('dynamic')->hasTable('academic_students_marks') ? 'academic_students_marks' : 'marks';
            $markedSubjectIds = DB::connection('dynamic')
                ->table($marksTable . ' as sm')
                ->join('previosly_saved_marks_entry as me', 'me.id', '=', 'sm.marks_id')
                ->where('sm.student_id', (string) $studentId)
                ->pluck('me.subject_id')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->all();

            $existingIds = $assigned->pluck('id')->map(fn ($id) => (int) $id)->all();
            $missingIds = array_diff($markedSubjectIds, $existingIds);
            if (!empty($missingIds)) {
                $extraSubjects = DB::connection('dynamic')
                    ->table('subjectmaster as s')
                    ->whereIn('s.id', $missingIds)
                    ->whereNotIn(DB::raw('LOWER(TRIM(s.subject_name))'), ['test', 'te4st', 'dummy'])
                    ->select($actualColumns)
                    ->get();
                $assigned = $assigned->concat($extraSubjects)->unique('id')->values();
            }
        }

        if ($assigned->isNotEmpty()) {
            return $assigned;
        }

        // Fallback to subjects
        if (Schema::connection('dynamic')->hasTable('subjectmaster')) {
            return DB::connection('dynamic')
                ->table('subjectmaster as s')
                ->where(function ($q) {
                    if (Schema::connection('dynamic')->hasColumn('subjectmaster', 'is_delete')) {
                        $q->where('s.is_delete', 0)->orWhereNull('s.is_delete');
                    }
                })
                ->whereNotIn(DB::raw('LOWER(TRIM(s.subject_name))'), ['test', 'te4st', 'dummy'])
                ->select($actualColumns)
                ->orderBy('s.subject_name')
                ->get();
        }

        return collect();
    }

    private function studentsForClass(?int $classId = null, ?string $className = null, ?string $sectionName = null)
    {
        if (!Schema::connection('dynamic')->hasTable('student_registration')) {
            return collect();
        }

        if (!$classId && !$className) {
            $classId = 14;
        }

        $class = $classId ? Classes::find($classId) : null;
        if (!$className && $class) {
            $className = $class->class_name;
        }

        $siblingClassIds = [];
        if ($className) {
            $numericClass = AcademicHelper::fromRoman($className) ?: $className;
            $siblingClassIds = Classes::query()
                ->where('class_name', $className)
                ->orWhere('class_name', ltrim($className, '0'))
                ->orWhere('class_name', $numericClass)
                ->orWhere('class_name', ltrim($numericClass, '0'))
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();
        } elseif ($classId) {
            $siblingClassIds = [(string) $classId];
        }


        $query = DB::connection('dynamic')
            ->table('student_registration')
            ->where(function ($q) {
                $q->where('is_archived', 0)->orWhereNull('is_archived');
            })
            ->where(function ($inner) use ($classId, $className, $siblingClassIds) {
                if (!empty($siblingClassIds)) {
                    $inner->whereIn('class_id', $siblingClassIds);
                } elseif ($classId) {
                    $inner->where('class_id', (string) $classId);
                }
                if ($className) {
                    $inner->orWhere('class_name', $className)
                        ->orWhere('class_name', ltrim($className, '0'))
                        ->orWhereRaw('LOWER(class_name) = ?', [strtolower($className)]);
                }
            });

        if ($sectionName) {
            $query->where(function ($q) use ($sectionName) {
                $q->where('section_name', $sectionName)
                    ->orWhere('section_name', 'like', '%' . $sectionName . '%');
            });
        }

        $students = $query->orderBy('student_name')->get();
        $reportAccess = app(\App\Services\StaffReportAccessService::class);
        if (!$reportAccess->isStaffUser()) {
            return $students;
        }

        return $students->filter(function ($student) use ($classId, $className, $sectionName, $reportAccess) {
            $studentClass = $student->class_id ?: $student->class_name ?: $classId ?: $className;
            $studentClassRow = !empty($student->class_id) && is_numeric($student->class_id)
                ? Classes::query()->find((int) $student->class_id)
                : null;
            $studentSection = $student->section_name ?: $studentClassRow?->section_name ?: $sectionName;
            return $studentClass && $studentSection && $reportAccess->canAccess($studentClass, $studentSection);
        })->values();
    }

    private function studentById(int $id)
    {
        if (!Schema::connection('dynamic')->hasTable('student_registration')) {
            return null;
        }

        return DB::connection('dynamic')
            ->table('student_registration')
            ->where('id', $id)
            ->first();
    }

    private function marksClassIdsFor(?int $classId): array
    {
        if (!$classId) {
            return [];
        }

        $class = Classes::find($classId);
        if (!$class) {
            return [$classId];
        }

        $name = $class->class_name;
        return Classes::query()
            ->where('class_name', $name)
            ->orWhere('class_name', ltrim($name, '0'))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function rollNoFor(?int $studentId, ?int $classId, ?int $examId): string
    {
        if (!$studentId) {
            return '';
        }

        if (Schema::connection('dynamic')->hasTable('academic_student_roll_no')) {
            $query = DB::connection('dynamic')
                ->table('academic_student_roll_no')
                ->where('student_id', (string) $studentId);

            if ($examId) {
                $query->where('exam_id', (string) $examId);
            }
            if ($classId) {
                $classIds = $this->marksClassIdsFor($classId);
                $query->whereIn('class_id', array_map('strval', $classIds));
            }

            $record = $query->orderByDesc('id')->first();
            if ($record && !empty($record->roll_no)) {
                return (string) $record->roll_no;
            }
        }

        $student = $this->studentById($studentId);
        return (string) ($student->roll_no ?? ($student->scholar_no ?? ''));
    }

    private function studentMeta($student): array
    {
        if (!$student) {
            return [
                'scholar_no' => '',
                'student_name' => '',
                'father_name' => '',
                'mother_name' => '',
                'date_of_birth' => '',
            ];
        }

        $json = [];
        if (!empty($student->json_str)) {
            $decoded = json_decode($student->json_str, true);
            if (is_array($decoded)) {
                $json = $decoded;
            }
        }

        $fatherPrefix = trim($json['fathername_prefix'] ?? '');
        $fatherName = trim($json['fathername'] ?? ($student->father_name ?? ''));
        $fullFather = $fatherPrefix !== '' ? ($fatherPrefix . ' ' . $fatherName) : $fatherName;

        $motherPrefix = trim($json['mothername_prefix'] ?? '');
        $motherName = trim($json['mothername'] ?? ($student->mother_name ?? ''));
        $fullMother = $motherPrefix !== '' ? ($motherPrefix . ' ' . $motherName) : $motherName;

        $dobRaw = $json['student_dob'] ?? ($student->date_of_birth ?? '');
        $dobFormatted = $this->formatSeniorDob($dobRaw);

        return [
            'scholar_no' => (string) ($student->scholar_no ?? ($student->form_number ?? '')),
            'student_name' => (string) ($student->student_name ?? ''),
            'father_name' => $fullFather,
            'mother_name' => $fullMother,
            'date_of_birth' => $dobFormatted,
        ];
    }

    private function classSectionText($class, $student): string
    {
        $className = optional($class)->class_name ?? ($student->class_name ?? '');
        $sectionName = optional($class)->section_name ?? ($student->section_name ?? '');

        $romanClass = AcademicHelper::toRoman($className);
        if ($sectionName !== '') {
            return $romanClass . '-' . $sectionName;
        }

        return $romanClass;
    }

    public function formatSeniorDob(?string $value): string
    {
        if (empty($value)) return '';

        try {
            // Check d-m-Y or Y-m-d
            if (preg_match('/^(\d{1,2})[- \/.](\d{1,2})[- \/.](00\d{2}|\d{4})$/', trim($value), $m)) {
                $year = (int) $m[3];
                if ($year < 100) $year += 2000;
                $date = Carbon::createFromDate($year, (int) $m[2], (int) $m[1]);
                return $date->format('M-d-Y');
            }
            $date = Carbon::parse($value);
            return $date->format('M-d-Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    public function formatSeniorDate(?string $value, string $defaultFormat = 'd-M-Y'): string
    {
        if (empty($value)) {
            return now()->format($defaultFormat);
        }

        try {
            return Carbon::parse($value)->format($defaultFormat);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function collectiveAttendanceForMarksheet(?int $studentId, ?int $classId, ?int $examId, ?string $sessionYear, string $fallback = ''): string
    {
        if (!$studentId) {
            return $fallback;
        }

        $sessionVariants = $this->sessionYearVariants($sessionYear);
        $classIds = $this->marksClassIdsFor($classId);

        if (Schema::connection('dynamic')->hasTable('academic_attendance_collectives') && Schema::connection('dynamic')->hasTable('academic_attendance_collective_details')) {
            $record = DB::connection('dynamic')
                ->table('academic_attendance_collective_details as acd')
                ->join('academic_attendance_collectives as ac', 'acd.collective_id', '=', 'ac.id')
                ->where('ac.is_delete', 0)
                ->where('acd.student_id', $studentId)
                ->when(!empty($classIds), fn ($q) => $q->whereIn('ac.class_id', $classIds))
                ->when($examId, fn ($q) => $q->where('ac.exam_id', $examId))
                ->when(!empty($sessionVariants), fn ($q) => $q->whereIn('ac.academic_session', $sessionVariants))
                ->orderByDesc('ac.id')
                ->select('acd.present_days', 'acd.working_days', 'acd.attendance_percentage')
                ->first();

            if (!$record) {
                $record = DB::connection('dynamic')
                    ->table('academic_attendance_collective_details as acd')
                    ->join('academic_attendance_collectives as ac', 'acd.collective_id', '=', 'ac.id')
                    ->where('ac.is_delete', 0)
                    ->where('acd.student_id', $studentId)
                    ->when(!empty($classIds), fn ($q) => $q->whereIn('ac.class_id', $classIds))
                    ->when(!empty($sessionVariants), fn ($q) => $q->whereIn('ac.academic_session', $sessionVariants))
                    ->orderByDesc('ac.id')
                    ->select('acd.present_days', 'acd.working_days', 'acd.attendance_percentage')
                    ->first();
            }

            if ($record && (float) $record->working_days > 0) {
                $pres = ((int) $record->present_days == $record->present_days) ? (int) $record->present_days : (float) $record->present_days;
                $work = (int) $record->working_days;
                return "{$pres} / {$work}";
            }
        }

        if ($fallback !== '' && !str_contains($fallback, '%')) {
            return $fallback;
        }

        if (Schema::connection('dynamic')->hasTable('academic_attendance_details') && Schema::connection('dynamic')->hasTable('academic_attendance')) {
            $dailyQuery = DB::connection('dynamic')
                ->table('academic_attendance_details as aad')
                ->join('academic_attendance as aa', 'aad.attendance_id', '=', 'aa.id')
                ->where('aad.student_id', $studentId)
                ->where(function ($q) {
                    if (Schema::connection('dynamic')->hasColumn('academic_attendance', 'is_delete')) {
                        $q->where('aa.is_delete', 0)->orWhereNull('aa.is_delete');
                    }
                });

            if (!empty($classIds)) {
                $dailyQuery->whereIn('aa.class_id', array_map('strval', $classIds));
            }
            if (!empty($sessionVariants)) {
                $dailyQuery->whereIn('aa.academic_session', $sessionVariants);
            }

            $totalDays = $dailyQuery->count();
            if ($totalDays > 0) {
                $presentCount = (clone $dailyQuery)
                    ->whereIn(DB::raw('LOWER(aad.status)'), ['present', 'p', 'half_day', 'pl'])
                    ->selectRaw("SUM(CASE WHEN LOWER(aad.status) IN ('present', 'p', 'pl') THEN 1 WHEN LOWER(aad.status) = 'half_day' THEN 0.5 ELSE 0 END) as total")
                    ->value('total');
                $presentDays = $presentCount !== null ? (float) $presentCount : 0.0;
                $formattedPresent = ((int) $presentDays == $presentDays) ? (int) $presentDays : number_format($presentDays, 1);
                return "{$formattedPresent} / {$totalDays}";
            }
        }

        return (!str_contains($fallback, '%')) ? $fallback : '';
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

    private function teacherRemarkForStudent(?int $studentId, ?int $examId): ?string
    {
        if (!$studentId || !Schema::connection('dynamic')->hasTable('academic_student_remarks')) {
            return null;
        }

        $row = DB::connection('dynamic')
            ->table('academic_student_remarks as sr')
            ->where('sr.student_id', $studentId)
            ->when($examId && Schema::connection('dynamic')->hasTable('academic_teacher_remark_entries'), function ($q) use ($examId) {
                $q->join('academic_teacher_remark_entries as tr', 'tr.id', '=', 'sr.teacher_remark_entry_id')
                    ->where('tr.exam_id', $examId);
            })
            ->latest('sr.id')
            ->select('sr.remark_text')
            ->first();

        return $row->remark_text ?? null;
    }

    private function getLatestMarksheetIds(?int $classId = null, array $filterExamIds = []): array
    {
        $baseQuery = Marksheet::query()
            ->where(function ($query) {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.marksheet_type')) = 'senior_11_12'")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.print_data.marksheet_type')) = 'senior_11_12'");
            })
            ->when($classId, fn ($q) => $q->where('class_id', $classId));

        if (!empty($filterExamIds)) {
            return (clone $baseQuery)
                ->whereIn('exam_id', $filterExamIds)
                ->groupBy('student_id')
                ->selectRaw('MAX(id) as id')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $rows = (clone $baseQuery)
            ->select('id', 'student_id', 'exam_id', 'snapshot')
            ->orderByDesc('id')
            ->get();

        $seen = [];
        $latestIds = [];
        foreach ($rows as $row) {
            $key = (string) $row->student_id;
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $latestIds[] = (int) $row->id;
            }
        }

        return $latestIds;
    }

    public function resolveDynamicSessionYear(Request $request): string
    {
        $session = session('academic_session') ?: session('session_name');
        if (!empty($session)) {
            return (string) $session;
        }

        $dbName = Config::get('database.connections.dynamic.database');
        if (is_string($dbName) && preg_match('/^\d{4}_\d{4}$/', $dbName)) {
            return $dbName;
        }

        return '2025_2026';
    }
}
