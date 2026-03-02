@extends('backend.layouts.main')
@section('main-container')
<style type="text/css">
   .remove_border{
   border: none;
   outline: none;
   pointer-events: none;
   }
   input {
    position: relative;
}

.uperletter{
  text-transform: capitalize;
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
</style>
<form action="{{url('save-course-fees-structure-master')}}" method="post">
<div class="main-content pt-4">
<div class="form_section1_div">
   <div class="breadcrumb">
      <h1 class="me-2">Course Fees Master</h1>
   </div>   
   
      @csrf
      <div class="row">
         <div class="col-md-4 form-group mb-3">
            <label for="lastName1">Class Name</label>
            <select id="Batch" class="form-control" name="class_name" autocomplete="shipping address-level1" required>
            <option disabled selected>Please select</option>
               @(!empty($class_names)):
                  @foreach($class_names as $class_name):
                     <option {{ (!empty(session('sl_classname')) && (session('sl_classname')==$class_name->class_name )) ? 'selected' : '' }} value="{{ $class_name->class_name }}">{{ $class_name->class_name }}</option>
                  @endforeach
               @ifend
            </select>
         </div>
         <div class="col-md-4 form-group mb-3">
            <label for="lastName1">Session Name</label>
            <!-- <select id="session_name" class="form-control" name="session_name" autocomplete="shipping address-level1" required>
               session_name
               <option value="" disabled selected>Please select</option>
               @foreach(config('global.session_name') as $each)
               <option {{ (!empty(session('sl_sessionname')) && (session('sl_sessionname')==$each )) ? 'selected' : '' }} value="{{ $each }}">{{ $each }}</option>
               @endforeach
            </select> -->
            <input type="text" readonly id="session_name" class="form-control" value="" name="session_name">
         </div>
         <div class="col-md-4 form-group mb-3">
            <label for="lastName1">Fees Type Name</label>
            <select id="Fees_Type_Name" class="form-control" name="fees_type_name" autocomplete="shipping address-level1" required>
               <option value="" selected>Please select</option>
               @if(!empty($fees_types))
                  @foreach($fees_types as $fees_type)
                       <option {{ (!empty(session('sl_feetypename')) && (session('sl_feetypename')==$fees_type->fees_type )) ? 'selected' : '' }} value="{{ $fees_type->fees_type }}">{{ $fees_type->fees_type }}</option>
                  @endforeach
               @endif
            </select>
         </div>
         <!-- <div class="col-md-4 form-group mb-3">
            <label for="lastName1">Caste /  Category </label><br>
            <select id="Fees_Type_Name" class="form-control" name="cast_category" autocomplete="shipping address-level1" required>
               <option disabled selected>Please select</option>
               <option value="same_for_all">Same for all</option>
               <option value="Opt1">Opt1</option>
               <option value="Opt2">Opt2</option>
               <option value="Opt3">Opt3</option>
            </select>
         </div> -->
         <!-- <div class="col-md-4 form-group mb-3">
            <label for="lastName1">Batch </label><br>
             <select id="batch" class="form-control" name="batch" autocomplete="shipping address-level1"
                            required>
                            <option value="" disabled selected>Please select</option>
                            <option {{ (!empty(session('sl_batch')) && (session('sl_batch')=='same_for_all' )) ? 'selected' : '' }} value="same_for_all">Same for all</option>
                            <option {{ (!empty(session('sl_batch')) && (session('sl_batch')=='Opt1' )) ? 'selected' : '' }} value="Opt1">Opt1</option>
                            <option {{ (!empty(session('sl_batch')) && (session('sl_batch')=='Opt2' )) ? 'selected' : '' }} value="Opt2">Opt2</option>
                            <option {{ (!empty(session('sl_batch')) && (session('sl_batch')=='Opt3' )) ? 'selected' : '' }} value="Opt3">Opt3</option>
                        </select>
         </div> -->
      </div>
      <br>
      <div class="row">
         <!-- <div class="col-md-9 form-group mb-3">
            <h5>Copy same structure from  previous years with  dates adjustments :-</h5>
         </div>
         <div class="col-md-3 form-group mb-3">
            <button class="btn btn-success m-1 get_privious_structure" type="button">Copy Previous</button>
         </div> -->
         <div class="col-md-4 form-group mb-3">
            <h5>Copy same structure as the following structure :-</h5>
         </div>
         <div class="col-md-2 form-group mb-3">
            <!-- <select id="Fees_Type_Name" class="form-control " name="copy_session_structure" autocomplete="shipping address-level1">
               <option disabled selected >Please select</option>
               @foreach(config('global.session_name') as $each)
               <option value="{{$each}}">{{$each}}</option>
               @endforeach
            </select> -->
            <select name="copy_session_structure" id="Fees_Type_Name" class="form-control" >
               <option value="" > -- Please Select -- </option>
               @foreach($databaseNames as $databaseName)
                  @if (is_numeric(substr($databaseName, 0, 1)))
                     <option value=<?php echo $databaseName; ?> ><?php echo $databaseName; ?></option>
                     @endif
               @endforeach
            </select>
         </div>
         <div class="col-md-2 form-group mb-3">
            <select id="Batch" class="form-control" name="class_namepre" autocomplete="shipping address-level1" required>
            <option disabled selected>Please select</option>
               @(!empty($class_names)):
                  @foreach($class_names as $class_name):
                     <option value="{{ $class_name->class_name }}">{{ $class_name->class_name }}</option>
                  @endforeach
               @ifend
            </select>
            </div>
            
         <div class="col-md-2 form-group mb-3">
            <button class="btn btn-success m-1 get_privious_structure" type="button">Copy Previous</button>
         </div> 
         <div>   
               <b><span class="err_resp_text text-red"></span></b>
            </div>
         </div>
       <!--   <div class="col-md-3 form-group mb-3">
            <button class="btn btn-success m-1" type="button">Copy Selected</button>
         </div> -->
      </div>
      <br>
      <br>
      <div class="row">
        <!--  <div class="col-md-5 form-group mb-3">
            <table class="table table-borderless">
               <thead>
                  <tr>
                     <th scope="col">Fees Date</th>
                     <th scope="col">Due Date</th>
                     <th scope="col">Term</th>
                  </tr>
               </thead>
               <tbody>
                  <tr>
                     <td><input name="" class="form-control cls_fees" id="IMEI" type="date" placeholder="" /></td>
                     <td><input name="" class="form-control cls_fees" id="IMEI" type="date" placeholder="" /></td>
                     <td>
                        <select id="term" class="form-control cls_fees" name="" autocomplete="shipping address-level1" required>
                           <option value="" disabled selected>Please select</option>
                           @foreach(config('global.term') as $each)
                           <option value="{{$each}}">{{$each}}</option>
                           @endforeach
                        </select>
                     </td>
                  </tr>
               </tbody>
            </table>
            <button class="btn btn-danger m-1" onclick="ClearFeesFields();" type="button">Clear Fees</button>
            <table class="table" id="structure_table">
               <thead>
                  <tr>
                     <th scope="col">Sr.</th>
                     <th scope="col">Account Name</th>
                     <th scope="col">Fees</th>
                     <th scope="col">Select</th>
                  </tr>
               </thead>
               <tbody>
                  @if(!empty($course_fees_head_master))
                  @foreach($course_fees_head_master as $each)
                  <tr>
                     <th scope="row">{{$loop->iteration}}</th>
                     <td>{{$each->ac_head_name}}</td>
                     <td><input name="imei" class="form-control orderFees" id="IMEI" type="text" placeholder="" /></td>
                     <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                  </tr>
                  @endforeach
                  @else
                  <tr>
                     <td colspan="4" class="text-center">No record found</td>
                  </tr>
                  @endif
               </tbody>
            </table>
         </div>
         <div class="col-md-1 form-group mb-3" style="margin-top:20%;">

            <a href="javascript:void(0)" class="btn btn-info m-1 save_structure" id="save_structure" data-test='this is eestt data'>>>></a>
            <br>
            <button class="btn btn-info m-1"  type="button"><<<</button>
         </div> -->
         <div class="col-md-12 form-group mb-3">
            <!-- <button class="btn btn-primary" data-toggle="modal" data-target="#addNewStructure"></button> -->
            <button class="btn btn-primary mb-2" type="button" data-bs-toggle="modal" data-target=".addNewStructure" >Add New Structure row</button>
            <!-- right side table -->
            <br><br>
            <!-- <table class="table structure_table table-bordered"> -->
            <table class="display table table-striped structure_table table-bordered" id="zero_configuration_table" style="width: 100%">
               <thead>
                  <tr>
                     <th scope="col">Sr.</th>
                     <th scope="col">Fees Date</th>
                     <th scope="col">Account Name</th>
                     <th scope="col">Fees</th>
                     <th scope="col">Due Date</th>
                     <th scope="col">Term</th>
                     <!-- <th scope="col">Select</th> -->
                     <th scope="col">Action</th>
                  </tr>
               </thead>
               <tbody class="structure_row_table">
                  @php $total = 0; @endphp
                  @if(sizeof($holds_structure_row)>0)
                     @php $i = 0; @endphp
                     @foreach($holds_structure_row as $each1)
                     @php $total += $each1->fees; @endphp
                     
                  <tr>
                     <th scope="row">{{$loop->iteration}}</th>
                     <td><input type="text" name="fees_date[]" class="remove_border" value="{{date("d-m-Y", strtotime($each1->fees_date))}}"></td>
                     <td><input type="text" name="account_name[]" class="remove_border" value="{{$each1->account_name}}"></td>
                     <td><input name="fees[]" value="{{$each1->fees}}" class="form-control orderFees_main" id="IMEI" type="text" placeholder="" /></td>
                     <td><input type="due_date" name="due_date[]" value="{{date("d-m-Y", strtotime($each1->due_date))}}" class="remove_border"></td>
                     <td>
                        <select id="Fees_Type_Name" class="form-control" name="term[]" autocomplete="shipping address-level1" required>
                           <option value="{{$each1->term}}"  selected>{{$each1->term}}</option>
                           @foreach($terms as $each)
                           <option value="{{$each->terms}}">{{$each->terms}}</option>
                           @endforeach
                        </select>
                     </td>
                     <!-- <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td> -->
                     <td><button class="btn btn-danger m-1 removeItem" data-delete_id='{{$each1->id}}' type="button">Delete</button></td>
                  </tr>
                     @php $i++; @endphp
                     @endforeach
                  @else
                  <tr class="noRecordFoundText">
                     <td colspan="7" class="text-center">
                        <label>Add New Rows</label>  
                     </td>
                  </tr>
                  
                  @endif
               </tbody>
               <tfoot>
                     <tr>
                        <th scope="col">Sr.</th>
                        <th scope="col">Fees Date</th>
                        <th scope="col">Account Name</th>
                        <th scope="col">Fees</th>
                        <th scope="col">Due Date</th>
                        <th scope="col">Term</th>
                        <!-- <th scope="col">Select</th> -->
                        <th scope="col">Action</th>
                     </tr>
                  </tfoot>
            </table>
         </div>
      </div>
      <div class="row">
         <div class="col-md-4 form-group mb-3">
            <h5>Total above fees :</h5>
            <input type="text" name="total_above_fees" value="{{$total}}" class="form-control order_fees_total_main" readonly />
         </div>
         <!-- <div class="col-md-4 form-group mb-4" style="text-align:right;"> -->
         <!-- </div> -->
      </div>
      <div class="row">
         <div class="col-md-12 text-center">
          <button class="btn btn-primary" onclick="setvalue()">Submit</button>
                        {{-- <button class="btn btn-info">New</button> --}}
                        {{-- <button class="btn btn-danger">Delete</button> --}}
         </div>
      </div>
  
</div>
 </form>
<!-- end of main-content -->
<!-- Modal -->
<div class="modal fade addNewStructure" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" >
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="exampleModalCenterTitle">
               Add New Structure Row
            </h5>
            <button
               class="btn btn-close"
               type="button"
               data-bs-dismiss="modal"
               aria-label="Close"
               ></button>
         </div>
         <form>
         @csrf
         <div class="modal-body">
               <div class="row">
                  <div class="col-md-4">
                     <div class="form-group">
                        <label class="col-form-label">Fees Date:</label>
                        {{-- <input name="fees_date_str" class="form-control fees_date_str dateInput" type="date"  /> --}}
                        <input name="fees_date_str" id="picker2" class="form-control fees_date_str" type="date"  />
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="form-group">
                        <label class="col-form-label">Due Date:</label>
                        <input class="form-control due_date_str" type="date" name="due_date_str" id="picker2" />
                     </div>
                  </div>
                  <div class="col-md-4">
                        <div class="form-group">
                           <label class="col-form-label">Term:</label>
                           <select id="term" class="form-control" name="term_str" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              @foreach($terms as $each)
                           <option value="{{$each->terms}}">{{$each->terms}}</option>
                           @endforeach
                           </select>
                        </div>
                        <div class="row">
                           <span class="validation_err_str_form text-red"></span>
                        </div>
                     </div>
                  <!-- <div class="row" id="rowContainer">
                     <div class="col-md-4">
                        <div class="form-group">
                           <label class="col-form-label">Account Name:</label>
                           <select name="account_name_str[]" class="form-control account_name_str" >
                           <option value="">-- Select The Head --</option>
                              @if(!empty($course_fees_head_master))
                                 @foreach($course_fees_head_master as $each)
                                 <option value="{{$each->ac_head_name}}" value="{{$each->ac_head_name}}">{{$each->ac_head_name}}</option>
                                 @endforeach
                              @endif
                           </select>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label class="col-form-label">Fees:</label>
                           <input class="form-control fees_str" type="text" name="fees_str[]" />
                        </div>
                     </div>
                  </div> -->
                  <div class="row" id="rowContainer">
                  </div>
                  <div>
                     <b><span class="save_structure_row_resp text-success"></span></b>
                  </div>
               </div>
         </div>
         <div class="modal-footer">
            <button class="btn btn-primary add_new_row" type="button"> Add New Row
            </button>
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal"> Close
            </button>
            <button class="btn btn-primary ms-2 save_row_btn" type="button"> Save Row </button>
         </div>
         </form>
      </div>
   </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/sweetalert2.min.css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
<script src="{{url('assets/backend')}}/js/plugins/sweetalert2.min.js"></script>
<script src="{{url('assets/backend')}}/js/scripts/sweetalert.script.min.js"></script>
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
<script type="text/javascript">
 /*-------------------------------------------------------------------------------------------*/  
$('.structure_table').on('click', '.removeItem', function(event) {

   // $('.removeItem').click(function (event) {
      // alert("asdasd");
     event.preventDefault();
      var elm = $(this);
      var delete_id = $(this).data('delete_id');
     
     swal({
       title: 'Are you sure?',
       text: "It will permanently deleted !",
       type: 'warning',
       showCancelButton: true,
       confirmButtonColor: 'success',
       cancelButtonColor: '#d33',
       confirmButtonText: 'Yes, delete it!'
     }).then(function() {
       $(elm).parents("tr").remove()  
       
       var myUrl = "{{url('delete-structure-row')}}";

       $.ajax({
           url: myUrl,
           type: "POST",
           data: { 
               "_token": "{{ csrf_token() }}",
               delete_id : delete_id
           },
           success: function () {
             swal(
               'Deleted!',
               'Your file has been deleted.',
               'success'
             );
            var sum_main = 0;
            $(".orderFees_main").each(function(){
                  if($(this).val()!==''){
                     sum_main -= parseInt($(this).val());
                  }
            });
            $('.order_fees_total_main').val(Math.abs(sum_main));
             // $(".structure_table").load(location.href + " .structure_table");
           }
       });
   


     })
      
   });
 /*-------------------------------------------------------------------------------------------*/  
   
   function ClearFeesFields() {
      $(".cls_fees").val("");
      $(".cls_fees").val("");
   }
 /*-------------------------------------------------------------------------------------------*/  
   
   $(document).ready(function() {
    function sum() {
       var sum = 0;
       $(".orderFees").each(function(){
             if($(this).val()!==''){
                sum += parseInt($(this).val());
             }
       });
          $('.order_fees_total').val(sum);
    }
   
    function resetForm() {
            $('#structureForm')[0].reset();
        }

        // Attach click event handler to the "Add New Structure Row" button
        $('.addNewStructure').on('shown.bs.modal', function () {
            $(".account_name_str").val("");
            $(".fees_date_str").val("");
            $("#term").val("");
            $(".due_date_str").val("");
            $(".fees_str").val("");
            $('#rowContainer').empty();
        });

    $(document).on('keyup change', ".orderFees", function() {
       setTimeout(sum, 100);
    });
   
    function sum_main() {
       var sum_main = 0;
       $(".orderFees_main").each(function(){
             if($(this).val()!==''){
                sum_main += parseInt($(this).val());
             }
       });
          $('.order_fees_total_main').val(sum_main);
    }
   
    $(document).on('keyup change', ".orderFees_main", function() {
       setTimeout(sum_main, 100);
    });
   
    

 /*-------------------------------------------------------------------------------------------*/  
    /*course fees master structure ready*/

    var tableControl= document.getElementById('structure_table');
    var arrayOfValues = [];
     $('#save_structure').click(function() {
       // alert("Asdasd");
         $('input:checkbox:checked', tableControl).each(function() {
             arrayOfValues.push($(this).closest('tr').find('td:last').text());
         }).get();
     });
     console.log(arrayOfValues);
   
    /*course fees master structure ready*/

 /*-------------------------------------------------------------------------------------------*/  
   
   $('.get_privious_structure').click(function(){
   
    var session_name = $('select[name="copy_session_structure"]').val();
    var class_name = $('select[name="class_namepre"]').val();
   
    if(session_name!==''){
   
      $(".order_fees_total_main").remove();
      // $($(.order_fees_total_main).closest("tr")).remove()

       $.ajax({
           url: '{{url("get_previous_structure")}}',
           type: "GET",
           data: { 
               "_token": "{{ csrf_token() }}",
               session_name : session_name,
               class_name : class_name
           },
           success: function (data) {
               console.log(data);
                if(data=='notfound'){
                   $('.err_resp_text').text('No Record Found*');
                }else{

                     $('.noRecordFoundText').hide();
                     // var order_fees_total_main = 0;
                     var order_fees_total_main = parseInt($('.order_fees_total_main').val());

                     var count = 1;
                     $.each(jQuery.parseJSON(data), function(idx,obj) {

                        console.log(obj);
                           var tableRow = '<tr><td>'+count+'</td><td>'+obj.fees_date+'</td><td>'+obj.account_name+'</td><td><input type="text" name="fees[]" value="'+obj.fees+'" class="form-control orderFees_main"></td><td>'+obj.due_date+'</td><td><select id="Fees_Type_Name" class="form-control feetypeid'+idx+'" name="term[]" autocomplete="shipping address-level1" required>'+'<option value="" disabled selected>Please select</option><?php foreach(config("global.term") as $each){ ?><option value="<?php echo $each; ?>"><?php echo $each; ?></option><?php } ?></select></td><td><button class="btn btn-danger m-1 removeItem" data-delete_id="'+obj.id+'" type="button">Delete</button></td>'+'</tr>'; 
                           $('table').find('tbody').append(tableRow);

                           $(`.feetypeid${idx}`).val(obj.term).change();

                           order_fees_total_main += parseInt(obj.fees);

                           count++;

                     });

                }

                $('.order_fees_total_main').val(order_fees_total_main);
           }
       });
   
    }else{
       console.log("session empty");
    }
   
   
   
   });
   
 /*-------------------------------------------------------------------------------------------*/  
   
   // Insert Curse fees structure row

   $('.save_row_btn').click(function(e){
      e.preventDefault();
   var data1 = [];
   var fees_date_str = $('.fees_date_str').val();
   var due_date_str = $('.due_date_str').val();
   var term_str = $('select[name="term_str"]').val();

   
   $('.row').each(function() {
   var account_name_str = $(this).find('select[name="account_name_str[]"]').val();
   var fees_str = $(this).find('input[name="fees_str[]"]').val();

      data1.push({
      account_name_str:account_name_str,
      fees_str:fees_str
      });
   });

    if(due_date_str!=='' && term_str!==''){

       $.ajax({
           url: '{{url("save-structure-row")}}',
           type: "POST",
           data: { 
               "_token": "{{ csrf_token() }}",
               fees_date_str : fees_date_str,
               due_date_str : due_date_str,
               term_str : term_str,
               data1 : data1,
           },
           success: function (data) {
               $('.save_structure_row_resp').show();
               $('.save_structure_row_resp').text('Row Added*');

               setTimeout(function() {
                   $('.save_structure_row_resp').fadeOut('fast');
                   $('.btn-close').trigger('click');
               }, 300);

               $(".structure_table").load(location.href + " .structure_table");
               $(".order_fees_total_main").load(location.href + ".order_fees_total_main");
               total();
           }
       });
   

    }else{
      $('.validation_err_str_form').text("Oops please fill all fields in form*");
    }

   });

   function total(){
      $.ajax({
           url: '{{url("total-structure-row")}}',
           type: "GET",
           data: { 
               "_token": "{{ csrf_token() }}",
               // fees_date_str : fees_date_str,
           },
           success: function (data) {
               console.log(data);
            $(".order_fees_total_main").val(data);
           }
       });
   }


   $(function(){
    var dtToday = new Date();
    
    var month = dtToday.getMonth() + 1;
    var day = dtToday.getDate();
    var year = dtToday.getFullYear();
    if(month < 10)
        month = '0' + month.toString();
    if(day < 10)
        day = '0' + day.toString();
    
    var minDate= year + '-' + month + '-' + day;
       
       $('.dateInput').attr('min', minDate);
   });


   function addNewRow() {
      //console.log("hello")
      var newRowHtml = `<div class="row">
         <div class="col-md-4">
            <div class="form-group">
               <label class="col-form-label">Account Name:</label>
               <select name="account_name_str[]" class="form-control account_name_str" >
                  @if(!empty($course_fees_head_master))
                  <option value="" >-- Select The Head --</option>
                     @foreach($course_fees_head_master as $each)
                     <option value="{{$each->ac_head_name}}" value="{{$each->ac_head_name}}">{{$each->ac_head_name}}</option>
                     @endforeach
                  @endif
               </select>
            </div>
         </div>
         <div class="col-md-3">
            <div class="form-group">
               <label class="col-form-label">Fees:</label>
               <input class="form-control" type="text" name="fees_str[]" />
            </div>
         </div>
         <div class="col-md-2">
            <div class="form-group">
               <label class="col-form-label">Action<br></label>
               <button class="btn btn-danger remove_row" type="button">Remove</button>
            </div>
         </div>
      </div>`;
      $('#rowContainer').append(newRowHtml);
   }

    // Event handler for the "Add New Row" button
   $('.add_new_row').click(function () {
      addNewRow();
   });

   $(document).on('click', '.remove_row', function () {
      $(this).closest('.row').remove();
   });


 /*-------------------------------------------------------------------------------------------*/  

   setTimeout(function() {
      var year = $("#year").val()
      $("#session_name").val(year);
   }, 1000);

});
   
</script>

<script>
   document.addEventListener('DOMContentLoaded', function () {
       var select = document.getElementById('Fees_Type_Name');
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

@endsection