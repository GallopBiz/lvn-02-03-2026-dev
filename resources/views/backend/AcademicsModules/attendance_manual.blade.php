@extends('backend.layouts.main')
@section('main-container')
<style>
    .manual-att-page { color: #24324b; }
    .manual-panel { background: #fff; border: 1px solid #e7edf5; border-radius: 8px; box-shadow: 0 4px 14px rgba(36,50,75,.05); padding: 22px; }
    .summary-grid { display: grid; grid-template-columns: repeat(5, minmax(130px, 1fr)); gap: 12px; }
    .summary-card { border: 1px solid #e7edf5; border-radius: 8px; padding: 14px 18px; background: #f8fbff; }
    .summary-card span { display: block; color: #69758a; font-size: 11px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
    .summary-card strong { font-size: 24px; color: #1e293b; }
    .badge-low { background: #fff0f0; color: #b42318; border: 1px solid #ffd0d0; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
    .badge-ok { background: #e9f8ef; color: #167146; border: 1px solid #bfe9ce; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
    .badge-admin { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 12px; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
    .table thead th { background: #f4f7fb; white-space: nowrap; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.4px; }
    .table tbody td { vertical-align: middle; }
    .quick-toolbar { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; }
    .input-cell { width: 100px; text-align: center; font-weight: 600; }
    .input-cell:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
    @media print {
        body, body * {
            visibility: visible !important;
        }
        #print-area, #print-area * {
            visibility: visible !important;
        }
        #print-area {
            position: static !important;
            width: 100% !important;
            left: auto !important;
            top: auto !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .no-print,
        .main-header,
        .sidebar,
        .breadcrumb,
        .app-admin-wrap > .sidebar,
        .app-footer,
        .separator-breadcrumb,
        .alert,
        .quick-toolbar,
        .summary-grid,
        .d-print-none {
            display: none !important;
            visibility: hidden !important;
        }
        .app-admin-wrap,
        .main-content-wrap,
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            position: static !important;
            float: none !important;
        }
        .manual-panel {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            background: transparent !important;
        }
        .print-only-header {
            display: block !important;
        }
        .form-control.input-cell,
        input[type="number"],
        input[type="text"] {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            font-size: 11px !important;
            color: #000 !important;
            text-align: center !important;
            width: 100% !important;
            -moz-appearance: textfield !important;
        }
        .table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        .table th, .table td {
            border: 1px solid #cbd5e1 !important;
            padding: 4px 6px !important;
            font-size: 10px !important;
            color: #000 !important;
        }
        .table th {
            background-color: #f1f5f9 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        tr {
            page-break-inside: avoid;
        }
    }
    .print-only-header {
        display: none;
    }
    @media (max-width: 991px) { .summary-grid { grid-template-columns: repeat(2, minmax(130px, 1fr)); } }
</style>

@php
    $studentCount = $studentRows->count();
    $avgPercentage = $studentCount ? round($studentRows->avg('percentage'), 2) : 0;
    $belowCount = $studentRows->where('is_below_minimum', true)->count();
    $eligibleCount = $studentCount - $belowCount;
@endphp

<div class="main-content manual-att-page pt-3">
    <div class="breadcrumb d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h2 class="mb-1 font-weight-bold">Manual Student Attendance & Working Days</h2>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="badge badge-admin"><i class="i-Administrator mr-1"></i> Admin Only Feature</span>
                <span class="text-muted ml-2" style="font-size: 13px;">Manually set and adjust school working days and each student's present days for report cards & academic records.</span>
            </div>
        </div>
        <div class="no-print mt-2 mt-md-0 d-flex align-items-center flex-wrap">
            <button type="button" class="btn btn-outline-secondary btn-sm mr-2" onclick="goBackToSource('{{ url()->previous() !== url()->current() ? url()->previous() : route('academic.attendance.monthly-report') }}')">
                <i class="i-Arrow-Left mr-1 font-weight-bold"></i> Back
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm mr-1" onclick="printManualAttendance()"><i class="i-Print mr-1"></i> Print / PDF</button>
            <button class="btn btn-outline-success btn-sm mr-1" onclick="exportTable('manualAttendanceTable','student-manual-attendance.xls')"><i class="i-File-Excel mr-1"></i> Excel</button>
            <a href="{{ route('academic.attendance.collective') }}" class="btn btn-outline-info btn-sm"><i class="i-File-Clipboard-Text--Image mr-1"></i> Auto-Calculated Attendance</a>
        </div>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if(session('success')) <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}</div> @endif
    @if(isset($errors) && $errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    @if($editingCollective)
        <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap mb-3">
            <span><strong><i class="i-Pen-4 mr-1"></i> Editing Manual Attendance Entry #{{ $editingCollective->id }}</strong> (Class: {{ $editingCollective->class_name }}, Section: {{ $editingCollective->section_name }}, Session: {{ $editingCollective->academic_session }}). Adjust values below and click "Update Manual Attendance".</span>
            <a href="{{ route('academic.attendance.manual') }}" class="btn btn-sm btn-outline-dark">Cancel Edit / New Entry</a>
        </div>
    @endif

    {{-- CRITERIA FILTER PANEL --}}
    <div class="manual-panel mb-3 no-print">
        <h5 class="mb-3 font-weight-bold text-dark"><i class="i-Filter-2 mr-1"></i> Select Class & Examination Criteria</h5>
        <form method="GET" action="{{ route('academic.attendance.manual') }}" id="manualFilterForm">
            <div class="row align-items-center">
                <div class="col-lg-2 col-md-4 form-group">
                    <label class="font-weight-600">Academic Session <span class="text-danger">*</span></label>
                    <input type="text" name="academic_session" value="{{ $filters['academic_session'] }}" class="form-control" placeholder="2025-2026" required>
                </div>

                <div class="col-lg-2 col-md-4 form-group">
                    <label class="font-weight-600">Class <span class="text-danger">*</span></label>
                    <select name="class_name" id="manualClassSelect" class="form-control" onchange="onClassChange(this)" required>
                        <option value="">-- Select Class --</option>
                        @foreach($classList as $cName)
                            <option value="{{ $cName }}" @selected($selectedClassName === $cName)>
                                {{ $cName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-4 form-group">
                    <label class="font-weight-600">Section <span class="text-danger">*</span></label>
                    <select name="section_name" id="manualSectionSelect" class="form-control" onchange="onSectionChange(this)" required>
                        <option value="">-- Select Section --</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec }}" @selected($selectedSectionName === $sec)>{{ $sec }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 form-group">
                    <label class="font-weight-600">Exam / Assessment (Optional)</label>
                    <select name="exam_id" id="manualExamSelect" class="form-control" onchange="onExamChange(this)">
                        <option value="">-- Whole Session / Overall --</option>
                        @if($selectedClassName && $exams->isNotEmpty())
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" @selected((int)$filters['exam_id'] === (int)$exam->id)>
                                    {{ $exam->exam_name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 form-group">
                    <label class="font-weight-600">School Working Days</label>
                    <input type="number" name="school_working_days" id="headerWorkingDays" value="{{ $schoolWorkingDays }}" class="form-control text-center font-weight-bold" min="1" max="365" placeholder="e.g. 210">
                </div>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-3 col-md-6 form-group mb-0">
                    <label class="text-muted" style="font-size: 12px;">Start Date (Optional range tracker)</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-lg-3 col-md-6 form-group mb-0">
                    <label class="text-muted" style="font-size: 12px;">End Date (Optional range tracker)</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-lg-6 col-md-12 form-group mb-0 d-flex align-items-end justify-content-end mt-2 mt-md-0">
                    <button type="submit" class="btn btn-primary mr-2"><i class="i-Eye mr-1"></i> Load Students</button>
                    <a href="{{ route('academic.attendance.manual') }}" class="btn btn-outline-secondary"><i class="i-Repeat mr-1"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>

    @if($selectedClassName && $selectedSectionName)
        {{-- SUMMARY COUNTERS --}}
        <div class="summary-grid mb-3">
            <div class="summary-card"><span>Total Students</span><strong id="cardTotalStudents">{{ $studentCount }}</strong></div>
            <div class="summary-card"><span>School Working Days</span><strong id="cardWorkingDays">{{ $schoolWorkingDays }}</strong></div>
            <div class="summary-card"><span>Class Average</span><strong id="cardAvgPct">{{ $avgPercentage }}%</strong></div>
            <div class="summary-card"><span>Eligible (&ge;75%)</span><strong id="cardEligible" class="text-success">{{ $eligibleCount }}</strong></div>
            <div class="summary-card"><span>Below 75%</span><strong id="cardBelow" class="text-danger">{{ $belowCount }}</strong></div>
        </div>

        {{-- MANUAL ENTRY FORM & TABLE --}}
        <div id="print-area" class="manual-panel mb-4 print-area">
            <div class="print-only-header mb-3">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2px solid #1f2a3d; padding-bottom:8px; margin-bottom:10px;">
                    <div>
                        <h3 style="margin:0; font-weight:800; font-size:20px; color:#0f172a;">Lokmanya Vidya Niketan</h3>
                        <div style="font-weight:700; font-size:13px; text-transform:uppercase; color:#2563eb; margin-top:2px;">Student Attendance & Working Days Record (Manual Adjustment)</div>
                    </div>
                    <div style="text-align:right; font-size:10px; color:#475569; line-height:1.5;">
                        <div><strong>Printed:</strong> {{ now()->format('d M Y, h:i A') }}</div>
                        <div><strong>Session:</strong> {{ $filters['academic_session'] }}</div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:10px;">
                    <div style="border:1px solid #d9e1ec; padding:6px; border-radius:4px;"><span style="display:block; font-size:8.5px; color:#64748b; text-transform:uppercase;">Class & Section</span><strong>{{ $selectedClassName }} - {{ $selectedSectionName }}</strong></div>
                    <div style="border:1px solid #d9e1ec; padding:6px; border-radius:4px;"><span style="display:block; font-size:8.5px; color:#64748b; text-transform:uppercase;">Exam / Assessment</span><strong>{{ optional($exams->firstWhere('id', (int)$filters['exam_id']))->exam_name ?: 'Session Overall' }}</strong></div>
                    <div style="border:1px solid #d9e1ec; padding:6px; border-radius:4px;"><span style="display:block; font-size:8.5px; color:#64748b; text-transform:uppercase;">School Working Days</span><strong>{{ $schoolWorkingDays }} Days</strong></div>
                    <div style="border:1px solid #d9e1ec; padding:6px; border-radius:4px;"><span style="display:block; font-size:8.5px; color:#64748b; text-transform:uppercase;">Adjusted By</span><strong>Admin (Manual Entry)</strong></div>
                </div>
                <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:8px; margin-bottom:10px;">
                    <div style="border:1px solid #cbd5e1; background:#f8fafc; padding:6px; border-radius:4px;"><span style="display:block; font-size:8.5px; color:#64748b; text-transform:uppercase;">Total Students</span><strong>{{ $studentCount }}</strong></div>
                    <div style="border:1px solid #cbd5e1; background:#f8fafc; padding:6px; border-radius:4px;"><span style="display:block; font-size:8.5px; color:#64748b; text-transform:uppercase;">Working Days</span><strong>{{ $schoolWorkingDays }}</strong></div>
                    <div style="border:1px solid #cbd5e1; background:#f8fafc; padding:6px; border-radius:4px;"><span style="display:block; font-size:8.5px; color:#64748b; text-transform:uppercase;">Average Attendance</span><strong>{{ $avgPercentage }}%</strong></div>
                    <div style="border:1px solid #cbd5e1; background:#f8fafc; padding:6px; border-radius:4px;"><span style="display:block; font-size:8.5px; color:#64748b; text-transform:uppercase;">CBSE Eligible (&ge;75%)</span><strong style="color:#16a34a;">{{ $eligibleCount }}</strong></div>
                    <div style="border:1px solid #cbd5e1; background:#f8fafc; padding:6px; border-radius:4px;"><span style="display:block; font-size:8.5px; color:#64748b; text-transform:uppercase;">Below 75% Shortage</span><strong style="color:#dc2626;">{{ $belowCount }}</strong></div>
                </div>
            </div>
            <form method="POST" action="{{ route('academic.attendance.manual.store') }}" id="manualAttendanceSaveForm">
                @csrf
                @if($editingCollective)
                    <input type="hidden" name="collective_id" value="{{ $editingCollective->id }}">
                @endif
                <input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
                <input type="hidden" name="class_name" value="{{ $selectedClassName }}">
                <input type="hidden" name="section_name" value="{{ $selectedSectionName }}">
                <input type="hidden" name="academic_session" value="{{ $filters['academic_session'] }}">
                <input type="hidden" name="exam_id" value="{{ $filters['exam_id'] }}">
                <input type="hidden" name="start_date" value="{{ $filters['start_date'] }}">
                <input type="hidden" name="end_date" value="{{ $filters['end_date'] }}">

                <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark">
                            Class: {{ $selectedClassName }} - Section: {{ $selectedSectionName }}
                            @if($filters['exam_id'])
                                @php $ex = $exams->firstWhere('id', (int)$filters['exam_id']); @endphp
                                <span class="badge badge-info ml-2">{{ optional($ex)->exam_name }}</span>
                            @endif
                        </h4>
                        <p class="text-muted mb-0" style="font-size: 13px;">
                            You can edit working days or present days for any student individually, or apply school working days to all students with one click.
                        </p>
                    </div>

                    <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mt-md-0 no-print">
                        <button type="submit" class="btn btn-success btn-lg px-4 font-weight-bold" @disabled($studentRows->isEmpty())>
                            <i class="i-Disk mr-1"></i> {{ $editingCollective ? 'Update Manual Attendance' : 'Save Manual Attendance' }}
                        </button>
                    </div>
                </div>

                {{-- QUICK ACTION TOOLBAR --}}
                <div class="quick-toolbar no-print">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center mr-3">
                            <label class="mb-0 mr-2 font-weight-600" style="white-space:nowrap;">School Working Days:</label>
                            <input type="number" name="school_working_days" id="batchWorkingDays" value="{{ $schoolWorkingDays }}" class="form-control form-control-sm text-center font-weight-bold mr-2" style="width: 90px;" min="1" max="365">
                            <button type="button" class="btn btn-sm btn-outline-primary mr-2" onclick="applyWorkingDaysToAll()" title="Apply this working days value to all student rows below">
                                <i class="i-Check mr-1"></i> Apply to All
                            </button>
                            @if(isset($autoWorkingDays) && $autoWorkingDays > 0)
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetToAutoWorkingDays({{ $autoWorkingDays }})" title="Reset to auto-calculated formula: Total Days ({{ $autoStats['total_days'] ?? 0 }}) - Sundays ({{ $autoStats['sundays'] ?? 0 }}) - Holidays ({{ $autoStats['holidays'] ?? 0 }}) = {{ $autoWorkingDays }} days">
                                    <i class="i-Calculator mr-1"></i> Auto Formula: {{ $autoWorkingDays }} Days
                                </button>
                            @endif
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-info" onclick="fillFullAttendance()" title="Sets each student's present days equal to their working days">
                            <i class="i-Yes mr-1"></i> Fill 100% Present
                        </button>

                        <a href="{{ request()->fullUrlWithQuery(['prefill_daily' => 1]) }}" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Prefill present days from daily attendance logs? Any current unsaved adjustments on this screen will be replaced.')" title="Fetches count of daily attendance marked present">
                            <i class="i-Data-Download mr-1"></i> Prefill from Daily Attendance
                        </a>

                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearPresentDays()" title="Reset all present days to 0">
                            <i class="i-Close mr-1"></i> Clear Present Days
                        </button>
                    </div>

                    <div class="d-flex align-items-center">
                        <input type="text" id="studentTableSearch" class="form-control form-control-sm" placeholder="Search student name / scholar no..." onkeyup="filterStudentTable()" style="min-width: 220px;">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="manualAttendanceTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">SNo</th>
                                <th>Scholar No</th>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Father Name</th>
                                <th class="text-center" style="width: 130px;">
                                    Working Days <span class="text-danger">*</span>
                                </th>
                                <th class="text-center" style="width: 130px;">
                                    Present Days <span class="text-danger">*</span>
                                </th>
                                <th class="text-center" style="width: 100px;">Attendance %</th>
                                <th class="text-center" style="width: 120px;">CBSE 75% Rule</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                            @forelse($studentRows as $idx => $row)
                                <tr class="student-row" data-name="{{ strtolower($row['student_name']) }}" data-scholar="{{ strtolower($row['scholar_no']) }}">
                                    <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="font-weight-600">{{ $row['scholar_no'] }}</span>
                                        <input type="hidden" name="students[{{ $row['student_id'] }}][student_id]" value="{{ $row['student_id'] }}">
                                        <input type="hidden" name="students[{{ $row['student_id'] }}][scholar_no]" value="{{ $row['scholar_no'] }}">
                                    </td>
                                    <td>{{ $row['roll_no'] ?: '-' }}</td>
                                    <td>
                                        <span class="font-weight-bold text-dark">{{ $row['student_name'] }}</span>
                                        <input type="hidden" name="students[{{ $row['student_id'] }}][student_name]" value="{{ $row['student_name'] }}">
                                    </td>
                                    <td class="text-muted" style="font-size: 13px;">{{ $row['father_name'] ?: '-' }}</td>
                                    <td class="text-center">
                                        <input type="number" 
                                               name="students[{{ $row['student_id'] }}][working_days]" 
                                               value="{{ $row['working_days'] }}" 
                                               class="form-control form-control-sm input-cell working-days-field" 
                                               min="0" max="365" 
                                               oninput="recalcRow(this)" 
                                               required>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" 
                                               step="0.5" 
                                               name="students[{{ $row['student_id'] }}][present_days]" 
                                               value="{{ $row['present_days'] }}" 
                                               class="form-control form-control-sm input-cell present-days-field" 
                                               min="0" max="365" 
                                               oninput="recalcRow(this)" 
                                               required>
                                    </td>
                                    <td class="text-center pct-cell">
                                        <span class="pct-text">{{ number_format($row['percentage'], 2) }}%</span>
                                    </td>
                                    <td class="text-center status-cell">
                                        @if($row['is_below_minimum'])
                                            <span class="badge badge-low"><i class="i-Warning mr-1"></i> Below 75%</span>
                                        @else
                                            <span class="badge badge-ok"><i class="i-Yes mr-1"></i> Eligible</span>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="students[{{ $row['student_id'] }}][remarks]" 
                                               value="{{ $row['remarks'] }}" 
                                               class="form-control form-control-sm" 
                                               placeholder="Optional notes">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        No students found in class <strong>{{ optional($selectedClass)->class_name }}</strong>, section <strong>{{ $filters['section_name'] }}</strong>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($studentRows->isNotEmpty())
                    <div class="d-flex justify-content-between align-items-center flex-wrap mt-3 pt-3 border-top no-print">
                        <div class="text-muted" style="font-size: 13px;">
                            Total {{ $studentCount }} students ready to save. Attendance percentages are automatically recalculated.
                        </div>
                        <button type="submit" class="btn btn-success btn-lg px-4 font-weight-bold">
                            <i class="i-Disk mr-1"></i> {{ $editingCollective ? 'Update Manual Attendance' : 'Save Manual Attendance' }}
                        </button>
                    </div>
                @endif
            </form>
            <div class="print-only-header" style="margin-top:36px; padding-top:10px;">
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:24px; text-align:center; font-size:10.5px; font-weight:600;">
                    <div style="border-top:1px solid #334155; padding-top:6px;">Class Teacher Signature</div>
                    <div style="border-top:1px solid #334155; padding-top:6px;">Academic Coordinator Signature</div>
                    <div style="border-top:1px solid #334155; padding-top:6px;">Principal Signature & Seal</div>
                </div>
            </div>
        </div>
    @else
        <div class="manual-panel text-center py-5 mb-4">
            <i class="i-Checked-User text-primary mb-3" style="font-size: 54px; display: block;"></i>
            <h4 class="font-weight-bold">Select Class and Section Above</h4>
            <p class="text-muted max-w-500 mx-auto">
                Select an Academic Session, Class, and Section from the filter panel above to load the student list and adjust their school working days and present days.
            </p>
        </div>
    @endif

    {{-- PREVIOUSLY SAVED MANUAL ATTENDANCE RECORDS --}}
    <div class="manual-panel no-print">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div>
                <h4 class="mb-1 font-weight-bold text-dark"><i class="i-Folder-Archive mr-1"></i> Previously Saved Manual Attendance Records</h4>
                <p class="text-muted mb-0" style="font-size: 13px;">Records created or manually adjusted by Admin.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>SNo</th>
                        <th>Exam / Type</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Academic Session</th>
                        <th class="text-center">School Working Days</th>
                        <th class="text-center">Students</th>
                        <th>Remarks</th>
                        <th>Saved On</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($savedCollectives as $saved)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $saved->exam_name ?: 'Session Collective' }}</strong>
                                <span class="badge badge-admin ml-1" style="font-size: 10px;">Manual</span>
                            </td>
                            <td>{{ optional($saved->className)->class_name ?: $saved->class_name }}</td>
                            <td><span class="badge badge-light border">{{ $saved->section_name }}</span></td>
                            <td>{{ $saved->academic_session }}</td>
                            <td class="text-center font-weight-bold">{{ $saved->working_days }}</td>
                            <td class="text-center">{{ $saved->student_count }}</td>
                            <td class="text-muted" style="font-size: 12px;">{{ $saved->remarks ?: '-' }}</td>
                            <td style="font-size: 12px;">{{ optional($saved->created_at)->format('d M Y, h:i A') }}</td>
                            <td class="text-right">
                                <a href="{{ route('academic.attendance.manual', ['edit_id' => $saved->id]) }}" class="btn btn-sm btn-outline-info mr-1" title="Edit this manual entry">
                                    <i class="i-Pen-4"></i> Edit
                                </a>
                                <a href="{{ route('academic.attendance.manual.print', $saved) }}" target="_blank" class="btn btn-sm btn-outline-primary mr-1" title="Printable report">
                                    <i class="i-Print"></i> Print
                                </a>
                                <form method="POST" action="{{ route('academic.attendance.manual.delete', $saved) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this saved manual attendance record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete record">
                                        <i class="i-Close"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                No manual attendance records saved yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    window.sectionsByClass = @json($sectionsByClass ?? []);
    window.classExamsMap = @json($classExamsMap ?? []);

    // When Class changes: update section dropdown, populate assigned exams, and submit to update view
    function onClassChange(selectEl) {
        var cName = selectEl.value;
        var secSelect = document.getElementById('manualSectionSelect');
        var examSelect = document.getElementById('manualExamSelect');

        if (secSelect) {
            var sections = (window.sectionsByClass && window.sectionsByClass[cName]) || [];
            var secHtml = '<option value="">-- Select Section --</option>';
            sections.forEach(function(s) {
                secHtml += '<option value="' + s + '">' + s + '</option>';
            });
            secSelect.innerHTML = secHtml;
            secSelect.value = '';
        }

        if (examSelect) {
            var exams = (window.classExamsMap && window.classExamsMap[cName]) || [];
            var examHtml = '<option value="">-- Whole Session / Overall --</option>';
            exams.forEach(function(ex) {
                examHtml += '<option value="' + ex.id + '">' + ex.name + '</option>';
            });
            examSelect.innerHTML = examHtml;
            examSelect.value = '';
        }

        document.getElementById('manualFilterForm').submit();
    }

    // When Section changes: submit filter form to load students
    function onSectionChange(selectEl) {
        document.getElementById('manualFilterForm').submit();
    }

    // When Exam changes: submit to reload attendance for that exam
    function onExamChange(selectEl) {
        document.getElementById('manualFilterForm').submit();
    }

    // Recalculate row percentage and CBSE 75% rule on input
    function recalcRow(inputElem) {
        var row = inputElem.closest('tr');
        if (!row) return;

        var workingInput = row.querySelector('.working-days-field');
        var presentInput = row.querySelector('.present-days-field');
        var pctCell = row.querySelector('.pct-text');
        var statusCell = row.querySelector('.status-cell');

        var workingDays = parseFloat(workingInput.value) || 0;
        var presentDays = parseFloat(presentInput.value) || 0;

        // Visual warning if present days exceed working days
        if (workingDays > 0 && presentDays > workingDays) {
            presentInput.style.borderColor = '#ef4444';
            presentInput.style.backgroundColor = '#fef2f2';
        } else {
            presentInput.style.borderColor = '';
            presentInput.style.backgroundColor = '';
        }

        var percentage = 0;
        if (workingDays > 0) {
            percentage = Math.round((presentDays / workingDays) * 10000) / 100;
        }

        pctCell.textContent = percentage.toFixed(2) + '%';

        if (percentage < 75) {
            statusCell.innerHTML = '<span class="badge badge-low"><i class="i-Warning mr-1"></i> Below 75%</span>';
        } else {
            statusCell.innerHTML = '<span class="badge badge-ok"><i class="i-Yes mr-1"></i> Eligible</span>';
        }

        recalcSummary();
    }

    // Apply global working days to all rows
    function applyWorkingDaysToAll() {
        var batchVal = document.getElementById('batchWorkingDays').value;
        if (!batchVal || batchVal <= 0) {
            alert('Please enter a valid school working days number.');
            return;
        }

        var inputs = document.querySelectorAll('.working-days-field');
        inputs.forEach(function(input) {
            input.value = batchVal;
            recalcRow(input);
        });

        // Also update header working days
        var headerInput = document.getElementById('headerWorkingDays');
        if (headerInput) headerInput.value = batchVal;
        var cardWd = document.getElementById('cardWorkingDays');
        if (cardWd) cardWd.textContent = batchVal;
    }

    // Reset working days to auto-calculated formula value
    function resetToAutoWorkingDays(autoVal) {
        if (!autoVal || autoVal <= 0) return;

        var batchInput = document.getElementById('batchWorkingDays');
        if (batchInput) batchInput.value = autoVal;

        var headerInput = document.getElementById('headerWorkingDays');
        if (headerInput) headerInput.value = autoVal;

        var cardWd = document.getElementById('cardWorkingDays');
        if (cardWd) cardWd.textContent = autoVal;

        var inputs = document.querySelectorAll('.working-days-field');
        inputs.forEach(function(input) {
            input.value = autoVal;
            recalcRow(input);
        });
    }

    // Quick fill 100% present days for all students
    function fillFullAttendance() {
        if (!confirm('Set all students\' present days equal to their working days?')) return;

        var rows = document.querySelectorAll('.student-row');
        rows.forEach(function(row) {
            var workingVal = row.querySelector('.working-days-field').value || 0;
            var presentInput = row.querySelector('.present-days-field');
            presentInput.value = workingVal;
            recalcRow(presentInput);
        });
    }

    // Clear all present days
    function clearPresentDays() {
        if (!confirm('Clear all present days to 0?')) return;

        var presentInputs = document.querySelectorAll('.present-days-field');
        presentInputs.forEach(function(input) {
            input.value = 0;
            recalcRow(input);
        });
    }

    // Recalculate summary cards
    function recalcSummary() {
        var rows = document.querySelectorAll('.student-row');
        if (!rows.length) return;

        var total = rows.length;
        var sumPct = 0;
        var below = 0;

        rows.forEach(function(row) {
            var workingDays = parseFloat(row.querySelector('.working-days-field').value) || 0;
            var presentDays = parseFloat(row.querySelector('.present-days-field').value) || 0;
            var pct = workingDays > 0 ? (presentDays / workingDays) * 100 : 0;
            sumPct += pct;
            if (pct < 75) below++;
        });

        var avg = total > 0 ? (sumPct / total).toFixed(2) : 0;
        var eligible = total - below;

        var elAvg = document.getElementById('cardAvgPct');
        if (elAvg) elAvg.textContent = avg + '%';
        var elBelow = document.getElementById('cardBelow');
        if (elBelow) elBelow.textContent = below;
        var elEligible = document.getElementById('cardEligible');
        if (elEligible) elEligible.textContent = eligible;
    }

    // Live search filter in student table
    function filterStudentTable() {
        var search = document.getElementById('studentTableSearch').value.toLowerCase().trim();
        var rows = document.querySelectorAll('.student-row');

        rows.forEach(function(row) {
            var name = row.getAttribute('data-name') || '';
            var scholar = row.getAttribute('data-scholar') || '';
            if (name.includes(search) || scholar.includes(search)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Printable view of manual attendance table
    function printManualAttendance() {
        var table = document.getElementById('manualAttendanceTable');
        if (!table) {
            alert('Please select Class and Section to load student attendance before printing.');
            return;
        }
        var rows = table.querySelectorAll('tbody#studentTableBody tr.student-row');
        if (!rows || rows.length === 0) {
            alert('Please select Class and Section to load student attendance before printing.');
            return;
        }

        var academicSession = document.querySelector('input[name="academic_session"]') ? document.querySelector('input[name="academic_session"]').value : '{{ $filters["academic_session"] ?? "" }}';
        var classSelect = document.getElementById('manualClassSelect');
        var className = (classSelect && classSelect.value) ? classSelect.value : '{{ $selectedClassName ?? "" }}';
        var sectionSelect = document.getElementById('manualSectionSelect');
        var sectionName = (sectionSelect && sectionSelect.value) ? sectionSelect.value : '{{ $selectedSectionName ?? "" }}';
        var examSelect = document.getElementById('manualExamSelect');
        var examName = (examSelect && examSelect.selectedIndex > 0) ? examSelect.options[examSelect.selectedIndex].text.trim() : 'Whole Session / Overall';
        var workingDaysInp = document.getElementById('batchWorkingDays') || document.getElementById('headerWorkingDays');
        var workingDays = workingDaysInp ? workingDaysInp.value : '{{ $schoolWorkingDays }}';

        var cardTotal = document.getElementById('cardTotalStudents');
        var totalStudents = cardTotal ? cardTotal.innerText : rows.length;
        var cardAvg = document.getElementById('cardAvgPct');
        var avgPct = cardAvg ? cardAvg.innerText : '0%';
        var cardEl = document.getElementById('cardEligible');
        var eligible = cardEl ? cardEl.innerText : '0';
        var cardBl = document.getElementById('cardBelow');
        var below = cardBl ? cardBl.innerText : '0';

        var rowsHtml = '';
        rows.forEach(function(row, idx) {
            var scholar = row.children[1] ? (row.children[1].querySelector('span') ? row.children[1].querySelector('span').innerText.trim() : row.children[1].innerText.trim()) : '';
            var roll = row.children[2] ? row.children[2].innerText.trim() : '-';
            var name = row.children[3] ? (row.children[3].querySelector('span') ? row.children[3].querySelector('span').innerText.trim() : row.children[3].innerText.trim()) : '';
            var father = row.children[4] ? row.children[4].innerText.trim() : '-';
            var wInp = row.querySelector('.working-days-field');
            var pInp = row.querySelector('.present-days-field');
            var wVal = wInp ? wInp.value : '';
            var pVal = pInp ? pInp.value : '';
            var pctSpan = row.querySelector('.pct-text');
            var pctVal = pctSpan ? pctSpan.innerText.trim() : '0.00%';
            var isBelow = parseFloat(pctVal) < 75;
            var rInp = row.querySelector('input[name*="[remarks]"]');
            var remVal = rInp ? rInp.value : '';

            rowsHtml += `
                <tr>
                    <td style="text-align:center;">${idx + 1}</td>
                    <td>${scholar}</td>
                    <td style="text-align:center;">${roll}</td>
                    <td><strong>${name}</strong></td>
                    <td style="color:#475569;">${father}</td>
                    <td style="text-align:right; font-weight:600;">${wVal}</td>
                    <td style="text-align:right; font-weight:600;">${pVal}</td>
                    <td style="text-align:right; font-weight:bold;">${pctVal}</td>
                    <td style="text-align:center; font-weight:bold; color:${isBelow ? '#b91c1c; background:#fef2f2;' : '#15803d; background:#f0fdf4;'}">
                        ${isBelow ? 'Below 75%' : 'Eligible'}
                    </td>
                    <td style="font-size:9.5px; color:#64748b;">${remVal || '-'}</td>
                </tr>
            `;
        });

        var now = new Date();
        var printedOn = now.toLocaleDateString('en-GB') + ' ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

        var printHtml = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Student Manual Attendance Report - ${className} ${sectionName}</title>
                <style>
                    @page { size: A4 portrait; margin: 10mm; }
                    * { box-sizing: border-box; }
                    body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; color: #1f2a3d; background: #fff; }
                    .print-actions { position: sticky; top: 0; z-index: 5; padding: 10px 18px; background: #fff; border-bottom: 1px solid #d9e1ec; display: flex; justify-content: flex-end; gap: 10px; }
                    .print-actions button { border: 1px solid #1f6feb; background: #1f6feb; color: #fff; border-radius: 4px; padding: 7px 16px; cursor: pointer; font-weight: 600; font-size: 13px; }
                    .print-actions .btn-close { background: #6c757d; border-color: #6c757d; }
                    .sheet { width: 100%; margin: 0 auto; background: #fff; padding: 6px 12px; }
                    .school-header { display: grid; grid-template-columns: 1fr auto; gap: 12px; border-bottom: 2px solid #1f2a3d; padding-bottom: 10px; margin-bottom: 12px; }
                    .school-name { font-size: 20px; font-weight: 800; color: #0f172a; }
                    .report-title { font-size: 13px; font-weight: 700; margin-top: 4px; text-transform: uppercase; color: #2563eb; }
                    .meta { text-align: right; font-size: 10px; line-height: 1.6; color: #475569; }
                    .criteria, .summary { display: grid; gap: 8px; margin: 10px 0; }
                    .criteria { grid-template-columns: repeat(4, 1fr); }
                    .summary { grid-template-columns: repeat(5, 1fr); }
                    .box { border: 1px solid #d9e1ec; border-radius: 4px; padding: 6px 8px; background: #fff; }
                    .summary .box { background: #f8fafc; border-color: #cbd5e1; }
                    .label { display: block; font-size: 8.5px; color: #64748b; text-transform: uppercase; margin-bottom: 2px; font-weight: 600; }
                    .value { font-size: 11px; font-weight: 700; color: #0f172a; }
                    .summary strong { display: block; font-size: 15px; color: #0f172a; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
                    th, td { border: 1px solid #cbd5e1; padding: 5px 6px; font-size: 9.5px; vertical-align: middle; word-wrap: break-word; }
                    th { background: #f1f5f9; font-weight: 700; text-align: left; color: #334155; }
                    .footer { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 36px; font-size: 10.5px; }
                    .sign { border-top: 1px solid #334155; padding-top: 6px; text-align: center; font-weight: 600; }
                    @media print {
                        .print-actions { display: none !important; }
                        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        tr { page-break-inside: avoid; }
                    }
                </style>
            </head>
            <body>
                <div class="print-actions">
                    <button type="button" class="btn-close" onclick="window.close()">Close</button>
                    <button type="button" onclick="window.print()">Print / Save as PDF</button>
                </div>
                <main class="sheet">
                    <header class="school-header">
                        <div>
                            <div class="school-name">Lokmanya Vidya Niketan</div>
                            <div class="report-title">Student Attendance & Working Days Record (Manual Adjustment)</div>
                        </div>
                        <div class="meta">
                            <div><strong>Printed:</strong> ${printedOn}</div>
                            <div><strong>Session:</strong> ${academicSession}</div>
                        </div>
                    </header>

                    <section class="criteria">
                        <div class="box"><span class="label">Class & Section</span><span class="value">${className} - ${sectionName}</span></div>
                        <div class="box"><span class="label">Exam / Assessment</span><span class="value">${examName}</span></div>
                        <div class="box"><span class="label">School Working Days</span><span class="value">${workingDays} Days</span></div>
                        <div class="box"><span class="label">Adjusted By</span><span class="value">Admin (Manual Entry)</span></div>
                    </section>

                    <section class="summary">
                        <div class="box"><span class="label">Total Students</span><strong>${totalStudents}</strong></div>
                        <div class="box"><span class="label">School Working Days</span><strong>${workingDays}</strong></div>
                        <div class="box"><span class="label">Average Attendance</span><strong>${avgPct}</strong></div>
                        <div class="box"><span class="label">CBSE &ge; 75% Eligible</span><strong style="color:#16a34a;">${eligible}</strong></div>
                        <div class="box"><span class="label">Below 75% Shortage</span><strong style="color:#dc2626;">${below}</strong></div>
                    </section>

                    <table>
                        <thead>
                            <tr>
                                <th style="width: 32px; text-align:center;">SNo</th>
                                <th style="width: 70px;">Scholar No</th>
                                <th style="width: 50px; text-align:center;">Roll No</th>
                                <th>Student Name</th>
                                <th>Father Name</th>
                                <th style="width: 60px; text-align:right;">Working</th>
                                <th style="width: 60px; text-align:right;">Present</th>
                                <th style="width: 65px; text-align:right;">%</th>
                                <th style="width: 75px; text-align:center;">CBSE 75%</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>

                    <div class="footer">
                        <div class="sign">Class Teacher Signature</div>
                        <div class="sign">Academic Coordinator Signature</div>
                        <div class="sign">Principal Signature & Seal</div>
                    </div>
                </main>
            </body>
            </html>
        `;

        var printWin = window.open('', '_blank', 'width=1150,height=850');
        if (!printWin) {
            window.print();
            return;
        }
        printWin.document.open();
        printWin.document.write(printHtml);
        printWin.document.close();
        printWin.focus();
        setTimeout(function() {
            printWin.print();
        }, 350);
    }

    // Export table to Excel
    function exportTable(tableId, filename) {
        var table = document.getElementById(tableId);
        if (!table) return;

        // Clone table to remove input fields and export clean text
        var clone = table.cloneNode(true);
        var inputs = clone.querySelectorAll('input');
        inputs.forEach(function(inp) {
            var span = document.createElement('span');
            span.textContent = inp.value;
            inp.parentNode.replaceChild(span, inp);
        });

        var html = clone.outerHTML.replace(/ /g, '%20');
        var link = document.createElement('a');
        link.href = 'data:application/vnd.ms-excel,' + html;
        link.download = filename;
        link.click();
    }

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
