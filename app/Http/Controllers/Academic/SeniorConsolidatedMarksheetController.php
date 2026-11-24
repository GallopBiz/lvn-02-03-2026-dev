<?php

namespace App\Http\Controllers\Academic;

use App\Academic\AcademicHelper;
use App\Http\Controllers\Controller;
use App\Models\Academic\Exam;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SeniorConsolidatedMarksheetController extends Controller
{
    private array $seniorClassNames = ['11', '12', 'Class 11', 'Class 12', 'XI', 'XII', '011', '012'];
    private array $seniorClassIds = [14, 15, 41, 42, 43, 44];

    public function index(Request $request)
    {
        $sessionYear = $this->resolveDynamicSessionYear($request);
        $classes = $this->seniorClassOptions();
        $sections = $this->sectionsForRequest($request);
        $sectionsByClass = $this->sectionsByClass();
        $rawExams = $this->seniorExams();
        $groupedExams = $this->groupedExams($rawExams);
        $exams = $rawExams;

        $streams = $this->availableStreams();
        $combinations = $this->availableCombinations();

        $reports = $request->hasAny(['class_name', 'section_name', 'class_id', 'exam_id', 'stream_id', 'combination_id'])
            ? $this->buildReports($request)
            : collect();

        $missingMarksClasses = $reports->filter(fn ($r) => empty($r['has_marks']))->map(function ($r) {
            return AcademicHelper::formatClassSection($r['class']->class_name, $r['class']->section_name);
        })->unique()->values()->all();

        return view('backend.AcademicsModules.senior_consolidated_marksheet', compact(
            'classes',
            'sections',
            'sectionsByClass',
            'exams',
            'groupedExams',
            'streams',
            'combinations',
            'reports',
            'missingMarksClasses'
        ));
    }

    public function print(Request $request)
    {
        $this->resolveDynamicSessionYear($request);
        $reports = $this->buildReports($request);
        $selectedExam = $request->integer('exam_id') ? Exam::query()->find($request->integer('exam_id')) : null;

        return view('backend.AcademicsModules.senior_consolidated_marksheet_print', compact('reports', 'selectedExam'));
    }

    public function buildReports(Request $request): Collection
    {
        $className = $request->filled('classname')
            ? (string) $request->input('classname')
            : ($request->filled('class_name') ? (string) $request->input('class_name') : null);
        $sectionName = $request->filled('section_name') ? (string) $request->input('section_name') : null;
        $classId = $request->integer('class_id') ?: null;
        $selectedExam = $request->integer('exam_id') ? Exam::query()->find($request->integer('exam_id')) : null;

        $classes = $this->seniorClasses()
            ->when($className, fn ($items) => $items->where('class_name', $className))
            ->when($sectionName, fn ($items) => $items->where('section_name', $sectionName))
            ->when($classId && !$className, fn ($items) => $items->where('id', $classId))
            ->values();

        return $classes
            ->map(fn ($class) => $this->buildClassReport($class, $selectedExam, $request))
            ->filter(fn ($report) => count($report['students']) > 0)
            ->values();
    }

    private function buildClassReport($class, ?Exam $selectedExam, Request $request): array
    {
        $sessionYear = $this->resolveDynamicSessionYear($request);
        $students = $this->studentsForClass((int) $class->id, $class->class_name, $class->section_name);

        // Map exams for senior classes
        $examMap = $this->seniorExamMapForClass((int) $class->id, $selectedExam);
        $activeExam = $selectedExam ?: ($examMap['term_2'] ?? ($examMap['term_1'] ?? null));
        $activeExamId = $activeExam?->id ? (int) $activeExam->id : null;

        $isTerm1 = $this->isTerm1Report($activeExam, $examMap);
        $showTerm2 = !$isTerm1;

        // Fetch student stream and combination mapping
        $combAssignments = $this->studentCombinationAssignments();

        // Fetch marks for all students in one query
        $examIds = collect($examMap)->filter()->pluck('id')->map(fn ($id) => (int) $id)->unique()->values()->all();
        $studentIds = $students->pluck('id')->map(fn ($id) => (int) $id)->all();
        $marks = $this->marksForStudents($studentIds, (int) $class->id, $examIds);

        // Fetch subjects assigned to students / combinations
        $subjectsByStudent = $this->assignedSubjectsForStudents($students, $class, $marks, $combAssignments);
        $reportAccess = app(\App\Services\StaffReportAccessService::class);
        $subjectsByStudent = $subjectsByStudent->map(function ($subjects) use ($class, $reportAccess) {
            return $reportAccess->filterSubjectData($subjects, $class->id, $class->section_name);
        });

        // Filter stream/combination if requested
        $filterStreamId = $request->integer('stream_id') ?: null;
        $filterCombId = $request->integer('combination_id') ?: null;

        $hasAnyMarksInClass = false;

        $studentRows = $students->map(function ($student) use (
            $marks,
            $subjectsByStudent,
            $combAssignments,
            $examMap,
            $class,
            $showTerm2,
            $sessionYear,
            $activeExamId,
            &$hasAnyMarksInClass
        ) {
            $studentId = (int) $student->id;
            $combData = $combAssignments[$studentId] ?? [
                'combination_id' => 9999,
                'combination_name' => 'General Combination',
                'stream_id' => 9999,
                'stream_name' => 'General Stream',
            ];

            $subjects = $subjectsByStudent->get($studentId, collect());
            $subjectRows = [];

            $term1TheorySum = 0.0;
            $term1PracticalSum = 0.0;
            $term1GrandSum = 0.0;

            $term2TheorySum = 0.0;
            $term2PracticalSum = 0.0;
            $term2GrandSum = 0.0;

            $term2ObtainedSum = 0.0;
            $term2MaxSum = 0.0;
            $term1ObtainedSum = 0.0;
            $term1MaxSum = 0.0;

            $hasTerm1Marks = false;
            $hasTerm2Marks = false;
            $hasFailingSubject = false;

            // Identify optional subject name
            $optionalSubjectName = '-';
            foreach ($subjects as $sub) {
                if (strtolower(trim((string) ($sub->subject_type ?? ''))) === 'optional') {
                    $optionalSubjectName = $sub->subject_name;
                    break;
                }
            }
            if ($optionalSubjectName === '-' && !empty($combData['combination_name'])) {
                // Try to infer from combination name e.g. "PCM 12+PE", "PCM 12+CS", etc.
                if (preg_match('/\+([A-Z0-9\s]+)$/i', $combData['combination_name'], $m)) {
                    $short = strtoupper(trim($m[1]));
                    $known = [
                        'PE' => 'Physical Education',
                        'CS' => 'Computer Science',
                        'IP' => 'Informatics Practices',
                        'DANCE' => 'Dance',
                        'MUSIC' => 'Music',
                        'PSYCHOLOGY' => 'Psychology',
                        'APPLIED MATH' => 'Applied Mathematics',
                        'ESHIP' => 'Entrepreneurship',
                        'HINDI' => 'Hindi (11-12)',
                    ];
                    if (isset($known[$short])) {
                        $optionalSubjectName = $known[$short];
                    }
                }
            }

            foreach ($subjects as $subject) {
                $subjectId = (int) $subject->id;
                $subjectName = $subject->subject_name;

                // Max marks configuration for this subject
                $hyMaxConfig = $this->getSeniorSubjectMaxMarks(
                    $subjectName,
                    $examMap['term_1']?->max_marks_theory,
                    $examMap['term_1']?->max_marks_practical
                );
                $annualMaxConfig = $this->getSeniorSubjectMaxMarks(
                    $subjectName,
                    $examMap['term_2']?->max_marks_theory,
                    $examMap['term_2']?->max_marks_practical
                );

                // Term 1 Marks
                $pt1Mark = $examMap['pt_1'] ? $this->findMark($marks, $studentId, $subjectId, (int) $examMap['pt_1']->id) : null;
                $hyMark = $examMap['term_1'] ? $this->findMark($marks, $studentId, $subjectId, (int) $examMap['term_1']->id) : null;

                $pt1Val = $this->convertSeniorPtMark($this->extractObtainedMark($pt1Mark));
                $hyTheoryVal = $this->extractTheoryMark($hyMark);
                $hyPracticalVal = $this->extractPracticalMark($hyMark);

                $hyTotal = '';
                if ($hyTheoryVal !== '' || $hyPracticalVal !== '') {
                    $th = is_numeric($hyTheoryVal) ? (float) $hyTheoryVal : 0.0;
                    $pr = is_numeric($hyPracticalVal) ? (float) $hyPracticalVal : 0.0;
                    $hyTotal = (float) ($th + $pr);
                    $hasTerm1Marks = true;
                    $hasAnyMarksInClass = true;

                    $term1TheorySum += $th;
                    $term1PracticalSum += $pr;
                    $term1GrandSum += $hyTotal;

                    $term1ObtainedSum += $hyTotal;
                    $term1MaxSum += 100.0;
                }

                $t1Grade = is_numeric($hyTotal) ? $this->gradeFor((float) $hyTotal) : '';

                // Term 2 Marks
                $pt2Mark = $examMap['pt_2'] ? $this->findMark($marks, $studentId, $subjectId, (int) $examMap['pt_2']->id) : null;
                $annualMark = $examMap['term_2'] ? $this->findMark($marks, $studentId, $subjectId, (int) $examMap['term_2']->id) : null;

                $pt2Val = $this->convertSeniorPtMark($this->extractObtainedMark($pt2Mark));
                $annualTheoryVal = $this->extractTheoryMark($annualMark);
                $annualPracticalVal = $this->extractPracticalMark($annualMark);

                $annualTotal = '';
                if ($annualTheoryVal !== '' || $annualPracticalVal !== '') {
                    $ath = is_numeric($annualTheoryVal) ? (float) $annualTheoryVal : 0.0;
                    $apr = is_numeric($annualPracticalVal) ? (float) $annualPracticalVal : 0.0;
                    $annualTotal = (float) ($ath + $apr);
                    $hasTerm2Marks = true;
                    $hasAnyMarksInClass = true;

                    $term2TheorySum += $ath;
                    $term2PracticalSum += $apr;
                    $term2GrandSum += $annualTotal;

                    $term2ObtainedSum += $annualTotal;
                    $term2MaxSum += 100.0;
                }

                $t2Grade = is_numeric($annualTotal) ? $this->gradeFor((float) $annualTotal) : '';

                // Pass / Fail Check (33% passing rule)
                $t1Failed = false;
                if ($hyTotal !== '' && is_numeric($hyTotal)) {
                    $thMin = ($hyMaxConfig['theory'] * 0.33);
                    $prMin = ($hyMaxConfig['practical'] * 0.33);
                    $thObt = is_numeric($hyTheoryVal) ? (float) $hyTheoryVal : 0.0;
                    $prObt = is_numeric($hyPracticalVal) ? (float) $hyPracticalVal : 0.0;
                    if ($thObt < $thMin || $prObt < $prMin || (float) $hyTotal < 33.0) {
                        $t1Failed = true;
                    }
                }

                $t2Failed = false;
                if ($annualTotal !== '' && is_numeric($annualTotal)) {
                    $athMin = ($annualMaxConfig['theory'] * 0.33);
                    $aprMin = ($annualMaxConfig['practical'] * 0.33);
                    $athObt = is_numeric($annualTheoryVal) ? (float) $annualTheoryVal : 0.0;
                    $aprObt = is_numeric($annualPracticalVal) ? (float) $annualPracticalVal : 0.0;
                    if ($athObt < $athMin || $aprObt < $aprMin || (float) $annualTotal < 33.0) {
                        $t2Failed = true;
                    }
                }

                $subjectFailed = ($showTerm2 && $hasTerm2Marks) ? ($t1Failed || $t2Failed) : $t1Failed;
                if ($subjectFailed) {
                    $hasFailingSubject = true;
                }

                $subjectRows[] = [
                    'subject' => $subjectName,
                    'term_1' => [
                        'pt' => $pt1Val,
                        'theory' => $hyTheoryVal,
                        'practical' => $hyPracticalVal,
                        'total' => $hyTotal,
                        'grade' => $t1Grade,
                    ],
                    'term_2' => [
                        'pt' => $pt2Val,
                        'theory' => $annualTheoryVal,
                        'practical' => $annualPracticalVal,
                        'total' => $annualTotal,
                        'grade' => $t2Grade,
                    ],
                ];
            }

            // Percentage calculation: Term II percentage calculated exclusively from Term II marks and corresponding max marks
            $percentage = null;
            if ($showTerm2 && $hasTerm2Marks && $term2MaxSum > 0) {
                $percentage = round(($term2ObtainedSum / $term2MaxSum) * 100, 2);
            } elseif (!$showTerm2 && $hasTerm1Marks && $term1MaxSum > 0) {
                $percentage = round(($term1ObtainedSum / $term1MaxSum) * 100, 2);
            }

            $division = $percentage !== null ? $this->divisionFor($percentage) : '';
            $result = '';
            $resultShort = '';
            if ($hasTerm2Marks || $hasTerm1Marks) {
                if ($hasFailingSubject) {
                    $result = 'Fail';
                    $resultShort = 'F';
                } else {
                    $result = 'Pass';
                    $resultShort = 'P';
                }
            }

            return [
                'student' => $student,
                'scholar_no' => $student->scholar_no ?? '',
                'attendance' => $this->collectiveAttendanceForMarksheet($studentId, (int) $class->id, $activeExamId, $sessionYear),
                'stream_id' => $combData['stream_id'],
                'stream_name' => $combData['stream_name'],
                'combination_id' => $combData['combination_id'],
                'combination_name' => $combData['combination_name'],
                'optional_subject' => $optionalSubjectName,
                'subjects' => $subjectRows,
                'term_1_theory_total' => $term1TheorySum,
                'term_1_practical_total' => $term1PracticalSum,
                'term_1_total' => $term1GrandSum,
                'term_2_theory_total' => $term2TheorySum,
                'term_2_practical_total' => $term2PracticalSum,
                'term_2_total' => $term2GrandSum,
                'percentage' => $percentage,
                'division' => $division,
                'result' => $result,
                'result_short' => $resultShort,
            ];
        });

        // Filter by stream or combination if requested
        if ($filterStreamId) {
            $studentRows = $studentRows->filter(fn ($sr) => (int) $sr['stream_id'] === $filterStreamId)->values();
        }
        if ($filterCombId) {
            $studentRows = $studentRows->filter(fn ($sr) => (int) $sr['combination_id'] === $filterCombId)->values();
        }

        // Group by Stream -> Subject Combination, and sort students alphabetically within combination
        $groupedData = [];
        foreach ($studentRows as $row) {
            $sName = strtoupper(trim($row['stream_name'] ?: 'GENERAL STREAM'));
            $cName = trim($row['combination_name'] ?: 'General Combination');
            $cId = (int) $row['combination_id'];
            $sId = (int) $row['stream_id'];

            $groupKey = $sName . '|||' . $cName;
            if (!isset($groupedData[$groupKey])) {
                $groupedData[$groupKey] = [
                    'stream_id' => $sId,
                    'stream_name' => $sName,
                    'combination_id' => $cId,
                    'combination_name' => $cName,
                    'students' => [],
                ];
            }
            $groupedData[$groupKey]['students'][] = $row;
        }

        // Sort groups by Stream Name ASC, then Combination Name ASC
        uasort($groupedData, function ($a, $b) {
            $sCmp = strcasecmp($a['stream_name'], $b['stream_name']);
            if ($sCmp !== 0) {
                return $sCmp;
            }
            return strcasecmp($a['combination_name'], $b['combination_name']);
        });

        // Sort students within each group alphabetically
        foreach ($groupedData as &$grp) {
            usort($grp['students'], function ($a, $b) {
                return strnatcasecmp($a['student']->student_name ?? '', $b['student']->student_name ?? '');
            });
        }
        unset($grp);

        $groups = array_values($groupedData);

        $normClass = \App\Academic\AcademicHelper::normalizeClassIdentifier($class?->class_name);
        $isClass12 = ($normClass === '12');
        $defaultSeniorExamTitle = !empty($showTerm2)
            ? ($isClass12 ? 'Pre Board Examination' : 'Annual Examination')
            : 'Half Yearly Examination';

        return [
            'class' => $class,
            'exam' => $activeExam,
            'exam_title' => $activeExam ? ($activeExam->exam_title ?: $activeExam->exam_name) : $defaultSeniorExamTitle,
            'session_year' => $sessionYear,
            'class_teacher' => $this->classTeacherName($class),
            'students' => $studentRows,
            'groups' => $groups,
            'has_marks' => $hasAnyMarksInClass,
            'is_term_1' => $isTerm1,
            'show_term_2' => $showTerm2,
        ];
    }

    private function seniorClasses()
    {
        return app(\App\Services\StaffReportAccessService::class)->filterClassOptions(
            Classes::query()
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
            ->orderBy('section_name')
            ->get()
        );
    }

    private function seniorClassOptions()
    {
        return $this->seniorClasses()
            ->pluck('class_name')
            ->filter()
            ->unique()
            ->values();
    }

    private function sectionsForRequest(Request $request)
    {
        $className = $request->filled('class_name') ? (string) $request->input('class_name') : null;

        $sections = $this->seniorClasses()
            ->when($className, fn ($items) => $items->where('class_name', $className))
            ->pluck('section_name')
            ->filter()
            ->unique()
            ->values();

        if ($className) {
            $allowed = app(\App\Services\StaffReportAccessService::class)->allowedSections($className);
            if (is_array($allowed)) {
                return $sections->filter(fn ($section) => in_array($section, $allowed, true))->values();
            }
        }

        return $sections;
    }

    private function sectionsByClass()
    {
        $base = $this->seniorClasses()
            ->groupBy('class_name')
            ->map(fn ($items) => $items
                ->pluck('section_name')
                ->filter()
                ->unique()
                ->values())
            ->toArray();

        $augmented = [];
        foreach ($base as $cName => $secs) {
            $augmented[$cName] = $secs;
            $stripped = ltrim((string) $cName, '0');
            $augmented[$stripped] = $secs;
            $roman = AcademicHelper::toRoman((string) $cName);
            if ($roman !== '') {
                $augmented[$roman] = $secs;
            }
        }

        return $augmented;
    }

    private function seniorExams()
    {
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

        return $rawExams;
    }

    private function groupedExams($exams)
    {
        return $exams->groupBy(function ($exam) {
            return $exam->exam_group_id ?: ($exam->exam_name . '|' . $exam->exam_type);
        })->map(function ($group) {
            $first = $group->first();
            $displayName = trim((string) $first->exam_name);
            $examType = trim((string) $first->exam_type);
            if ($examType !== '' && strcasecmp($displayName, $examType) !== 0) {
                $displayName .= ' - ' . $examType;
            }

            return [
                'id' => (int) $first->id,
                'exam_name' => (string) $first->exam_name,
                'display_name' => $displayName,
                'all_exam_ids' => $group->pluck('id')->map(fn ($id) => (int) $id)->all(),
            ];
        })->values();
    }

    private function availableStreams()
    {
        if (!Schema::connection('dynamic')->hasTable('streams')) {
            return collect();
        }

        return DB::connection('dynamic')
            ->table('streams')
            ->orderBy('streams')
            ->select('id', 'streams as stream_name')
            ->get();
    }

    private function availableCombinations()
    {
        if (!Schema::connection('dynamic')->hasTable('subject_combinations')) {
            return collect();
        }

        return DB::connection('dynamic')
            ->table('subject_combinations')
            ->orderBy('combination_name')
            ->select('id', 'combination_name', 'streams as stream_id')
            ->get();
    }

    private function studentsForClass(int $classId, ?string $className = null, ?string $sectionName = null)
    {
        if (!Schema::connection('dynamic')->hasTable('student_registration')) {
            return collect();
        }

        $class = Classes::query()->find($classId);
        if (!$className && $class) {
            $className = $class->class_name;
        }
        if (!$sectionName && $class) {
            $sectionName = $class->section_name;
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
                if (Schema::connection('dynamic')->hasColumn('student_registration', 'json_str')) {
                    $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(json_str, '$.section_name')) = ?", [$sectionName]);
                }
            });
        }

        $students = $query->orderBy('student_name')->get();

        // Enforce student visibility rule: Selected Date >= Student Registration Date
        $asOfDate = request()->input('date', request()->input('print_date', request()->input('exam_date')));
        if (empty($asOfDate)) {
            $asOfDate = now()->toDateString();
        }
        return $this->filterStudentsByRegistrationDate($students, $asOfDate);
    }

    private function studentCombinationAssignments(): array
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
                'combination_id' => $a->combination_id ?: 9999,
                'combination_name' => $a->combination_name ?: 'General Combination',
                'stream_id' => $a->stream_id ?: 9999,
                'stream_name' => $a->stream_name ?: 'General Stream',
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

    private function assignedSubjectsForStudents($students, $class, $marks, array $combAssignments)
    {
        $subjectColumns = ['id', 'subject_name', 'subject_type', 'evaluation', 'practical', 'do_not_print_in_main_scholastic_area', 'special_formatting_classes'];
        $actualColumns = [];
        foreach ($subjectColumns as $col) {
            if (Schema::connection('dynamic')->hasColumn('subjectmaster', $col)) {
                $actualColumns[] = 's.' . $col;
            }
        }
        if (empty($actualColumns)) {
            $actualColumns = ['s.id', 's.subject_name', 's.subject_type'];
        }

        // Fetch combinations with their subjects
        $combSubjects = collect();
        if (Schema::connection('dynamic')->hasTable('combination_subject') && Schema::connection('dynamic')->hasTable('subjectmaster')) {
            $combSubjects = DB::connection('dynamic')
                ->table('combination_subject as cs')
                ->join('subjectmaster as s', 's.id', '=', 'cs.subject_id')
                ->where(function ($q) {
                    if (Schema::connection('dynamic')->hasColumn('subjectmaster', 'is_delete')) {
                        $q->where('s.is_delete', 0)->orWhereNull('s.is_delete');
                    }
                })
                ->whereNotIn(DB::raw('LOWER(TRIM(s.subject_name))'), ['test', 'te4st', 'dummy'])
                ->select(array_merge($actualColumns, ['cs.subject_combination_id', 'cs.subject_order']))
                ->orderBy('cs.subject_order')
                ->orderBy('s.subject_name')
                ->get()
                ->groupBy('subject_combination_id');
        }

        // Fetch class-level assigned subjects as fallback
        $classAssignedSubjects = collect();
        if (Schema::connection('dynamic')->hasTable('academic_class_subject') && Schema::connection('dynamic')->hasTable('subjectmaster')) {
            $classAssignedSubjects = DB::connection('dynamic')
                ->table('academic_class_subject as acs')
                ->join('subjectmaster as s', 's.id', '=', 'acs.subject_id')
                ->whereIn('acs.class_id', $this->marksClassIdsFor((int) $class->id))
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

        return $students->mapWithKeys(function ($student) use ($combAssignments, $combSubjects, $classAssignedSubjects, $marks, $class) {
            $studentId = (int) $student->id;
            $combId = $combAssignments[$studentId]['combination_id'] ?? null;

            $assigned = collect();
            if ($combId && isset($combSubjects[$combId])) {
                $assigned = $combSubjects[$combId];
            }

            // Exclude Non Academic co-scholastic subjects, test subjects, and practical duplicate subjects (... P)
            $filterCleanScholastic = function ($collection) use ($class) {
                return $collection->filter(function ($s) use ($class) {
                    $name = trim((string) ($s->subject_name ?? ''));
                    if (preg_match('/\b(test|te4st|dummy)\b/i', $name)) {
                        return false;
                    }
                    if (preg_match('/\s+[pP]$/i', $name)) {
                        return false;
                    }
                    $sType = strtolower(trim((string) ($s->subject_type ?? '')));
                    return !preg_match('/non[\s-]*academic/i', $sType) && !AcademicHelper::isSpecialFormattingForClass($s, $class->id, $class->class_name);
                });
            };

            $assigned = $filterCleanScholastic($assigned);

            // ONLY if student does NOT have assigned combination subjects, check marks or class subjects as fallback
            if ($assigned->isEmpty()) {
                // Append any subjects with marks for this student
                $studentMarks = $marks->filter(fn ($m) => (int) $m->student_id === $studentId);
                if ($studentMarks->isNotEmpty()) {
                    $markedSubIds = $studentMarks->pluck('subject_id')->map(fn ($id) => (int) $id)->unique()->all();
                    $existingIds = $assigned->pluck('id')->map(fn ($id) => (int) $id)->all();
                    $missingIds = array_diff($markedSubIds, $existingIds);
                    if (!empty($missingIds)) {
                        $extra = DB::connection('dynamic')
                            ->table('subjectmaster as s')
                            ->whereIn('s.id', $missingIds)
                            ->select(['id', 'subject_name', 'subject_type'])
                            ->get();
                        $assigned = $assigned->concat($filterCleanScholastic($extra));
                    }
                }

                if ($assigned->isEmpty() && $classAssignedSubjects->isNotEmpty()) {
                    $cleanClassSubs = $filterCleanScholastic($classAssignedSubjects)->unique('id')->values();
                    // If class has marked subjects, only keep those marked in class
                    $classMarkedSubIds = $marks->pluck('subject_id')->map(fn ($id) => (int) $id)->unique()->all();
                    if (!empty($classMarkedSubIds)) {
                        $activeClassSubs = $cleanClassSubs->filter(fn ($s) => in_array((int) $s->id, $classMarkedSubIds, true));
                        $assigned = $activeClassSubs->isNotEmpty() ? $activeClassSubs : $cleanClassSubs->take(5);
                    } else {
                        $assigned = $cleanClassSubs->take(5);
                    }
                }
            }

            $assigned = $assigned->unique('id')->values();

            return [$studentId => $assigned];
        });
    }

    private function marksForStudents(array $studentIds, int $classId, array $examIds)
    {
        if (empty($studentIds)) {
            return collect();
        }

        $hasStudentMarks = Schema::connection('dynamic')->hasTable('academic_students_marks');
        $hasMarksTable = Schema::connection('dynamic')->hasTable('marks');
        $hasSavedEntry = Schema::connection('dynamic')->hasTable('previosly_saved_marks_entry');

        if (!$hasSavedEntry || (!$hasStudentMarks && !$hasMarksTable)) {
            return collect();
        }

        $marksTable = $hasStudentMarks ? 'academic_students_marks' : 'marks';
        $classIds = $this->marksClassIdsFor($classId);

        $query = DB::connection('dynamic')
            ->table($marksTable . ' as sm')
            ->join('previosly_saved_marks_entry as me', 'me.id', '=', 'sm.marks_id')
            ->whereIn('sm.student_id', array_map('strval', $studentIds));

        if (!empty($classIds)) {
            $query->whereIn('me.class_id', array_map('strval', $classIds));
        }
        if (!empty($examIds)) {
            $query->whereIn('me.exam_id', array_map('strval', $examIds));
        }

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

    private function findMark($marks, int $studentId, int $subjectId, int $examId)
    {
        return $marks->first(function ($m) use ($studentId, $subjectId, $examId) {
            return (int) ($m->student_id ?? 0) === $studentId
                && (int) ($m->subject_id ?? 0) === $subjectId
                && (int) ($m->exam_id ?? 0) === $examId;
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

        // 80 Theory + 20 Practical / Internal (Standard CBSE Default)
        return ['theory' => 80, 'practical' => 20];
    }

    private function seniorExamMapForClass(int $classId, ?Exam $selectedExam): array
    {
        $classIds = $this->marksClassIdsFor($classId);
        $exams = Exam::query()
            ->when(!empty($classIds), fn ($q) => $q->whereIn('class_id', $classIds))
            ->get();

        if ($exams->isEmpty()) {
            $exams = Exam::all();
        }

        if ($selectedExam && !in_array((int) $selectedExam->id, $exams->pluck('id')->map(fn ($id) => (int) $id)->all(), true)) {
            $exams->push($selectedExam);
        }

        return [
            'pt_1' => $this->examByAliases($exams, ['pt1', 'pti', 'periodictest1', 'periodictesti', 'pa1']),
            'term_1' => $this->examByAliases($exams, ['term1', 'termi', 'halfyearly', 'halfyearlyexam', 'midterm']),
            'pt_2' => $this->examByAliases($exams, ['pt2', 'ptii', 'periodictest2', 'periodictestii', 'pa2']),
            'term_2' => $this->examByAliases($exams, ['term2', 'termii', 'annual', 'annualexam', 'annualexamination', 'final']),
        ];
    }

    private function examByAliases($exams, array $aliases)
    {
        return $exams->first(function ($exam) use ($aliases) {
            $keys = [
                $this->normalizeExamType($exam->exam_type ?? ''),
                $this->normalizeExamType($exam->exam_name ?? ''),
            ];

            foreach ($keys as $key) {
                foreach ($aliases as $alias) {
                    if ($key === $alias || ($key !== '' && str_contains($key, $alias))) {
                        return true;
                    }
                }
            }

            return false;
        });
    }

    private function normalizeExamType(string $val): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/', '', $val));
    }

    private function isTerm1Report(?Exam $activeExam, array $examMap): bool
    {
        if (!$activeExam) {
            return false;
        }

        $term1Id = $examMap['term_1']?->id ? (int) $examMap['term_1']->id : null;
        $pt1Id = $examMap['pt_1']?->id ? (int) $examMap['pt_1']->id : null;
        $activeId = (int) $activeExam->id;

        if ($activeId === $term1Id || $activeId === $pt1Id) {
            return true;
        }

        $normalized = $this->normalizeExamType($activeExam->exam_type . ' ' . $activeExam->exam_name);
        return str_contains($normalized, 'term1')
            || str_contains($normalized, 'halfyearly')
            || str_contains($normalized, 'midterm')
            || str_contains($normalized, 'pt1')
            || str_contains($normalized, 'pa1');
    }

    private function marksClassIdsFor(int $classId): array
    {
        $class = Classes::query()->find($classId);
        if (!$class || !$class->class_name) {
            return [$classId];
        }

        $className = (string) $class->class_name;
        $numericClass = AcademicHelper::fromRoman($className) ?: $className;

        return Classes::query()
            ->where(function ($q) use ($className, $numericClass) {
                $q->where('class_name', $className)
                    ->orWhere('class_name', ltrim($className, '0'))
                    ->orWhere('class_name', $numericClass)
                    ->orWhere('class_name', ltrim($numericClass, '0'));
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function gradeFor(float $percentage): string
    {
        if ($percentage >= 91) return 'A1';
        if ($percentage >= 81) return 'A2';
        if ($percentage >= 71) return 'B1';
        if ($percentage >= 61) return 'B2';
        if ($percentage >= 51) return 'C1';
        if ($percentage >= 41) return 'C2';
        if ($percentage >= 33) return 'D';
        return 'E';
    }

    private function divisionFor(float $percentage): string
    {
        if ($percentage >= 60.0) return 'First';
        if ($percentage >= 45.0) return 'Second';
        if ($percentage >= 33.0) return 'Third';
        return '';
    }

    private function classTeacherName($class): string
    {
        if (Schema::connection('dynamic')->hasTable('teacher_subjects')) {
            $query = DB::connection('dynamic')
                ->table('teacher_subjects as ts')
                ->whereIn('ts.class_id', $this->marksClassIdsFor((int) $class->id))
                ->where('ts.section_name', $class->section_name)
                ->where('ts.role', 'Class Teacher')
                ->when(Schema::connection('dynamic')->hasColumn('teacher_subjects', 'is_delete'), function ($q) {
                    $q->where('ts.is_delete', 0)->orWhereNull('ts.is_delete');
                })
                ->limit(1);

            if (Schema::connection('dynamic')->hasColumn('teacher_subjects', 'teacher_name')) {
                $teacher = (clone $query)->value('ts.teacher_name');
            } elseif (Schema::connection('dynamic')->hasTable('hrms_employees')) {
                $teacher = (clone $query)
                    ->leftJoin('hrms_employees as he', 'he.id', '=', 'ts.teacher_id')
                    ->selectRaw("TRIM(CONCAT(COALESCE(he.first_name, ''), ' ', COALESCE(he.last_name, ''))) as teacher_name")
                    ->value('teacher_name');
            } else {
                $teacher = '';
            }

            if ($teacher) {
                return $teacher;
            }
        }

        if (Schema::connection('dynamic')->hasTable('classasigntoteacher')) {
            $assigned = DB::connection('dynamic')
                ->table('classasigntoteacher')
                ->where('Class', $class->class_name)
                ->where('Section', $class->section_name)
                ->when(Schema::connection('dynamic')->hasColumn('classasigntoteacher', 'is_delete'), function ($q) {
                    $q->where('is_delete', 0)->orWhereNull('is_delete');
                })
                ->first();

            if ($assigned) {
                return trim($assigned->teacher_name ?? $assigned->teacher_namee ?? $assigned->Teacher_1 ?? '');
            }
        }

        return '';
    }

    private function resolveDynamicSessionYear(Request $request): string
    {
        $session = session('academic_session') ?: session('session_name');
        if (!empty($session)) {
            return (string) $session;
        }

        $dbName = \Illuminate\Support\Facades\Config::get('database.connections.dynamic.database');
        if (is_string($dbName) && preg_match('/^\d{4}_\d{4}$/', $dbName)) {
            return $dbName;
        }

        return '2025_2026';
    }

    private function collectiveAttendanceForMarksheet(?int $studentId, ?int $classId, ?int $examId, ?string $sessionYear, string $fallback = ''): string
    {
        if (!$studentId) {
            return $fallback;
        }

        $classIds = $classId ? $this->marksClassIdsFor($classId) : [];

        if (Schema::connection('dynamic')->hasTable('academic_attendance_collectives') && Schema::connection('dynamic')->hasTable('academic_attendance_collective_details')) {
            $record = DB::connection('dynamic')
                ->table('academic_attendance_collective_details as acd')
                ->join('academic_attendance_collectives as ac', 'acd.collective_id', '=', 'ac.id')
                ->where('ac.is_delete', 0)
                ->where('acd.student_id', $studentId)
                ->when(!empty($classIds), fn ($q) => $q->whereIn('ac.class_id', $classIds))
                ->when($examId, fn ($q) => $q->where('ac.exam_id', $examId))
                ->orderByDesc('ac.id')
                ->select('acd.present_days', 'acd.working_days')
                ->first();

            if (!$record) {
                $record = DB::connection('dynamic')
                    ->table('academic_attendance_collective_details as acd')
                    ->join('academic_attendance_collectives as ac', 'acd.collective_id', '=', 'ac.id')
                    ->where('ac.is_delete', 0)
                    ->where('acd.student_id', $studentId)
                    ->when(!empty($classIds), fn ($q) => $q->whereIn('ac.class_id', $classIds))
                    ->orderByDesc('ac.id')
                    ->select('acd.present_days', 'acd.working_days')
                    ->first();
            }

            if ($record && (float) $record->working_days > 0) {
                $pres = ((int) $record->present_days == $record->present_days) ? (int) $record->present_days : (float) $record->present_days;
                $work = (int) $record->working_days;
                return "{$pres} / {$work}";
            }
        }

        return $fallback;
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
