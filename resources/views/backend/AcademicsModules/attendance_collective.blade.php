@extends('backend.layouts.main')
@section('main-container')
<style>
    .collective-page { color:#24324b; }
    .collective-panel { background:#fff; border:1px solid #e7edf5; border-radius:8px; box-shadow:0 8px 20px rgba(36,50,75,.05); padding:20px; }
    .summary-grid { display:grid; grid-template-columns:repeat(6,minmax(120px,1fr)); gap:12px; }
    .summary-card { border:1px solid #e7edf5; border-radius:8px; padding:14px; background:#f8fbff; }
    .summary-card span { display:block; color:#69758a; font-size:12px; text-transform:uppercase; }
    .summary-card strong { font-size:24px; }
    .badge-low { background:#fff0f0; color:#b42318; border:1px solid #ffd0d0; }
    .badge-ok { background:#e9f8ef; color:#167146; border:1px solid #bfe9ce; }
    .table thead th { background:#f4f7fb; white-space:nowrap; }
    @media print { .no-print, .main-header, .sidebar, .breadcrumb { display:none !important; } .main-content { margin:0 !important; padding:0 !important; } }
    @media (max-width: 991px) { .summary-grid { grid-template-columns:repeat(2,minmax(130px,1fr)); } }
</style>

@php
    $belowCount = $collectiveRows->where('is_below_minimum', true)->count();
    $avgPercentage = $collectiveRows->count() ? round($collectiveRows->avg('percentage'), 2) : 0;
    $selectedClass = $classes->firstWhere('id', (int) $filters['class_id']);
@endphp

<div class="main-content collective-page pt-4">
    <div class="breadcrumb d-flex justify-content-between align-items-center flex-wrap">
        <h2 class="mb-2">Student Collective Attendance</h2>
        <div class="no-print">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">Print / PDF</button>
            <button class="btn btn-outline-success btn-sm" onclick="exportTable('collectiveTable','student-collective-attendance.xls')">Excel</button>
        </div>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    @if($editingCollective)
        <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap">
            <span>Editing saved collective attendance #{{ $editingCollective->id }}. Change criteria if needed, then click Update Collective.</span>
            <a href="{{ route('academic.attendance.collective') }}" class="btn btn-sm btn-outline-secondary">Cancel Edit</a>
        </div>
    @endif

    <div class="collective-panel mb-3 no-print">
        <form method="GET" action="{{ route('academic.attendance.collective') }}" id="collectiveFilterForm">
            <div class="row">
                <div class="col-lg-2 col-md-4 form-group">
                    <label>Academic Session</label>
                    <input type="text" name="academic_session" value="{{ $filters['academic_session'] }}" class="form-control" placeholder="2025-2026">
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="form-control">
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="form-control">
                </div>
                <div class="col-lg-3 col-md-4 form-group">
                    <label>Teacher</label>
                    <select name="teacher_id" class="form-control">
                        <option value="">Teacher/Admin</option>
                        @foreach($teachers as $teacherSubject)
                            @php $teacher = $teacherSubject->Teacher; @endphp
                            @if($teacher)
                                <option value="{{ $teacher->id }}" @selected((string)$filters['teacher_id'] === (string)$teacher->id)>
                                    {{ $teacher->first_name }} {{ $teacher->last_name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-4 form-group">
                    <label>Class</label>
                    <select name="class_id" class="form-control" onchange="document.getElementById('collectiveFilterForm').submit()">
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected((string)$filters['class_id'] === (string)$class->id)>{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label>Section</label>
                    <select name="section_name" class="form-control" onchange="document.getElementById('collectiveFilterForm').submit()">
                        <option value="">Select section</option>
                        @foreach($sections as $section)
                            <option value="{{ $section }}" @selected($filters['section_name'] === $section)>{{ $section }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 col-md-6 form-group">
                    <label>Exam</label>
                    <select name="exam_id" class="form-control" onchange="setExamName(this)">
                        <option value="">Session Collective</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" data-name="{{ $exam->exam_name }}" @selected((int)$filters['exam_id'] === (int)$exam->id)>
                                {{ $exam->exam_name }}
                                @if(optional($exam->classInfo)->class_name) - {{ optional($exam->classInfo)->class_name }} @endif
                                @if($exam->session_year) ({{ str_replace('_', '-', $exam->session_year) }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-6 col-md-6 form-group d-flex align-items-end">
                    <button class="btn btn-primary mr-2">Pick From Daily Attendance</button>
                    <a class="btn btn-outline-secondary" href="{{ route('academic.attendance.collective') }}">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="summary-grid mb-3">
        <div class="summary-card"><span>Total Days</span><strong>{{ $totalDays }}</strong></div>
        <div class="summary-card"><span>Sundays</span><strong>{{ $sundays }}</strong></div>
        <div class="summary-card"><span>Holidays</span><strong>{{ $holidays }}</strong></div>
        <div class="summary-card"><span>Working Days</span><strong>{{ $workingDays }}</strong></div>
        <div class="summary-card"><span>Students</span><strong>{{ $collectiveRows->count() }}</strong></div>
        <div class="summary-card"><span>Below 75%</span><strong>{{ $belowCount }}</strong></div>
    </div>

    <div class="collective-panel mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div>
                <h4 class="mb-1">Student Wise Details</h4>
                <p class="text-muted mb-0">Working Days = Total selected days - Sundays - HRMS holiday/vacation/closure ranges. Present Days count only Present attendance records.</p>
            </div>
            <form method="POST" action="{{ route('academic.attendance.collective.store') }}" class="no-print">
                @csrf
                @if($editingCollective)
                    <input type="hidden" name="collective_id" value="{{ $editingCollective->id }}">
                @endif
                <input type="hidden" name="teacher_id" value="{{ $filters['teacher_id'] }}">
                <input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
                <input type="hidden" name="class_name" value="{{ optional($selectedClass)->class_name }}">
                <input type="hidden" name="section_name" value="{{ $filters['section_name'] }}">
                <input type="hidden" name="exam_id" value="{{ $filters['exam_id'] }}">
                <input type="hidden" name="exam_name" value="{{ $filters['exam_name'] }}">
                <input type="hidden" name="academic_session" value="{{ $filters['academic_session'] }}">
                <input type="hidden" name="start_date" value="{{ $filters['start_date'] }}">
                <input type="hidden" name="end_date" value="{{ $filters['end_date'] }}">
                <button class="btn btn-success" @disabled($collectiveRows->isEmpty() || $workingDays <= 0)>{{ $editingCollective ? 'Update Collective' : 'Save Collective' }}</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover" id="collectiveTable">
                <thead>
                    <tr>
                        <th>SNo</th>
                        <th>Student Name</th>
                        <th>Scholar No</th>
                        <th>Present Days</th>
                        <th>Working Days</th>
                        <th>Attendance %</th>
                        <th>CBSE 75%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($collectiveRows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row['student_name'] }}</td>
                            <td>{{ $row['scholar_no'] }}</td>
                            <td>{{ number_format($row['present_days'], 2) }}</td>
                            <td>{{ $row['working_days'] }}</td>
                            <td>{{ number_format($row['percentage'], 2) }}%</td>
                            <td>
                                @if($row['is_below_minimum'])
                                    <span class="badge badge-low">Below 75%</span>
                                @else
                                    <span class="badge badge-ok">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Select criteria and pick from daily attendance.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="collective-panel no-print">
        <h4 class="mb-3">Previously Saved Collective Attendance</h4>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>SNO</th>
                        <th>Exam Name</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Session</th>
                        <th>Working Days</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($savedCollectives as $saved)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $saved->exam_name ?: 'Session Collective' }}</td>
                            <td>{{ optional($saved->className)->class_name ?: $saved->class_name }}</td>
                            <td>{{ $saved->section_name }}</td>
                            <td>{{ $saved->academic_session }}</td>
                            <td>{{ $saved->working_days }}</td>
                            <td>
                                <a href="{{ route('academic.attendance.collective', ['edit_collective_id' => $saved->id]) }}" class="btn btn-sm btn-outline-info">Edit</a>
                                <a href="{{ route('academic.attendance.collective.print', $saved) }}" target="_blank" class="btn btn-sm btn-outline-primary">Print</a>
                                <form method="POST" action="{{ route('academic.attendance.collective.delete', $saved) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete saved collective attendance?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No saved collective attendance yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function exportTable(tableId, filename) {
        var table = document.getElementById(tableId);
        var html = table.outerHTML.replace(/ /g, '%20');
        var link = document.createElement('a');
        link.href = 'data:application/vnd.ms-excel,' + html;
        link.download = filename;
        link.click();
    }
</script>
@endsection
