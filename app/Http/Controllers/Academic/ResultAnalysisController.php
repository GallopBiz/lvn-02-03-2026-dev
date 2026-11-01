<?php

namespace App\Http\Controllers\Academic;

use App\Academic\AcademicHelper;
use App\Http\Controllers\Controller;
use App\Models\Academic\Exam;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResultAnalysisController extends Controller
{
    private array $seniorClassNames = ['11', '12', 'Class 11', 'Class 12', 'XI', 'XII', '011', '012'];
    private array $seniorClassIds = [14, 15, 41, 42, 43, 44];

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
        $sessionYear = $this->resolveDynamicSessionYear($request);
        $classes = $this->classOptions();
        $sections = $this->sectionsForRequest($request);
        $sectionsByClass = $this->sectionsByClass();
        $rawExams = Exam::with('classInfo')->latest('id')->get();
        $groupedExams = $this->groupedExams($rawExams);
        $exams = $rawExams;

        $reports = $request->hasAny(['class_name', 'section_name', 'class_id', 'exam_id'])
            ? $this->buildReports($request)
            : collect();

        return view('backend.AcademicsModules.result_analysis', compact(
            'classes',
            'sections',
            'sectionsByClass',
            'exams',
            'groupedExams',
            'reports',
            'sessionYear'
        ));
    }

    public function topperList(Request $request)
    {
        return view('backend.AcademicsModules.topper_list', $this->buildTopperReportData($request));
    }

    public function topperListPrint(Request $request)
    {
        return view('backend.AcademicsModules.topper_list_print', $this->buildTopperReportData($request));
    }

    private function buildTopperReportData(Request $request): array
    {
        $sessionYear = $this->resolveDynamicSessionYear($request);
        $classes = $this->classOptions();
        $sections = $this->sectionsForRequest($request);
        $rawExams = Exam::with('classInfo')->latest('id')->get();
        $groupedExams = $this->groupedExams($rawExams);
        $selectedSession = (string) ($request->query('session') ?: str_replace('-', '_', $sessionYear));
        $selectedClass = (string) $request->query('class_name', '');
        $selectedSection = (string) $request->query('section_name', '');
        $rankingType = (string) $request->query('ranking_type', 'section');
        $selectedClassRecord = $this->classes()->first(fn ($class) => (string) $class->class_name === $selectedClass);
        $selectedExamId = $request->integer('exam_id');
        $validExam = $groupedExams->contains(function ($exam) use ($selectedExamId) {
            return (int) $exam['id'] === $selectedExamId
                || in_array($selectedExamId, $exam['all_exam_ids'], true);
        });
        $validRankingType = in_array($rankingType, ['all_sections', 'section', 'stream_wise'], true);
        $isSeniorClass = $selectedClassRecord
            ? $this->isSeniorClass($selectedClassRecord->class_name, (int) $selectedClassRecord->id)
            : false;
        $hasFilters = $selectedClass !== ''
            && $selectedExamId > 0
            && $classes->contains($selectedClass)
            && $validExam
            && $validRankingType
            && ($rankingType !== 'section' || ($selectedSection !== '' && $sections->contains($selectedSection)))
            && ($rankingType !== 'stream_wise' || $isSeniorClass);
        $reportRequest = clone $request;
        if ($rankingType !== 'section') {
            $reportRequest->query->remove('section_name');
        }
        $reports = $hasFilters ? $this->buildReports($reportRequest) : collect();
        $rankedStudents = collect();

        foreach ($reports as $report) {
            $rollNumbers = collect();
            if (
                Schema::connection('dynamic')->hasTable('academic_student_roll_no')
                && !empty($report['exam'])
            ) {
                $rollNumbers = DB::connection('dynamic')
                    ->table('academic_student_roll_no')
                    ->where('class_id', (string) $report['class']->class_name)
                    ->where('section_id', (string) $report['class']->section_name)
                    ->where('session_id', (string) $report['session_year'])
                    ->where('exam_id', (int) $report['exam']->id)
                    ->pluck('roll_no', 'student_id');
            }

            foreach ($report['students'] as $studentResult) {
                $totalObtained = 0.0;
                $totalMaximum = 0.0;

                foreach ($studentResult['subject_scores'] as $score) {
                    if (empty($score['enrolled'])) {
                        continue;
                    }

                    $totalMaximum += (float) ($score['max_marks'] ?? 0);
                    if (!empty($score['appeared'])) {
                        $totalObtained += (float) ($score['obtained'] ?? 0);
                    }
                }

                if ($totalMaximum <= 0) {
                    continue;
                }

                $student = $studentResult['student'];
                $studentId = (int) ($student->id ?? 0);
                $rankedStudents->push([
                    'student' => $student,
                    'student_name' => (string) ($studentResult['student_name'] ?? ''),
                    'class_name' => (string) $report['class']->class_name,
                    'section_name' => (string) $report['class']->section_name,
                    'roll_no' => $rollNumbers->get($studentId)
                        ?: ($student->roll_no ?? $student->roll_number ?? ''),
                    'photo' => (string) ($student->student_image_url ?? ''),
                    'stream' => $this->resolveReportStream(
                        $studentResult['stream_name'] ?? '',
                        $studentResult['combination_name'] ?? ''
                    ),
                    'total_obtained' => $totalObtained,
                    'total_max' => $totalMaximum,
                    'percentage' => round(($totalObtained / $totalMaximum) * 100, 2),
                ]);
            }
        }

        $rankedStudents = $rankedStudents
            ->sort(function ($a, $b) {
                $percentageOrder = $b['percentage'] <=> $a['percentage'];
                if ($percentageOrder !== 0) {
                    return $percentageOrder;
                }

                $nameOrder = strnatcasecmp($a['student_name'], $b['student_name']);
                if ($nameOrder !== 0) {
                    return $nameOrder;
                }

                return ((int) ($a['student']->id ?? 0)) <=> ((int) ($b['student']->id ?? 0));
            })->values();

        if ($rankingType === 'section') {
            $rankedStudents = $rankedStudents
                ->filter(fn ($student) => $student['section_name'] === $selectedSection)
                ->values();
        }

        if ($rankingType === 'stream_wise') {
            $studentsByStream = $rankedStudents
                ->groupBy(fn ($student) => $student['stream'] ?: 'Unassigned')
                ->all();
            $streamNames = array_keys($studentsByStream);
            usort($streamNames, function ($streamA, $streamB) {
                $priorityOrder = $this->getReportStreamPriority($streamA) <=> $this->getReportStreamPriority($streamB);
                return $priorityOrder !== 0 ? $priorityOrder : strnatcasecmp($streamA, $streamB);
            });
            $rankGroups = collect($streamNames)->map(function ($stream) use ($studentsByStream) {
                $students = $this->assignCompetitionRanks($studentsByStream[$stream]->values());

                return [
                    'label' => $stream,
                    'students' => $students,
                    'top_students' => $students->take(3)->values(),
                ];
            });
        } else {
            $rankedStudents = $this->assignCompetitionRanks($rankedStudents);
            $classDisplay = 'Class ' . (AcademicHelper::toRoman($selectedClass) ?: $selectedClass);
            $groupLabel = $rankingType === 'section'
                ? $classDisplay . ' - ' . $selectedSection
                : $classDisplay . ' - All Sections';
            $rankGroups = collect([[
                'label' => $groupLabel,
                'students' => $rankedStudents,
                'top_students' => $rankedStudents->take(3)->values(),
            ]]);
        }

        return [
            'classes' => $classes,
            'sections' => $sections,
            'groupedExams' => $groupedExams,
            'sessionOptions' => collect(view()->shared('databaseNames') ?? [])
                ->filter(fn ($name) => is_string($name) && preg_match('/^\d{4}_\d{4}$/', $name))
                ->unique()
                ->sortDesc()
                ->values(),
            'selectedSession' => $selectedSession,
            'sessionYear' => $sessionYear,
            'rankingType' => $rankingType,
            'rankGroups' => $rankGroups,
            'rankedStudents' => $rankedStudents,
            'selectedExam' => $reports->first()['exam'] ?? null,
            'hasFilters' => $hasFilters,
            'isSeniorClass' => $isSeniorClass,
        ];
    }

    private function assignCompetitionRanks(Collection $students): Collection
    {
        $previousPercentage = null;
        $rank = 0;

        return $students->values()->map(function ($student, $index) use (&$previousPercentage, &$rank) {
            if ($previousPercentage === null || $student['percentage'] !== $previousPercentage) {
                $rank = $index + 1;
                $previousPercentage = $student['percentage'];
            }

            $student['rank'] = $rank;
            return $student;
        });
    }

    private function resolveReportStream(?string $streamName, ?string $combinationName): string
    {
        $stream = strtoupper(trim((string) $streamName));
        $combination = strtoupper(trim((string) $combinationName));

        foreach (['PCMB', 'PCM', 'PCB'] as $scienceStream) {
            if (preg_match('/\b' . $scienceStream . '\b/', $stream . ' ' . $combination)) {
                return $scienceStream;
            }
        }

        if (preg_match('/\b(COMMERCE|COMMERCE STREAM)\b/', $stream . ' ' . $combination)) {
            return 'Commerce';
        }

        if (preg_match('/\b(HUMANITIES|ARTS)\b/', $stream . ' ' . $combination)) {
            return 'Humanities';
        }

        return $stream !== '' ? ucwords(strtolower($stream)) : 'Unassigned';
    }

    private function getReportStreamPriority(string $stream): int
    {
        return match (strtoupper(trim($stream))) {
            'PCM' => 1,
            'PCB' => 2,
            'PCMB' => 3,
            'SCIENCE' => 4,
            'COMMERCE' => 5,
            'HUMANITIES', 'ARTS' => 6,
            'UNASSIGNED' => 99,
            default => 7,
        };
    }

    public function print(Request $request)
    {
        $sessionYear = $this->resolveDynamicSessionYear($request);
        $reports = $this->buildReports($request);
        $selectedExam = $request->integer('exam_id') ? Exam::query()->find($request->integer('exam_id')) : null;

        return view('backend.AcademicsModules.result_analysis_print', compact(
            'reports',
            'selectedExam',
            'sessionYear'
        ));
    }

    public function exportExcel(Request $request)
    {
        $sessionYear = $this->resolveDynamicSessionYear($request);
        $reports = $this->buildReports($request);

        $metricRows = [
            'TOTAL ON ROLL',
            'APPEARED',
            'PASSED',
            'FAIL/ COMPT.',
            'PASS PERCENTAGE',
            'HIGHEST',
            'LOWEST',
            'CLASS AVERAGE',
            'ABOVE 90',
            'ABOVE 75',
            'I Div.',
            'II Div.',
            'III Div.',
            'Below 33',
        ];

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $html .= '<style>';
        $html .= 'table { border-collapse: collapse; font-family: Calibri, Arial, sans-serif; font-size: 11pt; }';
        $html .= 'th, td { border: 0.5pt solid #222222; padding: 5px 8px; text-align: center; vertical-align: middle; }';
        $html .= '.title-head { font-size: 14pt; font-weight: bold; text-align: center; border: none; }';
        $html .= '.sub-head { font-size: 12pt; font-weight: bold; text-align: center; border: none; }';
        $html .= '.meta-row { font-size: 10pt; font-weight: bold; text-align: left; border: none; }';
        $html .= '.th-bg { background-color: #E2EFDA; font-weight: bold; }';
        $html .= '.th-overall { background-color: #DDEBF7; font-weight: bold; }';
        $html .= '.metric-label { font-weight: bold; text-align: left; background-color: #F2F2F2; }';
        $html .= '.highlight-bold { font-weight: bold; background-color: #FFF2CC; }';
        $html .= '.text-danger { color: #C00000; font-weight: bold; }';
        $html .= '</style>';
        $html .= '</head><body>';

        if ($reports->isEmpty()) {
            $html .= '<p>No Result Analysis records found for the selected filters.</p>';
        } else {
            foreach ($reports as $report) {
                $cols = $report['subject_columns'];
                $totalCols = count($cols) + 2; // Metric + subjects + OVERALL

                $className = AcademicHelper::formatClassSection($report['class']->class_name, $report['class']->section_name);
                $examTitle = $report['exam_title'];
                $session = str_replace('_', '-', $report['session_year']);

                $html .= '<table border="1">';
                // Title
                $html .= '<tr><td colspan="' . $totalCols . '" class="title-head">LOKMANYA VIDYA NIKETAN</td></tr>';
                $html .= '<tr><td colspan="' . $totalCols . '" class="sub-head">RESULT ANALYSIS &mdash; ' . e($className) . '</td></tr>';
                $html .= '<tr><td colspan="' . $totalCols . '" class="meta-row">Exam: ' . e($examTitle) . ' | Session: ' . e($session) . ' | Class Teacher: ' . e($report['class_teacher'] ?: 'Not Assigned') . ' | Total On Roll: ' . e($report['total_on_roll']) . '</td></tr>';
                $html .= '<tr><td colspan="' . $totalCols . '" style="border:none;">&nbsp;</td></tr>';

                // Table Header
                $html .= '<tr>';
                $html .= '<th class="th-bg" style="text-align: left;">METRIC</th>';
                foreach ($cols as $colKey => $colDef) {
                    $html .= '<th class="th-bg">' . e($colDef['label']) . '</th>';
                }
                $html .= '<th class="th-overall">OVERALL</th>';
                $html .= '</tr>';

                // The 14 Metric Rows
                foreach ($metricRows as $metricKey) {
                    $isHighlight = in_array($metricKey, ['PASS PERCENTAGE', 'CLASS AVERAGE'], true);
                    $isDanger = in_array($metricKey, ['FAIL/ COMPT.', 'Below 33'], true);
                    $cellClass = $isHighlight ? 'highlight-bold' : ($isDanger ? 'text-danger' : '');

                    $html .= '<tr>';
                    $html .= '<td class="metric-label">' . e($metricKey) . '</td>';
                    foreach ($cols as $colKey => $colDef) {
                        $val = $report['stats'][$metricKey][$colKey] ?? 0;
                        $html .= '<td class="' . $cellClass . '">' . e($val) . '</td>';
                    }
                    $overallVal = $report['stats'][$metricKey]['OVERALL'] ?? 0;
                    $html .= '<td class="' . $cellClass . ' th-overall">' . e($overallVal) . '</td>';
                    $html .= '</tr>';
                }

                $html .= '</table>';
                $html .= '<br><br>';
            }
        }

        $html .= '</body></html>';

        $filterClass = $request->input('class_name') ?: 'all';
        $filterSection = $request->input('section_name') ?: 'all';
        $filename = 'result-analysis-' . strtolower($filterClass) . '-' . strtolower($filterSection) . '-' . date('Ymd_His') . '.xls';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function buildReports(Request $request): Collection
    {
        $className = $request->filled('classname')
            ? (string) $request->input('classname')
            : ($request->filled('class_name') ? (string) $request->input('class_name') : null);
        $sectionName = $request->filled('section_name') ? (string) $request->input('section_name') : null;
        $classId = $request->integer('class_id') ?: null;
        $selectedExam = $request->integer('exam_id') ? Exam::query()->find($request->integer('exam_id')) : null;

        $classes = $this->classes()
            ->when($className, fn ($items) => $items->where('class_name', $className))
            ->when($sectionName, fn ($items) => $items->where('section_name', $sectionName))
            ->when($classId && !$className, fn ($items) => $items->where('id', $classId))
            ->values();

        return $classes
            ->map(fn ($class) => $this->buildClassResultAnalysis($class, $selectedExam, $request))
            ->filter(fn ($report) => count($report['students']) > 0)
            ->values();
    }

    private function buildClassResultAnalysis($class, ?Exam $selectedExam, Request $request): array
    {
        $sessionYear = $this->resolveDynamicSessionYear($request);
        $isSenior = $this->isSeniorClass($class->class_name, (int) $class->id);

        // Fetch students enrolled in this class and section
        $students = $this->studentsForClass((int) $class->id, $class->class_name, $class->section_name);
        $totalOnRoll = $students->count();

        // Resolve exam for this class
        $resolvedExam = $selectedExam ? $this->resolveExamForClass((int) $selectedExam->id, (int) $class->id, $class->class_name) : null;
        $activeExam = $resolvedExam ?: $selectedExam;

        $examTitle = $activeExam ? ($activeExam->exam_title ?: $activeExam->exam_name) : 'Academic Examination';
        if ($activeExam && !empty($activeExam->exam_type) && strcasecmp($activeExam->exam_name, $activeExam->exam_type) !== 0) {
            $examTitle .= ' (' . $activeExam->exam_type . ')';
        }

        // Fetch student stream & combination assignments for senior classes
        $combAssignments = $isSenior ? $this->studentCombinationAssignments() : [];

        // Exam IDs to query marks
        $examIds = $activeExam ? [(int) $activeExam->id] : [];
        if (empty($examIds)) {
            // Fallback: look for available exams for this class
            $examIds = DB::connection('dynamic')->table('academic_exam')
                ->whereIn('class_id', $this->marksClassIdsFor((int) $class->id))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $studentIds = $students->pluck('id')->map(fn ($id) => (int) $id)->all();
        $marks = $this->marksForStudents($studentIds, (int) $class->id, $examIds);
        $reportAccess = app(\App\Services\StaffReportAccessService::class);
        $allowedSubjectIds = $reportAccess->allowedSubjectIds($class->id, $class->section_name);
        if (is_array($allowedSubjectIds)) {
            $marks = $reportAccess->filterSubjectData($marks, $class->id, $class->section_name, 'subject_id');
        }

        // Determine Subject Columns
        // In senior classes (like in reference image): ENGLISH, MATHS, PHYSICS, CHEMISTRY, BIOLOGY, OPTIONAL
        $subjectColumns = $this->determineSubjectColumns($class, $students, $marks, $combAssignments, $isSenior);
        if (is_array($allowedSubjectIds)) {
            $allowedSubjectNames = DB::connection('dynamic')->table('subjectmaster')
                ->whereIn('id', $allowedSubjectIds)
                ->pluck('subject_name')
                ->filter()
                ->all();

            $subjectColumns = array_filter($subjectColumns, function ($column) use ($allowedSubjectIds, $allowedSubjectNames) {
                if (isset($column['subject_id'])) {
                    return in_array((int) $column['subject_id'], $allowedSubjectIds, true);
                }

                if (!empty($column['pattern'])) {
                    foreach ($allowedSubjectNames as $subjectName) {
                        if (preg_match($column['pattern'], (string) $subjectName)) {
                            return true;
                        }
                    }
                    return false;
                }

                return in_array(strtolower((string) ($column['label'] ?? '')), array_map('strtolower', $allowedSubjectNames), true);
            });
        }

        $hasAnyMarksInClass = false;
        $studentRows = [];

        foreach ($students as $student) {
            $studentId = (int) $student->id;
            $combData = $combAssignments[$studentId] ?? null;

            if (empty($combData['combination_name']) && !empty($student->subject_combination)) {
                $combData = [
                    'combination_id' => 9999,
                    'combination_name' => $student->subject_combination,
                    'stream_id' => 9999,
                    'stream_name' => '',
                ];
            }
            if (empty($combData['combination_name']) && !empty($student->json_str)) {
                $json = is_array($student->json_str) ? $student->json_str : json_decode((string) $student->json_str, true);
                if (is_array($json) && (!empty($json['subject_combination']) || !empty($json['combination']))) {
                    $cName = $json['subject_combination'] ?? $json['combination'];
                    $combData = [
                        'combination_id' => 9999,
                        'combination_name' => $cName,
                        'stream_id' => 9999,
                        'stream_name' => '',
                    ];
                }
            }

            $studentTotal = 0.0;
            $studentMaxTotal = 0.0;
            $studentAppeared = false;
            $failedSubjects = [];
            $subjectScores = [];

            foreach ($subjectColumns as $colKey => $colDef) {
                $subScore = $this->extractStudentSubjectScore($studentId, $colKey, $colDef, $marks, $combData, $activeExam);
                $subjectScores[$colKey] = $subScore;

                if ($subScore['appeared']) {
                    $studentAppeared = true;
                    $studentTotal += $subScore['obtained'];
                    $studentMaxTotal += $subScore['max_marks'];
                    $hasAnyMarksInClass = true;

                    if ($subScore['is_failed']) {
                        $failedSubjects[] = $colDef['label'];
                    }
                }
            }

            $percentage = null;
            if ($studentAppeared && $studentMaxTotal > 0) {
                $percentage = round(($studentTotal / $studentMaxTotal) * 100, 1);
            }

            $division = '';
            if ($percentage !== null) {
                if ($percentage >= 60.0) $division = 'I Div.';
                elseif ($percentage >= 45.0) $division = 'II Div.';
                elseif ($percentage >= 33.0) $division = 'III Div.';
                else $division = 'Below 33';
            }

            $resultStatus = '';
            $resultCode = '';
            $failSubjectName = '';

            if ($studentAppeared) {
                $failCount = count($failedSubjects);
                if ($failCount === 0) {
                    $resultStatus = 'PASS';
                    $resultCode = 'PASS';
                } elseif ($failCount === 1) {
                    $failSubjectName = $failedSubjects[0];
                    $resultStatus = 'COMP ' . strtoupper($failSubjectName);
                    $resultCode = 'COMP';
                } else {
                    $resultStatus = 'FAIL';
                    $resultCode = 'FAIL';
                }
            } else {
                $resultStatus = 'AB';
                $resultCode = 'AB';
            }

            $studentRows[] = [
                'student' => $student,
                'scholar_no' => $student->scholar_no ?? '',
                'student_name' => $student->student_name ?? '',
                'combination_name' => $combData['combination_name'] ?? '',
                'stream_name' => $combData['stream_name'] ?? '',
                'subject_scores' => $subjectScores,
                'total_obtained' => $studentAppeared ? $studentTotal : null,
                'total_max' => $studentAppeared ? $studentMaxTotal : null,
                'percentage' => $percentage,
                'division' => $division,
                'result_status' => $resultStatus,
                'result_code' => $resultCode,
                'failed_subjects' => $failedSubjects,
                'appeared' => $studentAppeared,
            ];
        }

        // Sort students: If senior class, sort by combination group, then name; otherwise by name or scholar_no
        if ($isSenior) {
            usort($studentRows, function ($a, $b) {
                $cmp = strnatcasecmp($a['combination_name'], $b['combination_name']);
                if ($cmp !== 0) return $cmp;
                return strnatcasecmp($a['student_name'], $b['student_name']);
            });
        } else {
            usort($studentRows, function ($a, $b) {
                return strnatcasecmp($a['student_name'], $b['student_name']);
            });
        }

        // Compute the 14 Result Analysis Statistics Rows (matching reference image)
        $analysisColumns = array_keys($subjectColumns);
        $analysisColumns[] = 'OVERALL';

        $stats = [
            'TOTAL ON ROLL' => [],
            'APPEARED' => [],
            'PASSED' => [],
            'FAIL/ COMPT.' => [],
            'PASS PERCENTAGE' => [],
            'HIGHEST' => [],
            'LOWEST' => [],
            'CLASS AVERAGE' => [],
            'ABOVE 90' => [],
            'ABOVE 75' => [],
            'I Div.' => [],
            'II Div.' => [],
            'III Div.' => [],
            'Below 33' => [],
        ];

        foreach ($analysisColumns as $colKey) {
            if ($colKey !== 'OVERALL') {
                $colDef = $subjectColumns[$colKey];
                $onRoll = 0;
                $appeared = 0;
                $passed = 0;
                $failed = 0;
                $obtainedList = [];
                $above90 = 0;
                $above75 = 0;
                $iDiv = 0;
                $iiDiv = 0;
                $iiiDiv = 0;
                $below33 = 0;

                foreach ($studentRows as $row) {
                    $score = $row['subject_scores'][$colKey] ?? null;
                    if ($score && $score['enrolled']) {
                        $onRoll++;
                    }
                    if ($score && $score['appeared']) {
                        $appeared++;
                        $num = $score['obtained'];
                        $pct = $score['max_marks'] > 0 ? ($num / $score['max_marks']) * 100 : 0;
                        $obtainedList[] = $num;

                        if ($pct >= 33.0) {
                            $passed++;
                        } else {
                            $failed++;
                        }

                        if ($pct >= 90.0) $above90++;
                        if ($pct >= 75.0) $above75++;

                        if ($pct >= 60.0) $iDiv++;
                        elseif ($pct >= 45.0) $iiDiv++;
                        elseif ($pct >= 33.0) $iiiDiv++;
                        else $below33++;
                    }
                }

                $passPct = $appeared > 0 ? round(($passed / $appeared) * 100, 1) : 0;
                $highest = count($obtainedList) > 0 ? max($obtainedList) : 0;
                $lowest = count($obtainedList) > 0 ? min($obtainedList) : 0;
                $avg = count($obtainedList) > 0 ? round(array_sum($obtainedList) / count($obtainedList), 1) : 0;

                $stats['TOTAL ON ROLL'][$colKey] = $onRoll;
                $stats['APPEARED'][$colKey] = $appeared;
                $stats['PASSED'][$colKey] = $passed;
                $stats['FAIL/ COMPT.'][$colKey] = $failed;
                $stats['PASS PERCENTAGE'][$colKey] = $passPct;
                $stats['HIGHEST'][$colKey] = number_format($highest, 1);
                $stats['LOWEST'][$colKey] = number_format($lowest, 1);
                $stats['CLASS AVERAGE'][$colKey] = number_format($avg, 1);
                $stats['ABOVE 90'][$colKey] = $above90;
                $stats['ABOVE 75'][$colKey] = $above75;
                $stats['I Div.'][$colKey] = $iDiv;
                $stats['II Div.'][$colKey] = $iiDiv;
                $stats['III Div.'][$colKey] = $iiiDiv;
                $stats['Below 33'][$colKey] = $below33;
            } else {
                // OVERALL column calculations
                $onRoll = $totalOnRoll;
                $appeared = 0;
                $passed = 0;
                $failed = 0;
                $pctList = [];
                $above90 = 0;
                $above75 = 0;
                $iDiv = 0;
                $iiDiv = 0;
                $iiiDiv = 0;
                $below33 = 0;

                foreach ($studentRows as $row) {
                    if ($row['appeared'] && $row['percentage'] !== null) {
                        $appeared++;
                        $pct = $row['percentage'];
                        $pctList[] = $pct;

                        if ($row['result_code'] === 'PASS') {
                            $passed++;
                        } else {
                            $failed++;
                        }

                        if ($pct >= 90.0) $above90++;
                        if ($pct >= 75.0) $above75++;

                        if ($pct >= 60.0) $iDiv++;
                        elseif ($pct >= 45.0) $iiDiv++;
                        elseif ($pct >= 33.0) $iiiDiv++;
                        else $below33++;
                    }
                }

                $passPct = $appeared > 0 ? round(($passed / $appeared) * 100, 1) : 0;
                $highest = count($pctList) > 0 ? max($pctList) : 0;
                $lowest = count($pctList) > 0 ? min($pctList) : 0;
                $avg = count($pctList) > 0 ? round(array_sum($pctList) / count($pctList), 1) : 0;

                $stats['TOTAL ON ROLL'][$colKey] = $onRoll;
                $stats['APPEARED'][$colKey] = $appeared;
                $stats['PASSED'][$colKey] = $passed;
                $stats['FAIL/ COMPT.'][$colKey] = $failed;
                $stats['PASS PERCENTAGE'][$colKey] = $passPct;
                $stats['HIGHEST'][$colKey] = number_format($highest, 1);
                $stats['LOWEST'][$colKey] = number_format($lowest, 1);
                $stats['CLASS AVERAGE'][$colKey] = number_format($avg, 1);
                $stats['ABOVE 90'][$colKey] = $above90;
                $stats['ABOVE 75'][$colKey] = $above75;
                $stats['I Div.'][$colKey] = $iDiv;
                $stats['II Div.'][$colKey] = $iiDiv;
                $stats['III Div.'][$colKey] = $iiiDiv;
                $stats['Below 33'][$colKey] = $below33;
            }
        }

        $classTeacher = $this->classTeacherName($class);

        return [
            'class' => $class,
            'is_senior' => $isSenior,
            'exam' => $activeExam,
            'exam_title' => $examTitle,
            'session_year' => $sessionYear,
            'class_teacher' => $classTeacher,
            'subject_columns' => $subjectColumns,
            'students' => $studentRows,
            'stats' => $stats,
            'total_on_roll' => $totalOnRoll,
            'has_marks' => $hasAnyMarksInClass,
        ];
    }

    private function determineSubjectColumns($class, Collection $students, Collection $marks, array $combAssignments, bool $isSenior): array
    {
        $columns = [];

        if ($isSenior) {
            // Find distinct academic subjects appearing in marks or assigned to class
            $markedSubjectNames = $marks->pluck('subject_name')->filter()->unique()->values()->all();

            // Known canonical senior subjects
            $seniorSubjectOrder = [
                'ENGLISH' => ['pattern' => '/english/i', 'label' => 'ENGLISH'],
                'MATHS' => ['pattern' => '/math/i', 'label' => 'MATHS'],
                'PHYSICS' => ['pattern' => '/physics/i', 'label' => 'PHYSICS'],
                'CHEMISTRY' => ['pattern' => '/chemistry/i', 'label' => 'CHEMISTRY'],
                'BIOLOGY' => ['pattern' => '/bio/i', 'label' => 'BIOLOGY'],
                'ACCOUNTANCY' => ['pattern' => '/account/i', 'label' => 'ACCOUNTANCY'],
                'BUSINESS STUDIES' => ['pattern' => '/business/i', 'label' => 'BST'],
                'ECONOMICS' => ['pattern' => '/economic/i', 'label' => 'ECONOMICS'],
                'HISTORY' => ['pattern' => '/history/i', 'label' => 'HISTORY'],
                'POLITICAL SCIENCE' => ['pattern' => '/political/i', 'label' => 'POL SCIENCE'],
            ];

            // Optional subjects pattern
            $optionalPattern = '/(physical education|\bpe\b|computer science|\bcs\b|informatics practices|\bip\b|dance|music|applied math|psychology|entrepreneurship|hindi)/i';

            // Check which core senior subjects are present
            foreach ($seniorSubjectOrder as $key => $info) {
                $hasMark = false;
                foreach ($markedSubjectNames as $sName) {
                    if (preg_match($info['pattern'], $sName) && !preg_match($optionalPattern, $sName)) {
                        $hasMark = true;
                        break;
                    }
                }
                // Also check combination names
                if (!$hasMark) {
                    foreach ($combAssignments as $cData) {
                        $cName = $cData['combination_name'] ?? '';
                        if (preg_match($info['pattern'], $cName) && !preg_match($optionalPattern, $cName)) {
                            $hasMark = true;
                            break;
                        }
                    }
                }

                if ($hasMark) {
                    $columns[$key] = [
                        'label' => $info['label'],
                        'type' => 'core',
                        'pattern' => $info['pattern'],
                    ];
                }
            }

            // Always provide the OPTIONAL column for senior classes (groups 5th/6th elective subjects like PE, CS, IP, Dance, Music)
            $columns['OPTIONAL'] = [
                'label' => 'OPTIONAL',
                'type' => 'optional',
                'pattern' => $optionalPattern,
            ];

            // If no core subjects detected yet (e.g. fresh year), supply standard Science & Commerce defaults
            if (count($columns) <= 1) {
                $columns = [
                    'ENGLISH' => ['label' => 'ENGLISH', 'type' => 'core', 'pattern' => '/english/i'],
                    'MATHS' => ['label' => 'MATHS', 'type' => 'core', 'pattern' => '/math/i'],
                    'PHYSICS' => ['label' => 'PHYSICS', 'type' => 'core', 'pattern' => '/physics/i'],
                    'CHEMISTRY' => ['label' => 'CHEMISTRY', 'type' => 'core', 'pattern' => '/chemistry/i'],
                    'BIOLOGY' => ['label' => 'BIOLOGY', 'type' => 'core', 'pattern' => '/bio/i'],
                    'OPTIONAL' => ['label' => 'OPTIONAL', 'type' => 'optional', 'pattern' => $optionalPattern],
                ];
            }
        } else {
            // Regular classes (1 to 10): dynamic subjects from marks table or subjectmaster
            $markedSubjects = $marks->unique('subject_id')->values();

            if ($markedSubjects->isNotEmpty()) {
                foreach ($markedSubjects as $sub) {
                    $sName = strtoupper(trim((string) ($sub->subject_name ?: 'SUBJECT')));
                    $columns['SUB_' . $sub->subject_id] = [
                        'label' => $sName,
                        'type' => 'regular',
                        'subject_id' => (int) $sub->subject_id,
                        'subject_name' => $sub->subject_name,
                    ];
                }
            } else {
                // Fallback from academic_class_subject or subjectmaster
                $fallbackSubs = DB::connection('dynamic')->table('academic_class_subject as acs')
                    ->join('subjectmaster as s', 's.id', '=', 'acs.subject_id')
                    ->where('acs.class_id', $class->id)
                    ->where(function ($q) {
                        $q->where('s.is_delete', 0)->orWhereNull('s.is_delete');
                    })
                    ->select('s.id', 's.subject_name')
                    ->orderBy('acs.id')
                    ->get();

                if ($fallbackSubs->isNotEmpty()) {
                    foreach ($fallbackSubs as $sub) {
                        $sName = strtoupper(trim((string) ($sub->subject_name ?: 'SUBJECT')));
                        $columns['SUB_' . $sub->id] = [
                            'label' => $sName,
                            'type' => 'regular',
                            'subject_id' => (int) $sub->id,
                            'subject_name' => $sub->subject_name,
                        ];
                    }
                } else {
                    // Default scholastic subjects
                    $defaultSubs = ['ENGLISH', 'HINDI', 'MATHEMATICS', 'SCIENCE', 'SOCIAL SCIENCE'];
                    foreach ($defaultSubs as $dSub) {
                        $columns[$dSub] = [
                            'label' => $dSub,
                            'type' => 'default',
                            'subject_name' => $dSub,
                        ];
                    }
                }
            }
        }

        return $columns;
    }

    private function extractStudentSubjectScore(int $studentId, string $colKey, array $colDef, Collection $marks, ?array $combData, ?Exam $exam): array
    {
        $maxMarks = 100.0;
        if ($exam && !empty($exam->max_marks_theory)) {
            $thMax = (float) $exam->max_marks_theory;
            $prMax = !empty($exam->max_marks_practical) ? (float) $exam->max_marks_practical : 0.0;
            $maxMarks = $thMax + $prMax;
            if ($maxMarks <= 0) $maxMarks = 100.0;
        }

        if ($colDef['type'] === 'core') {
            // Find mark matching the pattern
            $matchedMark = $marks->first(function ($m) use ($studentId, $colDef) {
                if ((int) ($m->student_id ?? 0) !== $studentId) return false;
                return preg_match($colDef['pattern'], (string) ($m->subject_name ?? ''));
            });

            // Check if student is enrolled in this subject (from combination name or marks)
            $isEnrolled = false;
            if ($matchedMark) {
                $isEnrolled = true;
            } elseif ($combData && !empty($combData['combination_name'])) {
                $cName = strtoupper($combData['combination_name']);
                switch ($colKey) {
                    case 'ENGLISH':
                        $isEnrolled = true;
                        break;
                    case 'PHYSICS':
                        $isEnrolled = preg_match('/\b(PCM|PCB|PCMB|PHYSICS)\b/i', $cName) || str_contains($cName, 'PHYSICS');
                        break;
                    case 'CHEMISTRY':
                        $isEnrolled = preg_match('/\b(PCM|PCB|PCMB|CHEMISTRY)\b/i', $cName) || str_contains($cName, 'CHEMISTRY');
                        break;
                    case 'MATHS':
                        $isEnrolled = (preg_match('/\b(PCM|PCMB)\b/i', $cName) || str_contains($cName, 'MATH')) && !str_contains($cName, 'APPLIED MATH');
                        break;
                    case 'BIOLOGY':
                        $isEnrolled = preg_match('/\b(PCB|PCMB)\b/i', $cName) || str_contains($cName, 'BIO');
                        break;
                    case 'ACCOUNTANCY':
                        $isEnrolled = str_contains($cName, 'COMMERCE') || str_contains($cName, 'ACCOUNT');
                        break;
                    case 'BUSINESS STUDIES':
                        $isEnrolled = str_contains($cName, 'COMMERCE') || str_contains($cName, 'BUSINESS') || str_contains($cName, 'BST');
                        break;
                    case 'ECONOMICS':
                        $isEnrolled = str_contains($cName, 'COMMERCE') || str_contains($cName, 'ECONOMICS') || str_contains($cName, 'ECO');
                        break;
                    default:
                        $isEnrolled = preg_match($colDef['pattern'], $cName);
                        break;
                }
            } else {
                if ($colKey === 'ENGLISH') {
                    $isEnrolled = true;
                }
            }

            if (!$matchedMark) {
                return [
                    'enrolled' => $isEnrolled,
                    'appeared' => false,
                    'is_absent' => false,
                    'obtained' => 0.0,
                    'max_marks' => $maxMarks,
                    'display' => $isEnrolled ? '-' : '-',
                    'is_failed' => false,
                ];
            }

            $isAbsent = !empty($matchedMark->is_absent);
            $val = $matchedMark->total_marks ?? ($matchedMark->mark_theory ?? null);
            $obtained = is_numeric($val) ? (float) $val : 0.0;
            $isFailed = !$isAbsent && ($obtained < ($maxMarks * 0.33));

            return [
                'enrolled' => true,
                'appeared' => !$isAbsent && is_numeric($val),
                'is_absent' => $isAbsent,
                'obtained' => $obtained,
                'max_marks' => $maxMarks,
                'display' => $isAbsent ? 'AB' : (is_numeric($val) ? number_format($obtained, 1) : '-'),
                'is_failed' => $isFailed,
            ];
        }

        if ($colDef['type'] === 'optional') {
            // Find optional mark matching optional pattern
            $matchedMark = $marks->first(function ($m) use ($studentId, $colDef) {
                if ((int) ($m->student_id ?? 0) !== $studentId) return false;
                $sName = (string) ($m->subject_name ?? '');
                $sType = (string) ($m->subject_type ?? '');
                return strcasecmp($sType, 'Optional') === 0 || preg_match($colDef['pattern'], $sName);
            });

            // Enrolled if has mark or student combination has optional subject
            $isEnrolled = false;
            $optionalSubName = '';
            if ($matchedMark) {
                $isEnrolled = true;
                $optionalSubName = $matchedMark->subject_name;
            } elseif ($combData && !empty($combData['combination_name'])) {
                $isEnrolled = true;
                $optionalSubName = $combData['combination_name'];
            }

            if (!$matchedMark) {
                return [
                    'enrolled' => $isEnrolled,
                    'appeared' => false,
                    'is_absent' => false,
                    'obtained' => 0.0,
                    'max_marks' => $maxMarks,
                    'display' => '-',
                    'optional_name' => $optionalSubName,
                    'is_failed' => false,
                ];
            }

            $isAbsent = !empty($matchedMark->is_absent);
            $val = $matchedMark->total_marks ?? ($matchedMark->mark_theory ?? null);
            $obtained = is_numeric($val) ? (float) $val : 0.0;
            $isFailed = !$isAbsent && ($obtained < ($maxMarks * 0.33));

            return [
                'enrolled' => true,
                'appeared' => !$isAbsent && is_numeric($val),
                'is_absent' => $isAbsent,
                'obtained' => $obtained,
                'max_marks' => $maxMarks,
                'display' => $isAbsent ? 'AB' : (is_numeric($val) ? number_format($obtained, 1) : '-'),
                'optional_name' => $matchedMark->subject_name ?? $optionalSubName,
                'is_failed' => $isFailed,
            ];
        }

        // Regular subject by ID or Name
        $matchedMark = $marks->first(function ($m) use ($studentId, $colDef) {
            if ((int) ($m->student_id ?? 0) !== $studentId) return false;
            if (isset($colDef['subject_id'])) {
                return (int) ($m->subject_id ?? 0) === $colDef['subject_id'];
            }
            return strcasecmp(trim((string) ($m->subject_name ?? '')), trim((string) ($colDef['subject_name'] ?? ''))) === 0;
        });

        if (!$matchedMark) {
            return [
                'enrolled' => true,
                'appeared' => false,
                'is_absent' => false,
                'obtained' => 0.0,
                'max_marks' => $maxMarks,
                'display' => '-',
                'is_failed' => false,
            ];
        }

        $isAbsent = !empty($matchedMark->is_absent);
        $val = $matchedMark->total_marks ?? ($matchedMark->mark_theory ?? null);
        $obtained = is_numeric($val) ? (float) $val : 0.0;
        $isFailed = !$isAbsent && ($obtained < ($maxMarks * 0.33));

        return [
            'enrolled' => true,
            'appeared' => !$isAbsent && is_numeric($val),
            'is_absent' => $isAbsent,
            'obtained' => $obtained,
            'max_marks' => $maxMarks,
            'display' => $isAbsent ? 'AB' : (is_numeric($val) ? number_format($obtained, 1) : '-'),
            'is_failed' => $isFailed,
        ];
    }

    private function marksForStudents(array $studentIds, int $classId, array $examIds): Collection
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
            ->leftJoin('subjectmaster as s', 's.id', '=', 'me.subject_id')
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

        return $query->select(
            'sm.*',
            'me.exam_id',
            'me.subject_id',
            'me.class_id',
            's.subject_name',
            's.subject_type'
        )->get();
    }

    private function classes(): Collection
    {
        return app(\App\Services\StaffReportAccessService::class)->filterClassOptions(
            Classes::query()
            ->orderBy('class_name')
            ->orderBy('section_name')
            ->get()
        );
    }

    private function classOptions(): Collection
    {
        return $this->classes()
            ->pluck('class_name')
            ->filter()
            ->unique()
            ->values();
    }

    private function sectionsForRequest(Request $request): Collection
    {
        $className = $request->filled('class_name') ? (string) $request->input('class_name') : null;

        $sections = $this->classes()
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

    private function sectionsByClass(): array
    {
        $base = $this->classes()
            ->groupBy('class_name')
            ->map(fn ($items) => $items->pluck('section_name')->filter()->unique()->values())
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

    private function groupedExams(Collection $rawExams): Collection
    {
        return $rawExams->groupBy(function ($exam) {
            return $exam->exam_group_id ?: ($exam->exam_name . '|' . $exam->exam_type);
        })->map(function ($group) {
            $first = $group->first();

            $classesData = [];
            foreach ($group as $examItem) {
                $cName = optional($examItem->classInfo)->class_name;
                $cId = (int) $examItem->class_id;
                if ($cName || $cId) {
                    $classesData[] = [
                        'exam_id' => (int) $examItem->id,
                        'class_id' => (int) $examItem->class_id,
                        'class_name' => (string) ($cName ?: ('Class ' . $cId)),
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
                'class_names' => collect($uniqueClasses)->pluck('class_name')->map(fn ($n) => (string) $n)->all(),
                'all_exam_ids' => $group->pluck('id')->map(fn ($id) => (int) $id)->all(),
            ];
        })->values();
    }

    private function resolveExamForClass(?int $examId, ?int $classId, ?string $className = null): ?Exam
    {
        if (!$examId) {
            return null;
        }

        $selectedExam = Exam::query()->find($examId);
        if (!$selectedExam) {
            return null;
        }

        if ((int) $selectedExam->class_id === (int) $classId) {
            return $selectedExam;
        }

        $classIds = $this->marksClassIdsFor($classId);
        $examGroupId = $selectedExam->exam_group_id ?: null;

        $query = Exam::query();
        if ($examGroupId) {
            $query->where('exam_group_id', $examGroupId)->whereIn('class_id', $classIds);
        } else {
            $query->where('exam_name', $selectedExam->exam_name)
                ->where('exam_type', $selectedExam->exam_type)
                ->whereIn('class_id', $classIds);
        }

        return $query->first() ?: $selectedExam;
    }

    private function studentsForClass(int $classId, ?string $className = null, ?string $sectionName = null): Collection
    {
        if (!Schema::connection('dynamic')->hasTable('student_registration')) {
            return collect();
        }

        $class = Classes::query()->find($classId);
        $cName = $className ?: ($class ? $class->class_name : null);
        $sName = $sectionName ?: ($class ? $class->section_name : null);

        $query = DB::connection('dynamic')->table('student_registration')
            ->where(function ($q) {
                $q->where('is_archived', 0)->orWhereNull('is_archived');
            })
            ->where(function ($q) use ($classId, $cName) {
                $q->where('class_id', (string) $classId);
                if (!empty($cName)) {
                    $q->orWhere('class_name', $cName);
                    $stripped = ltrim($cName, '0');
                    if ($stripped !== $cName) {
                        $q->orWhere('class_name', $stripped);
                    }
                }
            });

        if (!empty($sName)) {
            $query->where(function ($q) use ($sName) {
                $q->where('section_name', $sName);
                if (Schema::connection('dynamic')->hasColumn('student_registration', 'json_str')) {
                    $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(json_str, '$.section_name')) = ?", [$sName]);
                }
            });
        }

        return $query->orderBy('student_name')->get();
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
                'sc.id as combination_id',
                'sc.combination_name',
                'str.id as stream_id',
                'str.streams as stream_name'
            );

        if ($hasIsDelete) {
            $query->addSelect('sas.is_delete');
        }

        $rows = $query->orderBy('sas.id', 'asc')->get();
        $activeMap = [];
        $fallbackMap = [];

        foreach ($rows as $row) {
            $raw = $row->students_details;
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
                'combination_id' => $row->combination_id,
                'combination_name' => $row->combination_name ?: 'General Combination',
                'stream_id' => $row->stream_id,
                'stream_name' => $row->stream_name ?: 'General Stream',
            ];

            $isDeleted = $hasIsDelete && !empty($row->is_delete) && (int) $row->is_delete === 1;

            foreach ($ids as $sId) {
                if (!$isDeleted) {
                    $activeMap[$sId] = $combInfo;
                } else {
                    $fallbackMap[$sId] = $combInfo;
                }
            }
        }

        return $activeMap + $fallbackMap;
    }

    private function isSeniorClass(?string $className, ?int $classId = null): bool
    {
        if ($classId && in_array($classId, $this->seniorClassIds, true)) {
            return true;
        }

        if (empty($className)) {
            return false;
        }

        $normalized = strtoupper(trim($className));
        if (in_array($normalized, $this->seniorClassNames, true)) {
            return true;
        }

        $digits = preg_replace('/[^0-9]/', '', $className);
        return $digits === '11' || $digits === '12';
    }

    private function marksClassIdsFor(?int $classId): array
    {
        if (!$classId) return [];

        $class = Classes::query()->find($classId);
        if (!$class) return [(int) $classId];

        $matchedIds = Classes::query()
            ->where('class_name', $class->class_name)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_merge([(int) $classId], $matchedIds)));
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
                $teacher = null;
            }

            if (!empty($teacher)) {
                return trim($teacher);
            }
        }

        return '';
    }

    private function resolveDynamicSessionYear(Request $request): string
    {
        $session = $request->query('session')
            ?? $request->query('academic_session')
            ?? session('academic_session')
            ?? session('session_name');

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
