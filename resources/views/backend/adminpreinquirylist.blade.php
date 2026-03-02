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

.uperletter{
  text-transform: capitalize;
} 
</style>
<div class="main-content pt-4"> 
          <div class="breadcrumb">     
            <h1 class="me-2">Pre Enquiry</h1>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
                  <div class="form_section1_div">  
                    <form class="" novalidate="novalidate" method="post" action="{{url('filter-preenquiry')}}">
                        @csrf
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



                           {{-- <div class="col-md-2 form-group mb-3">
                              <label for="firstName1">Class :</label>
                              <select id="class" class="form-control" name="class" autocomplete="shipping address-level1" required>
                                    <option value=""> -- Select -- </option>
                                     @foreach(config('global.class_name') as $each)
                              <option value="{{$each}}">{{$each}}</option>
                              @endforeach
                              </select>
                           </div> --}}


                            <div class="col-md-2 form-group mb-3">
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
                          
                           
                            <div class="col-md-3">
                                <button class="btn btn-primary">Search</button>
                                <input type="reset" class="btn btn-danger text text-white" value="Reset">
                                {{-- <a class="btn btn-primary" href="{{url('adminenquirylist')}}">Clear</a> --}}
                            </div><div class="separator-breadcrumb"></div>
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
                      <input type="hidden" name="table_name" value="inquiry_registration">
                      <!-- <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button> -->
                  </form></div>


                  </h4>
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                         <!--  <th>Application For</th> -->
                          <th>S No.</th>
                          <th>Enquiry No.</th>
                          <th>Class Name</th>                                               
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Session Name</th>
                          <th>Mobile Number</th>
                          <th>Enquiry Date</th>
                          <th>Year</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(sizeof($all_inquiry))
                                                <?php $i=1; ?>
                        @foreach($all_inquiry as $each_inq)
                         <?php $notificationData1 = json_decode($each_inq->json_str, true);?>
                        <tr>
                         <!--  <td>{{$each_inq->application_for}}</td> -->
                          <td><?php echo $i; ?></td>
                          <td>{{$each_inq->form_number}}</td>
                          <td>{{$each_inq->class_name}}</td>
                          <td><?php if(!empty($notificationData1['studentname_prefix'])){ echo $notificationData1['studentname_prefix'].' '; } echo ucwords($each_inq->student_name);?></td>
                          <td><?php if(!empty($notificationData1['fathername_prefix'])){ echo $notificationData1['fathername_prefix'].' '; } if(!empty($notificationData1['fathername'])){ echo ucwords($notificationData1['fathername']);}?> </td>
                          <td>{{$each_inq->session_name}}</td>
                          <td>{{$each_inq->mobile_number}}</td>
                          
                          <td>{{ date('d-m-Y', strtotime($each_inq->created_at)) }}</td>
                          <td>
                            @if(!empty($each_inq->next_year))
                              <?php 
                              $currentYear = date('Y');
                              $nextYear = date('Y') + 1;
                              $currentSchoolYear = $each_inq->session_name;
                              
                                $originalYear = $currentSchoolYear;

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
                            <?php if(!empty($_COOKIE['selectedYear'])){ ?>
                              {{ $_COOKIE['selectedYear'] }}
                            <?php } ?>
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
                              <!-- <div class="dropdown-menu btn btn-success" aria-labelledby="dropdownMenuButton"> <a class="dropdown-item" href="{{ route('enquiryeditlist',$each_inq->id) }}">Edit</a>
                              </div>  -->
                            </div>
                          </td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                        @else
                       <tr><td colspan="9" class="text-center"><span class="fontcolor-error">There Are No Records Available</span></td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <!-- <th>Application For</th> -->
                          <th>S No.</th>
                          <th>Enquiry No.</th>
                          <th>Class Name</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Session Name</th>
                          <th>Mobile Number</th>
                          <th>Enquiry Date</th>
                          <th>Year</th>
                          <th>Status</th>
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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<script>
  $(document).ready( function() {
    setTimeout(function() {
      var year = $("#year").val()
      $("#session_name").val(year);
    }, 1000);
  });
</script>

@endsection 
