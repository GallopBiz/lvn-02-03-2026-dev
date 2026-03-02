@extends('backend.layouts.main')
@section('main-container')
<style>
    input {
    position: relative;
}
    input[type="date"]::-webkit-calendar-picker-indicator {
    background-position: right;
    background-size: auto;
    cursor: pointer;
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    top: 7px;
    width: auto;
}

</style>
<div class="main-content pt-4">
<meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- <h2>hyy</h2> -->
    @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
          <div class="breadcrumb">
          <h2>Bus Attendance List </h2>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
          <div class="col-md-12 mb-4">
            <div class="form_section1_div">  
                    <form class="" novalidate="novalidate" method="post" action="{{url('filter-busattendence')}}">
                        @csrf
                        <div class="row">
                            <!-- <div class="col-md-3 form-group mb-3">
                              <label for="studentname">Student Name</label>
                              <input name="student_name" 
                                 class="form-control"
                                 id="studentname"
                                 placeholder="Enter Student Name"
                                 />
                           </div> -->
                           <div class="col-md-2 form-group mb-3">   
                                  <lable>Start Date</lable>
                                    <input type="date"  id="picker2" name="fromdate" class="form-control" placeholder="dd-mm-yyyy" >
                                    <span class="fromdate_msg validation_err"></span>
                                <!-- </div> -->
                            </div>
                            <div class="col-md-2 form-group mb-3">
                                  <lable>End Date</lable>
                                      <input type="date"  id="picker2" name="todate" class="form-control"  placeholder="dd-mm-yyyy" >
                                      <span class="todate_msg validation_err"></span>
                                  <!-- </div> -->
                            </div>
                          
                           
                            <div class="col-md-1">
                              <br>
                                <button class="btn btn-primary">Search</button>
                            </div><div class="separator-breadcrumb"></div>
                            <!-- <div class="col-md-1">
                                <button class="btn btn-warning">Export</button>
                            </div> -->
                        </div>
                    </form>
                </div>
              </div>
            <div class="col-md-7 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  @php
                      $i = 0;
                    @endphp
                  <div class="table-responsive">
                  <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>No</th>
                            <th>Bus staff Name</th>
                            <th>Bus no.</th>
                            <th>View all</th>
                            
                        </tr>
                      </thead>
                      
                      <tbody>
                        @if(!empty($listbusattandence))
                        @foreach($listbusattandence as $listA)
                        <tr>
                            <td>{{++$i}}</td>
                            <td>{{$listA->DC_name}}</td>
                            <td>{{$listA->Bus_no}}</td>
                            <td><button class="btn btn-primary m-1"  onclick="viewAll({{ $listA->id }})" >View all</button>
                            </td>
                        </tr>
                    @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                            <th>No</th>
                            <th>Bus staff Name</th>
                            <th>Bus no.</th>
                            <th>View all</th>
                            
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <!-- student list  -->
            <div class="col-md-5 mb-4">
                <div class="card-body">
                    <div class="table-responsive" style="height: 775px; overflow-y: auto;">
                        <table class="display table table-striped table-bordered" id="bus_Attend" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Address</th>
                                    <th>Class Name</th>
                                    <th>Attendance</th>
                                </tr>
                            </thead>
                            <tbody id="bus_Attend_data">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- end student list -->
          </div>
    <!-- end of main-content -->
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function() {
    // Function to handle the date input
    $("#picker2").on("input", function() {
      // Do something with the date value
      const dateValue = $(this).val();
      console.log("Selected Date:", dateValue);
    });
  });
</script>
@endsection