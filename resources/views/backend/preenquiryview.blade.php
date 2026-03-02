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
    <!-- <h2>hyy</h2> -->   
          <div class="breadcrumb">
          <h2>Pre Enquiry Information</h2>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
                    <!-- student information -->
                    <section class="ul-product-detail">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <a href="{{url('admin-pre-enquiryform')}}"><button style="float:right" class="btn btn-primary" type="button">Pre Enquiry List</button></a>
                                        <div class="separator-breadcrumb"></div>
                                        @if(!empty($all_inquiry))
                                                <?php $i=1; ?>
                                                @foreach($all_inquiry as $each_inq)
                                                 <?php $notificationData1 = json_decode($each_inq->json_str, true);?>
                                        <div class="col-lg-3">
                                            <!-- <div class="ul-product-detail__image"><img src="{{url('assets/frontend/')}}/images/logo.png" alt="" /></div> -->
                                            <div class="card-body text-center">
                                        <div class="avatar box-shadow-2 mb-3 uperletter" style="border-radius:50%;"><img src="{{url('assets/backend/')}}/images/student.png" alt="" /></div>
                                        <h5 class="m-0 uperletter"><?php if(!empty($notificationData1['studentname_prefix'])){ echo $notificationData1['studentname_prefix'].' '; } echo ucwords($each_inq->student_name); ?></h5>
                                        <p class="mt-0">Class - {{$each_inq->class_name}}</p>
                                        <!-- <button class="btn btn-primary btn-rounded">Contact Jassica</button> -->
                                        <div class="card-socials-simple mt-4">
                                        <div class="table-responsive">
                                            <form method="post" action="{{ url('update_preenquiryview') }}" >
                                            @csrf
                                            <input type="hidden" name="update_id" value="<?php echo $each_inq->id; ?>">
                                            <input type="hidden" name="enquirydate" value="<?php echo (!empty($notificationData1['enquirydate'])) ? $notificationData1['enquirydate'] : ""; ?>">
                                            <input type="hidden" name="formno" value="<?php echo (!empty($notificationData1['formno'])) ? $notificationData1['formno'] : ""; ?>">
                                            <input type="hidden" name="enquiryno" value="<?php echo (!empty($notificationData1['enquiryno'])) ? $notificationData1['enquiryno'] : ""; ?>">
                                            <input type="hidden" name="admission_type" value="<?php echo (!empty($notificationData1['admission_type'])) ? $notificationData1['admission_type'] : ""; ?>">
                                            <input type="hidden" name="pincode" value="<?php echo (!empty($notificationData1['pincode'])) ? $notificationData1['pincode'] : ""; ?>">
                                            <table class="table table-striped">
                                        <thead>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <!-- <th scope="row">2</th> -->
                                                <th>Name </th>
                                                <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                <td class="uperletter">
                                                    <?php if (!empty($each_inq->student_name)) { ?>
                                                        <span class="class_name_display"> <?php echo $each_inq->student_name; ?></span>
                                                        <div style="display: none;" class="input-group mb-3" >
                                                            <div class="input-group-prepend">
                                                                <?php $arr = ["Master","Mr","Miss"]; ?>
                                                                <select id="studentname_prefix" style="display: none;" class="form-control class_name_input btn btn-outline-primary dropdown-toggle" name="studentname_prefix" autocomplete="shipping address-level1" >
                                                                    <?php foreach($arr as $obj) {
                                                                        if ($notificationData1['studentname_prefix'] == $obj){
                                                                            echo '<option selected value="'.$notificationData1['studentname_prefix'].'">'.$notificationData1['studentname_prefix'].' </option>';
                                                                        } else {
                                                                            echo '<option value="'.$obj.'">'.$obj.' </option>';
                                                                        }
                                                                    }?>
                                                                </select>
                                                            </div>
                                                            <input type="text"
                                                                class="form-control uperletter"
                                                                id="studentname"
                                                                style="display: none;"
                                                                placeholder="Enter Student Name" name="studentname" />
                                                            </div>
                                                            <span class="student_name_msg validation_err"></span>                          
                                                        </div>
                                                        <input type="text" name="studentname" class="form-control class_name_input" style="display: none;" value="<?php echo $each_inq->student_name; ?>" />
                                                    <?php } else { ?>
                                                        <div style="display: none;" class="input-group mb-3" >
                                                            <div class="input-group-prepend">
                                                            <select id="studentname_prefix" style="display: none;" class="form-control class_name_input btn btn-outline-primary dropdown-toggle" name="studentname_prefix" autocomplete="shipping address-level1" >
                                                                    <option value="Master">Master</option>
                                                                    <option value="Mr">Mr.</option>
                                                                    <option value="Miss">Miss</option>
                                                                </select>
                                                            </div>
                                                            <input type="text"
                                                                class="form-control uperletter class_name_input"
                                                                id="studentname"
                                                                style="display: none;"
                                                                placeholder="Enter Student Name" name="studentname" />
                                                            </div>
                                                            <span class="student_name_msg validation_err"></span>                          
                                                        </div>
                                                        <input type="text" name="studentname" class="form-control class_name_input" style="display: none;" value="" />
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <!-- <th scope="row">2</th> -->
                                              <!--   <th>Pre Enquiry for class </th>
                                                <td>{{$each_inq->class_name}}</td> -->
                                            </tr>
                                            <tr>
                                                <!-- <th scope="row">2</th> -->
                                                <th>Session </th>
                                                <td>
                                                    <?php if (!empty($each_inq->session_name)) { ?>
                                                        <span class="class_name_display"><?php echo $each_inq->session_name; ?></span>
                                                        <select id="session" name="session" class="form-control class_name_input" style="display: none;" name="session" autocomplete="shipping address-level1">
                                                            <option value="" disabled selected>Please Select</option>
                                                            @foreach(config('global.session_name') as $each)
                                                                @if($each == $each_inq->session_name)
                                                                    <option selected value="{{$each}}">{{$each}}</option>
                                                                @else 
                                                                    <option value="{{$each}}">{{$each}}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    <?php } else { ?>
                                                        <select id="session" name="session" class="form-control class_name_input" style="display: none;" name="session" autocomplete="shipping address-level1">
                                                            <option value="" disabled selected>Please Select</option>
                                                            @foreach(config('global.session_name') as $each)
                                                                <option value="{{$each}}">{{$each}}</option>
                                                            @endforeach
                                                        </select>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <!-- <th scope="row">2</th> -->
                                                <th>Form No. </th>
                                                <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                <td><?php if(!empty($each_inq->form_number)){ echo ucwords($each_inq->form_number);} ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                    </div>
                                    </div>
                                        </div>
                                        <div class="col-lg-9">
                                           
                       
                        <div class="card text-left">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-3">Student Information
                                <!-- <input type="checkbox" id="edit_checkbox" /> -->
                                <img id="edit_checkbox" src="{{url('assets/frontend')}}/images/edit-new.png" height="20px" width="20px" alt="">
                                <!-- <label for="edit_checkbox">Edit</label> -->
                                </h4>
                                
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                      <a
                        class="nav-link active"
                        id="home-basic-tab"
                        data-bs-toggle="tab"
                        href="#homeBasic"
                        role="tab"
                        aria-controls="homeBasic"
                        aria-selected="true"
                        >Basic Information</a
                      >
                    </li>
                    <!-- <li class="nav-item">
                      <a
                        class="nav-link"
                        id="contact-basic-tab"
                        data-bs-toggle="tab"
                        href="#contactBasic"
                        role="tab"
                        aria-controls="contactBasic"
                        aria-selected="false"
                        >Contact</a
                      >
                    </li>
                    <li class="nav-item">
                      <a
                        class="nav-link"
                        id="profile-basic-tab"
                        data-bs-toggle="tab"
                        href="#profileBasic"
                        role="tab"
                        aria-controls="profileBasic"
                        aria-selected="false"
                        >Siblings Details</a
                      >
                    </li> -->
                  </ul>
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="homeBasic" role="tabpanel" aria-labelledby="home-basic-tab">
                                        <h3>Student Details</h3> 
                                    <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <th>Pre Enquiry for class</th>
                                            <td>
                                                <?php if (!empty($each_inq->class_name)) { ?>
                                                    <span class="class_name_display"><?php echo $each_inq->class_name; ?></span>
                                                    <div style="display: none;" class="input-group class_name_input mb-3" >
                                                        <div class="input-group-prepend">
                                                            <select id="classname" class="form-control class_name_input" name="classname" autocomplete="">
                                                                <option value="">Please select</option>
                                                                @foreach($classlist as $each)
                                                                    @if($each_inq->class_name == $each->class_name)
                                                                        <option selected value="{{$each->class_name}}">{{$each->class_name}}</option>
                                                                    @else
                                                                        <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <?php } else { ?>
                                                        <div style="display: none;" class="input-group class_name_input mb-3" >
                                                            <div class="input-group-prepend">
                                                                <select id="classname" class="form-control class_name_input" name="classname" autocomplete="">
                                                                    <option value="" disabled selected>Please select</option>
                                                                    @foreach($classlist as $each)
                                                                        <option selected value="{{$each->class_name}}">{{$each->class_name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                <?php } ?>
                                            </td>
                                            
                                        </tr>
                                            <tr>
                                                <th>Father's Name  </th>
                                                <td class="uperletter">
                                                    <?php if (!empty($notificationData1['fathername_prefix'])) { ?>
                                                        <span class="class_name_display"><?php echo $notificationData1['fathername_prefix']; ?> <?php echo $notificationData1['fathername']; ?></span>
                                                        <div class="input-group mb-3 class_name_input" style="display: none;">
                                                            <div class="input-group-prepend class_name_input" style="display: none;">
                                                            <?php $arrf = ["Mr","Dr","Late"]; ?>
                                                                <select id="fathername_prefix" class="form-control class_name_input btn btn-outline-primary dropdown-toggle" style="display: none;" name="fathername_prefix" autocomplete="shipping address-level1" >
                                                                    <?php foreach($arrf as $objf){
                                                                        if($objf == $notificationData1['fathername_prefix']){
                                                                            echo '<option selected value="'.$notificationData1['fathername_prefix'].'">'.$notificationData1['fathername_prefix'].'</option>';
                                                                        } else {
                                                                            echo '<option value="'.$objf.'">'.$objf.'</option>';
                                                                        }
                                                                    } ?>
                                                                </select>
                                                            </div>
                                                            <input type="text" name="fathername" class="form-control class_name_input" style="display: none;" value="<?php echo $notificationData1['fathername']; ?>" />
                                                        </div>
                                                    <?php } else { ?>
                                                        <div class="input-group mb-3 class_name_input" style="display: none;">
                                                            <div class="input-group-prepend class_name_input" style="display: none;">
                                                                <select id="fathername_prefix" class="form-control btn btn-outline-primary dropdown-toggle" style="display: none;" name="fathername_prefix" autocomplete="shipping address-level1" >
                                                                    <?php foreach($arrf as $objf){
                                                                        echo '<option value="'.$objf.'">'.$objf.'</option>';
                                                                    } ?>
                                                                </select>
                                                            </div>
                                                            <input type="text" name="fathername" class="form-control class_name_input" style="display: none;" value="" />
                                                         </div>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <!-- <th scope="row">2</th> -->
                                                <th>Mother's Name  </th>
                                                <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                <td class="uperletter">
                                                    <?php if (!empty($notificationData1['mothername_prefix'])) { ?>
                                                        <div class="input-group mb-3 class_name_input" style="display: none;">
                                                            <div class="input-group-prepend class_name_input" style="display: none;" >
                                                            <?php $arrg = ["Mrs","Dr","Late"]; ?>
                                                                <select id="mothername_prefix" class="form-control class_name_input btn btn-outline-primary dropdown-toggle" style="display: none;" name="mothername_prefix" autocomplete="shipping address-level1" >
                                                                    <?php foreach($arrg as $objg){
                                                                        if($objg == $notificationData1['mothername_prefix']){
                                                                            echo '<option selected value="'.$notificationData1['mothername_prefix'].'">'.$notificationData1['mothername_prefix'].'</option>';
                                                                        } else {
                                                                            echo '<option value="'.$objg.'">'.$objg.'</option>';
                                                                        }
                                                                    } ?>
                                                                </select>
                                                            </div>
                                                            <input type="text" name="mothername" class="form-control class_name_input" style="display: none;" value="<?php echo $notificationData1['mothername']; ?>" />
                                                        </div>
                                                        <span class="class_name_display"><?php echo $notificationData1['mothername_prefix']; ?> <?php echo $notificationData1['mothername']; ?></span>                                                        
                                                    <?php } else { ?>
                                                        <div class="input-group mb-3 class_name_input" style="display: none;" >
                                                            <div class="input-group-prepend class_name_input" style="display: none;">
                                                                <select id="mothername_prefix" class="form-control btn btn-outline-primary dropdown-toggle" style="display: none;" name="mothername_prefix" autocomplete="shipping address-level1" >
                                                                    <?php foreach($arrg as $objg){
                                                                        echo '<option value="'.$objg.'">'.$objg.'</option>';
                                                                    } ?>
                                                                </select>
                                                            </div>
                                                            <input type="text" name="mothername" class="form-control class_name_input" style="display: none;" value="" />
                                                         </div>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Address </th>
                                                <td class="uperletter">
                                                    <?php if (!empty($notificationData1['address'])) { ?>
                                                        <span class="class_name_display"><?php echo $notificationData1['address']; ?></span>
                                                        <input type="text" name="address" class="form-control class_name_input" style="display: none;" value="<?php echo $notificationData1['address']; ?>" />
                                                    <?php } else { ?>
                                                        <input type="text" name="address" class="form-control class_name_input" style="display: none;" value="" />
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <!-- <th scope="row">2</th> -->
                                                <th>State - City</th>
                                                <td class="uperletter">
                                                    <?php if (!empty($notificationData1['state'])) { ?>
                                                        <div class="col-md-3 form-group mb-3 class_name_input" style="display: none;">
                                                            <select id="state-dd" class="form-control class_name_input" style="display: none;" name="state">
                                                                <option>Select</option>
                                                                @foreach ($states as $data)
                                                                @if($data->name == $notificationData1['state'])
                                                                <option selected value="{{$data->id}}">
                                                                    {{$data->name}}
                                                                </option>
                                                                @else
                                                                <option value="{{$data->id}}">
                                                                    {{$data->name}}
                                                                </option>
                                                                @endif
                                                                @endforeach  
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 class_name_input form-group mb-3" style="display: none;" >
                                                            <select id="city-dd" class="form-control class_name_input" name="city" style="display: none;" >
                                                            </select>
                                                        </div>
                                                        <span class="class_name_display"><?php echo $notificationData1['state']; ?> <?php echo $notificationData1['city']; ?></span>
                                                        
                                                    
                                                    <?php } else { ?>
                                                        <div class="col-md-3 form-group mb-3 class_name_input" style="display: none;">
                                                        <select id="state-dd" class="form-control class_name_input" style="display: none;" name="state">
                                                            <option>Select</option>
                                                            @foreach ($states as $data)
                                                            <option value="{{$data->id}}">
                                                                {{$data->name}}
                                                            </option>
                                                            @endforeach  
                                                        </select>
                                                        </div>
                                                        <div class="col-md-3 class_name_input form-group mb-3" style="display: none;" >
                                                            <select id="city-dd" class="form-control class_name_input" name="city" style="display: none;" >
                                                            </select>
                                                        </div>
                                                        <?php } ?>
                                                </td>
                                                <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                            </tr>
                                           
                                            <tr>
                                                <!-- <th scope="row">2</th> -->
                                                <th>Date of Birth - Gender </th>
                                                <td>
                                                    <?php if (!empty($notificationData1['student_dob'])) { ?>
                                                        <div class="col-md-3 form-group mb-3 class_name_input" style="display: none;">
                                                            <input name="student_dob" class="form-control class_name_input" id="picker2" style="display: none;" placeholder="dd-mm-yyyy" value="<?php echo $notificationData1['student_dob']; ?>" name="date_of_birth" type="date">
                                                        </div>
                                                        <span class="class_name_display"><?php echo date('d-m-Y', strtotime($notificationData1['student_dob'])); ?> <?php echo $notificationData1['gender']; ?></span>
                                                        <div class="col-md-3 form-group mb-3 class_name_input" style="display: none;">
                                                        <select id="gender" class="form-control class_name_input" style="display: none;" name="gender" autocomplete="shipping address-level1" >
                                                            <option value="" disabled selected>Please select</option>
                                                            @foreach(config('global.gender') as $each)
                                                                @if($each == $notificationData1['gender'])
                                                                    <option selected value="{{$each}}">{{$each}}</option>
                                                                @else
                                                                    <option value="{{$each}}">{{$each}}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                        </div>
                                                    <?php } else { ?>
                                                        <input name="student_dob" class="form-control class_name_input" id="picker2" style="display: none;" placeholder="dd-mm-yyyy" value="" name="date_of_birth" type="date">
                                                        <input name="gender" class="form-control class_name_input" id="picker2" style="display: none;" placeholder="dd-mm-yyyy" value="" name="gender" type="text">
                                                    <?php } ?>
                                                </td>
                                                <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                            </tr>
                                            <tr>
                                                    <th style="width:25%">Father's Contact No. </th>
                                                    <td>
                                                        <?php if (!empty($notificationData1['fathermobile'])) { ?>
                                                            <div class="col-md-3 form-group mb-3 class_name_input" style="display: none;">
                                                            <input class="form-control class_name_input"
                                                                id="fathermobile" maxlength="10" style="display: none;" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check();"
                                                                placeholder="Enter father mobile no" name="fathermobile" value="<?php echo $notificationData1['fathermobile']; ?>"
                                                            />
                                                            <!-- <span class="application_for_msg validation_err"  id ="validation_err"></span> -->
                                                            </div>
                                                            <span class="class_name_display"><?php echo $notificationData1['fathermobile']; ?></span>
                                                        <?php } else { ?>
                                                            <div class="col-md-3 form-group mb-3 class_name_input" style="display: none;">
                                                            <input class="form-control class_name_input"
                                                                id="fathermobile" maxlength="10" style="display: none;" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check();"
                                                                placeholder="Enter father mobile no" name="fathermobile" value=""
                                                            />
                                                            <!-- <span class="application_for_msg validation_err"  id ="validation_err"></span>     -->
                                                            </div>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th style="width:25%">Mother's Contact No.</th>
                                                    <td>
                                                        <?php if (!empty($notificationData1['mothermobile'])) { ?>
                                                            <span class="class_name_display"><?php echo $notificationData1['mothermobile']; ?></span>
                                                            <div class="col-md-3 form-group mb-3">
                                                            <input
                                                                class="form-control class_name_input"
                                                                id="mothermobile"  placeholder="" maxlength="10" style="display: none;" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check2();"
                                                                placeholder="Enter mother mobile no" name="mothermobile" return false;" value="<?php echo $notificationData1['mothermobile']; ?>"
                                                            /><span class="application_for_msg validation_err" id="validation_err2"></span>
                                                            </div>
                                                        <?php } else { ?>
                                                            <div class="col-md-3 form-group mb-3">
                                                            <input
                                                                class="form-control class_name_input"
                                                                id="mothermobile"  placeholder="" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" style="display: none;" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check2();"
                                                                placeholder="Enter mother mobile no" name="mothermobile" return false;" value=""
                                                            /><span class="application_for_msg validation_err" id="validation_err2"></span>
                                                            </div>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th style="width:25%">Admission For.</th>
                                                    <td>
                                                    @if(!empty($each_inq->next_year))
                                                        <?php 
                                                            $originalYear = date("Y")."_".date("Y") + 1;

                                                            // Split the string into two years
                                                            $years = explode("_", $originalYear);
                                                            
                                                            if (count($years) === 2) {
                                                                $startYear = intval($years[0]);
                                                                $endYear = intval($years[1]);
                                                                $newStartYear = $startYear + 1;
                                                                $newEndYear = $endYear + 1;
                                                                $newYear = $newStartYear . "_" . $newEndYear;
                                                                echo $newYear; // Output: 2024_2025
                                                            } else {
                                                                echo "Invalid input format";
                                                            }
                                                        ?>
                                                        @else
                                                        <?php if (!empty($each_inq->session_name)) { ?>
                                                            <span class="class_name_display"><?php echo $each_inq->session_name; ?></span>
                                                        <?php } ?>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th style="width:25%">
                                                        <a href="#" class="form-control btn btn-success class_name_input" id="class_name_input_s" style="display: none;">Submit</a>
                                                    </th>
                                                    <td style="display: none;" class="class_name_input" style="width:25%">
                                                        <input type="submit" class="form-control text-text-wight class_name_input_f" style="display: none;" value="Final Submit" />
                                                    </td>
                                                </tr>
                                        </tbody>
                                    </table>
                                </form>
                                </div>
                                    </div>
                                    
                                    
                                    </div>
                                </div>
                       
                        @endforeach
                        @endif
                        
                    </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- <Add>To Cart</Add> -->
                    <!-- end student information -->
                  
                
             
          </div>
    <!-- end of main-content -->
</div>
<input type="hidden" id="checkbox_state" value="0">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $("#edit_checkbox").click(function () {
            // Get the current state from the hidden input
            var currentState = $("#checkbox_state").val();
            $('#state-dd').trigger('change');
            // Toggle the state between 0 and 1
            var newState = (currentState === "0") ? "1" : "0";
            
            // Update the hidden input's value
            $("#checkbox_state").val(newState);
            
            // Check the state and perform actions accordingly
            if (newState === "1") {
                $(this).css("background-color", "green");
                // Code to execute when the "checkbox" is checked
                $(".class_name_display").hide();
                $(".class_name_input").show().focus();
                $(".class_name_input_f").hide();
                
            } else {
                $(this).css("background-color", ""); 
                // Code to execute when the "checkbox" is unchecked
                $(".class_name_display").show();
                $(".class_name_input").hide();
                $(".class_name_input_f").hide();
            }
        });

        $("#class_name_input_s").click(function () {
            $(".class_name_input_f").show();
            $("#class_name_input_s").hide();
            $(".class_name_input_f").css("background-color", "green");
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
            
            var city_name = '<?php echo !empty($notificationData1['city']) ? $notificationData1['city'] :"" ; ?>';
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
                            if (value.name == city_name) {
                                $("#city-dd").append('<option selected value="' + value
                                .name + '">' + value.name + '</option>');
                            } else {
                                $("#city-dd").append('<option value="' + value
                                .name + '">' + value.name + '</option>');
                            }
                            
                        });
                    }
                });
            });
    });
</script>

@endsection