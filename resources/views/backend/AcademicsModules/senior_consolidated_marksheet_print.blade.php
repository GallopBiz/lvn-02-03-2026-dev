@extends('backend.layouts.main')

@section('main-container')
    <style id="senior-consolidated-print-styles">
        @page { size: A4 landscape; margin: 4mm 6mm; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #fff; }
        .consolidated-preview { overflow: auto; background: #f5f6f8; padding: 16px; }
        .consolidated-print-area { width: max-content; margin: 0 auto; color: #111; font-family: "Times New Roman", Times, serif; background: #fff; }
        .print-actions { padding: 10px; text-align: center; font-family: Arial, sans-serif; }
        .print-actions button { border: 0; background: #1b5fbf; color: #fff; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .sheet { width: 285mm; min-height: 0; height: auto; margin: 0 auto; padding: 0; page-break-after: always; break-after: page; page-break-inside: avoid; break-inside: avoid; overflow: visible; }
        .sheet:last-child { page-break-after: auto; break-after: auto; }
        .title { text-align: center; line-height: 1.1; margin-bottom: 0.5mm; }
        .title h2 { margin: 0; font-size: 14.5px; font-weight: bold; }
        .title h3 { margin: 0.2mm 0 0; font-size: 13.5px; font-weight: bold; }
        .title .exam-line { margin-top: 0.2mm; font-size: 12px; font-weight: bold; }
        .meta { display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; font-weight: bold; margin: 0.4mm 0; }
        .stream-banner { background: #f2f2f2; border: 1px solid #333; padding: 1mm 2mm; margin-bottom: 0.5mm; display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: bold; }
        .consolidated-print-area table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .consolidated-print-area th,
        .consolidated-print-area td { border: 1px solid #222; padding: 0.25mm 0.4mm; font-size: 12.5px; line-height: 1.02; vertical-align: middle; overflow-wrap: anywhere; }
        .consolidated-print-area th { text-align: center; font-weight: bold; }
        .consolidated-print-area td.center { text-align: center; }
        .consolidated-print-area td.right { text-align: right; }
        
        /* Column Widths totaling 284mm */
        .sr { width: 6.5mm; }
        .name { width: 34mm; }
        .scholar { width: 16.5mm; }
        .optional-sub { width: 26mm; }
        .subject { width: 32mm; }
        .tiny { width: 11mm; }
        .total { width: 13mm; }
        .grade { width: 10mm; }
        .percent { width: 13mm; }
        .division { width: 14mm; }
        .result { width: 13mm; }

        /* Term 1 Only Mode widths */
        .term1-mode .name { width: 45mm; }
        .term1-mode .scholar { width: 22mm; }
        .term1-mode .optional-sub { width: 35mm; }
        .term1-mode .subject { width: 42mm; }
        .term1-mode .tiny { width: 15mm; }
        .term1-mode .total { width: 18mm; }
        .term1-mode .grade { width: 15mm; }
        .term1-mode .percent { width: 18mm; }
        .term1-mode .division { width: 18mm; }
        .term1-mode .result { width: 18mm; }

        .student-group { page-break-inside: avoid; break-inside: avoid; }
        .student-start td { border-top-width: 1.5px; }
        .rollup td { font-weight: bold; background: #f6f6f6; }
        .nowrap { white-space: nowrap; }

        @media print {
            @page { size: A4 landscape; margin: 4mm 6mm; }
            body { margin: 0; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .main-content, .consolidated-preview { margin: 0 !important; padding: 0 !important; background: #fff !important; }
            .breadcrumb, .separator-breadcrumb, .print-actions { display: none !important; }
            .sheet { margin: 0 auto !important; padding: 0 !important; min-height: 0 !important; height: auto !important; break-after: page !important; page-break-after: always !important; break-inside: avoid !important; page-break-inside: avoid !important; overflow: visible !important; }
            .sheet:last-child { break-after: auto !important; page-break-after: auto !important; }
        }
    </style>

<div class="main-content pt-4">
    <div class="breadcrumb">
        <h2>Senior Consolidated Marksheet Print</h2>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="print-actions">
        <button onclick="printSeniorConsolidatedMarksheet()">Print</button>
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">Back</a>
    </div>

    <div class="consolidated-preview">
        <div class="consolidated-print-area">
            @php
                $formatMark = fn ($value) => is_numeric($value) && (float) $value != 0 ? number_format((float) $value, 2, '.', '') : (is_numeric($value) && (float) $value == 0 ? '0.00' : '-');
                $formatPercentage = fn ($value) => is_numeric($value) ? number_format((float) $value, 2, '.', '') : '';
            @endphp

            @forelse($reports as $report)
                @php
                    $globalSerialCounter = 1;
                @endphp

                @foreach($report['groups'] as $group)
                    @php
                        // Paginate students within each Stream & Subject Combination group
                        // Dynamic budget: 4-5 students per page (max 32 rows per sheet)
                        $groupPages = [];
                        $currentPage = [];
                        $currentRowCount = 0;
                        $maxRowsPerPage = 32;
                        $maxStudentsPerPage = 5;

                        foreach ($group['students'] as $studentRow) {
                            $subjectCount = max(1, count($studentRow['subjects']));
                            $studentTotalRows = $subjectCount + 1; // subjects + 1 rollup row

                            $wouldExceedStudents = count($currentPage) >= $maxStudentsPerPage;
                            $wouldExceedRows = ($currentRowCount + $studentTotalRows) > $maxRowsPerPage;

                            if (!empty($currentPage) && ($wouldExceedStudents || $wouldExceedRows)) {
                                $groupPages[] = $currentPage;
                                $currentPage = [];
                                $currentRowCount = 0;
                            }

                            $currentPage[] = $studentRow;
                            $currentRowCount += $studentTotalRows;
                        }

                        if (!empty($currentPage)) {
                            $groupPages[] = $currentPage;
                        }

                        $totalPagesInGroup = count($groupPages);
                    @endphp

                    @foreach($groupPages as $pageIdx => $studentsPage)
                        @php
                            $normClass = \App\Academic\AcademicHelper::normalizeClassIdentifier(optional($report['class'] ?? null)->class_name ?? '');
                            $isClass12 = ($normClass === '12');

                            $term1Heading = 'Half Yearly Exam';
                            $term2Heading = $isClass12 ? 'Pre Board' : 'Annual Exam';

                            $rawExamTitle = $report['exam_title'] ?? (!empty($report['show_term_2']) ? ($isClass12 ? 'Pre Board Examination' : 'Annual Examination') : 'Half Yearly Examination');
                            $examTitleDisplay = $rawExamTitle;
                            if (preg_match('/\bterm[\s_-]*(?:2|ii)\b/i', $examTitleDisplay) || str_contains(strtolower($examTitleDisplay), 'annual')) {
                                $examTitleDisplay = $isClass12 ? 'Pre Board Examination' : 'Annual Examination';
                            } elseif (preg_match('/\bterm[\s_-]*(?:1|i)\b/i', $examTitleDisplay)) {
                                $examTitleDisplay = 'Half Yearly Examination';
                            }
                        @endphp
                        <main class="sheet {{ empty($report['show_term_2']) ? 'term1-mode' : '' }}">
                            <div class="title">
                                <h2>Lokmanya Vidya Niketan</h2>
                                <h3>Consolidated Marksheet (Senior 11-12)</h3>
                                <div class="exam-line">Exam Name : {{ $examTitleDisplay }}</div>
                                <div class="exam-line">Academic Session : {{ str_replace('_', '-', $report['session_year'] ?? '') }}</div>
                            </div>

                            <div class="meta">
                                <div>Class-Section : {{ \App\Academic\AcademicHelper::formatClassSection($report['class']->class_name, $report['class']->section_name) }}</div>
                                <div>Class Teacher : {{ $report['class_teacher'] ?: 'Not Assigned' }}</div>
                            </div>

                            <div class="stream-banner">
                                <div>
                                    <span>STREAM : {{ $group['stream_name'] }}</span>
                                    <span style="margin: 0 4mm;">|</span>
                                    <span>COMBINATION : {{ $group['combination_name'] }}</span>
                                </div>
                                <div>
                                    @if($totalPagesInGroup > 1)
                                        <span style="font-size: 11px;">Page {{ $pageIdx + 1 }} of {{ $totalPagesInGroup }}</span>
                                    @endif
                                </div>
                            </div>

                            <table>
                                <thead>
                                    <tr>
                                        <th class="sr" rowspan="2">Sr.<br>No.</th>
                                        <th class="name" rowspan="2">Student Name</th>
                                        <th class="scholar" rowspan="2">Sch. No<br>Att.</th>
                                        <th class="optional-sub" rowspan="2">Optional<br>Subject</th>
                                        <th class="subject" rowspan="2">Subject</th>
                                        <th colspan="5">{{ $term1Heading }}</th>
                                        @if(!empty($report['show_term_2']))
                                            <th colspan="5">{{ $term2Heading }}</th>
                                        @endif
                                        <th class="percent" rowspan="2">Perc%</th>
                                        <th class="division" rowspan="2">Division</th>
                                        <th class="result" rowspan="2">Result</th>
                                    </tr>
                                    <tr>
                                        <th class="tiny">PT-1<br>(5)</th>
                                        <th class="tiny">TH</th>
                                        <th class="tiny">PR</th>
                                        <th class="total">Total<br>(100)</th>
                                        <th class="grade">Grade</th>
                                        @if(!empty($report['show_term_2']))
                                            <th class="tiny">PT-2<br>(5)</th>
                                            <th class="tiny">TH</th>
                                            <th class="tiny">PR</th>
                                            <th class="total">Total<br>(100)</th>
                                            <th class="grade">Grade</th>
                                        @endif
                                    </tr>
                                </thead>
                                @foreach($studentsPage as $studentRow)
                                    @php
                                        $serialNo = $globalSerialCounter++;
                                        $displaySubjects = collect($studentRow['subjects']);
                                        $rowspanCount = $displaySubjects->count() + ($displaySubjects->count() > 0 ? 1 : 0);
                                    @endphp
                                    <tbody class="student-group">
                                        @forelse($displaySubjects as $sIdx => $sub)
                                            <tr class="{{ $sIdx === 0 ? 'student-start' : '' }}">
                                                @if($sIdx === 0)
                                                    <td class="center" rowspan="{{ $rowspanCount }}">{{ $serialNo }}</td>
                                                    <td rowspan="{{ $rowspanCount }}">{{ $studentRow['student']->student_name }}</td>
                                                    <td class="center" rowspan="{{ $rowspanCount }}">
                                                        {{ $studentRow['scholar_no'] }}<br>
                                                        {{ $studentRow['attendance'] ?: '-' }}
                                                    </td>
                                                    <td class="center" rowspan="{{ $rowspanCount }}">
                                                        {{ $studentRow['optional_subject'] ?: '-' }}
                                                    </td>
                                                @endif
                                                <td>{{ $sub['subject'] }}</td>
                                                {{-- Term 1 --}}
                                                <td class="center">{{ is_numeric($sub['term_1']['pt']) ? $formatMark($sub['term_1']['pt']) : ($sub['term_1']['pt'] ?: '-') }}</td>
                                                <td class="center">{{ is_numeric($sub['term_1']['theory']) ? $formatMark($sub['term_1']['theory']) : ($sub['term_1']['theory'] ?: '-') }}</td>
                                                <td class="center">{{ is_numeric($sub['term_1']['practical']) ? $formatMark($sub['term_1']['practical']) : ($sub['term_1']['practical'] ?: '-') }}</td>
                                                <td class="center font-weight-bold">{{ is_numeric($sub['term_1']['total']) ? $formatMark($sub['term_1']['total']) : ($sub['term_1']['total'] ?: '-') }}</td>
                                                <td class="center">{{ $sub['term_1']['grade'] ?: '-' }}</td>
                                                {{-- Term 2 --}}
                                                @if(!empty($report['show_term_2']))
                                                    <td class="center">{{ is_numeric($sub['term_2']['pt']) ? $formatMark($sub['term_2']['pt']) : ($sub['term_2']['pt'] ?: '-') }}</td>
                                                    <td class="center">{{ is_numeric($sub['term_2']['theory']) ? $formatMark($sub['term_2']['theory']) : ($sub['term_2']['theory'] ?: '-') }}</td>
                                                    <td class="center">{{ is_numeric($sub['term_2']['practical']) ? $formatMark($sub['term_2']['practical']) : ($sub['term_2']['practical'] ?: '-') }}</td>
                                                    <td class="center font-weight-bold">{{ is_numeric($sub['term_2']['total']) ? $formatMark($sub['term_2']['total']) : ($sub['term_2']['total'] ?: '-') }}</td>
                                                    <td class="center">{{ $sub['term_2']['grade'] ?: '-' }}</td>
                                                @endif
                                                <td class="center"></td>
                                                <td class="center"></td>
                                                <td class="center"></td>
                                            </tr>
                                        @empty
                                            <tr class="student-start">
                                                <td class="center">{{ $serialNo }}</td>
                                                <td>{{ $studentRow['student']->student_name }}</td>
                                                <td class="center">
                                                    {{ $studentRow['scholar_no'] }}<br>
                                                    {{ $studentRow['attendance'] ?: '-' }}
                                                </td>
                                                <td class="center">{{ $studentRow['optional_subject'] ?: '-' }}</td>
                                                <td colspan="{{ !empty($report['show_term_2']) ? 11 : 6 }}" class="center">No assigned subjects found</td>
                                                <td class="center">{{ $studentRow['percentage'] !== null ? $formatPercentage($studentRow['percentage']) : '' }}</td>
                                                <td class="center">{{ $studentRow['division'] }}</td>
                                                <td class="center">{{ $studentRow['result_short'] ?? ($studentRow['result'] === 'Pass' ? 'P' : ($studentRow['result'] === 'Fail' ? 'F' : '')) }}</td>
                                            </tr>
                                        @endforelse

                                        @if($displaySubjects->count() > 0)
                                            <tr class="rollup">
                                                <td class="right" style="font-weight: bold; padding-right: 2mm;">Total:</td>
                                                <td></td>
                                                <td class="center">{{ (float) ($studentRow['term_1_theory_total'] ?? 0) > 0 ? $formatMark($studentRow['term_1_theory_total']) : '' }}</td>
                                                <td class="center">{{ (float) ($studentRow['term_1_practical_total'] ?? 0) > 0 ? $formatMark($studentRow['term_1_practical_total']) : '' }}</td>
                                                <td class="center">{{ (float) ($studentRow['term_1_total'] ?? 0) > 0 ? $formatMark($studentRow['term_1_total']) : '' }}</td>
                                                <td></td>
                                                @if(!empty($report['show_term_2']))
                                                    <td></td>
                                                    <td class="center">{{ (float) ($studentRow['term_2_theory_total'] ?? 0) > 0 ? $formatMark($studentRow['term_2_theory_total']) : '' }}</td>
                                                    <td class="center">{{ (float) ($studentRow['term_2_practical_total'] ?? 0) > 0 ? $formatMark($studentRow['term_2_practical_total']) : '' }}</td>
                                                    <td class="center">{{ (float) ($studentRow['term_2_total'] ?? 0) > 0 ? $formatMark($studentRow['term_2_total']) : '' }}</td>
                                                    <td></td>
                                                @endif
                                                <td class="center">{{ $studentRow['percentage'] !== null ? $formatPercentage($studentRow['percentage']) : '' }}</td>
                                                <td class="center">{{ $studentRow['division'] }}</td>
                                                <td class="center">{{ $studentRow['result_short'] ?? ($studentRow['result'] === 'Pass' ? 'P' : ($studentRow['result'] === 'Fail' ? 'F' : '')) }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                @endforeach
                            </table>
                        </main>
                    @endforeach
                @endforeach
            @empty
                <main class="sheet">
                    <div class="title">
                        <h2>No senior consolidated marks found.</h2>
                    </div>
                </main>
            @endforelse
        </div>
    </div>
</div>

<script>
function printSeniorConsolidatedMarksheet() {
    const content = document.querySelector('.consolidated-print-area').cloneNode(true);
    const styles = document.getElementById('senior-consolidated-print-styles').innerHTML;
    const printWindow = window.open('', '_blank', 'width=1200,height=800');

    printWindow.document.open();
    printWindow.document.write(`
        <html>
        <head>
            <title>Senior Consolidated Marksheet</title>
            <style>${styles}</style>
        </head>
        <body></body>
        </html>
    `);
    printWindow.document.body.appendChild(content);
    printWindow.document.close();
    printWindow.onload = () => {
        setTimeout(() => {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }, 500);
    };
}
</script>
@endsection

