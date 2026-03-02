@extends('backend.layouts.main')
@section('main-container')
<style>
    .ct_res_table{
        height: 600px; overflow: auto;
    }
</style>
    <div class="main-content">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <div class="breadcrumb">
            <h1 class="me-2">Daily Attandence Entry :-</h1>
        </div>
        <div class="separator-breadcrumb border-top"></div>
        @if(!empty($dailyreportMarster))
        <form id="attendanceForm" class="p-4 progress-form" action="{{ route('dailyattandenceUpdateInfo') }}" method="post">
            <input type="hidden" name="attendance_id" value="{{ $dailyreportMarster->id }}">
        @else
        <form id="attendanceForm" class="p-4 progress-form" action="{{ url('dailyattandence') }}" method="post">
        @endif
            @csrf
            <div class="row">
                <div class="col-md-5 mb-4">
                    <div class = "row">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>

                                        <td><label for="firstName1">Attandence Date:</label></td>
                                        <td><input name="Attandence_Name" class="form-control" id="callno" type="date"
                                                placeholder="Attandence_Name" required @if(!empty($dailyreportMarster)) value="{{ $dailyreportMarster->Attandence_date }}" disabled @endif/></td>
                                    </tr>
                                    <tr>

                                        <td><label for="firstName1">Teacher:</label></td>
                                        <td>
                                            <select name="teacher_name" class="form-control" id="teacher_name" required @if(!empty($dailyreportMarster)) disabled @endif>
                                                <option value="">-- Please select --</option>
                                                @foreach ($teacherlist as $teacherlist)
                                                    <option
                                                        {{ !empty($dailyreportMarster) && ($teacherlist->Teacher->id = $dailyreportMarster->Teacher_id) ? 'selected' : '' }}
                                                        value="{{ $teacherlist->Teacher->id }}">
                                                        {{ $teacherlist->Teacher->first_name }} {{ $teacherlist->Teacher->last_name }} - {{ $teacherlist->Teacher->biometricDetails->ess_emp_code?? '' }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>

                                        <td><label for="class_name">Class:</label></td>
                                        <td>
                                            <select id="class_name" class="form-control" name="class_name" autocomplete=""
                                                required @if(!empty($dailyreportMarster)) disabled @endif>
                                                <option value="" >--Please select--</option>
                                                @if(!empty($dailyreportMarster))
                                                <option value="{{ $dailyreportMarster->class_id }}"  selected>{{ $dailyreportMarster->Class->class_name }}</option>
                                                @endif
                                            </select>

                                        </td>


                                    </tr>

                                    <tr>

                                        <td><label for="firstName1">Section:</label></td>
                                        <td><select name="section_name" class="form-control" id="section_name" @if(!empty($dailyreportMarster)) disabled @endif>
                                            @if(!empty($dailyreportMarster))
                                                <option value="{{ $dailyreportMarster->section_name }}"  selected>{{ $dailyreportMarster->section_name }}</option>
                                                @endif
                                            </select></td>
                                    </tr>
                                    <tr>
                                        <td><button class="btn btn-primary">Submit</button></td>
                                        <td> @if(empty($dailyreportMarster))<button type="reset" class="btn btn-primary" name="btn"
                                                value="Reset Form">Reset</button> @endif</td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        {{-- <div class="row">
                            <div class="col-md-2 form-group mb-3">
                                <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                                <label for="lastName1">from date:</label>
                                <input type="date" class="form-control" id="picker2-" />
                            </div>
                            <div class="col-md-2 form-group mb-3">
                                <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                                <label for="lastName1">to date:</label>
                                <input type="date" class="form-control" id="picker2-" />
                            </div>
                            <div class="col-md-2 form-group mb-3">
                                <br>
                                <button class="btn btn-primary">show</button>
                            </div>
                        </div> --}}

                    </div>
                    <div class="row">

                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="row">
                        <h4>Marks Attandence below :-</h4>
                        <div class="table-responsive @if(!empty($dailyreportMarster)) ct_res_table @endif" id="student_list_table">
                            <table class="display table table-striped table-bordered" id="another_div">
                                <thead id="another_div">
                                    @if(!empty($dailyreportMarster))
                                    <tr>
                                    <th scope="col">S.no.</th>
                                    <th scope="col">Roll no.</th>
                                    <th scope="col">Board roll no.</th>
                                    <th scope="col">Student Name</th>
                                    <th scope="col">Attandence:
                                        <select class="form-control" onchange="updateRadioButtons(this)">
                                        <option value="Please Select">Please Select</option>
                                        <option value="P" >P</option>
                                        <option value="A">A</option>
                                        <option value="N">N</option>
                                        <option value="E">E</option>
                                        <option value="O">O</option>
                                        <option value="OFF">OFF</option>
                                        <option value="PL">PL</option>
                                        </select>
                                    </th>
                                    </tr>
                                    @endif
                                </thead>
                                <tbody id="bus_Attend_data">
                                    @if(!empty($dailyreportMarster))
                                        @php
                                            $sno = 1;
                                        @endphp
                                        @foreach ($studentAttendances as $entry)
                                            <tr>
                                            <td style="font-size: 16px; font-weight: bold;"> {{ $sno++ }} </td>
                                            <td style="font-size: 16px;"> {{ $entry->student->form_number }}</td>
                                            <td style="font-size: 16px;"> {{ $entry->student->id }} </td>
                                            <td style="font-size: 16px;"> {{ $entry->student->student_name }} </td>
                                            <td class="updateselectvalue">
                                                <label><input type="radio" name="attendance[ {{ $entry->student->id }} ]" value="P"
                                                    @if(!empty($dailyreportMarster) && $entry->status == "P") checked @endif> P</label>
                                                <label><input type="radio" name="attendance[ {{ $entry->student->id }} ]" value="A"
                                                    @if(!empty($dailyreportMarster) && $entry->status == "A") checked @endif> A</label>
                                                <label><input type="radio" name="attendance[ {{ $entry->student->id }} ]" value="N"
                                                    @if(!empty($dailyreportMarster) && $entry->status == "N") checked @endif> N</label>
                                                <label><input type="radio" name="attendance[ {{ $entry->student->id }} ]" value="E"
                                                    @if(!empty($dailyreportMarster) && $entry->status == "E") checked @endif> E</label>
                                                <label><input type="radio" name="attendance[ {{ $entry->student->id }} ]" value="O"
                                                    @if(!empty($dailyreportMarster) && $entry->status == "O") checked @endif> O</label>
                                                <label><input type="radio" name="attendance[ {{ $entry->student->id }} ]" value="OFF"
                                                    @if(!empty($dailyreportMarster) && $entry->status == "OFF") checked @endif> OFF</label>
                                                <label><input type="radio" name="attendance[ {{ $entry->student->id }} ]" value="PL"
                                                    @if(!empty($dailyreportMarster) && $entry->status == "PL") checked @endif> PL</label>
                                            </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        @if(empty($dailyreportMarster))
        <div class="row">
            <div class="col-12 table-responsive" style="height: 250px; overflow: auto;">
                <table class="display table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">S.no.</th>
                            <th scope="col">Teacher Name</th>
                            <th scope="col">Class</th>
                            <th scope="col">Sec.</th>
                            <th scope="col">Attendance Date</th>
                            <th scope="col">Create Date</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i = 0;
                        @endphp
                        @if (!empty($dailyreport))
                            @foreach ($dailyreport as $listR)
                                <tr>
                                    <th scope="row">{{ ++$i }}</th>
                                    <td>{{ $listR->Teacher->first_name }} {{ $listR->Teacher->last_name }} - {{ $listR->Teacher->biometricDetails->ess_emp_code?? '' }}</td>
                                    <td>{{ $listR->Class->class_name }}</td>
                                    <td>{{ $listR->section_name }}</td>
                                    <td>{{ date('d-m-Y', strtotime($listR->Attandence_date)) }}</td>
                                    <td>{{ date('d-m-Y', strtotime($listR->created_at)) }}</td>
                                    <td><a class="btn btn-primary m-1" href="{{ route('dailyattandenceUpdate',$listR->id) }}">Edit</a></td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center">No Data Found</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endif


        <!-- end of main-content -->
    </div>
    <script>
        var responseData;
        var stu;
        var checkedValues = []; // Initialize the array globally
        // Function to fetch data and populate the table
        function fetch_select(class_id,section_name) {
            // Reset the checkedValues array
            checkedValues = [];
            var newRow = "";
            var class_id = class_id;
            var section_name = section_name;

            $.ajax({
                data: {
                    class_id: class_id,
                    section_name: section_name
                },
                url: "{{ url('classwisestudent') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                datatype: 'json',
                success: function(response) {
                    responseData = response;
                    var stuData = JSON.parse(responseData);
                    stu = stuData; // Assign 'stuData' to the global 'stu' variable
                    $('#student_list_table').addClass('ct_res_table');
                    // Clear the existing rows from the table body and header
                    $('#bus_Attend_data tbody').empty();
                    $('#another_div thead').empty();

                    // Construct the table header with a single select dropdown
                    var tableHeader = '<tr>' +
                        '<th scope="col">S.no.</th>' +
                        '<th scope="col">Roll no.</th>' +
                        '<th scope="col">Board roll no.</th>' +
                        '<th scope="col">Student Name</th>' +
                        '<th scope="col">Attandence: ' +
                        '<select class="form-control" onchange="updateRadioButtons(this)">' +
                        '<option value="Please Select">Please Select</option>' +
                        '<option value="P">P</option>' +
                        '<option value="A">A</option>' +
                        '<option value="N">N</option>' +
                        '<option value="E">E</option>' +
                        '<option value="O">O</option>' +
                        '<option value="OFF">OFF</option>' +
                        '<option value="PL">PL</option>' +
                        '</select>' +
                        '</th>' +
                        '</tr>';
                    $('#another_div thead').html(tableHeader);

                    // Iterate over the arrays and construct the table rows
                    for (var i = 0; i < stu.length; i++) {
                        // Construct the table rows for attendance
                        newRow += '<tr>' +
                            '<td style="font-size: 16px; font-weight: bold;">' + (i + 1) + '</td>' +
                            '<td style="font-size: 16px;">' + stu[i].form_number + '</td>' +
                            '<td style="font-size: 16px;">' + stu[i].id + '</td>' +
                            '<td style="font-size: 16px;">' + stu[i].student_name + '</td>' +
                            '<td class="updateselectvalue">' +
                                '<label><input type="radio" name="attendance[' + stu[i].id + ']" value="P" checked> P</label>' +
                                '<label><input type="radio" name="attendance[' + stu[i].id + ']" value="A"> A</label>' +
                                '<label><input type="radio" name="attendance[' + stu[i].id + ']" value="N"> N</label>' +
                                '<label><input type="radio" name="attendance[' + stu[i].id + ']" value="E"> E</label>' +
                                '<label><input type="radio" name="attendance[' + stu[i].id + ']" value="O"> O</label>' +
                                '<label><input type="radio" name="attendance[' + stu[i].id + ']" value="OFF"> OFF</label>' +
                                '<label><input type="radio" name="attendance[' + stu[i].id + ']" value="PL"> PL</label>' +
                            '</td>' +
                            '</tr>';

                    }
                    $('#bus_Attend_data').html(newRow);
                    // Attach the change event handler to the radio buttons
                    /* $(document).on('change', 'input[type="radio"]', function() {
                        console.log(1);
                        updateCheckedValues(this);
                    }); */

                }
            });
        }
        // Fetch student data based on class, stream and section
        document.addEventListener("DOMContentLoaded", function(){
            $('#class_name').on('change', function(){
                var class_id = $("#class_name").text();
                var section_name = $("#section_name").val();
                if(section_name){
                    fetch_select(class_id,section_name);
                }
            });
            $('#section_name').on('change', function(){
                var class_id = $("#class_name").find('option:selected').text();
                var section_name = $("#section_name").val();
                if(class_id){
                    fetch_select(class_id,section_name);
                }
                //fetch_select();
            });
        });
        // Function to update the checked values array
        /* function updateCheckedValues(radioButton) {
            console.log('updateCheckedValues function called');

            var selectedValue = $('input[name="' + $(radioButton).attr('name') + '"]:checked').val();
            console.log('Selected Value:', selectedValue);

            // Reset the checkedValues array
            checkedValues = [];

            // Iterate over the radio buttons and collect the checked values
            $('input[type="radio"]:checked').each(function() {
                var rowIndex = $(this).closest('tr').index();
                var selectedValue = $(this).val();
                var studentName = stu[rowIndex].student_name;
                var formNumber = stu[rowIndex].form_number;
                var studentId = stu[rowIndex].id;

                checkedValues.push({
                    row: rowIndex,
                    value: selectedValue,
                    student_name: studentName,
                    form_Number: formNumber,
                    student_id: studentId,
                });
            });
            console.log('Checked Values:', checkedValues);

            // Set the value of the hidden input field with the collected checked values as JSON
            $('#attendance_data').val(JSON.stringify(checkedValues));
        } */

        // Function to update the radio buttons based on the selected value in the header dropdown
        function updateRadioButtons(selectElement) {
            var selectedValue = selectElement.value;
            var checkedValues = []; // reset before pushing fresh values

            // Apply selected value to all radio buttons per student
            $('input[name^="attendance["]').each(function() {
                if ($(this).val() === selectedValue) {
                    $(this).prop('checked', true);
                }
            });

            // Loop through rows to collect data after update
            $('#bus_Attend_data tr').each(function(rowIndex) {
                var radios = $(this).find('input[type="radio"]:checked');
                if (radios.length > 0) {
                    var selectedValue = radios.val();
                    var studentData = stu[rowIndex]; // assuming stu[i] matches table row

                    checkedValues.push({
                        row: rowIndex,
                        value: selectedValue,
                        student_name: studentData.student_name,
                        form_number: studentData.form_number,
                        student_id: studentData.id,
                    });
                }
            });

            // Store JSON-encoded data in a hidden field
            $('#attendance_data').val(JSON.stringify(checkedValues));

            // Reset dropdown
            selectElement.selectedIndex = 0;
        }
        document.addEventListener("DOMContentLoaded", function() {

            function showSections() {
                var iso2 = $("#classname").val();
                let token = document.getElementsByName("_token")[0].value
                console.log(iso2);
                if (iso2) {
                    $.ajax({
                        data: {
                            id: iso2
                        },
                        url: "{{ url('classsection-view') }}/" + iso2,
                        headers: {
                            'X-CSRF-TOKEN': token
                        },
                        method: "POST",
                        dataType: 'json',
                        success: function(data) {
                            // console.log(data);
                            $('#section_name').html('<option value=""> -- Select All -- </option>');
                            for (var i = 0; i < data.length; i++) {
                                var studentData = data[i].section_name;
                                // console.log(studentData, ' ', selected_section);
                                $('#section_name').append('<option value="' + studentData + '">' +
                                    studentData + '</option>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                } else {
                    $('#section_name').html('<option value="">Select class first</option>');
                }
            }


            $('#classname').on('change', showSections)

        })

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
    </script>
@endsection
