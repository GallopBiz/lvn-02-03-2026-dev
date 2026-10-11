@extends('backend.layouts.main')

@section('main-container')
@php
    $sheets = $sheets ?? [['template' => $template, 'printData' => $printData]];
@endphp

<style id="term1-print-styles">
    @page {
        size: A4 portrait;
        margin: 5mm 8mm 6mm 8mm;
    }

    :root {
        --t1-font: "Times New Roman", Times, "Nimbus Roman No9 L", serif;
        --t1-header-space: 12mm;
    }

    * {
        box-sizing: border-box;
    }

    .t1-preview-container {
        overflow: auto;
        background: #eef1f5;
        padding: 20px 10px;
        min-height: 100vh;
    }

    .t1-actions-bar {
        max-width: 200mm;
        margin: 0 auto 15px auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        background: #ffffff;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .t1-print-area {
        width: 100%;
        max-width: 200mm;
        margin: 0 auto;
        color: #000;
        font-family: var(--t1-font);
    }

    .sheet-term-1 {
        width: 196mm;
        min-height: 275mm;
        margin: 0 auto 25px auto;
        padding: 6mm 8mm 6mm 8mm;
        position: relative;
        background: #fff;
        color: #000;
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        break-after: page;
        page-break-after: always;
        page-break-inside: avoid;
        font-family: var(--t1-font);
    }

    .sheet-term-1:last-child {
        margin-bottom: 0;
        break-after: auto;
        page-break-after: auto;
    }

    .sheet-term-1 table {
        font-family: var(--t1-font);
    }

    /* Header & Logo */
    .t1-header {
        width: 100%;
        margin-bottom: 2mm;
        padding-top: var(--t1-header-space, 12mm);
        transition: padding-top 0.15s ease;
    }

    .t1-header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        min-height: 18mm;
        padding-bottom: 2mm;
    }

    .t1-header-left {
        flex: 1;
    }

    .t1-header-title {
        font-size: 14px;
        font-weight: bold;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        color: #000;
        line-height: 1.2;
    }

    .t1-header-logo {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .t1-header-logo svg {
        display: block;
        width: 62px;
        height: 62px;
    }

    .t1-session-wrap {
        text-align: center;
        margin-top: 2mm;
        margin-bottom: 4mm;
    }

    .t1-session-title {
        font-size: 13.5px;
        font-weight: bold;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        display: inline-block;
        padding-bottom: 1.5mm;
        border-bottom: 1.5px solid #000;
    }

    /* Body */
    .t1-body {
        width: 100%;
    }

    /* Student Info (Two Columns) */
    .t1-student-info {
        width: 100%;
        margin-bottom: 5mm;
        font-size: 13px;
        line-height: 1.6;
    }

    .t1-student-info table {
        width: 100%;
        border-collapse: collapse;
    }

    .t1-student-info td {
        padding: 1.2px 0;
        vertical-align: top;
    }

    .t1-student-info td.lbl {
        font-weight: bold;
        white-space: nowrap;
    }

    .t1-student-info td.sep {
        width: 12px;
        text-align: center;
        font-weight: bold;
    }

    .t1-student-info td.val {
        font-weight: normal;
    }

    /* Section Headings */
    .t1-section-heading {
        text-align: center;
        font-weight: bold;
        font-size: 13px;
        letter-spacing: 0.6px;
        margin: 2mm 0 2mm 0;
        text-transform: uppercase;
    }

    .t1-sub-heading {
        text-align: center;
        font-weight: bold;
        font-size: 12px;
        letter-spacing: 0.5px;
        margin-bottom: 1.5mm;
        text-transform: uppercase;
    }

    /* Scholastic Areas Table */
    .t1-scholastic-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 11.5px;
        line-height: 1.15;
        margin-bottom: 3.5mm;
    }

    .t1-scholastic-table th,
    .t1-scholastic-table td {
        border: 1px solid #000;
        padding: 1.8mm 1mm;
        vertical-align: middle;
        text-align: center;
    }

    .t1-scholastic-table th {
        font-weight: bold;
        font-size: 11px;
        background: #fff;
    }

    .t1-scholastic-table td.subj {
        text-align: left;
        padding-left: 2.5mm;
        font-weight: 500;
    }

    /* Additional / Grade-Only Subject Table */
    .t1-grade-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 11.5px;
        margin-bottom: 4.5mm;
    }

    .t1-grade-table th,
    .t1-grade-table td {
        border: 1px solid #000;
        padding: 1.8mm 2.5mm;
        vertical-align: middle;
    }

    .t1-grade-table th {
        font-weight: bold;
        font-size: 11.5px;
    }

    .t1-grade-table td.subj {
        text-align: left;
        font-weight: 500;
    }

    .t1-grade-table td.grd {
        text-align: center;
        font-weight: bold;
    }

    /* Co-Scholastic & Discipline Grid */
    .t1-co-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5mm;
        margin-bottom: 4.5mm;
    }

    .t1-co-subtable {
        width: 100%;
        border-collapse: collapse;
        font-size: 11.5px;
    }

    .t1-co-subtable th,
    .t1-co-subtable td {
        border: 1px solid #000;
        padding: 1.8mm 2.5mm;
        vertical-align: middle;
    }

    .t1-co-subtable th {
        font-weight: bold;
        font-size: 11px;
    }

    .t1-co-subtable td.grd {
        width: 22%;
        text-align: center;
        font-weight: bold;
    }

    /* Class Teacher's Remarks */
    .t1-remarks-wrap {
        margin-bottom: 7mm;
    }

    .t1-remarks-box {
        border: 1px solid #000;
        padding: 2.5mm 4mm;
        font-size: 12.5px;
        min-height: 8.5mm;
        line-height: 1.35;
    }

    /* Footer Signatures */
    .t1-signatures-wrap {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        font-size: 13px;
        font-weight: bold;
        margin-bottom: 6mm;
        padding: 0 1mm;
    }

    .t1-attendance-text {
        font-weight: bold;
        font-size: 13px;
    }

    /* Grading Scale Legend */
    .t1-legend-wrap {
        text-align: center;
        font-size: 11.5px;
        line-height: 1.4;
    }

    .t1-legend-title {
        font-weight: bold;
        margin-bottom: 1.2px;
    }

    .t1-legend-scale {
        font-weight: normal;
        word-spacing: 1px;
    }

    @media print {
        /* Override Gull theme body * { visibility: hidden; } */
        body * {
            visibility: hidden !important;
        }

        #print-area,
        #print-area *,
        .t1-print-area,
        .t1-print-area *,
        .sheet-term-1,
        .sheet-term-1 * {
            visibility: visible !important;
        }

        #print-area {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .main-header,
        .header-menu,
        .sidebar-left,
        .side-content-wrap,
        .breadcrumb,
        .separator-breadcrumb,
        .t1-actions-bar,
        .app-footer {
            display: none !important;
            visibility: hidden !important;
        }

        .main-content,
        .main-content-wrap,
        .t1-preview-container {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            width: 100% !important;
            min-height: auto !important;
        }

        .sheet-term-1 {
            width: 100% !important;
            max-width: 100% !important;
            min-height: auto !important;
            margin: 0 auto !important;
            padding: 6mm 8mm 6mm 8mm !important;
            box-shadow: none !important;
            page-break-after: always !important;
            break-after: page !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .t1-header {
            width: 100% !important;
            padding-top: var(--t1-header-space, 12mm) !important;
            margin-bottom: 2mm !important;
        }

        .t1-header-top {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-end !important;
            min-height: 18mm !important;
        }

        .t1-header-logo {
            display: flex !important;
        }

        .t1-header-logo svg {
            display: block !important;
        }

        .sheet-term-1:last-child {
            page-break-after: auto !important;
            break-after: auto !important;
        }
    }
</style>

<div class="main-content p-0">
    <div class="t1-preview-container">
        <!-- Floating Actions Bar -->
        <div class="t1-actions-bar">
            <div class="d-flex align-items-center flex-wrap">
                <a href="{{ route('academic.term1-marksheet.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="i-Arrow-Back"></i> Back
                </a>
                <span class="ml-3 font-weight-bold text-muted" style="font-size: 13px;">
                    Total Sheets: {{ count($sheets) }} (Portrait A4)
                </span>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap: 15px;">
                <div class="d-flex align-items-center">
                    <label for="headerSpaceSelect" class="mb-0 mr-2 font-weight-bold text-dark" style="font-size: 12.5px;">
                        <i class="i-Arrow-Down font-weight-bold mr-1 text-primary"></i> Top Margin:
                    </label>
                    <select id="headerSpaceSelect" class="form-control form-control-sm font-weight-bold" style="width: auto; height: 32px;" onchange="setHeaderSpace(this.value)">
                        <option value="0mm">0 mm</option>
                        <option value="5mm">5 mm</option>
                        <option value="10mm">10 mm</option>
                        <option value="12mm" selected>12 mm (Default)</option>
                        <option value="15mm">15 mm</option>
                        <option value="20mm">20 mm</option>
                        <option value="25mm">25 mm</option>
                        <option value="30mm">30 mm</option>
                    </select>
                </div>
                <div class="d-flex align-items-center">
                    <div class="custom-control custom-checkbox" title="Toggle Title Text">
                        <input type="checkbox" class="custom-control-input" id="showTitleCheckbox" checked onchange="toggleHeaderTitle(this.checked)">
                        <label class="custom-control-label font-weight-bold text-dark" for="showTitleCheckbox" style="font-size: 12px; cursor: pointer;">
                            Header Title
                        </label>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-primary btn-sm px-4" onclick="window.print()">
                        <i class="i-Printer"></i> Print Term 1 Report Card
                    </button>
                </div>
            </div>
        </div>

        <div class="t1-print-area" id="print-area">
            @foreach($sheets as $sheet)
                @php
                    $pData = $sheet['printData'] ?? [];
                    $studentMeta = $pData['student_meta'] ?? [];
                    $sessionYear = str_replace('_', '-', $pData['session_year'] ?? '2025-2026');
                    $scholasticRows = $pData['scholastic_rows'] ?? [];
                    $gradeOnlyRows = $pData['grade_only_rows'] ?? [];
                    $coScholastic = $pData['co_scholastic'] ?? [];
                    $discipline = $pData['discipline'] ?? [];
                    $remarks = $pData['remarks'] ?? 'Needs to develop a better sense of responsibility.';
                    $attendance = $pData['attendance'] ?? '/';
                    $rollNo = $pData['roll_no'] ?? '';
                    $classRoman = $pData['class_roman'] ?? (optional($pData['class'])->class_name ?? '');
                    $sectionName = $pData['section_name'] ?? (optional($pData['class'])->section_name ?? '');
                @endphp

                <main class="sheet-term-1">
                    <!-- Top Header with School Logo -->
                    <header class="t1-header">
                        <div class="t1-header-top">
                            <div class="t1-header-left">
                                <div class="t1-header-title">TERM - I REPORT CARD</div>
                            </div>

                            <!-- Top Right School Seal in Header -->
                            <div class="t1-header-logo">
                                <svg width="62" height="62" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="60" cy="60" r="57" stroke="#000" stroke-width="2.5" fill="#fff"/>
                                    <circle cx="60" cy="60" r="51" stroke="#000" stroke-width="1.2" stroke-dasharray="2.5,2" fill="#fff"/>
                                    <circle cx="60" cy="60" r="39" stroke="#000" stroke-width="1.5" fill="#fff"/>
                                    
                                    <path id="t1SealArcTop_{{ $loop->index }}" d="M 17,60 A 43,43 0 0,1 103,60" fill="none"/>
                                    <text font-size="8.2" font-family="'Times New Roman', serif" font-weight="bold" letter-spacing="1.2">
                                        <textPath href="#t1SealArcTop_{{ $loop->index }}" startOffset="50%" text-anchor="middle">
                                            LOKMANYA VIDYA NIKETAN
                                        </textPath>
                                    </text>

                                    <!-- Central Torch & Book Emblem -->
                                    <g transform="translate(60, 57)">
                                        <!-- Flame -->
                                        <path d="M0,-16 Q4,-8 0,-4 Q-4,-8 0,-16 Z" fill="#000"/>
                                        <!-- Torch cup -->
                                        <path d="M-5,-4 L5,-4 L3,3 L-3,3 Z" fill="#000"/>
                                        <!-- Torch handle -->
                                        <rect x="-1.5" y="3" width="3" height="10" fill="#000"/>
                                        <!-- Book base -->
                                        <path d="M-11,12 Q0,9 11,12 L9,15 Q0,12 -9,15 Z" fill="#000"/>
                                    </g>

                                    <text x="60" y="105" font-size="8.5" font-family="'Times New Roman', serif" font-weight="bold" letter-spacing="1" text-anchor="middle">INDORE</text>
                                </svg>
                            </div>
                        </div>

                        <!-- Academic Session Title -->
                        <div class="t1-session-wrap">
                            <div class="t1-session-title">SESSION : {{ $sessionYear }}</div>
                        </div>
                    </header>

                    <!-- Body Content -->
                    <div class="t1-body">

                    <!-- Student Info (2 Columns) -->
                    <section class="t1-student-info">
                        <table>
                            <tr>
                                <td class="lbl" style="width: 20%;">Student Name</td>
                                <td class="sep">:</td>
                                <td class="val" style="width: 32%;">{{ $studentMeta['student_name'] ?? '' }}</td>

                                <td class="lbl" style="width: 18%;">Scholar No</td>
                                <td class="sep">:</td>
                                <td class="val" style="width: 26%;">{{ $studentMeta['scholar_no'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Mother's Name</td>
                                <td class="sep">:</td>
                                <td class="val">{{ $studentMeta['mother_name'] ?? '' }}</td>

                                <td class="lbl">Roll No</td>
                                <td class="sep">:</td>
                                <td class="val">{{ $rollNo }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Father's Name</td>
                                <td class="sep">:</td>
                                <td class="val">{{ $studentMeta['father_name'] ?? '' }}</td>

                                <td class="lbl">Class</td>
                                <td class="sep">:</td>
                                <td class="val">{{ $classRoman }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Date Of Birth</td>
                                <td class="sep">:</td>
                                <td class="val">{{ $studentMeta['date_of_birth'] ?? '' }}</td>

                                <td class="lbl">Section</td>
                                <td class="sep">:</td>
                                <td class="val">{{ $sectionName }}</td>
                            </tr>
                        </table>
                    </section>

                    <!-- Scholastic Areas Heading -->
                    <div class="t1-section-heading">SCHOLASTIC AREAS</div>

                    <!-- Scholastic Table (Exact 9 Columns) -->
                    <table class="t1-scholastic-table">
                        <thead>
                            <tr>
                                <th style="width: 5.5%;">S.<br>No</th>
                                <th style="width: 25.5%;">Subject</th>
                                <th style="width: 10%;">Periodic<br>Test (5)</th>
                                <th style="width: 10%;">Multiple<br>Ass. (5)</th>
                                <th style="width: 9%;">Portfolio<br>(5)</th>
                                <th style="width: 11.5%;">Subject<br>Enrichment<br>(5)</th>
                                <th style="width: 11%;">Half Yearly<br>Exam (80)</th>
                                <th style="width: 10%;">Marks<br>Obtained<br>(100)</th>
                                <th style="width: 7.5%;">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($scholasticRows as $idx => $row)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td class="subj">{{ $row['subject'] }}</td>
                                    <td>{{ $row['pt'] }}</td>
                                    <td>{{ $row['mas'] }}</td>
                                    <td>{{ $row['portfolio'] }}</td>
                                    <td>{{ $row['sea'] }}</td>
                                    <td>{{ $row['theory'] }}</td>
                                    <td>{{ $row['obtained'] }}</td>
                                    <td style="font-weight: bold;">{{ $row['grade'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-2">No scholastic subjects found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Additional / Grade-Only Subjects Table -->
                    @if(count($gradeOnlyRows) > 0)
                        <table class="t1-grade-table">
                            <thead>
                                <tr>
                                    <th style="width: 76.5%; text-align: left; padding-left: 2.5mm;">Subject</th>
                                    <th style="width: 23.5%; text-align: center;">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gradeOnlyRows as $gRow)
                                    <tr>
                                        <td class="subj">{{ $gRow['subject'] }}</td>
                                        <td class="grd">{{ $gRow['grade'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <!-- Co-Scholastic and Discipline Side-by-Side -->
                    <div class="t1-co-grid">
                        <div>
                            <div class="t1-sub-heading">CO-SCHOLASTIC AREAS</div>
                            <table class="t1-co-subtable">
                                <thead>
                                    <tr>
                                        <th style="text-align: left; padding-left: 2.5mm;">Grading on Three Point Scale A,B &amp; C</th>
                                        <th style="width: 22%; text-align: center;">Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($coScholastic as $cs)
                                        <tr>
                                            <td style="text-align: left; padding-left: 2.5mm;">{{ $cs['title'] }}</td>
                                            <td class="grd">{{ $cs['grade'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div>
                            <div class="t1-sub-heading">DISCIPLINE</div>
                            <table class="t1-co-subtable">
                                <thead>
                                    <tr>
                                        <th style="text-align: left; padding-left: 2.5mm;">Grading on Three Point Scale A,B &amp; C</th>
                                        <th style="width: 22%; text-align: center;">Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($discipline as $disc)
                                        <tr>
                                            <td style="text-align: left; padding-left: 2.5mm;">{{ $disc['title'] }}</td>
                                            <td class="grd">{{ $disc['grade'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Class Teacher's Remarks -->
                    <div class="t1-remarks-wrap">
                        <div class="t1-sub-heading">CLASS TEACHER'S REMARKS</div>
                        <div class="t1-remarks-box">
                            {{ $remarks }}
                        </div>
                    </div>

                    <!-- Footer Signatures & Attendance -->
                    <div class="t1-signatures-wrap">
                        <div class="t1-attendance-text">Attendance : {{ $attendance }}</div>
                        <div>Signature of Class Teacher</div>
                        <div>Signature of Principal</div>
                    </div>

                    <!-- Grading Scale Legend -->
                    <div class="t1-legend-wrap">
                        <div class="t1-legend-title">Grading Scale for Scholastic Areas</div>
                        <div class="t1-legend-scale">91-100 : A1, 81-90 : A2, 71-80 : B1, 61-70 : B2, 51-60 : C1, 41-50 : C2, 33-40 : D, 32 &amp; Below : E</div>
                    </div>
                    </div><!-- /.t1-body -->
                </main>
            @endforeach
        </div>
    </div>
</div>

<script>
    function setHeaderSpace(val) {
        document.documentElement.style.setProperty('--t1-header-space', val);
        const headers = document.querySelectorAll('.t1-header');
        headers.forEach(h => {
            h.style.setProperty('padding-top', val, 'important');
        });
        try {
            localStorage.setItem('t1_header_space', val);
        } catch(e) {}
    }

    function toggleHeaderTitle(show) {
        const titles = document.querySelectorAll('.t1-header-title');
        titles.forEach(t => {
            t.style.display = show ? 'block' : 'none';
        });
        try {
            localStorage.setItem('t1_show_header_title', show ? '1' : '0');
        } catch(e) {}
    }

    document.addEventListener('DOMContentLoaded', function() {
        try {
            const savedSpace = localStorage.getItem('t1_header_space');
            if (savedSpace) {
                const select = document.getElementById('headerSpaceSelect');
                if (select) {
                    select.value = savedSpace;
                }
                setHeaderSpace(savedSpace);
            }
            const savedTitle = localStorage.getItem('t1_show_header_title');
            if (savedTitle !== null) {
                const chk = document.getElementById('showTitleCheckbox');
                if (chk) {
                    chk.checked = (savedTitle === '1');
                    toggleHeaderTitle(savedTitle === '1');
                }
            }
        } catch(e) {}
    });
</script>
@endsection
