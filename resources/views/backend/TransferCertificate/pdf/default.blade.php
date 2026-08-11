@php
    $snapshot = $certificate->snapshot ?? [];
    $student = $snapshot['student'] ?? [];
    $parents = $snapshot['parents'] ?? [];
    $admission = $snapshot['admission'] ?? [];
    $form = $certificate->tc_form_data ?? [];
    $isDuplicate = optional($certificate->latestIssue)->is_duplicate;
    $isPdf = $isPdf ?? false;

    $value = function ($key, $default = '') use ($form) {
        $result = data_get($form, $key, $default);
        return ($result === null || $result === '') ? '' : $result;
    };

    $formatDate = function ($date) {
        if (empty($date)) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($date)->format('d-M-Y');
        } catch (\Throwable) {
            return $date;
        }
    };

    $formatSession = function ($session) {
        return str_replace('_', '-', (string) $session);
    };

    $formatClass = function ($class) {
        $class = trim((string) $class);

        if ($class === '') {
            return '';
        }

        $parts = preg_split('/\s*-\s*/', $class, 2);
        $baseClass = trim($parts[0] ?? '');
        $sectionText = isset($parts[1]) ? trim($parts[1]) : '';
        $normalized = strtoupper(str_replace([' ', '_', '-'], '', $baseClass));
        $romanMap = [
            '1' => 'I',
            '01' => 'I',
            '2' => 'II',
            '02' => 'II',
            '3' => 'III',
            '03' => 'III',
            '4' => 'IV',
            '04' => 'IV',
            '5' => 'V',
            '05' => 'V',
            '6' => 'VI',
            '06' => 'VI',
            '7' => 'VII',
            '07' => 'VII',
            '8' => 'VIII',
            '08' => 'VIII',
            '9' => 'IX',
            '09' => 'IX',
            '10' => 'X',
            '11' => 'XI',
            '12' => 'XII',
            'KG1' => 'KG-I',
            'KG01' => 'KG-I',
            'KGI' => 'KG-I',
            'KG2' => 'KG-II',
            'KG02' => 'KG-II',
            'KGII' => 'KG-II',
        ];

        $displayClass = $romanMap[$normalized] ?? $baseClass;

        return trim($displayClass . ($sectionText ? ' - ' . $sectionText : ''));
    };

    $studentName = $value('name', $student['student_name'] ?? $certificate->student_name);
    $motherName = $value('mother_name', $parents['mother_name'] ?? '');
    $fatherName = $value('father_name', $parents['father_name'] ?? '');
    $dob = $formatDate($value('date_of_birth', $student['date_of_birth'] ?? ''));
    $dobWords = $value('date_of_birth_words');
    $admissionDate = $formatDate($value('date_of_admitted', $admission['admission_date'] ?? ''));
    $admittedClass = $value('class_to_which_admitted', $admission['application_for'] ?? '');
    $studyingClass = $value('class_studying', $certificate->class_name);
    $section = $value('section', $certificate->section_name);
    $classStudiedText = $formatClass(trim($studyingClass . ($section ? ' - ' . $section : '')));
    $promotionClass = $formatClass($value('promotion_class_figures'));
    $lastExamResult = $value('result');
    $lastExamRawClass = $value('last_exam_result');
    $lastExamClass = in_array(strtoupper($lastExamRawClass), ['PASSED', 'FAILED', 'COMPARTMENT', 'PROMOTED'], true)
        ? $formatClass($studyingClass)
        : $formatClass($lastExamRawClass ?: $studyingClass);
    $lastExamSession = $formatSession($value('year_of_passing', $certificate->session_name));
    $lastExamText = trim($lastExamClass . ($lastExamResult ? ', ' . $lastExamResult : '') . ($lastExamSession ? ' ' . $lastExamSession : ''));
    $schoolDuesDate = $formatDate($value('school_dues_paid_upto'));
    $applicationDate = $formatDate($value('application_date'));
    $issueDate = $formatDate($certificate->issue_date);

    $rows = [
        ['label' => 'Name of Student', 'value' => $studentName],
        ['label' => 'Mother\'s / Guardian\'s Name', 'value' => $motherName],
        ['label' => 'Father\'s / Guardian\'s Name', 'value' => $fatherName],
        ['label' => 'Nationality', 'value' => $value('nationality', 'INDIAN')],
        ['label' => 'Whether the candidate belongs to SC or ST', 'value' => $value('caste')],
        ['label' => 'Date of admission in the school with Class', 'value' => trim($admissionDate . ($admittedClass ? ' ' . $formatClass($admittedClass) : ''))],
        ['label' => 'Date of birth (in Christian Era)', 'label_note' => 'according to admission register', 'split' => [
            ['label' => 'In figures', 'value' => $dob],
            ['label' => 'In words', 'value' => $dobWords],
        ]],
        ['label' => 'Class in which the pupil last studied', 'split' => [
            ['label' => 'In figures', 'value' => $classStudiedText],
            ['label' => 'In words', 'value' => $value('class_studying_words')],
        ]],
        ['label' => 'School / Board\'s Annual Examination last taken with result', 'value' => $lastExamText],
        ['label' => 'Whether failed, if so once/twice in the same class', 'value' => $value('failed_once_twice')],
        ['label' => 'Subjects studied', 'value' => $value('subjects_studied')],
        ['label' => 'Whether qualified for promotion to higher class', 'split' => [
            ['label' => 'If so to which class', 'value' => $promotionClass],
        ], 'value' => $value('promotion_qualified')],
        ['label' => 'Month upto which the school dues / Paid', 'value' => $schoolDuesDate ?: $value('school_dues_paid_upto_words')],
        ['label' => 'Any fees concession availed of if so the nature of such concession', 'value' => $value('fee_concession')],
        ['label' => 'Total No. of working days', 'value' => $value('total_working_days')],
        ['label' => 'Total No. of working days present', 'value' => $value('working_days_present')],
        ['label' => 'Whether NCC Cadet / Boy Scout / Girl Guide', 'value' => $value('ncc_scout_guide')],
        ['label' => 'Games played or extra-curricular activities in which the pupil usually took part (mention achievement level there in)', 'value' => $value('games_activities')],
        ['label' => 'General conduct', 'value' => $certificate->conduct],
        ['label' => 'Date of application for certificate', 'value' => $applicationDate],
        ['label' => 'Date of issue of certificate', 'value' => $issueDate],
        ['label' => 'Reason for leaving the school', 'value' => $certificate->reason_for_leaving],
        ['label' => 'Any other remarks', 'value' => $certificate->remarks],
    ];
@endphp

<style>
    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    html,
    body {
        margin: 0;
        padding: 0;
    }

    /* Place the visible border inside the printable area.
       @page margin is 8mm, so keep the wrapper smaller by 16mm total. */
    .tc-page-wrap {
        background: #fff;
        padding: 0;
        width: calc(210mm - 16mm); /* 210mm minus left+right page margins */
        min-height: calc(297mm - 16mm);
        overflow: visible;
        margin: 8mm auto; /* sit inside @page margins */
        box-sizing: border-box;
    }

    .tc-a4 {
        width: 200mm; /* inner width adjusted for smaller page margin */
        min-height: calc(265mm - 16mm);
        margin: 0 auto;
        padding: {{ $isPdf ? '8mm' : '10mm' }};
        background: #fff;
        color: #111;
        font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
        font-size: {{ $isPdf ? '10.5px' : '11px' }};
        line-height: {{ $isPdf ? '1.04' : '1.12' }};
        position: relative;
        box-sizing: border-box;
        overflow: visible;
    }

    .tc-a4 * {
        box-sizing: border-box;
    }

    /* ===== Reserved blank space for pre-printed letterhead ===== */
    /* This paper already has the logo, school name, address and
       "School Leaving Certificate" title printed on it, so we just
       leave blank vertical space instead of rendering that header.
       Adjust the height below to match your letterhead's printed area. */
    .tc-letterhead-space {
        height: {{ $isPdf ? '30mm' : '28mm' }};
    }

    .tc-tcno-line {
        font-size: {{ $isPdf ? '10px' : '11px' }};
        font-weight: 700;
        margin-bottom: 2px;
    }
    /* ===== End header ===== */

    .tc-top-line {
        border-top: 1px solid #111;
        margin-bottom: {{ $isPdf ? '4px' : '6px' }};
    }

    .tc-meta {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: {{ $isPdf ? '4px' : '8px' }};
        font-size: {{ $isPdf ? '11px' : '12px' }};
        table-layout: fixed;
    }

    .tc-meta td {
        padding: 0 4px 2px 4px;
        vertical-align: bottom;
        white-space: nowrap;
    }

    .tc-meta-label,
    .tc-meta-book-label,
    .tc-meta-scholar-label {
        width: 78px;
        font-weight: 700;
        text-align: right;
        padding-right: 6px;
    }

    .tc-meta-colon {
        width: 8px;
        text-align: center;
        font-weight: 700;
        padding: 0 4px;
    }

    .tc-meta-value {
        font-weight: 700;
        text-align: left;
        padding-left: 4px;
    }

    .tc-meta-spacer {
        width: 12px;
    }

    .tc-meta-book-label {
        width: 50px;
        font-weight: 700;
    }

    .tc-meta-book-value {
        width: 78px;
        font-weight: 700;
    }

    .tc-meta-scholar-label {
        width: 62px;
        font-weight: 700;
    }

    .tc-list {
        width: 100%;
        border-collapse: collapse;
    }

    .tc-list td {
        padding: {{ $isPdf ? '4px' : '5px 5px' }};
        vertical-align: top;
    }

    .tc-list tr {
        page-break-inside: avoid;
    }

    .tc-label {
        width: 58%;
        font-weight: 700;
        padding-right: 6px;
    }

    .tc-colon {
        width: 4%;
        text-align: center;
        font-weight: 700;
        padding-right: 6px;
    }

    .tc-value {
        font-weight: 600;
        text-transform: uppercase;
        word-break: break-word;
        word-wrap: break-word;
        white-space: normal;
        padding-left: 4px;
    }

    .tc-no {
        width: 4%;
        padding-right: 6px !important;
        text-align: right;
        font-weight: 700;
    }

    .tc-label-note {
        display: block;
        margin-top: 1px;
        font-size: {{ $isPdf ? '9px' : '10px' }};
    }

    .tc-subjects-value {
        text-align: left;
        line-height: 1.14;
        word-spacing: normal;
        overflow-wrap: break-word;
        word-break: normal;
        word-wrap: break-word;
        white-space: normal;
    }

    .tc-split-label-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .tc-split-label-table td {
        padding: 0;
        vertical-align: top;
    }

    .tc-split-main-label {
        font-weight: 700;
        padding-right: 6px !important;
    }

    .tc-split-sub-label {
        width: 84px;
        font-weight: 700;
        text-transform: none;
        text-align: left;
        white-space: nowrap;
    }

    .tc-split-full-label {
        font-weight: 700;
        text-transform: none;
    }

    .tc-watermark {
        position: absolute;
        top: 42%;
        left: 13%;
        right: 13%;
        text-align: center;
        color: rgba(180, 0, 0, .14);
        font-size: 42px;
        font-weight: 700;
        text-transform: uppercase;
        transform: rotate(-24deg);
        z-index: 0;
    }

    .tc-content {
        position: relative;
        z-index: 1;
    }

    .tc-signatures {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: {{ $isPdf ? '10.5px' : '11px' }};
        margin-top: {{ $isPdf ? '5mm' : '6mm' }};
    }

    .tc-signatures td {
        padding: 0 8px;
        vertical-align: bottom;
        box-sizing: border-box;
        word-wrap: break-word;
        overflow-wrap: anywhere;
    }

    .tc-sign {
        width: 33.333%;
        max-width: 33.333%;
        vertical-align: bottom;
        font-weight: 400;
        padding: 6px 0;
        white-space: normal;
    }

    .tc-sign-center {
        text-align: center;
    }
    .tc-sign-right {
        text-align: right;
        padding-right: 8px;
    }
    .tc-sign small {
        display: block;
        font-size: 10px;
        margin-top: 6px;
        font-weight: 400;
    }

    @if(!$isPdf)
        @media screen {
            .tc-page-wrap {
                background: transparent;
                padding: 12px 0;
                height: auto;
                overflow: visible;
            }

            .tc-a4 {
                height: 200mm;
                border: none;
                border-radius: 0;
                box-shadow: none;
            }
        }

        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            .main-content,
            .tc-page-wrap,
            .tc-page-wrap * {
                visibility: visible !important;
            }

            .main-content {
                width: 210mm;
                margin: 0;
                padding: 0;
            }

            .tc-page-wrap {
                position: absolute;
                /* position inside the page margins to avoid printer clipping
                   Use left/right insets instead of fixed width to prevent rounding/scale clipping */
                left: 8mm;
                right: 8mm;
                top: 8mm;
                bottom: 8mm;
                padding: 0;
                background: #fff;
                overflow: visible;
                box-sizing: border-box;
                page-break-after: avoid;
                page-break-before: avoid;
                page-break-inside: avoid;
            }

            .tc-a4 {
                width: 100%;
                min-height: calc(100% - 20mm);
                margin: 0;
                padding: 10mm;
                box-shadow: none;
                page-break-after: avoid;
                page-break-before: avoid;
                page-break-inside: avoid;
            }

            .btn,
            .breadcrumb,
            .separator-breadcrumb,
            .tc-print-actions,
            .main-header,
            .side-content-wrap,
            .sidebar-overlay {
                display: none !important;
            }
        }
    @endif
</style>

<div class="tc-page-wrap">
    <div class="tc-a4">
        @if($isDuplicate)
            <div class="tc-watermark">Duplicate Transfer Certificate</div>
        @endif

        <div class="tc-content">

            {{-- Blank space reserved for the pre-printed letterhead
                 (logo, school name, address, title, affiliation line) --}}
            <div class="tc-letterhead-space"></div>

            <table class="tc-meta">
                <tr>
                    <td class="tc-meta-label">TC No./ Date</td>
                    <td class="tc-meta-colon">:</td>
                    <td class="tc-meta-value">{{ $certificate->certificate_no }}{{ $issueDate ? '/' . $issueDate : '' }}</td>
                    <td class="tc-meta-spacer"></td>
                    <td class="tc-meta-scholar-label">Scholar No.</td>
                    <td class="tc-meta-colon">:</td>
                    <td class="tc-meta-value">{{ $certificate->scholar_no }}</td>
                </tr>
            </table>

            <div class="tc-top-line"></div>

            <table class="tc-list">
                @foreach($rows as $index => $row)
                    <tr>
                        <td class="tc-no">{{ $index + 1 }}.</td>
                        <td class="tc-label">
                            @if(!empty($row['split']) && empty($row['value']))
                                <table class="tc-split-label-table">
                                    <tr>
                                        <td class="tc-split-main-label">
                                            {{ $row['label'] }}
                                        </td>
                                        <td class="tc-split-sub-label">{{ $row['split'][0]['label'] ?? '' }}</td>
                                    </tr>
                                </table>
                            @else
                                {{ $row['label'] }}
                                @if(!empty($row['label_note']))
                                    <span class="tc-label-note">{{ $row['label_note'] }}</span>
                                @endif
                            @endif
                        </td>
                        @if(!empty($row['split']))
                            @php
                                $firstSplit = $row['split'][0] ?? null;
                            @endphp
                            <td class="tc-colon">:</td>
                            <td class="tc-value">{{ !empty($row['value']) ? $row['value'] : ($firstSplit['value'] ?? '') }}</td>
                        @else
                            <td class="tc-colon">:</td>
                            <td class="tc-value {{ $row['label'] === 'Subjects studied' ? 'tc-subjects-value' : '' }}">
                                @if(!empty($row['value']))
                                    {{ $row['value'] }}
                                @endif
                            </td>
                        @endif
                    </tr>
                    @if(!empty($row['split']))
                        @foreach(!empty($row['value']) ? $row['split'] : array_slice($row['split'], 1) as $split)
                            <tr>
                                <td class="tc-no"></td>
                                <td class="tc-label">
                                    @if(!empty($row['value']))
                                        <span class="tc-split-full-label">{{ $split['label'] }}</span>
                                    @else
                                        <table class="tc-split-label-table">
                                            <tr>
                                                <td class="tc-split-main-label">
                                                    @if(!empty($row['label_note']) && $loop->first)
                                                        {{ $row['label_note'] }}
                                                    @endif
                                                </td>
                                                <td class="tc-split-sub-label">{{ $split['label'] }}</td>
                                            </tr>
                                        </table>
                                    @endif
                                </td>
                                <td class="tc-colon">:</td>
                                <td class="tc-value">{{ $split['value'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </table>
        </div>

        <table class="tc-signatures">
            <tr>
                <td class="tc-sign">Signature of Class Teacher</td>
                <td class="tc-sign tc-sign-center">
                    Prepared &amp; Checked by
                    <small>(Full Name &amp; Designation)</small>
                </td>
                <td class="tc-sign tc-sign-center">Principal's Signature with Seal</td>
            </tr>
        </table>
    </div>
</div>
