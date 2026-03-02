<!-- include script -->
<head>
   <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<script src="{{url('assets/frontend/js/js_external.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{url('assets/frontend/css/css_external.css')}}">
<div class="mx-auto container">
   <!-- Progress Form -->
   <form id="progress-form" class="p-4 progress-form" action="{{url('save-student-inquiry')}}"  novalidate method="post">
      {{ csrf_field() }}
      <!-- Step Navigation -->
      <h4>Student Registration Form</h4>
      <div class="d-flex align-items-start mb-3 sm:mb-5 progress-form__tabs" role="tablist">
         <button id="progress-form__tab-1" class="flex-1 px-0 pt-2 progress-form__tabs-item" type="button" role="tab" aria-controls="progress-form__panel-1" aria-selected="true">
            <span class="d-block step" aria-hidden="true">Page 1 <span class="sm:d-none">of 3</span></span>
            <!-- Page 1 -->
         </button>
         <button id="progress-form__tab-2" class="flex-1 px-0 pt-2 progress-form__tabs-item" type="button" role="tab" aria-controls="progress-form__panel-2" aria-selected="false" tabindex="-1" aria-disabled="true">
            <span class="d-block step" aria-hidden="true">Page 2 <span class="sm:d-none">of 3</span></span>
            <!-- Page 2 -->
         </button>
         <!-- <button id="progress-form__tab-3" class="flex-1 px-0 pt-2 progress-form__tabs-item" type="button" role="tab" aria-controls="progress-form__panel-3" aria-selected="false" tabindex="-1" aria-disabled="true">
            <span class="d-block step" aria-hidden="true">Page 3 <span class="sm:d-none">of 3</span></span>
           
         </button> -->
      </div>
      <!-- / End Step Navigation -->
      <!-- Step 1 -->
      <section id="progress-form__panel-1" role="tabpanel" aria-labelledby="progress-form__tab-1" tabindex="0">
         <div class="sm:d-grid sm:grid-col-3 sm:mt-3">
            <div class="mt-3 sm:mt-0 form__field">
               <label for="first-name">
               Application For
               <span data-required="true" aria-hidden="true"></span>
               </label>
               <!-- <input id="application-for" type="text" name="application-for" autocomplete="given-name" required> -->
               <span data-required="true" aria-hidden="true"></span>
               </label>
               <select id="application-for" name="application_for" autocomplete="shipping address-level1" required>
                  <option value="" disabled selected>Please select</option>
                  @foreach(config('global.application_for') as $each)
                  <option value="cbse">{{$each}}</option>
                  @endforeach
               </select>
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="last-name">
               Student Name
               <span data-required="true" aria-hidden="true"></span>
               </label>
               <input id="student-name" type="text" name="student_name" autocomplete="family-name" required>
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="nationality">
                  Nationality
               </label>
               <input id="nationality" type="text" name="nationality" autocomplete="" >    
            </div>
            <div class="mt-3  form__field">
               <label for="last-name">
               Gender
               <span data-required="true" aria-hidden="true"></span>
               </label>
               <select id="gender" name="gender" autocomplete="shipping address-level1" required>
				<option disabled selected>Please select</option>
				@foreach(config('global.gender') as $each)
				<option value="{{$each}}">{{$each}}</option>
				@endforeach
               </select>
            </div>
            <div class="mt-3  form__field">
               <label for="nationality">
               Date Of Birth
               <span data-required="true" aria-hidden="true"></span>
               </label>
               <input id="dob" type="text" name="dob" autocomplete="family-name" >
            </div>
            <div class="mt-3  form__field">
               <label for="last-name">
               Cast
               <span data-required="true" aria-hidden="true"></span>
               </label>
               <select id="cast" name="cast" autocomplete="shipping address-level1" required>
                  <option value="" disabled selected>Please select</option>
                   @foreach(config('global.caste') as $each)
                  <option value="{{$each}}">{{$each}}</option>
                  @endforeach
               </select>
            </div>
            <div class="mt-3  form__field">
               <label for="nationality">
               Religion
               <span data-required="true" aria-hidden="true"></span>
               </label>
               <select id="religion" name="religion" autocomplete="shipping address-level1" required>
                  <option value="" disabled selected>Please select</option>
					@foreach(config('global.religion') as $each)
					<option value="{{$each}}">{{$each}}</option>
					@endforeach      
               </select>
            </div>
            <div class="mt-3 form__field">
               <label for="last-name">
               Category
               <span data-required="true" aria-hidden="true"></span>
               </label>
               <select id="category" name="category" autocomplete="shipping address-level1" required>
                  <option value="" disabled selected>Please select</option>
                  <option value="c1">C1</option>
                  <option value="c2">C2</option>
               </select>
            </div>
            <div class="mt-3  form__field">
               <label for="nationality">
               Class Name
               <span data-required="true" aria-hidden="true"></span>
               </label>
               <select id="class-name" name="class_name" autocomplete="shipping address-level1" required>
                  <option value="" disabled selected>Please select</option>
					@foreach(config('global.class_name') as $each)
					<option value="{{$each}}">{{$each}}</option>
					@endforeach        
               </select>
            </div>
            <div class="mt-3  form__field">
               <label for="last-name">
               Session Name
               <span data-required="true" aria-hidden="true"></span>
               </label>
               <select id="session-name" name="session_name" autocomplete="shipping address-level1" required>
                  <option value="" disabled selected>Please select</option>
					@foreach(config('global.session_name') as $each)
					<option value="{{$each}}">{{$each}}</option>
					@endforeach  
               </select>
            </div>
            <div class="mt-3 form__field">
               <label for="address">
               Present Address
               </label>
               <input id="present-address" type="text" name="present_address" autocomplete="" >
            </div>
            <div class="mt-3 form__field">
               <label for="address-2">
               Permanent Address
               </label>
               <input id="permanent-address" type="text" name="permanent_address" autocomplete="">
            </div>
            <div class="mt-3 form__field">
               <label for="phone-number">
               Phone Number
               </label>
               <input id="phone_number" type="tel" name="phone_number" autocomplete="tel" inputmode="tel" >
            </div>
            <div class="mt-3 form__field">
               <label for="mobile-number">
               Mobile Number
               </label>
               <input id="mobile-number" type="tel" name="mobile_number" autocomplete="tel" inputmode="tel" required>
            </div>
            <div class="mt-3 form__field">
               <label for="mother">
               Mother Tongue
               </label>
               <input id="mother-tongue" type="text" name="mother_tongue" autocomplete="" >
            </div>
            <div class="mt-3 form__field">
               <label for="remarks">
               Remarks
               </label>
               <input id="remarks" type="text" name="remarks" autocomplete="" >
            </div>
            <div class="mt-3 form__field">
               <label for="vaccaination">
               vaccaination
               </label>
               <input id="vaccaination" type="text" name="vaccaination" autocomplete="" >
            </div>
            <div class="mt-3 form__field">
               <label for="medical">
               Medical Conserns (any)
               </label>
               <input id="medical" type="text" name="medical" autocomplete="" >
            </div>
         </div>
         <div class="mt-3 form__field">
            <label for="email-address">
            Name of Present Play School / Day Care (if any) 
            </label>
            <input id="playschool" type="text" name="playschool" autocomplete="" inputmode="" >
         </div>
         <div class="mt-1 form__field">
            <label class="form__choice-wrapper">
            <input id="medical" type="checkbox" name="medical" value="Yes" checked>
            <span>If a Sibling (real Brother / Sister ) also applying for addmission into the school. Please give Details.</span>
            </label>
         </div>
         <div class="mt-1 form__field">
            <label class="form__choice-wrapper">
            <input id="transport" type="checkbox" name="transport" value="Yes" >
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
            <input id="address-proff" type="checkbox" name="address_proff" value="Yes" >
            <span>Address Proff</span>
            </label>
         </div>
         <div class="mt-1 form__field">
            <label class="form__choice-wrapper">
            <input id="lasr-repor-card" type="checkbox" name="last_report_card" value="Yes" >
            <span>Last Report Card</span>
            </label>
         </div>
         <!-- <div class="mt-3 form__field">
            <label for="email-address">
              Email address
              <span data-required="true" aria-hidden="true"></span>
            </label>
            <input id="email-address" type="email" name="email-address" autocomplete="email" inputmode="email" required>
            </div> -->
         <!-- <div class="mt-3 form__field">
            <label for="phone-number">
              Phone number (optional)
            </label>
            <input id="phone-number" type="tel" name="phone-number" autocomplete="tel" inputmode="tel">
            </div> -->
         <div class="d-flex align-items-center justify-center sm:justify-end mt-4 sm:mt-5">
            <button type="button" data-action="next">
            Continue
            </button>
         </div>
      </section>
      <!-- / End Step 1 -->
      <!-- Step 2 -->
      <section id="progress-form__panel-2" role="tabpanel" aria-labelledby="progress-form__tab-2" tabindex="0" hidden>
         <h4>Father's Details</h4>
         <div class="sm:d-grid sm:grid-col-3 sm:mt-3">
            <div class="mt-3 sm:mt-0 form__field">
               <label for="address-city">
               Father Name
               </label>
               <input id="father-name" type="text" name="father_name" autocomplete="" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="bod">
               Date of Birth
               </label>
               <input id="father-dob" type="text" name="father_dob" autocomplete="" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="education">
               Education
               </label>
               <input id="father-education" type="text" name="father_education" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="occupation">
               Occupation
               </label>
               <input id="father-occupation" type="text" name="father_occupation" autocomplete="" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="orga">
                  Organization
                  <input id="father-organization" type="text" name="father_organization" autocomplete="" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
            <label for="designation">
            Designation
            </label>
            <input id="father-designation" type="text" name="father_designation" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="officetelephone">
               Office Telephone
               </label>
               <input id="father-telephone" type="text" name="father_telephone" autocomplete="" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="email">
                  Email id
                  <input id="email-address" type="email" name="father_email"  autocomplete="email" inputmode="email" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
            <label for="fathermobile">
            Mobile No.
            </label>
            <input id="father-mobile" type="tel"  autocomplete="tel" inputmode="tel" name="father_mobile" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="father-address">
               Residental Address</label>
               <input id="email-address" type="text" name="father_resi"  autocomplete=""  >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="emergancy">
               Emergency contact No.
               </label>
               <input id="father-emer-mobile" type="tel"  autocomplete="tel" inputmode="tel" name="father_emer_mobile" >
            </div>
         </div>
         <!-- change -->
         <h4>Mother's Details</h4>
         <div class="sm:d-grid sm:grid-col-3 sm:mt-3">
            <div class="mt-3 sm:mt-0 form__field">
               <label for="mother">
               Mother Name
               </label>
               <input id="mother-name" type="text" name="mother_name" autocomplete="" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="bod">
               Date of Birth
               </label>
               <input id="mother-dob" type="text" name="mother_dob" autocomplete="" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="education">
               Education
               </label>
               <input id="mother-education" type="text" name="mother_education" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="occupation">
               Occupation
               </label>
               <input id="mother-occupation" type="text" name="mother_occupation" autocomplete="" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="orga">
                  Organization
                  <input id="mother-organization" type="text" name="mother_organization" autocomplete="" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
            <label for="designation">
            Designation
            </label>
            <input id="mother-designation" type="text" name="mother_designation" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="officetelephone">
               Office Telephone
               </label>
               <input id="mother-telephone" type="text" name="mother_telephone" autocomplete="" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="email">
                  Email id
                  <input id="mother-address" type="email" name="mother_email"  autocomplete="email" inputmode="email" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
            <label for="fathermobile">
            Mobile No.
            </label>
            <input id="mother-mobile" type="tel"  autocomplete="tel" inputmode="tel" name="mother_mobile" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="father-address">
               Residental Address</label>
               <input id="mother-address" type="text" name="mother_resi"  autocomplete=""  >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="emergancy">
               Emergency contact No.
               </label>
               <input id="mother-emer-mobile" type="tel"  autocomplete="tel" inputmode="tel" name="mother_emer_mobile" >
            </div>
         </div>
         <!--  Change -->
         <!-- change -->
         <h4>Guardian Details</h4>
         <div class="sm:d-grid sm:grid-col-3 sm:mt-3">
            <div class="mt-3 sm:mt-0 form__field">
               <label for="mother">
               Guardian Name
               </label>
               <input id="guardian-name" type="text" name="guardian_name" autocomplete="" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="officetelephone">
               Office Telephone
               </label>
               <input id="guardian-telephone" type="text" name="guardian_telephone" autocomplete="" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="email">
                  Email id
                  <input id="guardian-address" type="email" name="guardian_email"  autocomplete="email" inputmode="email" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
            <label for="guardianmobile">
            Mobile No.
            </label>
            <input id="guardian-mobile" type="tel"  autocomplete="tel" inputmode="tel" name="guardian_mobile" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="guardian-address">
               permanent Address</label>
               <input id="guardian-address" type="text" name="guardian_resi"  autocomplete=""  >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="emergancy">
               Emergency contact No.
               </label>
               <input id="guardian-emer-mobile" type="tel"  autocomplete="tel" inputmode="tel" name="guardian_eme_mobile" >
            </div>
            <div class="mt-3 sm:mt-0 form__field">
               <label for="guardian-relation">
                  Guardian Relation
                  <input id="guardian-relation" type="text" name="guardian_relation"  autocomplete=""  required>
            </div>
         </div>
         <!--  Change -->
         <h4>Others Details</h4>
         <div class="sm:d-grid sm:grid-col-3 sm:mt-3">
         <div class="mt-3 sm:mt-0 form__field">
         <label for="parents">
         Parents Are
         </label>
         <select id="parents-are" name="parents_are" autocomplete="" >
         <option value="" disabled selected>Please select</option>
		@foreach(config('global.parents_are') as $each)
		<option value="cbse">{{$each}}</option>
		@endforeach 
         </select>  
         <label class="form__choice-wrapper">
         <input id="adopted" type="checkbox" name="is_child_adopted" value="Yes">
         <span>Child is an Adopted Child</span>
         </label>
         </div>        
         <div class="mt-3 sm:mt-0 form__field">
         <label for="">
         Child Live With
         </label>
         <select id="childlivewith" name="childlivewith" autocomplete="" >
         <option value="" disabled selected>Please select</option>
			@foreach(config('global.child_live_with') as $each)
			<option value="cbse">{{$each}}</option>
			@endforeach 
         </select>  
         </div>
         <div class="mt-3 sm:mt-0 form__field">
         <label for="guardian-local">
         Local Guardian</label>
         <input id="guardian-local" type="text" name="local_guardian"  autocomplete=""  >
         </div>
         <div class="mt-3 sm:mt-0 form__field">
         <label for="localguardian-address">
         Local Address</label>
         <input id="localguardian-address" type="text" name="localguardian_address"  autocomplete=""  >
         </div>
         <div class="mt-3 sm:mt-0 form__field">
         <label for="localemergancy">
         Phone No.
         </label>
         <input id="guardian-local-phone" type="tel"  autocomplete="tel" inputmode="tel" name="other_phone_number" >
         </div>
         <div class="mt-3 sm:mt-0 form__field">
         <label for="localguardianmobile">
         Mobile No.
         </label>
         <input id="localguardian-mobile" type="tel"  autocomplete="tel" inputmode="tel" name="other_mobile_number" >
         </div>
         <div class="mt-3 sm:mt-0 form__field">
         <label for="email">
         Email id</label>
         <input id="localguardian-address" type="email" name="other_email"  autocomplete="email" inputmode="email" >
         </div>
         <div class="mt-3 sm:mt-0 form__field">
         <label for="">
         Parents Category
         </label>
		<select id="parents-cat" name="other_parents_cat" autocomplete="" >
		<option value="" disabled selected>Please select</option>
		@foreach(config('global.parents_category') as $each)
		<option value="cbse">{{$each}}</option>
		@endforeach 
		</select>  
         </div>
         <div class="mt-3 sm:mt-0 form__field">
         <label for="cat">
         New Category</label>
         <input id="newcategory" type="text" name="newcategory"   inputmode="" >
         </div>
         <div class="mt-3 sm:mt-0 form__field">
         <label for="">
         House
         </label>
		<select id="house" name="house" autocomplete="" >
		<option disabled selected>Please select</option>
			@foreach(config('global.house') as $each)
			<option value="cbse">{{$each}}</option>
			@endforeach 
		</select>  
         </div>
         <div class="mt-3 sm:mt-0 form__field">
         <label for="house">
         New House</label>
         <input id="newhouse" type="text" name="newhouse"   inputmode="" >
         </div>
         <!--  Change -->
         </div>
         <div class="d-flex flex-column-reverse sm:flex-row align-items-center justify-center sm:justify-end mt-4 sm:mt-5">
            <button type="button" class="mt-1 sm:mt-0 button--simple" data-action="prev">
            Back
            </button>
            <button type="submit" data-action="" value="submit">
            Submit
            <!-- Continue -->
            </button>
         </div>
      </section>
       </form>
       
      <!-- / End Step 2 -->
      <!-- Step 3 -->
    <!--   <section id="progress-form__panel-3" role="tabpanel" aria-labelledby="progress-form__tab-3" tabindex="0" hidden>
         <div class="mt-3 form__field">
            <label for="product-satisfaction">
            How would you rate your level of satisfaction with the service you received?
            <span data-required="true" aria-hidden="true"></span>
            </label>
            <select id="product-satisfaction" name="product_satisfaction" required>
               <option value="" disabled selected>Please select</option>
               <option value="Highly satisfied">Highly satisfied</option>
               <option value="Very satisfied">Very satisfied</option>
               <option value="Satisfied">Satisfied</option>
               <option value="Very dissatisfied">Very dissatisfied</option>
               <option value="Highly dissatisfied">Highly dissatisfied</option>
            </select>
         </div>
         <div class="mt-3 form__field">
            <label for="product-recommendation">
            How likely are you to recommend our products to friends or family?
            <span data-required="true" aria-hidden="true"></span>
            </label>
            <select id="product-recommendation" name="product_recommendation" required>
               <option value="" disabled selected>Please select</option>
               <option value="Highly likely">Highly likely</option>
               <option value="Very likely">Very likely</option>
               <option value="Likely">Satisfied</option>
               <option value="Very unlikely">Very unlikely</option>
               <option value="Highly unlikely">Highly unlikely</option>
            </select>
         </div>
         <fieldset id="product-purchase" class="mt-3 form__field">
            <legend>
               Which of the following products have you purchased in the past 6 months? Please check all that apply.
            </legend>
            <label class="form__choice-wrapper">
            <input type="checkbox" name="product_purchase" value="A">
            <span>Product A</span>
            </label>
            <label class="form__choice-wrapper">
            <input type="checkbox" name="product_purchase" value="B">
            <span>Product B</span>
            </label>
            <label class="form__choice-wrapper">
            <input type="checkbox" name="product_purchase" value="C">
            <span>Product C</span>
            </label>
         </fieldset>
         <div class="mt-3 form__field">
            <label for="product-feedback">
            Do you have any additional feedback or comments about our products?
            </label>
            <textarea id="product-feedback" name="product_feedback" rows="5"></textarea>
         </div>
         <div class="d-flex flex-column-reverse sm:flex-row align-items-center justify-center sm:justify-end mt-4 sm:mt-5">
            <button type="button" class="mt-1 sm:mt-0 button--simple" data-action="prev">
            Back
            </button>
            <button type="submit">
            Submit
            </button>
         </div>
      </section> -->
      <!-- / End Step 3 -->
      <!-- Thank You -->
      <section id="progress-form__thank-you" hidden>
         <p>Thank you for your submission!</p>
         <p>We appreciate you contacting us. One of our team members will get back to you very&nbsp;soon.</p>
      </section>
      <!-- / End Thank You -->
   <!-- </form> -->
   <!-- / End Progress Form -->
</div>
