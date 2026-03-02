@extends('layouts.app')
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
input[type="date"] {
    position: relative;
}

input[type="date"]:after {
    content: "\25BC"; 
    color: #555;
    padding: 0 10px;
}
input[type="date"]::-webkit-calendar-picker-indicator {
    position: absolute;
    top: 10px;
    left: 0px;
    right: 0;
    bottom: 0;
    width: auto;
    height: auto;
    color: transparent;
    background: transparent;
}

input[type="date"]::-webkit-inner-spin-button {
    z-index: 1;
}

 input[type="date"]::-webkit-clear-button {
     z-index: 1;
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
.ui-menu .ui-menu-item {
font-size: 0.813rem;
    width: 646px;
}
.input-group > :not(:first-child):not(.dropdown-menu):not(.valid-tooltip):not(.valid-feedback):not(.invalid-tooltip):not(.invalid-feedback) {
    border-radius: 0.375rem;



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
@if (count($errors) > 0)
    <div class="alert alert-danger">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
 <form  method="post" action="{{url('saveenquiry')}}" id="form-id" class="rg_form" >
        <div class="main-content">
          <div class="breadcrumb">
            <h1>Edit Enquiry</h1> 
            <ul>
              <li>           
                    <!-- <a class="btn btn-primary text-white" href="{{ url('followupdate') }}">Go Back</a> -->
            </li>
            </ul>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">           
                    @foreach($all_inquiry as $each_inq)
                         <?php $notificationData = json_decode($each_inq->json_str, true); ?>
   
                    @endforeach        
 <!--  SmartWizard html -->
         <div id="smartwizard">
            <ul>
               <li class="" id="step1"><a href="#step-1">Step 1<br /><small>Student Details</small></a></li>
               <li id="step2">
                  <a href="#step-2"
                     >Step 2<br /><small>Enquiry For</small></a
                     >
               </li>
               <li>
                  <a href="#step-3"
                     >Step 3<br /><small>Details of Siblings</small></a
                     >
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
                     Student Edit Enquiry
                  </h3>
                 
                  <div class="form_section1_div">
                       
                       <div class="row">
                       <!--  <div class="col-md-4 form-group mb-3">
                            <label for="student">Student Name</label>
                             <input type="text" name="sname" value="{{ $each_inq->student_name }}" class="form-control" placeholder="sName">                        
                        </div> -->
                        <div class="col-md-4 form-group mb-3">
                           <label for="firstName1">Enquiry Session:<b class="validation_err">*</b></label>
                           <input name="_token" type="hidden" value="{{ csrf_token() }}">
                           <!-- <select id="session" class="form-control" name="session" autocomplete="shipping address-level1" >
                              <option value="" disabled selected>Please Select</option>
                              @foreach(config('global.session_name') as $each)
                              <option <?php //if($each_inq->session_name == $each){ echo "selected";}?>  value="{{$each}}">{{$each}}</option>
                              @endforeach
                           </select> -->
                           <input type="text" readonly id="session_name" class="form-control" value="" name="session_name">
                           <span class="session_msg validation_err"></span>

                         </div>
                         <div class="col-md-2 form-group mb-3">
                          <label for="enquiryno">Enquiry For Next Year</label>
                          <br>
                          <input id="next_year" type="checkbox" <?php echo (empty($each_inq->next_year)) ? '' : 'checked="checked"'; ?> name="next_year" value="1" />

                          <span class="enquiryno_msg validation_err"></span>
                        </div>
                        <div class="col-lg-6 form-group mb-3">

                           <label for="studentname">Student Name<b class="validation_err">*</b></label>
                           <div class="input-group mb-3" >
                                  <div class="input-group-prepend">
                                   <select id="studentname_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="studentname_prefix" autocomplete="shipping address-level1" >
                                        <option <?php if(!empty($notificationData['studentname_prefix'])){ if($notificationData['studentname_prefix'] == "Master"){ echo "selected";} }?>  value="Master">Master</option>
                                        <option <?php if(!empty($notificationData['studentname_prefix'])){ if($notificationData['studentname_prefix'] == "Mr"){ echo "selected";} }?>  value="Mr">Mr.</option>
                                        <option <?php if(!empty($notificationData['studentname_prefix'])){ if($notificationData['studentname_prefix'] == "Miss"){ echo "selected";} }?>  value="Miss">Miss</option>
                                     </select>
                                  </div>
                                  <input type="text"
                                     class="form-control uperletter"
                                     id="studentname"
                                     placeholder="Enter Student Name" name="studentname" value="<?php if(!empty($each_inq->student_name)){ echo $each_inq->student_name;}?>" required />
                                     <span class="student_name_msg validation_err"></span>
                                </div>                          
                         </div> 

                         <div class="col-md-4 form-group mb-3">
                           <label for="enquiryno">Enquiry No.</label>
                           <input
                             class="form-control"
                             id="enquiryno"
                             type="text"
                             placeholder="Enter your Enquiry no." name="enquiryno" required value="<?php if(!empty($notificationData['enquiryno'])){ echo $notificationData['enquiryno'];} ?>"
                           />
                           <input type="hidden" name="enquiryno2" class="form-control" value="<?php if(!empty($notificationData['enquiryno'])){ echo $notificationData['enquiryno'];} ?>">
                         
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
                           <input
                             class="form-control"
                             id="formno"
                             placeholder="" name="formno" required value="<?php if(!empty($notificationData['formno'])){ echo $notificationData['formno'];} ?>"
                           /><span class="formno_msg validation_err"></span>
                         </div>
                         <div class="col-md-4 form-group mb-3">
                           <label for="picker2">Enquiry Date</label>
                           <input
                             class="form-control "
                             id="picker2"
                             placeholder="dd-mm-yyyy" max="9999-12-31"
                             name="enquirydate" type="date" value="<?php if(!empty($notificationData['enquirydate'])){ echo $notificationData['enquirydate'];} ?>"
                           />
                         </div>
                         
                         <div class="col-md-6 form-group mb-3">
                           <label for="picker2">Date Of Birth</label>
                           <input
                             class="form-control "
                             id="picker2"
                             placeholder="dd-mm-yyyy" max="9999-12-31" data-rule-required="true"
                             name="student_dob"  type="date" value="<?php if(!empty($notificationData['student_dob'])){ echo $notificationData['student_dob'];}?>"
                           />
                         </div>
                       <div class="col-md-6 form-group mb-3">
                         <label for="firstName1">Gender</label>
                           <select id="gender" class="form-control" name="gender" autocomplete="shipping address-level1" >
                              @if(!empty($notificationData['gender']))
                                                <option>Please Select</option>

                                                @foreach(config('global.gender') as $each)
                                                @if($notificationData['gender'] == $each)
                                                <option selected value="{{$each}}">{{$each}}</option>
                                                @else
                                                <option value="{{$each}}">{{$each}}</option>
                                                @endif
                                                @endforeach
                                                @else
                                                <option>Please Select</option>
                                                @foreach(config('global.gender') as $each)
                                                <option value="{{$each}}">{{$each}}</option>
                                                @endforeach
                                                @endif
                           </select>
                         </div>           
                        <div class="col-lg-4 form-group mb-3">                         
                           <label for="fathername">Father Name</label>
                            <div class="input-group mb-3" >
                                  <div class="input-group-prepend">
                           <select id="fathername_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="fathername_prefix" autocomplete="shipping address-level1" >
                                        <option <?php if(!empty($notificationData['fathername_prefix'])){ if($notificationData['fathername_prefix'] == "Mr"){ echo "selected";}}?> value="Mr">Mr.</option>
                                        <option <?php if(!empty($notificationData['fathername_prefix'])){ if($notificationData['fathername_prefix'] == "Dr"){echo "selected";}}?> value="Dr">Dr.</option>
                                        <option <?php if(!empty($notificationData['fathername_prefix'])){ if($notificationData['fathername_prefix'] == "Late"){echo "selected";}}?> value="Late">Late</option>
                                     </select>
                                  </div>
                           <input
                             class="form-control uperletter"
                             id="fathername"
                             type="text"
                             placeholder="Enter your father name" name="fathername" value="<?php if(!empty($notificationData['fathername'])){ echo $notificationData['fathername']; }?>"
                           />
                         </div>
                       
                         </div>
                        
                        <div class="col-md-4 form-group mb-3">
                           <label for="phone">Father Mobile No.</label>
                           <input class="form-control" value="{{ $each_inq->mobile_number }}"
                             id="fathermobile"  placeholder="" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check();"
                             placeholder="Enter father mobile no" name="fathermobile" return false;" 
                           /><span class="application_for_msg validation_err"  id ="validation_err"></span>
                         </div>
                         <div class="col-md-4 form-group mb-3">
                           <label for="mothername">Father Occupation</label>
                          
                           <select id="fatheroccupation" class="form-control" name="fatheroccupation" autocomplete="shipping address-level1" >
                              <option value="" disabled selected>Please select</option>
                              @foreach(config('global.occupation') as $each)
                              <option <?php if(!empty($notificationData['fatheroccupation'])){ if($notificationData['fatheroccupation'] == $each){ echo "selected";} }?> value="{{$each}}">{{$each}}</option>
                              @endforeach                              
                           </select>
                          
                         </div>
                        
                                               
                         <div class="col-lg-4 form-group mb-3">
                           <label for="mothername">Mother Name</label>
                           <div class="input-group mb-3" >
                                  <div class="input-group-prepend">
                           <select id="mothername_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="mothername_prefix" autocomplete="shipping address-level1" >
                                        <option <?php if(!empty($notificationData['mothername_prefix'])){ if($notificationData['mothername_prefix'] == "Mrs") { echo "selected";}}?> value="Mrs">Mrs.</option>
                                        <option <?php if(!empty($notificationData['mothername_prefix'])){ if($notificationData['mothername_prefix'] == "Dr") { echo "selected";} }?> value="Dr">Dr.</option>
                                        <option <?php if(!empty($notificationData['mothername_prefix'])){ if($notificationData['mothername_prefix'] == "Late") { echo "selected";}}?> value="Late">Late</option>
                                     </select>
                                  </div>
                           <input
                             class="form-control uperletter"
                             id="mothername"
                             type="text" name="mothername"
                             placeholder="Enter your mother name" value="<?php if(!empty($notificationData['mothername'])){ echo $notificationData['mothername'];}?>"
                           />
                         </div>
                       </div>
                         <div class="col-md-4 form-group mb-3">
                           <label for="phone2">Mother Mobile No.</label>
                           <input
                             class="form-control"
                              id="mothermobile"  placeholder="" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check2();" value="{{ $notificationData['mothermobile']}}"
                              placeholder="Enter mother mobile no" name="mothermobile" return false;"
                           /><span class="application_for_msg validation_err" id="validation_err2"></span>
                         </div>
                         
                         <div class="col-md-4 form-group mb-3">
                           <label for="mothername">Mother Occupation</label>
                          <!--  <input
                             class="form-control"
                             id="motheroccupation"
                             type="text"
                             placeholder="Enter your mother occupation"
                           /> -->
                           <select id="motheroccupation" class="form-control" name="motheroccupation" autocomplete="shipping address-level1" >
                              <option value="" disabled selected>Please select</option>
                              @foreach(config('global.occupation_mother') as $each)
                              <option <?php if(!empty($notificationData['motheroccupation'])){ if($notificationData['motheroccupation'] == $each){ echo "selected";} }?> value="{{$each}}">{{$each}}</option>
                              @endforeach
                           </select>
                         </div>                             

                       </div>
               
                  </div>
               </div>

               <div id="step-2">
                  <h3 class="border-bottom border-gray pb-2">
                     Enquiry For
                  </h3>
                  <div>
                    @foreach($all_inquiry as $each_inq)
                         <?php $notificationData = json_decode($each_inq->json_str, true); ?>
   
                    @endforeach 
                     <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="credit1">Class Name</label>
                             <!-- <input type="text" name="class" value="{{ $each_inq->class_name }}" class="form-control" placeholder="Class Name"> -->
                            <select class="form-control" name="class">
                                @foreach(config('global.class_name') as $each)
                              <option <?php if(!empty($each_inq->class_name)){ if($each_inq->class_name == $each){ echo "selected";}}?> value="{{$each}}">{{$each}}</option>
                              @endforeach
                          </select>
                        
                           <span class="classname_msg validation_err"></span>
                         </div>
                         <div class="col-md-6 form-group mb-3">
                           <label for="enquiryno">Admission Type</label>
                           <select id="admission-type" class="form-control" name="admission_type" autocomplete="" >
                              <option value=""  selected>Please select</option>
                              @foreach(config('global.admission_type') as $each)
                              <option <?php if(!empty($notificationData['admission_type'])){ if($notificationData['admission_type'] == $each){ echo "selected";}}?> value="{{$each}}">{{$each}}</option>
                              @endforeach
                           </select>
                           <span class="addmission_msg validation_err"></span>
                         </div>
                         <div class="col-md-6 form-group mb-3">
                           <label for="pincode">Email Id</label>
                            <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter email" fdprocessedid="csh6vi" name="email" value="<?php if(!empty($notificationData['email'])){ echo $notificationData['email'];} ?>">
                            <span class="application_for_msg validation_err" id="validation_err3"></span>
                         </div>
                         <div class="col-md-3 form-group mb-3">
                            <label for="remark">Remark</label>
                           <select id="" class="form-control" name="remarkstatus" autocomplete="" required>
                              <option value="" disabled selected>Please select</option>
                              @foreach(config('global.remarkstatus') as $each)
                              <option <?php if(!empty($notificationData['remarkstatus'])){ if($notificationData['remarkstatus'] == $each){ echo "selected";} }?> value="{{$each}}">{{$each}}</option>
                              @endforeach
                           </select>
                         </div>                               
                          
                        <div class="col-md-3 form-group mb-3">
                            <label for="remark">Remark Status</label>
                           <select id="" class="form-control" name="remark" autocomplete="">
                              <option value="" disabled selected>Please select</option>
                              <option <?php if(!empty($notificationData['remarks'])){ if($notificationData['remarks'] == "Fees Structure"){ echo "selected";} }?> value="Fees Structure">Fees Structure</option>
                              <option <?php if(!empty($notificationData['remarks'])){ if($notificationData['remarks'] == "Co-Curricular Activities"){ echo "selected";} }?> value="Co-Curricular Activities">Co-Curricular Activities</option>
                             
                              <option <?php if(!empty($notificationData['remarks'])){ if($notificationData['remarks'] == "Infrastructure"){ echo "selected";} }?> value="Infrastructure">Infrastructure</option>
                              <option <?php if(!empty($notificationData['remarks'])){ if($notificationData['remarks'] == "Games/ Sports"){ echo "selected";} }?> value="Games/ Sports">Games/ Sports</option>
                              <option <?php if(!empty($notificationData['remarks'])){ if($notificationData['remarks'] == "If reject,Interview Not cleared"){ echo "selected";} }?> value="If reject,Interview Not cleared">If reject,Interview Not cleared</option>
                              <option <?php if(!empty($notificationData['remarks'])){ if($notificationData['remarks'] == "Others"){ echo "selected";} }?> value="Others">Others</option>
                           </select>
                         </div>                               
                                       
                          <div class="form-group mb-3">
                        <!-- <select  id="country-dd" class="form-control">
                            <option value="">Select Country</option>
                            <?php //print_r($states);?>
                            @foreach ($states as $data)
                            <option value="{{$data->id}}">
                                {{$data->name}}
                            </option>
                            @endforeach
                        </select> -->
                    </div>
                       <div class="col-md-3 form-group mb-3">
                        <label for="state">State</label>
                          <select id="state-dd" class="form-control"  name="state">
                            <option>Select</option>
                             @foreach ($states as $data)
                              <option <?php if(!empty($notificationData['state'])){ if($notificationData['state'] == $data->name){ echo "selected";} }?> value="{{$data->id}}" data-name="{{$data->name}}">
                                  {{$data->name}}
                              </option>
                              @endforeach  
                          </select>
                      </div>
                       <div class="col-md-3 form-group mb-3">
                        <label for="city">City</label>
                          <select id="city-dd" class="form-control" name="city">
                            <option value="<?php if(!empty($notificationData['city'])){ echo $notificationData['city'];}?>"><?php if(!empty($notificationData['city'])){ echo $notificationData['city'];}?></option>
                          </select>
                      </div>

                                                 
                         <div class="col-md-3 form-group mb-3">
                           <label for="address">Address</label>
                           <input
                             class="form-control uperletter"
                             id="address"
                             type="text"
                             placeholder="Enter your address" name="address" value="<?php if(!empty($notificationData['address'])){ echo $notificationData['address'];}?>"
                           />
                         </div>
                         
                         <div class="col-md-3 form-group mb-3">
                           <label for="pincode">Pin</label>
                           <input
                             class="form-control"
                             id="pincode"
                             type="text"
                             placeholder="Enter your pin" name="pincode" value="{{$notificationData['pincode']}}" maxlength="6" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check2();"
                           />
                         </div>
                         
                         
                       
                         <div class="col-md-3 form-group mb-3">
                           <label for="religion">Religion</label>
                           <select id="religion" class="form-control" name="religion" autocomplete="" >
                              <option value="" disabled selected>Please select</option>
                              @foreach(config('global.religion') as $each)
                              <option <?php if(!empty($notificationData['religion'])){ if($notificationData['religion'] == $each){ echo "selected";} } ?> value="{{$each}}">{{$each}}</option>
                              @endforeach
                           </select>
                         </div>
                         <div class="col-md-3 form-group mb-3">
                           <label for="category">Category</label>
                           <select id="category" class="form-control" name="category" autocomplete="" >
                              <option value="" disabled selected>Please select</option>
                              @foreach(config('global.cate') as $each)
                              <option <?php if(!empty($notificationData['category'])){ if($notificationData['category'] == $each){ echo "selected";} }?> value="{{$each}}">{{$each}}</option>
                              @endforeach
                           </select>
                         </div>
                       

                         <div class="col-md-6 form-group mb-3">
                           <label for="category">Select and Enter Caste Name</label>
                            
                           <input type="text" name="caste" id="tags" class="form-control uperletter" value=" <?php if(!empty($notificationData['caste'])){ echo $notificationData['caste'];}?>" >
                              
                         </div>
                         {{-- <div class="col-md-3 form-group mb-3">
                           <label for="received_amount">Received Amount</label>
                         <!--   <input
                             class="form-control"
                             id="received_amount"
                             type="text"
                             placeholder="Enter ammount" name="received_amount"
                           /> -->
                           <select id="received_amount" class="form-control" name="received_amount" autocomplete="" >
                              <option value="" disabled selected>Please select</option>
                              @foreach(config('global.receivedammount') as $each)
                              <option </?php if(!empty($notificationData['received_amount'])){ if($notificationData['received_amount'] == $each){ echo "selected";} }?> value="{{$each}}">{{$each}}</option>
                              @endforeach
                           </select>
                         </div> --}}


                         <div class="col-md-3 form-group mb-3">
                          <label for="received_amount">Received Amount</label>
                          <select id="received_amount" class="form-control" onchange="checkIfOnline()" name="received_amount" autocomplete="">
                              <option value="" disabled selected>Please select</option>
                              @foreach(config('global.receivedammount') as $each)
                              <option value="{{$each}}" {{ !empty($notificationData['received_amount']) && $notificationData['received_amount'] == $each ? 'selected' : '' }}>{{$each}}</option>
                              @endforeach
                          </select>
                      </div>
                        
                         {{-- <'?php if(!empty($notificationData['reference_number'])){?>
                          <div id="extra" name="extra" class="col-md-3 form-group mb-3">

                            <label for="presentlyclass">Reference Number</label>
                            <input class="form-control" type="text" id="desc" name="reference_number" value="</?php if(!empty($notificationData['reference_number'])){ echo $notificationData['reference_number']; }?>" >
                          </div>
                      </?php } ?> --}}
                      <div style="display: none" class="col-md-3 form-group mb-3" id="desc">
                        <label for="presentlyclass">Reference Number</label>
                        <input class="form-control" type="text" name="reference_number" value="{{ !empty($notificationData['reference_number']) ? $notificationData['reference_number'] : '' }}" required>
                    </div>

                      <div id="amount" name="amount" class="col-md-3 form-group mb-3">
                        <label for="presentlyclass">Amount</label>
                        <input class="form-control" type="text" placeholder="Add Amount" id="amount" name="amount"  value=" <?php if(!empty($notificationData['amount'])){ echo $notificationData['amount'];}?>"  required>
                      </div>
                         <div class="col-md-3 form-group mb-3">
                           <label for="presentlyclass">Presently Studing in Class</label>
                           <select id="presentlyclass" class="form-control" name="presentlyclass" autocomplete="" >
                              <option value="" selected>Please select</option>
                              @foreach(config('global.class_name') as $each)
                              <option <?php if(!empty($notificationData['presentlyclass'])){ if($notificationData['presentlyclass'] == $each){ echo "selected";} }?>  value="{{$each}}">{{$each}}</option>
                              @endforeach
                           </select>
                         </div>
                         <div class="col-md-3 form-group mb-3">
                           <label for="presentlyschool">School</label>
                            <input class="form-control uperletter" id="presentlyschool" type="text" placeholder="Enter School name"  name="presentlyschool" value="<?php if(!empty($notificationData['presentlyschool'])){ echo $notificationData['presentlyschool'];}?>">
                         </div>
                         <div class="col-md-3 form-group mb-3">
                           <label for="hear_aboutus">How did you hear about us</label>
                           <select id="hear_aboutus" class="form-control" name="hear_aboutus" autocomplete="" >
                              <option value="" disabled selected>Please select</option>
                              @foreach(config('global.hearaboutus') as $each)
                              <option <?php if(!empty($notificationData['hear_aboutus'])){ if($notificationData['hear_aboutus'] == $each){ echo "selected";} }?>  value="{{$each}}">{{$each}}</option>
                              @endforeach
                           </select>                         
                         </div>
                         <!-- <div class="col-md-3 form-group mb-3">
                           <label for="follow-up-date">Follow Up Date</label>
                            <input class="form-control" type="date"  id="picker2-" type="text" placeholder="dd-mm-yyyy"  name="follow_up_date" >
                         </div> -->
                         <div class="col-md-6 form-group mb-3">
                           <label for="follow-up-date">Follow Up Date</label>
                            <input class="form-control" type="date"  id="picker2-" type="text" placeholder="dd-mm-yyyy"  name="follow_up_date" max="9999-12-31" value="<?php if(!empty($notificationData['follow_up_date'])){ echo $notificationData['follow_up_date'];}?>">
                         </div>
                         <div class="col-md-6 form-group mb-3">
                           <label for="enquiry_through">Enquiry through</label>
                           <select id="enquiry_through" class="form-control" name="enquiry_through" autocomplete="" >
                              <option value="" disabled selected>Please select</option>
                              @foreach(config('global.enquirythrough') as $each)
                              <option <?php if(!empty($notificationData['enquiry_through'])){ if($notificationData['enquiry_through'] == $each){ echo "selected";} }?>  value="{{$each}}">{{$each}}</option>
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
                        <?php //print_r($notificationData);exit; 
                        if(!empty($notificationData['siblings_details'])){ 
                            foreach($notificationData['siblings_details'] as $sibling){ 
                                if(!empty($sibling)){ 
                                foreach($sibling as $sibling2){
                                         //echo $sibling2['siblings_namesecond'];
                                    }
                                }
                                //    
                                 }
                            
                        }?>
                        <h6 class="field_wrapper">Do you have any siblings? <input class="coupon_question" type="checkbox" name="coupon_question" value="1" id="coupon_question"  />
                        <span class="item-text">Yes</span></h6>

                        <div class="col-md-6 form-group mb-3 answer">
                           <label for="formidsearch">Enquiry No Search (By - Father Name)</label>
                           <input
                             class="form-control uperletter"
                             id="searchfather"
                             placeholder="" name="searchfather" value="<?php if(!empty($notificationData['searchfather'])){ echo $notificationData['searchfather'];}?>" required
                           /><span class="validation_err"></span><span class="error_msg validation_err"></span>
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
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet"/>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.0/js/bootstrap.min.js"></script>

        <div class="group_wrapper">
           <div class="gethtml">  
            <table id="itemTable" class="table answer">
                <thead>
                  </thead>
                <tbody class="field_wrapper">
                    <tr class="item">
                        <td><label for="siblings_name">Name</label>
                            <input class="form-control uperletter" id="siblings_namesecond" type="text" placeholder="Enter name"  name="siblings_namesecond[0]" value="<?php if(!empty($sibling2['siblings_namesecond'])){ echo $sibling2['siblings_namesecond'];}?>">                       
                        </td>
                        <td><label for="sibling_class_second">Class</label>
                            <select id="sibling_class_second" class="form-control" name="sibling_class_second[0]" autocomplete="" >
                                <option value="" disabled selected>Please select</option>
                                @foreach(config('global.class_name') as $each)
                                <option value="{{$each}}" <?php if(!empty($sibling2['sibling_class_second'])){ if($sibling2['sibling_class_second'] == $each){ echo "selected"; }}?>>{{$each}}</option>
                                @endforeach
                            </select>                    
                        </td>
                        <td><label for="siblings_school_second">Section</label>
                            <input class="form-control" id="siblings_section_second" type="text" placeholder="Enter Section"  name="siblings_section_second[0]" value="<?php if(!empty($sibling2['siblings_section_second'])){ echo $sibling2['siblings_section_second'];}?>">                  
                        </td>
                        <td><label for="siblings_school_second">Date Of Birth</label>
                            <input class="form-control"   id="picker2-" type="text" placeholder="dd-mm-yyyy"  name="siblings_bod_second[0]" max="9999-12-31" value="<?php if(!empty($sibling2['siblings_bod_second'])){ echo date('d-m-Y',strtotime($sibling2['siblings_bod_second']));}?>">                
                        </td>        
                        <!-- <td><label for="siblings_school_second">Action</label><br><a href="javascript:void(0);" class="add_button btn btn-sm btn-primary" title="Add field"><i class="fa fa-plus"></i></a></td> -->
                    </tr>
                </tbody>
            </table>
        </div>
        </div>
                        <div class="col-md-12">
                            <input type="hidden" name="idd" id="idd" value="{{$each_inq->id}}">
                          <button class="btn btn-primary submit_btn" type="submit">Submit</button>
                        </div>
                     </div>                              
                        </div>
                            </div>
                  
                        
                      </div>
                    </div>
                   </div>
                 </div>
                   </form> 
<script type="text/javascript">
    $('.date').datepicker({  
       format: 'dd-mm-yyyy'
     }); 
     $('.date1').datepicker({  
       format: 'dd-mm-yyyy'
     });  
   
</script>
<script src="{{url('assets/backend')}}/js/plugins/jquery-3.3.1.min.js"></script>
<script type="text/javascript">
    $('input[type="checkbox"]').on('change', function() {
    $('input[name="' + this.name + '"]').not(this).prop('checked', false);
});  
function check()
{
  var mobile = document.getElementById('fathermobile');
  var message = document.getElementById('validation_err');
  if(mobile.value.length!=10){
     message.innerHTML = "required 10 digits, match requested format!"
    }else { message.innerHTML =  "";}}
function check2()
{
  var mobile = document.getElementById('mothermobile');
  var message2 = document.getElementById('validation_err2');
  if(mobile.value.length!=10){
     message2.innerHTML = "required 10 digits, match requested format!"
    }else { message2.innerHTML =  "";}}

 $('#exampleInputEmail1').on('keyup', function() { 
    var re = /([A-Z0-9a-z_-][^@])+?@[^$#<>?]+?\.[\w]{2,4}/.test(this.value);
    if(!re) {
     $("#validation_err3").html("Invalid Email Format").show().fadeOut(3000);
   } else {
     $("#validation_err3").hide();
  }
 });


$(".submit_btn").on('click', function (e) {

      e.preventDefault();

      var session = $('select[name="session"]').val();
      var studentname = $('input[name="studentname"]').val();
      var enquiryno = $('input[name="enquiryno"]').val();
      var formno = $('input[name="formno"]').val();
      // var student_caste = $('input[name="student_caste"]').val();
      // var religion = $('select[name="religion"]').val();
       // var classname = $('select[name="classname"]').val();
       // var addmission = $('select[name="addmission_type"]').val();
       var allinputmsg=""; var allinputmsg2="";

      if(session==null){
        $('.session_msg').text("This field is required*");
       allinputmsg = '1'; 

      }else{

        $('.session_msg').text("");
      }

      if(studentname==''){
        $('.student_name_msg').text("This field is required*");
      }else{

        $('.student_name_msg').text("");
      }
      if(enquiryno==''){
        $('.enquiryno_msg').text("This field is required*");
        //allinputmsg+= ", Enquiry No";
      }else{

        $('.enquiryno_msg').text("");
      }
      // if(classname==null){
      //   $('.classname_msg').text("This field is required*");
      //   allinputmsg2 = '1';
      // }else{

      //   $('.classname_msg').text("");
      // }
      // if(addmission==null){
      //   $('.addmission_msg').text("This field is required*");
      //   //allinputmsg+= ", Admission Type";
      // }else{

      //   $('.addmission_msg').text("");
      // }
      if(formno==''){
        $('.formno_msg').text("This field is required*");
        //allinputmsg+= ", Enquiry No";
      }else{

        $('.formno_msg').text("");
      }


      
      if(studentname!=='' &&  session!==null &&  enquiryno!=='' && formno!==''){
        var myForm = document.getElementById("form-id");
        event.preventDefault();
         myForm.submit();
         $('.rg_form').submit();
      }else{
         $('.allinput_msg').text("All required fields must be completed before you submit the form*");
         if(allinputmsg=="1"){
              $("#step1").addClass('s1');

         }else{
              $("#step1").removeClass('s1');
         }
         if(allinputmsg2=="1"){
              $("#step2").addClass('s1');
         }else{
              $("#step").removeClass('s1');
         }

         console.log("invalid form");
      }


   });


</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
          setTimeout(function() {
                var year = $("#year").val()
                $("#session_name").val(year);
              }, 1000);
            $('#country-dd').on('change', function () {
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
                    success: function (result) {
                        $('#state-dd').html('<option value="">Select State</option>');
                        $.each(result.states, function (key, value) {
                            $("#state-dd").append('<option value="' + value
                                .id + '">' + value.name + '</option>');
                        });
                        $('#city-dd').html('<option value="">Select City</option>');
                    }
                });
            });
            $('#state-dd').on('change', function () {
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
                    success: function (res) {
                        $('#city-dd').html('<option value="">Select City</option>');
                        $.each(res.cities, function (key, value) {
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
    var add_group = $('.add_group');
    var group_wrapper = $('.group_wrapper');


    var max_field = 10;
    var add_button = $('.add_button');
    var wrapper = $('.field_wrapper');
      
    var html_fields = '' +
        '<tr class="item">' +
        '<td><div class="form-group mb-0"> <input class="form-control" id="siblings_namesecond" type="text" placeholder="Enter name"  name="siblings_namesecond[1]"></div> </td> ' +
        '<td><div class="form-group mb-0"><select id="sibling_class_second" class="form-control" name="sibling_class_second[1]" autocomplete="" ><option value="" disabled selected>Please select</option>@foreach(config("global.class_name") as $each)<option value="{{$each}}">{{$each}}</option>@endforeach</select> </div> </td> ' +
        '<td><input class="form-control" id="siblings_section_second" type="text" placeholder="Enter Section"  name="siblings_section_second[1]"></td> '+
        '<td><input class="form-control" type="date"  id="picker2-" type="text" placeholder="dd-mm-yyyy"  name="siblings_bod_second[1]"></td> ' +        
        '<td><a href="javascript:void(0);" class="remove_button btn btn-sm btn-danger"><i class="fa fa-minus"></i></a> </td>' +
        '</tr>';

    var x = 1;
    var y = 1;
      

    $(add_button).click(function(){
        if(x < max_field){
            x++;
            $(this).closest(wrapper).append(html_fields);
        }
    });   
    

    $(wrapper).on('click', '.remove_button', function(e){
        e.preventDefault();
        $(this).closest('tr').remove();
        x--;
    });

$(document).ready(function() {
$('#smartwizard').smartWizard({
  // Properties
    selected: 0,  // Selected Step, 0 = first step   
    keyNavigation: false, // Enable/Disable key navigation(left and right keys are used if enabled)
    enableAllSteps: false,  // Enable/Disable all steps on first load
    
    buttonOrder: ['finish', 'next', 'prev']  // button order, to hide a button remove it from the list
}); 
}); 
// sibling details hide
    $(".answer").hide();
    $(".coupon_question").click(function() {
        if($(this).is(":checked")) {
            $(".answer").show();
        } else {
            $(".answer").hide();
        }
    });
    $(document).ready(function() {
    var ch = document.getElementById("coupon_question").checked;
   // alert(ch);
    if(ch == true){
    $('.coupon_question').prop('checked', true).triggerHandler('click');
     $(".answer").show();
}else{
    $('.coupon_question').prop('', false).triggerHandler('click');
}
});

    
// end 
    function getsiblingbyfathers()
      {
            var form_number = $("#searchfather").val(); 
         $('.pick_inq_data2').attr('data-form_number',form_number);
      }


      /*Ajax for pick data from form no*/
      var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
      $(".pick_inq_data2").on('click', function () {

      var form_number = $('.pick_inq_data2').attr('data-form_number');
      
      $.ajax({
        url: '{{ url("getsiblingbyfathersname") }}',
        type: 'post',
        data: {_token: CSRF_TOKEN, form_number: form_number},
         dataType: 'html',
        success: function(response){
         console.log(response); 
           $('.gethtml').html(response);
         if(response.inq_data){
             
         }

        },
         error: function() {
        // alert('There was some error performing the AJAX call!');
                   
      }
      });

   });
    $(window).ready(function() {
        $("#form-id").on("keypress", function (event) {
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
                 <script type="text/javascript">$.noConflict();</script> 

        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <script>
            $.noConflict();
           jQuery(document).ready(function($){
                var availableTags = [
                    <?php foreach($caste as $each){ ?>
                        "<?php echo $each->caste_name;?>",
                    <?php }?>
                    
                ];
                $( "#tags" ).autocomplete({
                    source: availableTags
                });
            });
            jQuery(document).ready(function($){
          
                var availableTags2 = [
                    <?php foreach($all_inquiry2 as $each2){ 
                          $inq_str_data = json_decode($each2->json_str, true);  ?>
                         "<?php if(!empty($inq_str_data['fathername'])){ echo $inq_str_data['fathername']; }?>",
                   <?php } ?>
                    
                ];
                $( "#searchfather" ).autocomplete({
                    source: availableTags2
                });
            });
           
        </script> 
        
        
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

  function checkIfYes() {
    if (document.getElementById('received_amount').value == 'Online') {
      document.getElementById('extra').style.display = '';
      document.getElementById('auth_by').disabled = false;
      document.getElementById('desc').disabled = false;
    } else {
      document.getElementById('extra').style.display = 'none';
    }
  }




//   function checkIfOnline() {
//     console.log("Function called");
//     var receivedAmount = document.getElementById("received_amount").value;
//     console.log("Received Amount:", receivedAmount);

//     var referenceNumberField = document.getElementById("desc");

//     if (receivedAmount.toLowerCase() === "online") {
        
//         console.log("Displaying Reference Number field");
//         referenceNumberField.style.display = "block";
//     } else {
      
//         console.log("Hiding Reference Number field");
//         referenceNumberField.style.display = "none";
//     }
//     alert(receivedAmount);
// }
</script>
<script>
    function checkIfOnline() {
        var receivedAmount = document.getElementById("received_amount").value;
        var referenceNumberField = document.getElementById("desc");

      
        if (receivedAmount.toLowerCase() === "online") {
      
            referenceNumberField.style.display = "block";
        } else {
        
            referenceNumberField.style.display = "none";
        }
    }
    window.onload = checkIfOnline;
</script>

@endsection

