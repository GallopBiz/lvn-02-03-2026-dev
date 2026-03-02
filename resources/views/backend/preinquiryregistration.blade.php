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

.capitalize-text {
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
   <div class="breadcrumb">
      <h1 class="me-2">Pre Enquiry Entry</h1>
   </div>
   <div class="separator-breadcrumb border-top"></div>
   <div class="row">
      <div class="col-md-12">
         <!--  SmartWizard html -->
         <div id="">
            <div>
               <div id="step-1">
                 <div class="form_section1_div">
                     <form novalidate="novalidate" method="post" action="{{url('save_student_preinquiryentry')}}" class="rg_form" id="form-id">
                       <div class="row">
                        <div class="col-md-3 form-group mb-3">
                           <label for="firstName1">Enquiry Session:<b class="validation_err">*</b></label>
                           <input name="_token" type="hidden" value="{{ csrf_token() }}">
                            <input type="text" readonly id="session" class="form-control" value="" name="session">
                           
                           <!-- <select id="session" class="form-control" name="session" autocomplete="shipping address-level1">
                              <option value="" disabled selected>Please Select</option>
                              @foreach($databaseNames as $databaseName)
                                  <option value="{{$databaseName}}">{{$databaseName}}</option>
                              @endforeach
                           </select> -->
                           <span class="session_msg validation_err"></span>
                         </div>
                         <div class="col-md-1 form-group mb-3">
                           <label for="enquiryno">Enquiry No.</label>
                           <input
                             class="form-control"
                             id="enquiryno"
                             type="text" disabled
                             placeholder="Enter your Enquiry no." name="enquiryno" required value="{{$Userid}}"
                           /><input type="hidden" name="formno2" value="{{$Userid}}">
                           <span class="enquiryno_msg validation_err"></span>
                         </div>
                         <div class="col-md-2 form-group mb-3">
                           <label for="enquiryno">Enquiry For Next Year</label>
                           <br>
                           <input
                             id="next_year"
                             type="checkbox"
                             name="next_year" required value="1"
                           />
                           <span class="enquiryno_msg validation_err"></span>
                         </div>

                         <div class="col-md-3 form-group mb-3">
                           <label for="enquiryno">Form No.</label>
                           <input
                             class="form-control"
                             id="formno"
                             type="text" 
                             placeholder="Enter your formno no." name="formno" required value="{{$Userid}}"
                           /><span class="enquiryno_msg validation_err"></span>
                         </div>

                         <div class="col-md-3 form-group mb-3">
                           <label for="picker2">Enquiry Date</label><?php $mytime = Carbon\Carbon::now(); $mytime->toDateTimeString();?>
                           <input
                             class="form-control "
                             id="picker2"
                             placeholder="dd-mm-yyyy"
                             name="enquirydate" type="date" max="9999-12-31" value="<?php echo date('d-m-Y',strtotime($mytime

                              ));?>"
                           />
                         </div>
                          <div class="col-md-6 form-group mb-3">
                           <label for="enquiryno">Admission Type</label>
                           <select id="admission-type" class="form-control" name="admission_type" required>
                              <option value="" disabled selected>Please select</option>
                              @foreach(config('global.admission_type') as $each)
                              <option value="{{$each}}" <?php if($each == 'CBSE'){ echo "selected";}?>>{{$each}}</option>
                              @endforeach
                           </select>
                           <span class="addmission_msg validation_err"></span>
                         </div>

{{-- 
                         <div class="col-md-6 form-group mb-3">
                          <label for="lastName1">Class Name </label>
                          <?php //print_r($classname); ?>
                          <select id="classname" class="form-control" name="classname" autocomplete="" required>
                                    <option value="" disabled selected>Please select</option>
                                    @if(!empty($datas))
                                    <?php //print_r($datas); ?>
                                      @foreach($datas as $each)
                                        @if(!empty($classname) && $each->class_name == $classname)
                                        <option value="{{$each->class_name}}" selected>{{$each->class_name}}</option>
                                        @else
                                        <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                                        @endif
                                      @endforeach
                                    @endif
                                 </select>
                              </div> --}}

                          <div class="col-md-6 form-group mb-3">
                           <label for="firstName1">Class Name</label>
                           <select id="classname" class="form-control" name="classname" autocomplete="" required>
                              <option value="" disabled selected>Please select</option>
                              @foreach($classlist as $each)
                              <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                              @endforeach
                               {{-- @foreach(config('global.class_name') as $each)
                              <option value="{{$each}}">{{$each}}</option>
                              @endforeach  --}}
                           </select>
                           <span class="classname_msg validation_err"></span>
                         </div> 


                         <div class="col-lg-6 form-group mb-3">

                           <label for="studentname">Student Name<b class="validation_err">*</b></label>
                           <div class="input-group mb-3" >
                                  <div class="input-group-prepend">
                                   <select id="studentname_prefix" class="form-control btn btn-outline-primary dropdown-toggle " name="studentname_prefix" autocomplete="shipping address-level1" >
                                    <option value="Please Select">Please Select</option>
                                        <option value="Master">Master</option>
                                        <option value="Mr">Mr.</option>
                                        <option value="Miss">Miss</option>
                                     </select>
                                  </div>
                                  <input type="text"
                                     class="form-control uperletter"
                                     id="studentname"
                                     placeholder="Enter Student Name" name="studentname" required />
                                  </div>
                                  <span class="student_name_msg validation_err"></span>                          
                         </div> 
                         <div class="col-md-3 form-group mb-3">
                           <label for="picker2">Date Of Birth</label>
                           <!-- <input
                             class="form-control "
                             id="picker2"
                             placeholder="dd-mm-yyyy"
                             name="student_dob"  type="date" max="9999-12-31"
                           /> -->
                           <input class="form-control" id="picker2" placeholder="dd-mm-yyyy" name="date_of_birth" type="date">

                         </div>  
                         <div class="col-md-3 form-group mb-3">
                         <label for="firstName1">Gender</label>
                           <select id="gender" class="form-control" name="gender" autocomplete="shipping address-level1" >
                              <option value="Please select"  selected>Please select</option>
                              @foreach(config('global.gender') as $each)
                              <option value="{{$each}}">{{$each}}</option>
                              @endforeach
                           </select>
                         </div>
                         <div class="col-lg-6 form-group mb-3">
                         
                           <label for="fathername">Father Name</label>
                            <div class="input-group mb-3" >
                                  <div class="input-group-prepend">
                                    <select id="fathername_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="fathername_prefix" autocomplete="shipping address-level1" >
                                      <option value="Please Select">Please Select</option>
                                        <option value="Mr">Mr.</option>
                                        <option value="Dr">Dr.</option>
                                        <option value="Late">Late</option>
                                     </select>
                                  </div>
                           <input
                             class="form-control uperletter"
                             id="fathername"
                             type="text"
                             placeholder="Enter your father name" name ="fathername"
                           />
                         </div>
                       
                         </div> 
                         <div class="col-md-6 form-group mb-3">
                           <label for="phone">Father Mobile No.</label>
                           <input class="form-control"
                             id="fathermobile"  placeholder="" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check();"
                             placeholder="Enter father mobile no" name="fathermobile" return false;" 
                           /><span class="application_for_msg validation_err"  id ="validation_err"></span>
                         </div>    

                         <div class="col-lg-6 form-group mb-3">
                           <label for="mothername">Mother Name</label>
                           <div class="input-group mb-3" >
                                  <div class="input-group-prepend">
                           <select id="mothername_prefix" class="form-control btn btn-outline-primary dropdown-toggle" name="mothername_prefix" autocomplete="shipping address-level1" >
                            <option value="Please Select">Please Select</option>
                                        <option value="Mrs">Mrs.</option>
                                        <option value="Dr">Dr.</option>
                                        <option value="Late">Late</option>
                                     </select>
                                  </div>
                           <input
                             class="form-control uperletter"
                             id="mothername"
                             type="text"
                             placeholder="Enter your mother name" name="mothername"
                           />
                         </div>
                       </div>
                         <div class="col-md-6 form-group mb-3">
                           <label for="phone2">Mother Mobile No.</label>
                           <input
                             class="form-control"
                              id="mothermobile"  placeholder="" maxlength="10" pattern="\d{3}-\d{3}-\d{4}" onkeyup="this.value = this.value.replace(/[^0-9-]/g, '');check2();"
                              placeholder="Enter mother mobile no" name="mothermobile" return false;"
                           /><span class="application_for_msg validation_err" id="validation_err2"></span>
                         </div>
                         
                          {{-- <div class="col-md-3 form-group mb-3">
                        <label for="state">State</label>
                          <select id="state-dd" class="form-control uperletter "  name="state">
                            <option>Select</option>
                             @foreach ($states as $data)
                              <option value="{{$data->id}}">
                                  {{$data->name}}
                              </option>
                              @endforeach  
                          </select>
                      </div> --}}
                      <div class="col-md-3 form-group mb-3">
                        <label for="state">State</label>
                        <select id="state-dd" class="form-control uperletter" name="state">
                            <option>Select</option>
                            @foreach ($states as $data)
                                <option value="{{ $data->id }}">
                                    {{ $data->name }}
                                </option>
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
                           <input
                             class="form-control uperletter"
                             id="address"
                             type="text"
                             placeholder="Enter your address" name="address"
                           />
                         </div>
                         
                         <div class="col-md-3 form-group mb-3">
                           <label for="pincode">Pin</label>
                           <input
                             class="form-control"
                             id="pincode"
                             type="text"
                             placeholder="Enter your pin" name="pincode" maxlength="6"
                           />
                         </div>
                                                   
                       </div>
                       <div class="col-md-12">
                           <button class="btn btn-primary submit_btn" type="submit" onclick="submitForm(event)">Submit</button> 
                          <!-- <input type="submit" name="submit" value="Submit" class="btn btn-primary submit_btn"> -->
                        </div>
               
                  </div>
               </div>

                               
                          </form> 
                      </div>
                    </div>
                   </div>
                  </div>
              </div>
            <!-- </div>
            </div> -->
    <!-- end of main-content -->
<!-- </div> -->
<script src="{{url('assets/backend')}}/js/plugins/jquery-3.3.1.min.js"></script>
<script type="text/javascript">
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

      var session = $('input[name="session"]').val();
      var studentname = $('input[name="studentname"]').val();
      var enquiryno = $('input[name="enquiryno"]').val();
     // var formno = $('input[name="formno"]').val();
      // var student_caste = $('input[name="student_caste"]').val();
      // var religion = $('select[name="religion"]').val();
       var classname = $('select[name="classname"]').val();
       var addmission = $('select[name="admission_type"]').val();
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
      // if(enquiryno==''){
      //   $('.enquiryno_msg').text("This field is required*");
      //   //allinputmsg+= ", Enquiry No";
      // }else{

      //   $('.enquiryno_msg').text("");
      // }
      if(classname==null){
        $('.classname_msg').text("This field is required*");
        allinputmsg2 = '1';
      }else{

        $('.classname_msg').text("");
      }
      if(addmission==null){
        $('.addmission_msg').text("This field is required*");
        //allinputmsg+= ", Admission Type";
      }else{

        $('.addmission_msg').text("");
      }
      // if(formno==''){
      //   $('.formno_msg').text("This field is required*");
      //   //allinputmsg+= ", Enquiry No";
      // }else{

      //   $('.formno_msg').text("");
      // }


      
      if(studentname!=='' &&  session!==null &&  classname!==null &&  addmission!==null ){
         $('.rg_form').submit();
         var myForm = document.getElementById("form-id");
     // var result = document.getElementById("result");
     
         event.preventDefault();
         myForm.submit();
         
      }else{
         $('.allinput_msg').text("All required fields must be completed before you submit the form*");
         
         console.log("invalid form");
      }
   });


</script>
{{-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> --}}


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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
        $(document).ready(function () {
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

            setTimeout(function() {
              var year = $("#year").val()
              $("#session").val(year);
            }, 1000);
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
        '<td><input class="form-control" id="siblings_school_second" type="text" placeholder="Enter school name"  name="siblings_school_second[1]"></td> '+
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

    $(".answer").hide();
    $(".coupon_question").click(function() {
        if($(this).is(":checked")) {
            $(".answer").show();
        } else {
            $(".answer").hide();
        }
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function() {
    // Function to handle the date input
    $("#picker2").on("input", function() {
      // Do something with the date value
      const dateValue = $(this).val();
      console.log("Selected Date:", dateValue);
    });
  });
</script>








@endsection