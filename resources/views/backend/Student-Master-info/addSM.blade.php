@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
   <div class="breadcrumb">
      <h1 class="me-2">Student Registration</h1>
   </div>
   <div class="separator-breadcrumb border-top"></div>
   <div class="row">
      <div class="col-md-6 p-2">
         <label for="firstName1">Select Form No to Pick Information form inquiry</label>
         <select id="inq-form-no" class="form-control" name="inq_form_selection" required>
            <option disabled selected>Please select</option>
            @if(!empty($inqArr))
            @foreach($inqArr as $each)
            <option value="{{$each->form_number}}">{{$each->form_number}} {{$each->student_name}}</option>
            @endforeach
            @endif
         </select>
      </div>
      <div class="col-md-6" style="padding-top: 28px!important;">
         <button class="btn btn-primary pick_inq_data">Pick Data</button>
      </div>
      <div class="col-md-12">
         <!--  SmartWizard html -->
         <div id="smartwizard">
            <ul>
               <li><a href="#step-1">Step 1<br /><small>Student Details</small></a></li>
               <li>
                  <a href="#step-2"
                     >Step 2<br /><small>Personal Details</small></a
                     >
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
                     Student Enquiry Entry Form
                  </h3>
                  <div class="form_section1_div">
                     <form class="needs-validation" novalidate="novalidate" method="post" action="{{url('save-student-inquiry')}}">
                        <div class="row">
                           <div class="col-md-4 form-group mb-3">
                              <label for="firstName1">Application For:</label>
                              <select id="application_for" class="form-control" name="application_for" autocomplete="shipping address-level1" required>
                                 <option value="" disabled selected>Please select</option>
                                 <option value="">Day School</option>
                              </select>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="studentname">Student Name</label>
                              <input name="student_name" 
                                 class="form-control"
                                 id="studentname"
                                 placeholder="Enter Student Name"
                                 />
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="studentname">Nationality</label>
                              <input name="nationality" 
                                 class="form-control"
                                 id="studentname"
                                 placeholder="Enter Nationality"
                                 />
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="firstName1">Gender</label>
                              <select id="gender" class="form-control" name="gender" autocomplete="shipping address-level1" required>
                                 <option value="" disabled selected>Please select</option>
                                 @foreach(config('global.gender') as $each)
                                 <option value="{{$each}}">{{$each}}</option>
                                 @endforeach
                              </select>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="Caste">Caste</label>
                              <input class="form-control Caste" id="Caste" placeholder="Student Caste" name="student_caste" />
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="religion">Religion</label>
                              <select id="religion" class="form-control" name="religion" autocomplete="" required>
                                 <option value="" disabled selected>Please select</option>
                                 @foreach(config('global.religion') as $each)
                                 <option value="{{$each}}">{{$each}}</option>
                                 @endforeach
                              </select>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="category">Category</label>
                              <select id="category" class="form-control" name="category" autocomplete="" required>
                                 <option value="" disabled selected>Please select</option>
                                 @foreach(config('global.cate') as $each)
                                 <option value="{{$each}}">{{$each}}</option>
                                 @endforeach
                              </select>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="firstName1">Class Name</label>
                              <select id="classname" class="form-control" name="classname" autocomplete="" required>
                                 <option value="" disabled selected>Please select</option>
                                 @foreach(config('global.class_name') as $each)
                                 <option value="{{$each}}">{{$each}}</option>
                                 @endforeach
                              </select>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="firstName1">Session Name</label>
                              <select id="session_name" class="form-control" name="session_name" autocomplete="" required>
                                 <option value="" disabled selected>Please select</option>
                                 @foreach(config('global.session_name') as $each)
                                 <option value="{{$each}}">{{$each}}</option>
                                 @endforeach
                              </select>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="address">Present Address :</label>
                              <input class="form-control" id="address" type="text" placeholder="Enter address" name="present_address" />
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="address">Permanent Address :</label>
                              <input class="form-control" id="address" type="text" placeholder="Enter address" name="permanent_address" />
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="address">Phone Number :</label>
                              <input class="form-control" id="phone number" type="text" placeholder="Enter phone number" name="phone_number" />
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="address">Mobile Number :</label>
                              <input class="form-control" id="phone number" type="text" placeholder="Enter phone number" name="mobile_number" />
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="firstName1">Mother tongue</label>
                              <input name="mother_name" class="form-control" id="mother_tongue" type="text" placeholder="Enter Mother tongue" />
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="remark">Remarks</label>
                              <input name="remarks" class="form-control" id="remarks" type="text"placeholder="Enter remark" />
                           </div>
                           <div class="col-md-6 form-group mb-3">
                              <label for="remark">vaccaination</label>
                              <input name="vaccaination" class="form-control" id="vaccaination" type="text"placeholder="Enter vaccaination" />
                           </div>
                           <div class="col-md-6 form-group mb`-3">
                              <label for="remark">Medical Conserns (any)</label>
                              <input name='student_medical_conserns' class="form-control" id="Medical Conserns (any)" type="text"placeholder="Enter Medical Conserns (any)" />
                           </div>
                           <div class="col-md-4 form-group mb`-3">
                              <label for="remark">Name of Present Play School / Day Care (if any)</label>
                              <input name="present_school_name" class="form-control" id="Medical Conserns (any)" type="text" placeholder="Enter Name of Present Play School / Day Care (if any)" />
                              <div class="mt-1 form__field">
                                 <label class="form__choice-wrapper">
                                 <input id="" type="checkbox" name="is_sibling_applied_for_admission" value="Yes">
                                 <span>If a Sibling (real Brother / Sister ) also applying for addmission into the school. Please give Details.</span>
                                 </label>
                              </div>
                              <div class="mt-1 form__field">
                                 <label class="form__choice-wrapper">
                                 <input name="required_school_trasnport" id="transport" type="checkbox" name="transport">
                                 <span>Require School Transport</span>
                                 </label>
                              </div>
                              <div class="mt-1 form__field">
                                 <label class="form__choice-wrapper">
                                 <input id="birth-certificate" type="checkbox" name="birth_certificate" value="Yes" >
                                 <span>Birth Certificate</span>
                                 </label>
                              </div>
                              <div class="mt-1 form__field">
                                 <label class="form__choice-wrapper">
                                 <input id="transfer-certificate" type="checkbox" name="transfer_certificate" value="Yes" >
                                 <span>Transfer Certificate</span>
                                 </label>
                              </div>
                              <div class="mt-1 form__field">
                                 <label class="form__choice-wrapper">
                                 <input id="address-proff" type="checkbox" name="address_proof" value="Yes" >
                                 <span>Address Proff</span>
                                 </label>
                              </div>
                              <div class="mt-1 form__field">
                                 <label class="form__choice-wrapper">
                                 <input id="lasr-repor-card" type="checkbox" name="last_report_card" value="Yes" >
                                 <span>Last Report Card</span>
                                 </label>
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
               <div class="col-md-4 form-group mb-3">
               <label for="fathername">Father Name</label>
               <input name="student_father_name" class="form-control" id="fathername" type="text" placeholder="Enter father name" />
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Date Of Birth</label>
               <input name="father_dob" class="form-control date4" id="picker2-" type="text" placeholder="dd-mm-yyyy"  name="siblings_bod">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Education</label>
               <input name="father_education" class="form-control date4" id="picker2-" type="text" placeholder="Enter Education"  name="father_education">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Organization</label>
               <input name="father_organization" class="form-control date4" id="picker2-" type="text" placeholder="Enter Organization"  name="father_education">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="mothername">Designation</label>
               <input name="father_designation"
                  class="form-control"
                  id="fatheroccupation"
                  type="text"
                  placeholder="Enter father occupation"
                  />
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Office Telephone</label>
               <input name="father_office_telephone" class="form-control date4" id="picker2-" type="text" placeholder="Enter Organization"  name="father_education">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Email id</label>
               <input name="father_email_id" class="form-control date4" id="picker2-" type="email" placeholder="Enter Email" >
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Mobile No.</label>
               <input name="father_mobile" class="form-control date4" id="picker2-" type="email" placeholder="Enter Email"  >
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Residental Address</label>
               <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Email"  name="father_res_address">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Emergency contact No.</label>
               <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Email"  name="father_emergency_contact">
               </div>
               <h4>Mother's Details</h4>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">Mother Name</label>
               <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter email" fdprocessedid="csh6vi" name="mother_name">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">Date of Birth</label>
               <input class="form-control" id="exampleInputEmail1" type="date"  name="mother_dob">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">Education</label>
               <input class="form-control" id="exampleInputEmail1" type="text" placeholder="Enter email" name="mother_education">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">Occupation</label>
               <input class="form-control" id="exampleInputEmail1" type="text" placeholder="Enter Mother Occupation" name="mother_ocupation">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">Organization</label>
               <input class="form-control" id="exampleInputEmail1" type="text" placeholder="Enter Organization" name="mother_organization">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">Designation</label>
               <input class="form-control" id="exampleInputEmail1" type="text" placeholder="Enter Designation" name="mother_organization">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Office Telephone</label>
               <input class="form-control date4" id="picker2-" type="text" placeholder="Enter Organization"  name="mother_office_telephone">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Email id</label>
               <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Email"  name="mother_email">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="phone">Mobile No.</label>
               <input
                  class="form-control"
                  id="phone"
                  placeholder="Enter mobile no" name="mother_mobile"
                  />
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Residental Address</label>
               <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Email"  name="mother_res_address">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Emergency contact No.</label>
               <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Email"  name="mother_emergency_contact">
               </div>
               <h4>Guardian Details</h4>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Guardian Name</label>
               <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Email"  name="guardian_name">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Office Telephone</label>
               <input class="form-control date4" id="picker2-" type="text" placeholder="Enter Organization"  name="guardian_office_telephone">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Email id</label>
               <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Email"  name="guardian_email_id">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="phone">Mobile No.</label>
               <input name="guardian_mobile" 
                  class="form-control"
                  id="phone"
                  placeholder="Enter mobile no" name="guardian_mobile"
                  />
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="address">Permanent Address :</label>
               <input class="form-control" id="address" type="text" placeholder="Enter address" name="guardian_permanent_address" />
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Emergency contact No.</label>
               <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Email"  name="guardian_emergency_contact">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">Guardian Relation.</label>
               <input class="form-control date4" id="picker2-" type="email" placeholder="Enter Email"  name="guardian_relation">
               </div>
               <h4>Others Details</h4>
               <div class="col-md-4 form-group mb-3">
               <label for="enquiryno">Parents Are</label>
               <select id="admission-type" class="form-control" name="parents_are" autocomplete="" required>
               <option disabled selected>Please select</option>
               <option value="parents">Parents</option>
               </select>
               <input id="adopted" type="checkbox" name="is_child_adopted" value="Yes">
               <span>Child is an Adopted Child</span>
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="enquiryno">Child Live With</label>
               <select id="admission-type" class="form-control" name="child_live_with" autocomplete="" required>
               <option disabled selected>Please select</option>
               <option value="parents">Parents</option>
               </select>
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">Local Guardian</label>
               <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter email" fdprocessedid="csh6vi" name="local_guardian">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">Local Address</label>
               <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter email" fdprocessedid="csh6vi" name="local_address">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">Phone No.</label>
               <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter email" fdprocessedid="csh6vi" name="email">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">Mobile No.</label>
               <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter email" fdprocessedid="csh6vi" name="email">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">Mobile No.</label>
               <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter email" fdprocessedid="csh6vi" name="email">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">Parents Category</label>
               <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter email" fdprocessedid="csh6vi" name="email">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">New Category</label>
               <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter email" fdprocessedid="csh6vi" name="email">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">House</label>
               <select id="admission-type" class="form-control" name="admission_type" autocomplete="" required>
               <option disabled selected>Please select</option>
               <option value="house">house</option>
               </select>
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="pincode">New House</label>
               <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter email" fdprocessedid="csh6vi" name="email">
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
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_name">1.Name</label>
               <input class="form-control" id="siblings_name" type="text" placeholder="Enter name"  name="siblings_name">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="sibling_class">Class</label>
               <select id="sibling_class" class="form-control" name="sibling_class" autocomplete="" >
               <option value="" disabled selected>Please select</option>
               @foreach(config('global.class_name') as $each)
               <option value="{{$each}}">{{$each}}</option>
               @endforeach
               </select>                         
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school">School</label>
               <input class="form-control" id="siblings_school" type="text" placeholder="Enter school name"  name="siblings_school">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_name">2.Name</label>
               <input class="form-control" id="siblings_namesecond" type="text" placeholder="Enter name"  name="siblings_namesecond">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="sibling_class_second">Class</label>
               <select id="sibling_class_second" class="form-control" name="sibling_class_second" autocomplete="" >
               <option value="" disabled selected>Please select</option>
               @foreach(config('global.class_name') as $each)
               <option value="{{$each}}">{{$each}}</option>
               @endforeach
               </select>                         
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school_second">School</label>
               <input class="form-control" id="siblings_school_second" type="text" placeholder="Enter school name"  name="siblings_school_second">
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="siblings_school_second">Date Of Birth</label>
               <input class="form-control date5" id="picker2-" type="text" placeholder="dd-mm-yyyy"  name="siblings_bod_second">
               </div>
               </div>
               </div>
               </div>
               <div id="step-4">
               <h3 class="border-bottom border-gray pb-2">
               Bank Details
               </h3>
               <div>
               <div class="row">
               <div class="col-md-4 form-group mb-3">
               <label for="headname">Head Name</label>
               <select id="headname" class="form-control" name="headname" autocomplete="" >
               <option value="" disabled selected>Please select</option>
               @foreach(config('global.headname') as $each)
               <option value="{{$each}}">{{$each}}</option>
               @endforeach
               </select>                         
               </div>
               <div class="col-md-4 form-group mb-3">
               <label for="chq_cash">DD/Chq/Cash</label>
               <select id="sibling_class_second" class="form-control" name="chq_cash" autocomplete="" >
               <!-- <option value="" disabled selected>Please select</option> -->
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
               </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- end of main-content -->
</div>
<script type="text/javascript">
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
</script>
@endsection