<?php
namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Academic\Services\ExamService;
use App\Models\Academic\Exam;
use App\Models\ExamType;
use App\Models\Classes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    protected $examService;

    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }

    public function store(Request $request)
    {
        $sessionYear = $this->resolveDynamicSessionYear($request);
        if (empty($sessionYear)) {
            return redirect()->back()->withInput()->with('error', 'No academic session is selected. Choose a session year from the header, then try again.');
        }

        $examTypes = $this->activeExamTypes();
        if ($examTypes->isEmpty()) {
            return redirect()->back()->withInput()->with('error', 'Please create at least one exam type first.');
        }

        $validated = $request->validate([
            'exam_name' => 'required|string|max:100',
            'exam_type' => ['required', 'string', Rule::in($examTypes->all())],
            'max_marks_theory' => 'nullable|integer',
            'max_marks_practical' => 'nullable|integer',
            'fail_percent' => 'nullable|numeric',
            'is_ser' => 'nullable|boolean',
            'class_ids' => 'required|array',
            'class_ids.*' => 'required|integer|exists:classes,id',
        ]);

        DB::transaction(function () use ($validated, $request, $sessionYear) {
            $this->createExamRows(
                $validated,
                $sessionYear,
                $request->has('is_ser') ? 1 : 0,
                auth()->id()
            );
        });

        return redirect()->back()->with('success', 'Exam created successfully!');
    }

    public function create(Request $request)
    {
        $examTypes = $this->activeExamTypes();
        $currentSessionYear = $this->resolveDynamicSessionYear($request);
        // Get unique classes by class_name, optionally filter by session_year if available in your classes table
        $classes = \App\Models\Classes::query()
            ->when(
                \Schema::hasColumn('classes', 'session_year') && $currentSessionYear,
                function ($query) use ($currentSessionYear) {
                    $query->where('session_year', $currentSessionYear);
                }
            )
            ->get()
            ->unique('class_name')
            ->values();
        $savedExamsQuery = \App\Models\Academic\Exam::with('classInfo');
        if (Schema::hasColumn('academic_exam', 'deleted_at')) {
            $savedExamsQuery->whereNull('deleted_at');
        }
        $savedExams = $savedExamsQuery
            ->latest('id')
            ->get();
        $savedExamGroups = $savedExams
            ->groupBy(function ($exam) {
                return $exam->exam_group_id ?: implode('|', [
                    $exam->exam_name,
                    $exam->exam_type,
                    $exam->session_year,
                    $exam->max_marks_theory,
                    $exam->max_marks_practical,
                    $exam->fail_percent,
                    $exam->is_ser,
                    $exam->created_by,
                ]);
            })
            ->map(function ($group) {
                $first = $group->first();
                $classNames = $group
                    ->pluck('classInfo.class_name')
                    ->filter()
                    ->unique()
                    ->implode(', ');

                return (object) [
                    'id' => $first->id,
                    'exam_group_id' => $first->exam_group_id,
                    'exam_name' => $first->exam_name,
                    'exam_type' => $first->exam_type,
                    'session_year' => $first->session_year,
                    'class_names' => $classNames !== '' ? $classNames : '-',
                ];
            })
            ->values();

        $sourceSession = $request->query('source_session');
        $sourceExamGroups = collect();
        if (is_string($sourceSession) && preg_match('/^\d{4}_\d{4}$/', $sourceSession)) {
            try {
                $sourceExams = DB::table("{$sourceSession}.academic_exam")->get();
                $sourceExamGroups = $sourceExams
                    ->groupBy(function ($exam) {
                        return implode('|', [
                            $exam->exam_name ?? '',
                            $exam->exam_type ?? '',
                            $exam->session_year ?? '',
                            $exam->max_marks_theory ?? '',
                            $exam->max_marks_practical ?? '',
                            $exam->fail_percent ?? '',
                            $exam->is_ser ?? '',
                        ]);
                    })
                    ->map(function ($group) use ($sourceSession) {
                        $first = $group->first();
                        $sourceClassIds = $group->pluck('class_id')->filter()->unique()->values()->all();
                        $sourceClassNames = collect();
                        if (count($sourceClassIds) > 0) {
                            $sourceClassNames = DB::table("{$sourceSession}.classes")
                                ->whereIn('id', $sourceClassIds)
                                ->pluck('class_name');
                        }

                        return (object) [
                            'seed_id' => $first->id,
                            'exam_name' => $first->exam_name,
                            'exam_type' => $first->exam_type,
                            'session_year' => $first->session_year,
                            'class_names' => $sourceClassNames->filter()->unique()->implode(', '),
                        ];
                    })
                    ->values();
            } catch (\Throwable $e) {
                $sourceExamGroups = collect();
            }
        }

        return view(
            'backend.AcademicsModules.exam_create',
            compact('examTypes', 'classes', 'savedExamGroups', 'currentSessionYear', 'sourceExamGroups', 'sourceSession')
        );
    }

    public function edit(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
        $currentSessionYear = $this->resolveDynamicSessionYear($request) ?? $exam->session_year;
        $groupRows = $this->buildExamGroupQuery($exam)->with('classInfo')->get();
        $selectedClassIds = $groupRows->pluck('class_id')->filter()->unique()->values()->all();
        $classes = \App\Models\Classes::query()
            ->when(
                \Schema::hasColumn('classes', 'session_year') && $currentSessionYear,
                function ($query) use ($currentSessionYear) {
                    $query->where('session_year', $currentSessionYear);
                }
            )
            ->get()
            ->unique('class_name')
            ->values();
        $examTypes = $this->activeExamTypes();

        return view(
            'backend.AcademicsModules.exam_edit',
            compact('exam', 'examTypes', 'classes', 'selectedClassIds', 'currentSessionYear')
        );
    }

    public function update(Request $request, $id)
    {
        $seedExam = Exam::findOrFail($id);
        $sessionYear = $this->resolveDynamicSessionYear($request) ?? $seedExam->session_year;
        if (empty($sessionYear)) {
            return redirect()->back()->withInput()->with('error', 'No academic session is selected. Choose a session year from the header, then try again.');
        }

        $examTypes = $this->activeExamTypes();
        if ($examTypes->isEmpty()) {
            return redirect()->back()->withInput()->with('error', 'Please create at least one exam type first.');
        }

        $validated = $request->validate([
            'exam_name' => 'required|string|max:100',
            'exam_type' => ['required', 'string', Rule::in($examTypes->all())],
            'max_marks_theory' => 'nullable|integer',
            'max_marks_practical' => 'nullable|integer',
            'fail_percent' => 'nullable|numeric',
            'is_ser' => 'nullable|boolean',
            'class_ids' => 'required|array',
            'class_ids.*' => 'required|integer|exists:classes,id',
        ]);

        DB::transaction(function () use ($seedExam, $validated, $request, $sessionYear) {
            $this->buildExamGroupQuery($seedExam)->delete();
            $this->createExamRows(
                $validated,
                $sessionYear,
                $request->has('is_ser') ? 1 : 0,
                $seedExam->created_by,
                $seedExam->exam_group_id // keep same group id when updating
            );
        });

        return redirect()->route('academic.exams.create')->with('success', 'Exam updated successfully!');
    }

    public function destroy($id)
    {
        $exam = Exam::findOrFail($id);

        $groupIds = $this->buildExamGroupQuery($exam)->pluck('id')->all();
        if ($this->examGroupHasMarks($groupIds)) {
            return redirect()
                ->route('academic.exams.create')
                ->with('error', 'Cannot delete: marks already entered for this exam.');
        }

        $query = $this->buildExamGroupQuery($exam);
        if (Schema::hasColumn('academic_exam', 'deleted_at')) {
            $deletedCount = $query->update(['deleted_at' => now()]);
        } else {
            $deletedCount = $query->delete();
        }

        return redirect()
            ->route('academic.exams.create')
            ->with('success', $deletedCount > 0 ? 'Exam deleted successfully!' : 'No exam records deleted.');
    }

    public function duplicate(Request $request, $id)
    {
        $seedExam = Exam::findOrFail($id);
        $sessionYear = $this->resolveDynamicSessionYear($request) ?? $seedExam->session_year;
        if (empty($sessionYear)) {
            return redirect()->back()->with('error', 'No academic session is selected. Choose a session year from the header, then try again.');
        }

        $groupRows = $this->buildExamGroupQuery($seedExam)->get();
        $classIds = $groupRows->pluck('class_id')->filter()->unique()->values()->all();
        if (count($classIds) === 0) {
            return redirect()->back()->with('error', 'No classes found for this exam to duplicate.');
        }

        $payload = [
            'exam_name' => $seedExam->exam_name,
            'exam_type' => $seedExam->exam_type,
            'max_marks_theory' => $seedExam->max_marks_theory,
            'max_marks_practical' => $seedExam->max_marks_practical,
            'fail_percent' => $seedExam->fail_percent,
            'class_ids' => $classIds,
        ];

        $newId = null;
        DB::transaction(function () use ($payload, $sessionYear, $seedExam, &$newId) {
            $newId = $this->createExamRows($payload, $sessionYear, (int) $seedExam->is_ser, auth()->id());
        });

        return redirect()
            ->route('academic.exams.edit', $newId)
            ->with('success', 'Exam duplicated. Now you can edit it.');
    }

    public function copyFromSession(Request $request)
    {
        $targetSession = $this->resolveDynamicSessionYear($request);
        if (empty($targetSession)) {
            return redirect()->back()->with('error', 'No target session is selected. Choose a session year from the header, then try again.');
        }

        $validated = $request->validate([
            'source_session' => 'required|string',
            'source_exam_ids' => 'nullable|array',
            'source_exam_ids.*' => 'integer',
        ]);
        $sourceSession = $validated['source_session'];
        if (!preg_match('/^\d{4}_\d{4}$/', $sourceSession)) {
            return redirect()->back()->with('error', 'Invalid source session selected.');
        }
        if ($sourceSession === $targetSession) {
            return redirect()->back()->with('error', 'Source session and target session cannot be same.');
        }

        // Map classes by class_name: source class_id -> target class_id
        $sourceClasses = DB::table("{$sourceSession}.classes")->select('id', 'class_name')->get();
        $targetClasses = Classes::query()->select('id', 'class_name')->get();
        $targetByName = $targetClasses
            ->groupBy('class_name')
            ->map(fn($rows) => optional($rows->first())->id)
            ->all();
        $sourceIdToTargetId = [];
        foreach ($sourceClasses as $sc) {
            $name = $sc->class_name;
            if (is_string($name) && isset($targetByName[$name])) {
                $sourceIdToTargetId[(int) $sc->id] = (int) $targetByName[$name];
            }
        }

        $sourceExamIds = $validated['source_exam_ids'] ?? null;
        $sourceExamsQuery = DB::table("{$sourceSession}.academic_exam");
        if (is_array($sourceExamIds) && count($sourceExamIds) > 0) {
            $sourceExamsQuery->whereIn('id', $sourceExamIds);
        }
        $sourceExams = $sourceExamsQuery->get();
        if ($sourceExams->count() === 0) {
            return redirect()->back()->with('error', "No exams found for selected items in source session {$sourceSession}.");
        }

        $copiedGroups = 0;
        $skippedRows = 0;
        $lastNewId = null;

        DB::transaction(function () use (
            $sourceExams,
            $sourceIdToTargetId,
            $targetSession,
            &$copiedGroups,
            &$skippedRows,
            &$lastNewId
        ) {
            $groups = $sourceExams->groupBy(function ($exam) {
                return implode('|', [
                    $exam->exam_name ?? '',
                    $exam->exam_type ?? '',
                    $exam->session_year ?? '',
                    $exam->max_marks_theory ?? '',
                    $exam->max_marks_practical ?? '',
                    $exam->fail_percent ?? '',
                    $exam->is_ser ?? '',
                ]);
            });

            foreach ($groups as $groupRows) {
                $first = $groupRows->first();
                $targetClassIds = [];
                foreach ($groupRows as $row) {
                    $srcClassId = (int) ($row->class_id ?? 0);
                    if ($srcClassId && isset($sourceIdToTargetId[$srcClassId])) {
                        $targetClassIds[] = $sourceIdToTargetId[$srcClassId];
                    } else {
                        $skippedRows++;
                    }
                }
                $targetClassIds = array_values(array_unique(array_filter($targetClassIds)));
                if (count($targetClassIds) === 0) {
                    continue;
                }

                $payload = [
                    'exam_name' => $first->exam_name,
                    'exam_type' => $first->exam_type,
                    'max_marks_theory' => $first->max_marks_theory,
                    'max_marks_practical' => $first->max_marks_practical,
                    'fail_percent' => $first->fail_percent,
                    'class_ids' => $targetClassIds,
                ];

                $lastNewId = $this->createExamRows(
                    $payload,
                    $targetSession,
                    (int) ($first->is_ser ?? 0),
                    auth()->id()
                );
                $copiedGroups++;
            }
        });

        $msg = "Copied {$copiedGroups} exam(s) from {$sourceSession} to {$targetSession}.";
        if ($skippedRows > 0) {
            $msg .= " Skipped {$skippedRows} class mapping(s) (missing classes in target session).";
        }

        if ($lastNewId) {
            return redirect()->route('academic.exams.edit', $lastNewId)->with('success', $msg . ' Now you can edit.');
        }

        return redirect()->back()->with('success', $msg);
    }

    private function createExamRows(array $validated, string $sessionYear, int $isSer, ?int $createdBy, ?string $examGroupId = null): int
    {
        $examGroupId = $examGroupId ?: (string) Str::uuid();
        $firstId = null;
        foreach ($validated['class_ids'] as $classId) {
            $created = Exam::create([
                'exam_group_id' => $examGroupId,
                'exam_name' => $validated['exam_name'],
                'exam_type' => $validated['exam_type'],
                'max_marks_theory' => $validated['max_marks_theory'] ?? null,
                'max_marks_practical' => $validated['max_marks_practical'] ?? null,
                'fail_percent' => $validated['fail_percent'] ?? null,
                'is_ser' => $isSer,
                'class_id' => $classId,
                'session_year' => $sessionYear,
                'created_by' => $createdBy,
            ]);
            if ($firstId === null) {
                $firstId = $created->id;
            }
        }
        return (int) ($firstId ?? 0);
    }

    private function activeExamTypes()
    {
        return ExamType::query()
            ->where('is_delete', 0)
            ->orderBy('examtype')
            ->pluck('examtype')
            ->filter()
            ->values();
    }

    private function buildExamGroupQuery(Exam $exam): Builder
    {
        if (!empty($exam->exam_group_id)) {
            return Exam::query()->where('exam_group_id', $exam->exam_group_id);
        }

        $query = Exam::query()
            ->where('exam_name', $exam->exam_name)
            ->where('exam_type', $exam->exam_type)
            ->where('session_year', $exam->session_year)
            ->where('is_ser', $exam->is_ser);

        $exam->max_marks_theory === null
            ? $query->whereNull('max_marks_theory')
            : $query->where('max_marks_theory', $exam->max_marks_theory);

        $exam->max_marks_practical === null
            ? $query->whereNull('max_marks_practical')
            : $query->where('max_marks_practical', $exam->max_marks_practical);

        $exam->fail_percent === null
            ? $query->whereNull('fail_percent')
            : $query->where('fail_percent', $exam->fail_percent);

        if ($exam->created_by === null) {
            $query->whereNull('created_by');
        } else {
            $query->where('created_by', $exam->created_by);
        }

        return $query;
    }

    private function examGroupHasMarks(array $examIds): bool
    {
        $examIds = array_values(array_filter(array_map('intval', $examIds)));
        if (count($examIds) === 0) {
            return false;
        }

        // Marks table used in this codebase that contains exam_id:
        // - previosly_saved_marks_entry.exam_id (string)
        // - academic_exam_subject_marks.exam_id (bigint)
        $has = false;

        if (Schema::hasTable('previosly_saved_marks_entry') && Schema::hasColumn('previosly_saved_marks_entry', 'exam_id')) {
            $has = DB::table('previosly_saved_marks_entry')
                ->whereIn('exam_id', array_map('strval', $examIds))
                ->where(function ($q) {
                    if (Schema::hasColumn('previosly_saved_marks_entry', 'is_delete')) {
                        $q->where('is_delete', 0);
                    }
                })
                ->exists();
        }
        if ($has) {
            return true;
        }

        if (Schema::hasTable('academic_exam_subject_marks') && Schema::hasColumn('academic_exam_subject_marks', 'exam_id')) {
            $has = DB::table('academic_exam_subject_marks')
                ->whereIn('exam_id', $examIds)
                ->exists();
        }

        return $has;
    }

    /**
     * Active academic session matches the dynamically switched DB (staff session/cookie selectedYear).
     */
    private function resolveDynamicSessionYear(Request $request): ?string
    {
        $year = $request->session()->get('selectedYear');
        if (empty($year)) {
            $year = $request->cookie('selectedYear');
        }
        if (empty($year)) {
            $year = Config::get('database.connections.dynamic.database');
        }
        if (empty($year)) {
            $defaultConnection = Config::get('database.default');
            $year = Config::get("database.connections.{$defaultConnection}.database");
        }

        // Session DB names are like 2025_2026; fall back only when value looks like a session name.
        if (is_string($year) && preg_match('/^\d{4}_\d{4}$/', $year)) {
            return $year;
        }

        return null;
    }
}
