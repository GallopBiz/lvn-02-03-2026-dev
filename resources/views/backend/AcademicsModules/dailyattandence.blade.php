@extends('backend.layouts.main')
@section('main-container')
@php
    $detailByStudent = $editingAttendance ? $editingAttendance->details->keyBy('student_id') : collect();
    $isStaffAttendanceUser = auth()->guard('staff')->check() && !auth()->guard('web')->check();
@endphp
<style>
    .attendance-page { color: #24324b; }
    .attendance-toolbar, .attendance-panel { background: #fff; border: 1px solid #e7edf5; border-radius: 8px; box-shadow: 0 8px 20px rgba(36, 50, 75, .05); }
    .attendance-toolbar { padding: 18px; }
    .attendance-panel { padding: 20px; }
    .summary-grid { display: grid; grid-template-columns: repeat(6, minmax(120px, 1fr)); gap: 12px; }
    .summary-card { border: 1px solid #e7edf5; border-radius: 8px; padding: 14px; background: #f8fbff; }
    .summary-card span { display: block; color: #69758a; font-size: 12px; text-transform: uppercase; }
    .summary-card strong { font-size: 24px; }
    .status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; font-weight: 600; font-size: 12px; border: 1px solid transparent; cursor: pointer; margin: 2px; }
    .status-pill input { margin: 0; }
    .status-present { background: #e9f8ef; color: #167146; border-color: #bfe9ce; }
    .status-absent { background: #fff0f0; color: #b42318; border-color: #ffd0d0; }
    .status-leave { background: #fff7df; color: #936600; border-color: #ffe6a3; }
    .status-half_day { background: #eaf2ff; color: #2257a7; border-color: #c9dcff; }
    .status-pl { background: #f0edff; color: #5740a8; border-color: #d8d0ff; }
    .table thead th { white-space: nowrap; background: #f4f7fb; color: #41516a; border-bottom: 1px solid #e3e9f2; }
    .table td { vertical-align: middle; }
    .student-search { max-width: 320px; }
    @media (max-width: 991px) { .summary-grid { grid-template-columns: repeat(2, minmax(120px, 1fr)); } }
    @media (max-width: 575px) { .summary-grid { grid-template-columns: 1fr; } .attendance-panel { padding: 14px; } }
</style>

<div class="main-content attendance-page pt-4">
    <div class="breadcrumb d-flex justify-content-between align-items-center flex-wrap">
        <h2 class="mb-2">
            {{ $editingAttendance ? 'Edit Daily Attendance' : 'Daily Attendance' }}
            @if($editingAttendance && $editingAttendance->is_locked)
                <span class="badge badge-dark ml-2">Locked</span>
            @endif
        </h2>
        <a href="{{ route('Attandencereports') }}" class="btn btn-outline-primary btn-sm">Reports</a>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    @if($nonWorkingDay)
        <div class="alert alert-warning">
            {{ $nonWorkingDay['label'] }}. Attendance cannot be marked for this date.
        </div>
    @endif

    <div class="summary-grid mb-3">
        <div class="summary-card"><span>Total Students</span><strong id="totalStudents">{{ $summary['total_students'] }}</strong></div>
        <div class="summary-card"><span>Present</span><strong id="presentCount">{{ $summary['present'] }}</strong></div>
        <div class="summary-card"><span>Absent</span><strong id="absentCount">{{ $summary['absent'] }}</strong></div>
        <div class="summary-card"><span>Leave</span><strong id="leaveCount">{{ $summary['leave'] }}</strong></div>
        <div class="summary-card"><span>Half Day</span><strong id="halfDayCount">{{ $summary['half_day'] }}</strong></div>
        <div class="summary-card"><span>PL</span><strong id="plCount">{{ $summary['pl'] }}</strong></div>
    </div>

    <div class="attendance-toolbar mb-3">
        <form method="GET" action="{{ $editingAttendance ? route('dailyattandenceUpdate', $editingAttendance->id) : route('dailyattandence') }}" id="filterForm">
            <div class="row">
                <div class="col-lg-2 col-md-4 form-group">
                    <label>Date</label>
                    <input type="date" name="date" value="{{ $filters['date'] }}" class="form-control" onchange="document.getElementById('filterForm').submit()">
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label>Academic Session</label>
                    <input type="text" name="academic_session" value="{{ $filters['academic_session'] }}" class="form-control" placeholder="2026-2027">
                </div>
                <div class="col-lg-3 col-md-4 form-group">
                    <label>Class</label>
                    <select name="class_id" class="form-control" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" data-name="{{ $class->class_name }}" @selected((string)$filters['class_id'] === (string)$class->id)>{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label>Section</label>
                    <select name="section_name" class="form-control" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Select section</option>
                        @foreach($sections as $section)
                            <option value="{{ $section }}" @selected($filters['section_name'] === $section)>{{ $section }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-8 form-group d-flex align-items-end">
                    <button class="btn btn-primary mr-2" type="submit">Load Students</button>
                    <a class="btn btn-outline-secondary" href="{{ route('dailyattandence') }}">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <form method="POST" action="{{ $editingAttendance ? route('dailyattandenceUpdateInfo') : route('dailyattandence') }}" id="attendanceForm">
        @csrf
        @if($editingAttendance)
            <input type="hidden" name="attendance_id" value="{{ $editingAttendance->id }}">
        @endif
        <input type="hidden" name="attendance_date" value="{{ $filters['date'] }}">
        <input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
        <input type="hidden" name="section_name" value="{{ $filters['section_name'] }}">
        <input type="hidden" name="academic_session" value="{{ $filters['academic_session'] }}">
        <input type="hidden" name="class_name" value="{{ optional($classes->firstWhere('id', (int) $filters['class_id']))->class_name }}">

        <div class="attendance-panel mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <div>
                    <h4 class="mb-1">Mark Attendance</h4>
                    <p class="text-muted mb-0">Bulk mark the class, then adjust individual students where needed.</p>
                </div>
                <div class="d-flex flex-wrap align-items-center">
                    <select name="teacher_id" class="form-control mr-2 mb-2" style="min-width:220px">
                        <option value="">Teacher/Admin</option>
                        @foreach($teachers as $teacherSubject)
                            @php $teacher = $teacherSubject->Teacher; @endphp
                            @if($teacher)
                                <option value="{{ $teacher->id }}" @selected($editingAttendance && (string)$editingAttendance->teacher_id === (string)$teacher->id)>
                                    {{ $teacher->first_name }} {{ $teacher->last_name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <input type="search" id="studentSearch" class="form-control student-search mb-2" placeholder="Search student">
                </div>
            </div>

            <div class="mb-3">
                <span class="text-muted mr-2">Set all:</span>
                @foreach($statuses as $key => $label)
                    <button type="button" class="btn btn-sm btn-outline-primary mb-1" onclick="markAll('{{ $key }}')">{{ $label }}</button>
                @endforeach
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="attendanceTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Roll No.</th>
                            <th>Scholar No.</th>
                            <th>Student</th>
                            <th>Status</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            @php
                                $detail = $detailByStudent->get($student->id);
                                $selected = old("attendance.$student->id", optional($detail)->status ?: 'present');
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $student->form_number }}</td>
                                <td>{{ $student->scholar_no ?: $student->id }}</td>
                                <td class="student-name">{{ $student->student_name }}</td>
                                <td>
                                    @foreach($statuses as $key => $label)
                                        <label class="status-pill status-{{ $key }}" title="{{ $key === 'pl' ? 'Preparation Leave' : $label }}">
                                            <input type="radio" name="attendance[{{ $student->id }}]" value="{{ $key }}" @checked($selected === $key)>
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </td>
                                <td><input type="text" name="remarks[{{ $student->id }}]" value="{{ old("remarks.$student->id", optional($detail)->remarks) }}" class="form-control" placeholder="Optional"></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Choose class and section to load students.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="text-right mt-3">
                <button class="btn btn-primary" type="submit" @disabled($students->isEmpty() || $nonWorkingDay)>Save Attendance</button>
            </div>
        </div>
    </form>

    @if($editingAttendance)
        <div class="attendance-panel mb-4 no-print">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h4 class="mb-1">Attendance Controls</h4>
                    <p class="text-muted mb-0">
                        @if($editingAttendance->is_locked)
                            Locked attendance must be unlocked by admin before edit or delete.
                        @else
                            Lock attendance after final verification.
                        @endif
                    </p>
                </div>
                <div class="d-flex flex-wrap">
                    @if(!$editingAttendance->is_locked && $isStaffAttendanceUser)
                        <form method="POST" action="{{ route('dailyattandence.lock', $editingAttendance) }}" class="mr-2 mb-2">
                            @csrf
                            <button class="btn btn-dark" type="submit" onclick="return confirm('Lock this attendance? After locking, only admin can edit or delete it.');">Lock Attendance</button>
                        </form>
                    @endif
                    @if($editingAttendance->is_locked && !$isStaffAttendanceUser)
                        <form method="POST" action="{{ route('dailyattandence.unlock', $editingAttendance) }}" class="mr-2 mb-2">
                            @csrf
                            <button class="btn btn-warning" type="submit" onclick="return confirm('Unlock this attendance?');">Unlock</button>
                        </form>
                    @endif
                    @if(!$editingAttendance->is_locked)
                        <form method="POST" action="{{ route('dailyattandence.delete', $editingAttendance) }}" class="mb-2">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit" onclick="return confirm('Delete this attendance entry?');">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if(!$editingAttendance)
        <div class="attendance-panel">
            <h4 class="mb-3">Recent Attendance</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Date</th><th>Class</th><th>Section</th><th>Present</th><th>Absent</th><th>Leave</th><th>Half Day</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($recentAttendance as $item)
                            <tr>
                                <td>{{ optional($item->attendance_date)->format('d-m-Y') }}</td>
                                <td>{{ optional($item->className)->class_name ?: $item->class_name }}</td>
                                <td>{{ $item->section_name }}</td>
                                <td>{{ $item->present_count }}</td>
                                <td>{{ $item->absent_count }}</td>
                                <td>{{ $item->leave_count }}</td>
                                <td>{{ $item->half_day_count }}</td>
                                <td>
                                    @if($item->is_locked)
                                        <span class="badge badge-dark">Locked</span>
                                    @else
                                        <span class="badge badge-success">Open</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap">
                                        @if(!$item->is_locked)
                                            <a class="btn btn-sm btn-outline-primary mr-1 mb-1" href="{{ route('dailyattandenceUpdate', $item->id) }}">Edit</a>
                                        @endif
                                        @if(!$item->is_locked && $isStaffAttendanceUser)
                                            <form method="POST" action="{{ route('dailyattandence.lock', $item) }}" class="mr-1 mb-1">
                                                @csrf
                                                <button class="btn btn-sm btn-dark" type="submit" onclick="return confirm('Lock this attendance? After locking, only admin can edit or delete it.');">Lock</button>
                                            </form>
                                        @endif
                                        @if($item->is_locked && !$isStaffAttendanceUser)
                                            <form method="POST" action="{{ route('dailyattandence.unlock', $item) }}" class="mr-1 mb-1">
                                                @csrf
                                                <button class="btn btn-sm btn-warning" type="submit" onclick="return confirm('Unlock this attendance?');">Unlock</button>
                                            </form>
                                        @endif
                                        @if(!$item->is_locked)
                                            <form method="POST" action="{{ route('dailyattandence.delete', $item) }}" class="mb-1">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Delete this attendance entry?');">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">No attendance recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<script>
    function markAll(status) {
        document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(function(input) {
            input.checked = true;
        });
        updateCounts();
    }

    function updateCounts() {
        var counts = {present: 0, absent: 0, leave: 0, half_day: 0, pl: 0};
        document.querySelectorAll('#attendanceTable tbody tr').forEach(function(row) {
            if (row.style.display === 'none') return;
            var checked = row.querySelector('input[type="radio"]:checked');
            if (checked && counts.hasOwnProperty(checked.value)) counts[checked.value]++;
        });
        document.getElementById('totalStudents').textContent = Object.values(counts).reduce(function(a, b) { return a + b; }, 0);
        document.getElementById('presentCount').textContent = counts.present;
        document.getElementById('absentCount').textContent = counts.absent + counts.leave;
        document.getElementById('leaveCount').textContent = counts.leave;
        document.getElementById('halfDayCount').textContent = counts.half_day;
        document.getElementById('plCount').textContent = counts.pl;
    }

    document.querySelectorAll('#attendanceTable input[type="radio"]').forEach(function(input) {
        input.addEventListener('change', updateCounts);
    });
    document.getElementById('studentSearch')?.addEventListener('input', function(e) {
        var term = e.target.value.toLowerCase();
        document.querySelectorAll('#attendanceTable tbody tr').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().indexOf(term) > -1 ? '' : 'none';
        });
        updateCounts();
    });
    updateCounts();
</script>
@endsection
