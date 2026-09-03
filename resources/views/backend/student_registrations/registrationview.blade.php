@extends('backend.layouts.main')
@section('main-container')


<style>

.uperletter{
  text-transform: capitalize;
}

</style>

<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-content pt-4">
    <!-- <h2>hyy</h2> -->
    @if ($message = Session::get('success'))
    <div class="alert alert-success">
        <p>{{ $message }}</p>
    </div>
    @endif

    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>    
    <div class="breadcrumb">
        <h2>Registration Information </h2>
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
                                <div class="w-100 text-end">
                                    <a href="{{url('student-registrations')}}"><button class="btn btn-primary" type="button">Registration List</button></a>
                                </div>
                                <div class="separator-breadcrumb"></div>
                                @if(!empty($all_inquiry))
                                <?php $i = 1; ?>
                                @foreach($all_inquiry as $each_inq)
                                <?php $notificationData1 = json_decode($each_inq->json_str, true); ?>
                                <div class="col-lg-3">
                                    <!-- <div class="ul-product-detail__image"><img src="{{url('assets/frontend/')}}/images/logo.png" alt="" /></div> -->
                                    <div class="card-body text-center uperletter">
                                        <div class="avatar box-shadow-2 mb-3 uperletter" style="border-radius:50%;"><img src="{{ asset($each_inq->student_image_url ?? 'default-avatar.png') }}" alt="Student Photo">
									</div>
                                        <h5 class="m-0 uperletter"><?php if (!empty($notificationData1['studentname_prefix'])) {
                                                            echo $notificationData1['studentname_prefix'] . ' ';
                                                        } ?>{{$each_inq->student_name}}</h5>
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
                                                            <th><b>Scholar Number</b> </th>
                                                            <td><b><?php if (!empty($each_inq->scholar_no)) {
                                                                        echo $each_inq->scholar_no;
                                                                    } ?></b></td>
                                                        </tr>
                                                        <tr>
                                                            <!-- <th scope="row">2</th> -->
                                                            <th>Name </th>
                                                            <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                            <td>{{$each_inq->student_name}}</td>
                                                        </tr>

                                                        <tr>
                                                            <!-- <th scope="row">2</th> -->
                                                            <th>Class </th>
                                                            <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                            <td>{{$each_inq->class_name}}</td>
                                                        </tr>
                                                        <tr>
                                                            <!-- <th scope="row">2</th> -->
                                                            <th>Session </th>
                                                            <td>{{$each_inq->session_name}}</td>
                                                        </tr>
                                                        <tr>
                                                            <!-- <th scope="row">2</th> -->
                                                            <th>Form No. </th>
                                                            <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                            <td>{{$each_inq->form_number}}</td>
                                                        </tr>
                                                        <tr>
                                                            <!-- <th scope="row">2</th> -->
                                                            <th>Reset Password <br> ( LVN@123 ) </th>
                                                            <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                            <td> <button class="btn btn-primary" id="resetpassword" value="{{$each_inq->id}}"> Reset </button>
                                                                <br>
                                                                <div class="text text-success" id="success"></div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Archive Status</th>
                                                            <td>
                                                                @if(!empty($each_inq->is_archived))
                                                                    <form method="POST" action="{{ route('restore-student', $each_inq->id) }}">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-success">Restore Student</button>
                                                                    </form>
                                                                @else
                                                                    <form method="POST" action="{{ route('archive-student', $each_inq->id) }}" onsubmit="return confirm('Are you sure you want to archive this student? This student will no longer appear in the active ERP records.');">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-warning">Archive Student</button>
                                                                    </form>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body text-center uperletter">
                                        <div class="form_section1_div">
                                            <form method="post" action="{{ route('bonafide.generateCertificate') }}" novalidate="novalidate">
                                                @csrf

                                                <!-- Scholar Search Field -->
                                                <div class="row">
                                                    <div class="col-md-12 form-group mb-3">
                                                        <label for="scholar_search">Scholar Search</label>
                                                        <input type="text" class="form-control" id="scholar_search" name="scholar_no" placeholder="Enter Scholar No." value="{{ old('scholar_no') }}">
                                                        <span class="validation_err">{{ $errors->first('scholar_no') }}</span>
                                                    </div>
                                                </div>

                                                <!-- Full-Width Text Editor -->
                                                <div class="row">
                                                    <div class="col-md-12 form-group mb-3">
                                                        <label for="remarks">Certificate Content</label>
                                                        <div id="editor" style="height: 300px;"></div>  <!-- Quill editor will be here -->
                                                        <input type="hidden" name="remarks" id="remarks">  <!-- Hidden input to store editor content -->
                                                        <span class="validation_err">{{ $errors->first('remarks') }}</span>
                                                    </div>
                                                </div>

                                                <div class="row mt-2">
                                                    <div class="col-md-12 text-left">
                                                        <button type="submit" class="btn btn-primary">Generate Certificate</button>
                                                        <input type="reset" class="btn btn-danger text-white" value="Reset">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        
                                        <script>
                                            // Initialize Quill editor for full-width
                                            var quill = new Quill('#editor', {
                                                theme: 'snow',
                                                modules: {
                                                    toolbar: [
                                                        ['bold', 'italic', 'underline'],
                                                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                                        [{ 'align': [] }],
                                                        ['link']
                                                    ]
                                                },
                                                placeholder: 'Enter certificate content here...',
                                                readOnly: false,
                                            });

                                            // Set the default content in the editor
                                            var defaultText = `Certified that Ms. Anvi Sharma D/O Mr. Mayuri Sharma Niketan session 2025-2026 Class. V is a bonafide student of Lokmanya Vidya Section..... Kautilya 7165 As per office record her scholar no. 15-Oct-2015.
                                            Permanent and local (temporary) addresses are as under.

                                            This certificate has been issued as per the request of the student regarding ....

                                            Permanent address: 735/24 Samaj Wadi Indira Nagar, Indore, Madhya Pradesh`;

                                            // Set the default text in the Quill editor
                                            quill.root.innerHTML = defaultText;

                                            // On form submit, copy the content from Quill to the hidden input
                                            $('form').submit(function() {
                                                var content = quill.root.innerHTML;  // Get content from Quill editor
                                                $('#remarks').val(content);  // Set content to hidden input
                                            });
                                        </script>
                                    </div>
                                </div>
                                <div class="col-lg-9">


                                    <div class="card text-left">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title mb-3">Student Information</h4>
                                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" id="home-basic-tab" data-bs-toggle="tab" href="#homeBasic" role="tab" aria-controls="homeBasic" aria-selected="true">Basic Information</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" id="contact-basic-tab" data-bs-toggle="tab" href="#contactBasic" role="tab" aria-controls="contactBasic" aria-selected="false">Others Information</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" id="profile-basic-tab" data-bs-toggle="tab" href="#profileBasic" role="tab" aria-controls="profileBasic" aria-selected="false">Siblings Details</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" id="upload-documents" data-bs-toggle="tab" href="#uploadDocument" role="tab" aria-controls="uploadDocument" aria-selected="false">Upload Documents</a>
                                                    </li>
                                                    <!-- <li class="nav-item">
                                                        <a class="nav-link" id="fees-Details" data-bs-toggle="tab" href="#feesDetails" role="tab" aria-controls="feesDetails" aria-selected="false">Fees Details</a>
                                                    </li> -->
                                                </ul>
                                                <div class="tab-content" id="myTabContent">
                                                    <div class="tab-pane fade show active" id="homeBasic" role="tabpanel" aria-labelledby="home-basic-tab">
                                                        <h3>Student Details</h3>
                                                        <!-- Include jQuery -->
                                                        <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
                                                        <!-- Include Date Range Picker -->
                                                        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
                                                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css" />
                                                        <!-- <input type="text" placeholder = "dd/mm/yyyy" name="date"> -->
                                                        <div class="table-responsive">
                                                            <table class="table">
                                                                <thead>
                                                                </thead>
                                                                <tbody>

                                                                    <tr>
                                                                        <!-- <th scope="row">2</th> -->
                                                                        <th>Class </th>
                                                                        <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                                        <td>{{$each_inq->class_name}}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Admission Type </th>
                                                                        <td class="uperletter"><?php if (!empty($each_inq->application_for)) {
                                                                                echo $each_inq->application_for . ' ';
                                                                            } ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Father's Name </th>
                                                                        <td class="uperletter"><?php if (!empty($notificationData1['fathername_prefix'])) {
                                                                                echo $notificationData1['fathername_prefix'] . ' ';
                                                                            } ?><?php if (!empty($notificationData1['fathername'])) {
                                                                                    echo $notificationData1['fathername'] . ' ';
                                                                                } ?> </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <!-- <th scope="row">2</th> -->
                                                                        <th>Mother's Name </th>
                                                                        <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                                        <td class="uperletter"><?php if (!empty($notificationData1['mothername_prefix'])) {
                                                                                echo $notificationData1['mothername_prefix'] . ' ';
                                                                            } ?>
                                                                            <?php if (!empty($notificationData1['mothername'])) {
                                                                                echo $notificationData1['mothername'] . ' ';
                                                                            } ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Address </th>
                                                                        <td class="uperletter"><?php if (!empty($notificationData1['present_address'])) {
                                                                                echo $notificationData1['present_address'] . ' ';
                                                                            } ?>
                                                                            <?php if (!empty($notificationData1['city'])) {
                                                                                echo $notificationData1['city'] . ' ';
                                                                            } ?>
                                                                            <?php if (!empty($notificationData1['state'])) {
                                                                                echo $notificationData1['state'] . ' ';
                                                                            } ?>
                                                                            <?php if (!empty($notificationData1['pincode'])) {
                                                                                echo $notificationData1['pincode'] . ' ';
                                                                            } ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="uperletter" >Address </th>
                                                                        <td class="uperletter"><?php if (!empty($notificationData1['permanent_address'])) {
                                                                                echo $notificationData1['permanent_address'] . ' ';
                                                                            } ?>
                                                                        </td>
                                                                    </tr>


                                                                    <tr>
                                                                        <th>Date of Birth </th>
                                                                        <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                                        <td> <?php if (!empty($notificationData1['student_dob'])) {
                                                                                echo date('d-m-Y', strtotime($notificationData1['student_dob'])) . ' ';
                                                                            } ?> </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Gender </th>
                                                                        <!-- <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td> -->
                                                                        <td><?php if (!empty($notificationData1['gender'])) {

                                                                                    echo $notificationData1['gender'] . ' ';
                                                                                    } ?></td>
                                                                    </tr>
                                                         
                                                                    <tr>
                                                                        <th>Category </th>
                                                                        <td><?php if (!empty($notificationData1['category'])) {
                                                                                echo $notificationData1['category'] . ' ';
                                                                            } ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th style="width:25%">Father's Contact No. </th>
                                                                        <td><?php if (!empty($notificationData1['father_mobile'])) {
                                                                                echo $notificationData1['father_mobile'] . ' ';
                                                                            } ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th style="width:25%">Mother's Contact No.</th>
                                                                        <td><?php if (!empty($notificationData1['mobile_number'])) {
                                                                                echo $notificationData1['mobile_number'] . ' ';
                                                                            } ?></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="contactBasic" role="tabpanel" aria-labelledby="contact-basic-tab">

                                                        <div class="table-responsive">
                                                            <table class="table">
                                                                <tbody>

                                                                    <tr>
                                                                        <th style="width:25%">Nationality </th>
                                                                        <td><?php if (!empty($notificationData1['nationality'])) {
                                                                                echo $notificationData1['nationality'] . ' ';
                                                                            } ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th style="width:25%">Caste</th>
                                                                        <td><?php if (!empty($notificationData1['student_caste'])) {
                                                                                echo $notificationData1['student_caste'] . ' ';
                                                                            } ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th style="width:25%">Religion</th>
                                                                        <td><?php if (!empty($notificationData1['religion'])) {
                                                                                echo $notificationData1['religion'] . ' ';
                                                                            } ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th style="width:25%">Remark</th>
                                                                        <td><?php if (!empty($notificationData1['Remarks'])) {
                                                                                echo $notificationData1['Remarks'] . ' ';
                                                                            } ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th style="width:25%">Vaccaination</th>
                                                                        <td><?php if (!empty($notificationData1['Vaccaination
'])) {
                                                                                echo $notificationData1['Vaccaination
'] . ' ';
                                                                            } ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th style="width:25%">Medical Conserns</th>
                                                                        <td><?php if (!empty($notificationData1['student_medical_conserns'])) {
                                                                                echo $notificationData1['student_medical_conserns'] . ' ';
                                                                            } ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th style="width:25%">Name of Present Play School / Day Care</th>
                                                                        <td class="uperletter"><?php if (!empty($notificationData1['present_school_name'])) {
                                                                                echo $notificationData1['present_school_name'] . ' ';
                                                                            } ?></td>
                                                                    </tr>

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="profileBasic" role="tabpanel" aria-labelledby="profile-basic-tab">
                                                        <div class="table-responsive">
                                                            <table class="table">

                                                                <thead>
                                                                    <th>Siblings Name</th>
                                                                    <th>Siblings Class</th>
                                                                    <th>Siblings School</th>
                                                                    <th>Date Of Birth</th>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><?php if (!empty($notificationData1['siblings_namesecond'][0])) {
                                                                                echo $notificationData1['siblings_namesecond'][0] . ' ';
                                                                            } ?></td>

                                                                        <td><?php if (!empty($notificationData1['sibling_class'])) {
                                                                                echo $notificationData1['sibling_class'] . ' ';
                                                                            } ?></td>

                                                                        <td><?php if (!empty($notificationData1['siblings_school'])) {
                                                                                echo $notificationData1['siblings_school'] . ' ';
                                                                            } ?></td>

                                                                        <td><?php if (!empty($notificationData1['siblings_bod_second'][0])) {
                                                                                echo $notificationData1['siblings_bod_second'][0] . ' ';
                                                                            } ?></td>
                                                                    </tr>

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="uploadDocument" role="tabpanel" aria-labelledby="upload-documents">
                                                        <div class="table-responsive">
                                                            <table class="table">

                                                                <tbody>
                                                                    <tr>
                                                                        <th>Aadhar Certificate</th>
                                                                        <td>
                                                                            <?php  if(!empty($notificationData1['aadharProff'])){ $a = "aadharProff"."-".$notificationData1['aadharProff'] ; ?>
                                                                            <a class="btn btn-raised ripple btn-primary m-1" href="{{url('downloadcertificate').'/'.$a}}">Download</a><?php  }else{ echo "image not uploaded"; }?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Birth Certificate</th>
                                                                        <td><?php if(!empty($notificationData1['BirthCertificate'])){  $a = "BirthCertificate"."-".$notificationData1['BirthCertificate'] ; ?>
                                                                            <a class="btn btn-raised ripple btn-primary m-1" href="{{url('downloadcertificate').'/'.$a}}">Download</a><?php  }else{ echo "image not uploaded"; }?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Transfer Certificate</th>
                                                                        <td><?php if(!empty($notificationData1['TransferCertificate'])){ $a = "TransferCertificate"."-".$notificationData1['TransferCertificate'] ; ?>
                                                                            <a class="btn btn-raised ripple btn-primary m-1" href="{{url('downloadcertificate').'/'.$a}}">Download</a><?php  }else{ echo "image not uploaded"; }?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Address Proff</th>
                                                                        <td><?php if(!empty($notificationData1['AddressProff'])){ $a = "AddressProff"."-".$notificationData1['AddressProff'] ; ?>
                                                                            <a class="btn btn-raised ripple btn-primary m-1" href="{{url('downloadcertificate').'/'.$a}}">Download</a><?php  }else{ echo "image not uploaded"; }?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Cast Certificate</th>
                                                                        <td><?php if(!empty($notificationData1['CastProff'])){ $a = "CastProff"."-".$notificationData1['CastProff'] ; ?>
                                                                            <a class="btn btn-raised ripple btn-primary m-1" href="{{url('downloadcertificate').'/'.$a}}">Download</a><?php  }else{ echo "image not uploaded"; }?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>SSSM-ID Card</th>
                                                                        <td><?php if(!empty($notificationData1['sssmprof'])){ $a = "sssmprof"."-".$notificationData1['sssmprof'] ; ?>
                                                                            <a class="btn btn-raised ripple btn-primary m-1" href="{{url('downloadcertificate').'/'.$a}}">Download</a><?php  }else{ echo "image not uploaded"; }?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Last Report Card</th>
                                                                        <td><?php if(!empty($notificationData1['LastReportCard'])){ $a = "LastReportCard"."-".$notificationData1['LastReportCard'] ; ?>
                                                                            <a class="btn btn-raised ripple btn-primary m-1" href="{{url('downloadcertificate').'/'.$a}}">Download</a><?php  }else{ echo "image not uploaded"; }?></td>
                                                                    </tr>

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="feesDetails" role="tabpanel" aria-labelledby="fees-Details">
                                                        <?php
                                                        if (!empty($Vehicallist[0])) { ?>
                                                            <!-- <h1>Generate chart for this student </h1> -->
                                                            <!-- start section -->
                                                            <div class="main-content">
                                                                <!-- ============ Body content start ============= -->
                                                                <div class="main-content">
                                                                    <!-- <div class="separator-breadcrumb border-top"></div> -->
                                                                    <div class="row">
                                                                        <div class="separator-breadcrumb border-top"></div>
                                                                        <form id="progress-form" class="p-4 progress-form" action="{{ url('edit-student-fees-structure-master') }}" novalidate method="post">
                                                                            @csrf
                                                                            <div class="row">

                                                                                <?php $feesstr = json_decode($Vehicallist[0]->json_str, true);
                                                                                $de =  json_decode($feesstr[0]['json_str']);
                                                                                $jsonData = json_encode($de);
                                                                                if(isset($notificationData1['required_school_transport'])){$busValue = $notificationData1['required_school_transport'];}
                                                                                // $busValue = $notificationData1['required_school_transport'];
                                                                                // $showBusFacility = isset($notificationData1['required_school_trasnport']) && $notificationData1['required_school_trasnport'] == 1;
                                                                                // $position = array_search('required_school_trasnport', array_keys($notificationData1));
                                                                                //  $checkubsdata = json_decode($checkBus[0]->json_str, true);

                                                                                //    print_r($notificationData1);
                                                                                // exit;
                                                                                ?>
                                                                                <input type="hidden" name="stid" value="{{$Vehicallist[0]->student_id}}">
                                                                                <input type="hidden" name="id" value="{{$Vehicallist[0]->id}}">
                                                                                <input type="hidden" name="fulldata" value="{{$Vehicallist}}">




                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <div class="row">
                                                                                    <div class="col-md-8">
                                                                                        <button type="button" id="addRowButton" class="btn btn-success">Add Row</button>
                                                                                    </div>
                                                                                    <div class="col-md-4">
                                                                                        <input type="checkbox" id="busFacility" name="is_bus_facility" <?php if (!empty($notificationData1['required_school_transport'])) {
                                                                                                                                                            echo "checked";
                                                                                                                                                        } ?>>
                                                                                        <span>
                                                                                            <label class="form__choice-wrapper">
                                                                                                <label class="form-label text-primary" id="busFacility" for="form1">Bus Facility</label>
                                                                                            </label>
                                                                                        </span>
                                                                                        <span>
                                                                                            <div id="dropdownContainer">
                                                                                                <select id="busDropdown" class="form-control">
                                                                                                    @if(!empty($notificationData1['driver_name']))
                                                                                                    <option value=""> -- Please select -- </option>
                                                                                                    @foreach($drivername as $eachStudent)
                                                                                                    @if($notificationData1['driver_name'] == $eachStudent->ename)
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
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <br>
                                                                            <div id="data-container"></div>
                                                                            <div class="row">
                                                                                <div class="col-md-3">
                                                                                    <label>Total Amount:</label>
                                                                                    <input type="text" name="totalFees" id="totalFees" class="form-control" readonly>
                                                                                </div>
                                                                                <!-- Display bus facility -->
                                                                                <div class="col-md-5"></div>
                                                                                <div class="col-md-4">
                                                                                    @if(isset($notificationData1['required_school_transport']))
                                                                                        @if(!empty($busValue))
                                                                                            @if(!empty($notificationData1['bus_facility_start_date']))
                                                                                                <label>Bus Facility Start: {{$notificationData1['bus_facility_start_date']}}</label>
                                                                                            @endif
                                                                                        @else
                                                                                            @if(!empty($notificationData1['bus_facility_end_date']))
                                                                                                <label>Bus Facility End: {{$notificationData1['bus_facility_end_date']}}</label>
                                                                                            @endif
                                                                                        @endif
                                                                                    @endif

                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <label>Total Discounted Amount:</label>
                                                                                <input type="text" name="totalDiscountedFees" id="totalDiscountedFees" class="form-control" readonly>
                                                                            </div>
                                                                            <br>
                                                                            <div class="col-md-12">
                                                                                <button class="btn btn-primary">Submit</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                                <!-- end of main-content -->
                                                                <!-- Footer Start -->
                                                                <div class="flex-grow-1"></div>
                                                                <!-- fotter end -->
                                                            </div>






                                                            <link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/sweetalert2.min.css" />
                                                            <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
                                                            <script src="{{url('assets/backend')}}/js/plugins/sweetalert2.min.js"></script>
                                                            <script src="{{url('assets/backend')}}/js/scripts/sweetalert.script.min.js"></script>
                                                            <!-- Add jQuery library to your HTML if it's not already included -->
                                                            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

                                                            <script>
                                                                $(document).ready(function() {
                                                                    $('#busDropdown').change(function() {
                                                                        var isChecked = $('#busFacility').is(':checked') ? 1 : 0; // Set 1 if checked, 0 if unchecked
                                                                        var stuid = <?php echo json_encode($Vehicallist[0]->student_id); ?>;
                                                                        var selectedOption = $('#busDropdown').val();

                                                                        $.ajax({
                                                                            method: 'POST',
                                                                            headers: {
                                                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                                            },
                                                                            data: {
                                                                                isChecked: isChecked,
                                                                                value: stuid,
                                                                                driverName: selectedOption
                                                                            },
                                                                            url: "{{ url('bus-facility-on') }}",
                                                                            dataType: 'json',
                                                                            success: function(response) {
                                                                                // console.log(response);
                                                                                console.log('AJAX call triggered successfully.');
                                                                                // Handle the response if needed
                                                                                location.reload();

                                                                            },
                                                                            error: function(error) {
                                                                                console.error('Error triggering AJAX call: ' + error);
                                                                            }
                                                                        });
                                                                    });
                                                                });
                                                            </script>
                                                            <script>
                                                                // Sample JSON data (replace this with your actual JSON data)
                                                                // console.log($feesstr[0]['json_str']);
                                                                var notificationData1 = {!!json_encode($notificationData1)!!};
                                                                var jsonData = '<?php echo addslashes($jsonData); ?>';
                                                                var newrowdata = JSON.parse(jsonData);
                                                                // Parse the JSON data from the PHP object
                                                                var newstujsonData = {!!json_encode($all_inquiry[0]->json_str)!!};
                                                                var stujsonData = JSON.parse(newstujsonData);
                                                                var discountedFeesArray = [];
                                                                // console.log(newrowdata.account_name);

                                                                // Function to dynamically generate the HTML table
                                                                function generateTable() {
                                                                    var dataContainer = document.getElementById('data-container');
                                                                    dataContainer.innerHTML = ''; // Clear existing content

                                                                    var table = document.createElement('table');
                                                                    table.className = 'table table-bordered'; // Add Bootstrap classes for styling
                                                                    table.id = 'generatetablerow';
                                                                    // Create table header
                                                                    var thead = document.createElement('thead');
                                                                    var headerRow = document.createElement('tr');

                                                                    var headers = ['S.No.', 'Fees Date', 'Acc. Name', 'Fees', 'Discount', 'Due Date', 'Term', 'Clear'];
                                                                    headers.forEach(function(headerText) {
                                                                        var th = document.createElement('th');
                                                                        th.textContent = headerText;
                                                                        headerRow.appendChild(th);
                                                                    });

                                                                    thead.appendChild(headerRow);
                                                                    table.appendChild(thead);

                                                                    // Create table body
                                                                    var tbody = document.createElement('tbody');
                                                                    var jsonDataNew = JSON.parse(jsonData);
                                                                    // var jsonDataNew = JSON.parse(stujsonData);
                                                                    console.log(jsonDataNew);
                                                                    var tuitionFeesIndex = jsonDataNew.account_name.findIndex(item => item === 'TUITION FEES');
                                                                    console.log('Index of Tuition Fees:', tuitionFeesIndex);
                                                                    // Get the length of fees_date
                                                                    // Get the length of fees_date
                                                                    var totalDiscountedFees = 0;
                                                                    for (var i = 0; i < jsonDataNew.fees_date.length; i++) {
                                                                        var obj = {
                                                                            fees_date: jsonDataNew.fees_date[i],
                                                                            account_name: jsonDataNew.account_name[i],
                                                                            fees: jsonDataNew.fees[i],
                                                                            due_date: jsonDataNew.due_date[i],
                                                                            term: jsonDataNew.term[i],
                                                                        };
                                                                        var row = document.createElement('tr');

                                                                        // S.No.
                                                                        var tdSNo = document.createElement('td');
                                                                        tdSNo.textContent = i + 1; // Increment S.No. starting from 1
                                                                        row.appendChild(tdSNo);

                                                                        // Fees Date
                                                                        var tdFeesDate = document.createElement('td');
                                                                        var inputFeesDate = document.createElement('input');
                                                                        inputFeesDate.type = 'date';
                                                                        inputFeesDate.name = 'fees_date[]';
                                                                        inputFeesDate.value = obj.fees_date; // Set value or leave blank if undefined
                                                                        inputFeesDate.className = 'form-control';
                                                                        tdFeesDate.appendChild(inputFeesDate);
                                                                        row.appendChild(tdFeesDate);

                                                                        // Account Name
                                                                        var tdAccountName = document.createElement('td');
                                                                                var selectAccountName = document.createElement('select');
                                                                                selectAccountName.className = 'form-control';
                                                                                selectAccountName.name = 'account_name[]';

                                                                                // Add a "Please Select" option
                                                                                var pleaseSelectOption = document.createElement('option');
                                                                                pleaseSelectOption.value = ''; // You can set the value to an empty string or any other value you prefer
                                                                                pleaseSelectOption.textContent = 'Please Select';
                                                                                selectAccountName.appendChild(pleaseSelectOption);

                                                                                // Add options based on the condition
                                                                                if (obj.account_name === 'BUS FEES') {
                                                                                    var option = document.createElement('option');
                                                                                    option.value = 'BUS FEES';
                                                                                    option.textContent = 'BUS FEES';
                                                                                    option.selected = true;  // Select "BUS FEES" if it matches
                                                                                    selectAccountName.appendChild(option);
                                                                                }

                                                                                // Assuming you have PHP variable $course_fees_head_master available in your JavaScript code
                                                                                @if(!empty($course_fees_head_master))
                                                                                @foreach($course_fees_head_master as $each)
                                                                                // Skip adding "BUS FEES" if required_school_trasnport is set
                                                                                @if($each->ac_head_name !== 'BUS FEES' || empty($notificationData1['required_school_trasnport']))
                                                                                var option = document.createElement('option');
                                                                                option.value = '{{$each->ac_head_name}}';
                                                                                option.textContent = '{{$each->ac_head_name}}';
                                                                                selectAccountName.appendChild(option);
                                                                                @endif
                                                                                @endforeach
                                                                                @endif

                                                                                // Set the selected option based on obj.account_name
                                                                                selectAccountName.value = obj.account_name;
                                                                                tdAccountName.appendChild(selectAccountName);
                                                                                row.appendChild(tdAccountName);


                                                                        // Fees
                                                                        var tdFees = document.createElement('td');
                                                                        var inputFees = document.createElement('input');
                                                                        inputFees.type = 'text';
                                                                        inputFees.name = 'fees[]';
                                                                        inputFees.value = obj.fees || ''; // Set value or leave blank if undefined

                                                                        inputFees.oninput = function() {
                                                                            // Calculate discounted fees
                                                                            var discountPercentage = calculateDiscountPercentage(stujsonData);
                                                                            var feesValue = parseFloat(inputFees.value) || 0;
                                                                            var discountedFees = feesValue * (1 - discountPercentage);
                                                                            // console.log("yoyo");
                                                                            // Update the discounted fees input
                                                                            inputDiscountedFees.value = discountedFees.toFixed(2);
                                                                            // Update the total
                                                                            updateTotal();
                                                                        };

                                                                        inputFees.className = 'form-control orderFees_main';
                                                                        tdFees.appendChild(inputFees);
                                                                        row.appendChild(tdFees);
                                                                        // Function to calculate discount percentage based on conditions
                                                                        function calculateDiscountPercentage(stujsonData) {
                                                                            var discountPercentage = 0;

                                                                            if (stujsonData.staff_name && stujsonData.staff_name[0] !== 'Select Staff') {
                                                                                discountPercentage += 0.5; // 50% discount for staff
                                                                            }

                                                                            if (stujsonData.is_sibling_applied_for_admission === 'yes') {
                                                                                discountPercentage += 0.11; // 11% discount for sibling
                                                                            }

                                                                            if (stujsonData.staff_name && stujsonData.staff_name[0] !== 'Select Staff' && stujsonData.is_sibling_applied_for_admission === 'yes') {
                                                                                discountPercentage += 0.5 + 0.11;
                                                                            }

                                                                            return discountPercentage;
                                                                        }
                                                                        // Calculate the discount based on conditions
                                                                        var discountPercentage = 0;
                                                                        console.log("bahar ka staff");
                                                                        if (stujsonData.staff_name && stujsonData.staff_name[0] !== 'Select Staff') {
                                                                            discountPercentage += 0.5; // 50% discount for staff
                                                                            console.log("staff");
                                                                        }
                                                                        console.log(stujsonData.is_sibling_applied_for_admission);
                                                                        // console.log('is_sibling_applied_for_admission:', stujsonData.is_sibling_applied_for_admission);
                                                                        if (stujsonData.is_sibling_applied_for_admission === 'yes') {
                                                                            discountPercentage += 0.11; // 11% discount for sibling
                                                                            console.log("sibling");

                                                                        }
                                                                        console.log("bhara ke dono");
                                                                        // If both conditions are true, add both discounts
                                                                        if (stujsonData.staff_name && stujsonData.staff_name[0] !== 'Select Staff' && stujsonData.is_sibling_applied_for_admission === 'yes') {
                                                                            discountPercentage += 0.5 + 0.11;
                                                                            console.log("dono");

                                                                        }
                                                                        console.log("aaya to hai");
                                                                        if (i === tuitionFeesIndex) {
                                                                            console.log("hyy");
                                                                            // Calculate discounted fees
                                                                            console.log(inputFees.value);
                                                                            var discountedFees = parseFloat(inputFees.value) * (1 - discountPercentage);
                                                                            console.log(discountedFees);
                                                                        } else {
                                                                            console.log("no");
                                                                            var discountedFees = inputFees.value;
                                                                        }
                                                                        totalDiscountedFees += discountedFees; // Accumulate discounted fees for total

                                                                        // Store discounted fees in the array
                                                                        discountedFeesArray.push(discountedFees);
                                                                        // console.log(discountedFeesArray);
                                                                        // Display the discounted fees in a new column
                                                                        // Display the discounted fees as an input field
                                                                        var tdDiscountedFees = document.createElement('td');
                                                                        var inputDiscountedFees = document.createElement('input');
                                                                        inputDiscountedFees.type = 'text';
                                                                        inputDiscountedFees.name = 'discounted_fees[]';
                                                                        inputDiscountedFees.value = discountedFees; // Set the discounted fees as the value
                                                                        inputDiscountedFees.className = 'form-control orderFees_main';
                                                                        tdDiscountedFees.appendChild(inputDiscountedFees);
                                                                        row.appendChild(tdDiscountedFees);

                                                                        tbody.appendChild(row);
                                                                        // Due Date
                                                                        var tdDueDate = document.createElement('td');
                                                                        var inputDueDate = document.createElement('input');
                                                                        inputDueDate.type = 'date';
                                                                        inputDueDate.name = 'due_date[]';
                                                                        inputDueDate.value = obj.due_date || ''; // Set value or leave blank if undefined
                                                                        inputDueDate.className = 'form-control';
                                                                        tdDueDate.appendChild(inputDueDate);
                                                                        row.appendChild(tdDueDate);

                                                                        // Term
                                                                        var tdTerm = document.createElement('td');
                                                                        var selectTerm = document.createElement('select');
                                                                        selectTerm.className = 'form-control';
                                                                        selectTerm.name = 'term[]';

                                                                        for (var j = 0; j < jsonDataNew.term.length; j++) {
                                                                            var option = document.createElement('option');
                                                                            option.value = jsonDataNew.term[j];
                                                                            option.textContent = jsonDataNew.term[j];
                                                                            if (jsonDataNew.term[j] === '2nd') { // Replace '2nd' with the term value you want to pre-select
                                                                                option.selected = true;
                                                                            }
                                                                            selectTerm.appendChild(option);
                                                                        }

                                                                        tdTerm.appendChild(selectTerm);
                                                                        row.appendChild(tdTerm);

                                                                        // Clear Button
                                                                        var tdClear = document.createElement('td');
                                                                        var clearButton = document.createElement('button');
                                                                        clearButton.textContent = 'Clear';
                                                                        clearButton.className = 'btn btn-danger';

                                                                        clearButton.type = 'button';

                                                                        (function(index) {
                                                                            clearButton.onclick = function() {
                                                                                var sNo = index + 1;
                                                                                clearRowFields(sNo);
                                                                                return false;
                                                                            };
                                                                        })(i);

                                                                        tdClear.appendChild(clearButton);
                                                                        row.appendChild(tdClear);

                                                                        tbody.appendChild(row);
                                                                    }
                                                                    // Create an input for total discounted fees
                                                                    var totalDiscountedFeesInput = document.createElement('input');
                                                                    totalDiscountedFeesInput.type = 'text';
                                                                    totalDiscountedFeesInput.value = totalDiscountedFees;
                                                                    totalDiscountedFeesInput.readOnly = true;

                                                                    // Assuming you have a container element to append this to, replace 'yourContainerId' with the actual ID
                                                                    var container = document.getElementById('totalFees');
                                                                    container.appendChild(totalDiscountedFeesInput);
                                                                    table.appendChild(tbody);
                                                                    dataContainer.appendChild(table);
                                                                }

                                                                function calculateDiscountAndDisplay(inputFees, tdDiscountedFees) {
                                                                    // Get the entered fees value
                                                                    var enteredFees = parseFloat(inputFees.value) || 0;

                                                                    // Calculate the discount based on conditions
                                                                    var discountPercentage = 0;

                                                                    if (stujsonData.staff_name && stujsonData.staff_name[0] !== 'Select Staff') {
                                                                        discountPercentage += 0.5; // 50% discount for staff
                                                                    }

                                                                    if (stujsonData.is_sibling_applied_for_admission === 'yes') {
                                                                        discountPercentage += 0.11; // 11% discount for sibling
                                                                    }

                                                                    // If both conditions are true, add both discounts
                                                                    if (stujsonData.staff_name && stujsonData.staff_name[0] !== 'Select Staff' && stujsonData.is_sibling_applied_for_admission === 'yes') {
                                                                        discountPercentage += 0.5 + 0.11;
                                                                    }

                                                                    // Calculate discounted fees
                                                                    var discountedFees = enteredFees * (1 - discountPercentage);

                                                                    // Display the discounted fees in the respective cell
                                                                    tdDiscountedFees.textContent = discountedFees.toFixed(2);

                                                                    // Update the total fees
                                                                    updateTotal();
                                                                }
                                                                console.log(discountedFeesArray);

                                                                function calculateTotalSum(arr) {
                                                                    var totalSum = arr.reduce(function(acc, fee) {
                                                                        return acc + fee;
                                                                    }, 0);
                                                                    return totalSum;
                                                                }

                                                                // Calculate the total sum of discounted fees
                                                                var totalSumOfDiscountedFees = calculateTotalSum(discountedFeesArray);

                                                                console.log('Total Sum of Discounted Fees:', totalSumOfDiscountedFees);

                                                                // Function to clear fields of the current row

                                                                // Function to clear fields of the current row
                                                                function clearRowFields(sNo) {
                                                                    // alert(sNo);
                                                                    var fff = <?php echo json_encode($Vehicallist[0]->student_id); ?>;
                                                                    var wholedata = <?php echo json_encode($Vehicallist[0]); ?>;
                                                                    // console.log(fff);
                                                                    var row = document.querySelector('tbody tr:nth-child(' + sNo + ')');
                                                                    //  alert(fff);
                                                                    var inputs = row.querySelectorAll('input, select');

                                                                    inputs.forEach(function(input) {
                                                                        input.value = '';
                                                                    });

                                                                    // Make an AJAX request to delete the row on the server
                                                                    $.ajax({
                                                                        method: 'POST',
                                                                        headers: {
                                                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                                        },
                                                                        data: {
                                                                            sNo: sNo,
                                                                            value: fff,
                                                                            fulldata: wholedata,
                                                                            jsonData: JSON.stringify(jsonData)
                                                                        }, // Send jsonData as part of the request body
                                                                        url: "{{ url('student-delete-row') }}",
                                                                        dataType: 'json',
                                                                        success: function(response) {
                                                                            // Handle the response from the server if needed
                                                                            // console.log('AJAX request successful:', response);

                                                                            // Assuming the response contains the updated JSON data,
                                                                            // you can replace the current jsonData with the updated data
                                                                            jsonData = response.updatedData;
                                                                            // console.log("pratik00");
                                                                            location.reload();
                                                                            // console.log(jsonData);
                                                                        },
                                                                        error: function(xhr, status, error) {
                                                                            // Handle errors if the AJAX request fails
                                                                            console.error('AJAX request error:', status, error);
                                                                        }
                                                                    });
                                                                }

                                                                function generateTermDropdown(selectElement, selectedTerm) {
                                                                    // Assuming you have PHP variable $terms available in your JavaScript code
                                                                    @if(!empty($terms))
                                                                    @foreach($terms as $term)
                                                                    var option = document.createElement('option');
                                                                    option.value = '{{$term->terms}}';
                                                                    option.textContent = '{{$term->terms}}';

                                                                    // Check if this term matches the selectedTerm and set as selected
                                                                    if ('{{$term->terms}}' === selectedTerm) {
                                                                        option.selected = true;
                                                                    }

                                                                    selectElement.appendChild(option);
                                                                    @endforeach
                                                                    @endif
                                                                }

                                                                // Function to add a new row to the table
                                                                function addRow() {
                                                                    var tbody = document.querySelector('#generatetablerow tbody');
                                                                    var newRow = document.createElement('tr');

                                                                    // S.No.
                                                                    var tdSNo = document.createElement('td');
                                                                    tdSNo.textContent = tbody.children.length + 1; // Increment S.No. starting from the current row count + 1
                                                                    newRow.appendChild(tdSNo);

                                                                    // Fees Date
                                                                    var tdFeesDate = document.createElement('td');
                                                                    var inputFeesDate = document.createElement('input');
                                                                    inputFeesDate.type = 'date';
                                                                    inputFeesDate.name = 'fees_date[]';
                                                                    inputFeesDate.value = '';
                                                                    inputFeesDate.className = 'form-control';
                                                                    tdFeesDate.appendChild(inputFeesDate);
                                                                    newRow.appendChild(tdFeesDate);

                                                                    // Account Name
                                                                    var tdAccountName = document.createElement('td');
                                                                    var selectAccountName = document.createElement('select');
                                                                    selectAccountName.className = 'form-control';
                                                                    selectAccountName.name = 'account_name[]';

                                                                    // Add a "Please Select" option
                                                                    var pleaseSelectOption = document.createElement('option');
                                                                    pleaseSelectOption.value = ''; // You can set the value to an empty string or any other value you prefer
                                                                    pleaseSelectOption.textContent = 'Please Select';
                                                                    selectAccountName.appendChild(pleaseSelectOption);

                                                                    // Assuming you have PHP variable $course_fees_head_master available in your JavaScript code
                                                                    @if(!empty($course_fees_head_master))
                                                                    @foreach($course_fees_head_master as $each)
                                                                    var option = document.createElement('option');
                                                                    option.value = '{{$each->ac_head_name}}';
                                                                    option.textContent = '{{$each->ac_head_name}}';
                                                                    selectAccountName.appendChild(option);
                                                                    @endforeach
                                                                    @endif

                                                                    tdAccountName.appendChild(selectAccountName);
                                                                    newRow.appendChild(tdAccountName);
                                                                    // Add an event listener to get the selected value when it changes
                                                                    selectAccountName.addEventListener('change', function() {
                                                                        var selectedValue = this.value;
                                                                    });
                                                                    // Calculate discount for the new row
                                                                    var discountPercentage = 0;

                                                                    if (stujsonData.staff_name && stujsonData.staff_name[0] !== 'Select Staff') {
                                                                        discountPercentage += 0.5; // 50% discount for staff
                                                                    }

                                                                    if (stujsonData.is_sibling_applied_for_admission === 'yes') {
                                                                        discountPercentage += 0.11; // 11% discount for sibling
                                                                    }

                                                                    // If both conditions are true, add both discounts
                                                                    if (stujsonData.staff_name && stujsonData.staff_name[0] !== 'Select Staff' && stujsonData.is_sibling_applied_for_admission === 'yes') {
                                                                        discountPercentage += 0.5 + 0.11;
                                                                    }

                                                                    // Fees
                                                                    var tdFees = document.createElement('td');
                                                                    var inputFees = document.createElement('input');
                                                                    inputFees.type = 'text';
                                                                    inputFees.name = 'fees[]';
                                                                    inputFees.value = '';

                                                                    // Add onchange event handler to inputFees
                                                                    inputFees.onchange = function() {
                                                                        var selectedValue = selectAccountName.value;

                                                                        // Check if the selected account name is 'TUITION FEES'
                                                                        if (selectedValue === 'TUITION FEES') {
                                                                            // Apply a discount if the selected account name is 'TUITION FEES'
                                                                            var discountedFees = parseFloat(inputFees.value) * (1 - discountPercentage);
                                                                            tdDiscountedFeesInput.value = discountedFees.toFixed(2);
                                                                        } else {
                                                                            // If the selected account name is not 'TUITION FEES', set the input value as it is
                                                                            tdDiscountedFeesInput.value = inputFees.value;
                                                                        }

                                                                        updateTotal(); // Call the function to update the total when fees change
                                                                    };

                                                                    inputFees.className = 'form-control orderFees_main';
                                                                    tdFees.appendChild(inputFees);
                                                                    newRow.appendChild(tdFees);

                                                                    // Calculate discounted fees for the initial empty value
                                                                    var discountedFees = parseFloat(inputFees.value) * (1 - discountPercentage);

                                                                    // Display the discounted fees in a new column
                                                                    var tdDiscountedFees = document.createElement('td');
                                                                    var tdDiscountedFeesInput = document.createElement('input');
                                                                    tdDiscountedFeesInput.type = 'text';
                                                                    tdDiscountedFeesInput.name = 'discounted_fees[]';
                                                                    tdDiscountedFeesInput.value = ''; // Set initial value to an empty string
                                                                    tdDiscountedFeesInput.className = 'form-control orderFees_main';

                                                                    // Add onchange event handler to tdDiscountedFeesInput
                                                                    tdDiscountedFeesInput.onchange = function() {
                                                                        var newDiscountedFees = parseFloat(this.value);
                                                                        // Update the value when it changes
                                                                        updateTotal();
                                                                    };

                                                                    tdDiscountedFees.appendChild(tdDiscountedFeesInput);
                                                                    newRow.appendChild(tdDiscountedFees);
                                                                    // Due Date
                                                                    var tdDueDate = document.createElement('td');
                                                                    var inputDueDate = document.createElement('input');
                                                                    inputDueDate.type = 'date';
                                                                    inputDueDate.name = 'due_date[]';
                                                                    inputDueDate.value = '';
                                                                    inputDueDate.className = 'form-control';
                                                                    tdDueDate.appendChild(inputDueDate);
                                                                    newRow.appendChild(tdDueDate);

                                                                    // Term
                                                                    var tdTerm = document.createElement('td');
                                                                    var selectTerm = document.createElement('select');
                                                                    selectTerm.className = 'form-control';
                                                                    selectTerm.name = 'term[]';

                                                                    // Call the generateTermDropdown function to populate options
                                                                    generateTermDropdown(selectTerm, ''); // Pass the selected term as an empty string for new rows

                                                                    tdTerm.appendChild(selectTerm);
                                                                    newRow.appendChild(tdTerm);

                                                                    // Clear Button
                                                                    var tdClear = document.createElement('td');
                                                                    var clearButton = document.createElement('button');
                                                                    clearButton.textContent = 'Clear';
                                                                    clearButton.className = 'btn btn-danger';

                                                                    // Add type="button" to prevent form submission
                                                                    clearButton.type = 'button';

                                                                    clearButton.onclick = function() {
                                                                        // Remove the new row from the table
                                                                        tbody.removeChild(newRow);
                                                                        return false;
                                                                    };

                                                                    tdClear.appendChild(clearButton);
                                                                    newRow.appendChild(tdClear);

                                                                    tbody.appendChild(newRow);
                                                                    var specifiedTable = document.getElementById('generatetablerow');
                                                                    if (specifiedTable) {
                                                                        specifiedTable.querySelector('tbody').appendChild(newRow);
                                                                    }
                                                                    //  console.log(inputFees);

                                                                }
                                                                // Print the total sum of discounted fees outside the function
                                                                // Calculate the total sum of discounted fees
                                                                // Calculate the total sum of discounted fees
                                                                // var totalDiscountedFees = calculateTotalDiscountedFees(discountedFeesArray);
                                                                // console.log('Total Discounted Fees:', totalDiscountedFees);


                                                                // Function to calculate discounted fees based on discountPercentage
                                                                function calculateDiscountAndDisplay(inputFees, tdDiscountedFees) {
                                                                    var originalFees = parseFloat(inputFees.value);
                                                                    var discountPercentage = parseFloat(inputFees.getAttribute('data-discount'));

                                                                    if (!isNaN(originalFees) && !isNaN(discountPercentage)) {
                                                                        var discountedFees = originalFees * (1 - discountPercentage);
                                                                        tdDiscountedFees.textContent = discountedFees.toFixed(2);
                                                                        updateTotalDiscountedFees(); // Update the total discounted fees when discount changes
                                                                    }
                                                                }

                                                                // Function to update the total fees and total discounted fees
                                                                function updateTotal() {
                                                                    var totalFees = 0;
                                                                    var totalDiscountedFees = 0;

                                                                    // Calculate total fees and total discounted fees
                                                                    var feeInputs = document.querySelectorAll('input[name="fees[]"]');
                                                                    feeInputs.forEach(function(input) {
                                                                        var fee = parseFloat(input.value) || 0;
                                                                        totalFees += fee;
                                                                    });

                                                                    var discountedFeeInputs = document.querySelectorAll('input[name="discounted_fees[]"]');
                                                                    discountedFeeInputs.forEach(function(input) {
                                                                        var discountedFee = parseFloat(input.value) || 0;
                                                                        totalDiscountedFees += discountedFee;
                                                                    });

                                                                    // Update the total fees and total discounted fees in their respective input fields
                                                                    var totalFeesInput = document.getElementById('totalFees');
                                                                    totalFeesInput.value = totalFees.toFixed(2);

                                                                    var totalDiscountedFeesInput = document.getElementById('totalDiscountedFees');
                                                                    totalDiscountedFeesInput.value = totalDiscountedFees.toFixed(2);
                                                                }

                                                                // Function to update the total discounted fees
                                                                function updateTotalDiscountedFees() {
                                                                    var totalDiscountedFees = 0;

                                                                    var discountedFeeInputs = document.querySelectorAll('input[name="discounted_fees[]"]');
                                                                    discountedFeeInputs.forEach(function(input) {
                                                                        var discountedFee = parseFloat(input.value) || 0;
                                                                        totalDiscountedFees += discountedFee;
                                                                    });

                                                                    // Update the total discounted fees in its input field
                                                                    var totalDiscountedFeesInput = document.getElementById('totalDiscountedFees');
                                                                    totalDiscountedFeesInput.value = totalDiscountedFees.toFixed(2);
                                                                }

                                                                window.onload = function() {
                                                                    generateTable();
                                                                    updateTotal();

                                                                    var addRowButton = document.getElementById('addRowButton');
                                                                    addRowButton.addEventListener('click', function() {
                                                                        addRow();
                                                                        updateTotal();
                                                                    });

                                                                    // Add onchange event listener to discount input fields
                                                                    var discountInputs = document.querySelectorAll('input[name="discounted_fees[]"]');
                                                                    discountInputs.forEach(function(input) {
                                                                        input.addEventListener('change', function() {
                                                                            var tdDiscountedFees = input.parentElement; // Get the parent td
                                                                            calculateDiscountAndDisplay(input, tdDiscountedFees);
                                                                            updateTotal();
                                                                            updateTotalDiscountedFees(); // Update the total discounted fees when a discount changes
                                                                        });
                                                                    });
                                                                };
                                                            </script>


                                                            <script type="text/javascript">
                                                                $('.removeItem').click(function(event) {

                                                                    event.preventDefault();

                                                                    var delete_id = $(this).data('delete_id');

                                                                    $(this).parents('tr').hide();
                                                                    swal({
                                                                        title: 'Are you sure?',
                                                                        text: "It will permanently deleted !",
                                                                        type: 'warning',
                                                                        showCancelButton: true,
                                                                        confirmButtonColor: 'success',
                                                                        cancelButtonColor: '#d33',
                                                                        confirmButtonText: 'Yes, delete it!'
                                                                    }).then(function() {

                                                                        var myUrl = "{{url('course-fees-structure-master-delete')}}";

                                                                        $.ajax({
                                                                            url: myUrl,
                                                                            type: "POST",
                                                                            data: {
                                                                                "_token": "{{ csrf_token() }}",
                                                                                delete_id: delete_id
                                                                            },
                                                                            success: function(response) {

                                                                                swal(
                                                                                    'Deleted!',
                                                                                    'Your file has been deleted.',
                                                                                    'success'
                                                                                );

                                                                            }
                                                                        });

                                                                    })

                                                                });
                                                            </script>
                                                        <?php } else { ?>
                                                            <!-- start section -->

                                                            <h3>Generate due chart of this student </h3>

                                                            <!-- end section  -->

                                                        <?php } ?>
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
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script>
    jQuery(document).ready(function($) {
        $("#resetpassword").click(function() {
            var id = $("#resetpassword").val();
            $.ajax({
                type: "POST",
                data: {
                    id,
                    id
                },
                url: "{{ url('resetpasswordadmin') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(data) {
                    $("#success").text("Success Fully reset !");
                    console.log(data)
                }
            });
        });

    });
</script>

@endsection
