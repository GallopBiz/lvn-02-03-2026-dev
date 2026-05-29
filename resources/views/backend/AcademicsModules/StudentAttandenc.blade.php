@extends('backend.layouts.main')
@section('main-container')
<style>
    .attendance-page { color: #24324b; }
    .attendance-panel { background: #fff; border: 1px solid #e7edf5; border-radius: 8px; box-shadow: 0 8px 20px rgba(36, 50, 75, .05); padding: 20px; }
    .summary-grid { display: grid; grid-template-columns: repeat(4, minmax(140px, 1fr)); gap: 12px; }
    .summary-card { border: 1px solid #e7edf5; border-radius: 8px; padding: 14px; background: #f8fbff; }
    .summary-card span { display:block; color:#69758a; font-size:12px; text-transform:uppercase; }
    .summary-card strong { font-size:24px; }
    .status-badge { border-radius:999px; padding:5px 10px; font-weight:700; font-size:12px; }
    .status-present { background:#e9f8ef; color:#167146; }
    .status-absent { background:#fff0f0; color:#b42318; }
    .status-leave { background:#fff7df; color:#936600; }
    .status-half_day { background:#eaf2ff; color:#2257a7; }
    .status-pl { background:#f0edff; color:#5740a8; }
    .day-report-table { min-width: 980px; }
    .day-cell { min-width: 30px; height: 30px; text-align:center; font-weight:700; border-radius:4px; }
    .day-present { background:#138a36; color:#fff; }
    .day-absent, .day-leave { background:#d92d20; color:#fff; }
    .day-half_day { background:#2f80ed; color:#fff; font-size:11px; }
    .day-pl { background:#7c3aed; color:#fff; }
    .day-holiday { background:#0f7a2f; color:#fff; }
    .day-empty { background:#f3f6fa; color:#9aa6b2; }
    @media print { .no-print, .main-header, .sidebar, .breadcrumb { display:none !important; } .main-content { margin:0 !important; padding:0 !important; } }
    @media (max-width: 767px) { .summary-grid { grid-template-columns:1fr 1fr; } }
</style>

<div class="main-content attendance-page pt-4">
    <div class="breadcrumb d-flex justify-content-between align-items-center flex-wrap">
        <h2 class="mb-2">Student Wise Attendance</h2>
        <div class="no-print">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">Print / PDF</button>
            <button class="btn btn-outline-success btn-sm" onclick="exportTable('studentAttendanceTable','student-attendance.xls')">Excel</button>
        </div>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="attendance-panel mb-3 no-print">
        <form method="GET" action="{{ route('student-attandence-report') }}">
            <div class="row">
                <div class="col-lg-2 col-md-4 form-group">
                    <label>Academic Session</label>
                    <input type="text" name="academic_session" value="{{ $filters['academic_session'] }}" class="form-control">
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label>Month</label>
                    <input type="month" name="month" value="{{ $filters['month'] }}" class="form-control">
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label>From</label>
                    <input type="date" name="from_date" value="{{ $filters['from_date'] }}" class="form-control">
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label>To</label>
                    <input type="date" name="to_date" value="{{ $filters['to_date'] }}" class="form-control">
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label>Class</label>
                    <select name="class_id" class="form-control" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected((string)$filters['class_id'] === (string)$class->id)>{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label>Section</label>
                    <select name="section_name" class="form-control" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach($sections as $section)
                            <option value="{{ $section }}" @selected($filters['section_name'] === $section)>{{ $section }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 col-md-6 form-group">
                    <label>Student Search</label>
                    <input type="text" name="student_search" value="{{ $studentSearch }}" class="form-control" placeholder="Name, roll no, scholar no">
                </div>
                <div class="col-lg-4 col-md-6 form-group">
                    <label>Student</label>
                    <select name="student_id" class="form-control">
                        <option value="">Select student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" @selected((int)$filters['student_id'] === (int)$student->id)>
                                {{ $student->student_name }} @if($student->form_number) - {{ $student->form_number }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 form-group d-flex align-items-end">
                    <button class="btn btn-primary mr-2">Search</button>
                    <a class="btn btn-outline-primary mr-2" href="#studentDailyMonthReport">Show Day Wise Report</a>
                    <a class="btn btn-outline-secondary" href="{{ route('student-attandence-report') }}">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="summary-grid mb-3">
        <div class="summary-card"><span>Total Working Days</span><strong>{{ $summary['total_students'] }}</strong></div>
        <div class="summary-card"><span>Total Present</span><strong>{{ $summary['present'] }}</strong></div>
        <div class="summary-card"><span>Total Absent</span><strong>{{ $summary['absent'] }}</strong></div>
        <div class="summary-card"><span>Attendance %</span><strong>{{ number_format($summary['percentage'], 2) }}%</strong></div>
    </div>

    <div class="attendance-panel">
        <h4 class="mb-1">{{ $selectedStudent ? $selectedStudent->student_name : 'Attendance History' }}</h4>
        <p class="text-muted">{{ $selectedStudent ? 'Complete attendance history for the selected filters. Sundays and HRMS public holidays are excluded.' : 'Select a student to view attendance history.' }}</p>
        <div class="table-responsive">
            <table class="table table-hover" id="studentAttendanceTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Status</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $row)
                        <tr>
                            <td>{{ optional($row->attendance->attendance_date)->format('d-m-Y') }}</td>
                            <td>{{ optional($row->attendance->className)->class_name ?: $row->attendance->class_name }}</td>
                            <td>{{ $row->attendance->section_name }}</td>
                            <td><span class="status-badge status-{{ $row->status }}">{{ ucwords(str_replace('_', ' ', $row->status)) }}</span></td>
                            <td>{{ $row->remarks }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No attendance data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($history, 'links'))
            {{ $history->links('pagination::bootstrap-4') }}
        @endif
    </div>

    <div class="attendance-panel mt-3" id="studentDailyMonthReport">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div>
                <h4 class="mb-1">Daily Month Wise Report</h4>
                <p class="text-muted mb-0">Day-wise status for the selected student and month.</p>
            </div>
            @if($monthlyDetail)
                <strong>{{ $monthlyDetail['month_label'] }}</strong>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-bordered day-report-table" id="studentMonthWiseReportTable">
                <thead>
                    <tr>
                        <th>Student</th>
                        @if($monthlyDetail)
                            @foreach($monthlyDetail['days'] as $day)
                                <th>{{ $day }}</th>
                            @endforeach
                        @endif
                        <th>Days</th>
                        <th>Present</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    @if($monthlyDetail && $monthlyDetail['rows']->isNotEmpty())
                        @foreach($monthlyDetail['rows'] as $row)
                            <tr>
                                <td>{{ $row['student_name'] }}</td>
                                @foreach($monthlyDetail['days'] as $day)
                                    @php $cell = $row['cells'][$day]; @endphp
                                    <td class="day-cell {{ $cell['class'] }}" title="{{ $cell['title'] }}">{{ $cell['label'] }}</td>
                                @endforeach
                                <td>{{ $row['working_days'] }}</td>
                                <td>{{ number_format($row['present_days'], 2) }}</td>
                                <td>{{ number_format($row['percentage'], 2) }}%</td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td colspan="36" class="text-center text-muted">Select a student and month, then click Search.</td></tr>
                    @endif
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
