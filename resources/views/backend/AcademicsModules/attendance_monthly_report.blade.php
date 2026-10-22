@extends('backend.layouts.main')
@section('main-container')
<style>
    .monthly-report-page {
        color: #24324b;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .top-nav-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
    }
    .top-nav-btn {
        background-color: #7074b3;
        color: #ffffff !important;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 4px;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        transition: all 0.15s ease-in-out;
        border: 1px solid #5d619e;
    }
    .top-nav-btn:hover {
        background-color: #5851a2;
        border-color: #4b4492;
        color: #fff !important;
        box-shadow: 0 2px 6px rgba(112, 116, 179, 0.35);
    }
    .top-nav-btn.active {
        background-color: #4e488d;
        border-color: #3f3977;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.25);
    }
    .report-card {
        background: #ffffff;
        border: 1px solid #c9d2de;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        padding: 16px 18px;
        margin-bottom: 20px;
    }
    .report-title {
        font-size: 20px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 12px;
    }
    .report-tabs {
        display: flex;
        flex-wrap: wrap;
        border-bottom: 2px solid #7074b3;
        margin-bottom: 14px;
    }
    .report-tab {
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #4a5568;
        background: #eef0f6;
        border: 1px solid #cbd5e0;
        border-bottom: none;
        border-radius: 6px 6px 0 0;
        margin-right: 4px;
        text-decoration: none !important;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }
    .report-tab:hover {
        color: #7074b3;
        background: #ffffff;
    }
    .report-tab.active {
        background: #7074b3;
        color: #ffffff !important;
        border-color: #7074b3;
    }
    .filter-bar {
        background: #f7fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 10px 14px;
        margin-bottom: 16px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }
    .filter-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #2d3748;
    }
    .filter-item select, .filter-item input {
        font-size: 13px;
        padding: 4px 8px;
        height: 32px;
        border-radius: 4px;
        border: 1px solid #cbd5e0;
        background-color: #ffffff;
    }
    .btn-action-show {
        background-color: #7074b3;
        border-color: #7074b3;
        color: #ffffff;
        font-weight: 600;
        padding: 4px 14px;
        height: 32px;
        font-size: 13px;
        transition: all 0.15s ease-in-out;
    }
    .btn-action-show:hover {
        background-color: #5851a2;
        border-color: #5851a2;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(112, 116, 179, 0.4);
    }
    .btn-action-new {
        background-color: #ffffff;
        border: 1px solid #7074b3;
        color: #7074b3;
        font-weight: 600;
        padding: 4px 14px;
        height: 32px;
        font-size: 13px;
        transition: all 0.15s ease-in-out;
    }
    .btn-action-new:hover {
        background-color: #7074b3;
        border-color: #7074b3;
        color: #ffffff;
    }
    .btn-action-print {
        background-color: #374151;
        border-color: #374151;
        color: #ffffff;
        font-weight: 600;
        padding: 4px 14px;
        height: 32px;
        font-size: 13px;
        transition: all 0.15s ease-in-out;
    }
    .btn-action-print:hover {
        background-color: #1f2937;
        color: #ffffff;
    }
    .btn-action-excel {
        background-color: #167146;
        border-color: #167146;
        color: #ffffff;
        font-weight: 600;
        padding: 4px 14px;
        height: 32px;
        font-size: 13px;
        transition: all 0.15s ease-in-out;
    }
    .btn-action-excel:hover {
        background-color: #115333;
        color: #ffffff;
    }
    .btn-action-exit {
        background-color: #6b7280;
        border-color: #6b7280;
        color: #ffffff;
        font-weight: 600;
        padding: 4px 14px;
        height: 32px;
        font-size: 13px;
        transition: all 0.15s ease-in-out;
    }
    .btn-action-exit:hover {
        background-color: #4b5563;
        color: #ffffff;
    }

    /* Table Styles */
    .table-container {
        position: relative;
        overflow-x: auto;
        border: 1px solid #718096;
        background: #ffffff;
        max-height: 75vh;
    }
    .attendance-matrix-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 11px;
        text-align: center;
        white-space: nowrap;
    }
    .attendance-matrix-table th, .attendance-matrix-table td {
        border: 1px solid #a0aec0;
        padding: 4px 3px;
        line-height: 1.2;
    }
    .attendance-matrix-table thead th {
        background: linear-gradient(180deg, #718096 0%, #4a5568 100%);
        color: #ffffff;
        font-weight: 700;
        text-shadow: 0 1px 1px rgba(0,0,0,0.3);
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .attendance-matrix-table thead tr:nth-child(2) th {
        top: 24px;
        background: #5a6578;
        font-size: 10.5px;
    }
    .attendance-matrix-table th.th-group {
        background: linear-gradient(180deg, #4a5568 0%, #2d3748 100%);
        letter-spacing: 0.3px;
    }
    .attendance-matrix-table th.th-sunday {
        background: #1e7e34 !important;
        color: #ffffff !important;
    }
    .attendance-matrix-table td.cell-sunday {
        background-color: #137736 !important;
        color: #ffffff !important;
        font-weight: 700;
        text-align: center;
    }
    .attendance-matrix-table td.cell-present {
        color: #1a202c;
        font-weight: 600;
    }
    .attendance-matrix-table td.cell-absent {
        color: #c53030;
        font-weight: 700;
        background-color: #fff5f5;
    }
    .attendance-matrix-table td.cell-holiday {
        background-color: #feebc8;
        color: #7b341e;
        font-weight: 700;
    }
    .attendance-matrix-table td.cell-empty {
        color: #a0aec0;
    }
    .attendance-matrix-table td.cell-disabled {
        background-color: #edf2f7;
    }

    /* Fixed Left Columns on Horizontal Scroll */
    .col-sr { min-width: 32px; width: 32px; }
    .col-roll { min-width: 55px; width: 55px; }
    .col-bord { min-width: 65px; width: 65px; }
    .col-name { min-width: 150px; text-align: left !important; padding-left: 6px !important; }
    .col-day { min-width: 22px; width: 22px; }
    .col-metric { min-width: 48px; width: 48px; }

    .sticky-col {
        position: sticky;
        background-color: #ffffff;
        z-index: 2;
    }
    .attendance-matrix-table thead th.sticky-col {
        z-index: 20;
    }
    .sticky-sr { left: 0; }
    .sticky-roll { left: 32px; }
    .sticky-bord { left: 87px; }
    .sticky-name { left: 152px; border-right: 2px solid #4a5568 !important; }

    .attendance-matrix-table tbody tr:hover td {
        background-color: #f6f5fb;
    }
    .attendance-matrix-table tbody tr:hover td.cell-sunday {
        background-color: #116a30 !important;
    }

    /* Print Styles */
    @media print {
        @page {
            size: landscape;
            margin: 6mm;
        }
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
        .top-nav-bar,
        .report-tabs,
        .filter-bar,
        .alert,
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
        .report-card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            background: transparent !important;
        }
        .table-container {
            max-height: none !important;
            overflow: visible !important;
            border: 1px solid #718096 !important;
        }
        .attendance-matrix-table {
            font-size: 8px !important;
            width: 100% !important;
        }
        .attendance-matrix-table th, .attendance-matrix-table td {
            border: 1px solid #718096 !important;
            padding: 2px 1px !important;
        }
        .attendance-matrix-table thead th {
            background: #e2e8f0 !important;
            color: #000 !important;
            position: static !important;
            text-shadow: none !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .attendance-matrix-table td.cell-sunday, .attendance-matrix-table th.th-sunday {
            background-color: #137736 !important;
            color: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .attendance-matrix-table td.cell-absent {
            background-color: #fff5f5 !important;
            color: #c53030 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .attendance-matrix-table td.cell-holiday {
            background-color: #feebc8 !important;
            color: #7b341e !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .sticky-col {
            position: static !important;
        }
        .print-only-header {
            display: block !important;
        }
    }
    .print-only-header {
        display: none;
    }
</style>

@php
    $monthsList = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
    ];
@endphp

<div class="main-content monthly-report-page pt-3">
    <!-- Top Action Buttons (Matching Screenshot) -->
    <div class="top-nav-bar no-print">
        <a href="{{ route('dailyattandence') }}" class="top-nav-btn"><i class="i-Check mr-1"></i> Daily Attendance Entry</a>
        <a href="{{ route('academic.attendance.monthly-report') }}" class="top-nav-btn active"><i class="i-Calendar-4 mr-1"></i> Monthly Attendance Report</a>
        {{-- <a href="{{ route('student-attandence-report') }}" class="top-nav-btn"><i class="i-Student-Hat mr-1"></i> Attendance Status Report</a> --}}
        <a href="{{ route('Attandencereports') }}" class="top-nav-btn"><i class="i-Line-Chart mr-1"></i> Daily Student Attendance Report</a>
        <a href="{{ route('academic.attendance.collective') }}" class="top-nav-btn"><i class="i-File-Clipboard-Text--Image mr-1"></i> Student Collective Attendance</a>
        <a href="{{ route('academic.attendance.manual') }}" class="top-nav-btn"><i class="i-Gear mr-1"></i> Manual Student Attendance & Working Days</a>
    </div>

    <div class="report-card">
        <!-- Title & Sub-tabs -->
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
            <div class="report-title mb-0">Attendance Report</div>
            <div class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 13px;">
                Session: {{ $filters['academic_session'] }}
            </div>
        </div>

        <div class="report-tabs no-print">
            <span class="report-tab active">Monthly Atten. Summary(1 Meeting)</span>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('academic.attendance.monthly-report') }}" id="monthlyReportFilterForm" class="filter-bar no-print">
            <input type="hidden" name="academic_session" value="{{ $filters['academic_session'] }}">

            <div class="filter-item">
                <label>Class :</label>
                <select name="class_id" id="reportClassSelect" onchange="onClassFilterChange(this)">
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected((string)$filters['class_id'] === (string)$class->id || (string)$filters['class_id'] === (string)$class->class_name)>
                            {{ $class->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-item">
                <label>Section :</label>
                <select name="section_name" id="reportSectionSelect">
                    <option value="">-- Select Section --</option>
                    @foreach($sections as $section)
                        <option value="{{ $section }}" @selected($filters['section_name'] === $section)>
                            {{ $section }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-item">
                <label>Month / Year :</label>
                <select name="month">
                    @foreach($monthsList as $num => $abbr)
                        <option value="{{ $num }}" @selected((int)$selectedMonth === (int)$num)>{{ $abbr }}</option>
                    @endforeach
                </select>
                <select name="year">
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}" @selected((int)$selectedYear === (int)$yr)>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-item ml-auto">
                <button type="submit" class="btn btn-action-show mr-1"><i class="i-Magnifi-Glass1 mr-1"></i> Show</button>
                <a href="{{ route('academic.attendance.monthly-report') }}" class="btn btn-action-new mr-2"><i class="i-Repeat mr-1"></i> New</a>

                <label class="mb-0 mr-2 font-weight-normal text-muted" style="cursor: pointer;">
                    <input type="checkbox" id="pdfViewerToggle" onchange="togglePdfViewer(this)"> In PDF Viewer
                </label>

                <button type="button" class="btn btn-action-print mr-1" onclick="handlePrintReport()"><i class="i-Billing mr-1"></i> Print</button>
                <button type="button" class="btn btn-action-excel mr-1" onclick="exportMonthlyReportTable()"><i class="i-File-Excel mr-1"></i> Excel</button>
                <a href="{{ route('Academics') }}" class="btn btn-action-exit"><i class="i-Close mr-1"></i> Exit</a>
            </div>
        </form>

        <!-- Attendance Matrix Table -->
        @if($report && count($report['rows']) > 0)
            <div id="print-area" class="print-area">
                <div class="print-only-header mb-2" id="printReportHeader">
                    <div style="border-bottom:2px solid #2d3748; padding-bottom:6px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:flex-end;">
                        <div>
                            <div style="font-size:18px; font-weight:800; color:#1a202c;">Lokmanya Vidya Niketan</div>
                            <div style="font-size:12px; font-weight:700; color:#4b5563; text-transform:uppercase; margin-top:2px;">Monthly Student Attendance Sheet & Matrix</div>
                        </div>
                        <div style="font-size:10px; color:#4a5568; line-height:1.4; text-align:right;">
                            <div><strong>Printed:</strong> {{ now()->format('d M Y, h:i A') }}</div>
                            <div><strong>Session:</strong> {{ $filters['academic_session'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap" id="printReportMeta">
                <div>
                    <strong>Class:</strong> {{ optional($selectedClass)->class_name ?: $filters['class_id'] }} &nbsp;|&nbsp;
                    <strong>Section:</strong> {{ $filters['section_name'] }} &nbsp;|&nbsp;
                    <strong>Month:</strong> {{ $report['month_label'] }} &nbsp;|&nbsp;
                    <strong>Total Students:</strong> {{ count($report['rows']) }}
                </div>
                <div class="text-muted font-italic" style="font-size: 11px;">
                    <span class="badge badge-success px-2 py-1 mr-1" style="background:#137736;">S</span> Sunday &nbsp;
                    <span class="badge badge-warning px-2 py-1 mr-1">H</span> Holiday &nbsp;
                    <strong>P:</strong> Present &nbsp;
                    <strong>A:</strong> Absent
                </div>
            </div>

            <div class="table-container" id="printableReportContainer">
                <table class="attendance-matrix-table" id="monthlySummaryTable">
                    <thead>
                        <tr>
                            <th rowspan="2" class="col-sr sticky-col sticky-sr">Sr</th>
                            <th rowspan="2" class="col-roll sticky-col sticky-roll">Roll No</th>
                            <th rowspan="2" class="col-bord sticky-col sticky-bord">Sch No.</th>
                            <th rowspan="2" class="col-name sticky-col sticky-name">Student Name</th>
                            @for($d = 1; $d <= 31; $d++)
                                @php
                                    $dayDate = ($d <= $report['days_in_month'])
                                        ? sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $d)
                                        : null;
                                    $isSundayHeader = $dayDate && isset($report['non_working_days'][$dayDate]) && $report['non_working_days'][$dayDate]['type'] === 'sunday';
                                @endphp
                                <th rowspan="2" class="col-day {{ $isSundayHeader ? 'th-sunday' : '' }}">{{ $d }}</th>
                            @endfor
                            <th colspan="3" class="th-group">Current Month</th>
                            <th colspan="3" class="th-group">Till Previous Month</th>
                            <th colspan="3" class="th-group">Overall Attendance</th>
                        </tr>
                        <tr>
                            <th class="col-metric">Days</th>
                            <th class="col-metric">Present</th>
                            <th class="col-metric">( % )</th>
                            <th class="col-metric">Days</th>
                            <th class="col-metric">Present</th>
                            <th class="col-metric">( % )</th>
                            <th class="col-metric">Days</th>
                            <th class="col-metric">Present</th>
                            <th class="col-metric">( % )</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['rows'] as $index => $row)
                            <tr>
                                <td class="col-sr sticky-col sticky-sr">{{ $index + 1 }}</td>
                                <td class="col-roll sticky-col sticky-roll">{{ $row['roll_no'] ?: '-' }}</td>
                                <td class="col-bord sticky-col sticky-bord">{{ $row['scholar_no'] ?: '-' }}</td>
                                <td class="col-name sticky-col sticky-name font-weight-bold">{{ $row['student_name'] }}</td>

                                @for($d = 1; $d <= 31; $d++)
                                    @if($d <= $report['days_in_month'])
                                        @php $cell = $row['cells'][$d] ?? ['label' => '-', 'class' => 'cell-empty', 'type' => 'empty']; @endphp
                                        <td class="col-day {{ $cell['class'] }}" title="{{ $cell['title'] ?? '' }}">
                                            {{ $cell['label'] }}
                                        </td>
                                    @else
                                        <td class="col-day cell-disabled"></td>
                                    @endif
                                @endfor

                                <!-- Current Month -->
                                <td class="col-metric">{{ number_format($row['current_month']['days'], 2) }}</td>
                                <td class="col-metric">{{ number_format($row['current_month']['present'], 2) }}</td>
                                <td class="col-metric font-weight-600">{{ number_format($row['current_month']['percentage'], 2) }}</td>

                                <!-- Till Previous Month -->
                                <td class="col-metric">{{ number_format($row['till_prev_month']['days'], 2) }}</td>
                                <td class="col-metric">{{ number_format($row['till_prev_month']['present'], 2) }}</td>
                                <td class="col-metric font-weight-600">{{ number_format($row['till_prev_month']['percentage'], 2) }}</td>

                                <!-- Overall Attendance -->
                                <td class="col-metric font-weight-bold">{{ number_format($row['overall']['days'], 2) }}</td>
                                <td class="col-metric font-weight-bold">{{ number_format($row['overall']['present'], 2) }}</td>
                                <td class="col-metric font-weight-bold {{ $row['overall']['percentage'] < 75 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($row['overall']['percentage'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="print-only-header" style="margin-top:24px;">
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; font-size:10px; font-weight:600; text-align:center;">
                    <div style="border-top:1px solid #333; padding-top:4px;">Class Teacher Signature</div>
                    <div style="border-top:1px solid #333; padding-top:4px;">Academic Coordinator Signature</div>
                    <div style="border-top:1px solid #333; padding-top:4px;">Principal Signature & Seal</div>
                </div>
            </div>
        </div>
        @else
            <div class="alert alert-info py-4 text-center my-3" style="background:#f6f5fb; border-color:#dcd8ef; color:#4e488d;">
                <i class="i-Information mr-2" style="font-size: 20px; vertical-align: middle;"></i>
                @if(!$filters['class_id'] || !$filters['section_name'])
                    Please select <strong>Class</strong> and <strong>Section</strong>, choose <strong>Month / Year</strong>, and click <strong>Show</strong> to view the attendance report.
                @else
                    No student attendance records found for the selected Class, Section, and Month.
                @endif
            </div>
        @endif
    </div>
</div>

<script>
    function onClassFilterChange(selectEl) {
        var secSelect = document.getElementById('reportSectionSelect');
        if (secSelect) {
            secSelect.value = '';
        }
        document.getElementById('monthlyReportFilterForm').submit();
    }

    function togglePdfViewer(checkbox) {
        if (checkbox.checked) {
            handlePrintReport();
        }
    }

    function handlePrintReport() {
        var table = document.getElementById('monthlySummaryTable');
        if (!table) {
            alert('Please select Class and Section and click Show to view the attendance report before printing.');
            return;
        }

        var className = @json(optional($selectedClass)->class_name ?: $filters['class_id'] ?? '');
        var sectionName = @json($filters['section_name'] ?? '');
        var monthLabel = @json($report['month_label'] ?? '');
        var sessionName = @json($filters['academic_session'] ?? '');
        var totalStudents = @json(count($report['rows'] ?? []));
        var now = new Date();
        var printedOn = now.toLocaleDateString('en-GB') + ' ' + now.toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'});

        var tableHtml = table.outerHTML;

        var printHtml = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Monthly Attendance Report - ${className} ${sectionName} - ${monthLabel}</title>
                <style>
                    @page { size: A4 landscape; margin: 6mm; }
                    * { box-sizing: border-box; }
                    body { margin: 0; padding: 4px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; color: #111; background: #fff; }
                    .print-actions { position: sticky; top: 0; z-index: 999; padding: 8px 12px; background: #fff; border-bottom: 1px solid #cbd5e0; display: flex; justify-content: flex-end; gap: 8px; }
                    .print-actions button { border: 1px solid #1f6feb; background: #1f6feb; color: #fff; border-radius: 4px; padding: 6px 14px; cursor: pointer; font-weight: 600; font-size: 12px; }
                    .print-actions .btn-close { background: #6c757d; border-color: #6c757d; }
                    .school-header { border-bottom: 2px solid #2d3748; padding-bottom: 6px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: flex-end; }
                    .school-name { font-size: 18px; font-weight: 800; color: #1a202c; }
                    .report-title { font-size: 12px; font-weight: 700; color: #4b5563; text-transform: uppercase; margin-top: 2px; }
                    .meta-info { font-size: 10px; color: #4a5568; line-height: 1.4; text-align: right; }
                    .report-criteria-bar { display: flex; justify-content: space-between; align-items: center; font-size: 10.5px; font-weight: 600; margin-bottom: 6px; padding: 4px 6px; background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 4px; }
                    .attendance-matrix-table { border-collapse: collapse; width: 100%; font-size: 8px; text-align: center; }
                    .attendance-matrix-table th, .attendance-matrix-table td { border: 1px solid #718096; padding: 2px 1px; line-height: 1.1; }
                    .attendance-matrix-table thead th { background: #e2e8f0 !important; color: #000 !important; font-weight: 700; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .attendance-matrix-table th.th-sunday, .attendance-matrix-table td.cell-sunday { background-color: #137736 !important; color: #ffffff !important; font-weight: 700; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .attendance-matrix-table td.cell-absent { color: #c53030; font-weight: 700; background-color: #fff5f5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .attendance-matrix-table td.cell-holiday { background-color: #feebc8 !important; color: #7b341e; font-weight: 700; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .col-name { text-align: left !important; padding-left: 3px !important; }
                    .footer-signs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 24px; font-size: 9.5px; font-weight: 600; text-align: center; }
                    .sign-line { border-top: 1px solid #333; padding-top: 4px; }
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
                    <button type="button" onclick="window.print()">Print / Save PDF</button>
                </div>
                <div class="school-header">
                    <div>
                        <div class="school-name">Lokmanya Vidya Niketan</div>
                        <div class="report-title">Monthly Student Attendance Sheet & Matrix</div>
                    </div>
                    <div class="meta-info">
                        <div><strong>Printed:</strong> ${printedOn}</div>
                        <div><strong>Session:</strong> ${sessionName}</div>
                    </div>
                </div>
                <div class="report-criteria-bar">
                    <div>
                        <strong>Class:</strong> ${className} &nbsp;|&nbsp;
                        <strong>Section:</strong> ${sectionName} &nbsp;|&nbsp;
                        <strong>Month:</strong> ${monthLabel} &nbsp;|&nbsp;
                        <strong>Total Students:</strong> ${totalStudents}
                    </div>
                    <div style="font-size: 9.5px;">
                        <span style="display:inline-block; background:#137736; color:#fff; padding:1px 4px; border-radius:2px; font-weight:bold;">S</span> Sunday &nbsp;
                        <span style="display:inline-block; background:#feebc8; color:#7b341e; padding:1px 4px; border-radius:2px; font-weight:bold;">H</span> Holiday &nbsp;
                        <strong>P:</strong> Present &nbsp;
                        <strong>A:</strong> Absent
                    </div>
                </div>
                ${tableHtml}
                <div class="footer-signs">
                    <div class="sign-line">Class Teacher Signature</div>
                    <div class="sign-line">Academic Coordinator Signature</div>
                    <div class="sign-line">Principal Signature & Seal</div>
                </div>
            </body>
            </html>
        `;

        var printWin = window.open('', '_blank', 'width=1350,height=850');
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

    function exportMonthlyReportTable() {
        var table = document.getElementById('monthlySummaryTable');
        if (!table) {
            alert('Please generate the report before exporting.');
            return;
        }

        var html = table.outerHTML;
        var blob = new Blob([
            '\uFEFF' + '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">' +
            '<head><meta charset="UTF-8"><style>table, th, td { border: 1px solid #777; border-collapse: collapse; text-align: center; font-family: Arial; } th { background-color: #555; color: #fff; } .cell-sunday { background-color: #137736; color: #fff; font-weight: bold; }</style></head>' +
            '<body>' + html + '</body></html>'
        ], { type: 'application/vnd.ms-excel;charset=utf-8' });

        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'Monthly_Attendance_Report_{{ optional($selectedClass)->class_name ?: $filters["class_id"] }}_{{ $filters["section_name"] }}_{{ $selectedMonth }}_{{ $selectedYear }}.xls';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
</script>
@endsection
