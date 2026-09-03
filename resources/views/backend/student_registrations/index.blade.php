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

.uperletter{
  text-transform: capitalize;
}  
</style>
<div class="main-content pt-4">
          <div class="breadcrumb">
            <h1 class="me-2">All Registration </h1>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">

            <div class="col-md-12 mb-4">
				<div class="form_section1_div">
					<form method="post" action="{{ url('student-registration-filter') }}" id="filterForm">
						@csrf
						<div class="row">
							<div class="col-md-2 form-group mb-3">
								<label for="session_name">Session :</label>
								<input type="text" readonly id="session_name" class="form-control" value="{{ $jsonArr['session_name'] ?? '' }}" name="session_name">
							</div>

							<div class="col-md-2 form-group mb-3">
								<label for="form_number">Form Number</label>
								<input name="form_number" class="form-control" id="form_number" placeholder="Form Number" value="{{ $jsonArr['form_number'] ?? '' }}" />
							</div>

							<div class="col-md-2 form-group mb-3">
								<label for="serial_number">Serial number</label>
								<input name="serial_number" class="form-control" id="serial_number" placeholder="Serial Number" value="{{ $jsonArr['serial_number'] ?? '' }}" />
							</div>

							<div class="col-md-2 form-group mb-3">
								<label for="classname">Class Name</label>
								<select id="classname" class="form-control" name="classname">
									<option value=""> -- Please select -- </option>
									@foreach($classlist as $each)
										<option value="{{ $each->class_name }}" {{ (!empty($jsonArr['class_name']) && $jsonArr['class_name'] == $each->class_name) ? 'selected' : '' }}>
											{{ $each->class_name }}
										</option>
									@endforeach
								</select>
								<span class="classname_msg validation_err"></span>
							</div>

							<div class="col-md-2 form-group mb-3">
								<label for="application_for">Application For</label>
								<select name="application_for" class="form-control" id="application_for">
									<option value="">-- Select --</option>
									<option value="RTE" {{ (!empty($jsonArr['application_for']) && $jsonArr['application_for'] == 'RTE') ? 'selected' : '' }}>RTE</option>
									<option value="CBSE" {{ (!empty($jsonArr['application_for']) && $jsonArr['application_for'] == 'CBSE') ? 'selected' : '' }}>CBSE (Non RTE)</option>
								</select>
							</div>

							<div class="col-md-4 form-group mb-3">
								<label for="studentname">Student Name</label>
								<input name="student_name" class="form-control uperletter" id="studentname" placeholder="Enter Student Name" value="{{ $jsonArr['student_name'] ?? '' }}" />
							</div>

							<div class="col-md-6 d-flex align-items-end gap-2">
								<button class="btn btn-primary" type="submit">Search</button>
								<!--<input type="reset" class="btn btn-danger text-white" value="Reset">-->
								<input type="button" class="btn btn-secondary" id="clearFilterBtn" value="Clear Filter">
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
                  <a href="{{route('archived-students')}}"><button class="btn btn-outline-secondary" type="button">Archived Students</button></a>
                  <form method="POST" action="{{ route('export_registrations.csv') }}">
						@csrf
						<input type="hidden" name="form_number" value="{{ $jsonArr['form_number'] ?? '' }}">
						<input type="hidden" name="serial_number" value="{{ $jsonArr['serial_number'] ?? '' }}">
						<input type="hidden" name="class_name" value="{{ $jsonArr['class_name'] ?? '' }}">
						<input type="hidden" name="application_for" value="{{ $jsonArr['application_for'] ?? '' }}">
						<input type="hidden" name="student_name" value="{{ $jsonArr['student_name'] ?? '' }}">
						<input type="hidden" name="session_name" value="{{ $jsonArr['session_name'] ?? '' }}">
						<button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
				</form>

                  <!-- <a href="{{url('add-student-registrations')}}"><button class="btn btn-warning m-3">Export</button></a> -->
                </h4>
                  
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                          <th>S.No.</th>
                          <th>Form No.</th>
                          <th>Scholar No</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Class Name</th>
                          <th>Section Name</th>
                          <th>DOB</th>
                          <th>Session Name</th>
                          <th>Mobile Number</th>
                          <th>Create Date</th>
						  <th>Reg. Date</th>
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
                          <td><?php if(!empty($each_inq->scholar_no)){ echo $each_inq->scholar_no; }?></td>
                          <td><?php if(!empty($each_inq->studentname_prefix)){ echo ucwords($each_inq->studentname_prefix).' '; } if(!empty($each_inq->student_name)){ echo ucwords($each_inq->student_name); } ?></td>
                          <td><?php  if(!empty($each_inq->fathername_prefix)){ echo ucwords($each_inq->fathername_prefix).' '; } if(!empty($notificationData1['student_father_name'])){
                            echo ucwords($notificationData1['student_father_name']);
                          } else {  if(!empty($each_inq->fathername_prefix)){ echo ucwords($each_inq->fathername_prefix).' '; }
                            if(!empty($notificationData1['fathername'])){
                            echo ucwords($notificationData1['fathername']);
                          }
                          } ?> </td>
                          <td>{{$each_inq->class_name}}</td>
                          <td>{{ $notificationData1['section_name'] ?? '' }}</td>
                          <td>{{date('d-m-Y',strtotime($each_inq->date_of_birth))}}</td>
                          <td>
                            <?php
                              $sessionDisplay = $each_inq->session_name;
                              if (!empty($notificationData1['batch'])) {
                                  $sessionDisplay = $notificationData1['batch'];
                              } elseif (!empty($notificationData1['intended_session'])) {
                                  $sessionDisplay = $notificationData1['intended_session'];
                              }
                            ?>
                            {{$sessionDisplay}}
                          </td>
                          <td><?php if(!empty($notificationData1['father_mobile'])){
                            echo $notificationData1['father_mobile'];
                          }else {echo $each_inq->mobile_number; }?></td>
                          <td>{{ date('d-m-Y', strtotime($each_inq->created_at)) }}</td>
						  <td>{{ date('d-m-Y', strtotime($each_inq->registration_date)) }}</td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-primary dropdown-toggle" id="dropdownMenuButton" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Actions
                              </button>
                              <div class="dropdown-menu btn btn-danger " aria-labelledby="dropdownMenuButton"> <a class="dropdown-item " href="{{ route('registrationviewlist',$each_inq->id) }}">View</a>
                                 <a class="dropdown-item" href="{{ route('registrationeditlist',$each_inq->id) }}">Edit</a>
                                 <form method="POST" action="{{ route('archive-student', $each_inq->id) }}" onsubmit="return confirm('Are you sure you want to archive this student? This student will no longer appear in the active ERP records.');">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Archive Student</button>
                                 </form>
                              <!-- <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"><a class="dropdown-item" href="{{url('inquiry-edit')}}/{{$each_inq->id}}">Edit</a>
                              </div> -->
                            </div>
                          </td>
                        </tr>
                        @endforeach
                        @else
                        <tr><td colspan="13" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>SNo.</th>
                          <th>Form No.</th>
                          <th>Scholar No</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Class Name</th>
                          <th>Section Name</th>
                          <th>DOB</th>
                          <th>Session Name</th>
                          <th>Mobile Number</th>
                          <th>Create Date</th>
						  <th>Reg. Date</th>
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
    jQuery(document).ready(function($) {
        // Autocomplete for student name
        var availableTags2 = [
            @foreach($all_inquiry as $each2)
                "{{ $each2->student_name }}",
            @endforeach
        ];
        $("#studentname").autocomplete({
            source: availableTags2
        });

        // Set session value after a delay
        setTimeout(function() {
            var year = $("#year").val();
            $("#session_name").val(year);
        }, 1000);

        // Clear Filter Button
        $('#clearFilterBtn').click(function () {
            $('#form_number').val('');
            $('#serial_number').val('');
            $('#classname').val('');
            $('#application_for').val('');
            $('#studentname').val('');
        });

        function keepLatestFirst() {
            if (!$.fn.DataTable || !$('#zero_configuration_table').length) {
                return;
            }

            if ($.fn.DataTable.isDataTable('#zero_configuration_table')) {
                $('#zero_configuration_table').DataTable().order([0, 'asc']).draw();
                return;
            }

            $('#zero_configuration_table').DataTable({
                order: [[0, 'asc']]
            });
        }

        keepLatestFirst();
        setTimeout(keepLatestFirst, 500);
    });
</script>


@endsection 

