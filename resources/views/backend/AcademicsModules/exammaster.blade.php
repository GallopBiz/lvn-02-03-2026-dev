@extends('backend.layouts.main')
@section('main-container')

<style>
    .uperletter {
        text-transform: capitalize;
    }

    th.action-checkbox-header {
        position: relative;
    }

    th.action-checkbox-header input {
        position: absolute;
        left: 60px;
        /* Adjust the amount of space as needed */
    }
</style>
@php
$i = 0;
$srn = 1;
@endphp
<div class="main-content pt-4">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="row">
        <div class="col-md-12 form-group mb-3">
            <div class="form_section1_div">
                <div class="breadcrumb">
                    <h1 class="me-2">Exam Master</h1>
                </div>
                @if(!empty($exam_master))
                    <form id="update_exammaster" class="p-4 progress-form" onsubmit="update_exammaster(event)">
                        <input type="hidden" id="id" @if(!empty($exam_master))
                            value="{{ $exam_master->id }}"  @else value="" @endif name="id">
                        <input type="hidden" name="exam_type" value="{{ $exam_master->exam_type }}">
                        <input type="hidden" name="classes" value="{{ $exam_master->class_id }}">
                        <input type="hidden" name="stream" value="{{ $exam_master->stream_id }}">
                @else
                    <form id="create_exammaster" class="p-4 progress-form" onsubmit="create_exammaster(event)">
                @endif
                    @csrf
                    <div class="row">
                        <div class="col-md-3 form-group mb-3">
                            <label for="exam_name">Exam Name </label>
                            <input class="form-control uperletter" id="exam_name" name="exam_name" type="text"
                                @if(!empty($exam_master))
                                value=" {{ $exam_master->exam_name }}"  @else value="" @endif
                                placeholder="Exam Name " required/>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="exam_type">Exam Type</label>
                            <select name="exam_type" class="form-control" id="exam_type" {{ ( (!empty($exam_master)) ) ? 'disabled' : '' }} required>
                                <option value="">-- Please select --</option>

                                @foreach ($examtypelist as $examtypelist)
                                <option {{( (!empty($exam_master)) && ($exam_master->exam_type == $examtypelist->id)) ? 'selected' : '' }} value="{{ $examtypelist->id }}">{{ $examtypelist->examtype }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="class_select">Classes</label>
                            <select name="classes" class="form-control" id="class_select" {{ ( (!empty($exam_master)) ) ? 'disabled' : '' }} required>
                                <option value="">-- Please select --</option>
                                @foreach ($studentclasses as $studentclass)
                                <option value="{{ $studentclass->id }}" {{ ( (!empty($exam_master)) && ($exam_master->class_id == $studentclass->id)) ? 'selected' : '' }}>
                                    {{ $studentclass->class_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="stream">Stream</label>
                            <select name="stream" class="form-control " id="stream_select" {{ ( (!empty($exam_master)) ) ? 'disabled' : '' }}>
                                <option value="">-- Please select --</option>
                                @foreach ($streams as $stream)
                                    <option value="{{ $stream->id }}"
                                        {{ ( (!empty($exam_master)) && ($exam_master->stream_id == $stream->id)) ? 'selected' : '' }}>
                                        {{ $stream->streams }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group mb-3">
                            <table class="display table table-striped table-bordered"
                                    id="deafult_ordering_table_wrapper" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Subject Name</th>
                                        <th>Subject Type</th>
                                        <th>Min Marks</th>
                                        <th>Max Marks</th>
                                    </tr>
                                </thead>
                                <tbody class="subject_list" id="subject_list">
                                    @if(!empty($exam_master))
                                        @foreach($examsubjectMarks as $examsubjectMark)
                                            <tr>
                                                <td>{{ $srn++ }}</td>
                                                <td data-id="{{ $examsubjectMark->subject_id }}">{{ $examsubjectMark->subject->subject_name }}</td>
                                                <td>{{ $examsubjectMark->subject->practical == 'Yes' ? "Practical" : "Theory" }}</td>
                                                <td>
                                                    <input name="min_marks[{{ $examsubjectMark->subject_id }}]" class="form-control" type="number" value="{{ $examsubjectMark->min_marks }}" placeholder="Min Marks" required />
                                                </td>
                                                <td>
                                                    <input name="max_marks[{{ $examsubjectMark->subject_id }}]" class="form-control" type="number" value="{{ $examsubjectMark->max_marks }}" placeholder="Max Marks" required />
                                                </td>
                                            </tr>

                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-42">
                            <button class="btn btn-primary" type="submit">Submit</button>
                            @if(empty($exam_master))
                            <button type="button" id="reset" class="btn btn-primary" name="btn"
                                value="Reset Form">Reset</button>
                                @endif
                            @if(request()->route()->getName() !== 'exammaster')
                                <a href="{{ url('exammaster') }}" class="btn btn-primary">Add New</a>
                            @endif
                        </div>
                    </div>
            </div>
        </div>
    </div>

    @if(empty($exam_master))
    <div class="row">
        <br>
        <div class="separator-breadcrumb border-top"></div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="breadcrumb">
                    <h1 class="me-2">List of Saved Exam Name :-</h1>
                </div>
                <div class="separator-breadcrumb border-top"></div>

                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="card text-start">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="display table table-striped table-bordered"
                                        id="zero_configuration_table" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>Sr.</th>
                                                <th>Exam Name</th>
                                                <th>Exam Type </th>
                                                <th>Class </th>
                                                <th>Action </th>
                                            </tr>
                                        </thead>
                                        <tbody>


                                            @if(!empty($exammasters))
                                            @foreach($exammasters as $exammaster)
                                            <tr>
                                                <td>{{++$i}}</td>
                                                <td class="uperletter">{{$exammaster->exam_name}}</td>
                                                <td class="uperletter">{{$exammaster->examType->examtype}}</td>
                                                <td class="uperletter">{{$exammaster->Classname->class_name}} {{ $exammaster->stream_id ? '(' . optional($exammaster->Stream)->streams . ')' : '' }}</td>
                                                <td class='d-flex'>

                                                    <a class="btn btn-primary m-1"
                                                        href="{{ url('view-exammaster') .'/'.$exammaster->id}}">Edit</a>

                                                    <?php $a = "exammasters"."-".$exammaster->id ; ?>
                                                    <a class="btn btn-raised ripple btn-danger m-1"
                                                        href="{{url('delete-exammaster').'/'.$a}}"
                                                        onclick="confirmDelete(event)">Delete</a>

                                                </td>
                                            </tr>
                                            @endforeach
                                            @else
                                            <tr>
                                                <td colspan="9" class="text-center">No Data Found</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                        <tfoot>

                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
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
    $(document).ready(function () {
        // Handle the "Action" checkbox change event
        $('.class_checkbox_header').change(function () {
            // Get the state of the "Action" checkbox
            var isChecked = $(this).prop('checked');

            // Set the state of all checkboxes in the column based on the "Action" checkbox
            $('.class_checkbox').prop('checked', isChecked);
        });
    });
</script>

<script>
    function resetForm() {
      // Get the input field by its ID
      var inputField = document.getElementById('remarks');
      // Set the value of the input field to an empty string
      if (inputField) {
          inputField.value = '';
      }
  }
</script>



<script>
    function confirmDelete(event) {
        event.preventDefault(); // Prevents the default link navigation

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
                // If the user clicks on "Yes, delete it!", navigate to the delete URL
                window.location.href = event.target.href;
            }
        });
    }

    function create_exammaster(e){
        e.preventDefault();
        const _token = document.getElementsByName("_token")[0].value;
        let form = document.getElementById("create_exammaster");
        var formData = new FormData(form)
        formData.append('_token', _token);
        $.ajax({
            url: "{{url('save-exammaster')}}",
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                console.log(response);
                if(response.status == "success"){
                    toastr.success(response.message, "Success");
                    setTimeout(() => {
                    window.location.href = "{{ route('exammaster') }}";
                    }, 1500);
                }else{
                    toastr.error(response.message, "Error");
                }
            }
        });
    }
    function update_exammaster(e){
        console.log("on update");
        e.preventDefault();
        $("#classes_error").html("")
        const _token = document.getElementsByName("_token")[0].value;
        let form = document.getElementById("update_exammaster");
        var formData = new FormData(form)
        formData.append('_token', _token);
        // formData.append('file', $('#file_path').prop('files')[0])
        $.ajax({
            // url: "/lvn-school/public/store-exammaster",
            url: "{{url('store-exammaster')}}",
            type: "POST",
            // dataType: "json",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                console.log(response);
                if(response.status == "success"){
                    toastr.success(response.message, "Success");
                    setTimeout(() => {
                    window.location.href = "{{ route('exammaster') }}";
                    }, 1500);
                }else{
                    toastr.error(response.message, "Error");
                }
            }
        });
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
      var select = document.getElementById('exam_type');
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
            $("#exam_name").val("");
            $("#max_marks_theory").val("");
            $("#max_marks_practical").val("");
            $("#fail_if").val("");
            $("#exam_type").val("");
            $("#remarks").val("");
            $(".class_checkbox").prop('checked', false);
        });
    })


</script>
<script>
    function fetchSubjects(classId,streamId){
        $.get('{{ route("fetch_subjects_classwise_for_exam") }}', { class_id: classId, stream_id: streamId, _token: '{{ csrf_token() }}' }, function (data) {

            let rows = '';
            let srn = 1;
            if (data.length === 0) {
                rows = `
                    <tr>
                        <td colspan="5" class="text-center text-muted">No subjects found for this class.</td>
                    </tr>
                `;
            } else {
                data.forEach(function(subject) {
                    const subjectType = subject.practical === "Yes" ? "Practical" : "Theory";

                    rows += `
                        <tr>
                            <td>${srn}</td>
                            <td data-id="${subject.subject_id}">${subject.subject_name}</td>
                            <td>${subjectType}</td>
                            <td>
                                <input name="min_marks[${subject.subject_id}]" class="form-control" type="number" placeholder="Min Marks" required />
                            </td>
                            <td>
                                <input name="max_marks[${subject.subject_id}]" class="form-control" type="number" placeholder="Max Marks" required />
                            </td>
                        </tr>
                    `;
                    srn += 1;
                });
            }

            $('#subject_list').html(rows);
        });
    }
    $(document).ready(function () {
        const classSelect = $('#class_select');
        // On change of class
        classSelect.on('change', function () {
            const selectedOption = $(this).find('option:selected');
            const classId = selectedOption.val();
            const className = selectedOption.text();
            if (className.includes('11') || className.includes('12')) {
                const streamSelect = $('#stream_select');
                const selectedstream = $('#stream_select').find('option:selected');

                const streamId = selectedstream.val();
                console.log('Stream ID :', streamId);
                if(streamId){
                    console.log('Calling fetchSubjects for:', className);
                    fetchSubjects(classId,streamId);
                }
                //console.log('Senior class selected. Skipping fetchSubjects().');
                return;
            }

            // Only call fetchSubjects() if not 11 or 12
            console.log('Calling fetchSubjects for:', className);
            fetchSubjects(classId,"");
        });
    });
    $(document).ready(function () {

        const streamSelect = $('#stream_select');
        streamSelect.on('change', function () {
            const selectedstream = $('#stream_select').find('option:selected');
            const streamId = selectedstream.val();
            const selectedOption = $('#class_select').find('option:selected');

            const classId = selectedOption.val();
            const className = selectedOption.text();

            if (!classId) return; // Guard clause if nothing is selected

            // Check if className contains '11' or '12'
            if (className.includes('11') || className.includes('12')) {

                if(streamId){
                    fetchSubjects(classId,streamId);
                }
                //console.log('Senior class selected. Skipping fetchSubjects().');
                return;
            }
            //fetchSubjects(classId,"");
        });
    });
</script>


@endsection
