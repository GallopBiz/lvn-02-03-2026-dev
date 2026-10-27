@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4">
    <div class="breadcrumb">
        <h2>Class 11 & 12 Marksheet Management</h2>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(!empty($errors) && $errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif


    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Generate / Print Senior Marksheet (XI - XII)</h4>

            <form method="POST" action="{{ route('academic.senior-marksheets.generate') }}" id="seniorMarksheetGenerateForm">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-3 form-group mb-3">
                        <label class="font-weight-bold">Exam (Optional / Filter)</label>
                        <select class="form-control" name="exam_id" id="print_exam_id">
                            <option value="">All Exams / Cumulative (PT1 + HY + PT2 + Annual)</option>
                            @foreach($groupedExams as $gExam)
                                <option value="{{ $gExam['id'] }}" {{ (string) $selectedExamId === (string) $gExam['id'] || in_array((int)$selectedExamId, $gExam['all_exam_ids']) ? 'selected' : '' }}>
                                    {{ $gExam['display_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group mb-3">
                        <label class="font-weight-bold">Class <span class="text-danger">*</span></label>
                        <select class="form-control" name="class_id" id="print_class_id" required>
                            <option value="">Select Class (11 or 12)</option>
                            @foreach($availableClasses as $class)
                                <option value="{{ $class['class_id'] }}" data-class-name="{{ $class['class_name'] }}" {{ (string) $selectedClassId === (string) $class['class_id'] ? 'selected' : '' }}>
                                    {{ \App\Academic\AcademicHelper::toRoman($class['class_name']) }}
                                </option>
                            @endforeach

                        </select>
                    </div>
                    <div class="col-md-2 form-group mb-3">
                        <label class="font-weight-bold">Section</label>
                        <select class="form-control" name="section_name" id="print_section_name">
                            <option value="">All Sections</option>
                            @if(!empty($availableSections))
                                @foreach($availableSections as $sec)
                                    <option value="{{ $sec }}" {{ (string) ($selectedSectionName ?? '') === (string) $sec ? 'selected' : '' }}>
                                        {{ $sec }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-3">
                        <label class="font-weight-bold">Student</label>
                        <select class="form-control" name="student_id" id="print_student_id">
                            <option value="">All students in selected class{{ !empty($students) && $students->count() ? ' (' . $students->count() . ' students)' : '' }}</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">
                                    {{ $student->student_name }}@if(!empty($student->section_name)) ({{ $student->section_name }})@endif{{ !empty($student->scholar_no) ? ' - '.$student->scholar_no : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group mb-3">
                        <label class="font-weight-bold">Marksheet Print Date</label>
                        <input type="date" class="form-control" name="print_date" id="print_date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-12 d-flex align-items-center mt-1">
                        <button class="btn btn-success mr-2" type="submit">
                            <i class="i-File-Clipboard-Text--Image mr-1"></i> Generate & Save
                        </button>
                        <button class="btn btn-outline-primary" type="button" onclick="openSeniorMarksheetPreview()">
                            <i class="i-Eye mr-1"></i> Print Preview
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Generated Class 11-12 Marksheets Table --}}
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Generated Class 11 & 12 Marksheets</h4>
            <form method="GET" action="{{ route('academic.senior-marksheets.index') }}" class="row align-items-end mb-3" id="generatedSearchForm">
                <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                <input type="hidden" name="section_name" value="{{ $selectedSectionName }}">
                <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">
                <input type="hidden" name="per_page" id="generated_per_page_input" value="{{ $perPage ?? 20 }}">
                <div class="col-md-3 form-group mb-md-0">
                    <label>Search Generated Marksheets</label>
                    <input class="form-control" name="generated_search" value="{{ $generatedSearch }}" placeholder="Search student, scholar no, exam or session">
                </div>
                <div class="col-md-3 form-group mb-md-0">
                    <label>Class Filter</label>
                    <select class="form-control" name="generated_class_id">
                        <option value="">All Classes (XI - XII)</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ (string) $generatedClassId === (string) $class->id ? 'selected' : '' }}>
                                {{ \App\Academic\AcademicHelper::toRoman($class->class_name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group mb-md-0">
                    <label>Exam Filter</label>
                    <select class="form-control" name="generated_exam_id">
                        <option value="">All Exams</option>
                        @foreach($groupedExams as $gExam)
                            <option value="{{ $gExam['id'] }}" {{ (string) $generatedExamId === (string) $gExam['id'] || in_array((int)$generatedExamId, $gExam['all_exam_ids']) ? 'selected' : '' }}>
                                {{ $gExam['display_name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit">Search</button>
                    <a class="btn btn-outline-secondary" href="{{ route('academic.senior-marksheets.index', array_filter(['class_id' => $selectedClassId, 'exam_id' => $selectedExamId, 'per_page' => $perPage ?? 20])) }}">Clear</a>
                </div>
            </form>

            <form method="POST" action="{{ route('academic.senior-marksheets.generated.bulk-print') }}" id="generatedBulkForm">
                @csrf
                <input type="hidden" name="per_page" value="{{ $perPage ?? 20 }}">
                <input type="hidden" name="generated_search" value="{{ $generatedSearch }}">
                <input type="hidden" name="generated_class_id" value="{{ $generatedClassId }}">
                <input type="hidden" name="generated_exam_id" value="{{ $generatedExamId }}">
                <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                <input type="hidden" name="section_name" value="{{ $selectedSectionName }}">
                <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2 mb-2 mb-md-0">
                        <label class="mb-0 text-muted font-weight-normal mr-1">Show</label>
                        <select class="form-control form-control-sm d-inline-block" style="width: 75px;" onchange="changeGeneratedPerPage(this.value)">
                            @foreach([10, 20, 50, 100, 200, 500] as $size)
                                <option value="{{ $size }}" {{ (int) ($perPage ?? 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <label class="mb-0 text-muted font-weight-normal ml-1">entries</label>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-primary" type="button" onclick="submitGeneratedBulk('print')">Bulk Print <span id="selected_print_count"></span></button>
                        <button class="btn btn-sm btn-danger" type="button" onclick="submitGeneratedBulk('delete')">Bulk Delete <span id="selected_delete_count"></span></button>
                        <span class="text-muted ml-2">Showing {{ $generatedMarksheets->firstItem() ?? 0 }} to {{ $generatedMarksheets->lastItem() ?? 0 }} of {{ $generatedMarksheets->total() }} entries</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th style="width: 34px;">
                                    <input type="checkbox" id="select_all_generated" onclick="toggleGeneratedSelection(this)">
                                </th>
                                <th>Student</th>
                                <th>Scholar No</th>
                                <th>Class</th>
                                <th>Exam</th>
                                <th>Session</th>
                                <th>Generated On</th>
                                <th>Print Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($generatedMarksheets as $marksheet)
                                @php($snapshot = $marksheet->snapshot ?: [])
                                @php($printData = $snapshot['print_data'] ?? [])
                                @php($studentMeta = $printData['student_meta'] ?? [])
                                <tr>
                                    <td>
                                        <input type="checkbox" class="generated-check" name="marksheet_ids[]" value="{{ $marksheet->id }}">
                                    </td>
                                    <td>{{ $snapshot['student_name'] ?? ('Student #'.$marksheet->student_id) }}</td>
                                    <td>{{ $snapshot['scholar_no'] ?? ($studentMeta['scholar_no'] ?? '-') }}</td>
                                    <td>{{ \App\Academic\AcademicHelper::toRoman($snapshot['class_name'] ?? ('Class #'.$marksheet->class_id)) }}</td>
                                    <td>{{ $snapshot['exam_name'] ?? 'Annual Examination' }}</td>
                                    <td>{{ $marksheet->session_year ?: '-' }}</td>
                                    <td>{{ optional($marksheet->created_at)->format('d M Y h:i A') }}</td>
                                    <td>{{ $snapshot['print_date'] ?? ($printData['date'] ?? '-') }}</td>
                                    <td>
                                        <a class="btn btn-sm btn-primary" target="_blank" href="{{ route('academic.senior-marksheets.generated.print', $marksheet) }}">Print</a>
                                        <button class="btn btn-sm btn-danger" type="button" onclick="deleteGeneratedSingle('{{ route('academic.senior-marksheets.generated.delete', $marksheet) }}')">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center">No Class 11-12 marksheets generated yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <div class="text-muted small">
                    Showing {{ $generatedMarksheets->firstItem() ?? 0 }} to {{ $generatedMarksheets->lastItem() ?? 0 }} of {{ $generatedMarksheets->total() }} entries
                </div>
                <div>
                    {{ $generatedMarksheets->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var sectionsByClass = @json($sectionsByClass ?? []);
    var studentsDataUrl = '{{ route('academic.senior-marksheets.students-data') }}';

    var examSelect = document.getElementById('print_exam_id');
    var classSelect = document.getElementById('print_class_id');
    var sectionSelect = document.getElementById('print_section_name');
    var studentSelect = document.getElementById('print_student_id');

    function getSelectedClassName() {
        if (!classSelect || classSelect.selectedIndex < 0) return '';
        var opt = classSelect.options[classSelect.selectedIndex];
        if (!opt) return '';
        return opt.getAttribute('data-class-name') || opt.dataset.className || opt.value || opt.text.trim();
    }

    function renderSections(preservedSection) {
        if (!sectionSelect) return;
        var className = getSelectedClassName();
        var optText = classSelect && classSelect.selectedIndex >= 0 ? classSelect.options[classSelect.selectedIndex].text.trim() : '';
        var classVal = classSelect ? classSelect.value : '';

        sectionSelect.innerHTML = '<option value="">All Sections</option>';

        var secList = sectionsByClass[className]
            || sectionsByClass[className.replace(/^0+/, '')]
            || sectionsByClass[optText]
            || sectionsByClass[classVal]
            || [];

        secList.forEach(function (sec) {
            var opt = document.createElement('option');
            opt.value = sec;
            opt.textContent = sec;
            if (preservedSection && String(preservedSection).toLowerCase() === String(sec).toLowerCase()) {
                opt.selected = true;
            }
            sectionSelect.appendChild(opt);
        });
    }

    function loadStudents(classId, className, sectionName) {
        if (!studentSelect) return;
        studentSelect.innerHTML = '<option value="">Loading students...</option>';

        var params = new URLSearchParams();
        if (classId) params.set('class_id', classId);
        if (className) params.set('class_name', className);
        if (sectionName) params.set('section_name', sectionName);
        if (examSelect && examSelect.value) params.set('exam_id', examSelect.value);

        fetch(studentsDataUrl + '?' + params.toString())
            .then(function (res) { return res.json(); })
            .then(function (data) {
                studentSelect.innerHTML = '<option value="">All students in selected class (' + (data.students ? data.students.length : 0) + ' students)</option>';
                (data.students || []).forEach(function (s) {
                    var opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.student_name + (s.section_name ? ' (' + s.section_name + ')' : '') + (s.scholar_no ? ' - ' + s.scholar_no : '');
                    studentSelect.appendChild(opt);
                });
            })
            .catch(function () {
                studentSelect.innerHTML = '<option value="">All students in selected class</option>';
            });
    }

    if (classSelect) {
        classSelect.addEventListener('change', function () {
            renderSections('');
            loadStudents(classSelect.value, getSelectedClassName(), '');
        });

        // Initialize sections on page load if class already chosen
        if (classSelect.value && sectionSelect && sectionSelect.options.length <= 1) {
            renderSections('{{ $selectedSectionName }}');
        }
    }

    if (sectionSelect) {
        sectionSelect.addEventListener('change', function () {
            loadStudents(classSelect.value, getSelectedClassName(), sectionSelect.value);
        });
    }


    function openSeniorMarksheetPreview() {
        var classVal = classSelect ? classSelect.value : '';
        if (!classVal) {
            alert('Please select Class 11 or 12.');
            if (classSelect) classSelect.focus();
            return;
        }

        var params = new URLSearchParams();
        params.set('class_id', classVal);

        var sectionVal = sectionSelect ? sectionSelect.value : '';
        if (sectionVal) {
            params.set('section_name', sectionVal);
        }

        var examVal = examSelect ? examSelect.value : '';
        if (examVal) {
            params.set('exam_id', examVal);
        }

        var studentVal = studentSelect ? studentSelect.value : '';
        if (studentVal) {
            params.set('student_id', studentVal);
        }

        var printDateEl = document.getElementById('print_date');
        if (printDateEl && printDateEl.value) {
            params.set('print_date', printDateEl.value);
        }



        window.open('{{ route('academic.senior-marksheets.print') }}?' + params.toString(), '_blank');
    }

    function changeGeneratedPerPage(val) {
        var input = document.getElementById('generated_per_page_input');
        if (input) input.value = val;
        var searchForm = document.getElementById('generatedSearchForm');
        if (searchForm) searchForm.submit();
    }

    function selectedGeneratedCount() {
        return document.querySelectorAll('.generated-check:checked').length;
    }

    function updateGeneratedSelectionUI() {
        var count = selectedGeneratedCount();
        var printCountEl = document.getElementById('selected_print_count');
        var deleteCountEl = document.getElementById('selected_delete_count');
        if (printCountEl) printCountEl.textContent = count > 0 ? '(' + count + ')' : '';
        if (deleteCountEl) deleteCountEl.textContent = count > 0 ? '(' + count + ')' : '';

        var allCheckboxes = document.querySelectorAll('.generated-check');
        var selectAllEl = document.getElementById('select_all_generated');
        if (selectAllEl && allCheckboxes.length > 0) {
            selectAllEl.checked = (count === allCheckboxes.length);
        }
    }

    function toggleGeneratedSelection(source) {
        document.querySelectorAll('.generated-check').forEach(function (checkbox) {
            checkbox.checked = source.checked;
        });
        updateGeneratedSelectionUI();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.generated-check').forEach(function (checkbox) {
            checkbox.addEventListener('change', updateGeneratedSelectionUI);
        });
        updateGeneratedSelectionUI();
    });

    function submitGeneratedBulk(action) {
        var count = selectedGeneratedCount();
        if (count === 0) {
            alert('Please select at least one marksheet.');
            return;
        }

        var form = document.getElementById('generatedBulkForm');
        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete ' + count + ' selected generated marksheet(s)?')) {
                return;
            }
            form.action = '{{ route('academic.senior-marksheets.generated.bulk-delete') }}';
            form.target = '';
            form.submit();
        } else {
            var ids = Array.prototype.map.call(document.querySelectorAll('.generated-check:checked'), function (checkbox) {
                return checkbox.value;
            }).join(',');
            var bulkPrintUrl = '{{ route('academic.senior-marksheets.generated.bulk-print-page', ['marksheets' => '__IDS__']) }}'.replace('__IDS__', ids);
            window.open(bulkPrintUrl, '_blank');
        }
    }

    function deleteGeneratedSingle(action) {
        if (!confirm('Delete this generated marksheet?')) {
            return;
        }
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        form.innerHTML = '@csrf @method('DELETE')';
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endsection
