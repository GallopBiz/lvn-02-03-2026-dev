@extends('backend.layouts.main')
@section('main-container')
@php
    $detailByStudent = $editingAttendance ? $editingAttendance->details->keyBy('student_id') : collect();
    $isStaffAttendanceUser = isset($isStaffAttendanceUser) ? $isStaffAttendanceUser : (auth()->guard('staff')->check() && !auth()->guard('web')->check());
@endphp
<style>
    .attendance-page { color: #24324b; }
    .attendance-toolbar, .attendance-panel { background: #fff; border: 1px solid #e7edf5; border-radius: 8px; box-shadow: 0 8px 20px rgba(36, 50, 75, .05); }
    .attendance-toolbar { padding: 18px; }
    .attendance-panel { padding: 20px; }
    .summary-grid { display: grid; grid-template-columns: repeat(5, minmax(120px, 1fr)); gap: 12px; }
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
    .attendance-panel .pagination { margin-bottom: 0; }
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
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-outline-secondary btn-sm mr-2" onclick="goBackToSource('{{ url()->previous() !== url()->current() ? url()->previous() : route('academic.attendance.monthly-report') }}')">
                <i class="i-Arrow-Left mr-1 font-weight-bold"></i> Back
            </button>
            <a href="{{ route('Attandencereports') }}" class="btn btn-outline-primary btn-sm">Reports</a>
        </div>
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
    @if($isFutureDate)
        <div class="alert alert-warning">
            Attendance cannot be marked for a future date.
        </div>
    @endif

    <div id="attendanceAlreadyMarkedContainer">
        @if(isset($existingAttendance) && $existingAttendance)
            <div class="alert alert-danger d-flex justify-content-between align-items-center flex-wrap shadow-sm mb-3" id="attendanceAlreadyMarkedAlert" role="alert">
                <div class="d-flex align-items-center mr-3 my-1">
                    <i class="i-Information mr-2 font-weight-bold" style="font-size: 24px;"></i>
                    <div>
                        <strong class="d-block" style="font-size: 15px;">Attendance has already been submitted for this date.</strong>
                        <span class="small">
                            Attendance for <strong>{{ optional($selectedClass)->class_name ?: $filters['class_id'] }} (Section: {{ $filters['section_name'] ?: 'All' }})</strong> on <strong>{{ \Carbon\Carbon::parse($filters['date'])->format('d-m-Y') }}</strong> was already submitted on {{ optional($existingAttendance->created_at)->format('d-m-Y h:i A') ?: '-' }}.
                        </span>
                    </div>
                </div>
                <div class="my-1">
                    @if(!$existingAttendance->is_locked)
                        <a href="{{ route('dailyattandenceUpdate', $existingAttendance->id) }}" class="btn btn-danger btn-sm font-weight-bold shadow-sm">
                            <i class="i-Pen-2 mr-1"></i> Edit Existing Attendance
                        </a>
                    @else
                        <span class="badge badge-dark p-2 font-weight-bold">Locked</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="summary-grid mb-3">
        <div class="summary-card"><span>Total Students</span><strong id="totalStudents">{{ $summary['total_students'] }}</strong></div>
        <div class="summary-card"><span>Present</span><strong id="presentCount">{{ $summary['present'] }}</strong></div>
        <div class="summary-card"><span>Absent</span><strong id="absentCount">{{ $summary['absent'] }}</strong></div>
        <div class="summary-card"><span>Leave</span><strong id="leaveCount">{{ $summary['leave'] }}</strong></div>
        <div class="summary-card"><span>PL</span><strong id="plCount">{{ $summary['pl'] }}</strong></div>
    </div>

    <div class="attendance-toolbar mb-3">
        <form method="GET" action="{{ $editingAttendance ? route('dailyattandenceUpdate', $editingAttendance->id) : route('dailyattandence') }}" id="filterForm">
            <div class="row">
                <div class="col-lg-2 col-md-4 form-group">
                    <label>Date</label>
                    <input type="date" name="date" id="attendanceDateInput" value="{{ $filters['date'] }}" max="{{ now()->toDateString() }}" class="form-control" onchange="checkAttendanceBeforeSubmit(this)">
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label>Academic Session</label>
                    <input type="text" name="academic_session" value="{{ $filters['academic_session'] }}" class="form-control" placeholder="2026-2027">
                </div>
                <div class="col-lg-3 col-md-4 form-group">
                    <label>Class</label>
                    <select name="class_id" id="classSelectInput" class="form-control" onchange="var sec = this.form.querySelector('[name=section_name]'); if(sec) sec.value = ''; checkAttendanceBeforeSubmit(this);">
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" data-name="{{ $class->class_name }}" @selected((string)$filters['class_id'] === (string)$class->id || (optional($selectedClass)->class_name === $class->class_name))>{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label>Section</label>
                    <select name="section_name" id="sectionSelectInput" class="form-control" onchange="checkAttendanceBeforeSubmit(this)">
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
                <button type="button" class="btn btn-sm btn-outline-secondary mb-1" onclick="clearAll()">Clear (Blank)</button>
            </div>

            @if(isset($existingAttendance) && $existingAttendance)
                <div class="alert alert-warning mb-3">
                    <i class="i-Information mr-1 font-weight-bold"></i> <strong>Attendance already marked:</strong> Duplicate attendance submission is disabled for this date. Click <strong>Edit Existing Attendance</strong> above to make changes.
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover" id="attendanceTable">
                    <thead>
                        <tr>
                            <th>#</th>
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
                                $selected = old("attendance.$student->id", optional($detail)->status);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $student->scholar_no ?: $student->id }}</td>
                                <td class="student-name">{{ $student->student_name }}</td>
                                <td>
                                    @foreach($statuses as $key => $label)
                                        <label class="status-pill status-{{ $key }}" title="{{ $key === 'pl' ? 'Preparation Leave' : $label }}">
                                            <input type="radio" name="attendance[{{ $student->id }}]" value="{{ $key }}" @checked($selected === $key) @disabled(isset($existingAttendance) && $existingAttendance)>
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </td>
                                <td><input type="text" name="remarks[{{ $student->id }}]" value="{{ old("remarks.$student->id", optional($detail)->remarks) }}" class="form-control" placeholder="Optional" @disabled(isset($existingAttendance) && $existingAttendance)></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Choose class and section to load students.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="text-right mt-3">
                <button class="btn btn-primary" id="saveAttendanceBtn" type="submit" @disabled($students->isEmpty() || $nonWorkingDay || $isFutureDate || (isset($existingAttendance) && $existingAttendance))>Save Attendance</button>
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
                    @canDelete('Academic > Attendance > Daily Attendance')
                    @if(!$editingAttendance->is_locked)
                        <form method="POST" action="{{ route('dailyattandence.delete', $editingAttendance) }}" class="mb-2">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit" onclick="return confirm('Delete this attendance entry?');">Delete</button>
                        </form>
                    @endif
                    @endcanDelete
                </div>
            </div>
        </div>
    @endif

    @if(!$editingAttendance)
        <div class="attendance-panel">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <div class="mb-2">
                    <h4 class="mb-1">Recent Attendance</h4>
                    <span class="text-muted small">Arranged by date. Check already filled attendance by date or search.</span>
                </div>
                <form method="GET" action="{{ route('dailyattandence') }}" class="d-flex align-items-center flex-wrap mb-2" style="gap: 10px;">
                    @if(request('class_id'))
                        <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                    @endif
                    @if(request('section_name'))
                        <input type="hidden" name="section_name" value="{{ request('section_name') }}">
                    @endif
                    @if(request('per_page'))
                        <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    @endif
                    <div class="d-flex align-items-center">
                        <label class="mr-2 mb-0 small font-weight-bold text-muted text-nowrap">Date:</label>
                        <input type="date" name="history_date" value="{{ request('history_date', request('filter_date')) }}" class="form-control form-control-sm" style="width: 155px;" onchange="this.form.submit()">
                    </div>
                    @if(request('history_date') || request('filter_date'))
                        <a href="{{ route('dailyattandence', array_filter(['class_id' => request('class_id'), 'section_name' => request('section_name'), 'per_page' => request('per_page')])) }}" class="btn btn-outline-danger btn-sm text-nowrap" title="Clear Date Filter">
                            <i class="i-Close mr-1"></i> Clear Date
                        </a>
                    @endif
                    <div class="d-flex align-items-center">
                        <input type="search" id="recentAttendanceSearch" class="form-control form-control-sm" style="width: 210px;" placeholder="Instant search...">
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table" id="recentAttendanceTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Marked By</th>
                            <th>Total Students</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Leave</th>
                            <th>PL</th>
                            <th>Submitted Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendance as $item)
                            <tr>
                                <td>{{ optional($item->attendance_date)->format('d-m-Y') }}</td>
                                <td>{{ optional($item->className)->class_name ?: $item->class_name }}</td>
                                <td>{{ $item->section_name }}</td>
                                <td>
                                    @if(optional($item->teacher)->first_name)
                                        <span class="font-weight-bold">{{ trim($item->teacher->first_name . ' ' . $item->teacher->last_name) }}</span>
                                    @else
                                        <span class="text-muted">Admin / School</span>
                                    @endif
                                </td>
                                <td>{{ $item->total_students }}</td>
                                <td>{{ $item->present_count }}</td>
                                <td>{{ max(0, $item->absent_count - $item->leave_count) }}</td>
                                <td>{{ $item->leave_count }}</td>
                                <td>{{ $item->pl_count }}</td>
                                <td>
                                    <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px;">
                                        <i class="i-Clock mr-1"></i>{{ optional($item->created_at)->format('d-m-Y h:i A') ?: '-' }}
                                    </span>
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
                                        @canDelete('Academic > Attendance > Daily Attendance')
                                        @if(!$item->is_locked)
                                            <form method="POST" action="{{ route('dailyattandence.delete', $item) }}" class="mb-1">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Delete this attendance entry?');">Delete</button>
                                            </form>
                                        @endif
                                        @endcanDelete
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    @if(request('history_date') || request('filter_date'))
                                        No attendance recorded for {{ \Carbon\Carbon::parse(request('history_date', request('filter_date')))->format('d-m-Y') }}.
                                    @else
                                        No attendance recorded yet.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($recentAttendance, 'links'))
                <div class="d-flex justify-content-between align-items-center flex-wrap mt-3 pt-3 border-top" style="gap: 12px;">
                    <div class="d-flex align-items-center flex-wrap" style="gap: 15px;">
                        <form method="GET" action="{{ route('dailyattandence') }}" class="d-inline-flex align-items-center mb-0">
                            @if(request('history_date'))
                                <input type="hidden" name="history_date" value="{{ request('history_date') }}">
                            @endif
                            @if(request('filter_date'))
                                <input type="hidden" name="filter_date" value="{{ request('filter_date') }}">
                            @endif
                            @if(request('class_id'))
                                <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                            @endif
                            @if(request('section_name'))
                                <input type="hidden" name="section_name" value="{{ request('section_name') }}">
                            @endif
                            <label class="mr-2 mb-0 small text-muted font-weight-bold text-nowrap">Show:</label>
                            <select name="per_page" class="form-control form-control-sm" style="width: 70px;" onchange="this.form.submit()">
                                <option value="5" @selected(request('per_page', 5) == 5)>5</option>
                                <option value="10" @selected(request('per_page') == 10)>10</option>
                                <option value="25" @selected(request('per_page') == 25)>25</option>
                                <option value="50" @selected(request('per_page') == 50)>50</option>
                            </select>
                            <span class="ml-2 small text-muted text-nowrap">entries</span>
                        </form>
                        <span class="text-muted small text-nowrap">
                            @if($recentAttendance->total() > 0)
                                Showing {{ $recentAttendance->firstItem() ?? 1 }} to {{ $recentAttendance->lastItem() ?? $recentAttendance->total() }} of {{ $recentAttendance->total() }} records
                            @else
                                Showing 0 records
                            @endif
                        </span>
                    </div>
                    <div class="d-flex align-items-center">
                        @if($recentAttendance->hasPages())
                            {!! $recentAttendance->links('pagination::bootstrap-4') !!}
                        @else
                            <nav aria-label="Attendance pagination">
                                <ul class="pagination mb-0">
                                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">&lsaquo;</span></li>
                                    <li class="page-item active" aria-current="page"><span class="page-link">1</span></li>
                                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">&rsaquo;</span></li>
                                </ul>
                            </nav>
                        @endif
                    </div>
                </div>
            @endif
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

    function clearAll() {
        document.querySelectorAll('#attendanceTable input[type="radio"]').forEach(function(input) {
            input.checked = false;
        });
        updateCounts();
    }

    function updateCounts() {
        var counts = {present: 0, absent: 0, leave: 0, pl: 0};
        var total = 0;
        document.querySelectorAll('#attendanceTable tbody tr').forEach(function(row) {
            if (row.style.display === 'none') return;
            if (row.querySelector('input[type="radio"]')) total++;
            var checked = row.querySelector('input[type="radio"]:checked');
            if (checked && counts.hasOwnProperty(checked.value)) counts[checked.value]++;
        });
        document.getElementById('totalStudents').textContent = total;
        document.getElementById('presentCount').textContent = counts.present;
        document.getElementById('absentCount').textContent = counts.absent;
        document.getElementById('leaveCount').textContent = counts.leave;
        if (document.getElementById('halfDayCount')) {
            document.getElementById('halfDayCount').textContent = counts.half_day || 0;
        }
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
    document.getElementById('recentAttendanceSearch')?.addEventListener('input', function(e) {
        var term = e.target.value.toLowerCase();
        document.querySelectorAll('#recentAttendanceTable tbody tr').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().indexOf(term) > -1 ? '' : 'none';
        });
    });
    updateCounts();

    function checkAttendanceBeforeSubmit(triggerElement) {
        var form = document.getElementById('filterForm');
        var dateInput = form.querySelector('[name="date"]');
        var dateVal = dateInput ? dateInput.value : '';
        var classSelect = form.querySelector('[name="class_id"]');
        var secSelect = form.querySelector('[name="section_name"]');
        var classId = classSelect ? classSelect.value : '';
        var sectionName = secSelect ? secSelect.value : '';
        var sessionInput = form.querySelector('[name="academic_session"]');
        var sessionVal = sessionInput ? sessionInput.value : '';

        if (!dateVal || !classId) {
            form.submit();
            return;
        }

        var checkUrl = "{{ route('dailyattandence.check-marked') }}?date=" + encodeURIComponent(dateVal) +
            "&class_id=" + encodeURIComponent(classId) +
            "&section_name=" + encodeURIComponent(sectionName) +
            "&academic_session=" + encodeURIComponent(sessionVal);

        fetch(checkUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.already_marked) {
                renderAlreadyMarkedAlert(data);
                form.submit();
            } else {
                clearAlreadyMarkedAlert();
                form.submit();
            }
        })
        .catch(function() {
            form.submit();
        });
    }

    function renderAlreadyMarkedAlert(data) {
        var container = document.getElementById('attendanceAlreadyMarkedContainer');
        if (!container) return;
        var editBtnHtml = data.edit_url && !data.is_locked
            ? '<a href="' + data.edit_url + '" class="btn btn-danger btn-sm font-weight-bold shadow-sm"><i class="i-Pen-2 mr-1"></i> Edit Existing Attendance</a>'
            : (data.is_locked ? '<span class="badge badge-dark p-2 font-weight-bold">Locked</span>' : '');

        container.innerHTML = '<div class="alert alert-danger d-flex justify-content-between align-items-center flex-wrap shadow-sm mb-3" id="attendanceAlreadyMarkedAlert" role="alert">' +
            '<div class="d-flex align-items-center mr-3 my-1">' +
                '<i class="i-Information mr-2 font-weight-bold" style="font-size: 24px;"></i>' +
                '<div>' +
                    '<strong class="d-block" style="font-size: 15px;">' + (data.message || 'Attendance has already been submitted for this date.') + '</strong>' +
                    '<span class="small">Attendance for <strong>' + (data.class_name || '') + ' (' + (data.section_name || 'All') + ')</strong> on <strong>' + (data.attendance_date || '') + '</strong> was already submitted' + (data.submitted_at ? ' on ' + data.submitted_at : '') + '.</span>' +
                '</div>' +
            '</div>' +
            '<div class="my-1">' + editBtnHtml + '</div>' +
        '</div>';

        var saveBtn = document.getElementById('saveAttendanceBtn');
        if (saveBtn) saveBtn.disabled = true;

        document.querySelectorAll('#attendanceTable input[type="radio"]').forEach(function(r) {
            r.disabled = true;
        });
    }

    function clearAlreadyMarkedAlert() {
        var container = document.getElementById('attendanceAlreadyMarkedContainer');
        if (container) container.innerHTML = '';
        var saveBtn = document.getElementById('saveAttendanceBtn');
        if (saveBtn) saveBtn.disabled = false;
        document.querySelectorAll('#attendanceTable input[type="radio"]').forEach(function(r) {
            r.disabled = false;
        });
    }

    var dateCheckTimeout = null;
    document.getElementById('attendanceDateInput')?.addEventListener('input', function() {
        var input = this;
        clearTimeout(dateCheckTimeout);
        dateCheckTimeout = setTimeout(function() {
            var dateVal = input.value;
            if (!dateVal || dateVal.length < 10) return;
            var form = document.getElementById('filterForm');
            var classSelect = form.querySelector('[name="class_id"]');
            var secSelect = form.querySelector('[name="section_name"]');
            var classId = classSelect ? classSelect.value : '';
            var sectionName = secSelect ? secSelect.value : '';
            var sessionInput = form.querySelector('[name="academic_session"]');
            var sessionVal = sessionInput ? sessionInput.value : '';

            var checkUrl = "{{ route('dailyattandence.check-marked') }}?date=" + encodeURIComponent(dateVal) +
                "&class_id=" + encodeURIComponent(classId) +
                "&section_name=" + encodeURIComponent(sectionName) +
                "&academic_session=" + encodeURIComponent(sessionVal);

            fetch(checkUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.already_marked) {
                    renderAlreadyMarkedAlert(data);
                } else {
                    clearAlreadyMarkedAlert();
                }
            })
            .catch(function() {});
        }, 200);
    });

    document.getElementById('attendanceForm')?.addEventListener('submit', function(e) {
        if (document.getElementById('attendanceAlreadyMarkedAlert')) {
            e.preventDefault();
            alert('Attendance has already been submitted for this date.');
            return false;
        }
        var checked = document.querySelectorAll('#attendanceTable input[type="radio"]:checked');
        if (checked.length === 0) {
            e.preventDefault();
            alert('Please select attendance status for students before saving.');
            return false;
        }
    });

    (function() {
        try {
            var pageKey = 'back_source_' + window.location.pathname;
            var ref = document.referrer;
            if (ref && !ref.includes(window.location.pathname)) {
                sessionStorage.setItem(pageKey, ref);
            }
        } catch (e) {}
    })();

    function goBackToSource(fallbackUrl) {
        try {
            var pageKey = 'back_source_' + window.location.pathname;
            var saved = sessionStorage.getItem(pageKey);
            if (saved && saved !== window.location.href && !saved.includes(window.location.pathname)) {
                sessionStorage.removeItem(pageKey);
                window.location.href = saved;
                return;
            }
        } catch (e) {}
        if (document.referrer && document.referrer !== window.location.href && !document.referrer.includes(window.location.pathname)) {
            window.location.href = document.referrer;
            return;
        }
        if (window.history.length > 1) {
            window.history.back();
            return;
        }
        window.location.href = fallbackUrl;
    }
</script>
@endsection
