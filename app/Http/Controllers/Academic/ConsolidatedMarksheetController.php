<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Exam;
use App\Models\Classes;
use App\Models\Classname;
use App\Models\SubjectAssignStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ConsolidatedMarksheetController extends Controller
{
    public function index(Request $request)
    {
        $classes = $this->classOptions();
        $sections = $this->sectionsForRequest($request);
        $sectionsByClass = $this->sectionsByClass();
        $exams = Exam::with('classInfo')->latest('id')->get();
        $reports = $request->hasAny(['class_name', 'section_name', 'class_id', 'exam_id'])
            ? $this->buildReports($request)
            : collect();

        return view('backend.AcademicsModules.consolidated_marksheet', compact('classes', 'sections', 'sectionsByClass', 'exams', 'reports'));
    }

    public function print(Request $request)
    {
        $reports = $this->buildReports($request);

        $selectedExam = $request->integer('exam_id') ? Exam::query()->find($request->integer('exam_id')) : null;

        return view('backend.AcademicsModules.consolidated_marksheet_print', compact('reports', 'selectedExam'));
    }

    private function buildReports(Request $request)
    {
        $className = $request->filled('class_name') ? (string) $request->input('class_name') : null;
        $sectionName = $request->filled('section_name') ? (string) $request->input('section_name') : null;
        $classId = $request->integer('class_id') ?: null;
        $selectedExam = $request->integer('exam_id') ? Exam::query()->find($request->integer('exam_id')) : null;
        $classes = $this->classes()
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
        $students = $this->studentsForClass((int) $class->id);
        $examMap = $this->examMapForClass((int) $class->id, $selectedExam, $request);
        $examIds = collect($examMap)->filter()->pluck('id')->map(fn ($id) => (int) $id)->unique()->values()->all();
        $marks = $this->marksForStudents($students->pluck('id')->all(), (int) $class->id, $examIds);
        $subjectsByStudent = $this->assignedSubjectsForStudents($students, $class, $marks);

        $studentRows = $students->map(function ($student) use ($marks, $subjectsByStudent, $examMap, $class, $selectedExam) {
            $subjects = $subjectsByStudent->get((int) $student->id, collect());
            $subjectRows = [];
            $studentTotal = 0.0;
            $studentMax = 0.0;
            $hasFailingSubject = false;

            foreach ($subjects as $subject) {
                $term1 = $this->termValues(
                    $this->findMark($marks, $student->id, $subject->id, $examMap['term_1']?->id ?? null),
                    $this->findMark($marks, $student->id, $subject->id, $examMap['pt_1']?->id ?? null),
                    $examMap['term_1'] ?? null,
                    $examMap['pt_1'] ?? null,
                    $class->class_name ?? null,
                    $subject->subject_type ?? null
                );
                $term2 = $this->termValues(
                    $this->findMark($marks, $student->id, $subject->id, $examMap['term_2']?->id ?? null),
                    $this->findMark($marks, $student->id, $subject->id, $examMap['pt_2']?->id ?? null),
                    $examMap['term_2'] ?? null,
                    $examMap['pt_2'] ?? null,
                    $class->class_name ?? null,
                    $subject->subject_type ?? null
                );

                $subjectFailed = $term1['result'] === 'Fail'
                    || $term2['result'] === 'Fail'
                    || $this->isBelowFailPercent($term1['total'] + $term2['total'], 200, $selectedExam);
                $hasFailingSubject = $hasFailingSubject || $subjectFailed;
                $studentTotal += $term1['total'] + $term2['total'];
                $studentMax += 200;

                $subjectRows[] = [
                    'subject' => $subject->subject_name,
                    'term_1' => $term1,
                    'term_2' => $term2,
                    'result' => $subjectFailed ? 'Fail' : 'Pass',
                ];
            }

            $percentage = $studentMax > 0 ? $this->roundPercentage(($studentTotal / $studentMax) * 100) : null;
            $failed = $percentage === null
                ? false
                : ($hasFailingSubject || $this->isBelowFailPercent($studentTotal, $studentMax, $selectedExam));

            return [
                'student' => $student,
                'attendance' => '',
                'subjects' => $subjectRows,
                'grand_total' => $studentTotal,
                'grade' => $percentage === null ? '' : $this->gradeFor($percentage, $class->class_name ?? null, null),
                'percentage' => $percentage,
                'division' => $this->divisionFor($percentage),
                'result' => $percentage === null ? '' : ($failed ? 'Fail' : 'Pass'),
            ];
        })->values()->all();

        return [
            'class' => $class,
            'session_year' => $this->resolveDynamicSessionYear($request),
            'exam_title' => $selectedExam?->exam_name ?: 'Consolidated Term - II (Annual) Examination',
            'class_teacher' => $this->classTeacherName($class),
            'students' => $studentRows,
        ];
    }

    private function classes()
    {
        return Classes::query()
            ->orderBy('class_name')
            ->orderBy('section_name')
            ->get();
    }

    private function classOptions()
    {
        return $this->classes()
            ->pluck('class_name')
            ->filter()
            ->unique()
            ->values();
    }

    private function sectionsForRequest(Request $request)
    {
        $className = $request->filled('class_name') ? (string) $request->input('class_name') : null;

        return $this->classes()
            ->when($className, fn ($items) => $items->where('class_name', $className))
            ->pluck('section_name')
            ->filter()
            ->unique()
            ->values();
    }

    private function sectionsByClass()
    {
        return $this->classes()
            ->groupBy('class_name')
            ->map(fn ($items) => $items
                ->pluck('section_name')
                ->filter()
                ->unique()
                ->values())
            ->toArray();
    }

    private function studentsForClass(int $classId)
    {
        if (!Schema::connection('dynamic')->hasTable('student_registration')) {
            return collect();
        }

        $class = Classes::query()->find($classId);

        return DB::connection('dynamic')
            ->table('student_registration')
            ->where(function ($query) use ($classId, $class) {
                $query->where('class_id', (string) $classId);
                if ($class && $class->class_name) {
                    $query->orWhere('class_name', $class->class_name);
                }
            })
            ->when($class && $class->section_name, function ($query) use ($class) {
                $query->where(function ($inner) use ($class) {
                    $inner->where('section_name', $class->section_name);

                    if (Schema::connection('dynamic')->hasColumn('student_registration', 'json_str')) {
                        $inner->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(json_str, '$.section_name')) = ?", [$class->section_name]);
                    }
                });
            })
            ->orderBy('student_name')
            ->get();
    }

    private function assignedSubjectsForStudents($students, $class, $marks)
    {
        $markedSubjectsByStudent = $marks
            ->groupBy(fn ($mark) => (int) $mark->student_id)
            ->map(fn ($items) => $items
                ->map(fn ($mark) => (object) [
                    'id' => (int) $mark->subject_id,
                    'subject_name' => $mark->subject_name ?: 'Subject',
                    'subject_type' => $mark->subject_type,
                ])
                ->unique('id')
                ->values());

        $assignments = $this->subjectAssignmentsForClass($class);
        $subjectsByCombination = $this->subjectsByCombination($assignments->pluck('assign_this_combtoall')->filter()->unique()->all());

        return $students->mapWithKeys(function ($student) use ($assignments, $subjectsByCombination, $markedSubjectsByStudent) {
            $studentAssignments = $assignments->filter(function ($assignment) use ($student) {
                return $this->assignmentAppliesToStudent($assignment->students_details ?? null, (int) $student->id);
            });

            $assignedSubjects = $studentAssignments
                ->flatMap(fn ($assignment) => $subjectsByCombination->get((int) $assignment->assign_this_combtoall, collect()))
                ->unique('id')
                ->values();

            $subjects = $assignedSubjects->isNotEmpty()
                ? $assignedSubjects
                : $markedSubjectsByStudent->get((int) $student->id, collect());

            $subjects = $subjects->unique('id')->sortBy('subject_name')->values();

            return [(int) $student->id => $subjects];
        });
    }

    private function assignmentAppliesToStudent($studentsDetails, int $studentId): bool
    {
        if ($studentsDetails === null || $studentsDetails === '') {
            return true;
        }

        if (is_array($studentsDetails)) {
            return collect($studentsDetails)->contains(fn ($id) => (int) $id === $studentId);
        }

        $details = trim((string) $studentsDetails);
        $decoded = json_decode($details, true);
        if (is_array($decoded)) {
            return collect($decoded)->flatten()->contains(fn ($id) => (int) $id === $studentId);
        }

        return collect(preg_split('/\s*,\s*/', $details))
            ->filter()
            ->contains(fn ($id) => (int) $id === $studentId);
    }

    private function subjectAssignmentsForClass($class)
    {
        if (!Schema::connection('dynamic')->hasTable('subject_assign_student')) {
            return collect();
        }

        return SubjectAssignStudent::query()
            ->where('class_name', $class->class_name)
            ->when(Schema::connection('dynamic')->hasColumn('subject_assign_student', 'is_delete'), function ($query) {
                $query->where(function ($inner) {
                    $inner->where('is_delete', 0)->orWhereNull('is_delete');
                });
            })
            ->where(function ($query) use ($class) {
                $query->whereNull('section_name')->orWhere('section_name', '');
                if ($class->section_name) {
                    $query->orWhere('section_name', $class->section_name);
                }
            })
            ->get();
    }

    private function subjectsByCombination(array $combinationIds)
    {
        if (empty($combinationIds)
            || !Schema::connection('dynamic')->hasTable('combination_subject')
            || !Schema::connection('dynamic')->hasTable('subjectmaster')) {
            return collect();
        }

        return DB::connection('dynamic')
            ->table('combination_subject as cs')
            ->join('subjectmaster as s', 's.id', '=', 'cs.subject_id')
            ->whereIn('cs.subject_combination_id', $combinationIds)
            ->when(Schema::connection('dynamic')->hasColumn('subjectmaster', 'is_delete'), function ($query) {
                $query->where(function ($inner) {
                    $inner->where('s.is_delete', 0)->orWhereNull('s.is_delete');
                });
            })
            ->orderBy('cs.subject_order')
            ->orderBy('s.subject_name')
            ->select('cs.subject_combination_id', 's.id', 's.subject_name', 's.subject_type')
            ->get()
            ->groupBy(fn ($row) => (int) $row->subject_combination_id)
            ->map(fn ($items) => $items
                ->map(fn ($row) => (object) [
                    'id' => (int) $row->id,
                    'subject_name' => $row->subject_name ?: 'Subject',
                    'subject_type' => $row->subject_type,
                ])
                ->unique('id')
                ->values());
    }

    private function classSubjects($class)
    {
        if (!Schema::connection('dynamic')->hasTable('academic_class_subject')
            || !Schema::connection('dynamic')->hasTable('subjectmaster')) {
            return collect();
        }

        $classIds = $this->marksClassIdsFor((int) $class->id);

        return DB::connection('dynamic')
            ->table('academic_class_subject as acs')
            ->join('subjectmaster as s', 's.id', '=', 'acs.subject_id')
            ->whereIn('acs.class_id', $classIds)
            ->when(Schema::connection('dynamic')->hasColumn('subjectmaster', 'is_delete'), function ($query) {
                $query->where(function ($inner) {
                    $inner->where('s.is_delete', 0)->orWhereNull('s.is_delete');
                });
            })
            ->orderBy('s.subject_name')
            ->select('s.id', 's.subject_name', 's.subject_type')
            ->get()
            ->map(fn ($row) => (object) [
                'id' => (int) $row->id,
                'subject_name' => $row->subject_name ?: 'Subject',
                'subject_type' => $row->subject_type,
            ])
            ->unique('id')
            ->values();
    }

    private function classTeacherName($class): string
    {
        if (Schema::connection('dynamic')->hasTable('teacher_subjects')) {
            $query = DB::connection('dynamic')
                ->table('teacher_subjects as ts')
                ->whereIn('ts.class_id', $this->marksClassIdsFor((int) $class->id))
                ->where('ts.section_name', $class->section_name)
                ->where('ts.role', 'Class Teacher')
                ->when(Schema::connection('dynamic')->hasColumn('teacher_subjects', 'is_delete'), function ($query) {
                    $query->where(function ($inner) {
                        $inner->where('ts.is_delete', 0)->orWhereNull('ts.is_delete');
                    });
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

        if (!Schema::connection('dynamic')->hasTable('classasigntoteacher')) {
            return '';
        }

        $assigned = DB::connection('dynamic')
            ->table('classasigntoteacher')
            ->where('Class', $class->class_name)
            ->where('Section', $class->section_name)
            ->when(Schema::connection('dynamic')->hasColumn('classasigntoteacher', 'is_delete'), function ($query) {
                $query->where(function ($inner) {
                    $inner->where('is_delete', 0)->orWhereNull('is_delete');
                });
            })
            ->first();

        return $assigned ? trim($assigned->teacher_name ?? $assigned->teacher_namee ?? $assigned->Teacher_1 ?? '') : '';
    }

    private function examMapForClass(int $classId, ?Exam $selectedExam, Request $request): array
    {
        $classIds = $this->marksClassIdsFor($classId);
        $query = Exam::query()->whereIn('class_id', $classIds);

        if (Schema::connection('dynamic')->hasColumn('academic_exam', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $exams = $query->get();
        if ($selectedExam && in_array((int) $selectedExam->class_id, $classIds, true)) {
            $exams->push($selectedExam);
        }

        return [
            'pt_1' => $this->examByAliases($exams, ['pt1', 'pti', 'periodictest1', 'periodictesti']),
            'term_1' => $this->examByAliases($exams, ['term1', 'termi', 'halfyearly', 'halfyearlyexam', 'halfyearlyexamination']),
            'pt_2' => $this->examByAliases($exams, ['pt2', 'ptii', 'periodictest2', 'periodictestii']),
            'term_2' => $this->examByAliases($exams, ['term2', 'termii', 'annual', 'annualexam', 'annualexamination']),
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

    private function marksForStudents(array $studentIds, int $classId, array $examIds)
    {
        if (empty($studentIds)) {
            return collect();
        }

        if (!Schema::connection('dynamic')->hasTable('academic_students_marks') || !Schema::connection('dynamic')->hasTable('previosly_saved_marks_entry')) {
            return collect();
        }

        $query = DB::connection('dynamic')
            ->table('academic_students_marks as sm')
            ->join('previosly_saved_marks_entry as me', 'me.id', '=', 'sm.marks_id')
            ->leftJoin('subjectmaster as s', 's.id', '=', 'me.subject_id')
            ->whereIn('sm.student_id', array_map('strval', $studentIds))
            ->whereIn('me.class_id', array_map('strval', $this->marksClassIdsFor($classId)))
            ->when(!empty($examIds), fn ($inner) => $inner->whereIn('me.exam_id', array_map('strval', $examIds)));

        if (Schema::connection('dynamic')->hasColumn('academic_students_marks', 'is_delete')) {
            $query->where(function ($inner) {
                $inner->where('sm.is_delete', 0)->orWhereNull('sm.is_delete');
            });
        }

        if (Schema::connection('dynamic')->hasColumn('previosly_saved_marks_entry', 'is_delete')) {
            $query->where(function ($inner) {
                $inner->where('me.is_delete', 0)->orWhereNull('me.is_delete');
            });
        }

        return $query
            ->select('sm.*', 'me.exam_id', 'me.subject_id', 'me.class_id', 's.subject_name', 's.subject_type')
            ->get();
    }

    private function findMark($marks, int $studentId, int $subjectId, ?int $examId)
    {
        if (!$examId) {
            return null;
        }

        return $marks->first(fn ($mark) => (int) $mark->student_id === $studentId
            && (int) $mark->subject_id === $subjectId
            && (int) $mark->exam_id === $examId);
    }

    private function termValues($termMark, $ptMark, $termExam, $ptExam, ?string $className = null, ?string $subjectType = null): array
    {
        $theory = $this->numericMark($this->markValue($termMark, 'mark_theory'));
        $pt = $this->convertedPtMark($ptMark, $ptExam);
        $mas = $this->internalAssessmentValue($termMark, ['MA/MAS', 'MAS', 'MA', 'Multiple Assessment']);
        $pf = $this->internalAssessmentValue($termMark, ['PF', 'Portfolio', 'NB', 'Notebook']);
        $sea = $this->internalAssessmentValue($termMark, ['SE', 'SEA', 'Subject Enrichment']);
        $total = min(100, $pt + $mas + $pf + $sea + $theory);

        $roundedTotal = $this->roundMark($total);

        return [
            'pt' => $this->roundMark($pt),
            'mas' => $this->roundMark($mas),
            'pf' => $this->roundMark($pf),
            'sea' => $this->roundMark($sea),
            'theory' => $this->roundMark($theory),
            'total' => $roundedTotal,
            'grade' => $this->gradeFor($roundedTotal, $className, $subjectType),
            'result' => $this->isBelowFailPercent($total, 100, $termExam) ? 'Fail' : 'Pass',
        ];
    }

    private function markValue($mark, string $field): string
    {
        if (!$mark) {
            return '';
        }

        $aliases = [
            'total_marks' => ['total_marks', 'subject_marks'],
            'mark_theory' => ['mark_theory', 'subject_marks'],
            'mark_practical' => ['mark_practical'],
            'grade' => ['grade'],
        ];

        foreach ($aliases[$field] ?? [$field] as $column) {
            if (isset($mark->{$column}) && $mark->{$column} !== '') {
                return (string) $mark->{$column};
            }
        }

        return '';
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
            $max = max(1, $obtained);
        }

        return $this->roundMark(min(5, ($obtained / $max) * 5));
    }

    private function internalAssessmentValue($mark, array $keys): float
    {
        if (!$mark || empty($mark->internal_assessment_marks)) {
            return 0.0;
        }

        $decoded = json_decode((string) $mark->internal_assessment_marks, true);
        if (!is_array($decoded)) {
            return 0.0;
        }

        $normalizedKeys = collect($keys)
            ->map(fn ($key) => Str::lower(str_replace([' ', '-', '_', '/'], '', (string) $key)))
            ->all();

        foreach ($decoded as $key => $value) {
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

    private function roundPercentage(float $value): float
    {
        return round($value, 2, PHP_ROUND_HALF_UP);
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

    private function formatMark($value): string
    {
        return number_format($this->roundMark((float) $value), 2, '.', '');
    }

    private function gradeFor(float $marks, ?string $className = null, ?string $subjectType = null): string
    {
        $dynamicGrade = $this->gradeFromMaster($marks, $className, $subjectType);
        if ($dynamicGrade !== '') {
            return $dynamicGrade;
        }

        $scale = [
            ['grade' => 'A1', 'min' => 91],
            ['grade' => 'A2', 'min' => 81],
            ['grade' => 'B1', 'min' => 71],
            ['grade' => 'B2', 'min' => 61],
            ['grade' => 'C1', 'min' => 51],
            ['grade' => 'C2', 'min' => 41],
            ['grade' => 'D', 'min' => 33],
            ['grade' => 'E', 'min' => 0],
        ];

        foreach ($scale as $range) {
            if ($marks >= $range['min']) {
                return $range['grade'];
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

    private function divisionFor(?float $percentage): string
    {
        if ($percentage === null) {
            return '';
        }

        if ($percentage >= 60) {
            return 'I';
        }

        if ($percentage >= 45) {
            return 'II';
        }

        if ($percentage >= 33) {
            return 'III';
        }

        return '';
    }

    private function marksClassIdsFor(int $classId): array
    {
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

    private function normalizeExamType(?string $type): string
    {
        return Str::lower(preg_replace('/[^a-z0-9]+/i', '', (string) $type));
    }

    private function resolveDynamicSessionYear(Request $request): ?string
    {
        $year = $request->session()->get('selectedYear') ?: $request->cookie('selectedYear');
        $year = $year ?: Config::get('database.connections.dynamic.database');
        return is_string($year) && preg_match('/^\d{4}_\d{4}$/', $year) ? $year : null;
    }

    private function dummyReports(Request $request)
    {
        $class = (object) [
            'class_name' => $request->input('class_name') ?: 'VIII',
            'section_name' => $request->input('section_name') ?: 'Kautilya',
        ];

        $subjects = ['English', 'Hindi', 'Sanskrit', 'Mathematics', 'Science', 'Social Science'];
        $students = [
            ['name' => 'Arnav Gupta', 'scholar_no' => '5969', 'attendance' => '173/209', 'base' => 0],
            ['name' => 'Meenal Sharma', 'scholar_no' => '6021', 'attendance' => '181/209', 'base' => 4],
            ['name' => 'Raghav Verma', 'scholar_no' => '6114', 'attendance' => '168/209', 'base' => -3],
        ];

        $studentRows = collect($students)->map(function ($student) use ($subjects) {
            $subjectRows = [];
            $grandTotal = 0.0;

            foreach ($subjects as $index => $subject) {
                $term1Theory = max(35, 55 + $student['base'] - ($index * 2));
                $term2Theory = max(35, 52 + $student['base'] - $index);
                $term1 = [
                    'pt' => 3.00,
                    'mas' => 4.00,
                    'pf' => 4.00,
                    'sea' => 4.00,
                    'theory' => $term1Theory,
                    'total' => $term1Theory + 15,
                    'grade' => '',
                ];
                $term2 = [
                    'pt' => 4.00,
                    'mas' => 4.00,
                    'pf' => 4.00,
                    'sea' => 4.00,
                    'theory' => $term2Theory,
                    'total' => $term2Theory + 16,
                    'grade' => '',
                ];

                $grandTotal += $term1['total'] + $term2['total'];
                $subjectRows[] = [
                    'subject' => $subject,
                    'term_1' => $term1,
                    'term_2' => $term2,
                ];
            }

            $percentage = round(($grandTotal / (count($subjects) * 200)) * 100, 2);

            return [
                'student' => (object) [
                    'student_name' => $student['name'],
                    'scholar_no' => $student['scholar_no'],
                ],
                'attendance' => $student['attendance'],
                'subjects' => $subjectRows,
                'grand_total' => $grandTotal,
                'grade' => $this->gradeFor($percentage),
                'percentage' => $percentage,
                'division' => $this->divisionFor($percentage),
                'result' => $percentage >= 33 ? 'Pass' : 'Fail',
            ];
        })->values()->all();

        return collect([[
            'class' => $class,
            'session_year' => $this->resolveDynamicSessionYear($request) ?: '2025_2026',
            'exam_title' => $request->integer('exam_id')
                ? (Exam::query()->find($request->integer('exam_id'))?->exam_name ?: 'Term - II (Annual) Examination')
                : 'Term - II (Annual) Examination',
            'class_teacher' => 'Meenal Gupta',
            'students' => $studentRows,
        ]]);
    }
}
