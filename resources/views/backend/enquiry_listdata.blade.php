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


.uperletter{
  text-transform: capitalize;
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
.blink {
  animation: blinker 1.5s linear infinite;  
  font-family: sans-serif;
            }
@keyframes blinker {
  50% {
  opacity: 0;
  }
}

</style>
<meta name="csrf-token" content="{{ csrf_token() }}" />
        <div class="main-content pt-4">
          <div class="breadcrumb">
            <h1 class="me-2">Enquiry List</h1>
          </div>
            <!-- Modal -->

            

            <div class="separator-breadcrumb border-top"></div>
            <div class="row">
              <div class="col-md-10 mb-4">
                  <div class="form_section1_div">
                      <form class="rg_form" novalidate="novalidate" method="post" action="{{url('filter-enquiry-list')}}">
                          {{ csrf_field() }}
                          <div class="row">
                          <div class="col-md-2 form-group mb-3">
                              <label for="firstName1">Session :</label>
                            <input type="text" readonly id="session_name" class="form-control" value="" name="session_name">

                              <!-- <select id="session_name" class="form-control" name="session_name" autocomplete="shipping address-level1" required>
                                <option value="" disabled selected>Please Select</option>
                                @foreach($databaseNames as $databaseName)
                                  <option value="{{$databaseName}}">{{$databaseName}}</option>
                                @endforeach
                              </select> -->
                           </div>
                           

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
                          <div class="col-md-3 form-group mb-3">
                              <label for="studentname">Student Name</label>
                              <input name="student_name" 
                                 class="form-control uperletter"
                                 id="studentname"
                                 placeholder="Enter Student Name"
                                 />
                           </div>
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
                          </div>  
                          
                          <div class="col-md-3">
                                <button class="btn btn-primary">Search</button>
                                <input type="reset" class="btn btn-danger text text-white" value="Reset">
                                {{-- <a class="btn btn-primary" href="{{url('adminenquirylist')}}">Clear</a> --}}
                            </div>
                              <!-- <div class="col-md-1">
                                  <button class="btn btn-warning">Export</button>
                              </div> -->
                          </div>
                      </form>
                  </div>
              </div>
              <div class="col-md-2 mb-4">
                <form class="" novalidate="novalidate" method="post" action="{{url('filter-enquiry-list')}}">
                  {{ csrf_field() }}
                  <input type="hidden" id="picker2" name="todayfollow" class="form-control" value="<?php echo date("Y-m-d");?>">
                  <div class="col-md-12 form-group mb-3">
                      {{-- <div class="card-body">
                          <label> Today Follow up </label>
                          <button class="btn w-100 btn-outline-warning mb-2 " id="" style="background-color:#ffb400; color: black;">
                              * Today Followup : </?php echo count($todatfollowu ?? []);?>*
                          </button>
                      </div> --}}
                  </div>
              </form>
              
            </div>
            <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                <div class="card-title mb-3 text-end"><a class="dropdown-item" href="{{url('enquiry-csv') }}">Export CSV</a></div>
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

















                    {{-- <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                          <th>S No.</th>    
                          <th>Class Name</th>                                               
                          <th>Student Name</th>
                          <th>Session Name</th>                          
                          <th>Date</th>
                          
                          
                          {{-- <th>Action</th> --}}
                        {{-- </tr> --}}
                      {{-- </thead> --}}
                      {{-- <tbody>
                        @if(!empty($all_inquiry))
                        </?php $i=1; ?>
                        @foreach($all_inquiry as $each_inq)
                        </?php //echo '<pre>'; print_r($each_inq);?>
                         </?php $notificationData1 = json_decode($each_inq->json_str, true);?>
                        <tr </?php if(!empty($notificationData1['folloupdate_status'])){ if($notificationData1['folloupdate_status'] == "Finally Closed" or $notificationData1['folloupdate_status'] == "Cancel") {echo 'class="disabled"';} } ?>>
                          <td></?php echo $i; ?></td>
                          <td>{{$each_inq->form_number}}</td>
                          <td>{{$each_inq->form_number}}</td>
                          <!-- <td>{{ date('d-m-Y', strtotime($each_inq->created_at)) }}</td> -->
                          <td></?php if(!empty($notificationData1['folloupdate_status'])){ if($notificationData1['folloupdate_status'] == "Form Taken"){ echo "";}else{  if($notificationData1['follow_up_date'] != null) { echo date('d-m-Y', strtotime($notificationData1['follow_up_date']));} } }?></td>
                          <td></?php if(!empty($each_inq->class_name)){ echo $each_inq->class_name;}?></td>
                          <td></?php if(!empty($each_inq->student_name)){ echo ucwords($each_inq->student_name);}?></td>
                          <td></?php if(!empty($each_inq->session_name)){ echo $each_inq->session_name;}?></td>
                          <td></?php if(!empty($each_inq->mobile_number)){ echo $each_inq->mobile_number;}?></td>
                          <td>
                            <div class="dropdown"> --}}
                                {{-- <select name="save_status</?php echo $i; ?>" aaa="</?php echo $each_inq->id; ?>" jsonstr="{{$each_inq->json_str}}" id="save_status</?php echo $i; ?>" class="form-control"> --}}
                                   {{-- </?php   --}}
                                    {{-- //if ($notificationData1['folloupdate_status'] == '') {  --}}
                                      {{-- //  echo '<option value="">Select</option>'; --}}
                                    {{-- //} else { ?> --}}
                                        <!-- </option> -->
                                    {{-- </?php //}  --}}
                                    {{-- // if($notificationData1['folloupdate_status'] == "Finally Closed"){ } --}}
                                      {{-- //else { ?> --}}
                                    {{-- </?php if(!empty($notificationData1['folloupdate_status'])){
                                    ?>    
                                    <option value="Form Taken">?php if(!empty($notificationData1['folloupdate_status'])){ echo $notificationData1['folloupdate_status']; }?></option> 
                                    </?php if($notificationData1['folloupdate_status'] == "Admission/Confirm" or $notificationData1['folloupdate_status'] == "Cancel"){?>
                                      <option value="Form Taken">Form Taken</option>  
                                      <option value="Admission/Confirm">Admission/Confirm</option>
                                      <option value="Cancel">Cancel</option>
                                      <option value="Pending">Pending</option>
                                   </?php }else{?> 
                                    <option value="Form Taken">Form Taken</option>  
                                    <option value="Admission/Confirm">Admission/Confirm</option>
                                    <option value="Cancel">Cancel</option>
                                    <option value="Pending">Pending</option>
                                    
                                  </?php }}else{ ?>
                                    <option value="Pending">Pending</option>
                                     <option value="Form Taken">Form Taken</option> 
                                    <option value="Admission/Confirm">Admission/Confirm</option>
                                    <option value="Cancel">Cancel</option>
                                    </?php } ?>

                                </select>
                            </div>
                          </td>
                          <td>Admin</td>

                          <td>
                            <div class="dropdown">
                              <a class="btn btn-primary btn-block" href="{{ route('follow.edit',$each_inq->id) }}">Save Date</a>
                            </div>
                          </td> --}}

                        {{-- </tr>
                        </?php $i++; $notificationData='';?>
                        @endforeach
                         <input type="hidden" id="select_count" name="select_count" value="</?php echo $i-1; ?>">
                        @else
                        <tr><td colspan="9" class="text-center"><span class="fontcolor-error">There Are No Records Available</span></td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr> --}}
                          {{-- <th>S No.</th> --}}
                          {{-- <th>Enquiry No.</th> --}}
                          {{-- <th>Form No.</th> --}}
                          <!-- <th>Inter Dt.</th> -->
                          {{-- <th>Follow up date</th> --}}

                          {{-- <th>Class Name</th>                                               
                          <th>Student Name</th>
                          <th>Session Name</th>                          
                          <th>Data</th> --}}

                          {{-- <th>Status</th> --}}
                          {{-- <th>Updated by</th> --}}
                          {{-- <th>Action</th> --}}
                        {{-- </tr> --}}
                      {{-- </tfoot> --}}
                    {{-- </table> --}}





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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    {{-- <script>
$(document).ready(function () {
    var count = $('#select_count').val();
    for (i = 1; i <= count; i++) {
        $('#save_status' + i).on('change', function () {
            var selectedOption = $(this).val();
            var id = $(this).attr('aaa');
            var jsonstr = $(this).attr('jsonstr');
            
            if (selectedOption === 'Cancel') {
                // Prompt the user for a remark
                Swal.fire({
                    title: 'Cancel Confirmation',
                    text: 'Please provide a remark for cancellation:',
                    input: 'text',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var cancelRemark = result.value;
                        // Continue with your AJAX request
                        sendData(id, selectedOption, jsonstr, cancelRemark);
                    } else {
                        // User canceled the change, revert back to previous selection if needed
                        $(this).val('Form Taken'); // Or any other default value
                        Swal.fire("Cancelled", "Your imaginary file is safe :)", "error");
                    }
                });
            } else {
                Swal.fire({
                    title: 'Confirm',
                    text: 'Are you sure you want to change the selection?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            sendData(id, selectedOption, jsonstr, '');
                        } else {
                            // User canceled the change, revert back to previous selection if needed
                            $(this).val('Form Taken'); // Or any other default value
                            Swal.fire("Cancelled", "Your imaginary file is safe :)", "error");
                        }
                // No need for remark, continue with your AJAX request
               
            });
        }});
    }
    
    function sendData(id, selectedOption, jsonstr, cancelRemark) {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: 'save-followup-status',
            type: 'post',
            data: {
                _token: CSRF_TOKEN,
                id: id,
                save_status: selectedOption,
                jsonstr: jsonstr,
                cancelRemark: cancelRemark // Send the cancel remark
            },
            dataType: 'json',
                success: function(response){
                    if (response.success != ''){
                        // $("#bsModal3").modal('show');
                        console.log(response.success);

                    Swal.fire({
                        title: 'Status change!',
                        text: 'Status are successfully Changed!',
                        icon: 'success'
                    });
                }
            }
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



let currentDate = new Date().toISOString().slice(0, 10);
currentDate = currentDate.split("-").reverse().join("-");
    // Set the current date as the default value for the date input fields
    
    document.getElementById("fromDate").value = currentDate;
    document.getElementById("toDate").value = currentDate;


    $(document).ready(function () {
    // Your initial table data
    var tableData = </?php echo json_encode($all_inquiry); ?>;
    var currentDate = "</?php echo date("d-m-Y"); ?>"; // Get the current date in dd-mm-yyyy format

    // Filter the table data to show only the current date by default
    var filteredData = tableData.filter(item => item.follow_up_date === currentDate);

    // Function to update the table based on selected date and status
    function updateTable() {
        var selectedDate = $('#dateFilter').val();
        var selectedStatus = $('#statusFilter').val();

        var filteredData = tableData;

        // Filter by date if a date is selected
        if (selectedDate) {
            filteredData = filteredData.filter(item => item.follow_up_date === selectedDate);
        }

        // Filter by status if a status is selected
        if (selectedStatus) {
            filteredData = filteredData.filter(item => item.folloupdate_status === selectedStatus);
        }

        // Clear the table
        $('#zero_configuration_table tbody').empty();

        // Populate the table with filtered data
        filteredData.forEach(function (item) {
           
        });
    }

    // Update the table when date or status selection changes
    $('#dateFilter, #statusFilter').change(updateTable);

    // Populate the date dropdown with unique dates from your data
    var uniqueDates = [...new Set(tableData.map(item => item.follow_up_date))];
    uniqueDates.forEach(function (date) {
        $('#dateFilter').append($('<option>', {
            value: date,
            text: date
        }));
    });

    
    $('#dateFilter').val(currentDate);

    
    updateTable();
});

    </script> --}}

    <script>
      $(document).ready(function() {
        setTimeout(function() {
          var year = $("#year").val()
          $("#session_name").val(year);
        }, 1000);
      //   $("#fromDate").on("input", function() {
          
      //     const dateValue = $(this).val();
      //     console.log("Selected Date:", dateValue);
      //   });



      var count = $('#select_count').val();
    for (i = 1; i <= count; i++) {
        $('#save_status' + i).on('change', function () {
            var selectedOption = $(this).val();
            var id = $(this).attr('aaa');
            var jsonstr = $(this).attr('jsonstr');
            
            if (selectedOption === 'Cancel') {
                // Prompt the user for a remark
                Swal.fire({
                    title: 'Cancel Confirmation',
                    text: 'Please provide a remark for cancellation:',
                    input: 'text',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var cancelRemark = result.value;
                        // Continue with your AJAX request
                        sendData(id, selectedOption, jsonstr, cancelRemark);
                    } else {
                        // User canceled the change, revert back to previous selection if needed
                        $(this).val('Form Taken'); // Or any other default value
                        Swal.fire("Cancelled", "Your imaginary file is safe :)", "error");
                    }
                });
            } else {
                Swal.fire({
                    title: 'Confirm',
                    text: 'Are you sure you want to change the selection?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            sendData(id, selectedOption, jsonstr, '');
                        } else {
                            // User canceled the change, revert back to previous selection if needed
                            $(this).val('Form Taken'); // Or any other default value
                            Swal.fire("Cancelled", "Your imaginary file is safe :)", "error");
                        }
                // No need for remark, continue with your AJAX request
               
            });
        }});
    }


    function sendData(id, selectedOption, jsonstr, cancelRemark) {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: 'save-followup-status',
            type: 'post',
            data: {
                _token: CSRF_TOKEN,
                id: id,
                save_status: selectedOption,
                jsonstr: jsonstr,
                cancelRemark: cancelRemark // Send the cancel remark
            },
            dataType: 'json',
                success: function(response){
                    if (response.success != ''){
                        // $("#bsModal3").modal('show');
                        console.log(response.success);

                    Swal.fire({
                        title: 'Status change!',
                        text: 'Status are successfully Changed!',
                        icon: 'success'
                    });
                }
            }
        });
    }
    
      });
    </script>
    
    <!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script> -->
    <script>
    
    document.addEventListener('DOMContentLoaded', function() {
      var today = new Date().toISOString().split('T')[0];
      document.getElementById('fromDate').value = today;
    });
</script>





@endsection 
