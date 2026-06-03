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
        margin: 0;
    }

    html,
    body {
        margin: 0;
        padding: 0;
    }

    .tc-page-wrap {
        background: #fff;
        padding: 0;
        width: 210mm;
        height: {{ $isPdf ? '297mm' : 'auto' }};
        overflow: hidden;
    }

    .tc-a4 {
        width: {{ $isPdf ? '180mm' : '210mm' }};
        height: {{ $isPdf ? '277mm' : '297mm' }};
        min-height: 0;
        margin: 0 auto;
        padding: {{ $isPdf ? '12mm 15mm 8mm' : '15mm 17mm 12mm' }};
        background: #fff;
        color: #111;
        font-family: Arial, Helvetica, "DejaVu Sans", sans-serif;
        font-size: {{ $isPdf ? '10.8px' : '11.5px' }};
        line-height: {{ $isPdf ? '1.12' : '1.16' }};
        position: relative;
        box-sizing: border-box;
        overflow: hidden;
    }

    .tc-a4 * {
        box-sizing: border-box;
    }

    .tc-top-line {
        border-top: 1px solid #111;
        margin-bottom: {{ $isPdf ? '7px' : '10px' }};
    }

    .tc-meta {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: {{ $isPdf ? '6px' : '8px' }};
        font-size: {{ $isPdf ? '10.8px' : '11.5px' }};
        table-layout: fixed;
    }

    .tc-meta td {
        padding: 0 2px 3px 0;
        vertical-align: bottom;
        white-space: nowrap;
    }

    .tc-meta-label {
        width: 82px;
        font-weight: 700;
    }

    .tc-meta-colon {
        width: 10px;
        text-align: center;
        font-weight: 700;
    }

    .tc-meta-value {
        width: 120px;
        font-weight: 700;
    }

    .tc-meta-spacer {
        width: 18px;
    }

    .tc-meta-book-label {
        width: 58px;
        font-weight: 700;
    }

    .tc-meta-book-value {
        width: 92px;
        font-weight: 700;
    }

    .tc-meta-scholar-label {
        width: 70px;
        font-weight: 700;
    }

    .tc-list {
        width: 100%;
        border-collapse: collapse;
    }

    .tc-list td {
        padding: {{ $isPdf ? '2.15px 0' : '3px 0' }};
        vertical-align: top;
    }

    .tc-no {
        width: 20px;
        padding-right: 4px !important;
        text-align: right;
        font-weight: 700;
    }

    .tc-label {
        width: 320px;
        font-weight: 700;
    }

    .tc-label-note {
        display: block;
        margin-top: 2px;
    }

    .tc-colon {
        width: 14px;
        text-align: center;
        font-weight: 700;
    }

    .tc-value {
        font-weight: 700;
        text-transform: uppercase;
        word-break: break-word;
        word-wrap: break-word;
        white-space: normal;
    }

    .tc-subjects-value {
        text-align: left;
        line-height: 1.22;
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
        padding-right: 8px !important;
    }

    .tc-split-sub-label {
        width: 96px;
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
        font-size: {{ $isPdf ? '10.8px' : '11.5px' }};
        margin-top: {{ $isPdf ? '5mm' : '9mm' }};
    }

    .tc-sign {
        width: 33.33%;
        vertical-align: bottom;
        font-weight: 400;
        padding: 0;
    }

    .tc-sign-center {
        text-align: center;
    }

    .tc-sign-right {
        text-align: right;
    }

    .tc-sign small {
        display: block;
        font-size: 10.5px;
        margin-top: 3px;
    }

    @if(!$isPdf)
        @media screen {
            .tc-page-wrap {
                background: #e9edf3;
                padding: 18px 0;
                height: auto;
                overflow: visible;
            }

            .tc-a4 {
                height: 297mm;
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

            body * {
                visibility: hidden;
            }

            .tc-page-wrap,
            .tc-page-wrap * {
                visibility: visible;
            }

            .tc-page-wrap {
                position: absolute;
                left: 0;
                top: 0;
                width: 210mm;
                height: 297mm;
                padding: 0;
                background: #fff;
                overflow: hidden;
            }

            .tc-a4 {
                width: 210mm;
                height: 297mm;
                margin: 0;
                box-shadow: none;
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
            <table class="tc-meta">
                <tr>
                    <td class="tc-meta-label">TC No./ Date</td>
                    <td class="tc-meta-colon">:</td>
                    <td class="tc-meta-value">{{ $certificate->certificate_no }}{{ $issueDate ? '/' . $issueDate : '' }}</td>
                    <td class="tc-meta-spacer"></td>
                    <td class="tc-meta-book-label">Book No.</td>
                    <td class="tc-meta-colon">:</td>
                    <td class="tc-meta-book-value">{{ $value('book_no') }}</td>
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
                <td class="tc-sign tc-sign-right">Principal's Signature with Seal</td>
            </tr>
        </table>
    </div>
</div>
