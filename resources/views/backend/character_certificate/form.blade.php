@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-3">
    {{-- Breadcrumb Header --}}
    <div class="breadcrumb d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3">
        <div>
            <h1 class="mb-1 font-weight-bold text-dark d-flex align-items-center">
                <i class="i-Certificate text-primary mr-2" style="font-size: 1.8rem;"></i>
                Character Certificate
            </h1>
            <ul class="d-flex align-items-center mb-0 p-0 text-muted" style="list-style: none; gap: 8px; font-size: 13px;">
                <li><a href="{{ url('/') }}" class="text-muted">Home</a></li>
                <li>/</li>
                <li><a href="#" class="text-muted">Certificates</a></li>
                <li>/</li>
                <li class="text-primary font-weight-bold">Character Certificate</li>
            </ul>
        </div>
        <div class="mt-2 mt-md-0 d-flex align-items-center gap-2">
            <span class="badge badge-primary px-3 py-2 text-white" style="background-color: #663399; font-size: 0.85rem;">
                <i class="i-Calendar mr-1"></i> Academic Session: {{ $sessionFormatted ?? '2025-26' }}
            </span>
        </div>
    </div>
    <div class="separator-breadcrumb border-top mb-4"></div>

    <form id="certForm" method="POST" action="{{ route('character-certificate.generateCertificate') }}" target="_blank">
        @csrf

        <div class="row">
            {{-- Left Column: Controls & Editable Fields --}}
            <div class="col-xl-7 col-lg-6">

                {{-- Card 1: Student Selection --}}
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="font-weight-bold text-primary mb-0 d-flex align-items-center">
                            <i class="i-Student-MaleFemale mr-2 font-weight-bold" style="font-size: 1.1rem;"></i>
                            1. Select Student
                        </h6>
                        <span class="badge badge-light border text-muted px-2 py-1 small">Dynamic Auto-Fill</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            <div class="col-md-5 form-group mb-2">
                                <label class="font-weight-bold small text-dark mb-1">
                                    <i class="i-Filter-2 mr-1 text-muted"></i> Filter by Class
                                </label>
                                <select class="form-control" id="classFilter">
                                    <option value="">All Existing Classes</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c['value'] }}">{{ $c['display'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7 form-group mb-2">
                                <label class="font-weight-bold small text-dark mb-1">
                                    <i class="i-Search-People mr-1 text-muted"></i> Search Student <span class="text-danger">*</span>
                                </label>
                                <select class="form-control select2" id="student_select" required style="width: 100%;">
                                    <option value="">Search by Name or Scholar No...</option>
                                    @foreach($students as $st)
                                        @php
                                            $json = json_decode($st->json_str ?? '{}', true) ?: [];
                                            $sName = $json['studentname'] ?? $st->student_name ?? '';
                                            $fName = $json['fathername'] ?? '';
                                            $dobFormatted = $st->date_of_birth ? date('d-m-Y', strtotime($st->date_of_birth)) : '';
                                            $gender = strtolower($json['gender'] ?? 'male');
                                            $selected = (!empty($preselectedStudent) && (string)$preselectedStudent['scholar_no'] === (string)$st->scholar_no) ? 'selected' : '';
                                            $cRoman = \App\Academic\AcademicHelper::toRoman((string)$st->class_name);
                                            $classLabel = $cRoman ? "Class {$cRoman}" : "Class {$st->class_name}";
                                        @endphp
                                        <option value="{{ $st->scholar_no }}"
                                            {{ $selected }}
                                            data-name="{{ $sName }}"
                                            data-father="{{ $fName }}"
                                            data-dob="{{ $dobFormatted }}"
                                            data-class="{{ $st->class_name }}"
                                            data-class-display="{{ $classLabel }}"
                                            data-section="{{ $st->section_name }}"
                                            data-gender="{{ $gender }}">
                                            {{ $sName }} &mdash; Scholar #{{ $st->scholar_no }} ({{ $classLabel }}{{ $st->section_name ? ' - '.$st->section_name : '' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Active Student Info Strip --}}
                        <div class="student-info-strip mt-3 p-3 rounded" id="studentInfoStrip" style="background-color: #f8fafc; border: 1px solid #e2e8f0; display: none;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge badge-success text-white px-2 py-1 small">
                                    <i class="i-Yes mr-1"></i> Student Selected
                                </span>
                                <span class="text-muted small" id="infoClassSec"></span>
                            </div>
                            <div class="row g-2 small">
                                <div class="col-sm-6">
                                    <strong class="text-muted">Student:</strong> <span class="font-weight-bold text-dark" id="infoName">-</span>
                                </div>
                                <div class="col-sm-6">
                                    <strong class="text-muted">Father:</strong> <span class="font-weight-bold text-dark" id="infoFather">-</span>
                                </div>
                                <div class="col-sm-6">
                                    <strong class="text-muted">Date of Birth:</strong> <span class="font-weight-bold text-dark" id="infoDob">-</span>
                                </div>
                                <div class="col-sm-6">
                                    <strong class="text-muted">Scholar No:</strong> <span class="font-weight-bold text-dark font-monospace" id="infoScholar">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Certificate Content & Text Wording --}}
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="font-weight-bold text-dark mb-0 d-flex align-items-center">
                            <i class="i-Pen-4 mr-2 text-primary" style="font-size: 1.1rem;"></i>
                            2. Certificate Details &amp; Wording (Editable)
                        </h6>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="resetTextBtn" title="Reset all wording to reference layout">
                            <i class="i-Repeat mr-1"></i> Reset to Default
                        </button>
                    </div>
                    <div class="card-body p-3">
                        {{-- Meta Row --}}
                        <div class="row g-2 mb-3">
                            <div class="col-md-4 form-group mb-2">
                                <label class="font-weight-bold small text-muted text-uppercase">Certificate Title</label>
                                <input type="text" class="form-control font-weight-bold" name="cert_title" id="cert_title" value="CHARACTER - CERTIFICATE">
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                <label class="font-weight-bold small text-muted text-uppercase">Academic Session</label>
                                <input type="text" class="form-control font-weight-bold" name="session_year" id="session_year" value="{{ $sessionFormatted ?? '2025-26' }}">
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                <label class="font-weight-bold small text-muted text-uppercase">Pronoun Style</label>
                                <select class="form-control" name="pronoun_mode" id="pronoun_mode">
                                    <option value="both" selected>Reference Format (Mast./Ms, Son/ Daughter)</option>
                                    <option value="boy">Boy Specific (Mast., Son of Mr., He, His)</option>
                                    <option value="girl">Girl Specific (Ms, Daughter of Mr., She, Her)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Editable Dynamic Fields --}}
                        <div class="p-3 mb-3 rounded" style="background-color: #f1f5f9; border: 1px solid #cbd5e1;">
                            <div class="small font-weight-bold text-uppercase text-dark mb-2 d-flex align-items-center">
                                <i class="i-ID-Card mr-1 text-primary"></i> Dynamic Student Fields (Underlined on Printout)
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6 form-group mb-2">
                                    <label class="small font-weight-bold text-muted">Student Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control bg-white font-weight-bold" name="student_name" id="student_name" placeholder="Student Name" value="{{ $preselectedStudent['student_name'] ?? '' }}" required>
                                </div>
                                <div class="col-md-6 form-group mb-2">
                                    <label class="small font-weight-bold text-muted">Father's Name</label>
                                    <input type="text" class="form-control bg-white font-weight-bold" name="father_name" id="father_name" placeholder="Father's Name" value="{{ $preselectedStudent['father_name'] ?? '' }}">
                                </div>
                                <div class="col-md-6 form-group mb-0">
                                    <label class="small font-weight-bold text-muted">Date of Birth (DD-MM-YYYY)</label>
                                    <input type="text" class="form-control bg-white font-weight-bold" name="dob" id="dob" placeholder="14-07-2008" value="{{ $preselectedStudent['dob'] ?? '' }}">
                                </div>
                                <div class="col-md-6 form-group mb-0">
                                    <label class="small font-weight-bold text-muted">Scholar Number</label>
                                    <input type="text" class="form-control bg-white font-weight-bold font-monospace" name="scholar_no" id="scholar_no" placeholder="6884" value="{{ $preselectedStudent['scholar_no'] ?? '' }}">
                                </div>
                            </div>
                        </div>

                        {{-- Paragraph 1 Editor --}}
                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-dark d-flex justify-content-between align-items-center mb-1">
                                <span>Paragraph 1 (Main Conduct Statement)</span>
                                <span class="badge badge-light border text-muted small">Underlines applied automatically</span>
                            </label>
                            <textarea class="form-control" name="paragraph_1" id="paragraph_1" rows="5" style="font-size: 13.5px; line-height: 1.6; font-family: 'Times New Roman', serif; font-style: italic;"></textarea>
                            <small class="text-muted mt-1 d-block">
                                <i class="i-Information mr-1"></i> You can freely modify any wording (e.g. conduct remarks, extracurricular activities, or phrases).
                            </small>
                        </div>

                        {{-- Paragraph 2 Editor --}}
                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Paragraph 2 (Examination / Class Passed)
                            </label>
                            <div class="input-group input-group-sm mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light font-weight-bold">Exam / Class:</span>
                                </div>
                                <input type="text" class="form-control" name="exam_name" id="exam_name" value="Senior Secondary School Certificate Examination 2026" placeholder="Examination or Class Name">
                            </div>
                            <textarea class="form-control" name="paragraph_2" id="paragraph_2" rows="2" style="font-size: 13.5px; font-family: 'Times New Roman', serif; font-style: italic;"></textarea>
                        </div>

                        {{-- Paragraph 3 Editor --}}
                        <div class="form-group mb-0">
                            <label class="font-weight-bold small text-dark mb-1">
                                Paragraph 3 (Closing Wish)
                            </label>
                            <input type="text" class="form-control" name="paragraph_3" id="paragraph_3" value="We wish him/ her bright future head." style="font-family: 'Times New Roman', serif; font-style: italic; font-size: 13.5px;">
                        </div>
                    </div>
                </div>

                {{-- Card 3: Letterhead & Print Settings --}}
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="font-weight-bold text-dark mb-0 d-flex align-items-center">
                            <i class="i-Gear mr-2 text-primary" style="font-size: 1.1rem;"></i>
                            3. Pre-printed Letterhead &amp; Print Alignment
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5 form-group mb-0">
                                <label class="small font-weight-bold text-muted mb-1">Top Blank Margin (for School Letterhead)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="blank_header_height" id="blank_header_height" value="55" min="20" max="100">
                                    <div class="input-group-append">
                                        <span class="input-group-text font-weight-bold">mm</span>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">Default 55mm leaves exact space for school logo &amp; address</small>
                            </div>
                            <div class="col-md-7 form-group mb-0">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="include_border" name="include_border" value="1" checked>
                                    <label class="custom-control-label small font-weight-bold text-dark" for="include_border">
                                        Include Outer Border Frame (as in reference image)
                                    </label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="include_header" name="include_header" value="1">
                                    <label class="custom-control-label small font-weight-bold text-muted" for="include_header" title="Check this only if printing on plain paper without pre-printed logo">
                                        Print School Header on Sheet <span class="badge badge-light border text-muted">Plain Paper Mode</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary btn-lg px-4 shadow">
                                <i class="i-Printer mr-1"></i> Generate &amp; Print Certificate
                            </button>
                            <button type="button" class="btn btn-outline-info btn-lg px-3" id="updatePreviewBtn">
                                <i class="i-Eye mr-1"></i> Refresh Preview
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Live A4 Sheet Preview Desk --}}
            <div class="col-xl-5 col-lg-6">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="font-weight-bold text-dark mb-0 d-flex align-items-center">
                            <i class="i-File-Horizontal-Text mr-2 text-primary"></i>
                            Live Certificate Preview
                        </h6>
                        <span class="badge badge-success px-2 py-1 text-white small">
                            <i class="i-Eye mr-1"></i> 1:1 Print Layout
                        </span>
                    </div>

                    {{-- Desk Backdrop --}}
                    <div class="card-body p-3 p-md-4 preview-desk rounded-bottom">
                        {{-- Realistic A4 Paper Sheet --}}
                        <div class="paper-sheet shadow-lg" id="previewSheet">
                            <div class="paper-border-box" id="previewBorderBox">

                                {{-- Plain Paper Header (Hidden by default for letterhead) --}}
                                <div class="preview-header-area" id="previewHeaderArea" style="display: none;">
                                    <div class="text-center pb-2 mb-2 border-bottom">
                                        <div class="font-weight-bold" style="font-size: 15px; text-transform: uppercase;">Lokmanya Vidya Niketan</div>
                                        <div style="font-size: 10px; color: #555;">HIGHER SECONDARY SCHOOL &bull; INDORE (M.P.)</div>
                                    </div>
                                </div>

                                {{-- Pre-printed Letterhead Blank Space --}}
                                <div class="letterhead-space-indicator" id="previewBlankSpace" style="height: 110px;">
                                    <div class="space-label">
                                        <i class="i-Arrow-Up mr-1"></i> Top Blank Space for Pre-printed School Letterhead <i class="i-Arrow-Up ml-1"></i>
                                    </div>
                                </div>

                                {{-- Title --}}
                                <div class="cert-preview-title" id="prevTitle">
                                    CHARACTER - CERTIFICATE
                                </div>

                                {{-- Session --}}
                                <div class="cert-preview-session" id="prevSession">
                                    2025-26
                                </div>

                                {{-- Paragraph 1 --}}
                                <div class="cert-preview-paragraph" id="prevP1">
                                    This is to certify that Mast./Ms <u>Aryan Borkar</u> Son/ Daughter of Mr. <u>Sanjay Borkar</u> was a regular student of this institution. He/ She was very sincere in studies, took active part in various extra-curricular activities. His/ Her conduct was good, with positive attitude. His/Her date of birth according to school record is <u>14-07-2008</u> and Scholar No. is <u>6884</u>.
                                </div>

                                {{-- Paragraph 2 --}}
                                <div class="cert-preview-paragraph" id="prevP2">
                                    He/ She passed Senior Secondary School Certificate Examination 2026 from this school.
                                </div>

                                {{-- Paragraph 3 --}}
                                <div class="cert-preview-paragraph" id="prevP3">
                                    We wish him/ her bright future head.
                                </div>

                                {{-- Signature (Pushed to bottom) --}}
                                <div class="cert-preview-signature">
                                    <div class="preview-sig-space"></div>
                                    <div class="preview-sig-title">PRINCIPAL</div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    /* Desk Backdrop */
    .preview-desk {
        background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
        min-height: 560px;
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }

    /* Realistic Paper Sheet */
    .paper-sheet {
        background: #ffffff;
        width: 100%;
        max-width: 430px;
        min-height: 600px;
        padding: 14px; /* Exact equal margin on all 4 sides */
        box-shadow: 0 14px 28px rgba(0,0,0,0.35), 0 10px 10px rgba(0,0,0,0.22);
        font-family: "Times New Roman", Times, Georgia, serif;
        color: #111111;
        box-sizing: border-box;
        border-radius: 2px;
        position: relative;
    }

    /* Complete Page Border Box */
    .paper-border-box {
        border: 2px solid #111111;
        padding: 12px 14px 12px 14px;
        min-height: 572px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        box-sizing: border-box;
        position: relative;
    }

    /* Blank Space Indicator */
    .letterhead-space-indicator {
        border: 1px dashed #94a3b8;
        background-color: #f8fafc;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 22px;
        transition: height 0.2s ease;
    }

    .space-label {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
        font-size: 10px;
        color: #64748b;
        font-style: normal;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-align: center;
        padding: 6px;
    }

    /* Typography on Sheet */
    .cert-preview-title {
        text-align: center;
        font-size: 15.5px;
        font-weight: bold;
        font-style: italic;
        text-decoration: underline;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
        text-underline-offset: 3px;
    }

    .cert-preview-session {
        text-align: center;
        font-size: 14.5px;
        font-weight: bold;
        text-decoration: underline;
        margin-bottom: 24px;
        text-underline-offset: 3px;
    }

    .cert-preview-paragraph {
        font-size: 12.5px;
        font-style: italic;
        line-height: 1.78;
        text-align: justify;
        margin-bottom: 18px;
        letter-spacing: 0.1px;
    }

    .cert-preview-paragraph u {
        text-decoration: underline;
        font-weight: 500;
        text-underline-offset: 2.5px;
    }

    .cert-preview-signature {
        margin-top: auto;
        text-align: right;
        padding-right: 15px;
        padding-bottom: 8px;
    }

    .preview-sig-space {
        height: 25px;
    }

    .preview-sig-title {
        font-weight: bold;
        font-size: 13.5px;
        font-style: normal;
        letter-spacing: 0.8px;
        text-transform: uppercase;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const studentSelect  = document.getElementById('student_select');
        const classFilter    = document.getElementById('classFilter');
        const studentNameIn  = document.getElementById('student_name');
        const fatherNameIn   = document.getElementById('father_name');
        const dobIn          = document.getElementById('dob');
        const scholarNoIn    = document.getElementById('scholar_no');
        const pronounMode    = document.getElementById('pronoun_mode');
        const examNameIn     = document.getElementById('exam_name');
        const sessionYearIn  = document.getElementById('session_year');
        const certTitleIn    = document.getElementById('cert_title');
        const p1Textarea     = document.getElementById('paragraph_1');
        const p2Textarea     = document.getElementById('paragraph_2');
        const p3Input        = document.getElementById('paragraph_3');
        const blankHeightIn  = document.getElementById('blank_header_height');
        const includeBorder  = document.getElementById('include_border');
        const includeHeader  = document.getElementById('include_header');

        // Summary strip elements
        const infoStrip      = document.getElementById('studentInfoStrip');
        const infoName       = document.getElementById('infoName');
        const infoFather     = document.getElementById('infoFather');
        const infoDob        = document.getElementById('infoDob');
        const infoScholar    = document.getElementById('infoScholar');
        const infoClassSec   = document.getElementById('infoClassSec');

        // Preview elements
        const prevTitle      = document.getElementById('prevTitle');
        const prevSession    = document.getElementById('prevSession');
        const prevP1         = document.getElementById('prevP1');
        const prevP2         = document.getElementById('prevP2');
        const prevP3         = document.getElementById('prevP3');
        const prevSheet      = document.getElementById('previewSheet');
        const prevBlank      = document.getElementById('previewBlankSpace');
        const prevHeaderArea = document.getElementById('previewHeaderArea');

        // Class Filter with flexible class matching (handles '11', '011', 'Class 11')
        if (classFilter && studentSelect) {
            classFilter.addEventListener('change', function () {
                const selected = this.value.trim().toLowerCase();
                const selNumeric = selected.replace(/^0+/, '');

                Array.from(studentSelect.options).forEach(function (opt) {
                    if (!opt.value) return; // Search placeholder
                    const rawClass = (opt.getAttribute('data-class') || '').trim().toLowerCase();
                    const rawNumeric = rawClass.replace(/^0+/, '');

                    if (!selected || rawClass === selected || rawNumeric === selNumeric) {
                        opt.style.display = '';
                    } else {
                        opt.style.display = 'none';
                    }
                });

                // If currently selected option is now hidden, reset selection
                const currentOpt = studentSelect.options[studentSelect.selectedIndex];
                if (currentOpt && currentOpt.style.display === 'none') {
                    studentSelect.value = '';
                    $(studentSelect).trigger('change');
                }
            });
        }

        // Paragraph builders matching reference image exactly
        function generateP1Text(name, father, dob, scholarNo, mode) {
            let salutation = 'Mast./Ms';
            let relation   = 'Son/ Daughter of Mr.';
            let heShe      = 'He/ She';
            let hisHer     = 'His/ Her';
            let hisHerDob  = 'His/Her';

            if (mode === 'boy') {
                salutation = 'Mast.';
                relation   = 'Son of Mr.';
                heShe      = 'He';
                hisHer     = 'His';
                hisHerDob  = 'His';
            } else if (mode === 'girl') {
                salutation = 'Ms';
                relation   = 'Daughter of Mr.';
                heShe      = 'She';
                hisHer     = 'Her';
                hisHerDob  = 'Her';
            }

            const sNameStr = name ? `<u>${name}</u>` : '<u>____________________</u>';
            const fNameStr = father ? `<u>${father}</u>` : '<u>____________________</u>';
            const dobStr   = dob ? `<u>${dob}</u>` : '<u>____________</u>';
            const schStr   = scholarNo ? `<u>${scholarNo}</u>` : '<u>________</u>';

            return `This is to certify that ${salutation} ${sNameStr} ${relation} ${fNameStr} was a regular student of this institution. ${heShe} was very sincere in studies, took active part in various extra-curricular activities. ${hisHer} conduct was good, with positive attitude. ${hisHerDob} date of birth according to school record is ${dobStr} and Scholar No. is ${schStr}.`;
        }

        function generateP2Text(exam, mode) {
            let heShe = (mode === 'boy') ? 'He' : ((mode === 'girl') ? 'She' : 'He/ She');
            return `${heShe} passed ${exam} from this school.`;
        }

        function generateP3Text(mode) {
            let himHer = (mode === 'boy') ? 'him' : ((mode === 'girl') ? 'her' : 'him/ her');
            return `We wish ${himHer} bright future head.`;
        }

        // Update live preview sheet
        function updateLivePreview() {
            if (prevTitle) prevTitle.textContent = certTitleIn.value || 'CHARACTER - CERTIFICATE';
            if (prevSession) prevSession.textContent = sessionYearIn.value || '2025-26';
            if (prevP1) prevP1.innerHTML = p1Textarea.value;
            if (prevP2) prevP2.innerHTML = p2Textarea.value;
            if (prevP3) prevP3.innerHTML = p3Input.value;

            const prevBox = document.getElementById('previewBorderBox');
            if (prevBox) {
                prevBox.style.border = includeBorder.checked ? '2px solid #111111' : 'none';
            }

            const isHeader = includeHeader.checked;
            if (prevHeaderArea) prevHeaderArea.style.display = isHeader ? 'block' : 'none';
            if (prevBlank) {
                prevBlank.style.display = isHeader ? 'none' : 'flex';
                const heightMm = parseInt(blankHeightIn.value || 55, 10);
                prevBlank.style.height = Math.round(heightMm * 2) + 'px';
            }
        }

        // Reset to default
        function resetDefaults() {
            const name = studentNameIn.value.trim();
            const father = fatherNameIn.value.trim();
            const dob = dobIn.value.trim();
            const scholar = scholarNoIn.value.trim();
            const mode = pronounMode.value;
            const exam = examNameIn.value.trim() || 'Senior Secondary School Certificate Examination 2026';

            p1Textarea.value = generateP1Text(name, father, dob, scholar, mode);
            p2Textarea.value = generateP2Text(exam, mode);
            p3Input.value    = generateP3Text(mode);
            updateLivePreview();
        }

        // On selecting student from dropdown
        function handleStudentSelect(opt) {
            if (!opt || !opt.value) {
                if (infoStrip) infoStrip.style.display = 'none';
                return;
            }

            const name         = opt.getAttribute('data-name') || '';
            const father       = opt.getAttribute('data-father') || '';
            const dob          = opt.getAttribute('data-dob') || '';
            const scholarNo    = opt.value;
            const gender       = opt.getAttribute('data-gender') || 'male';
            const className    = opt.getAttribute('data-class') || '';
            const classDisplay = opt.getAttribute('data-class-display') || className;
            const secName      = opt.getAttribute('data-section') || '';

            studentNameIn.value = name;
            fatherNameIn.value  = father;
            dobIn.value         = dob;
            scholarNoIn.value   = scholarNo;

            // Update info banner
            if (infoStrip) {
                infoStrip.style.display = 'block';
                if (infoName) infoName.textContent = name || '-';
                if (infoFather) infoFather.textContent = father || '-';
                if (infoDob) infoDob.textContent = dob || '-';
                if (infoScholar) infoScholar.textContent = scholarNo || '-';
                if (infoClassSec) infoClassSec.textContent = classDisplay + (secName ? ' - Section ' + secName : '');
            }

            // Auto-suggest exam passed
            const cLower = className.toLowerCase();
            if (['10', '010', 'class 10', 'x'].includes(cLower)) {
                examNameIn.value = 'Secondary School Examination 2026';
            } else {
                examNameIn.value = 'Senior Secondary School Certificate Examination 2026';
            }

            resetDefaults();
        }

        if (studentSelect) {
            studentSelect.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                handleStudentSelect(opt);
            });
        }

        // Input change listeners
        [studentNameIn, fatherNameIn, dobIn, scholarNoIn, pronounMode, examNameIn].forEach(function (el) {
            if (el) {
                el.addEventListener('input', resetDefaults);
                el.addEventListener('change', resetDefaults);
            }
        });

        [p1Textarea, p2Textarea, p3Input, certTitleIn, sessionYearIn, blankHeightIn, includeBorder, includeHeader].forEach(function (el) {
            if (el) {
                el.addEventListener('input', updateLivePreview);
                el.addEventListener('change', updateLivePreview);
            }
        });

        const resetBtn = document.getElementById('resetTextBtn');
        if (resetBtn) resetBtn.addEventListener('click', resetDefaults);

        const previewBtn = document.getElementById('updatePreviewBtn');
        if (previewBtn) previewBtn.addEventListener('click', updateLivePreview);

        // Select2 integration if loaded by main layout
        if (window.jQuery && jQuery.fn.select2) {
            jQuery('#student_select').select2({
                placeholder: "Search by Name or Scholar No...",
                allowClear: true,
                width: '100%'
            }).on('change', function () {
                const opt = this.options[this.selectedIndex];
                handleStudentSelect(opt);
            });

            jQuery('#classFilter').select2({
                placeholder: "All Existing Classes",
                allowClear: true,
                width: '100%'
            }).on('change', function () {
                classFilter.dispatchEvent(new Event('change'));
            });
        }

        // Initial setup
        if (studentSelect && studentSelect.value) {
            const opt = studentSelect.options[studentSelect.selectedIndex];
            handleStudentSelect(opt);
        } else {
            resetDefaults();
        }
    });
</script>
@endsection
