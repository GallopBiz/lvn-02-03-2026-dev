<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Models\Academic\AttendanceCollective;
use App\Models\Academic\AttendanceCollectiveDetail;
use App\Models\Academic\AttendanceDetail;
use App\Models\Academic\Exam;
use App\Models\Classes;
use App\Models\Classname;
use App\Models\Holidays;
use App\Models\Student_registration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ManualAttendanceController extends Controller
{
    /**
     * Enforce strict admin access: Only admin can access and manually adjust working days & present days.
     */
    private function ensureAdmin()
    {
        $isWebAdmin = Auth::guard('web')->check() && strtolower(trim(Auth::guard('web')->user()->type ?? '')) === 'a';
        $isStaffAdmin = Auth::guard('staff')->check() && (
            strtolower(trim(Auth::guard('staff')->user()->role ?? '')) === 'admin' ||
            (method_exists(Auth::guard('staff')->user(), 'hasRole') && Auth::guard('staff')->user()->hasRole('Admin'))
        );

        if (!$isWebAdmin && !$isStaffAdmin) {
            abort(403, 'Permission Denied: Only administrators can manually adjust school working days and students\' present days.');
        }
    }

    /**
     * Display the manual attendance adjustment form and previously saved records.
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $session = $request->query('academic_session', $this->defaultSession($request));
        [$sessionStart, $sessionEnd] = $this->sessionDateRange($session);

        $selectedClassName = null;
        if ($request->filled('class_name')) {
            $selectedClassName = (string) $request->input('class_name');
        } elseif ($request->filled('class_id')) {
            $rawId = $request->input('class_id');
            if (Classes::where('class_name', $rawId)->exists()) {
                $selectedClassName = (string) $rawId;
            } else {
                $cObj = Classes::find($rawId);
                $selectedClassName = optional($cObj)->class_name ?: (string) $rawId;
            }
        }
        $selectedSectionName = $request->query('section_name');

        $editingCollective = null;
        if ($request->integer('edit_id')) {
            $editingCollective = AttendanceCollective::with('details')
                ->where('is_delete', 0)
                ->findOrFail($request->integer('edit_id'));

            $selectedClassName = $editingCollective->class_name;
            if (!$selectedClassName && $editingCollective->class_id) {
                if (Classes::where('class_name', $editingCollective->class_id)->exists()) {
                    $selectedClassName = (string) $editingCollective->class_id;
                } else {
                    $cObj = Classes::find($editingCollective->class_id);
                    $selectedClassName = optional($cObj)->class_name ?: (string) $editingCollective->class_id;
                }
            }
            $selectedSectionName = $editingCollective->section_name;
        }

        $selectedClass = null;
        if ($selectedClassName && $selectedSectionName) {
            $selectedClass = Classes::where('class_name', $selectedClassName)
                ->where('section_name', $selectedSectionName)
                ->first();
        } elseif ($selectedClassName) {
            $selectedClass = Classes::where('class_name', $selectedClassName)->first();
        }

        $filters = [
            'academic_session' => $session,
            'class_id' => optional($selectedClass)->id ?: $selectedClassName,
            'class_name' => $selectedClassName,
            'section_name' => $selectedSectionName,
            'exam_id' => $request->integer('exam_id') ?: ($editingCollective ? $editingCollective->exam_id : null),
            'school_working_days' => $request->query('school_working_days', $editingCollective ? $editingCollective->working_days : null),
            'start_date' => $request->query('start_date', $editingCollective ? optional($editingCollective->start_date)->format('Y-m-d') : $sessionStart),
            'end_date' => $request->query('end_date', $editingCollective ? optional($editingCollective->end_date)->format('Y-m-d') : $sessionEnd),
        ];

        $startDate = $filters['start_date'] ?: $sessionStart;
        $endDate = $filters['end_date'] ?: $sessionEnd;
        $autoStats = $this->workingDayStats($startDate, $endDate);
        $autoWorkingDays = $autoStats['working_days'];

        $existingCollective = null;
        if ($editingCollective) {
            $existingCollective = $editingCollective;
        } elseif ($selectedClassName && $selectedSectionName) {
            $existingCollective = AttendanceCollective::with('details')
                ->where('is_delete', 0)
                ->where('is_manual', 1)
                ->where(function ($q) use ($filters, $selectedClassName) {
                    $q->where('class_id', $filters['class_id'])
                        ->orWhere('class_name', $selectedClassName);
                })
                ->where('section_name', $selectedSectionName)
                ->where('academic_session', $filters['academic_session'])
                ->when($filters['exam_id'], fn ($q) => $q->where('exam_id', $filters['exam_id']))
                ->latest('id')
                ->first();
        }

        // Determine effective school working days
        if ($filters['school_working_days'] !== null && $filters['school_working_days'] !== '') {
            $schoolWorkingDays = (int) $filters['school_working_days'];
        } elseif ($existingCollective && (int) $existingCollective->working_days > 0) {
            $schoolWorkingDays = (int) $existingCollective->working_days;
            $filters['school_working_days'] = $schoolWorkingDays;
        } else {
            $schoolWorkingDays = '';
        }

        $classList = $this->classList();
        $sectionsByClass = $this->sectionsByClass();
        $classExamsMap = $this->classExamsMap();
        $classes = $this->classes();
        $sections = $selectedClassName ? ($sectionsByClass[$selectedClassName] ?? collect()) : collect();

        // When a class is selected, load exams assigned to that class
        $exams = $selectedClassName
            ? $this->examsForClass($selectedClassName)
            : collect();

        if ($filters['exam_id']) {
            if ($exams->isEmpty()) {
                $filters['exam_id'] = null;
            } elseif (!$exams->contains('id', (int) $filters['exam_id'])) {
                // If previously selected exam exists by name for the newly selected class, preserve it
                $prevExam = Exam::find($filters['exam_id']);
                $matchingSameName = $prevExam ? $exams->firstWhere('exam_name', $prevExam->exam_name) : null;
                $filters['exam_id'] = $matchingSameName ? $matchingSameName->id : null;
            }
        }

        $students = collect();
        $studentRows = collect();

        if ($selectedClassName && $selectedSectionName) {
            $students = $this->studentsForClass($filters['class_id'], $selectedSectionName)->get();

            // Filter students by visibility rule: Selected Date >= Student Registration Date
            if (!empty($filters['end_date'])) {
                $students = $this->filterStudentsByRegistrationDate($students, $filters['end_date']);
            }

            // Ensure any student already part of the saved record is preserved for historical audit integrity
            if ($existingCollective && $existingCollective->details->isNotEmpty()) {
                $savedStudentIds = $existingCollective->details->pluck('student_id')->all();
                $allStudents = $this->studentsForClass($filters['class_id'], $selectedSectionName)->get();
                $savedStudents = $allStudents->whereIn('id', $savedStudentIds);
                $students = $students->concat($savedStudents)->unique('id')->values();
            }

            // Automatically calculate collective attendance based on registration dates
            $autoCalculated = $this->calculateCollectiveRows($students, $filters);
            $autoRows = collect($autoCalculated['rows'])->keyBy('student_id');

            if ($schoolWorkingDays === '' || $schoolWorkingDays === null) {
                $schoolWorkingDays = $autoCalculated['working_days'] > 0 ? $autoCalculated['working_days'] : '';
                $filters['school_working_days'] = $schoolWorkingDays;
            }

            $existingDetails = $existingCollective ? $existingCollective->details->keyBy('student_id') : collect();
            $prefillDaily = $request->boolean('prefill_daily');

            $studentRows = $students->map(function ($student) use ($existingDetails, $autoRows, $schoolWorkingDays, $prefillDaily) {
                $existing = $existingDetails->get($student->id);
                $auto = $autoRows->get($student->id);

                if ($existing && !$prefillDaily) {
                    // Manual modifications previously saved by admin take precedence
                    $presentDays = (float) $existing->present_days;
                    $workingDays = (int) ($existing->working_days ?: ($auto['working_days'] ?? $schoolWorkingDays));
                    $remarks = $existing->remarks ?: '';
                } elseif ($auto) {
                    // By default, automatically fetch the calculated Working Days and Present Days from Collective Attendance
                    $presentDays = (float) ($auto['present_days'] ?? 0);
                    $workingDays = (int) ($auto['working_days'] ?? $schoolWorkingDays);
                    $remarks = '';
                } else {
                    $presentDays = 0;
                    $workingDays = (int) ($schoolWorkingDays ?: 0);
                    $remarks = '';
                }

                $percentage = $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 2) : 0;

                $fatherName = '';
                if (!empty($student->json_str)) {
                    $decoded = is_array($student->json_str) ? $student->json_str : json_decode($student->json_str, true);
                    $fatherName = $decoded['father_name'] ?? ($decoded['FatherName'] ?? '');
                }

                return [
                    'student_id' => $student->id,
                    'student_name' => $student->student_name,
                    'scholar_no' => $student->scholar_no ?: $student->form_number,
                    'roll_no' => $student->form_number,
                    'father_name' => $fatherName,
                    'registration_date' => $auto['registration_date'] ?? null,
                    'effective_start_date' => $auto['effective_start_date'] ?? null,
                    'working_days' => $workingDays,
                    'present_days' => $presentDays,
                    'percentage' => $percentage,
                    'is_below_minimum' => $percentage < 75,
                    'remarks' => $remarks,
                ];
            })->values();
        }

        // Saved manual attendance entries
        $savedCollectives = AttendanceCollective::with('className', 'teacher')
            ->where('is_delete', 0)
            ->where('is_manual', 1)
            ->latest('id')
            ->limit(35)
            ->get();

        return view('backend.AcademicsModules.attendance_manual', compact(
            'filters',
            'classes',
            'classList',
            'selectedClassName',
            'selectedSectionName',
            'selectedClass',
            'sections',
            'sectionsByClass',
            'exams',
            'classExamsMap',
            'students',
            'studentRows',
            'schoolWorkingDays',
            'autoWorkingDays',
            'autoStats',
            'editingCollective',
            'savedCollectives'
        ));
    }

    /**
     * Store or update manually adjusted student working days and present days.
     */
    public function store(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'collective_id' => 'nullable|integer',
            'class_id' => 'required',
            'class_name' => 'nullable|string|max:100',
            'section_name' => 'required|string|max:100',
            'exam_id' => 'nullable|integer',
            'exam_name' => 'nullable|string|max:255',
            'academic_session' => 'required|string|max:50',
            'school_working_days' => 'required|numeric|min:1|max:365',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'remarks' => 'nullable|string|max:255',
            'students' => 'required|array|min:1',
            'students.*.student_id' => 'required|integer',
            'students.*.student_name' => 'required|string|max:255',
            'students.*.scholar_no' => 'nullable|string|max:100',
            'students.*.working_days' => 'required|numeric|min:0|max:365',
            'students.*.present_days' => 'required|numeric|min:0|max:365',
            'students.*.remarks' => 'nullable|string|max:255',
        ]);

        if (!empty($validated['class_name']) && !empty($validated['section_name'])) {
            $classObj = Classes::where('class_name', $validated['class_name'])
                ->where('section_name', $validated['section_name'])
                ->first();
            if ($classObj) {
                $validated['class_id'] = $classObj->id;
            }
        } elseif (empty($validated['class_name'])) {
            $classObj = Classes::where('id', $validated['class_id'])->orWhere('class_name', $validated['class_id'])->first();
            if (!$classObj && class_exists(Classname::class)) {
                $classObj = Classname::where('id', $validated['class_id'])->orWhere('class_name', $validated['class_id'])->first();
            }
            $validated['class_name'] = optional($classObj)->class_name ?: (string) $validated['class_id'];
        }

        if (!empty($validated['exam_id']) && empty($validated['exam_name'])) {
            $selectedExam = Exam::find($validated['exam_id']);
            $validated['exam_name'] = optional($selectedExam)->exam_name;
        }

        DB::transaction(function () use ($validated) {
            $collectiveData = [
                'teacher_id' => Auth::guard('staff')->id() ?: null,
                'class_id' => $validated['class_id'],
                'class_name' => $validated['class_name'],
                'section_name' => $validated['section_name'],
                'exam_id' => $validated['exam_id'] ?? null,
                'exam_name' => $validated['exam_name'] ?? null,
                'academic_session' => $validated['academic_session'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'working_days' => (int) $validated['school_working_days'],
                'student_count' => count($validated['students']),
                'is_manual' => 1,
                'remarks' => $validated['remarks'] ?? 'Manually adjusted by Admin',
                'is_delete' => 0,
            ];

            $collective = !empty($validated['collective_id'])
                ? tap(AttendanceCollective::where('is_delete', 0)->findOrFail($validated['collective_id']))->update($collectiveData)
                : AttendanceCollective::create($collectiveData);

            // Replace details
            AttendanceCollectiveDetail::where('collective_id', $collective->id)->delete();

            foreach ($validated['students'] as $item) {
                $workingDays = (int) $item['working_days'];
                $presentDays = (float) $item['present_days'];
                $percentage = $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 2) : 0;
                $isBelowMinimum = $percentage < 75;

                AttendanceCollectiveDetail::create([
                    'collective_id' => $collective->id,
                    'student_id' => $item['student_id'],
                    'student_name' => $item['student_name'],
                    'scholar_no' => $item['scholar_no'] ?? null,
                    'present_days' => $presentDays,
                    'working_days' => $workingDays,
                    'attendance_percentage' => $percentage,
                    'is_below_minimum' => $isBelowMinimum,
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }
        });

        return redirect()->route('academic.attendance.manual', [
            'class_id' => $validated['class_id'],
            'section_name' => $validated['section_name'],
            'academic_session' => $validated['academic_session'],
            'exam_id' => $validated['exam_id'] ?? null,
        ])->with('success', 'Manual student attendance and working days saved successfully.');
    }

    /**
     * Soft delete a saved manual attendance record.
     */
    public function destroy($collective)
    {
        $this->ensureAdmin();

        $model = $this->resolveCollectiveModel($collective);
        if (!$model) {
            abort(404, 'Manual attendance record not found.');
        }

        $model->update(['is_delete' => 1]);

        return redirect()->route('academic.attendance.manual')->with('success', 'Manual attendance record deleted successfully.');
    }

    /**
     * Printable view of manual attendance.
     */
    public function print($collective)
    {
        $this->ensureAdmin();

        $model = $this->resolveCollectiveModel($collective);
        if (!$model) {
            abort(404, 'Manual attendance record not found.');
        }

        $model->load('details.student', 'className', 'teacher');

        return view('backend.AcademicsModules.attendance_manual_print', ['collective' => $model]);
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

        $model = AttendanceCollective::where('is_delete', 0)->find($id);
        if ($model) {
            return $model;
        }

        try {
            $model = AttendanceCollective::on('dynamic')->where('is_delete', 0)->find($id);
            if ($model) {
                return $model;
            }
        } catch (\Throwable $e) {}

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

    /**
     * Get all active classes.
     */
    private function classes()
    {
        return Classes::query()->orderBy('class_name')->get()->unique('class_name')->values();
    }

    /**
     * Get sections for a given class.
     */
    private function sections($classId)
    {
        if (!$classId) {
            return collect();
        }

        $selectedClass = Classes::query()->where('id', $classId)->orWhere('class_name', $classId)->first();
        if (!$selectedClass && class_exists(Classname::class)) {
            $selectedClass = Classname::query()->where('id', $classId)->orWhere('class_name', $classId)->first();
        }
        $className = optional($selectedClass)->class_name ?: (string) $classId;

        $classRows = Classes::query()
            ->where('class_name', $className)
            ->orWhere('id', $classId)
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

        $knownOrder = ['Aryabhatta', 'Kautilya', 'Ramanujan'];
        return $allSections->sortBy(function ($sec) use ($knownOrder) {
            $idx = array_search($sec, $knownOrder);
            return $idx === false ? 999 . $sec : sprintf('%03d', $idx);
        })->values();
    }

    /**
     * Query students belonging to a class and optional section.
     */
    private function studentsForClass($classId, ?string $sectionName = null)
    {
        $class = Classes::query()->where('id', $classId)->orWhere('class_name', $classId)->first();
        if (!$class && class_exists(Classname::class)) {
            $class = Classname::query()->where('id', $classId)->orWhere('class_name', $classId)->first();
        }
        $className = optional($class)->class_name ?: (string) $classId;
        $classIds = Classes::query()->where('class_name', $className)->pluck('id')->push($classId)->unique()->values();

        $classAliases = array_unique(array_filter([
            $className,
            (string) $classId,
            ltrim((string) $className, '0'),
            is_numeric($className) ? sprintf('%02d', (int) $className) : null,
            class_exists(\App\Academic\AcademicHelper::class) ? \App\Academic\AcademicHelper::normalizeClassIdentifier($className) : null,
        ]));

        return Student_registration::query()
            ->where(function ($query) use ($classId, $className, $classIds, $classAliases) {
                $query->whereIn('class_name', $classAliases)
                    ->orWhere(function ($byClassId) use ($classIds, $classAliases) {
                        $byClassId->whereIn('class_id', $classIds)
                            ->where(function ($cq) use ($classAliases) {
                                $cq->whereIn('class_name', $classAliases)
                                    ->orWhereNull('class_name')
                                    ->orWhere('class_name', '');
                            });
                    });
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

    /**
     * Resolve class model by id or name.
     */
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

    /**
     * Apply working-day filter (exclude Sundays and HRMS holidays).
     */
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

    /**
     * Calculate collective rows strictly following the registration date logic.
     * Starts from the later of 1st April (session start) or student registration date.
     */
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

    /**
     * Determine normalized default academic session string.
     */
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

    /**
     * Compute working days stats (total days - Sundays - HRMS holidays).
     */
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

    /**
     * Default April 1 to March 31 session date range.
     */
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

    /**
     * Gather non-working days (Sundays and HRMS public holidays).
     */
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

    /**
     * Get sorted list of distinct class names.
     */
    private function classList()
    {
        $all = Classes::query()
            ->select('class_name')
            ->whereNotNull('class_name')
            ->distinct()
            ->pluck('class_name')
            ->values();

        $pre = ['Nursery', 'KG1', 'KG2', 'Kg1', 'Kg2'];
        return $all->sort(function ($a, $b) use ($pre) {
            $posA = array_search($a, $pre);
            $posB = array_search($b, $pre);
            if ($posA !== false && $posB !== false) return $posA <=> $posB;
            if ($posA !== false) return -1;
            if ($posB !== false) return 1;
            return strnatcasecmp($a, $b);
        })->values();
    }

    /**
     * Get map of class_name to distinct sections.
     */
    private function sectionsByClass(): array
    {
        $base = Classes::query()
            ->select('class_name', 'section_name')
            ->whereNotNull('class_name')
            ->whereNotNull('section_name')
            ->orderBy('section_name')
            ->get()
            ->groupBy('class_name')
            ->map(fn ($rows) => $rows->pluck('section_name')->unique()->values())
            ->toArray();

        $augmented = [];
        foreach ($base as $cName => $secs) {
            $augmented[$cName] = $secs;
            $stripped = ltrim((string) $cName, '0');
            $augmented[$stripped] = $secs;
            $roman = \App\Academic\AcademicHelper::toRoman((string) $cName);
            if ($roman !== '') {
                $augmented[$roman] = $secs;
            }
        }

        return $augmented;
    }


    /**
     * Build map of class_name to unique clean exams for client-side instant population.
     */
    private function classExamsMap(): array
    {
        $allExams = Exam::query()->orderByDesc('id')->get();
        $classes = Classes::query()->select('id', 'class_name')->get();
        $classList = $this->classList();
        $map = [];

        foreach ($classList as $cName) {
            $ids = $classes->where('class_name', $cName)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $matching = $allExams->filter(function ($e) use ($ids, $cName) {
                return in_array((int) $e->class_id, $ids, true)
                    || (!is_numeric($cName) && (string) $e->class_id === (string) $cName);
            })->unique('exam_name')->map(function ($e) {
                return [
                    'id' => $e->id,
                    'name' => $e->exam_name,
                ];
            })->values()->toArray();

            $map[$cName] = $matching;
        }

        return $map;
    }

    /**
     * Get unique exams for a given class name.
     */
    private function examsForClass(?string $className)
    {
        if (empty($className)) {
            return collect();
        }

        $classIds = Classes::where('class_name', $className)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return Exam::query()
            ->where(function ($q) use ($classIds, $className) {
                if (!empty($classIds)) {
                    $q->whereIn('class_id', $classIds);
                }
                if (!is_numeric($className)) {
                    $q->orWhere('class_id', $className);
                }
            })
            ->orderByDesc('id')
            ->get()
            ->unique('exam_name')
            ->values();
    }
}
