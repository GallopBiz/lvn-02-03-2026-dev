<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Analysis Print</title>
    <style id="result-analysis-print-styles">
        @page {
            size: A4 landscape;
            margin: 6mm 8mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            color: #111;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .print-actions {
            text-align: center;
            padding: 10px;
            background: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            font-family: Arial, sans-serif;
        }
        .print-actions button {
            background-color: #663399;
            color: #fff;
            border: 0;
            padding: 7px 18px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 8px;
        }
        .print-actions a {
            color: #475569;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            padding: 6px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #fff;
        }
        .report-page {
            width: 100%;
            max-width: 285mm;
            margin: 0 auto 12mm auto;
            page-break-after: always;
            break-after: page;
        }
        .report-page:last-child {
            page-break-after: auto;
            break-after: auto;
        }
        .header-title {
            text-align: center;
            line-height: 1.15;
            margin-bottom: 3mm;
        }
        .header-title h2 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-title h3 {
            margin: 1.5mm 0 0.5mm 0;
            font-size: 15px;
            font-weight: bold;
        }
        .header-title .sub-line {
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }
        .meta-strip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            font-weight: bold;
            border-top: 1px solid #111;
            border-bottom: 1px solid #111;
            padding: 1.5mm 1mm;
            margin-bottom: 3mm;
        }
        .section-heading {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin: 3mm 0 1.5mm 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 4mm;
        }
        th, td {
            border: 1px solid #222;
            padding: 1.2mm 1.5mm;
            font-size: 11px;
            vertical-align: middle;
            text-align: center;
            overflow-wrap: break-word;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-danger { color: #b91c1c !important; }
        .bg-light-col { background-color: #f8fafc; }
        .bg-overall-col { background-color: #f1f5f9; font-weight: bold; }
        .metric-label-cell {
            text-align: left;
            font-weight: bold;
            padding-left: 2mm;
            background-color: #f8fafc;
        }
        .highlight-row td {
            font-weight: bold;
        }
        .signatures-area {
            display: flex;
            justify-content: space-between;
            margin-top: 8mm;
            padding: 0 4mm;
            font-size: 11.5px;
            font-weight: bold;
        }
        .signatures-area div {
            text-align: center;
            border-top: 1px solid #111;
            padding-top: 1.5mm;
            min-width: 45mm;
        }
        @media print {
            .print-actions { display: none !important; }
            body { margin: 0; background: #fff; }
            .report-page { margin: 0 auto; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button onclick="window.print()">Print Report (Landscape)</button>
        <a href="javascript:window.close()">Close Window</a>
    </div>

    @forelse($reports as $report)
        <div class="report-page">
            {{-- Title --}}
            <div class="header-title">
                <h2>Lokmanya Vidya Niketan</h2>
                <h3>Result Analysis &mdash; Class {{ \App\Academic\AcademicHelper::toRoman($report['class']->class_name) ?: $report['class']->class_name }} - {{ $report['class']->section_name }}</h3>
                <div class="sub-line">
                    Exam: {{ $report['exam_title'] }} &nbsp;|&nbsp; Academic Session: {{ str_replace('_', '-', $report['session_year']) }}
                </div>
            </div>

            {{-- Meta Strip --}}
            <div class="meta-strip">
                <div>Class-Section : {{ \App\Academic\AcademicHelper::formatClassSection($report['class']->class_name, $report['class']->section_name) }}</div>
                <div>Class Teacher : {{ $report['class_teacher'] ?: 'Not Assigned' }}</div>
                <div>Total On Roll : {{ $report['total_on_roll'] }}</div>
            </div>

            {{-- Result Analysis Summary Table (The Reference Image Table) --}}
            <div class="section-heading">Subject-wise &amp; Overall Result Analysis Summary</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 18%;" class="text-left font-bold">METRIC</th>
                        @foreach($report['subject_columns'] as $colKey => $colDef)
                            <th class="font-bold text-center">{{ $colDef['label'] }}</th>
                        @endforeach
                        <th style="width: 14%;" class="font-bold text-center bg-overall-col">OVERALL</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $metricRows = [
                            'TOTAL ON ROLL' => 'font-normal',
                            'APPEARED' => 'font-normal',
                            'PASSED' => 'font-normal',
                            'FAIL/ COMPT.' => 'font-normal text-danger',
                            'PASS PERCENTAGE' => 'font-bold highlight-row',
                            'HIGHEST' => 'font-normal',
                            'LOWEST' => 'font-normal',
                            'CLASS AVERAGE' => 'font-bold highlight-row',
                            'ABOVE 90' => 'font-normal',
                            'ABOVE 75' => 'font-normal',
                            'I Div.' => 'font-normal',
                            'II Div.' => 'font-normal',
                            'III Div.' => 'font-normal',
                            'Below 33' => 'font-normal text-danger',
                        ];
                    @endphp

                    @foreach($metricRows as $metricKey => $style)
                        <tr class="{{ str_contains($style, 'highlight-row') ? 'highlight-row' : '' }}">
                            <td class="metric-label-cell">{{ $metricKey }}</td>
                            @foreach($report['subject_columns'] as $colKey => $colDef)
                                @php
                                    $val = $report['stats'][$metricKey][$colKey] ?? 0;
                                @endphp
                                <td class="{{ $style }}">{{ $val }}</td>
                            @endforeach
                            @php
                                $overallVal = $report['stats'][$metricKey]['OVERALL'] ?? 0;
                            @endphp
                            <td class="{{ $style }} bg-overall-col">{{ $overallVal }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Signatures Area --}}
            <div class="signatures-area">
                <div>Class Teacher</div>
                <div>Exam Incharge</div>
                <div>Principal</div>
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
            <h3>No Result Analysis Records Found to Print</h3>
            <p>Please check your filters and ensure examination marks exist for the selected class.</p>
        </div>
    @endforelse
</body>
</html>

