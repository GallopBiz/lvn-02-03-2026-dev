@extends('backend.layouts.main')
@section('main-container')
<style>
    .uperletter {
        text-transform: capitalize;
    }

</style>

<div class="main-content">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="breadcrumb">
        <h1 class="me-2">Marks Entry :-</h1>
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
            <div class="row">
                <div class="col-md-3 form-group mb-3">
                    <label for="firstName1">Teacher:</label>
                    <select required name="teacher_name" class="form-control" id="teacher_name" @if (!empty($stream_master)) disabled @endif>
                        <option value="">-- Please select --</option>
                        @if (!empty($stream_master))
                                {{ $c_stream = $stream_master->teacher_id }}
                                {{ $c_class_name = $stream_master->class_name }}
                                {{ $c_section_name = $stream_master->section_name }}
                                {{ $c_subject_name = $stream_master->subject_name }}
                                {{ $c_max_marks_p = $stream_master->max_marks_p }}
                                {{ $c_max_marks_t = $stream_master->max_marks_t }}
                                {{ $c_max_marks = $stream_master->max_marks }}
                                {{ $c_also_enter_practical_marks = $stream_master->also_enter_practical_marks }}
                        @endif
                        @foreach ($teacherlist as $teacherlist)
                        <option {{( (!empty($c_stream)) && ($c_stream==$teacherlist->Teacher->id)) ? 'selected' :
                                            '' }} value="{{ $teacherlist->Teacher->id }}">{{ $teacherlist->Teacher->first_name }} {{ $teacherlist->Teacher->last_name }} - {{ $teacherlist->Teacher->biometricDetails->ess_emp_code?? '' }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" id="marks_id" name="marks_id" value="<?php echo (!empty($stream_master) ? $stream_master->id : ''); ?>">
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="class_name">Class:</label>
                    <select required id="class_name" class="form-control" name="class_name" autocomplete="" required @if (!empty($stream_master)) disabled @endif>
                        <option value="">-- Please select --</option>
                        @if (!empty($stream_master))

                            <option {{( ($classlist->id == $stream_master->class_id)) ? 'selected' : '' }} value="{{ $classlist->id }}">{{ $classlist->class_name }}</option>

                        @endif
                    </select>
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="section_name">Section:</label>
                    <select required name="section_name" class="form-control" id="section_name" required @if (!empty($stream_master)) disabled @endif>
                        <option value=""> -- Please select -- </option>
                        @if (!empty($stream_master))
                        <option selected value="{{$stream_master->section_name}}">{{$stream_master->section_name}}</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="subject_name">Subject:</label>
                    <select required name="subject_name" class="form-control" id="subject_name" @if (!empty($stream_master)) disabled @endif>
                        <option value="">-- Please select --</option>
                        @if (!empty($stream_master))
                        <option selected value="{{ $stream_master->subject_id }}">{{ $stream_master->Subject->subject_name }}</option>
                        @else
                        @foreach ($subjectlist as $subjectlist)
                        <option {{( (!empty($c_stream)) && ($c_stream==$subjectlist->subject_name)) ? 'selected' :
                                            '' }} value="{{ $subjectlist->subject_name }}">{{ $subjectlist->subject_name }}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="exam_name">Term/Exam:</label>
                    <select required name="exam_name" class="form-control" id="exam_name" @if (!empty($stream_master)) disabled @endif>
                        <option value="">-- Please select --</option>
                        @if (!empty($stream_master))
                        {{ $c_stream = $stream_master->exam_id }}
                        @endif
                        @foreach ($examslist as $exam)
                        <option {{( (!empty($c_stream)) && ($c_stream == $exam->id)) ? 'selected' :
                                            '' }} value="{{ $exam->id }}">{{ $exam->exam_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="container-fluid">
                    <div class="row">
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <div class="card" style="width: 100%;">
                                    <div class="card-body">
                                        <div class="table-responsive" style="width: 100%;">
                                            <table class="display table table-striped table-bordered table-full-width" style="width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">S.no.</th>
                                                        {{-- <th scope="col">S.no.</th> --}}
                                                        <th scope="col">
                                                            <label>
                                                                <input type="checkbox" id="isAbsentToggle" class="toggle-checkbox" data-column-index="1"> Is Absent Th
                                                            </label>
                                                        </th>
                                                        <th scope="col">
                                                            <label>
                                                                <input type="checkbox" id="isAbsentPrToggle" class="toggle-checkbox" data-column-index="2"> Is Absent Pr
                                                            </label>
                                                        </th>
                                                        <th scope="col">Student Name</th>
                                                        <th scope="col">Enrollment no.</th>
                                                        <th scope="col">Scholar no.</th>
                                                        <th scope="col">Marks</th>
                                                        <th scope="col">Action</th>
                                                    </tr>





                                                </thead>
                                                <tbody id="studentdatatable">
                                                    @php
                                                    $i = 0;
                                                    @endphp
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
                                                                <td><input type="checkbox" class="is_absent_pr" @if($marks->is_absent_pr == '1') @checked(true) @endif /></td>
                                                                <td>{{ $marks->student_name }}</td>
                                                                <td><input type="hidden" class="student_id" value="{{ $marks->student_id }}" /></td>
                                                                <td>{{ $marks->scholar_no }}</td>
                                                                <td><input type="number" class="marks" value="{{ $marks->subject_marks }}" min="0" /></td>
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
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary col-2 mt-2" style="width:100px;">Submit</button>

                    </div>
                </div>


        </form>
        <div class="row ">
            <div class="col-md-12 my-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <h4>Previously saved marks entry :-</h4>
                            <div class="table-responsive" >
                                <table class="display table table-striped table-bordered" id="another_div">
                                    <thead>
                                        <tr>
                                            <th scope="col">S.no.</th>
                                            <th scope="col">Exam Name</th>
                                            <th scope="col">Exam Type</th>
                                            <th scope="col">Class</th>
                                            <th scope="col">Section</th>
                                            <th scope="col">Subject</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    @php
                                    $i = 0;
                                    @endphp
                                    <tbody id="">

                                        @if(!empty($stream))
                                        @foreach($stream as $streams)
                                        <tr>
                                            <td>{{++$i}}</td>
                                            <td class="uperletter">{{$streams->Exammaster->exam_name}}</td>
                                            <td class="uperletter">{{$streams->Exammaster->examType->examtype}}</td>
                                            <td>{{$streams->Class->class_name}}</td>
                                            <td>{{$streams->section_name}}</td>
                                            <td class="uperletter">{{$streams->Subject->subject_name}}</td>


                                            <td class='d-flex'>
                                                <a class="btn btn-primary m-1" href="{{ url('view-marks') .'/'.$streams->id}}">Edit</a>
                                                <!-- <form id="deleteForm" method="post" action="{{url('delete-streammaster')}}">
                                                            @csrf
                                                            <input type="hidden" name="table_name" value="streams">
                                                            <input type="hidden" name="delete_id" value="{{ $streams->id }}">
                                                            <button type="button" class="btn btn-danger m-1" onclick="confirmDelete(event)">Delete</button>
                                                        </form> -->
                                                <?php $a = "previosly_saved_marks_entry"."-".$streams->id ; ?>
                                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-marks').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
                                            </td>
                                        </tr>
                                        <!-- </?php $i++; ?> -->
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="9" class="text-center">No Data Found</td>
                                        </tr>
                                        @endif




                                    </tbody>
                                    <!-- Add this inside the form where you want to submit the radio button data -->
                                </table>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
</div>
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
                url: "{{ url('getteachersdata') }}",
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
                        $('#subject_name').append('<option value="' + subject_id + '">' + subjectName + '</option>');
                    }
                    $('#section_name').html('<option value=""> -- Select All -- </option>');
                    for (var i = 0; i < data['sections'].length; i++) {
                        var subjectName = data['sections'][i];
                        $('#section_name').append('<option value="' + subjectName + '">' + subjectName + '</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });
        function showStudentData() {
            var iso2 = $("#class_name").val();
            var section_name = $("#section_name").val();
            var subject_id = $("#subject_name").val();
            let token = document.getElementsByName("_token")[0].value;
            if (iso2) {
                $.ajax({
                    data: {
                        class: iso2,
                        section_name:section_name,
                        subject_id:subject_id
                    },
                    url: "{{url('class-studentdata')}}",
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
                                <td><input type="checkbox" class="is_absent_pr" /></td>
                                <td>${item.student_name}</td>
                                <td><input type="hidden" class="student_id" value="${item.id}" /></td>
                                <td>${item.scholar_no}</td>
                                <td><input type="number" class="marks" value="0" min="0" /></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="deleteRowData(event)">Delete</button></td>
                            </tr>`;
                        })

                        $("#studentdatatable").html(rows)

                    }
                    , error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            } else {
                $('#section_name').html('<option value="">Select class first</option>');
            }
        }
        $('#class_name').on('change', function() {
            var classId = $(this).val();
            const examDropdown = document.getElementById('exam_name');

                // Clear previous options
                examDropdown.innerHTML = '<option value="">-- Please select --</option>';

                if (!classId) return;

                // Filter exams based on selected class ID
                const filteredExams = allExams.filter(exam => exam.class_id == classId);
                // Populate the exam dropdown
                filteredExams.forEach(exam => {
                    const option = document.createElement('option');
                    option.value = exam.id;
                    option.textContent = exam.exam_name;
                    examDropdown.appendChild(option);
                });
        });
        $('#subject_name').on('change', () => {
             showStudentData();
        })
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
        let token = document.getElementsByName("_token")[0].value;
        let data = [];
        let bodydata = document.getElementById("studentdatatable").children;
        let studentData = [];
        $('#studentdatatable').find('.student-row').each(function () {
            const row = $(this);
            const studentId = row.find('.student_id').val();
            const marks = row.find('.marks').val();
            const isAbsent = row.find('.is_absent').is(':checked') ? 1 : 0;
            const isAbsentPr = row.find('.is_absent_pr').is(':checked') ? 1 : 0;

            studentData.push({
                student_id: studentId,
                marks: marks,
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
            students: studentData
        };
        $.ajax({
            data: postData
            , url: "{{url('save-marks')}}"
            , headers: {
                'X-CSRF-TOKEN': token
            }
            , method: "POST"
            , dataType: 'json'
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
            }
        });
    }

    function handleUpdateData(event) {
        event.preventDefault();
        let token = document.getElementsByName("_token")[0].value;
        let data = [];
        let bodydata = document.getElementById("studentdatatable").children;
        let studentData = [];
        $('#studentdatatable').find('.student-row').each(function () {
            const row = $(this);
            const studentId = row.find('.student_id').val();
            const marks = row.find('.marks').val();
            const isAbsent = row.find('.is_absent').is(':checked') ? 1 : 0;
            const isAbsentPr = row.find('.is_absent_pr').is(':checked') ? 1 : 0;

            studentData.push({
                student_id: studentId,
                marks: marks,
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
            students: studentData
        };
        $.ajax({
            data: postData
            , url: "{{url('store-marks')}}"
            , headers: {
                'X-CSRF-TOKEN': token
            }
            , method: "POST"
            , dataType: 'json'
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
            }
        });
    }
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



@endsection
