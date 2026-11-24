<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Models\Academic\AttendanceCollective;
use App\Models\Academic\AttendanceCollectiveDetail;
use App\Models\Academic\AttendanceDetail;
use App\Models\Academic\AttendanceMonthlyLock;
use App\Models\Academic\Exam;
use App\Models\Classes;
use App\Models\Classname;
use App\Models\Holidays;
use App\Models\Student_registration;
use App\Models\TeacherSubject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceController extends Controller
{
    private const STATUSES = [
        'present' => 'Present',
        'absent' => 'Absent',
        'leave' => 'Leave',
        'pl' => 'PL',
    ];

    public function daily(Request $request)
    {
        $filters = $this->filters($request);
        $isStaffUser = $this->isStaffAttendanceUser();

        if ($isStaffUser) {
            $assignedSubjects = $this->accessibleTeacherSubjects(true)->get();
            if ($assignedSubjects->isNotEmpty()) {
                if (empty($filters['class_id']) || !$this->canStaffAccessClassSection($filters['class_id'], $filters['section_name'], true)) {
                    $firstAssignment = $assignedSubjects->first();
                    $filters['class_id'] = $firstAssignment->class_id;
                    $filters['section_name'] = $firstAssignment->section_name;
                }
            }
        }

        $classes = $this->classes($isStaffUser);
        $selectedClass = $filters['class_id'] ? $this->resolveClass($filters['class_id']) : null;
        $sections = $this->sections($filters['class_id'], $isStaffUser);
        $allSections = $this->allDistinctSections($isStaffUser);
        $classSectionMap = [];
        foreach ($classes as $c) {
            $classSectionMap[$c->id] = $this->sections($c->id, $isStaffUser)->values()->toArray();
        }
        $teachers = TeacherSubject::with('Teacher')->where('is_delete', 0)->groupBy('teacher_id')->get();
        if ($isStaffUser && $filters['class_id'] && $filters['section_name']
            && !$this->canStaffAccessClassSection($filters['class_id'], $filters['section_name'], true)) {
            abort(403, 'You are not allowed to view students for this class/section.');
        }
        $students = $filters['class_id'] && (!$isStaffUser || $filters['section_name'])
            ? $this->studentsForClass(
                $filters['class_id'],
                $filters['section_name'],
                true
            )->get()
            : collect();

        // Selected Date >= Student Registration Date
        if (!empty($filters['date']) && $students->isNotEmpty()) {
            $students = $this->filterStudentsByRegistrationDate($students, $filters['date']);
        }

        $editingAttendance = null;
        if ($request->route('id')) {
            $editingAttendance = $this->accessibleAttendanceQuery()->with('details.student')->findOrFail($request->route('id'));
            if ($editingAttendance->is_locked) {
                return redirect()->route('dailyattandence')->with('error', $this->lockedAttendanceMessage());
            }
            if ($this->isStaffAttendanceUser() && $this->isMonthLockedForAttendance($editingAttendance)) {
                return redirect()->route('dailyattandence')->with('error', $this->lockedMonthlyAttendanceMessage());
            }
            $filters['date'] = optional($editingAttendance->attendance_date)->format('Y-m-d');
            $filters['class_id'] = $editingAttendance->class_id;
            $filters['section_name'] = $editingAttendance->section_name;
            $filters['academic_session'] = $editingAttendance->academic_session;
            if ($isStaffUser) {
                if (!$this->canStaffAccessClassSection($filters['class_id'], $filters['section_name'], true)) {
                    abort(403, 'You are not allowed to view students for this class/section.');
                }
                $students = $this->studentsForClass($filters['class_id'], $filters['section_name'], true)->get();
                if (!empty($filters['date']) && $students->isNotEmpty()) {
                    $students = $this->filterStudentsByRegistrationDate($students, $filters['date']);
                }
            } else {
                $students = $editingAttendance->details->pluck('student')->filter()->values();
            }
            $sections = $this->sections($filters['class_id'], $isStaffUser);
            $selectedClass = $this->resolveClass($filters['class_id']);
        }

        $existingAttendance = null;
        if (!$editingAttendance && !empty($filters['date']) && !empty($filters['class_id'])) {
            $existingAttendance = $this->findExistingAttendance(
                $filters['date'],
                $filters['class_id'],
                $filters['section_name'],
                $filters['academic_session'],
                null,
                $isStaffUser ? $this->staffEmployeeId() : null
            );
        }

        $recentAttendanceQuery = $this->accessibleAttendanceQuery()->with('teacher', 'className')
            ->where('is_delete', 0);

        $filterDate = $request->query('history_date', $request->query('filter_date'));
        if (!empty($filterDate)) {
            $recentAttendanceQuery->where(function ($query) use ($filterDate) {
                $query->whereDate('attendance_date', $filterDate)
                    ->orWhereDate('created_at', $filterDate);
            });
        }

        $perPage = $request->integer('per_page', 5);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 5;
        }

        $recentAttendance = $recentAttendanceQuery
            ->orderByDesc('attendance_date')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $summary = $editingAttendance
            ? $this->summaryFromDetails($editingAttendance->details)
            : $this->emptySummary($students->count());
        $nonWorkingDay = $this->nonWorkingDayInfo($filters['date']);
        $isFutureDate = $this->isFutureAttendanceDate($filters['date']);

        return view('backend.AcademicsModules.dailyattandence', [
            'statuses' => self::STATUSES,
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'sections' => $sections,
            'allSections' => $allSections,
            'classSectionMap' => $classSectionMap,
            'teachers' => $teachers,
            'students' => $students,
            'filters' => $filters,
            'recentAttendance' => $recentAttendance,
            'editingAttendance' => $editingAttendance,
            'existingAttendance' => $existingAttendance,
            'summary' => $summary,
            'nonWorkingDay' => $nonWorkingDay,
            'isFutureDate' => $isFutureDate,
            'isStaffAttendanceUser' => $isStaffUser,
        ]);
    }

    public function checkMarked(Request $request)
    {
        $date = $request->query('date', $request->query('attendance_date'));
        $classId = $request->query('class_id');
        $sectionName = $request->query('section_name');
        $session = $request->query('academic_session', $this->defaultSession($request));
        $isStaffUser = $this->isStaffAttendanceUser();
        $teacherId = $isStaffUser ? $this->staffEmployeeId() : ($request->query('teacher_id') ? (int) $request->query('teacher_id') : null);

        if ($isStaffUser) {
            if (empty($classId)) {
                $assignment = $this->accessibleTeacherSubjects(true)->first();
                if ($assignment) {
                    $classId = $assignment->class_id;
                    if (empty($sectionName)) {
                        $sectionName = $assignment->section_name;
                    }
                }
            } elseif ($sectionName && !$this->canStaffAccessClassSection($classId, $sectionName, true)) {
                return response()->json([
                    'already_marked' => false,
                    'allowed' => false,
                    'message' => 'You are not assigned as class teacher for this class/section.',
                ]);
            }
        }

        if (empty($date) || (empty($classId) && empty($teacherId))) {
            return response()->json([
                'already_marked' => false,
            ]);
        }

        $existing = $this->findExistingAttendance($date, $classId, $sectionName, $session, null, $teacherId);

        if ($existing) {
            $class = $this->resolveClass($classId ?: $existing->class_id);
            $className = optional($class)->class_name ?: (string) ($classId ?: $existing->class_id);
            return response()->json([
                'already_marked' => true,
                'message' => 'Attendance has already been submitted for this date.',
                'attendance_id' => $existing->id,
                'attendance_date' => optional($existing->attendance_date)->format('d-m-Y'),
                'class_name' => $className,
                'section_name' => $existing->section_name,
                'submitted_at' => optional($existing->created_at)->format('d-m-Y h:i A'),
                'is_locked' => (bool) $existing->is_locked,
                'edit_url' => route('dailyattandenceUpdate', $existing->id),
            ]);
        }

        return response()->json([
            'already_marked' => false,
        ]);
    }

    public function findExistingAttendance(?string $date, $classId, ?string $sectionName = null, ?string $session = null, ?int $excludeId = null, ?int $teacherId = null): ?Attendance
    {
        if (empty($date)) {
            return null;
        }

        if ($teacherId === null && $this->isStaffAttendanceUser()) {
            $teacherId = $this->staffEmployeeId();
        }

        if (empty($classId) && empty($teacherId)) {
            return null;
        }

        $query = Attendance::query()
            ->where('is_delete', 0)
            ->whereDate('attendance_date', $date);

        if ($teacherId) {
            $query->where(function ($tq) use ($teacherId) {
                $tq->where('Teacher_id', $teacherId)
                    ->orWhere('teacher_id', $teacherId);
            });
        }

        if (!empty($classId)) {
            $class = $this->resolveClass($classId);
            $className = optional($class)->class_name ?: (string) $classId;
            $classIds = Classes::query()->where('class_name', $className)->pluck('id')->push($classId)->filter()->unique()->values();

            $query->where(function ($q) use ($classIds, $className, $classId) {
                $q->whereIn('class_id', $classIds)
                    ->orWhere('class_id', $className)
                    ->orWhere('class_id', (string) $classId)
                    ->orWhere('class_name', $className);
            });

            if (!empty($sectionName)) {
                $query->where(function ($inner) use ($sectionName) {
                    $inner->where('section_name', $sectionName)
                        ->orWhere('section_name', 'All');
                });
            }
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }

    public function students(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'section_name' => 'nullable|string',
        ]);

        $isStaffUser = $this->isStaffAttendanceUser();
        if ($isStaffUser) {
            if (!$request->filled('section_name')) {
                return response()->json([]);
            }
            if (!$this->canStaffAccessClassSection(
                $request->input('class_id'),
                $request->input('section_name'),
                true
            )) {
                abort(403, 'You are not allowed to view students for this class/section.');
            }
        }

        $asOfDate = $request->input('date', $request->input('attendance_date'));
        $students = $this->studentsForClass(
            $request->class_id,
            $request->section_name,
            true
        )->get();

        if (!empty($asOfDate) && $students->isNotEmpty()) {
            $students = $this->filterStudentsByRegistrationDate($students, $asOfDate);
        }

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'attendance_date' => 'required|date',
            'teacher_id' => 'nullable|integer',
            'class_id' => 'required',
            'class_name' => 'nullable|string|max:100',
            'section_name' => 'required|string|max:100',
            'academic_session' => 'required|string|max:50',
            'attendance' => 'required|array|min:1',
            'attendance.*' => 'required|in:present,absent,leave,pl',
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string|max:255',
        ], [
            'attendance.required' => 'Please select attendance status for students before saving.',
            'attendance.min' => 'Please select attendance status for at least one student before saving.',
        ]);

        if (empty($validated['class_name'])) {
            $classObj = $this->resolveClass($validated['class_id']);
            $validated['class_name'] = optional($classObj)->class_name ?: (string) $validated['class_id'];
        }

        if ($this->isFutureAttendanceDate($validated['attendance_date'])) {
            return back()->withInput()->with('error', 'Attendance cannot be marked for a future date.');
        }

        $nonWorkingDay = $this->nonWorkingDayInfo($validated['attendance_date']);
        if ($nonWorkingDay) {
            return back()->withInput()->with('error', 'Attendance cannot be marked on ' . $nonWorkingDay['label'] . '.');
        }

        $attendanceId = $request->integer('attendance_id') ?: null;
        if ($attendanceId) {
            $attendance = $this->accessibleAttendanceQuery()->findOrFail($attendanceId);
            if ($attendance->is_locked) {
                return redirect()->route('dailyattandence')->with('error', $this->lockedAttendanceMessage());
            }
            if ($this->isStaffAttendanceUser() && $this->isMonthLockedForAttendance($attendance)) {
                return back()->withInput()->with('error', $this->lockedMonthlyAttendanceMessage());
            }
        }

        if (!$this->canStaffAccessClassSection(
            $validated['class_id'],
            $validated['section_name'],
            $this->isStaffAttendanceUser()
        )) {
            abort(403, 'You are not allowed to mark attendance for this class/section.');
        }

        $asOfDate = $validated['attendance_date'];
        $allowedStudents = $this->studentsForClass($validated['class_id'], $validated['section_name'], $this->isStaffAttendanceUser())->get();
        if (!empty($asOfDate)) {
            $allowedStudents = $this->filterStudentsByRegistrationDate($allowedStudents, $asOfDate);
        }
        $allowedStudentIds = $allowedStudents->pluck('id')->map(fn ($id) => (string) $id)->all();

        if ($this->isStaffAttendanceUser()) {
            $submittedStudentIds = array_map('strval', array_keys($validated['attendance']));
            if (array_diff($submittedStudentIds, $allowedStudentIds)) {
                abort(403, 'Attendance includes students outside your assigned class/section or not yet admitted on the selected date.');
            }
        }

        // Only mark students who are admitted on or before the selected attendance date
        $validated['attendance'] = array_intersect_key($validated['attendance'], array_flip($allowedStudentIds));

        if ($this->isStaffAttendanceUser() && AttendanceMonthlyLock::isMonthLocked(
            $validated['class_id'],
            $validated['section_name'],
            (int) Carbon::parse($validated['attendance_date'])->format('Y'),
            (int) Carbon::parse($validated['attendance_date'])->format('m')
        )) {
            return back()->withInput()->with('error', $this->lockedMonthlyAttendanceMessage());
        }

        $teacherId = $this->isStaffAttendanceUser() ? ($this->staffEmployeeId() ?: ($validated['teacher_id'] ?? null)) : ($validated['teacher_id'] ?? null);

        $duplicate = $this->findExistingAttendance(
            $validated['attendance_date'],
            $validated['class_id'],
            $validated['section_name'],
            $validated['academic_session'],
            $attendanceId ? (int) $attendanceId : null,
            $teacherId ? (int) $teacherId : null
        );

        if ($duplicate) {
            return back()->withInput()->with('error', 'Attendance has already been submitted for this date.');
        }

        $counts = collect($validated['attendance'])->countBy();
        $leaveCount = (int) ($counts['leave'] ?? 0);

        DB::transaction(function () use ($validated, $request, $attendanceId, $counts, $leaveCount, $teacherId) {
            $attendance = Attendance::updateOrCreate(
                ['id' => $attendanceId],
                [
                    'Teacher_id' => $teacherId,
                    'teacher_id' => $teacherId,
                    'class_id' => $validated['class_id'],
                    'class_name' => $validated['class_name'] ?? null,
                    'section_name' => $validated['section_name'],
                    'attendance_date' => $validated['attendance_date'],
                    'academic_session' => $validated['academic_session'],
                    'total_students' => count($validated['attendance']),
                    'present_count' => (int) ($counts['present'] ?? 0),
                    'absent_count' => (int) ($counts['absent'] ?? 0) + $leaveCount,
                    'leave_count' => $leaveCount,
                    'half_day_count' => (int) ($counts['half_day'] ?? 0),
                    'pl_count' => (int) ($counts['pl'] ?? 0),
                    'is_delete' => 0,
                ]
            );

            foreach ($validated['attendance'] as $studentId => $status) {
                AttendanceDetail::updateOrCreate(
                    ['attendance_id' => $attendance->id, 'student_id' => $studentId],
                    [
                        'status' => $status,
                        'remarks' => $request->input("remarks.$studentId"),
                    ]
                );
            }
        });

        return redirect()->route('dailyattandence')->with('success', 'Attendance saved successfully.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance = $this->accessibleAttendanceQuery()->findOrFail($attendance->id);

        if ($attendance->is_locked) {
            return redirect()->route('dailyattandence')->with('error', $this->lockedAttendanceMessage());
        }
        if ($this->isStaffAttendanceUser() && $this->isMonthLockedForAttendance($attendance)) {
            return redirect()->route('dailyattandence')->with('error', $this->lockedMonthlyAttendanceMessage());
        }

        $attendance->update(['is_delete' => 1]);

        return redirect()->route('dailyattandence')->with('success', 'Attendance deleted successfully.');
    }

    public function lock(Attendance $attendance)
    {
        if (!$this->isStaffAttendanceUser()) {
            abort(403, 'Only class teacher/staff users can lock attendance.');
        }

        $attendance = $this->accessibleAttendanceQuery()->findOrFail($attendance->id);
        if (!$this->canStaffAccessClassSection($attendance->class_id, $attendance->section_name, true)) {
            abort(403, 'Only the assigned class teacher can lock this attendance.');
        }
        if ($this->isMonthLockedForAttendance($attendance)) {
            return redirect()->route('dailyattandence')->with('error', $this->lockedMonthlyAttendanceMessage());
        }

        $attendance->update([
            'is_locked' => 1,
            'locked_at' => now(),
            'locked_by' => Auth::guard('staff')->id(),
        ]);

        return redirect()->route('dailyattandence')->with('success', 'Attendance locked successfully. Contact admin if changes are needed.');
    }

    public function unlock(Attendance $attendance)
    {
        if ($this->isStaffAttendanceUser()) {
            abort(403, 'Only admin can unlock attendance.');
        }

        $attendance->update([
            'is_locked' => 0,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        return redirect()->route('dailyattandence')->with('success', 'Attendance unlocked successfully.');
    }

    public function studentWise(Request $request)
    {
        $filters = $this->filters($request);
        $classes = $this->classes();
        $sections = $this->sections($filters['class_id']);
        $studentSearch = trim((string) $request->query('student_search', ''));
        $students = $this->studentOptions($filters['class_id'], $filters['section_name'], $studentSearch);

        $selectedStudent = $filters['student_id'] ? Student_registration::find($filters['student_id']) : null;
        if ($this->isStaffAttendanceUser() && $selectedStudent
            && !$students->contains(fn ($student) => (int) $student->id === (int) $selectedStudent->id)) {
            $selectedStudent = null;
        }
        $historyQuery = AttendanceDetail::with(['attendance.className', 'student'])
            ->join('academic_attendance', 'academic_attendance_details.attendance_id', '=', 'academic_attendance.id')
            ->where('academic_attendance.is_delete', 0)
            ->whereIn('academic_attendance_details.attendance_id', $this->accessibleAttendanceQuery()->select('id'))
            ->whereHas('attendance', function ($query) use ($filters) {
                $this->applyAttendanceFilters($query, $filters);
            })
            ->when($selectedStudent, function ($q) use ($selectedStudent) {
                $q->where('academic_attendance_details.student_id', $selectedStudent->id);
            })
            ->when(!$selectedStudent && $studentSearch !== '', function ($q) use ($studentSearch) {
                $q->whereHas('student', function ($sq) use ($studentSearch) {
                    $like = '%' . str_replace(' ', '%', $studentSearch) . '%';
                    $sq->where(function ($inner) use ($like) {
                        $inner->where('student_name', 'like', $like)
                            ->orWhere('form_number', 'like', $like)
                            ->orWhere('scholar_no', 'like', $like);
                    });
                });
            })
            ->orderByDesc('academic_attendance.attendance_date')
            ->select('academic_attendance_details.*');

        $summary = $this->summaryFromDetails((clone $historyQuery)->get());
        $history = $historyQuery->paginate(50)->appends($request->query());

        $monthlyDetail = ($filters['class_id'] && $filters['section_name'])
            ? $this->monthWiseAttendanceMatrix($filters, $selectedStudent ? $selectedStudent->id : null)
            : null;

        return view('backend.AcademicsModules.StudentAttandenc', compact(
            'classes',
            'sections',
            'students',
            'filters',
            'studentSearch',
            'selectedStudent',
            'history',
            'summary',
            'monthlyDetail'
        ));
    }

    public function reports(Request $request)
    {
        $filters = $this->filters($request);
        $classes = $this->classes();
        $sections = $this->sections($filters['class_id']);

        $recordsQuery = $this->accessibleAttendanceQuery()->with('className')
            ->where('is_delete', 0)
            ->whereHas('details', fn ($query) => $filters['student_id'] ? $query->where('student_id', $filters['student_id']) : $query)
            ->when(true, function ($query) use ($filters) {
                $this->applyAttendanceFilters($query, $filters);
            })
            ->latest('attendance_date');

        $records = (clone $recordsQuery)->paginate(30)->appends($request->query());
        $allRecords = (clone $recordsQuery)->get();
        $totals = [
            'total_students' => $allRecords->sum('total_students'),
            'present' => $allRecords->sum('present_count'),
            'absent' => $allRecords->sum('absent_count'),
            'leave' => $allRecords->sum('leave_count'),
            'half_day' => $allRecords->sum('half_day_count'),
            'pl' => $allRecords->sum('pl_count'),
        ];

        $students = $this->studentOptions($filters['class_id'], $filters['section_name'], trim((string) $request->query('student_search', '')));
        $effectiveMonth = $filters['month']
            ?: ($filters['from_date'] ? Carbon::parse($filters['from_date'])->format('Y-m') : null)
            ?: ($filters['to_date'] ? Carbon::parse($filters['to_date'])->format('Y-m') : null)
            ?: now()->format('Y-m');

        $monthWiseReport = ($filters['class_id'] && $filters['section_name'])
            ? $this->monthWiseAttendanceMatrix(array_merge($filters, ['month' => $effectiveMonth]))
            : null;

        return view('backend.AcademicsModules.Attandencereports', compact(
            'classes',
            'sections',
            'students',
            'filters',
            'records',
            'totals',
            'monthWiseReport'
        ));
    }

    public function monthlyReport(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|between:1900,2200',
            'month' => 'nullable|integer|between:1,12',
            'month_year' => 'nullable|date_format:Y-m',
        ]);

        $filters = $this->filters($request);

        $selectedYear = (int) ($request->query('year') ?: now()->format('Y'));
        $selectedMonth = (int) ($request->query('month') ?: now()->format('m'));

        if ($request->filled('month_year')) {
            $parts = explode('-', (string) $request->query('month_year'));
            if (count($parts) === 2) {
                $selectedYear = (int) $parts[0];
                $selectedMonth = (int) $parts[1];
            }
        }

        $filters['year'] = $selectedYear;
        $filters['month'] = sprintf('%04d-%02d', $selectedYear, $selectedMonth);

        $isStaffUser = $this->isStaffAttendanceUser();
        $classes = $this->classes($isStaffUser);
        $sections = $this->sections($filters['class_id'], $isStaffUser);

        if ($isStaffUser) {
            if ($filters['class_id'] && !$classes->contains(function ($class) use ($filters) {
                return (string) $class->id === (string) $filters['class_id']
                    || (string) $class->class_name === (string) $filters['class_id'];
            })) {
                abort(403, 'You are not allowed to view attendance for this class.');
            }
            if ($filters['section_name'] && (
                !$filters['class_id']
                || !$this->canStaffAccessClassSection($filters['class_id'], $filters['section_name'], true)
            )) {
                abort(403, 'You are not allowed to view attendance for this class/section as its class teacher.');
            }
        }

        $selectedClass = $filters['class_id'] ? $this->resolveClass($filters['class_id']) : null;

        $report = ($filters['class_id'] && $filters['section_name'])
            ? $this->monthWiseAttendanceMatrix($filters)
            : null;
        $monthlyLock = ($filters['class_id'] && $filters['section_name'])
            ? AttendanceMonthlyLock::getLockInfo($filters['class_id'], $filters['section_name'], $selectedYear, $selectedMonth)
            : null;

        $availableYears = range((int) now()->format('Y') - 2, (int) now()->format('Y') + 2);
        if (!in_array($selectedYear, $availableYears)) {
            $availableYears[] = $selectedYear;
            sort($availableYears);
        }

        return view('backend.AcademicsModules.attendance_monthly_report', [
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'sections' => $sections,
            'filters' => $filters,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'availableYears' => $availableYears,
            'report' => $report,
            'monthlyLock' => $monthlyLock,
        ]);
    }

    public function lockMonth(Request $request)
    {
        abort_unless($this->isStaffAttendanceUser(), 403, 'Only staff users can lock monthly attendance.');

        $validated = $request->validate([
            'class_id' => 'required|string|max:50',
            'section_name' => 'required|string|max:100',
            'year' => 'required|integer|between:1900,2200',
            'month' => 'required|integer|between:1,12',
        ]);

        if (!$this->canStaffAccessClassSection($validated['class_id'], $validated['section_name'], true)) {
            abort(403, 'Only the assigned class teacher can lock attendance for this class/section.');
        }

        $class = $this->resolveClass($validated['class_id']);
        $classId = (string) (optional($class)->id ?: $validated['class_id']);

        DB::transaction(function () use ($validated, $classId) {
            $lock = AttendanceMonthlyLock::query()
                ->where('class_id', $classId)
                ->where('section_name', $validated['section_name'])
                ->where('year', $validated['year'])
                ->where('month', $validated['month'])
                ->lockForUpdate()
                ->first();

            if ($lock && $lock->is_locked) {
                return;
            }

            $lock = $lock ?: new AttendanceMonthlyLock();
            $lock->fill([
                'academic_session' => null,
                'class_id' => $classId,
                'section_name' => $validated['section_name'],
                'year' => $validated['year'],
                'month' => $validated['month'],
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by' => Auth::guard('staff')->id(),
                'unlocked_at' => null,
                'unlocked_by' => null,
            ])->save();
        });

        return redirect()->route('staff.attendance.monthly-report', [
            'class_id' => $classId,
            'section_name' => $validated['section_name'],
            'year' => $validated['year'],
            'month' => $validated['month'],
        ])->with('success', 'Monthly attendance locked. Contact an administrator if it needs to be unlocked.');
    }

    public function unlockMonth(AttendanceMonthlyLock $lock)
    {
        abort_unless(Auth::guard('web')->check() && !$this->isStaffAttendanceUser(), 403, 'Only an administrator can unlock monthly attendance.');

        $lock->update([
            'is_locked' => false,
            'unlocked_at' => now(),
            'unlocked_by' => Auth::guard('web')->id(),
        ]);

        return redirect()->route('academic.attendance.monthly-report', [
            'class_id' => $lock->class_id,
            'section_name' => $lock->section_name,
            'year' => $lock->year,
            'month' => $lock->month,
        ])->with('success', 'Monthly attendance unlocked successfully.');
    }

    public function collective(Request $request)
    {
        $filters = $this->collectiveFilters($request);
        $editingCollective = null;
        if ($request->integer('edit_collective_id')) {
            $editingCollective = AttendanceCollective::where('is_delete', 0)->findOrFail($request->integer('edit_collective_id'));
            abort_unless(
                $this->canStaffAccessClassSection($editingCollective->class_id, $editingCollective->section_name),
                403,
                'You are not allowed to view attendance for this class/section.'
            );
            $filters = [
                'teacher_id' => $editingCollective->teacher_id,
                'class_id' => $editingCollective->class_id,
                'section_name' => $editingCollective->section_name,
                'exam_id' => $editingCollective->exam_id,
                'exam_name' => $editingCollective->exam_name,
                'academic_session' => $editingCollective->academic_session,
                'start_date' => optional($editingCollective->start_date)->format('Y-m-d'),
                'end_date' => optional($editingCollective->end_date)->format('Y-m-d'),
            ];
        }
        $classes = $this->classes();
        $sections = $this->sections($filters['class_id']);
        $teachers = TeacherSubject::with('Teacher')->where('is_delete', 0)->groupBy('teacher_id')->get();
        $selectedClass = null;
        $selectedClassName = null;
        if (!empty($filters['class_id'])) {
            $byName = Classes::where('class_name', (string) $filters['class_id'])->first();
            if ($byName) {
                $selectedClass = $byName;
                $selectedClassName = $byName->class_name;
            } else {
                $byId = Classes::find((int) $filters['class_id']);
                if ($byId) {
                    $selectedClass = $byId;
                    $selectedClassName = $byId->class_name;
                } else {
                    $selectedClassName = (string) $filters['class_id'];
                }
            }
        }

        $classIds = $selectedClassName
            ? Classes::where('class_name', $selectedClassName)->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $examsQuery = Exam::query()->orderByDesc('id');
        if ($selectedClassName) {
            $examsQuery->where(function ($q) use ($classIds, $selectedClassName) {
                if (!empty($classIds)) {
                    $q->whereIn('class_id', $classIds);
                }
                if (!is_numeric($selectedClassName)) {
                    $q->orWhere('class_id', $selectedClassName);
                }
            });
        }
        $exams = $examsQuery->get()->unique('exam_name')->values();

        if ($filters['exam_id']) {
            if ($exams->isEmpty()) {
                $filters['exam_id'] = null;
                $filters['exam_name'] = null;
            } elseif (!$exams->contains('id', (int) $filters['exam_id'])) {
                $prevExam = Exam::find($filters['exam_id']);
                $matchingSameName = $prevExam ? $exams->firstWhere('exam_name', $prevExam->exam_name) : null;
                $filters['exam_id'] = $matchingSameName ? $matchingSameName->id : null;
                $filters['exam_name'] = $matchingSameName ? $matchingSameName->exam_name : null;
            }
        }

        $students = collect();
        $collectiveRows = collect();
        $workingDays = 0;
        $totalDays = 0;
        $sundays = 0;
        $holidays = 0;

        if ($filters['class_id'] && $filters['section_name'] && $filters['start_date'] && $filters['end_date']) {
            $students = $this->studentsForClass($filters['class_id'], $filters['section_name'])->get();
            $students = $this->filterStudentsByRegistrationDate($students, $filters['end_date']);
            $calculated = $this->calculateCollectiveRows($students, $filters);
            $collectiveRows = $calculated['rows'];
            $workingDays = $calculated['working_days'];
            $totalDays = $calculated['total_days'];
            $sundays = $calculated['sundays'];
            $holidays = $calculated['holidays'];
        }

        $savedCollectives = AttendanceCollective::with('teacher', 'className')
            ->where('is_delete', 0)
            ->where(function ($q) {
                $q->whereNull('is_manual')->orWhere('is_manual', 0);
            })
            ->latest('id')
            ->limit(30)
            ->get();
        if ($this->isStaffAttendanceUser()) {
            $savedCollectives = $savedCollectives->filter(fn ($collective) =>
                $this->canStaffAccessClassSection($collective->class_id, $collective->section_name)
            )->values();
        }

        return view('backend.AcademicsModules.attendance_collective', compact(
            'filters',
            'classes',
            'sections',
            'teachers',
            'exams',
            'students',
            'collectiveRows',
            'workingDays',
            'totalDays',
            'sundays',
            'holidays',
            'editingCollective',
            'savedCollectives',
            'selectedClass'
        ));
    }

    public function storeCollective(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'nullable|integer',
            'collective_id' => 'nullable|integer',
            'class_id' => 'required',
            'class_name' => 'nullable|string|max:100',
            'section_name' => 'required|string|max:100',
            'exam_id' => 'nullable|integer',
            'exam_name' => 'nullable|string|max:255',
            'academic_session' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $selectedExam = !empty($validated['exam_id']) ? Exam::find($validated['exam_id']) : null;
        if ($selectedExam) {
            $validated['exam_name'] = $selectedExam->exam_name;
        }

        if (!$this->canStaffAccessClassSection($validated['class_id'], $validated['section_name'])) {
            abort(403, 'You are not allowed to save collective attendance for this class/section.');
        }

        $students = $this->studentsForClass($validated['class_id'], $validated['section_name'])->get();
        $students = $this->filterStudentsByRegistrationDate($students, $validated['end_date']);
        $calculated = $this->calculateCollectiveRows($students, $validated);

        if ($students->isEmpty()) {
            return back()->withInput()->with('error', 'No students found for this class and section.');
        }

        if ($calculated['working_days'] <= 0) {
            return back()->withInput()->with('error', 'No working days with recorded attendance found in this date range.');
        }

        DB::transaction(function () use ($validated, $calculated, $students) {
            $collectiveData = [
                'teacher_id' => $validated['teacher_id'] ?? null,
                'class_id' => $validated['class_id'],
                'class_name' => $validated['class_name'] ?? null,
                'section_name' => $validated['section_name'],
                'exam_id' => $validated['exam_id'] ?? null,
                'exam_name' => $validated['exam_name'] ?? null,
                'academic_session' => $validated['academic_session'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'working_days' => $calculated['working_days'],
                'student_count' => $students->count(),
                'is_delete' => 0,
            ];

            $collective = !empty($validated['collective_id'])
                ? tap(AttendanceCollective::where('is_delete', 0)->findOrFail($validated['collective_id']))->update($collectiveData)
                : AttendanceCollective::create($collectiveData);

            AttendanceCollectiveDetail::where('collective_id', $collective->id)->delete();

            foreach ($calculated['rows'] as $row) {
                AttendanceCollectiveDetail::create([
                    'collective_id' => $collective->id,
                    'student_id' => $row['student_id'],
                    'student_name' => $row['student_name'],
                    'scholar_no' => $row['scholar_no'],
                    'present_days' => $row['present_days'],
                    'working_days' => $row['working_days'],
                    'attendance_percentage' => $row['percentage'],
                    'is_below_minimum' => $row['is_below_minimum'],
                ]);
            }
        });

        return redirect()->route('academic.attendance.collective')->with('success', 'Collective attendance saved successfully.');
    }

    public function destroyCollective($collective)
    {
        $model = $this->resolveCollectiveModel($collective);
        if (!$model) {
            abort(404, 'Attendance collective record not found.');
        }
        abort_unless(
            $this->canStaffAccessClassSection($model->class_id, $model->section_name),
            403,
            'You are not allowed to edit attendance for this class/section.'
        );

        $model->update(['is_delete' => 1]);

        return redirect()->route('academic.attendance.collective')->with('success', 'Collective attendance deleted successfully.');
    }

    public function printCollective($collective)
    {
        $model = $this->resolveCollectiveModel($collective);
        if (!$model) {
            abort(404, 'Attendance collective record not found.');
        }
        abort_unless(
            $this->canStaffAccessClassSection($model->class_id, $model->section_name),
            403,
            'You are not allowed to view attendance for this class/section.'
        );

        $model->load('details', 'teacher', 'className');

        return view('backend.AcademicsModules.attendance_collective_print', ['collective' => $model]);
    }

    private function resolveCollectiveModel($collective): ?AttendanceCollective
    {
        if ($collective instanceof AttendanceCollective) {
            return $collective;
        }

        $id = is_numeric($collective) ? (int) $collective : null;
        if (!$id) {
            return null;
        }

        // Try standard find
        $model = AttendanceCollective::where('is_delete', 0)->find($id);
        if ($model) {
            return $model;
        }

        // Try dynamic connection
        try {
            $model = AttendanceCollective::on('dynamic')->where('is_delete', 0)->find($id);
            if ($model) {
                return $model;
            }
        } catch (\Throwable $e) {}

        // Fallback across available session databases
        $dbs = ['2025_2026', '2026_2027', 'gb_school_erp_26'];
        foreach ($dbs as $dbName) {
            try {
                $exists = DB::select("SELECT id FROM `$dbName`.`academic_attendance_collectives` WHERE id = ? AND (is_delete = 0 OR is_delete IS NULL)", [$id]);
                if (!empty($exists)) {
                    Config::set('database.connections.dynamic.database', $dbName);
                    Config::set('database.default', 'dynamic');
                    DB::purge('dynamic');
                    DB::reconnect('dynamic');
                    return AttendanceCollective::on('dynamic')->find($id);
                }
            } catch (\Throwable $e) {}
        }

        return null;
    }

    private function filters(Request $request): array
    {
        return [
            'class_id' => $request->query('class_id', $request->query('classname')),
            'section_name' => $request->query('section_name', $request->query('Section')),
            'student_id' => $request->integer('student_id') ?: $request->integer('inq_form_selection'),
            'date' => $request->query('date', now()->format('Y-m-d')),
            'from_date' => $request->query('from_date', $request->query('fromdate')),
            'to_date' => $request->query('to_date', $request->query('todate')),
            'month' => $request->query('month'),
            'academic_session' => $this->defaultSession($request),
        ];
    }

    private function collectiveFilters(Request $request): array
    {
        $session = $request->query('academic_session', $this->defaultSession());
        [$sessionStart, $sessionEnd] = $this->sessionDateRange($session);
        $exam = $request->integer('exam_id') ? Exam::find($request->integer('exam_id')) : null;

        return [
            'teacher_id' => $request->query('teacher_id'),
            'class_id' => $request->query('class_id'),
            'section_name' => $request->query('section_name'),
            'exam_id' => $request->integer('exam_id') ?: null,
            'exam_name' => optional($exam)->exam_name,
            'academic_session' => $session,
            'start_date' => $request->query('start_date', $sessionStart),
            'end_date' => $request->query('end_date', $sessionEnd),
        ];
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

    private function calculateCollectiveRows($students, array $filters): array
    {
        // Enforce visibility rule: Only students admitted on or before end_date should appear in this period
        if (!empty($filters['end_date'])) {
            $students = $this->filterStudentsByRegistrationDate($students, $filters['end_date']);
        }

        $workingDayStats = $this->workingDayStats($filters['start_date'], $filters['end_date']);

        $classObj = $this->resolveClass($filters['class_id']);
        $classIds = array_values(array_unique(array_filter([
            $filters['class_id'],
            optional($classObj)->id,
            optional($classObj)->class_name
        ])));

        $session = $filters['academic_session'] ?? '';
        $sessionVariants = array_unique(array_filter([
            $session,
            str_replace('_', '-', $session),
            str_replace('-', '_', $session),
        ]));

        [$sessionStart, $sessionEnd] = $this->sessionDateRange($session);

        // Count only days where attendance was recorded for this class/section
        // School Working Days: Leave days must be counted as school working days
        $recordedDaysQuery = Attendance::query()
            ->where('is_delete', 0)
            ->whereIn('class_id', $classIds)
            ->where('section_name', $filters['section_name'])
            ->when(!empty($sessionVariants), fn ($q) => $q->whereIn('academic_session', $sessionVariants))
            ->whereBetween('attendance_date', [$filters['start_date'], $filters['end_date']])
            ->where(function ($q) {
                $q->where('total_students', '>', 0)
                    ->orWhereHas('details');
            });
        $this->applyWorkingDayOnlyFilter($recordedDaysQuery);

        $recordedDates = $recordedDaysQuery
            ->distinct()
            ->pluck('attendance_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->sort()
            ->values();

        $workingDays = $recordedDates->count();

        $studentIds = $students->pluck('id')->values();

        $attendanceRecords = $studentIds->isNotEmpty()
            ? AttendanceDetail::query()
                ->join('academic_attendance', 'academic_attendance_details.attendance_id', '=', 'academic_attendance.id')
                ->where('academic_attendance.is_delete', 0)
                ->whereIn('academic_attendance.class_id', $classIds)
                ->where('academic_attendance.section_name', $filters['section_name'])
                ->when(!empty($sessionVariants), fn ($q) => $q->whereIn('academic_attendance.academic_session', $sessionVariants))
                ->whereBetween('academic_attendance.attendance_date', [$filters['start_date'], $filters['end_date']])
                ->whereIn('academic_attendance_details.student_id', $studentIds)
                ->whereIn('academic_attendance_details.status', ['present', 'half_day', 'absent', 'leave', 'pl'])
                ->when(true, function ($query) {
                    $this->applyWorkingDayOnlyFilter($query);
                })
                ->select(
                    'academic_attendance_details.student_id',
                    'academic_attendance_details.status',
                    'academic_attendance.attendance_date'
                )
                ->get()
                ->groupBy('student_id')
            : collect();

        $rows = $students->map(function ($student) use ($recordedDates, $attendanceRecords, $workingDays, $sessionStart, $filters) {
            $regDate = $this->resolveStudentRegistrationDate($student);

            // Attendance Starting Date Rule: Whichever is later: Session Start Date (1st April) or Student Registration Date
            $studentSessionStart = ($regDate && $regDate >= $sessionStart) ? $regDate : $sessionStart;
            $studentEffectiveStart = max($filters['start_date'], $studentSessionStart);

            if ($studentEffectiveStart > $filters['end_date']) {
                $studentWorkingDays = 0;
            } else {
                $studentWorkingDays = $recordedDates->filter(function ($date) use ($studentEffectiveStart, $filters) {
                    return $date >= $studentEffectiveStart && $date <= $filters['end_date'];
                })->count();
            }

            $records = $attendanceRecords->get($student->id, collect());
            $presentDays = 0.0;
            $markedDates = [];

            foreach ($records as $rec) {
                $attDate = Carbon::parse($rec->attendance_date)->format('Y-m-d');
                if ($attDate >= $studentEffectiveStart && $attDate <= $filters['end_date']) {
                    if (in_array($rec->status, ['present', 'half_day', 'absent'])) {
                        $markedDates[$attDate] = true;
                    }
                    if ($rec->status === 'present') {
                        $presentDays += 1.0;
                    } elseif ($rec->status === 'half_day') {
                        $presentDays += 0.5;
                    }
                }
            }

            $studentWorkingDaysMarked = count($markedDates);
            $percentage = $studentWorkingDays > 0 ? round(($presentDays / $studentWorkingDays) * 100, 2) : 0;

            return [
                'student_id' => $student->id,
                'student_name' => $student->student_name,
                'scholar_no' => $student->scholar_no ?: $student->form_number,
                'registration_date' => $regDate,
                'effective_start_date' => $studentEffectiveStart,
                'present_days' => $presentDays,
                'working_days' => $studentWorkingDays,
                'school_working_days' => $workingDays,
                'student_working_days' => $studentWorkingDaysMarked,
                'percentage' => $percentage,
                'is_below_minimum' => $percentage < 75,
            ];
        })->values();

        return [
            'working_days' => $workingDays,
            'total_days' => $workingDayStats['total_days'],
            'sundays' => $workingDayStats['sundays'],
            'holidays' => $workingDayStats['holidays'],
            'rows' => $rows,
        ];
    }

    private function workingAttendanceQuery(array $filters)
    {
        $query = Attendance::query()
            ->where('is_delete', 0)
            ->where('class_id', $filters['class_id'])
            ->where('section_name', $filters['section_name'])
            ->where('academic_session', $filters['academic_session'])
            ->whereBetween('attendance_date', [$filters['start_date'], $filters['end_date']])
            ->orderBy('attendance_date');

        $this->applyWorkingDayOnlyFilter($query);

        return $query;
    }

    private function workingDayStats(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        $totalDays = $start->diffInDays($end) + 1;
        $nonWorkingDays = $this->nonWorkingDaysForRange($start, $end);
        $sundays = collect($nonWorkingDays)->where('type', 'sunday')->count();
        $holidays = collect($nonWorkingDays)->where('type', 'holiday')->count();

        return [
            'total_days' => $totalDays,
            'sundays' => $sundays,
            'holidays' => $holidays,
            'working_days' => max(0, $totalDays - count($nonWorkingDays)),
        ];
    }

    private function sessionDateRange(string $session): array
    {
        if (preg_match('/^(\d{4})[-_](\d{2}|\d{4})$/', $session, $matches)) {
            $startYear = (int) $matches[1];
            $endYear = strlen($matches[2]) === 2
                ? (int) (substr((string) $startYear, 0, 2) . $matches[2])
                : (int) $matches[2];

            return [
                sprintf('%04d-04-01', $startYear),
                sprintf('%04d-03-31', $endYear),
            ];
        }

        $year = (int) now()->format('Y');

        return [
            sprintf('%04d-04-01', $year),
            sprintf('%04d-03-31', $year + 1),
        ];
    }

    private function monthWiseAttendanceMatrix(array $filters, ?int $onlyStudentId = null): array
    {
        $monthStr = $filters['month']
            ?: ($filters['from_date'] ? Carbon::parse($filters['from_date'])->format('Y-m') : null)
            ?: ($filters['to_date'] ? Carbon::parse($filters['to_date'])->format('Y-m') : null)
            ?: now()->format('Y-m');
        $month = Carbon::createFromFormat('Y-m', $monthStr);
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $daysInMonth = (int) $end->format('d');
        $days = range(1, $daysInMonth);
        $nonWorkingDays = $this->nonWorkingDaysForRange($start, $end);

        $studentsQuery = $this->studentsForClass(
            $filters['class_id'],
            $filters['section_name'],
            true
        );
        if ($onlyStudentId) {
            $studentsQuery->where('id', $onlyStudentId);
        }
        $students = $studentsQuery->get();
        $students = $this->filterStudentsByRegistrationDate($students, $end->toDateString());

        $classObj = $this->resolveClass($filters['class_id']);
        $classIds = array_values(array_unique(array_filter([
            $filters['class_id'],
            optional($classObj)->id,
            optional($classObj)->class_name
        ])));

        $attendanceRecords = Attendance::with('details')
            ->where('is_delete', 0)
            ->whereIn('class_id', $classIds)
            ->where('section_name', $filters['section_name'])
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->when(!empty($filters['academic_session']), function ($q) use ($filters) {
                $q->where('academic_session', $filters['academic_session']);
            })
            ->get()
            ->filter(function ($att) {
                return $att->total_students > 0 || $att->details->isNotEmpty();
            })
            ->keyBy(fn ($record) => optional($record->attendance_date)->format('Y-m-d'));

        // Calculate "Till Previous Month"
        $monthNum = (int) $month->format('m');
        $yearNum = (int) $month->format('Y');
        $sessionStart = ($monthNum >= 4)
            ? Carbon::createFromDate($yearNum, 4, 1)->startOfDay()
            : Carbon::createFromDate($yearNum - 1, 4, 1)->startOfDay();

        $hasPrevPeriod = $start->greaterThan($sessionStart);
        $prevWorkingDays = 0;
        $prevPresentCounts = collect();

        if ($hasPrevPeriod) {
            $prevStart = $sessionStart->copy();
            $prevEnd = $start->copy()->subDay()->endOfDay();

            $session = !empty($filters['academic_session']) ? $filters['academic_session'] : null;
            $sessionVariants = $session ? array_unique([
                $session,
                str_replace('_', '-', $session),
                str_replace('-', '_', $session),
            ]) : [];

            // School Working Days: Leave days must be counted as school working days
            $prevQuery = Attendance::where('is_delete', 0)
                ->whereIn('class_id', $classIds)
                ->where('section_name', $filters['section_name'])
                ->whereBetween('attendance_date', [$prevStart->toDateString(), $prevEnd->toDateString()])
                ->when(!empty($sessionVariants), function ($q) use ($sessionVariants) {
                    $q->whereIn('academic_session', $sessionVariants);
                });
            $this->applyWorkingDayOnlyFilter($prevQuery);
            $prevWorkingDays = (int) $prevQuery->distinct('attendance_date')->count('attendance_date');

            $prevPresentQuery = AttendanceDetail::query()
                ->join('academic_attendance', 'academic_attendance_details.attendance_id', '=', 'academic_attendance.id')
                ->where('academic_attendance.is_delete', 0)
                ->whereIn('academic_attendance.class_id', $classIds)
                ->where('academic_attendance.section_name', $filters['section_name'])
                ->whereBetween('academic_attendance.attendance_date', [$prevStart->toDateString(), $prevEnd->toDateString()])
                ->whereIn('academic_attendance_details.status', ['present', 'half_day'])
                ->when(!empty($sessionVariants), function ($q) use ($sessionVariants) {
                    $q->whereIn('academic_attendance.academic_session', $sessionVariants);
                })
                ->when(true, function ($q) {
                    $this->applyWorkingDayOnlyFilter($q);
                });

            if ($onlyStudentId) {
                $prevPresentQuery->where('academic_attendance_details.student_id', $onlyStudentId);
            }

            $prevPresentCounts = $prevPresentQuery
                ->selectRaw('academic_attendance_details.student_id, SUM(CASE WHEN academic_attendance_details.status = "half_day" THEN 0.5 ELSE 1.0 END) as total_present')
                ->groupBy('academic_attendance_details.student_id')
                ->pluck('total_present', 'student_id');

            // School Working Days: Leave days must be counted as school working days
            $prevStudentWorkingQuery = AttendanceDetail::query()
                ->join('academic_attendance', 'academic_attendance_details.attendance_id', '=', 'academic_attendance.id')
                ->where('academic_attendance.is_delete', 0)
                ->whereIn('academic_attendance.class_id', $classIds)
                ->where('academic_attendance.section_name', $filters['section_name'])
                ->whereBetween('academic_attendance.attendance_date', [$prevStart->toDateString(), $prevEnd->toDateString()])
                ->whereIn('academic_attendance_details.status', ['present', 'half_day', 'absent', 'leave', 'pl'])
                ->when(!empty($sessionVariants), function ($q) use ($sessionVariants) {
                    $q->whereIn('academic_attendance.academic_session', $sessionVariants);
                })
                ->when(true, function ($q) {
                    $this->applyWorkingDayOnlyFilter($q);
                });

            if ($onlyStudentId) {
                $prevStudentWorkingQuery->where('academic_attendance_details.student_id', $onlyStudentId);
            }

            $prevStudentWorkingCounts = $prevStudentWorkingQuery
                ->selectRaw('academic_attendance_details.student_id, COUNT(DISTINCT academic_attendance.attendance_date) as working_days')
                ->groupBy('academic_attendance_details.student_id')
                ->pluck('working_days', 'student_id');
        }

        // Fetch latest roll numbers generated from Roll No Tool (academic_student_roll_no)
        $rollMap = collect();
        $studentIds = $students->pluck('id')->filter()->values();
        if ($studentIds->isNotEmpty()) {
            $hasRollTable = Schema::connection('dynamic')->hasTable('academic_student_roll_no');
            $rollConn = $hasRollTable
                ? 'dynamic'
                : (Schema::hasTable('academic_student_roll_no') ? Config::get('database.default') : null);

            if ($rollConn) {
                $rollRows = DB::connection($rollConn)
                    ->table('academic_student_roll_no')
                    ->whereIn('student_id', $studentIds)
                    ->whereNotNull('roll_no')
                    ->where('roll_no', '!=', '')
                    ->orderBy('id', 'desc')
                    ->get(['student_id', 'roll_no']);

                $rollMap = $rollRows->unique('student_id')->pluck('roll_no', 'student_id');
            }
        }

        $rows = $students->map(function ($student) use ($days, $month, $attendanceRecords, $nonWorkingDays, $prevStudentWorkingCounts, $prevPresentCounts, $rollMap) {
            $regDate = $this->resolveStudentRegistrationDate($student);
            $presentDays = 0.0;
            $workingDays = 0;
            $cells = [];

            foreach ($days as $day) {
                $date = $month->copy()->day($day)->toDateString();

                // If student was not yet admitted on this date, show not admitted indicator
                if (!empty($regDate) && $date < $regDate) {
                    $cells[$day] = [
                        'label' => '-',
                        'class' => 'cell-empty day-not-admitted',
                        'type' => 'not_admitted',
                        'title' => 'Not yet admitted (Admitted: ' . Carbon::parse($regDate)->format('d-m-Y') . ')',
                    ];
                    continue;
                }

                $nonWorking = $nonWorkingDays[$date] ?? null;

                if ($nonWorking) {
                    $isSunday = ($nonWorking['type'] === 'sunday');
                    $cells[$day] = [
                        'label' => $isSunday ? 'S' : 'H',
                        'class' => $isSunday ? 'cell-sunday day-sunday' : 'cell-holiday day-holiday',
                        'type' => $nonWorking['type'],
                        'title' => $nonWorking['label'],
                    ];
                    continue;
                }

                $attendance = $attendanceRecords->get($date);
                if (!$attendance) {
                    $cells[$day] = [
                        'label' => '',
                        'class' => 'cell-empty day-empty',
                        'type' => 'empty',
                        'title' => 'Attendance not taken',
                    ];
                    continue;
                }

                $detail = $attendance->details->firstWhere('student_id', $student->id);
                $status = optional($detail)->status;
                $label = $this->statusShortLabel($status);
                $cells[$day] = [
                    'label' => $label,
                    'class' => 'cell-' . ($status ?: 'empty') . ' day-' . ($status ?: 'empty'),
                    'type' => $status ?: 'empty',
                    'title' => $status ? ucwords(str_replace('_', ' ', $status)) : 'Not marked',
                ];

                // School Working Days: Leave days must be counted as school working days
                if (in_array($status, ['present', 'half_day', 'absent', 'leave', 'pl'])) {
                    $workingDays++;
                }

                if ($status === 'present') {
                    $presentDays += 1.0;
                } elseif ($status === 'half_day') {
                    $presentDays += 0.5;
                }
            }

            $percentage = $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 2) : 0.00;

            $prevWorking = (float) ($prevStudentWorkingCounts[$student->id] ?? 0);
            $prevPresent = (float) ($prevPresentCounts[$student->id] ?? 0);
            $prevPercentage = $prevWorking > 0 ? round(($prevPresent / $prevWorking) * 100, 2) : 0.00;

            $overallWorking = $workingDays + $prevWorking;
            $overallPresent = $presentDays + $prevPresent;
            $overallPercentage = $overallWorking > 0 ? round(($overallPresent / $overallWorking) * 100, 2) : 0.00;

            $latestRoll = $rollMap->get($student->id) ?? $rollMap->get((string) $student->id);
            $resolvedRollNo = !empty($latestRoll)
                ? (string) $latestRoll
                : (string) ($student->roll_no ?? $student->roll_number ?? '');

            return [
                'student_id' => $student->id,
                'student_name' => $student->student_name,
                'roll_no' => $resolvedRollNo,
                'scholar_no' => $student->scholar_no ?: ($student->form_number ?: $student->id),
                'cells' => $cells,
                'present_days' => $presentDays,
                'working_days' => $workingDays,
                'percentage' => $percentage,
                'current_month' => [
                    'days' => (float) $workingDays,
                    'present' => (float) $presentDays,
                    'percentage' => (float) $percentage,
                ],
                'till_prev_month' => [
                    'days' => (float) $prevWorking,
                    'present' => (float) $prevPresent,
                    'percentage' => (float) $prevPercentage,
                ],
                'overall' => [
                    'days' => (float) $overallWorking,
                    'present' => (float) $overallPresent,
                    'percentage' => (float) $overallPercentage,
                ],
            ];
        })->values();

        return [
            'month_label' => $month->format('F Y'),
            'month_short' => $month->format('M'),
            'month_num' => (int) $month->format('m'),
            'year' => (int) $month->format('Y'),
            'days' => $days,
            'days_in_month' => $daysInMonth,
            'rows' => $rows,
            'prev_working_days' => $prevWorkingDays,
        ];
    }

    private function nonWorkingDaysForRange(Carbon $start, Carbon $end): array
    {
        $days = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isSunday()) {
                $days[$date->toDateString()] = [
                    'type' => 'sunday',
                    'label' => 'Sunday / Weekly Off',
                ];
            }
        }

        $holidays = Holidays::forAcademic()
            ->whereDate('HolidayStartDate', '<=', $end)
            ->where(function ($query) use ($start) {
                $query->whereDate('HolidayEndDate', '>=', $start)
                    ->orWhereNull('HolidayEndDate');
            })
            ->get();

        foreach ($holidays as $holiday) {
            $holidayStart = Carbon::parse($holiday->HolidayStartDate);
            if ($holidayStart->lt($start)) {
                $holidayStart = $start->copy();
            }

            $holidayEnd = $holiday->HolidayEndDate ? Carbon::parse($holiday->HolidayEndDate) : Carbon::parse($holiday->HolidayStartDate);
            if ($holidayEnd->gt($end)) {
                $holidayEnd = $end->copy();
            }

            for ($date = $holidayStart->copy(); $date->lte($holidayEnd); $date->addDay()) {
                $days[$date->toDateString()] = [
                    'type' => 'holiday',
                    'label' => 'Holiday: ' . ($holiday->HolidayName ?: 'Holiday'),
                ];
            }
        }

        return $days;
    }

    private function statusShortLabel(?string $status): string
    {
        return match ($status) {
            'present' => 'P',
            'absent' => 'A',
            'leave' => 'L',
            'half_day' => 'HD',
            'pl' => 'PL',
            default => '',
        };
    }

    private function classes(bool $classTeacherOnly = false)
    {
        $query = Classes::query()->orderBy('class_name');

        if ($this->isStaffAttendanceUser()) {
            $classIds = $this->accessibleTeacherSubjects($classTeacherOnly)->pluck('class_id')->unique()->values();
            $classNames = Classes::query()->whereIn('id', $classIds)->pluck('class_name')
                ->merge(Classname::query()->whereIn('id', $classIds)->pluck('class_name'))
                ->filter()
                ->unique();

            $query->where(function ($q) use ($classIds, $classNames) {
                $q->whereIn('id', $classIds)
                    ->orWhereIn('class_name', $classNames);
            });
        }

        return app(\App\Services\StaffReportAccessService::class)
            ->filterClassOptions($query->get())
            ->unique('class_name')
            ->values();
    }

    private function resolveClass($classId)
    {
        $class = Classes::query()->find($classId);
        if (!$class) {
            $class = Classes::query()->where('class_name', $classId)->first();
        }

        if (!$class && class_exists(Classname::class)) {
            $class = Classname::query()->find($classId);
            if (!$class) {
                $class = Classname::query()->where('class_name', $classId)->first();
            }
        }

        return $class;
    }

    private function sections($classId, bool $classTeacherOnly = false)
    {
        if (!$classId) {
            return collect();
        }

        $selectedClass = $this->resolveClass($classId);
        $className = optional($selectedClass)->class_name ?: (string) $classId;

        $classRows = Classes::query()
            ->where('class_name', $className)
            ->get();

        $sections = $classRows->pluck('section_name')
            ->filter()
            ->flatMap(fn ($section) => array_map('trim', explode(',', $section)));

        $studentSections = Student_registration::query()
            ->where(function ($q) use ($classId, $className, $classRows) {
                $q->where('class_name', $className)
                    ->orWhere('class_name', $classId)
                    ->orWhereIn('class_id', $classRows->pluck('id'));
            })
            ->whereNotNull('section_name')
            ->where('section_name', '!=', '')
            ->pluck('section_name');

        $allSections = $sections->concat($studentSections)
            ->filter()
            ->map(fn ($s) => trim($s))
            ->filter(fn ($s) => $s !== '')
            ->unique();

        $standardSections = ['Aryabhatta', 'Kautilya', 'Ramanujan'];
        $matchingStandard = $allSections->filter(fn ($s) => in_array($s, $standardSections))->values();
        $effectiveSections = $matchingStandard->isNotEmpty() ? $matchingStandard : $allSections;

        $sortedSections = $effectiveSections->sortBy(function ($sec) use ($standardSections) {
            $idx = array_search($sec, $standardSections);
            return $idx === false ? 999 . $sec : sprintf('%03d', $idx);
        })->values();

        if ($this->isStaffAttendanceUser()) {
            $classIds = $classRows->pluck('id')->push($classId)->unique()->values();
            $allowedSections = $this->accessibleTeacherSubjects($classTeacherOnly)
                ->where(function ($q) use ($classIds, $className, $classId) {
                    $q->whereIn('class_id', $classIds)
                        ->orWhere('class_id', $className)
                        ->orWhere('class_id', $classId);
                })
                ->pluck('section_name')
                ->filter()
                ->values();

            if (!$classTeacherOnly && $allowedSections->contains('All')) {
                return $sortedSections;
            }

            if (!$allowedSections->contains('All')) {
                $sortedSections = $sortedSections->filter(fn ($s) => $allowedSections->contains($s))->values();
            } else {
                $sortedSections = collect();
            }
        }

        return $sortedSections;
    }

    private function allDistinctSections(bool $classTeacherOnly = false)
    {
        $classSections = Classes::query()->pluck('section_name')
            ->filter()
            ->flatMap(fn ($s) => array_map('trim', explode(',', $s)));

        $studentSections = Student_registration::query()
            ->whereNotNull('section_name')
            ->where('section_name', '!=', '')
            ->distinct()
            ->pluck('section_name');

        $all = $classSections->concat($studentSections)
            ->filter()
            ->map(fn ($s) => trim($s))
            ->filter(fn ($s) => $s !== '')
            ->unique();

        $standardSections = ['Aryabhatta', 'Kautilya', 'Ramanujan'];
        $matchingStandard = $all->filter(fn ($s) => in_array($s, $standardSections))->values();
        $effective = $matchingStandard->isNotEmpty() ? $matchingStandard : $all;

        $sorted = $effective->sortBy(function ($sec) use ($standardSections) {
            $idx = array_search($sec, $standardSections);
            return $idx === false ? 999 . $sec : sprintf('%03d', $idx);
        })->values();

        if ($sorted->isEmpty()) {
            $sorted = collect($standardSections);
        }

        if ($this->isStaffAttendanceUser()) {
            $allowedSections = $this->accessibleTeacherSubjects($classTeacherOnly)
                ->pluck('section_name')
                ->filter()
                ->values();

            if (!$classTeacherOnly && $allowedSections->contains('All')) {
                return $sorted;
            }

            if (!$allowedSections->contains('All') && $allowedSections->isNotEmpty()) {
                return $sorted->filter(fn ($s) => $allowedSections->contains($s))->values();
            }
        }

        return $sorted;
    }

    private function studentsForClass($classId, ?string $sectionName = null, bool $strictSectionClassMatch = false)
    {
        $class = $this->resolveClass($classId);
        $className = optional($class)->class_name ?: (string) $classId;
        $classRows = Classes::query()->where('class_name', $className)->get();
        $classIds = $classRows->pluck('id')->push($classId)->unique()->values();

        if ($strictSectionClassMatch && !$sectionName) {
            $classRowIds = $classRows->pluck('id');
            if ($classRowIds->isEmpty()) {
                $classRowIds->push($classId);
            }

            return Student_registration::query()
                ->where(function ($query) use ($className, $classRowIds) {
                    $query->where('class_name', $className)
                        ->orWhereIn('class_id', $classRowIds);
                })
                ->orderBy('student_name')
                ->select('id', 'form_number', 'scholar_no', 'student_name', 'class_name', 'section_name', 'session_name', 'registration_date', 'json_str');
        }

        if ($strictSectionClassMatch && $sectionName) {
            $sectionClassIds = $classRows
                ->filter(function ($row) use ($sectionName) {
                    $sections = array_values(array_filter(
                        array_map('trim', explode(',', (string) $row->section_name)),
                        fn ($section) => $section !== ''
                    ));
                    return count($sections) === 1 && $sections[0] === $sectionName;
                })
                ->pluck('id')
                ->values();

            $sectionMatches = function ($query) use ($sectionName) {
                $query->where('section_name', $sectionName)
                    ->orWhere('json_str', 'like', '%"section_name":"' . $sectionName . '"%')
                    ->orWhere('json_str', 'like', '%"section_name": "' . $sectionName . '"%');
            };

            return Student_registration::query()
                ->where(function ($query) use ($sectionClassIds, $className, $sectionMatches) {
                    if ($sectionClassIds->isNotEmpty()) {
                        $query->where(function ($bySectionClass) use ($sectionClassIds, $sectionMatches) {
                            $bySectionClass->whereIn('class_id', $sectionClassIds)
                                ->where(function ($sectionQuery) use ($sectionMatches) {
                                    $sectionQuery->where($sectionMatches)
                                        ->orWhere(function ($unassigned) {
                                            $unassigned->where(function ($emptySection) {
                                                $emptySection->whereNull('section_name')
                                                    ->orWhere('section_name', '');
                                            })->where(function ($emptyJson) {
                                                $emptyJson->whereNull('json_str')
                                                    ->orWhere('json_str', '')
                                                    ->orWhere('json_str', 'not like', '%section_name%');
                                            });
                                        });
                                });
                        });
                    }

                    $query->orWhere(function ($legacy) use ($className, $sectionMatches) {
                        $legacy->where('class_name', $className)
                            ->where($sectionMatches);
                    });
                })
                ->orderBy('student_name')
                ->select('id', 'form_number', 'scholar_no', 'student_name', 'class_name', 'section_name', 'session_name', 'registration_date', 'json_str');
        }

        return Student_registration::query()
            ->where(function ($query) use ($classId, $className, $classIds) {
                $query->where('class_name', $classId)
                    ->orWhere('class_name', $className)
                    ->orWhereIn('class_id', $classIds);
            })
            ->when($sectionName, function ($query) use ($sectionName) {
                $query->where(function ($inner) use ($sectionName) {
                    $inner->where('section_name', $sectionName)
                        ->orWhere('json_str', 'like', '%"section_name":"' . $sectionName . '"%')
                        ->orWhere('json_str', 'like', '%"section_name": "' . $sectionName . '"%');
                });
            })
            ->orderBy('student_name')
            ->select('id', 'form_number', 'scholar_no', 'student_name', 'class_name', 'section_name', 'session_name', 'registration_date', 'json_str');
    }

    private function studentOptions($classId, ?string $sectionName, string $search)
    {
        $query = Student_registration::query();
        if ($this->isStaffAttendanceUser() && !$classId) {
            $studentIds = $this->accessibleTeacherSubjects()
                ->get(['class_id', 'section_name'])
                ->flatMap(function ($assignment) {
                    $section = trim((string) $assignment->section_name);
                    return $this->studentsForClass(
                        $assignment->class_id,
                        strcasecmp($section, 'All') === 0 ? null : $section,
                        true
                    )->pluck('id');
                })
                ->unique()
                ->values();
            $query->whereIn('id', $studentIds->isNotEmpty() ? $studentIds : [0]);
        }

        return $query
            ->when($classId, function ($query) use ($classId, $sectionName) {
                $ids = $this->studentsForClass($classId, $sectionName, $this->isStaffAttendanceUser())->pluck('id');
                $query->whereIn('id', $ids);
            })
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . str_replace(' ', '%', $search) . '%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('student_name', 'like', $like)
                        ->orWhere('form_number', 'like', $like)
                        ->orWhere('scholar_no', 'like', $like);
                });
            })
            ->orderBy('student_name')
            ->limit(100)
            ->get(['id', 'student_name', 'form_number', 'scholar_no']);
    }

    private function applyAttendanceFilters($query, array $filters): void
    {
        $query->when($filters['class_id'], function ($q) use ($filters) {
            $class = $this->resolveClass($filters['class_id']);
            $className = optional($class)->class_name ?: (string) $filters['class_id'];
            $classIds = Classes::query()->where('class_name', $className)->pluck('id')->push($filters['class_id'])->unique()->values();

            $q->where(function ($inner) use ($filters, $className, $classIds) {
                $inner->whereIn('class_id', $classIds)
                    ->orWhere('class_id', $filters['class_id'])
                    ->orWhere('class_name', $className)
                    ->orWhere('class_name', $filters['class_id']);
            });
        })
        ->when($filters['section_name'], fn ($q) => $q->where('section_name', $filters['section_name']));

        if (!empty($filters['academic_session'])) {
            $session = $filters['academic_session'];
            $variants = array_unique([
                $session,
                str_replace('_', '-', $session),
                str_replace('-', '_', $session),
            ]);

            $hasMatching = DB::connection('dynamic')->table('academic_attendance')
                ->where('is_delete', 0)
                ->whereIn('academic_session', $variants)
                ->exists();

            if ($hasMatching) {
                $query->whereIn('academic_session', $variants);
            }
        }

        if ($filters['from_date'] || $filters['to_date']) {
            $query->when($filters['from_date'], fn ($q) => $q->whereDate('attendance_date', '>=', $filters['from_date']))
                ->when($filters['to_date'], fn ($q) => $q->whereDate('attendance_date', '<=', $filters['to_date']));
        } elseif (!empty($filters['month'])) {
            $date = Carbon::createFromFormat('Y-m', $filters['month']);
            $query->whereBetween('attendance_date', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()]);
        }

        $this->applyWorkingDayOnlyFilter($query);
    }

    private function summaryFromDetails($details): array
    {
        $collection = collect($details);
        $counts = $collection->countBy('status');
        $pl = (int) ($counts['pl'] ?? 0);
        $leave = (int) ($counts['leave'] ?? 0);
        $present = (int) ($counts['present'] ?? 0);
        $halfDay = (int) ($counts['half_day'] ?? 0);
        $absent = (int) ($counts['absent'] ?? 0);

        // School Working Days: Leave days must be counted as school working days
        $schoolWorkingDays = $collection->count();

        // Student Working Days: Leave days must not be counted as individual student's working days
        $studentWorkingDays = max(0, $schoolWorkingDays - $pl - $leave);

        return [
            'total_students' => $schoolWorkingDays,
            'school_working_days' => $schoolWorkingDays,
            'student_working_days' => $studentWorkingDays,
            'present' => $present,
            'absent' => $absent + $leave,
            'leave' => $leave,
            'half_day' => $halfDay,
            'pl' => $pl,
            'percentage' => $studentWorkingDays ? round((($present + ($halfDay * 0.5)) / $studentWorkingDays) * 100, 2) : 0,
        ];
    }

    private function emptySummary(int $total): array
    {
        return [
            'total_students' => $total,
            'school_working_days' => $total,
            'student_working_days' => $total,
            'present' => 0,
            'absent' => 0,
            'leave' => 0,
            'half_day' => 0,
            'pl' => 0,
            'percentage' => 0,
        ];
    }

    private function defaultSession(?Request $request = null): string
    {
        $request = $request ?: request();
        $selected = $request->query('academic_session')
            ?: ($request->cookie('selectedYear')
                ?: ($_COOKIE['selectedYear'] ?? null)
                ?: ($request->hasSession() ? $request->session()->get('selectedYear') : null)
                ?: ($request->hasSession() ? $request->session()->get('db_names') : null)
                ?: Config::get('database.connections.dynamic.database')
                ?: Config::get('database.connections.mysql.database'));

        if (is_string($selected) && preg_match('/^(\d{4})[-_](\d{4})$/', trim($selected), $m)) {
            return $m[1] . '-' . $m[2];
        }

        if (is_string($selected) && preg_match('/^(\d{4})/', trim($selected), $m)) {
            $start = (int) $m[1];
            return $start . '-' . ($start + 1);
        }

        $year = (int) now()->format('Y');
        $month = (int) now()->format('m');
        $start = $month >= 4 ? $year : $year - 1;

        return $start . '-' . ($start + 1);
    }

    private function nonWorkingDayInfo(?string $date): ?array
    {
        if (!$date) {
            return null;
        }

        $attendanceDate = Carbon::parse($date);
        if ($attendanceDate->isSunday()) {
            return [
                'type' => 'sunday',
                'label' => 'Sunday / Weekly Off',
            ];
        }

        $holiday = Holidays::forAcademic()
            ->whereDate('HolidayStartDate', '<=', $attendanceDate)
            ->where(function ($query) use ($attendanceDate) {
                $query->whereDate('HolidayEndDate', '>=', $attendanceDate)
                    ->orWhereNull('HolidayEndDate');
            })
            ->first();

        if ($holiday) {
            return [
                'type' => 'holiday',
                'label' => 'Public Holiday: ' . ($holiday->HolidayName ?: 'Holiday'),
            ];
        }

        return null;
    }

    private function isFutureAttendanceDate(?string $date): bool
    {
        return !empty($date) && Carbon::parse($date)->startOfDay()->gt(Carbon::today());
    }

    private function applyWorkingDayOnlyFilter($query): void
    {
        $query->whereRaw('DAYOFWEEK(academic_attendance.attendance_date) <> 1')
            ->whereNotExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('hrms_holidays')
                    ->whereNull('hrms_holidays.deleted_at')
                    ->where('hrms_holidays.for_academic', 1)
                    ->whereRaw('hrms_holidays.HolidayStartDate <= academic_attendance.attendance_date')
                    ->where(function ($query) {
                        $query->whereRaw('hrms_holidays.HolidayEndDate >= academic_attendance.attendance_date')
                            ->orWhereNull('hrms_holidays.HolidayEndDate');
                    });
            });
    }

    private function isStaffAttendanceUser(): bool
    {
        if (class_exists(\App\Services\PermissionService::class) && \App\Services\PermissionService::isAdmin()) {
            return false;
        }

        if (Auth::guard('staff')->check()) {
            return true;
        }

        $user = Auth::guard('web')->user() ?: Auth::user();
        if ($user) {
            $role = strtolower(trim((string) ($user->role ?? '')));
            if ($role === 'academic' || $role === 'teacher' || !empty($user->employee_id)) {
                return true;
            }
        }

        return false;
    }

    private function isMonthLockedForAttendance(Attendance $attendance): bool
    {
        $date = Carbon::parse($attendance->attendance_date);

        return AttendanceMonthlyLock::isMonthLocked(
            $attendance->class_id,
            $attendance->section_name,
            (int) $date->format('Y'),
            (int) $date->format('m')
        );
    }

    private function staffEmployeeId(): ?int
    {
        if (!$this->isStaffAttendanceUser()) {
            return null;
        }

        $user = Auth::guard('staff')->user() ?: Auth::guard('web')->user() ?: Auth::user();
        return $user && !empty($user->employee_id) ? (int) $user->employee_id : null;
    }

    private function accessibleTeacherSubjects(bool $classTeacherOnly = false)
    {
        $query = TeacherSubject::where('is_delete', 0);

        if ($this->isStaffAttendanceUser()) {
            $query->where('teacher_id', $this->staffEmployeeId());
        }

        if ($classTeacherOnly) {
            $hasExplicitClassTeacher = (clone $query)->whereRaw('LOWER(TRIM(role)) = ?', ['class teacher'])->exists();
            if ($hasExplicitClassTeacher) {
                $query->whereRaw('LOWER(TRIM(role)) = ?', ['class teacher']);
            }
        }

        return $query;
    }

    private function accessibleAttendanceQuery(bool $classTeacherOnly = true)
    {
        $query = Attendance::query();

        if ($this->isStaffAttendanceUser()) {
            $teacherId = $this->staffEmployeeId();
            if (!$teacherId) {
                return $query->whereRaw('1 = 0');
            }

            // Each teacher should only be able to view their own attendance records
            $query->where(function ($q) use ($teacherId) {
                $q->where('Teacher_id', $teacherId)
                    ->orWhere('teacher_id', $teacherId);
            });

            $subjects = $this->accessibleTeacherSubjects($classTeacherOnly)
                ->get(['class_id', 'section_name'])
                ->groupBy('class_id');

            if ($subjects->isEmpty()) {
                return $query;
            }

            $query->where(function ($outer) use ($subjects) {
                foreach ($subjects as $classId => $rows) {
                    $class = $this->resolveClass($classId);
                    $className = optional($class)->class_name ?: (string) $classId;
                    $classIds = Classes::query()->where('class_name', $className)->pluck('id')->push($classId)->unique()->values();
                    $sections = $rows->pluck('section_name')->filter()->unique()->values();

                    $outer->orWhere(function ($inner) use ($classIds, $className, $classId, $sections) {
                        $inner->where(function ($classQuery) use ($classIds, $className, $classId) {
                            $classQuery->whereIn('class_id', $classIds)
                                ->orWhere('class_id', $className)
                                ->orWhere('class_id', $classId)
                                ->orWhere('class_name', $className);
                        });
                        if ($sections->isNotEmpty() && !$sections->contains('All')) {
                            $inner->whereIn('section_name', $sections);
                        }
                    });
                }
            });
        }

        return $query;
    }

    private function canStaffAccessClassSection($classId, ?string $sectionName, bool $classTeacherOnly = false): bool
    {
        if (!$this->isStaffAttendanceUser()) {
            return true;
        }

        if (!$classId || !$sectionName) {
            return false;
        }

        $class = $this->resolveClass($classId);
        $className = optional($class)->class_name ?: (string) $classId;
        $classIds = Classes::query()->where('class_name', $className)->pluck('id')->push($classId)->unique()->values();

        $assignments = $this->accessibleTeacherSubjects($classTeacherOnly)
            ->where(function ($query) use ($classIds, $className, $classId) {
                $query->whereIn('class_id', $classIds)
                    ->orWhere('class_id', $className)
                    ->orWhere('class_id', $classId);
            });

        if ($classTeacherOnly) {
            return $assignments->where('section_name', $sectionName)->exists();
        }

        return $assignments
            ->where(function ($query) use ($sectionName) {
                $query->where('section_name', $sectionName)->orWhere('section_name', 'All');
            })
            ->exists();
    }

    private function isLockedForStaff(Attendance $attendance): bool
    {
        return $this->isStaffAttendanceUser() && (bool) $attendance->is_locked;
    }

    private function lockedAttendanceMessage(): string
    {
        return 'This attendance is locked. Admin must unlock it before edit or delete.';
    }

    private function lockedMonthlyAttendanceMessage(): string
    {
        return 'Attendance for this month is locked. An administrator must unlock it before changes can be made.';
    }

    /**
     * Download a sample CSV template for daily attendance bulk import.
     */
    public function sampleCsv(Request $request)
    {
        $isStaffUser = $this->isStaffAttendanceUser();
        $classId = $request->query('class_id');
        $sectionName = $request->query('section_name');
        $month = (int) ($request->query('month') ?: now()->format('m'));
        $year = (int) ($request->query('year') ?: now()->format('Y'));

        if ($isStaffUser) {
            if (empty($classId) || empty($sectionName) || !$this->canStaffAccessClassSection($classId, $sectionName, true)) {
                $assignment = $this->accessibleTeacherSubjects(true)->first();
                if ($assignment) {
                    $classId = $assignment->class_id;
                    $sectionName = $assignment->section_name;
                }
            }
        }

        $classObj = $classId ? $this->resolveClass($classId) : null;
        $className = optional($classObj)->class_name ?: (string) ($classId ?: 'Class');
        $sectionName = $sectionName ?: 'Section';

        $monthDate = Carbon::createFromDate($year, $month, 1);
        $monthName = $monthDate->format('F');
        $daysInMonth = $monthDate->daysInMonth;

        $students = ($classId && $sectionName)
            ? $this->studentsForClass($classId, $sectionName, true)->get()
            : collect();

        $cleanSecFile = preg_replace('/[^A-Za-z0-9_-]/', '_', $sectionName);
        $filename = "attendance_template_{$className}_{$cleanSecFile}_{$monthName}_{$year}.csv";

        return response()->streamDownload(function () use ($monthName, $year, $className, $sectionName, $daysInMonth, $monthDate, $students) {
            $handle = fopen('php://output', 'w');

            // Line 1: Metadata row
            $metaRow = array_fill(0, 35, '');
            $metaRow[0] = "Month : {$monthName} - {$year}";
            $metaRow[1] = "Class : {$className} {$sectionName}";
            fputcsv($handle, $metaRow);

            // Line 2: Header grouping
            $groupRow = array_fill(0, 35, '');
            $groupRow[0] = 'Sr';
            $groupRow[1] = 'Scholar No';
            $groupRow[2] = 'Student Name';
            $groupRow[3] = 'Days';
            fputcsv($handle, $groupRow);

            // Line 3: Day numbers (1 to 31)
            $dayRow = ['', '', ''];
            for ($d = 1; $d <= 31; $d++) {
                $dayRow[] = (string) $d;
            }
            fputcsv($handle, $dayRow);

            // Lines 4+: Students with Sundays pre-marked as 'S'
            $sr = 1;
            if ($students->isNotEmpty()) {
                foreach ($students as $student) {
                    $row = [$sr++, (string) ($student->scholar_no ?? ''), $student->student_name];
                    for ($d = 1; $d <= 31; $d++) {
                        if ($d <= $daysInMonth) {
                            $curDate = Carbon::createFromDate($year, $monthDate->month, $d);
                            $row[] = $curDate->isSunday() ? 'S' : '';
                        } else {
                            $row[] = '';
                        }
                    }
                    fputcsv($handle, $row);
                }
            } else {
                $sampleRow = [1, '1001', 'Sample Student Name'];
                for ($d = 1; $d <= 31; $d++) {
                    if ($d <= $daysInMonth) {
                        $curDate = Carbon::createFromDate($year, $monthDate->month, $d);
                        $sampleRow[] = $curDate->isSunday() ? 'S' : '';
                    } else {
                        $sampleRow[] = '';
                    }
                }
                fputcsv($handle, $sampleRow);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Import monthly daily attendance from CSV or Excel file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'attendance_file' => 'required|file|max:10240',
            'class_id' => 'nullable',
            'section_name' => 'nullable|string',
            'month_year' => 'nullable|string',
            'academic_session' => 'nullable|string',
            'teacher_id' => 'nullable|integer',
        ], [
            'attendance_file.required' => 'Please choose a CSV or Excel file to upload.',
        ]);

        $file = $request->file('attendance_file');
        $rows = $this->parseUploadedSpreadsheet($file);

        if (empty($rows)) {
            return back()->with('error', 'The uploaded file is empty or could not be processed.');
        }

        // 1. Detect Metadata from File Row 0
        $metaMonth = null;
        $metaYear = null;
        $metaClassName = null;
        $metaSectionName = null;

        $firstRow = $rows[0] ?? [];
        foreach ($firstRow as $cell) {
            $cellStr = trim((string) $cell);
            if (preg_match('/Month\s*:\s*([A-Za-z]+)\s*-\s*(\d{4})/i', $cellStr, $m)) {
                $time = strtotime($m[1] . ' 1 2000');
                if ($time !== false) {
                    $metaMonth = (int) date('m', $time);
                    $metaYear = (int) $m[2];
                }
            }
            if (preg_match('/Class\s*:\s*([^\s,]+)(?:\s+(.+))?/i', $cellStr, $m)) {
                $metaClassName = trim($m[1]);
                $metaSectionName = trim($m[2] ?? '');
            }
        }

        // 2. Resolve Year and Month: Auto-detect from file if present, otherwise fallback to form input
        $selectedMonthYear = $request->input('month_year');
        if ($metaYear && $metaMonth) {
            $year = $metaYear;
            $month = $metaMonth;
        } elseif ($selectedMonthYear && preg_match('/^(\d{4})-(\d{1,2})$/', $selectedMonthYear, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
        } else {
            $year = (int) now()->format('Y');
            $month = (int) now()->format('m');
        }

        $targetMonth = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $targetMonth->daysInMonth;

        // 3. Resolve Class and Section
        $isStaffUser = $this->isStaffAttendanceUser();
        $classId = $request->input('class_id');
        $sectionName = $request->input('section_name');

        if ($isStaffUser) {
            $assignedSubjects = $this->accessibleTeacherSubjects(true)->get();
            if ($assignedSubjects->isEmpty()) {
                return back()->with('error', 'You are not assigned as a class teacher for any class.');
            }

            // If CSV header contains a class, check if the staff user is authorized for that specific class
            if ($metaClassName) {
                [$resolvedMetaClassId, $resolvedMetaSec] = $this->normalizeClassAndSection($metaClassName, $metaSectionName);
                if ($resolvedMetaClassId && $resolvedMetaSec && $this->canStaffAccessClassSection($resolvedMetaClassId, $resolvedMetaSec, true)) {
                    $classId = $resolvedMetaClassId;
                    $sectionName = $resolvedMetaSec;
                }
            }

            // Default to active/first assignment if still empty
            if (empty($classId) || empty($sectionName)) {
                $firstAssignment = $assignedSubjects->first();
                $classId = $firstAssignment->class_id;
                $sectionName = $firstAssignment->section_name;
            }

            // Strict security check: ensure staff can only mark/import for their own assigned classes
            if (!$this->canStaffAccessClassSection($classId, $sectionName, true)) {
                return back()->with('error', 'You are only authorized to mark/import attendance for your assigned class(es).');
            }
        } else {
            // Admin: If not selected in form, resolve from file metadata
            if ((empty($classId) || empty($sectionName)) && $metaClassName) {
                [$resolvedClassId, $resolvedSec] = $this->normalizeClassAndSection($metaClassName, $metaSectionName);
                $classId = $classId ?: $resolvedClassId;
                $sectionName = $sectionName ?: $resolvedSec;
            }
        }

        if (empty($classId) || empty($sectionName)) {
            return back()->with('error', 'Please select both Class and Section to import attendance.');
        }

        $selectedClass = $this->resolveClass($classId);
        if (!$selectedClass) {
            return back()->with('error', 'Could not find the specified class in the database.');
        }
        $resolvedClassId = $selectedClass->id;
        $className = $selectedClass->class_name;

        // 4. Resolve Academic Session & Teacher
        $session = $request->input('academic_session') ?: $this->defaultSession($request);
        $teacherId = $isStaffUser ? $this->staffEmployeeId() : ($request->input('teacher_id') ?: null);

        // 5. Detect Day Columns (1..31)
        $dayColMap = [];
        $headerRowIdx = null;
        for ($r = 0; $r <= min(5, count($rows) - 1); $r++) {
            $colMap = [];
            foreach ($rows[$r] as $cIdx => $val) {
                $val = trim((string) $val);
                if (is_numeric($val) && (int) $val >= 1 && (int) $val <= 31) {
                    $colMap[(int) $val] = $cIdx;
                }
            }
            if (count($colMap) >= 15 || (isset($colMap[1], $colMap[2]) && ($colMap[2] - $colMap[1] === 1))) {
                $dayColMap = $colMap;
                $headerRowIdx = $r;
                break;
            }
        }

        if (empty($dayColMap) || $headerRowIdx === null) {
            return back()->with('error', 'Could not locate day columns (1 to 31) in the uploaded file.');
        }

        $firstDayCol = min($dayColMap);

        // Detect column roles (Scholar No, Student Name, Serial No) in header rows before Day 1
        $scholarColIdx = null;
        $nameColIdx = null;
        $srColIdx = null;

        for ($c = 0; $c < $firstDayCol; $c++) {
            for ($r = 0; $r <= $headerRowIdx; $r++) {
                $headerCell = strtolower(trim((string) ($rows[$r][$c] ?? '')));
                if ($scholarColIdx === null && preg_match('/scholar|admission|adm[\s._-]?no|reg[\s._-]?no/i', $headerCell)) {
                    $scholarColIdx = $c;
                }
                if ($nameColIdx === null && preg_match('/student|name/i', $headerCell) && !preg_match('/scholar/i', $headerCell)) {
                    $nameColIdx = $c;
                }
                if ($srColIdx === null && preg_match('/^(sr|s\.?no|serial|roll)/i', $headerCell)) {
                    $srColIdx = $c;
                }
            }
        }

        // Default heuristic if not explicitly labelled in headers
        if ($firstDayCol >= 3 && $scholarColIdx === null) {
            $scholarColIdx = 1;
            $nameColIdx = $nameColIdx ?? 2;
        } elseif ($firstDayCol === 2 && $nameColIdx === null) {
            $nameColIdx = 1;
        }

        // 6. Fetch Students for the Class
        $students = $this->studentsForClass($resolvedClassId, $sectionName, true)->get();
        if ($students->isEmpty()) {
            return back()->with('error', "No active students found for Class {$className} - {$sectionName}.");
        }

        $normalizeScholar = function ($val) {
            return strtolower(trim((string) $val));
        };
        $normalizeName = function ($val) {
            return preg_replace('/\s+/', ' ', strtolower(trim((string) $val)));
        };

        $byScholar = [];
        $byName = [];
        foreach ($students as $s) {
            $cleanName = $normalizeName($s->student_name);
            $byName[$cleanName] = $s;

            if (!empty($s->scholar_no)) {
                $cleanSch = $normalizeScholar($s->scholar_no);
                $byScholar[$cleanSch] = $s;
                $noLeadingZeros = ltrim($cleanSch, '0');
                if ($noLeadingZeros !== '' && $noLeadingZeros !== $cleanSch) {
                    $byScholar[$noLeadingZeros] = $s;
                }
            }
        }

        // 7. Parse Student Attendance Rows
        $studentAttendance = [];
        $unmatchedNames = [];

        for ($i = $headerRowIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            $prefixCells = [];
            for ($c = 0; $c < $firstDayCol; $c++) {
                $prefixCells[$c] = trim((string) ($row[$c] ?? ''));
            }

            $nonEmpty = array_filter($prefixCells, fn ($v) => $v !== '');
            if (empty($nonEmpty)) {
                continue;
            }

            $joined = strtolower(implode(' ', $prefixCells));
            if (preg_match('/^(total|days|student|month|wd|present|current month|previous month)/i', $joined)) {
                continue;
            }

            $matchedStudent = null;

            // Strategy 1: Match by detected Scholar No column
            if ($scholarColIdx !== null && !empty($prefixCells[$scholarColIdx])) {
                $schVal = $normalizeScholar($prefixCells[$scholarColIdx]);
                if (isset($byScholar[$schVal])) {
                    $matchedStudent = $byScholar[$schVal];
                }
            }

            // Strategy 2: Check all prefix cells against Scholar No
            if (!$matchedStudent) {
                foreach ($prefixCells as $cIdx => $cellVal) {
                    if ($cIdx === $srColIdx || $cellVal === '') {
                        continue;
                    }
                    $schVal = $normalizeScholar($cellVal);
                    if (isset($byScholar[$schVal])) {
                        // Prevent low serial numbers (e.g. 1, 2) from matching scholar no if another cell matches student name
                        if (is_numeric($cellVal) && (int) $cellVal <= 100) {
                            $hasOtherNameMatch = false;
                            foreach ($prefixCells as $otherIdx => $otherVal) {
                                if ($otherIdx !== $cIdx && isset($byName[$normalizeName($otherVal)])) {
                                    $hasOtherNameMatch = true;
                                    break;
                                }
                            }
                            if ($hasOtherNameMatch) {
                                continue;
                            }
                        }
                        $matchedStudent = $byScholar[$schVal];
                        break;
                    }
                }
            }

            // Strategy 3: Match by detected Name column
            if (!$matchedStudent && $nameColIdx !== null && !empty($prefixCells[$nameColIdx])) {
                $cleanName = $normalizeName($prefixCells[$nameColIdx]);
                if (isset($byName[$cleanName])) {
                    $matchedStudent = $byName[$cleanName];
                } else {
                    foreach ($byName as $k => $v) {
                        if (str_starts_with($cleanName, $k) || str_starts_with($k, $cleanName)) {
                            $matchedStudent = $v;
                            break;
                        }
                    }
                }
            }

            // Strategy 4: Fallback scan prefix cells for Name
            if (!$matchedStudent) {
                foreach ($prefixCells as $cellVal) {
                    if ($cellVal === '' || is_numeric($cellVal)) {
                        continue;
                    }
                    $cleanName = $normalizeName($cellVal);
                    if (isset($byName[$cleanName])) {
                        $matchedStudent = $byName[$cleanName];
                        break;
                    } else {
                        foreach ($byName as $k => $v) {
                            if (str_starts_with($cleanName, $k) || str_starts_with($k, $cleanName)) {
                                $matchedStudent = $v;
                                break;
                            }
                        }
                        if ($matchedStudent) {
                            break;
                        }
                    }
                }
            }

            if (!$matchedStudent) {
                $unmatchedNames[] = implode(' | ', $nonEmpty);
                continue;
            }

            foreach ($dayColMap as $dayNum => $colIdx) {
                if ($dayNum > $daysInMonth) {
                    continue;
                }
                $rawVal = strtoupper(trim((string) ($row[$colIdx] ?? '')));
                $studentAttendance[$matchedStudent->id][$dayNum] = $rawVal;
            }
        }

        if (empty($studentAttendance)) {
            $msg = 'No student attendance data could be matched from the uploaded file.';
            if (!empty($unmatchedNames)) {
                $msg .= ' Unmatched names / rows: ' . implode(', ', array_slice($unmatchedNames, 0, 5));
            }
            return back()->with('error', $msg);
        }

        // 8. Identify Working Days & Save to Database
        $importedDays = 0;
        $updatedDates = [];
        $skippedFutureDays = 0;
        $skippedLockedDays = 0;
        $skippedNonWorkingDays = 0;

        DB::transaction(function () use (
            $daysInMonth,
            $year,
            $month,
            $resolvedClassId,
            $className,
            $sectionName,
            $session,
            $teacherId,
            $students,
            $studentAttendance,
            $isStaffUser,
            &$importedDays,
            &$updatedDates,
            &$skippedFutureDays,
            &$skippedLockedDays,
            &$skippedNonWorkingDays
        ) {
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);

                $hasPunches = false;
                $dayPunches = [];

                foreach ($students as $stu) {
                    $raw = $studentAttendance[$stu->id][$d] ?? '';
                    $status = match ($raw) {
                        'P', 'PRESENT' => 'present',
                        'A', 'ABSENT' => 'absent',
                        'L', 'LEAVE' => 'leave',
                        'PL' => 'pl',
                        'HD', 'HALF_DAY', 'HALF DAY' => 'half_day',
                        default => null,
                    };

                    if ($status) {
                        $hasPunches = true;
                        $dayPunches[$stu->id] = $status;
                    }
                }

                if (!$hasPunches) {
                    continue;
                }

                // Guard against accidental attendance marked on Sundays or Gazetted Holidays
                $nonWorkingDay = $this->nonWorkingDayInfo($dateStr);
                if ($nonWorkingDay) {
                    $skippedNonWorkingDays++;
                    continue;
                }

                if ($this->isFutureAttendanceDate($dateStr)) {
                    $skippedFutureDays++;
                    continue;
                }

                if ($isStaffUser && AttendanceMonthlyLock::isMonthLocked($resolvedClassId, $sectionName, $year, $month)) {
                    $skippedLockedDays++;
                    continue;
                }

                $existing = Attendance::where('is_delete', 0)
                    ->whereDate('attendance_date', $dateStr)
                    ->where('class_id', $resolvedClassId)
                    ->where('section_name', $sectionName)
                    ->first();

                if ($existing && $existing->is_locked && $isStaffUser) {
                    $skippedLockedDays++;
                    continue;
                }

                $counts = collect($dayPunches)->countBy();
                $leaveCount = (int) ($counts['leave'] ?? 0);

                $attendance = Attendance::updateOrCreate(
                    [
                        'id' => optional($existing)->id,
                        'attendance_date' => $dateStr,
                        'class_id' => $resolvedClassId,
                        'section_name' => $sectionName,
                    ],
                    [
                        'Teacher_id' => $teacherId,
                        'teacher_id' => $teacherId,
                        'class_name' => $className,
                        'academic_session' => $session,
                        'total_students' => count($dayPunches),
                        'present_count' => (int) ($counts['present'] ?? 0),
                        'absent_count' => (int) ($counts['absent'] ?? 0) + $leaveCount,
                        'leave_count' => $leaveCount,
                        'half_day_count' => (int) ($counts['half_day'] ?? 0),
                        'pl_count' => (int) ($counts['pl'] ?? 0),
                        'is_delete' => 0,
                    ]
                );

                foreach ($dayPunches as $studentId => $status) {
                    AttendanceDetail::updateOrCreate(
                        ['attendance_id' => $attendance->id, 'student_id' => $studentId],
                        ['status' => $status]
                    );
                }

                $importedDays++;
                $updatedDates[] = $dateStr;
            }
        });

        if ($importedDays === 0) {
            return back()->with('error', 'No working attendance days could be imported (dates were either future dates, locked, or marked as off-days).');
        }

        $matchedCount = count($studentAttendance);
        $totalClassStudents = $students->count();
        $monthName = $targetMonth->format('F Y');

        $successMsg = "Successfully imported {$importedDays} working days of attendance for Class {$className} - {$sectionName} ({$monthName}). Matched {$matchedCount} of {$totalClassStudents} students.";
        if (!empty($unmatchedNames)) {
            $successMsg .= " Note: " . count($unmatchedNames) . " name(s) in CSV could not be matched (" . implode(', ', array_slice(array_unique($unmatchedNames), 0, 3)) . ").";
        }
        if ($skippedLockedDays > 0) {
            $successMsg .= " Skipped {$skippedLockedDays} locked day(s).";
        }
        if ($skippedFutureDays > 0) {
            $successMsg .= " Skipped {$skippedFutureDays} future day(s).";
        }
        if ($skippedNonWorkingDays > 0) {
            $successMsg .= " Skipped {$skippedNonWorkingDays} non-working day(s) (Sundays / Holidays).";
        }

        return redirect()->route('dailyattandence', [
            'class_id' => $resolvedClassId,
            'section_name' => $sectionName,
            'date' => end($updatedDates) ?: $targetMonth->toDateString(),
            'academic_session' => $session,
        ])->with('success', $successMsg);
    }

    private function normalizeClassAndSection(?string $classStr, ?string $sectionStr): array
    {
        if (!$classStr) {
            return [null, null];
        }

        $cleanClass = trim($classStr);
        $romanMap = [
            'i' => '01', 'ii' => '02', 'iii' => '03', 'iv' => '04', 'v' => '05',
            'vi' => '06', 'vii' => '07', 'viii' => '08', 'ix' => '09', 'x' => '10',
            'xi' => '11', 'xii' => '12',
            '1' => '01', '2' => '02', '3' => '03', '4' => '04', '5' => '05',
            '6' => '06', '7' => '07', '8' => '08', '9' => '09',
        ];

        $mappedClassName = $romanMap[strtolower($cleanClass)] ?? $cleanClass;

        $class = Classes::query()
            ->where('class_name', $mappedClassName)
            ->orWhere('class_name', $cleanClass)
            ->orWhere('id', $cleanClass)
            ->first();

        $classId = optional($class)->id;
        $className = optional($class)->class_name ?: $mappedClassName;

        $cleanSec = trim((string) $sectionStr);
        $matchedSec = $cleanSec;

        if ($cleanSec !== '') {
            $sections = $this->sections($classId ?: $className);
            foreach ($sections as $s) {
                if (strcasecmp($s, $cleanSec) === 0 || str_starts_with(strtolower($s), strtolower($cleanSec)) || str_starts_with(strtolower($cleanSec), strtolower($s))) {
                    $matchedSec = $s;
                    break;
                }
            }
        }

        return [$classId ?: $className, $matchedSec];
    }

    private function parseUploadedSpreadsheet($file): array
    {
        $path = $file->getRealPath();
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext === 'xlsx') {
            return $this->parseXlsxContent($path);
        }

        return $this->parseCsvContent($path);
    }

    private function parseCsvContent(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }
        return $rows;
    }

    private function parseXlsxContent(string $path): array
    {
        if (!class_exists(\ZipArchive::class)) {
            return $this->parseCsvContent($path);
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = [];
        if (($xmlIndex = $zip->locateName('xl/sharedStrings.xml')) !== false) {
            $xmlData = $zip->getFromIndex($xmlIndex);
            $sxml = simplexml_load_string($xmlData);
            if ($sxml && isset($sxml->si)) {
                foreach ($sxml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        $sheetIndex = $zip->locateName('xl/worksheets/sheet1.xml');
        if ($sheetIndex === false) {
            $zip->close();
            return [];
        }

        $sheetXml = simplexml_load_string($zip->getFromIndex($sheetIndex));
        $zip->close();

        if (!$sheetXml || !isset($sheetXml->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($sheetXml->sheetData->row as $row) {
            $rowData = [];
            $maxCol = 0;
            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                preg_match('/([A-Z]+)(\d+)/', $ref, $m);
                $colLetters = $m[1] ?? 'A';
                $colIdx = 0;
                $len = strlen($colLetters);
                for ($k = 0; $k < $len; $k++) {
                    $colIdx = $colIdx * 26 + (ord($colLetters[$k]) - ord('A') + 1);
                }
                $colIdx -= 1;

                $val = '';
                $type = (string) $cell['t'];
                if ($type === 's') {
                    $sId = (int) $cell->v;
                    $val = $sharedStrings[$sId] ?? '';
                } elseif ($type === 'inlineStr') {
                    $val = (string) $cell->is->t;
                } else {
                    $val = (string) $cell->v;
                }

                $rowData[$colIdx] = $val;
                if ($colIdx > $maxCol) {
                    $maxCol = $colIdx;
                }
            }

            $fullRow = [];
            for ($c = 0; $c <= $maxCol; $c++) {
                $fullRow[$c] = $rowData[$c] ?? '';
            }
            $rows[] = $fullRow;
        }

        return $rows;
    }
}
