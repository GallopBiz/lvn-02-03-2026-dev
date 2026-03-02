@extends('layouts.app')
@section('main-container')

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
<!-- {!! Form::model($user, ['method' => 'PATCH','route' => ['users.update', $user->id]]) !!} -->
<div class="main-content">
    <div class="breadcrumb">
        <h1>Edit New User | </h1>
        <ul>
            <li>
                <a class="btn btn-primary text-white" href="{{ route('users.index') }}">Go Back</a>
            </li>
        </ul>
    </div>
    <div class="separator-breadcrumb border-top"></div>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="card-title mb-3">Form Inputs</div>


                    {!! Form::model($user, ['id'=>'MyForm','method' => 'PATCH','route' => ['users.update', $user->id]]) !!}
                    <div class="row">

                        <div id="smartwizard">

                            <ul>
                                <li class="" id="step1"><a href="#step-1">Step 1<br /><small>Student Details</small></a></li>
                                <li id="step2">
                                    <a href="#step-2">Step 2<br /><small>Enquiry For</small></a>
                                </li>
                                <li>
                                    <a href="#step-3">Step 3<br /><small>Details of Siblings</small></a>
                                </li>
                                <li>
                                    <a href="#step-4">Step 3<br /><small>Details of step 4</small></a>
                                </li>
                                <li>
                                    <a href="#step-5">Step 5<br /><small>Details of step 5</small></a>
                                </li>
                                {{-- <li>
                              <a href="#step-6">Step 6<br /><small>Details of Siblings</small></a>
                            </li> --}}

                            </ul>
                            <div>
                                <div id="step-1">



                                    <div class="col-md-6 form-group mb-3">
                                        <label for="firstName1">Name</label>
                                        @role('Admin')
                                        {!! Form::text('student_name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                                        @endrole
                                        @role('Student')
                                        {!! Form::text('student_name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                                        @endrole
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="exampleInputEmail1">Application Number</label>
                                        @role('Admin')
                                        {!! Form::text('form_number', null, array('placeholder' => 'Form Number','class' => 'form-control')) !!}
                                        @endrole
                                        @role('Student')
                                        {!! Form::text('form_number', null, array('placeholder' => 'Form Number','class' => 'form-control')) !!}
                                        @endrole
                                        <!--  <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="phone">Password</label>
                                        {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Confirm Password</label>
                                        {!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' => 'form-control')) !!}
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <!-- <div class="form-group"> -->
                                        <strong>Role:</strong>
                                        @role('Admin')
                                        {!! Form::select('roles[]', $roles,$userRole, array('class' => 'form-control','multiple')) !!}
                                        <!-- </div> -->
                                        @endrole
                                        <style>
                                            .hidden-select {
                                                display: none;
                                            }

                                        </style>
                                        @role('Student')
                                        {!! Form::select('roles[]', $roles,$userRole, array('class' => 'form-control hidden-select')) !!}
                                        @endrole
                                    </div>

                                    <div class="col-md-12 form-group mb-3">
                                        <strong>General Information:</strong>
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Branch Name</label>
                                        {!! Form::select('branch_name', [
                                        'option1' => 'Option 1',
                                        'option2' => 'Option 2',
                                        'option3' => 'Option 3',
                                        ], null, ['class' => 'branch_name', 'placeholder' => 'Select Branch']) !!}
                                    </div>


                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Employee First Name</label>
                                        {!! Form::password('employee_first_name', null, array('placeholder' => 'Employee First Name','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Employee Model Name</label>
                                        {!! Form::password('employee_Model_name', array('placeholder' => 'Employee Model Name','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Employee Last Name</label>
                                        {!! Form::password('employee-last-name', array('placeholder' => 'Employee Last Name','class' => 'form-control')) !!}
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Employee Name</label>
                                        {!! Form::password('employee-name', array('placeholder' => 'Employee Name','class' => 'form-control')) !!}
                                    </div>

                                </div>

                                <div id="step-2">
                                    {{-- add dropdown-toggle box prefix --}}
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Father Name</label>
                                        {!! Form::password('father-name', array('placeholder' => 'Father Name','class' => 'form-control')) !!}
                                    </div>

                                    {{-- add dropdown-toggle box prefix --}}
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Mother Name</label>
                                        {!! Form::password('mother-name', array('placeholder' => 'Mother Name','class' => 'form-control')) !!}
                                    </div>
                                </div>



                                <div id="step-3">
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Gender</label>
                                        {!! Form::select('gender', [
                                        'option1' => 'Option 1',
                                        'option2' => 'Option 2',
                                        'option3' => 'Option 3',
                                        ], null, ['class' => 'form-control', 'placeholder' => 'Select Branch']) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">DOB</label>
                                        {!! Form::password('dob', array('placeholder' => 'DOB','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Blood Group</label>
                                        {!! Form::select('bloodgroup', [
                                        'option1' => 'Option 1',
                                        'option2' => 'Option 2',
                                        'option3' => 'Option 3',
                                        ], null, ['class' => 'form-control', 'placeholder' => 'Select Blood Group']) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Official Mail</label>
                                        {!! Form::password('official-mail', array('placeholder' => 'Official Mail','class' => 'form-control')) !!}
                                    </div>


                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Mobile No.</label>
                                        {!! Form::password('mobile-no', array('placeholder' => 'Mobile No.','class' => 'form-control')) !!}
                                    </div>


                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Official Phone No.</label>
                                        {!! Form::password('official-phone-no', array('placeholder' => 'Official Phone No.','class' => 'form-control')) !!}
                                    </div>


                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Personal/Other Contact No.</label>
                                        {!! Form::password('personal-contact-no', array('placeholder' => 'Personal/Other Contact No.','class' => 'form-control')) !!}
                                    </div>


                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">personal Email</label>
                                        {!! Form::password('personal-email', array('placeholder' => 'personal Email','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">personal Email</label>
                                        {!! Form::password('personal-email', array('placeholder' => 'personal Email','class' => 'form-control')) !!}
                                    </div>

                                </div>
                                <div id="step-4">
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">personal Email</label>
                                        {!! Form::password('personal-email', array('placeholder' => 'personal Email','class' => 'form-control')) !!}
                                    </div>
                                    {{-- <textarea name="" id="" cols="30" rows="10"></textarea> --}}
                                    <div class="col-md-6 form-group mb-3">
                                        <strong>Address:</strong>
                                        {!! Form::select('roles[]', $roles, [], array('class' => 'form-control','multiple')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">permanent Address</label>
                                        {!! Form::password('permanent-address', array('placeholder' => 'permanent Address','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">City</label>
                                        {!! Form::password('city', array('placeholder' => 'City','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Taluda</label>
                                        {!! Form::password('taluda', array('placeholder' => 'Taluda','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">District</label>
                                        {!! Form::password('district', array('placeholder' => 'District','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Pin Code</label>
                                        {!! Form::password('pin-code', array('placeholder' => 'Pin Code','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">At Post</label>
                                        {!! Form::password('at-post', array('placeholder' => 'At Post','class' => 'form-control')) !!}
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Employee Code</label>
                                        {!! Form::password('employee-code', array('placeholder' => 'Employee Code','class' => 'form-control')) !!}
                                    </div>
                                </div>
                                <div id="step-5">
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Marital Status</label>
                                        {!! Form::select('marital-status', [
                                        'option1' => 'Option 1',
                                        'option2' => 'Option 2',
                                        'option3' => 'Option 3',
                                        ], null, ['class' => 'form-control', 'placeholder' => 'Select Marital Status']) !!}
                                    </div>


                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">DOM</label>
                                        {!! Form::password('dom', array('placeholder' => 'DOM','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Spouse Name</label>
                                        {!! Form::password('spouse-name', array('placeholder' => 'Spouse Name','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Spouse Contact No.</label>
                                        {!! Form::password('spouse-contact-no', array('placeholder' => 'Spouse Contact No.','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Spouse Occupation.</label>
                                        {!! Form::password('spouse-occpation', array('placeholder' => 'Spouse Occupation.','class' => 'form-control')) !!}
                                    </div>


                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">No Of Children</label>
                                        {!! Form::password('no-of-children', array('placeholder' => 'No Of Children','class' => 'form-control')) !!}
                                    </div>



                                    {{-- add checkbox --}}
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Testimonials Submitted</label>
                                        {!! Form::password('testimonials-submitted', array('placeholder' => 'Testimonials Submitted','class' => 'form-control')) !!}
                                    </div>

                                    {{-- add checkbox --}}
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Address Proof</label>
                                        {!! Form::password('address-proof', array('placeholder' => 'Address Proof','class' => 'form-control')) !!}
                                    </div>

                                    {{-- add checkbox --}}
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Identity Proof</label>
                                        {!! Form::password('indentity-proof', array('placeholder' => 'Identity Submitted','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Graduation Stream</label>
                                        {!! Form::password('graduation-stream', array('placeholder' => 'Graduation Stream','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Graduation Subject</label>
                                        {!! Form::password('graduation-subject', array('placeholder' => 'Graduation Subject','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Post Graduation Stream</label>
                                        {!! Form::password('post-graduation', array('placeholder' => 'Post Graduation Stream','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1"> Post Graduation Subject</label>
                                        {!! Form::password('post-graduation-subject', array('placeholder' => 'Post Graduation Subject','class' => 'form-control')) !!}
                                    </div>
                                    {{-- file --}}
                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1">Employee Photo</label>
                                        {!! Form::password('employee-photo', array('placeholder' => 'Employee Photo','class' => 'form-control')) !!}
                                    </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label for="credit1"> Security Deposit Cheque No.</label>
                                        {!! Form::password('security-deposite-no', array('placeholder' => 'Security Deposit Cheque No.','class' => 'form-control')) !!}
                                    </div>


                                    <div class="col-md-12">
                                        <input hidden type="text" name="jsoninput" id="jsoninput">
                                        <button onclick="handleFormSubmit(event)" type="button" class="btn btn-primary">Submit</button>
                                        <button id="submitBtn" type="submit" hidden class="btn btn-primary">Submit</button>
                                    </div>


                                    {{--
                      <div class="col-md-12">
                        <button class="btn btn-primary">Submit</button>
                      </div> --}}
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>





{!! Form::close() !!}


<script>
    $(document).ready(function() {
        $('#smartwizard').smartWizard({
            // Properties
            selected: 0, // Selected Step, 0 = first step   
            keyNavigation: false, // Enable/Disable key navigation(left and right keys are used if enabled)
            enableAllSteps: false, // Enable/Disable all steps on first load

            buttonOrder: ['finish', 'next', 'prev'] // button order, to hide a button remove it from the list
        });


    });

    function handleFormSubmit(e) {
        e.preventDefault();
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        const jsonData = JSON.parse(JSON.stringify(jQuery('#MyForm').serializeArray()));

        console.log("form data is:", jsonData);

        // $("#jsoninput").val(jsonData);
        $("#jsoninput").val(JSON.stringify(jsonData));

        $("#submitBtn").click();
    }

</script>

@endsection
