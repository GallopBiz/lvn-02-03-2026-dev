@extends('backend.layouts.main')

@section('main-container')
<style id="marksheet-print-styles">
    @page {
        size: A4 landscape;
        margin: 8mm 8mm 4mm 8mm;
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
        min-height: 194mm;
        height: auto;
        margin: 0 auto;
        padding: 42mm 0 2mm;
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
        right: 0mm;
        width: 21mm;
        text-align: right;
        line-height: 0;
        z-index: 10;
    }

    .top-right-logo img {
        width: 20mm;
        height: auto;
        max-height: 21mm;
        object-fit: contain;
        display: inline-block;
    }

    .date {
        text-align: right;
        font-size: 13px;
    }

    .title {
        text-align: center;
        font-weight: bold;
        font-size: 15px;
        line-height: 1.25;
        margin: 0.5mm 0 1.5mm;
    }

    .title .session-text {
        font-size: 18px;
    }

    .student-box {
        border: 1px solid #333;
        display: grid;
        grid-template-columns: 1fr 1fr;
        padding: 1mm 3.5mm;
        font-size: 13px;
        line-height: 1.2;
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
        font-size: 12px;
        line-height: 1.1;
    }

    .marks th,
    .marks td {
        border: 1px solid #333;
        padding: 0.5mm 1mm;
        text-align: center;
        vertical-align: middle;
        overflow-wrap: anywhere;
    }

    .marks .scholastic-area,
    .marks .subject {
        width: 35mm;
        text-align: left;
        padding-left: 2mm;
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
        margin-top: 1mm;
    }

    .co-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 12px;
        line-height: 1.1;
    }

    .co-table th,
    .co-table td {
        border: 1px solid #333;
        padding: 0.5mm 1mm;
        overflow-wrap: anywhere;
        vertical-align: middle;
    }

    .co-table th:first-child,
    .co-table td:first-child,
    .co-table .co-title {
        text-align: left;
        padding-left: 2mm;
    }

    .co-table td:last-child,
    .co-table th:last-child {
        width: 18mm;
        text-align: center;
    }

    .summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        margin-top: 1.5mm;
    }

    .remarks {
        font-weight: bold;
    }

    .summary-stats {
        display: flex;
        align-items: center;
        gap: 12mm;
        white-space: nowrap;
    }

    .summary-stats div {
        font-size: 13px;
    }

    .footer-area {
        margin-top: 2.5mm;
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        width: 100%;
    }

    .signature {
        font-size: 12.5px;
        font-weight: bold;
        line-height: 1.25;
    }

    .signature:first-child {
        text-align: left;
    }

    .signature:last-child {
        text-align: right;
    }

    .abbr-box {
        border: 1px solid #333;
        padding: 1mm 3.5mm;
        text-align: center;
        font-size: 11px;
        line-height: 1.25;
        font-weight: bold;
        margin: 0 4mm;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm 8mm 4mm 8mm;
        }

        body {
            margin: 0;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .main-header,
        .header-menu,
        .sidebar-left,
        .side-content-wrap,
        .app-footer {
            display: none !important;
            visibility: hidden !important;
        }

        .main-content,
        .marksheet-preview {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            overflow: visible !important;
        }

        .breadcrumb,
        .separator-breadcrumb,
        .print-actions {
            display: none !important;
        }

        .marksheet-print-area {
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
            padding: 42mm 0 2mm !important;
            overflow: visible !important;
            box-shadow: none !important;
            page-break-inside: avoid !important;
        }
    }
</style>

@php
    $previousUrl = url()->previous();
    $currentUrl = url()->current();
    $backUrl = route('marksheet');
    if ($previousUrl && $previousUrl !== $currentUrl && !str_contains($previousUrl, 'login') && !str_contains($previousUrl, 'signin')) {
        $backUrl = $previousUrl;
    }
@endphp

<div class="main-content pt-3">
    <div class="breadcrumb d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div>
            <h2 class="mb-0 font-weight-bold" style="font-size: 20px;">{{ $template->name }} Print</h2>
            <span class="text-muted small">Total sheets: {{ count($sheets ?? [['template' => $template, 'printData' => $printData]]) }} (Landscape A4)</span>
        </div>
        <div class="print-actions d-flex align-items-center gap-2">
            <a href="{{ $backUrl }}" class="btn btn-sm btn-secondary d-inline-flex align-items-center" onclick="handleBack(event)">
                <i class="fa fa-arrow-left mr-1"></i> Back
            </a>
            <button class="btn btn-sm btn-success d-inline-flex align-items-center" type="button" onclick="printMarksheet()">
                <i class="fa fa-print mr-1"></i> Print Marksheet
            </button>
        </div>
    </div>
    <div class="separator-breadcrumb border-top mb-3"></div>

    <div class="marksheet-preview">
        <div id="marksheetPrintArea" class="marksheet-print-area">
            @php
            $sheets = $sheets ?? [['template' => $template, 'printData' => $printData]];
            $formatMark = function ($value) {
                if (!is_numeric($value) || $value === '') {
                    return '';
                }
                $val = (float) $value;
                if (abs($val - round($val)) < 0.005) {
                    return (string) (int) round($val);
                }
                return number_format($val, 2, '.', '');
            };
            $formatPercentage = fn ($value) => (is_numeric($value) && $value !== '') ? number_format((float) $value, 2, '.', '') : '';
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
            $isSkillOrNonAcademic = function ($row) {
                if (!empty($row['is_non_academic']) || !empty($row['is_discipline_subject']) || !empty($row['is_skilled'])) {
                    return true;
                }
                $sub = strtolower(trim((string) ($row['subject'] ?? '')));
                return str_contains($sub, 'work education') 
                    || str_contains($sub, 'artificial intelligence') 
                    || str_contains($sub, 'marketing') 
                    || str_contains($sub, 'tourism') 
                    || str_contains($sub, 'food')
                    || str_contains($sub, 'office procedures');
            };
            $isSpecialSubject = function ($row) use ($isPrimaryClass, $isPrimaryAdditionalSubject) {
                if (isset($row['special_subject_formatting'])) {
                    return !empty($row['special_subject_formatting']);
                }
                if ($isPrimaryClass && $isPrimaryAdditionalSubject($row['subject'] ?? '')) {
                    return true;
                }
                return false;
            };

            $scholasticRows = $allRows
                ->reject(fn ($row) => $isSpecialSubject($row))
                ->reject(fn ($row) => $isSkillOrNonAcademic($row))
                ->values();

            $specialSubjectRows = $allRows
                ->filter(fn ($row) => $isSpecialSubject($row) && !$isSkillOrNonAcademic($row))
                ->values();

            if ($isPrimaryClass) {
                $primaryAdditionalSubjects = collect(['Computer Science', 'Sanskrit'])->filter(function ($subject) use ($specialSubjectRows, $scholasticRows, $hasSubject, $printData) {
                    if ($scholasticRows->contains(fn ($row) => $hasSubject([$row['subject'] ?? ''], $subject))) {
                        return false;
                    }
                    if ($specialSubjectRows->contains(fn ($row) => $hasSubject([$row['subject'] ?? ''], $subject))) {
                        return true;
                    }
                    return !empty($printData['is_blank']);
                })->map(function ($subject) use ($specialSubjectRows, $hasSubject, $printData) {
                    $matchedRow = $specialSubjectRows->first(fn ($row) => $hasSubject([$row['subject'] ?? ''], $subject));

                    return $matchedRow ?: [
                        'subject' => $subject,
                        'terms' => collect($printData['layout']['scholastic_terms'] ?? [])->map(fn ($term) => [
                            'cells' => array_fill(0, count($term['columns'] ?? []), ''),
                            'grade' => '',
                        ])->all(),
                        'grade' => !empty($printData['is_blank']) ? '' : ($printData['grading']['co_scholastic_default_grade'] ?? 'A'),
                        'grand_total' => '',
                    ];
                });
                $otherSpecial = $specialSubjectRows->reject(fn ($row) => $hasSubject([$row['subject'] ?? ''], 'computer') || $hasSubject([$row['subject'] ?? ''], 'sanskrit'));
                $separateSubjectRows = $primaryAdditionalSubjects->concat($otherSpecial)->values();
            } else {
                $separateSubjectRows = $specialSubjectRows;
            }
            @endphp
            <main class="sheet">
                <div class="top-right-logo">
                    <img src="{{ asset('images/cbse-logo.png') }}" alt="CBSE Logo">
                </div>
                <div class="date">Date : {{ $printData['date'] }}</div>

                @php
                    $normClass = \App\Academic\AcademicHelper::normalizeClassIdentifier(optional($printData['class'] ?? null)->class_name ?? ($printData['class_section'] ?? ''));
                    $isClass10Or12 = in_array($normClass, ['10', '12'], true);

                    $rawTitle = $printData['layout']['title'] ?? '';
                    if (preg_match('/term\s*[-_]?\s*(ii|2)\s*(\(([^)]+)\))?\s*/i', $rawTitle) || str_contains(strtolower($rawTitle), 'annual') || str_contains(strtolower($rawTitle), 'pre board')) {
                        $displayTitle = $isClass10Or12 ? 'Pre Board Examination Report Card' : 'Annual Examination Report Card';
                    } else {
                        $displayTitle = $rawTitle;
                    }
                @endphp
                <div class="title">
                    <div class="session-text">Academic Session : {{ str_replace('_', '-', $printData['session_year'] ?? '') }}</div>
                    <div>{{ $displayTitle }}</div>
                </div>

                <section class="student-box">
                    <table>
                        <tr>
                            <td>Scholar No.</td>
                            <td>: {{ $printData['student_meta']['scholar_no'] ?? '' }}</td>
                        </tr>
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
                            <td>Roll No.</td>
                            <td>: {{ $printData['roll_no'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Class-Section</td>
                            <td>: {{ \App\Academic\AcademicHelper::formatClassSection($printData['class_section'] ?? trim((optional($printData['class'])->class_name ?? '') . (optional($printData['class'])->section_name ? ' - ' . optional($printData['class'])->section_name : ''))) }}</td>
                        </tr>
                        @php
                        $attendanceDisplay = $printData['attendance'] ?? '';
                        if (str_contains($attendanceDisplay, '%')) {
                            $attendanceDisplay = '';
                        }
                        @endphp
                        <tr>
                            <td>Attendance</td>
                            <td>: {{ $attendanceDisplay }}</td>
                        </tr>
                        <tr>
                            <td>Date Of Birth</td>
                            <td>: {{ $printData['student_meta']['date_of_birth'] ?? '' }}</td>
                        </tr>
                    </table>
                </section>

                <table class="marks">
                    <thead>
                        <tr>
                            <th class="section scholastic-area">Scholastic Areas</th>
                            @foreach($printData['layout']['scholastic_terms'] as $term)
                            @php
                                $termLabel = $term['label'] ?? '';
                                $tKey = strtolower((string) ($term['key'] ?? ''));
                                if ($tKey === 'term_1' || preg_match('/\bterm[\s_-]*(?:1|i)\b/i', $termLabel) || strcasecmp($termLabel, 'half yearly exam') === 0 || strcasecmp($termLabel, 'half yearly') === 0) {
                                    $displayHeading = 'Half Yearly Exam';
                                } elseif ($tKey === 'term_2' || preg_match('/\bterm[\s_-]*(?:2|ii)\b/i', $termLabel) || strcasecmp($termLabel, 'annual exam') === 0 || strcasecmp($termLabel, 'annual') === 0 || strcasecmp($termLabel, 'pre board') === 0) {
                                    $displayHeading = $isClass10Or12 ? 'Pre Board' : 'Annual Exam';
                                } else {
                                    $displayHeading = $termLabel;
                                }
                            @endphp
                            <th class="section" colspan="{{ count($term['columns'] ?? []) }}">{{ $displayHeading }}</th>
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
                            @php
                            $rowGrade = $row['grade'] ?? '';
                            if (!empty($row['terms']) && is_array($row['terms'])) {
                                $term2 = $row['terms'][1] ?? null;
                                if (!empty($term2['grade'])) {
                                    $rowGrade = $term2['grade'];
                                }
                            }
                            @endphp
                            <td>{{ $rowGrade }}</td>
                            <td>{{ !empty($row['is_non_academic']) ? '' : $formatMark($row['grand_total']) }}</td>
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
                            $colLabel = strtolower((string) ($column['label'] ?? ''));
                            $isTheory = ($source === 'mark_theory' || $source === 'theory' || str_contains($source, 'theory') || str_contains($colLabel, 'theory') || str_contains($colLabel, '(80)'));
                            $isMarksObtained = (!empty($column['counts_in_total']) || $source === 'final_total' || str_contains($colLabel, 'marks obtained') || str_contains($colLabel, '(100)'));
                            $sum = $sumTermColumn($scholasticRows, $termIndex, $columnIndex);
                            $shouldDisplay = ($isTheory || $isMarksObtained) && empty($printData['is_blank']) && $sum > 0;
                            @endphp
                            <td>{{ $shouldDisplay ? $formatMark($sum) : '' }}</td>
                            @endforeach
                            @endforeach
                            <td></td>
                            <td></td>
                        </tr>
                        @if($separateSubjectRows->isNotEmpty())
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
                        @foreach($separateSubjectRows as $subject)
                        @php
                        $subjectName = $subject['subject'] ?? '';
                        $normalizedSubject = strtolower(preg_replace('/\s+/', ' ', trim($subjectName)));
                        $displaySubjectName = in_array($normalizedSubject, ['environmental studies', 'environmental study', 'environment studies', 'environment study'], true) 
                            ? 'EVS' 
                            : (str_contains($normalizedSubject, 'computer') ? 'Computer Science' : $subjectName);

                        $t1Grade = $subject['terms'][0]['grade'] ?? '';
                        $t2Grade = $subject['terms'][1]['grade'] ?? '';
                        if (empty($t2Grade) && !empty($subject['grade'])) {
                            $t2Grade = $subject['grade'];
                        }
                        if (empty($t1Grade) && !empty($subject['grade'])) {
                            $t1Grade = $subject['grade'];
                        }
                        if (!empty($printData['is_blank'])) {
                            $t1Grade = '';
                            $t2Grade = '';
                        }
                        @endphp
                        <tr>
                            <td class="subject">{{ $displaySubjectName }}</td>
                            @foreach($printData['layout']['scholastic_terms'] ?? [] as $termIndex => $term)
                            @php
                            $columnCount = count($term['columns'] ?? []);
                            $isLastTerm = $termIndex === count($printData['layout']['scholastic_terms'] ?? []) - 1;
                            @endphp
                            @if($isLastTerm)
                            @php
                            $termSubjColspan = min(2, $columnCount);
                            $termRestColspan = $columnCount - $termSubjColspan;
                            @endphp
                            <td class="subject" colspan="{{ $termSubjColspan }}">{{ $displaySubjectName }}</td>
                            @if($termRestColspan > 0)
                            <td colspan="{{ $termRestColspan }}"></td>
                            @endif
                            @else
                            @if($columnCount > 1)
                            <td colspan="{{ $columnCount - 1 }}"></td>
                            @endif
                            <td>{{ $termIndex === 0 ? $t1Grade : ($subject['terms'][$termIndex]['grade'] ?? '') }}</td>
                            @endif
                            @endforeach
                            <td></td>
                            <td>{{ $t2Grade }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                </table>

                @php
                $hasCoScholasticSections = false;
                foreach ($printData['layout']['co_scholastic_sections'] ?? [] as $section) {
                    if (!empty($section['items']) && count($section['items']) > 0) {
                        $hasCoScholasticSections = true;
                        break;
                    }
                }
                @endphp

                @if($hasCoScholasticSections)
                <section class="co-grid">
                    @foreach($printData['layout']['co_scholastic_sections'] ?? [] as $section)
                    @if(!empty($section['items']) && count($section['items']) > 0)
                    <table class="co-table">
                        <tr>
                            <th class="co-title">{{ $section['title'] ?? 'Co-Scholastic Area' }}</th>
                            <th>Grade</th>
                        </tr>
                        @php
                        $seenSectionCats = [];
                        @endphp
                        @foreach($section['items'] ?? [] as $item)
                        @php
                        $rawCat = preg_replace('/[^a-z0-9]/', '', strtolower((string) $item));
                        if ($rawCat === 'discipline') {
                            $canonicalCat = 'discipline';
                            $displayItemName = 'Discipline';
                        } elseif (str_contains($rawCat, 'workedu') || str_contains($rawCat, 'workexp')) {
                            $canonicalCat = 'work_education';
                            $displayItemName = 'Work Education';
                        } elseif (str_contains($rawCat, 'artedu') || $rawCat === 'art' || $rawCat === 'arts' || str_contains($rawCat, 'fineart') || str_contains($rawCat, 'drawing')) {
                            $canonicalCat = 'art_education';
                            $displayItemName = 'Art Education';
                        } elseif (str_contains($rawCat, 'health') || str_contains($rawCat, 'physicaledu') || str_contains($rawCat, 'pewellbeing') || str_contains($rawCat, 'wellbeing') || $rawCat === 'pe') {
                            $canonicalCat = 'health_pe';
                            $displayItemName = 'Health And Physical Education';
                        } else {
                            $canonicalCat = $rawCat;
                            $displayItemName = ucwords(strtolower((string) $item));
                        }

                        if (isset($seenSectionCats[$canonicalCat])) {
                            continue;
                        }
                        $seenSectionCats[$canonicalCat] = true;

                        $itemGrade = $section['grades'][$item] ?? null;
                        if (($itemGrade === null || $itemGrade === '') && !empty($section['grades'])) {
                            foreach ($section['grades'] as $gKey => $gVal) {
                                $gRaw = preg_replace('/[^a-z0-9]/', '', strtolower((string) $gKey));
                                if ($gRaw === 'discipline') $gCat = 'discipline';
                                elseif (str_contains($gRaw, 'workedu') || str_contains($gRaw, 'workexp')) $gCat = 'work_education';
                                elseif (str_contains($gRaw, 'artedu') || $gRaw === 'art' || $gRaw === 'arts' || str_contains($gRaw, 'fineart') || str_contains($gRaw, 'drawing')) $gCat = 'art_education';
                                elseif (str_contains($gRaw, 'health') || str_contains($gRaw, 'physicaledu') || str_contains($gRaw, 'pewellbeing') || str_contains($gRaw, 'wellbeing') || $gRaw === 'pe') $gCat = 'health_pe';
                                else $gCat = $gRaw;

                                if ($gCat === $canonicalCat && !empty($gVal)) {
                                    $itemGrade = $gVal;
                                    break;
                                }
                            }
                        }
                        $finalGrade = !empty($printData['is_blank']) ? '' : ($itemGrade ?: ($printData['grading']['co_scholastic_default_grade'] ?? 'A'));
                        @endphp
                        <tr>
                            <td><strong>{{ $displayItemName }}</strong></td>
                            <td>{{ $finalGrade }}</td>
                        </tr>
                        @endforeach
                    </table>
                    @endif
                    @endforeach
                </section>
                @endif

                <section class="summary">
                    <div class="remarks">Class Teacher's remarks : {{ $printData['remarks'] ?? '' }}</div>
                    <div class="summary-stats">
                        @php
                        $displayPercentage = $printData['percentage'] ?? null;
                        if (empty($printData['is_blank']) && $scholasticRows->isNotEmpty()) {
                            $terms = $printData['layout']['scholastic_terms'] ?? [];
                            $term2Index = null;
                            foreach ($terms as $tIdx => $tCfg) {
                                $tKey = strtolower((string) ($tCfg['key'] ?? ''));
                                $tLabel = strtolower((string) ($tCfg['label'] ?? ''));
                                $tExamType = strtolower((string) ($tCfg['exam_type'] ?? ''));
                                if ($tKey === 'term_2' || str_contains($tLabel, 'term ii') || str_contains($tLabel, 'term 2') || str_contains($tLabel, 'annual') || str_contains($tLabel, 'pre board') || $tExamType === 'term 2') {
                                    $term2Index = $tIdx;
                                    break;
                                }
                            }
                            if ($term2Index === null && count($terms) > 1) {
                                $term2Index = count($terms) - 1;
                            }
                            if ($term2Index === null && count($terms) > 0) {
                                $term2Index = 0;
                            }

                            if ($term2Index !== null && isset($terms[$term2Index])) {
                                $term2Cfg = $terms[$term2Index];
                                $totalCols = collect($term2Cfg['columns'] ?? [])->filter(fn ($col) => !empty($col['counts_in_total']));
                                $term2MaxPerSub = $totalCols->isNotEmpty()
                                    ? (float) $totalCols->sum(fn ($col) => (float) ($col['max'] ?? 0))
                                    : (float) collect($term2Cfg['columns'] ?? [])->sum(fn ($col) => (float) ($col['max'] ?? 0));
                                if ($term2MaxPerSub <= 0) {
                                    $term2MaxPerSub = 100.0;
                                }

                                $t2Obtained = 0.0;
                                $t2Max = 0.0;
                                foreach ($scholasticRows as $sRow) {
                                    $t2Obtained += (float) ($sRow['terms'][$term2Index]['total'] ?? 0);
                                    $t2Max += $term2MaxPerSub;
                                }

                                if ($t2Max > 0) {
                                    $displayPercentage = round(($t2Obtained / $t2Max) * 100, 2);
                                }
                            }
                        }
                        @endphp
                        <div>Percentage : {{ ($displayPercentage !== null && $displayPercentage !== '' && empty($printData['is_blank'])) ? $formatPercentage($displayPercentage) : '' }}</div>
                        {{-- <div>Result : {{ $printData['result'] ?? '' }}</div> --}}
                    </div>
                </section>

                <section class="footer-area">
                    <div class="signature">Signature of Class Teacher</div>
                    <div class="abbr-box">
                        <div>PT-Periodic Test, MAS-Multiple Assessment Strategy,</div>
                        <div>PF-Portfolio , SEA- Subject Enrichment Activity ,HY- Half Yearly</div>
                    </div>
                    <div class="signature">Signature of Principal</div>
                </section>
            </main>
            @endforeach
        </div>
    </div>

    <div class="print-actions d-flex justify-content-center align-items-center gap-2 mt-3 mb-4">
        <a href="{{ $backUrl }}" class="btn btn-sm btn-secondary d-inline-flex align-items-center" onclick="handleBack(event)">
            <i class="fa fa-arrow-left mr-1"></i> Back
        </a>
        <button class="btn btn-sm btn-success d-inline-flex align-items-center" type="button" onclick="printMarksheet()">
            <i class="fa fa-print mr-1"></i> Print Marksheet
        </button>
    </div>
</div>

<script>
    function handleBack(e) {
        if (window.history.length > 1 && document.referrer && document.referrer !== window.location.href) {
            e.preventDefault();
            window.history.back();
            return;
        }
        if (window.opener && !window.opener.closed) {
            e.preventDefault();
            window.close();
            return;
        }
    }

    function printMarksheet() {
        const content = document.getElementById('marksheetPrintArea').cloneNode(true);
        const styles = document.getElementById('marksheet-print-styles').innerHTML;
        const printWindow = window.open('', '_blank', 'width=1200,height=800');

        printWindow.document.open();
        printWindow.document.write(`
        <html>
        <head>
            <base href="{{ url('/') }}/">
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