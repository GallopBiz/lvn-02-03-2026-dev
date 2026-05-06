@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Roll Number & Admit Card Tools</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('academic.roll-no-tools') }}" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label>Class Name</label>
                <select name="class_name" class="form-control" onchange="this.form.submit()" required>
                    <option value="">Select Class</option>
                    @foreach($classList as $class)
                        <option value="{{ $class }}" {{ $className === $class ? 'selected' : '' }}>{{ $class }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Section Name</label>
                <select name="section_name" class="form-control" required>
                    <option value="">Select Section</option>
                    @foreach($sectionList as $section)
                        <option value="{{ $section }}" {{ $sectionName === $section ? 'selected' : '' }}>{{ $section }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Exam</label>
                <select name="exam_id" class="form-control">
                    <option value="">Select Exam</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}" {{ (string) $selectedExamId === (string) $exam->id ? 'selected' : '' }}>
                            {{ $exam->exam_name }} ({{ $exam->exam_type }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Show Students</button>
            </div>
        </div>
    </form>

    @if(!empty($className) && !empty($sectionName) && !empty($sessionName))
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">{{ $className }} - {{ $sectionName }} ({{ $sessionName }})</h5>
            <div>
                <a id="bulkGenerateBtn" href="{{ route('academic.generate-rolls', ['class' => $className, 'section' => $sectionName, 'session' => $sessionName, 'exam_id' => $selectedExamId]) }}" class="btn btn-warning d-none">
                    Generate Roll No (Bulk)
                </a>
                <button id="selectedGenerateBtn" type="button" class="btn btn-primary d-none">
                    Generate Roll No (Selected Students)
                </button>
                <a id="bulkPrintBtn" href="{{ route('academic.print-admit-cards', ['class' => $className, 'section' => $sectionName, 'session' => $sessionName, 'exam_id' => $selectedExamId]) }}" class="btn btn-info d-none">
                    Print Admit Cards (Bulk)
                </a>
                <button id="selectedPrintBtn" type="button" class="btn btn-info d-none">
                    Print Admit Cards (Selected)
                </button>
                @if($hasGeneratedRolls)
                    <a href="{{ route('academic.download-rolls', ['class' => $className, 'section' => $sectionName, 'session' => $sessionName]) }}" class="btn btn-success">
                        Download
                    </a>
                @endif
            </div>
        </div>

        <form id="selectedGenerateForm" method="POST" action="{{ route('academic.generate-rolls.selected') }}">
            @csrf
            <input type="hidden" name="class_name" value="{{ $className }}">
            <input type="hidden" name="section_name" value="{{ $sectionName }}">
            <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">
            <div id="selectedStudentsContainer"></div>
        </form>

        <form id="selectedPrintForm" method="POST" action="{{ route('academic.print-admit-cards.selected') }}" target="_blank">
            @csrf
            <input type="hidden" name="class_name" value="{{ $className }}">
            <input type="hidden" name="section_name" value="{{ $sectionName }}">
            <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">
            <div id="selectedStudentsPrintContainer"></div>
        </form>

        <form method="POST" action="{{ route('academic.room-numbers.save') }}">
            @csrf
            <input type="hidden" name="class_name" value="{{ $className }}">
            <input type="hidden" name="section_name" value="{{ $sectionName }}">
            <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">

            @if($students->isNotEmpty())
                <div class="row g-2 align-items-end mb-2">
                    <div class="col-md-3">
                        <label>Bulk Room No</label>
                        <input type="text" id="bulkRoomNo" class="form-control" placeholder="Enter room no">
                    </div>
                    <div class="col-md-5">
                        <button type="button" id="applyRoomSelectedBtn" class="btn btn-secondary">
                            Apply to Selected
                        </button>
                        <button type="button" id="applyRoomAllBtn" class="btn btn-secondary">
                            Apply to All
                        </button>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllStudents"></th>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Roll No</th>
                            <th style="width: 140px;">Room No</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $index => $student)
                            <tr>
                                <td>
                                    <input type="checkbox" class="student-checkbox" value="{{ $student->id }}">
                                </td>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $student->student_name }}</td>
                                <td>{{ $rollMap[$student->id] ?? '-' }}</td>
                                <td>
                                    <input
                                        type="text"
                                        name="room_numbers[{{ $student->id }}]"
                                        class="form-control form-control-sm room-input"
                                        data-student-id="{{ $student->id }}"
                                        value="{{ $roomMap[$student->id] ?? '' }}"
                                        placeholder="Room"
                                    >
                                </td>
                                <td>
                                    <a href="{{ route('academic.generate-roll.student', ['student' => $student->id, 'session' => $sessionName, 'exam_id' => $selectedExamId]) }}" class="btn btn-sm btn-primary">
                                        Generate Roll No
                                    </a>
                                    <a href="{{ route('academic.print-admit-card.student', ['student' => $student->id, 'session' => $sessionName, 'exam_id' => $selectedExamId]) }}" class="btn btn-sm btn-info" target="_blank">
                                        Print Admit Card
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No students found for selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->isNotEmpty())
                <button type="submit" class="btn btn-success mt-2">Save Room No</button>
            @endif
        </form>

        @if($hasGeneratedRolls)
            <a href="{{ route('academic.print-admit-cards', ['class' => $className, 'section' => $sectionName, 'session' => $sessionName, 'exam_id' => $selectedExamId]) }}" class="btn btn-info mt-2">
                Print Admit Cards
            </a>
        @endif
    @endif
</div>
<script>
    (function () {
        var selectAll = document.getElementById('selectAllStudents');
        var checkboxes = Array.from(document.querySelectorAll('.student-checkbox'));
        var bulkBtn = document.getElementById('bulkGenerateBtn');
        var selectedBtn = document.getElementById('selectedGenerateBtn');
        var bulkPrintBtn = document.getElementById('bulkPrintBtn');
        var selectedPrintBtn = document.getElementById('selectedPrintBtn');
        var selectedForm = document.getElementById('selectedGenerateForm');
        var selectedContainer = document.getElementById('selectedStudentsContainer');
        var selectedPrintForm = document.getElementById('selectedPrintForm');
        var selectedPrintContainer = document.getElementById('selectedStudentsPrintContainer');
        var bulkRoomNo = document.getElementById('bulkRoomNo');
        var applyRoomSelectedBtn = document.getElementById('applyRoomSelectedBtn');
        var applyRoomAllBtn = document.getElementById('applyRoomAllBtn');
        var roomInputs = Array.from(document.querySelectorAll('.room-input'));

        if (!selectAll || checkboxes.length === 0 || !bulkBtn || !selectedBtn || !bulkPrintBtn || !selectedPrintBtn || !selectedForm || !selectedContainer || !selectedPrintForm || !selectedPrintContainer) {
            return;
        }

        function getChecked() {
            return checkboxes.filter(function (cb) { return cb.checked; });
        }

        function syncButtons() {
            var checked = getChecked();
            var hasSelection = checked.length > 0;
            var allSelected = checked.length === checkboxes.length && checkboxes.length > 0;
            bulkBtn.classList.toggle('d-none', !allSelected);
            bulkPrintBtn.classList.toggle('d-none', !allSelected);
            selectedBtn.classList.toggle('d-none', !hasSelection);
            selectedPrintBtn.classList.toggle('d-none', !hasSelection);
            selectAll.checked = allSelected;
        }

        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (cb) { cb.checked = selectAll.checked; });
            syncButtons();
        });

        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', syncButtons);
        });

        selectedBtn.addEventListener('click', function () {
            var checked = getChecked();
            selectedContainer.innerHTML = '';

            checked.forEach(function (cb) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'student_ids[]';
                input.value = cb.value;
                selectedContainer.appendChild(input);
            });

            if (checked.length > 0) {
                selectedForm.submit();
            }
        });

        selectedPrintBtn.addEventListener('click', function () {
            var checked = getChecked();
            selectedPrintContainer.innerHTML = '';

            checked.forEach(function (cb) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'student_ids[]';
                input.value = cb.value;
                selectedPrintContainer.appendChild(input);
            });

            if (checked.length > 0) {
                selectedPrintForm.submit();
            }
        });

        if (bulkRoomNo && applyRoomSelectedBtn && applyRoomAllBtn && roomInputs.length > 0) {
            function applyRoomToInputs(inputs) {
                var roomNo = bulkRoomNo.value.trim();
                inputs.forEach(function (input) {
                    input.value = roomNo;
                });
            }

            applyRoomSelectedBtn.addEventListener('click', function () {
                var selectedIds = getChecked().map(function (cb) { return cb.value; });
                applyRoomToInputs(roomInputs.filter(function (input) {
                    return selectedIds.indexOf(input.dataset.studentId) !== -1;
                }));
            });

            applyRoomAllBtn.addEventListener('click', function () {
                applyRoomToInputs(roomInputs);
            });
        }

        syncButtons();
    })();
</script>
@endsection
