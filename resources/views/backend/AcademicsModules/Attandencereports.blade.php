@extends('backend.layouts.main')
@section('main-container')
<style>
    .attendance-page { color:#24324b; }
    .attendance-panel { background:#fff; border:1px solid #e7edf5; border-radius:8px; box-shadow:0 8px 20px rgba(36,50,75,.05); padding:20px; }
    .summary-grid { display:grid; grid-template-columns:repeat(6,minmax(130px,1fr)); gap:12px; }
    .summary-card { border:1px solid #e7edf5; border-radius:8px; padding:14px; background:#f8fbff; }
    .summary-card span { display:block; color:#69758a; font-size:12px; text-transform:uppercase; }
    .summary-card strong { font-size:24px; }
    .bar-track { height:10px; background:#edf2f7; border-radius:999px; overflow:hidden; min-width:120px; }
    .bar-fill { height:100%; background:#2f80ed; }
    .table thead th { background:#f4f7fb; white-space:nowrap; }
    .day-report-table { min-width: 1200px; }
    .day-cell { min-width: 30px; height: 30px; text-align:center; font-weight:700; border-radius:4px; color:#24324b; }
    .day-present { background:#138a36; color:#fff; }
    .day-absent, .day-leave { background:#d92d20; color:#fff; }
    .day-half_day { background:#2f80ed; color:#fff; font-size:11px; }
    .day-pl { background:#7c3aed; color:#fff; }
    .day-holiday { background:#0f7a2f; color:#fff; }
    .day-empty { background:#f3f6fa; color:#9aa6b2; }
    @media print { .no-print, .main-header, .sidebar, .breadcrumb { display:none !important; } .main-content { margin:0 !important; padding:0 !important; } }
    @media (max-width:991px) { .summary-grid { grid-template-columns:repeat(2,minmax(130px,1fr)); } }
</style>

@php
    $marked = max(1, $totals['present'] + $totals['absent'] + $totals['half_day']);
    $presentPercentage = round(($totals['present'] / $marked) * 100, 2);
@endphp

<div class="main-content attendance-page pt-4">
    <div class="breadcrumb d-flex justify-content-between align-items-center flex-wrap">
        <h2 class="mb-2">Attendance Reports</h2>
        <div class="no-print">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">Print / PDF</button>
            <button class="btn btn-outline-success btn-sm" onclick="exportTable('attendanceReportTable','attendance-report.xls')">Excel</button>
        </div>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="attendance-panel mb-3 no-print">
        <form method="GET" action="{{ route('Attandencereports') }}">
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
                    <select name="section_name" class="form-control">
                        <option value="">All</option>
                        @foreach($sections as $section)
                            <option value="{{ $section }}" @selected($filters['section_name'] === $section)>{{ $section }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 col-md-6 form-group">
                    <label>Student</label>
                    <select name="student_id" class="form-control">
                        <option value="">Class wise report</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" @selected((int)$filters['student_id'] === (int)$student->id)>
                                {{ $student->student_name }} @if($student->form_number) - {{ $student->form_number }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 col-md-6 form-group d-flex align-items-end">
                    <button class="btn btn-primary mr-2">Generate</button>
                    <a class="btn btn-outline-primary mr-2" href="#dailyMonthWiseReport">Show Day Wise Report</a>
                    <a class="btn btn-outline-secondary" href="{{ route('Attandencereports') }}">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="summary-grid mb-3">
        <div class="summary-card"><span>Total Marked</span><strong>{{ $marked }}</strong></div>
        <div class="summary-card"><span>Present</span><strong>{{ $totals['present'] }}</strong></div>
        <div class="summary-card"><span>Absent</span><strong>{{ $totals['absent'] }}</strong></div>
        <div class="summary-card"><span>Leave</span><strong>{{ $totals['leave'] }}</strong></div>
        <div class="summary-card"><span>PL</span><strong>{{ $totals['pl'] }}</strong></div>
        <div class="summary-card"><span>Present %</span><strong>{{ number_format($presentPercentage, 2) }}%</strong></div>
    </div>

    <div class="attendance-panel mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div>
                <h4 class="mb-1">Graphical Summary</h4>
                <p class="text-muted mb-0">Present percentage across selected working-day records. Sundays and HRMS public holidays are excluded.</p>
            </div>
            <strong>{{ number_format($presentPercentage, 2) }}%</strong>
        </div>
        <div class="bar-track"><div class="bar-fill" style="width: {{ min(100, $presentPercentage) }}%"></div></div>
    </div>

    <div class="attendance-panel">
        <h4 class="mb-3">Attendance Records</h4>
        <div class="table-responsive">
            <table class="table table-hover" id="attendanceReportTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Total</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Leave</th>
                        <th>Half Day</th>
                        <th>PL</th>
                        <th>Present %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        @php
                            $rowTotal = max(1, $record->present_count + $record->absent_count + $record->half_day_count);
                            $rowPercentage = round(($record->present_count / $rowTotal) * 100, 2);
                        @endphp
                        <tr>
                            <td>{{ optional($record->attendance_date)->format('d-m-Y') }}</td>
                            <td>{{ optional($record->className)->class_name ?: $record->class_name }}</td>
                            <td>{{ $record->section_name }}</td>
                            <td>{{ $record->total_students }}</td>
                            <td>{{ $record->present_count }}</td>
                            <td>{{ $record->absent_count }}</td>
                            <td>{{ $record->leave_count }}</td>
                            <td>{{ $record->half_day_count }}</td>
                            <td>{{ $record->pl_count }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="mr-2">{{ number_format($rowPercentage, 2) }}%</span>
                                    <div class="bar-track"><div class="bar-fill" style="width: {{ min(100, $rowPercentage) }}%"></div></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted">No attendance data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $records->links('pagination::bootstrap-4') }}
    </div>

    <div class="attendance-panel mt-3" id="dailyMonthWiseReport">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div>
                <h4 class="mb-1">Daily Student Attendance Report</h4>
                <p class="text-muted mb-0">Month-wise day detail with status colors. Sundays and HRMS holidays are shown as S/H.</p>
            </div>
            @if($monthWiseReport)
                <strong>{{ $monthWiseReport['month_label'] }}</strong>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-bordered day-report-table" id="dailyMonthWiseReportTable">
                <thead>
                    <tr>
                        <th>SNo</th>
                        <th>Roll No</th>
                        <th>Scholar No</th>
                        <th>Student Name</th>
                        @if($monthWiseReport)
                            @foreach($monthWiseReport['days'] as $day)
                                <th>{{ $day }}</th>
                            @endforeach
                        @endif
                        <th>Days</th>
                        <th>Present</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    @if($monthWiseReport && $monthWiseReport['rows']->isNotEmpty())
                        @foreach($monthWiseReport['rows'] as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row['roll_no'] }}</td>
                                <td>{{ $row['scholar_no'] }}</td>
                                <td>{{ $row['student_name'] }}</td>
                                @foreach($monthWiseReport['days'] as $day)
                                    @php $cell = $row['cells'][$day]; @endphp
                                    <td class="day-cell {{ $cell['class'] }}" title="{{ $cell['title'] }}">{{ $cell['label'] }}</td>
                                @endforeach
                                <td>{{ $row['working_days'] }}</td>
                                <td>{{ number_format($row['present_days'], 2) }}</td>
                                <td>{{ number_format($row['percentage'], 2) }}%</td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td colspan="40" class="text-center text-muted">Select Class, Section and Month, then click Generate.</td></tr>
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
