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
        $rawExams = Exam::with('classInfo')->latest('id')->get();
        $groupedExams = $this->groupedExams($rawExams);
        $exams = $rawExams;
        $reports = $request->hasAny(['class_name', 'section_name', 'class_id', 'exam_id'])
            ? $this->buildReports($request)
            : collect();

        $missingMarksClasses = $reports->filter(fn ($r) => empty($r['has_marks']))->map(function ($r) {
            return $r['class']->class_name . ($r['class']->section_name ? ' - ' . $r['class']->section_name : '');
        })->unique()->values()->all();

        return view('backend.AcademicsModules.consolidated_marksheet', compact('classes', 'sections', 'sectionsByClass', 'exams', 'groupedExams', 'reports', 'missingMarksClasses'));
    }

    public function print(Request $request)
    {
        $reports = $this->buildReports($request);

        $selectedExam = $request->integer('exam_id') ? Exam::query()->find($request->integer('exam_id')) : null;

        return view('backend.AcademicsModules.consolidated_marksheet_print', compact('reports', 'selectedExam'));
    }

    public function buildReports(Request $request)
    {
        $className = $request->filled('classname') ? (string) $request->input('classname') : ($request->filled('class_name') ? (string) $request->input('class_name') : null);
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
        $classResolvedExam = $selectedExam
            ? $this->resolveExamForClass((int) $selectedExam->id, (int) $class->id, $class->class_name)
            : null;
        $activeExam = $classResolvedExam ?: $selectedExam;
        $students = $this->studentsForClass((int) $class->id);
        $examMap = $this->examMapForClass((int) $class->id, $classResolvedExam, $request);
        $examIds = collect($examMap)->filter()->pluck('id')->map(fn ($id) => (int) $id)->unique()->values()->all();
        $marks = $this->marksForStudents($students->pluck('id')->all(), (int) $class->id, $examIds);
        $subjectsByStudent = $this->assignedSubjectsForStudents($students, $class, $marks);

        $isTerm1 = $this->isTerm1Report($activeExam, $examMap, $marks);
        $showTerm2 = !$isTerm1;

        $studentRows = $students->map(function ($student) use ($marks, $subjectsByStudent, $examMap, $class, $classResolvedExam, $showTerm2) {
            $subjects = $subjectsByStudent->get((int) $student->id, collect());
            $subjectRows = [];
            $term1Sum = 0.0;
            $term2Sum = 0.0;
            $term1TheorySum = 0.0;
            $term2TheorySum = 0.0;
            $hasTerm1Marks = false;
            $hasTerm2Marks = false;
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
                $term2 = $showTerm2
                    ? $this->termValues(
                        $this->findMark($marks, $student->id, $subject->id, $examMap['term_2']?->id ?? null),
                        $this->findMark($marks, $student->id, $subject->id, $examMap['pt_2']?->id ?? null),
                        $examMap['term_2'] ?? null,
                        $examMap['pt_2'] ?? null,
                        $class->class_name ?? null,
                        $subject->subject_type ?? null
                    )
                    : [
                        'pt' => '',
                        'mas' => '',
                        'pf' => '',
                        'sea' => '',
                        'theory' => '',
                        'total' => '',
                        'grade' => '',
                        'result' => '',
                    ];

                $t1Total = is_numeric($term1['total']) ? (float) $term1['total'] : 0.0;
                $t2Total = is_numeric($term2['total']) ? (float) $term2['total'] : 0.0;
                $t1Theory = is_numeric($term1['theory']) ? (float) $term1['theory'] : 0.0;
                $t2Theory = is_numeric($term2['theory']) ? (float) $term2['theory'] : 0.0;

                if ($t1Total > 0 || $t1Theory > 0 || (is_numeric($term1['pt']) && (float) $term1['pt'] > 0)) {
                    $hasTerm1Marks = true;
                }
                if ($showTerm2 && ($t2Total > 0 || $t2Theory > 0 || (is_numeric($term2['pt']) && (float) $term2['pt'] > 0))) {
                    $hasTerm2Marks = true;
                }

                $term1Sum += $t1Total;
                $term2Sum += $t2Total;
                $term1TheorySum += $t1Theory;
                $term2TheorySum += $t2Theory;

                $t1Failed = ($term1['result'] === 'Fail');
                $t2Failed = ($term2['result'] === 'Fail');
                $subjectFailed = ($showTerm2 && $hasTerm2Marks) ? ($t1Failed || $t2Failed) : $t1Failed;
                $hasFailingSubject = $hasFailingSubject || $subjectFailed;

                // Subject percentage, division and short result for display (Annual / active term)
                $subPercentage = ($showTerm2 && $t2Total > 0) ? $t2Total : ($t1Total > 0 ? $t1Total : null);
                $subDivision = $subPercentage !== null ? $this->divisionFor($subPercentage) : '';
                $subResultShort = $subjectFailed ? 'F' : (($t1Total > 0 || ($showTerm2 && $t2Total > 0)) ? 'P' : '');

                $subjectRows[] = [
                    'subject' => $subject->subject_name,
                    'term_1' => $term1,
                    'term_2' => $term2,
                    'percentage' => $subPercentage,
                    'division' => $subDivision,
                    'result' => $subjectFailed ? 'Fail' : ($subPercentage !== null ? 'Pass' : ''),
                    'result_short' => $subResultShort,
                ];
            }

            $subjectCount = count($subjectRows);
            $activeTotal = ($showTerm2 && $hasTerm2Marks) ? $term2Sum : $term1Sum;
            $activeMax = $subjectCount * 100;
            $percentage = $activeMax > 0 ? $this->roundPercentage(($activeTotal / $activeMax) * 100) : null;
            $failed = $percentage === null
                ? false
                : ($hasFailingSubject || $this->isBelowFailPercent($activeTotal, $activeMax, $classResolvedExam));

            return [
                'student' => $student,
                'attendance' => '',
                'subjects' => $subjectRows,
                'grand_total' => $activeTotal,
                'term_1_total' => $term1Sum,
                'term_2_total' => $term2Sum,
                'term_1_theory_total' => $term1TheorySum,
                'term_2_theory_total' => $term2TheorySum,
                'grade' => $percentage === null ? '' : $this->gradeFor($percentage, $class->class_name ?? null, null),
                'percentage' => $percentage,
                'division' => $this->divisionFor($percentage),
                'result' => $percentage === null ? '' : ($failed ? 'Fail' : 'Pass'),
                'result_short' => $percentage === null ? '' : ($failed ? 'F' : 'P'),
            ];
        })->values()->all();

        $defaultExamTitle = $isTerm1 ? 'Consolidated Term - I Examination' : 'Consolidated Term - II (Annual) Examination';
        $examTitle = $classResolvedExam?->exam_name ?: ($selectedExam?->exam_name ?: $defaultExamTitle);

        return [
            'class' => $class,
            'session_year' => $this->resolveDynamicSessionYear($request),
            'exam_title' => $examTitle,
            'class_teacher' => $this->classTeacherName($class),
            'students' => $studentRows,
            'has_marks' => $marks->isNotEmpty(),
            'marks_count' => $marks->count(),
            'is_term_1' => $isTerm1,
            'show_term_2' => $showTerm2,
        ];
    }

    private function isTerm1Report(?Exam $activeExam, array $examMap, $marks): bool
    {
        if ($activeExam) {
            $normName = $this->normalizeExamType($activeExam->exam_name ?? '');
            $normType = $this->normalizeExamType($activeExam->exam_type ?? '');

            if (str_contains($normName, 'term2') || str_contains($normName, 'termii') || str_contains($normName, 'annual') || str_contains($normName, 'final')
                || str_contains($normType, 'term2') || str_contains($normType, 'termii') || str_contains($normType, 'annual') || str_contains($normType, 'final')) {
                return false;
            }

            if (str_contains($normName, 'term1') || str_contains($normName, 'termi') || str_contains($normName, 'halfyearly') || str_contains($normName, 'pt1') || str_contains($normName, 'pti')
                || str_contains($normType, 'term1') || str_contains($normType, 'termi') || str_contains($normType, 'halfyearly') || str_contains($normType, 'pt1') || str_contains($normType, 'pti')) {
                return true;
            }
        }

        $t2ExamId = $examMap['term_2']?->id ?? null;
        $pt2ExamId = $examMap['pt_2']?->id ?? null;
        $t2ExamIds = array_filter([(int) $t2ExamId, (int) $pt2ExamId]);

        if (!empty($t2ExamIds)) {
            $hasT2Marks = $marks->contains(fn ($m) => in_array((int) $m->exam_id, $t2ExamIds, true));
            if ($hasT2Marks) {
                return false;
            }
        }

        return true;
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
            ->filter(fn ($mark) => strtolower(trim($mark->subject_type ?? '')) === 'academic' && !in_array(strtolower(trim($mark->subject_name ?? '')), ['test', 'te4st', 'dummy']))
            ->groupBy(fn ($mark) => (int) $mark->student_id)
            ->map(fn ($items) => $items
                ->map(fn ($mark) => (object) [
                    'id' => (int) $mark->subject_id,
                    'subject_name' => $mark->subject_name ?: 'Subject',
                    'subject_type' => $mark->subject_type,
                ])
                ->unique('id')
                ->values());

        $classMarkedSubjectIds = $marks
            ->filter(fn ($mark) => strtolower(trim($mark->subject_type ?? '')) === 'academic' && !in_array(strtolower(trim($mark->subject_name ?? '')), ['test', 'te4st', 'dummy']))
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $assignments = $this->subjectAssignmentsForClass($class);
        $subjectsByCombination = $this->subjectsByCombination($assignments->pluck('assign_this_combtoall')->filter()->unique()->all());

        $classAcademicSubjects = $this->classSubjects($class)
            ->filter(fn ($s) => strtolower(trim($s->subject_type ?? '')) === 'academic' && !in_array(strtolower(trim($s->subject_name ?? '')), ['test', 'te4st', 'dummy']));

        return $students->mapWithKeys(function ($student) use (
            $assignments,
            $subjectsByCombination,
            $markedSubjectsByStudent,
            $classMarkedSubjectIds,
            $classAcademicSubjects
        ) {
            $studentId = (int) $student->id;
            $studentMarks = $markedSubjectsByStudent->get($studentId, collect());

            $studentAssignments = $assignments->filter(function ($assignment) use ($student) {
                return $this->assignmentAppliesToStudent($assignment->students_details ?? null, (int) $student->id);
            });

            $assignedCombSubjects = $studentAssignments
                ->flatMap(fn ($assignment) => $subjectsByCombination->get((int) $assignment->assign_this_combtoall, collect()))
                ->filter(fn ($s) => strtolower(trim($s->subject_type ?? '')) === 'academic' && !in_array(strtolower(trim($s->subject_name ?? '')), ['test', 'te4st', 'dummy']))
                ->unique('id')
                ->values();

            if ($studentMarks->isNotEmpty()) {
                // Student has academic marks entered
                $subjects = $studentMarks;

                // If combination has additional active subjects with marks in the class, include them
                if ($assignedCombSubjects->isNotEmpty()) {
                    $activeCombSubjects = $assignedCombSubjects->filter(fn ($s) => in_array((int) $s->id, $classMarkedSubjectIds, true));
                    $subjects = $subjects->concat($activeCombSubjects);
                }
            } elseif ($assignedCombSubjects->isNotEmpty()) {
                // If student has no marks yet, prioritize combination subjects active in class marks, or use combination
                $activeComb = $assignedCombSubjects->filter(fn ($s) => in_array((int) $s->id, $classMarkedSubjectIds, true));
                $subjects = $activeComb->isNotEmpty() ? $activeComb : $assignedCombSubjects;
            } elseif (!empty($classMarkedSubjectIds)) {
                // No combination, but class has marks
                $subjects = $classAcademicSubjects->filter(fn ($s) => in_array((int) $s->id, $classMarkedSubjectIds, true));
                if ($subjects->isEmpty()) {
                    $subjects = $classAcademicSubjects;
                }
            } else {
                // Fallback to class academic subjects
                $subjects = $classAcademicSubjects;
            }

            $subjects = $subjects->unique('id')->sortBy('subject_name')->values();

            return [$studentId => $subjects];
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
            ->when(Schema::connection('dynamic')->hasColumn('subjectmaster', 'subject_type'), function ($query) {
                $query->whereRaw('LOWER(TRIM(s.subject_type)) = ?', ['academic']);
            })
            ->when(Schema::connection('dynamic')->hasColumn('subjectmaster', 'subject_name'), function ($query) {
                $query->whereNotIn(DB::raw('LOWER(TRIM(s.subject_name))'), ['test', 'te4st', 'dummy']);
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
            ->when(Schema::connection('dynamic')->hasColumn('subjectmaster', 'subject_type'), function ($query) {
                $query->whereRaw('LOWER(TRIM(s.subject_type)) = ?', ['academic']);
            })
            ->when(Schema::connection('dynamic')->hasColumn('subjectmaster', 'subject_name'), function ($query) {
                $query->whereNotIn(DB::raw('LOWER(TRIM(s.subject_name))'), ['test', 'te4st', 'dummy']);
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

        if (Schema::connection('dynamic')->hasColumn('subjectmaster', 'subject_type')) {
            $query->whereRaw('LOWER(TRIM(s.subject_type)) = ?', ['academic']);
        }

        if (Schema::connection('dynamic')->hasColumn('subjectmaster', 'subject_name')) {
            $query->whereNotIn(DB::raw('LOWER(TRIM(s.subject_name))'), ['test', 'te4st', 'dummy']);
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
        $hasAnyMark = ($termMark !== null) || ($ptMark !== null);

        if (!$hasAnyMark) {
            return [
                'pt' => '',
                'mas' => '',
                'pf' => '',
                'sea' => '',
                'theory' => '',
                'total' => '',
                'grade' => '',
                'result' => '',
            ];
        }

        $theory = $this->numericMark($this->markValue($termMark, 'mark_theory'));
        $pt = $this->convertedPtMark($ptMark, $ptExam, $termMark);
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

        if (($raw === '' || $raw === null) && $termMark) {
            $internal = $this->internalAssessmentMarks($termMark);
            foreach ($internal as $key => $value) {
                $norm = Str::lower(str_replace([' ', '-', '_', '/'], '', (string) $key));
                if (in_array($norm, ['pt', 'periodictest', 'pt1', 'pt2'], true) && $value !== null && $value !== '') {
                    $raw = $value;
                    break;
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

        // Converted Marks = (Obtained PT Marks ÷ 40) × 5, rounded off to nearest whole number
        $converted = ($obtained / 40.0) * 5.0;

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
                return $this->roundMark(min(5, $this->numericMark($value)));
            }
        }

        return 0.0;
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

    private function numericMark($value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function roundMark(float $value): float
    {
        return round($value, 2, PHP_ROUND_HALF_UP);
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
        $val = (float) $value;
        if (abs($val - round($val)) < 0.005) {
            return (string) (int) round($val);
        }

        return number_format($val, 2, '.', '');
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
        $year = ($request->hasSession() ? $request->session()->get('selectedYear') : null) ?: $request->cookie('selectedYear');
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

    private function groupedExams($rawExams)
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

        return $exam;
    }
}
