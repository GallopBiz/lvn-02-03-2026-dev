<?php

namespace App\Http\Controllers\Academic;

use App\Academic\AcademicHelper;
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
use Carbon\Carbon;

class MarksheetController extends Controller
{
    public function index(Request $request)
    {
        $generatedSearch = trim((string) $request->query('generated_search', ''));
        $generatedClassId = $request->integer('generated_class_id') ?: null;
        $generatedExamId = $request->integer('generated_exam_id') ?: null;
        $selectedClassId = $request->integer('class_id') ?: null;
        $selectedSectionName = trim((string) $request->input('section_name', '')) ?: null;
        $selectedExamId = $request->integer('exam_id') ?: null;

        $reportAccess = app(\App\Services\StaffReportAccessService::class);
        $classes = $reportAccess->filterClassOptions(Classes::query()
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
            ->whereNotNull('section_name')
            ->where('section_name', '!=', '')
            ->whereNotIn('section_name', ['testing', 'test Section', '12th out section'])
            ->orderBy('section_name')
            ->get()
        )
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
            $roman = \App\Academic\AcademicHelper::toRoman((string) $cName);
            if ($roman !== '') {
                $augmentedSections[$roman] = $secs;
            }

        }
        foreach ($classes as $cls) {
            $cName = (string) $cls->class_name;
            $secs = $sectionsByClass[$cName] ?? ($sectionsByClass[ltrim($cName, '0')] ?? []);
            if (!empty($secs)) {
                $augmentedSections[(string) $cls->id] = $secs;
            }
        }
        $sectionsByClass = $augmentedSections;

        $rawExams = Exam::with('classInfo')->latest('id')->get();

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
                        'display_name' => \App\Academic\AcademicHelper::toRoman((string) ($cName ?: ('Class ' . $cId))),
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

        $availableClasses = [];
        if ($selectedGroup) {
            $availableClasses = $selectedGroup['classes'];
        } elseif ($selectedClassId) {
            $availableClasses = $classes->map(fn ($c) => [
                'exam_id' => null,
                'class_id' => (int) $c->id,
                'class_name' => (string) $c->class_name,
                'display_name' => \App\Academic\AcademicHelper::toRoman((string) $c->class_name),
            ])->all();

        }

        $resolvedExam = $this->resolveExamForClass($selectedExamId, $selectedClassId);
        $resolvedExamId = $resolvedExam ? (int) $resolvedExam->id : $selectedExamId;

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

        $perPage = $request->integer('per_page', 20);
        $allowedPerPage = [10, 20, 50, 100, 200, 500];
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 20;
        }

        $latestMarksheetIds = $this->getLatestMarksheetIds($generatedClassId, $filterExamIds);

        $generatedMarksheets = Marksheet::query()
            ->whereIn('id', !empty($latestMarksheetIds) ? $latestMarksheetIds : [0])
            ->when($reportAccess->isStaffUser(), function ($query) use ($reportAccess) {
                $classIds = $reportAccess->filterClassOptions(Classes::query()->get())->pluck('id')->all();
                $query->whereIn('class_id', $classIds ?: [0]);
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
        $marksMissing = false;
        if ($selectedClassId) {
            $marksMissing = !$this->hasMarksForClassAndExam($selectedClassId, $selectedExamId, $selectedClassName, $selectedSectionName);
        }

        return view('backend.AcademicsModules.marksheet', compact(
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
            'selectedExamId',
            'resolvedExamId',
            'marksMissing'
        ));
    }

    public function getStudentsJson(Request $request)
    {
        $classId = $request->integer('class_id') ?: null;
        $className = trim((string) $request->input('class_name', '')) ?: null;
        $sectionName = trim((string) $request->input('section_name', '')) ?: null;
        $examId = $request->integer('exam_id') ?: null;
        if (!$classId && !$className) {
            return response()->json([
                'students' => [],
                'has_marks' => false,
            ]);
        }

        $students = $this->studentsForClass($classId, $className, $sectionName);
        $hasMarks = $this->hasMarksForClassAndExam($classId, $examId, $className, $sectionName);

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
            'has_marks' => $hasMarks,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|integer',
            'section_name' => 'nullable|string|max:100',
            'exam_id' => 'nullable|integer',
            'resolved_exam_id' => 'nullable|integer',
            'student_id' => 'nullable|integer',
            'attendance' => 'nullable|string|max:50',
            'print_date' => 'nullable|string|max:50',
            'date' => 'nullable|string|max:50',
        ]);

        $classId = (int) $validated['class_id'];
        $sectionName = !empty($validated['section_name']) ? trim($validated['section_name']) : null;
        $rawExamId = !empty($validated['resolved_exam_id']) ? (int) $validated['resolved_exam_id'] : (!empty($validated['exam_id']) ? (int) $validated['exam_id'] : null);
        $resolvedExam = $this->resolveExamForClass($rawExamId, $classId);
        $examId = $resolvedExam ? (int) $resolvedExam->id : $rawExamId;
        $studentId = !empty($validated['student_id']) ? (int) $validated['student_id'] : null;
        $template = $this->fixedTemplate();

        $students = $studentId
            ? collect([$this->studentById($studentId)])->filter()
            : $this->studentsForClass($classId, null, $sectionName);

        if ($students->isEmpty()) {
            return redirect()->route('marksheet', array_filter(['class_id' => $classId, 'section_name' => $sectionName, 'exam_id' => $rawExamId]))->with('error', 'No students found for this class / section.');
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
                    'exam_id' => $examId,
                ],
                [
                    'class_id' => $classId,
                    'session_year' => $printData['session_year'],
                    'snapshot' => [
                        'template_name' => $template->name,
                        'student_name' => $printData['student_meta']['student_name'],
                        'scholar_no' => $printData['student_meta']['scholar_no'],
                        'class_name' => optional($printData['class'])->class_name,
                        'exam_name' => optional($printData['exam'])->exam_name,
                        'print_date' => $printData['date'],
                        'print_data' => $snapshotPrintData,
                    ],
                    'status' => 'generated',
                ]
            );
            $count++;
        }

        return redirect()->route('marksheet', array_filter(['class_id' => $classId, 'section_name' => $sectionName, 'exam_id' => $rawExamId]))->with('success', "{$count} marksheet(s) generated and saved.");
    }

    public function print(Request $request)
    {
        $marksheetId = $request->integer('marksheet_id') ?: null;
        if ($marksheetId) {
            return $this->printGenerated(Marksheet::query()->findOrFail($marksheetId), $request);
        }

        $classId = $request->integer('class_id') ?: null;
        $sectionName = trim((string) $request->input('section_name', '')) ?: null;
        $rawExamId = $request->integer('resolved_exam_id') ?: ($request->integer('exam_id') ?: null);
        $resolvedExam = $this->resolveExamForClass($rawExamId, $classId);
        $examId = $resolvedExam ? (int) $resolvedExam->id : $rawExamId;
        $studentId = $request->integer('student_id') ?: null;
        $template = $this->fixedTemplate();
        if ($classId && !$studentId) {
            $students = $this->studentsForClass($classId, null, $sectionName);
            if ($students->isEmpty()) {
                return redirect()->route('marksheet', array_filter(['class_id' => $classId, 'section_name' => $sectionName, 'exam_id' => $rawExamId]))->with('error', 'No students found for this class / section.');
            }
            $sheets = $students->map(function ($student) use ($classId, $examId, $request, $template) {
                $printData = $this->buildPrintData($classId, $examId, (int) $student->id, $request);
                return compact('template', 'printData');
            })->all();
            $printData = $sheets[0]['printData'] ?? [];
        } else {
            $printData = $this->buildPrintData($classId, $examId, $studentId, $request);
            $sheets = [compact('template', 'printData')];
        }


        $selectedDateYmd = $this->parseDateToYmd($printData['date'] ?? null);
        return view('backend.AcademicsModules.marksheet_print', compact('template', 'printData', 'sheets', 'selectedDateYmd'));
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
            ['template' => $template, 'printData' => $printData] = $this->savedPrintSheet($marksheetModel, $request);
            $marksheetModel->forceFill(['printed_at' => now()])->save();
            $sheets = [compact('template', 'printData')];
            $selectedDateYmd = $this->parseDateToYmd($printData['date'] ?? null);

            return view('backend.AcademicsModules.marksheet_print', compact('template', 'printData', 'sheets', 'selectedDateYmd'));
        }

        // When marks are not entered or marksheet is not found:
        // Show a blank marksheet instead of 404!
        $id = is_numeric($marksheet) ? (int) $marksheet : null;
        $student = $id ? $this->studentById($id) : null;
        $classId = null;
        if ($student) {
            if (!empty($student->class_id) && is_numeric($student->class_id)) {
                $classId = (int) $student->class_id;
            } elseif (!empty($student->class_name)) {
                $matchedClass = Classes::query()->where('class_name', $student->class_name)->first();
                $classId = $matchedClass ? (int) $matchedClass->id : null;
            }
        }
        if (!$classId) {
            $classId = $request->integer('class_id') ?: null;
        }
        if (!$classId) {
            $firstClass = Classes::query()->orderBy('id')->first();
            $classId = $firstClass ? (int) $firstClass->id : null;
        }
        $examId = $request->integer('resolved_exam_id') ?: ($request->integer('exam_id') ?: null);

        $template = $this->fixedTemplate();
        $printData = $this->buildPrintData($classId, $examId, $student ? (int) $student->id : null, $request);
        $sheets = [compact('template', 'printData')];
        $selectedDateYmd = $this->parseDateToYmd($printData['date'] ?? null);
        return view('backend.AcademicsModules.marksheet_print', compact('template', 'printData', 'sheets', 'selectedDateYmd'));
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
            ->get()
            ->sortBy(function ($marksheet) {
                return strtolower($marksheet->snapshot['student_name'] ?? $marksheet->snapshot['print_data']['student_meta']['student_name'] ?? '');
            }, SORT_NATURAL)
            ->values();

        if ($marksheets->isEmpty()) {
            return redirect()->route('marksheet')->with('error', 'Please select at least one generated marksheet to print.');
        }

        $sheets = $marksheets->map(fn ($marksheet) => $this->savedPrintSheet($marksheet, $request))->values();
        Marksheet::query()->whereIn('id', $marksheets->pluck('id'))->update(['printed_at' => now()]);
        $template = $this->fixedTemplate('Bulk Generated Marksheets');

        $firstSheetDate = $sheets->first()['printData']['date'] ?? null;
        $selectedDateYmd = $this->parseDateToYmd($request->input('print_date', $request->input('date', $firstSheetDate)));

        return view('backend.AcademicsModules.marksheet_print', compact('template', 'sheets', 'selectedDateYmd'));
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

        $count = Marksheet::query()
            ->whereIn('id', $ids)
            ->delete();

        $redirectParams = array_filter($request->only([
            'per_page',
            'generated_search',
            'generated_class_id',
            'generated_exam_id',
            'class_id',
            'section_name',
            'exam_id',
        ]));

        return redirect()->route('marksheet', $redirectParams)
            ->with('success', "{$count} generated marksheet(s) deleted successfully.");
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

        $request = $request ?: request();
        $snapshotDate = $marksheet->snapshot['print_date'] ?? ($printData['date'] ?? null);
        $customDate = $request->input('print_date', $request->input('date', $snapshotDate));
        $formattedDate = $this->formatMarksheetDate($customDate, $snapshotDate ?: now()->format('F d, Y'));

        if ($marksheet->student_id && $marksheet->class_id) {
            $snapshotAttendance = $printData['attendance'] ?? '';
            $cleanedSnapshotAttendance = str_contains($snapshotAttendance, '%') ? '' : $snapshotAttendance;
            $liveRequest = $request->duplicate(
                array_merge($request->query(), array_filter([
                    'attendance' => $request->input('attendance', $cleanedSnapshotAttendance),
                    'date' => $formattedDate,
                    'print_date' => $formattedDate,
                ], fn ($value) => $value !== null)),
                $request->request->all()
            );
            $livePrintData = $this->buildPrintData(
                (int) $marksheet->class_id,
                $marksheet->exam_id ? (int) $marksheet->exam_id : null,
                (int) $marksheet->student_id,
                $liveRequest
            );

            if (!app(\App\Services\StaffReportAccessService::class)->isStaffUser()
                && !empty($livePrintData['is_blank'])
                && !empty($printData['rows'])
                && empty($printData['is_blank'])) {
                $printData['date'] = $formattedDate;
                if (!empty($livePrintData['attendance']) && $livePrintData['attendance'] !== '/') {
                    $printData['attendance'] = $livePrintData['attendance'];
                }
            } else {
                $printData = $livePrintData;
                // Preserve snapshot PT marks if live recalculation returned 0 or empty for any subject
                if (!empty($marksheet->snapshot['print_data']['rows']) && !empty($printData['rows'])) {
                    $snapshotRows = collect($marksheet->snapshot['print_data']['rows'])->keyBy('subject_id');
                    foreach ($printData['rows'] as &$row) {
                        $subId = $row['subject_id'] ?? null;
                        $snapRow = $subId ? $snapshotRows->get($subId) : null;
                        if ($snapRow && !empty($snapRow['terms'])) {
                            foreach ($row['terms'] as $tIdx => &$term) {
                                $snapTerm = $snapRow['terms'][$tIdx] ?? null;
                                if ($snapTerm && !empty($snapTerm['cells'])) {
                                    $snapPt = (string) ($snapTerm['cells'][0] ?? '');
                                    $livePt = (string) ($term['cells'][0] ?? '');
                                    if (($livePt === '0' || $livePt === '') && $snapPt !== '' && $snapPt !== '0') {
                                        $term['cells'][0] = $snapPt;
                                    }
                                }
                            }
                            unset($term);
                        }
                    }
                    unset($row);
                }
            }
        }

        foreach (['student', 'class', 'exam'] as $key) {
            if (isset($printData[$key]) && is_array($printData[$key])) {
                $printData[$key] = (object) $printData[$key];
            }
        }
        $printData['date'] = $formattedDate;

        // Ensure only subjects belonging to the student's assigned combination are displayed
        if ($marksheet->student_id && $marksheet->class_id) {
            $combSubjects = $this->subjectsFor((int) $marksheet->class_id, (int) $marksheet->student_id);
            if ($combSubjects->isNotEmpty()) {
                $allowedSubjectIds = $combSubjects->pluck('id')->map(fn ($id) => (int) $id)->all();
                $allowedSubjectNames = $combSubjects->pluck('subject_name')->map(fn ($n) => strtolower(trim((string) $n)))->all();
                $categoryOf = function ($name) {
                    $n = preg_replace('/[^a-z0-9]/', '', strtolower((string) $name));
                    if ($n === 'discipline') return 'discipline';
                    if (str_contains($n, 'workedu') || str_contains($n, 'workexp')) return 'work_education';
                    if (str_contains($n, 'artedu') || $n === 'art' || $n === 'arts' || str_contains($n, 'fineart') || str_contains($n, 'drawing')) return 'art_education';
                    if (str_contains($n, 'health') || str_contains($n, 'physicaledu') || str_contains($n, 'pewellbeing') || str_contains($n, 'wellbeing') || $n === 'pe') return 'health_pe';
                    return $n;
                };

                // Filter snapshot rows if they have extra subjects
                if (!empty($printData['rows'])) {
                    $printData['rows'] = array_values(array_filter($printData['rows'], function ($r) use ($allowedSubjectIds) {
                        $sid = isset($r['subject_id']) ? (int) $r['subject_id'] : null;
                        return $sid && in_array($sid, $allowedSubjectIds, true);
                    }));
                }

                // Check co-scholastic subjects in student's combination
                $combCoScholasticSubs = $combSubjects->filter(function ($s) use ($categoryOf) {
                    $subType = strtolower(trim((string) ($s->subject_type ?? '')));
                    $isSkilled = in_array($subType, ['skilled', 'skill', 'skill-based', 'skill based'], true) || str_contains($subType, 'skill');
                    $isNonAcademic = (bool) preg_match('/non[\s-]*academic/i', $subType);
                    $cat = $categoryOf($s->subject_name ?? '');
                    return $isSkilled || $isNonAcademic || in_array($cat, ['discipline', 'work_education', 'art_education', 'health_pe'], true);
                });

                if ($combCoScholasticSubs->isEmpty()) {
                    $printData['layout']['co_scholastic_sections'] = [];
                } else {
                    $allowedCoNames = $combCoScholasticSubs->pluck('subject_name')->map(fn ($n) => strtolower(trim((string) $n)))->all();
                    $allowedCoCats = $combCoScholasticSubs->map(fn ($s) => $categoryOf($s->subject_name))->all();

                    if (!empty($printData['layout']['co_scholastic_sections'])) {
                        foreach ($printData['layout']['co_scholastic_sections'] as &$sec) {
                            if (!empty($sec['items'])) {
                                $sec['items'] = array_values(array_filter($sec['items'], function ($item) use ($categoryOf, $allowedCoCats, $allowedCoNames) {
                                    $cat = $categoryOf($item);
                                    if (in_array($cat, $allowedCoCats, true)) {
                                        return true;
                                    }
                                    $normItem = strtolower(trim((string) $item));
                                    return in_array($normItem, $allowedCoNames, true)
                                        || collect($allowedCoNames)->contains(fn ($n) => str_contains($normItem, $n) || str_contains($n, $normItem));
                                }));
                            }
                        }
                        unset($sec);

                        $hasAnyItem = collect($printData['layout']['co_scholastic_sections'])->sum(fn ($s) => count($s['items'] ?? [])) > 0;
                        if (!$hasAnyItem) {
                            $printData['layout']['co_scholastic_sections'] = [];
                        }
                    }
                }
                $reportAccess = app(\App\Services\StaffReportAccessService::class);
                if ($reportAccess->isStaffUser()) {
                    $student = $this->studentById((int) $marksheet->student_id);
                    $class = Classes::query()->find((int) $marksheet->class_id);
                    $section = $student->section_name ?? $class?->section_name;
                    abort_unless(
                        $reportAccess->canAccess((int) $marksheet->class_id, $section),
                        403,
                        'You are not authorized to access reports for this class, section, or subject.'
                    );
                    $printData['rows'] = $reportAccess
                        ->filterSubjectData($printData['rows'] ?? [], (int) $marksheet->class_id, $section, 'subject_id')
                        ->all();
                }
            }
        }

        if (empty($printData['is_blank'])) {
            $term2Pct = $this->calculateTerm2Percentage($printData);
            if ($term2Pct !== null) {
                $printData['percentage'] = $term2Pct;
            }
        }

        if (!empty($printData['attendance']) && str_contains($printData['attendance'], '%')) {
            $liveAtt = $this->collectiveAttendanceForMarksheet(
                $marksheet->student_id ? (int) $marksheet->student_id : null,
                $marksheet->class_id ? (int) $marksheet->class_id : null,
                $marksheet->exam_id ? (int) $marksheet->exam_id : null,
                $this->resolveDynamicSessionYear($request)
            );
            $printData['attendance'] = ($liveAtt !== '' && $liveAtt !== '/') ? $liveAtt : '';
        }

        return compact('template', 'printData');
    }

    private function calculateTerm2Percentage(array $printData): ?float
    {
        $terms = $printData['layout']['scholastic_terms'] ?? [];
        if (empty($terms) || empty($printData['rows'])) {
            return null;
        }

        $term2Index = null;
        foreach ($terms as $tIdx => $tCfg) {
            $tKey = strtolower((string) ($tCfg['key'] ?? ''));
            $tLabel = strtolower((string) ($tCfg['label'] ?? ''));
            $tExamType = strtolower((string) ($tCfg['exam_type'] ?? ''));
            if ($tKey === 'term_2' || str_contains($tLabel, 'term ii') || str_contains($tLabel, 'term 2') || $tExamType === 'term 2') {
                $term2Index = $tIdx;
                break;
            }
        }
        if ($term2Index === null && count($terms) > 1) {
            $term2Index = count($terms) - 1;
        }
        if ($term2Index === null && count($terms) > 0) {
            $term2Index = 0;
        }

        if ($term2Index === null || !isset($terms[$term2Index])) {
            return null;
        }

        $term2Cfg = $terms[$term2Index];
        $term2MaxPerSubject = $this->termMax($term2Cfg);
        if ($term2MaxPerSubject <= 0) {
            $term2MaxPerSubject = 100.0;
        }

        $term2Obtained = 0.0;
        $term2Max = 0.0;
        foreach ($printData['rows'] as $row) {
            if (!empty($row['special_subject_formatting']) || !empty($row['is_non_academic'])) {
                continue;
            }
            $term2Obtained += (float) ($row['terms'][$term2Index]['total'] ?? 0);
            $term2Max += $term2MaxPerSubject;
        }

        if ($term2Max <= 0) {
            return null;
        }

        return $this->roundPercentage(($term2Obtained / $term2Max) * 100);
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
        $examMap = $this->marksheetExamMap($classId, $layout, $exam);
        $rollNo = $this->rollNoFor($studentId, $classId, $examId);

        $normClass = \App\Academic\AcademicHelper::normalizeClassIdentifier($class?->class_name);
        $isClass10Or12 = in_array($normClass, ['10', '12'], true);

        if ($isClass10Or12) {
            $layout['title'] = 'Pre Board Examination Report Card';
            foreach ($layout['scholastic_terms'] as &$tCfg) {
                if (($tCfg['key'] ?? '') === 'term_2') {
                    $tCfg['label'] = 'Pre Board';
                }
            }
            unset($tCfg);
        }

        $hasEnteredMarks = $studentId && $marks->isNotEmpty() && $marks->contains(function ($item) {
            return ($item->mark_theory !== null && $item->mark_theory !== '')
                || ($item->total_marks !== null && $item->total_marks !== '')
                || (!empty($item->internal_assessment_marks) && $item->internal_assessment_marks !== '[]' && $item->internal_assessment_marks !== '{}')
                || (!empty($item->grade) || !empty($item->overall_grade));
        });

        $rows = [];
        $grandTotal = 0.0;
        $maxTotal = 0.0;
        $hasFailingSubject = false;

        $term2Index = null;
        foreach ($layout['scholastic_terms'] as $tIdx => $tCfg) {
            $tKey = strtolower((string) ($tCfg['key'] ?? ''));
            $tLabel = strtolower((string) ($tCfg['label'] ?? ''));
            $tExamType = strtolower((string) ($tCfg['exam_type'] ?? ''));
            if ($tKey === 'term_2' || str_contains($tLabel, 'term ii') || str_contains($tLabel, 'term 2') || str_contains($tLabel, 'annual') || str_contains($tLabel, 'pre board') || $tExamType === 'term 2') {
                $term2Index = $tIdx;
                break;
            }
        }
        if ($term2Index === null && count($layout['scholastic_terms']) > 1) {
            $term2Index = count($layout['scholastic_terms']) - 1;
        }

        $term2GrandTotal = 0.0;
        $term2MaxTotal = 0.0;

        $categoryOf = function ($name) {
            $n = preg_replace('/[^a-z0-9]/', '', strtolower((string) $name));
            if ($n === 'discipline') {
                return 'discipline';
            }
            if (str_contains($n, 'workedu') || str_contains($n, 'workexp')) {
                return 'work_education';
            }
            if (str_contains($n, 'artedu') || $n === 'art' || $n === 'arts' || str_contains($n, 'fineart') || str_contains($n, 'drawing')) {
                return 'art_education';
            }
            if (str_contains($n, 'health') || str_contains($n, 'physicaledu') || str_contains($n, 'pewellbeing') || str_contains($n, 'wellbeing') || $n === 'pe') {
                return 'health_pe';
            }
            return $n;
        };

        $disciplineSubjects = [];

        foreach ($subjects as $subject) {
            $subjectId = (int) $subject->id;
            $subjectName = $this->marksheetSubjectName($subject->subject_name ?? $subject->name ?? '-');

            // Exclude dummy test subjects like te4st or test
            if (Str::startsWith(Str::lower($subjectName), 'test') || Str::contains(Str::lower($subjectName), ['te4st', 'dummy'])) {
                continue;
            }

            $subType = strtolower(trim((string) ($subject->subject_type ?? '')));
            $isSkilled = in_array($subType, ['skilled', 'skill', 'skill-based', 'skill based'], true)
                || str_contains($subType, 'skill');
            $isNonAcademic = (bool) preg_match('/non[\s-]*academic/i', $subType);
            $cat = $categoryOf($subjectName);
            $isCoScholasticCat = in_array($cat, ['discipline', 'work_education', 'art_education', 'health_pe'], true);
            $isDisciplineSubject = $isSkilled || $isNonAcademic || $isCoScholasticCat;

            if ($isDisciplineSubject) {
                $termGrades = [];
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

                    $savedGrade = $mark ? ($mark->grade ?: $mark->overall_grade ?: '') : '';
                    if ($savedGrade !== '') {
                        $termGrades[$termIndex] = $this->normalizeToThreePointGrade($savedGrade);
                    } else {
                        $termValues = $this->cbseTermValues($mark, $ptMark, $ptExam);
                        $termObtained = (float) $termValues['final_total'];
                        if ($termObtained > 0) {
                            $termGrades[$termIndex] = $this->calculateThreePointGrade($termObtained, 100);
                        } else {
                            $termGrades[$termIndex] = 'A';
                        }
                    }
                }

                $disciplineSubjects[] = [
                    'id' => $subjectId,
                    'title' => $subjectName,
                    'term_grades' => $termGrades,
                ];

                continue; // Exclude from scholastic $rows and totals
            }

            $row = [
                'subject_id' => $subjectId,
                'subject' => $subjectName,
                'special_subject_formatting' => \App\Academic\AcademicHelper::isSpecialFormattingForClass($subject, $classId, $class?->class_name),
                'is_non_academic' => false,
                'terms' => [],
                'grand_total' => '',
                'grade' => '',
                'result' => '',
            ];

            foreach ($layout['scholastic_terms'] as $termIndex => $term) {
                if (!$hasEnteredMarks) {
                    $row['terms'][$termIndex] = [
                        'cells' => array_fill(0, count($term['columns'] ?? []), ''),
                        'total' => '',
                        'grade' => '',
                        'result' => '',
                    ];
                    continue;
                }

                $termExam = $this->examForTerm($term, $termIndex, $examMap, $exam);
                $ptExam = $this->ptExamForTerm($term, $termIndex, $examMap);
                $termExamId = $termExam?->id;
                $ptExamId = $ptExam?->id;
                $mark = $marks->first(function ($item) use ($subject, $termExamId) {
                    return (int) ($item->subject_id ?? 0) === (int) $subject->id
                        && (!$termExamId || (int) ($item->exam_id ?? 0) === (int) $termExamId);
                });

                if (!$mark) {
                    $targetCanonical = $termIndex === 0 ? 'term1' : 'term2';
                    $mark = $marks->first(function ($item) use ($subject, $examMap, $targetCanonical) {
                        if ((int) ($item->subject_id ?? 0) !== (int) $subject->id) {
                            return false;
                        }
                        $itemExam = $examMap->first(fn ($e) => (int) ($e->id ?? 0) === (int) ($item->exam_id ?? 0));
                        return $itemExam && $this->canonicalExamType($itemExam->exam_type, $itemExam->exam_name) === $targetCanonical;
                    });
                }

                $ptMark = null;
                if ($ptExamId) {
                    $ptMark = $marks->first(function ($item) use ($subject, $ptExamId) {
                        return (int) ($item->subject_id ?? 0) === (int) $subject->id
                            && (int) ($item->exam_id ?? 0) === (int) $ptExamId;
                    });
                }

                if (!$ptMark) {
                    $targetPtCanonical = $termIndex === 0 ? 'pt1' : 'pt2';
                    $ptMark = $marks->first(function ($item) use ($subject, $examMap, $targetPtCanonical) {
                        if ((int) ($item->subject_id ?? 0) !== (int) $subject->id) {
                            return false;
                        }
                        $itemExam = $examMap->first(fn ($e) => (int) ($e->id ?? 0) === (int) ($item->exam_id ?? 0));
                        if ($itemExam) {
                            return $this->canonicalExamType($itemExam->exam_type, $itemExam->exam_name) === $targetPtCanonical;
                        }
                        return false;
                    });
                }

                if ($isNonAcademic) {
                    $savedGrade = $mark ? ($mark->grade ?: $mark->overall_grade ?: '') : '';
                    $row['terms'][$termIndex] = [
                        'cells' => array_fill(0, count($term['columns'] ?? []), ''),
                        'total' => '',
                        'grade' => $savedGrade,
                        'result' => '',
                    ];
                    continue;
                }

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
                    if ($term2Index !== null && $termIndex === $term2Index) {
                        $term2GrandTotal += $termTotal;
                        $term2MaxTotal += $termMax;
                    }
                }
                $row['terms'][$termIndex] = [
                    'cells' => $cells,
                    'total' => $termTotal,
                    'grade' => $this->gradeFor($termTotal, $termMax, $grading, $class->class_name ?? null, $subject->subject_type ?? null),
                    'result' => $termFailed ? 'Fail' : 'Pass',
                ];
                $row['grand_total'] = (float) ($row['grand_total'] ?? 0) + $termTotal;
            }

            if ($hasEnteredMarks) {
                // Determine Term 2 index for Grade calculation
                $term2Index = null;
                foreach ($layout['scholastic_terms'] as $tIdx => $tCfg) {
                    $tKey = strtolower((string) ($tCfg['key'] ?? ''));
                    $tLabel = strtolower((string) ($tCfg['label'] ?? ''));
                    $tExamType = strtolower((string) ($tCfg['exam_type'] ?? ''));
                    if ($tKey === 'term_2' || str_contains($tLabel, 'term ii') || str_contains($tLabel, 'term 2') || str_contains($tLabel, 'annual') || str_contains($tLabel, 'pre board') || $tExamType === 'term 2') {
                        $term2Index = $tIdx;
                        break;
                    }
                }
                if ($term2Index === null && count($layout['scholastic_terms']) > 1) {
                    $term2Index = count($layout['scholastic_terms']) - 1;
                }

                if ($isNonAcademic) {
                    $term2Grade = ($term2Index !== null && isset($row['terms'][$term2Index])) ? $row['terms'][$term2Index]['grade'] : '';
                    $row['grade'] = $term2Grade ?: (collect($row['terms'])->pluck('grade')->filter()->last() ?: '');
                    $row['grand_total'] = '';
                    $row['result'] = '';
                } else {
                    $rowMax = max(1, $this->rowMax($layout));
                    $rowFailed = $this->isBelowFailPercent((float) $row['grand_total'], $rowMax, $exam);
                    if (empty($row['special_subject_formatting'])) {
                        $hasFailingSubject = $hasFailingSubject || $rowFailed;
                    }

                    if ($term2Index !== null && isset($row['terms'][$term2Index])) {
                        $term2Total = (float) ($row['terms'][$term2Index]['total'] ?? 0);
                        $term2Max = max(1, (float) $this->termMax($layout['scholastic_terms'][$term2Index]));
                        $row['grade'] = $row['terms'][$term2Index]['grade'] ?: $this->gradeFor($term2Total, $term2Max, $grading, $class->class_name ?? null, $subject->subject_type ?? null);
                    } else {
                        $row['grade'] = $this->gradeFor((float) $row['grand_total'], $rowMax, $grading, $class->class_name ?? null, $subject->subject_type ?? null);
                    }

                    $row['result'] = $rowFailed ? 'Fail' : 'Pass';
                }
            }
            $rows[] = $row;
        }

        // Map co-scholastic grades from saved marks if available
        $allNonAcademicMarks = $marks->filter(function ($item) {
            return !empty($item->grade) || !empty($item->overall_grade);
        });

        $subjectsMap = Schema::connection('dynamic')->hasTable('subjectmaster')
            ? DB::connection('dynamic')->table('subjectmaster')->pluck('subject_name', 'id')
            : collect();

        $standardCategories = ['discipline', 'work_education', 'art_education', 'health_pe'];

        $assignedStandard = [];
        $extraCustom = [];

        foreach ($disciplineSubjects as $ds) {
            $cat = $categoryOf($ds['title']);
            if (in_array($cat, $standardCategories, true)) {
                if (!isset($assignedStandard[$cat])) {
                    $assignedStandard[$cat] = $ds['title'];
                }
            } else {
                if (!isset($extraCustom[$cat])) {
                    $extraCustom[$cat] = $ds['title'];
                }
            }
        }

        // If no co-scholastic or discipline subjects are assigned, hide the co-scholastic sections completely!
        if (empty($disciplineSubjects)) {
            $layout['co_scholastic_sections'] = [];
        } else {
            $coSections = [];
            foreach (($layout['co_scholastic_sections'] ?? []) as $section) {
                $secTitle = strtolower((string) ($section['title'] ?? ''));
                $isTerm2 = (bool) preg_match('/\bterm[\s-]*(?:ii|2)\b/i', $secTitle);
                $isTerm1 = !$isTerm2 && (bool) preg_match('/\bterm[\s-]*(?:i|1)\b/i', $secTitle);

                $targetTermIndex = $isTerm2 ? 1 : ($isTerm1 ? 0 : 0);
                $targetExam = null;
                if ($targetTermIndex !== null && isset($layout['scholastic_terms'][$targetTermIndex])) {
                    $targetExam = $this->examForTerm($layout['scholastic_terms'][$targetTermIndex], $targetTermIndex, $examMap, $exam);
                }

                $items = [];
                $seenCategories = [];

                // 1. Only include standard items that are actually assigned in the student's combination!
                foreach (($section['items'] ?? []) as $it) {
                    $cat = $categoryOf($it);
                    if (isset($assignedStandard[$cat])) {
                        if (!isset($seenCategories[$cat])) {
                            $seenCategories[$cat] = true;
                            $items[] = $assignedStandard[$cat];
                        }
                    }
                }

                // 2. Add extra custom discipline/skill subjects (like Food, Block Print)
                foreach ($extraCustom as $eCat => $eTitle) {
                    if (!isset($seenCategories[$eCat])) {
                        $seenCategories[$eCat] = true;
                        $items[] = ucwords(strtolower($eTitle));
                    }
                }

                if (empty($items)) {
                    continue;
                }

                $grades = [];
                foreach ($items as $item) {
                    $itemCat = $categoryOf($item);
                    $itemName = strtolower(trim((string) $item));
                    $foundGrade = null;

                    // 1. Check if this item is an assigned discipline subject
                    $matchedDiscipline = collect($disciplineSubjects)->first(function ($ds) use ($itemCat, $itemName, $categoryOf) {
                        if ($categoryOf($ds['title']) === $itemCat) {
                            return true;
                        }
                        $dName = strtolower(trim($ds['title']));
                        return $dName === $itemName || str_contains($dName, $itemName) || str_contains($itemName, $dName);
                    });

                    if ($matchedDiscipline && isset($matchedDiscipline['term_grades'][$targetTermIndex])) {
                        $foundGrade = $matchedDiscipline['term_grades'][$targetTermIndex];
                    }

                    // 2. Lookup in saved marks
                    if (!$foundGrade) {
                        foreach ($allNonAcademicMarks as $m) {
                            $sRaw = (string) ($subjectsMap[$m->subject_id] ?? '');
                            $sCat = $categoryOf($sRaw);
                            $sName = strtolower(trim($sRaw));
                            if ($sCat === $itemCat || $sName === $itemName || str_contains($sName, $itemName) || str_contains($itemName, $sName)) {
                                if ($targetExam && (int) $m->exam_id === (int) $targetExam->id) {
                                    $foundGrade = $this->normalizeToThreePointGrade($m->grade ?: $m->overall_grade);
                                    break;
                                } elseif (!$targetExam && empty($foundGrade)) {
                                    $foundGrade = $this->normalizeToThreePointGrade($m->grade ?: $m->overall_grade);
                                }
                            }
                        }
                    }

                    if (!$foundGrade) {
                        foreach ($allNonAcademicMarks as $m) {
                            $sRaw = (string) ($subjectsMap[$m->subject_id] ?? '');
                            $sCat = $categoryOf($sRaw);
                            $sName = strtolower(trim($sRaw));
                            if ($sCat === $itemCat || $sName === $itemName || str_contains($sName, $itemName) || str_contains($itemName, $sName)) {
                                $foundGrade = $this->normalizeToThreePointGrade($m->grade ?: $m->overall_grade);
                                break;
                            }
                        }
                    }

                    $grades[$item] = $foundGrade ?: ($grading['co_scholastic_default_grade'] ?? 'A');
                }
                $section['items'] = $items;
                $section['grades'] = $grades;
                $coSections[] = $section;
            }
            $layout['co_scholastic_sections'] = $coSections;
        }

        if (!$hasEnteredMarks) {
            $grandTotal = null;
            $percentage = null;
            $result = '';
            $remarks = '';
        } else {
            // Percentage calculated based exclusively on Term 2 marks
            $term2PercentageRaw = $term2MaxTotal > 0 ? ($term2GrandTotal / $term2MaxTotal) * 100 : null;
            $percentageRaw = $term2PercentageRaw ?? ($maxTotal > 0 ? ($grandTotal / $maxTotal) * 100 : null);
            $percentage = $percentageRaw !== null ? $this->roundPercentage($percentageRaw) : null;
            $result = $percentageRaw === null
                ? ''
                : ($hasFailingSubject || $this->isBelowFailPercent($grandTotal, $maxTotal, $exam) ? 'Fail' : 'Pass');
            $remarks = $result === 'Fail'
                ? 'Needs Improvement'
                : ($logic['default_remark'] ?? 'Passed and promoted to next class.');
        }

        return [
            'is_blank' => !$hasEnteredMarks,
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
            'attendance' => $hasEnteredMarks ? $this->collectiveAttendanceForMarksheet($studentId, $classId, $examId, $this->resolveDynamicSessionYear($request), $request->input('attendance', '')) : '',
            'date' => $this->formatMarksheetDate($request->input('print_date', $request->input('date')), now()->format('F d, Y')),
            'session_year' => $this->resolveDynamicSessionYear($request),
        ];
    }

    private function collectiveAttendanceForMarksheet(?int $studentId, ?int $classId, ?int $examId, ?string $sessionYear, string $fallback = ''): string
    {
        if (!$studentId || !$classId || !Schema::connection('dynamic')->hasTable('academic_attendance_collectives') || !Schema::connection('dynamic')->hasTable('academic_attendance_collective_details')) {
            return $fallback;
        }

        $sessionVariants = $this->sessionYearVariants($sessionYear);
        $record = DB::connection('dynamic')
            ->table('academic_attendance_collective_details as acd')
            ->join('academic_attendance_collectives as ac', 'acd.collective_id', '=', 'ac.id')
            ->where('ac.is_delete', 0)
            ->where('acd.student_id', $studentId)
            ->where('ac.class_id', $classId)
            ->when($examId, fn ($query) => $query->where('ac.exam_id', $examId))
            ->when(!empty($sessionVariants), fn ($query) => $query->whereIn('ac.academic_session', $sessionVariants))
            ->orderByDesc('ac.id')
            ->select('acd.attendance_percentage', 'acd.present_days', 'acd.working_days')
            ->first();

        if (!$record) {
            $record = DB::connection('dynamic')
                ->table('academic_attendance_collective_details as acd')
                ->join('academic_attendance_collectives as ac', 'acd.collective_id', '=', 'ac.id')
                ->where('ac.is_delete', 0)
                ->where('acd.student_id', $studentId)
                ->where('ac.class_id', $classId)
                ->when(!empty($sessionVariants), fn ($query) => $query->whereIn('ac.academic_session', $sessionVariants))
                ->orderByDesc('ac.id')
                ->select('acd.attendance_percentage', 'acd.present_days', 'acd.working_days')
                ->first();
        }

        if ($record && (float) $record->working_days > 0) {
            $pres = ((int) $record->present_days == $record->present_days) ? (int) $record->present_days : (float) $record->present_days;
            $work = (int) $record->working_days;
            return "{$pres} / {$work}";
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

            if ($classId) {
                $classIds = $this->marksClassIdsFor($classId);
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

    private function defaultLayoutConfig(): array
    {
        return [
            'paper' => 'a4-landscape',
            'title' => 'Annual Examination Report Card',
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
                    'label' => 'Half Yearly Exam',
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
                    'label' => 'Annual Exam',
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

    private function studentsForClass(?int $classId = null, ?string $className = null, ?string $sectionName = null)
    {
        if (!Schema::connection('dynamic')->hasTable('student_registration')) {
            return collect();
        }

        if (!$classId && !$className) {
            return collect();
        }

        $class = $classId ? Classes::query()->find($classId) : null;
        if (!$className && $class) {
            $className = $class->class_name;
        }

        $siblingClassIds = [];
        if ($className) {
            $numericClass = \App\Academic\AcademicHelper::fromRoman($className) ?: $className;
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


        $sectionClassIds = [];
        if ($sectionName) {
            $secQuery = Classes::query()
                ->where(function ($q) use ($sectionName) {
                    $q->where('section_name', $sectionName)
                        ->orWhere('section_name', 'like', '%' . $sectionName . '%');
                });
            if ($className) {
                $secQuery->where(function ($q) use ($className) {
                    $q->where('class_name', $className)
                        ->orWhere('class_name', ltrim($className, '0'));
                });
            }
            $sectionClassIds = $secQuery->pluck('id')->map(fn ($id) => (string) $id)->all();
        }

        $students = DB::connection('dynamic')
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
            })
            ->when($sectionName, function ($q) use ($sectionName, $sectionClassIds) {
                $q->where(function ($sub) use ($sectionName, $sectionClassIds) {
                    $sub->where('section_name', $sectionName)
                        ->orWhere('section_name', 'like', '%' . $sectionName . '%');
                    if (!empty($sectionClassIds)) {
                        $sub->orWhereIn('class_id', $sectionClassIds);
                    }
                });
            })
            ->orderBy('student_name')
            ->limit(500)
            ->get();

        // Enforce student visibility rule: Selected Date >= Student Registration Date
        $asOfDate = request()->input('date', request()->input('print_date', request()->input('exam_date')));
        if (empty($asOfDate)) {
            $asOfDate = now()->toDateString();
        }
        $students = $this->filterStudentsByRegistrationDate($students, $asOfDate);

        $reportAccess = app(\App\Services\StaffReportAccessService::class);
        if (!$reportAccess->isStaffUser()) {
            return $students;
        }

        return $students->filter(function ($student) use ($sectionName, $classId, $className, $reportAccess) {
            $studentClass = $student->class_id ?: $student->class_name ?: $classId ?: $className;
            $studentClassRow = !empty($student->class_id) && is_numeric($student->class_id)
                ? Classes::query()->find((int) $student->class_id)
                : null;
            $studentSection = $student->section_name ?: $studentClassRow?->section_name ?: $sectionName;
            return $studentClass && $studentSection && $reportAccess->canAccess($studentClass, $studentSection);
        })->values();
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
        $subjectColumns = $this->marksheetSubjectColumns();
        $assigned = collect();

        // 1. Check student-specific assignment
        if ($studentId && Schema::connection('dynamic')->hasTable('subject_assign_student') && Schema::connection('dynamic')->hasTable('combination_subject')) {
            $hasIsDelete = Schema::connection('dynamic')->hasColumn('subject_assign_student', 'is_delete');
            $query = DB::connection('dynamic')->table('subject_assign_student as sas');
            if ($class && !empty($class->class_name)) {
                $norm = AcademicHelper::normalizeClassIdentifier($class->class_name);
                $query->where(function ($q) use ($class, $norm) {
                    $q->where('sas.class_name', $class->class_name)
                      ->orWhere('sas.class_name', $norm)
                      ->orWhere('sas.class_name', ltrim($class->class_name, '0'));
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
                    ->select($subjectColumns)
                    ->orderBy('cs.subject_order')
                    ->orderBy('s.subject_name')
                    ->get()
                    ->unique('id')
                    ->values();
            }
        }

        // If combination subjects found, return ONLY those assigned subjects!
        // Strictly do NOT merge class-level skilled subjects or extra marked subjects
        if ($assigned->isNotEmpty()) {
            return $assigned;
        }

        // 2. Fallback: Check class-level assigned subjects (only when student has no assigned combination)
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
                ->select($subjectColumns)
                ->orderBy('s.subject_name')
                ->get()
                ->unique('id')
                ->values();
        }

        // 3. Fallback: Include any subjects where marks have been entered for this student (only when student has no assigned combination)
        if ($studentId && Schema::connection('dynamic')->hasTable('academic_students_marks') && Schema::connection('dynamic')->hasTable('previosly_saved_marks_entry')) {
            $markedSubjectIds = DB::connection('dynamic')
                ->table('academic_students_marks as sm')
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
                    ->select($subjectColumns)
                    ->get();
                $assigned = $assigned->concat($extraSubjects)->unique('id')->values();
            }
        }

        if ($assigned->isNotEmpty()) {
            return $assigned;
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
                        : DB::raw('0 as do_not_print_in_main_scholastic_area'),
                    Schema::connection('dynamic')->hasColumn('subjectmaster', 'special_formatting_classes')
                        ? 'special_formatting_classes'
                        : DB::raw('NULL as special_formatting_classes')
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

        $columns[] = Schema::connection('dynamic')->hasColumn('subjectmaster', 'special_formatting_classes')
            ? 's.special_formatting_classes'
            : DB::raw('NULL as special_formatting_classes');

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
        $classIds = $this->marksClassIdsFor($classId);
        if (!empty($classIds) && Schema::connection('dynamic')->hasTable('academic_exam')) {
            $examIds = array_merge($examIds, DB::connection('dynamic')
                ->table('academic_exam')
                ->whereIn('class_id', $classIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all());
        }
        $examIds = array_values(array_unique(array_filter($examIds)));

        $query = DB::connection('dynamic')
            ->table('academic_students_marks as sm')
            ->join('previosly_saved_marks_entry as me', 'me.id', '=', 'sm.marks_id')
            ->where('sm.student_id', (string) $studentId);

        if (count($examIds) > 0) {
            $query->whereIn('me.exam_id', array_map('strval', $examIds));
        }

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

        $marks = $query->select('sm.*', 'me.exam_id', 'me.subject_id', 'me.class_id')->get();

        // If no marks were found using the examIds filter, fetch all marks for this student
        if ($marks->isEmpty() && count($examIds) > 0) {
            $fallbackQuery = DB::connection('dynamic')
                ->table('academic_students_marks as sm')
                ->join('previosly_saved_marks_entry as me', 'me.id', '=', 'sm.marks_id')
                ->where('sm.student_id', (string) $studentId);

            if (Schema::connection('dynamic')->hasColumn('academic_students_marks', 'is_delete')) {
                $fallbackQuery->where(function ($q) {
                    $q->where('sm.is_delete', 0)->orWhereNull('sm.is_delete');
                });
            }
            if (Schema::connection('dynamic')->hasColumn('previosly_saved_marks_entry', 'is_delete')) {
                $fallbackQuery->where(function ($q) {
                    $q->where('me.is_delete', 0)->orWhereNull('me.is_delete');
                });
            }

            $marks = $fallbackQuery->select('sm.*', 'me.exam_id', 'me.subject_id', 'me.class_id')->get();
        }

        return $marks;
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
            $classIds = $this->marksClassIdsFor($classId);
            $query->when(!empty($classIds), fn ($q) => $q->whereIn('class_id', $classIds));
        }

        $exams = $query->get();
        if ($selectedExam && !$exams->contains('id', $selectedExam->id)) {
            $exams->push($selectedExam);
        }

        $map = collect();
        foreach ($exams as $exam) {
            $canonical = $this->canonicalExamType($exam->exam_type, $exam->exam_name);
            if ($canonical !== '' && !$map->has($canonical)) {
                $map->put($canonical, $exam);
            }
            $normType = $this->normalizeExamType($exam->exam_type);
            if ($normType !== '' && !$map->has($normType)) {
                $map->put($normType, $exam);
            }
            if (!empty($exam->exam_name)) {
                $normName = $this->normalizeExamType($exam->exam_name);
                if ($normName !== '' && !$map->has($normName)) {
                    $map->put($normName, $exam);
                }
            }
            $map->put((string) $exam->id, $exam);
        }

        return $map;
    }

    private function examForTerm(array $term, int $termIndex, $examMap, $selectedExam)
    {
        if (!empty($term['exam_id'])) {
            return Exam::query()->find((int) $term['exam_id']);
        }

        $termType = $term['exam_type'] ?? ($termIndex === 0 ? 'Term 1' : 'Term 2');
        $canonical = $this->canonicalExamType($termType);

        if ($selectedExam && $this->canonicalExamType($selectedExam->exam_type, $selectedExam->exam_name) === $canonical) {
            return $selectedExam;
        }

        if ($examMap->has($canonical)) {
            return $examMap->get($canonical);
        }

        $norm = $this->normalizeExamType($termType);
        if ($examMap->has($norm)) {
            return $examMap->get($norm);
        }

        foreach ($examMap as $exam) {
            if ($exam instanceof Exam && $this->canonicalExamType($exam->exam_type, $exam->exam_name) === $canonical) {
                return $exam;
            }
        }

        return null;
    }

    private function ptExamForTerm(array $term, int $termIndex, $examMap)
    {
        if (!empty($term['pt_exam_id'])) {
            return Exam::query()->find((int) $term['pt_exam_id']);
        }

        $ptType = $term['pt_exam_type'] ?? ($termIndex === 0 ? 'PT 1' : 'PT 2');
        $canonical = $this->canonicalExamType($ptType);

        if ($examMap->has($canonical)) {
            return $examMap->get($canonical);
        }

        $norm = $this->normalizeExamType($ptType);
        if ($examMap->has($norm)) {
            return $examMap->get($norm);
        }

        foreach ($examMap as $exam) {
            if ($exam instanceof Exam && $this->canonicalExamType($exam->exam_type, $exam->exam_name) === $canonical) {
                return $exam;
            }
        }

        return null;
    }

    private function canonicalExamType(?string $type, ?string $name = null): string
    {
        $raw = strtolower(trim((string) ($type ?: $name)));
        $clean = preg_replace('/[^a-z0-9]/', '', $raw);

        if (is_numeric($raw) && !empty($name)) {
            return $this->canonicalExamType(null, $name);
        }

        // 1. PT 2 / Periodic Test 2 / PT II
        if (
            preg_match('/\bpt[\s_-]*(2|ii)\b/i', $raw) ||
            preg_match('/periodic[\s_-]*test[\s_-]*(2|ii)\b/i', $raw) ||
            preg_match('/periodic[\s_-]*assessment[\s_-]*(2|ii)\b/i', $raw) ||
            in_array($clean, ['pt2', 'ptii', 'periodictest2', 'periodictestii', 'periodicassessment2', 'pa2'], true) ||
            str_starts_with($clean, 'pt2') ||
            str_starts_with($clean, 'ptii') ||
            str_starts_with($clean, 'periodictest2') ||
            str_starts_with($clean, 'periodictestii')
        ) {
            return 'pt2';
        }

        // 2. PT 1 / Periodic Test 1 / PT I
        if (
            preg_match('/\bpt[\s_-]*(1|i)\b/i', $raw) ||
            preg_match('/periodic[\s_-]*test[\s_-]*(1|i)\b/i', $raw) ||
            preg_match('/periodic[\s_-]*assessment[\s_-]*(1|i)\b/i', $raw) ||
            in_array($clean, ['pt1', 'pti', 'periodictest1', 'periodictesti', 'periodicassessment1', 'pa1'], true) ||
            str_starts_with($clean, 'pt1') ||
            str_starts_with($clean, 'pti') ||
            str_starts_with($clean, 'periodictest1') ||
            str_starts_with($clean, 'periodictesti')
        ) {
            return 'pt1';
        }

        // 3. Half Yearly / Mid Term (check before general yearly in Term 2)
        if (
            preg_match('/half[\s_-]*yearly/i', $raw) ||
            preg_match('/mid[\s_-]*term/i', $raw) ||
            in_array($clean, ['halfyearly', 'midterm'], true) ||
            str_starts_with($clean, 'halfyearly') ||
            str_starts_with($clean, 'midterm')
        ) {
            return 'term1';
        }

        // 4. Term 2 / Term II / Annual / Final / Yearly / Pre Board
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

        // 5. Term 1 / Term I
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
        if ($className) {
            $siblingIds = Classes::query()
                ->where(function ($q) use ($className) {
                    $q->where('class_name', $className)
                        ->orWhere('class_name', ltrim($className, '0'));
                })
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
            $ids = array_merge($ids, $siblingIds);

            if (Schema::connection('dynamic')->hasTable('class_name')) {
                $marksClassIds = Classname::query()
                    ->where(function ($q) use ($className) {
                        $q->where('class_name', $className)
                            ->orWhere('class_name', ltrim($className, '0'));
                    })
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
                $ids = array_merge($ids, $marksClassIds);
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
        $hasExamColumn = Schema::connection('dynamic')->hasColumn('academic_student_roll_no', 'exam_id');

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
            ->when($examId && $hasExamColumn, function ($q) use ($examId) {
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

        $student = DB::connection('dynamic')->table('student_registration')->where('is_archived', 0)->where('id', $studentId)->first();

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
        return \App\Academic\AcademicHelper::toRoman($className);
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
            'student_name' => $student->student_name ?? '',
            'mother_name' => $this->nameWithPrefix($json, ['mothername', 'mother_name', 'Mother Name', 'mother', 'student_mother_name'], ['mothername_prefix', 'mother_name_prefix']),
            'father_name' => $this->nameWithPrefix($json, ['fathername', 'father_name', 'Father Name', 'father', 'student_father_name'], ['fathername_prefix', 'father_name_prefix']),
            'scholar_no' => $student->scholar_no ?? '',
            'date_of_birth' => $dobFormatted,
            'phone' => $student->phone_number ?? $student->mobile_number ?? '',
        ];
    }

    private function formatDobDate(?string $dob): string
    {
        if (empty($dob)) {
            return '';
        }

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
        $pt = $this->convertedPtMark($ptMark, $ptExam, $termMark);
        $nb = $this->internalAssessmentValue($termMark, ['NB', 'Notebook', 'Portfolio', 'PF']);
        $mas = $this->internalAssessmentValue($termMark, ['MA/MAS', 'MAS', 'MA', 'Multiple Assessment']);
        $sea = $this->internalAssessmentValue($termMark, ['SE', 'SEA', 'Subject Enrichment']);
        $internalTotal = min(20, $pt + $nb + $mas + $sea);

        return [
            'theory' => $theory,
            'pt' => $pt,
            'nb' => $nb,
            'mas' => $mas,
            'sea' => $sea,
            'internal_total' => round($internalTotal, 2),
            'final_total' => round(min(100, $theory + $internalTotal), 2),
        ];
    }

    private function convertedPtMark($ptMark, $ptExam, $termMark = null): float
    {
        if (!$ptMark && !$termMark) {
            return 0.0;
        }

        $raw = null;
        if ($ptMark) {
            $raw = $this->markValue($ptMark, 'mark_theory');
            if ($raw === '' || $raw === null) {
                $raw = $this->markValue($ptMark, 'total_marks');
            }
        }

        $isInternal = false;
        $internalMax = null;
        if (($raw === '' || $raw === null) && $termMark) {
            $internal = $this->internalAssessmentMarks($termMark);
            foreach ($internal as $key => $value) {
                $norm = preg_replace('/[^a-z0-9]/', '', strtolower((string) $key));
                if (
                    in_array($norm, ['pt', 'periodictest', 'pt1', 'pt2', 'pt5', 'pt15', 'pt40', 'pt140', 'periodic', 'penpaper', 'penpapertest', 'ppt', 'pa', 'pa1', 'pa2'], true) ||
                    str_starts_with($norm, 'pt') ||
                    str_starts_with($norm, 'periodic')
                ) {
                    if ($value !== null && $value !== '') {
                        $raw = $value;
                        $isInternal = true;
                        if (str_contains((string)$key, '40') || str_contains($norm, '40')) {
                            $internalMax = 40.0;
                        } elseif (str_contains((string)$key, '5') || str_contains($norm, '5')) {
                            $internalMax = 5.0;
                        } elseif (str_contains((string)$key, '10') || str_contains($norm, '10')) {
                            $internalMax = 10.0;
                        } elseif (str_contains((string)$key, '20') || str_contains($norm, '20')) {
                            $internalMax = 20.0;
                        }
                        break;
                    }
                }
            }
        }

        if ($raw === '' || $raw === null || !is_numeric($raw)) {
            return 0.0;
        }

        $obtained = (float) $raw;
        if ($obtained <= 0) {
            return 0.0;
        }

        $max = 40.0;
        if ($ptExam) {
            $theoryMax = (float) ($ptExam->max_marks_theory ?? 0);
            $totalMax = (float) ($ptExam->max_marks ?? 0);
            if ($theoryMax > 0) {
                $max = $theoryMax;
            } elseif ($totalMax > 0) {
                $max = $totalMax;
            }
        } elseif ($isInternal) {
            $max = $internalMax ?: ($obtained <= 5.0 ? 5.0 : 40.0);
        }

        if ($max <= 5.0 || ($obtained <= 5.0 && $max <= 5.0)) {
            return (float) round(min(5.0, max(0.0, $obtained)), 1);
        }

        // Converted Marks = (Obtained PT Marks ÷ Max) × 5, rounded off to nearest whole number
        $converted = ($obtained / $max) * 5.0;

        return (float) round(min(5.0, max(0.0, $converted)), 0, PHP_ROUND_HALF_UP);
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
                return min(5.0, $this->numericMark($value));
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
        return round($value, 2, PHP_ROUND_HALF_UP);
    }

    private function roundPtMark(float $value): float
    {
        return round($value, 0, PHP_ROUND_HALF_UP);
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
        if (abs($value - round($value)) < 0.005) {
            return (string) (int) round($value);
        }

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

        $query = DB::connection('dynamic')->table('grademaster')
            ->where(function ($q) {
                if (Schema::connection('dynamic')->hasColumn('grademaster', 'is_delete')) {
                    $q->where('is_delete', 0)->orWhereNull('is_delete');
                }
            })
            ->whereRaw('CAST(min_per AS DECIMAL(10,2)) <= ?', [$marks])
            ->whereRaw('CAST(max_per AS DECIMAL(10,2)) >= ?', [$marks])
            ->orderByRaw('CAST(min_per AS DECIMAL(10,2)) DESC');

        if (!empty($subjectType)) {
            if (Schema::connection('dynamic')->hasColumn('grademaster', 'subject_type')) {
                $query->whereRaw('LOWER(TRIM(subject_type)) = ?', [strtolower(trim($subjectType))]);
            } elseif (Schema::connection('dynamic')->hasColumn('grademaster', 'applicable')) {
                $query->where(function ($q) use ($subjectType) {
                    $q->whereRaw('LOWER(TRIM(applicable)) = ?', [strtolower(trim($subjectType))])
                        ->orWhereNull('applicable')
                        ->orWhere('applicable', '');
                });
            }
        }

        $range = $query->get()->first(function ($item) use ($className) {
            $classes = collect(json_decode($item->groups ?? '[]', true) ?: []);
            return $classes->isEmpty() || !$className || $classes->contains($className);
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

        return null;
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

        return $exam;
    }

    private function hasMarksForClassAndExam(?int $classId, ?int $examId = null, ?string $className = null, ?string $sectionName = null): bool
    {
        if (!$classId && !$className) {
            return true;
        }

        if (!Schema::connection('dynamic')->hasTable('academic_students_marks') || !Schema::connection('dynamic')->hasTable('previosly_saved_marks_entry')) {
            return false;
        }

        $classIds = [];
        if ($classId) {
            $classIds = $this->marksClassIdsFor($classId);
            $cls = Classes::find($classId);
            if ($cls && !$className) {
                $className = $cls->class_name;
            }
        }
        if ($className) {
            $siblingIds = Classes::query()->where('class_name', $className)->orWhere('class_name', ltrim($className, '0'))->pluck('id')->all();
            $classIds = array_values(array_unique(array_merge($classIds, $siblingIds)));
        }

        $examIds = [];
        if ($examId) {
            $examIds[] = $examId;
            $resolvedExam = $this->resolveExamForClass($examId, $classId, $className);
            if ($resolvedExam) {
                $examIds[] = (int) $resolvedExam->id;
            }
            $exam = Exam::find($examId);
            if ($exam && !empty($exam->exam_group_id)) {
                $siblingExamIds = Exam::where('exam_group_id', $exam->exam_group_id)->pluck('id')->all();
                $examIds = array_merge($examIds, $siblingExamIds);
            }
        }
        $examIds = array_values(array_unique(array_filter($examIds)));

        $query = DB::connection('dynamic')
            ->table('previosly_saved_marks_entry as me')
            ->join('academic_students_marks as sm', 'sm.marks_id', '=', 'me.id')
            ->when(count($classIds) > 0, fn ($q) => $q->whereIn('me.class_id', array_map('strval', $classIds)))
            ->when(count($examIds) > 0, fn ($q) => $q->whereIn('me.exam_id', array_map('strval', $examIds)))
            ->when(!empty($sectionName), fn ($q) => $q->where('me.section_name', $sectionName));

        if (Schema::connection('dynamic')->hasColumn('previosly_saved_marks_entry', 'is_delete')) {
            $query->where(fn ($q) => $q->where('me.is_delete', 0)->orWhereNull('me.is_delete'));
        }
        if (Schema::connection('dynamic')->hasColumn('academic_students_marks', 'is_delete')) {
            $query->where(fn ($q) => $q->where('sm.is_delete', 0)->orWhereNull('sm.is_delete'));
        }

        return $query->exists();
    }

    private function normalizeExamIdentifier($rawExam, $examId = null): string
    {
        if (empty($rawExam)) {
            return 'exam_' . ($examId ?? 'unknown');
        }

        $norm = (string) $rawExam;
        $norm = preg_replace('/\s*\([^)]*\)/', '', $norm);
        $norm = preg_replace('/\b20\d\d[-_]20?\d\d\b/', '', $norm);
        $norm = preg_replace('/\b20\d\d\b/', '', $norm);
        $norm = strtolower(trim(preg_replace('/[\s\-_]+/', ' ', $norm)));
        $norm = preg_replace('/\bperiodic\s*test\b/', 'pt', $norm);
        $norm = preg_replace('/\bhalf\s*yearly\b/', 'hy', $norm);
        $cleaned = trim(preg_replace('/\b(exam|examination|test)\b/', '', $norm));
        if ($cleaned !== '') {
            $norm = trim(preg_replace('/\s+/', ' ', $cleaned));
        }

        return $norm !== '' ? $norm : ('exam_' . ($examId ?? 'unknown'));
    }

    private function getLatestMarksheetIds(?int $classId = null, array $filterExamIds = []): array
    {
        if (!empty($filterExamIds)) {
            return Marksheet::query()
                ->when($classId, fn ($q) => $q->where('class_id', $classId))
                ->whereIn('exam_id', $filterExamIds)
                ->groupBy('student_id')
                ->selectRaw('MAX(id) as id')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $rows = Marksheet::query()
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->select('id', 'student_id', 'exam_id', 'snapshot')
            ->orderByDesc('id')
            ->get();

        $seen = [];
        $latestIds = [];
        foreach ($rows as $row) {
            $snap = $row->snapshot ?: [];
            $rawExam = $snap['exam_name'] ?? ('exam_' . $row->exam_id);
            $normExam = $this->normalizeExamIdentifier($rawExam, $row->exam_id);
            $key = $row->student_id . '|' . $normExam;
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $latestIds[] = (int) $row->id;
            }
        }

        return $latestIds;
    }

    private function normalizeToThreePointGrade(?string $grade): string
    {
        $g = strtoupper(trim((string) $grade));
        if (in_array($g, ['A', 'B', 'C'], true)) {
            return $g;
        }
        if (in_array($g, ['A1', 'A2', 'A+'], true)) {
            return 'A';
        }
        if (in_array($g, ['B1', 'B2', 'B+'], true)) {
            return 'B';
        }
        if (in_array($g, ['C1', 'C2', 'D', 'E'], true)) {
            return 'C';
        }
        return $g !== '' ? $g : 'A';
    }

    private function calculateThreePointGrade(float $obtained, float $max = 100): string
    {
        if ($max <= 0) return 'A';
        $percent = ($obtained / $max) * 100;
        if ($percent >= 70.5) {
            return 'A';
        } elseif ($percent >= 50.5) {
            return 'B';
        } else {
            return 'C';
        }
    }

    private function formatMarksheetDate($rawDate, string $fallback = ''): string
    {
        if (empty($rawDate)) {
            return $fallback ?: now()->format('F d, Y');
        }
        try {
            return \Carbon\Carbon::parse($rawDate)->format('F d, Y');
        } catch (\Throwable $e) {
            return (string) $rawDate;
        }
    }

    private function parseDateToYmd($rawDate): string
    {
        if (empty($rawDate)) {
            return now()->format('Y-m-d');
        }
        try {
            return \Carbon\Carbon::parse($rawDate)->format('Y-m-d');
        } catch (\Throwable $e) {
            return now()->format('Y-m-d');
        }
    }

    /**
     * Resolve official student registration / admission date.
     * Checks registration_date column first, then json_str fallbacks.
     */
    private function resolveStudentRegistrationDate($student): ?string
    {
        $rawDate = $student->registration_date ?? null;
        if (empty($rawDate) || $rawDate === '0000-00-00') {
            $json = is_array($student->json_str ?? null)
                ? $student->json_str
                : json_decode($student->json_str ?? '', true);
            $rawDate = $json['admission_date'] ?? $json['registration_date'] ?? $json['date_of_admission'] ?? null;
        }

        if (empty($rawDate) || $rawDate === '0000-00-00') {
            return null;
        }

        try {
            return Carbon::parse($rawDate)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Filter students so only students admitted on or before the selected date/end-date are visible.
     * Rule: Selected Date >= Student Registration Date.
     */
    private function filterStudentsByRegistrationDate($students, ?string $asOfDate)
    {
        if (empty($asOfDate) || $students->isEmpty()) {
            return $students;
        }

        try {
            $asOfDateStr = Carbon::parse($asOfDate)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $students;
        }

        return $students->filter(function ($student) use ($asOfDateStr) {
            $regDate = $this->resolveStudentRegistrationDate($student);
            if (!empty($regDate)) {
                return $asOfDateStr >= $regDate;
            }
            return true;
        })->values();
    }
}
