@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
<meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- <h2>hyy</h2> -->   
    @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif.. 
                    <!-- student information -->
                    <section class="ul-product-detail">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        
                                        <div class="col-lg-12">
                        <div class="card text-left">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-3">Attandence Report</h4>
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                      <a
                        class="nav-link active"
                        id="home-basic-tab"
                        data-bs-toggle="tab"
                        href="#homeBasic"
                        role="tab"
                        aria-controls="homeBasic"
                        aria-selected="true"
                        >Monthly Atten. Summary(meeting 1)</a
                      >
                    </li>
                    <!-- <li class="nav-item">
                      <a
                        class="nav-link"
                        id="profile-basic-tab"
                        data-bs-toggle="tab"
                        href="#profileBasic"
                        role="tab"
                        aria-controls="profileBasic"
                        aria-selected="false"
                        >Monthly Atten. Summary(meeting 2)</a
                      >
                    </li> -->
                    <li class="nav-item">
                      <a
                        class="nav-link"
                        id="contact-basic-tab"
                        data-bs-toggle="tab"
                        href="#contactBasic"
                        role="tab"
                        aria-controls="contactBasic"
                        aria-selected="false"
                        >A/P reports</a
                      >
                    </li>
                    <li class="nav-item">
                      <a
                        class="nav-link"
                        id="MSC-basic-tab"
                        data-bs-toggle="tab"
                        href="#MSCBasic"
                        role="tab"
                        aria-controls="MSCBasic"
                        aria-selected="false"
                        >Monthly Strength Count</a
                      >
                    </li>
                    <!-- <li class="nav-item">
                      <a
                        class="nav-link"
                        id="Other-basic-tab"
                        data-bs-toggle="tab"
                        href="#OtherBasic"
                        role="tab"
                        aria-controls="OtherBasic"
                        aria-selected="false"
                        >Other</a
                      >
                    </li> -->
                  </ul>
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane show active" id="homeBasic" role="tabpanel" aria-labelledby="home-basic-tab">
                                        <!-- <h3>Student Details</h3> -->
                                        <form id="attendanceForm" class="p-4 progress-form" action="{{url('filterdailyattandence')}}"  method="post">
                                            @csrf
                                      <div class="row">


                                        <div class="col-md-3 form-group mb-3">
                                            <label for="firstName1">Class Name</label>
                                            <select id="classname" class="form-control" name="classname" autocomplete="" required>
                                               <option value="" disabled selected>Please select</option>
                                               @foreach($classlist as $each)
                                               <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                                               @endforeach
                                                {{-- @foreach(config('global.class_name') as $each)
                                               <option value="{{$each}}">{{$each}}</option>
                                               @endforeach  --}}
                                            </select>
                                            <span class="classname_msg validation_err"></span>
                                          </div> 


                                          {{-- <div class="col-md-3 form-group mb-3">
                                        <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                                            <label for="lastName1">Class</label>
                                            <select name="Exam_Name" class="form-control" id="Exam_Name">
                                                <option value="" selected> -- Please select -- </option>
                                                @foreach($uniqueNames as $country)
                                                        <option value="{{$country}}">{{$country}}</option>
                                                        @endforeach
                                            </select>
                                        </div> --}}
                                        
                                        <div class="col-md-3 form-group mb-3">
                                        <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                                            <label for="section_name">Section</label>
                                            <select name="Section" class="form-control" id="section_name">                                                
                                            </select>
                                        </div>
                                        <div class="col-md-3 form-group mb-3">
                                        <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                                            <label for="lastName1">Month/year:-</label>
                                            <input type="month" name="Month" class="form-control" />
                                        </div>
                                        <div class="col-md-3 form-group mb-3"><br>
                                        <button class="btn btn-primary">Submit</button>
                                        {{-- <button type="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button> --}}

                                        </div>
                                      </div>
                                    </form>
                                      <div class="table-responsive">
                                      <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">S.no.</th>
                                                <th scope="col">Roll no.</th>
                                                <th scope="col">Board roll no.</th>
                                                <th scope="col">Student Name</th>
                                                @for ($i = 1; $i <= $AdaysInMonth; $i++)
                                                    <th scope="col">{{ $i }}</th>
                                                @endfor
                                                <th scope="col">Days</th>
                                                <th scope="col">Current Month peresent(%)</th>
                                                <!-- <th scope="col">Previous Month peresent(%)</th>
                                                <th scope="col">Overall Month peresent(%)</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 0; ?>
                                            @if(!empty($classwiseAttendance))
                                            @foreach ($classwiseAttendance as $student)
                                            <?php $arraySize = count($decodedDataArray); ?>
                                                <tr>
                                                    <td>{{ $i }}</td>
                                                    <td>{{ $student->form_number }}</td>
                                                    <td>{{ $student->id }}</td>
                                                    <td>{{ $student->student_name }}</td>
                                                    @php
                                                        $totalPCount = 0;
                                                        $sundayDays = array_map(function ($date) {
                                                                    return date('j', strtotime($date));
                                                                }, $sundays);
                                                    @endphp

                                                    @for ($day = 1; $day <= $AdaysInMonth; $day++)
                                                        @php
                                                            $attendanceDateFound = false;
                                                        @endphp
                                                        <td>
                                                            @php
                                                                $attendanceDateFound = false;
                                                            @endphp

                                                            @foreach ($decodedDataArray as $nestedArray)
                                                                @php
                                                                    $attendanceDate = date('j', strtotime($nestedArray['Attandence_Name']));
                                                                    $formattedAttendanceDate = date('Y-m-d', strtotime($nestedArray['Attandence_Name']));
                                                                @endphp

                                                                @if ($attendanceDate == $day && isset($nestedArray[$i]['value']))
                                                                    {{ $nestedArray[$i]['value'] }}
                                                                    @if ($nestedArray[$i]['value'] === 'p')
                                                                        @php $totalPCount++; @endphp
                                                                    @endif
                                                                    @php $attendanceDateFound = true; @endphp
                                                                @endif

                                                            @endforeach

                                                            @if (in_array($day, $sundayDays))
                                                            <div style="background-color: green; padding: 5px; border-radius: 5px; text-align: center; color:white;">S</div>
                                                                @php $attendanceDateFound = true; @endphp
                                                            @endif

                                                            @if (!$attendanceDateFound)
                                                                -
                                                            @endif
                                                        </td>
                                                    @endfor

                                                    <td>{{ $totalPCount }}</td>
                                                    @if (!empty($arraySize))
                                                        @php
                                                            $percentage = ($totalPCount / $arraySize) * 100;
                                                        @endphp
                                                        <td>{{ number_format($percentage, 2) }}%</td>
                                                    @else
                                                        <td>-</td>
                                                    @endif

                                                </tr>
                                                <?php $i++; ?>
                                            @endforeach
                                            @else
                                                <tr><td colspan="{{ $AdaysInMonth + 6 }}" class="text-center">No Data Found</td></tr>
                                            @endif
                                        </tbody>
                                    </table>

                                </div>
                                    </div>
                                    <div class="tab-pane " id="contactBasic" role="tabpanel" aria-labelledby="contact-basic-tab">
                                    <form id="attendanceStatus" class="p-4 progress-form" action="{{url('filterstatusattandence')}}"  method="post">
                                            @csrf
                                    <div class="row">
                                        <div class="col-md-2 form-group mb-3">
                                        <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                                            <label for="lastName1">from date:</label>
                                            <input type="date" class="form-control" name ="fromdate" id="picker2-"  />
                                        </div>
                                        <div class="col-md-2 form-group mb-3">
                                        <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                                            <label for="lastName1">to date:</label>
                                            <input type="date" class="form-control" name ="todate" id="picker2-"  />
                                        </div>
                                        <div class="col-md-2 form-group mb-3">
                                        <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                                            <label for="lastName1">Class</label>
                                            <select name="Class" class="form-control" id="cClass">
                                            <option value="" selected> -- Please select -- </option>
                                            @foreach($classlist as $each)
                                            <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-2 form-group mb-3">
                                        <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                                            <label for="lastName1">Section</label>
                                            <select name="Csetcion" id="Csetcion" class="form-control">                                               
                                            </select>
                                        </div>
                                        <div class="col-md-2 form-group mb-3">
                                        <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                                            <label for="lastName1">Status</label>
                                            <select name="Status" class="form-control">
                                                <option value="" selected> -- Please select -- </option>
                                                <option value="A">Absent</option>
                                                <option value="p">Present</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 form-group mb-3">
                                            <br>
                                            <button class="btn btn-primary">show</button>
                                        </div>
                                    </div>
                                    </form>
                                    <div class="row">
                                    <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">S.no.</th>
                                                <th scope="col">Roll no.</th>
                                                <th scope="col">Board roll no.</th>
                                                <th scope="col">Student Name</th>
                                                <th scope="col">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id = "status_Attend_data">
                                        </tbody>
                                    </table>
                                    </div>
                                    </div>
                                    </div>
                                    <div class="tab-pane " id="MSCBasic" role="tabpanel" aria-labelledby="MSC-basic-tab">
                                    <div class="row">
                                    <form id="Classwisereport" class="p-4 progress-form" action="{{ url('filterclassattandence') }}" method="post">
                                        @csrf
                                        <div class="col-md-3 form-group mb-3">
                                            <label for="lastName1">Month/year:-</label>
                                            <input type="month" name="ClassWiseMonth" class="form-control" id="picker2-"  />
                                        </div>
                                        <div class="col-md-2 form-group mb-3">
                                            <br>
                                            <button type="submit" class="btn btn-primary">show</button>
                                        </div>
                                    </form>

                                    </div>
                                    <div class="row">
                                    
                                    <div class="table-responsive">
                                    <table class="table" id = "table-container">
                                       
                                    </table>
                                    </div>
                                    </div>
                                    </div>
                                    <!-- <div class="tab-pane fade" id="MSCBasic" role="tabpanel" aria-labelledby="MSC-basic-tab">
                                       hyy
                                    </div> -->
                            </div>
                        </div>
                    </div>
                </section>
                    <!-- end student information -->              
    <!-- end of main-content -->
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#Classwisereport').submit(function(event) {
        event.preventDefault();
        var formData = $('input[name="ClassWiseMonth"]').val();
        console.log('Form Data:', formData);

        // Perform the AJAX request
        $.ajax({
            data: { value: formData },
            url: "{{ url('filterclassattandence') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            method: "POST",
            datatype: 'json',
            success: function(response) {
                var responseData = JSON.parse(response); 
                console.log(responseData);

                if (responseData.classwise.length === 0) {
                    // If response data is empty, display "Data not found" message
                    var noDataMessage = '<tr><td colspan="' + (responseData.days_in_month + 3) + '" class="text-center">Data not found</td></tr>';
                    $('#table-container').empty().append(noDataMessage);
                } else {
                    var $table = $('<table class="table">');
                    var $thead = $('<thead>').appendTo($table);
                    var $trHead = $('<tr>').appendTo($thead);
                    $('<th scope="col">S.no.</th>').appendTo($trHead);
                    $('<th scope="col">Class Name</th>').appendTo($trHead);
                    $('<th scope="col">Section Name</th>').appendTo($trHead);
                    
                    for (var i = 1; i <= responseData.days_in_month; i++) {
                        $('<th scope="col">' + i + '<br> Strength Present (%)</th>').appendTo($trHead);
                    }
                    
                    var $tbody = $('<tbody id="class_Attend_data">').appendTo($table);

                    $.each(responseData.classwise, function(index, classData) {
                        var className = responseData.classes[responseData.classes.indexOf(classData.class)];

                        // Check if section information is available in classData and in responseData.sections array
                        var sectionName = classData.section || '';
                        console.log(sectionName);
                        var $tr = $('<tr>').appendTo($tbody);
                        $('<td>' + (index + 1) + '</td>').appendTo($tr);
                        $('<td>' + className + '</td>').appendTo($tr);
                        $('<td>' + sectionName + '</td>').appendTo($tr);

                        for (var i = 1; i <= responseData.days_in_month; i++) {
                            var dateClass = classData.dateclass; // Get the dateclass from the current classData
                            var date = new Date(dateClass);
                            var month = date.getMonth() + 1; // JavaScript months are 0-based
                            var day = date.getDate();
                            
                            var sundayDaysIntegers = responseData.sundaydays.map(Number);
                            var isSunday = sundayDaysIntegers.includes(i);

                            if (month === new Date().getMonth() + 1 && day === i) {
                                var $td = $('<td>').appendTo($tr);
                                var attendance = getAttendanceForDate(responseData.classwise, classData.class, classData.dateclass);

                                if (attendance) {
                                    var totalCount = attendance.countP + attendance.countA + attendance.countE;
                                    var percentage = (totalCount > 0) ? ((attendance.countP / totalCount) * 100).toFixed(2) + '%' : '-';
                                    $td.text(percentage);

                                    if (isSunday) {
                                        $td.html('<div style="background-color: green; color: white; text-align: center;">S</div>');
                                    }
                                } else {
                                    $td.text('-');
                                }
                            } else {
                                // If not a matching day, add an empty cell
                                var $td = $('<td>').appendTo($tr);
                                if (isSunday) {
                                    $td.html('<div style="background-color: green; color: white; text-align: center;">S</div>');
                                } else {
                                    $td.text('-');
                                }
                            }
                        }
                    });

                    // Append the table to the container
                    $('#table-container').empty().append($table);
                }
            }
        });
    });
});

function getAttendanceForDate(classwiseData, className, date) {
    for (var i = 0; i < classwiseData.length; i++) {
        if (classwiseData[i].class === className && classwiseData[i].dateclass === date) {
            return classwiseData[i];
        }
    }
    return null;
}

</script>
<script>
    $(document).ready(function() {
        $('#attendanceStatus').submit(function(event) {
    event.preventDefault();
    var fromdate = $('input[name="fromdate"]').val();
    var todate = $('input[name="todate"]').val();
    var className = $('select[name="Class"]').val();
    var section = $('select[name="Csetcion"]').val();
    var status = $('select[name="Status"]').val();
    
    var attenddatastatus = [fromdate, todate, className, section, status];
    console.log(status);
    // Perform the AJAX request
    $.ajax({
            data: { value: attenddatastatus },
            url: "{{ url('filterstatusattandence') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            method: "POST",
            datatype: 'json',
            success: function(response) {
    var checkstatus = JSON.parse(response);
    console.log(checkstatus);
    $('#status_Attend_data tbody').empty();

    if (checkstatus.length === 0) {
        // If response is empty, display "Data not found" message
        var noDataMessage = '<tr><td colspan="5" class="text-center" style="font-size: 16px;">Data not found</td></tr>';
        // console.log("hyy");
        $('#status_Attend_data').append(noDataMessage);
    } else {
        var k = 0;

        for (var i = 0; i < checkstatus.length; i++) {
            var formNumber = checkstatus[i].form_number || '';
            var id = checkstatus[i].id || '';
            var studentName = checkstatus[i].student_name || '';

            var jsonStr = JSON.parse(checkstatus[i].json_str || '[]'); // Parse the JSON string
            // Iterate over the array of objects inside json_str
            for (var j = 0; j < jsonStr.length; j++) {
                var rowData = jsonStr[j];
                if (status === rowData.value) { // Use the 'status' variable here
                    var subRow = '<tr>' +
                        '<td style="font-size: 16px; font-weight: bold;">' + (k + 1) + '</td>' +
                        '<td style="font-size: 16px;">' + rowData.form_Number + '</td>' +
                        '<td style="font-size: 16px;">' + rowData.row + '</td>' +
                        '<td style="font-size: 16px;">' + rowData.student_name + '</td>' +
                        '<td style="font-size: 16px;">' + rowData.value + '</td>' +
                        '</tr>';
                    k++;
                    console.log(subRow);
                    $('#status_Attend_data').append(subRow);
                }
            }
        }
    }
}

        });
    });
});


document.addEventListener("DOMContentLoaded", function() {

function showSections() {
    var iso2 = $("#classname").val();
    let token = document.getElementsByName("_token")[0].value
    console.log(iso2);
    if (iso2) {
        $.ajax({
            data: {
                id: iso2
            }
            , url: "{{url('classsection-view')}}/" + iso2
            , headers: {
                'X-CSRF-TOKEN': token
            }
            , method: "POST"
            , dataType: 'json'
            , success: function(data) {
                // console.log(data);
                $('#section_name').html('<option value=""> -- Select All -- </option>');
                for (var i = 0; i < data.length; i++) {
                    var studentData = data[i].section_name;
                    // console.log(studentData, ' ', selected_section);                            
                    $('#section_name').append('<option value="' + studentData + '">' + studentData + '</option>');
                }
            }
            , error: function(xhr, status, error) {
                console.error(error);
            }
        });
    } else {
        $('#section_name').html('<option value="">Select class first</option>');
    }
}

function cshowSections() {
    var iso2 = $("#cClass").val();
    let token = document.getElementsByName("_token")[0].value
    console.log(iso2);
    if (iso2) {
        $.ajax({
            data: {
                id: iso2
            }
            , url: "{{url('classsection-view')}}/" + iso2
            , headers: {
                'X-CSRF-TOKEN': token
            }
            , method: "POST"
            , dataType: 'json'
            , success: function(data) {
                // console.log(data);
                $('#Csetcion').html('<option value=""> -- Select All -- </option>');
                for (var i = 0; i < data.length; i++) {
                    var studentData = data[i].section_name;
                    // console.log(studentData, ' ', selected_section);                            
                    $('#Csetcion').append('<option value="' + studentData + '">' + studentData + '</option>');
                }
            }
            , error: function(xhr, status, error) {
                console.error(error);
            }
        });
    } else {
        $('#Csetcion').html('<option value="">Select class first</option>');
    }
}

// showSections();

$('#classname').on('change', showSections)
$('#cClass').on('change', cshowSections)

})

</script>


@endsection