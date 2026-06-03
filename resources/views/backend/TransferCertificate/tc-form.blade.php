@extends('backend.layouts.main')

@section('main-container')
@php
    $form = old('tc_form_data', $certificate->tc_form_data ?? []);
    $pick = function ($key, $default = '') use ($form) {
        return data_get($form, $key, $default);
    };
    $yesNo = ['' => '--Select--', 'YES' => 'YES', 'NO' => 'NO'];
    $resultOptions = ['' => '--Select--', 'PASSED' => 'PASSED', 'FAILED' => 'FAILED', 'COMPARTMENT' => 'COMPARTMENT', 'PROMOTED' => 'PROMOTED'];
    $failedOptions = ['' => '--Select--', 'N.A' => 'N.A', 'NO' => 'NO', 'ONCE' => 'ONCE', 'TWICE' => 'TWICE'];
@endphp

<style>
    .tc-compact-card .form-group { margin-bottom: 8px; }
    .tc-compact-card label { margin-bottom: 2px; font-size: 12px; font-weight: 600; color: #333; }
    .tc-compact-card .form-control { min-height: 32px; padding: 4px 8px; font-size: 13px; }
    .tc-grid { display: grid; grid-template-columns: repeat(3, minmax(220px, 1fr)); column-gap: 18px; row-gap: 2px; }
    .tc-section-title { grid-column: 1 / -1; margin: 12px 0 4px; color: #7074b3; font-size: 16px; font-weight: 700; border-bottom: 1px solid #e9e9ef; padding-bottom: 4px; }
    .tc-wide { grid-column: span 2; }
    .tc-actions { display: flex; gap: 8px; margin-top: 14px; }
    @media (max-width: 1100px) { .tc-grid { grid-template-columns: repeat(2, minmax(220px, 1fr)); } }
    @media (max-width: 760px) { .tc-grid { grid-template-columns: 1fr; } .tc-wide { grid-column: span 1; } }
</style>

<div class="main-content">
    <div class="breadcrumb">
        <h1>{{ $certificate ? 'Edit Transfer Certificate' : 'Create Transfer Certificate' }}</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card tc-compact-card">
        <div class="card-body">
            <form method="POST" action="{{ $certificate ? route('transfercertificate.update', $certificate->id) : route('transfercertificate.store') }}">
                @csrf

                <div class="tc-grid">
                    <div class="tc-section-title">Identification</div>

                    <div class="form-group">
                        <label>T.C. No. <span class="text-danger">*</span></label>
                        <input type="text" name="certificate_no" class="form-control" value="{{ old('certificate_no', $certificate->certificate_no ?? ($suggestedTcNo ?? '')) }}" required>
                    </div>

                    @if(!$certificate)
                        <div class="form-group">
                            <label>Name / Scholar Search <span class="text-danger">*</span></label>
                            <select name="student_id" id="student_id" class="form-control select2" data-placeholder="Search by name or scholar no." required>
                                <option value="">Search by name or scholar no.</option>
                                @foreach($students as $student)
                                    <option
                                        value="{{ $student->id }}"
                                        data-name="{{ $student->student_name }}"
                                        data-scholar="{{ $student->scholar_no }}"
                                        data-class="{{ $student->class_name }}"
                                        data-session="{{ $student->session_name }}"
                                    >
                                        {{ $student->student_name }} - {{ $student->scholar_no }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="student_id" value="{{ $certificate->student_id }}">
                        <div class="form-group">
                            <label>Name / Scholar</label>
                            <input type="text" class="form-control" value="{{ $certificate->student_name }} - {{ $certificate->scholar_no }}" readonly>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Roll No. <span class="text-danger">*</span></label>
                        <input type="text" name="tc_form_data[roll_no]" class="form-control" value="{{ $pick('roll_no') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="tc_form_data[name]" id="tc_name" class="form-control" value="{{ $pick('name', $certificate->student_name ?? '') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Scholar Serial No. <span class="text-danger">*</span></label>
                        <input type="text" name="tc_form_data[scholar_serial_no]" id="tc_scholar_serial_no" class="form-control" value="{{ $pick('scholar_serial_no', $certificate->scholar_no ?? '') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Academic Session</label>
                        <input type="text" name="session_name" id="tc_session_name" class="form-control" value="{{ old('session_name', $sessionName ?? $certificate->session_name ?? '') }}" readonly>
                    </div>

                    <div class="tc-section-title">Details</div>

                    <div class="form-group">
                        <label>Mother's Name</label>
                        <input type="text" name="tc_form_data[mother_name]" id="tc_mother_name" class="form-control" value="{{ $pick('mother_name') }}">
                    </div>
                    <div class="form-group">
                        <label>Father's Name</label>
                        <input type="text" name="tc_form_data[father_name]" id="tc_father_name" class="form-control" value="{{ $pick('father_name') }}">
                    </div>
                    <div class="form-group">
                        <label>Caste</label>
                        <input type="text" name="tc_form_data[caste]" id="tc_caste" class="form-control" value="{{ $pick('caste') }}">
                    </div>
                    <div class="form-group">
                        <label>Nationality</label>
                        <input type="text" name="tc_form_data[nationality]" id="tc_nationality" class="form-control" value="{{ $pick('nationality') }}">
                    </div>
                    <div class="form-group">
                        <label>Date of Admitted <span class="text-danger">*</span></label>
                        <input type="date" name="tc_form_data[date_of_admitted]" id="tc_date_of_admitted" class="form-control" value="{{ $pick('date_of_admitted') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Class To which Admitted</label>
                        <select name="tc_form_data[class_to_which_admitted]" id="tc_class_to_which_admitted" class="form-control">
                            <option value="">--Select--</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->class_name }}" @selected($pick('class_to_which_admitted') == $class->class_name)>{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Section</label>
                        <input type="text" name="tc_form_data[section]" id="tc_section" class="form-control" value="{{ $pick('section', $certificate->section_name ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label>Admission Session</label>
                        <input type="text" name="tc_form_data[admission_session]" id="tc_admission_session" class="form-control" value="{{ $pick('admission_session') }}">
                    </div>
                    <div class="form-group">
                        <label>Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="tc_form_data[date_of_birth]" id="tc_date_of_birth" class="form-control" value="{{ $pick('date_of_birth') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth (in words)</label>
                        <input type="text" name="tc_form_data[date_of_birth_words]" id="tc_date_of_birth_words" class="form-control" value="{{ $pick('date_of_birth_words') }}">
                    </div>
                    <div class="form-group">
                        <label>Class in which studying <span class="text-danger">*</span></label>
                        <select name="tc_form_data[class_studying]" id="tc_class_studying" class="form-control" required>
                            <option value="">--Select--</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->class_name }}" @selected($pick('class_studying', $certificate->class_name ?? '') == $class->class_name)>{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Last Examination Passed with Result</label>
                        <input type="text" name="tc_form_data[last_exam_result]" class="form-control" value="{{ $pick('last_exam_result') }}">
                    </div>
                    <div class="form-group">
                        <label>Result</label>
                        <select name="tc_form_data[result]" class="form-control">
                            @foreach($resultOptions as $value => $label)
                                <option value="{{ $value }}" @selected($pick('result') == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Whether Failed, once/twice in same class</label>
                        <select name="tc_form_data[failed_once_twice]" class="form-control">
                            @foreach($failedOptions as $value => $label)
                                <option value="{{ $value }}" @selected($pick('failed_once_twice') == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Year of Passing</label>
                        <input type="text" name="tc_form_data[year_of_passing]" class="form-control" value="{{ $pick('year_of_passing') }}">
                    </div>
                    <div class="form-group">
                        <label>Qualified for promotion</label>
                        <select name="tc_form_data[promotion_qualified]" class="form-control">
                            @foreach($yesNo as $value => $label)
                                <option value="{{ $value }}" @selected($pick('promotion_qualified') == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>If so, to which class (figures)</label>
                        <input type="text" name="tc_form_data[promotion_class_figures]" class="form-control" value="{{ $pick('promotion_class_figures') }}">
                    </div>
                    <div class="form-group tc-wide">
                        <label>Subjects Studied</label>
                        <input type="text" name="tc_form_data[subjects_studied]" id="tc_subjects_studied" class="form-control" value="{{ $pick('subjects_studied') }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>School Dues Paid Upto</label>
                        <input type="date" name="tc_form_data[school_dues_paid_upto]" class="form-control" value="{{ $pick('school_dues_paid_upto') }}">
                    </div>
                    <div class="form-group">
                        <label>Fees Concession Availed</label>
                        <select name="tc_form_data[fee_concession]" class="form-control">
                            @foreach($yesNo as $value => $label)
                                <option value="{{ $value }}" @selected($pick('fee_concession') == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>School Dues Paid Upto (In Words)</label>
                        <input type="text" name="tc_form_data[school_dues_paid_upto_words]" class="form-control" value="{{ $pick('school_dues_paid_upto_words') }}">
                    </div>
                    <div class="form-group">
                        <label>Class For Show Attendance</label>
                        <select name="tc_form_data[class_for_attendance]" class="form-control">
                            <option value="">--Select--</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->class_name }}" @selected($pick('class_for_attendance') == $class->class_name)>{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>NCC / Scout / Guide</label>
                        <input type="text" name="tc_form_data[ncc_scout_guide]" class="form-control" value="{{ $pick('ncc_scout_guide') }}">
                    </div>
                    <div class="form-group">
                        <label>Working days present</label>
                        <input type="number" step="0.01" name="tc_form_data[working_days_present]" id="tc_working_days_present" class="form-control" value="{{ $pick('working_days_present') }}">
                    </div>
                    <div class="form-group">
                        <label>Total Working Days</label>
                        <input type="number" name="tc_form_data[total_working_days]" id="tc_total_working_days" class="form-control" value="{{ $pick('total_working_days') }}">
                    </div>
                    <div class="form-group tc-wide">
                        <label>Games / extra-curricular activities</label>
                        <textarea name="tc_form_data[games_activities]" class="form-control" rows="2">{{ $pick('games_activities') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>General Conduct</label>
                        <input type="text" name="conduct" class="form-control" value="{{ old('conduct', $certificate->conduct ?? 'Good') }}">
                    </div>
                    <div class="form-group">
                        <label>Application Date</label>
                        <input type="date" name="tc_form_data[application_date]" class="form-control" value="{{ $pick('application_date') }}">
                    </div>
                    <div class="form-group">
                        <label>Date of issue of Certificate <span class="text-danger">*</span></label>
                        <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', optional($certificate?->issue_date)->format('Y-m-d') ?? now()->toDateString()) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Leaving Date</label>
                        <input type="date" name="leaving_date" class="form-control" value="{{ old('leaving_date', optional($certificate?->leaving_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group tc-wide">
                        <label>Reason for Leaving</label>
                        <textarea name="reason_for_leaving" class="form-control" rows="2">{{ old('reason_for_leaving', $certificate->reason_for_leaving ?? '') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Remarks If Any</label>
                        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $certificate->remarks ?? '') }}</textarea>
                    </div>
                </div>

                <div class="tc-actions">
                    <button type="submit" class="btn btn-primary">{{ $certificate ? 'Update' : 'Generate T.C.' }}</button>
                    <a href="{{ route('transfercertificate.index') }}" class="btn btn-light">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const studentSelect = document.getElementById('student_id');
const detailsUrlTemplate = @json(route('transfercertificate.student-details', ['id' => '__ID__']));

function fillValue(id, value, overwrite = true) {
    const input = document.getElementById(id);
    if (!input || value === null || value === undefined) return;
    if (overwrite || !input.value) input.value = value;
}

function fillFromSelectedOption() {
    if (!studentSelect || !studentSelect.value) return;
    const selected = studentSelect.options[studentSelect.selectedIndex];
    fillValue('tc_name', selected.dataset.name || '');
    fillValue('tc_scholar_serial_no', selected.dataset.scholar || '');
    fillValue('tc_session_name', selected.dataset.session || '');
    fillValue('tc_class_studying', selected.dataset.class || '');
}

function loadStudentDetails() {
    if (!studentSelect || !studentSelect.value) return;
    fillFromSelectedOption();

    const selectedClass = document.getElementById('tc_class_studying')?.value || '';
    const url = new URL(detailsUrlTemplate.replace('__ID__', studentSelect.value), window.location.origin);
    if (selectedClass) url.searchParams.set('class_name', selectedClass);

    fetch(url.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => {
            if (!response.ok) throw new Error('Unable to load student details');
            return response.json();
        })
        .then(data => {
            fillValue('tc_name', data.name);
            fillValue('tc_scholar_serial_no', data.scholar_serial_no);
            fillValue('tc_session_name', data.session_name);
            fillValue('tc_mother_name', data.mother_name);
            fillValue('tc_father_name', data.father_name);
            fillValue('tc_caste', data.caste);
            fillValue('tc_nationality', data.nationality || 'Indian');
            fillValue('tc_date_of_admitted', data.date_of_admitted);
            fillValue('tc_class_to_which_admitted', data.class_to_which_admitted);
            fillValue('tc_section', data.section_name);
            fillValue('tc_admission_session', data.admission_session);
            fillValue('tc_date_of_birth', data.date_of_birth);
            fillValue('tc_date_of_birth_words', data.date_of_birth_words || dateInWords(data.date_of_birth));
            fillValue('tc_class_studying', data.class_name);
            fillValue('tc_subjects_studied', data.subjects_studied);
            fillValue('tc_working_days_present', data.working_days_present);
            fillValue('tc_total_working_days', data.total_working_days);
        })
        .catch(error => {
            console.error(error);
        });
}

document.addEventListener('DOMContentLoaded', function () {
    studentSelect?.addEventListener('change', loadStudentDetails);
    document.getElementById('tc_class_studying')?.addEventListener('change', loadStudentDetails);
    document.getElementById('tc_date_of_birth')?.addEventListener('change', function () {
        fillValue('tc_date_of_birth_words', dateInWords(this.value));
    });

    if (window.jQuery) {
        window.jQuery('#student_id').on('select2:select change', loadStudentDetails);
    }
});

function dateInWords(dateValue) {
    if (!dateValue) return '';
    const date = new Date(dateValue + 'T00:00:00');
    if (Number.isNaN(date.getTime())) return '';

    const ordinalWords = {
        1: 'First', 2: 'Second', 3: 'Third', 4: 'Fourth', 5: 'Fifth',
        6: 'Sixth', 7: 'Seventh', 8: 'Eighth', 9: 'Ninth', 10: 'Tenth',
        11: 'Eleventh', 12: 'Twelfth', 13: 'Thirteenth', 14: 'Fourteenth',
        15: 'Fifteenth', 16: 'Sixteenth', 17: 'Seventeenth', 18: 'Eighteenth',
        19: 'Nineteenth', 20: 'Twentieth', 21: 'Twenty First', 22: 'Twenty Second',
        23: 'Twenty Third', 24: 'Twenty Fourth', 25: 'Twenty Fifth',
        26: 'Twenty Sixth', 27: 'Twenty Seventh', 28: 'Twenty Eighth',
        29: 'Twenty Ninth', 30: 'Thirtieth', 31: 'Thirty First'
    };

    return `${ordinalWords[date.getDate()]} ${date.toLocaleString('en-US', { month: 'long' })} ${numberToWords(date.getFullYear())}`.trim();
}

function numberToWords(number) {
    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    if (number < 20) return ones[number];
    if (number < 100) return `${tens[Math.floor(number / 10)]} ${ones[number % 10]}`.trim();
    if (number < 1000) return `${ones[Math.floor(number / 100)]} Hundred ${numberToWords(number % 100)}`.trim();
    if (number < 100000) return `${numberToWords(Math.floor(number / 1000))} Thousand ${numberToWords(number % 1000)}`.trim();
    return String(number);
}

</script>
@endsection
