@extends('backend.layouts.main')

@section('main-container')
<style id="senior-marksheet-print-styles">
    @page {
        size: A4 landscape;
        margin: 8mm;
    }

    :root {
        --marksheet-font: "Times New Roman", Times, "Nimbus Roman No9 L", serif;
    }

    * {
        box-sizing: border-box;
    }

    .marksheet-preview {
        overflow: auto;
        background: #f5f6f8;
        padding: 16px;
    }

    .marksheet-print-area {
        width: max-content;
        margin: 0 auto;
        color: #222;
        font-family: var(--marksheet-font);
        background: #fff;
    }

    .sheet {
        width: 281mm;
        min-height: 194mm;
        height: auto;
        margin: 0 auto;
        padding: 16mm 4mm 6mm;
        position: relative;
        overflow: visible;
        background: #fff;
        break-after: page;
        page-break-after: always;
        page-break-inside: avoid;
    }

    .sheet:last-child {
        break-after: auto;
        page-break-after: auto;
    }

    .sheet,
    .sheet table,
    .sheet th,
    .sheet td {
        font-family: var(--marksheet-font);
    }

    .top-right-logo {
        position: absolute;
        top: 2mm;
        right: 4mm;
        width: 24mm;
        text-align: right;
        line-height: 0;
        z-index: 10;
    }

    .top-right-logo img {
        width: 22mm;
        height: auto;
        max-height: 24mm;
        object-fit: contain;
        display: inline-block;
    }

    .print-date {
        text-align: right;
        font-size: 13px;
        margin-top: 20mm;
        margin-bottom: 2mm;
        font-weight: 500;
    }

    .header-title {
        text-align: center;
        margin-bottom: 3mm;
    }

    .header-title .session-text {
        font-size: 16px;
        font-weight: bold;
        letter-spacing: 0.5px;
    }

    .header-title .report-card-text {
        font-size: 18px;
        font-weight: bold;
        margin-top: 1mm;
    }

    .student-info-box {
        width: 100%;
        margin-bottom: 2.5mm;
        font-size: 13.5px;
        line-height: 1.35;
    }

    .student-info-table {
        width: 100%;
        border-collapse: collapse;
    }

    .student-info-table td {
        padding: 1.2px 2px;
        vertical-align: top;
    }

    .student-info-table .label-col {
        font-weight: 600;
        white-space: nowrap;
    }

    .student-info-table .val-col {
        font-weight: 500;
    }

    /* Marks Table Styling */
    table.senior-marks-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 11.5px;
        line-height: 1.15;
        border: 1.5px solid #222;
    }

    .senior-marks-table th,
    .senior-marks-table td {
        border: 1px solid #333;
        padding: 3px 2px;
        text-align: center;
        vertical-align: middle;
        overflow-wrap: anywhere;
    }

    .senior-marks-table th {
        font-weight: bold;
        background: #fff;
    }

    .senior-marks-table .col-sub-name {
        width: 38mm;
        text-align: left;
        padding-left: 5px;
    }

    .senior-marks-table .col-pt {
        width: 12.5mm;
    }

    .senior-marks-table .col-mm-obt {
        width: 13mm;
    }

    .senior-marks-table .col-obt-100 {
        width: 16mm;
    }

    .senior-marks-table .col-total-140 {
        width: 15mm;
    }

    .senior-marks-table .col-grand-280 {
        width: 16mm;
    }

    .senior-marks-table td.subject-name {
        text-align: left;
        padding-left: 5px;
        font-weight: normal;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .senior-marks-table tr.total-row td,
    .senior-marks-table tr.percentage-row td {
        font-weight: bold;
    }

    .senior-marks-table tr.remark-att-row td {
        font-size: 12px;
        padding: 4px 6px;
    }

    .footer-signatures {
        margin-top: 14mm;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        width: 100%;
        font-size: 13.5px;
        font-weight: bold;
        padding: 0 4mm;
    }

    .footer-signatures .signature-left {
        text-align: left;
    }

    .footer-signatures .signature-right {
        text-align: right;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            color: #000 !important;
        }

        .main-content,
        .main-content * {
            visibility: visible;
        }

        .header-top,
        .sidebar-left,
        .sidebar-left-secondary,
        .breadcrumb,
        .separator-breadcrumb,
        .print-actions,
        .marksheet-preview {
            display: none !important;
            padding: 0 !important;
            margin: 0 !important;
            background: none !important;
        }

        .marksheet-print-area {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background: #fff !important;
        }

        .sheet {
            margin: 0 auto !important;
            width: 281mm !important;
            height: auto !important;
            min-height: 194mm !important;
            overflow: visible !important;
            box-shadow: none !important;
            page-break-inside: avoid !important;
            padding-top: 10mm !important;
        }
    }
</style>

@php
    $previousUrl = url()->previous();
    $currentUrl = url()->current();
    $backUrl = route('academic.senior-marksheets.index');
    if ($previousUrl && $previousUrl !== $currentUrl && !str_contains($previousUrl, 'login') && !str_contains($previousUrl, 'signin')) {
        $backUrl = $previousUrl;
    }
    $sheets = $sheets ?? [['printData' => $printData]];
@endphp

<div class="main-content pt-3">
    <div class="breadcrumb d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div>
            <h2 class="mb-0 font-weight-bold" style="font-size: 20px;">Class 11 & 12 Marksheet Print</h2>
            <span class="text-muted small">Total sheets: {{ count($sheets) }} (Landscape A4)</span>
        </div>
        <div class="print-actions d-flex align-items-center gap-2">
            <a href="{{ $backUrl }}" class="btn btn-sm btn-secondary d-inline-flex align-items-center">
                <i class="fa fa-arrow-left mr-1"></i> Back
            </a>
            <button class="btn btn-sm btn-success d-inline-flex align-items-center" type="button" onclick="window.print()">
                <i class="fa fa-print mr-1"></i> Print Marksheet
            </button>
        </div>
    </div>
    <div class="separator-breadcrumb border-top mb-3"></div>

    <div class="marksheet-preview">
        <div id="seniorMarksheetPrintArea" class="marksheet-print-area">
            @foreach($sheets as $sheet)
            @php
                $data = $sheet['printData'] ?? [];
                $rows = $data['rows'] ?? [];
                $meta = $data['student_meta'] ?? [];
            @endphp
            <main class="sheet">
                {{-- Top Right CBSE Emblem --}}
                <div class="top-right-logo">
                    <img src="{{ asset('images/cbse-logo.png') }}" alt="CBSE Emblem">
                </div>

                {{-- Header Title --}}
                <div class="header-title">
                    <div class="session-text">Academic Session : {{ $data['display_session'] ?? '2025-2026' }}</div>
                    <div class="report-card-text">{{ $data['title'] ?? 'Term - II (Annual) Examination Report Card' }}</div>
                </div>

                {{-- Date Top Right --}}
                <div class="print-date">Date : {{ $data['date'] ?? date('d-M-Y') }}</div>

                {{-- Student Information Box (Exact 2-Column layout matching reference image) --}}
                <div class="student-info-box">
                    <table class="student-info-table">
                        <tr>
                            <td style="width: 50%;">
                                <table style="width: 100%;">
                                    <tr>
                                        <td class="label-col" style="width: 32mm;">Student's Name</td>
                                        <td class="val-col">: {{ $meta['student_name'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label-col">Mother's Name</td>
                                        <td class="val-col">: {{ $meta['mother_name'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label-col">Father's Name</td>
                                        <td class="val-col">: {{ $meta['father_name'] ?? '' }}</td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width: 50%;">
                                <table style="width: 100%;">
                                    <tr>
                                        <td class="label-col" style="width: 32mm;">Scholar No.</td>
                                        <td class="val-col">: {{ $meta['scholar_no'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label-col">Roll No.</td>
                                        <td class="val-col">: {{ $data['roll_no'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label-col">Class-Section</td>
                                        <td class="val-col">: {{ \App\Academic\AcademicHelper::formatClassSection($data['class_section'] ?? '') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label-col">Date of Birth</td>
                                        <td class="val-col">: {{ $meta['date_of_birth'] ?? '' }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- Exact 16-Column Senior Marksheet Table --}}
                <table class="senior-marks-table">
                    <thead>
                        {{-- Super Header Row --}}
                        <tr>
                            <th rowspan="4" class="col-sub-name" style="vertical-align: top; padding: 4px 6px;">
                                <div style="font-size: 12px; font-weight: bold; text-align: left;">Scholastic Area</div>
                                <div style="font-size: 12px; font-weight: bold; text-align: center; margin-top: 14px;">Subject's Name</div>
                            </th>
                            <th colspan="7" style="font-size: 13px;">Term - I</th>
                            <th colspan="8" style="font-size: 13px;">Term-II</th>
                        </tr>
                        {{-- Sub Header Row --}}
                        <tr>
                            <th rowspan="2" class="col-pt">PT - 1<br>(40)</th>
                            <th colspan="4">Half Yearly Exam</th>
                            <th rowspan="2" class="col-obt-100">Marks<br>Obtained<br>HY (100)</th>
                            <th rowspan="2" class="col-total-140">Grand<br>Total<br>(140)</th>

                            <th rowspan="2" class="col-pt">PT - II<br>(40)</th>
                            <th colspan="4">Annual Exam</th>
                            <th rowspan="2" class="col-obt-100">Marks<br>Obtained<br>(100)</th>
                            <th rowspan="2" class="col-total-140">Total<br>(140)</th>
                            <th rowspan="2" class="col-grand-280">Grand<br>Total<br>(280)</th>
                        </tr>
                        {{-- Theory & Practical Group Row --}}
                        <tr>
                            <th colspan="2">Theory</th>
                            <th colspan="2">Practical</th>
                            <th colspan="2">Theory</th>
                            <th colspan="2">Practical</th>
                        </tr>
                        {{-- M.M. and M.OBT. Row --}}
                        <tr>
                            <th>M.M.</th>
                            <th>M.OBT.</th>
                            <th>M.M.</th>
                            <th>M.OBT.</th>
                            <th>M.M.</th>
                            <th>M.OBT.</th>
                            <th>M.M.</th>
                            <th>M.OBT.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                        <tr>
                            <td class="subject-name">{{ $row['subject_name'] ?? '' }}</td>
                            {{-- Term I --}}
                            <td>{{ $row['pt1'] !== '' ? $row['pt1'] : '' }}</td>
                            <td>{{ $row['hy_theory_mm'] ?? '' }}</td>
                            <td>{{ $row['hy_theory_obt'] !== '' ? $row['hy_theory_obt'] : '' }}</td>
                            <td>{{ $row['hy_practical_mm'] ?? '' }}</td>
                            <td>{{ $row['hy_practical_obt'] !== '' ? $row['hy_practical_obt'] : '' }}</td>
                            <td style="font-weight: 500;">{{ $row['hy_obtained'] !== '' ? $row['hy_obtained'] : '' }}</td>
                            <td style="font-weight: 500;">{{ $row['hy_grand_total'] !== '' ? $row['hy_grand_total'] : '' }}</td>
                            {{-- Term II --}}
                            <td>{{ $row['pt2'] !== '' ? $row['pt2'] : '' }}</td>
                            <td>{{ $row['annual_theory_mm'] ?? '' }}</td>
                            <td>{{ $row['annual_theory_obt'] !== '' ? $row['annual_theory_obt'] : '' }}</td>
                            <td>{{ $row['annual_practical_mm'] ?? '' }}</td>
                            <td>{{ $row['annual_practical_obt'] !== '' ? $row['annual_practical_obt'] : '' }}</td>
                            <td style="font-weight: 500;">{{ $row['annual_obtained'] !== '' ? $row['annual_obtained'] : '' }}</td>
                            <td style="font-weight: 500;">{{ $row['annual_total'] !== '' ? $row['annual_total'] : '' }}</td>
                            <td style="font-weight: bold;">{{ $row['combined_grand_total'] !== '' ? $row['combined_grand_total'] : '' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="16" class="text-center py-3">No subjects found for this student.</td>
                        </tr>
                        @endforelse

                        {{-- Total Row --}}
                        <tr class="total-row">
                            <td style="text-align: center;">Total</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: center;">{{ $data['total_hy_obtained'] ?? '' }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: center;">{{ $data['total_annual_obtained'] ?? '' }}</td>
                            <td></td>
                            <td></td>
                        </tr>

                        {{-- Percentage Row --}}
                        <tr class="percentage-row">
                            <td style="text-align: center;">Percentage</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: center;">{{ $data['hy_percentage'] ?? '' }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td colspan="2" style="text-align: center;">Percentage</td>
                            <td style="text-align: center;">{{ $data['annual_percentage'] ?? '' }}</td>
                            <td></td>
                            <td></td>
                        </tr>

                        {{-- Remarks and Attendance Row --}}
                        <tr class="remark-att-row">
                            <td colspan="11" style="text-align: left; padding: 5px 8px;">
                                <strong>Class Teacher Remark : </strong> &nbsp; {{ $data['remarks'] ?? 'Passed and promoted to next class.' }}
                            </td>
                            <td colspan="5" style="text-align: right; padding: 5px 8px; white-space: nowrap;">
                                <strong>Attendance : </strong> &nbsp; {{ $data['attendance'] ?? '' }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- Signatures Footer --}}
                <div class="footer-signatures">
                    <div class="signature-left">Signature of Class Teacher</div>
                    <div class="signature-right">Signature of Principal</div>
                </div>
            </main>
            @endforeach
        </div>
    </div>
</div>
@endsection
