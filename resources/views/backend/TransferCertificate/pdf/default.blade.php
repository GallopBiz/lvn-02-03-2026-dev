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
    $metaIssueDate = '';
    if (!empty($certificate->issue_date)) {
        try {
            $metaIssueDate = \Carbon\Carbon::parse($certificate->issue_date)->format('d-m-Y');
        } catch (\Throwable) {
            $metaIssueDate = (string) $certificate->issue_date;
        }
    }
    $tcSeqNo = $certificate->certificate_no;
    if (preg_match('/(?:^|\/)(\d+)$/', (string) $certificate->certificate_no, $matches)) {
        $tcSeqNo = $matches[1];
    }
    $tcMetaValue = $metaIssueDate ? "{$tcSeqNo}/{$metaIssueDate}" : $tcSeqNo;
    $penApaarId = $value('pen_apaar_id') ?: trim($value('pen_no') . ($value('apaar_id') ? ' / ' . $value('apaar_id') : ''));

    $categoryValue = $value('category') ?: $value('caste');
    if (!empty($categoryValue)) {
        $rawCatVal = trim((string) $categoryValue);
        if (preg_match('/^(.*?)\s*\(Category\)/i', $rawCatVal, $m)) {
            $categoryValue = trim($m[1]);
        } elseif (str_contains($rawCatVal, '-')) {
            $parts = explode('-', $rawCatVal, 2);
            $firstPart = trim($parts[0]);
            $upperFirst = strtoupper($firstPart);
            if (in_array($upperFirst, ['GENERAL', 'GEN', 'GEN.', 'OBC', 'SC', 'ST', 'UR', 'OPEN', 'UNRESERVED'])) {
                $categoryValue = in_array($upperFirst, ['GEN', 'GEN.', 'GENERAL', 'UR', 'OPEN', 'UNRESERVED']) ? 'General' : $upperFirst;
            } else {
                $categoryValue = $firstPart;
            }
        } elseif (preg_match('/^(.*?)\s*\(Caste\)$/i', $rawCatVal, $m)) {
            $rawCat = $student['category'] ?? ($student['student_category'] ?? null);
            if ($rawCat) {
                $upperRaw = strtoupper(trim($rawCat));
                $categoryValue = in_array($upperRaw, ['GEN', 'GEN.', 'GENERAL', 'UR', 'OPEN', 'UNRESERVED']) ? 'General' : (in_array($upperRaw, ['OBC', 'SC', 'ST']) ? $upperRaw : ucfirst(trim($rawCat)));
            } else {
                $categoryValue = '';
            }
        }
    }

    $rows = [
        ['label' => 'Name of Student', 'value' => $studentName],
        ['label' => 'Mother\'s / Guardian\'s Name', 'value' => $motherName],
        ['label' => 'Father\'s / Guardian\'s Name', 'value' => $fatherName],
        ['label' => 'Nationality', 'value' => $value('nationality', 'INDIAN')],
        ['label' => 'Whether the candidate belongs to SC or ST', 'value' => $categoryValue],
        ['label' => 'Date of admission in the school with Class', 'value' => trim($admissionDate . ($admittedClass ? ' ' . $formatClass($admittedClass) : ''))],
        ['label' => 'Date of birth (in Christian Era)', 'label_note' => 'according to admission register', 'split' => [
            ['label' => 'In figures', 'value' => $dob, 'is_dob' => true],
            ['label' => 'In words', 'value' => $dobWords, 'is_dob' => true],
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
        ['label' => 'PEN No. / APAAR ID', 'value' => $penApaarId ?: '--'],
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
        font-size: {{ $isPdf ? '11.5px' : '12px' }};
        line-height: {{ $isPdf ? '1.05' : '1.14' }};
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
        font-size: {{ $isPdf ? '11px' : '12px' }};
        font-weight: normal;
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
        table-layout: fixed;
        margin-bottom: {{ $isPdf ? '5px' : '8px' }};
        font-size: {{ $isPdf ? '12px' : '13px' }};
    }

    .tc-meta td {
        padding: 0 2px 2px 2px;
        vertical-align: middle;
        white-space: nowrap;
        font-weight: normal;
    }

    .tc-meta-left {
        text-align: left;
        width: 62%;
    }

    .tc-meta-right {
        text-align: left;
        width: 31%;
        padding-left: 0;
    }

    .tc-meta-label,
    .tc-meta-scholar-label {
        font-weight: normal;
        display: inline-block;
    }

    .tc-meta-colon {
        font-weight: normal;
        display: inline-block;
        padding: 0 4px;
        text-align: center;
    }

    .tc-meta-value,
    .tc-meta-scholar-value {
        font-weight: normal;
        display: inline-block;
    }

    .tc-list {
        width: 100%;
        border-collapse: collapse;
    }

    .tc-list td {
        padding: {{ $isPdf ? '3.5px 4px' : '4px 5px' }};
        vertical-align: top;
    }

    .tc-list tr {
        page-break-inside: avoid;
    }

    .tc-label {
        width: 58%;
        font-weight: normal;
        padding-right: 6px;
    }

    .tc-colon {
        width: 4%;
        text-align: center;
        font-weight: normal;
        padding-right: 6px;
    }

    .tc-value {
        font-weight: normal;
        text-transform: uppercase;
        word-break: break-word;
        word-wrap: break-word;
        white-space: normal;
        padding-left: 4px;
    }

    .tc-dob-bold {
        font-weight: 700 !important;
    }

    .tc-no {
        width: 4%;
        padding-right: 6px !important;
        text-align: right;
        font-weight: normal;
    }

    .tc-label-note {
        display: block;
        margin-top: 1px;
        font-size: {{ $isPdf ? '10px' : '11px' }};
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
        font-weight: normal;
        padding-right: 6px !important;
    }

    .tc-split-sub-label {
        width: 84px;
        font-weight: normal;
        text-transform: none;
        text-align: left;
        white-space: nowrap;
    }

    .tc-split-full-label {
        font-weight: normal;
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
        font-size: {{ $isPdf ? '11.5px' : '12px' }};
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
        font-size: 11px;
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
                    <td class="tc-meta-left">
                        <span class="tc-meta-label">TC No./ Date</span>
                        <span class="tc-meta-colon">:</span>
                        <span class="tc-meta-value">{{ $tcMetaValue }}</span>
                    </td>
                    <td class="tc-meta-right">
                        <span class="tc-meta-scholar-label">Scholar No.</span>
                        <span class="tc-meta-colon">:</span>
                        <span class="tc-meta-scholar-value">{{ $certificate->scholar_no }}</span>
                    </td>
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
                                $isDobFirst = ($row['label'] === 'Date of birth (in Christian Era)' || !empty($firstSplit['is_dob']));
                            @endphp
                            <td class="tc-colon">:</td>
                            <td class="tc-value {{ $isDobFirst ? 'tc-dob-bold' : '' }}">{{ !empty($row['value']) ? $row['value'] : ($firstSplit['value'] ?? '') }}</td>
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
                            @php
                                $isDobSub = ($row['label'] === 'Date of birth (in Christian Era)' || !empty($split['is_dob']));
                            @endphp
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
                                <td class="tc-value {{ $isDobSub ? 'tc-dob-bold' : '' }}">{{ $split['value'] ?? '' }}</td>
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
