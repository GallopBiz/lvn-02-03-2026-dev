@extends('backend.layouts.main')
@section('main-container')
@php
    $iaEnabled = !empty($stream_master) && !empty($marksData) && $marksData->contains(function ($marks) {
        return !empty($marks->internal_assessment_marks);
    });
    $practicalEnabled = !empty($stream_master) && !empty($marksData) && $marksData->contains(function ($marks) {
        return (float) ($marks->mark_practical ?? 0) > 0 || !empty($marks->is_absent_pr);
    });
    $marksUrlPrefix = !empty($isStaffMarksUser) ? 'staff/' : '';
@endphp
<style>
    .uperletter {
        text-transform: capitalize;
    }

    .marks-saving-text {
        font-size: 13px;
        color: #555;
        margin-left: 10px;
    }

    .saved-marks-scroll {
        max-height: 520px;
        overflow-y: auto;
    }

</style>

<div class="main-content">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="breadcrumb d-flex justify-content-between align-items-center">
        <h1 class="me-2">Marks Entry :-</h1>
        @if(!empty($stream_master))
            <div>
                @if(!empty($isStaffMarksUser) && empty($stream_master->is_locked))
                    <form action="{{ route('staff.lock-marks-entry', $stream_master->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-dark" onclick="return confirm('Lock this marks entry? After locking, you must contact admin to make changes.');">Lock Entry</button>
                    </form>
                @elseif(!empty($isStaffMarksUser) && !empty($stream_master->is_locked))
                    <button type="button" class="btn btn-secondary" disabled>Entry Locked</button>
                @endif
                <a href="{{ !empty($isStaffMarksUser) ? route('staff.marks') : route('marks') }}" class="btn btn-secondary">Back</a>
            </div>
        @endif
    </div>


    <div class="separator-breadcrumb border-top"></div>
    @if(!empty($stream_master))
    <form id="progress-form" class="p-4 progress-form" onsubmit="handleUpdateData(event)">
        <input type="hidden" @if(!empty($stream_master))  value=" {{ $stream_master->id }}"  @else value="" @endif name="id">
        {{-- <div class="separator-breadcrumb border-top"></div> --}}


        @else
        <form onsubmit="handleSubmitData(event)" id="progress-form" class="p-4 progress-form">
            @endif

            @csrf
            <input type="hidden" id="marks_id" name="marks_id" value="{{ !empty($stream_master) ? $stream_master->id : '' }}">

            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h4 class="mb-3">Select Criteria</h4>

                            <div class="form-group mb-3">
                                <label for="teacher_name">Teacher:</label>
                                <select required name="teacher_name" class="form-control" id="teacher_name" @if (!empty($stream_master) || !empty($isStaffMarksUser)) disabled @endif>
                                    <option value="">-- Please select --</option>
                                    @if (!empty($stream_master))
                                        {{ $c_stream = $stream_master->teacher_id }}
                                    @endif
                                    @foreach ($teacherlist as $teacherlist)
                                        <option {{( (!empty($c_stream)) && ($c_stream==$teacherlist->Teacher->id)) || (!empty($staffEmployeeId) && $staffEmployeeId==$teacherlist->Teacher->id) ? 'selected' : '' }} value="{{ $teacherlist->Teacher->id }}">{{ $teacherlist->Teacher->first_name }} {{ $teacherlist->Teacher->last_name }} - {{ $teacherlist->Teacher->biometricDetails->ess_emp_code?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="class_name">Class:</label>
                                <select required id="class_name" class="form-control" name="class_name" autocomplete="" @if (!empty($stream_master)) disabled @endif>
                                    <option value="">-- Please select --</option>
                                    @if (!empty($stream_master))
                                        <option {{( ($classlist->id == $stream_master->class_id)) ? 'selected' : '' }} value="{{ $classlist->id }}">{{ $classlist->class_name }}</option>
                                    @else
                                        @foreach ($classlist as $class)
                                            <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="section_name">Section:</label>
                                <select required name="section_name" class="form-control" id="section_name" @if (!empty($stream_master)) disabled @endif>
                                    <option value=""> -- Please select -- </option>
                                    @if (!empty($stream_master))
                                        <option selected value="{{$stream_master->section_name}}">{{$stream_master->section_name}}</option>
                                    @endif
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="subject_name">Subject:</label>
                                <select required name="subject_name" class="form-control" id="subject_name" @if (!empty($stream_master)) disabled @endif>
                                    <option value="">-- Please select --</option>
                                    @if (!empty($stream_master))
                                        <option selected value="{{ $stream_master->subject_id }}" data-subject-type="{{ $stream_master->Subject->subject_type ?? '' }}">{{ $stream_master->Subject->subject_name }}</option>
                                    @else
                                        @foreach ($subjectlist as $subjectlist)
                                            <option value="{{ $subjectlist->id }}" data-subject-type="{{ $subjectlist->subject_type ?? '' }}">{{ $subjectlist->subject_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="exam_name">Term/Exam:</label>
                                <select required name="exam_name" class="form-control" id="exam_name" @if (!empty($stream_master)) disabled @endif>
                                    <option value="">-- Please select --</option>
                                    @if (!empty($stream_master))
                                        @foreach ($examslist as $exam)
                                            @if ($exam->id == $stream_master->exam_id)
                                                <option selected value="{{ $exam->id }}">{{ $exam->exam_name }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                    <label for="max_marks_theory">Max Marks (Theory)</label>
                                    <input type="number" class="form-control" id="max_marks_theory" name="max_marks_theory" min="0">
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="max_marks_practical">Max Marks (Practical)</label>
                                    <input type="number" class="form-control" id="max_marks_practical" name="max_marks_practical" min="0">
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="max_marks">Max Marks</label>
                                    <input type="number" class="form-control" id="max_marks" name="max_marks" min="0">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label>
                                    <input type="checkbox" id="internal_assessment_toggle" {{ $iaEnabled ? 'checked' : '' }}>
                                    Internal Assessment
                                </label>
                            </div>
                            <div class="form-group mb-3">
                                <label>
                                    <input type="checkbox" id="practical_marks_toggle" {{ $practicalEnabled ? 'checked' : '' }}>
                                    Practical Marks
                                </label>
                            </div>
                            <button type="button" class="btn btn-info" id="show_students_btn">Show Students</button>
                            <div id="marks_entry_status" class="alert alert-info mt-3 d-none"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h4 class="mb-3">Previously Saved Marks Entry :-</h4>
                            <div class="table-responsive saved-marks-scroll">
                                <table class="display table table-striped table-bordered" id="another_div">
                                    <thead>
                                        <tr>
                                            <th scope="col">S.no.</th>
                                            <th scope="col">Exam Name</th>
                                            <th scope="col">Class</th>
                                            <th scope="col">Section</th>
                                            <th scope="col">Subject</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    @php $i = 0; @endphp
                                    <tbody>
                                        @if(!empty($stream) && count($stream))
                                            @foreach($stream as $streams)
                                                <tr>
                                                    <td>{{++$i}}</td>
                                                    <td class="uperletter">{{ $streams->Exammaster->exam_name ?? '-' }}</td>
                                                    <td>{{$streams->Class->class_name}}</td>
                                                    <td>{{$streams->section_name}}</td>
                                                    <td class="uperletter">{{$streams->Subject->subject_name}}</td>
                                                    <td class="d-flex">
                                                        <a class="btn btn-primary m-1" href="{{ url($marksUrlPrefix . 'view-marks') .'/'.$streams->id}}">Edit</a>
                                                        <?php $a = "previosly_saved_marks_entry"."-".$streams->id ; ?>
                                                        @if(!empty($isStaffMarksUser) && (empty($streams->is_locked) && empty($streams->Exammaster->is_locked)))
                                                            <form action="{{ route('staff.lock-marks-entry', $streams->id) }}" method="POST" style="display:inline;">
                                                                @csrf
                                                                <button type="submit" class="btn btn-dark m-1" onclick="return confirm('Lock this marks entry? After locking, you must contact admin to make changes.');">Lock</button>
                                                            </form>
                                                        @endif
                                                        @if(!empty($isStaffMarksUser) && (!empty($streams->is_locked) || !empty($streams->Exammaster->is_locked)))
                                                            <button type="button" class="btn btn-secondary m-1" disabled>Locked</button>
                                                        @elseif(empty($isStaffMarksUser) && !empty($streams->is_locked))
                                                            <form action="{{ url('unlock-marks-entry/' . $streams->id) }}" method="POST" style="display:inline;">
                                                                @csrf
                                                                <button type="submit" class="btn btn-warning m-1" onclick="return confirm('Unlock this marks entry for staff editing?');">Unlock</button>
                                                            </form>
                                                        @else
                                                            <a class="btn btn-raised ripple btn-danger m-1" href="{{url($marksUrlPrefix . 'delete-marks').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" class="text-center">No Data Found</td>
                                            </tr>
                                        @endif
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
                        <h4 class="mb-0">Student Marks</h4>
                        <div class="d-flex align-items-center">
                            <span id="marks_save_status" class="marks-saving-text d-none">Saving marks...</span>
                            <button type="submit" id="marks_submit_btn" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                    <div class="table-responsive" style="width: 100%;">
                        <table class="display table table-striped table-bordered table-full-width" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th scope="col">S.no.</th>
                                    <th scope="col">
                                        <label>
                                            <input type="checkbox" id="isAbsentToggle" class="toggle-checkbox" data-column-index="1"> Is Absent Th
                                        </label>
                                    </th>
                                    <th scope="col" class="practical-column">
                                        <label>
                                            <input type="checkbox" id="isAbsentPrToggle" class="toggle-checkbox" data-column-index="2"> Is Absent Pr
                                        </label>
                                    </th>
                                    <th scope="col">Scholar no.</th>
                                    <th scope="col">Student Name</th>
                                    <th scope="col">Roll No.</th>
                                    <th scope="col">Marks (Theory)</th>
                                    <th scope="col" class="practical-column">Marks (Practical)</th>
                                    @foreach($internalAssessments ?? [] as $assessment)
                                        <th scope="col" class="ia-column d-none" data-ia-code="{{ $assessment->assessment_code }}">{{ $assessment->assessment_code }}</th>
                                    @endforeach
                                    <th scope="col">Total Marks</th>
                                    <th scope="col">Over All Grade</th>
                                    {{-- <th scope="col">Result</th> --}}
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody id="studentdatatable">
                                @php $i = 0; @endphp
                                @if(!empty($dailyreport))
                                    @foreach($dailyreport as $listR)
                                        <tr>
                                            <th scope="row">{{++$i}}</th>
                                            <td>{{$listR->Teacher_Name}}</td>
                                            <td>{{$listR->class_name}}</td>
                                            <td>{{$listR->section_name}}</td>
                                            <td>{{$listR->period_meeting}}</td>
                                            <td>{{date('d-m-Y', strtotime($listR->create_date))}}</td>
                                        </tr>
                                    @endforeach
                                @elseif(!empty($stream_master))
                                    @foreach ($marksData as $marks )
                                        <tr class="student-row">
                                            <th scope="row">{{++$i}}</th>
                                            <td><input type="checkbox" class="is_absent" @if($marks->is_absent == '1') @checked(true) @endif></td>
                                            <td class="practical-column"><input type="checkbox" class="is_absent_pr" @if($marks->is_absent_pr == '1') @checked(true) @endif /></td>
                                            <td>
                                                {{ $marks->scholar_no }}
                                                <input type="hidden" class="scholar_no" value="{{ $marks->scholar_no ?? '' }}" />
                                            </td>
                                            <td>{{ $marks->student_name }}</td>
                                            <td>
                                                {{ $marks->roll_no ?? '' }}
                                                <input type="hidden" class="student_id" value="{{ $marks->student_id }}" />
                                                <input type="hidden" class="roll_no" value="{{ $marks->roll_no ?? '' }}" />
                                            </td>
                                            <td><input type="text" class="form-control mark_theory" value="{{ $marks->mark_theory ?? $marks->subject_marks ?? 0 }}" /></td>
                                            <td class="practical-column"><input type="text" class="form-control mark_practical" value="{{ $marks->mark_practical ?? 0 }}" /></td>
                                            @php
                                                $rawIaMarks = $marks->internal_assessment_marks ?? null;
                                                $decodedIaMarks = is_string($rawIaMarks) ? json_decode($rawIaMarks, true) : $rawIaMarks;
                                                $iaMarks = is_array($decodedIaMarks) ? $decodedIaMarks : [];
                                                $singleIaMark = '';

                                                if (!is_array($decodedIaMarks) && $rawIaMarks !== null && $rawIaMarks !== '') {
                                                    $singleIaMark = $decodedIaMarks ?? $rawIaMarks;
                                                } elseif (count($iaMarks) === 1 && array_keys($iaMarks) === [0]) {
                                                    $singleIaMark = $iaMarks[0];
                                                }
                                            @endphp
                                            @foreach($internalAssessments ?? [] as $assessment)
                                                <td class="ia-column d-none">
                                                    <input type="text" class="form-control internal_assessment_mark" data-ia-code="{{ $assessment->assessment_code }}" value="{{ $iaMarks[$assessment->assessment_code] ?? ($loop->first ? $singleIaMark : '') }}" />
                                                </td>
                                            @endforeach
                                            <td><input type="text" class="form-control total_marks" value="{{ $marks->total_marks ?? $marks->subject_marks ?? 0 }}" readonly /></td>
                                            <td><input type="text" class="form-control grade" value="{{ $marks->grade ?? $marks->overall_grade ?? '' }}" readonly /></td>
                                            {{-- <td>
                                                <select class="form-control result">
                                                    <option value="">--</option>
                                                    <option value="D" {{ (($marks->result ?? '') == 'D') ? 'selected' : '' }}>D</option>
                                                    <option value="S" {{ (($marks->result ?? '') == 'S') ? 'selected' : '' }}>S</option>
                                                </select>
                                            </td> --}}
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


<!-- end of main-content -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var select = document.getElementById('teacher_name');
        for (var i = 0; i < select.options.length; i++) {
            select.options[i].innerText = capitalizeFirstLetter(select.options[i].innerText);
        }
    });

    function capitalizeFirstLetter(str) {
        var words = str.toLowerCase().split(' ');
        for (var i = 0; i < words.length; i++) {
            words[i] = words[i].charAt(0).toUpperCase() + words[i].substring(1);
        }
        return words.join(' ');
    }

    $("#reset").on("click", function() {
        $('#progress-form select').each(function(idx, sel) {
            for (let i = 0; i < sel.children.length; i++) {
                sel.children[i].removeAttribute("selected");
            }
        });
        $('#progress-form input').each(function(idx, sel) {
            for (let i = 0; i < sel.children.length; i++) {
                sel.children[i].value = "";
            }
        });
    });

</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var select = document.getElementById('exam_name');
        for (var i = 0; i < select.options.length; i++) {
            select.options[i].innerText = capitalizeFirstLetter(select.options[i].innerText);
        }
    });

    function capitalizeFirstLetter(str) {
        var words = str.toLowerCase().split(' ');
        for (var i = 0; i < words.length; i++) {
            words[i] = words[i].charAt(0).toUpperCase() + words[i].substring(1);
        }
        return words.join(' ');
    }

</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var select = document.getElementById('subject_name');
        for (var i = 0; i < select.options.length; i++) {
            select.options[i].innerText = capitalizeFirstLetter(select.options[i].innerText);
        }
    });

    function capitalizeFirstLetter(str) {
        var words = str.toLowerCase().split(' ');
        for (var i = 0; i < words.length; i++) {
            words[i] = words[i].charAt(0).toUpperCase() + words[i].substring(1);
        }
        return words.join(' ');
    }

</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    function confirmDelete(event) {
        event.preventDefault(); // Prevents the default link navigation

        Swal.fire({
            title: 'Are you sure?'
            , text: "You won't be able to revert this!"
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonColor: '#d33'
            , cancelButtonColor: '#3085d6'
            , confirmButtonText: 'Yes, delete it!'
            , cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // If the user clicks on "Yes, delete it!", navigate to the delete URL
                window.location.href = event.target.href;
            }
        });
    }

</script>
<script>
    const allExams = @json($examslist);
    const activeInternalAssessments = @json($internalAssessments ?? []);
    const isStaffMarksUser = @json(!empty($isStaffMarksUser));
    const isCurrentMarksEntryLocked = @json(!empty($stream_master->is_locked));
    const hasSavedPracticalMarks = @json($practicalEnabled);
    const gradeRanges = @json($gradeRanges ?? []);

    function resetExamMaxMarks() {
        $('#max_marks_theory').val('');
        $('#max_marks_practical').val('');
        $('#max_marks').val('');
        $('.total_marks').removeAttr('data-max');
    }

    function setExamMaxMarks(examId) {
        const exam = allExams.find(item => String(item.id) === String(examId));
        if (!exam) {
            resetExamMaxMarks();
            return;
        }

        const theory = Number(exam.max_marks_theory || 0);
        const practical = Number(exam.max_marks_practical || 0);
        const total = theory + practical;

        $('#max_marks_theory').val(theory || '');
        $('#max_marks_practical').val(practical || '');
        $('#max_marks').val(total || '');
        $('#practical_marks_toggle').prop('checked', practical > 0 || hasSavedPracticalMarks);
        togglePracticalColumns();

        if (total > 0) {
            $('.total_marks').attr('data-max', total);
        } else {
            $('.total_marks').removeAttr('data-max');
        }

        applyExamLockState(exam);
    }

    function refreshTotalMaxMarks() {
        const theory = Number($('#max_marks_theory').val() || 0);
        const practical = Number($('#max_marks_practical').val() || 0);
        const total = theory + practical;

        $('#max_marks').val(total || '');
        if (total > 0) {
            $('.total_marks').attr('data-max', total);
        } else {
            $('.total_marks').removeAttr('data-max');
        }
    }

    function numericValue(value) {
        const parsed = parseFloat(value);
        return isNaN(parsed) ? 0 : parsed;
    }

    function isPracticalEnabled() {
        return $('#practical_marks_toggle').is(':checked');
    }

    function selectedExam() {
        const examId = $('#exam_name').val();
        return allExams.find(item => String(item.id) === String(examId)) || null;
    }

    function isPtExam() {
        const exam = selectedExam();
        return exam && /\bPT\s*[-]?\s*[12]\b/i.test(String(exam.exam_name || ''));
    }

    function roundMark(value) {
        return Math.round(value);
    }

    function convertPtMarksToFivePoint(obtainedMarks) {
        const maxMarks = numericValue($('#max_marks').val());
        if (!isPtExam() || maxMarks <= 0) {
            return obtainedMarks;
        }

        return roundMark((obtainedMarks / maxMarks) * 5);
    }

    function selectedClassName() {
        return $('#class_name option:selected').text().trim();
    }

    function selectedSubjectType() {
        return $('#subject_name option:selected').data('subject-type') || '';
    }

    function gradeFromMaster(marks) {
        const className = selectedClassName();
        const subjectType = String(selectedSubjectType()).trim().toLowerCase();
        if (!subjectType) {
            return '';
        }

        const match = gradeRanges.find((range) => {
            let classes = [];
            try {
                classes = range.groups ? JSON.parse(range.groups) : [];
            } catch (e) {
                classes = [];
            }

            const classMatches = classes.length === 0 || classes.includes(className);
            const subjectMatches = String(range.subject_type || '').trim().toLowerCase() === subjectType;
            const from = Number(range.min_per || 0);
            const to = Number(range.max_per || 0);

            return classMatches && subjectMatches && marks >= from && marks <= to;
        });

        return match ? (match.grade || '') : '';
    }

    function recalculateStudentTotal(row) {
        const theory = numericValue(row.find('.mark_theory').val());
        const practical = isPracticalEnabled() ? numericValue(row.find('.mark_practical').val()) : 0;
        let internalAssessmentTotal = 0;
        if (isInternalAssessmentEnabled()) {
            row.find('.internal_assessment_mark').each(function () {
                internalAssessmentTotal += numericValue($(this).val());
            });
        }
        const total = roundMark(convertPtMarksToFivePoint(theory + practical) + internalAssessmentTotal);
        row.find('.total_marks').val(total);
        row.find('.grade').val(gradeFromMaster(total));
    }

    function resetMarksEntryStatus() {
        $('#marks_entry_status').addClass('d-none').removeClass('alert-warning alert-info').addClass('alert-info').html('');
        $('#show_students_btn').prop('disabled', false);
        $('#marks_submit_btn').prop('disabled', false);
        applyMarksEntryLockState();
    }

    function applyMarksEntryLockState() {
        if (!isStaffMarksUser || !isCurrentMarksEntryLocked) {
            return;
        }

        $('#marks_entry_status')
            .removeClass('d-none alert-info')
            .addClass('alert-warning')
            .html('This marks entry is locked. Please contact admin to unlock it before editing or deleting marks.');
        $('#show_students_btn').prop('disabled', true);
        $('#marks_submit_btn').prop('disabled', true);
        $('#studentdatatable').find('input, select, button').prop('disabled', true);
    }

    function applyExamLockState(exam) {
        if (!isStaffMarksUser || !exam || Number(exam.is_locked || 0) !== 1) {
            return;
        }

        $('#marks_entry_status')
            .removeClass('d-none alert-info')
            .addClass('alert-warning')
            .html('This exam is locked. Please contact admin to unlock it before editing or deleting marks.');
        $('#show_students_btn').prop('disabled', true);
        $('#marks_submit_btn').prop('disabled', true);
    }

    function isInternalAssessmentEnabled() {
        return $('#internal_assessment_toggle').is(':checked');
    }

    function toggleInternalAssessmentColumns() {
        $('.ia-column').toggleClass('d-none', !isInternalAssessmentEnabled());
        $('.student-row').each(function () {
            recalculateStudentTotal($(this));
        });
    }

    function togglePracticalColumns() {
        $('.practical-column').toggleClass('d-none', !isPracticalEnabled());
        if (!isPracticalEnabled()) {
            $('.mark_practical').val(0);
            $('.is_absent_pr').prop('checked', false);
            $('#isAbsentPrToggle').prop('checked', false);
        }
        $('.student-row').each(function () {
            recalculateStudentTotal($(this));
        });
    }

    function internalAssessmentCells() {
        return activeInternalAssessments.map((assessment) => `
            <td class="ia-column ${isInternalAssessmentEnabled() ? '' : 'd-none'}">
                <input type="text" class="form-control internal_assessment_mark" data-ia-code="${assessment.assessment_code}" value="" />
            </td>
        `).join('');
    }

    function collectInternalAssessmentMarks(row) {
        const marks = {};
        if (!isInternalAssessmentEnabled()) {
            return marks;
        }

        row.find('.internal_assessment_mark').each(function () {
            const input = $(this);
            marks[input.attr('data-ia-code')] = input.val();
        });

        return marks;
    }

    function ajaxErrorMessage(xhr) {
        return xhr.responseJSON?.message
            || xhr.responseJSON?.errors?.[Object.keys(xhr.responseJSON?.errors || {})[0]]?.[0]
            || xhr.responseText
            || "Marks could not be saved. Please try again.";
    }

    function maxMarksAlert() {
        alert('Entered marks should not be greater than maximum marks.');
    }

    function validateObtainedMarks() {
        const maxTheory = numericValue($('#max_marks_theory').val());
        const maxPractical = numericValue($('#max_marks_practical').val());
        const maxTotal = numericValue($('#max_marks').val());
        let isValid = true;

        $('#studentdatatable').find('.student-row').each(function () {
            const row = $(this);
            const theory = numericValue(row.find('.mark_theory').val());
            const practical = isPracticalEnabled() ? numericValue(row.find('.mark_practical').val()) : 0;

            if ((maxTheory > 0 && theory > maxTheory)
                || (isPracticalEnabled() && maxPractical > 0 && practical > maxPractical)
                || (maxTotal > 0 && theory + practical > maxTotal)) {
                isValid = false;
                return false;
            }
        });

        if (!isValid) {
            maxMarksAlert();
        }

        return isValid;
    }
</script>
<script>
    function deleteRowData(e) {
        const elm = e.target.parentElement.parentElement;
        elm.remove();
    }
        document.getElementById("teacher_name").addEventListener("change", (e) => {
            let teacher = e.target.value;
            let token = document.getElementsByName("_token")[0].value

            $.ajax({
                data: {
                    teacher: teacher
                },
                url: "{{ url($marksUrlPrefix . 'getteachersdata') }}",
                headers: {
                    'X-CSRF-TOKEN': token
                },
                method: "POST",
                dataType: 'json',
                success: function(data) {
                    // Handle classes dropdown
                    $('#class_name').html('<option value=""> -- Select All -- </option>');
                    for (var i = 0; i < data['classes'].length; i++) {
                        var className = data['classes'][i].class.class_name;
                        var class_id = data['classes'][i].class_id;
                        $('#class_name').append('<option value="' + class_id + '">' + className + '</option>');
                    }

                    // Handle subjects dropdown
                    $('#subject_name').html('<option value=""> -- Select All -- </option>');
                    for (var i = 0; i < data['subjects'].length; i++) {
                        var subjectName = data['subjects'][i].subject.subject_name;
                        var subject_id = data['subjects'][i].subject_id;
                        var subjectType = data['subjects'][i].subject.subject_type || '';
                        $('#subject_name').append('<option value="' + subject_id + '" data-subject-type="' + subjectType + '">' + subjectName + '</option>');
                    }
                    $('#section_name').html('<option value=""> -- Select All -- </option>');
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });
        function loadTeacherClassAssignments(classId, subjectId = '') {
            const teacher = $("#teacher_name").val();
            let token = document.getElementsByName("_token")[0].value;

            $('#subject_name').html('<option value=""> -- Select All -- </option>');
            $('#section_name').html('<option value=""> -- Select All -- </option>');

            if (!teacher || !classId) {
                return;
            }

            $.ajax({
                data: {
                    teacher: teacher,
                    class_name: classId,
                    subject_id: subjectId
                },
                url: "{{ url($marksUrlPrefix . 'getteachersandsubject') }}",
                headers: {
                    'X-CSRF-TOKEN': token
                },
                method: "POST",
                dataType: 'json',
                success: function(data) {
                    for (var i = 0; i < data['sections'].length; i++) {
                        var sectionName = data['sections'][i];
                        $('#section_name').append('<option value="' + sectionName + '">' + sectionName + '</option>');
                    }

                    for (var i = 0; i < data['subjects'].length; i++) {
                        if (!data['subjects'][i].subject) {
                            continue;
                        }
                        var subjectName = data['subjects'][i].subject.subject_name;
                        var subjectId = data['subjects'][i].subject_id;
                        var subjectType = data['subjects'][i].subject.subject_type || '';
                        var selected = subjectId == $("#subject_name").data('selected-subject') ? ' selected' : '';
                        $('#subject_name').append('<option value="' + subjectId + '" data-subject-type="' + subjectType + '"' + selected + '>' + subjectName + '</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
        @if(!empty($isStaffMarksUser) && !empty($staffEmployeeId) && empty($stream_master))
            document.getElementById("teacher_name").dispatchEvent(new Event("change"));
        @endif
        function checkMarksEntryStatus() {
            @if(!empty($stream_master))
                return;
            @endif
            const teacherName = $("#teacher_name").val();
            const className = $("#class_name").val();
            const sectionName = $("#section_name").val();
            const examName = $("#exam_name").val();
            const subjectName = $("#subject_name").val();
            let token = document.getElementsByName("_token")[0].value;

            resetMarksEntryStatus();
            if (!teacherName || !className || !sectionName || !examName || !subjectName) {
                return;
            }

            $.ajax({
                data: {
                    teacher_name: teacherName,
                    class_name: className,
                    section_name: sectionName,
                    exam_name: examName,
                    subject_name: subjectName
                },
                url: "{{ url($marksUrlPrefix . 'check-marks-entry-status') }}",
                headers: {
                    'X-CSRF-TOKEN': token
                },
                method: "POST",
                dataType: 'json',
                success: function(data) {
                    if (data.is_locked) {
                        $('#marks_entry_status')
                            .removeClass('d-none alert-info')
                            .addClass('alert-warning')
                            .html(data.message);
                        $('#show_students_btn').prop('disabled', true);
                        $('#marks_submit_btn').prop('disabled', true);
                        $("#studentdatatable").html('<tr><td colspan="12" class="text-center">Exam is locked. Contact admin to unlock.</td></tr>');
                        toastr.warning(data.message, "Exam Locked");
                        return;
                    }

                    if (data.exists) {
                        $('#marks_entry_status')
                            .removeClass('d-none alert-info')
                            .addClass('alert-warning')
                            .html(`${data.message} <a href="${data.edit_url}" class="alert-link">Open existing entry</a>`);
                        $('#show_students_btn').prop('disabled', true);
                        $('#marks_submit_btn').prop('disabled', true);
                        $("#studentdatatable").html('<tr><td colspan="12" class="text-center">Marks already entered. Open existing entry to update.</td></tr>');
                        toastr.info(data.message, "Already Completed");
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
        function showStudentData() {
            var iso2 = $("#class_name").val();
            var section_name = $("#section_name").val();
            var subject_id = $("#subject_name").val();
            var exam_id = $("#exam_name").val();
            let token = document.getElementsByName("_token")[0].value;
            if (iso2 && section_name && subject_id && exam_id) {
                $.ajax({
                    data: {
                        class: iso2,
                        section_name:section_name,
                        subject_id:subject_id,
                        exam_name: exam_id
                    },
                    url: "{{url($marksUrlPrefix . 'class-studentdata')}}",
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                    method: "POST",
                    dataType: 'json',
                    success: function(data) {
                        let rows = '';
                        data.students.map((item, index) => {
                            rows += `
                            <tr class="student-row">
                                <td>${index + 1}</td>
                                <td><input type="checkbox" class="is_absent" /></td>
                                <td class="practical-column"><input type="checkbox" class="is_absent_pr" /></td>
                                <td>
                                    ${item.scholar_no || ''}
                                    <input type="hidden" class="scholar_no" value="${item.scholar_no || ''}" />
                                </td>
                                <td>${item.student_name}</td>
                                <td>
                                    ${item.roll_no || ''}
                                    <input type="hidden" class="student_id" value="${item.id}" />
                                    <input type="hidden" class="roll_no" value="${item.roll_no || ''}" />
                                </td>
                                <td><input type="text" class="form-control mark_theory" value="0" /></td>
                                <td class="practical-column"><input type="text" class="form-control mark_practical" value="0" /></td>
                                ${internalAssessmentCells()}
                                <td><input type="text" class="form-control total_marks" value="0" readonly /></td>
                                <td><input type="text" class="form-control grade" value="" readonly /></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="deleteRowData(event)">Delete</button></td>
                            </tr>`;
                        })

                        $("#studentdatatable").html(rows)
                        setExamMaxMarks($("#exam_name").val());
                        toggleInternalAssessmentColumns();
                        togglePracticalColumns();

                    }
                    , error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            } else {
                toastr.error("Please select class, section, subject, and term/exam first.", "Required");
            }
        }
        $('#class_name').on('change', function() {
            var classId = $(this).val();
            const examDropdown = document.getElementById('exam_name');

                // Clear previous options
                examDropdown.innerHTML = '<option value="">-- Please select --</option>';
                resetExamMaxMarks();
                resetMarksEntryStatus();
                $("#subject_name").removeData('selected-subject');
                loadTeacherClassAssignments(classId);
                $('.student-row').each(function () {
                    recalculateStudentTotal($(this));
                });
                $("#studentdatatable").html('<tr><td colspan="12" class="text-center">No Data Found</td></tr>');

                if (!classId) return;

                // Filter exams based on selected class ID
                const filteredExams = allExams.filter(exam => exam.marks_class_id == classId || exam.class_id == classId);
                const uniqueExams = [];
                const seenExamIds = new Set();
                filteredExams.forEach(exam => {
                    if (!seenExamIds.has(exam.id)) {
                        seenExamIds.add(exam.id);
                        uniqueExams.push(exam);
                    }
                });
                // Populate the exam dropdown
                uniqueExams.forEach(exam => {
                    const option = document.createElement('option');
                    option.value = exam.id;
                    option.textContent = exam.exam_name;
                    examDropdown.appendChild(option);
                });
        });
        $('#exam_name').on('change', function() {
            setExamMaxMarks($(this).val());
            checkMarksEntryStatus();
        });
        @if(!empty($stream_master))
            setExamMaxMarks("{{ $stream_master->exam_id }}");
        @endif
        $('#max_marks_theory, #max_marks_practical').on('input', refreshTotalMaxMarks);
        $('#max_marks').on('input', function() {
            const total = Number($(this).val() || 0);
            if (total > 0) {
                $('.total_marks').attr('data-max', total);
            } else {
                $('.total_marks').removeAttr('data-max');
            }
        });
        $('#subject_name').on('change', function() {
            const classId = $("#class_name").val();
            const subjectId = $(this).val();
            $("#subject_name").data('selected-subject', subjectId);
            if (classId) {
                loadTeacherClassAssignments(classId, subjectId);
            }
            $('.student-row').each(function () {
                recalculateStudentTotal($(this));
            });
            checkMarksEntryStatus();
        });
        $('#teacher_name, #section_name').on('change', checkMarksEntryStatus);
        $('#internal_assessment_toggle').on('change', toggleInternalAssessmentColumns);
        $('#practical_marks_toggle').on('change', togglePracticalColumns);
        toggleInternalAssessmentColumns();
        togglePracticalColumns();
        applyMarksEntryLockState();
        $('#show_students_btn').on('click', () => {
             showStudentData();
        })
        $(document).on('input', '.mark_theory, .mark_practical, .internal_assessment_mark', function() {
            if (!validateObtainedMarks()) {
                $(this).val('');
            }
            recalculateStudentTotal($(this).closest('tr'));
        });
        function setMarksSaving(isSaving) {
            const submitBtn = $('#marks_submit_btn');
            submitBtn.prop('disabled', isSaving);
            submitBtn.text(isSaving ? 'Saving...' : 'Submit');
            $('#marks_save_status').toggleClass('d-none', !isSaving);
        }
        $('#isAbsentToggle').on('change', (e) => {
            let bodydata = document.getElementById("studentdatatable").children
            for (var i = 0; i < bodydata.length; i++) {
                var row = bodydata[i];
                row.children[1].children[0].checked = e.target.checked
            }
        })
        $('#isAbsentPrToggle').on('change', (e) => {
            let bodydata = document.getElementById("studentdatatable").children
            for (var i = 0; i < bodydata.length; i++) {
                var row = bodydata[i];
                row.children[2].children[0].checked = e.target.checked
            }
        })
    function handleSubmitData(event) {
        event.preventDefault();
        if (!validateObtainedMarks()) {
            return;
        }
        let token = document.getElementsByName("_token")[0].value;
        let data = [];
        let bodydata = document.getElementById("studentdatatable").children;
        let studentData = [];
        $('#studentdatatable').find('.student-row').each(function () {
            const row = $(this);
            const studentId = row.find('.student_id').val();
            const rollNo = row.find('.roll_no').val();
            const scholarNo = row.find('.scholar_no').val();
            const markTheory = row.find('.mark_theory').val();
            const markPractical = isPracticalEnabled() ? row.find('.mark_practical').val() : 0;
            const totalMarks = row.find('.total_marks').val();
            const grade = row.find('.grade').val();
            const result = '';
            const internalAssessments = collectInternalAssessmentMarks(row);
            const isAbsent = row.find('.is_absent').is(':checked') ? 1 : 0;
            const isAbsentPr = isPracticalEnabled() && row.find('.is_absent_pr').is(':checked') ? 1 : 0;

            studentData.push({
                student_id: studentId,
                roll_no: rollNo,
                scholar_no: scholarNo,
                mark_theory: markTheory,
                mark_practical: markPractical,
                total_marks: totalMarks,
                grade: grade,
                result: result,
                internal_assessments: internalAssessments,
                internal_assessment_marks: JSON.stringify(internalAssessments),
                is_absent: isAbsent,
                is_absent_pr: isAbsentPr
            });
        });
        let postData = {
            teacher_name: $("#teacher_name").val(),
            class_name: $("#class_name").val(),
            section_name: $("#section_name").val(),
            exam_name: $("#exam_name").val(),
            subject_name: $("#subject_name").val(),
            max_marks_theory: $("#max_marks_theory").val(),
            max_marks_practical: $("#max_marks_practical").val(),
            max_marks: $("#max_marks").val(),
            students: studentData
        };
        $.ajax({
            data: postData
            , url: "{{url($marksUrlPrefix . 'save-marks')}}"
            , headers: {
                'X-CSRF-TOKEN': token
            }
            , method: "POST"
            , dataType: 'json'
            , beforeSend: function() {
                setMarksSaving(true);
            }
            , complete: function() {
                setMarksSaving(false);
            }
            , success: function(response) {
                if (response.status === "success") {
                    toastr.success("Updated successfully.", "Success");
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    console.error("Unexpected response format.");
                }
            },

            error: function(xhr, status, error) {
                console.error(error);
                toastr.error(ajaxErrorMessage(xhr), "Error");
            }
        });
    }

    function handleUpdateData(event) {
        event.preventDefault();
        if (!validateObtainedMarks()) {
            return;
        }
        let token = document.getElementsByName("_token")[0].value;
        let data = [];
        let bodydata = document.getElementById("studentdatatable").children;
        let studentData = [];
        $('#studentdatatable').find('.student-row').each(function () {
            const row = $(this);
            const studentId = row.find('.student_id').val();
            const rollNo = row.find('.roll_no').val();
            const scholarNo = row.find('.scholar_no').val();
            const markTheory = row.find('.mark_theory').val();
            const markPractical = isPracticalEnabled() ? row.find('.mark_practical').val() : 0;
            const totalMarks = row.find('.total_marks').val();
            const grade = row.find('.grade').val();
            const result = '';
            const internalAssessments = collectInternalAssessmentMarks(row);
            const isAbsent = row.find('.is_absent').is(':checked') ? 1 : 0;
            const isAbsentPr = isPracticalEnabled() && row.find('.is_absent_pr').is(':checked') ? 1 : 0;

            studentData.push({
                student_id: studentId,
                roll_no: rollNo,
                scholar_no: scholarNo,
                mark_theory: markTheory,
                mark_practical: markPractical,
                total_marks: totalMarks,
                grade: grade,
                result: result,
                internal_assessments: internalAssessments,
                internal_assessment_marks: JSON.stringify(internalAssessments),
                is_absent: isAbsent,
                is_absent_pr: isAbsentPr
            });
        });
        let postData = {
            marks_id: $("#marks_id").val(),
            teacher_name: $("#teacher_name").val(),
            class_name: $("#class_name").val(),
            section_name: $("#section_name").val(),
            exam_name: $("#exam_name").val(),
            subject_name: $("#subject_name").val(),
            max_marks_theory: $("#max_marks_theory").val(),
            max_marks_practical: $("#max_marks_practical").val(),
            max_marks: $("#max_marks").val(),
            students: studentData
        };
        $.ajax({
            data: postData
            , url: "{{url($marksUrlPrefix . 'store-marks')}}"
            , headers: {
                'X-CSRF-TOKEN': token
            }
            , method: "POST"
            , dataType: 'json'
            , beforeSend: function() {
                setMarksSaving(true);
            }
            , complete: function() {
                setMarksSaving(false);
            }
            , success: function(response) {
                if (response.status === "success") {
                    toastr.success("Updated successfully.", "Success");
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    console.error("Unexpected response format.");
                }
            },

            error: function(xhr, status, error) {
                console.error(error);
                toastr.error(ajaxErrorMessage(xhr), "Error");
            }
        });
    }
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



@endsection
