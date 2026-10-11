@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4">
    <div class="breadcrumb d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1">Term 1 Report Card Management</h2>
            <p class="text-muted mb-0">CBSE Term I Examination Report Card in Portrait A4 format</p>
        </div>
        <div>
            <a href="{{ route('marksheet') }}" class="btn btn-outline-secondary btn-sm">
                <i class="i-Arrow-Back"></i> Annual Marksheet Area
            </a>
        </div>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Horizontal Filter / Print / Generate Card -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title font-weight-bold text-primary mb-3">
                <i class="i-Filter-2 mr-1"></i> Filter &amp; Print Term 1 Report Card
            </h5>

            <form method="POST" action="{{ route('academic.term1-marksheet.generate') }}" id="term1MarksheetForm">
                @csrf

                <!-- Row 1: Horizontal Filter Options -->
                <div class="row">
                    <div class="col-md-3 col-sm-6 form-group">
                        <label class="font-weight-bold">Class <span class="text-danger">*</span></label>
                        <select class="form-control" name="class_name" id="t1_class_name" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $c)
                                @php $cName = $c->class_name; @endphp
                                <option value="{{ $cName }}" {{ (string) $selectedClassName === (string) $cName ? 'selected' : '' }}>
                                    {{ in_array($cName, ['Nursery', 'KG1', 'KG2', 'Kg1', 'Kg2']) ? $cName : 'Class ' . (is_numeric($cName) ? (int)$cName : $cName) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6 form-group">
                        <label class="font-weight-bold">Section</label>
                        <select class="form-control" name="section_name" id="t1_section_name">
                            <option value="">All Sections</option>
                            @foreach($sections as $sec)
                                <option value="{{ $sec }}" {{ (string) $selectedSectionName === (string) $sec ? 'selected' : '' }}>
                                    {{ $sec }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6 form-group">
                        <label class="font-weight-bold">Exam Name</label>
                        <select class="form-control" name="exam_id" id="t1_exam_id">
                            <option value="">Auto-detect (Term 1 / Half Yearly / PT 1)</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ (string) $selectedExamId === (string) $exam->id ? 'selected' : '' }}>
                                    {{ $exam->exam_name }}{{ $exam->exam_type ? ' - '.$exam->exam_type : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6 form-group">
                        <label class="font-weight-bold">Student</label>
                        <select class="form-control" name="student_id" id="t1_student_id">
                            <option value="">All students in selected class/section</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ (string) $selectedStudentId === (string) $student->id ? 'selected' : '' }}>
                                    {{ $student->student_name }}{{ $student->scholar_no ? ' ('.$student->scholar_no.')' : '' }}{{ !empty($student->section_name) ? ' - Sec: '.$student->section_name : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row 2: Remarks + Action Buttons -->
                <div class="row align-items-center mt-2">
                    <div class="col-md-6 col-12 form-group mb-md-0">
                        <label class="font-weight-bold">Class Teacher's Remarks (Optional)</label>
                        <input type="text" class="form-control" name="remarks" id="t1_remarks" placeholder="Needs to develop a better sense of responsibility." value="{{ request('remarks') }}">
                        <small class="form-text text-muted">Leave empty to use student's saved remark or standard default.</small>
                    </div>

                    <div class="col-md-6 col-12 d-flex justify-content-md-end justify-content-start align-items-center pt-md-3 mt-md-1">
                        <button class="btn btn-primary btn-lg px-4 mr-2 shadow-sm" type="button" id="btnPreview" onclick="openTerm1Preview()">
                            <i class="i-Printer mr-1"></i> Print Preview (Portrait)
                        </button>
                        <button class="btn btn-success btn-lg px-4 shadow-sm" type="submit" id="btnGenerate">
                            <i class="i-Save mr-1"></i> Generate &amp; Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Generated Term 1 Marksheets Table -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-title mb-0">Generated Term 1 Report Cards</h4>
                <span class="badge badge-pill badge-info px-3 py-1 font-weight-normal">
                    Total: {{ $generatedMarksheets->total() }} records
                </span>
            </div>

            <!-- Filter form -->
            <form method="GET" action="{{ route('academic.term1-marksheet.index') }}" class="row align-items-end mb-3">
                <input type="hidden" name="class_name" value="{{ $selectedClassName }}">
                <input type="hidden" name="section_name" value="{{ $selectedSectionName }}">
                <div class="col-md-4 form-group mb-md-0">
                    <label class="font-weight-bold small">Search</label>
                    <input class="form-control" name="generated_search" value="{{ $generatedSearch }}" placeholder="Student name, scholar no, exam or session">
                </div>
                <div class="col-md-3 form-group mb-md-0">
                    <label class="font-weight-bold small">Filter Class</label>
                    <select class="form-control" name="generated_class_name">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                            @php $cName = $c->class_name; @endphp
                            <option value="{{ $cName }}" {{ (string) $generatedClassName === (string) $cName ? 'selected' : '' }}>
                                {{ in_array($cName, ['Nursery', 'KG1', 'KG2', 'Kg1', 'Kg2']) ? $cName : 'Class ' . (is_numeric($cName) ? (int)$cName : $cName) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group mb-md-0">
                    <label class="font-weight-bold small">Filter Exam</label>
                    <select class="form-control" name="generated_exam_id">
                        <option value="">All Exams</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" {{ (string) $generatedExamId === (string) $exam->id ? 'selected' : '' }}>
                                {{ $exam->exam_name }}{{ $exam->exam_type ? ' - '.$exam->exam_type : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-block" type="submit">Filter</button>
                    <a class="btn btn-outline-secondary btn-block mt-1" href="{{ route('academic.term1-marksheet.index', array_filter(['class_name' => $selectedClassName, 'section_name' => $selectedSectionName])) }}">Reset</a>
                </div>
            </form>

            <form method="POST" action="{{ route('academic.term1-marksheet.generated.bulk-print') }}" id="t1BulkForm">
                @csrf
                <div class="mb-3 d-flex align-items-center">
                    <button class="btn btn-sm btn-primary mr-2" type="button" onclick="submitT1Bulk('print')">
                        <i class="i-Printer"></i> Bulk Print Selected
                    </button>
                    <button class="btn btn-sm btn-outline-danger" type="button" onclick="submitT1Bulk('delete')">
                        <i class="i-Close"></i> Bulk Delete
                    </button>
                    <span class="text-muted ml-3 small" id="t1SelectedCount">0 selected</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 38px; text-align: center;">
                                    <input type="checkbox" id="select_all_t1" onclick="toggleT1Selection(this)">
                                </th>
                                <th>Student</th>
                                <th>Scholar No</th>
                                <th>Class &amp; Section</th>
                                <th>Exam</th>
                                <th>Session</th>
                                <th>Generated On</th>
                                <th style="width: 140px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($generatedMarksheets as $marksheet)
                                @php
                                    $snapshot = $marksheet->snapshot ?: [];
                                    $pData = $snapshot['print_data'] ?? [];
                                    $studentMeta = $pData['student_meta'] ?? [];
                                    $studentName = $snapshot['student_name'] ?? ($studentMeta['student_name'] ?? 'Student #'.$marksheet->student_id);
                                    $scholarNo = $snapshot['scholar_no'] ?? ($studentMeta['scholar_no'] ?? '-');
                                    $className = $snapshot['class_name'] ?? (optional($pData['class'])->class_name ?? '-');
                                    $sectionName = $pData['section_name'] ?? '';
                                @endphp
                                <tr>
                                    <td class="text-center align-middle">
                                        <input type="checkbox" class="t1-check" name="marksheet_ids[]" value="{{ $marksheet->id }}" onchange="updateT1Count()">
                                    </td>
                                    <td class="align-middle font-weight-bold text-primary">{{ $studentName }}</td>
                                    <td class="align-middle">{{ $scholarNo }}</td>
                                    <td class="align-middle">
                                        Class {{ $className }}{{ !empty($sectionName) ? ' - '.$sectionName : '' }}
                                    </td>
                                    <td class="align-middle">{{ $snapshot['exam_name'] ?? ($marksheet->exam_id ? 'Exam #'.$marksheet->exam_id : 'Term 1 Exam') }}</td>
                                    <td class="align-middle">{{ $marksheet->session_year ?: '-' }}</td>
                                    <td class="align-middle">{{ optional($marksheet->created_at)->format('d M Y h:i A') }}</td>
                                    <td class="text-center align-middle">
                                        <a href="{{ route('academic.term1-marksheet.generated.print', $marksheet) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Print in Portrait">
                                            <i class="i-Printer"></i> Print
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteT1Single('{{ route('academic.term1-marksheet.generated.delete', $marksheet) }}')">
                                            <i class="i-Close"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="i-Folder-Empty text-28 d-block mb-1"></i>
                                        No Term 1 report cards generated yet. Select a class above to generate report cards.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="mt-3">
                {{ $generatedMarksheets->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<script>
    var sectionsByClass = @json($sectionsByClass);
    var studentsDataUrl = '{{ route('academic.term1-marksheet.students-data') }}';

    var classSelect = document.getElementById('t1_class_name');
    var sectionSelect = document.getElementById('t1_section_name');
    var studentSelect = document.getElementById('t1_student_id');

    if (classSelect) {
        classSelect.addEventListener('change', function () {
            var selectedClass = this.value;
            populateSections(selectedClass);
            loadStudents(selectedClass, sectionSelect.value);
        });
    }

    if (sectionSelect) {
        sectionSelect.addEventListener('change', function () {
            var selectedClass = classSelect ? classSelect.value : '';
            var selectedSection = this.value;
            loadStudents(selectedClass, selectedSection);
        });
    }

    function populateSections(className) {
        if (!sectionSelect) return;
        var currentSection = sectionSelect.value;
        sectionSelect.innerHTML = '<option value="">All Sections</option>';

        if (!className) return;

        var availableSections = sectionsByClass[className] || sectionsByClass[className.replace(/^0+/, '')] || [];
        availableSections.forEach(function (sec) {
            var opt = document.createElement('option');
            opt.value = sec;
            opt.textContent = sec;
            if (sec === currentSection) {
                opt.selected = true;
            }
            sectionSelect.appendChild(opt);
        });
    }

    function loadStudents(className, sectionName) {
        if (!studentSelect) return;
        studentSelect.innerHTML = '<option value="">Loading students...</option>';

        if (!className) {
            studentSelect.innerHTML = '<option value="">All students in selected class/section</option>';
            return;
        }

        var url = new URL(studentsDataUrl, window.location.origin);
        url.searchParams.set('class_name', className);
        if (sectionName) {
            url.searchParams.set('section_name', sectionName);
        }

        fetch(url.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) { return response.json(); })
        .then(function (students) {
            studentSelect.innerHTML = '<option value="">All students in selected class/section (' + students.length + ' students)</option>';
            students.forEach(function (st) {
                var opt = document.createElement('option');
                opt.value = st.id;
                var text = st.student_name;
                if (st.scholar_no) text += ' (' + st.scholar_no + ')';
                if (st.section_name) text += ' - Sec: ' + st.section_name;
                opt.textContent = text;
                studentSelect.appendChild(opt);
            });
        })
        .catch(function (err) {
            console.error('Error loading students:', err);
            studentSelect.innerHTML = '<option value="">All students in selected class/section</option>';
        });
    }

    function openTerm1Preview() {
        var classVal = classSelect ? classSelect.value : '';
        if (!classVal) {
            alert('Please select a Class first.');
            if (classSelect) classSelect.focus();
            return;
        }

        var params = new URLSearchParams();
        ['class_name', 'section_name', 'exam_id', 'student_id', 'remarks'].forEach(function (name) {
            var input = document.querySelector('#term1MarksheetForm [name="' + name + '"]');
            if (input && input.value) {
                params.set(name, input.value);
            }
        });
        window.open('{{ route('academic.term1-marksheet.print') }}?' + params.toString(), '_blank');
    }

    document.getElementById('term1MarksheetForm').addEventListener('submit', function (e) {
        var classVal = classSelect ? classSelect.value : '';
        if (!classVal) {
            e.preventDefault();
            alert('Please select a Class first.');
            if (classSelect) classSelect.focus();
        }
    });

    function toggleT1Selection(source) {
        document.querySelectorAll('.t1-check').forEach(function (checkbox) {
            checkbox.checked = source.checked;
        });
        updateT1Count();
    }

    function updateT1Count() {
        var count = document.querySelectorAll('.t1-check:checked').length;
        document.getElementById('t1SelectedCount').textContent = count + ' selected';
    }

    function submitT1Bulk(action) {
        var checked = document.querySelectorAll('.t1-check:checked');
        if (checked.length === 0) {
            alert('Please select at least one generated report card.');
            return;
        }

        var form = document.getElementById('t1BulkForm');
        var methodInput = document.getElementById('t1_bulk_method');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.id = 't1_bulk_method';
            form.appendChild(methodInput);
        }

        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete ' + checked.length + ' selected report card(s)?')) {
                return;
            }
            form.action = '{{ route('academic.term1-marksheet.generated.bulk-delete') }}';
            form.target = '';
            methodInput.disabled = false;
            methodInput.value = 'DELETE';
            form.submit();
        } else {
            var ids = Array.prototype.map.call(checked, function (c) { return c.value; }).join(',');
            var url = '{{ route('academic.term1-marksheet.generated.bulk-print-page', ['marksheets' => '__IDS__']) }}'.replace('__IDS__', ids);
            window.open(url, '_blank');
        }
    }

    function deleteT1Single(action) {
        if (!confirm('Delete this generated Term 1 report card?')) {
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
