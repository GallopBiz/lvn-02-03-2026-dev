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
          <h2>Bus Attendance List </h2>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
          <div class="col-md-12 mb-4">
            <div class="form_section1_div">  
            <h4 class="card-title mb-3 text-end"><a href="{{url('bus-attandence-list')}}"><button class="btn btn-outline-primary" type="button">Back</button></a></h4>
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
                        @if(!empty($filterlistbusattandence))
                        @foreach($filterlistbusattandence as $listA)
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
@endsection