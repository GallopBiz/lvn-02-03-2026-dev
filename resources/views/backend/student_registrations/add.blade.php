@extends('backend.layouts.main')
@section('main-container')

<!-- ext css -->
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

    .select2-container--default .select2-selection--single {
        background-color: #f3f4f6 !important;
    }

    .select2-hidden-accessible {
        position: relative !important;
    }
</style>

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
 .s1{
   background-color: #ff4c51;
 } 
 .uperletter{
   text-transform: capitalize;
 }  
 .sw-theme-default .sw-toolbar-top  {  
     display: none;
 } 
 .mb-3 {
      margin-bottom: 0.4rem !important; 
 }
 </style>




<div class="main-content pt-4">
    <div class="breadcrumb">
        <h1 class="me-2">Student Registration</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>
    <div class="row">


        <div class="col-md-6 p-2">
            <label for="firstName1">Select Form No to Pick Information form inquiry</label>
            <select id="inq-form-no" class="form-control uperletter" onchange="getValAndAssign(this);" name="inq_form_selection" required>
                <option selected>Please select</option>
                @if (!empty($inqArr))
                @foreach ($inqArr as $each)
                @if ($each->type != 't')
                <option value="{{ $each->form_number }}">
                    {{ $each->student_name }}

                    @if ($each->form_number)
                    - {{ $each->form_number }}
                    @endif

                    <?php
                    $jsondata = json_decode($each->json_str);
                    ?>
                    @if ($each->json_str)
                    @if (isset($jsondata->is_staff_applied_for_admission) && $jsondata->is_staff_applied_for_admission != '')
                    - Staff
                    @else
                    @if ($each->application_for == 'RTE')
                    - RTE
                    @else
                    - Non RTE
                    @endif
                    @endif
                    @else
                    @if ($each->application_for == 'RTE')
                    - RTE
                    @else
                    - Non RTE
                    @endif
                    @endif

                    @if ($each->json_str)
                    @if (isset($jsondata->siblings_name) && $jsondata->siblings_name != '')
                    - Sibling
                    @endif
                    @endif
                </option>
                @endif
                @endforeach
                @endif
            </select>
        </div>

        <div class="col-md-6" style="padding-top: 28px!important;">
            <button class="btn btn-primary pick_inq_data" data-form_number=''>Pick Data</button>
        </div>
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
                            <form novalidate="novalidate" id="form-id" method="post" action="{{ url('save-student-registration') }}" class="rg_form" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="studentname">Scholar Number</label>
                                        <?php //<input name="scholar_no2" class="form-control " value="{{$uid}}" id="scholar_no2" placeholder="Scholar Number" readonly disabled />
										?>
                                        <input name="scholar_no" class="form-control " value="{{$uid}}" id="scholar_no" placeholder="Scholar Number" type="number" />
                                        <input name="received_amount" class="form-control "  id="received_amount" placeholder="Scholar Number" type="hidden" />
                                        <input name="amount" class="form-control "  id="amount" placeholder="Scholar Number" type="hidden" />
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
										<label for="registration_date">Registration Date</label>
										<input type="date" name="registration_date" class="form-control" id="registration_date" required>
									</div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="firstName1">Application Type<b class="validation_err">*</b></label>
                                        <select id="application_for" class="form-control" name="application_for" autocomplete="shipping address-level1" required>
                                            <option value="" disabled selected>Please select</option>
                                            @foreach(config('global.admission_type') as $each)
                                            <option value="{{$each}}">{{$each}}</option>
                                            @endforeach
                                        </select>
                                        <span class="application_for_msg validation_err"></span>
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="studentname">Form Number</label>
                                        <input name="form_number" class="form-control" id="studentname" placeholder="Form Number" />
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
                                                    <option value="Please Select">Please Select</option>
                                                    <option value="Master">Master</option>
                                                    <option value="Mr.">Mr.</option>
                                                    <option value="Miss">Miss</option>
                                                </select>
                                            </div>
                                            <input type="text" class="form-control uperletter" id="studentname" placeholder="Enter Student Name" name="studentname" required />
                                            <span class="student_name_msg validation_err"></span>
                                        </div>

                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="studentname">Student DOB<b class="validation_err">*</b></label>
                                        <input type="date" name="student_dob" class="form-control" id="student_dob" placeholder="Enter Student DOB" />
                                        <span class="student_dob_msg validation_err"></span>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="studentname">Nationality<b class="validation_err">*</b></label>
                                        <input name="nationality" class="form-control uperletter" id="studentname" placeholder="Enter Nationality" value="Indian" />
                                        <!-- <span class="student_dob_msg validation_err"></span> -->
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="firstName1">Gender</label>
                                        <select id="gender" class="form-control" name="gender" required>
                                            <option value="" disabled selected>Please select</option>
                                            @foreach(config('global.gender') as $each)
                                            <option value="{{$each}}">{{$each}}</option>
                                            @endforeach
                                        </select>
                                        <span class="gender_msg validation_err"></span>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="Caste">Caste<b class="validation_err">*</b></label>
                                        <!-- <input class="form-control Caste" id="Caste" placeholder="Student Caste" name="student_caste" /> -->
                                        <select id="Caste" class="form-control " name="student_caste">
                                            <option value="" disabled selected>Please select</option>
                                            @foreach($caste as $each)
                                            <option value="{{$each->caste_name}}">{{$each->caste_name}}</option>
                                            @endforeach
                                        </select>
                                        <span class="student_caste_msg validation_err"></span>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="religion">Religion<b class="validation_err">*</b></label>
                                        <select id="religion" class="form-control" name="religion" required>
                                            <option value="" disabled selected>Please select</option>
                                            @foreach(config('global.religion') as $each)
                                            <option value="{{$each}}">{{$each}}</option>
                                            @endforeach
                                        </select>
                                        <span class="religion_msg validation_err"></span>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="category">Category<b class="validation_err">*</b></label>
                                        <select id="category" class="form-control" name="category" autocomplete="" required>
                                            <option value="" disabled selected>Please select</option>
                                            @foreach(config('global.cate') as $each)
                                            <option value="{{$each}}">{{$each}}</option>
                                            @endforeach
                                        </select>
                                        <span class="category_msg validation_err"></span>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="firstName1">Class Name<b class="validation_err">*</b></label>
                                        <select id="classname" class="form-control" name="classname" autocomplete="" required>
                                            <option value="" disabled selected>Please select</option>
                                            @foreach($classlist as $each)
                                            <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                                            @endforeach
                                        </select>
                                        <span class="classname_msg validation_err"></span>

                                    </div>

                                    <!-- <div class="col-md-4 form-group mb-3">
                                            <label for="firstName1">Section<b class="validation_err">*</b></label>
                                            <select id="section" class="form-control" name="section" autocomplete=""
                                                required>
                                                <option disabled selected>Please select</option>
                                                @foreach (config('global.section') as $each)
                                                    <option value="{{ $each }}">{{ $each }}</option>
                                                @endforeach
                                            </select>


                                        </div> -->
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="firstName1">Session Name<b class="validation_err">*</b></label>
                                        <input type="text" readonly id="session_name" class="form-control" value="" name="session_name">
                                        <!-- <select id="session_name" class="form-control" name="session_name"
                                                autocomplete="" required>
                                                <option disabled selected>Please select</option>
                                                @foreach (config('global.session_name') as $each)
                                                    <option value="{{ $each }}">{{ $each }}</option>
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
                                            <option value="" disabled selected> - Year - </option>
                                            @foreach($databaseNames as $databaseName)
                                                @if (is_numeric(substr($databaseName, 0, 1)))
                                                    <option value=<?php echo $databaseName; ?> ><?php echo $databaseName; ?></option>
                                                @endif
                                            @endforeach
                                            <!-- <option value="<?php //echo $nextSchoolYear; ?>"><?php //echo $nextSchoolYear; ?></option> -->
                                        </select>
                                        <span class="batch_dob_msg validation_err"></span>

                                    </div>

                                    <div class="col-md-4 form-group mb-3">
                                        <label for="address">Present Address :</label>
                                        <input class="form-control uperletter" id="address" type="text" placeholder="Enter Present Address" name="present_address" required oninput="convertToCamelCase(this)" />
                                        <span class="present_address_msg validation_err"></span>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="address">Permanent Address :</label>
                                        <input class="form-control uperletter" id="address" type="text" placeholder="Permanent Address" name="permanent_address" />
                                        <span class="permanent_address_msg validation_err"></span>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="address">Phone Number :</label>
                                        <input class="form-control" id="phone number" type="text" placeholder="Enter Phone Number" name="phone_number" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="address">Mobile Number :</label>
                                        <input class="form-control" id="phonenumber" type="text" placeholder="Enter Mobile Number" name="mobile_number" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check();" name="fathermobile" return false; /><span class="phonenumber_for_msg validation_err" id="validation_err"></span>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="firstName1">Mother Tongue</label>
                                        <input name="mother_tongue" class="form-control uperletter" id="mother_tongue" type="text" placeholder="Enter Mother tongue" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="remark">Remarks</label>
                                        <input name="remarks" class="form-control uperletter" id="remarks" type="text" placeholder="Enter remark" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="remark">Vaccaination</label>
                                        <input name="vaccaination" class="form-control uperletter" id="vaccaination" type="text" placeholder="Enter vaccaination" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="address">Scholer SSSM ID :</label>
                                        <input class="form-control" id="sssmid" type="text" placeholder="Enter SSSMID " name="SSSMID" maxlength="9" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check7();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err7"></span>
                                    </div>
									<div class="col-md-4 form-group mb-3">
                                        <label for="address">Family SSSM ID :</label>
                                        <input class="form-control" id="family_SSSMID" type="text" placeholder="Enter SSSMID" name="family_SSSMID" maxlength="9" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check8();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err8"></span>
                                    </div>

                                    <div class="col-md-4 form-group mb-3">
                                        <label for="address">Aadhar No.:</label>
                                        <input class="form-control" id="AadharNo" type="text" placeholder="Enter Aadhar Number" name="AadharNo" maxlength="12" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check9();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err9"></span>
                                    </div>
                                    <div class="col-md-6 form-group mb`-3">
                                        <label for="remark">Medical Conserns (any)</label>
                                        <input name='student_medical_conserns' class="form-control uperletter" id="Medical Conserns (any)" type="text" placeholder="Enter Medical Conserns (any)" />
                                    </div>
                                    <div class="col-md-12 form-group mb-3">
                                        <label for="remark">Name of Present Play School / Day Care (if any)</label>
                                        <input name="present_school_name" class="form-control uperletter" id="Medical Conserns (any)" type="text" placeholder="Enter Name of Present Play School / Day Care (if any)" />
                                        <br>
                                        <h3 class="border-bottom border-gray pb-2">Details of Siblings</h3>
                                        <div class="mt-1 form__field">
                                            <h6 class="field_wrapper">Do you have any siblings?
                                                <input class="coupon_question" type="checkbox" name="is_sibling_applied_for_admission" value="1" id="checkBox" />
                                                <span class="item-text">Yes</span>
                                            </h6>

                                            <div class="col-md-6 form-group mb-3 answer">
                                                <label for="formidsearch">Enquiry No Search (By - Father Name)</label>
                                                <input class="form-control uperletter" id="searchfather" placeholder="" name="searchfather" required />
                                                <span class="validation_err"></span><span class="error_msg validation_err"></span>
                                                <span class="error_msg3 validation_err3"></span>
                                            </div>
                                            <div class="col-md-6 form-group mb-3 answer">
                                                <label for="formidsearch"></label>
                                                <div class="col-md-12">
                                                    <button type="button" class="btn btn-primary pick_inq_data2" onclick="getsiblingbyfathers();" data-form_number="" fdprocessedid="7t8lyh">Pick Data</button>
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
                                                                    <input class="form-control uperletter" id="siblings_namesecond" type="text" placeholder="Enter name" name="siblings_namesecond[0]" value="">
                                                                    <span class="siblings_namesecond_msg validation_err"></span>
                                                                </td>
                                                                <td>
                                                                    <label for="sibling_class_second">Class</label>
                                                                    <select id="sibling_class_second" class="form-control" name="sibling_class_second[0]" autocomplete="">
                                                                        <option value="" disabled selected>Please select</option>
                                                                        @foreach(config('global.class_name') as $each)
                                                                        <option value="{{$each}}">{{$each}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <span class="sibling_class_second_msg validation_err"></span>
                                                                </td>
                                                                <td>
                                                                    <label for="siblings_school_second">Section</label>
                                                                    <input class="form-control" id="siblings_section_second" type="text" placeholder="Enter Section" name="siblings_section_second[0]" value="">
                                                                    <span class="siblings_section_second_msg validation_err"></span>
                                                                </td>
                                                                <td>
                                                                    <label for="siblings_school_second">Date Of Birth</label>
                                                                    <input class="form-control" id="picker2-" type="text" placeholder="dd-mm-yyyy" name="siblings_bod_second[0]" max="9999-12-31" value="">
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
                                                <input type="checkbox" class="is_driver_applied" name="required_school_transport" value="1">
                                                <span>School Transport</span>
                                            </label>

                                            <div class="row has_diver_div" style="display:none;">
                                                <div class="customer_records">
                                                    <div class="row">

                                                        <div class="col-md-3 form-group mb-3">
                                                            <label for="remark">Driver Name</label>
                                                            <select name="driver_name" id="driver_name" class="form-control staff_selection" style="width:100%;">
                                                                <option>Select Staff</option>
                                                                <?php
                                                                foreach ($drivername as $eachStudent) {
                                                                    // Check if the 'type' column has the value 't'

                                                                    echo '<option value="' . $eachStudent->ename . '">' . $eachStudent->ename . '</option>';
                                                                }
                                                                ?>
                                                            </select>
                                                            <?php $newdate = date('d-m-Y'); ?>
                                                            <input type="hidden" name="bus_facility_start_date" value="<?php echo $newdate; ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input type="checkbox" class="is_staff_applied" name="is_staff_applied_for_admission" value="yes">
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
                                                                <option>Select Staff</option>
                                                                <?php
                                                                foreach ($stutdentsArr as $eachStudent) {
                                                                    // Check if the 'type' column has the value 't'
                                                                    if ($eachStudent->type == 't') {
                                                                        echo '<option value="' . $eachStudent->staff_name . '" data-form_number="' . $eachStudent->form_number . '" data-class_name="' . $eachStudent->class_name . '">' . $eachStudent->staff_name . '</option>';
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="checked_birth_certificate" type="checkbox" name="birth_certificate_chk" value="yes">
                                                <span>Birth Certificate</span>
                                            </label>
                                            <div class="row birth_certificate_input_div" style="display:none;">
                                                    <input class="form-control" id="BirthCertificate" type="file" placeholder="Upload Birth Certificate" name="BirthCertificate" />
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="transfer_certificate" type="checkbox" name="transfer_certificate_chk" value="yes">
                                                <span>Transfer Certificate</span>
                                            </label>
                                            <div class="row transfer_certificate_input_div" style="display:none;">
                                                    <input class="form-control" id="TransferCertificate" type="file" placeholder="Upload Transfer Certificate" name="TransferCertificate" />
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="address_proof" type="checkbox" name="address_proof_chk" value="yes">
                                                <span>Address Proff</span>
                                            </label>
                                            <div class="row address_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="AddressProff" type="file" placeholder="Upload Address Proff" name="AddressProff" />
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="cast" type="checkbox" name="cast_chk" value="yes">
                                                <span>Cast Certificate</span>
                                            </label>
                                            <div class="row cast_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="CastProff" type="file" placeholder="Upload Cast Proff" name="CastProff" />
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="aadhar" type="checkbox" name="aadhar_chk" value="yes">
                                                <span>Aadhar Card</span>
                                            </label>
                                            <div class="row aadhar_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="aadharProff" type="file" placeholder="Upload Cast Proff" name="aadharProff" />
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="bankb" type="checkbox" name="bankb_chk" value="yes">
                                                <span>Bank Pass Book Copy</span>
                                            </label>
                                            <div class="row bankb_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="bankbProff" type="file" placeholder="Upload Cast Proff" name="bankbProff" />
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="stuprof" type="checkbox" name="stuprof_chk" value="yes">
                                                <span>Student Photo</span>
                                            </label>
                                            <div class="row stuprof_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="StuProf" type="file" placeholder="Upload Stu Proff" name="StuProf" />
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="sssmprof" type="checkbox" name="sssmprof_chk" value="yes">
                                                <span>SSSM-ID/Family-id</span>
                                            </label>
                                            <div class="row sssmprof_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="sssmprof" type="file" placeholder="Upload Stu Proff" name="sssmprof" />
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="salaryprof" type="checkbox" name="salaryprof_chk" value="yes">
                                                <span>Salary Certificate/Income Tax Return</span>
                                            </label>
                                            <div class="row salaryprof_proof_input_div" style="display:none;">
                                                    <input class="form-control" id="salaryprof" type="file" placeholder="Upload Stu Proff" name="salaryprof" />
                                            </div>
                                        </div>
                                        <div class="mt-1 form__field">
                                            <label class="form__choice-wrapper">
                                                <input id="birth-certificate" class="last_report_card" type="checkbox" name="last_report_card_chk" value="yes">
                                                <span>Last Report Card</span>
                                            </label>
                                            <div class="row last_report_card_input_div" style="display:none;">
                                                    <input class="form-control" id="LastReportCard" type="file" placeholder="Upload Last Report Card" name="LastReportCard" />
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
                                            <select id="fathername_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="fathername_prefix" autocomplete="shipping address-level1" required>
                                                <option value="Please Select">Please Select</option>
                                                <option value="Mr">Mr.</option>
                                                <option value="Dr">Dr.</option>
                                                <option value="Late">Late</option>
                                            </select>
                                        </div>
                                        <input class="form-control uperletter" id="fathername" type="text" placeholder="Enter your father name" name="fathername" />
                                    </div>

                                </div>
                                <!-- <div class="col-md-4 form-group mb-3">
                                        <label for="siblings_school">Date Of Birth</label>
                                        <input name="father_dob" class="form-control date4" id="picker2-"
                                            type="date" placeholder="dd-mm-yyyy" name="siblings_bod" required>
                                    </div> -->
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Education</label>
                                    <input name="father_education" class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter Education" name="father_education" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="mothername">Occupation</label>
                                    <input name="father_designation" class="form-control uperletter" id="fatheroccupation" type="text" placeholder="Enter father occupation" required />
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Organization</label>
                                    <input name="father_organization" class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter Organization" name="father_education" required>
                                </div>

                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Office Telephone</label>
                                    <input name="father_office_telephone" class="form-control date4" id="picker2-" type="text" placeholder="Office Telephone" name="father_education" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Email ID</label>
                                    <input name="father_email_id" class="form-control date4" id="picker2-" type="email" placeholder="Enter Email ID" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Mobile No.</label>
                                    <input name="father_mobile" class="form-control date4" id="fathermobile" type="text" placeholder="Enter Mobile No." maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check2();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err2"></span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">Father SSSM ID :</label>
                                    <input class="form-control" id="fathersssmid" type="text" placeholder="Enter Father SSSM ID" name="fatherSSSMID" maxlength="9" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check9();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err9"></span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">Aadhar No.:</label>
                                    <input class="form-control" id="fatherAadharNo" type="text" placeholder="Enter Aadhar No." name="fatherAadharNo" maxlength="12" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check8();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err8"></span>
                                </div>
                                {{-- <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Residental Address</label>
                                    <input class="form-control date4" id="father_resi" type="email" placeholder="Enter Residental Address" name="father_res_address" required>
                                </div> --}}


                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">Residental Address</label>
                                    <input class="form-control uperletter" id="address" type="text" placeholder="Enter address" name="present_address" required onclick="fillData()" />
                                    <span class="present_address_msg validation_err"></span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Emergency contact No.</label>
                                    <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Emergency contact No." name="father_emergency_contact" required>
                                </div>
                                <h4>Mother's Details</h4>
                                <!-- <div class="col-md-4 form-group mb-3">
                                                       <label for="pincode">Mother Name</label>
                                                       <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter Mother Name" fdprocessedid="csh6vi" name="mother_name" required>
                                                       </div> -->
                                <div class="col-lg-4 form-group mb-3">
                                    <label for="mothername">Mother Name</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <select id="mothername_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="mothername_prefix" autocomplete="shipping address-level1" required>
                                                <option value="Please Select">Please Select</option>
                                                <option value="Mrs">Mrs.</option>
                                                <option value="Dr">Dr.</option>
                                                <option value="Late">Late</option>
                                            </select>
                                        </div>
                                        <input class="form-control uperletter" id="mother_name" name="mothername" type="text" placeholder="Enter your mother name" />
                                    </div>
                                </div>
                                <!-- <div class="col-md-4 form-group mb-3">
                                        <label for="pincode">Date of Birth</label>
                                        <input class="form-control" id="exampleInputEmail1" type="date"
                                            name="mother_dob" required>
                                    </div> -->
                                <div class="col-md-4 form-group mb-3">
                                    <label for="pincode">Education</label>
                                    <input class="form-control uperletter" id="exampleInputEmail1" type="text" placeholder="Enter Education" name="mother_education" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="pincode">Occupation</label>
                                    <input class="form-control uperletter" id="exampleInputEmail1" type="text" placeholder="Enter Mother Occupation" name="mother_organization" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="pincode">Organization</label>
                                    <input class="form-control uperletter" id="exampleInputEmail1" type="text" placeholder="Enter Mother Organization" name="mother_organization" required>
                                </div>

                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Office Telephone</label>
                                    <input class="form-control date4" id="picker2-" type="text" placeholder="Enter Office Telephone" name="mother_office_telephone" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Email ID</label>
                                    <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Mother Email" name="mother_email" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="phone">Mobile No.</label>
                                    <input class="form-control" id="mothermobile" placeholder="Enter mobile no" name="mother_mobile" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check3();" return false; /><span class="mothermobile_for_msg validation_err" id="validation_err3"></span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">Mother SSSM ID :</label>
                                    <input class="form-control" id="mothersssmid" type="text" placeholder="Enter SSSM ID" name="motherSSSMID" maxlength="9" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check8();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err8"></span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">Aadhar No.:</label>
                                    <input class="form-control" id="motherAadharNo" type="text" placeholder="Enter Aadhar No." name="motherAadharNo" maxlength="12" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check8();" return false; /><span class="fathermobile_for_msg validation_err" id="validation_err8"></span>
                                </div>
                                {{-- <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Residental Address</label>
                                    <input class="form-control date4" id="mother_resi" type="text" placeholder="Enter Residental Address" name="mother_res_address">
                                </div> --}}


                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">Residental Address</label>
                                    <input class="form-control uperletter" id="address" type="text" placeholder="Enter address" name="present_address" required onclick="fillData()" />
                                    <span class="present_address_msg validation_err"></span>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Emergency Contact No.</label>
                                    <input class="form-control date4" id="picker2-" type="text" placeholder="Enter Emergency contact No" name="mother_emergency_contact" required>
                                </div>
                                <h4>Guardian Details</h4>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Guardian Name</label>
                                    <input class="form-control date4 uperletter" id="picker2-" type="email" placeholder="Enter Guardian Name" name="guardian_name" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Office Telephone</label>
                                    <input class="form-control date4" id="picker2-" type="text" placeholder="Enter Office Telephone" name="guardian_office_telephone" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Email ID</label>
                                    <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Email ID" name="guardian_email_id" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="phone">Mobile No.</label>
                                    <input name="guardian_mobile" class="form-control" placeholder="Enter Mobile No." name="guardian_mobile" maxlength="10" id="guardianmobile" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check4();" return false; /><span class="guardianmobile_for_msg validation_err" id="validation_err4"></span>

                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">Permanent Address :</label>
                                    <input class="form-control uperletter" id="address" type="text" placeholder="Enter Permanent address" name="guardian_permanent_address" required />
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Emergency Contact No.</label>
                                    <input class="form-control date4" id="picker2-" type="email" placeholder="Emergency contact No." name="guardian_emergency_contact" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Guardian Relation.</label>
                                    <input class="form-control date4 uperletter" id="picker2-" type="email" placeholder="Enter Guardian Relation" name="guardian_relation" required>
                                </div>


                                <h4>Student Bank Details</h4>


                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Bank Name</label>
                                    <input class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter name" name="bankName" required>
                                </div>

                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Branch Name</label>
                                    <input class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter branch" name="branchName" required>
                                </div>


                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Account Number</label>
                                    <input class="form-control date4" id="picker2-" type="text" placeholder="Enter accont number" name="account_number" required>
                                </div>


                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">IFSC Code</label>
                                    <input class="form-control date4 uperletter" id="picker2-" type="text" placeholder="Enter ifsc code" name="ifsc_code" required>
                                </div>

                                {{-- <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Office Telephone</label>
                                    <input class="form-control date4" id="picker2-" type="text" placeholder="Enter Office Telephone" name="guardian_office_telephone" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Email id</label>
                                    <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Email id" name="guardian_email_id" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="phone">Mobile No.</label>
                                    <input name="guardian_mobile" class="form-control" placeholder="Enter mobile no" name="guardian_mobile" maxlength="10" id="guardianmobile" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check4();" return false; /><span class="guardianmobile_for_msg validation_err" id="validation_err4"></span>

                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="address">Permanent Address :</label>
                                    <input class="form-control" id="address" type="text" placeholder="Enter Permanent address" name="guardian_permanent_address" required />
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Emergency contact No.</label>
                                    <input class="form-control date4" id="picker2-" type="email" placeholder="Emergency contact No." name="guardian_emergency_contact" required>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="siblings_school">Guardian Relation.</label>
                                    <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Guardian Relation" name="guardian_relation" required>
                                </div> --}}



                            </div>

                            <div class="col-md-12">
                                <input type="hidden" name="iid" value="">
                                <input type="submit" name="submit" value="Submit" class="btn btn-primary ">
                            </div>
                            </form>

                        </div>
                        <!-- step 3 -->
                        <!--   <h3 class="border-bottom border-gray pb-2">
                                                       Details of Siblings
                                                       </h3>
                                                       <div>
                                                       <div class="row">
                                                       <div class="col-md-3 form-group mb-3">
                                                       <label for="siblings_name">1.Name</label>
                                                       <input class="form-control" id="siblings_name" type="text" placeholder="Enter name"  name="siblings_name">
                                                       </div>
                                                       <div class="col-md-3 form-group mb-3">
                                                       <label for="sibling_class">Class</label>
                                                       <select id="sibling_class" class="form-control" name="sibling_class" autocomplete="" >
                                                       <option value="" disabled selected>Please select</option>
                                                       @foreach (config('global.class_name') as $each)
    <option value="{{ $each }}">{{ $each }}</option>
    @endforeach
                                                       </select>
                                                       </div>
                                                       <div class="col-md-3 form-group mb-3">
                                                       <label for="siblings_school">School</label>
                                                       <input class="form-control" id="siblings_school" type="text" placeholder="Enter school name"  name="siblings_school">
                                                       </div>
                                                       <div class="col-md-3 form-group mb-3">
                                                       <label for="siblings_school_second">Date Of Birth</label>
                                                       <input class="form-control date5" id="picker2-" type="text" placeholder="dd-mm-yyyy"  name="siblings_bod_second">
                                                       </div>
                                                       <div class="col-md-3 form-group mb-3">
                                                       <label for="siblings_name">2.Name</label>
                                                       <input class="form-control" id="siblings_namesecond" type="text" placeholder="Enter name"  name="siblings_name2">
                                                       </div>
                                                       <div class="col-md-3 form-group mb-3">
                                                       <label for="sibling_class_second">Class</label>
                                                       <select id="sibling_class_second" class="form-control" name="sibling_class2" autocomplete="" >
                                                       <option value="" disabled selected>Please select</option>
                                                       @foreach (config('global.class_name') as $each)
    <option value="{{ $each }}">{{ $each }}</option>
    @endforeach
                                                       </select>
                                                       </div>
                                                       <div class="col-md-3 form-group mb-3">
                                                       <label for="siblings_school_second">School</label>
                                                       <input class="form-control" id="siblings_school_second" type="text" placeholder="Enter school name"  name="siblings_school_second">
                                                       </div>
                                                       <div class="col-md-3 form-group mb-3">
                                                       <label for="siblings_school_second">Date Of Birth</label>
                                                       <input class="form-control date5" id="picker2-" type="text" placeholder="dd-mm-yyyy"  name="siblings_bod_second">
                                                       </div>
                                                       </div>
                                                       </div> -->
                        <!-- step 4 -->
                        <!-- <h3 class="border-bottom border-gray pb-2">
                                                       Bank Details
                                                       </h3>
                                                       <div>
                                                       <div class="row">
                                                       <div class="col-md-4 form-group mb-3">
                                                       <label for="headname">Head Name</label>
                                                       <select id="headname" class="form-control" name="headname" autocomplete="" >
                                                       <option value="" disabled selected>Please select</option>
                                                       @foreach (config('global.headname') as $each)
    <option value="{{ $each }}">{{ $each }}</option>
    @endforeach
                                                       </select>
                                                       </div>
                                                       <div class="col-md-4 form-group mb-3">
                                                       <label for="chq_cash">DD/Chq/Cash</label>
                                                       <select id="sibling_class_second" class="form-control" name="chq_cash" autocomplete="" >
                                                       <option value="cash">Cash</option>
                                                       <option value="dd">DD</option>
                                                       <option value="chq">cheque</option>
                                                       </select>
                                                       </div>
                                                       <div class="col-md-4 form-group mb-3">
                                                       <label for="siblings_school_second">Bank A/C</label>
                                                       <select name="back_ac" class="form-control">
                                                       <option value="">Select</option>
                                                       </select>
                                                       </div>
                                                       <div class="col-md-4 form-group mb-3">
                                                       <label for="siblings_school_second">Non Stu. Rcpt No.</label>
                                                       <input class="form-control" id="non_stu-" type="text" placeholder=""  name="non_stu">
                                                       </div>
                                                       <div class="col-md-12">
                                                       <button class="btn btn-primary">Submit</button>
                                                       </div>
                                                       </form>
                                                       </div>
                                                       </div> -->
                    </div>
                    <div id="step-3">
                    </div>
                    <div id="step-4">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end of main-content -->
</div>

<script src="{{ url('assets/backend') }}/js/plugins/jquery-3.3.1.min.js"></script>


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
            url: '{{ url('getDataByFormNumberstudent_registration') }}',
            type: 'post',
            data: {
                _token: CSRF_TOKEN,
                form_number: form_number
            },
            // dataType: 'json',
            success: function(response) {

                console.log(response.inq_str_data.caste);
                if (response.inq_data) {
                    /*assign value on pick data*/
                    // $('select[name="application_for"] option[value='+response.inq_str_data.admission_type+']').attr("selected","selected");
                    $('select[name="application_for"]').val(response.inq_str_data.admission_type);
                    $('input[name="form_number"]').val(response.inq_data.form_number);
                    $('select[name="studentname_prefix"]').val(response.inq_str_data
                        .studentname_prefix);
                    $('input[name="studentname"]').val(response.inq_data.student_name);
                    $('input[name="received_amount"]').val(response.inq_str_data.received_amount);
                    $('input[name="amount"]').val(response.inq_str_data.amount);
                    
                    $('input[name="student_dob"]').val(response.inq_data.date_of_birth);
                    $('select[name="gender"]').val(response.inq_str_data.gender);
                    $('select[name="student_caste"]').val(response.inq_str_data.caste);
                    $('select[name="religion"]').val(response.inq_str_data.religion);
                    $('select[name="category"]').val(response.inq_str_data.category);
                    $('select[name="classname"]').val(response.inq_data.class_name);
                    $('select[name="session_name"]').val(response.inq_data.session_name);
                    // $('input[name="present_address"]').val(response.inq_str_data.address);
                    $('input[name="permanent_address"]').val(response.inq_str_data.address);
                    $('input[name="phone_number"]').val(response.inq_str_data.fathermobile);
                    $('input[name="mobile_number"]').val(response.inq_str_data.fathermobile);
                    $('input[name="father_organization"]').val(response.inq_str_data.fatheroccupation);
					$('input[name="father_designation"]').val(response.inq_str_data.fatheroccupation);
                    $('input[name="mother_organization"]').val(response.inq_str_data.motheroccupation);
					//$('input[name="mother_organization"]').val(response.inq_str_data.motheroccupation);
                    
                    $('input[name="present_school_name"]').val(response.inq_str_data.presentlyschool);
                    $('select[name="fathername_prefix"]').val(response.inq_str_data
                        .fathername_prefix);
                    $('input[name="fathername"]').val(response.inq_str_data.fathername);
                    $('input[name="fathermobile"]').val(response.inq_str_data.father_mobile);
                    // $('input[name="present_address"]').val(response.inq_str_data.father_resi);
                    $('select[name="mothername_prefix"]').val(response.inq_str_data
                        .mothername_prefix);
                    $('input[name="mothername"]').val(response.inq_str_data.mothername);
                    // $('input[name="mother_res_address"]').val(response.inq_str_data.mother_resi);
                    // $('input[name="present_address"]').val(response.inq_str_data.mother_resi);
                    $('input[name="mother_emergency_contact"]').val(response.inq_str_data
                        .mother_mobile);
                    $('input[name="remarks"]').val(response.inq_str_data.remarks);
                    $('input[name="father_email_id"]').val(response.inq_str_data.email);
                    $('input[name="father_mobile"]').val(response.inq_str_data.fathermobile);
                    $('input[name="iid"]').val(response.inq_data.id);
                    $('input[name="mother_mobile"]').val(response.inq_str_data.mothermobile);


$('input[name="father_resi"]').val(response.inq_str_data.address);
$('input[name="mother_resi"]').val(response.inq_str_data.address);
$('input[name="present_address"]').val(response.inq_str_data.address);


                    


                }

            }
        });

    });

    function getsiblingbyfathers() {
        var form_number = $("#searchfather").val();
        $('.pick_inq_data2').attr('data-form_number', form_number);
    }

    /*Ajax for pick data from form no*/
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $(".pick_inq_data2").on('click', function() {

        var form_number = $('.pick_inq_data2').attr('data-form_number');

        $.ajax({
            url: '{{ url("getsiblingbyfathersname") }}',
            type: 'post',
            data: {
                _token: CSRF_TOKEN,
                form_number: form_number
            },
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
        } else {

            $('.application_for_msg').text("");
        }

        if (student_name == '') {
            $('.student_name_msg').text("This field is required*");
        } else {

            $('.student_name_msg').text("");
        }

        if (student_dob == '') {
            $('.student_dob_msg').text("This field is required*");
        } else {
            $('.student_dob_msg').text("");

        }
        if (batch == null) {
            $('.batch_dob_msg').text("This field is required*");
        } else {
            $('.batch_dob_msg').text("");

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

        if (application_for !== null && student_name !== '' && student_dob !== '' &&batch !==null && student_caste !== '' && religion !== null && classname !== null && session_name !== null && category !== null) {
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

    //     $('.has_siblings_div').show();

    // } else {

    //     $('.has_siblings_div').hide();
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

    //     if ($(this).is(":checked")) {

    //         $('.has_siblings_div').show();

    //     } else {

    //         $('.has_siblings_div').hide();
    //     }

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

    function check8() {
        var mobile = document.getElementById('family_SSSMID');
        var message = document.getElementById('validation_err8');
        if (mobile.value.length != 9 && mobile.value.length != 0 ) {
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



@endsection