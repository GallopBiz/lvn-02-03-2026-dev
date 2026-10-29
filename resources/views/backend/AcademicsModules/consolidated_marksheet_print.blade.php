@extends('backend.layouts.main')

@section('main-container')
    <style id="consolidated-marksheet-print-styles">
        @page { size: A4 landscape; margin: 4mm 6mm; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #fff; }
        .consolidated-preview { overflow: auto; background: #f5f6f8; padding: 16px; }
        .consolidated-print-area { width: max-content; margin: 0 auto; color: #111; font-family: "Times New Roman", Times, serif; background: #fff; }
        .print-actions { padding: 10px; text-align: center; font-family: Arial, sans-serif; }
        .print-actions button { border: 0; background: #1b5fbf; color: #fff; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
        .sheet { width: 285mm; min-height: 0; height: auto; margin: 0 auto; padding: 0; page-break-after: always; break-after: page; page-break-inside: avoid; break-inside: avoid; overflow: visible; }
        .sheet:last-child { page-break-after: auto; break-after: auto; }
        .title { text-align: center; line-height: 1.1; margin-bottom: 0.5mm; }
        .title h2 { margin: 0; font-size: 14px; font-weight: bold; }
        .title h3 { margin: 0.2mm 0 0; font-size: 13px; font-weight: bold; }
        .title .exam-line { margin-top: 0.2mm; font-size: 11.5px; font-weight: bold; }
        .meta { display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; font-weight: bold; margin: 0.5mm 0; }
        .consolidated-print-area table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .consolidated-print-area th,
        .consolidated-print-area td { border: 1px solid #222; padding: 0.25mm 0.4mm; font-size: 13px; line-height: 1.02; vertical-align: middle; overflow-wrap: anywhere; }
        .consolidated-print-area th { text-align: center; font-weight: bold; }
        .consolidated-print-area td.center { text-align: center; }
        .consolidated-print-area td.right { text-align: right; }
        .sr { width: 6mm; }
        .name { width: 27mm; }
        .scholar { width: 15mm; }
        .subject { width: 28mm; }
        .tiny { width: 8.5mm; }
        .total { width: 10mm; }
        .grade { width: 9mm; }
        .percent { width: 10mm; }
        .division { width: 11mm; }
        .result { width: 11mm; }

        .term1-mode .name { width: 40mm; }
        .term1-mode .scholar { width: 22mm; }
        .term1-mode .subject { width: 35mm; }
        .term1-mode .tiny { width: 16mm; }
        .term1-mode .total { width: 20mm; }
        .term1-mode .grade { width: 16mm; }
        .term1-mode .percent { width: 20mm; }
        .term1-mode .division { width: 20mm; }
        .term1-mode .result { width: 20mm; }
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
        <h2>Consolidated Marksheet Print</h2>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="print-actions">
        <button onclick="printConsolidatedMarksheet()">Print</button>
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">Back</a>
    </div>

    <div class="consolidated-preview">
        <div class="consolidated-print-area">
            @php
                $formatMark = fn ($value) => number_format(round((float) $value, 0, PHP_ROUND_HALF_UP), 2, '.', '');
                $formatPercentage = fn ($value) => number_format((float) $value, 2, '.', '');
                $hasSubject = function ($subjects, $subject) {
                    $needle = strtolower(preg_replace('/\s+/', ' ', trim((string) $subject)));
                    return collect($subjects)->contains(fn ($item) => str_contains($item, $needle) || str_contains($needle, $item));
                };
            @endphp
            @forelse($reports as $report)
                @php
                    $className = (string) ($report['class']->class_name ?? '');
                    $isClassOne = preg_match('/(^|\D)(1|i|one|first)(\D|$)/i', $className);
                    $specialNames = $report['special_subject_names'] ?? [];

                    $studentPages = [];
                    $currentPage = [];
                    $currentRowCount = 0;
                    $maxRowsPerPage = 35; // Safe vertical row limit for A4 landscape at 13px font
                    $maxStudentsPerPage = 6; // Display 6 records per page for fewer subjects when space allows

                    foreach ($report['students'] as $studentRow) {
                        $subjectRows = collect($studentRow['subjects']);
                        $existingSubjects = $subjectRows->pluck('subject')->map(fn ($subject) => strtolower(preg_replace('/\s+/', ' ', trim((string) $subject))))->all();
                        $classOneSubjects = $isClassOne
                            ? collect(['Computer', 'Sanskrit'])
                                ->reject(fn ($subject) => in_array(strtolower(trim((string) $subject)), $specialNames, true) || $hasSubject($existingSubjects, $subject))
                                ->map(fn ($subject) => [
                                    'subject' => $subject,
                                    'term_1' => ['pt' => '', 'mas' => '', 'pf' => '', 'sea' => '', 'theory' => '', 'total' => '', 'grade' => ''],
                                    'term_2' => ['pt' => '', 'mas' => '', 'pf' => '', 'sea' => '', 'theory' => '', 'total' => '', 'grade' => ''],
                                    'percentage' => null,
                                    'division' => '',
                                    'result' => '',
                                    'result_short' => '',
                                ])
                                ->values()
                            : collect();
                        $displaySubjects = $subjectRows
                            ->reject(fn ($s) => in_array(strtolower(trim((string) ($s['subject'] ?? ''))), $specialNames, true))
                            ->merge($classOneSubjects)
                            ->values();
                        $studentRows = max(1, $displaySubjects->count()) + ($subjectRows->count() > 0 ? 1 : 0);

                        $wouldExceedStudents = count($currentPage) >= $maxStudentsPerPage;
                        $wouldExceedRows = ($currentRowCount + $studentRows) > $maxRowsPerPage;

                        if (!empty($currentPage) && ($wouldExceedStudents || $wouldExceedRows)) {
                            $studentPages[] = $currentPage;
                            $currentPage = [];
                            $currentRowCount = 0;
                        }

                        $currentPage[] = $studentRow;
                        $currentRowCount += $studentRows;
                    }

                    if (!empty($currentPage)) {
                        $studentPages[] = $currentPage;
                    }

                    $globalSerialCounter = 1;
                @endphp
                @foreach($studentPages as $studentsPage)
                    <main class="sheet {{ empty($report['show_term_2']) ? 'term1-mode' : '' }}">
                <div class="title">
                    <h2>Lokmanya Vidya Niketan</h2>
                    <h3>Consolidated Marksheet</h3>
                    <div class="exam-line">Exam Name : {{ $report['exam_title'] ?? (!empty($report['show_term_2']) ? 'Term - II (Annual) Examination' : 'Term - I Examination') }}</div>
                    <div class="exam-line">Academic Session : {{ str_replace('_', '-', $report['session_year'] ?? '') }}</div>
                </div>

                <div class="meta">
                    <div>Class-Section : {{ \App\Academic\AcademicHelper::formatClassSection($report['class']->class_name, $report['class']->section_name) }}</div>
                    <div>Class Teacher : {{ $report['class_teacher'] }}</div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th class="sr" rowspan="2">Sr.<br>No.</th>
                            <th class="name" rowspan="2">Student Name</th>
                            <th class="scholar" rowspan="2">Sch. No<br>Att.</th>
                            <th class="subject" rowspan="2">Subject</th>
                            <th colspan="7">Term - I</th>
                            @if(!empty($report['show_term_2']))
                                <th colspan="7">Term - II</th>
                            @endif
                            <th class="percent" rowspan="2">Perc%</th>
                            <th class="division" rowspan="2">Division</th>
                            <th class="result" rowspan="2">Result</th>
                        </tr>
                        <tr>
                            <th class="tiny">PT<br>(5)</th>
                            <th class="tiny">MAS<br>(5)</th>
                            <th class="tiny">PF<br>(5)</th>
                            <th class="tiny">SE<br>(5)</th>
                            <th class="tiny">HY<br>(80)</th>
                            <th class="total">Total<br>(100)</th>
                            <th class="grade">Grade</th>
                            @if(!empty($report['show_term_2']))
                                <th class="tiny">PT<br>(5)</th>
                                <th class="tiny">MAS<br>(5)</th>
                                <th class="tiny">NBS<br>(5)</th>
                                <th class="tiny">SE<br>(5)</th>
                                <th class="tiny">ANN<br>(80)</th>
                                <th class="total">Total<br>(100)</th>
                                <th class="grade">Grade</th>
                            @endif
                        </tr>
                    </thead>
                    @foreach($studentsPage as $studentRow)
                        @php
                            $serialNo = $globalSerialCounter++;
                            $subjectRows = collect($studentRow['subjects']);
                            $existingSubjects = $subjectRows->pluck('subject')->map(fn ($subject) => strtolower(preg_replace('/\s+/', ' ', trim((string) $subject))))->all();
                            $specialNames = $report['special_subject_names'] ?? [];
                            $classOneSubjects = $isClassOne
                                ? collect(['Computer', 'Sanskrit'])
                                    ->reject(fn ($subject) => in_array(strtolower(trim((string) $subject)), $specialNames, true) || $hasSubject($existingSubjects, $subject))
                                    ->map(fn ($subject) => [
                                        'subject' => $subject,
                                        'term_1' => ['pt' => '', 'mas' => '', 'pf' => '', 'sea' => '', 'theory' => '', 'total' => '', 'grade' => ''],
                                        'term_2' => ['pt' => '', 'mas' => '', 'pf' => '', 'sea' => '', 'theory' => '', 'total' => '', 'grade' => ''],
                                        'percentage' => null,
                                        'division' => '',
                                        'result' => '',
                                        'result_short' => '',
                                    ])
                                    ->values()
                                : collect();
                            $displaySubjects = $subjectRows
                                ->reject(fn ($s) => in_array(strtolower(trim((string) ($s['subject'] ?? ''))), $specialNames, true))
                                ->merge($classOneSubjects)
                                ->values();
                            $rowspanCount = $displaySubjects->count() + ($subjectRows->count() > 0 ? 1 : 0);
                        @endphp
                        <tbody class="student-group">
                            @forelse($displaySubjects as $subjectIndex => $subject)
                                <tr class="{{ $subjectIndex === 0 ? 'student-start' : '' }}">
                                    @if($subjectIndex === 0)
                                        <td class="center" rowspan="{{ $rowspanCount }}">{{ $serialNo }}</td>
                                        <td rowspan="{{ $rowspanCount }}">{{ $studentRow['student']->student_name }}</td>
                                        <td class="center" rowspan="{{ $rowspanCount }}">
                                            {{ $studentRow['student']->scholar_no }}<br>
                                            {{ $studentRow['attendance'] ?: '-' }}
                                        </td>
                                    @endif
                                    <td>{{ $subject['subject'] }}</td>
                                    <td class="center">{{ is_numeric($subject['term_1']['pt']) ? $formatMark($subject['term_1']['pt']) : '-' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_1']['mas']) ? $formatMark($subject['term_1']['mas']) : '-' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_1']['pf']) ? $formatMark($subject['term_1']['pf']) : '-' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_1']['sea']) ? $formatMark($subject['term_1']['sea']) : '-' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_1']['theory']) ? $formatMark($subject['term_1']['theory']) : '-' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_1']['total']) ? $formatMark($subject['term_1']['total']) : '-' }}</td>
                                    <td class="center">{{ $subject['term_1']['grade'] ?: '-' }}</td>
                                    @if(!empty($report['show_term_2']))
                                        <td class="center">{{ is_numeric($subject['term_2']['pt']) ? $formatMark($subject['term_2']['pt']) : '-' }}</td>
                                        <td class="center">{{ is_numeric($subject['term_2']['mas']) ? $formatMark($subject['term_2']['mas']) : '-' }}</td>
                                        <td class="center">{{ is_numeric($subject['term_2']['pf']) ? $formatMark($subject['term_2']['pf']) : '-' }}</td>
                                        <td class="center">{{ is_numeric($subject['term_2']['sea']) ? $formatMark($subject['term_2']['sea']) : '-' }}</td>
                                        <td class="center">{{ is_numeric($subject['term_2']['theory']) ? $formatMark($subject['term_2']['theory']) : '-' }}</td>
                                        <td class="center">{{ is_numeric($subject['term_2']['total']) ? $formatMark($subject['term_2']['total']) : '-' }}</td>
                                        <td class="center">{{ $subject['term_2']['grade'] ?: '-' }}</td>
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
                                        {{ $studentRow['student']->scholar_no }}<br>
                                        {{ $studentRow['attendance'] ?: '-' }}
                                    </td>
                                    <td colspan="{{ !empty($report['show_term_2']) ? 15 : 8 }}" class="center">No assigned subjects found</td>
                                    <td class="center">{{ $studentRow['percentage'] !== null ? $formatPercentage($studentRow['percentage']) : '' }}</td>
                                    <td class="center">{{ $studentRow['division'] }}</td>
                                    <td class="center">{{ $studentRow['result_short'] ?? ($studentRow['result'] === 'Pass' ? 'P' : ($studentRow['result'] === 'Fail' ? 'F' : '')) }}</td>
                                </tr>
                            @endforelse
                            @if($subjectRows->count() > 0)
                                <tr class="rollup">
                                    <td colspan="5"></td>
                                    <td class="center">{{ (float) ($studentRow['term_1_theory_total'] ?? 0) > 0 ? $formatMark($studentRow['term_1_theory_total']) : '' }}</td>
                                    <td class="center">{{ (float) ($studentRow['term_1_total'] ?? 0) > 0 ? $formatMark($studentRow['term_1_total']) : '' }}</td>
                                    <td></td>
                                    @if(!empty($report['show_term_2']))
                                        <td colspan="4"></td>
                                        <td class="center">{{ (float) ($studentRow['term_2_theory_total'] ?? 0) > 0 ? $formatMark($studentRow['term_2_theory_total']) : '' }}</td>
                                        <td class="center">{{ (float) ($studentRow['term_2_total'] ?? 0) > 0 ? $formatMark($studentRow['term_2_total']) : ((float) ($studentRow['grand_total'] ?? 0) > 0 ? $formatMark($studentRow['grand_total']) : '') }}</td>
                                        <td></td>
                                    @endif
                                    <td class="center">{{ $studentRow['percentage'] !== null ? $formatPercentage($studentRow['percentage']) : '' }}</td>
                                    <td class="center">{{ $studentRow['division'] }}</td>
                                    <td class="center">{{ $studentRow['result_short'] ?? ($studentRow['result'] === 'Pass' ? 'P' : ($studentRow['result'] === 'Fail' ? 'F' : $studentRow['result'])) }}</td>
                                </tr>
                            @endif
                        </tbody>
                    @endforeach
                </table>
            </main>
                @endforeach
            @empty
                <main class="sheet">
                    <div class="title">
                        <h2>No consolidated marks found.</h2>
                    </div>
                </main>
            @endforelse
        </div>
    </div>
</div>

<script>
function printConsolidatedMarksheet() {
    const content = document.querySelector('.consolidated-print-area').cloneNode(true);
    const styles = document.getElementById('consolidated-marksheet-print-styles').innerHTML;
    const printWindow = window.open('', '_blank', 'width=1200,height=800');

    printWindow.document.open();
    printWindow.document.write(`
        <html>
        <head>
            <title>Consolidated Marksheet</title>
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
