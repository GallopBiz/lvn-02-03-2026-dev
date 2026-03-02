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
    width: 300;

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

.uperletter{
  text-transform: capitalize;
}

</style>
<meta name="csrf-token" content="{{ csrf_token() }}" />
        <div class="main-content pt-4">
          <div class="breadcrumb">
            <h1 class="me-2">Selection Process</h1>
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
                    <form class="" novalidate="novalidate" method="post" action="{{url('filter-student-registration')}}">
                        @csrf
                        <div class="row">
                           <div class="col-md-2 form-group mb-3">
                              <label for="firstName1">Session :</label>
                              <input type="text" readonly id="session_name" class="form-control" value="" name="session_name">
                              <!-- <select id="session_name" class="form-control" name="session_name" autocomplete="shipping address-level1" required>
                                    <option <?php //if(!empty($jsonArr['session_name'])){  if($jsonArr['session_name'] == "2023-2024"){ echo "selected";} }?>  value="2023-2024" selected>2023-2024</option>
                                    <option <?php //if(!empty($jsonArr['session_name'])){  if($jsonArr['session_name'] == "2022-2023"){ echo "selected";} }?>  value="2022-2023" >2022-2023</option>
                                    <option <?php //if(!empty($jsonArr['session_name'])){ if($jsonArr['session_name'] == "2021-2022"){ echo "selected";}}?>  value="2021-2022" >2021-2022</option>
                                    <option <?php //if(!empty($jsonArr['session_name'])){ if($jsonArr['session_name'] == "2020-2021"){ echo "selected";} }?>  value="2020-2021" >2020-2021</option>
                              </select> -->
                           </div>
                           <div class="col-md-2 form-group mb-3">
                              <label for="studentname">Form Number</label>
                              <input name="form_number" class="form-control" id="form_number" placeholder="Form Number" value="<?php if(!empty($jsonArr['form_number'])){ echo $jsonArr['form_number']; }?>" />
                           </div>
                           <div class="col-md-2 form-group mb-3">
                              <label for="studentname">Student Name</label>
                              <input name="student_name" 
                                 class="form-control uperletter"
                                 id="studentname"
                                 placeholder="Enter Student Name" value="<?php if(!empty($jsonArr['studentname'])){  echo $jsonArr['studentname']; } ?>" 
                                 />
                           </div>
                           <div class="col-md-2 form-group mb-3">
                              <label for="firstName1">Status :</label>
                              <select id="save_status" class="form-control" name="save_status" autocomplete="shipping address-level1" required>
                              <?php $selected = ""; ?> 
                                    @if(!empty($jsonArr))
                                          <?php $selected = $jsonArr['save_status']; ?>
                                      @else       
                                      @endif                     
                                      <option value="" <?php echo ($selected == "") ? 'selected' : ''; ?> > -- Please select -- </option>
                                      <option value="Form Submitted" <?php echo ($selected  == "Form Submitted") ? 'selected' : ''; ?> >Form Submitted</option>
                                      <option value="Form Selected" <?php echo ($selected  == "Form Selected") ? 'selected' : ''; ?> >Form Selected</option>
                                      <option value="Rejected (Interview)" <?php echo ($selected  == "Rejected (Interview)") ? 'selected' : ''; ?> >Rejected (Interview)</option>
                                      <option value="Admission On Hold" <?php echo ($selected  == "Admission On Hold") ? 'selected' : ''; ?> >Admission On Hold</option>
                                      <option value="Registered" <?php echo ($selected  == "Registered") ? 'selected' : ''; ?> >Registered</option> 
                              </select>
                           </div>

                            


                           <div class="col-md-2 form-group mb-3">                          <label for="lastName1">Class Name </label>
                          <?php //print_r($classname); ?>
                          <select id="classname" class="form-control" name="classname" autocomplete="" required>
                                    <option value="" disabled selected>Please select</option>
                                    @if(!empty($datas))
                                    <?php //print_r($datas); ?>
                                      @foreach($datas as $each)
                                        @if(!empty($classname) && $each->class_name == $classname)
                                        <option value="{{$each->class_name}}" selected>{{$each->class_name}}</option>
                                        @else
                                        <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                                        @endif
                                      @endforeach
                                    @endif
                                 </select>
                              </div>                              
                          

                           {{-- <div class="col-md-2 form-group mb-3">
                              <label for="firstName1">Class :</label>
                              <select id="class" class="form-control" name="class" autocomplete="shipping address-level1" required>
                                    <option value=""> -- Select -- </option>
                                     @foreach(config('global.class_name') as $each)
                                    <option <?php if(!empty($jsonArr['class'])){ if($jsonArr['class'] == $each){ echo "selected";}}?> value="{{$each}}">{{$each}}</option>
                                    @endforeach
                              </select>
                           </div>  --}}

                           <div class="col-md-2 form-group mb-3">
                              <label for="firstName1">Gender</label>
                              <select id="gender" class="form-control" name="gender" autocomplete="shipping address-level1" required>
                                 <option value="" disabled selected>Please select</option>
                                 @foreach(config('global.gender') as $each)
                                 <option <?php if(!empty($jsonArr['gender'])){  if($jsonArr['gender'] == $each){ echo "selected";}}?> value="{{$each}}">{{$each}}</option>
                                 @endforeach
                              </select>
                           </div>
                           
                           <!-- <div class="col-md-8 form-group mb-3">
                              
                           </div> -->
                            <div class="col-md-4">
                                <button class="btn btn-primary">Search</button>
                                {{-- <a class="btn btn-primary" href="{{url('selection-process')}}">Clear</a> --}}
                                
                                <input type="reset" class="btn btn-danger text text-white" value="Reset">
                            </div>
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
                  <div class="table-responsive">
                  <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="student_id_select_p">
                      <input type="hidden" name="column_names[]" value="pick_shedule_name">
                      <input type="hidden" name="column_names[]" value="pick_up_routes">
                      <input type="hidden" name="column_names[]" value="pickup_area_name">
                      <input type="hidden" name="column_names[]" value="pickup_bus_stop_names">
                      <input type="hidden" name="column_names[]" value="pickup_bus_no">
                      <input type="hidden" name="column_names[]" value="drop_shedule_name">
                      <input type="hidden" name="column_names[]" value="drop_up_route">
                      <input type="hidden" name="column_names[]" value="drop_area_name">
                      <input type="hidden" name="column_names[]" value="drop_bus_stop_name">
                      <input type="hidden" name="table_name" value="teacherbusassign">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div>
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>Sr.</th>
                            <th>Form Date</th>
                            <th>Form No.</th>
                            <th>Class Name</th>
                            <th>Session Name</th>
                            <th>Student Name</th>
                            <th>Father Name</th>
                            <th>Mobile Number</th>
                            <th>Mother Name</th>
                            <!-- <th>DOB</th> -->
                            <th style="width:10%">Save Status</th>
                            <th>Updated by</th>
                            <!--<th>Address</th> -->
                        </tr>
                      </thead>
                      <tbody>
                        @if(sizeof($all_inquiry))
                        <?php $i=1; ?>
                        @foreach($all_inquiry as $each_inq)
                        <tr>
                          <?php 
                            $arr = json_decode($each_inq->json_str,1);
                          ?>  
                          <td><?php echo $i; ?></td>

                          <td><?php echo (!empty($each_inq->created_at)) ? date('d-m-Y', strtotime($each_inq->created_at )) : '' ; ?></td>
                          <td><?php echo (!empty($each_inq->form_number)) ? $each_inq->form_number : '' ; ?></td>
                          <td><?php echo (!empty($each_inq->student_name)) ? $each_inq->class_name : '' ; ?></td>
                          <td><?php echo (!empty($each_inq->session_name)) ? $each_inq->session_name : '' ; ?></td>
                          <td><?php echo (!empty($arr['studentname_prefix'])) ? $arr['studentname_prefix'].' ' : '' ; ?><?php echo (!empty($each_inq->student_name)) ? ucwords($each_inq->student_name) : '' ; ?></td>
                          <td><?php echo (!empty($arr['fathername_prefix'])) ? $arr['fathername_prefix'].' ' : '' ; ?><?php echo (!empty($arr['fathername'])) ? ucwords($arr['fathername']) : '' ; ?></td>
                          <td><?php echo (!empty($each_inq->mobile_number)) ? $each_inq->mobile_number : '' ; ?></td>
                          <td><?php echo (!empty($arr['mothername_prefix'])) ? ucwords($arr['mothername_prefix'].'
                          ') : '' ; ?><?php echo (!empty($arr['mothername'])) ? ucwords($arr['mothername']) : '' ; ?></td>
                          
                          <td>
                            <div class="dropdown">
                                <select class="form-control" name="save_status<?php echo $i; ?>" aaa="<?php echo $each_inq->id; ?>" id="save_status<?php echo $i; ?>">
                                <?php
                                 if ((empty($each_inq->save_status))) { 
                                         echo '<option value="Pending">Pending</option>';
                                     }
                                     // else { ?>
                                    //   <!--   <option value="<?php //echo (!empty($each_inq->save_status)) ? $each_inq->save_status : '' ; ?>" selected="selected">
                                    //         <?php //echo (!empty($each_inq->save_status)) ? $each_inq->save_status : '' ; ?>
                                    //     </option> -->
                                    <?php //} ?>
                                    <option value="Form Submitted" <?php if(!empty($each_inq->save_status)){ if($each_inq->save_status == "Form Submitted"){ echo 'selected';} } ?>>Form Submitted</option>
                                    <option value="Form Selected" <?php if(!empty($each_inq->save_status)){ if($each_inq->save_status == "Form Selected"){ echo 'selected';} } ?>>Form Selected</option>
                                    <option value="Rejected (Interview)" <?php if(!empty($each_inq->save_status)){ if($each_inq->save_status == "Rejected (Interview)"){ echo 'selected';} } ?>>Rejected (Interview)</option>
                                    <option value="Admission On Hold" <?php if(!empty($each_inq->save_status)){ if($each_inq->save_status == "Admission On Hold"){ echo 'selected';} } ?>>Admission On Hold</option>
                                    <!-- <option value="Offer Rejected">Offer Rejected</option>
                                    <option value="Not Reported">Not Reported</option> -->
                                    <option value="Registered" <?php if(!empty($each_inq->save_status)){ if($each_inq->save_status == "Registered"){ echo 'selected';} } ?>>Registered</option>
                                   <!--  <option value="Interaction Done">Interaction Done</option>
                                    <option value="Missed the Deadline">Missed the Deadline</option> -->
                                </select>
                            </div>
                          </td>
                          <td>Admin</td>
                         <!--  <td><?php //echo (!empty($each_inq->created_at)) ? date('d-m-Y', strtotime($each_inq->created_at )) : '' ; ?></td>
                          <td><?php //echo (!empty($arr['address'])) ? $arr['address'] : '' ; ?></td> -->                    
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                        <input type="hidden" id="select_count" name="select_count" value="<?php echo $i-1; ?>">

                        @else
                       <tr><td colspan="9" class="text-center"><span class="fontcolor-error">There Are No Records Available</span></td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>Sr.</th>
                          <th>Form Date</th>
                          <th>Form No.</th>
                          <th>Class Name</th>
                          <th>Session Name</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Mobile Number</th>
                          <th>Mother Number</th>
                          <!-- <th>DOB</th> -->
                          <th>Save Status</th>
                          <th>Updated by</th>
                          <!-- <th>Date</th>
                          <th>Address</th> -->
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- end of main-content -->
          <!-- Modal dialog -->



        </div>
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
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
                 <script type="text/javascript">$.noConflict();</script> 

        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <script>
            $.noConflict();
            jQuery(document).ready(function($){
              setTimeout(function() {
                var year = $("#year").val()
                $("#session_name").val(year);
              }, 1000);
                var availableTags2 = [
                    <?php foreach($all_inquiry as $each2){ 
                         // $inq_str_data = json_decode($each2->json_str, true);  ?>
                         "<?php if(!empty($each2->student_name)){ echo $each2->student_name; }?>",
                   <?php } ?>
                    
                ];
                $( "#studentname" ).autocomplete({
                    source: availableTags2
                });
            });
        </script> 
@endsection 
