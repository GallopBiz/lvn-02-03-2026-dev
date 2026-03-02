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
<form novalidate="novalidate" method="post" action="{{url('save_student_inquiryentry')}}" id="form-id" class="rg_form">
  <div class="main-content pt-4">
    <div class="breadcrumb">
      <h1 class="me-2">Enquiry Entry</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>
    <div class="row">
      <div class="col-md-12">
        <!--  SmartWizard html -->
        <div id="smartwizard">
          <ul>
            <li class="" id="step1"><a href="#step-1">Step 1<br /><small>Student Details</small></a></li>
            <li id="step2">
              <a href="#step-2">Step 2<br /><small>Enquiry For</small></a>
            </li>
            <li>
              <a href="#step-3">Step 3<br /><small>Details of Siblings</small></a>
            </li>
            <!--  <li>
                  <a href="#step-4"
                     >Step 4<br /><small>Bank Details</small></a
                     >
               </li> -->
          </ul>
          <div>
            <div id="step-1">
              <h3 class="border-bottom border-gray pb-2">
                Student Enquiry Entry Form
              </h3>



              <div class="form_section1_div">



                <div class="row">
                  <div class="col-md-6 form-group mb-3">
                    <label for="formidsearch">Enquiry No Search (By - Form No., Student Name, Parent Name, Mobile No.)</label>
                    <input class="form-control uperletter" id="searchenquiryno" placeholder="" name="searchenquiryno" required /><span class="validation_err"></span><span class="error_msg validation_err"></span>
                    <span class="error_msg2 validation_err2"></span>
                  </div>
                  <div class="col-md-6 form-group mb-3">
                    <label for="formidsearch"></label>
                    <div class="col-md-12">
                      <button type="button" class="btn btn-primary pick_inq_data" onclick="getValAndAssign();" data-form_number="" fdprocessedid="7t8lyh">Pick Data</button>

                    </div>
                  </div>


                  <div class="col-md-4 form-group mb-3">
                    <label for="firstName1">Enquiry Session:<b class="validation_err">*</b></label>
                    <input name="_token" type="hidden" value="{{ csrf_token() }}">
                    <!-- <select id="session" class="form-control" name="session" required>
                              <option value="" disabled selected>Please Select</option>
                              @foreach(config('global.session_name') as $each)
                              <option value="{{$each}}">{{$each}}</option>
                              @endforeach
                           </select> -->
                    <input type="text" readonly id="session" class="form-control" value="" name="session">
                    <span class="session_msg validation_err"></span>

                  </div>
                  <div class="col-md-2 form-group mb-3">
                    <label for="enquiryno">Enquiry For Next Year</label>
                    <br>
                    <input id="next_year" type="checkbox" name="next_year" required value="1" />
                    <span class="enquiryno_msg validation_err"></span>
                  </div>

                  <div class="col-lg-6 form-group mb-3">

                    <label for="studentname">Student Name<b class="validation_err">*</b></label>
                    <div class="input-group mb-3">
                      <div class="input-group-prepend">
                        <select id="studentname_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="studentname_prefix" autocomplete="shipping address-level1">
                          <option value="Please Select">Please Select</option>
                          <option value="Master">Master</option>
                          <option value="Mr">Mr.</option>
                          <option value="Miss">Miss</option>
                        </select>
                      </div>
                      <input type="text" class="form-control uperletter" id="studentname" placeholder="Enter Student Name" name="studentname" required />

                    </div><span class="student_name_msg validation_err"></span>

                  </div>
                  <div class="col-md-4 form-group mb-3">
                    <label for="enquiryno">Enquiry No.</label>
                    <input class="form-control" id="eno" type="text" disabled placeholder="Enter your Enquiry no." name="eno" required value="{{$Userid}}" /><input value="{{$Userid}}" type="hidden" name="enquiryno" id="enquiryno">
                    <span class="enquiryno_msg validation_err"></span>
                  </div>
                  <!-- <div class="col-md-3 form-group mb-3">
                           <label for="formidsearch">Form ID Search</label>
                           <input
                             class="form-control"
                             id="formidsearch"
                             placeholder="ID" 
                           />
                           <div class="invalid-feedback">
                                Please provide a valid city.
                            </div>
                         </div> -->
                  <div class="col-md-4 form-group mb-3">
                    <label for="formidsearch">Form No</label>
                    <input type="text" class="form-control" id="formno" placeholder="" name="formno" required /><span class="formno_msg validation_err"></span>
                  </div>

                  <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Enquiry Date</label>
                    <input class="form-control " id="picker2" placeholder="dd-mm-yyyy" name="enquirydate" type="date" max="9999-12-31" />
                  </div>

                  <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Date Of Birth</label>
                    <input class="form-control " id="picker2" placeholder="dd-mm-yyyy" name="student_dob" type="date" max="9999-12-31" /><span class="student_dob_msg validation_err"></span>
                  </div>
                  <div class="col-md-4 form-group mb-3">
                    <label for="firstName1">Gender</label>
                    <select id="gender" class="form-control" name="gender">
                      <option value="" selected>Please select</option>
                      @foreach(config('global.gender') as $each)
                      <option value="{{$each}}">{{$each}}</option>
                      @endforeach
                    </select>
                    <span class="gender_msg validation_err"></span>
                  </div>



                  <div class="col-lg-4 form-group mb-3">

                    <label for="fathername">Father Name</label>
                    <div class="input-group mb-3">
                      <div class="input-group-prepend">
                        <select id="fathername_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="fathername_prefix" autocomplete="">
                          <option value="Please Select">Please Select</option>
                          <option value="Mr">Mr.</option>
                          <option value="Dr">Dr.</option>
                          <option value="Late">Late</option>
                        </select>
                      </div>
                      <input class="form-control uperletter" id="fathername" type="text" placeholder="Enter your father name" name="fathername" />
                    </div>
                    <span class="fathername_msg validation_err"></span>
                  </div>
                  <div class="col-md-4 form-group mb-3">
                    <label for="phone">Father Mobile No.</label>
                    <input class="form-control" id="fathermobile" placeholder="" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check();" placeholder="Enter father mobile no" name="fathermobile" return false;" /><span class="application_for_msg validation_err" id="validation_err"></span>
                    <span class="fathermobile_msg validation_err"></span>
                  </div>
                  <div class="col-md-4 form-group mb-3">
                    <label for="mothername">Father Occupation</label>
                    <!--  <input
                             class="form-control"
                             id="fatheroccupation" name="fatheroccupation"
                             type="text"
                             placeholder="Enter your father occupation"
                           /> -->
                    <select id="fatheroccupation" class="form-control" name="fatheroccupation" autocomplete="shipping address-level1">
                      <option value="" disabled selected>Please select</option>
                      @foreach(config('global.occupation') as $each)
                      <option value="{{$each}}">{{$each}}</option>
                      @endforeach
                    </select>

                  </div>
                  <div class="col-lg-4 form-group mb-3">
                    <label for="mothername">Mother Name</label>
                    <div class="input-group mb-3">
                      <div class="input-group-prepend">
                        <select id="mothername_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="mothername_prefix" autocomplete="shipping address-level1">
                          <option value="Please Select">Please Select</option>
                          <option value="Mrs">Mrs.</option>
                          <option value="Dr">Dr.</option>
                          <option value="Late">Late</option>
                        </select>
                      </div>
                      <input class="form-control uperletter" id="mothername" name="mothername" type="text" placeholder="Enter your mother name" />
                    </div>
                  </div>
                  <div class="col-md-4 form-group mb-3">
                    <label for="phone2">Mother Mobile No.</label>
                    <input class="form-control" id="mothermobile" placeholder="" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check2();" placeholder="Enter mother mobile no" name="mothermobile" return false;" /><span class="application_for_msg validation_err" id="validation_err2"></span>
                  </div>

                  <div class="col-md-4 form-group mb-3">
                    <label for="mothername">Mother Occupation</label>
                    <!--  <input
                             class="form-control"
                             id="motheroccupation"
                             type="text"
                             placeholder="Enter your mother occupation"
                           /> -->
                    <select id="motheroccupation" class="form-control" name="motheroccupation" autocomplete="shipping address-level1">
                      <option value="" disabled selected>Please select</option>
                      @foreach(config('global.occupation_mother') as $each)
                      <option value="{{$each}}">{{$each}}</option>
                      @endforeach
                    </select>
                    <!-- Housewife / Homemaker -->
                  </div>

                </div>

              </div>
            </div>

            <div id="step-2">
              <h3 class="border-bottom border-gray pb-2">
                Enquiry For
              </h3>
              <div>
                <div class="row">
                  <div class="col-md-6 form-group mb-3">
                    <label for="firstName1">Class Name</label>
                    <!-- <select id="classname" class="form-control" name="classname">
                      <option value="" disabled selected>Please select</option>
                      @foreach(config('global.class_name') as $each)
                      <option value="{{$each}}">{{$each}}</option>
                      @endforeach
                    </select> -->

                    <select id="classname" class="form-control" name="classname" autocomplete="" required>
                      <option value="" disabled selected>Please select</option>
                      @if(!empty($classlist))
                      <?php //print_r($datas); ?>
                        @foreach($classlist as $each)
                          @if(!empty($classname) && $each->class_name == $classname)
                          <option value="{{$each->class_name}}" selected>{{$each->class_name}}</option>
                          @else
                          <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                          @endif
                        @endforeach
                      @endif
                    </select>
                    <span class="classname_msg validation_err"></span>
                  </div>
                  <div class="col-md-6 form-group mb-3">
                    <label for="enquiryno">Admission Type</label>
                    <select id="admission_type" class="form-control" name="admission_type" autocomplete="">
                      <option value="" disabled selected>Please select</option>
                      @foreach(config('global.admission_type') as $each)
                      <option value="{{$each}}">{{$each}}</option>
                      @endforeach
                    </select>
                    <span class="addmission_msg validation_err"></span>
                  </div>
                  <div class="col-md-6 form-group mb-3">
                    <label for="pincode">Email Id</label>
                    <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter email" fdprocessedid="csh6vi" name="email">
                    <span class="application_for_msg validation_err" id="validation_err3"></span>
                  </div>
                  <!-- <div class="col-md-3 form-group mb-3">
                           <label for="remark">Remark</label>
                           <input class="form-control" id="remark" type="text" name="remark"
                             placeholder="Enter remark"
                           />
                        </div> -->
                  <div class="col-md-6 form-group mb-3">
                    <label for="remark">Remark</label>
                    <select id="" class="form-control" name="remarkstatus" autocomplete="" required>
                      <option value="" disabled selected>Please select</option>
                      
                      @foreach(config('global.remarkstatus') as $each)
                      <option value="{{$each}}">{{$each}}</option>
                      <?php if ($each == 'Form Taken' or $each == 'Pending') { ?>
                      <?php } ?>
                      @endforeach
                    </select>
                  </div>

                  <!-- <div class="col-md-3 form-group mb-3">
                            <label for="remark">Remark Status</label>
                           <select id="" class="form-control" name="remark" autocomplete="">
                              <option value="" disabled selected>Please select</option>
                              <option value="Fees Structure">Fees Structure</option>
                              <option value="Co-Curricular Activities">Co-Curricular Activities</option>
                              <option value="Co-Curricular Activities">Co-Curricular Activities</option>
                              <option value="Infrastructure">Infrastructure</option>
                              <option value="Games/ Sports">Games/ Sports</option>
                              <option value="If reject,Interview Not cleared">If reject,Interview Not cleared</option>
                              <option value="Others">Others</option>
                           </select>
                         </div> -->
                  <div class="form-group mb-3">
                  </div>

                  <div class="col-md-3 form-group mb-3">
                    <label for="state">State</label>
                    <select id="state-dd" class="form-control" name="state">
                      <option value="">Select</option>
                      @foreach ($states as $data)
                      <option value="{{$data->id}}">{{$data->name}}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-3 form-group mb-3">
                    <label for="city">City</label>
                    <select id="city-dd" class="form-control" name="city">
                    </select>
                  </div>


                  <div class="col-md-3 form-group mb-3">
                    <label for="address">Address</label>
                    <input class="form-control uperletter" id="address" type="text" placeholder="Enter your address" name="address" />
                  </div>

                  <div class="col-md-3 form-group mb-3">
                    <label for="pincode">Pin</label>
                    <input class="form-control" id="pincode" type="text" placeholder="Enter your pin" name="pincode" />
                  </div>



                  <div class="col-md-3 form-group mb-3">
                    <label for="religion">Religion</label>
                    <select id="religion" class="form-control" name="religion" autocomplete="">
                      <option value="" disabled selected>Please select</option>
                      @foreach(config('global.religion') as $each)
                      <option value="{{$each}}">{{$each}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3 form-group mb-3">
                    <label for="category">Category</label>
                    <select id="category" class="form-control" name="category" autocomplete="">
                      <option value="" disabled selected>Please select</option>
                      @foreach(config('global.cate') as $each)
                      <option value="{{$each}}">{{$each}}</option>
                      @endforeach
                    </select>
                    <span class="category_msg validation_err"></span>
                  </div>

                  <!-- <div class="col-md-3 form-group mb-3">
                           <label for="caste">Caste</label>
                           <select id="caste" class="form-control" name="caste" autocomplete="" >
                              <option value="" disabled selected>Please select</option>
                          
                               @foreach ($caste as $each)
                                     <option value="{{$each->caste_name}}">{{$each->caste_name}}</option> 
                              @endforeach
                           </select>
                         </div> -->
                  <div class="col-md-6 form-group mb-3">
                    <label for="category">Select and Enter Caste Name</label>

                    <input type="text" name="caste" id="tags" class="form-control uperletter">
                    <span class="caste_msg validation_err"></span>
                  </div>



                  <div class="col-md-3 form-group mb-3">
                    <label for="received_amount">Received Amount</label>
                    <!--   <input
                             class="form-control"
                             id="received_amount"
                             type="text"
                             placeholder="Enter ammount" name="received_amount"
                           /> -->
                    <select id="received_amount" class="form-control" onchange='checkIfYes()' name="received_amount" autocomplete="">
                      <option value="" disabled selected>Please select</option>
                      @foreach(config('global.receivedammount') as $each)
                      <option value="{{$each}}">{{$each}}</option>
                      @endforeach
                    </select>
                  </div>
                  
                  <div id="extra" name="extra" style="display: none" class="col-md-3 form-group mb-3">

                    <label for="presentlyclass">Reference Number</label>
                    <input class="form-control" type="text" id="desc" name="reference_number" required>
                  </div>
                  <div id="amount" name="amount" class="col-md-3 form-group mb-3">
                    <label for="presentlyclass">Amount</label>
                    <input class="form-control" type="text" placeholder="Add Amount" id="amount" name="amount" required>
                  </div>
                  <div class="col-md-3 form-group mb-3">
                    <label for="presentlyclass">Presently Studing in Class</label>
                    <select id="presentlyclass" class="form-control" name="presentlyclass" autocomplete="">
                      <option value="" selected>Please select</option>
                            @foreach($classlist as $each)
                              <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                              @endforeach 
                    </select>
                  </div>
                  <div class="col-md-3 form-group mb-3">
                    <label for="presentlyschool">Presently School Name</label>
                    <input class="form-control uperletter" id="presentlyschool" type="text" placeholder="Enter School name" name="presentlyschool">
                  </div>
                  <div class="col-md-3 form-group mb-3">
                    <label for="hear_aboutus">How did you hear about us</label>
                    <select id="hear_aboutus" class="form-control" name="hear_aboutus" autocomplete="">
                      <option value="" disabled selected>Please select</option>
                      @foreach(config('global.hearaboutus') as $each)
                      <option value="{{$each}}">{{$each}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6 form-group mb-3">
                    <label for="follow-up-date">Follow Up Date</label>
                    <input class="form-control" type="date" id="picker2-" type="text" placeholder="dd-mm-yyyy" name="follow_up_date" max="9999-12-31">
                  </div>
                  <div class="col-md-6 form-group mb-3">
                    <label for="enquiry_through">Enquiry through</label>
                    <select id="enquiry_through" class="form-control" name="enquiry_through" autocomplete="">
                      <option value="" disabled selected>Please select</option>
                      @foreach(config('global.enquirythrough') as $each)
                      <option value="{{$each}}">{{$each}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>


              </div>
            </div>
            <div id="step-3">
              <h3 class="border-bottom border-gray pb-2">
                Details of Siblings
              </h3>
              <div>
                <div class="row">
                  <div class="row">
                    <h6 class="field_wrapper">Do you have any siblings?
                      <input class="coupon_question" type="checkbox" name="coupon_question" value="1" id="checkBox" />
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

                    <span class="allinput_msg validation_err"></span>

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
                    <div class="separator-breadcrumb border-top"></div>
                    <div class="col-md-12">
                      <input type="hidden" name="iid" value="" name="">
                      <!-- <input type="submit" name="submit" value="Submit" class="btn btn-primary submit_btn" id="submitbutton"> -->
                      <button class="btn btn-primary submit_btn btn-submit" type="submit">Submit</button>
                    </div>

                  </div>
                </div>
              </div>


            </div>
          </div>
        </div>
      </div>
    </div>
</form>
<!-- </div>
            </div> -->
<!-- end of main-content -->
<!-- </div> -->

<script>
  document.addEventListener('DOMContentLoaded', function () {
      var select = document.getElementById('state-dd');
      for (var i = 0; i < select.options.length; i++) {
          select.options[i].innerText = capitalizeFirstLetter(select.options[i].innerText);
      }
  });

  function capitalizeFirstLetter(str) {
      var words = str.toLowerCase().split(' ');
      for (var i = 0; i < words.length; i++) {
          words[i] = words[i].charAt(0).toUpperCase() + words[i].substring(1);
      }
      return words.join(' ');
  }
</script>


<script src="{{url('assets/backend')}}/js/plugins/jquery-3.3.1.min.js"></script>
<script type="text/javascript">
  function check() {
    var mobile = document.getElementById('fathermobile');
    var message = document.getElementById('validation_err');
    if (mobile.value.length != 10) {
      message.innerHTML = "required 10 digits, match requested format!"
    } else {
      message.innerHTML = "";
    }
  }

  function check2() {
    var mobile = document.getElementById('mothermobile');
    var message2 = document.getElementById('validation_err2');
    if (mobile.value.length != 10) {
      message2.innerHTML = "required 10 digits, match requested format!"
    } else {
      message2.innerHTML = "";
    }
  }

  $('#exampleInputEmail1').on('keyup', function() {
    var re = /([A-Z0-9a-z_-][^@])+?@[^$#<>?]+?\.[\w]{2,4}/.test(this.value);
    if (!re) {
      $("#validation_err3").html("Invalid Email Format").show().fadeOut(3000);
    } else {
      $("#validation_err3").hide();
    }
  });

  $(".submit_btn").on('click', function(e) {

    e.preventDefault();

    var session = $('input[name="session"]').val();
    var next_year = $('input[name="next_year"]').val();
    var studentname = $('input[name="studentname"]').val();
    var enquiryno = $('input[name="enquiryno"]').val();
    var formno = $('input[name="formno"]').val();
    // var student_caste = $('input[name="student_caste"]').val();
    // var religion = $('select[name="religion"]').val();
    var classname = $('select[name="classname"]').val();
    var addmission = $('select[name="admission_type"]').val();
    var student_dob = $('input[name="student_dob"]').val();

    var gender = $('select[name="gender"]').val();
    var fathername = $('input[name="fathername"]').val();
    var fathermobile = $('input[name="fathermobile"]').val();

    var category = $('select[name="category"]').val();
    var caste = $('input[name="caste"]').val();
    var coupon_question = $('input[name="coupon_question"]').val();
    var r = $('#checkBox').is(':checked') ? $('#checkBox').val() : false;
    //alert(r);
    var allinputmsg = "";
    var allinputmsg2 = "";
    if (session == null) {
      $('.session_msg').text("This field is required*");
      allinputmsg = '1';

    } else {

      $('.session_msg').text("");
    }

    if (studentname == '') {
      $('.student_name_msg').text("This field is required*");
      allinputmsg = '1';
    } else {

      $('.student_name_msg').text("");
    }
    if (enquiryno == '') {
      $('.enquiryno_msg').text("This field is required*");
      //allinputmsg+= ", Enquiry No";
    } else {

      $('.enquiryno_msg').text("");
    }
    if (classname == null) {
      $('.classname_msg').text("This field is required*");
      allinputmsg2 = '1';
    } else {

      $('.classname_msg').text("");
    }
    if (addmission == null) {
      $('.addmission_msg').text("This field is required*");
      //allinputmsg+= ", Admission Type";
      allinputmsg2 = '1';
    } else {

      $('.addmission_msg').text("");
    }
    if (formno == '') {
      $('.formno_msg').text("This field is required*");
      //allinputmsg+= ", Enquiry No";
      allinputmsg = '1';
    } else {

      $('.formno_msg').text("");
    }
    if (student_dob == '') {
      $('.student_dob_msg').text("This field is required*");
      //allinputmsg+= ", Enquiry No";
      allinputmsg = '1';
    } else {

      $('.student_dob_msg').text("");
    }

    if (gender == null) {
      $('.gender_msg').text("This field is required*");
      //allinputmsg+= ", Enquiry No";
      allinputmsg = '1';
    } else {

      $('.gender_msg').text("");
    }
    if (fathername == '') {
      $('.fathername_msg').text("This field is required*");
      //allinputmsg+= ", Enquiry No";
      allinputmsg = '1';
    } else {

      $('.fathername_msg').text("");
    }
    if (fathermobile == '') {
      $('.fathermobile_msg').text("This field is required*");
      //allinputmsg+= ", Enquiry No";
      allinputmsg = '1';
    } else {

      $('.fathermobile_msg').text("");
    }

    if (category == null) {
      $('.category_msg').text("This field is required*");
      allinputmsg2 = '1';

    } else {

      $('.category_msg').text("");
    }
    if (caste == '') {
      $('.caste_msg').text("This field is required*");
      allinputmsg2 = '1';
    } else {

      $('.caste_msg').text("");
    }
    //alert(coupon_question);
    if (r == '1') {
      var siblings_namesecond = $('input[name="siblings_namesecond[0]"]').val();
      var sibling_class_second = $('select[name="sibling_class_second[0]"]').val();
      var siblings_section_second = $('input[name="siblings_section_second[0]"]').val();
      var siblings_bod_second = $('input[name="siblings_bod_second[0]"]').val();
      //alert("first");
      if (siblings_namesecond == '') {

        $('.siblings_namesecond_msg').text("This field is required*");
        //allinputmsg+= ", Enquiry No";
      } else {
        $('.siblings_namesecond_msg').text("");
      }
      if (sibling_class_second == null) {

        $('.sibling_class_second_msg').text("This field is required*");
        //allinputmsg+= ", Enquiry No";
      } else {

        $('.sibling_class_second_msg').text("");
      }
      if (siblings_section_second == '') {

        $('.siblings_section_second_msg').text("This field is required*");
        //allinputmsg+= ", Enquiry No";
      } else {

        $('.siblings_section_second_msg').text("");
      }
      if (siblings_bod_second == '') {

        $('.siblings_bod_second_msg').text("This field is required*");
        //allinputmsg+= ", Enquiry No";
      } else {

        $('.siblings_bod_second_msg').text("");
      }
      $('.session_msg').text("This field is required*");
      //  allinputmsg = '1'; 

    } else {

      $('.session_msg').text("");
    }




    if (studentname !== '' && session !== null && classname !== null && formno !== '' && student_dob !== '' && gender !== null && fathername !== '' && fathermobile !== '' && addmission !== null && category !== null && caste !== '') {
      $('.rg_form').submit();
      var myForm = document.getElementById("form-id");
      event.preventDefault();
      myForm.submit();

    } else {
      $('.allinput_msg').text("All required fields must be completed before you submit the form*");
      if (allinputmsg == "1" || allinputmsg != "") {
        $("#step1").addClass('s1');
      } else {
        $("#step1").removeClass('s1');
      }

      if (allinputmsg2 == "1" || allinputmsg2 != "") {
        $("#step2").addClass('s1');
      } else {
        $("#step2").removeClass('s1');
      }

      console.log("invalid form");
    }


  });
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
  $(document).ready(function() {
    $('#country-dd').on('change', function() {
      var idCountry = this.value;
      $("#state-dd").html('');
      $.ajax({
        url: "{{url('api/fetch-states')}}",
        type: "POST",
        data: {
          country_id: idCountry,
          _token: '{{csrf_token()}}'
        },
        dataType: 'json',
        success: function(result) {
          $('#state-dd').html('<option value="">Select State</option>');
          $.each(result.states, function(key, value) {
            $("#state-dd").append('<option value="' + value
              .id + '">' + value.name + '</option>');
          });
          $('#city-dd').html('<option value="">Select City</option>');

        }
      });
    });
    $('#state-dd').on('change', function() {
      var idState = this.value;
      $("#city-dd").html('');
      $.ajax({
        url: "{{url('api/fetch-cities')}}",
        type: "POST",
        data: {
          state_id: idState,
          _token: '{{csrf_token()}}'
        },
        dataType: 'json',
        success: function(res) {
          $('#city-dd').html('<option value="">Select City</option>');
          $.each(res.cities, function(key, value) {
            $("#city-dd").append('<option value="' + value
              .name + '">' + value.name + '</option>');
          });
        }
      });
    });
  });
</script>
<script type="text/javascript">
  var max_group = 5;
  var max_field = 10;
  var add_button = $('.add_button');
  var wrapper = $('.field_wrapper');
  var i = 1;
  var html_fields = '' +
    '<tr class="item">' +
    '<td><input class="form-control" id="siblings_namesecond[' + i + ']" type="text" name="siblings_namesecond[' + i + ']"></td><td><select id="sibling_class_second[' + i + ']" class="form-control" name="sibling_class_second[' + i + ']"  autocomplete=""><option value="" disabled selected>Please select</option>@foreach(config("global.class_name") as $each)<option value="{{$each}}">{{$each}}</option>@endforeach</select></td> ' +
    '<td><select id="siblings_section_second[' + i + ']" class="form-control" name="siblings_section_second[' + i + ']" autocomplete="" ><option value="" disabled selected>Please select</option><option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option></select></td> ' +
    '<td><input class="form-control" type="date"  id="picker2-" type="text" placeholder="dd-mm-yyyy"  name="siblings_bod_second[' + i + ']"></td> ' +
    '<td><a href="javascript:void(0);" class="remove_button btn btn-sm btn-danger"><i class="fa fa-minus"></i></a> </td>' +
    '</tr>';

  var x = 1;
  var y = 1;
  i++;

  $(add_button).click(function() {
    if (x < max_field) {
      x++;
      $(this).closest(wrapper).append(html_fields);
    }
  });


  $(wrapper).on('click', '.remove_button', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
    x--;
  });

  // sibling details hide
  // $(".answer").hide();
  // $(".coupon_question").click(function() 
  // {
  //     if($(this).is(":checked")) {
  //         $(".answer").show();
  //     } else {
  //         $(".answer").hide();
  //     }
  // });

  // end 

  function getValAndAssign() {
    var form_number = $("#searchenquiryno").val();
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
        if (response.inq_data) { //alert(response.statevalue);
          /*assign value on pick data*/
          // $('select[name="application_for"] option[value='+response.inq_str_data.admission_type+']').attr("selected","selected");
          $('input[name="eno"]').val(response.inq_str_data.enquiryno);
          $('input[name="enquiryno"]').val(response.inq_str_data.enquiryno);
          $('select[name="application_for"]').val(response.inq_str_data.admission_type);
          $('select[name="session"]').val(response.inq_data.session_name);
          $('input[name="formno"]').val(response.inq_data.form_number);
          $('input[name="enquiryno"]').val(response.inq_data.form_number);
          if (response.inq_data.next_year === '1') {
            $('input[name="next_year"]').prop('checked', true);
          } else {
            $('input[name="next_year"]').prop('checked', false);
          }
          $('select[name="studentname_prefix"]').val(response.inq_str_data.studentname_prefix);
          $('input[name="studentname"]').val(response.inq_data.student_name);
          $('select[name="gender"]').val(response.inq_str_data.gender);
          $('input[name="student_dob"]').val(response.inq_data.date_of_birth);
          $('input[name="enquirydate"]').val(response.inq_str_data.enquirydate);
          $('input[name="email"]').val(response.inq_str_data.email);

          $('input[name="caste"]').val(response.inq_str_data.caste);
          $('select[name="religion"]').val(response.inq_str_data.religion);
          $('select[name="category"]').val(response.inq_str_data.category);
          $('select[name="classname"]').val(response.inq_data.class_name);

          $('input[name="present_address"]').val(response.present_address);
          $('input[name="permanent_address"]').val(response.permanent_address);
          $('input[name="phone_number"]').val(response.phone_number);
          $('input[name="mobile_number"]').val(response.mobile_number);
          $('select[name="fathername_prefix"]').val(response.inq_str_data.fathername_prefix);
          $('input[name="fathername"]').val(response.inq_str_data.fathername);
          $('input[name="fathermobile"]').val(response.inq_str_data.fathermobile);
          $('input[name="father_res_address"]').val(response.inq_str_data.father_resi);
          $('select[name="mothername_prefix"]').val(response.inq_str_data.mothername_prefix);
          $('input[name="mothername"]').val(response.inq_str_data.mothername);
          $('input[name="mothermobile"]').val(response.inq_str_data.mothermobile);
          $('input[name="mother_res_address"]').val(response.inq_str_data.mother_resi);
          $('input[name="mother_emergency_contact"]').val(response.inq_str_data.mother_mobile);
          $('input[name="address"]').val(response.inq_str_data.address);
          $('select[name="state"]').val(response.statevalue);
          //$('select[name="state"]').val(response.inq_str_data.state);
          // $('#state_id').html(response);
          $('select[name="city"]').html('<option value="' + response.inq_str_data.city + '">' + response.inq_str_data.city + '</option>');
          //  $('select[name="city"]').val(response.inq_str_data.city);
          $('input[name="pincode"]').val(response.inq_str_data.pincode);
          $('select[name="admission_type"]').val(response.inq_str_data.admission_type);
          $('select[name="fatheroccupation"]').val(response.inq_str_data.fatheroccupation);
          $('select[name="motheroccupation"]').val(response.inq_str_data.motheroccupation);
          $('input[name="follow_up_date"]').val(response.inq_str_data.follow_up_date);


          $('input[name="iid"]').val(response.inq_data.id);
          $('.error_msg').text("");
          $('.error_msg2').text("One Records are Available ");




        }

      },
      error: function() {
        // alert('There was some error performing the AJAX call!');
        $('select[name="application_for"]').val("");
        $('select[name="session"]').val("");
        $('input[name="formno"]').val("");
        $('input[name="enquiryno"]').val("");
        $('input[name="next_year"]').val("");
        $('select[name="studentname_prefix"]').val("");
        $('input[name="studentname"]').val("");
        $('select[name="gender"]').val("");
        $('input[name="student_dob"]').val("");
        $('input[name="enquirydate"]').val("");

        $('input[name="student_caste"]').val("");
        $('select[name="religion"]').val("");
        $('select[name="category"]').val("");
        $('select[name="classname"]').val("");

        $('input[name="present_address"]').val("");
        $('input[name="permanent_address"]').val("");
        $('input[name="phone_number"]').val("");
        $('input[name="mobile_number"]').val("");
        $('select[name="fathername_prefix"]').val("");
        $('input[name="fathername"]').val("");
        $('input[name="fathermobile"]').val("");
        $('input[name="father_res_address"]').val("");
        $('select[name="mothername_prefix"]').val("");
        $('input[name="mothername"]').val("");
        $('input[name="mothermobile"]').val("");
        $('input[name="mother_res_address"]').val("");
        $('input[name="mother_emergency_contact"]').val("");

        $('input[name="address"]').val("");
        $('select[name="state"]').val("");
        $('select[name="city"]').val("");
        $('input[name="pincode"]').val("");
        $('select[name="admission_type"]').val("");
        $('input[name="iid"]').val("");
        $('.error_msg').text("No Records Available");
        $('.error_msg2').text("");

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
  $(document).ready(function() {
    $('#smartwizard').smartWizard({
      // Properties
      selected: 0, // Selected Step, 0 = first step   
      keyNavigation: false, // Enable/Disable key navigation(left and right keys are used if enabled)
      enableAllSteps: false, // Enable/Disable all steps on first load

      buttonOrder: ['finish', 'next', 'prev'] // button order, to hide a button remove it from the list
    });
  });

  // Caste name search
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
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script type="text/javascript">
  $.noConflict();
</script>

<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script>
  $.noConflict();
  jQuery(document).ready(function($) {
    var availableTags = [
      <?php foreach ($caste as $each) { ?> "<?php echo $each->caste_name; ?>",
      <?php } ?>

    ];
    $("#tags").autocomplete({
      source: availableTags
    });
  });
  jQuery(document).ready(function($) {
    var presentlyschool = [
      <?php foreach ($presentlyschool as $each) { ?> "<?php echo $each->school_name; ?>",
      <?php } ?>

    ];
    $("#presentlyschool").autocomplete({
      source: presentlyschool
    });
  });
  // presentlyschool
  jQuery(document).ready(function($) {

    var availableTags2 = [
      <?php foreach ($all_inquiry as $each2) {
        $inq_str_data = json_decode($each2->json_str, true);  ?> "<?php if (!empty($inq_str_data['fathername'])) {
                                                                    echo $inq_str_data['fathername'];
                                                                  } ?>",
      <?php } ?>

    ];
    console.log("hyy");
    $("#searchfather").autocomplete({
      source: availableTags2
    });
  });
  jQuery(document).ready(function($) {

    var availableTags3 = [
      <?php foreach ($all_inquiry as $each2) {
        $inq_str_data = json_decode($each2->json_str, true);  ?> "<?php if (!empty($inq_str_data['fathername'])) {
                                                                    echo $inq_str_data['fathername'];
                                                                  } ?>",
        "<?php if (!empty($each2->student_name)) {
            echo $each2->student_name;
          } ?>",
        // "<?php if (!empty($inq_str_data['fathermobile'])) {
              echo $inq_str_data['fathermobile'];
            } ?>",
        "<?php if (!empty($inq_str_data['enquiryno'])) {
            echo $inq_str_data['enquiryno'];
          } ?>",
      <?php } ?>

    ];
    $("#searchenquiryno").autocomplete({
      source: availableTags3
    });

    setTimeout(function() {
      var year = $("#year").val()
      $("#session").val(year);
    }, 1000);
  });

  function checkIfYes() {
    if (document.getElementById('received_amount').value == 'Online') {
      document.getElementById('extra').style.display = '';
      document.getElementById('auth_by').disabled = false;
      document.getElementById('desc').disabled = false;
    } else {
      document.getElementById('extra').style.display = 'none';
    }
  }
</script>
@endsection