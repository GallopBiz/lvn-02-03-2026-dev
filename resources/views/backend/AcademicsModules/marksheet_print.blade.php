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
        padding: 30mm 0 18mm;
        position: relative;
        overflow: hidden;
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
        grid-template-columns: 1fr 32mm 34mm 32mm;
        gap: 5mm;
        align-items: center;
        font-size: 13px;
        margin-top: 3mm;
    }

    .remarks {
        font-weight: bold;
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
            $hasSubject = function ($subjects, $subject) {
            $needle = strtolower(preg_replace('/\s+/', ' ', trim((string) $subject)));
            return collect($subjects)->contains(fn ($item) => str_contains($item, $needle) || str_contains($needle, $item));
            };
            $isPrimaryAdditionalSubject = function ($subject) {
            $value = strtolower(preg_replace('/\s+/', ' ', trim((string) $subject)));

            return str_contains($value, 'computer') || str_contains($value, 'sanskrit');
            };
            $classNumber = function ($className) {
            $value = strtolower(preg_replace('/\s+/', ' ', trim((string) $className)));
            if (preg_match('/(^|\D)([1-5])(\D|$)/', $value, $match)) {
            return (int) $match[2];
            }

            $romanMap = ['i' => 1, 'ii' => 2, 'iii' => 3, 'iv' => 4, 'v' => 5];
            foreach ($romanMap as $roman => $number) {
            if (preg_match('/(^|\W)' . preg_quote($roman, '/') . '(\W|$)/', $value)) {
            return $number;
            }
            }

            $wordMap = ['one' => 1, 'first' => 1, 'two' => 2, 'second' => 2, 'three' => 3, 'third' => 3, 'four' => 4, 'fourth' => 4, 'five' => 5, 'fifth' => 5];
            foreach ($wordMap as $word => $number) {
            if (preg_match('/(^|\W)' . preg_quote($word, '/') . '(\W|$)/', $value)) {
            return $number;
            }
            }

            return null;
            };
            @endphp
            @foreach($sheets as $sheet)
            @php
            $template = $sheet['template'];
            $printData = $sheet['printData'];
            $className = (string) optional($printData['class'])->class_name;
            $isPrimaryClass = in_array($classNumber($className), [1, 2, 3, 4, 5], true);
            $allRows = collect($printData['rows']);
            $primarySubjectRows = $isPrimaryClass ? $allRows->filter(fn ($row) => $isPrimaryAdditionalSubject($row['subject'] ?? ''))->values() : collect();
            $scholasticRows = $isPrimaryClass ? $allRows->reject(fn ($row) => $isPrimaryAdditionalSubject($row['subject'] ?? ''))->values() : $allRows;
            $primaryAdditionalSubjects = $isPrimaryClass
            ? collect(['Computer Science', 'Sanskrit'])->map(function ($subject) use ($primarySubjectRows, $hasSubject, $printData) {
            $matchedRow = $primarySubjectRows->first(fn ($row) => $hasSubject([$row['subject'] ?? ''], $subject));

            return $matchedRow ?: [
            'subject' => $subject,
            'terms' => collect($printData['layout']['scholastic_terms'] ?? [])->map(fn ($term) => [
            'cells' => array_fill(0, count($term['columns'] ?? []), ''),
            'grade' => '',
            ])->all(),
            'grade' => $printData['grading']['co_scholastic_default_grade'] ?? 'A',
            'grand_total' => '',
            ];
            })
            : collect();
            @endphp
            <main class="sheet">
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
                            <td>Class</td>
                            <td>: {{ optional($printData['class'])->class_name }}</td>
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
                            <th>{{ $column['label'] ?? '' }}</th>
                            @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbodnpm run devy>
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
                        @foreach($primaryAdditionalSubjects as $subject)
                        @php
                        $subjectName = $subject['subject'] ?? '';
                        $normalizedSubject = strtolower(preg_replace('/\s+/', ' ', trim($subjectName)));
                        @endphp
                        <tr>
                            <td class="subject">{{ str_contains($normalizedSubject, 'computer') ? 'Computer Science' : $subjectName }}</td>
                            @foreach($subject['terms'] ?? [] as $term)
                            @foreach($term['cells'] ?? [] as $cell)
                            <td>{{ $cell }}</td>
                            @endforeach
                            @endforeach
                            <td>{{ $subject['grade'] ?? '' }}</td>
                            <td>{{ is_numeric($subject['grand_total'] ?? null) ? $formatMark($subject['grand_total']) : '' }}</td>
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
                    <div>Result : {{ $printData['result'] }}</div>
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