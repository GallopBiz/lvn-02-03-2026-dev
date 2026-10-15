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

.analytics-inline-row {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    margin-left: -5px;
    margin-right: -5px;
    padding-bottom: 6px;
    scrollbar-width: thin;
}
.analytics-inline-col {
    flex: 1 1 0;
    min-width: 135px;
    padding-left: 5px;
    padding-right: 5px;
}
.analytics-card {
    transition: all 0.2s ease-in-out;
    border-radius: 8px;
    border: 1px solid rgba(0,0,0,0.08);
    background: #fff;
    cursor: pointer;
    height: 100%;
}
.analytics-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.1) !important;
}
.analytics-card.border-accent-primary {
    border-left: 4px solid #663399 !important;
}
.analytics-card.border-accent-success {
    border-left: 4px solid #4caf50 !important;
}
.analytics-card.border-accent-warning {
    border-left: 4px solid #ff9800 !important;
}
.analytics-card.border-accent-info {
    border-left: 4px solid #00bcd4 !important;
}
.analytics-card.border-accent-secondary {
    border-left: 4px solid #6c757d !important;
}
.analytics-card.border-accent-danger {
    border-left: 4px solid #f44336 !important;
}
.analytics-card.border-accent-dark {
    border-left: 4px solid #343a40 !important;
}
.analytics-card.active-filter {
    background: #fbf9ff;
    box-shadow: 0 4px 14px rgba(102, 51, 153, 0.12) !important;
    border-color: rgba(102, 51, 153, 0.25);
}
.analytics-card .card-body {
    user-select: none;
    padding: 0.85rem 0.95rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.analytics-card .analytics-title-wrap {
    min-height: 32px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 4px;
}
.analytics-card h6 {
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1.2;
    margin-bottom: 0;
}
.analytics-card h3 {
    font-size: 1.55rem;
    font-weight: 700;
    line-height: 1.1;
    margin-top: 6px;
    margin-bottom: 0;
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

          <!-- Analytics Section -->
          @php
              if (!isset($analytics)) {
                  $analytics = [
                      'total' => count($all_inquiry),
                      'form_selected' => 0,
                      'pending' => 0,
                      'form_submitted' => 0,
                      'admission_on_hold' => 0,
                      'rejected' => 0,
                      'registered' => 0,
                  ];
                  foreach ($all_inquiry as $item) {
                      $st = trim($item->save_status ?? '');
                      if (empty($st) || strcasecmp($st, 'Pending') === 0) {
                          $analytics['pending']++;
                      } elseif (strcasecmp($st, 'Form Selected') === 0) {
                          $analytics['form_selected']++;
                      } elseif (strcasecmp($st, 'Form Submitted') === 0) {
                          $analytics['form_submitted']++;
                      } elseif (strcasecmp($st, 'Admission On Hold') === 0) {
                          $analytics['admission_on_hold']++;
                      } elseif (strcasecmp($st, 'Rejected (Interview)') === 0 || strcasecmp($st, 'Rejected') === 0) {
                          $analytics['rejected']++;
                      } elseif (strcasecmp($st, 'Registered') === 0) {
                          $analytics['registered']++;
                      }
                  }
              }
              $currentStatusFilter = $jsonArr['save_status'] ?? null;
          @endphp

          <div class="analytics-inline-row mb-4">
              <div class="analytics-inline-col mb-2">
                  <div class="card analytics-card shadow-sm border-accent-primary {{ ($currentStatusFilter === '' || $currentStatusFilter === null) && isset($jsonArr) ? 'active-filter' : '' }}" data-status="" role="button" title="Click to show all entries">
                      <div class="card-body">
                          <div class="analytics-title-wrap">
                              <h6 class="text-muted">Total Entries</h6>
                              @if(($currentStatusFilter === '' || $currentStatusFilter === null) && isset($jsonArr))
                                  <span class="badge bg-primary text-white" style="font-size: 0.65rem; padding: 2px 4px;">Active</span>
                              @endif
                          </div>
                          <h3 class="text-dark">{{ number_format($analytics['total']) }}</h3>
                      </div>
                  </div>
              </div>
              <div class="analytics-inline-col mb-2">
                  <div class="card analytics-card shadow-sm border-accent-success {{ ($currentStatusFilter === 'Form Selected') ? 'active-filter' : '' }}" data-status="Form Selected" role="button" title="Click to filter by Form Selected">
                      <div class="card-body">
                          <div class="analytics-title-wrap">
                              <h6 class="text-muted">Form Selected</h6>
                              @if($currentStatusFilter === 'Form Selected')
                                  <span class="badge bg-success text-white" style="font-size: 0.65rem; padding: 2px 4px;">Active</span>
                              @endif
                          </div>
                          <h3 class="text-success">{{ number_format($analytics['form_selected']) }}</h3>
                      </div>
                  </div>
              </div>
              <div class="analytics-inline-col mb-2">
                  <div class="card analytics-card shadow-sm border-accent-warning {{ ($currentStatusFilter === 'Pending') ? 'active-filter' : '' }}" data-status="Pending" role="button" title="Click to filter by Pending">
                      <div class="card-body">
                          <div class="analytics-title-wrap">
                              <h6 class="text-muted">Pending</h6>
                              @if($currentStatusFilter === 'Pending')
                                  <span class="badge bg-warning text-dark" style="font-size: 0.65rem; padding: 2px 4px;">Active</span>
                              @endif
                          </div>
                          <h3 class="text-warning">{{ number_format($analytics['pending']) }}</h3>
                      </div>
                  </div>
              </div>
              <div class="analytics-inline-col mb-2">
                  <div class="card analytics-card shadow-sm border-accent-info {{ ($currentStatusFilter === 'Form Submitted') ? 'active-filter' : '' }}" data-status="Form Submitted" role="button" title="Click to filter by Form Submitted">
                      <div class="card-body">
                          <div class="analytics-title-wrap">
                              <h6 class="text-muted">Form Submitted</h6>
                              @if($currentStatusFilter === 'Form Submitted')
                                  <span class="badge bg-info text-white" style="font-size: 0.65rem; padding: 2px 4px;">Active</span>
                              @endif
                          </div>
                          <h3 class="text-info">{{ number_format($analytics['form_submitted']) }}</h3>
                      </div>
                  </div>
              </div>
              <div class="analytics-inline-col mb-2">
                  <div class="card analytics-card shadow-sm border-accent-secondary {{ ($currentStatusFilter === 'Admission On Hold') ? 'active-filter' : '' }}" data-status="Admission On Hold" role="button" title="Click to filter by Admission On Hold">
                      <div class="card-body">
                          <div class="analytics-title-wrap">
                              <h6 class="text-muted">Admission On Hold</h6>
                              @if($currentStatusFilter === 'Admission On Hold')
                                  <span class="badge bg-secondary text-white" style="font-size: 0.65rem; padding: 2px 4px;">Active</span>
                              @endif
                          </div>
                          <h3 class="text-secondary">{{ number_format($analytics['admission_on_hold']) }}</h3>
                      </div>
                  </div>
              </div>
              <div class="analytics-inline-col mb-2">
                  <div class="card analytics-card shadow-sm border-accent-danger {{ ($currentStatusFilter === 'Rejected (Interview)') ? 'active-filter' : '' }}" data-status="Rejected (Interview)" role="button" title="Click to filter by Rejected (Interview)">
                      <div class="card-body">
                          <div class="analytics-title-wrap">
                              <h6 class="text-muted">Rejected (Interview)</h6>
                              @if($currentStatusFilter === 'Rejected (Interview)')
                                  <span class="badge bg-danger text-white" style="font-size: 0.65rem; padding: 2px 4px;">Active</span>
                              @endif
                          </div>
                          <h3 class="text-danger">{{ number_format($analytics['rejected']) }}</h3>
                      </div>
                  </div>
              </div>
              <div class="analytics-inline-col mb-2">
                  <div class="card analytics-card shadow-sm border-accent-dark {{ ($currentStatusFilter === 'Registered') ? 'active-filter' : '' }}" data-status="Registered" role="button" title="Click to filter by Registered">
                      <div class="card-body">
                          <div class="analytics-title-wrap">
                              <h6 class="text-muted">Registered</h6>
                              @if($currentStatusFilter === 'Registered')
                                  <span class="badge bg-dark text-white" style="font-size: 0.65rem; padding: 2px 4px;">Active</span>
                              @endif
                          </div>
                          <h3 class="text-dark">{{ number_format($analytics['registered']) }}</h3>
                      </div>
                  </div>
              </div>
          </div>
          <!-- End Analytics Section -->

          <div class="row">
            <div class="col-md-12 mb-4">
                <div class="form_section1_div">
                    <form class="" id="selectionFilterForm" novalidate="novalidate" method="post" action="{{url('filter-student-registration')}}">
                        @csrf
                        <div class="row">
                           <div class="col-md-2 form-group mb-3">
                              <label for="firstName1">Session :</label>
                              <input type="text" readonly id="session_name" class="form-control" value="{{ $jsonArr['session_name'] ?? '' }}" name="session_name">
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
                                      <option value="Pending" <?php echo ($selected  == "Pending") ? 'selected' : ''; ?> >Pending</option>
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
                                
                                <input type="reset" class="btn btn-danger text text-white" value="Reset" onclick="window.location.href='{{url('selection-process')}}'; return false;">
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
                  <div class="card-title mb-3 text-end"><form method="POST" id="export_selection_process_form" action="{{ route('export_selection_process.csv') }}">
                      @csrf
                      <input type="hidden" name="form_number" value="{{ $jsonArr['form_number'] ?? '' }}">
                      <input type="hidden" name="student_name" value="{{ $jsonArr['studentname'] ?? ($jsonArr['student_name'] ?? '') }}">
                      <input type="hidden" name="save_status" value="{{ $jsonArr['save_status'] ?? '' }}">
                      <input type="hidden" name="class_name" value="{{ $jsonArr['class_name'] ?? ($classname ?? '') }}">
                      <input type="hidden" name="gender" value="{{ $jsonArr['gender'] ?? '' }}">
                      <input type="hidden" name="session_name" value="{{ $jsonArr['session_name'] ?? '' }}">
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
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
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

                $('#export_selection_process_form').on('submit', function() {
                    var sess = $('#session_name').val();
                    var formNo = $('#form_number').val();
                    var stuName = $('#studentname').val();
                    var saveStat = $('#save_status').val();
                    var clsName = $('#classname').val();
                    var gen = $('#gender').val();

                    if (sess) $(this).find('input[name="session_name"]').val(sess);
                    if (formNo) $(this).find('input[name="form_number"]').val(formNo);
                    if (stuName) $(this).find('input[name="student_name"]').val(stuName);
                    if (saveStat) $(this).find('input[name="save_status"]').val(saveStat);
                    if (clsName) $(this).find('input[name="class_name"]').val(clsName);
                    if (gen) $(this).find('input[name="gender"]').val(gen);
                });

                $(document).on('click', '.analytics-card', function() {
                    var status = $(this).data('status');
                    $('#save_status').val(status);
                    $('#selectionFilterForm').submit();
                });
            });
        </script> 
@endsection 
