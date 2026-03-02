@extends('backend.layouts.main')
@section('main-container')

<style>

.uperletter{
  text-transform: capitalize;
}

</style>
<div class="main-content pt-4">
    <!-- <h2>hyy</h2> -->   
    @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
          <div class="breadcrumb">
          <h2>Student Enquiry Information</h2>
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
                                        @role('Admin')
                                        <a href="{{url('adminenquirylist')}}"><button style="float:right" class="btn btn-primary" type="button">All Enquiry List</button></a>
                                        @endrole
                                        <div class="separator-breadcrumb"></div>
                                        @if(!empty($all_inquiry))
                                                <?php $i=1; ?>
                                                @foreach($all_inquiry as $each_inq)
                                                
                                                 <?php $notificationData1 = json_decode($each_inq->json_str, true);?>
                                        <div class="col-lg-3">
                                            <!-- <div class="ul-product-detail__image"><img src="{{url('assets/frontend/')}}/images/logo.png" alt="" /></div> -->
                                            <div class="card-body text-center">
                                        <div class="avatar box-shadow-2 mb-3" style="border-radius:50%;"><img src="{{url('assets/backend/')}}/images/student.png" alt="" /></div>
                                        <h5 class="m-0 uperletter"><?php if(!empty($notificationData1['studentname_prefix'])){ echo $notificationData1['studentname_prefix'].' '; }?>{{$each_inq->student_name}}</h5>
                                        <p class="mt-0">Class - {{$each_inq->class_name}}</p>
                                        <!-- <button class="btn btn-primary btn-rounded">Contact Jassica</button> -->
                                        <div class="card-socials-simple mt-4">
                                        <div class="table-responsive">
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
                                                <th>Scholar NO. </th>
                                                <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                <td>{{$notificationData1['enquiryno']}}</td>
                                            </tr>
                                            <!-- <tr> -->
                                                <!-- <th scope="row">2</th> -->
                                                <!-- <th>Class </th> -->
                                                <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                <!-- <td>{{$each_inq->class_name}}</td> -->
                                                
                                            <!-- </tr> -->
                                            
                                        </tbody>
                                    </table>
                                </div>
                                    </div>
                                    </div>
                                        </div>
                                        <div class="col-lg-9">
                                            <!-- <div class="ul-product-detail__brand-name mb-4">
                                                <h5 class="heading">Student Name</h5><span class="text-mute">Scholar NO.</span>
                                            </div> -->
                                            <!-- <div class="ul-product-detail__price-and-rating d-flex align-items-baseline">
                                                <h3 class="font-weight-700 text-primary mb-0 mr-2">$2,300</h3><span class="text-mute font-weight-800 mr-2">
                                                    <del>$1,150</del></span><small class="text-success font-weight-700">50% off</small>
                                            </div> -->
                                            <!--
                        <div class="ul-product-detail__rating">
                                                                        <ul>
                                                                            <li></li>
                                                                        </ul>
                                                                    </div> 
                        -->
                       
                        <div class="card text-left">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-3">Student Information
                                @role('Admin')
                                <img id="edit_checkbox" src="{{url('assets/frontend')}}/images/edit-new.png" height="20px" width="20px" alt="">
                                @endrole                
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
                    <li class="nav-item">
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
                    </li>
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
                                                <!-- <th scope="row">2</th> -->
                                                <th>Class </th>
                                                <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                <!-- <td>{{$each_inq->class_name}}</td> -->
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
                                                <th>Father's Name - Occupation </th>
                                                <!-- <td> -->
                                                    <!-- <?php if(!empty($notificationData1['fathername_prefix'])){ echo $notificationData1['fathername_prefix'].' '; }?><?php if(!empty($notificationData1['fathername'])){ echo $notificationData1['fathername'].' '; }?> - <?php if(!empty($notificationData1['fatheroccupation'])){ echo $notificationData1['fatheroccupation'].' '; }?> -->
                                                <!-- </td> -->

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
                                                            <input type="text" name="fatheroccupation" class="form-control class_name_input" style="display: none;" value="<?php echo $notificationData1['fatheroccupation']; ?>" />
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
                                                            <input type="text" name="fatheroccupation" class="form-control class_name_input" style="display: none;" value="" />
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
                                                
                                            </tr>
                                            <tr>
                                                <!-- <th scope="row">2</th> -->
                                                <th>Mother's Name - Occupation </th>
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
                                                            <input type="text" name="motheroccupation" class="form-control class_name_input" style="display: none;" value="<?php echo $notificationData1['motheroccupation']; ?>" />
                                                        </div>
                                                        <span class="class_name_display"><?php echo $notificationData1['mothername_prefix']; ?> <?php echo $notificationData1['mothername']; ?> <?php echo $notificationData1['motheroccupation']; ?></span>                                                 
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
                                                            <input type="text" name="motheroccupation" class="form-control class_name_input" style="display: none;" value="" />
                                                         </div>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <!-- <th scope="row">2</th> -->
                                                <th>Religion - Cast</th>
                                                <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                <td class="uperletter">
                                                    <?php if (!empty($notificationData1['religion'])) { ?>
                                                    <div class="col-md-3 form-group mb-3 class_name_input" style="display: none;">
                                                        <select id="state-dd" class="form-control class_name_input" style="display: none;" name="religion">
                                                            <option>Select</option>
                                                            <?php $reli = ['Hindu','Bohra','Islam','Jain','Muslim','Sikh','Other']; ?>
                                                            @foreach ($reli as $data)
                                                            @if($data == $notificationData1['religion'])
                                                            <option selected value="{{$data}}">
                                                                {{$data}}
                                                            </option>
                                                            @else
                                                            <option value="{{$data}}">
                                                                {{$data}}
                                                            </option>
                                                            @endif
                                                            @endforeach  
                                                        </select>
                                                        <span class="class_name_display"><?php echo $notificationData1['religion']; ?></span>
                                                    </div>
                                                    <?php } else { ?>
                                                        <div class="col-md-3 form-group mb-3 class_name_input" style="display: none;">
                                                        <select id="state-dd" class="form-control class_name_input" style="display: none;" name="religion">
                                                            <option>Select</option>
                                                            <?php $reli = ['Hindu','Bohra','Islam','Jain','Muslim','Sikh','Other']; ?>
                                                            @foreach ($reli as $data)
                                                            <option value="{{$data}}">
                                                                {{$data}}
                                                            </option>
                                                            @endforeach  
                                                        </select>
                                                        </div>
                                                    <?php } ?>

                                                    <?php if (!empty($notificationData1['caste'])) { ?>
                                                        <div class="col-md-3 form-group mb-3 class_name_input" style="display: none;">
                                                            <input type="text" name="caste" class="form-control class_name_input" style="display: none;" value="<?php echo $notificationData1['caste']; ?>" />
                                                        </div>
                                                        <span class="class_name_display"><?php echo $notificationData1['caste']; ?></span>
                                                    <?php } else { ?>
                                                        <div class="col-md-3 form-group mb-3 class_name_input" style="display: none;">
                                                        <input type="text" name="caste" class="form-control class_name_input" style="display: none;" value="" />
                                                        </div>
                                                    <?php } ?>

                                                </td>
                                            </tr>
                                            <tr>
                                                <!-- <th scope="row">2</th> -->
                                                <th>Category </th>
                                                <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                <td>
                                                <?php if (!empty($notificationData1['category'])) { ?>
                                                    <select id="state-dd" class="form-control class_name_input" style="display: none;" name="category">
                                                        <option>Select</option>
                                                        <?php $reli = ['GEN','ST','SC','OBC','UR']; ?>
                                                        @foreach ($reli as $data)
                                                        @if($data == $notificationData1['category'])
                                                        <option selected value="{{$data}}">
                                                            {{$data}}
                                                        </option>
                                                        @else
                                                        <option value="{{$data}}">
                                                            {{$data}}
                                                        </option>
                                                        @endif
                                                        @endforeach  
                                                    </select>
                                                    <span class="class_name_display"><?php echo $notificationData1['category']; ?></span>
                                                <?php } else { ?>
                                                    <select id="state-dd" class="form-control class_name_input" style="display: none;" name="category">
                                                        <option>Select</option>
                                                        <?php $reli = ['GEN','ST','SC','OBC','UR']; ?>
                                                        @foreach ($reli as $data)
                                                        <option value="{{$data}}">
                                                            {{$data}}
                                                        </option>
                                                        @endforeach  
                                                    </select>
                                                <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <!-- <th scope="row">2</th> -->
                                                <th>Date of Birth - Gender </th>
                                                <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                <td> 
                                                    <!-- <?php if(!empty($notificationData1['student_dob'])){ echo date('d-m-Y',strtotime($notificationData1['student_dob']));}?> -  <?php if(!empty($notificationData1['gender'])){ echo $notificationData1['gender'];}?> -->
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
                                            </tr>
                                            @role('Student')
                                            <tr>
                                               
                                            
                                            
                                            
                                            <style>
        /* CSS to style the edit icon */
        /* .edit-icon {
            cursor: pointer;
        } */

    
        .submit-button {
            background-color: blue;
            color: white;
            margin-left: 30px;
            border-radius: 5px;
        }

        
    </style>              
                                            
                                            
                                            <!-- @if(session('error')) -->
    <!-- <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif -->
                                             <form action="{{ route('password.update') }}" method="post">
                                                  @csrf
                                                <th>Password  </th>
                                                <td> *****<span style="cursor: pointer;" onclick="showEditForm()">✏️(edit)</span>
            
                                                   <div id="edit-form" style="display: none;">
                <!-- <input type="password" id="password" placeholder="Password" oninput="validatePassword()"> -->

                                                         <div class="form-group">
                                                     <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="New Password">
                                                     @error('password')
                                                     <div class="invalid-feedback">{{ $message }}</div>
                                                     @enderror
                                                    </div>

                        <div class="form-group">
                            <input type="password" id="confirmPassword" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm New Password" oninput="validatePassword()">
                            <input value="{{ $id }}" type="text" id="id" class="form-control d-none" name="id">
                        </div>


                <!-- <input type="password" id="confirmPassword" placeholder="Confirm Password" oninput="validatePassword()"> -->
                <!-- <button class="submit-button" id="submitButton" disabled onclick="submitForm()">Submit</button> -->
                <button type="submit" class="btn btn-primary" id="submitButton" disabled>
                            Update Password
                        </button>
                <p id="error-message" class="error-message" style="display: none; color: red;">Passwords do not match.</p>
          
                </div>
            </td>
    </form>
                                            </tr>
                                            @endrole
                                        </tbody>
                                    </table>
                                </div>
                                    </div>
                                    
                                    <div class="tab-pane fade" id="contactBasic" role="tabpanel" aria-labelledby="contact-basic-tab">

                                        <div class="table-responsive">
                                            <table class="table">
                                            <tbody>
                                                
                                                <tr>
                                                    <th style="width:25%">Father's Contact No. </th>
                                                    <td>
                                                        <!-- <?php if(!empty($notificationData1['fathermobile'])){ echo $notificationData1['fathermobile'];}?> -->
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
                                                        <!-- <?php if(!empty($notificationData1['mothermobile'])){ echo $notificationData1['mothermobile'];}?> -->
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
                                                
                                            </tbody>
                                            </table>
                                        </div>    
                                    </div>
                                    <div class="tab-pane fade" id="profileBasic" role="tabpanel" aria-labelledby="profile-basic-tab">
                                    <h6 class="field_wrapper">Do you have any siblings? <input class="coupon_question" type="checkbox" name="coupon_question" value="1" id="coupon_question"  />
                                    <?php if (!empty($notificationData1['siblings_details'])) { ?>
                                        <div class="table-responsive class_name_display">
                                            <table class="table">
                                            <?php if(!empty($notificationData1['siblings_details'])){
                                                   foreach($notificationData1['siblings_details'] as $sibling){
                                                      if(!empty($sibling)){       
                                                            foreach($sibling as $sibling2){
                                                                if(!empty($sibling2)){
                                                                }  
                                                      }          
                                                    }
                                                   }
                                                   }else{ echo "Sibling Details not Added";}?>
                                            <?php if(!empty($sibling2['siblings_namesecond'])){ ?>       
                                            <thead>
                                                <th>Siblings Name</th>
                                                <th>Siblings Class</th>
                                                <th>Siblings School</th>
                                                <th>Date Of Birth</th>
                                            </thead> 
                                            <tbody>                                              
                                                <tr>
                                                   <td><?php if(!empty($sibling2['siblings_namesecond'])){ echo $sibling2['siblings_namesecond'];}?></td>
                                                                                                  
                                                    <td><?php if(!empty($sibling2['sibling_class_second'])){ echo $sibling2['sibling_class_second'];}?></td>
                                                
                                                   <td><?php if(!empty($sibling2['siblings_section_second'])){ echo $sibling2['siblings_section_second'];}?></td>
                                                                                               
                                                    <td><?php if(!empty($sibling2['siblings_bod_second'])){ echo $sibling2['siblings_bod_second'];}?></td>
                                                </tr>
                                                
                                            </tbody><?php } ?>
                                            </table>
                                        </div> 
                                        
                                        <span style="display: none;" class="item-text class_name_input">Yes</span></h6>

                                        <div style="display: none;" class="col-md-6 form-group mb-3 answer class_name_input">
                                        <label for="formidsearch">Enquiry No Search (By - Father Name)</label>
                                        <input
                                            style="display: none;"
                                            class="form-control class_name_input"
                                            id="searchfather"
                                            placeholder="" name="searchfather" required
                                        /><span style="display: none;" class="validation_err class_name_input"></span><span class="error_msg validation_err class_name_input"></span>
                                        <span style="display: none;" class="error_msg3 validation_err3 class_name_input"></span>
                                        </div>
                                        <div style="display: none;" class="col-md-6 form-group mb-3 answer class_name_input">
                                        <label for="formidsearch"></label>
                                        <div style="display: none;" class="col-md-12 class_name_input">
                                        <button type="button" style="display: none;" class="btn btn-primary pick_inq_data2 class_name_input class_name_input" onclick="getsiblingbyfathers();" data-form_number="" fdprocessedid="7t8lyh">Pick Data</button>
                                        </div>
                                        
                                        </div>
                                        <span style="display: none;" class="allinput_msg validation_err class_name_input"></span>
                                        <!-- hide -->
                                        <div class="group_wrapper class_name_input" style="display: none;">
                                        <div class="gethtml">  
                                            <table id="itemTable" class="table answer">
                                                <thead>
                                                </thead>
                                                <tbody class="field_wrapper">
                                                    <tr class="item">
                                                        <td><label for="siblings_name">Name</label>
                                                            <input class="form-control" id="siblings_namesecond" type="text" placeholder="Enter name"  name="siblings_namesecond[0]" value="<?php if(!empty($sibling2['siblings_namesecond'])){ echo $sibling2['siblings_namesecond'];}?>">                       
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

                                        <?php } else { ?>
                                                <div class="table-responsive class_name_display">
                                                <table class="table">
                                                <?php if(!empty($notificationData1['siblings_details'])){
                                                    foreach($notificationData1['siblings_details'] as $sibling){
                                                        if(!empty($sibling)){       
                                                                foreach($sibling as $sibling2){
                                                                    if(!empty($sibling2)){
                                                                    }  
                                                        }          
                                                        }
                                                    }
                                                    }else{ echo "Sibling Details not Added";}?>
                                                <?php if(!empty($sibling2['siblings_namesecond'])){ ?>       
                                                <thead>
                                                    <th>Siblings Name</th>
                                                    <th>Siblings Class</th>
                                                    <th>Siblings School</th>
                                                    <th>Date Of Birth</th>
                                                </thead> 
                                                <tbody>                                              
                                                    <tr>
                                                    <td><?php if(!empty($sibling2['siblings_namesecond'])){ echo $sibling2['siblings_namesecond'];}?></td>
                                                                                                    
                                                        <td><?php if(!empty($sibling2['sibling_class_second'])){ echo $sibling2['sibling_class_second'];}?></td>
                                                    
                                                    <td><?php if(!empty($sibling2['siblings_section_second'])){ echo $sibling2['siblings_section_second'];}?></td>
                                                                                                
                                                        <td><?php if(!empty($sibling2['siblings_bod_second'])){ echo $sibling2['siblings_bod_second'];}?></td>
                                                    </tr>
                                                    
                                                </tbody><?php } ?>
                                                </table>
                                            </div> 
                                            
                                            <span style="display: none;" class="item-text class_name_input">Yes</span></h6>

                                            <div style="display: none;" class="col-md-6 form-group mb-3 answer class_name_input">
                                            <label for="formidsearch">Enquiry No Search (By - Father Name)</label>
                                            <input
                                                style="display: none;"
                                                class="form-control class_name_input"
                                                id="searchfather"
                                                placeholder="" name="searchfather" required
                                            /><span style="display: none;" class="validation_err class_name_input"></span><span class="error_msg validation_err class_name_input"></span>
                                            <span style="display: none;" class="error_msg3 validation_err3 class_name_input"></span>
                                            </div>
                                            <div style="display: none;" class="col-md-6 form-group mb-3 answer class_name_input">
                                            <label for="formidsearch"></label>
                                            <div style="display: none;" class="col-md-12 class_name_input">
                                            <button type="button" style="display: none;" class="btn btn-primary pick_inq_data2 class_name_input class_name_input" onclick="getsiblingbyfathers();" data-form_number="" fdprocessedid="7t8lyh">Pick Data</button>
                                            </div>
                                            
                                            </div>
                                            <span style="display: none;" class="allinput_msg validation_err class_name_input"></span>



                                            <div class="group_wrapper class_name_input" style="display: none;">
                                            <div class="gethtml">  
                                                <table id="itemTable" class="table answer">
                                                    <thead>
                                                    </thead>
                                                    <tbody class="field_wrapper">
                                                        <tr class="item">
                                                            <td><label for="siblings_name">Name</label>
                                                                <input class="form-control" id="siblings_namesecond" type="text" placeholder="Enter name"  name="siblings_namesecond[0]" value="<?php if(!empty($sibling2['siblings_namesecond'])){ echo $sibling2['siblings_namesecond'];}?>">                       
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

                                        <?php } ?>


                                    </div>
                                </div>
                       
                        @endforeach
                        @endif
                        @role('Student')
                        <style>
                            .disabled-link {
                            pointer-events: none;
                            /* Optionally, change the appearance to make it visually disabled */
                            opacity: 0.6;
                            cursor: not-allowed;
                            }
                        </style>

                        <!-- <a class="btn btn-primary"  href="http://localhost/lvn-school/public/users/{{Auth::user()->id}}/edit">Edit Password</a> -->
                        @endrole
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
            
            var city_name = '<?php echo $notificationData1['city']; ?>';
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

            $(".answer").hide();
                $(".coupon_question").click(function() {
                    if($(this).is(":checked")) {
                        $(".answer").show();
                    } else {
                        $(".answer").hide();
                    }
                });
    });
</script>
<script>
        function showEditForm() {
            // Show the edit form when the icon is clicked
            document.getElementById('edit-form').style.display = 'table-row'; // Display as a table row
        }

        function validatePassword() {
            // Get the values of password and confirm password fields
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirmPassword').value;
            var submitButton = document.getElementById('submitButton');
            var errorMessage = document.getElementById('error-message');

            // Check if passwords match
            if (password === confirmPassword) {
                // Enable the submit button and hide the error message
                submitButton.disabled = false;
                errorMessage.style.display = 'none';
            } else {
                // Disable the submit button and show the error message
                submitButton.disabled = true;
                errorMessage.style.display = 'block';
            }
        }

        function submitForm() {
            // Implement your logic to handle form submission here
            alert('Form submitted successfully!');
        }
    </script>

<script>
    function validatePassword() {
        // Get the values of password and confirm password fields
        var password = document.getElementById('password').value;
        var confirmPassword = document.getElementById('confirmPassword').value;
        var submitButton = document.getElementById('submitButton');
        var errorMessage = document.getElementById('error-message');

        // Check if passwords match
        if (password === confirmPassword) {
            // Enable the submit button and hide the error message
            submitButton.disabled = false;
            errorMessage.style.display = 'none';
        } else {
            // Disable the submit button and show the error message
            submitButton.disabled = true;
            errorMessage.style.display = 'block';
        }
    }
</script>



<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Get form and button elements
        const form = document.getElementById('password-update-form');
        const updateButton = document.getElementById('update-password-button');

        // Add an event listener to the button
        updateButton.addEventListener('click', function() {
            // Validate the passwords
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                const errorMessage = document.getElementById('error-message');
                errorMessage.style.display = 'block';
                return;
            }

            // Create a FormData object from the form
            const formData = new FormData(form);

            // Send an AJAX request to update the password
            fetch('{{ route('password.update') }}', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Password updated successfully
                    alert('Password updated successfully!');
                    // You can also update the UI or perform any other actions here
                } else {
                    // Password update failed
                    alert('Password update failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the password.');
            });
        });
    });
</script>



@endsection