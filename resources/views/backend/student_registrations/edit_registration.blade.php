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
      <?php $notificationData1 = json_decode($each_inq->json_str, true);
      // echo"<pre>";print_r($notificationData1);exit;
      ?>
      <div class="col-md-12">
         <!--  SmartWizard html -->
         <div id="smartwizard">
            <ul>
               <li><a href="#step-1">Step 1<br /><small>Student Details</small></a></li>
               <li>
                  <a href="#step-2">Step 2<br /><small>Personal Details</small></a>
               </li>
               <!--  <li>
                  <a href="#step-3"
                     >Step 3<br /><small>Details of Siblings</small></a
                     >
                  </li>
                  <li>
                  <a href="#step-4"
                     >Step 4<br /><small>Bank Details</small></a
                     >
                  </li> -->
            </ul>
            <div>

               <div id="step-1">
                  <h3 class="border-bottom border-gray pb-2">
                     Student Registration Form
                  </h3>
                  <div class="form_section1_div">
                     <form novalidate="novalidate" method="post" action="{{url('saveeditregistration')}}" class="rg_form" id="form-id" enctype="multipart/form-data">
                        @csrf
                        <input name="scholar_no" class="form-control "  value="<?php if(!empty($notificationData1['scholar_no'])){ echo $notificationData1['scholar_no'];} ?>" id="scholar_no" placeholder="Scholar Number" type="hidden" />
                        <input name="received_amount" class="form-control " value="<?php if(!empty($notificationData1['received_amount'])){ echo $notificationData1['received_amount'];} ?>" id="received_amount" placeholder="Scholar Number" type="hidden" />
                        <input name="amount" class="form-control " value="<?php if(!empty($notificationData1['amount'])){ echo $notificationData1['amount'];} ?>" id="amount" placeholder="Scholar Number" type="hidden" />

                        <input type="hidden" name="bus_facility_start_date" value="<?php if(!empty($notificationData1['bus_facility_start_date'])){ echo $notificationData1['bus_facility_start_date'];} ?>">
                        <input type="hidden" name="bus_facility_end_date" value="<?php if(!empty($notificationData1['bus_facility_end_date'])){ echo $notificationData1['bus_facility_end_date'];} ?>">
                        <div class="row">
                           <input type="hidden" name="id" value="<?php echo $each_inq->id; ?>">
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
								<label for="registration_date">Registration Date</label>
								<input type="date" name="registration_date" class="form-control" id="registration_date" 
									   value="{{ $each_inq->registration_date }}" 
									   required>
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
                                    <select id="studentname_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="studentname_prefix" autocomplete="shipping address-level1">
                                       <option <?php if (!empty($notificationData1['studentname_prefix'])) {
                                                   if ($notificationData1['studentname_prefix'] == "Master") {
                                                      echo "select";
                                                   }
                                                } ?> value="Master">Master</option>
                                       <option <?php if (!empty($notificationData1['studentname_prefix'])) {
                                                   if ($notificationData1['studentname_prefix'] == "Mr.") {
                                                      echo "select";
                                                   }
                                                } ?> value="Mr.">Mr.</option>
                                       <option <?php if (!empty($notificationData1['studentname_prefix'])) {
                                                   if ($notificationData1['studentname_prefix'] == "Miss") {
                                                      echo "select";
                                                   }
                                                } ?> value="Miss">Miss</option>
                                    </select>
                                 </div>
                                 <input type="text" class="form-control uperletter" id="studentname" placeholder="Enter Student Name" name="studentname" required value="<?php if (!empty($notificationData1['studentname'])) {
                                                                                                                                                      echo $notificationData1['studentname'];
                                                                                                                                                   } ?>" />
                                 <span class="student_name_msg validation_err"></span>
                              </div>
                                                                                                                                                                          
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="studentname">Student DOB<b class="validation_err">*</b></label>
                              <input type="date" name="student_dob" class="form-control" id="student_dob" placeholder="Enter Student DOB" value="<?php if (!empty($notificationData1['student_dob'])) {
                                                                                                                                                      echo $notificationData1['student_dob'];
                                                                                                                                                   } ?>" />
                              <span class="student_dob_msg validation_err"></span>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="studentname">Nationality<b class="validation_err">*</b></label>
                              <input name="nationality" class="form-control uperletter" id="nationality" placeholder="Enter Nationality" value="<?php if (!empty($notificationData1['nationality'])) {
                                                                                                                                          echo $notificationData1['nationality'];
                                                                                                                                       } ?>" />
                              <span class="student_dob_msg validation_err"></span>
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
                              <label for="Caste">Caste<b class="validation_err">*</b></label>
                              <!-- <input class="form-control Caste" id="Caste" placeholder="Student Caste" name="student_caste" /> -->
                              <select id="Caste" class="form-control Caste" name="student_caste" autocomplete="">
                                 <option value="" disabled selected>Please select</option>
                                 @foreach(config('global.caste') as $each)
                                 <option <?php if (!empty($notificationData1['student_caste'])) {
                                             if ($notificationData1['student_caste'] == $each) {
                                                echo "selected";
                                             }
                                          } ?> value="{{$each}}">{{$each}}</option>
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
                           {{-- <div class="col-md-4 form-group mb-3">
                              <label for="category">Category<b class="validation_err">*</b></label>
                              <select id="category" class="form-control" name="category" autocomplete="" required>
                                 <option value="" disabled selected>Please select</option>
                                 @foreach(config('global.cate') as $each)
                                 <option </?php if ($notificationData1['category'] == $each) {
                                             echo "selected";
                                          } ?> value="{{$each}}">{{$each}}</option>
                                 @endforeach
                              </select>
                              <span class="category_msg validation_err"></span>
                           </div> --}}


                           <div class="col-md-4 form-group mb-3">
                              <label for="category">Category<b class="validation_err">*</b></label>
                              <select id="category" class="form-control" name="category" autocomplete="" required>
                                  <option value="" disabled selected>Please select</option>
                                  @foreach(config('global.cate') as $each)
                                      <option value="{{$each}}" {{ isset($notificationData1['category']) && $notificationData1['category'] == $each ? 'selected' : '' }}>
                                          {{$each}}
                                      </option>
                                  @endforeach
                              </select>
                              <span class="category_msg validation_err"></span>
                          </div>
                
                           <div class="col-md-4 form-group mb-3">
                              <label for="firstName1">Class Name<b class="validation_err">*</b></label>
                              <select id="classname" class="form-control" name="classname" autocomplete="" required>
                                 <option disabled selected>Please select</option>
                                 @foreach(config('global.class_name') as $each)
                                 <option <?php if ($each_inq->class_name == $each) {
                                             echo "selected";
                                          } ?> value="{{$each}}">{{$each}}</option>
                                 @endforeach
                              </select>
                              <span class="classname_msg validation_err"></span>

                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="firstName1">Session Name<b class="validation_err">*</b></label>
                              <input type="text" readonly id="session_name" class="form-control" value="" name="session_name">
                              <!-- <select id="session_name" class="form-control" name="session_name" autocomplete="" required>
                                 <option disabled selected>Please select</option>
                                 @foreach(config('global.session_name') as $each)
                                 <option <?php //if ($each_inq->session_name == $each) {
                                             //echo "selected";
                                          //} ?> value="{{$each}}">{{$each}}</option>
                                 @endforeach
                              </select> -->
                              <span class="session_name_msg validation_err"></span>

                           </div>
                           <div class="col-md-4 form-group mb-3">
                                        <label for="firstName1">Batch Name<b class="validation_err">*</b></label>
                                        <!-- <input type="text" readonly id="session_name" class="form-control" value="" name="session_name"> -->
                                        <?php 
                                            $currentYear = date('Y') + 1;
                                            $nextYear = date('Y') + 2;
                                            $nextSchoolYear = $currentYear . '_' . $nextYear;
                                            $currentSchoolYear = session('db_names');
                                            
                                        ?>
                                        
                                        <select name="batch" id="batch" class="form-control" >
                                            <option value="select" selected> - Year - </option>
                                            @foreach($databaseNames as $databaseName)
                                                @if (is_numeric(substr($databaseName, 0, 1)))
                                                   <?php 
                                                      if(!empty($notificationData1['batch'])){ ?>
                                                      <?php if($notificationData1['batch'] == $databaseName){ ?>
                                                         <option selected value=<?php echo $databaseName; ?> ><?php echo $databaseName; ?></option>
                                                      <?php } else { ?>
                                                         <option value=<?php echo $databaseName; ?> ><?php echo $databaseName; ?></option>
                                                      <?php }?>
                                                   <?php } else { ?>
                                                      <option value=<?php echo $databaseName; ?> ><?php echo $databaseName; ?></option>
                                                   <?php } ?>
                                                   
                                                @endif
                                            @endforeach
                                            <!-- <option value="<?php //echo $nextSchoolYear; ?>"><?php //echo $nextSchoolYear; ?></option> -->
                                        </select>
                                        <span class="session_name_msg validation_err"></span>

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
                                                                                                                                                      } ?>" />
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="address">Mobile Number :</label>
                              <input class="form-control" id="phonenumber" type="text" placeholder="Enter Mobile Number" name="mobile_number" value="<?php if (!empty($notificationData1['mobile_number'])) {
                                                                                                                                                         echo $notificationData1['mobile_number'];
                                                                                                                                                      } ?>" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check();" name="fathermobile" return false; /><span class="phonenumber_for_msg validation_err" id="validation_err"></span>
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
                                                                                                                                    } ?>" maxlength="9" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check7();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err7"></span>
                                    </div>

                                    <div class="col-md-4 form-group mb-3">
                                        <label for="address">Family SSSM ID :</label>
                                        <input class="form-control" id="family_SSSMID" type="text" placeholder="Enter SSSMID"  value="<?php if (!empty($notificationData1['family_SSSMID'])) {
                                                                                                                                       echo $notificationData1['family_SSSMID'];
                                                                                                                                    } ?>" name="family_SSSMID" maxlength="9" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check8();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err7"></span>
                                    </div>

                                    <div class="col-md-4 form-group mb-3">
                                        <label for="address">Aadhar No.:</label>
                                        <input class="form-control" id="AadharNo" type="text" placeholder="Enter Aadhar Number" name="AadharNo"  value="<?php if (!empty($notificationData1['AadharNo'])) {
                                                                                                                                       echo $notificationData1['AadharNo'];
                                                                                                                                    } ?>" maxlength="12" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check9();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err9"></span>
                                    </div>
                                    <div class="col-md-6 form-group mb`-3">
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
                                            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
                                            <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.2/css/bootstrap.min.css" rel="stylesheet"/> -->
                                            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />

                                            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.0/js/bootstrap.min.js"></script>
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
                                                                  <select id="section_name" class="form-control" name="section_name" autocomplete="shipping address-level1" required>
                                                                                                                                  
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
                                                @if(!empty($notificationData1))
                                                <option value=""> -- Please select -- </option>

                                                @foreach($drivername as $eachStudent)
                                                {{-- @if($notificationData1['driver_name'] == $eachStudent->ename) --}}
                                                @if(isset($notificationData1['driver_name']) && $notificationData1['driver_name'] == $eachStudent->ename)

                                                <option selected value="{{$eachStudent->ename}}">{{$eachStudent->ename}}</option>
                                                @else
                                                <option value="{{$eachStudent->ename}}">{{$eachStudent->ename}}</option>
                                                @endif
                                                @endforeach
                                                @else
                                                <option value="" selected> -- Please select -- </option>
                                                @foreach($drivername as $eachStudent)
                                                <option value="{{$eachStudent->ename}}">{{$eachStudent->ename}}</option>
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
                                                                @if(!empty($notificationData1))
                                                                                                                        
                                                                <option>Select Staff</option>
                                                                <?php
                                                                foreach ($all_inquiry as $eachStudent) {
                                                                  if(isset($notificationData1['staff_name']) && $notificationData1['staff_name'] == $eachStudent->staff_name){
                                                                    // Check if the 'type' column has the value 't'
                                                                    if ($eachStudent->type == 't') {
                                                                        echo '<option value="' . $eachStudent->staff_name . '" data-form_number="' . $eachStudent->form_number . '" data-class_name="' . $eachStudent->class_name . '">' . $eachStudent->staff_name . '</option>';
                                                                    }}else{
                                                                     if ($eachStudent->type == 't') {
                                                                        echo '<option value="' . $eachStudent->staff_name . '" data-form_number="' . $eachStudent->form_number . '" data-class_name="' . $eachStudent->class_name . '">' . $eachStudent->staff_name . '</option>';
                                                                    }
                                                                    }
                                                                }?>                                                                
                                                                @else
                                                                <?php
                                                                  if ($eachStudent->type == 't') {
                                                                     echo '<option value="' . $eachStudent->staff_name . '" data-form_number="' . $eachStudent->form_number . '" data-class_name="' . $eachStudent->class_name . '">' . $eachStudent->staff_name . '</option>';
                                                                 }
                                                                ?>
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
                                                if ($notificationData1['fathername_prefix'] == "Mr.") {
                                                   echo "select";
                                                }
                                             } ?> value="Mr.">Mr.</option>
                                    <option <?php if (!empty($notificationData1['fathername_prefix'])) {
                                                if ($notificationData1['fathername_prefix'] == "Dr.") {
                                                   echo "select";
                                                }
                                             } ?> value="Dr">Dr.</option>
                                    <option <?php if (!empty($notificationData1['fathername_prefix'])) {
                                                if ($notificationData1['fathername_prefix'] == "Late") {
                                                   echo "select";
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
                           <input name="father_dob" class="form-control date4" id="picker2-" type="date" placeholder="dd-mm-yyyy" name="siblings_bod" value="<?php if (!empty($notificationData1['father_dob'])) {
                                                                                                                                                                  echo $notificationData1['father_dob'];
                                                                                                                                                               } ?>">
                        </div> -->
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Education</label>
                           <input name="father_education" class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter Education" name="father_education" value="<?php if (!empty($notificationData1['father_education'])) {
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
                           <input name="father_organization" class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter Organization" name="father_education" value="<?php if (!empty($notificationData1['father_organization'])) {
                                                                                                                                                                                       echo $notificationData1['father_organization'];
                                                                                                                                                                                    } ?>">
                        </div>
                        
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Office Telephone</label>
                           <input name="father_office_telephone" class="form-control date4" id="picker2-" type="text" placeholder="Enter Office Telephone" name="father_education" value="<?php if (!empty($notificationData1['father_office_telephone'])) {
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
                           <input name="father_mobile" class="form-control date4" id="fathermobile" type="text" placeholder="Enter Mobile No" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check2();" return false; value="<?php if (!empty($notificationData1['father_mobile'])) {
                                                                                                                                                                                                                                                                                       echo $notificationData1['father_mobile'];
                                                                                                                                                                                                                                                                                    } ?>" /><span class="fathermobile_for_msg validation_err" id="validation_err2"></span>
</div>                     
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">SSSM ID :</label>
                                    <input class="form-control" id="fathersssmid" type="text"  value="<?php if (!empty($notificationData1['fatherSSSMID'])) {echo $notificationData1['fatherSSSMID'];} ?>"  placeholder="Enter phone number" name="fatherSSSMID" maxlength="9" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check9();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err9"></span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">Aadhar No.:</label>
                                    <input class="form-control" id="fatherAadharNo" type="text"  value="<?php if (!empty($notificationData1['fatherAadharNo'])) {echo $notificationData1['fatherAadharNo'];} ?>" placeholder="Enter Aadhar No." name="fatherAadharNo" maxlength="12" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check8();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err8"></span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Residental Address</label>
                           <input class="form-control date4 uperletter" id="picker2-" type="email" placeholder="Enter Residental Address" name="father_res_address" value="<?php if (!empty($notificationData1['father_res_address'])) {
                                                                                                                                                                     echo $notificationData1['father_res_address'];
                                                                                                                                                                  } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Emergency contact No.</label>
                           <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Emergency contact No." name="father_emergency_contact" value="<?php if (!empty($notificationData1['father_emergency_contact'])) {
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
                                                if ($notificationData1['mothername_prefix'] == "Mrs.") {
                                                   echo "selected";
                                                }
                                             } ?> value="Mrs">Mrs.</option>
                                    <option <?php if (!empty($notificationData1['mothername_prefix'])) {
                                                if ($notificationData1['mothername_prefix'] == "Dr.") {
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
                           <input class="form-control" id="mothermobile" placeholder="Enter Mobile No." name="mother_mobile" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check3();" return false; value="<?php if (!empty($notificationData1['mother_mobile'])) {
                                                                                                                                                                                                                                                                     echo $notificationData1['mother_mobile'];
                                                                                                                                                                                                                                                                  } ?>" /><span class="mothermobile_for_msg validation_err" id="validation_err3"></span>
 </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">SSSM ID :</label>
                                    <input class="form-control" id="mothersssmid" type="text" placeholder="Enter SSSM ID" name="motherSSSMID" value="<?php if (!empty($notificationData1['motherSSSMID'])) {echo $notificationData1['motherSSSMID'];} ?>" maxlength="9" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check8();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err8"></span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">Aadhar No.:</label>
                                    <input class="form-control" id="motherAadharNo" type="text" placeholder="Enter Aadhar No." name="motherAadharNo" value="<?php if (!empty($notificationData1['motherAadharNo'])) {echo $notificationData1['motherAadharNo'];} ?>" maxlength="12" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check8();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err8"></span>
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
                           <input class="form-control date4 uperletter" id="picker2-" type="email" placeholder="Enter Guardian Name" name="guardian_name" value="<?php if (!empty($notificationData1['guardian_name'])) {
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
                           <input name="guardian_mobile" class="form-control" placeholder="Enter Mobile No." name="guardian_mobile" maxlength="10" id="guardianmobile" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check4();" return false; value="<?php if (!empty($notificationData1['guardian_mobile'])) {
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
                           <input class="form-control date4 uperletter" id="picker2-" type="email" placeholder="Emergency Contact No." name="guardian_emergency_contact" value="<?php if (!empty($notificationData1['guardian_emergency_contact'])) {
                                                                                                                                                                        echo $notificationData1['guardian_emergency_contact'];
                                                                                                                                                                     } ?>">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                           <label for="siblings_school">Guardian Relation.</label>
                           <input class="form-control date4 uperletter" id="picker2-" type="email" placeholder="Enter Guardian Relation" name="guardian_relation" value="<?php if (!empty($notificationData1['guardian_relation'])) {
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
                     </form>

                  </div>

               </div>
               <div id="step-3">
               </div>
               <div id="step-4">
               </div>
            </div>
            @endforeach
            @endif
         </div>
      </div>
   </div>
   <!-- end of main-content -->
</div>

<script src="{{url('assets/backend')}}/js/plugins/jquery-3.3.1.min.js"></script>


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
               $('input[name="student_dob"]').val(response.inq_data.date_of_birth);
               $('select[name="gender"]').val(response.inq_str_data.gender);
               $('select[name="student_caste"]').val(response.inq_str_data.caste);
               $('select[name="religion"]').val(response.inq_str_data.religion);
               $('select[name="category"]').val(response.inq_str_data.category);
               $('select[name="classname"]').val(response.inq_data.class_name);
               $('select[name="session_name"]').val(response.inq_data.session_name);
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

   /*validate form*/
   $(".submit_btn").on('click', function(e) {

      e.preventDefault();

      var application_for = $('select[name="application_for"]').val();
      var student_name = $('input[name="student_name"]').val();
      var student_dob = $('input[name="student_dob"]').val();
      var student_caste = $('input[name="student_caste"]').val();
      var religion = $('select[name="religion"]').val();
      var classname = $('select[name="classname"]').val();
      var session_name = $('select[name="session_name"]').val();

      if (application_for == null) {
         $('.application_for_msg').text("This field is required*");
         allinputmsg = '1';
      } else {

         $('.application_for_msg').text("");
      }

      if (student_name == null) {
         $('.student_name_msg').text("This field is required*");
         allinputmsg = '1';
      } else {

         $('.student_name_msg').text("");
      }

      if (student_dob == '') {
         $('.student_dob_msg').text("This field is required*");
      } else {
         $('.student_dob_msg').text("");

      }

      if (student_caste == '') {
         $('.student_caste_msg').text("This field is required*");
      } else {
         $('.student_caste_msg').text("");

      }

      if (religion == '') {
         $('.religion_msg').text("This field is required*");
      } else {
         $('.religion_msg').text("");

      }

      if (classname == null) {

         $('.classname_msg').text("This field is required*");
      } else {
         $('.classname_msg').text("");

      }

      if (session_name == null) {
         $('.session_name_msg').text("This field is required*");
      } else {
         $('.session_name_msg').text("");

      }

      if (category == null) {
         $('.category_msg').text("This field is required*");
      } else {
         $('.category_msg').text("");

      }

      if (application_for !== null && student_name !== '' && student_dob !== '' && student_caste !== '' && religion !== '' && classname !== null && session_name !== null && category !== null) {
         var myForm = document.getElementById("form-id");
         event.preventDefault();
         myForm.submit();
         $('.rg_form').submit();
      } else {
         $('.allinput_msg').text("All required fields must be completed before you submit the form*");
         if (allinputmsg == "1") {
            $("#step1").addClass('s1');

         } else {
            $("#step1").removeClass('s1');
         }
         console.log("invalid form");
      }


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
   $('.date').datepicker({
      format: 'dd-mm-yyyy'
   });
   $('.date1').datepicker({
      format: 'dd-mm-yyyy'
   });
   $('.date2').datepicker({
      format: 'dd-mm-yyyy'
   });
   $('.date3').datepicker({
      format: 'dd-mm-yyyy'
   });
   $('.date4').datepicker({
      format: 'dd-mm-yyyy'
   });
   $('.date5').datepicker({
      format: 'dd-mm-yyyy'
   });

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
   $.noConflict();
   jQuery(document).ready(function($) {
      $("#inq-form-no").select2();
      $("#sibling_name").select2();

      setTimeout(function() {
         var year = $("#year").val()
         $("#session_name").val(year);
      }, 1000);
   });
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" />
<style type="text/css"></style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.full.min.js"></script>
<script type="text/javascript">
   $.noConflict();
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
                $('#section_name').html('<option value=""> -- Select All -- </option>');
                for (var i = 0; i < data.length; i++) {
                    var sectionData = data[i].section_name;
                    $('#section_name').append('<option value="' + sectionData + '">' + sectionData + '</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    } else {
        $('#section_name').html('<option value="">Select class first</option>');
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