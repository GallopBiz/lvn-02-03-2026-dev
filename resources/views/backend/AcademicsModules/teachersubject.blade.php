@extends('backend.layouts.main')
@section('main-container')
@php
$i = 0;
@endphp
<div class="main-content">
    <div class="form_section1_div">
        <div class="breadcrumb">
            @if (!empty($teacher_subject))
            <h1 class="me-2">Edit Teacher Class Subject </h1>
            @else
            <h1 class="me-2">Teacher Class Subject </h1>
            @endif
        </div>

        <div class="separator-breadcrumb border-top"></div>
        @if (!empty($teacher_subject['id']))

        <form id="progress-form" class="p-4 progress-form" action="{{ url('store-teachersubject') }}" method="post">
            <input type="hidden" value="{{$teacher_subject['id']}}" name="id">
            @else
            <form id="progress-form" class="p-4 progress-form" action="{{ url('save-teachersubject') }}" method="post">
                @endif
                @csrf
                <div class="row">

                    <div class="col-md-3 form-group mb-3">
                        <label for="class_name">Class Name <span class="text-danger">*</span></label>
                        <select required id="class_name" class="form-control" name="class_name" autocomplete=""
                            required>
                            @if (!empty($teacher_subject))
                            <option value="" disabled hidden>Please select</option>
                            @foreach($classlist as $each)
                            <option value="{{$each->id}}" {{ ($teacher_subject->class_id == $each->id)
                                ? 'selected' : ''}}>{{$each->class_name}}</option>
                            @endforeach
                            @else
                            <option value="" disabled selected>Please select</option>
                            @foreach($classlist as $each)
                            <option value="{{$each->id}}">{{$each->class_name}}</option>
                            @endforeach
                            @endif
                            {{-- @foreach(config('global.class_name') as $each)
                            <option value="{{$each}}">{{$each}}</option>
                            @endforeach --}}
                        </select>
                        <span class="classname_msg validation_err"></span>
                    </div>
                    {{-- <div class="col-md-3 form-group mb-3">
                        <label for="class_name">Class Name</label>
                        <select id="class_name" class="form-control" name="class_name" autocomplete="" required>
                            <option value="" disabled selected>Please select</option>
                            @foreach($classlist as $each)
                            <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                            @endforeach
                            {{-- @foreach(config('global.class_name') as $each)
                            <option value="{{$each}}">{{$each}}</option>
                            @endforeach --}}
                            {{--
                        </select>
                        <span class="classname_msg validation_err"></span>
                    </div> --}}

                    {{-- <div class="col-md-3 form-group mb-3">
                        <label for="studentname">Class</label>
                        <select id="classname" class="form-control" name="classname"
                            autocomplete="shipping address-level1" required>
                            @if(!empty($classname))
                            <option value=""> -- Please select -- </option>
                            @foreach($datas as $country)
                            @if($classname == $country->class_name)
                            <option selected value="{{$country->class_name}}">{{$country->class_name}}</option>
                            @else
                            <option value="{{$country->class_name}}">{{$country->class_name}}</option>
                            @endif
                            @endforeach
                            @else
                            <option value="" selected> -- Please select -- </option>
                            @foreach($datas as $country)
                            <option value="{{$country->class_name}}">{{$country->class_name}}</option>
                            @endforeach
                            @endif
                        </select>
                    </div> --}}
                    {{-- <div class="col-md-3 form-group mb-3"> --}}
                        {{--
                        <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                        <label for="lastName1">Class Name</label>
                        <select name="class_name" class="form-control" id="class_name">
                            @if (!empty($teacher_subjects))
                            <option value=""> -- Please select -- </option>
                            <option value="" selected> -- Please select -- </option>
                            @foreach ($studentclasses as $studentclasses)
                            <option value="{{ $studentclasses->class_name }}" {{
                                $teacher_subjects[sizeof($teacher_subjects) - 1]->class_name ==
                                $studentclasses->class_name ? 'selected' : '' }}>
                                {{ $studentclasses->class_name }}</option>
                            @endforeach
                            @else
                            <option value="" selected> -- Please select -- </option>
                            @foreach ($studentclasses as $studentclasses)
                            <option value="{{ $studentclasses->class_name }}">{{ $studentclasses->class_name }}
                            </option>
                            @endforeach
                        </select>
                        @endif
                    </div> --}}
                    {{--
                         <div class="col-md-3 form-group mb-3">
                            <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                            <label for="lastName1">Section Name</label>
                            <select name="section_name" class="form-control" id="section_name">
                                @if (!empty($teacher_subjects))
                                <option value="" selected> -- Please select -- </option>
                                <!-- <option value="All">All</option> -->
                                <option value="All" {{ $teacher_subjects[sizeof($teacher_subjects) - 1]->section_name ==
                                    'All' ? 'selected' : '' }}>
                                    All</option>
                                <option value="A" {{ $teacher_subjects[sizeof($teacher_subjects) - 1]->section_name == 'A' ?
                                    'selected' : '' }}>
                                    A</option>

                                <option value="B" {{ $teacher_subjects[sizeof($teacher_subjects) - 1]->section_name == 'B' ?
                                    'selected' : '' }}>
                                    B</option> --}}
                                <!-- <option value="A">A</option>
                                            <option value="B">B</option>
                                            <option value="C">C</option> -->
                                {{-- @else
                                <option value="" selected> -- Please select -- </option>
                                <option value="All">All</option>
                                <option value="A">A</option>
                                <option value="B">B</option>


                            </select>
                            @endif
                    --}}
                        <div class="col-md-3 form-group mb-3">
                            <label for="section_name">Section <span class="text-danger">*</span></label>
                            <select required id="section_name" class="form-control" name="section_name"
                                autocomplete="shipping address-level1" required>
                                <option value=""> -- Select All -- </option>
                                <option value="All">All</option>
                                <option value="Kautilya" @if (!empty($teacher_subject) && $teacher_subject->section_name == "Kautilya") selected @endif>Kautilya</option>
                                <option value="Ramanujan" @if (!empty($teacher_subject) && $teacher_subject->section_name == "Ramanujan") selected @endif>Ramanujan</option>
                                <option value="Aryabhatta" @if (!empty($teacher_subject) && $teacher_subject->section_name == "Aryabhatta") selected @endif>Aryabhatta</option>
                            </select>
                        </div>
                        {{--
                    </div> --}}
                    {{-- <div class="col-md-3 form-group mb-3">
                        <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                        <label for="lastName1">Subject Name</label>
                        <select name="subject_name" class="form-control" id="subject_name">
                            @if(!empty($teacher_subject))
                            <option value=""> -- Please select -- </option>
                            @foreach($subjects as $subject)
                            @if($teacher_subject['subject_name'] == $subject->subject_name)
                            <option selected value="{{$subject->subject_name}}">{{$subject->subject_name}}</option>
                            @else
                            <option value="{{$subject->subject_name}}">{{$subject->subject_name}}</option>
                            @endif
                            @endforeach
                            @else
                            <option value="" selected> -- Please select -- </option>
                            @foreach ($subjects as $subject)
                            <option value="{{ $subject->subject_name }}">{{ $subject->subject_name }}</option>

                            {{-- <option {{( (!empty($c_stream)) && ($c_stream==$subject->subject_name)) ? 'selected' :
                                '' }} value="{{ $subject->subject_name }}">{{ $subject->subject_name }}</option> --}}

                            {{-- @endforeach --}}
                            {{-- @endif --}}


                            {{--
                        </select> --}}
                        {{--
                    </div> --}}

                    <div class="col-md-3 form-group mb-3">
                        <label for="subject_name">Subject Name <span class="text-danger">*</span></label>
                        <select required name="subject_name" class="form-control" id="subject_name">
                            <option value="">-- Please select --</option>
                            @if(!empty($teacher_subject))
                            @foreach($subjects as $subject)
                            <option {{ $teacher_subject->subject_id ==$subject->id ? 'selected' : '' }}
                                value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-3">
                        <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                        <label for="teacher_name">Teacher Name</label>
                        <!-- <option value="11th SC(BPSY)">Teacher name 1</option> -->
                        <!-- <option value="11th SC(MPSY)">Teacher name 2</option> -->
                        <select required name="teacher_name" class="form-control" id="teacher_name">
                            <option value="">-- Please select --</option>
                            @foreach ($employee as $each)
                            <option {{( (!empty($teacher_subject)) && ($teacher_subject->teacher_id == $each->id)) ? 'selected' : '' }} value="{{ $each->id }}">
                                {{ $each->first_name }} {{ $each->last_name }} - {{ $each->biometricDetails->ess_emp_code?? '' }}</option>
                            @endforeach
                            
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-3">
                        <td>
                            <label for="role">Role:</label>
                        </td>
                        <td>
                            <select required name="role" class="form-control" id="role">
                                <option value="">-- Please select --</option>
                                <option value="Class Teacher" @if (!empty($teacher_subject) && $teacher_subject->role == "Class Teacher") selected @endif>Class Teacher</option>
                                <option value="Regular Teacher" @if (!empty($teacher_subject) && $teacher_subject->role == "Regular Teacher") selected @endif>Regular Teacher</option>
                            </select>
                        </td>
                    </div>
                    {{-- <div class="row"> --}}

                        <!--
                                <div class="col-md-3 form-group mb-3">
                                        <label for="remarks"> </label>
                                        <input
                                          class="form-control"
                                          id="remarks"
                                          name="remarks"
                                          type="text"
                                          {{-- @if (!empty($exam_master)) @foreach ($exam_master as $exammaster) --}}
                                          {{-- value=" {{ $exammaster->remarks }}" --}}
                                        {{-- @endforeach --}}
                                      {{-- @else --}}
                                        {{-- value="" @endif --}}
                                          placeholder="Remark"
                                        />
                                      </div> -->


                        <div class="col-md-12">
                            <button class="btn btn-primary" name="btn" value="submit text">Submit</button>
                            {{-- <button type="button" id="reset" class="btn btn-primary" name="btn"
                                value="Reset Form">Reset</button> --}}

                            @if(request()->route()->getName() !== 'teachersubject')
                            <a href="{{ url('teachersubject') }}" class="btn btn-primary">Add New</a>
                            @endif




                        </div>
                    </div>
            </form><br>


            <form id="" class="" action="{{ url('copy-teachersubject') }}" novalidate method="post">
                @csrf

                {{-- <div class="col-md-3 form-group mb-3">
                    <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                    <label for="pre_session">Session Name</label>
                    <select name="pre_session" class="form-control" id="pre_session">
                        @if (!empty($sessions))
                        <option value=""> -- Please select -- </option>
                        <option value="" selected> -- Please select -- </option>
                        @foreach ($pre_studentsessions as $pre_studentsession)
                        <option value="{{ $pre_studentsession->session_name }}" {{ $sessions[sizeof($sessions) - 1]->
                            session_name == $pre_studentsession->session_name ? 'selected' : '' }}>
                            {{ $pre_studentsession->session_name }}</option>
                        @endforeach
                        @else
                        <option value="" selected> -- Please select -- </option>
                        @foreach ($pre_studentsessions as $pre_studentsession)
                        <option value="{{ $pre_studentsession->session_name }}">
                            {{ $pre_studentsession->session_name }}
                        </option>
                        @endforeach
                    </select>
                    @endif
                </div> --}}

                {{--
                <button type="button" onclick="copy_data(event)" class="btn btn-primary">Copy from previous
                    year</button> --}}
            </form>

    </div>
    @if (empty($teacher_subject))
    <div class="breadcrumb">
        <h1 class="me-2">List of saved Teacher Subject :-</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card text-start">
                <div class="card-body">
                    <!-- <h4 class="card-title mb-3 text-end"><a href="{{ url('add-student-registrations') }}"><button class="btn btn-outline-primary" type="button">Create Registration</button></a></h4> -->
                    <div class="teacher-subject-list-tools mb-3">
                        <div class="row align-items-end">
                            <div class="col-md-2 form-group mb-2">
                                <label for="filter_class">Class</label>
                                <select id="filter_class" class="form-control teacher-subject-filter">
                                    <option value="">All</option>
                                    @foreach($teachersubjects->pluck('Class.class_name')->filter()->unique()->sort() as $className)
                                        <option value="{{ $className }}">{{ $className }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label for="filter_section">Section</label>
                                <select id="filter_section" class="form-control teacher-subject-filter">
                                    <option value="">All</option>
                                    @foreach($teachersubjects->pluck('section_name')->filter()->unique()->sort() as $sectionName)
                                        <option value="{{ $sectionName }}">{{ $sectionName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label for="filter_subject">Subject</label>
                                <select id="filter_subject" class="form-control teacher-subject-filter">
                                    <option value="">All</option>
                                    @foreach($teachersubjects->pluck('Subject.subject_name')->filter()->unique()->sort() as $subjectName)
                                        <option value="{{ $subjectName }}">{{ $subjectName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label for="filter_teacher">Teacher</label>
                                <select id="filter_teacher" class="form-control teacher-subject-filter">
                                    <option value="">All</option>
                                    @foreach($teachersubjects->map(fn ($item) => trim(($item->Teacher->first_name ?? '') . ' ' . ($item->Teacher->last_name ?? '')))->filter()->unique()->sort() as $teacherName)
                                        <option value="{{ $teacherName }}">{{ $teacherName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label for="filter_role">Role</label>
                                <select id="filter_role" class="form-control teacher-subject-filter">
                                    <option value="">All</option>
                                    @foreach($teachersubjects->pluck('role')->filter()->unique()->sort() as $roleName)
                                        <option value="{{ $roleName }}">{{ $roleName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <div class="d-flex flex-wrap">
                                    <button type="button" id="reset_teacher_subject_filters" class="btn btn-secondary mr-2 mb-2">Reset</button>
                                    <button type="button" id="export_teacher_subject_excel" class="btn btn-success mb-2">Export Excel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="zero_configuration_table"
                            style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Class Name</th>
                                    <th>Section Name</th>
                                    <th>Subject Name</th>
                                    <th>Teacher Name</th>
                                    <th>Role</th>
                                    {{-- <th>Date</th> --}}
                                    <th>Action</th>
                                    {{-- <th>Session Name </th> --}}
                                    {{-- <th>Action</th> --}}
                                </tr>
                            </thead>
                            <tbody>


                                @if (!empty($teachersubjects))
                                @foreach ($teachersubjects as $teachersubject)
                                <tr>
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $teachersubject->Class->class_name }}</td>
                                    <td>{{ $teachersubject->section_name }}</td>
                                    <td>{{ $teachersubject->Subject->subject_name }}</td>
                                    <td>{{ $teachersubject->Teacher->first_name }} {{$teachersubject->Teacher->last_name}}</td>
                                    <td>{{ $teachersubject->role }}</td>
                                    {{-- <td>{{ date('d-m-Y', strtotime($teachersubject->updated_at)) }}</td> --}}
                                    {{-- <td>{{ $teachersubject->session_name }}</td> --}}
                                    <td class='d-flex'>
                                        <a class="btn btn-primary m-1"
                                            href="{{ url('view-teachersubject') .'/'.$teachersubject->id}}">Edit</a>
                                        <form id="deleteForm" method="post" action="{{url('delete-teachersubject')}}">
                                                    @csrf
                                                    <input type="hidden" name="table_name" value="teacher_subjects">
                                                    <input type="hidden" name="delete_id" value="{{ $teachersubject->id }}">
                                                    {{-- <button type="button" class="btn btn-danger m-1" onclick="confirmDelete(event)">Delete</button> --}}
                                                </form>
                                        <?php $a = "subject_combinations"."-".$teachersubject->id ; ?>
                                        <a class="btn btn-raised ripple btn-danger m-1"
                                            href="{{url('delete-teachersubject').'/'.$teachersubject->id}}"
                                            onclick="confirmDelete(event)">Delete</a>
                                    </td>

                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="9" class="text-center">No Data Found</td>
                                </tr>
                                @endif


                                <!-- <tr>
                                                <td>2</td>
                                                <td>12th</td>
                                                <td>A</td>
                                                <td>Maths</td>
                                                <td>Teacher Name 2</td>
                                                <td>Digtwise</td>
                                                <td><a class="btn btn-primary m-1" href="#">Edit</a><br>
                                                    <a class="btn btn-danger m-1" href="#">delete</a></td>
                                            </tr>
                                          </tbody>
                                          <tfoot>
                                            <tr>
                                              <th>Sr.</th>
                                              <th>Class Name</th>
                                              <th>Section Name</th>
                                              <th>Subject Name</th>
                                              <th>Teacher Name</th>
                                              <th>Action</th>
                                            </tr>  -->


                                </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- end of main-content -->
</div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    function confirmDelete(event) {
        event.preventDefault(); // Prevents the default form submission

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // If the user clicks on "Yes, delete it!", manually submit the form
                //document.getElementById('deleteForm').submit();
                window.location.href = event.target.href;
            }
        });
    }

    function copy_data(e) {
        e.preventDefault();


        if ((!$('#pre_session').val()) || ($('#pre_session').val() == '')) {
            return
        }
        if ((!$('#session_name').val()) || ($('#session_name').val() == '')) {
            return
        }

        let url = "{{ url('copy-teachersubject') }}"
        const _token = document.getElementsByName("_token")[0].value;

        var formData = new FormData()
        formData.append('_token', _token);
        formData.append('class_name', $('#class_name').val());
        formData.append('section_name', $('#section_name').val());
        formData.append('subject_name', $('#subject_name').val());
        formData.append('teacher_name', $('#teacher_name').val());
        formData.append('session_name', $('#session_name').val());
        formData.append('pre_session', $('#pre_session').val());

        // formData.append('file', $('#file_path').prop('files')[0])
        $.ajax({
            url: url,
            type: "POST",
            // dataType: "json",
            data: formData,
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                console.log("success", response);
                Swal.fire({
                    icon: 'success',
                    title: 'success',
                    text: 'techer subject has been copied successfully.',
                })
                setTimeout(() => {
                    location.reload()
                }, 1500);

            },
            error: function(err) {
                console.log("error", err);
                if (err.status == 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'success',
                        text: 'techer subject has been copied successfully.',
                    })
                    setTimeout(() => {
                        location.reload()
                    }, 1500);
                }
            }

        });

    }


    document.addEventListener("DOMContentLoaded", function() {
        function fetchSubjects(classId){
            $.get('{{ route("fetchSubjectsForTeacher") }}', { class_id: classId, _token: '{{ csrf_token() }}' }, function (data) {
                let subjectOptions = `<option value="">--Please Select--</option>`;
                data.forEach(function(subject) {
                    subjectOptions += `<option value="${subject.id}">${subject.subject_name}</option>`;
                });
                $('#subject_name').html(subjectOptions).trigger('change');
            });
        }
        function showSubject() {
            var classId = $("#class_name").val();
            if (!classId) return;
            fetchSubjects(classId);
        }

        $('#class_name').on('change', showSubject)

    });

</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
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
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
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

    document.addEventListener('DOMContentLoaded', function() {
    $("#reset").on("click", function () {
        $("#class_name").val("");
        $("#section_name").val("");
        $("#subject_name").val("");
        $("#teacher_name").val("");
    });

})

</script>
<script>
    window.addEventListener('load', function () {
        if (!window.jQuery || !$.fn.DataTable || !document.getElementById('zero_configuration_table')) {
            return;
        }

        var table = $.fn.dataTable.isDataTable('#zero_configuration_table')
            ? $('#zero_configuration_table').DataTable()
            : $('#zero_configuration_table').DataTable();

        var filters = [
            { selector: '#filter_class', column: 1 },
            { selector: '#filter_section', column: 2 },
            { selector: '#filter_subject', column: 3 },
            { selector: '#filter_teacher', column: 4 },
            { selector: '#filter_role', column: 5 }
        ];

        $('.teacher-subject-filter').select2({
            placeholder: 'All',
            allowClear: true,
            width: '100%'
        });

        function escapeRegex(value) {
            return $.fn.dataTable.util.escapeRegex(value);
        }

        function applyFilters() {
            filters.forEach(function (filter) {
                var value = $(filter.selector).val();
                table.column(filter.column).search(value ? '^' + escapeRegex(value) + '$' : '', true, false);
            });

            table.draw();
        }

        function cleanCell(value) {
            return $('<div>').html(value || '').text().replace(/\s+/g, ' ').trim();
        }

        function exportRows() {
            var headers = ['Sr.', 'Class Name', 'Section Name', 'Subject Name', 'Teacher Name', 'Role'];
            var rows = table.rows({ search: 'applied' }).data().toArray().map(function (row) {
                return row.slice(0, 6).map(cleanCell);
            });
            var fileName = 'teacher-subjects-' + new Date().toISOString().slice(0, 10);

            exportExcel(headers, rows, fileName);
        }

        function exportExcel(headers, rows, fileName) {
            var html = '<table><thead><tr>' + headers.map(function (header) {
                return '<th>' + escapeHtml(header) + '</th>';
            }).join('') + '</tr></thead><tbody>' + rows.map(function (row) {
                return '<tr>' + row.map(function (cell) {
                    return '<td>' + escapeHtml(cell) + '</td>';
                }).join('') + '</tr>';
            }).join('') + '</tbody></table>';

            downloadFile(html, fileName + '.xls', 'application/vnd.ms-excel;charset=utf-8;');
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function downloadFile(content, fileName, mimeType) {
            var blob = new Blob([content], { type: mimeType });
            var link = document.createElement('a');

            link.href = URL.createObjectURL(blob);
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        }

        $('.teacher-subject-filter').on('change', applyFilters);

        $('#reset_teacher_subject_filters').on('click', function () {
            $('.teacher-subject-filter').val('').trigger('change');
        });

        $('#export_teacher_subject_excel').on('click', function () {
            exportRows();
        });
    });
</script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#subject_name').select2({
            placeholder: "Select a subject",
            allowClear: true,
            width: '100%'
        });

        $('#teacher_name').select2({
            placeholder: "Select a teacher",
            allowClear: true,
            width: '100%'
        });
    });
</script>
<style>
    .select2-container--default .select2-selection--single {
    background-color: #f3f4f6 !important;
}
</style>

@endsection
