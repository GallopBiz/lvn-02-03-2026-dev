<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Models\Academic\AttendanceCollective;
use App\Models\Academic\AttendanceCollectiveDetail;
use App\Models\Academic\AttendanceDetail;
use App\Models\Academic\Exam;
use App\Models\Classes;
use App\Models\Holidays;
use App\Models\Student_registration;
use App\Models\TeacherSubject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    private const STATUSES = [
        'present' => 'Present',
        'absent' => 'Absent',
        'leave' => 'Leave',
        'half_day' => 'Half Day',
        'pl' => 'PL',
    ];

    public function daily(Request $request)
    {
        $filters = $this->filters($request);
        $classes = $this->classes();
        $sections = $this->sections($filters['class_id']);
        $teachers = TeacherSubject::with('Teacher')->where('is_delete', 0)->groupBy('teacher_id')->get();
        $students = $filters['class_id']
            ? $this->studentsForClass($filters['class_id'], $filters['section_name'])->get()
            : collect();

        $editingAttendance = null;
        if ($request->route('id')) {
            $editingAttendance = $this->accessibleAttendanceQuery()->with('details.student')->findOrFail($request->route('id'));
            if ($editingAttendance->is_locked) {
                return redirect()->route('dailyattandence')->with('error', $this->lockedAttendanceMessage());
            }
            $filters['date'] = optional($editingAttendance->attendance_date)->format('Y-m-d');
            $filters['class_id'] = $editingAttendance->class_id;
            $filters['section_name'] = $editingAttendance->section_name;
            $filters['academic_session'] = $editingAttendance->academic_session;
            $students = $editingAttendance->details->pluck('student')->filter()->values();
        }

        $recentAttendance = $this->accessibleAttendanceQuery()->with('teacher', 'className')
            ->where('is_delete', 0)
            ->latest('attendance_date')
            ->latest('id')
            ->limit(25)
            ->get();

        $summary = $editingAttendance
            ? $this->summaryFromDetails($editingAttendance->details)
            : $this->emptySummary($students->count());
        $nonWorkingDay = $this->nonWorkingDayInfo($filters['date']);

        return view('backend.AcademicsModules.dailyattandence', [
            'statuses' => self::STATUSES,
            'classes' => $classes,
            'sections' => $sections,
            'teachers' => $teachers,
            'students' => $students,
            'filters' => $filters,
            'recentAttendance' => $recentAttendance,
            'editingAttendance' => $editingAttendance,
            'summary' => $summary,
            'nonWorkingDay' => $nonWorkingDay,
        ]);
    }

    public function students(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'section_name' => 'nullable|string',
        ]);

        return response()->json($this->studentsForClass($request->class_id, $request->section_name)->get());
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
            'attendance.*' => 'required|in:present,absent,leave,half_day,pl',
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string|max:255',
        ]);

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
        } elseif (!$this->canStaffAccessClassSection($validated['class_id'], $validated['section_name'])) {
            abort(403, 'You are not allowed to mark attendance for this class/section.');
        }

        $duplicate = Attendance::query()
            ->where('is_delete', 0)
            ->where('class_id', $validated['class_id'])
            ->where('section_name', $validated['section_name'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->where('academic_session', $validated['academic_session'])
            ->when($attendanceId, fn ($query) => $query->where('id', '!=', $attendanceId))
            ->exists();

        if ($duplicate) {
            return back()->withInput()->with('error', 'Attendance is already recorded for this class, section, session and date.');
        }

        $counts = collect($validated['attendance'])->countBy();
        $leaveCount = (int) ($counts['leave'] ?? 0);

        DB::transaction(function () use ($validated, $request, $attendanceId, $counts, $leaveCount) {
            $attendance = Attendance::updateOrCreate(
                ['id' => $attendanceId],
                [
                    'teacher_id' => $validated['teacher_id'] ?? null,
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
        $history = collect();
        $summary = $this->emptySummary(0);

        if ($selectedStudent) {
            $historyQuery = AttendanceDetail::with('attendance.className')
                ->where('student_id', $selectedStudent->id)
                ->whereHas('attendance', function ($query) use ($filters) {
                    $this->applyAttendanceFilters($query, $filters);
                })
                ->join('academic_attendance', 'academic_attendance_details.attendance_id', '=', 'academic_attendance.id')
                ->orderByDesc('academic_attendance.attendance_date')
                ->select('academic_attendance_details.*');

            $summary = $this->summaryFromDetails((clone $historyQuery)->get());
            $history = $historyQuery->paginate(40)->appends($request->query());
        }
        $monthlyDetail = $selectedStudent && $filters['month'] && $filters['class_id'] && $filters['section_name']
            ? $this->monthWiseAttendanceMatrix($filters, $selectedStudent->id)
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
        $monthWiseReport = ($filters['class_id'] && $filters['section_name'] && $filters['month'])
            ? $this->monthWiseAttendanceMatrix($filters)
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

    public function collective(Request $request)
    {
        $filters = $this->collectiveFilters($request);
        $editingCollective = null;
        if ($request->integer('edit_collective_id')) {
            $editingCollective = AttendanceCollective::where('is_delete', 0)->findOrFail($request->integer('edit_collective_id'));
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
        $exams = Exam::with('classInfo')
            ->orderByDesc('id')
            ->get();

        $students = collect();
        $collectiveRows = collect();
        $workingDays = 0;
        $totalDays = 0;
        $sundays = 0;
        $holidays = 0;

        if ($filters['class_id'] && $filters['section_name'] && $filters['start_date'] && $filters['end_date']) {
            $students = $this->studentsForClass($filters['class_id'], $filters['section_name'])->get();
            $calculated = $this->calculateCollectiveRows($students, $filters);
            $collectiveRows = $calculated['rows'];
            $workingDays = $calculated['working_days'];
            $totalDays = $calculated['total_days'];
            $sundays = $calculated['sundays'];
            $holidays = $calculated['holidays'];
        }

        $savedCollectives = AttendanceCollective::with('teacher', 'className')
            ->where('is_delete', 0)
            ->latest('id')
            ->limit(30)
            ->get();

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
            'savedCollectives'
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
        $calculated = $this->calculateCollectiveRows($students, $validated);

        if ($students->isEmpty()) {
            return back()->withInput()->with('error', 'No students found for this class and section.');
        }

        if ($calculated['working_days'] <= 0) {
            return back()->withInput()->with('error', 'No working days found in this date range after excluding Sundays and HRMS holidays.');
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

    public function destroyCollective(AttendanceCollective $collective)
    {
        $collective->update(['is_delete' => 1]);

        return redirect()->route('academic.attendance.collective')->with('success', 'Collective attendance deleted successfully.');
    }

    public function printCollective(AttendanceCollective $collective)
    {
        $collective->load('details', 'teacher', 'className');

        return view('backend.AcademicsModules.attendance_collective_print', compact('collective'));
    }

    private function filters(Request $request): array
    {
        $month = $request->query('month', now()->format('Y-m'));

        return [
            'class_id' => $request->query('class_id', $request->query('classname')),
            'section_name' => $request->query('section_name', $request->query('Section')),
            'student_id' => $request->integer('student_id') ?: $request->integer('inq_form_selection'),
            'date' => $request->query('date', now()->format('Y-m-d')),
            'from_date' => $request->query('from_date', $request->query('fromdate')),
            'to_date' => $request->query('to_date', $request->query('todate')),
            'month' => $month,
            'academic_session' => $request->query('academic_session', $this->defaultSession()),
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

    private function calculateCollectiveRows($students, array $filters): array
    {
        $workingDayStats = $this->workingDayStats($filters['start_date'], $filters['end_date']);
        $workingDays = $workingDayStats['working_days'];
        $studentIds = $students->pluck('id')->values();
        $presentCounts = $studentIds->isNotEmpty()
            ? AttendanceDetail::query()
                ->join('academic_attendance', 'academic_attendance_details.attendance_id', '=', 'academic_attendance.id')
                ->where('academic_attendance.is_delete', 0)
                ->where('academic_attendance.class_id', $filters['class_id'])
                ->where('academic_attendance.section_name', $filters['section_name'])
                ->where('academic_attendance.academic_session', $filters['academic_session'])
                ->whereBetween('academic_attendance.attendance_date', [$filters['start_date'], $filters['end_date']])
                ->where('academic_attendance_details.status', 'present')
                ->whereIn('academic_attendance_details.student_id', $studentIds)
                ->when(true, function ($query) {
                    $this->applyWorkingDayOnlyFilter($query);
                })
                ->groupBy('academic_attendance_details.student_id')
                ->selectRaw('academic_attendance_details.student_id, COUNT(*) as present_days')
                ->pluck('present_days', 'student_id')
            : collect();

        $rows = $students->map(function ($student) use ($presentCounts, $workingDays) {
            $presentDays = (int) ($presentCounts[$student->id] ?? 0);
            $percentage = $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 2) : 0;

            return [
                'student_id' => $student->id,
                'student_name' => $student->student_name,
                'scholar_no' => $student->scholar_no ?: $student->form_number,
                'present_days' => $presentDays,
                'working_days' => $workingDays,
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
        $month = Carbon::createFromFormat('Y-m', $filters['month']);
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $days = range(1, (int) $end->format('d'));
        $nonWorkingDays = $this->nonWorkingDaysForRange($start, $end);

        $studentsQuery = $this->studentsForClass($filters['class_id'], $filters['section_name']);
        if ($onlyStudentId) {
            $studentsQuery->where('id', $onlyStudentId);
        }
        $students = $studentsQuery->get();

        $attendanceRecords = Attendance::with('details')
            ->where('is_delete', 0)
            ->where('class_id', $filters['class_id'])
            ->where('section_name', $filters['section_name'])
            ->where('academic_session', $filters['academic_session'])
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($record) => optional($record->attendance_date)->format('Y-m-d'));

        $rows = $students->map(function ($student) use ($days, $month, $attendanceRecords, $nonWorkingDays) {
            $presentDays = 0;
            $workingDays = 0;
            $cells = [];

            foreach ($days as $day) {
                $date = $month->copy()->day($day)->toDateString();
                $nonWorking = $nonWorkingDays[$date] ?? null;

                if ($nonWorking) {
                    $cells[$day] = [
                        'label' => $nonWorking['type'] === 'sunday' ? 'S' : 'H',
                        'class' => 'day-holiday',
                        'title' => $nonWorking['label'],
                    ];
                    continue;
                }

                $attendance = $attendanceRecords->get($date);
                if (!$attendance) {
                    $cells[$day] = [
                        'label' => '-',
                        'class' => 'day-empty',
                        'title' => 'Attendance not taken',
                    ];
                    continue;
                }

                $workingDays++;
                $detail = $attendance->details->firstWhere('student_id', $student->id);
                $status = optional($detail)->status;
                $label = $this->statusShortLabel($status);
                $cells[$day] = [
                    'label' => $label,
                    'class' => 'day-' . ($status ?: 'empty'),
                    'title' => $status ? ucwords(str_replace('_', ' ', $status)) : 'Not marked',
                ];

                if ($status === 'present') {
                    $presentDays++;
                } elseif ($status === 'half_day') {
                    $presentDays += 0.5;
                }
            }

            $percentage = $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 2) : 0;

            return [
                'student_id' => $student->id,
                'student_name' => $student->student_name,
                'roll_no' => $student->form_number,
                'scholar_no' => $student->scholar_no ?: $student->id,
                'cells' => $cells,
                'present_days' => $presentDays,
                'working_days' => $workingDays,
                'percentage' => $percentage,
            ];
        })->values();

        return [
            'month_label' => $month->format('F Y'),
            'days' => $days,
            'rows' => $rows,
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

        $holidays = Holidays::whereDate('HolidayStartDate', '<=', $end)
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
            default => '-',
        };
    }

    private function classes()
    {
        $query = Classes::query()->orderBy('class_name');

        if ($this->isStaffAttendanceUser()) {
            $classIds = $this->accessibleTeacherSubjects(true)->pluck('class_id')->unique()->values();
            $query->whereIn('id', $classIds);
        }

        return $query->get()->unique('class_name')->values();
    }

    private function sections($classId)
    {
        if (!$classId) {
            return collect();
        }

        $query = Classes::query()
            ->where('id', $classId)
            ->orWhere('class_name', $classId);

        if ($this->isStaffAttendanceUser()) {
            $allowedSections = $this->accessibleTeacherSubjects()
                ->where('class_id', $classId)
                ->pluck('section_name')
                ->filter()
                ->values();

            if ($allowedSections->isNotEmpty() && !$allowedSections->contains('All')) {
                $query->whereIn('section_name', $allowedSections);
            }
        }

        return $query
            ->pluck('section_name')
            ->filter()
            ->flatMap(fn ($section) => array_map('trim', explode(',', $section)))
            ->unique()
            ->values();
    }

    private function studentsForClass($classId, ?string $sectionName = null)
    {
        $class = Classes::query()->where('id', $classId)->orWhere('class_name', $classId)->first();
        $className = optional($class)->class_name ?: $classId;

        return Student_registration::query()
            ->where(function ($query) use ($classId, $className) {
                $query->where('class_name', $classId)->orWhere('class_name', $className);
            })
            ->when($sectionName, function ($query) use ($sectionName) {
                $query->where(function ($inner) use ($sectionName) {
                    $inner->where('json_str', 'like', '%"section_name":"' . $sectionName . '"%')
                        ->orWhere('json_str', 'like', '%"section_name": "' . $sectionName . '"%');
                });
            })
            ->orderBy('student_name')
            ->select('id', 'form_number', 'scholar_no', 'student_name', 'class_name', 'session_name', 'json_str');
    }

    private function studentOptions($classId, ?string $sectionName, string $search)
    {
        return Student_registration::query()
            ->when($classId, function ($query) use ($classId, $sectionName) {
                $ids = $this->studentsForClass($classId, $sectionName)->pluck('id');
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
        $query->when($filters['class_id'], fn ($q) => $q->where('class_id', $filters['class_id']))
            ->when($filters['section_name'], fn ($q) => $q->where('section_name', $filters['section_name']))
            ->when($filters['academic_session'], fn ($q) => $q->where('academic_session', $filters['academic_session']));

        if ($filters['from_date'] || $filters['to_date']) {
            $query->when($filters['from_date'], fn ($q) => $q->whereDate('attendance_date', '>=', $filters['from_date']))
                ->when($filters['to_date'], fn ($q) => $q->whereDate('attendance_date', '<=', $filters['to_date']));
        } elseif ($filters['month']) {
            $date = Carbon::createFromFormat('Y-m', $filters['month']);
            $query->whereBetween('attendance_date', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()]);
        }

        $this->applyWorkingDayOnlyFilter($query);
    }

    private function summaryFromDetails($details): array
    {
        $counts = collect($details)->countBy('status');
        $pl = (int) ($counts['pl'] ?? 0);
        $workingDays = max(0, collect($details)->count() - $pl);
        $present = (int) ($counts['present'] ?? 0);
        $halfDay = (int) ($counts['half_day'] ?? 0);
        $leave = (int) ($counts['leave'] ?? 0);

        return [
            'total_students' => $workingDays,
            'present' => $present,
            'absent' => (int) ($counts['absent'] ?? 0) + $leave,
            'leave' => $leave,
            'half_day' => $halfDay,
            'pl' => $pl,
            'percentage' => $workingDays ? round((($present + ($halfDay * 0.5)) / $workingDays) * 100, 2) : 0,
        ];
    }

    private function emptySummary(int $total): array
    {
        return [
            'total_students' => $total,
            'present' => 0,
            'absent' => 0,
            'leave' => 0,
            'half_day' => 0,
            'pl' => 0,
            'percentage' => 0,
        ];
    }

    private function defaultSession(): string
    {
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

        $holiday = Holidays::whereDate('HolidayStartDate', '<=', $attendanceDate)
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

    private function applyWorkingDayOnlyFilter($query): void
    {
        $query->whereRaw('DAYOFWEEK(academic_attendance.attendance_date) <> 1')
            ->whereNotExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('hrms_holidays')
                    ->whereNull('hrms_holidays.deleted_at')
                    ->whereRaw('hrms_holidays.HolidayStartDate <= academic_attendance.attendance_date')
                    ->where(function ($query) {
                        $query->whereRaw('hrms_holidays.HolidayEndDate >= academic_attendance.attendance_date')
                            ->orWhereNull('hrms_holidays.HolidayEndDate');
                    });
            });
    }

    private function isStaffAttendanceUser(): bool
    {
        return Auth::guard('staff')->check() && !Auth::guard('web')->check();
    }

    private function staffEmployeeId(): ?int
    {
        return $this->isStaffAttendanceUser() ? (int) Auth::guard('staff')->user()->employee_id : null;
    }

    private function accessibleTeacherSubjects(bool $classTeacherOnly = false)
    {
        $query = TeacherSubject::where('is_delete', 0);

        if ($this->isStaffAttendanceUser()) {
            $query->where('teacher_id', $this->staffEmployeeId());
        }

        if ($classTeacherOnly) {
            $query->where('role', 'Class Teacher');
        }

        return $query;
    }

    private function accessibleAttendanceQuery()
    {
        $query = Attendance::query();

        if ($this->isStaffAttendanceUser()) {
            $subjects = $this->accessibleTeacherSubjects()
            ->get(['class_id', 'section_name'])
            ->groupBy('class_id');

            if ($subjects->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            $query->where(function ($outer) use ($subjects) {
                foreach ($subjects as $classId => $rows) {
                    $sections = $rows->pluck('section_name')->filter()->unique()->values();
                    $outer->orWhere(function ($inner) use ($classId, $sections) {
                        $inner->where('class_id', $classId);
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

        return $this->accessibleTeacherSubjects($classTeacherOnly)
            ->where('class_id', $classId)
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
}
