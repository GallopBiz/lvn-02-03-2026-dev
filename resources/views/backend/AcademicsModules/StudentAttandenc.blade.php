@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
<meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- <h2>hyy</h2> -->
    @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
          <div class="breadcrumb">
          <h2>Student wise Attandence</h2>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
          <div class="col-md-12 mb-4">
            <div class="form_section1_div">
                    <form class="" novalidate="novalidate" method="post" action="{{url('filter-classattendence')}}">
                        @csrf
                        <div class="row">

                          <div class="col-md-2 form-group mb-3">
                            <label for="firstName1">Class Name</label>
                            <select id="classname" class="form-control" name="classname" autocomplete="" required>
                               <option value="" disabled selected>Please select</option>
                               @foreach($classlist as $each)
                               <option value="{{$each->id}}">{{$each->class_name}}</option>
                               @endforeach
                                {{-- @foreach(config('global.class_name') as $each)
                               <option value="{{$each}}">{{$each}}</option>
                               @endforeach  --}}
                            </select>
                            <span class="classname_msg validation_err"></span>
                          </div>



                            {{-- <div class="col-md-2 form-group mb-3">
                              <label for="studentname">Class</label>
                              <select id="class" class="form-control" name="Class" autocomplete="shipping address-level1" required>
                                    <option value="" selected> -- Please select -- </option>
                                     @foreach($uniqueNames as $country)
                                        <option value="{{$country}}">{{$country}}</option>
                                     @endforeach
                              </select>
                           </div> --}}


                           <div class="col-md-2 form-group mb-3">
                              <label for="studentname">Student</label>
                              <select id="inq-form-nomenu" class="search-student-dropdown  form-control uperletter " onchange="getValAndAssign(event);" name="inq_form_selection" required>
                <option selected>--Please Select--</option>
                <?php $student_data = app('global_areas');
                // print_r($student_data);

                ?>
                @if (!empty($student_data))

                @foreach ($student_data as $each)
                @if ($each->type != 't')
                <option value="{{ $each->id }}">
                    {{ $each->student_name }}

                    @if ($each->form_number)
                    - {{ $each->form_number }}
                    @endif

                    <?php
                    $jsondata = json_decode($each->json_str);
                    ?>
                    @if ($each->json_str)
                    @if (isset($jsondata->is_staff_applied_for_admission) && $jsondata->is_staff_applied_for_admission != '')
                    - Staff
                    @else
                    @if ($each->application_for == 'RTE')
                    - RTE
                    @else
                    - Non RTE
                    @endif
                    @endif
                    @else
                    @if ($each->application_for == 'RTE')
                    - RTE
                    @else
                    - Non RTE
                    @endif
                    @endif

                    @if ($each->json_str)
                    @if (isset($jsondata->siblings_name) && $jsondata->siblings_name != '')
                    - Sibling
                    @endif
                    @endif
                </option>
                @endif
                @endforeach
                @endif
            </select>
                           </div>



                           <div class="col-md-2 form-group mb-3">
                                  <lable>Start Date</lable>
                                    <input type="date"  id="picker2" name="fromdate" class="form-control" placeholder="dd-mm-yyyy" >
                                    <span class="fromdate_msg validation_err" required></span>
                                <!-- </div> -->
                            </div>
                            <div class="col-md-2 form-group mb-3">
                                  <lable>End Date</lable>
                                      <input type="date"  id="picker2" name="todate" class="form-control"  placeholder="dd-mm-yyyy" >
                                      <span class="todate_msg validation_err" required></span>
                                  <!-- </div> -->
                            </div>


                            <div class="col-md-2">
                              <br>
                                <button class="btn btn-primary">Search</button>
                                <button type="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>

                            </div>

                            <div class="separator-breadcrumb"></div>
                            <!-- <div class="col-md-1">
                                <button class="btn btn-warning">Export</button>
                            </div> -->
                        </div>
                    </form>
                </div>
              </div>
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  @php
                      $i = 0;
                    @endphp
                  <div class="table-responsive">
                  <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                  <tbody>
                          @if(!empty($finalAttandence))
                              <tr>
                                  <td>Name</td>
                                  <td>{{ $finalAttandence[2][0]->student_name }}</td>
                              </tr>
                              <tr>
                                  <td>Class Number</td>
                                  <td>{{ $finalAttandence[0] }}</td>
                              </tr>
                              <tr>
                                  <td>Attendance</td>
                                  <td>{{ $finalAttandence[1] }}</td>
                              </tr>
                              <tr>
                                  <td>Class</td>
                                  <td>{{ $finalAttandence[3] }}</td>
                              </tr>
                          @else
                              <tr>
                                  <td colspan="2" class="text-center">No Data Found</td>
                              </tr>
                          @endif
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
    <!-- end of main-content -->
</div>
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<!-- Include jQuery -->

{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script> --}}

<script>
    jQuery(document).ready(function($) {
        // Initialize Select2
        // $('#inq-form-nomenu').select2();
        $('.search-student-dropdown').select2();

        // Wait for Select2 to be fully initialized before applying styles
        // $('#inq-form-nomenu').on('select2:open', function() {
        //     // Capitalize the text in the search box
        //     $('.select2-search__field').css('text-transform', 'capitalize');
        // });
    });
</script>








<script>
//  function to class wise student

$(document).ready(function () {
  $('#classname').on('change', function () {
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
                      // console.log(data);
                      $('#student').html('<option value=""> -- Please select -- </option>');

                      for (var i = 0; i < data.length; i++) {
                          var studentData = JSON.parse(data[i].json_str); // Parse the JSON string
                          var studentName = data[i].student_name;
                          var fatherName = studentData.student_father_name;

                          var fullName = studentName + " - " + fatherName;
                          console.log(fullName);
                          $('#student').append('<option value="' + fullName + '">' + fullName + '</option>');
                      }
                  },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        } else {
            $('#student').html('<option value="">Select class first</option>');
        }
    });
});

// finish logci

  var responseData; // Variable to store the response
  function viewAll(id) {
    console.log(id);
    $.ajax({
      data: { id: id },
      url: "{{ url('busstudent-view') }}/" + id,
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      method: "POST",
      dataType: 'json',
      success: function(response) {
        responseData = response;
  console.log(responseData);
  responseData = JSON.parse(JSON.stringify(responseData));
  console.log(responseData.length);
  var stu = JSON.parse(responseData[0].json_str);
  // console.log(stu.length);
  // Clear the existing table body
  $('#bus_Attend_data tbody').empty();
  // Iterate over the arrays and construct the table rows
  for (var i = 0; i < stu.length; i++) {

    // console.log(stu[i].name);
    var newRow =  '<tr>' +
      '<td style="font-size: 16px; font-weight: bold;">' + stu[i].name + '</td>' +
      '<td style="font-size: 16px;">' + stu[i].class + '</td>' +
      '<td style="font-size: 16px;">' + stu[i].address + '</td>' +
      '<td style="font-size: 16px;">' + stu[i].student_id + '</td>' +
      '</tr>';
      // console.log(newRow);
      $('#bus_Attend_data').append(newRow);
  }
},
      error: function(xhr) {
        console.error(xhr.responseText);
      }
    });
  }
</script>
@endsection
