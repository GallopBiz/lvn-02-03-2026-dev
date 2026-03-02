@extends('backend.layouts.main')     
 
{{-- <//?php echo $todate ?> --}}
{{-- </?php echo $fromdate ?> --}}
  
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
tr.disabled {
    background-color: #8080806b;
}
</style>
<meta name="csrf-token" content="{{ csrf_token() }}" />
        <div class="main-content pt-4">
          <div class="breadcrumb">
            <h1 class="me-2">Enquiry List</h1>
          </div>
            <!-- Modal -->
           
          <!-- end Modal -->

            <!-- Modal confirm -->
            <div class="modal" id="confirmModal" style="display: none;">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                      Information
                    </h5>
                    <button class="btn btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body" id="confirmMessage">
                  </div>
                 
                </div>
              </div>
            </div>
            <!-- end modal -->
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-10 mb-4">
                <div class="form_section1_div">

                <form class="" novalidate="novalidate" method="post" action="{{url('filter-enquiry-list')}}">
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
                                <a class="btn btn-primary" href="{{url('enquiry-data')}}">Clear</a>
                            </div>
                            <div class="separator-breadcrumb "></div>
                            <!-- <div class="col-md-1">
                                <button class="btn btn-warning">Export</button>
                            </div> -->
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-2 mb-4">
            <form class="rg_form" novalidate="novalidate" method="post" action="{{url('filter-enquiry-list')}}">
             {{ csrf_field() }}
              <input type="hidden"  id="picker2" name="todayfollow" class="form-control" value="<?php echo date("Y-m-d");?>">
            <div class="col-md-12 form-group mb-3">
                             
          </div>
          </form>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                <div class="card-title mb-3 text-end">
                    <a class="dropdown-item" href="{{ url('filter-enquiry-csv') . '?' . http_build_query($jsonArr) }}">Export CSV</a>
                </div>

                  <div class="table-responsive">

                  <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
  <!-- Table headers -->
  <thead>
      <tr>
          <th>S No.</th>
          <th>Class Name</th>
          <th>Student Name</th>
          <th>Session Name</th>
          <th>Date</th>
          <th>Mode</th>
          <th>Amount</th>
          <!-- Add or modify other headers as needed -->
      </tr>
  </thead>

  <tbody>
    @if(!empty($all_inquiry))
        <?php $i=1; ?>
        @foreach($all_inquiry as $each_inq)
            <?php
                $notificationData1 = json_decode($each_inq->json_str, true);
                $enquiryDate = !empty($notificationData1['enquirydate']) ? date('d-m-Y', strtotime($notificationData1['enquirydate'])) : '';
                $receivedamount = !empty($notificationData1['received_amount']) ? $notificationData1['received_amount'] : '';
                $referencenumber = !empty($notificationData1['reference_number']) ? $notificationData1['reference_number'] : '';
            ?>
            <tr <?php if(!empty($notificationData1['folloupdate_status'])){ if($notificationData1['folloupdate_status'] == "Finally Closed" or $notificationData1['folloupdate_status'] == "Cancel") {echo 'class="disabled"';} } ?>>
                <td><?php echo $i; ?></td>
                <td>{{$each_inq->class_name}}</td>
                <td class="uperletter">{{$each_inq->student_name}}</td>
                <td>{{$each_inq->session_name}}</td>
                <td>{{$enquiryDate}}</td> <!-- Display the extracted enquiry date -->
                <td>{{$receivedamount}}</td>
                <td>{{$referencenumber}}</td>
                
            </tr>
            <?php $i++; $notificationData='';?>
        @endforeach
    @else
        <tr><td colspan="5" class="text-center"><span class="fontcolor-error">There Are No Records Available</span></td></tr>
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
     
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.6/dist/sweetalert2.min.css">

          <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.6/dist/sweetalert2.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
$(document).ready(function(){
      
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
            var jsonstr = $(this).attr('jsonstr'); 
            var save_status = $(this).val();
            // alert(save_status);
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
            // url: 'save-followup-status',
            type: 'post',
            data: {_token: CSRF_TOKEN, id: id, save_status: save_status, jsonstr: jsonstr},
            dataType: 'json',
                success: function(response){
                    if (response.success != ''){
                        // $("#bsModal3").modal('show');
                        console.log(response.success);

                        Swal.fire({ title: 'Status change!', text: 'Status are successfully Changed!', icon: 'success'});
                    }
                }


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

    //validation
    $(".submit_btn").on('click', function (e) {

      e.preventDefault();

      var fromdate = $('input[name="fromdate"]').val();
      var todate = $('input[name="todate"]').val();
      
      if(fromdate==''){
        $('.fromdate_msg').text("This field is required*");
      }else{

        $('.fromdate_msg').text("");
      }

      if(todate==''){
        $('.todate_msg').text("This field is required*");
      }else{

        $('.todate_msg').text("");
      }

     
      if(fromdate!=='' &&  todate!==''){
         $('.rg_form').submit();
      }else{
         console.log("invalid form");
      }


   });



  //  const currentDate = new Date().toISOString().slice(0, 10);
//  document.getElementById("fromDate").value = currentDate;
// document.getElementById("toDate").value = currentDate;






    </script>




@endsection 