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
                    <label class="font-weight-600">Academic Session</label>
                    <input type="text" name="academic_session" value="{{ $filters['academic_session'] }}" class="form-control">
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label class="font-weight-600">From Date</label>
                    <input type="date" name="from_date" value="{{ $filters['from_date'] }}" class="form-control">
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label class="font-weight-600">To Date</label>
                    <input type="date" name="to_date" value="{{ $filters['to_date'] }}" class="form-control">
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label class="font-weight-600">Class</label>
                    <select name="class_id" class="form-control" onchange="var sec = this.form.querySelector('[name=section_name]'); if(sec) sec.value = ''; this.form.submit();">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected((string)$filters['class_id'] === (string)$class->id || (string)$filters['class_id'] === (string)$class->class_name)>{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label class="font-weight-600">Section</label>
                    <select name="section_name" class="form-control" onchange="this.form.submit()">
                        <option value="">All Sections</option>
                        @foreach($sections as $section)
                            <option value="{{ $section }}" @selected($filters['section_name'] === $section)>{{ $section }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 form-group">
                    <label class="font-weight-600">Student Search</label>
                    <input type="text" name="student_search" value="{{ $studentSearch }}" class="form-control" placeholder="Name, roll no, scholar no">
                </div>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-5 col-md-6 form-group mb-0">
                    <label class="font-weight-600">Student (Optional - Leave blank for all students)</label>
                    <select name="student_id" class="form-control">
                        <option value="">-- All Students --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" @selected((int)$filters['student_id'] === (int)$student->id)>
                                {{ $student->student_name }} @if($student->scholar_no || $student->form_number) ({{ $student->scholar_no ?: $student->form_number }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-7 col-md-6 form-group mb-0 d-flex align-items-end justify-content-end mt-2 mt-md-0 flex-wrap">
                    <button type="submit" class="btn btn-primary mr-2 mb-1"><i class="i-Magnifi-Glass1 mr-1"></i> Search</button>
                    <a class="btn btn-outline-info mr-2 mb-1" href="{{ route('academic.attendance.monthly-report', request()->query()) }}"><i class="i-Calendar-4 mr-1"></i> Monthly Summary Format</a>
                    @if($monthlyDetail)
                        <a class="btn btn-outline-primary mr-2 mb-1" href="#studentDailyMonthReport"><i class="i-Calendar mr-1"></i> Day Wise Report</a>
                    @endif
                    <a class="btn btn-outline-secondary mb-1" href="{{ route('student-attandence-report') }}"><i class="i-Repeat mr-1"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="summary-grid mb-3">
        <div class="summary-card"><span>{{ $selectedStudent ? 'Total Working Days' : 'Total Records' }}</span><strong>{{ $summary['total_students'] }}</strong></div>
        <div class="summary-card"><span>Total Present</span><strong>{{ $summary['present'] }}</strong></div>
        <div class="summary-card"><span>Total Absent</span><strong>{{ $summary['absent'] }}</strong></div>
        <div class="summary-card"><span>Attendance %</span><strong>{{ number_format($summary['percentage'], 2) }}%</strong></div>
    </div>

    <div class="attendance-panel">
        <h4 class="mb-1">{{ $selectedStudent ? $selectedStudent->student_name . ' - Attendance History' : 'All Students Attendance Report' }}</h4>
        <p class="text-muted">{{ $selectedStudent ? 'Complete attendance history for ' . $selectedStudent->student_name . '. Sundays and HRMS public holidays are excluded.' : 'Attendance records for all students matching the selected filters. Sundays and HRMS public holidays are excluded.' }}</p>
        <div class="table-responsive">
            <table class="table table-hover table-striped" id="studentAttendanceTable">
                <thead>
                    <tr>
                        <th style="width: 60px;">SNo</th>
                        @if(!$selectedStudent)
                            <th>Student Name</th>
                            <th>Scholar / Roll No</th>
                        @endif
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
                            <td>{{ ($history instanceof \Illuminate\Pagination\LengthAwarePaginator ? ($history->firstItem() + $loop->index) : $loop->iteration) }}</td>
                            @if(!$selectedStudent)
                                <td class="font-weight-600">
                                    {{ optional($row->student)->student_name ?: 'Student #' . $row->student_id }}
                                </td>
                                <td>{{ optional($row->student)->scholar_no ?: optional($row->student)->form_number ?: '-' }}</td>
                            @endif
                            <td>{{ optional($row->attendance->attendance_date)->format('d-m-Y') }}</td>
                            <td>{{ optional($row->attendance->className)->class_name ?: $row->attendance->class_name }}</td>
                            <td>{{ $row->attendance->section_name }}</td>
                            <td><span class="status-badge status-{{ $row->status }}">{{ ucwords(str_replace('_', ' ', $row->status)) }}</span></td>
                            <td>{{ $row->remarks ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $selectedStudent ? 6 : 8 }}" class="text-center text-muted py-4">No attendance data found for the selected criteria.</td></tr>
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
                <h4 class="mb-1">{{ $selectedStudent ? $selectedStudent->student_name . ' - Day Wise Report' : 'Day Wise Attendance Matrix' }}</h4>
                <p class="text-muted mb-0">{{ $selectedStudent ? 'Day-wise status for ' . $selectedStudent->student_name . '.' : 'Day-wise status for all students in the selected class and section.' }}</p>
            </div>
            <div class="d-flex align-items-center flex-wrap">
                @if($monthlyDetail)
                    <strong class="mr-3">{{ $monthlyDetail['month_label'] }}</strong>
                @endif
                <a class="btn btn-sm btn-outline-info" href="{{ route('academic.attendance.monthly-report', request()->query()) }}">
                    <i class="i-File-Clipboard-Text--Image mr-1"></i> Open Classic Summary Format
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered day-report-table" id="studentMonthWiseReportTable">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle">Student</th>
                        @if($monthlyDetail)
                            @foreach($monthlyDetail['days'] as $day)
                                <th rowspan="2" class="align-middle text-center">{{ $day }}</th>
                            @endforeach
                        @endif
                        <th colspan="3" class="text-center bg-light">Current Month</th>
                        <th colspan="3" class="text-center bg-light">Till Prev Month</th>
                        <th colspan="3" class="text-center bg-light">Overall</th>
                    </tr>
                    <tr>
                        <th class="text-center">Days</th>
                        <th class="text-center">Pres</th>
                        <th class="text-center">%</th>
                        <th class="text-center">Days</th>
                        <th class="text-center">Pres</th>
                        <th class="text-center">%</th>
                        <th class="text-center">Days</th>
                        <th class="text-center">Pres</th>
                        <th class="text-center">%</th>
                    </tr>
                </thead>
                <tbody>
                    @if($monthlyDetail && $monthlyDetail['rows']->isNotEmpty())
                        @foreach($monthlyDetail['rows'] as $row)
                            <tr>
                                <td class="font-weight-600">{{ $row['student_name'] }}</td>
                                @foreach($monthlyDetail['days'] as $day)
                                    @php $cell = $row['cells'][$day]; @endphp
                                    <td class="day-cell {{ $cell['class'] }}" title="{{ $cell['title'] }}">{{ $cell['label'] }}</td>
                                @endforeach
                                <td class="text-center">{{ number_format($row['current_month']['days'], 2) }}</td>
                                <td class="text-center">{{ number_format($row['current_month']['present'], 2) }}</td>
                                <td class="text-center font-weight-600">{{ number_format($row['current_month']['percentage'], 2) }}%</td>
                                <td class="text-center">{{ number_format($row['till_prev_month']['days'], 2) }}</td>
                                <td class="text-center">{{ number_format($row['till_prev_month']['present'], 2) }}</td>
                                <td class="text-center font-weight-600">{{ number_format($row['till_prev_month']['percentage'], 2) }}%</td>
                                <td class="text-center font-weight-bold">{{ number_format($row['overall']['days'], 2) }}</td>
                                <td class="text-center font-weight-bold">{{ number_format($row['overall']['present'], 2) }}</td>
                                <td class="text-center font-weight-bold {{ $row['overall']['percentage'] < 75 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($row['overall']['percentage'], 2) }}%
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td colspan="45" class="text-center text-muted py-3">Select Class and Section, then click Search to view the day-wise matrix report.</td></tr>
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
