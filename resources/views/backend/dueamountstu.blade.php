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
            <h1 class="me-2">Search By Date</h1>
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
            <div class="col-md-12 mb-4">
                <div class="form_section1_div">
                    <form class="rg_form" novalidate="novalidate" method="post" action="{{url('filter-duestuamount')}}">
                        @csrf
                        <div class="row">
                        <div class="col-md-2 form-group mb-3">   
                          <lable>Date</lable>
                            <input type="date"  id="picker2" name="fromdate" class="form-control" placeholder="dd-mm-yyyy" required>
                            <span class="fromdate_msg validation_err"></span>
                        <!-- </div> -->
                        </div>
                        <div class="col-md-2 form-group mb-3">
                        <lable>Date</lable>
                            <input type="date"  id="picker2" name="todate" class="form-control"  placeholder="dd-mm-yyyy" required>
                            <span class="todate_msg validation_err"></span>
                        <!-- </div> -->
                        </div>
                        <div class="col-md-2 form-group mb-3">
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
                          <div class="col-md-2 form-group mb-3">
                              <label for="studentname">Student Name</label>
                              <input name="student_name" 
                                 class="form-control uperletter"
                                 id="studentname"
                                 placeholder="Enter Student Name"
                                 />
                           </div>
                           <div class="col-md-2 form-group mb-3">
                              <label for="studentname">Receipt No.</label>
                              <input name="receipt_number" 
                                 class="form-control uperletter"
                                 id="receipt_number"
                                 placeholder="Enter Receipt No."
                                 />
                           </div>                           
                           <div class="col-md-3">
                                  <br>
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
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                <div class="card-title mb-3 text-end"><a class="dropdown-item" href="{{url('dueamountstu-csv') }}">Export CSV</a></div>
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                          <th>S No.</th>
                          <th>Fees date</th>
                          <th>Due date</th>
                          <th>Student Name</th>
                          <th>Amount</th>                                               
                          <th>Mode</th>                                               
                          <th>Receipt No.</th> 
                          <th>scholar No</th>                                               
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(!empty($all_inquiry))
                              <?php $i=1; ?>
                        @foreach($all_inquiry as $each_inq)                              
                        <tr>
                          <td><?php echo $i; ?></td>
                          {{-- <td>{{$each_inq->fees_date}}</td> --}}
                          {{-- <td>{{$each_inq->due_date}}</td> --}}
                          <td>{{ date('d-m-Y', strtotime($each_inq->fees_date)) }}</td> 
                          <td>{{ date('d-m-Y', strtotime($each_inq->due_date)) }}</td> 
                          <td>{{$each_inq->student_name}}</td>
                          <td>{{$each_inq->totalnextyear}}</td>
                          <td>{{$each_inq->received_type}}</td>
                          <td>{{$each_inq->receipt_number}}</td>
                          <td>{{$each_inq->scholar_no}}</td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-primary dropdown-toggle" id="dropdownMenuButton" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Actions
                              </button>
                              <div class="dropdown-menu btn btn-danger " aria-labelledby="dropdownMenuButton">
                              <?php $a =$each_inq->scholar_no; ?>
                                <a class="dropdown-item" href="{{url('perstudueamount').'/'.$a}}">View</a> 
								<a class="dropdown-item" href="{{ url('admission-receipt/'.$each_inq->scholar_no) }}">Print Receipt</a> 	
                              </div>
                            </div>
                          </td>
                        </tr>
                        <?php $i++; $notificationData='';?>
                        @endforeach
                         <input type="hidden" id="select_count" name="select_count" value="<?php echo $i-1; ?>">
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>S No.</th>
                          <th>Fees date</th>
                          <th>Due date</th>
                          <th>Student Name</th>
                          <th>Amount</th>                                               
                          <th>Mode</th>                                               
                          <th>Receipt No.</th> 
                          <th>scholar No</th>                                               
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

    </script>
@endsection 