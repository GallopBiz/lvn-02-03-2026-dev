@extends('backend.layouts.main')
@section('main-container')
<script>
function toggleElements(elementId) {
    var checkbox = document.getElementById(elementId + "Checkbox");
    var select = document.getElementById(elementId + "Select");
    
    if (checkbox.checked) {
        select.disabled = true;
    } else {
        select.disabled = false;
    }
}

function toggleCheckbox(elementId) {
    var checkbox = document.getElementById(elementId + "Checkbox");
    var select = document.getElementById(elementId + "Select");
    
    if (select.value !== "") {
        checkbox.disabled = true;
    } else {
        checkbox.disabled = false;
    }
}
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Fees Register</h1>
        </div>
        <div class="separator-breadcrumb border-top"></div>
        <form id="progress-form" class="p-4 progress-form" action="{{url('get-fees-register-data')}}"  novalidate method="post">
            @csrf
            <div class="row">
                <div class="row">
                <div class="col-md-2 form-group mb-3">
                    <!-- <label for="lastName1">From date:</label> -->
                    <h4>From date:</h4>
                    <!-- <input type="date" class="form-control" id="picker2" name="From_date" /> -->
                </div>
                <div class="col-md-2 form-group mb-3">
                    <!-- <label for="lastName1">From date:</label> -->
                    <input name="fromdate" class="form-control" id="callno" type="date"
                    placeholder="Attandence_Name" required />
                </div>
                <div class="col-md-2 form-group mb-3"></div>
                <div class="col-md-2 form-group mb-3"></div>
                <div class="col-md-2 form-group mb-3">
                    <!-- <label for="lastName1">To date:</label> -->
                    <h4>To date:</h4>
                    <!-- <input type="date" class="form-control" id="picker2" name="To_date" /> -->
                </div>
                <div class="col-md-2 form-group mb-3">
                    <!-- <label for="lastName1">To date:</label> -->
                    <input name="todate" class="form-control" id="callno" type="date"
                    placeholder="Attandence_Name" required/>
                </div>
                </div>
                <div class="col-md-12 form-group mb-3"><br></div>
                
                <div class="col-md-2 form-group mb-3">
                    <label for="lastName1">All class</label>
                    <input type="checkbox" id="AllClassCheckbox" name="All_class" onclick="toggleElements('AllClass')" />
                </div>
                <div class="col-md-2 form-group mb-3">
                    <select id="AllClassSelect" class="form-control" name="classes" autocomplete="shipping address-level1" onchange="toggleCheckbox('AllClass')" required>
                        <option value="" disabled selected>Please select</option>
                        @foreach($uniqueNames as $country)
                            <option value="{{$country}}">{{$country}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label for="lastName1">Section</label>
                    <input type="checkbox" id="SectionCheckbox" name="Section" onclick="toggleElements('Section')" />
                </div>
                <div class="col-md-2 form-group mb-3">
                    <select id="SectionSelect" class="form-control" name="section" autocomplete="shipping address-level1" onchange="toggleCheckbox('Section')" required>
                        <option value="" disabled selected>Please select</option>
                        @foreach(config('global.section') as $each)
                            <option value="{{$each}}">{{$each}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label for="lastName1">All Heads</label>
                    <input type="checkbox" id="HeadsCheckbox" name="All_Heads" onclick="toggleElements('Heads')" />
                </div>
                <div class="col-md-2 form-group mb-3">
                    <select id="HeadsSelect" class="form-control" name="allheads" autocomplete="shipping address-level1" onchange="toggleCheckbox('Heads')" required>
                        <option value="" disabled selected>Please select</option>
                        @foreach($uniqueheadnameNames as $heads)
                            <option value="{{$heads}}">{{$heads}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label for="lastName1">All Student</label>
                    <input type="checkbox" id="StudentCheckbox" name="All_Student" onclick="toggleElements('Student')" />
                </div>
                <div class="col-md-2 form-group mb-3">
                    <select id="Student" class="form-control" name="Student" autocomplete="shipping address-level1" onchange="toggleCheckbox('Student')" required>
                        <option value="" selected> -- Please select -- </option>
                        <!-- Add dynamic options here if needed -->
                    </select>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label for="lastName1">All Type</label>
                    <input type="checkbox" id="TypeCheckbox" name="All_Type" onclick="toggleElements('Type')" />
                </div>
                <div class="col-md-2 form-group mb-3">
                    <select id="TypeSelect" class="form-control" name="alltype" autocomplete="shipping address-level1" onchange="toggleCheckbox('Type')" required>
                        <option value="" disabled selected>Please select</option>
                        @foreach($feestypemaster as $typefees)
                            <option value="{{$typefees->fees_type}}">{{$typefees->fees_type}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group mb-3">
                    <label for="lastName1">Current/Discontinued</label>
                </div>
                <div class="col-md-2 form-group mb-3">
                    <select id="Current/Discontinued" class="form-control" name="Current/Discontinued" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              <option value="All">All</option>
                              <option value="Current">Current</option>
                              <option value="Discontinued">Discontinued</option>
                           </select>
                </div>
                <div class="col-md-2 form-group mb-3">
                    <label for="lastName1">Bus Fees Type</label>
                </div>
                <div class="col-md-2 form-group mb-3">
                    <select id="BusFeesType" class="form-control" name="BusFeesType" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              @foreach($bustypefees as $bfees)
                                <option value="{{$bfees->busfeestypename}}">{{$bfees->busfeestypename}}</option>
                             @endforeach
                           </select>
                </div>
                <div class="col-md-2 form-group mb-3">
                    <label for="lastName1">Batch</label>
                </div>
                <div class="col-md-2 form-group mb-3">
                    <select id="Batch" class="form-control" name="Batch" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              @foreach($uniquesession as $session)
                                <option value="{{$session}}">{{$session}}</option>
                             @endforeach
                           </select>
                </div>
                <div class="col-md-2 form-group mb-3">
                    <label for="lastName1">Fees Type</label>
                </div>
                <div class="col-md-2 form-group mb-3">
                    <select id="FeesType" class="form-control" name="FeesType" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              @foreach($alltype as $tfees)
                                <option value="{{$tfees->feestype}}">{{$tfees->feestype}}</option>
                             @endforeach
                           </select>
                </div>
                <div class="col-md-2 form-group mb-3">
                    <input type="checkbox" id="ChequeDateWise" name="Cheque-Date-Wise"/>
                    <label for="lastName1">Cheque Date Wise</label>
                </div>                
                <div class="col-md-12">
                    <button class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
    <!-- end of main-content -->
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
//  function to class wise student 
  	
$(document).ready(function () {
    $('#AllClassSelect').on('change', function () {
        var iso2 = $(this).val();
        console.log(iso2);
        if (iso2) {

            $.ajax({
                data: { id: iso2 },
                url: "{{url('classstudent-view')}}/" + iso2,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                method: "POST",
                dataType: 'json',
                success: function (data) {
                      console.log(data);
                      $('#Student').html('<option value=""> -- Please select -- </option>');
                      
                      for (var i = 0; i < data.length; i++) {
                          var studentData = JSON.parse(data[i].json_str); // Parse the JSON string
                          var studentName = data[i].student_name;
                          var studentid = data[i].id;
                          var fatherName = studentData.student_father_name;
                          
                          var fullName = studentName + " - " + fatherName;
                          console.log(fullName);
                          $('#Student').append('<option value="' + studentid + '">' + fullName + '</option>');
                      }
                  },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });



            
        } else {
            $('#Student').html('<option value="">Select class first</option>');
        }
    });
});

// finish logci 
</script>
@endsection