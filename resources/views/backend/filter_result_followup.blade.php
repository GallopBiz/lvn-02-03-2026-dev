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
            <h1 class="me-2">Follow Up Date</h1>
          </div>
            <!-- Modal -->
            <div class="modal" id="bsModal3">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                      Information
                    </h5>
                    <button class="btn btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <h4>Status Change success fully</h4>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="confirmCancel1">Close</button>
                  </div>
                </div>
              </div>
            </div>
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
                  <div class="modal-footer">
                    <button type="button" class="btn btn-success text-white" id="confirmOk">Ok</button>
                    <button type="button" class="btn btn-danger text-white" id="confirmCancel">Cancel</button>
                  </div>
                </div>
              </div>
            </div>
            <!-- end modal -->
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-10 mb-4">
                <div class="form_section1_div">

                    <form class="needs-validation rg_form" novalidate="novalidate" method="post" action="{{url('filter-followup')}}"  enctype="multipart/form-data">
                       {{ csrf_field() }}
                        <div class="row">
                          <div class="col-md-2 form-group mb-3">
                            <label>From Date</label>
                            <input  value="<?php echo $fromdate ?>" type="date" id="fromDate" name="fromdate" class="newdatepicker form-control" placeholder="dd-mm-yyyy" required>
                            <span class="fromdate_msg validation_err"></span>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label>To Date</label>
                            <input  value="<?php echo $todate ?>" type="date" id="toDate" name="todate" class="newdatepicker form-control" placeholder="dd-mm-yyyy" required>
                            <span class="todate_msg validation_err"></span>
                        </div>

                           <div class="col-md-8 form-group mb-3">
                              
                           </div>
                            <div class="col-md-10">
                                <button class="btn btn-primary submit_btn">Search</button>
                                <a class="btn btn-primary" href="{{url('followupdate')}}">Clear</a>
                            </div>
                            <!-- <div class="col-md-1">
                                <button class="btn btn-warning">Export</button>
                            </div> -->
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-2 mb-4">
			<?php /*
            <form class="rg_form_new" novalidate="novalidate" method="post" action="{{url('filter-followup')}}">
             {{ csrf_field() }}
              <input type="hidden"  id="picker2" name="todayfollow" class="form-control" value="<?php echo date("Y-m-d");?>">
            <div class="col-md-12 form-group mb-3">
                             <div class="card-body">
                                <lable> Today Follow up </lable>
                           <button class="btn w-100 btn-outline-warning mb-2 blink" id="" style="background-color:#ffb400; color: black;"><?php if($all_inquiry){ $follow= count($all_inquiry); }?>
                                  * Today Followup : <?php if(!empty($follow)){ echo count($all_inquiry);}else{ echo "0";}?>*
                            </button>                            
                              </div> 
          </div>
          </form> */  ?>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  <h4 class="card-title mb-3 text-end">
                   </h4>
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                          <th>S No.</th>
                          <th>Enquiry No.</th>
                          <th>Form No.</th>
                          <!-- <th>Inter Dt.</th> -->
                          <th>Follow up date</th>
                          <th>Class Name</th>                                               
                          <th>Student Name</th>
                          <th>Session Name</th>                          
                          <th>Mobile Number</th>
                          <th>Status</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(sizeof($all_inquiry))
                        <?php $i=1; ?>
                        @foreach($all_inquiry as $each_inq)
                        <?php //echo '<pre>'; print_r($each_inq);?>
                         <?php $notificationData1 = json_decode($each_inq->json_str, true);?>
                              
                        <tr <?php if(!empty($notificationData1['folloupdate_status'])){ if($notificationData1['folloupdate_status'] == "Finally Closed" or $notificationData1['folloupdate_status'] == "Cancel") {echo 'class="disabled"';} } ?>>
                          <td><?php echo $i; ?></td>
                          <td>{{$each_inq->form_number}}</td>
                          <td>{{$each_inq->form_number}}</td>
                          <!-- <td>{{ date('d-m-Y', strtotime($each_inq->created_at)) }}</td> -->
                          <td><?php if(!empty($notificationData1['folloupdate_status'])){ if($notificationData1['folloupdate_status'] == "Form Taken"){ echo "";}else{  if($notificationData1['follow_up_date'] != null) { echo date('d-m-Y', strtotime($notificationData1['follow_up_date']));} } }?></td>
                          <td><?php if(!empty($each_inq->class_name)){ echo $each_inq->class_name;}?></td>
                          <td><?php if(!empty($each_inq->student_name)){ echo ucwords($each_inq->student_name);}?></td>
                          <td><?php if(!empty($each_inq->session_name)){ echo $each_inq->session_name;}?></td>
                          <td><?php if(!empty($each_inq->mobile_number)){ echo $each_inq->mobile_number;}?></td>
                          <td>
                            <div class="dropdown">
                                <select name="save_status<?php echo $i; ?>" aaa="<?php echo $each_inq->id; ?>" jsonstr="{{$each_inq->json_str}}" id="save_status<?php echo $i; ?>" class="form-control">
                                   <?php
                                    //if ($notificationData1['folloupdate_status'] == '') { 
                                      //  echo '<option value="">Select</option>';
                                    //} else { ?>
                                        <!-- </option> -->
                                    <?php //} 
                                    // if($notificationData1['folloupdate_status'] == "Finally Closed"){ }
                                      //else { ?>
                                    <?php if(!empty($notificationData1['folloupdate_status'])){
                                    ?>    
                                    <option value="Form Taken"><?php if(!empty($notificationData1['folloupdate_status'])){ echo $notificationData1['folloupdate_status']; }?></option> 
                                    <option value="Form Taken">Form Taken</option> 
                                    <option value="Finally Closed">Finally Closed</option>
                                    <option value="Cancel">Cancel</option>
                                    <option value="Follows">Pending</option>
                                    
                                  <?php }else{ ?>
                                    <option value="Follows">Pending</option>
                                     <option value="Form Taken">Form Taken</option> 
                                    <option value="Finally Closed">Finally Closed</option>
                                    <option value="Cancel">Cancel</option>
                                    <?php } ?>

                                </select>
                            </div>
                          </td>

                          <td>
                            <div class="dropdown">
                              <a class="btn btn-primary btn-block" href="{{ route('follow.edit',$each_inq->id) }}">Save Date</a>
                            </div>
                          </td>

                        </tr>
                        <?php $i++; $notificationData='';?>
                        @endforeach
                         <input type="hidden" id="select_count" name="select_count" value="<?php echo $i-1; ?>">
                        @else
                        <tr><td colspan="9" class="text-center"><span class="fontcolor-error">There Are No Records Available</span></td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>S No.</th>
                          <th>Enquiry No.</th>
                          <th>Form No.</th>
                          <!-- <th>Inter Dt.</th> -->
                          <th>Follow up date</th>
                          <th>Class Name</th>                                               
                          <th>Student Name</th>
                          <th>Session Name</th>                          
                          <th>Mobile Number</th>
                          <th>Status</th>
                          <th>Action</th>
                        </tr>
                      </tfoot>
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
            url: 'save-followup-status',
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