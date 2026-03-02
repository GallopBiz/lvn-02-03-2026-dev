<!-- include script -->

<script src="{{url('assets/frontend/js/js_external.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{url('assets/frontend/css/lite-purple.min.css')}}">

<div class="mx-auto container">
   <!-- Progress Form -->
   <div class="main-content">
          <div class="breadcrumb">
            <ul>

            </ul>
          </div>
          <div class="separator-breadcrumb border-top"></div>
       <div class="row">
            <div class="col-md-12">
              <div class="card mb-4">
                <div class="card-body">
                  <div class="card-title mb-3">Student Registration Form</div>
                  <form action="create" method="post" >
                  <input type = "hidden" name = "_token" value = "<?php echo csrf_token(); ?>"><input type = "hidden" name = "_token" value = "<?php echo csrf_token(); ?>">
                    <div class="row">
                        <div class="col-md-3 form-group mb-3">
                            <label for="application_For">Application For</label>
                            <select id="application-for" class="form-control" name="application_for" autocomplete="shipping address-level1" required>
                                <option value="" disabled selected>Please select</option>
                                @foreach(config('global.application_for') as $each)
                                <option value="cbse">{{$each}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="lastName1">Student Name</label>
                            <input id="student-name" class="form-control" type="text" name="student_name" autocomplete="family-name" required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="last-name">
                            Gender
                            <span data-required="true" aria-hidden="true"></span>
                            </label>
                            <select id="gender" class="form-control" name="gender" autocomplete="shipping address-level1" required>
                                <option disabled selected>Please select</option>
                                @foreach(config('global.gender') as $each)
                                <option value="{{$each}}">{{$each}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 form-group mb-3">
                            <label for="nationality">
                            Date Of Birth
                            <span data-required="true" aria-hidden="true"></span>
                            </label>
                            <input id="dob" type="text" class="form-control" name="dob" autocomplete="family-name" >
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="last-name">
                            Cast
                            <span data-required="true" aria-hidden="true"></span>
                            </label>
                            <select id="cast" name="cast" class="form-control" autocomplete="shipping address-level1" required>
                                <option value="" disabled selected>Please select</option>
                                @foreach(config('global.cast') as $each)
                                <option value="{{$each}}">{{$each}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="nationality">
                            Religion
                            <span data-required="true" aria-hidden="true"></span>
                            </label>
                            <select id="religion" class="form-control" name="religion" autocomplete="shipping address-level1" required>
                                <option value="" disabled selected>Please select</option>
                                    @foreach(config('global.religion') as $each)
                                    <option value="{{$each}}">{{$each}}</option>
                                    @endforeach      
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="last-name">
                            Category
                            <span data-required="true" aria-hidden="true"></span>
                            </label>
                            <select id="category" class="form-control" name="category" autocomplete="shipping address-level1" required>
                                <option value="" disabled selected>Please select</option>
                                <option value="c1">C1</option>
                                <option value="c2">C2</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="nationality">
                            Class Name
                            <span data-required="true" aria-hidden="true"></span>
                            </label>
                            <select id="class-name" class="form-control" name="class_name" autocomplete="shipping address-level1" required>
                                <option value="" disabled selected>Please select</option>
                                    @foreach(config('global.class_name') as $each)
                                    <option value="{{$each}}">{{$each}}</option>
                                    @endforeach        
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="last-name">
                            Session Name
                            <span data-required="true" aria-hidden="true"></span>
                            </label>
                            <select id="session-name" class="form-control" name="session_name" autocomplete="shipping address-level1" required>
                                <option value="" disabled selected>Please select</option>
                                    @foreach(config('global.session_name') as $each)
                                    <option value="{{$each}}">{{$each}}</option>
                                    @endforeach  
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="mobile-number">
                            Mobile Number
                            </label>
                            <input id="mobile-number" class="form-control" type="tel" name="mobile_number" autocomplete="tel" inputmode="tel" required>
                        </div>

                    <div class="card-title mb-3">Father's Details</div>
                    <div class="row">
                        <div class="col-md-3 form-group mb-3">
                            <label for="email">
                                Email id</label>
                                <input id="email-address" type="text" class="form-control" name="father_email"  autocomplete=""  required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="fathermobile">
                            Mobile No.
                            </label>
                            <input id="mother-mobile" type="tel" class="form-control" autocomplete="tel" inputmode="tel" name="father_mobile" >
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="father-address">
                            Residental Address</label>
                            <input id="email-address" type="text" class="form-control" name="father_resi"  autocomplete=""  required>
                        </div>
                    </div>
                    <div class="card-title mb-3">Mother's Details</div>
                    <div class="row">
                        <div class="col-md-3 form-group mb-3">
                            <label for="email">
                                Email id</label>
                                <input id="email-address" type="text" class="form-control" name="mother_resi"  autocomplete=""  required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="fathermobile">
                            Mobile No.
                            </label>
                            <input id="mother-mobile" type="tel" class="form-control" autocomplete="tel" inputmode="tel" name="mother_mobile" >
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="father-address">
                            Residental Address</label>
                            <input id="email-address" type="text" class="form-control" name="mother_resi"  autocomplete=""  required>
                        </div>
                    </div>

                      <div class="col-md-12">
                        <button class="btn btn-primary">Submit</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
        </div>     
    </div>
   <!-- </form> -->
   <!-- / End Progress Form -->
</div>