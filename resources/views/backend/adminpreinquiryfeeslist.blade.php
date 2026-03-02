@extends('backend.layouts.main')
@section('main-container')
<style type="text/css">
   .validation_err {
      color: red !important;
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
   .uperletter {
      text-transform: capitalize;
   }
</style>

<div class="main-content pt-4">
   <div class="breadcrumb">
      <h1 class="me-2">Pre Enquiry fees list</h1>
   </div>
   <div class="separator-breadcrumb border-top"></div>

   <div class="row">
      <div class="form_section1_div">
         <form class="" method="post" action="{{ url('filter-preenquiry') }}">
            @csrf
            <div class="row">
               <div class="col-md-2 form-group mb-3">
                  <label for="firstName1">Session :</label>
                  <input type="text" readonly id="session_name" class="form-control" value="" name="session_name">
               </div>

               <div class="col-md-2 form-group mb-3">
                  <label for="firstName1">Class Name</label>
                  <select id="classname" class="form-control" name="classname" required>
                     <option value="" disabled selected>Please select</option>
                     @foreach($classlist as $each)
                     <option value="{{ $each->class_name }}">{{ $each->class_name }}</option>
                     @endforeach
                  </select>
                  <span class="classname_msg validation_err"></span>
               </div>

               <div class="col-md-2 form-group mb-3">
                  <label for="studentname">Student Name</label>
                  <input name="student_name" class="form-control uperletter" id="studentname" placeholder="Enter Student Name" />
               </div>

               <div class="col-md-2 form-group mb-3">
                  <label>Start Date</label>
                  <input type="date" name="fromdate" class="form-control" placeholder="dd-mm-yyyy">
                  <span class="fromdate_msg validation_err"></span>
               </div>

               <div class="col-md-2 form-group mb-3">
                  <label>End Date</label>
                  <input type="date" name="todate" class="form-control" placeholder="dd-mm-yyyy">
                  <span class="todate_msg validation_err"></span>
               </div>

               <div class="col-md-3">
                  <button class="btn btn-primary">Search</button>
                  <input type="reset" class="btn btn-danger text text-white" value="Reset">
               </div>
            </div>
         </form>
      </div>
   </div>

   <div class="separator-breadcrumb border-top"></div>

   <div class="col-md-12 mb-4">
      <div class="card text-start">
         <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
               <a href="{{ url('admin-preenquiryform') }}">
                  <button class="btn btn-primary" type="button">Create Pre Enquiry</button>
               </a>

               <form method="POST" action="{{ route('admin.preenquiryfees.export') }}">
                  @csrf
                  <button type="submit" class="btn btn-warning">Export CSV</button>
               </form>
            </div>

            <div class="table-responsive">
               <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                  <thead>
                     <tr>
                        <th>S No.</th>
                        <th>Enquiry No.</th>
                        <th>Class Name</th>
                        <th>Student Name</th>
                        <th>Father Name</th>
                        <th>Session Name</th>
                        <th>Mobile Number</th>
                        <th>Amount</th>
                        <th>Enquiry Date</th>
                        <th>Year</th>
                        <th>Status</th>
                     </tr>
                  </thead>
                  <tbody>
                     @if(sizeof($all_inquiry))
                     @php $i = 1; @endphp
                     @foreach($all_inquiry as $each_inq)
                     @php $notificationData1 = json_decode($each_inq->json_str, true); @endphp
                     <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $each_inq->form_number }}</td>
                        <td>{{ $each_inq->class_name }}</td>
                        <td>{{ (!empty($notificationData1['studentname_prefix']) ? $notificationData1['studentname_prefix'].' ' : '') . ucwords($each_inq->student_name) }}</td>
                        <td>{{ (!empty($notificationData1['fathername_prefix']) ? $notificationData1['fathername_prefix'].' ' : '') . (!empty($notificationData1['fathername']) ? ucwords($notificationData1['fathername']) : '') }}</td>
                        <td>{{ $each_inq->session_name }}</td>
                        <td>{{ $each_inq->mobile_number }}</td>
                        <td>500</td>
                        <td>{{ date('d-m-Y', strtotime($each_inq->created_at)) }}</td>
                        <td>
                           @if(!empty($each_inq->next_year))
                           @php
                           $years = explode("_", $each_inq->session_name);
                           echo count($years) === 2 ? ($years[0] + 1) . "_" . ($years[1] + 1) : 'Invalid';
                           @endphp
                           @else
                           {{ $_COOKIE['selectedYear'] ?? '' }}
                           @endif
                        </td>
                        <td>
                           <div class="dropdown">
                              <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                 Actions
                              </button>
                              <div class="dropdown-menu">
                                 <a class="dropdown-item" href="{{ route('preenquiryviewlist', $each_inq->id) }}">View</a>
                                 <a class="dropdown-item" target="_blank" href="{{ route('enquiryrecipt', $each_inq->id) }}">Receipt</a>
                              </div>
                           </div>
                        </td>
                     </tr>
                     @endforeach
                     @else
                     <tr>
                        <td colspan="11" class="text-center text-danger">There Are No Records Available</td>
                     </tr>
                     @endif
                  </tbody>
                  <tfoot>
                     <tr>
                        <th>S No.</th>
                        <th>Enquiry No.</th>
                        <th>Class Name</th>
                        <th>Student Name</th>
                        <th>Father Name</th>
                        <th>Session Name</th>
                        <th>Mobile Number</th>
                        <th>Amount</th>
                        <th>Enquiry Date</th>
                        <th>Year</th>
                        <th>Status</th>
                     </tr>
                  </tfoot>
               </table>
               <h4>Total Enquiry Amount: {{ $enquirecount * 500 }}</h4>
            </div>
         </div>
      </div>
   </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script>
   $(document).ready(function () {
      setTimeout(function () {
         var year = $("#year").val();
         $("#session_name").val(year);
      }, 1000);
   });
</script>
@endsection
