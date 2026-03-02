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

.uperletter{
  text-transform: capitalize;
}

</style>
<div class="main-content pt-4"> 
          <div class="breadcrumb">     
            <h1 class="me-2">Student All Enquiry</h1>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
                  <div class="form_section1_div">  
                    <form class="" novalidate="novalidate" method="post" action="{{url('filter-allenquiry')}}">
                        @csrf
                        <div class="row">
                           <div class="col-md-3 form-group mb-3">
                              <label for="firstName1">Session :</label>
                              <input type="text" readonly id="session_name" class="form-control" value="" name="session_name">
                              <!-- <select id="session_name" class="form-control" name="session_name" autocomplete="shipping address-level1" required>
                                    <option value="2023-2024" selected>2023-2024</option>
                                    <option value="2022-2023" >2022-2023</option>
                                    <option value="2021-2022" >2021-2022</option>
                                    <option value="2020-2021" >2020-2021</option>
                              </select> -->
                              
                           </div>
                           <div class="col-md-3 form-group mb-3">
                              <label for="studentname">Form Number</label>
                              <input name="form_number" class="form-control" id="form_number" placeholder="Form Number" />
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

{{--                            
                           <div class="col-md-3 form-group mb-3">
                              <label for="firstName1">Class :</label>
                              <select id="class" class="form-control" name="class" autocomplete="shipping address-level1" required>
                                    <option value=""> -- Select -- </option>
                                     @foreach(config('global.class_name') as $each)
                              <option value="{{$each}}">{{$each}}</option>
                              @endforeach
                              </select>
                           </div> --}}



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

                          <!--  <div class="col-md-2 form-group mb-3">   
                                  <lable>Start Date</lable>
                                    <input type="date"  id="picker2" name="fromdate" class="form-control" placeholder="dd-mm-yyyy" >
                                    <span class="fromdate_msg validation_err"></span>
                               
                            </div>
                            <div class="col-md-2 form-group mb-3">
                                  <lable>End Date</lable>
                                      <input type="date"  id="picker2" name="todate" class="form-control"  placeholder="dd-mm-yyyy" >
                                      <span class="todate_msg validation_err"></span>
                               
                            </div> -->
                          
                           
                            
                            <!-- <div class="col-md-1">
                                <button class="btn btn-warning">Export</button>
                            </div> -->
                        </div>
                        <div class="row">
                        <div class="col-md-3">
                                <button class="btn btn-primary">Search</button>
                                 {{-- <a class="btn btn-primary" href="{{url('adminenquirylist')}}">Clear</a> --}}

                                 
                                 <input type="reset" class="btn btn-danger text text-white" value="Reset">
                            </div>
                            <div class="separator-breadcrumb"></div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="separator-breadcrumb border-top"></div>
                      <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  <h4 class="card-title mb-3 text-end">
                    <a href="{{url('admin-enquiryform')}}"><button class="btn btn-primary" type="button">Create Enquiry</button></a>
                    <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="application_for">
                      <input type="hidden" name="column_names[]" value="form_number">
                      <input type="hidden" name="column_names[]" value="date_of_birth">
                      <input type="hidden" name="column_names[]" value="class_name">
                      <input type="hidden" name="column_names[]" value="student_name">
                      <input type="hidden" name="column_names[]" value="gender">
                      <input type="hidden" name="column_names[]" value="session_name">
                      <input type="hidden" name="column_names[]" value="json_str">
                      <input type="hidden" name="column_names[]" value="phone_number">
                      <input type="hidden" name="column_names[]" value="mobile_number">
                      <input type="hidden" name="column_names[]" value="inq_mode">
                      <input type="hidden" name="column_names[]" value="status">
                      <input type="hidden" name="column_names[]" value="save_status">
                      <input type="hidden" name="start_date" id="start_date" value="">
                      <input type="hidden" name="end_date" id="end_date" value="">
                      <input type="hidden" name="table_name" value="inquiry_registration">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div>


                  </h4>
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                         <!--  <th>Application For</th> -->
                          <th>S No.</th>
                          <th>Form No.</th>
                          <th>Class Name</th>                                               
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Mother Name</th>
                          <th>Session Name</th>
                          <th>Mobile Number</th>
                          <th>Enquiry Date</th>
                          <th>Year</th>
                          <th>Status</th>
                          <th>Updated by</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(!empty($all_inquiry))
                                                <?php $i=1; ?>
                        @foreach($all_inquiry as $each_inq)
                         <?php $notificationData1 = json_decode($each_inq->json_str, true);?>
                        <tr>
                         <!--  <td>{{$each_inq->application_for}}</td> -->
                          <td><?php echo $i; ?></td>
                          <td>{{$each_inq->form_number}}</td> 
                          <td>{{$each_inq->class_name}}</td>
                          <td><?php if(!empty($notificationData1['studentname_prefix'])){ echo $notificationData1['studentname_prefix'].' '; }?><?php if(!empty($each_inq->student_name)){ echo ucwords($each_inq->student_name).' '; }?></td>
                          <td><?php if(!empty($notificationData1['fathername_prefix'])){ echo $notificationData1['fathername_prefix'].' '; } if(!empty($notificationData1['fathername'])){ echo ucwords($notificationData1['fathername']);}?> </td>
                          <td><?php if(!empty($notificationData1['mothername_prefix'])){ echo $notificationData1['mothername_prefix'].' '; } if(!empty($notificationData1['mothername'])){ echo ucwords($notificationData1['mothername']);}?> </td>
                          <td><?php if(!empty($each_inq->session_name)){ echo $each_inq->session_name;}?></td>
                          <td><?php if(!empty($each_inq->mobile_number)){ echo $each_inq->mobile_number; }?></td>
                          <!-- <td>@if($each_inq->inq_mode=='off') offline @elseif($each_inq->inq_mode=='on') Online @endif</td> -->
                          <td>
							<?php 
								if (!empty($notificationData1['enquirydate'])) {
									echo date('d-m-Y', strtotime($notificationData1['enquirydate']));
								} else {
									echo '-';
								}
							?>
						</td>

                          <td>
                            @if(empty($each_inq->session_name))
                              <?php 
                                $originalYear = $each_inq->session_name;

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
                              {{ $each_inq->session_name }}
                            @endif
                          </td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-primary dropdown-toggle" id="dropdownMenuButton" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Actions
                              </button>
                              <div class="dropdown-menu btn btn-danger " aria-labelledby="dropdownMenuButton"> <a class="dropdown-item " href="{{ route('enquiryviewlist',$each_inq->id) }}">View</a>
                                <a class="dropdown-item" href="{{ route('enquiryeditlist',$each_inq->id) }}">Edit</a>
                                <a class="dropdown-item" target="new" href="{{ route('enquiryrecipt',$each_inq->id) }}">Receipt</a>
                              </div> 
                              <!-- <div class="dropdown-menu btn btn-success" aria-labelledby="dropdownMenuButton"> <a class="dropdown-item" href="{{ route('enquiryeditlist',$each_inq->id) }}">Edit</a>
                              </div>  -->
                            </div>
                          </td>
                          <td>Admin</td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
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
                          <th>Enquiry Date</th>
                          <th>Year</th>
                          <th>Status</th>
                          <th>Updated by</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        
          <!-- end of main-content -->
        <!-- </div> -->
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
        <script>
          document.addEventListener('DOMContentLoaded', function () {
              // Get the date input elements by name
              var fromDateInput = document.querySelector('input[name="fromdate"]');
              var toDateInput = document.querySelector('input[name="todate"]');

              // Add change event listeners to the date inputs
              fromDateInput.addEventListener('change', function () {
                $("#start_date").val(fromDateInput.value);
                  // console.log('Start Date selected:', fromDateInput.value);
              });

              toDateInput.addEventListener('change', function () {
                $("#end_date").val(toDateInput.value);
                  // console.log('End Date selected:', toDateInput.value);
              });
          });
      </script>
@endsection 
