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

   .s1 {
      background-color: #ff4c51;
   }

   .uperletter {
      text-transform: capitalize;
   }

   .sw-theme-default .sw-toolbar-top {
      display: none;
   }

   .mb-3 {
      margin-bottom: 0.4rem !important;
   }

   .select2-container .select2-selection--single {
      background-color: #f3f4f6 !important;
      border: 1px solid #ced4da !important;
      height: 34px !important;
      border-radius: 4px !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 34px !important;
      color: #47404f !important;
      padding-left: 10px !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 34px !important;
      top: 0 !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__placeholder {
      color: #6c757d !important;
   }

   .error_msg2 {
      color: green;
   }

   .validation_err2 {
      color: green !important;
   }

   .ui-menu .ui-menu-item {
      font-size: 0.813rem;
      width: 646px;

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
      <h1 class="me-2">Edit Student Registration</h1>
   </div>
   <div class="separator-breadcrumb border-top"></div>
   <div class="row">
      @if(!empty($all_inquiry))
      @foreach($all_inquiry as $each_inq)
      <?php
         $notificationData1 = json_decode($each_inq->json_str, true);
         if (!is_array($notificationData1)) {
             $notificationData1 = [];
         }

         // 1. Student DOB normalization to YYYY-MM-DD
         $rawDob = !empty($notificationData1['student_dob']) ? $notificationData1['student_dob'] : (!empty($each_inq->date_of_birth) ? $each_inq->date_of_birth : '');
         $formattedDob = '';
         if (!empty($rawDob)) {
             $rawDob = trim($rawDob);
             if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDob)) {
                 $parts = explode('-', $rawDob);
                 if (str_starts_with($parts[0], '00')) {
                     $parts[0] = '20' . substr($parts[0], 2);
                 }
                 $formattedDob = implode('-', $parts);
             } elseif (preg_match('/^(\d{1,2})[-\/\.](\d{1,2})[-\/\.](00|19|20)?(\d{2})$/', $rawDob, $m)) {
                 $d = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                 $mo = str_pad($m[2], 2, '0', STR_PAD_LEFT);
                 $prefix = !empty($m[3]) ? $m[3] : '';
                 $yy = $m[4];
                 if ($prefix === '00' || empty($prefix)) {
                     $year = ($yy > 30) ? ('19' . $yy) : ('20' . $yy);
                 } else {
                     $year = $prefix . $yy;
                 }
                 $formattedDob = "$year-$mo-$d";
             } else {
                 $ts = strtotime($rawDob);
                 if ($ts && $ts > 0) {
                     $y = (int)date('Y', $ts);
                     if ($y < 100) {
                         $y = ($y > 30) ? 1900 + $y : 2000 + $y;
                         $formattedDob = sprintf('%04d-%s-%s', $y, date('m', $ts), date('d', $ts));
                     } else {
                         $formattedDob = date('Y-m-d', $ts);
                     }
                 } else {
                     $formattedDob = $rawDob;
                 }
             }
         }

         // 2. Nationality
         $savedNationality = !empty($notificationData1['nationality']) ? trim($notificationData1['nationality']) : 'Indian';
         if (strcasecmp($savedNationality, 'INDIA') === 0 || strcasecmp($savedNationality, 'Indian') === 0) {
             $savedNationality = 'Indian';
         }
         $nationalityOptions = ['Indian', 'NRI', 'Others'];
         if (!empty($savedNationality) && !in_array($savedNationality, $nationalityOptions)) {
             $nationalityOptions[] = $savedNationality;
         }

         // 3. Category
         $savedCategory = !empty($notificationData1['category']) ? trim($notificationData1['category']) : '';

         // 4. Class Name
         $savedClass = !empty($each_inq->class_name) ? trim($each_inq->class_name) : (!empty($notificationData1['classname']) ? trim($notificationData1['classname']) : '');
         $rawClasses = [];
         if (!empty($classlist)) {
             foreach ($classlist as $c) {
                 $rawClasses[] = is_object($c) ? ($c->class_name ?? '') : (string)$c;
             }
         }
         if (empty($rawClasses)) {
             $rawClasses = config('global.class_name') ?? [];
         }
         if (!empty($savedClass)) {
             $rawClasses[] = $savedClass;
         }
         $allClasses = [];
         $classSeen = [];
         foreach ($rawClasses as $cl) {
             $cl = trim($cl);
             if ($cl === '') continue;
             $lower = strtolower($cl);
             if (!isset($classSeen[$lower])) {
                 $classSeen[$lower] = true;
                 if (strcasecmp($savedClass, $cl) === 0) {
                     $allClasses[] = $savedClass;
                 } else {
                     $allClasses[] = $cl;
                 }
             }
         }

         // 5. Session Name
         $savedSession = !empty($each_inq->session_name) ? trim($each_inq->session_name) : (!empty($notificationData1['session_name']) ? trim($notificationData1['session_name']) : (session('db_names') ?? '2025_2026'));
         $rawSessions = [];
         if (!empty($databaseNames) && is_array($databaseNames)) {
             foreach ($databaseNames as $db) {
                 if (is_numeric(substr($db, 0, 1))) {
                     $rawSessions[] = $db;
                 }
             }
         }
         $globalSessions = config('global.session_name') ?? [];
         foreach ($globalSessions as $gs) {
             $rawSessions[] = str_replace('-', '_', $gs);
         }
         if (!empty($savedSession)) {
             $rawSessions[] = $savedSession;
         }
         $sessionList = [];
         $sessionSeen = [];
         foreach ($rawSessions as $s) {
             $sKey = str_replace('-', '_', $s);
             if (!isset($sessionSeen[$sKey])) {
                 $sessionSeen[$sKey] = true;
                 if (str_replace('-', '_', $savedSession) === $sKey) {
                     $sessionList[] = $savedSession;
                 } else {
                     $sessionList[] = $s;
                 }
             }
         }

         // 6. Batch Name
         $savedBatch = (!empty($notificationData1['batch']) && $notificationData1['batch'] !== 'select') ? trim($notificationData1['batch']) : (!empty($each_inq->batch) ? trim($each_inq->batch) : '');
         $rawBatches = [];
         if (!empty($databaseNames) && is_array($databaseNames)) {
             foreach ($databaseNames as $db) {
                 if (is_numeric(substr($db, 0, 1))) {
                     $rawBatches[] = $db;
                 }
             }
         }
         for ($y = 2015; $y <= 2030; $y++) {
             $rawBatches[] = $y . '_' . ($y + 1);
         }
         if (!empty($savedBatch) && $savedBatch !== 'select') {
             $rawBatches[] = $savedBatch;
         }
         $batchList = [];
         $batchSeen = [];
         foreach ($rawBatches as $b) {
             $bKey = str_replace('-', '_', $b);
             if (!isset($batchSeen[$bKey])) {
                 $batchSeen[$bKey] = true;
                 if (str_replace('-', '_', $savedBatch) === $bKey) {
                     $batchList[] = $savedBatch;
                 } else {
                     $batchList[] = $b;
                 }
             }
         }
         sort($batchList);

         // 7. Caste
         $savedCaste = (!empty($notificationData1['student_caste']) && $notificationData1['student_caste'] !== '0' && $notificationData1['student_caste'] !== 'select')
             ? trim($notificationData1['student_caste'])
             : ((!empty($notificationData1['caste']) && $notificationData1['caste'] !== '0' && $notificationData1['caste'] !== 'select')
                 ? trim($notificationData1['caste'])
                 : '');
         $rawCastes = [];
         if (!empty($caste) && count($caste) > 0) {
             foreach ($caste as $c) {
                 $rawCastes[] = is_object($c) ? ($c->caste_name ?? '') : (string)$c;
             }
         }
         $globalCastes = config('global.caste') ?? [];
         foreach ($globalCastes as $gc) {
             $rawCastes[] = $gc;
         }
         if (!empty($savedCaste) && $savedCaste !== '0') {
             $rawCastes[] = $savedCaste;
         }
         $casteList = [];
         $casteSeen = [];
         foreach ($rawCastes as $c) {
             $c = trim($c);
             if ($c === '' || $c === '0') continue;
             $lower = strtolower($c);
             if (!isset($casteSeen[$lower])) {
                 $casteSeen[$lower] = true;
                 if (strcasecmp($savedCaste, $c) === 0) {
                     $casteList[] = $savedCaste;
                 } else {
                     $casteList[] = $c;
                 }
             }
         }
         natcasesort($casteList);
         $casteList = array_values($casteList);
      ?>
      <div class="col-md-12">
         <form novalidate="novalidate" method="post" action="{{url('saveeditregistration')}}" class="rg_form" id="form-id" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="<?php echo $each_inq->id; ?>">
            <input name="scholar_no" class="form-control "  value="<?php if(!empty($notificationData1['scholar_no'])){ echo $notificationData1['scholar_no'];} ?>" id="scholar_no" placeholder="Scholar Number" type="hidden" />
            <input name="received_amount" class="form-control " value="<?php if(!empty($notificationData1['received_amount'])){ echo $notificationData1['received_amount'];} ?>" id="received_amount" placeholder="Scholar Number" type="hidden" />
            <input name="amount" class="form-control " value="<?php if(!empty($notificationData1['amount'])){ echo $notificationData1['amount'];} ?>" id="amount" placeholder="Scholar Number" type="hidden" />
            <input type="hidden" name="bus_facility_start_date" value="<?php if(!empty($notificationData1['bus_facility_start_date'])){ echo $notificationData1['bus_facility_start_date'];} ?>">
            <input type="hidden" name="bus_facility_end_date" value="<?php if(!empty($notificationData1['bus_facility_end_date'])){ echo $notificationData1['bus_facility_end_date'];} ?>">
            <!--  SmartWizard html -->
            <div id="smartwizard">
               <ul>
                  <li><a href="#step-1">Step 1<br /><small>Student Details</small></a></li>
                  <li>
                     <a href="#step-2">Step 2<br /><small>Personal Details</small></a>
                  </li>
               </ul>
               <div>

                  <div id="step-1">
                     <h3 class="border-bottom border-gray pb-2">
                        Student Registration Form
                     </h3>
                     <div class="form_section1_div">
                        <div class="row">
                           <div class="col-md-4 form-group mb-3">
                              <label for="firstName1">Application Type<b class="validation_err">*</b></label>
                              <select id="application_for" class="form-control" name="application_for" autocomplete="shipping address-level1" required>
                                 @if(!empty($each_inq->application_for))
                                                <option>Please Select</option>

                                                @foreach(config('global.admission_type') as $each)
                                                @if($each_inq->application_for == $each)
                                                <option selected value="{{$each}}">{{$each}}</option>
                                                @else
                                                <option value="{{$each}}">{{$each}}</option>
                                                @endif
                                                @endforeach
                                                @else
                                                <option>Please Select</option>
                                                @foreach (config('global.admission_type') as $each)
                                                <option value="{{$each}}">{{$each}}</option>
                                                @endforeach
                                                @endif
                              </select>
                              <span class="application_for_msg validation_err"></span>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="studentname">Form Number</label>
                              <input name="form_number" class="form-control studentform_number" id="studentname" placeholder="Form Number" value="<?php if (!empty($each_inq->form_number)) {
                                 echo $each_inq->form_number;
                              } ?>" />
                           </div>
						   <div class="col-md-4 form-group mb-3">
								<label for="registration_date">Registration Date<b class="validation_err">*</b></label>
								<input type="date" name="registration_date" class="form-control" id="registration_date" 
									   value="{{ !empty($each_inq->registration_date) ? $each_inq->registration_date : date('Y-m-d') }}" required>
								<span class="registration_date_msg validation_err"></span>


							</div>

                           
						   <div class="col-md-3 form-group mb-3">
                           
                           <label for="studentname">Scholar No</label>
                              <input name="scholar_no" class="form-control" id="scholar_no" placeholder="Form Number" readonly value="<?php if (!empty($each_inq->scholar_no)) {
                                 echo $each_inq->scholar_no;
                              } ?>" />
                           </div>
                           <!-- <div class="col-md-4 form-group mb-3">
                              <label for="studentname">Student Name<b class="validation_err">*</b></label>
                              <input name="student_name" 
                                 class="form-control"
                                 id="studentname"
                                 placeholder="Enter Student Name"
                                 />
                                 <span class="student_name_msg validation_err"></span>
                           </div> -->
                           <div class="col-lg-6 form-group mb-3">

                              <label for="studentname">Student Name<b class="validation_err">*</b></label>
                              <div class="input-group mb-3">
                                 <div class="input-group-prepend">
                                    @php
                                       $savedStudentPrefix = !empty($notificationData1['studentname_prefix']) ? $notificationData1['studentname_prefix'] : (!empty($each_inq->studentname_prefix) ? $each_inq->studentname_prefix : 'Master');
                                    @endphp
                                    <select id="studentname_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="studentname_prefix" autocomplete="shipping address-level1">
                                       <option value="Master" {{ (strcasecmp($savedStudentPrefix, 'Master') === 0) ? 'selected' : '' }}>Master</option>
                                       <option value="Mr." {{ (strcasecmp($savedStudentPrefix, 'Mr.') === 0 || strcasecmp($savedStudentPrefix, 'Mr') === 0) ? 'selected' : '' }}>Mr.</option>
                                       <option value="Miss" {{ (strcasecmp($savedStudentPrefix, 'Miss') === 0) ? 'selected' : '' }}>Miss</option>
                                    </select>
                                 </div>
                                 <input type="text" class="form-control uperletter" id="studentname" placeholder="Enter Student Name" name="studentname" required value="<?php if (!empty($notificationData1['studentname'])) {
                                                                                                                                                      echo $notificationData1['studentname'];
                                                                                                                                                   } elseif (!empty($each_inq->student_name)) {
                                                                                                                                                      echo $each_inq->student_name;
                                                                                                                                                   } ?>" />
                                 <span class="student_name_msg validation_err"></span>
                              </div>
                                                                                                                                                                          
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="student_dob">Student DOB<b class="validation_err">*</b></label>
                              <input type="date" name="student_dob" class="form-control" id="student_dob" placeholder="Enter Student DOB" value="{{ $formattedDob }}" required />
                              <span class="student_dob_msg validation_err"></span>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="nationality">Nationality<b class="validation_err">*</b></label>
                              <select id="nationality" class="form-control" name="nationality" required>
                                 <option value="" disabled {{ empty($savedNationality) ? 'selected' : '' }}>Please select</option>
                                 @foreach($nationalityOptions as $natOption)
                                    <option value="{{ $natOption }}" {{ (strcasecmp($savedNationality, $natOption) === 0 || (strcasecmp($savedNationality, 'INDIA') === 0 && strcasecmp($natOption, 'Indian') === 0)) ? 'selected' : '' }}>{{ $natOption }}</option>
                                 @endforeach
                              </select>
                              <span class="nationality_msg validation_err"></span>
                           </div>

                           {{-- <div class="col-md-4 form-group mb-3">
                              <label for="firstName1">Gender</label>
                              <select id="gender" class="form-control" name="gender" required>
                                 <option value="" disabled selected>Please select</option>
                                 @foreach(config('global.gender') as $each)
                                 <option </?php if ($notificationData1['gender'] == $each) {
                                             echo "selected";
                                          } ?> value="{{$each}}">{{$each}}</option>
                                 @endforeach
                              </select> --}}



                              <div class="col-md-4 form-group mb-3">
                                 <label for="firstName1">Gender</label>
                                 <select id="gender" class="form-control" name="gender" required>
                                     <option value="" disabled selected>Please select</option>
                                     @foreach(config('global.gender') as $each)
                                         <option value="{{$each}}" {{ isset($notificationData1['gender']) && $notificationData1['gender'] == $each ? 'selected' : '' }}>
                                             {{$each}}
                                         </option>
                                     @endforeach
                                 </select>
                             {{-- </div> --}}
               
                              <span class="gender_msg validation_err"></span>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="Caste">Caste</label>
                              <select id="Caste" class="form-control Caste select2" name="student_caste" data-placeholder="Please select caste">
                                 <option value="" {{ empty($savedCaste) ? 'selected' : '' }}>Please select caste</option>
                                 @foreach($casteList as $eachCaste)
                                    <option value="{{ $eachCaste }}" {{ (strcasecmp($savedCaste, $eachCaste) === 0) ? 'selected' : '' }}>{{ $eachCaste }}</option>
                                 @endforeach
                              </select>
                              <span class="student_caste_msg validation_err"></span>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="religion">Religion<b class="validation_err">*</b></label>
                              <select id="religion" class="form-control" name="religion" required>
                                 <option value="" disabled selected>Please select</option>
                                 @foreach(config('global.religion') as $each)
                                 <option <?php if (!empty($notificationData1['religion'])) {
                                             if ($notificationData1['religion'] == $each) {
                                                echo "selected";
                                             }
                                          } ?> value="{{$each}}">{{$each}}</option>
                                 @endforeach
                              </select>
                              <span class="religion validation_err"></span>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="category">Category<b class="validation_err">*</b></label>
                              <select id="category" class="form-control" name="category" required>
                                  <option value="" disabled {{ empty($savedCategory) ? 'selected' : '' }}>Please select</option>
                                  @foreach(config('global.cate') as $each)
                                      <option value="{{ $each }}" {{ (strcasecmp($savedCategory, $each) === 0) ? 'selected' : '' }}>
                                          {{ $each }}
                                      </option>
                                  @endforeach
                              </select>
                              <span class="category_msg validation_err"></span>
                           </div>
                
                           <div class="col-md-4 form-group mb-3">
                              <label for="classname">Class Name<b class="validation_err">*</b></label>
                              <select id="classname" class="form-control" name="classname" required>
                                 <option value="" disabled {{ empty($savedClass) ? 'selected' : '' }}>Please select</option>
                                 @foreach($allClasses as $each)
                                    <option value="{{ $each }}" {{ ((string)$savedClass === (string)$each || (is_numeric($savedClass) && is_numeric($each) && (int)$savedClass === (int)$each) || strcasecmp($savedClass, $each) === 0) ? 'selected' : '' }}>{{ $each }}</option>
                                 @endforeach
                              </select>
                              <span class="classname_msg validation_err"></span>
                           </div>
                           <div class="col-md-4 form-group mb-3" id="main_section_container">
                              <label for="student_section_name">Section</label>
                              @php
                                 $savedStudentSection = !empty($each_inq->section_name) ? trim($each_inq->section_name) : (!empty($notificationData1['section_name']) ? trim($notificationData1['section_name']) : 'D');
                              @endphp
                              <select id="student_section_name" class="form-control" name="section_name">
                                 <option value="D" {{ ($savedStudentSection === 'D' || empty($savedStudentSection)) ? 'selected' : '' }}>D (Temporary)</option>
                                 <option value="Aryabhatta" {{ (strcasecmp($savedStudentSection, 'Aryabhatta') === 0) ? 'selected' : '' }}>Aryabhatta</option>
                                 <option value="Kautilya" {{ (strcasecmp($savedStudentSection, 'Kautilya') === 0) ? 'selected' : '' }}>Kautilya</option>
                                 <option value="Ramanujan" {{ (strcasecmp($savedStudentSection, 'Ramanujan') === 0) ? 'selected' : '' }}>Ramanujan</option>
                              </select>
                              <span class="section_name_msg validation_err"></span>
                           </div>

                           <div class="col-md-4 form-group mb-3" id="stream_selection_wrapper" style="display: none;">
                              <label for="stream_id">Stream <b class="validation_err">*</b></label>
                              <select id="stream_id" class="form-control" name="stream_id">
                                 <option value="" disabled selected>Please select Stream</option>
                              </select>
                              <span class="stream_id_msg validation_err"></span>
                           </div>

                           <div class="col-md-4 form-group mb-3" id="combination_selection_wrapper" style="display: none;">
                              <label for="combination_id">Subject Combination <b class="validation_err">*</b></label>
                              <select id="combination_id" class="form-control select2" name="combination_id" data-placeholder="Please select Subject Combination">
                                 <option value="" disabled selected>Please select Subject Combination</option>
                              </select>
                              <span class="combination_id_msg validation_err"></span>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="session_name">Session Name<b class="validation_err">*</b></label>
                              <select id="session_name" class="form-control" name="session_name" required>
                                 <option value="" disabled {{ empty($savedSession) ? 'selected' : '' }}>Please select</option>
                                 @foreach($sessionList as $eachSession)
                                    <option value="{{ $eachSession }}" {{ ($savedSession === $eachSession) ? 'selected' : '' }}>{{ $eachSession }}</option>
                                 @endforeach
                              </select>
                              <span class="session_name_msg validation_err"></span>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="batch">Batch Name<b class="validation_err">*</b></label>
                              <select name="batch" id="batch" class="form-control" required>
                                 <option value="select" disabled {{ empty($savedBatch) || $savedBatch === 'select' ? 'selected' : '' }}> - Year - </option>
                                 @foreach($batchList as $b)
                                    <option value="{{ $b }}" {{ ($savedBatch === $b) ? 'selected' : '' }}>{{ $b }}</option>
                                 @endforeach
                              </select>
                              <span class="batch_msg validation_err"></span>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="address">Present Address :</label>
                              <input class="form-control uperletter" id="address" type="text" placeholder="Enter Present Address" name="present_address" value="<?php if (!empty($notificationData1['present_address'])) {
                                                                                                                                                echo $notificationData1['present_address'];
                                                                                                                                             } ?>" />
                              <span class="present_address_msg validation_err"></span>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="address">Permanent Address :</label>
                              <input class="form-control uperletter" id="address" type="text" placeholder="Enter Permanent Address" name="permanent_address" value="<?php if (!empty($notificationData1['permanent_address'])) {
                                                                                                                                                   echo $notificationData1['permanent_address'];
                                                                                                                                                } ?>" />
                              <span class="permanent_address_msg validation_err"></span>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="address">Phone Number :</label>
                              <input class="form-control" id="phone number" type="text" placeholder="Enter Phone Number" name="phone_number" value="<?php if (!empty($notificationData1['phone_number'])) {
                                                                                                                                                         echo $notificationData1['phone_number'];
                                                                                                                                                      } elseif (!empty($each_inq->phone_number)) {
                                                                                                                                                         echo $each_inq->phone_number;
                                                                                                                                                      } ?>" />
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="address">Mobile Number :</label>
                              <input class="form-control" id="phonenumber" type="text" placeholder="Enter Mobile Number" name="mobile_number" value="<?php if (!empty($notificationData1['mobile_number'])) {
                                                                                                                                                         echo $notificationData1['mobile_number'];
                                                                                                                                                      } elseif (!empty($each_inq->mobile_number)) {
                                                                                                                                                         echo $each_inq->mobile_number;
                                                                                                                                                      } ?>" maxlength="10" pattern="\d{10}" onkeyup="this.value = this.value.replace(/[^0-9]/g, '');check();" /><span class="phonenumber_for_msg validation_err" id="validation_err"></span>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="firstName1">Mother Tongue</label>
                              <input name="mother_tongue" class="form-control uperletter" id="mother_tongue" type="text" placeholder="Enter Mother Tongue" value="<?php if (!empty($notificationData1['mother_tongue'])) {
                                                                                                                                                            echo $notificationData1['mother_tongue'];
                                                                                                                                                         } ?>" />
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="remark">Remarks</label>
                              <input name="remarks" class="form-control uperletter" id="remarks" type="text" placeholder="Enter remark" value="<?php if (!empty($notificationData1['remarks'])) {
                                                                                                                                       echo $notificationData1['remarks'];
                                                                                                                                    } ?>" />
                           </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="remark">Vaccaination</label>
                                        <input name="vaccaination" class="form-control" id="vaccaination" type="text" placeholder="Enter vaccaination" value="<?php if (!empty($notificationData1['vaccaination'])) {
                                                                                                                                       echo $notificationData1['vaccaination'];
                                                                                                                                    } ?>" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="address">Scholar SSSM ID :</label>
                                        <input class="form-control uperletter" id="sssmid" type="text" placeholder="Enter SSSMID " name="SSSMID" value="<?php if (!empty($notificationData1['SSSMID'])) {
                                                                                                                                       echo $notificationData1['SSSMID'];
                                                                                                                                    } ?>" maxlength="9" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check7();" /><span class="fathermobile_for_msg validation_err" id="validation_err7"></span>
                                    </div>

                                    <div class="col-md-4 form-group mb-3">
                                        <label for="family_SSSMID">Family SSSM ID :</label>
                                        <input class="form-control" id="family_SSSMID" type="text" placeholder="Enter SSSMID" value="<?php if (!empty($notificationData1['family_SSSMID'])) {
                                                                                                                                       echo $notificationData1['family_SSSMID'];
                                                                                                                                    } ?>" name="family_SSSMID" maxlength="8" pattern="\d{8}" onkeyup="this.value = this.value.replace(/[^0-9]/g, '');check8();" /><span class="fathermobile_for_msg validation_err" id="validation_err_family_sssmid"></span>
                                    </div>

                                    <div class="col-md-4 form-group mb-3">
                                        <label for="address">Aadhar No.:</label>
                                        <input class="form-control" id="AadharNo" type="text" placeholder="Enter Aadhar Number" name="AadharNo"  value="<?php if (!empty($notificationData1['AadharNo'])) {
                                                                                                                                       echo $notificationData1['AadharNo'];
                                                                                                                                    } ?>" maxlength="12" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check9();" /><span class="fathermobile_for_msg validation_err" id="validation_err9"></span>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="apaar_id">Apaar ID</label>
                                        <input class="form-control" id="apaar_id" type="text" placeholder="Enter Apaar ID" name="apaar_id" value="<?php if (!empty($notificationData1['apaar_id'])) {
                                                                                                                                       echo $notificationData1['apaar_id'];
                                                                                                                                    } ?>" maxlength="20" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="pen_no">PEN No.</label>
                                        <input class="form-control" id="pen_no" type="text" placeholder="Enter PEN No." name="pen_no" value="<?php if (!empty($notificationData1['pen_no'])) {
                                                                                                                                       echo $notificationData1['pen_no'];
                                                                                                                                    } ?>" maxlength="20" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="remark">Medical Conserns (any)</label>
                                        <input name='student_medical_conserns' class="form-control uperletter" value="<?php if (!empty($notificationData1['student_medical_conserns'])) {
                                                                                                                                       echo $notificationData1['student_medical_conserns'];
                                                                                                                                    } ?>" id="Medical Conserns (any)" type="text" placeholder="Enter Medical Conserns (any)" />
                                    </div>
                           <div class="col-md-12 form-group mb-3">
                           <label for="remark">Name of Present Play School / Day Care (if any)</label>
                                        <input name="present_school_name" class="form-control uperletter" id="Medical Conserns (any)" value="<?php if (!empty($notificationData1['present_school_name'])) {
                                                                                                                                       echo $notificationData1['present_school_name'];
                                                                                                                                    } ?>" type="text" placeholder="Enter Name of Present Play School / Day Care (if any)" />
                                        <br>
                                        <h3 class="border-bottom border-gray pb-2">Details of Siblings</h3>
                                        <div class="mt-1 form__field">
                                            <h6 class="field_wrapper">Do you have any siblings?
                                                <input class="coupon_question" type="checkbox" name="is_sibling_applied_for_admission" value="1" <?php if (!empty($notificationData1['is_sibling_applied_for_admission'])) {
                                                                                                                           echo "checked";
                                                                                                                        } ?> id="checkBox" />
                                                <span class="item-text">Yes</span>
                                            </h6>

                                            <div class="col-md-6 form-group mb-3 answer">
                                                <label for="formidsearch">Enquiry No Search (By - Father Name)</label>
                                                <input class="form-control uperletter" id="searchfather" placeholder="" name="searchfather"  value="<?php if (!empty($notificationData1['searchfather'])) {
                                                                                                                                       echo $notificationData1['searchfather'];
                                                                                                                                    } ?>" required />
                                                <span class="validation_err"></span><span class="error_msg validation_err"></span>
                                                <span class="error_msg3 validation_err3"></span>
                                            </div>
                                            <div class="col-md-6 form-group mb-3 answer">
                                                <label for="formidsearch"></label>
                                                <div class="col-md-12">
                                                    <button type="button" class="btn btn-primary pick_inq_data2" onclick="getsiblingbyfathersname();" data-form_number="" fdprocessedid="7t8lyh">Pick Data</button>
                                                </div>
                                            </div>
                                            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
                                            <div class="group_wrapper">

                                                <div class="gethtml">
                                                    <table id="itemTable" class="table answer">
                                                        <thead>
                                                        </thead>
                                                        <tbody class="field_wrapper">

                                                            <tr class="item">
                                                                <td>
                                                                    <label for="siblings_name">Name</label>
                                                                    <input class="form-control uperletter" id="siblings_namesecond" type="text" placeholder="Enter name" name="siblings_namesecond[0]" value="<?php if (!empty($notificationData1['siblings_namesecond'][0])) {
                                                                                                                                       echo $notificationData1['siblings_namesecond'][0];
                                                                                                                                    } ?>">
                                                                    <span class="siblings_namesecond_msg validation_err"></span>
                                                                </td>


                                                                <td>
                                                                  <label for="sibling_class_second">Class</label>
                                                                  <select id="sibling_class_second" class="form-control" name="sibling_class_second[0]" autocomplete="">
                                                                      {{-- @if(!empty($notificationData1)) --}}
                                                                      {{-- @if($notificationData1['sibling_class_second'][0] == $each) --}}
                                                                      @if(isset($notificationData1['sibling_class_second']) && is_array($notificationData1['sibling_class_second']) && count($notificationData1['sibling_class_second']) > 0)
                                                                      @if($notificationData1['sibling_class_second'][0] == $each)
                                                              
                                                                      <option value=""> -- Please select -- </option>
                                                                      @foreach(config('global.class_name') as $each)
                                                                          @if(!empty($notificationData1['sibling_class_second']))
                                                                              @if($notificationData1['sibling_class_second'][0] == $each)
                                                                              <option selected value="{{$each}}">{{$each}}</option>
                                                                              @else
                                                                              <option value="{{$each}}">{{$each}}</option>
                                                                              @endif
                                                                          @endif
                                                                      @endforeach
                                                                      @endif
                                                                      @else
                                                                      <option value="" disabled selected>Please select</option>
                                                                      @foreach(config('global.class_name') as $each)
                                                                          <option value="{{$each}}">{{$each}}</option>
                                                                      @endforeach
                                                                      @endif
                                                                  </select>
                                                                  <span class="sibling_class_second_msg validation_err"></span>
                                                              </td>
                                                              

                                                                 {{-- <td>
                                                                    <label for="siblings_school_second">Section</label>
                                                                    <input class="form-control" id="siblings_section_second" type="text" placeholder="Enter Section" name="siblings_section_second[0]" value="<//?php if (!empty($notificationData1['siblings_section_second'][0])) {
                                                                                                                                       echo $notificationData1['siblings_section_second'][0];
                                                                                                                                    } ?>">
                                                                    <span class="siblings_section_second_msg validation_err"></span>
                                                                </td>  --}}

                                                                <td>
                                                                  <label for="section_name">Section</label>
                                                                  <select id="siblings_section_name" class="form-control" name="siblings_section_second[0]" autocomplete="shipping address-level1">
                                                                                                                                  
                                                                      @if (!empty($teacher_subject))
                                                                      <option value="{{ $teacher_subject->section_name}}" selected>{{ $teacher_subject->section_name}}</option>
                                                                      @else
                                                              
                                                                      @endif
                                                                  </select>
                                                                </td>

                                                             



                                                                <td>
                                                                    <label for="siblings_school_second">Date Of Birth</label>
                                                                    <input class="form-control" id="picker2-" type="text" placeholder="dd-mm-yyyy" name="siblings_bod_second[0]" max="9999-12-31" value="<?php if (!empty($notificationData1['siblings_bod_second'][0])) {
                                                                                                                                       echo $notificationData1['siblings_bod_second'][0];
                                                                                                                                    } ?>">
                                                                    <span class="siblings_bod_second_msg validation_err"></span>
                                                                </td>
                                                            </tr>

                                                        </tbody>
                                                    </table>
                                                            <!-- <table id="itemTable" class="table answer">
                                                                <tbody class="field_wrapper">
                                                                    <tr class="item">
                                                                        <td><label for="siblings_name">Name</label>
                                                                            <input class="form-control" id="siblings_namesecond[]" type="text" placeholder="Enter name"  name="siblings_namesecond[]">                       
                                                                        </td>
                                                                        <td><label for="sibling_class_second">Class</label>
                                                                            <select id="sibling_class_second[]" class="form-control" name="sibling_class_second[]" autocomplete="" >
                                                                                <option value="" disabled selected>Please select</option>
                                                                                @foreach(config('global.class_name') as $each)
                                                                                <option value="{{$each}}">{{$each}}</option>
                                                                                @endforeach
                                                                            </select>                    
                                                                        </td>
                                                                        <td><label for="siblings_school_second">Section</label>
                                                                            <select id="siblings_section_second[]" class="form-control" name="siblings_section_second[]" autocomplete="" >
                                                                                <option value="" disabled selected>Please select</option>
                                                                                <option value="A">A</option>
                                                                                <option value="B">B</option>
                                                                                <option value="C">C</option>
                                                                                <option value="D">D</option>
                                                                            </select> 
                                                                            <input class="form-control" id="siblings_section_second[]" type="text" placeholder="Enter Section"  name="siblings_section_second[]">                  
                                                                        </td>
                                                                        <td><label for="siblings_school_second">Date Of Birth</label>
                                                                            <input class="form-control" type="date"  id="picker2-" type="text" placeholder="dd-mm-yyyy"  name="siblings_bod_second[]" max="9999-12-31">                
                                                                        </td>        
                                                                        <td><label for="siblings_school_second">Action</label><br><a href="javascript:void(0);" class="add_button btn btn-sm btn-primary" title="Add field"><i class="fa fa-plus"></i></a></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table> -->
                                                </div>
                                                <div class="result"></div>
                                            </div>
                                        </div>
                              <div class="mt-1 form__field">
                                 <label class="form__choice-wrapper">
                                    <input type="checkbox" class="is_driver_applied" name="required_school_transport"  value="1" <?php if (!empty($notificationData1['required_school_transport'])) {
                                                                                                                           echo "checked";
                                                                                                                        } ?>>


                                    <span>School Transport </span>
                                 </label>

                                 <div class="row has_diver_div" style="display:none;">
                                    <div class="customer_records">
                                       <div class="row">

                                          <div class="col-md-3 form-group mb-3">
                                             <label for="remark">Driver Name</label>
                                             <select name="driver_name" id="driver_name" class="form-control staff_selection" style="width:100%;">
                                                <option value=""> -- Please select -- </option>
                                                @php
                                                   $savedDriver = isset($notificationData1['driver_name']) ? trim($notificationData1['driver_name']) : '';
                                                   $driverFound = false;
                                                @endphp
                                                @if(!empty($drivername))
                                                   @foreach($drivername as $eachStudent)
                                                      @php
                                                         $dName = trim($eachStudent->ename);
                                                         if ($savedDriver !== '' && strcasecmp($savedDriver, $dName) === 0) {
                                                            $driverFound = true;
                                                         }
                                                      @endphp
                                                      @if($savedDriver !== '' && strcasecmp($savedDriver, $dName) === 0)
                                                         <option selected value="{{$dName}}">{{$dName}}</option>
                                                      @else
                                                         <option value="{{$dName}}">{{$dName}}</option>
                                                      @endif
                                                   @endforeach
                                                   @if($savedDriver !== '' && !$driverFound)
                                                      <option selected value="{{$savedDriver}}">{{$savedDriver}}</option>
                                                   @endif
                                                @endif
                                             </select>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input type="checkbox" class="is_staff_applied" name="is_staff_applied_for_admission" value="yes" <?php if (!empty($notificationData1['is_staff_applied_for_admission'])) {
                                                                                                                           echo "checked";
                                                                                                                        } ?>>
                                                <span>If a Staff (staff's child) also applying for
                                                    addmission into the
                                                    school. Please give Details.</span>
                                            </label>

                                            <div class="row has_staff_div" style="display:none;">
                                                <div class="customer_records">
                                                    <div class="row">

                                                        <div class="col-md-3 form-group mb-3">
                                                            <label for="remark">Staff Name</label>
                                                            <select name="staff_name[]" id="staff_name" class="form-control staff_selection" style="width:100%;">
                                                                 <option value="">Select Staff</option>
                                                                 @php
                                                                    $savedStaff = "";
                                                                    if (isset($notificationData1['staff_name'])) {
                                                                        if (is_array($notificationData1['staff_name'])) {
                                                                            $savedStaff = trim($notificationData1['staff_name'][0] ?? "");
                                                                        } else {
                                                                            $savedStaff = trim($notificationData1['staff_name']);
                                                                        }
                                                                    } elseif (!empty($each_inq->staff_name)) {
                                                                        $savedStaff = trim($each_inq->staff_name);
                                                                    }
                                                                    $staffFound = false;
                                                                 @endphp
                                                                 @if (!empty($staffList) && count($staffList) > 0)
                                                                     @foreach ($staffList as $eachStaff)
                                                                         @php
                                                                             $empName = trim($eachStaff->full_name);
                                                                             $isSelected = ($savedStaff !== "" && strcasecmp($savedStaff, $empName) === 0);
                                                                             if ($isSelected) {
                                                                                 $staffFound = true;
                                                                             }
                                                                         @endphp
                                                                         @if (!empty($empName))
                                                                             <option value="{{ $empName }}" {{ $isSelected ? 'selected' : '' }}>{{ $empName }}</option>
                                                                         @endif
                                                                     @endforeach
                                                                     @if ($savedStaff !== "" && !$staffFound && strcasecmp($savedStaff, 'Select Staff') !== 0)
                                                                         <option value="{{ $savedStaff }}" selected>{{ $savedStaff }}</option>
                                                                     @endif
                                                                 @elseif (!empty($all_inquiry))
                                                                     @foreach ($all_inquiry as $eachStudent)
                                                                         @if (isset($eachStudent->type) && $eachStudent->type == 't' && !empty($eachStudent->staff_name))
                                                                             <option value="{{ $eachStudent->staff_name }}" {{ ($savedStaff !== "" && strcasecmp($savedStaff, $eachStudent->staff_name) === 0) ? 'selected' : '' }}>{{ $eachStudent->staff_name }}</option>
                                                                         @endif
                                                                     @endforeach
                                                                 @endif
                                                             </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                              <div class="mt-1 form__field">
                                 <label class="form__choice-wrapper">
                                    <input id="birth-certificate" class="checked_birth_certificate" type="checkbox" name="birth_certificate_chk" value="yes" <?php if (!empty($notificationData1['birth_certificate_chk'])) {
                                                                                                                           echo "checked";
                                                                                                                        } ?>>
                                    <span>Birth Certificate</span>
                                 </label>
                                            <div class="row birth_certificate_input_div" style="display:none;">
                                            <input class="form-control" id="BirthCertificate" type="file" placeholder="Upload Birth Certificate" name="BirthCertificate" value="<?php if (!empty($notificationData1['BirthCertificate'])) {
                                                                                                                                       echo $notificationData1['BirthCertificate'];
                                                                                                                                    } ?>" />
                                                                                                                                    <input type="hidden" name="BirthCertificate1" value="<?php if (!empty($notificationData1['BirthCertificate'])) {
                                                                                                                                       echo $notificationData1['BirthCertificate'];
                                                                                                                                    } ?>"/>
                                                                                                                                     @if (!empty($notificationData1['BirthCertificate']))
        <p>Uploaded Birth Certificate: {{ $notificationData1['BirthCertificate'] }}</p>
    @else
        <p>No Birth Certificate uploaded.</p>
    @endif
                                            </div>
                              </div>
                              <div class="mt-1 form__field">
                                 <label class="form__choice-wrapper">
                                    <input id="birth-certificate" class="transfer_certificate" type="checkbox" name="transfer_certificate_chk" value="yes" <?php if (!empty($notificationData1['transfer_certificate_chk'])) {
                                                                                                                           echo "checked";
                                                                                                                        } ?>>
                                    <span>Transfer Certificate</span>
                                 </label>
                                            <div class="row transfer_certificate_input_div" style="display:none;">
                                                    <input class="form-control" id="TransferCertificate" type="file" placeholder="Upload Transfer Certificate" name="TransferCertificate"  value="<?php if (!empty($notificationData1['TransferCertificate'])) {
                                                                                                                                       echo $notificationData1['TransferCertificate'];
                                                                                                                                    } ?>" />
                                                                                                                                     <input type="hidden" name="TransferCertificate1" value="<?php if (!empty($notificationData1['TransferCertificate'])) {
                                                                                                                                       echo $notificationData1['TransferCertificate'];
                                                                                                                                    } ?>"/>
                                                                                                                                     @if (!empty($notificationData1['TransferCertificate']))
        <p>Uploaded Birth Certificate: {{ $notificationData1['TransferCertificate'] }}</p>
    @else
        <p>No Birth Certificate uploaded.</p>
    @endif
                                            </div>
                                        </div>
                              <div class="mt-1 form__field">
                                 <label class="form__choice-wrapper">
                                    <input id="birth-certificate" class="address_proof" type="checkbox" name="address_proof_chk" value="yes" <?php if (!empty($notificationData1['address_proof_chk'])) {
                                                                                                                           echo "checked";
                                                                                                                        } ?>>
                                    <span>Address Proff</span>
                                 </label>
                                 <div class="row address_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="AddressProff" type="file" placeholder="Upload Address Proff" name="AddressProff"  value="<?php if (!empty($notificationData1['AddressProff'])) {
                                                                                                                                       echo $notificationData1['AddressProff'];
                                                                                                                                    } ?>"/>
                                                                                                                                    <input type="hidden" name="AddressProff1" value="<?php if (!empty($notificationData1['AddressProff'])) {
                                                                                                                                       echo $notificationData1['AddressProff'];
                                                                                                                                    } ?>"/>
                                                                                                                                     @if (!empty($notificationData1['AddressProff']))
        <p>Uploaded Birth Certificate: {{ $notificationData1['AddressProff'] }}</p>
    @else
        <p>No Birth Certificate uploaded.</p>
    @endif
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="cast" type="checkbox" name="cast_chk" value="yes" <?php if (!empty($notificationData1['cast_chk'])) {
                                                                                                                           echo "checked";
                                                                                                                        } ?>>
                                                <span>Caste Certificate</span>
                                            </label>
                                            <div class="row cast_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="CastProff" type="file" placeholder="Upload Cast Proff" name="CastProff" value="<?php if (!empty($notificationData1['CastProff'])) {
                                                                                                                                       echo $notificationData1['CastProff'];
                                                                                                                                    } ?>"/>
                                                                                                                                    <input type="hidden" name="CastProff1" value="<?php if (!empty($notificationData1['CastProff'])) {
                                                                                                                                       echo $notificationData1['CastProff'];
                                                                                                                                    } ?>"/>
                                                                                                                                     @if (!empty($notificationData1['CastProff']))
        <p>Uploaded Birth Certificate: {{ $notificationData1['CastProff'] }}</p>
    @else
        <p>No Birth Certificate uploaded.</p>
    @endif
                                            </div>
                              </div>
                              <div class="mt-1 form__field">
                                 <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="aadhar" type="checkbox" name="aadhar_chk" value="yes" <?php if (!empty($notificationData1['aadhar_chk'])) {
                                                                                                                           echo "checked";
                                                                                                                        } ?>>
                                                <span>Aadhar Card</span>
                                            </label>
                                            <div class="row aadhar_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="aadharProff" type="file" placeholder="Upload Aadhar Proof" name="aadharProff" value="<?php if (!empty($notificationData1['aadharProff'])) {
                                                                                                                                       echo $notificationData1['aadharProff'];
                                                                                                                                    } ?>"/>
                                                                                                                                     <input type="hidden" name="aadharProff1" value="<?php if (!empty($notificationData1['aadharProff'])) {
                                                                                                                                       echo $notificationData1['aadharProff'];
                                                                                                                                    } ?>"/>
                                                                                                                                    @if (!empty($notificationData1['aadharProff']))
        <p>Uploaded Birth Certificate: {{ $notificationData1['aadharProff'] }}</p>
    @else
        <p>No Birth Certificate uploaded.</p>
    @endif
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="bankb" type="checkbox" name="bankb_chk" value="yes" <?php if (!empty($notificationData1['bankb_chk'])) {
                                                                                                                           echo "checked";
                                                                                                                        } ?>>
                                                <span>Bank Pass Book Copy</span>
                                            </label>
                                            <div class="row bankb_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="bankbProff" type="file" placeholder="Upload Bank Pass Book" name="bankbProff" value="<?php if (!empty($notificationData1['bankbProff'])) {
                                                                                                                                       echo $notificationData1['bankbProff'];
                                                                                                                                    } ?>" />
                                                                                                                                    <input type="hidden" name="bankbProff1" value="<?php if (!empty($notificationData1['bankbProff'])) {
                                                                                                                                       echo $notificationData1['bankbProff'];
                                                                                                                                    } ?>"/>
                                                                                                                                     @if (!empty($notificationData1['bankbProff']))
        <p>Uploaded Birth Certificate: {{ $notificationData1['bankbProff'] }}</p>
    @else
        <p>No Birth Certificate uploaded.</p>
    @endif
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="stuprof" type="checkbox" name="stuprof_chk" value="yes"<?php if (!empty($notificationData1['stuprof_chk'])) {
                                                                                                                           echo "checked";
                                                                                                                        } ?>>
                                                <span>Student Photo</span>
                                            </label>
                                            <div class="row stuprof_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="StuProf" type="file" placeholder="Upload Stu Photo" name="StuProf" value="<?php if (!empty($notificationData1['StuProf'])) {
                                                                                                                                       echo $notificationData1['StuProf'];
                                                                                                                                    } ?>"  />
                                                                                                                                    <input type="hidden" name="StuProf1" value="<?php if (!empty($notificationData1['StuProf'])) {
                                                                                                                                       echo $notificationData1['StuProf'];
                                                                                                                                    } ?>"/>
                                                                                                                                     @if (!empty($notificationData1['StuProf']))
        <p>Uploaded Birth Certificate: {{ $notificationData1['StuProf'] }}</p>
    @else
        <p>No Birth Certificate uploaded.</p>
    @endif
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="sssmprof" type="checkbox" name="sssmprof_chk" value="yes" <?php if (!empty($notificationData1['sssmprof_chk'])) {
                                                                                                                           echo "checked";
                                                                                                                        } ?>>
                                                <span>SSSM-ID/Family-ID</span>
                                            </label>
                                            <div class="row sssmprof_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="sssmprof" type="file" placeholder="Upload SSSM-ID/Family-ID" name="sssmprof" value="<?php if (!empty($notificationData1['StuProf'])) {
                                                                                                                                       echo $notificationData1['StuProf'];
                                                                                                                                    } ?>"  />
                                                                                                                                    <input type="hidden" name="sssmprof1" value="<?php if (!empty($notificationData1['sssmprof'])) {
                                                                                                                                       echo $notificationData1['sssmprof'];
                                                                                                                                    } ?>"/>
                                                                                                                                    @if (!empty($notificationData1['sssmprof']))
        <p>Uploaded Birth Certificate: {{ $notificationData1['sssmprof'] }}</p>
    @else
        <p>No Birth Certificate uploaded.</p>
    @endif
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="salaryprof" type="checkbox" name="salaryprof_chk" value="yes" <?php if (!empty($notificationData1['salaryprof_chk'])) {
                                                                                                                           echo "checked";
                                                                                                                        } ?>>
                                                <span>Salary Certificate/Income Tax Return</span>
                                            </label>
                                            <div class="row salaryprof_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="salaryprof" type="file" placeholder="Upload Stu Proff" name="salaryprof" value="<?php if (!empty($notificationData1['salaryprof'])) {
                                                                                                                                       echo $notificationData1['salaryprof'];
                                                                                                                                    } ?>"  />
                                                                                                                                    <input type="hidden" name="salaryprof1" value="<?php if (!empty($notificationData1['salaryprof'])) {
                                                                                                                                       echo $notificationData1['salaryprof'];
                                                                                                                                    } ?>"/>
                                                                                                                                    @if (!empty($notificationData1['salaryprof']))
        <p>Uploaded Birth Certificate: {{ $notificationData1['salaryprof'] }}</p>
    @else
        <p>No Birth Certificate uploaded.</p>
    @endif
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                    <input id="birth-certificate" class="last_report_card" type="checkbox" name="last_report_card_chk" value="yes" <?php if (!empty($notificationData1['last_report_card_chk'])) {
                                                                                                                           echo "checked";
                                                                                                                        } ?>>
                                    <span>Last Report Card</span>
                                 </label>
                                            <div class="row last_report_card_input_div" style="display:none;">
                                                    <input class="form-control" id="LastReportCard" type="file" placeholder="Upload Last Report Card" name="LastReportCard" value="<?php if (!empty($notificationData1['LastReportCard'])) {
                                                                                                                                       echo $notificationData1['LastReportCard'];
                                                                                                                                    } ?>"  />
                                                                                                                                     <input type="hidden" name="LastReportCard1" value="<?php if (!empty($notificationData1['LastReportCard'])) {
                                                                                                                                       echo $notificationData1['LastReportCard'];
                                                                                                                                    } ?>"/>
                                                                                                                                    @if (!empty($notificationData1['LastReportCard']))
        <p>Uploaded Birth Certificate: {{ $notificationData1['LastReportCard'] }}</p>
    @else
        <p>No Birth Certificate uploaded.</p>
    @endif
                                            </div>
                              </div>
                           </div>
                        </div>
                  </div>
               </div>
               <div id="step-2">
                  <h3 class="border-bottom border-gray pb-2">
                     Father's Details
                  </h3>
                  <div>
                     <div class="row">

                        <div class="col-lg-4 form-group mb-3">
                           <label for="fathername">Father Name</label>
                           <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                 <select id="fathername_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="fathername_prefix" autocomplete="shipping address-level1">
                                    <option <?php if (!empty($notificationData1['fathername_prefix'])) {
                                                if ($notificationData1['fathername_prefix'] == "Mr." || $notificationData1['fathername_prefix'] == "Mr") {
                                                   echo "selected";
                                                }
                                             } ?> value="Mr.">Mr.</option>
                                    <option <?php if (!empty($notificationData1['fathername_prefix'])) {
                                                if ($notificationData1['fathername_prefix'] == "Dr." || $notificationData1['fathername_prefix'] == "Dr") {
                                                   echo "selected";
                                                }
                                             } ?> value="Dr.">Dr.</option>
                                    <option <?php if (!empty($notificationData1['fathername_prefix'])) {
                                                if ($notificationData1['fathername_prefix'] == "Late") {
                                                   echo "selected";
                                                }
                                             } ?> value="Late">Late</option>
                                 </select>
                              </div>
                              <input class="form-control uperletter" id="fathername" type="text" placeholder="Enter your father name" name="fathername" value="<?php if (!empty($notificationData1['fathername'])) {
                                                                                                                                                                  echo $notificationData1['fathername'];
                                                                                                                                                               } ?>" />
                           </div>

                        </div>
                        <!-- <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Date Of Birth</label>
                           <input name="father_dob" class="form-control date4" id="picker2-" type="date" placeholder="dd-mm-yyyy" value="<?php if (!empty($notificationData1['father_dob'])) {
                                                                                                                                                                  echo $notificationData1['father_dob'];
                                                                                                                                                               } ?>">
                        </div> -->
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Education</label>
                           <input name="father_education" class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter Education" value="<?php if (!empty($notificationData1['father_education'])) {
                                                                                                                                                                                 echo $notificationData1['father_education'];
                                                                                                                                                                              } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="mothername">Occupation</label>
                           <input name="father_designation" class="form-control uperletter" id="fatheroccupation" type="text" placeholder="Enter father occupation" value="<?php if (!empty($notificationData1['father_designation'])) {
                                                                                                                                                                     echo $notificationData1['father_designation'];
                                                                                                                                                                  } ?>" />
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Organization</label>
                           <input name="father_organization" class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter Organization" value="<?php if (!empty($notificationData1['father_organization'])) {
                                                                                                                                                                                       echo $notificationData1['father_organization'];
                                                                                                                                                                                    } ?>">
                        </div>
                        
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Office Telephone</label>
                           <input name="father_office_telephone" class="form-control date4" id="picker2-" type="text" placeholder="Enter Office Telephone" value="<?php if (!empty($notificationData1['father_office_telephone'])) {
                                                                                                                                                                                          echo $notificationData1['father_office_telephone'];
                                                                                                                                                                                       } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Email ID</label>
                           <input name="father_email_id" class="form-control date4" id="picker2-" type="email" placeholder="Enter Email" value="<?php if (!empty($notificationData1['father_email_id'])) {
                                                                                                                                                   echo $notificationData1['father_email_id'];
                                                                                                                                                } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Mobile No.</label>
                           <input name="father_mobile" class="form-control date4" id="fathermobile" type="text" placeholder="Enter Mobile No" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check2();" value="<?php if (!empty($notificationData1['father_mobile'])) {
                                                                                                                                                                                                                                                                                       echo $notificationData1['father_mobile'];
                                                                                                                                                                                                                                                                                    } ?>" /><span class="fathermobile_for_msg validation_err" id="validation_err2"></span>
</div>                     
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">SSSM ID :</label>
                                    <input class="form-control" id="fathersssmid" type="text"  value="<?php if (!empty($notificationData1['fatherSSSMID'])) {echo $notificationData1['fatherSSSMID'];} ?>"  placeholder="Enter phone number" name="fatherSSSMID" maxlength="9" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check9();" /><span class="fathermobile_for_msg validation_err" id="validation_err9"></span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">Aadhar No.:</label>
                                    <input class="form-control" id="fatherAadharNo" type="text"  value="<?php if (!empty($notificationData1['fatherAadharNo'])) {echo $notificationData1['fatherAadharNo'];} ?>" placeholder="Enter Aadhar No." name="fatherAadharNo" maxlength="12" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check8();" /><span class="fathermobile_for_msg validation_err" id="validation_err8"></span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Residental Address</label>
                           <input class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter Residental Address" name="father_res_address" value="<?php if (!empty($notificationData1['father_res_address'])) {
                                                                                                                                                                     echo $notificationData1['father_res_address'];
                                                                                                                                                                  } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Emergency contact No.</label>
                           <input class="form-control date4" id="picker2-" type="text" placeholder="Enter Emergency contact No." name="father_emergency_contact" value="<?php if (!empty($notificationData1['father_emergency_contact'])) {
                                                                                                                                                                              echo $notificationData1['father_emergency_contact'];
                                                                                                                                                                           } ?>">
                        </div>
                        <h4>Mother's Details</h4>
                        <!-- <div class="col-md-4 form-group mb-3">
               <label for="pincode">Mother Name</label>
               <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter Mother Name" fdprocessedid="csh6vi" name="mother_name">
               </div> -->
                        <div class="col-lg-4 form-group mb-3">
                           <label for="mothername">Mother Name</label>
                           <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                 <select id="mothername_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="mothername_prefix" autocomplete="shipping address-level1">
                                    <option <?php if (!empty($notificationData1['mothername_prefix'])) {
                                                if ($notificationData1['mothername_prefix'] == "Mrs" || $notificationData1['mothername_prefix'] == "Mrs.") {
                                                   echo "selected";
                                                }
                                             } ?> value="Mrs">Mrs.</option>
                                    <option <?php if (!empty($notificationData1['mothername_prefix'])) {
                                                if ($notificationData1['mothername_prefix'] == "Dr" || $notificationData1['mothername_prefix'] == "Dr.") {
                                                   echo "selected";
                                                }
                                             } ?> value="Dr">Dr.</option>
                                    <option <?php if (!empty($notificationData1['mothername_prefix'])) {
                                                if ($notificationData1['mothername_prefix'] == "Late") {
                                                   echo "selected";
                                                }
                                             } ?> value="Late">Late</option>
                                 </select>
                              </div>
                              <input class="form-control uperletter" id="mother_name" name="mothername" type="text" placeholder="Enter your mother name" value="<?php if (!empty($notificationData1['mothername'])) {
                                                                                                                                                                     echo $notificationData1['mothername'];
                                                                                                                                                                  } ?>" />
                           </div>
                        </div>
                        <!-- <div class="col-md-4 form-group mb-3">
                           <label for="pincode">Date of Birth</label>
                           <input class="form-control" id="exampleInputEmail1" type="date" name="mother_dob" value="<?php if (!empty($notificationData1['mother_dob'])) {
                                                                                                                        echo $notificationData1['mother_dob'];
                                                                                                                     } ?>">
                        </div> -->
                        <div class="col-md-4 form-group mb-3">
                           <label for="pincode">Education</label>
                           <input class="form-control uperletter" id="exampleInputEmail1" type="text" placeholder="Enter Education" name="mother_education" value="<?php if (!empty($notificationData1['mother_education'])) {
                                                                                                                                                            echo $notificationData1['mother_education'];
                                                                                                                                                         } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="pincode">Occupation</label>
                           <input class="form-control uperletter" id="exampleInputEmail1" type="text" placeholder="Enter Mother Occupation" name="mother_ocupation" value="<?php if (!empty($notificationData1['mother_ocupation'])) {
                                                                                                                                                                     echo $notificationData1['mother_ocupation'];
                                                                                                                                                                  } ?>">
                        </div>

                        <div class="col-md-4 form-group mb-3">
                           <label for="pincode">Designation</label>
                           <input class="form-control uperletter" id="exampleInputEmail1" type="text" placeholder="Enter Mother Designation" name="mother_organization" value="<?php if (!empty($notificationData1['mother_organization'])) {
                                                                                                                                                                        echo $notificationData1['mother_organization'];
                                                                                                                                                                     } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Office Telephone</label>
                           <input class="form-control date4" id="picker2-" type="text" placeholder="Enter Office Telephone" name="mother_office_telephone" value="<?php if (!empty($notificationData1['mother_office_telephone'])) {
                                                                                                                                                                        echo $notificationData1['mother_office_telephone'];
                                                                                                                                                                     } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Email ID</label>
                           <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Mother Email" name="mother_email" value="<?php if (!empty($notificationData1['mother_email'])) {
                                                                                                                                                         echo $notificationData1['mother_email'];
                                                                                                                                                      } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="phone">Mobile No.</label>
                           <input class="form-control" id="mothermobile" placeholder="Enter Mobile No." name="mother_mobile" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check3();" value="<?php if (!empty($notificationData1['mother_mobile'])) {
                                                                                                                                                                                                                                                                     echo $notificationData1['mother_mobile'];
                                                                                                                                                                                                                                                                  } ?>" /><span class="mothermobile_for_msg validation_err" id="validation_err3"></span>
 </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">SSSM ID :</label>
                                    <input class="form-control" id="mothersssmid" type="text" placeholder="Enter SSSM ID" name="motherSSSMID" value="<?php if (!empty($notificationData1['motherSSSMID'])) {echo $notificationData1['motherSSSMID'];} ?>" maxlength="9" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check8();" /><span class="fathermobile_for_msg validation_err" id="validation_err8"></span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">Aadhar No.:</label>
                                    <input class="form-control" id="motherAadharNo" type="text" placeholder="Enter Aadhar No." name="motherAadharNo" value="<?php if (!empty($notificationData1['motherAadharNo'])) {echo $notificationData1['motherAadharNo'];} ?>" maxlength="12" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check8();" /><span class="fathermobile_for_msg validation_err" id="validation_err8"></span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Residental Address</label>
                           <input class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter Residental Address" name="mother_res_address" value="<?php if (!empty($notificationData1['mother_res_address'])) {
                                                                                                                                                                  echo $notificationData1['mother_res_address'];
                                                                                                                                                               } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Emergency contact No.</label>
                           <input class="form-control date4" id="picker2-" type="text" placeholder="Enter Emergency contact No" name="mother_emergency_contact" value="<?php if (!empty($notificationData1['mother_emergency_contact'])) {
                                                                                                                                                                           echo $notificationData1['mother_emergency_contact'];
                                                                                                                                                                        } ?>">
                        </div>
                        <h4>Guardian Details</h4>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Guardian Name</label>
                           <input class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter Guardian Name" name="guardian_name" value="<?php if (!empty($notificationData1['guardian_name'])) {
                                                                                                                                                         echo $notificationData1['guardian_name'];
                                                                                                                                                      } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Office Telephone</label>
                           <input class="form-control date4" id="picker2-" type="text" placeholder="Enter Office Telephone" name="guardian_office_telephone" value="<?php if (!empty($notificationData1['guardian_office_telephone'])) {
                                                                                                                                                                        echo $notificationData1['guardian_office_telephone'];
                                                                                                                                                                     } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Email ID</label>
                           <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Email ID" name="guardian_email_id" value="<?php if (!empty($notificationData1['guardian_email_id'])) {
                                                                                                                                                         echo $notificationData1['guardian_email_id'];
                                                                                                                                                      } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="phone">Mobile No.</label>
                           <input class="form-control" placeholder="Enter Mobile No." name="guardian_mobile" maxlength="10" id="guardianmobile" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check4();" value="<?php if (!empty($notificationData1['guardian_mobile'])) {
                                                                                                                                                                                                                                                                                                echo $notificationData1['guardian_mobile'];
                                                                                                                                                                                                                                                                                             } ?>" /><span class="guardianmobile_for_msg validation_err" id="validation_err4"></span>

                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="address">Permanent Address :</label>
                           <input class="form-control uperletter" id="address" type="text" placeholder="Enter Permanent Address" name="guardian_permanent_address" value="<?php if (!empty($notificationData1['guardian_permanent_address'])) {
                                                                                                                                                                  echo $notificationData1['guardian_permanent_address'];
                                                                                                                                                               } ?>" />
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Emergency Contact No.</label>
                           <input class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Emergency Contact No." name="guardian_emergency_contact" value="<?php if (!empty($notificationData1['guardian_emergency_contact'])) {
                                                                                                                                                                        echo $notificationData1['guardian_emergency_contact'];
                                                                                                                                                                     } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Guardian Relation.</label>
                           <input class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter Guardian Relation" name="guardian_relation" value="<?php if (!empty($notificationData1['guardian_relation'])) {
                                                                                                                                                                  echo $notificationData1['guardian_relation'];
                                                                                                                                                               } ?>">
                        </div>
                        <h4>Student Bank Details</h4>


                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Bank Name</label>
                                    <input class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter name" name="bankName" value="<?php if (!empty($notificationData1['bankName'])) {
                                                                                                                                                                  echo $notificationData1['bankName'];
                                                                                                                                                               } ?>" required>
                                </div>

                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Branch Name</label>
                                    <input class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter Branch" name="branchName" value="<?php if (!empty($notificationData1['branchName'])) {
                                                                                                                                                                  echo $notificationData1['branchName'];
                                                                                                                                                               } ?>" required>
                                </div>


                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Account Number</label>
                                    <input class="form-control date4" id="picker2-" type="text" placeholder="Enter Accont Number" name="account_number" value="<?php if (!empty($notificationData1['account_number'])) {
                                                                                                                                                                  echo $notificationData1['account_number'];
                                                                                                                                                               } ?>"  required>
                                </div>


                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">IFSC Code</label>
                                    <input class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter IFSC Code" name="ifsc_code"  value="<?php if (!empty($notificationData1['ifsc_code'])) {
                                                                                                                                                                  echo $notificationData1['ifsc_code'];
                                                                                                                                                               } ?>" required>
                                </div>
                     </div>
                     <span class="allinput_msg validation_err"></span>
                     <div class="col-md-12">
                        <button class="btn btn-primary submit_btn">Submit</button>
                     </div>

                  </div>

               </div>
            </div>
         </div>
         </form>
      </div>
      @endforeach
      @endif
   </div>
   <!-- end of main-content -->
</div>

<script type="text/javascript">
    $(window).ready(function() {
        $("#form-id").on("keypress", function(event) {
            console.log("aaya");
            var keyPressed = event.keyCode || event.which;
            if (keyPressed === 13) {
                //alert("You pressed the Enter key!!");
                event.preventDefault();
                return false;
            }
        });
    });
   function getValAndAssign(selectOption) {
      var form_number = selectOption.value;
      $('.pick_inq_data').attr('data-form_number', form_number);
   }

   /*Ajax for pick data from form no*/
   var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
   $(".pick_inq_data").on('click', function() {

      var form_number = $('.pick_inq_data').attr('data-form_number');

      $.ajax({
         url: '{{ url("getDataByFormNumber") }}',
         type: 'post',
         data: {
            _token: CSRF_TOKEN,
            form_number: form_number
         },
         // dataType: 'json',
         success: function(response) {

            console.log(response);
            if (response.inq_data) {
               /*assign value on pick data*/
               // $('select[name="application_for"] option[value='+response.inq_str_data.admission_type+']').attr("selected","selected");
               $('select[name="application_for"]').val(response.inq_str_data.admission_type);
               $('input[name="form_number"]').val(response.inq_data.form_number);
               $('select[name="studentname_prefix"]').val(response.inq_str_data.studentname_prefix);
               $('input[name="studentname"]').val(response.inq_data.student_name);
               if (response.inq_data.date_of_birth) {
                  var rawDobVal = response.inq_data.date_of_birth;
                  if (rawDobVal.match(/^\d{4}-\d{2}-\d{2}$/)) {
                     $('input[name="student_dob"]').val(rawDobVal);
                  } else {
                     var parts = rawDobVal.split(/[-\/\.]/);
                     if (parts.length === 3) {
                        var yPart = parts[2].length === 2 ? '20' + parts[2] : (parts[2].startsWith('00') ? '20' + parts[2].substr(2) : parts[2]);
                        $('input[name="student_dob"]').val(yPart + '-' + parts[1].padStart(2, '0') + '-' + parts[0].padStart(2, '0'));
                     } else {
                        $('input[name="student_dob"]').val(rawDobVal);
                     }
                  }
               }
               if (response.inq_str_data && response.inq_str_data.nationality) {
                  var natVal = response.inq_str_data.nationality;
                  if ($('select[name="nationality"] option[value="' + natVal + '"]').length === 0) {
                     $('select[name="nationality"]').append(new Option(natVal, natVal, true, true));
                  }
                  $('select[name="nationality"]').val(natVal);
               }
               $('select[name="gender"]').val(response.inq_str_data.gender);
               if (response.inq_str_data && (response.inq_str_data.caste || response.inq_str_data.student_caste)) {
                  var castePick = response.inq_str_data.student_caste || response.inq_str_data.caste;
                  if (castePick && castePick !== '0') {
                     if ($('select[name="student_caste"] option[value="' + castePick + '"]').length === 0) {
                        var newCasteOption = new Option(castePick, castePick, true, true);
                        $('select[name="student_caste"]').append(newCasteOption);
                     }
                     $('select[name="student_caste"]').val(castePick).trigger('change');
                  }
               }
               $('select[name="religion"]').val(response.inq_str_data.religion);
               $('select[name="category"]').val(response.inq_str_data.category);
               if (response.inq_data.class_name) {
                  if ($('select[name="classname"] option[value="' + response.inq_data.class_name + '"]').length === 0) {
                     $('select[name="classname"]').append(new Option(response.inq_data.class_name, response.inq_data.class_name, true, true));
                  }
                  $('select[name="classname"]').val(response.inq_data.class_name);
               }
               if (response.inq_data.session_name) {
                  if ($('select[name="session_name"] option[value="' + response.inq_data.session_name + '"]').length === 0) {
                     $('select[name="session_name"]').append(new Option(response.inq_data.session_name, response.inq_data.session_name, true, true));
                  }
                  $('select[name="session_name"]').val(response.inq_data.session_name);
               }
               if (response.inq_str_data && response.inq_str_data.batch) {
                  var bVal = response.inq_str_data.batch;
                  if ($('select[name="batch"] option[value="' + bVal + '"]').length === 0) {
                     $('select[name="batch"]').append(new Option(bVal, bVal, true, true));
                  }
                  $('select[name="batch"]').val(bVal);
               }
               $('input[name="present_address"]').val(response.inq_str_data.address);
               $('input[name="permanent_address"]').val(response.inq_str_data.address);
               $('input[name="phone_number"]').val(response.inq_str_data.fathermobile);
               $('input[name="mobile_number"]').val(response.inq_str_data.fathermobile);
               $('select[name="fathername_prefix"]').val(response.inq_str_data.fathername_prefix);
               $('input[name="fathername"]').val(response.inq_str_data.fathername);
               $('input[name="fathermobile"]').val(response.inq_str_data.father_mobile);
               $('input[name="father_res_address"]').val(response.inq_str_data.father_resi);
               $('select[name="mothername_prefix"]').val(response.inq_str_data.mothername_prefix);
               $('input[name="mothername"]').val(response.inq_str_data.mothername);
               $('input[name="mother_res_address"]').val(response.inq_str_data.mother_resi);
               $('input[name="mother_emergency_contact"]').val(response.inq_str_data.mother_mobile);
               $('input[name="remarks"]').val(response.inq_str_data.remarks);
               $('input[name="father_email_id"]').val(response.inq_str_data.email);
               $('input[name="father_mobile"]').val(response.inq_str_data.father_mobile);




            }

         }
      });

   });

   /* Step validation helpers */
   function showStepAlert(stepTitle, missingFields) {
      var msg = "Please complete the following required fields in " + stepTitle + " before proceeding:\n\n• " + missingFields.join("\n• ");
      alert(msg);
   }

   function focusFirstInvalid(elem) {
      if (elem && elem.length) {
         try {
            elem.focus();
            if (elem.offset()) {
               $('html, body').animate({ scrollTop: elem.offset().top - 120 }, 200);
            }
         } catch (err) {}
      }
   }

   function validateStep1() {
      var missing = [];
      var firstInvalid = null;

      var application_for = $('select[name="application_for"]').val();
      if (!application_for || application_for === '' || application_for === 'Please Select') {
         $('.application_for_msg').text("This field is required*");
         missing.push("Application Type");
         if (!firstInvalid) firstInvalid = $('select[name="application_for"]');
      } else {
         $('.application_for_msg').text("");
      }

      var registration_date = $('input[name="registration_date"]').val();
      if (!registration_date || registration_date.trim() === '') {
         $('.registration_date_msg').text("This field is required*");
         missing.push("Registration Date");
         if (!firstInvalid) firstInvalid = $('input[name="registration_date"]');
      } else {
         $('.registration_date_msg').text("");
      }

      var student_name = $('input[name="studentname"]').val() || $('input[name="student_name"]').val() || '';
      student_name = $.trim(student_name);
      if (!student_name) {
         $('.student_name_msg').text("This field is required*");
         missing.push("Student Name");
         if (!firstInvalid) firstInvalid = $('input[name="studentname"]');
      } else {
         $('.student_name_msg').text("");
      }

      var student_dob = $('input[name="student_dob"]').val() || '';
      student_dob = $.trim(student_dob);
      if (!student_dob) {
         $('.student_dob_msg').text("This field is required*");
         missing.push("Student Date of Birth");
         if (!firstInvalid) firstInvalid = $('input[name="student_dob"]');
      } else {
         $('.student_dob_msg').text("");
      }

      var nationality = $('select[name="nationality"], input[name="nationality"]').val() || '';
      nationality = $.trim(nationality);
      if (!nationality || nationality === '' || nationality === 'select' || nationality === 'Please select') {
         $('.nationality_msg').text("This field is required*");
         missing.push("Nationality");
         if (!firstInvalid) firstInvalid = $('select[name="nationality"]');
      } else {
         $('.nationality_msg').text("");
      }

      var gender = $('select[name="gender"]').val() || '';
      gender = $.trim(gender);
      if (!gender || gender === '' || gender === 'select' || gender === 'Please select') {
         $('.gender_msg').text("This field is required*");
         missing.push("Gender");
         if (!firstInvalid) firstInvalid = $('select[name="gender"]');
      } else {
         $('.gender_msg').text("");
      }

      // Note: Caste is optional, so student_caste is not required

      var religion = $('select[name="religion"]').val() || '';
      religion = $.trim(religion);
      if (!religion || religion === '' || religion === 'select' || religion === 'Please select') {
         $('.religion_msg').text("This field is required*");
         missing.push("Religion");
         if (!firstInvalid) firstInvalid = $('select[name="religion"]');
      } else {
         $('.religion_msg').text("");
      }

      var category = $('select[name="category"]').val() || '';
      category = $.trim(category);
      if (!category || category === '' || category === 'select' || category === 'Please select') {
         $('.category_msg').text("This field is required*");
         missing.push("Category");
         if (!firstInvalid) firstInvalid = $('select[name="category"]');
      } else {
         $('.category_msg').text("");
      }

      var classname = $('select[name="classname"]').val() || $('#sibling_class_second').val() || '';
      classname = $.trim(classname);
      if (!classname || classname === '' || classname === 'select' || classname === 'Please select') {
         $('.classname_msg').text("This field is required*");
         missing.push("Class Name");
         if (!firstInvalid) firstInvalid = $('select[name="classname"]');
      } else {
         $('.classname_msg').text("");
      }

      if (classname === '11' || classname === '12') {
         var streamId = $('#stream_id').val() || '';
         if (!streamId || streamId === '' || streamId === 'select') {
            $('.stream_id_msg').text("This field is required*");
            missing.push("Stream");
            if (!firstInvalid) firstInvalid = $('#stream_id');
         } else {
            $('.stream_id_msg').text("");
         }

         var combinationId = $('#combination_id').val() || '';
         if (!combinationId || combinationId === '' || combinationId === 'select') {
            $('.combination_id_msg').text("This field is required*");
            missing.push("Subject Combination");
            if (!firstInvalid) firstInvalid = $('#combination_id');
         } else {
            $('.combination_id_msg').text("");
         }
      } else {
         $('.stream_id_msg').text("");
         $('.combination_id_msg').text("");
      }

      var session_name = $('[name="session_name"]').val() || '';
      session_name = $.trim(session_name);
      if (!session_name || session_name === '' || session_name === 'select' || session_name === 'Please select') {
         $('.session_name_msg').text("This field is required*");
         missing.push("Session Name");
         if (!firstInvalid) firstInvalid = $('[name="session_name"]');
      } else {
         $('.session_name_msg').text("");
      }

      var batch = $('select[name="batch"]').val() || '';
      batch = $.trim(batch);
      if (!batch || batch === '' || batch === 'select' || batch === ' - Year - ') {
         $('.batch_msg').text("This field is required*");
         missing.push("Batch Name");
         if (!firstInvalid) firstInvalid = $('select[name="batch"]');
      } else {
         $('.batch_msg').text("");
      }

      var famSSSM = $('#family_SSSMID').val();
      if (famSSSM && famSSSM.trim() !== '') {
         var famClean = famSSSM.replace(/[^0-9]/g, '');
         if (famClean.length !== 8) {
            $('#validation_err_family_sssmid').text("Family SSSMID must be exactly 8 digits!");
            missing.push("Family SSSMID (must be exactly 8 digits)");
            if (!firstInvalid) firstInvalid = $('#family_SSSMID');
         } else {
            $('#validation_err_family_sssmid').text("");
         }
      } else {
         $('#validation_err_family_sssmid').text("");
      }

      return {
         isValid: missing.length === 0,
         missing: missing,
         firstInvalid: firstInvalid
      };
   }

   function validateStep2() {
      var missing = [];
      var firstInvalid = null;

      var bankName = $('input[name="bankName"]').val();
      if (!bankName || bankName.trim() === '') {
         missing.push("Bank Name");
         if (!firstInvalid) firstInvalid = $('input[name="bankName"]');
      }

      var branchName = $('input[name="branchName"]').val();
      if (!branchName || branchName.trim() === '') {
         missing.push("Branch Name");
         if (!firstInvalid) firstInvalid = $('input[name="branchName"]');
      }

      var accNo = $('input[name="account_number"]').val();
      if (!accNo || accNo.trim() === '') {
         missing.push("Account Number");
         if (!firstInvalid) firstInvalid = $('input[name="account_number"]');
      }

      var ifsc = $('input[name="ifsc_code"]').val();
      if (!ifsc || ifsc.trim() === '') {
         missing.push("IFSC Code");
         if (!firstInvalid) firstInvalid = $('input[name="ifsc_code"]');
      }

      return {
         isValid: missing.length === 0,
         missing: missing,
         firstInvalid: firstInvalid
      };
   }

   /* SmartWizard step validation on leaveStep */
   $("#smartwizard").on("leaveStep", function (e, anchorObject, stepNumber, stepDirection) {
      if (stepNumber === 0 && stepDirection === 'forward') {
         var check1 = validateStep1();
         if (!check1.isValid) {
            showStepAlert("Step 1 (Student Details)", check1.missing);
            focusFirstInvalid(check1.firstInvalid);
            return false;
         }
      }
   });

   /* Enforce Next button and Step 2 tab navigation validation */
   $(document).on('click', '.sw-btn-next, a[href="#step-2"]', function(e) {
      if ($('#step-1').is(':visible')) {
         var check1 = validateStep1();
         if (!check1.isValid) {
            e.preventDefault();
            e.stopImmediatePropagation();
            showStepAlert("Step 1 (Student Details)", check1.missing);
            focusFirstInvalid(check1.firstInvalid);
            return false;
         }
      }
   });

   /* Submit button validation */
   $(".submit_btn").on('click', function(e) {
      e.preventDefault();

      var check1 = validateStep1();
      if (!check1.isValid) {
         showStepAlert("Step 1 (Student Details)", check1.missing);
         if ($('#smartwizard').data('smartWizard')) {
            $('#smartwizard').smartWizard("goToStep", 0);
         } else {
            $('a[href="#step-1"]').trigger('click');
         }
         focusFirstInvalid(check1.firstInvalid);
         return false;
      }

      var check2 = validateStep2();
      if (!check2.isValid) {
         showStepAlert("Step 2 (Personal / Bank Details)", check2.missing);
         focusFirstInvalid(check2.firstInvalid);
         return false;
      }

      $('.allinput_msg').text("");
      var myForm = document.getElementById("form-id");
      HTMLFormElement.prototype.submit.call(myForm);
   });
   /*checkbox Actions*/
   // DOM ready checkbox actions
   // if ($(this).is(":checked")) {

   //    $('.has_siblings_div').show();

   // } else {

   //    $('.has_siblings_div').hide();
   // }
   if ($(this).is(":checked")) {

      $('.birth_certificate_input_div').show();

   } else {

      $('.birth_certificate_input_div').hide();
   }
   if ($(this).is(":checked")) {

      $('.transfer_certificate_input_div').show();

   } else {

      $('.transfer_certificate_input_div').hide();
   }
   if ($(this).is(":checked")) {

      $('.address_proof_input_div').show();

   } else {

      $('.address_proof_input_div').hide();
   }
    if ($(this).is(":checked")) {

        $('.cast_proof_input_div').show();

    } else {

        $('.cast_proof_input_div').hide();
    }
    if ($(this).is(":checked")) {

        $('.stuprof_proof_input_div').show();

    } else {

        $('.stuprof_proof_input_div').hide();
    }
    if ($(this).is(":checked")) {

        $('.salaryprof_proof_input_div').show();

    } else {

        $('.salaryprof_proof_input_div').hide();
    }
    if ($(this).is(":checked")) {

        $('.aadhar_proof_input_div').show();

    } else {

        $('.aadhar_proof_input_div').hide();
    }
    if ($(this).is(":checked")) {

        $('.sssmprof_proof_input_div').show();

    } else {

        $('.sssmprof_proof_input_div').hide();
    }
    if ($(this).is(":checked")) {

        $('.bankb_proof_input_div').show();

    } else {

        $('.bankb_proof_input_div').hide();
    }
   if ($(this).is(":checked")) {

      $('.last_report_card_input_div').show();

   } else {

      $('.last_report_card_input_div').hide();
   }

   // on check actions
   // $('.is_sibling_applied').click(function() {

   //    if ($(this).is(":checked")) {

   //       $('.has_siblings_div').show();

   //    } else {

   //       $('.has_siblings_div').hide();
   //    }

   // });
   $('.is_staff_applied').click(function() {

      if ($(this).is(":checked")) {

         $('.has_staff_div').show();

      } else {

         $('.has_staff_div').hide();
      }

   });



   $('.is_transport_applied').click(function() {

      if ($(this).is(":checked")) {

         $('.has_transport_div').show();

      } else {

         $('.has_transport_div').hide();
      }

   });









   $('.is_driver_applied').click(function() {

      if ($(this).is(":checked")) {

         $('.has_diver_div').show();

      } else {

         $('.has_diver_div').hide();
      }

   });

   $('.checked_birth_certificate').click(function() {

      if ($(this).is(":checked")) {

         $('.birth_certificate_input_div').show();

      } else {

         $('.birth_certificate_input_div').hide();
      }

   });

   $('.transfer_certificate').click(function() {

      if ($(this).is(":checked")) {

         $('.transfer_certificate_input_div').show();

      } else {

         $('.transfer_certificate_input_div').hide();
      }

   });

   $('.address_proof').click(function() {

      if ($(this).is(":checked")) {

         $('.address_proof_input_div').show();

      } else {

         $('.address_proof_input_div').hide();
      }

   });
    $('.sssmprof').click(function() {

    if ($(this).is(":checked")) {

        $('.sssmprof_proof_input_div').show();

    } else {

        $('.sssmprof_proof_input_div').hide();
    }

    });
    $('.bankb').click(function() {

    if ($(this).is(":checked")) {

        $('.bankb_proof_input_div').show();

    } else {

        $('.bankb_proof_input_div').hide();
    }

    });
    
    $('.aadhar').click(function() {

    if ($(this).is(":checked")) {

        $('.aadhar_proof_input_div').show();

    } else {

        $('.aadhar_proof_input_div').hide();
    }

    });
    
    $('.cast').click(function() {

    if ($(this).is(":checked")) {

        $('.cast_proof_input_div').show();

    } else {

        $('.cast_proof_input_div').hide();
    }

    });
    $('.stuprof').click(function() {

    if ($(this).is(":checked")) {

        $('.stuprof_proof_input_div').show();

    } else {

        $('.stuprof_proof_input_div').hide();
    }

    });
    $('.salaryprof').click(function() {

    if ($(this).is(":checked")) {

        $('.salaryprof_proof_input_div').show();

    } else {

        $('.salaryprof_proof_input_div').hide();
    }

    });
   $('.last_report_card').click(function() {

      if ($(this).is(":checked")) {

         $('.last_report_card_input_div').show();

      } else {

         $('.last_report_card_input_div').hide();
      }

   });

   /*checkbox*/



   var abc = 1;
   $('#sibling_name').on('change', function() {
      var name = $(this).val();
      // var save_status = $(this).val();
      //alert(name);

      var form_number = $(this).find("option:selected").data('form_number');
      $('#serial_number').val(form_number);
      // alert(form_number);
      var class_name = $(this).find("option:selected").data('class_name');
      $('#sibling_class_name').val(class_name);
      var scholar_number = $(this).find("option:selected").data('form_number');
      $('#scholar_number').val(scholar_number);

   });

   $('#staff_name').on('change', function() {
      var name = $(this).val();
      // var save_status = $(this).val();
      //alert(name);

      var form_number = $(this).find("option:selected").data('form_number');
      $('#serial_number').val(form_number);
      // alert(form_number);
      var class_name = $(this).find("option:selected").data('class_name');
      $('#sibling_class_name').val(class_name);
      var scholar_number = $(this).find("option:selected").data('form_number');
      $('#scholar_number').val(scholar_number);

   });
   $('#driver_name').on('change', function() {
      var name = $(this).val();
      // var save_status = $(this).val();
      //alert(name);

      var form_number = $(this).find("option:selected").data('form_number');
      $('#serial_number').val(form_number);
      // alert(form_number);
      var class_name = $(this).find("option:selected").data('class_name');
      $('#sibling_class_name').val(class_name);
      var scholar_number = $(this).find("option:selected").data('form_number');
      $('#scholar_number').val(scholar_number);

   });

   $('.extra-fields-customer').click(function() {
      $('.customer_records').clone().appendTo('.customer_records_dynamic');
      $('.customer_records_dynamic .customer_records').addClass('single remove');
      $('.single .extra-fields-customer').remove();
      $('.single').append('<a href="#" class="remove-field btn-remove-customer">Remove Row</a>');
      $('.customer_records_dynamic > .single').attr("class", "remove");

      $('.customer_records_dynamic :input').each(function() {
         $('#sibling_name' + abc).on('change', function() {
            var name = $(this).val();
            var form_number = $(this).find("option:selected").data('form_number');
            $('#serial_number' + abc).val(form_number);
            var sibling_class_name = $(this).find("option:selected").data('class_name');
            $('#sibling_class_name').val(sibling_class_name);
            var scholar_number = $(this).find("option:selected").data('scholar_number');
            $('#scholar_number').val(scholar_number);


         });
         var count = 0;
         var fieldname = $(this).attr("id");
         $(this).attr('id', fieldname + abc);
         count++;
      });
      //   abc++;
   });

   $(document).on('click', '.remove-field', function(e) {
      $(this).parent('.remove').remove();
      e.preventDefault();
   });



   /*Show date*/
   if ($.fn.datepicker) {
      $('.date, .date1, .date2, .date3, .date4, .date5').datepicker({
         format: 'dd-mm-yyyy'
      });
   }

   function check() {
      var mobile = document.getElementById('phonenumber');
      var message = document.getElementById('validation_err');
      if (mobile.value.length != 10) {
         message.innerHTML = "required 10 digits, match requested format!"
      } else {
         message.innerHTML = "";
      }
   }

   function check2() {
      var mobile = document.getElementById('fathermobile');
      var message2 = document.getElementById('validation_err2');
      if (mobile.value.length != 10) {
         message2.innerHTML = "required 10 digits, match requested format!"
      } else {
         message2.innerHTML = "";
      }
   }

   function check3() {
      var mobile = document.getElementById('mothermobile');
      var message3 = document.getElementById('validation_err3');
      if (mobile.value.length != 10) {
         message3.innerHTML = "required 10 digits, match requested format!"
      } else {
         message3.innerHTML = "";
      }
   }

   function check4() {
      var mobile = document.getElementById('guardianmobile');
      var message4 = document.getElementById('validation_err4');
      if (mobile.value.length != 10) {
         message4.innerHTML = "required 10 digits, match requested format!"
      } else {
         message4.innerHTML = "";
      }
   }

   function check5() {
      var mobile = document.getElementById('localmobile');
      var message5 = document.getElementById('validation_err5');
      if (mobile.value.length != 10) {
         message5.innerHTML = "required 10 digits, match requested format!"
      } else {
         message5.innerHTML = "";
      }
   }

   function check6() {
      var mobile = document.getElementById('phoneno');
      var message6 = document.getElementById('validation_err6');
      if (mobile.value.length != 10) {
         message6.innerHTML = "required 10 digits, match requested format!"
      } else {
         message6.innerHTML = "";
      }
   }

   function check7() {
      var mobile = document.getElementById('sssmid');
      var message = document.getElementById('validation_err7');
      if (mobile.value.length != 9) {
         message.innerHTML = "required 9 digits, match requested format!"
      } else {
         message.innerHTML = "";
      }
   }

   function check8() {
      var mobile = document.getElementById('family_SSSMID');
      var message = document.getElementById('validation_err_family_sssmid') || document.getElementById('validation_err8');
      if (mobile && message) {
         if (mobile.value.length != 8 && mobile.value.length != 0) {
            message.innerHTML = "Family SSSMID must be exactly 8 digits!";
         } else {
            message.innerHTML = "";
         }
      }
   }

   function check9() {
      var mobile = document.getElementById('AadharNo');
      var message = document.getElementById('validation_err9');
      if (mobile.value.length != 12) {
         message.innerHTML = "required 12 digits, match requested format!"
      } else {
         message.innerHTML = "";
      }
   }
   $(window).ready(function() {
      $("#form-id").on("keypress", function(event) {
         console.log("aaya");
         var keyPressed = event.keyCode || event.which;
         if (keyPressed === 13) {
            //alert("You pressed the Enter key!!");
            event.preventDefault();
            return false;
         }
      });
   });
</script>
<script type="text/javascript">
   jQuery(document).ready(function($) {
      if ($.fn.select2) {
         $("#inq-form-no").select2({ width: '100%' });
         $("#sibling_name").select2({ width: '100%' });
         $("#Caste.select2").select2({
            width: '100%',
            placeholder: 'Please select caste',
            allowClear: true
         });
         $("#Caste").on('change', function() {
            if ($(this).val()) {
               $('.student_caste_msg').text("");
            }
         });
      }

      $('input[name="studentname"], input[name="student_dob"], select[name="nationality"], select[name="category"], select[name="classname"], select[name="session_name"], select[name="batch"], select[name="student_caste"], select[name="religion"], select[name="application_for"], input[name="registration_date"]').on('input change', function() {
         var name = $(this).attr('name');
         if (name === 'studentname') $('.student_name_msg').text('');
         if (name === 'student_dob') $('.student_dob_msg').text('');
         if (name === 'nationality') $('.nationality_msg').text('');
         if (name === 'category') $('.category_msg').text('');
         if (name === 'classname') $('.classname_msg').text('');
         if (name === 'session_name') $('.session_name_msg').text('');
         if (name === 'batch') $('.batch_msg').text('');
         if (name === 'student_caste') $('.student_caste_msg').text('');
         if (name === 'religion') $('.religion_msg').text('');
         if (name === 'application_for') $('.application_for_msg').text('');
         if (name === 'registration_date') $('.registration_date_msg').text('');
      });

      var initialStreamId = @json($assignedStreamId ?? null);
      var initialCombinationId = @json($assignedCombinationId ?? null);

      function initCombinationSelect2() {
         if ($.fn.select2) {
            var comb = $('#combination_id');
            if (comb.hasClass('select2-hidden-accessible')) {
               comb.select2('destroy');
            }
            comb.select2({
               width: '100%',
               placeholder: 'Please select Subject Combination',
               allowClear: true,
               minimumResultsForSearch: 0
            });
         }
      }

      function handleSeniorClassStreamSelection(selectedClass, preselectedStreamId, preselectedCombinationId) {
         selectedClass = (selectedClass || '').trim();
         if (selectedClass === '11' || selectedClass === '12') {
            $('#stream_selection_wrapper').slideDown(200);
            $('#combination_selection_wrapper').slideDown(200, function() {
               initCombinationSelect2();
            });
            loadClassStreams(selectedClass, preselectedStreamId, preselectedCombinationId);
         } else {
            $('#stream_selection_wrapper').slideUp(200);
            $('#combination_selection_wrapper').slideUp(200);
            $('#stream_id').empty().append(new Option('Please select Stream', ''));
            var comb = $('#combination_id');
            if ($.fn.select2 && comb.hasClass('select2-hidden-accessible')) {
               comb.select2('destroy');
            }
            comb.empty().append(new Option('Please select Subject Combination', ''));
            initCombinationSelect2();
            $('.stream_id_msg').text('');
            $('.combination_id_msg').text('');
         }
      }

      function loadClassStreams(selectedClass, preselectedStreamId, preselectedCombinationId) {
         var streamSelect = $('#stream_id');
         streamSelect.empty().append(new Option('Loading streams...', ''));
         var comb = $('#combination_id');
         if ($.fn.select2 && comb.hasClass('select2-hidden-accessible')) {
            comb.select2('destroy');
         }
         comb.empty().append(new Option('Please select Stream first', ''));
         initCombinationSelect2();

         $.ajax({
            url: '{{ url("academic/class-streams") }}',
            type: 'GET',
            data: { class_name: selectedClass },
            success: function(res) {
               streamSelect.empty();
               streamSelect.append(new Option('Please select Stream', ''));
               if (res && res.streams && res.streams.length > 0) {
                  res.streams.forEach(function(s) {
                     var opt = new Option(s.stream_name, s.id);
                     streamSelect.append(opt);
                  });
                  if (preselectedStreamId) {
                     streamSelect.val(preselectedStreamId);
                     loadStreamCombinations(selectedClass, preselectedStreamId, preselectedCombinationId);
                  }
               } else {
                  streamSelect.append(new Option('No streams available for Class ' + selectedClass, ''));
               }
            },
            error: function() {
               streamSelect.empty().append(new Option('Failed to load streams', ''));
            }
         });
      }

      function loadStreamCombinations(selectedClass, streamId, preselectedCombinationId) {
         var combSelect = $('#combination_id');
         if ($.fn.select2 && combSelect.hasClass('select2-hidden-accessible')) {
            combSelect.select2('destroy');
         }
         combSelect.empty().append(new Option('Loading combinations...', ''));
         initCombinationSelect2();

         $.ajax({
            url: '{{ url("academic/stream-combinations") }}',
            type: 'GET',
            data: { class_name: selectedClass, stream_id: streamId },
            success: function(res) {
               if ($.fn.select2 && combSelect.hasClass('select2-hidden-accessible')) {
                  combSelect.select2('destroy');
               }
               combSelect.empty();
               combSelect.append(new Option('Please select Subject Combination', ''));
               if (res && res.combinations && res.combinations.length > 0) {
                  res.combinations.forEach(function(c) {
                     var label = c.combination_name;
                     if (c.alise_name && c.alise_name.trim() !== '') {
                        label += ' (' + c.alise_name + ')';
                     }
                     var opt = new Option(label, c.id);
                     combSelect.append(opt);
                  });
                  if (preselectedCombinationId) {
                     combSelect.val(preselectedCombinationId);
                  }
               } else {
                  combSelect.append(new Option('No combinations available for this stream', ''));
               }
               initCombinationSelect2();
            },
            error: function() {
               if ($.fn.select2 && combSelect.hasClass('select2-hidden-accessible')) {
                  combSelect.select2('destroy');
               }
               combSelect.empty().append(new Option('Failed to load combinations', ''));
               initCombinationSelect2();
            }
         });
      }

      $('#classname').on('change', function() {
         var val = $(this).val();
         handleSeniorClassStreamSelection(val);
      });

      $('#stream_id').on('change', function() {
         var streamId = $(this).val();
         var selectedClass = $('#classname').val();
         var isSenior = (selectedClass === '11' || selectedClass === '12');
         if (isSenior) {
            var streamText = $('#stream_id option:selected').text().toUpperCase();
            if (streamText.indexOf('SCIENCE') !== -1 || streamId == 3) {
               $('#student_section_name').val('Kautilya').trigger('change');
            } else if (streamText.indexOf('COMMERCE') !== -1 || streamText.indexOf('HUMANITIES') !== -1 || streamId == 1 || streamId == 2) {
               $('#student_section_name').val('Ramanujan').trigger('change');
            }
         }
         if (streamId) {
            $('.stream_id_msg').text('');
            loadStreamCombinations(selectedClass, streamId);
         } else {
            var comb = $('#combination_id');
            if ($.fn.select2 && comb.hasClass('select2-hidden-accessible')) {
               comb.select2('destroy');
            }
            comb.empty().append(new Option('Please select Stream first', ''));
            initCombinationSelect2();
         }
      });

      $('#combination_id').on('change', function() {
         if ($(this).val()) {
            $('.combination_id_msg').text('');
         }
      });

      if ($('#classname').val()) {
         handleSeniorClassStreamSelection($('#classname').val(), initialStreamId, initialCombinationId);
      }
   });
</script>

<script>

document.addEventListener("DOMContentLoaded", function() {

function showSections() {
    var selectedClass = $("#sibling_class_second").val(); 
    var token = document.getElementsByName("_token")[0].value;

// alert("error");

    if (selectedClass) {
        $.ajax({
            data: { id: selectedClass },
            url: `{{url('classsection-view')}}/${selectedClass}`, 
            method: "POST",
            headers: { 'X-CSRF-TOKEN': token },
            dataType: 'json',
            success: function(data) {
                $('#siblings_section_name').html('<option value=""> -- Select All -- </option>');
                for (var i = 0; i < data.length; i++) {
                    var sectionData = data[i].section_name;
                    $('#siblings_section_name').append('<option value="' + sectionData + '">' + sectionData + '</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    } else {
        $('#siblings_section_name').html('<option value="">Select class first</option>');
    }
}


$('#sibling_class_second').on('change', showSections);


showSections();
});

function getsiblingbyfathers() {
   var form_number = $("#searchfather").val();
   $('.pick_inq_data2').attr('data-form_number', form_number);
}

 /*Ajax for pick data from form no*/
 var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
  $(".pick_inq_data2").on('click', function() {

    var form_number = $('#searchfather').val();
    var token = document.getElementsByName("_token")[0].value;
    $.ajax({
      url: '{{ url("getsiblingbyfathersname") }}',
      type: 'post',
      data: {
        form_number: form_number
      },
      headers: { 'X-CSRF-TOKEN': token },
      dataType: 'html',
      success: function(response) {
        console.log(response);
        $('.gethtml').html(response);
        if (response.inq_data) {

        }

      },
      error: function() {
        // alert('There was some error performing the AJAX call!');

      }
    });

  });




</script>



@endsection