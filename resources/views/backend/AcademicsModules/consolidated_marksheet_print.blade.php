<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Consolidated Marksheet</title>
    <style>
        @page { size: A4 landscape; margin: 7mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; font-family: "Times New Roman", Times, serif; background: #fff; }
        .print-actions { padding: 10px; text-align: right; font-family: Arial, sans-serif; }
        .print-actions button { border: 0; background: #1b5fbf; color: #fff; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
        .sheet { width: 283mm; min-height: 196mm; margin: 0 auto; padding: 6mm 5mm; page-break-after: always; break-after: page; overflow: hidden; }
        .sheet:last-child { page-break-after: auto; break-after: auto; }
        .title { text-align: center; line-height: 1.2; margin-bottom: 2mm; }
        .title h2 { margin: 0; font-size: 16px; font-weight: bold; }
        .title h3 { margin: 1mm 0 0; font-size: 15px; font-weight: bold; }
        .title .exam-line { margin-top: 1mm; font-size: 14px; font-weight: bold; }
        .meta { display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: bold; margin: 2mm 0; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #222; padding: 0.65mm 0.8mm; font-size: 10.4px; line-height: 1.08; vertical-align: top; overflow-wrap: anywhere; }
        th { text-align: center; font-weight: bold; }
        td.center { text-align: center; }
        td.right { text-align: right; }
        .sr { width: 7mm; }
        .name { width: 28mm; }
        .scholar { width: 16mm; }
        .subject { width: 30mm; }
        .tiny { width: 9mm; }
        .total { width: 11mm; }
        .grade { width: 10mm; }
        .percent { width: 11mm; }
        .division { width: 12mm; }
        .result { width: 12mm; }
        .student-start td { border-top-width: 1.5px; }
        .rollup td { font-weight: bold; background: #f6f6f6; }
        .nowrap { white-space: nowrap; }
        @media print {
            .print-actions { display: none; }
            .sheet { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button onclick="window.print()">Print</button>
    </div>

    @forelse($reports as $report)
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
                <tbody>
                    @foreach($report['students'] as $studentIndex => $studentRow)
                        @forelse($studentRow['subjects'] as $subjectIndex => $subject)
                            <tr class="{{ $subjectIndex === 0 ? 'student-start' : '' }}">
                                @if($subjectIndex === 0)
                                    <td class="center" rowspan="{{ count($studentRow['subjects']) + 1 }}">{{ $studentIndex + 1 }}</td>
                                    <td rowspan="{{ count($studentRow['subjects']) + 1 }}">{{ $studentRow['student']->student_name }}</td>
                                    <td class="center" rowspan="{{ count($studentRow['subjects']) + 1 }}">
                                        {{ $studentRow['student']->scholar_no }}<br>
                                        {{ $studentRow['attendance'] }}
                                    </td>
                                @endif
                                <td>{{ $subject['subject'] }}</td>
                                <td class="center">{{ number_format($subject['term_1']['pt'], 2) }}</td>
                                <td class="center">{{ number_format($subject['term_1']['mas'], 2) }}</td>
                                <td class="center">{{ number_format($subject['term_1']['pf'], 2) }}</td>
                                <td class="center">{{ number_format($subject['term_1']['sea'], 2) }}</td>
                                <td class="center">{{ number_format($subject['term_1']['theory'], 2) }}</td>
                                <td class="center">{{ number_format($subject['term_1']['total'], 2) }}</td>
                                <td class="center">{{ $subject['term_1']['grade'] }}</td>
                                <td class="center">{{ number_format($subject['term_2']['pt'], 2) }}</td>
                                <td class="center">{{ number_format($subject['term_2']['mas'], 2) }}</td>
                                <td class="center">{{ number_format($subject['term_2']['pf'], 2) }}</td>
                                <td class="center">{{ number_format($subject['term_2']['sea'], 2) }}</td>
                                <td class="center">{{ number_format($subject['term_2']['theory'], 2) }}</td>
                                <td class="center">{{ number_format($subject['term_2']['total'], 2) }}</td>
                                <td class="center">{{ $subject['term_2']['grade'] }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @empty
                            <tr class="student-start">
                                <td class="center">{{ $studentIndex + 1 }}</td>
                                <td>{{ $studentRow['student']->student_name }}</td>
                                <td class="center">
                                    {{ $studentRow['student']->scholar_no }}<br>
                                    {{ $studentRow['attendance'] }}
                                </td>
                                <td colspan="15" class="center">No assigned subjects found</td>
                                <td class="center">{{ $studentRow['percentage'] !== null ? number_format($studentRow['percentage'], 2) : '' }}</td>
                                <td class="center">{{ $studentRow['division'] }}</td>
                                <td class="center">{{ $studentRow['result'] }}</td>
                            </tr>
                        @endforelse
                        @if(count($studentRow['subjects']) > 0)
                        <tr class="rollup">
                            <td colspan="6"></td>
                            <td class="center">{{ number_format(collect($studentRow['subjects'])->sum(fn ($subject) => $subject['term_1']['total']), 2) }}</td>
                            <td></td>
                            <td colspan="5"></td>
                            <td class="center">{{ number_format(collect($studentRow['subjects'])->sum(fn ($subject) => $subject['term_2']['total']), 2) }}</td>
                            <td></td>
                            <td class="center">{{ $studentRow['percentage'] !== null ? number_format($studentRow['percentage'], 2) : '' }}</td>
                            <td class="center">{{ $studentRow['division'] }}</td>
                            <td class="center">{{ $studentRow['result'] }}</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </main>
    @empty
        <main class="sheet">
            <div class="title">
                <h2>No consolidated marks found.</h2>
            </div>
        </main>
    @endforelse
</body>
</html>
