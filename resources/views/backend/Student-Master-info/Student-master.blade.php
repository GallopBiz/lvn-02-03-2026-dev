@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
    <div class="breadcrumb">
        <h1 class="me-2">Student Master Information</h1>   
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
                        <a href="#step-2">Step 2<br /><small>Personal Details</small></a>
                    </li>
                     <li>
                  <a href="#step-3"
                     >Step 3<br /><small>Details of Siblings</small></a
                     >
                  </li>
                  <li>
                  <a href="#step-4"
                     >Step 4<br /><small>Details of Siblings</small></a
                     >
                  </li>
                  <li>
                  <a href="#step-5"
                     >Step 5<br /><small>Bank Details</small></a
                     >
                  </li>
                </ul>
                <div>
                    <div id="step-1">
                        <form class="needs-validation" novalidate="novalidate" method="post"
                                action="{{url('save-student-inquiry')}}">
                        <h3 class="border-bottom border-gray pb-2">
                            Student Enquiry Entry Form
                        </h3>
                        <div class="form_section1_div">
                                <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                            <label for="studentname">Student Name</label>
                                            <input name="student_name" class="form-control" id="studentname" type="text"
                                                placeholder="Enter Student Name" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <br>
                                            <input name="student_name" class="form-control" id="studentname" type="text"
                                                placeholder="Enter Student Name in Hindi" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="firstName1">Class studied:</label>
                                            <select id="application_for" class="form-control" name="application_for"
                                                autocomplete="shipping address-level1" required>
                                                <option value="" disabled selected>Please select</option>
                                                <option value="">A</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="studentname">enrolment NO.</label>
                                            <input name="enrolment" class="form-control" id="studentname"
                                                placeholder="Enter enrolment" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="studentname">role NO.</label>
                                            <input name="enrolment" class="form-control" id="studentname"
                                                placeholder="Enter enrolment" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="studentname">CCE ROLL NO.</label>
                                            <input name="Enter   CCE ROLE" class="form-control" id="studentname"
                                                placeholder="Enter   CCE ROLE" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="studentname">Serial NO.</label>
                                            <input name="Serial" class="form-control" id="studentname"
                                                placeholder="Enter   Serial no." />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="firstName1">Batch</label>
                                            <select id="gender" class="form-control" name="Batch"
                                                autocomplete="shipping address-level1" required>
                                                <option value="" disabled selected>Please select</option>
                                                <option value="">2023-2023</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="firstName1">Joning Class</label>
                                            <select id="gender" class="form-control" name="Jclass"
                                                autocomplete="shipping address-level1" required>
                                                <option value="" disabled selected>Please select</option>
                                                <option value="">A</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="firstName1">Joning Section</label>
                                            <select id="gender" class="form-control" name="JSection"
                                                autocomplete="shipping address-level1" required>
                                                <option value="" disabled selected>Please select</option>
                                                <option value="">A</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="firstName1">Current Class</label>
                                            <select id="gender" class="form-control" name="Cclass"
                                                autocomplete="shipping address-level1" required>
                                                <option value="" disabled selected>Please select</option>
                                                <option value="">A</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="firstName1">Current Section</label>
                                            <select id="gender" class="form-control" name="CSection"
                                                autocomplete="shipping address-level1" required>
                                                <option value="" disabled selected>Please select</option>
                                                <option value="">A</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="firstName1">Student Type</label>
                                            <select id="gender" class="form-control" name="StudntType"
                                                autocomplete="shipping address-level1" required>
                                                <option value="" disabled selected>Please select</option>
                                                <option value="">A</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="Caste">Time Machine Card NO</label>
                                            <input class="form-control Caste" id="Caste"
                                                placeholder="Time Machine Card NO" name="student_caste" />
                                        </div>
                                        <h3>General Information:</h3>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="Caste">Admission Date :</label>
                                            <input type="date" class="form-control Caste" id="Caste"
                                                name="Admissiondate" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="Caste">Fees Apply Date :</label>
                                            <input type="date" class="form-control Caste" id="Caste"
                                                name="feesapplydate" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="Caste">Date of Birth :</label>
                                            <input type="date" class="form-control Caste" id="Caste" name="dob" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="religion">Gender</label>
                                            <select id="religion" class="form-control" name="gender" autocomplete=""
                                                required>
                                                <option value="">Please select</option>
                                                <option value="">male</option>
                                                <option value="">female</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="religion">Religion</label>
                                            <select id="religion" class="form-control" name="religion" autocomplete=""
                                                required>
                                                <option value="">Please select</option>
                                                <option value="">Hindu</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="category">Category</label>
                                            <select id="category" class="form-control" name="category" autocomplete=""
                                                required>
                                                <option value="">Please select</option>
                                                <option value="">Gen</option>
                                                <option value="">Stsc</option>
                                                <option value="">Obc</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="firstName1">Sub Caste</label>
                                            <select id="classname" class="form-control" name="subcaste" autocomplete="">
                                                <option value="">Please select</option>
                                                <option value="">A</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="firstName1">Place of Birth</label>
                                            <input class="form-control" id="address" type="text"
                                                placeholder="Place of Birth" name="Place_of_Birth" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="firstName1">Nationality</label>
                                            <input class="form-control" id="address" type="text"
                                                placeholder="Nationality" name="nationality" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="firstName1">Blood Group</label>
                                            <select id="session_name" class="form-control" name="Blood_Group"
                                                autocomplete="">
                                                <option value="">Please select</option>
                                                <option value="">A</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="address">Mother Tongue :</label>
                                            <input class="form-control" id="address" type="text"
                                                placeholder="Enter address" name="Mother Tongue" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="address">Student Email :</label>
                                            <input class="form-control" id="address" type="email"
                                                placeholder="Student Email" name="Student_Email" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="address">Student Mobile :</label>
                                            <input class="form-control" id="address" type="number"
                                                placeholder="Student Mobile" name="Student_Mobile" />
                                        </div>
                                        <h3>Sibling Information</h3>
                                        <p>If sibling of this also study in Institute then use following option to
                                            specify
                                            them.</P>
                                            <div class="col-md-4 form-group mb-3">
                                            <label for="address">This student is :</label>
                                            <input type="checkbox" id="vehicle1" name="vehicle1" value="Bike">
                                            <label for="vehicle1"> Younger Sibling</label><br>
                                            <input type="checkbox" id="vehicle2" name="vehicle2" value="Car">
                                            <label for="vehicle2"> Elder Sibling</label>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="firstName1">Sibling Name</label>
                                            <select id="session_name" class="form-control" name="Blood_Group"
                                                autocomplete="">
                                                <option value="">Please select</option>
                                                <option value="">A</option>
                                            </select>
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
                                                    <input name="student_father_name" class="form-control"
                                                        id="fathername" type="text" placeholder="Enter father name" />
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="siblings_school">Date Of Birth</label>
                                                    <input name="father_dob" class="form-control date4" id="picker2-"
                                                        type="text" placeholder="dd-mm-yyyy" name="siblings_bod">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="siblings_school">Education</label>
                                                    <input name="father_education" class="form-control date4"
                                                        id="picker2-" type="text" placeholder="Enter Education"
                                                        name="father_education">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="siblings_school">Organization</label>
                                                    <input name="father_organization" class="form-control date4"
                                                        id="picker2-" type="text" placeholder="Enter Organization"
                                                        name="father_education">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="mothername">Designation</label>
                                                    <input name="father_designation" class="form-control"
                                                        id="fatheroccupation" type="text"
                                                        placeholder="Enter father occupation" />
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="siblings_school">Office Telephone</label>
                                                    <input name="father_office_telephone" class="form-control date4"
                                                        id="picker2-" type="text" placeholder="Enter Organization"
                                                        name="father_education">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="siblings_school">Email id</label>
                                                    <input name="father_email_id" class="form-control date4"
                                                        id="picker2-" type="email" placeholder="Enter Email">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="siblings_school">Mobile No.</label>
                                                    <input name="father_mobile" class="form-control date4" id="picker2-"
                                                        type="number" placeholder="Enter Email">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="siblings_school">Annual Income</label>
                                                    <input name="Annual_Income" class="form-control date4" id="picker2-"
                                                        type="number" placeholder="Enter Email">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="siblings_school">Residental Address</label>
                                                    <input class="form-control date4" id="picker2-" type="text"
                                                        placeholder="Residental Address" name="father_res_address">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="siblings_school">Whatsaap No.</label>
                                                    <input class="form-control date4" id="picker2-" type="number"
                                                        placeholder="Whatsaap No" name="father_emergency_contact">
                                                </div>
                                <h4>Mother's Details</h4>
                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">Mother Name</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="email"
                                                        placeholder="Enter email" fdprocessedid="csh6vi"
                                                        name="mother_name">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">Date of Birth</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="date"
                                                        name="mother_dob">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">Education</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="text"
                                                        placeholder="Enter email" name="mother_education">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">Occupation</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="text"
                                                        placeholder="Enter Mother Occupation" name="mother_ocupation">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">Organization</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="text"
                                                        placeholder="Enter Organization" name="mother_organization">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">Designation</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="text"
                                                        placeholder="Enter Designation" name="mother_organization">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="siblings_school">Office Telephone</label>
                                                    <input class="form-control date4" id="picker2-" type="text"
                                                        placeholder="Enter Organization" name="mother_office_telephone">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="siblings_school">Email id</label>
                                                    <input class="form-control date4" id="picker2-" type="email"
                                                        placeholder="Enter Email" name="mother_email">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="phone">Mobile No.</label>
                                                    <input class="form-control" id="phone" placeholder="Enter mobile no"
                                                        name="mother_mobile" />
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="siblings_school">Residental Address</label>
                                                    <textarea class="form-control date4" id="picker2-" type="text"
                                                        placeholder="Residental Address"
                                                        name="mother_res_address"></textarea>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="siblings_school">Whatsaap No.</label>
                                                    <input class="form-control date4" id="picker2-" type="number"
                                                        placeholder="Whatsaap No." name="mother_emergency_contact">
                                                </div>
                                                <h4>Parnment Address</h4>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">Address</label>
                                                    <textarea class="form-control date4" id="picker2-" type="text"
                                                        placeholder=" Address" name="address"></textarea>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">City</label>
                                                    <select id="admission-type" class="form-control" name="City"
                                                        autocomplete="" required>
                                                        <option disabled selected>Please select</option>
                                                        <option value="">A</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">Country</label>
                                                    <select id="admission-type" class="form-control" name="Country"
                                                        autocomplete="" required>
                                                        <option disabled selected>Please select</option>
                                                        <option value="">India</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">State</label>
                                                    <select id="admission-type" class="form-control" name="State"
                                                        autocomplete="" required>
                                                        <option disabled selected>Please select</option>
                                                        <option value="">M.P.</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">District</label>
                                                    <select id="admission-type" class="form-control" name="District"
                                                        autocomplete="" required>
                                                        <option disabled selected>Please select</option>
                                                        <option value="">A</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">Tehsil</label>
                                                    <select id="admission-type" class="form-control" name="District"
                                                        autocomplete="" required>
                                                        <option disabled selected>Please select</option>
                                                        <option value="">A</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">Pin No.</label>
                                                    <select id="admission-type" class="form-control" name="District"
                                                        autocomplete="" required>
                                                        <option disabled selected>Please select</option>
                                                        <option value="">A</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">STD Code</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="text"
                                                        placeholder="STD Code" fdprocessedid="csh6vi" name="STD_code">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">Phone NO.</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="number"
                                                        placeholder="Phone NO." fdprocessedid="csh6vi" name="Phone_NO.">
                                                </div>
                                                <h4>Local Address</h4>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">Address</label>
                                                    <textarea class="form-control date4" id="picker2-" type="text"
                                                        placeholder=" Address" name="address"></textarea>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">City</label>
                                                    <select id="admission-type" class="form-control" name="City"
                                                        autocomplete="" required>
                                                        <option disabled selected>Please select</option>
                                                        <option value="">A</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">Country</label>
                                                    <select id="admission-type" class="form-control" name="Country"
                                                        autocomplete="" required>
                                                        <option disabled selected>Please select</option>
                                                        <option value="">India</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">State</label>
                                                    <select id="admission-type" class="form-control" name="State"
                                                        autocomplete="" required>
                                                        <option disabled selected>Please select</option>
                                                        <option value="">M.P.</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">District</label>
                                                    <select id="admission-type" class="form-control" name="District"
                                                        autocomplete="" required>
                                                        <option disabled selected>Please select</option>
                                                        <option value="">A</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">Tehsil</label>
                                                    <select id="admission-type" class="form-control" name="District"
                                                        autocomplete="" required>
                                                        <option disabled selected>Please select</option>
                                                        <option value="">A</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="enquiryno">Pin No.</label>
                                                    <select id="admission-type" class="form-control" name="District"
                                                        autocomplete="" required>
                                                        <option disabled selected>Please select</option>
                                                        <option value="">A</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">STD Code</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="text"
                                                        placeholder="STD Code" fdprocessedid="csh6vi" name="STD_code">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">Phone NO.</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="number"
                                                        placeholder="Phone NO." fdprocessedid="csh6vi" name="Phone_NO.">
                                                </div>
                                                <h3> Student Bank Detail</h3>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">Bank Name</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="text"
                                                        placeholder="Bank Name" fdprocessedid="csh6vi" name="Bank_Name">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">Branch Name</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="text"
                                                        placeholder="Branch Name" fdprocessedid="csh6vi"
                                                        name="Branch_Name">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">A/C no.</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="number"
                                                        placeholder="A/C no." fdprocessedid="csh6vi" name="A/C_no.">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="pincode">IFSC code</label>
                                                    <input class="form-control" id="exampleInputEmail1" type="text"
                                                        placeholder="IFSC code" fdprocessedid="csh6vi" name="IFSC_code">
                                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="step-3">
                    <h3 class="border-bottom border-gray pb-2">
                                    General Education Information :-
                                </h3>
                        <div>
                            <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Previous Institute:</label>
                                            <input name="Previous_Institute" class="form-control" id="fathername"
                                                type="text" placeholder="Previous Institute" />
                                        </div>
                                        <h3>10th Information :</h3>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">10th School Name:</label>
                                            <input name="10th_School_Name" class="form-control" id="fathername"
                                                type="text" placeholder="10th School Name" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">10th Board/University:</label>
                                            <input name="10th_Board/University" class="form-control" id="fathername"
                                                type="text" placeholder="10th Board/University" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">10th Passing Years:</label>
                                            <input name="10th_Passing_Years" class="form-control" id="fathername"
                                                type="text" placeholder="10th_Passing_Years" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">10th Obtaint Marks:</label>
                                            <input name="10th_Obtaint_Marks" class="form-control" id="fathername"
                                                type="text" placeholder="10th Obtaint Marks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">10th Total Marks:</label>
                                            <input name="10th_Total_Marks" class="form-control" id="fathername"
                                                type="text" placeholder="10th Total Marks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">10th%:</label>
                                            <input name="10th%" class="form-control" id="fathername" type="text"
                                                placeholder="10th%" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">10th CGPA:</label>
                                            <input name="10th CGPA" class="form-control" id="fathername" type="text"
                                                placeholder="10th CGPA" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">10th Rank/Grade/Division:</label>
                                            <input name="10th_Rank/Grade/Division" class="form-control" id="fathername"
                                                type="text" placeholder="10th Rank/Grade/Division" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">10th Roll No.:</label>
                                            <input name="10th_Roll_No." class="form-control" id="fathername" type="text"
                                                placeholder="10th_Roll_No." />
                                        </div>
                                        <h3> 12th Information :</h3>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">12th School Name:</label>
                                            <input name="12th_School_Name" class="form-control" id="fathername"
                                                type="text" placeholder="12th School Name" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">12th Board/University:</label>
                                            <input name="12th_Board/University" class="form-control" id="fathername"
                                                type="text" placeholder="12th Board/University" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">12th School District:</label>
                                            <input name="12th_School_District" class="form-control" id="fathername"
                                                type="text" placeholder="12th_School_District" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">State:</label>
                                            <input name="State" class="form-control" id="fathername" type="text"
                                                placeholder="State" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Pin Code:</label>
                                            <input name="Pin_Code" class="form-control" id="fathername" type="number"
                                                placeholder="Pin_Code" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">12th Obtaint Marks:</label>
                                            <input name="12th_Obtaint_Marks" class="form-control" id="fathername"
                                                type="number" placeholder="12th Obtaint Marks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">12th Total Marks:</label>
                                            <input name="12th_Total_Marks" class="form-control" id="fathername"
                                                type="number" placeholder="12th Total Marks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">12th%:</label>
                                            <input name="12th%" class="form-control" id="fathername" type="text"
                                                placeholder="12th%" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">12th CGPA:</label>
                                            <input name="12th CGPA" class="form-control" id="fathername" type="text"
                                                placeholder="12th CGPA" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">12th Rank/Grade/Division:</label>
                                            <input name="12th_Rank/Grade/Division" class="form-control" id="fathername"
                                                type="text" placeholder="12th Rank/Grade/Division" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">12th Roll No.:</label>
                                            <input name="12th_Roll_No." class="form-control" id="fathername" type="text"
                                                placeholder="12th_Roll_No." />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="enquiryno">Stream/Subject</label>
                                            <select id="admission-type" class="form-control" name="Stream/Subject"
                                                autocomplete="" required>
                                                <option disabled selected>Please select</option>
                                                <option value="">Maths</option>
                                                <option value="">Science</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Additional subject:</label>
                                            <input name="Additional_subject" class="form-control" id="fathername"
                                                type="text" placeholder="Additional subject" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Center code:</label>
                                            <input name="Center_code" class="form-control" id="fathername" type="text"
                                                placeholder="Center code" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Admit Card Id:</label>
                                            <input name="Admit_Card_Id" class="form-control" id="fathername" type="text"
                                                placeholder="Admit Card Id" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">School Code:</label>
                                            <input name="School_Code" class="form-control" id="fathername" type="text"
                                                placeholder="School_Code" />
                                        </div>
                                        <h3>Graduation Information:</h3>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">College Name:</label>
                                            <input name="College Name" class="form-control" id="fathername" type="text"
                                                placeholder="College Name" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">University:</label>
                                            <input name="University" class="form-control" id="fathername" type="text"
                                                placeholder="University" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Years:</label>
                                            <input name="Years" class="form-control" id="fathername" type="text"
                                                placeholder="Years" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername"> Obtaint Marks:</label>
                                            <input name="Obtaint_Marks" class="form-control" id="fathername" type="text"
                                                placeholder="Obtaint Marks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Total Marks:</label>
                                            <input name="Total_Marks" class="form-control" id="fathername" type="text"
                                                placeholder="Total Marks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Graduaction %:</label>
                                            <input name="Graduaction%" class="form-control" id="fathername" type="text"
                                                placeholder="Graduaction %" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Rank/Grade/Division:</label>
                                            <input name="Rank/Grade/Division" class="form-control" id="fathername"
                                                type="text" placeholder="Rank/Grade/Division" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Roll No.:</label>
                                            <input name="Roll_No." class="form-control" id="fathername" type="text"
                                                placeholder="Roll_No." />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Ist sem/year(%/SGPA):</label>
                                            <input name="Ist_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="Ist_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">IIst sem/year(%/SGPA):</label>
                                            <input name="IIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="Ist_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">IIIst sem/year(%/SGPA):</label>
                                            <input name="IIIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="IIIst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">IVst sem/year(%/SGPA):</label>
                                            <input name="IVst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="IVst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Vst sem/year(%/SGPA):</label>
                                            <input name="Vst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="Vst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">VIst sem/year(%/SGPA):</label>
                                            <input name="VIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="VIst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Ist sem/year(%/SGPA):</label>
                                            <input name="VIIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="VIIst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">VIIIst sem/year(%/SGPA):</label>
                                            <input name="VIIIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="VIIIst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">IXst sem/year(%/SGPA):</label>
                                            <input name="IXst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="IXst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Xth sem/year(%/SGPA):</label>
                                            <input name="X_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="X_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">XIst sem/year(%/SGPA):</label>
                                            <input name="XIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="XIst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">XIIst sem/year(%/SGPA):</label>
                                            <input name="XIIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="XIIst_sem/year(%/SGPA)" />
                                        </div>
                                        <h3>Diploma's Information:</h3>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">College Name:</label>
                                            <input name="dCollege Name" class="form-control" id="fathername" type="text"
                                                placeholder="College Name" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">University:</label>
                                            <input name="DUniversity" class="form-control" id="fathername" type="text"
                                                placeholder="University" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Years:</label>
                                            <input name="DYears" class="form-control" id="fathername" type="text"
                                                placeholder="Years" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername"> Obtaint Marks:</label>
                                            <input name="DObtaint_Marks" class="form-control" id="fathername"
                                                type="text" placeholder="Obtaint Marks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Total Marks:</label>
                                            <input name="DTotal_Marks" class="form-control" id="fathername" type="text"
                                                placeholder="Total Marks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Graduaction %:</label>
                                            <input name="DGraduaction%" class="form-control" id="fathername" type="text"
                                                placeholder="Graduaction %" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Rank/Grade/Division:</label>
                                            <input name="DRank/Grade/Division" class="form-control" id="fathername"
                                                type="text" placeholder="Rank/Grade/Division" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Roll No.:</label>
                                            <input name="DRoll_No." class="form-control" id="fathername" type="text"
                                                placeholder="Roll_No." />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Ist sem/year(%/SGPA):</label>
                                            <input name="DIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="Ist_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">IIst sem/year(%/SGPA):</label>
                                            <input name="DIIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="Ist_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">IIIst sem/year(%/SGPA):</label>
                                            <input name="DIIIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="IIIst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">IVst sem/year(%/SGPA):</label>
                                            <input name="DIVst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="IVst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Vst sem/year(%/SGPA):</label>
                                            <input name="DVst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="Vst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">VIst sem/year(%/SGPA):</label>
                                            <input name="DVIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="VIst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Ist sem/year(%/SGPA):</label>
                                            <input name="DVIIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="VIIst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">VIIIst sem/year(%/SGPA):</label>
                                            <input name="DVIIIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="VIIIst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">IXst sem/year(%/SGPA):</label>
                                            <input name="DIXst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="IXst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Xth sem/year(%/SGPA):</label>
                                            <input name="DX_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="X_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">XIst sem/year(%/SGPA):</label>
                                            <input name="DXIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="XIst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">XIIst sem/year(%/SGPA):</label>
                                            <input name="DXIIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="XIIst_sem/year(%/SGPA)" />
                                        </div>
                                        <h3>Other Information:</h3>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">College Name:</label>
                                            <input name="OCollege Name" class="form-control" id="fathername" type="text"
                                                placeholder="College Name" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">University:</label>
                                            <input name="OUniversity" class="form-control" id="fathername" type="text"
                                                placeholder="University" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Years:</label>
                                            <input name="OYears" class="form-control" id="fathername" type="text"
                                                placeholder="Years" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername"> Obtaint Marks:</label>
                                            <input name="OObtaint_Marks" class="form-control" id="fathername"
                                                type="text" placeholder="Obtaint Marks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Total Marks:</label>
                                            <input name="OTotal_Marks" class="form-control" id="fathername" type="text"
                                                placeholder="Total Marks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Graduaction %:</label>
                                            <input name="OGraduaction%" class="form-control" id="fathername" type="text"
                                                placeholder="Graduaction %" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Rank/Grade/Division:</label>
                                            <input name="ORank/Grade/Division" class="form-control" id="fathername"
                                                type="text" placeholder="Rank/Grade/Division" />
                                        </div>

                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Roll No.:</label>
                                            <input name="ORoll_No." class="form-control" id="fathername" type="text"
                                                placeholder="Roll_No." />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Stream:</label>
                                            <input name="OStream" class="form-control" id="fathername" type="text"
                                                placeholder="Stream" />
                                        </div>
                                        <h3>Post Graduaction Information:</h3>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">College Name:</label>
                                            <input name="pCollege Name" class="form-control" id="fathername" type="text"
                                                placeholder="College Name" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">University:</label>
                                            <input name="pUniversity" class="form-control" id="fathername" type="text"
                                                placeholder="University" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Years:</label>
                                            <input name="pYears" class="form-control" id="fathername" type="text"
                                                placeholder="Years" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername"> Obtaint Marks:</label>
                                            <input name="pObtaint_Marks" class="form-control" id="fathername"
                                                type="text" placeholder="Obtaint Marks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Total Marks:</label>
                                            <input name="pTotal_Marks" class="form-control" id="fathername" type="text"
                                                placeholder="Total Marks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Graduaction %:</label>
                                            <input name="pGraduaction%" class="form-control" id="fathername" type="text"
                                                placeholder="Graduaction %" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Rank/Grade/Division:</label>
                                            <input name="pRank/Grade/Division" class="form-control" id="fathername"
                                                type="text" placeholder="Rank/Grade/Division" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Roll No.:</label>
                                            <input name="pRoll_No." class="form-control" id="fathername" type="text"
                                                placeholder="Roll_No." />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Ist sem/year(%/SGPA):</label>
                                            <input name="pIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="Ist_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">IIst sem/year(%/SGPA):</label>
                                            <input name="pIIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="Ist_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">IIIst sem/year(%/SGPA):</label>
                                            <input name="DIIIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="IIIst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">IVst sem/year(%/SGPA):</label>
                                            <input name="pIVst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="IVst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Vst sem/year(%/SGPA):</label>
                                            <input name="pVst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="Vst_sem/year(%/SGPA)" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">VIst sem/year(%/SGPA):</label>
                                            <input name="pVIst_sem/year(%/SGPA)" class="form-control" id="fathername"
                                                type="text" placeholder="VIst_sem/year(%/SGPA)" />
                                        </div>
                                        <h3>Qualified Exam Information:</h3>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Qualified Exam Name:</label>
                                            <input name="Qualified_Exam_Name" class="form-control" id="fathername"
                                                type="text" placeholder="Qualified Exam Name" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Qualified Exam Roll no.:</label>
                                            <input name="Qualified_Exam_Roll_no." class="form-control" id="fathername"
                                                type="text" placeholder="Qualified Exam Roll no." />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Qualified Exam Rank:</label>
                                            <input name="Qualified_Exam_Rank" class="form-control" id="fathername"
                                                type="text" placeholder="Qualified Exam Rank" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername"> Qualified Exam Quota:</label>
                                            <input name="Qualified-Exam_Quota" class="form-control" id="fathername"
                                                type="text" placeholder="Qualified_Exam_Quota" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Qualified Exam Marks:</label>
                                            <input name="Qualified_Exam_Marks" class="form-control" id="fathername"
                                                type="text" placeholder="Qualified Exam Marks" />
                                        </div>
                                        <h3>Other Information:</h3>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Backlog Count:</label>
                                            <input name="Backlog_Count" class="form-control" id="fathername" type="text"
                                                placeholder="Backlog_Count" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Backlog History:</label>
                                            <input name="Backlog_History" class="form-control" id="fathername"
                                                type="text" placeholder="Backlog_History" />
                                        </div>
                                        <h3>Eduction Qualification:</h3>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="enquiryno">Passed Exam</label>
                                            <select id="admission-type" class="form-control" name="Passed_Exam"
                                                autocomplete="" required>
                                                <option disabled selected>Please select</option>
                                                <option value="">A</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Year:</label>
                                            <input name="year" class="form-control" id="fathername" type="text"
                                                placeholder="year" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="enquiryno">Quota</label>
                                            <select id="admission-type" class="form-control" name="Passed_Exam"
                                                autocomplete="" required>
                                                <option disabled selected>Please select</option>
                                                <option value="">A</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Board/Univ.:</label>
                                            <input name="Board/Univ." class="form-control" id="fathername" type="text"
                                                placeholder="Board/Univ." />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Roll NO.:</label>
                                            <input name="Roll_NO." class="form-control" id="fathername" type="text"
                                                placeholder="Roll NO." />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Max.Marks:</label>
                                            <input name="Max.Marks" class="form-control" id="fathername" type="text"
                                                placeholder="Max.Marks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Agt.Obt.:</label>
                                            <input name="Agt.Obt." class="form-control" id="fathername" type="text"
                                                placeholder="Agt.Obt." />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Aggrgate%:</label>
                                            <input name="Aggrgate%" class="form-control" id="fathername" type="text"
                                                placeholder="Aggrgate%" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Rank:</label>
                                            <input name="Rank" class="form-control" id="fathername" type="text"
                                                placeholder="Rank" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Remarks:</label>
                                            <input name="Remarks." class="form-control" id="fathername" type="text"
                                                placeholder="Remarks" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="fathername">Institute:</label>
                                            <input name="Institute." class="form-control" id="fathername" type="text"
                                                placeholder="Institute" />
                                        </div>
                            </div>
                        </div>
                    </div>
                    <div id="step-4">
                        <h3 class="border-bottom border-gray pb-2">
                        Document submission :-
                        </h3>
                        <div>
                            <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">Document Name:</label>
                                        <select id="admission-type" class="form-control" name="Document_Name"
                                            autocomplete="" required>
                                            <option disabled selected>Please select</option>
                                            <option value="">A</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">Submitted on:</label>
                                        <input name="Submitted_on" class="form-control" id="fathername" type="text"
                                            placeholder="Submitted on" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <label for="fathername">Returned to Student:</label>
                                        <input type="checkbox" id="vehicle1" name="vehicle1" value="Bike">
                                        <label for="vehicle1">Yes</label><br>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">Returned on:</label>
                                        <input name="Returned_on" class="form-control" id="fathername" type="text"
                                            placeholder="Returned on" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">upload file:</label>
                                        <input name="upload_file" class="form-control" id="fathername" type="file" />
                                    </div>
                                    <h3>Various Details :</h3>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">House Name:</label>
                                        <select id="admission-type" class="form-control" name="House_Name"
                                            autocomplete="" required>
                                            <option disabled selected>Please select</option>
                                            <option value="">A</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">Aadhar NO.:</label>
                                        <input name="Aadhar_NO." class="form-control" id="fathername" type="text"
                                            placeholder="Aadhar NO." />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">Samagra Id:</label>
                                        <input name="Samagra_Id" class="form-control" id="fathername" type="text"
                                            placeholder="Samagra Id" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">Candidate Id:</label>
                                        <input name="Candidate_Id" class="form-control" id="fathername" type="text"
                                            placeholder="Candidate Id" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">On Hold date:</label>
                                        <input name="On_Hold_date" class="form-control" id="fathername" type="date" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">Annual Income:</label>
                                        <input name="Annual_Income" class="form-control" id="fathername" type="text"
                                            placeholder="Annual Income" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <input type="checkbox" id="vehicle1" name="cheques" value="Bike">
                                        <label for="vehicle1">Don't Accept fees by cheques For the student </label><br>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <input type="checkbox" id="vehicle1" name="Scholarship" value="Bike">
                                        <label for="vehicle1">Scholarship Student</label><br>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">Scholarship Type:</label><br>
                                        <input type="checkbox" id="vehicle1" name="Scholarship_Type" value="Bike">
                                        <label for="vehicle1">Loan Student</label><br>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <input type="checkbox" id="vehicle1" name="Parents_is_Employee" value="Bike">
                                        <label for="vehicle1">Parents is Employee and Adjust Fees by salary
                                            A/C</label><br>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <input type="checkbox" id="vehicle1" name="Student_is_on_hold" value="Bike">
                                        <label for="vehicle1">Student is on hold</label><br>
                                    </div>
                                    
                                    
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <input type="checkbox" id="vehicle1" name="BPL" value="Bike">
                                        <label for="vehicle1">Student is BPL</label><br>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <input type="checkbox" id="vehicle1" name="RTE" value="Bike">
                                        <label for="vehicle1">Student is RTE</label><br>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <input type="checkbox" id="vehicle1" name="FeesC" value="Bike">
                                        <label for="vehicle1">Don't Accept Fees by Cash For the Student </label><br>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <input type="checkbox" id="vehicle1" name="Minoriaty" value="Bike">
                                        <label for="vehicle1">is Minoriaty Child</label><br>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <input type="checkbox" id="vehicle1" name="Only_Child" value="Bike">
                                        <label for="vehicle1">is Only Child</label><br>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <input type="checkbox" id="vehicle1" name="physically" value="Bike">
                                        <label for="vehicle1">Are you physically challanged</label><br>
                                        <textarea id="physicallyA" name="physicallyA" rows="3" cols="50">
                                                    </textarea>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <input type="checkbox" id="vehicle1" name="Chronic" value="Bike">
                                        <label for="vehicle1">Do you suffer form any Chronic Ailment</label><br>
                                        <textarea id="physicallyA" name="Chronic" rows="3" cols="50">
                                                    </textarea>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <input type="checkbox" id="vehicle1" name="Probation" value="Bike">
                                        <label for="vehicle1">Have you Ever Been Suspended,Dismieed or put On Acadmic
                                            Probation At Any school or college?</label><br>
                                        <textarea id="physicallyA" name="Probation" rows="3" cols="50">
                                                    </textarea>
                                    </div>
                                    <h3> Discontinue Details:</h3>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">Reqest on:</label>
                                        <input name="Reqest_on" class="form-control" id="fathername" type="text"
                                            placeholder="Reqest_on" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">Leaving Reason:</label>
                                        <select id="admission-type" class="form-control" name="Leaving_Reason"
                                            autocomplete="" required>
                                            <option disabled selected>Please select</option>
                                            <option value="">A</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">School Last date:</label>
                                        <input name="School_Last_date" class="form-control" id="fathername" type="date"
                                            placeholder="School Last date" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">TC no.:</label>
                                        <input name="TC_no." class="form-control" id="fathername" type="text"
                                            placeholder="TC no." />
                                    </div>
                                    
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="fathername">Leaving Date:</label>
                                        <input name="Leaving_Date" class="form-control" id="fathername" type="date"
                                            placeholder="Leaving Date" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <label for="fathername">Request A/C Section:</label>
                                        <input type="checkbox" id="vehicle1" name="Request" value="Bike">
                                        <label for="vehicle1">Yes Request / Requested</label><br>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <label for="fathername">Student Discontinued:</label>
                                        <input type="checkbox" id="vehicle1" name="Discontinued" value="Bike">
                                        <label for="vehicle1">Discontinued</label><br>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <br>
                                        <label for="fathername">TC issued :</label>
                                        <input type="checkbox" id="vehicle1" name="TC" value="Bike">
                                        <label for="vehicle1">Issued</label><br>
                                    </div>
                            </div>
                        </div>
                    </div>
                    <div id="step-5">
                        <h3 class="border-bottom border-gray pb-2">
                        Other Remarks :-
                        </h3>
                        <div>
                            <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <textarea id="Other_Remarks" name="Other_Remarks" rows="3" cols="75" placeholder="Other Remarks" ></textarea>
                                </div>
                                <h3>Photographs:</h3>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="fathername">Student Photo:</label><br>
                                    <input name="Student_Photo" class="form-control" id="fathername" type="file"
                                        placeholder="Student Photo" />
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="fathername">Father Photo:</label><br>
                                    <input name="Father_Photo" class="form-control" id="fathername" type="file"
                                        placeholder="Father Photo" />
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="fathername">Mother's Photo:</label><br>
                                    <input name="Mother's_Photo" class="form-control" id="fathername" type="file"
                                        placeholder="Mother's Photo" />
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="fathername">Gauridan's Photo:</label><br>
                                    <input name="Gauridan's_Photo" class="form-control" id="fathername" type="file"
                                        placeholder="Gauridan's Photo" />
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