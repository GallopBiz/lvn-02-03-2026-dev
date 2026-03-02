@extends('backend.layouts.main')
@section('main-container')
<style type="text/css">
   .validation_err{
      color: red!important;
   }
   input[type="number"] {
    appearance: textfield;
    -webkit-appearance: textfield;
    -moz-appearance: textfield;
}
input {
    position: relative;
}
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
<meta name="csrf-token" content="{{ csrf_token() }}" />  
        <div class="main-content pt-4">
          <div class="breadcrumb">
            <h1 class="me-2">Pre Enquiry</h1>
          </div>
          
                             <div class="form_section1_div">
                    <form class="" novalidate="novalidate" method="post" action="{{url('filter-preenquiry')}}">
                        @csrf
                        <div class="row">
                           <div class="col-md-2 form-group mb-3">
                              <label for="firstName1">Session :</label>
                              <input type="text" readonly id="session_name" class="form-control" value="" name="session_name">
                              <!-- <select id="session_name" class="form-control" name="session_name" autocomplete="shipping address-level1" required>
                                    <option <?php //if($jsonArr['session_name'] == "2023-2024"){ echo "selected";}?>  value="2023-2024" selected>2023-2024</option>
                                    <option <?php //if($jsonArr['session_name'] == "2022-2023"){ echo "selected";}?>  value="2022-2023" >2022-2023</option>
                                    <option <?php //if($jsonArr['session_name'] == "2021-2022"){ echo "selected";}?>  value="2021-2022" >2021-2022</option>
                                    <option <?php //if($jsonArr['session_name'] == "2020-2021"){ echo "selected";}?>  value="2020-2021" >2020-2021</option>
                              </select> -->
                           </div>
                                               
                           <div class="col-md-2 form-group mb-3">
                              <label for="firstName1">Class :</label>
                              <select id="class" class="form-control" name="class" autocomplete="shipping address-level1" >
                              @if(!empty($EnqSecdata[0]))
                                    <option value=""> -- Please select -- </option>
                                            {{-- @foreach($studentclasses as $studentclasses)
                                                @if($EnqSecdata[0]['class_name'] != $studentclasses->class_name)
                                                <option value="{{ $studentclasses->class_name }}">{{ $studentclasses->class_name }}
                                                </option>
                                                @else
                                                  <option selected value="{{$EnqSecdata[0]['class_name']}}">{{$EnqSecdata[0]['class_name']}}</option>

                                                @endif

                                            @endforeach --}}

                                            @foreach($studentclasses as $studentclass)
                                            @if($EnqSecdata[0]->class_name != $studentclass->class_name)
                                                <option value="{{ $studentclass->class_name }}">{{ $studentclass->class_name }}</option>
                                            @else
                                                <option selected value="{{ $EnqSecdata[0]->class_name }}">{{ $EnqSecdata[0]->class_name }}</option>
                                            @endif
                                        @endforeach





                                @else
                                @if (!empty($studentclasses))
                                        <option value="" selected> -- Please select -- </option>
                                            @foreach($studentclasses as $studentclasses)
                                                <option value="{{ $studentclasses->class_name }}">{{ $studentclasses->class_name }}
                                                </option>
                                            @endforeach
                                @else
                                    <option value="" selected> -- Please select -- </option>
                                @endif
                                @endif
                              </select>
                           </div>

                            <div class="col-md-2 form-group mb-3">
                              <label for="studentname">Student Name</label>
                              <input name="student_name" 
                                 class="form-control"
                                 id="studentname"
                                 placeholder="Enter Student Name" value="<?php if(!empty($jsonArr['student_name'])){ echo $jsonArr['student_name'];}?>"
                                 />
                           </div>

                           <div class="col-md-2 form-group mb-3">   
                                  <lable>Start Date</lable>
                                    <input type="date"  id="picker2" name="fromdate" class="form-control" placeholder="dd-mm-yyyy" value="<?php if(!empty($jsonArr['fromdate'])){ echo $jsonArr['fromdate']; }?>">
                                    <span class="fromdate_msg validation_err"></span>
                                <!-- </div> -->
                            </div>
                            <div class="col-md-2 form-group mb-3">
                                  <lable>End Date</lable>
                                      <input type="date"  id="picker2" name="todate" class="form-control"  placeholder="dd-mm-yyyy" value="<?php if(!empty($jsonArr['todate'])){ echo $jsonArr['todate']; }?>" >
                                      <span class="todate_msg validation_err"></span>
                                  <!-- </div> -->
                            </div>
                           
                            <div class="col-md-3">
                                <button class="btn btn-primary" id="search">Search</button>
                                <a class="btn btn-primary" href="{{url('admin-pre-enquiryform')}}">Clear</a>
                            </div>
                            <div class="separator-breadcrumb "></div>
                            <!-- <div class="col-md-1">
                                <button class="btn btn-warning">Export</button>
                            </div> -->
                        </div>
                    </form>
                </div>
            </div>
          <div class="separator-breadcrumb border-top"></div>

            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                    <h4 class="card-title mb-3 text-end">
                    <a href="{{url('admin-preenquiryform')}}"><button class="btn btn-primary" type="button">Create Pre Enquiry</button></a>
                  </h4>
                  <div class="table-responsive">
                    
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>S No.</th>
                          <th>Form No.</th>
                          <th>Class Name</th>                                               
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Session Name</th>
                          <th>Mobile Number</th>
                          <th>scholar No</th>
                          <th>Enquiry Date</th>
                          <th>Year</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(sizeof($all_inquiry))
                         @if(!empty($all_inquiry))
                                                <?php $i=1;  ?>
                        @foreach($all_inquiry as $each_inq)
                        <?php $notificationData1 = json_decode($each_inq->json_str, true);?>
                        <tr>
                         <!--  <td>{{$each_inq->application_for}}</td> -->
                          <td><?php echo $i; ?></td>
                          <td>{{$each_inq->form_number}}</td>
                          <td>{{$each_inq->class_name}}</td>
                          <td>{{$each_inq->student_name}}</td>
                          <td>{{$notificationData1['fathername_prefix']}} {{$notificationData1['fathername']}}</td>
                          <td>{{$each_inq->session_name}}</td>
                         <td>{{$each_inq->mobile_number}}</td>
                         <td>{{$each_inq->scholar_no}}</td>
                          <!-- <td>@if($each_inq->inq_mode=='off') offline @elseif($each_inq->inq_mode=='on') Online @endif</td> -->
                          <td>{{ date('d-m-Y', strtotime($each_inq->created_at)) }}</td>
                          <td>
                            @if(!empty($each_inq->next_year))
                              <?php 
                                $originalYear = $_COOKIE['selectedYear'];

                                // Split the string into two years
                                $years = explode("_", $originalYear);
                                
                                if (count($years) === 2) {
                                    $startYear = intval($years[0]);
                                    $endYear = intval($years[1]);
                                    $newStartYear = $startYear + 1;
                                    $newEndYear = $endYear + 1;
                                    $newYear = $newStartYear . "_" . $newEndYear;
                                    echo $newYear; // Output: 2024_2025
                                } else {
                                  echo "Invalid input format";
                                }
                              ?>
                            @else
                              {{ $_COOKIE['selectedYear'] }}
                            @endif
                          </td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-primary dropdown-toggle" id="dropdownMenuButton" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Actions
                              </button>
                              <div class="dropdown-menu btn btn-danger " aria-labelledby="dropdownMenuButton"> <a class="dropdown-item " href="{{ route('preenquiryviewlist',$each_inq->id) }}">View</a>
                                <!-- <a class="dropdown-item" href="{{ route('enquiryeditlist',$each_inq->id) }}">Edit</a> -->
                              </div> 
                              <!-- <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"> <a class="dropdown-item" href="{{ route('enquiryeditlist',$each_inq->id) }}">Edit</a>
                              </div> -->
                            </div>
                          </td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                        @else
                        <tr><td colspan="9" class="text-center"><span class="fontcolor-error">There Are No Records Available</span></td></tr>
                        @endif
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <!-- <th>Application For</th> -->
                          <th>S No.</th>
                          <th>Form No.</th>
                          <th>Class Name</th>                                               
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Session Name</th>
                          <th>Mobile Number</th>
                          <th>Enquiry Date</th>
                          <th>Year</th>
                          <th>Status</th>
                          <td>scholar No</td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            <!-- </div>
          </div> -->
          <!-- end of main-content -->
          <!-- Modal dialog -->



        <!-- </div> -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.6/dist/sweetalert2.min.css">

          <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.6/dist/sweetalert2.min.js"></script>


    <script>
    $(document).ready(function(){
      $(document).ready( function() {
        setTimeout(function() {
          var year = $("#year").val()
          $("#session_name").val(year);
        }, 1000);
      });
        // Department Change
        // var count = $('#select_count').val();
        // for (i=1; i<= count; i++){
        //     $('#save_status'+i).on('change', function () {
        //         var id = $(this).attr('aaa');
        //         var save_status = $(this).val();
        //         // alert(save_status);
        //         var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        //         $.ajax({
        //         url: 'save-selection-process',
        //         type: 'post',
        //         data: {_token: CSRF_TOKEN, id: id, save_status: save_status},
        //         dataType: 'json',
        //             success: function(response){
        //                 if (response.success != ''){
        //                     $("#bsModal3").modal('show');
        //                     // console.log(response.success);
        //                 }
        //             }
        //         });
        //     });
        // }
var count = $('#select_count').val();
for (i=1; i<= count; i++){
    $('#save_status'+i).on('change', function () {
      Swal.fire({
        title: 'Confirm',
        text: 'Are you sure you want to change the selection?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No'
        }).then((result) => {
        if (result.isConfirmed) {
            var id = $(this).attr('aaa');
            var save_status = $(this).val();
            // alert(save_status);
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
            url: 'save-selection-process',
            type: 'post',
            data: {_token: CSRF_TOKEN, id: id, save_status: save_status},
            dataType: 'json',
                success: function(response){
                    if (response.success != ''){
                        // $("#bsModal3").modal('show');
                        console.log(response.success);

                        Swal.fire({ title: 'Status change!', text: 'Status are successfully Changed!', icon: 'success'});
                    }
                }

//        var count = $('#select_count').val();
//        for (i=1; i<= count; i++){
//            $('#save_status'+i).on('change', function () {
//              var id = $(this).attr('aaa');
//              var save_status = $(this).val();
//              confirmDialog(YOUR_MESSAGE_STRING_CONST, function(){
//                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
//                $.ajax({
//                url: 'save-selection-process',
//                type: 'post',
//                data: {_token: CSRF_TOKEN, id: id, save_status: save_status},
//                dataType: 'json',
//                    success: function(response){
//                        if (response.success != ''){
//                            $("#bsModal3").modal('show');
//                            console.log(response.success);
//                        }
//                    }
//                });
//              });
            });
        } else {
          // User canceled the change, revert back to previous selection if needed
            $(this).prop('selectedIndex',0);
            Swal.fire("Cancelled", "Your imaginary file is safe :)", "error");
        }
      });
    });

    }

    });

    var YOUR_MESSAGE_STRING_CONST = "<h4>Please confirm Status need to be change?<h4>";
    function confirmDialog(message, onConfirm){
      var fClose = function(){
        modal.modal("hide");
      };
      var fClosee = function(){
        var modal = $("#bsModal3");
        // modal.modal("show");
        modal.modal("hide");
      };
      var modal = $("#confirmModal");
      modal.modal("show");
      $("#confirmMessage").empty().append(message);
      $("#confirmOk").unbind().one('click', onConfirm).one('click', fClose);
      $("#confirmCancel").unbind().one("click", fClose);
      $("#confirmCancel1").unbind().one("click", fClosee);

    }
    </script>
@endsection 
