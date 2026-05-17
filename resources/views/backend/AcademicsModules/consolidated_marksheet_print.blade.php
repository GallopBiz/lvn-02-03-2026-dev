@extends('backend.layouts.main')

@section('main-container')
    <style id="consolidated-marksheet-print-styles">
        @page { size: A4 landscape; margin: 6mm; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #fff; }
        .consolidated-preview { overflow: auto; background: #f5f6f8; padding: 16px; }
        .consolidated-print-area { width: max-content; margin: 0 auto; color: #111; font-family: "Times New Roman", Times, serif; background: #fff; }
        .print-actions { padding: 10px; text-align: center; font-family: Arial, sans-serif; }
        .print-actions button { border: 0; background: #1b5fbf; color: #fff; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
        .sheet { width: 285mm; min-height: 198mm; margin: 0 auto; padding: 4mm 4mm; page-break-after: always; break-after: page; overflow: hidden; }
        .sheet:last-child { page-break-after: auto; break-after: auto; }
        .title { text-align: center; line-height: 1.12; margin-bottom: 1mm; }
        .title h2 { margin: 0; font-size: 14px; font-weight: bold; }
        .title h3 { margin: 0.5mm 0 0; font-size: 13px; font-weight: bold; }
        .title .exam-line { margin-top: 0.5mm; font-size: 12px; font-weight: bold; }
        .meta { display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; font-weight: bold; margin: 1mm 0; }
        .consolidated-print-area table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .consolidated-print-area th,
        .consolidated-print-area td { border: 1px solid #222; padding: 0.35mm 0.55mm; font-size: 9.8px; line-height: 1.02; vertical-align: top; overflow-wrap: anywhere; }
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
        .student-group { page-break-inside: avoid; break-inside: avoid; }
        .student-start td { border-top-width: 1.5px; }
        .rollup td { font-weight: bold; background: #f6f6f6; }
        .nowrap { white-space: nowrap; }
        @media print {
            body { margin: 0; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .main-content, .consolidated-preview { margin: 0 !important; padding: 0 !important; background: #fff !important; }
            .breadcrumb, .separator-breadcrumb, .print-actions { display: none !important; }
            .sheet { margin: 0; break-after: page; page-break-after: always; }
            .sheet:last-child { break-after: auto; page-break-after: auto; }
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
                    $studentPages = collect($report['students'])->values()->chunk(4);
                @endphp
                @foreach($studentPages as $pageIndex => $studentsPage)
                    <main class="sheet">
                <div class="title">
                    <h2>Lokmanya Vidya Niketan</h2>
                    <h3>Consolidated Marksheet</h3>
                    <div class="exam-line">Exam Name : {{ $report['exam_title'] ?? 'Term - II (Annual) Examination' }}</div>
                    <div class="exam-line">Academic Session : {{ str_replace('_', '-', $report['session_year'] ?? '') }}</div>
                </div>

                <div class="meta">
                    <div>Class-Section : {{ $report['class']->class_name }}{{ $report['class']->section_name ? ' - '.$report['class']->section_name : '' }}</div>
                    <div>Class Teacher : {{ $report['class_teacher'] }}</div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th class="sr" rowspan="2">Sr.<br>No.</th>
                            <th class="name" rowspan="2">Student Name</th>
                            <th class="scholar" rowspan="2">Scholar No<br>Attendance</th>
                            <th class="subject" rowspan="2">Subject</th>
                            <th colspan="7">Term - I</th>
                            <th colspan="7">Term - II</th>
                            <th class="percent" rowspan="2">%</th>
                            <th class="division" rowspan="2">Division</th>
                            <th class="result" rowspan="2">Result</th>
                        </tr>
                        <tr>
                            <th class="tiny">PT<br>(5)</th>
                            <th class="tiny">MAS<br>(5)</th>
                            <th class="tiny">PF<br>(5)</th>
                            <th class="tiny">SEA<br>(5)</th>
                            <th class="tiny">HY<br>(80)</th>
                            <th class="total">Total<br>(100)</th>
                            <th class="grade">Grade</th>
                            <th class="tiny">PT<br>(5)</th>
                            <th class="tiny">MAS<br>(5)</th>
                            <th class="tiny">PF<br>(5)</th>
                            <th class="tiny">SE<br>(5)</th>
                            <th class="tiny">ANN<br>(80)</th>
                            <th class="total">Total<br>(100)</th>
                            <th class="grade">Grade</th>
                        </tr>
                    </thead>
                    @foreach($studentsPage->values() as $studentIndex => $studentRow)
                        @php
                            $serialNo = ($pageIndex * 4) + $studentIndex + 1;
                            $subjectRows = collect($studentRow['subjects']);
                            $existingSubjects = $subjectRows->pluck('subject')->map(fn ($subject) => strtolower(preg_replace('/\s+/', ' ', trim((string) $subject))))->all();
                            $classOneSubjects = $isClassOne
                                ? collect(['Computer', 'Sanskrit'])
                                    ->reject(fn ($subject) => $hasSubject($existingSubjects, $subject))
                                    ->map(fn ($subject) => [
                                        'subject' => $subject,
                                        'term_1' => ['pt' => '', 'mas' => '', 'pf' => '', 'sea' => '', 'theory' => '', 'total' => '', 'grade' => ''],
                                        'term_2' => ['pt' => '', 'mas' => '', 'pf' => '', 'sea' => '', 'theory' => '', 'total' => '', 'grade' => ''],
                                    ])
                                    ->values()
                                : collect();
                            $displaySubjects = $subjectRows->merge($classOneSubjects)->values();
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
                                            {{ $studentRow['attendance'] }}
                                        </td>
                                    @endif
                                    <td>{{ $subject['subject'] }}</td>
                                    <td class="center">{{ is_numeric($subject['term_1']['pt']) ? $formatMark($subject['term_1']['pt']) : '' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_1']['mas']) ? $formatMark($subject['term_1']['mas']) : '' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_1']['pf']) ? $formatMark($subject['term_1']['pf']) : '' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_1']['sea']) ? $formatMark($subject['term_1']['sea']) : '' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_1']['theory']) ? $formatMark($subject['term_1']['theory']) : '' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_1']['total']) ? $formatMark($subject['term_1']['total']) : '' }}</td>
                                    <td class="center">{{ $subject['term_1']['grade'] }}</td>
                                    <td class="center">{{ is_numeric($subject['term_2']['pt']) ? $formatMark($subject['term_2']['pt']) : '' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_2']['mas']) ? $formatMark($subject['term_2']['mas']) : '' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_2']['pf']) ? $formatMark($subject['term_2']['pf']) : '' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_2']['sea']) ? $formatMark($subject['term_2']['sea']) : '' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_2']['theory']) ? $formatMark($subject['term_2']['theory']) : '' }}</td>
                                    <td class="center">{{ is_numeric($subject['term_2']['total']) ? $formatMark($subject['term_2']['total']) : '' }}</td>
                                    <td class="center">{{ $subject['term_2']['grade'] }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @empty
                                <tr class="student-start">
                                    <td class="center">{{ $serialNo }}</td>
                                    <td>{{ $studentRow['student']->student_name }}</td>
                                    <td class="center">
                                        {{ $studentRow['student']->scholar_no }}<br>
                                        {{ $studentRow['attendance'] }}
                                    </td>
                                    <td colspan="15" class="center">No assigned subjects found</td>
                                    <td class="center">{{ $studentRow['percentage'] !== null ? $formatPercentage($studentRow['percentage']) : '' }}</td>
                                    <td class="center">{{ $studentRow['division'] }}</td>
                                    <td class="center">{{ $studentRow['result'] }}</td>
                                </tr>
                            @endforelse
                            @if($subjectRows->count() > 0)
                                <tr class="rollup">
                                    <td colspan="5"></td>
                                    <td class="center">{{ $formatMark($subjectRows->sum(fn ($subject) => $subject['term_1']['theory'])) }}</td>
                                    <td class="center">{{ $formatMark($subjectRows->sum(fn ($subject) => $subject['term_1']['total'])) }}</td>
                                    <td></td>
                                    <td colspan="4"></td>
                                    <td class="center">{{ $formatMark($subjectRows->sum(fn ($subject) => $subject['term_2']['theory'])) }}</td>
                                    <td class="center">{{ $formatMark($subjectRows->sum(fn ($subject) => $subject['term_2']['total'])) }}</td>
                                    <td></td>
                                    <td class="center">{{ $studentRow['percentage'] !== null ? $formatPercentage($studentRow['percentage']) : '' }}</td>
                                    <td class="center">{{ $studentRow['division'] }}</td>
                                    <td class="center">{{ $studentRow['result'] }}</td>
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
