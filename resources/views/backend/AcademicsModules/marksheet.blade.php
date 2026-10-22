@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4">
    <div class="breadcrumb">
        <h2>Marksheet Management</h2>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row">
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">Generate / Print Marksheet</h4>
                    <div id="marks_missing_alert" class="alert alert-warning mb-3" style="{{ !empty($marksMissing) ? '' : 'display: none;' }}">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="i-Warning-Window mr-2 font-weight-bold" style="font-size: 1.1rem;"></i>
                                <strong>Marks Not Entered:</strong> Marks have not been entered yet for this class / exam.
                            </div>
                            <a href="{{ url('marks') }}" target="_blank" class="btn btn-sm btn-warning font-weight-bold text-dark ml-2 text-nowrap">
                                <i class="i-Pen-4 mr-1"></i> Enter Marks Here
                            </a>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('academic.marksheets.generate') }}" id="marksheetGenerateForm">
                        @csrf
                        <div class="form-group">
                            <label>Exam</label>
                            <select class="form-control" name="exam_id" id="print_exam_id">
                                <option value="">Select exam</option>
                                @foreach($groupedExams as $gExam)
                                    <option value="{{ $gExam['id'] }}" {{ (string) $selectedExamId === (string) $gExam['id'] || in_array((int)$selectedExamId, $gExam['all_exam_ids']) ? 'selected' : '' }}>
                                        {{ $gExam['display_name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="resolved_exam_id" id="print_resolved_exam_id" value="{{ $resolvedExamId ?? $selectedExamId }}">
                        </div>
                        <div class="form-group">
                            <label>Class</label>
                            <select class="form-control" name="class_id" id="print_class_id">
                                <option value="">{{ !empty($availableClasses) ? 'Select class' : 'Select exam first' }}</option>
                                @foreach($availableClasses as $class)
                                    <option value="{{ $class['class_id'] }}" data-exam-id="{{ $class['exam_id'] }}" {{ (string) $selectedClassId === (string) $class['class_id'] ? 'selected' : '' }}>
                                        {{ $class['class_name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Section</label>
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
                        <div class="form-group">
                            <label>Student</label>
                            <select class="form-control" name="student_id" id="print_student_id">
                                <option value="">All students in selected class{{ !empty($students) && $students->count() ? ' (' . $students->count() . ' students)' : '' }}</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">
                                        {{ $student->student_name }}@if(!empty($student->section_name)) ({{ $student->section_name }})@endif{{ !empty($student->scholar_no) ? ' - '.$student->scholar_no : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-success" type="submit">Generate & Save</button>
                        <button class="btn btn-outline-primary" type="button" onclick="openMarksheetPreview()">Print Preview</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Generated Marksheets</h4>
            <form method="GET" action="{{ route('marksheet') }}" class="row align-items-end mb-3" id="generatedSearchForm">
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
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ (string) $generatedClassId === (string) $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }}
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
                    <a class="btn btn-outline-secondary" href="{{ route('marksheet', array_filter(['class_id' => $selectedClassId, 'exam_id' => $selectedExamId, 'per_page' => $perPage ?? 20])) }}">Clear</a>
                </div>
            </form>

            <form method="POST" action="{{ route('academic.marksheets.generated.bulk-print') }}" id="generatedBulkForm">
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
                                    <td>{{ $snapshot['class_name'] ?? ('Class #'.$marksheet->class_id) }}</td>
                                    <td>{{ $snapshot['exam_name'] ?? ($marksheet->exam_id ? 'Exam #'.$marksheet->exam_id : '-') }}</td>
                                    <td>{{ $marksheet->session_year ?: '-' }}</td>
                                    <td>{{ optional($marksheet->created_at)->format('d M Y h:i A') }}</td>
                                    <td>
                                        <a class="btn btn-sm btn-primary" target="_blank" href="{{ route('marksheet.generated.print', $marksheet) }}">Print</a>
                                        <button class="btn btn-sm btn-danger" type="button" onclick="deleteGeneratedSingle('{{ route('academic.marksheets.generated.delete', $marksheet) }}')">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center">No marksheets generated yet.</td></tr>
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
    var groupedExams = @json($groupedExams);
    var allClasses = @json($classes);
    var sectionsByClass = @json($sectionsByClass);
    var studentsDataUrl = '{{ route('academic.marksheets.students-data') }}';

    var examSelect = document.getElementById('print_exam_id');
    var classSelect = document.getElementById('print_class_id');
    var sectionSelect = document.getElementById('print_section_name');
    var studentSelect = document.getElementById('print_student_id');
    var resolvedExamInput = document.getElementById('print_resolved_exam_id');

    function getSelectedExamData(examId) {
        if (!examId) return null;
        return groupedExams.find(function (g) {
            return String(g.id) === String(examId) ||
                (g.all_exam_ids && g.all_exam_ids.map(String).indexOf(String(examId)) !== -1);
        });
    }

    function renderSections(preserveSection) {
        if (!sectionSelect) return;
        var selectedOption = classSelect && classSelect.selectedIndex >= 0 ? classSelect.options[classSelect.selectedIndex] : null;
        var selectedClassName = selectedOption ? selectedOption.textContent.trim() : '';

        var currentVal = preserveSection !== undefined ? preserveSection : (sectionSelect.value || '');

        var sections = [];
        if (selectedClassName) {
            sections = sectionsByClass[selectedClassName] || sectionsByClass[selectedClassName.replace(/^0+/, '')] || [];
        }

        sectionSelect.innerHTML = '<option value="">All Sections</option>';
        var matched = false;
        sections.forEach(function (sec) {
            var opt = document.createElement('option');
            opt.value = sec;
            opt.textContent = sec;
            if (currentVal && String(sec).toLowerCase() === String(currentVal).toLowerCase()) {
                opt.selected = true;
                matched = true;
            }
            sectionSelect.appendChild(opt);
        });

        if (currentVal && !matched && preserveSection === undefined) {
            sectionSelect.value = '';
        }
    }

    function onExamChange(isInitial) {
        var selectedExamId = examSelect ? examSelect.value : '';
        if (!selectedExamId) {
            if (classSelect) {
                classSelect.innerHTML = '<option value="">Select exam first</option>';
                classSelect.value = '';
            }
            if (sectionSelect) {
                sectionSelect.innerHTML = '<option value="">All Sections</option>';
                sectionSelect.value = '';
            }
            if (studentSelect) {
                studentSelect.innerHTML = '<option value="">All students in selected class</option>';
                studentSelect.value = '';
            }
            if (resolvedExamInput) resolvedExamInput.value = '';
            var alertEl = document.getElementById('marks_missing_alert');
            if (alertEl) alertEl.style.display = 'none';
            return;
        }

        var selectedExam = getSelectedExamData(selectedExamId);
        if (!selectedExam) return;

        var previouslySelectedClass = classSelect ? classSelect.value : '';
        var classes = selectedExam.classes || [];

        if (classSelect) {
            classSelect.innerHTML = '<option value="">Select class</option>';

            var matchingClassItem = null;
            classes.forEach(function (c) {
                var opt = document.createElement('option');
                opt.value = c.class_id;
                opt.textContent = c.class_name;
                opt.dataset.examId = c.exam_id;
                if (previouslySelectedClass && String(c.class_id) === String(previouslySelectedClass)) {
                    opt.selected = true;
                    matchingClassItem = c;
                }
                classSelect.appendChild(opt);
            });

            if (previouslySelectedClass && !matchingClassItem && !isInitial) {
                classSelect.value = '';
                if (sectionSelect) {
                    sectionSelect.innerHTML = '<option value="">All Sections</option>';
                    sectionSelect.value = '';
                }
                if (studentSelect) {
                    studentSelect.innerHTML = '<option value="">All students in selected class</option>';
                    studentSelect.value = '';
                }
                if (resolvedExamInput) resolvedExamInput.value = '';
                var alertEl = document.getElementById('marks_missing_alert');
                if (alertEl) alertEl.style.display = 'none';
            } else if (matchingClassItem) {
                if (resolvedExamInput) resolvedExamInput.value = matchingClassItem.exam_id;
                renderSections(sectionSelect ? sectionSelect.value : '');
                loadStudents(classSelect.value, matchingClassItem.class_name, sectionSelect ? sectionSelect.value : '');
            }
        }
    }

    function onClassChange() {
        var selectedClassId = classSelect ? classSelect.value : '';
        if (!selectedClassId) {
            if (sectionSelect) {
                sectionSelect.innerHTML = '<option value="">All Sections</option>';
                sectionSelect.value = '';
            }
            if (studentSelect) {
                studentSelect.innerHTML = '<option value="">All students in selected class</option>';
                studentSelect.value = '';
            }
            if (resolvedExamInput && examSelect) resolvedExamInput.value = examSelect.value;
            var alertEl = document.getElementById('marks_missing_alert');
            if (alertEl) alertEl.style.display = 'none';
            return;
        }

        var selectedOption = classSelect.options[classSelect.selectedIndex];
        var specificExamId = selectedOption ? selectedOption.getAttribute('data-exam-id') : null;
        var selectedClassName = selectedOption ? selectedOption.textContent.trim() : '';
        if (resolvedExamInput) {
            resolvedExamInput.value = specificExamId || (examSelect ? examSelect.value : '');
        }

        renderSections('');
        loadStudents(selectedClassId, selectedClassName, '');
    }

    function onSectionChange() {
        var selectedClassId = classSelect ? classSelect.value : '';
        var selectedOption = classSelect && classSelect.selectedIndex >= 0 ? classSelect.options[classSelect.selectedIndex] : null;
        var selectedClassName = selectedOption ? selectedOption.textContent.trim() : '';
        var selectedSection = sectionSelect ? sectionSelect.value : '';

        loadStudents(selectedClassId, selectedClassName, selectedSection);
    }

    function loadStudents(classId, className, sectionName) {
        if (!studentSelect) return;
        studentSelect.innerHTML = '<option value="">Loading students...</option>';

        if (!classId && !className) {
            studentSelect.innerHTML = '<option value="">All students in selected class</option>';
            var alertEl = document.getElementById('marks_missing_alert');
            if (alertEl) alertEl.style.display = 'none';
            return;
        }

        var url = new URL(studentsDataUrl, window.location.origin);
        if (classId) url.searchParams.set('class_id', classId);
        if (className) url.searchParams.set('class_name', className);
        if (sectionName) url.searchParams.set('section_name', sectionName);
        var examVal = (resolvedExamInput && resolvedExamInput.value) ? resolvedExamInput.value : (examSelect ? examSelect.value : '');
        if (examVal) url.searchParams.set('exam_id', examVal);

        fetch(url.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (res) { return res.json(); })
        .then(function (payload) {
            var students = Array.isArray(payload) ? payload : (payload.students || []);
            var hasMarks = Array.isArray(payload) ? true : !!payload.has_marks;

            var alertEl = document.getElementById('marks_missing_alert');
            if (alertEl) {
                alertEl.style.display = (!hasMarks && classId) ? '' : 'none';
            }

            var placeholderText = sectionName
                ? ('All students in selected section' + (students.length ? ' (' + students.length + ' students)' : ''))
                : ('All students in selected class' + (students.length ? ' (' + students.length + ' students)' : ''));
            studentSelect.innerHTML = '<option value="">' + placeholderText + '</option>';
            students.forEach(function (st) {
                var opt = document.createElement('option');
                opt.value = st.id;
                var text = st.student_name;
                if (st.section_name) {
                    text += ' (' + st.section_name + ')';
                }
                if (st.scholar_no) {
                    text += ' - ' + st.scholar_no;
                }
                opt.textContent = text;
                studentSelect.appendChild(opt);
            });
        })
        .catch(function (err) {
            console.error('Error loading students:', err);
            studentSelect.innerHTML = '<option value="">All students in selected class</option>';
        });
    }

    if (examSelect) {
        examSelect.addEventListener('change', function () {
            onExamChange(false);
        });
    }

    if (classSelect) {
        classSelect.addEventListener('change', onClassChange);
    }

    if (sectionSelect) {
        sectionSelect.addEventListener('change', onSectionChange);
    }

    // If a class is already selected on load and student select is empty, fetch students
    if (classSelect && classSelect.value) {
        var initialOption = classSelect.options[classSelect.selectedIndex];
        var initialClassName = initialOption ? initialOption.textContent.trim() : '';
        var initialSection = sectionSelect ? sectionSelect.value : '';
        if (sectionSelect && sectionSelect.options.length <= 1) {
            renderSections(initialSection);
        }
        if (studentSelect && studentSelect.options.length <= 1) {
            loadStudents(classSelect.value, initialClassName, initialSection);
        }
    }

    function openMarksheetPreview() {
        var classVal = classSelect ? classSelect.value : '';
        if (!classVal) {
            alert('Please select a Class.');
            if (classSelect) classSelect.focus();
            return;
        }

        var alertEl = document.getElementById('marks_missing_alert');
        if (alertEl && alertEl.style.display !== 'none') {
            if (!confirm('Notice: Marks have not been entered for this class/exam yet.\n\nPrint preview will display empty/zero marks. Do you still want to proceed?')) {
                return;
            }
        }

        var params = new URLSearchParams();
        params.set('class_id', classVal);

        var sectionVal = sectionSelect ? sectionSelect.value : '';
        if (sectionVal) {
            params.set('section_name', sectionVal);
        }

        var examVal = (resolvedExamInput && resolvedExamInput.value) ? resolvedExamInput.value : (examSelect ? examSelect.value : '');
        if (examVal) {
            params.set('exam_id', examVal);
        }

        var studentVal = studentSelect ? studentSelect.value : '';
        if (studentVal) {
            params.set('student_id', studentVal);
        }

        window.open('{{ route('academic.marksheets.print') }}?' + params.toString(), '_blank');
    }

    var genForm = document.getElementById('marksheetGenerateForm');
    if (genForm) {
        genForm.addEventListener('submit', function (e) {
            var classVal = classSelect ? classSelect.value : '';
            if (!classVal) {
                e.preventDefault();
                alert('Please select a Class.');
                if (classSelect) classSelect.focus();
                return;
            }

            var alertEl = document.getElementById('marks_missing_alert');
            if (alertEl && alertEl.style.display !== 'none') {
                if (!confirm('Notice: Marks have not been entered for this class/exam yet.\n\nGenerated marksheets will have empty/zero marks. Do you still want to proceed?')) {
                    e.preventDefault();
                }
            }
        });
    }

    function changeGeneratedPerPage(val) {
        var input = document.getElementById('generated_per_page_input');
        if (input) {
            input.value = val;
        }
        var searchForm = document.getElementById('generatedSearchForm');
        if (searchForm) {
            searchForm.submit();
        } else {
            var url = new URL(window.location.href);
            url.searchParams.set('per_page', val);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }
    }

    function selectedGeneratedCount() {
        return document.querySelectorAll('.generated-check:checked').length;
    }

    function updateGeneratedSelectionUI() {
        var count = selectedGeneratedCount();
        var printCountEl = document.getElementById('selected_print_count');
        var deleteCountEl = document.getElementById('selected_delete_count');
        if (printCountEl) {
            printCountEl.textContent = count > 0 ? '(' + count + ')' : '';
        }
        if (deleteCountEl) {
            deleteCountEl.textContent = count > 0 ? '(' + count + ')' : '';
        }
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
        var methodInput = document.getElementById('generated_bulk_method');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.id = 'generated_bulk_method';
            form.appendChild(methodInput);
        }

        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete ' + count + ' selected generated marksheet(s)?')) {
                return;
            }
            form.action = '{{ route('academic.marksheets.generated.bulk-delete') }}';
            form.target = '';
            methodInput.disabled = false;
            methodInput.value = 'DELETE';
            form.submit();
        } else {
            var ids = Array.prototype.map.call(document.querySelectorAll('.generated-check:checked'), function (checkbox) {
                return checkbox.value;
            }).join(',');
            var bulkPrintUrl = '{{ route('marksheet.generated.bulk-print', ['marksheets' => '__IDS__']) }}'.replace('__IDS__', ids);
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
