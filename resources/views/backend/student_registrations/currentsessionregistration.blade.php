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
</style>
<div class="main-content pt-4">
          <div class="breadcrumb">
            <h1 class="me-2">All Registration</h1>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">

            <div class="col-md-12 mb-4">
              <div class="form_section1_div">
                <form method="post" action="{{url('student-registration-filter')}}">
                    @csrf
                    <div class="row">
                       <div class="col-md-2 form-group mb-3">
                          <label for="firstName1">Session :</label>
                          <input type="text" readonly id="session_name" class="form-control" value="" name="session_name">
                          <!-- <select id="session_name" class="form-control" name="session_name" autocomplete="shipping address-level1" required>                           
                              @foreach(config('global.session_name') as $each)
                              <option value="{{$each}}" <?php if(!empty($jsonArr['session_name'])){ if($jsonArr['session_name'] == $each){ echo "selected"; } } ?><?php if(empty($jsonArr['session_name'])){ if($each == "2023-2024"){ echo "selected"; } } ?>>{{$each}}</option>
                              @endforeach
                           </select> -->
                       </div>
                       <div class="col-md-2 form-group mb-3">
                          <label for="studentname">Form Number</label>
                          <input name="form_number" class="form-control" id="form_number" placeholder="Form Number" value="<?php if(!empty($jsonArr['form_number'])){ echo $jsonArr['form_number']; }?>" />
                       </div>
                       <div class="col-md-2 form-group mb-3">
                        <label for="serial_number">Serial number</label>
                        <input name="serial_number" class="form-control" id="form_number" placeholder="Serial Number" value="<?php if(!empty($jsonArr['serial_number'])){ echo $jsonArr['serial_number']; }?>" />
                      </div>

                      <div class="col-md-2 form-group mb-3">
                        <label for="firstName1">Class Name</label>
                        <select id="classname" class="form-control" name="classname" autocomplete="" required>
                           @if(!empty($jsonArr))
                            <option value=""> -- Please select -- </option>
                                    @foreach($classlist as $each)
                                        @if($jsonArr['class_name'] != $each->class_name)
                                        <option value="{{ $each->class_name }}">{{ $each ->class_name }}
                                        </option>
                                        @else
                                           <option selected value="{{$jsonArr['class_name']}}">{{$jsonArr['class_name']}}</option>

                                        @endif

                                     @endforeach
                        @else
                        @if (!empty($classlist))
                                <option value="" selected> -- Please select -- </option>
                                     @foreach($classlist as $studentclasses)
                                        <option value="{{ $studentclasses->class_name }}">{{ $studentclasses->class_name }}
                                        </option>
                                     @endforeach
                        @else
                            <option value="" selected> -- Please select -- </option>
                        @endif
                        @endif
                        </select>
                        <span class="classname_msg validation_err"></span>
                      </div>
                      
                       {{-- <div class="col-md-2 form-group mb-3">
                          <label for="firstName1">Class :</label>
                          <select id="class" class="form-control" name="class" autocomplete="shipping address-level1" >
                               <option value=""> -- Select -- </option>
                                     @foreach(config('global.class_name') as $each)
                                    <option value="{{$each}}" <//?php if(!empty($jsonArr['class'])){ if($jsonArr['class'] == $each){ echo "selected"; } }?>>{{$each}}</option>
                                    @endforeach
                          </select>
                       </div> --}}


                       <div class="col-md-4 form-group mb-3">
                          <label for="studentname">Student Name</label>
                          <input name="student_name" 
                             class="form-control"
                             id="studentname"
                             placeholder="Enter Student Name" value="<?php if(!empty($jsonArr['student_name'])){  echo $jsonArr['student_name']; } ?>"
                             />
                       </div>
                       <div class="col-md-8 form-group mb-3">
                          
                       </div>
                        <div class="col-md-6">
                            <button class="btn btn-primary">Search</button>
                            {{-- <a class="btn btn-primary" href="{{url('student-registrations')}}">Clear</a> --}}
                            
                            <input type="reset" class="btn btn-danger text text-white" value="Reset">
                        </div>
                    </div>
                </form>
            </div>
            </div>
            
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  
                <h4 class="card-title mb-3 text-end"><a href="{{url('add-student-registrations')}}"><button class="btn btn-outline-primary" type="button">Create Registration</button></a>
                  <a href="{{url('curren-year-student-registrations')}}"><button class="btn btn-outline-primary" type="button">Current Year Registration</button></a>
                  <!-- <a href="{{url('add-student-registrations')}}"><button class="btn btn-warning m-3">Export</button></a> -->
                </h4>
                  
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                          <th>SNo.</th>
                          <th>Form No.</th>
                          <th>DOB</th>
                          <th>Class Name</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Session Name</th>
                          <th>Mobile Number</th>
                          <th>Registration Date</th>
                          <th>Updated by</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody> 
                        @if(!empty($all_inquiry)) 
                        @foreach($all_inquiry as $each_inq) 
                          <?php $notificationData1 = json_decode($each_inq->json_str, true); ?>

                        <tr>
                          <td>{{$loop->iteration}}</td>
                          <td>{{$each_inq->form_number}}</td>
                          <td>{{date('d-m-Y',strtotime($each_inq->date_of_birth))}}</td>
                          <td>{{$each_inq->class_name}}</td>
                          <td><?php if(!empty($each_inq->studentname_prefix)){ echo ucwords($each_inq->studentname_prefix).' '; } if(!empty($each_inq->student_name)){ echo ucwords($each_inq->student_name); } ?></td>
                          <td><?php  if(!empty($each_inq->fathername_prefix)){ echo ucwords($each_inq->fathername_prefix).' '; } if(!empty($notificationData1['student_father_name'])){
                            echo ucwords($notificationData1['student_father_name']);
                          } else {  if(!empty($each_inq->fathername_prefix)){ echo ucwords($each_inq->fathername_prefix).' '; }
                            if(!empty($notificationData1['fathername'])){
                            echo ucwords($notificationData1['fathername']);
                          }
                          } ?> </td>
                          <td>{{$each_inq->session_name}}</td>
                          <td><?php if(!empty($notificationData1['father_mobile'])){
                            echo $notificationData1['father_mobile'];
                          }else {echo $each_inq->mobile_number; }?></td>
                          <td>{{ date('d-m-Y', strtotime($each_inq->created_at)) }}</td>
                          <td>Admin</td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-primary dropdown-toggle" id="dropdownMenuButton" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Actions
                              </button>
                              <div class="dropdown-menu btn btn-danger " aria-labelledby="dropdownMenuButton"> <a class="dropdown-item " href="{{ route('registrationviewlist',$each_inq->id) }}">View</a>
                                 <a class="dropdown-item" href="{{ route('registrationeditlist',$each_inq->id) }}">Edit</a>
                              <!-- <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"><a class="dropdown-item" href="{{url('inquiry-edit')}}/{{$each_inq->id}}">Edit</a>
                              </div> -->
                            </div>
                          </td>
                        </tr>
                        @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>SNo.</th>
                          <th>Form No.</th>
                          <th>DOB</th>
                          <th>Class Name</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Session Name</th>
                          <th>Mobile Number</th>
                          <th>Registration Date</th>
                          <th>Updated by</th>
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
        </div>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
                 <!-- <script type="text/javascript">$.noConflict();</script>  -->

        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <script>
            // $.noConflict();
            jQuery(document).ready(function($){
          
                var availableTags2 = [
                    <?php foreach($all_inquiry as $each2){ 
                         // $inq_str_data = json_decode($each2->json_str, true);  ?>
                         "<?php if(!empty($each2->student_name)){ echo $each2->student_name; }?>",
                   <?php } ?>
                    
                ];
                $( "#studentname" ).autocomplete({
                    source: availableTags2
                });

                setTimeout(function() {
                  var year = $("#year").val()
                  $("#session_name").val(year);
                }, 1000);
            });
        </script> 

@endsection 

