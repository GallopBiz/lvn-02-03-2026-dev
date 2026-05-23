<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicRemark;
use App\Models\Academic\StudentRemark;
use App\Models\Academic\TeacherRemarkEntry;
use App\Models\Classname;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class TeacherRemarkEntryController extends Controller
{
    public function index()
    {
        $staffEmployeeId = $this->staffEmployeeId();
        $stream = $this->accessibleRemarkEntries()->latest('id')->get();
        $teacherlist = $this->accessibleTeacherSubjects()->with('Teacher.biometricDetails')->groupBy('teacher_id')->get();
        $examslist = $this->accessibleAcademicExams();
        $remarks = $this->activeRemarks();
        $isStaffRemarkUser = $this->isStaffRemarkUser();

        if ($isStaffRemarkUser) {
            $classlist = $this->accessibleTeacherSubjects()
                ->with('Class')
                ->groupBy('class_id')
                ->get()
                ->pluck('Class')
                ->filter()
                ->values();
        } else {
            $classlist = Classname::select('id', 'class_name')->where('is_delete', 0)->get();
        }

        return view('backend.AcademicsModules.teacher_remark_entry', compact(
            'classlist',
            'stream',
            'teacherlist',
            'examslist',
            'remarks',
            'isStaffRemarkUser',
            'staffEmployeeId'
        ));
    }

    public function create(Request $request)
    {
        $validator = $this->validateRemarkRequest($request);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $teacherId = $this->resolveTeacherId($request);
        if (!$this->canAccessClassSection($teacherId, $request->class_name, $request->section_name)) {
            return response()->json(['status' => 'error', 'message' => 'You are not allowed to enter remarks for this class/section.'], 403);
        }

        $data = [
            'teacher_id' => $teacherId,
            'class_id' => $request->class_name,
            'section_name' => $request->section_name,
            'exam_id' => $request->exam_name,
            'remark_id' => $request->remark_id,
        ];

        $matchingEntries = $this->sameRemarkEntryQuery($teacherId, $request->class_name, $request->section_name, $request->exam_name)
            ->orderBy('id')
            ->get();
        $entry = $matchingEntries->first();
        if ($entry) {
            $entry->update($data);
            $duplicateIds = $matchingEntries->skip(1)->pluck('id');
            if ($duplicateIds->isNotEmpty()) {
                TeacherRemarkEntry::whereIn('id', $duplicateIds)->update(['is_delete' => 1]);
            }
        } else {
            $entry = TeacherRemarkEntry::create($data);
        }

        $this->saveStudentRemarks($entry->id, $request->students);

        return response()->json(['status' => 'success', 'remark_entry_id' => $entry->id]);
    }

    public function view($id)
    {
        $stream_master = $this->accessibleRemarkEntries()->where('id', $id)->firstOrFail();
        $staffEmployeeId = $this->staffEmployeeId();
        $stream = $this->accessibleRemarkEntries()->latest('id')->get();
        $teacherlist = $this->accessibleTeacherSubjects()->with('Teacher.biometricDetails')->groupBy('teacher_id')->get();
        $examslist = $this->accessibleAcademicExams();
        $remarks = $this->activeRemarks();
        $isStaffRemarkUser = $this->isStaffRemarkUser();
        $classlist = Classname::select('id', 'class_name')->where('id', $stream_master->class_id)->get();

        $studentRemarks = DB::connection('dynamic')->table('academic_student_remarks')
            ->join('student_registration', 'academic_student_remarks.student_id', '=', 'student_registration.id')
            ->where('academic_student_remarks.teacher_remark_entry_id', $stream_master->id)
            ->select('academic_student_remarks.*', 'student_registration.student_name')
            ->orderBy('student_registration.student_name')
            ->get();

        return view('backend.AcademicsModules.teacher_remark_entry', compact(
            'studentRemarks',
            'classlist',
            'stream_master',
            'stream',
            'teacherlist',
            'examslist',
            'remarks',
            'isStaffRemarkUser',
            'staffEmployeeId'
        ));
    }

    public function store(Request $request)
    {
        $validator = $this->validateRemarkRequest($request, true);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $entry = $this->accessibleRemarkEntries()->where('id', $request->remark_entry_id)->first();
        if (!$entry) {
            return response()->json(['status' => 'error', 'message' => 'Remark entry not found or access denied.'], 403);
        }

        $teacherId = $this->resolveTeacherId($request);
        if (!$this->canAccessClassSection($teacherId, $request->class_name, $request->section_name)) {
            return response()->json(['status' => 'error', 'message' => 'You are not allowed to update remarks for this class/section.'], 403);
        }

        $entry->update([
            'teacher_id' => $teacherId,
            'class_id' => $request->class_name,
            'section_name' => $request->section_name,
            'exam_id' => $request->exam_name,
            'remark_id' => $request->remark_id,
        ]);

        $this->saveStudentRemarks($entry->id, $request->students);

        return response()->json(['status' => 'success']);
    }

    public function classStudentData(Request $request)
    {
        $classId = $request->class;
        $sectionName = $request->section_name;

        if (!$this->canAccessClassSection($this->resolveTeacherId($request), $classId, $sectionName)) {
            return response()->json(['students' => [], 'message' => 'You are not allowed to access this class/section.'], 403);
        }

        $className = Classname::where('id', $classId)->value('class_name');
        if (!$className) {
            return ['students' => []];
        }

        $students = DB::connection('dynamic')->table('student_registration')
            ->where(function ($query) use ($classId, $className) {
                $query->where('class_id', $classId)
                    ->orWhere('class_name', $className);
            })
            ->where(function ($query) use ($sectionName) {
                $query->where('section_name', $sectionName)
                    ->orWhereJsonContains('json_str->section_name', $sectionName);
            })
            ->orderBy('student_name')
            ->get();

        $rollMap = $this->studentRollMap($className, $sectionName, $request->exam_name);
        $students = $students->map(function ($student) use ($rollMap) {
            $student->roll_no = $rollMap->get($student->id, $student->roll_no ?? '');
            return $student;
        });

        return ['students' => $students];
    }

    public function checkRemarkEntryStatus(Request $request)
    {
        $teacherId = $this->resolveTeacherId($request);
        if (!$this->canAccessClassSection($teacherId, $request->class_name, $request->section_name)) {
            return response()->json(['exists' => false, 'message' => 'You are not allowed to access this class/section.'], 403);
        }

        $entry = $this->sameRemarkEntryQuery($teacherId, $request->class_name, $request->section_name, $request->exam_name)->first();

        return response()->json([
            'exists' => (bool) $entry,
            'remark_entry_id' => $entry->id ?? null,
            'edit_url' => $entry ? url($this->remarkRoutePrefix() . 'view-teacher-remark-entry/' . $entry->id) : null,
            'message' => $entry ? 'Teacher remark entry is already completed for the selected class, section and exam.' : null,
        ]);
    }

    public function pickMarks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'teacher_name' => $this->isStaffRemarkUser() ? 'nullable' : 'required|integer',
            'class_name' => 'required|integer',
            'section_name' => 'required|string',
            'exam_name' => 'required|integer',
            'students' => 'required|array',
            'students.*' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        if (!$this->canAccessClassSection($this->resolveTeacherId($request), $request->class_name, $request->section_name)) {
            return response()->json(['status' => 'error', 'message' => 'You are not allowed to access marks for this class/section.'], 403);
        }

        $studentMarksTable = $this->studentMarksTable();
        if (!Schema::connection('dynamic')->hasTable($studentMarksTable)) {
            return response()->json(['status' => 'error', 'message' => 'Student marks table was not found.'], 404);
        }

        $studentIds = collect($request->students)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $marksRows = DB::connection('dynamic')->table('previosly_saved_marks_entry as psm')
            ->join($studentMarksTable . ' as sm', 'sm.marks_id', '=', 'psm.id')
            ->leftJoin('academic_exam_subject_marks as esm', function ($join) {
                $join->on('esm.exam_id', '=', 'psm.exam_id')
                    ->on('esm.subject_id', '=', 'psm.subject_id');
            })
            ->leftJoin('academic_exam as ae', 'ae.id', '=', 'psm.exam_id')
            ->where('psm.is_delete', 0)
            ->where('psm.class_id', $request->class_name)
            ->where('psm.section_name', $request->section_name)
            ->where('psm.exam_id', $request->exam_name)
            ->whereIn('sm.student_id', $studentIds)
            ->select(
                'sm.student_id',
                'sm.total_marks',
                'sm.subject_marks',
                'sm.mark_theory',
                'sm.mark_practical',
                'sm.is_absent',
                'sm.is_absent_pr',
                'esm.max_marks',
                'ae.max_marks_theory',
                'ae.max_marks_practical'
            )
            ->get();

        $summary = $marksRows
            ->groupBy('student_id')
            ->map(function ($rows) {
                $totalObtained = 0;
                $totalMarks = 0;

                foreach ($rows as $row) {
                    $totalObtained += $this->markValue($row->total_marks ?? null)
                        ?? $this->markValue($row->subject_marks ?? null)
                        ?? ($this->numericMark($row->mark_theory ?? null) + $this->numericMark($row->mark_practical ?? null));

                    $subjectMax = $this->numericMark($row->max_marks ?? null);
                    if ($subjectMax <= 0) {
                        $subjectMax = $this->numericMark($row->max_marks_theory ?? null) + $this->numericMark($row->max_marks_practical ?? null);
                    }
                    $totalMarks += $subjectMax;
                }

                return [
                    'total_marks' => $this->formatNumber($totalMarks),
                    'total_obtained' => $this->formatNumber($totalObtained),
                    'grade' => $this->overallGrade($totalObtained, $totalMarks),
                ];
            });

        return response()->json(['status' => 'success', 'marks' => $summary]);
    }

    public function destroy($id)
    {
        [$tableName, $entryId] = array_pad(explode('-', $id), 2, null);
        if ($tableName !== 'academic_teacher_remark_entries' || !$entryId) {
            abort(400, 'Invalid remark table.');
        }

        $entry = $this->accessibleRemarkEntries()->where('id', $entryId)->first();
        if (!$entry) {
            abort(403, 'You are not allowed to delete this remark entry.');
        }

        $entry->update(['is_delete' => 1]);

        return redirect()->route($this->isStaffRemarkUser() ? 'staff.teacher-remark-entry' : 'teacher-remark-entry')
            ->with('success', 'Record successfully removed');
    }

    private function validateRemarkRequest(Request $request, bool $isUpdate = false)
    {
        $rules = [
            'teacher_name' => $this->isStaffRemarkUser() ? 'nullable' : 'required|integer',
            'class_name' => 'required|integer',
            'section_name' => 'required|string',
            'exam_name' => 'required|integer',
            'remark_id' => 'required|integer',
            'students' => 'required|array',
            'students.*.student_id' => 'required|integer',
            'students.*.roll_no' => 'nullable|string|max:50',
            'students.*.scholar_no' => 'nullable|string|max:50',
            'students.*.remark_id' => 'nullable|integer',
            'students.*.remark_text' => 'nullable|string|max:255',
            'students.*.total_marks' => 'nullable|numeric|min:0',
            'students.*.total_obtained' => 'nullable|numeric|min:0',
            'students.*.grade' => 'nullable|string|max:20',
            'students.*.result_remark' => 'nullable|string|max:255',
        ];

        if ($isUpdate) {
            $rules['remark_entry_id'] = 'required|integer';
        }

        return Validator::make($request->all(), $rules);
    }

    private function saveStudentRemarks(int $entryId, ?array $students): void
    {
        if (empty($students)) {
            return;
        }

        $existingRows = StudentRemark::where('teacher_remark_entry_id', $entryId)
            ->whereIn('student_id', collect($students)->pluck('student_id')->filter()->values())
            ->pluck('id', 'student_id');

        DB::connection('dynamic')->transaction(function () use ($entryId, $students, $existingRows) {
            foreach ($students as $student) {
                $data = [
                    'teacher_remark_entry_id' => $entryId,
                    'student_id' => $student['student_id'],
                    'roll_no' => $student['roll_no'] ?? null,
                    'scholar_no' => $student['scholar_no'] ?? null,
                    'remark_id' => $student['remark_id'] ?? null,
                    'remark_text' => $student['remark_text'] ?? null,
                    'total_marks' => $student['total_marks'] ?? null,
                    'total_obtained' => $student['total_obtained'] ?? null,
                    'grade' => $student['grade'] ?? null,
                    'result_remark' => $student['result_remark'] ?? null,
                ];

                $existingId = $existingRows->get($student['student_id']);
                if ($existingId) {
                    StudentRemark::where('id', $existingId)->update($data);
                } else {
                    StudentRemark::create($data);
                }
            }
        });
    }

    private function isStaffRemarkUser(): bool
    {
        return Auth::guard('staff')->check() && !Auth::guard('web')->check();
    }

    private function remarkRoutePrefix(): string
    {
        return $this->isStaffRemarkUser() ? 'staff/' : '';
    }

    private function staffEmployeeId(): ?int
    {
        return $this->isStaffRemarkUser() ? (int) Auth::guard('staff')->user()->employee_id : null;
    }

    private function resolveTeacherId(Request $request): ?int
    {
        return $this->isStaffRemarkUser()
            ? $this->staffEmployeeId()
            : (int) $request->teacher_name;
    }

    private function accessibleTeacherSubjects()
    {
        $query = TeacherSubject::where('is_delete', 0);

        if ($this->isStaffRemarkUser()) {
            $query->where('teacher_id', $this->staffEmployeeId());
        }

        return $query;
    }

    private function accessibleRemarkEntries()
    {
        $query = TeacherRemarkEntry::with(['className', 'exam', 'remark'])->where('is_delete', 0);

        if ($this->isStaffRemarkUser()) {
            $query->where('teacher_id', $this->staffEmployeeId());
        }

        return $query;
    }

    private function sameRemarkEntryQuery($teacherId, $classId, $sectionName, $examId)
    {
        return $this->accessibleRemarkEntries()
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('section_name', $sectionName)
            ->where('exam_id', $examId);
    }

    private function canAccessClassSection(?int $teacherId, $classId, ?string $sectionName): bool
    {
        if (!$this->isStaffRemarkUser()) {
            return true;
        }

        if (!$teacherId || !$classId || !$sectionName) {
            return false;
        }

        return TeacherSubject::where('is_delete', 0)
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where(function ($query) use ($sectionName) {
                $query->where('section_name', $sectionName)
                    ->orWhere('section_name', 'All');
            })
            ->exists();
    }

    private function activeRemarks()
    {
        if (!Schema::connection('dynamic')->hasTable('academic_remarksmaster')) {
            return collect();
        }

        return AcademicRemark::where('is_delete', 0)
            ->where(function ($query) {
                $query->whereNull('not_show')->orWhere('not_show', 'No');
            })
            ->orderBy('remark')
            ->get();
    }

    private function accessibleAcademicExams()
    {
        if (!Schema::connection('dynamic')->hasTable('academic_exam')) {
            return collect();
        }

        $query = DB::connection('dynamic')->table('academic_exam as ae')
            ->leftJoin('classes as c', 'c.id', '=', 'ae.class_id')
            ->leftJoin('class_name as cn', 'cn.class_name', '=', 'c.class_name')
            ->select('ae.*', 'c.class_name as class_name_text', 'cn.id as marks_class_id');

        if (Schema::connection('dynamic')->hasColumn('academic_exam', 'deleted_at')) {
            $query->whereNull('ae.deleted_at');
        }

        if ($this->isStaffRemarkUser()) {
            $query->whereIn('cn.id', $this->accessibleClassIds());
        }

        return $query->orderBy('ae.id', 'desc')->get();
    }

    private function accessibleClassIds()
    {
        return $this->accessibleTeacherSubjects()
            ->select('class_id')
            ->distinct()
            ->pluck('class_id')
            ->filter()
            ->values();
    }

    private function studentRollMap(string $className, string $sectionName, $examId)
    {
        if (!Schema::connection('dynamic')->hasTable('academic_student_roll_no')) {
            return collect();
        }

        return DB::connection('dynamic')->table('academic_student_roll_no')
            ->where('class_id', $className)
            ->where('section_id', $sectionName)
            ->where('session_id', $this->activeSessionName())
            ->when($examId, fn ($query) => $query->where('exam_id', $examId))
            ->pluck('roll_no', 'student_id');
    }

    private function activeSessionName(): string
    {
        return request()->session()->get('selectedYear')
            ?? request()->cookie('selectedYear')
            ?? config('database.connections.dynamic.database');
    }

    private function studentMarksTable(): string
    {
        return Schema::connection('dynamic')->hasTable('academic_students_marks')
            ? 'academic_students_marks'
            : 'marks';
    }

    private function numericMark($value): float
    {
        return is_numeric($value) ? (float) $value : 0;
    }

    private function markValue($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function formatNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    private function overallGrade(float $obtained, float $max): string
    {
        if ($max <= 0 || !Schema::connection('dynamic')->hasTable('grademaster')) {
            return '';
        }

        $percentage = ($obtained / $max) * 100;

        return DB::connection('dynamic')->table('grademaster')
            ->where('is_delete', 0)
            ->where('min_per', '<=', $percentage)
            ->where('max_per', '>=', $percentage)
            ->orderByRaw('CAST(min_per AS DECIMAL(10,2)) DESC')
            ->value('grade') ?? '';
    }
}
