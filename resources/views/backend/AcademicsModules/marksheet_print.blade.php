@extends('backend.layouts.main')

@section('main-container')
<style id="marksheet-print-styles">
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
        color: #2f2f2f;
        font-family: var(--marksheet-font);
        background: #fff;
    }

    .sheet {
        width: 281mm;
        height: 194mm;
        margin: 0 auto;
        padding: 30mm 0 34mm;
        position: relative;
        overflow: hidden;
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

    .date {
        text-align: right;
        font-size: 11px;
    }

    .cbse-logo {
        position: absolute;
        top: 8mm;
        right: 1mm;
        width: 18mm;
        height: 18mm;
        object-fit: contain;
    }

    .title {
        text-align: center;
        font-weight: bold;
        font-size: 15px;
        line-height: 1.35;
        margin: 1mm 0 2mm;
    }

    .student-box {
        border: 1px solid #333;
        display: grid;
        grid-template-columns: 1fr 1fr;
        padding: 2mm 4mm;
        font-size: 13px;
        line-height: 1.35;
    }

    .student-box table {
        width: 100%;
        border-collapse: collapse;
    }

    .student-box td:first-child {
        width: 38mm;
        font-weight: bold;
    }

    table.marks {
        width: 100%;
        max-width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 11.5px;
        line-height: 1.08;
    }

    .marks th,
    .marks td {
        border: 1px solid #333;
        padding: 0.8mm 1mm;
        text-align: center;
        vertical-align: middle;
        overflow-wrap: anywhere;
    }

    .marks .scholastic-area,
    .marks .subject {
        width: 35mm;
    }

    .marks .subject {
        text-align: left;
    }

    .marks .section {
        font-weight: bold;
        background: #f5f5f5;
    }

    .marks .total-label {
        text-align: center;
        font-weight: bold;
    }

    .co-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }

    .co-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 12px;
    }

    .co-table th,
    .co-table td {
        border: 1px solid #333;
        padding: 1.1mm 2mm;
        overflow-wrap: anywhere;
    }

    .co-table td:last-child,
    .co-table th:last-child {
        width: 18mm;
        text-align: center;
    }

    .summary {
        display: grid;
        grid-template-columns: 1fr 34mm 42mm;
        gap: 5mm;
        align-items: center;
        font-size: 13px;
        margin-top: 3mm;
        margin-bottom: 2mm;
    }

    .summary div:nth-child(2),
    .summary div:nth-child(3) {
        text-align: right;
    }

    .remarks {
        font-weight: bold;
    }

    .footer-area {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        display: grid;
        grid-template-columns: 58mm 132mm 58mm;
        align-items: end;
        justify-content: space-between;
        gap: 0;
        font-size: 12px;
        background: #fff;
    }

    .signature {
        min-height: 16mm;
        display: flex;
        align-items: end;
        justify-content: center;
        font-weight: bold;
        text-align: center;
    }

    .signature:first-child {
        justify-content: flex-start;
        text-align: left;
    }

    .signature:last-child {
        justify-content: flex-end;
        text-align: right;
    }

    .abbr-box {
        border: 1px solid #333;
        padding: 2mm 3mm;
        text-align: center;
        font-weight: bold;
        line-height: 1.25;
    }

    @media print {
        body {
            margin: 0;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .main-content,
        .marksheet-preview {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        .breadcrumb,
        .separator-breadcrumb,
        .print-actions {
            display: none !important;
        }

        .sheet {
            margin: 0;
        }
    }
</style>

<div class="main-content pt-4">
    <div class="breadcrumb">
        <h2>{{ $template->name }} Print</h2>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="marksheet-preview">
        <div id="marksheetPrintArea" class="marksheet-print-area">
            @php
            $sheets = $sheets ?? [['template' => $template, 'printData' => $printData]];
            $formatMark = fn ($value) => number_format(round((float) $value, 0, PHP_ROUND_HALF_UP), 2, '.', '');
            $formatPercentage = fn ($value) => number_format((float) $value, 2, '.', '');
            $sumTermColumn = function ($rows, $termIndex, $cellIndex) {
            return collect($rows)->sum(function ($row) use ($termIndex, $cellIndex) {
            $value = $row['terms'][$termIndex]['cells'][$cellIndex] ?? null;
            return is_numeric($value) ? (float) $value : 0;
            });
            };
            @endphp
            @foreach($sheets as $sheet)
            @php
            $template = $sheet['template'];
            $printData = $sheet['printData'];
            $allRows = collect($printData['rows']);
            $specialSubjectRows = $allRows->filter(fn ($row) => !empty($row['special_subject_formatting']))->values();
            $scholasticRows = $allRows->reject(fn ($row) => !empty($row['special_subject_formatting']))->values();
            @endphp
            <main class="sheet">
                <img class="cbse-logo" src="{{ url('images/cbse-logo.png') }}" alt="CBSE">
                <div class="date">Date : {{ $printData['date'] }}</div>

                <div class="title">
                    <div>Academic Session : {{ str_replace('_', '-', $printData['session_year'] ?? '') }}</div>
                    <div>{{ $printData['layout']['title'] ?? '' }}</div>
                </div>

                <section class="student-box">
                    <table>
                        <tr>
                            <td>Student's Name</td>
                            <td>: {{ $printData['student_meta']['student_name'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Mother's Name</td>
                            <td>: {{ $printData['student_meta']['mother_name'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Father's Name</td>
                            <td>: {{ $printData['student_meta']['father_name'] ?? '' }}</td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td>Scholar No</td>
                            <td>: {{ $printData['student_meta']['scholar_no'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Roll No</td>
                            <td>: {{ $printData['roll_no'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Date of Birth</td>
                            <td>: {{ $printData['student_meta']['date_of_birth'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Class-Section</td>
                            <td>: {{ $printData['class_section'] ?? optional($printData['class'])->class_name }}</td>
                        </tr>
                    </table>
                </section>

                <table class="marks">
                    <thead>
                        <tr>
                            <th class="section scholastic-area">Scholastic Areas</th>
                            @foreach($printData['layout']['scholastic_terms'] as $term)
                            <th class="section" colspan="{{ count($term['columns'] ?? []) }}">{{ $term['label'] ?? '' }}</th>
                            @endforeach
                            <th class="section" rowspan="2">Grade</th>
                            <th class="section" rowspan="2">Grand<br>Total</th>
                        </tr>
                        <tr>
                            <th class="subject">Subject Name</th>
                            @foreach($printData['layout']['scholastic_terms'] as $term)
                            @foreach($term['columns'] ?? [] as $column)
                            @php $columnLabel = (string) ($column['label'] ?? ''); @endphp
                            <th>
                                {{ $columnLabel }}
                                @if(!empty($column['max']) && !str_contains($columnLabel, '('))
                                <br>({{ $column['max'] }})
                                @endif
                            </th>
                            @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($scholasticRows as $row)
                        @php
                        $subjectName = $row['subject'] ?? '';
                        $normalizedSubject = strtolower(preg_replace('/\s+/', ' ', trim($subjectName)));
                        @endphp
                        <tr>
                            <td class="subject">{{ in_array($normalizedSubject, ['environmental studies', 'environmental study', 'environment studies', 'environment study'], true) ? 'EVS' : $subjectName }}</td>
                            @foreach($row['terms'] as $term)
                            @foreach($term['cells'] as $cell)
                            <td>{{ $cell }}</td>
                            @endforeach
                            @endforeach
                            <td>{{ $row['grade'] }}</td>
                            <td>{{ $formatMark($row['grand_total']) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="20">No subjects configured for this class.</td>
                        </tr>
                        @endforelse
                        <tr>
                            <td class="total-label">Total</td>
                            @foreach($printData['layout']['scholastic_terms'] as $termIndex => $term)
                            @foreach($term['columns'] ?? [] as $columnIndex => $column)
                            @php
                            $source = strtolower((string) ($column['source'] ?? ''));
                            @endphp
                            <td>{{ ($source !== 'mark_theory' && $source !== 'theory' && !empty($column['counts_in_total'])) ? $formatMark($sumTermColumn($scholasticRows, $termIndex, $columnIndex)) : '' }}</td>
                            @endforeach
                            @endforeach
                            <td></td>
                            <td></td>
                        </tr>
                        @if($specialSubjectRows->isNotEmpty())
                        <tr>
                            <td></td>
                            @foreach($printData['layout']['scholastic_terms'] ?? [] as $termIndex => $term)
                            @php
                            $columnCount = count($term['columns'] ?? []);
                            $isLastTerm = $termIndex === count($printData['layout']['scholastic_terms'] ?? []) - 1;
                            @endphp
                            @if($isLastTerm)
                            <td colspan="{{ $columnCount }}"></td>
                            @else
                            @if($columnCount > 1)
                            <td colspan="{{ $columnCount - 1 }}"></td>
                            @endif
                            <td class="total-label">Grade</td>
                            @endif
                            @endforeach
                            <td></td>
                            <td class="total-label">Grade</td>
                        </tr>
                        @endif
                        @foreach($specialSubjectRows as $subject)
                        @php
                        $subjectName = $subject['subject'] ?? '';
                        $normalizedSubject = strtolower(preg_replace('/\s+/', ' ', trim($subjectName)));
                        $displaySubjectName = in_array($normalizedSubject, ['environmental studies', 'environmental study', 'environment studies', 'environment study'], true) ? 'EVS' : $subjectName;
                        @endphp
                        <tr>
                            <td class="subject">{{ $displaySubjectName }}</td>
                            @foreach($printData['layout']['scholastic_terms'] ?? [] as $termIndex => $term)
                            @php
                            $columnCount = count($term['columns'] ?? []);
                            $isLastTerm = $termIndex === count($printData['layout']['scholastic_terms'] ?? []) - 1;
                            @endphp
                            @if($isLastTerm)
                            <td class="subject" colspan="{{ $columnCount }}">{{ $displaySubjectName }}</td>
                            @else
                            @if($columnCount > 1)
                            <td class="subject" colspan="{{ $columnCount - 1 }}"></td>
                            @endif
                            <td>{{ $subject['terms'][$termIndex]['grade'] ?? '' }}</td>
                            @endif
                            @endforeach
                            <td></td>
                            <td>{{ $subject['terms'][count($printData['layout']['scholastic_terms'] ?? []) - 1]['grade'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <section class="co-grid">
                    @foreach($printData['layout']['co_scholastic_sections'] ?? [] as $section)
                    <table class="co-table">
                        <tr>
                            <th>{{ $section['title'] ?? 'Co-Scholastic Area' }}</th>
                            <th>Grade</th>
                        </tr>
                        @foreach($section['items'] ?? [] as $item)
                        <tr>
                            <td><strong>{{ $item }}</strong></td>
                            <td>{{ $printData['grading']['co_scholastic_default_grade'] ?? 'A' }}</td>
                        </tr>
                        @endforeach
                    </table>
                    @endforeach
                </section>

                <section class="summary">
                    <div class="remarks">Class Teacher's remarks : {{ $printData['remarks'] }}</div>
                    <div>Percentage : {{ $printData['percentage'] !== null ? $formatPercentage($printData['percentage']) : '' }}</div>
                    <div>Attendance : {{ $printData['attendance'] }}</div>
                </section>

                <section class="footer-area">
                    <div class="signature">Signature of Class Teacher</div>
                    <div class="abbr-box">
                        <div>PT-Periodic Test, MAS-Multiple Assessment Strategy,</div>
                        <div>NB-Notebook, SEA-Subject Enrichment Activity</div>
                    </div>
                    <div class="signature">Signature of Principal</div>
                </section>
            </main>
            @endforeach
        </div>
    </div>

    <div class="print-actions d-flex justify-content-center gap-2 mt-3 mb-3">
        <button class="btn btn-sm btn-success" onclick="printMarksheet()">Print Marksheet</button>
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">Back</a>
    </div>
</div>

<script>
    function printMarksheet() {
        const content = document.getElementById('marksheetPrintArea').cloneNode(true);
        const styles = document.getElementById('marksheet-print-styles').innerHTML;
        const printWindow = window.open('', '_blank', 'width=1200,height=800');

        printWindow.document.open();
        printWindow.document.write(`
        <html>
        <head>
            <title>Print Marksheet</title>
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
