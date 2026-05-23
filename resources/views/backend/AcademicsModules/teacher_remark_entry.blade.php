@extends('backend.layouts.main')
@section('main-container')
@php
    $remarkUrlPrefix = !empty($isStaffRemarkUser) ? 'staff/' : '';
@endphp
<style>
    .uperletter {
        text-transform: capitalize;
    }

    .saved-remarks-scroll {
        max-height: 520px;
        overflow-y: auto;
    }
</style>

<div class="main-content">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="breadcrumb d-flex justify-content-between align-items-center">
        <h1 class="me-2">Teacher Remark Entry :-</h1>
        @if(!empty($stream_master))
            <a href="{{ !empty($isStaffRemarkUser) ? route('staff.teacher-remark-entry') : route('teacher-remark-entry') }}" class="btn btn-secondary">Back</a>
        @endif
    </div>

    <div class="separator-breadcrumb border-top"></div>

    <form id="remark-entry-form" class="p-4" onsubmit="{{ !empty($stream_master) ? 'handleUpdateData(event)' : 'handleSubmitData(event)' }}">
        @csrf
        <input type="hidden" id="remark_entry_id" name="remark_entry_id" value="{{ !empty($stream_master) ? $stream_master->id : '' }}">

        <div class="row mb-4">
            <div class="col-lg-5 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="mb-3">Select Criteria For Remark Entry :-</h4>

                        <div class="form-group mb-3">
                            <label for="teacher_name">Teacher : <span class="text-danger">*</span></label>
                            <select required name="teacher_name" class="form-control" id="teacher_name" @if (!empty($stream_master) || !empty($isStaffRemarkUser)) disabled @endif>
                                <option value="">--Select--</option>
                                @php $selectedTeacher = $stream_master->teacher_id ?? $staffEmployeeId ?? null; @endphp
                                @foreach ($teacherlist as $teacherAssignment)
                                    @if(!empty($teacherAssignment->Teacher))
                                        <option value="{{ $teacherAssignment->Teacher->id }}" {{ (string) $selectedTeacher === (string) $teacherAssignment->Teacher->id ? 'selected' : '' }}>
                                            {{ $teacherAssignment->Teacher->first_name }} {{ $teacherAssignment->Teacher->last_name }} - {{ $teacherAssignment->Teacher->biometricDetails->ess_emp_code ?? '' }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="class_name">Class : <span class="text-danger">*</span></label>
                            <select required id="class_name" class="form-control" name="class_name" @if (!empty($stream_master)) disabled @endif>
                                <option value="">--Select--</option>
                                @foreach ($classlist as $class)
                                    <option value="{{ $class->id }}" {{ !empty($stream_master) && (string) $stream_master->class_id === (string) $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="section_name">Section : <span class="text-danger">*</span></label>
                            <select required name="section_name" class="form-control" id="section_name" @if (!empty($stream_master)) disabled @endif>
                                <option value="">--Select--</option>
                                @if(!empty($stream_master))
                                    <option selected value="{{ $stream_master->section_name }}">{{ $stream_master->section_name }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="exam_name">Term / Exam : <span class="text-danger">*</span></label>
                            <select required name="exam_name" class="form-control" id="exam_name" @if (!empty($stream_master)) disabled @endif>
                                <option value="">--Select--</option>
                                @if(!empty($stream_master))
                                    @foreach($examslist as $exam)
                                        @if((string) $exam->id === (string) $stream_master->exam_id)
                                            <option selected value="{{ $exam->id }}">{{ $exam->exam_name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="remark_id">Remarks : <span class="text-danger">*</span></label>
                            <select required name="remark_id" class="form-control" id="remark_id">
                                <option value="">--Select--</option>
                                @foreach($remarks as $remark)
                                    <option value="{{ $remark->id }}" {{ !empty($stream_master) && (string) $stream_master->remark_id === (string) $remark->id ? 'selected' : '' }}>{{ $remark->remark }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="remark_entry_status" class="alert alert-info mt-3 d-none"></div>
                        <button type="button" class="btn btn-info" id="show_students_btn">Show Students</button>
                        <button type="button" class="btn btn-success" id="pick_marks_btn">Pick Marks</button>
                        <button type="button" class="btn btn-primary" id="fill_to_all_btn">Fill To All</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="mb-3">Previously Saved Remarks Entry :-</h4>
                        <div class="table-responsive saved-remarks-scroll">
                            <table class="display table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">S.no.</th>
                                        <th scope="col">Exam Name</th>
                                        <th scope="col">Class</th>
                                        <th scope="col">Section</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stream as $entry)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="uperletter">{{ $entry->exam->exam_name ?? '-' }}</td>
                                            <td>{{ $entry->className->class_name ?? '-' }}</td>
                                            <td>{{ $entry->section_name }}</td>
                                            <td class="d-flex">
                                                <a class="btn btn-primary m-1" href="{{ url($remarkUrlPrefix . 'view-teacher-remark-entry/' . $entry->id) }}">Edit</a>
                                                @php $deleteKey = 'academic_teacher_remark_entries-' . $entry->id; @endphp
                                                <a class="btn btn-raised ripple btn-danger m-1" href="{{ url($remarkUrlPrefix . 'delete-teacher-remark-entry/' . $deleteKey) }}" onclick="confirmDelete(event)">Delete</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No Data Found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Student Remarks</h4>
                    <button type="submit" id="remark_submit_btn" class="btn btn-primary">Save</button>
                </div>
                <div class="table-responsive" style="width: 100%;">
                    <table class="display table table-striped table-bordered table-full-width" style="width: 100%;">
                        <thead>
                            <tr>
                                <th scope="col">S.no.</th>
                                <th scope="col"><input type="checkbox" id="select_all_students"></th>
                                <th scope="col">Scholar no.</th>
                                <th scope="col">Student Name</th>
                                <th scope="col">Roll No.</th>
                                <th scope="col">Total Marks</th>
                                <th scope="col">Total Obt.</th>
                                <th scope="col">%</th>
                                <th scope="col">Grade</th>
                                <th scope="col">Remark</th>
                                <th scope="col">Result Remark</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="studentdatatable">
                            @if(!empty($studentRemarks) && count($studentRemarks))
                                @foreach($studentRemarks as $studentRemark)
                                    <tr class="student-row">
                                        <td>{{ $loop->iteration }}</td>
                                        <td><input type="checkbox" class="student_select" checked></td>
                                        <td>
                                            {{ $studentRemark->scholar_no }}
                                            <input type="hidden" class="scholar_no" value="{{ $studentRemark->scholar_no }}">
                                        </td>
                                        <td>{{ $studentRemark->student_name }}</td>
                                        <td>
                                            {{ $studentRemark->roll_no }}
                                            <input type="hidden" class="student_id" value="{{ $studentRemark->student_id }}">
                                            <input type="hidden" class="roll_no" value="{{ $studentRemark->roll_no }}">
                                        </td>
                                        <td><input type="text" class="form-control total_marks" value="{{ $studentRemark->total_marks }}" readonly></td>
                                        <td><input type="text" class="form-control total_obtained" value="{{ $studentRemark->total_obtained }}" readonly></td>
                                        <td>
                                            @php
                                                $percent = ($studentRemark->total_marks && $studentRemark->total_marks > 0) ? round(($studentRemark->total_obtained / $studentRemark->total_marks) * 100, 2) : '';
                                            @endphp
                                            <input type="text" class="form-control percent" value="{{ $percent }}" readonly style="width: 80px;">
                                        </td>
                                        <td><input type="text" class="form-control grade" value="{{ $studentRemark->grade }}" readonly></td>
                                        <td>
                                            <select class="form-control student_remark_id" style="min-width: 180px; max-width: 300px;">
                                                <option value="">--Select--</option>
                                                @foreach($remarks as $remark)
                                                    <option value="{{ $remark->id }}" {{ (string) $studentRemark->remark_id === (string) $remark->id ? 'selected' : '' }}>{{ $remark->remark }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control result_remark" value="{{ $studentRemark->result_remark }}" style="min-width: 180px; max-width: 300px;"></td>
                                        <td><button type="button" class="btn btn-danger btn-sm" onclick="deleteRowData(event)">Delete</button></td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="12" class="text-center">No Data Found</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const allExams = @json($examslist);
    const allRemarks = @json($remarks);
    const remarkUrlPrefix = "{{ $remarkUrlPrefix }}";

    function ajaxErrorMessage(xhr) {
        return xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong.';
    }

    function remarkOptions(selectedId = '') {
        let html = '<option value="">--Select--</option>';
        allRemarks.forEach((remark) => {
            const selected = String(remark.id) === String(selectedId) ? ' selected' : '';
            html += `<option value="${remark.id}"${selected}>${remark.remark || ''}</option>`;
        });
        return html;
    }

    function resetRemarkEntryStatus() {
        $('#remark_entry_status').addClass('d-none alert-info').removeClass('alert-warning').html('');
        $('#show_students_btn').prop('disabled', false);
        $('#remark_submit_btn').prop('disabled', false);
    }

    function selectedRemarkText(selectElement) {
        return $(selectElement).find('option:selected').text() || '';
    }

    function collectStudentRemarks() {
        const studentData = [];
        $('#studentdatatable').find('.student-row').each(function () {
            const row = $(this);
            const remarkSelect = row.find('.student_remark_id');
            studentData.push({
                student_id: row.find('.student_id').val(),
                roll_no: row.find('.roll_no').val(),
                scholar_no: row.find('.scholar_no').val(),
                remark_id: remarkSelect.val(),
                remark_text: selectedRemarkText(remarkSelect),
                total_marks: row.find('.total_marks').val(),
                total_obtained: row.find('.total_obtained').val(),
                grade: row.find('.grade').val(),
                result_remark: row.find('.result_remark').val()
            });
        });
        return studentData;
    }

    function postData() {
        return {
            remark_entry_id: $("#remark_entry_id").val(),
            teacher_name: $("#teacher_name").val(),
            class_name: $("#class_name").val(),
            section_name: $("#section_name").val(),
            exam_name: $("#exam_name").val(),
            remark_id: $("#remark_id").val(),
            students: collectStudentRemarks()
        };
    }

    function setSaving(isSaving) {
        $('#remark_submit_btn').prop('disabled', isSaving).text(isSaving ? 'Saving...' : 'Save');
    }

    function saveRemarkEntry(url) {
        const data = postData();
        if (!data.students.length) {
            toastr.error('Please show students first.', 'Required');
            return;
        }

        $.ajax({
            data: data,
            url: url,
            headers: { 'X-CSRF-TOKEN': document.getElementsByName('_token')[0].value },
            method: 'POST',
            dataType: 'json',
            beforeSend: function() {
                setSaving(true);
            },
            complete: function() {
                setSaving(false);
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success('Updated successfully.', 'Success');
                    setTimeout(() => location.reload(), 1200);
                }
            },
            error: function(xhr) {
                toastr.error(ajaxErrorMessage(xhr), 'Error');
            }
        });
    }

    function handleSubmitData(event) {
        event.preventDefault();
        saveRemarkEntry("{{ url($remarkUrlPrefix . 'save-teacher-remark-entry') }}");
    }

    function handleUpdateData(event) {
        event.preventDefault();
        saveRemarkEntry("{{ url($remarkUrlPrefix . 'store-teacher-remark-entry') }}");
    }

    function deleteRowData(event) {
        event.preventDefault();
        $(event.target).closest('tr').remove();
        refreshRowNumbers();
    }

    function refreshRowNumbers() {
        $('#studentdatatable').find('.student-row').each(function(index) {
            $(this).children().first().text(index + 1);
        });
    }

    function fillToAll() {
        const remarkId = $('#remark_id').val();
        if (!remarkId) {
            toastr.error('Please select remarks first.', 'Required');
            return;
        }
        $('.student_remark_id').val(remarkId);
    }

    function selectedStudentIds() {
        const ids = [];
        $('#studentdatatable').find('.student-row').each(function () {
            const row = $(this);
            if (row.find('.student_select').is(':checked')) {
                ids.push(row.find('.student_id').val());
            }
        });
        return ids;
    }

    function pickMarks() {
        const ids = selectedStudentIds();
        if (!ids.length) {
            toastr.error('Please select at least one student.', 'Required');
            return;
        }
        if (!$('#class_name').val() || !$('#section_name').val() || !$('#exam_name').val()) {
            toastr.error('Please select class, section, and term/exam first.', 'Required');
            return;
        }

        $.ajax({
            data: {
                teacher_name: $('#teacher_name').val(),
                class_name: $('#class_name').val(),
                section_name: $('#section_name').val(),
                exam_name: $('#exam_name').val(),
                students: ids
            },
            url: "{{ url($remarkUrlPrefix . 'teacher-remark-pick-marks') }}",
            headers: { 'X-CSRF-TOKEN': document.getElementsByName('_token')[0].value },
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                const marks = response.marks || {};
                $('#studentdatatable').find('.student-row').each(function () {
                    const row = $(this);
                    const studentId = row.find('.student_id').val();
                    if (!row.find('.student_select').is(':checked') || !marks[studentId]) {
                        return;
                    }
                    row.find('.total_marks').val(marks[studentId].total_marks || '');
                    row.find('.total_obtained').val(marks[studentId].total_obtained || '');
                    const totalMarks = parseFloat(marks[studentId].total_marks || 0);
                    const totalObtained = parseFloat(marks[studentId].total_obtained || 0);
                    row.find('.percent').val(totalMarks > 0 ? ((totalObtained / totalMarks) * 100).toFixed(2) : '');
                    row.find('.grade').val(marks[studentId].grade || '');
                });
                toastr.success('Marks picked successfully.', 'Success');
            },
            error: function(xhr) {
                toastr.error(ajaxErrorMessage(xhr), 'Error');
            }
        });
    }

    function showStudentData() {
        const classId = $('#class_name').val();
        const sectionName = $('#section_name').val();
        const examId = $('#exam_name').val();
        const remarkId = $('#remark_id').val();

        if (!classId || !sectionName || !examId || !remarkId) {
            toastr.error('Please select teacher, class, section, term/exam, and remarks first.', 'Required');
            return;
        }

        $.ajax({
            data: {
                teacher_name: $('#teacher_name').val(),
                class: classId,
                section_name: sectionName,
                exam_name: examId
            },
            url: "{{ url($remarkUrlPrefix . 'teacher-remark-class-studentdata') }}",
            headers: { 'X-CSRF-TOKEN': document.getElementsByName('_token')[0].value },
            method: 'POST',
            dataType: 'json',
            success: function(data) {
                let rows = '';
                data.students.forEach((item, index) => {
                    rows += `
                        <tr class="student-row">
                            <td>${index + 1}</td>
                            <td><input type="checkbox" class="student_select" checked></td>
                            <td>
                                ${item.scholar_no || ''}
                                <input type="hidden" class="scholar_no" value="${item.scholar_no || ''}" />
                            </td>
                            <td>${item.student_name || ''}</td>
                            <td>
                                ${item.roll_no || ''}
                                <input type="hidden" class="student_id" value="${item.id}" />
                                <input type="hidden" class="roll_no" value="${item.roll_no || ''}" />
                            </td>
                            <td><input type="text" class="form-control total_marks" value="" readonly /></td>
                            <td><input type="text" class="form-control total_obtained" value="" readonly /></td>
                            <td><input type="text" class="form-control percent" value="" readonly style="width: 80px;" /></td>
                            <td><input type="text" class="form-control grade" value="" readonly /></td>
                            <td><select class="form-control student_remark_id">${remarkOptions(remarkId)}</select></td>
                            <td><input type="text" class="form-control result_remark" value="" /></td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="deleteRowData(event)">Delete</button></td>
                        </tr>`;
                });
                $('#studentdatatable').html(rows || '<tr><td colspan="12" class="text-center">No Data Found</td></tr>');
            },
            error: function(xhr) {
                toastr.error(ajaxErrorMessage(xhr), 'Error');
            }
        });
    }

    function loadTeacherClassAssignments(classId) {
        const teacher = $('#teacher_name').val();
        $('#section_name').html('<option value="">--Select--</option>');
        if (!teacher || !classId) {
            return;
        }

        $.ajax({
            data: {
                teacher: teacher,
                class_name: classId
            },
            url: "{{ url($remarkUrlPrefix . 'getteachersandsubject') }}",
            headers: { 'X-CSRF-TOKEN': document.getElementsByName('_token')[0].value },
            method: 'POST',
            dataType: 'json',
            success: function(data) {
                data.sections.forEach((sectionName) => {
                    $('#section_name').append(`<option value="${sectionName}">${sectionName}</option>`);
                });
            }
        });
    }

    function checkRemarkEntryStatus() {
        @if(!empty($stream_master))
            return;
        @endif
        const data = postData();
        resetRemarkEntryStatus();
        if (!data.teacher_name || !data.class_name || !data.section_name || !data.exam_name) {
            return;
        }

        $.ajax({
            data: data,
            url: "{{ url($remarkUrlPrefix . 'check-teacher-remark-entry-status') }}",
            headers: { 'X-CSRF-TOKEN': document.getElementsByName('_token')[0].value },
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.exists) {
                    $('#remark_entry_status')
                        .removeClass('d-none alert-info')
                        .addClass('alert-warning')
                        .html(`${response.message} <a href="${response.edit_url}" class="alert-link">Open existing entry</a>`);
                    $('#show_students_btn').prop('disabled', true);
                    $('#remark_submit_btn').prop('disabled', true);
                    $('#studentdatatable').html('<tr><td colspan="12" class="text-center">Remarks already entered. Open existing entry to update.</td></tr>');
                }
            }
        });
    }

    $('#class_name').on('change', function() {
        const classId = $(this).val();
        const examDropdown = document.getElementById('exam_name');
        examDropdown.innerHTML = '<option value="">--Select--</option>';
        resetRemarkEntryStatus();
        loadTeacherClassAssignments(classId);
        $('#studentdatatable').html('<tr><td colspan="12" class="text-center">No Data Found</td></tr>');

        if (!classId) {
            return;
        }

        const seenExamIds = new Set();
        allExams
            .filter((exam) => String(exam.marks_class_id) === String(classId) || String(exam.class_id) === String(classId))
            .forEach((exam) => {
                if (seenExamIds.has(exam.id)) {
                    return;
                }
                seenExamIds.add(exam.id);
                const option = document.createElement('option');
                option.value = exam.id;
                option.textContent = exam.exam_name;
                examDropdown.appendChild(option);
            });
    });

    $('#teacher_name, #section_name, #exam_name').on('change', checkRemarkEntryStatus);
    $('#show_students_btn').on('click', showStudentData);
    $('#pick_marks_btn').on('click', pickMarks);
    $('#fill_to_all_btn').on('click', fillToAll);
    $('#select_all_students').on('change', function () {
        $('.student_select').prop('checked', $(this).is(':checked'));
    });

    @if(!empty($isStaffRemarkUser) && !empty($staffEmployeeId) && empty($stream_master))
        document.getElementById('teacher_name').dispatchEvent(new Event('change'));
    @endif
</script>
@endsection
