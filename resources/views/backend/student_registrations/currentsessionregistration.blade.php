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

   /* Sticky table container and header */
   .table-responsive.table-sticky-container {
      max-height: 72vh;
      min-height: 380px;
      overflow-y: auto;
      overflow-x: auto;
      position: relative;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      background: #fff;
   }

   #zero_configuration_table {
      border-collapse: separate !important;
      border-spacing: 0;
      width: 100% !important;
      margin-bottom: 0 !important;
   }

   #zero_configuration_table thead th {
      position: sticky !important;
      top: 0 !important;
      z-index: 5 !important;
      background: #f1f3f7 !important;
      color: #333333 !important;
      font-weight: 700 !important;
      border-top: 1px solid #d1d5db !important;
      border-bottom: 2px solid #bbc1cb !important;
      border-right: 1px solid #e5e7eb !important;
      white-space: nowrap !important;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
      vertical-align: middle !important;
      padding: 10px 12px !important;
   }

   #zero_configuration_table tbody td {
      white-space: nowrap !important;
      vertical-align: middle !important;
      padding: 9px 12px !important;
      border-bottom: 1px solid #e5e7eb !important;
      border-right: 1px solid #f1f3f7 !important;
   }

   /* Increase width of the S.No / first column so numbers are not cramped */
   #zero_configuration_table th.sno-column,
   #zero_configuration_table td.sno-column,
   #zero_configuration_table th:first-child,
   #zero_configuration_table td:first-child,
   #zero_configuration_table thead th.sorting_asc:first-child,
   #zero_configuration_table thead th.sorting_desc:first-child,
   #zero_configuration_table td.sorting_1:first-child {
      min-width: 85px !important;
      width: 85px !important;
      max-width: 95px !important;
      text-align: center !important;
      padding-left: 12px !important;
      padding-right: 12px !important;
   }

   #zero_configuration_table tbody tr:hover {
      background-color: #f8fafc !important;
   }

   #zero_configuration_table_filter {
      display: none !important;
   }

   .student-search-box {
      min-width: 300px;
      max-width: 520px;
   }

   .student-search-box .input-group-text {
      background-color: #f8fafc;
      font-weight: 600;
      color: #4f46e5;
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
                    <div class="row align-items-end">
                       <div class="col-md-2 form-group mb-3">
                          <label for="firstName1">Session :</label>
                          <input type="text" readonly id="session_name" class="form-control" value="" name="session_name">
                       </div>
                       <div class="col-md-2 form-group mb-3">
                          <label for="studentname">Form Number</label>
                          <input name="form_number" class="form-control" id="form_number" placeholder="Form Number" value="<?php if(!empty($jsonArr['form_number'])){ echo $jsonArr['form_number']; }?>" />
                       </div>
                       <div class="col-md-2 form-group mb-3">
                        <label for="serial_number">Serial number</label>
                        <input name="serial_number" class="form-control" id="serial_number" placeholder="Serial Number" value="<?php if(!empty($jsonArr['serial_number'])){ echo $jsonArr['serial_number']; }?>" />
                      </div>

                      <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Class Name</label>
                        <select id="classname" class="form-control" name="classname" autocomplete="">
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

                      <div class="col-md-3 form-group mb-3 d-flex gap-2">
                          <button class="btn btn-primary flex-fill" type="submit">Search</button>
                          <input type="reset" class="btn btn-danger text-white flex-fill" value="Reset">
                      </div>
                    </div>
                </form>
            </div>
            </div>
            
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  
                  <!-- Card Header: Title & Action Buttons -->
                  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                    <div class="d-flex align-items-center">
                      <h4 class="card-title mb-0 me-2">Active Students (Current Year)</h4>
                      <span class="badge bg-success text-white border" id="active_students_badge">{{ count($all_inquiry) }} Active Students</span>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                      <a href="{{url('add-student-registrations')}}" class="btn btn-outline-primary btn-sm">Create Registration</a>
                      <a href="{{url('curren-year-student-registrations')}}" class="btn btn-outline-primary active btn-sm">Current Year Registration</a>
                      <a href="{{route('archived-students')}}" class="btn btn-outline-secondary btn-sm position-relative">
                        Archived Students
                        @if(isset($archivedCount) && $archivedCount > 0)
                          <span class="badge bg-secondary text-white ms-1">{{ $archivedCount }}</span>
                        @endif
                      </a>
                    </div>
                  </div>

                  <!-- Live Search Bar: Arranged properly above table -->
                  <div class="row align-items-center mb-3">
                    <div class="col-12 col-md-8 col-lg-6">
                      <div class="input-group student-search-box shadow-sm">
                        <span class="input-group-text">
                          <i class="i-Search-on-Cloud me-1"></i> Search
                        </span>
                        <input type="text" id="live_student_search" class="form-control" placeholder="Search Student Name, Parent Name, Scholar No, Mobile No..." autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="clear_live_student_search" title="Clear Search">
                          Clear
                        </button>
                      </div>
                      <small class="text-muted d-block mt-1" style="font-size: 11px;">
                        <i class="i-Information me-1 text-primary"></i> Instant search as you type: Student Name, Parent Name, Scholar No., Mobile No.
                      </small>
                    </div>
                  </div>
                  
                  <div class="table-responsive table-sticky-container">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                          <th class="sno-column">SNo.</th>
                          <th>Form No.</th>
                          <th>DOB</th>
                          <th>Class Name</th>
                          <th>Section</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Current Session</th>
                          <th>Admission Session</th>
                          <th>Batch</th>
                          <th>Mobile Number</th>
                          <th>Registration Date</th>
                          <th>Updated by</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody> 
                        @if(!empty($all_inquiry)) 
                        @foreach($all_inquiry as $each_inq) 
                          @if(!empty($each_inq->is_archived) && ($each_inq->is_archived == 1 || $each_inq->is_archived === '1'))
                            @continue
                          @endif
                          <?php $notificationData1 = json_decode($each_inq->json_str, true); ?>

                        <tr>
                          <td class="sno-column">{{$loop->iteration}}</td>
                          <td>
                            {{$each_inq->form_number}}
                            @if(!empty($each_inq->scholar_no))
                              <span class="d-none search-scholar-no">{{ $each_inq->scholar_no }}</span>
                            @endif
                          </td>
                          <td>{{date('d-m-Y',strtotime($each_inq->date_of_birth))}}</td>
                          <td>{{$each_inq->class_name}}</td>
                          <td>{{ !empty($each_inq->section_name) ? $each_inq->section_name : (!empty($notificationData1['section_name']) ? $notificationData1['section_name'] : '-') }}</td>
                          <td><?php if(!empty($each_inq->studentname_prefix)){ echo ucwords($each_inq->studentname_prefix).' '; } if(!empty($each_inq->student_name)){ echo ucwords($each_inq->student_name); } ?></td>
                          <td>
                            <?php
                              $fatherName = '';
                              if(!empty($each_inq->fathername_prefix)){ $fatherName .= ucwords($each_inq->fathername_prefix).' '; }
                              if(!empty($notificationData1['student_father_name'])){
                                $fatherName .= ucwords($notificationData1['student_father_name']);
                              } elseif(!empty($notificationData1['fathername'])){
                                $fatherName .= ucwords($notificationData1['fathername']);
                              }

                              $motherName = '';
                              if(!empty($notificationData1['mothername_prefix'])){ $motherName .= ucwords($notificationData1['mothername_prefix']).' '; }
                              if(!empty($notificationData1['mothername'])){
                                $motherName .= ucwords($notificationData1['mothername']);
                              } elseif(!empty($notificationData1['mother_name'])){
                                $motherName .= ucwords($notificationData1['mother_name']);
                              }
                            ?>
                            {{ !empty($fatherName) ? $fatherName : (!empty($motherName) ? $motherName : '-') }}
                            @if(!empty($motherName))
                              <span class="d-none search-parent-mother">{{ $motherName }}</span>
                            @endif
                          </td>
                          <?php
                              $rawCurrent = $each_inq->session_name ?: (session('db_names') ?: '2025_2026');
                              $currentSessionDisplay = str_replace('_', '-', $rawCurrent);

                              $isNextYear = !empty($notificationData1['next_year']) && ($notificationData1['next_year'] == 1 || $notificationData1['next_year'] === '1');
                              $rawAdmission = $notificationData1['admission_session'] ?? ($notificationData1['intended_session'] ?? null);

                              if ($isNextYear && empty($rawAdmission)) {
                                  $parts = preg_split('/[_-]/', $rawCurrent);
                                  if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                                      $rawAdmission = ((int)$parts[0] + 1) . '-' . ((int)$parts[1] + 1);
                                  }
                              }
                              if (empty($rawAdmission)) {
                                  $rawAdmission = $rawCurrent;
                              }
                              $admissionSessionDisplay = str_replace('_', '-', $rawAdmission);

                              $rawBatch = $notificationData1['batch'] ?? $rawAdmission;
                              $batchDisplay = str_replace('_', '-', $rawBatch);

                              $isUpcoming = ($isNextYear || $admissionSessionDisplay !== $currentSessionDisplay);
                          ?>
                          <td>{{$currentSessionDisplay}}</td>
                          <td>
                              {{$admissionSessionDisplay}}
                              @if($isUpcoming)
                                  <span class="badge bg-success text-white ms-1" style="font-size: 11px;">Next Year</span>
                              @endif
                          </td>
                          <td>{{$batchDisplay}}</td>
                          <td>
                            <?php
                              $primaryMobile = !empty($notificationData1['father_mobile']) ? $notificationData1['father_mobile'] : $each_inq->mobile_number;
                              $altMobiles = [];
                              if (!empty($notificationData1['mother_mobile']) && $notificationData1['mother_mobile'] != $primaryMobile) {
                                $altMobiles[] = $notificationData1['mother_mobile'];
                              }
                              if (!empty($each_inq->mobile_number) && $each_inq->mobile_number != $primaryMobile) {
                                $altMobiles[] = $each_inq->mobile_number;
                              }
                            ?>
                            {{ $primaryMobile ?: '-' }}
                            @if(!empty($altMobiles))
                              <span class="d-none search-alt-mobile">{{ implode(' ', $altMobiles) }}</span>
                            @endif
                          </td>
                          <td>{{ date('d-m-Y', strtotime($each_inq->created_at)) }}</td>
                          <td>Admin</td>
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
                        <tr><td colspan="14" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th class="sno-column">SNo.</th>
                          <th>Form No.</th>
                          <th>DOB</th>
                          <th>Class Name</th>
                          <th>Section</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Current Session</th>
                          <th>Admission Session</th>
                          <th>Batch</th>
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
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <script>
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

                function applyLiveStudentSearch(query) {
                    query = (query || '').trim();
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#zero_configuration_table')) {
                        $('#zero_configuration_table').DataTable().search(query).draw();
                    } else {
                        var tokens = query.toLowerCase().split(/\s+/).filter(Boolean);
                        $('#zero_configuration_table tbody tr').each(function () {
                            var row = $(this);
                            if (row.find('td').length <= 1) {
                                return;
                            }
                            if (tokens.length === 0) {
                                row.show();
                                return;
                            }
                            var rowText = row.text().toLowerCase();
                            var matches = tokens.every(function (t) {
                                return rowText.indexOf(t) > -1;
                            });
                            row.toggle(matches);
                        });
                    }
                }

                $(document).on('keyup input', '#live_student_search', function () {
                    applyLiveStudentSearch($(this).val());
                });

                $(document).on('click', '#clear_live_student_search', function () {
                    $('#live_student_search').val('');
                    applyLiveStudentSearch('');
                    $('#live_student_search').focus();
                });

                $(document).on('keyup input', '#studentname', function () {
                    var val = $(this).val();
                    $('#live_student_search').val(val);
                    applyLiveStudentSearch(val);
                });

                function keepLatestFirst() {
                    if (!$.fn.DataTable || !$('#zero_configuration_table').length) {
                        return;
                    }

                    var currentSearch = $('#live_student_search').val() || '';

                    if ($.fn.DataTable.isDataTable('#zero_configuration_table')) {
                        var dt = $('#zero_configuration_table').DataTable();
                        dt.order([0, 'asc']);
                        if (currentSearch) {
                            dt.search(currentSearch);
                        }
                        dt.draw();
                        return;
                    }

                    var dt = $('#zero_configuration_table').DataTable({
                        order: [[0, 'asc']]
                    });
                    if (currentSearch) {
                        dt.search(currentSearch).draw();
                    }
                }

                keepLatestFirst();
                setTimeout(keepLatestFirst, 500);
            });
        </script> 

@endsection 
