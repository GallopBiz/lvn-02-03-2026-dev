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
.ui-menu .ui-menu-item {
font-size: 0.813rem;
    width: 300px;

}
.ui-autocomplete {
    max-height: 150px;
    overflow-y: auto;
    overflow-x: hidden;

    padding-right: 20px;
}
 html .ui-autocomplete {
    height: 150px;
}
</style>
<meta name="csrf-token" content="{{ csrf_token() }}" />  
        <div class="main-content pt-4">
          <div class="breadcrumb">
            <h1 class="me-2">Student All Enquiry</h1>
          </div>
                    <div class="form_section1_div">
                    <form class="" novalidate="novalidate" method="post" action="{{url('filter-allenquiry')}}">
                        @csrf
                        <div class="row">
                            
                           <div class="col-md-3 form-group mb-3">
                              <label for="firstName1">Session :</label>
                              <select id="session_name" class="form-control" name="session_name" autocomplete="shipping address-level1" required>
									<option <?php if(!empty($jsonArr['session_name'])){ if($jsonArr['session_name'] == "2024-2025"){ echo "selected";} }?>  value="2024_2025" selected>2024-2025</option>
                                    <option <?php if(!empty($jsonArr['session_name'])){ if($jsonArr['session_name'] == "2023-2024"){ echo "selected";} }?>  value="2023-2024" selected>2023-2024</option>
                                    <option <?php if(!empty($jsonArr['session_name'])){ if($jsonArr['session_name'] == "2022-2023"){ echo "selected";} }?>  value="2022-2023" >2022-2023</option>
                                    <option <?php if(!empty($jsonArr['session_name'])){ if($jsonArr['session_name'] == "2021-2022"){ echo "selected";}}?>  value="2021-2022" >2021-2022</option>
                                    <option <?php if(!empty($jsonArr['session_name'])){ if($jsonArr['session_name'] == "2020-2021"){ echo "selected";}}?>  value="2020-2021" >2020-2021</option>
                              </select>
                           </div>
                           <div class="col-md-3 form-group mb-3">
                              <label for="studentname">Form Number</label>
                              <input name="form_number" class="form-control" id="form_number" placeholder="Form Number" value="<?php if(!empty($jsonArr['formno'])){ echo $jsonArr['formno'];}?>" />
                           </div>
                           
                           <div class="col-md-3 form-group mb-3">
                              <label for="firstName1">Class :</label>
                              <select id="class" class="form-control" name="class" autocomplete="shipping address-level1" >
                              @if(!empty($EnqSecdata[0]))
                                    <option value=""> -- Please select -- </option>
                                            @foreach($studentclasses as $studentclasses)
                                                @if($EnqSecdata[0]['class_name'] != $studentclasses->class_name)
                                                <option value="{{ $studentclasses->class_name }}">{{ $studentclasses->class_name }}
                                                </option>
                                                @else
                                                  <option selected value="{{$EnqSecdata[0]['class_name']}}">{{$EnqSecdata[0]['class_name']}}</option>

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

                            <div class="col-md-3 form-group mb-3">
                              <label for="studentname">Student Name</label>
                              <input name="student_name" 
                                 class="form-control"
                                 id="studentname"
                                 placeholder="Enter Student Name"
                                 value="<?php if(!empty($jsonArr['student_name'])){ echo ucwords($jsonArr['student_name']);}?>"  />
                           </div>

                            <div class="col-md-3">
                                <button class="btn btn-primary">Search</button>
                                <a class="btn btn-primary" href="{{url('adminenquirylist')}}">Clear</a>
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
                    <a href="{{url('adminenquirylist')}}"><button class="btn btn-primary" type="button">All Enquiry</button></a>
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
                          <th>Mother Name</th>
                          <th>Session Name</th>
                          <th>Mobile Number</th>
                          <th>scholar No</th>
                          <th>Enquiry Date</th>
                          <th>Status</th>
                          <th>Updated by</th>
                        </tr>
                      </thead>
                      <tbody>
                       
                        @if(sizeof($all_inquiry))
                         @if(!empty($all_inquiry))
                                                <?php $i=1; ?>
                        @foreach($all_inquiry as $each_inq)
                        <?php if(!empty($each_inq->json_str)){ $notificationData1 = json_decode($each_inq->json_str, true); }?>
                        <tr>
                          <td><?php echo $i; ?></td>
                          <td>{{$each_inq->form_number}}</td> 
                          <td>{{$each_inq->class_name}}</td>
                          <td><?php if(!empty($notificationData1['studentname_prefix'])){ echo $notificationData1['studentname_prefix'].' '; }?><?php if(!empty($each_inq->student_name)){ echo ucwords($each_inq->student_name).' '; }?></td>
                          <td><?php if(!empty($notificationData1['fathername_prefix'])){ echo $notificationData1['fathername_prefix'].' '; } if(!empty($notificationData1['fathername'])){ echo ucwords($notificationData1['fathername']);}?> </td>
                          <td><?php if(!empty($notificationData1['mothername_prefix'])){ echo $notificationData1['mothername_prefix'].' '; } if(!empty($notificationData1['mothername'])){ echo ucwords($notificationData1['mothername']);}?> </td>
                          <td><?php if(!empty($each_inq->session_name)){ echo $each_inq->session_name;}?></td>
                          <td><?php if(!empty($each_inq->mobile_number)){ echo $each_inq->mobile_number; }?></td>
                          <!-- <td>@if($each_inq->inq_mode=='off') offline @elseif($each_inq->inq_mode=='on') Online @endif</td> -->
                          <td><?php if(!empty($each_inq->scholar_no)){ echo $each_inq->scholar_no; }?></td>
                          <td><?php if(!empty($each_inq->created_at)){ echo date('d-m-Y', strtotime($each_inq->created_at));}?></td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-primary dropdown-toggle" id="dropdownMenuButton" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Actions
                              </button>
                               <div class="dropdown-menu btn btn-danger " aria-labelledby="dropdownMenuButton"> <a class="dropdown-item " href="{{ route('enquiryviewlist',$each_inq->id) }}">View</a>
                                <a class="dropdown-item" href="{{ route('enquiryeditlist',$each_inq->id) }}">Edit</a>
                                <a class="dropdown-item" target="new" href="{{ route('enquiryrecipt',$each_inq->id) }}">Recipt</a>
                              </div> 
                              <!-- <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"> <a class="dropdown-item" href="{{ route('enquiryeditlist',$each_inq->id) }}">Edit</a>
                              </div> -->
                            </div>
                          </td>
                          <td>Admin</td>
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
                           <th>Mother Name</th>
                          <th>Session Name</th>
                          <th>Mobile Number</th>
                         <!--  <th>Mode</th> -->
                          <th>Enquiry Date</th>
                          <th>Status</th>
                          <th>Updated by</th>
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
                        console.log(response.success);​
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
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
                 <script type="text/javascript">$.noConflict();</script> 

        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script>
            $.noConflict();
            jQuery(document).ready(function($){
          
                var availableTags2 = [
                    <?php foreach($all_inquiry2 as $each2){ 
                         // $inq_str_data = json_decode($each2->json_str, true);  ?>
                         "<?php if(!empty($each2->student_name)){ echo ucwords($each2->student_name); }?>",
                   <?php } ?>
                    
                ];
                $( "#studentname" ).autocomplete({
                    source: availableTags2
                });
            });
        </script> 
@endsection 
