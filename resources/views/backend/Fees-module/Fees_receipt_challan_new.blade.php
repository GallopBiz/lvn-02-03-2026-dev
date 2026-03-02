@extends('backend.layouts.main')
@section('main-container')
<!-- <style>
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

.uperletter{
  text-transform: capitalize;
}  

.bs-searchbox input{
    text-transform: capitalize;
  }

  .dropdown-menu li a{
      text-transform: capitalize;
  }




</style> -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- <div class="main-content"> -->

    <!-- ============ Body content start ============= -->
    <div class="main-content">

          <!-- ============ Body content start ============= -->
        <div class="main-content">
          <div class="breadcrumb">
            <h1>Fees Receipt Challan </h1>
          </div>
          <div class="separator-breadcrumb border-top"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="{{url('save_feesreceipt_challan')}}" class="p-4 progress-form" novalidate method="POST">
                            {{ csrf_field() }}
                                <div class="row">

                                    <div class="col-md-4 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <label class="form-label" for="search_student">Student Search</label>
                                            <select required class="selectpicker" name="search_student" id="search_student" data-live-search="true">
                                                <option data-tokens="">Select The Student</option>
                                                <?php if(!empty($data_student_name)) {
                                                    foreach($data_student_name as $name){
                                                        echo '<option value="'.$name->id.'" data-tokens="'.$name->student_name.'">'.$name->student_name.'-'.$name->form_number.'</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <label class="form-label" for="form1"><br></label>
                                            <!-- <form action="{{url('search-student-ledger')}}"> -->
                                                <p id="ledger" class="btn btn-primary form-control disabled" >Go To Ledger</p>
                                            <!-- </form> -->
                                            <!-- <a href="{{url('search-student-ledger')}}" target="_blank" class="btn btn-primary form-control">Go To Ledger</a> -->
                                        </div>
                                    </div>

                                   
 <div class="col-md-3 form-group mb-3">
    <div class="form-outline w-auto">
        <label class="form-label" for="form1">Date :</label>
        <input type="date" name="student_dob" class="form-control dateInput" id="student_dob" placeholder="Enter Student DOB" />
    </div>
</div>
                                    <!-- <div class="col-md-4 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <label class="form-label" for="form1">Recpt/Chain :</label>
                                            <select name="recpt_chain" class="form-control" id="recpt_chain">
                                                <option value="Receipt">Receipt</option>
                                            </select>
                                        </div>
                                    </div> -->
                                    <!-- <div class="col-md-4 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <label class="form-label" for="form1">Ropt/Chin No :</label>
                                            <input type="text" name="ropt_chin_no" class="form-control" disabled>
                                        </div>
                                    </div> -->

                                    <div class="col-md-3 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <label class="form-label" for="form1">Due Upto :</label>
                                            <select required name="due_upto" id="due_upto_date" onchange="select_due_upto(this);" class="form-control">
                                                <option value="select"> -- Select -- </option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- <div class="col-md-3 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <label class="form-label" for="form1">Due Upto :</label>
                                            <!-- <input type="date" name="student_dob" class="form-control" id="student_dob" placeholder="Enter Student DOB"/> -->
                                            <select name="due_upto" id="due_upto_date" onchange="select_due_upto(this);" class="form-control" >
                                                <option value="select"> -- Select -- </option>
                                             
                                            </select>
                                        </div>
                                    </div> --}}


                                </div>
                                <div class="row">
                                    <div class="col-md-3 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <lable>Name : </lable>
                                            <input type="hidden" id="name_student_geti" name="name_student">
                                            <lable id="name_student_get" class="text text-danger" ></lable>
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <lable>Father's Name :  </lable>
                                            <input type="hidden" id="name_father_geti" name="name_father">
                                            <lable id="name_father_get" class="text text-danger" ></lable>
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <lable>Class Section :  </lable>
                                            <input type="hidden" id="name_classsection_geti" name="name_classsection">
                                            <lable id="name_class_get" class="text text-danger" ></lable>
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <lable>Adm Dt </lable>
                                            <input type="hidden" id="name_adm_geti" name="name_admdt">
                                            <lable id="name_adm_get" class="text text-danger" ></lable>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <lable>Serial No : </lable>
                                            <input type="hidden" id="name_formno_geti" name="name_formno">
                                            <lable id="name_formno_get" class="text text-danger" ></lable>
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <lable>Scholar No : </lable>
                                            <input type="hidden" id="name_scholarno_geti" name="name_scholarno">
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-6">
                                        <div class="form-outline w-auto">
                                            <lable>Fees Type : </lable>
                                                <input type="radio" checked name="feestype" value="0"><span> Current </span><span class="checkmark"></span>
                                                <input type="radio" name="feestype" value="1"><span> Next Yr </span><span class="checkmark"></span>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-md-12 form-group mb-6">
                                    <div class="form-outline w-auto">
                                        <label class="form-label text-primary" id="lumpsum_fees_text" for="form1">lumpsum fees deposit : </label>
                                        <?php echo(!empty($lumpsum_fees->remark) ? $lumpsum_fees->remark : ''); ?>
                                        <input style="margin-right: 10px;" type="checkbox" name="lumpsum_fees" id="lumpsum_fees" value="<?php echo str_replace('%', '', $lumpsum_fees->remark ?? ''); ?>">
                                        
                                        <label class="form-label text-primary" id="siblings_text" for="form1">Siblings <?php echo $siblings->remark ?? '' ?></label>
                                        <input style="margin-right: 10px;" type="checkbox" name="siblings" disabled id="siblings" value="<?php echo str_replace('%', '', $siblings->remark ?? ''); ?>">
                                        
                                        <label class="form-label text-primary" id="staff_ward_text" for="form1">Staff Ward <?php echo $staff_ward->remark ?? '' ?></label>
                                        <input style="margin-right: 10px;" type="checkbox" name="staff_ward" id="staff_ward" value="<?php echo str_replace('%', '', $staff_ward->remark ?? ''); ?>">
                                        
                                        <label class="form-label text-primary" id="lvn_text" for="form1">LVNM <?php echo $lvn->remark ?? '' ?></label>
                                        <input style="margin-right: 10px;" type="checkbox"  name="lvn" id="lvn" value="<?php echo str_replace('%', '', $lvn->remark ?? ''); ?>">
                                    </div>

                                    </div>
                                    <p id="description_text"></p>
                                    <input type="hidden" id="tuition_amount_input" name="tuition_amount_" value="">
                                </div>


                                <div class="row">

                                    <div class="col-md-12 form-group mb-12">
                                    <div class="table-responsive">
                                    <table class="table table-bordered head_table">
                                        <tr>
                                            <!-- <td>Sr</td> -->
                                            <td>Head Name</td>
                                            <td>Due Ammount</td>
                                            <td>Rec. Ammount</td>
                                            <td>From Date</td>
                                            <td>Term</td>
                                            <td>Due Date</td>
                                            <td>Action</td>
                                        </tr>
                                        <tbody class="head_table_body">

                                        </tbody>
                                        <!--  <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr> -->

                                        <!--   <tr>
                                            <td colspan="8" class="text-center">no data found</td>
                                        </tr> -->

                                    </table>
                                            </div>
                                    <div>

                                            <!-- <a href="javascript:void(0)" class="btn-sm btn-success mb-4 add_new_head_btn add_row_head_btn" >Add New Head</a> -->
                                                <input type="hidden" id="count_class" value="">
                                            <!--      <table class="table table-bordered add_new_head_div mt-3" style="display:none;">
                                                <tr>
                                                    <td>Head Name</td>
                                                    <td>Due Ammount</td>
                                                    <td>From Date</td>
                                                    <td>To Date</td>
                                                    <td>Due Date</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <select class="form-control" name="head_name">
                                                            <option>Select Head</option>
                                                            @if(!empty($course_fees_head_orders_list_arr))

                                                                @foreach($course_fees_head_orders_list_arr as $each)

                                                                <option>{{$each->ac_head_name}}</option>

                                                                @endforeach

                                                            @endif
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control head_due_amount" name="head_due_amount">
                                                    </td>
                                                    <td>
                                                        <input type="date" name="head_from_date" class="form-control head_from_date">
                                                    </td>
                                                    <td>
                                                        <input type="date" name="head_to_date" class="form-control head_to_date">
                                                    </td>
                                                    <td>
                                                        <input type="date" name="head_due_date" class="form-control head_due_date">
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0)" class="btn-sm btn-success add_row_head_btn">Add</a>
                                                    </td>
                                                </tr>

                                            </table> -->
                                    </div>
                                    </div>

                                </div>

                              <div class="row d-flex justify-content-between">

                                <div class="row">
                                    <!-- separator table of single form  -->
                                 <div class="col-md-6 mb-4">
                                    <div class="card text-start">
                                    <div class="card-body">
                                <div class="table-responsive">
                                     <table class="table table-borderless.">
                                        <tbody>
                                        <tr>
                                                <td colspan="1"> Total Due Amount : <input type="text" disabled name="after_discount" value="0" class="form-control">
                                                    <input type="hidden" name="hidden_after_discount" value="">
                                                <br></td>
                                                <td colspan="1"> After Discount : <input type="text" disabled name="total_dueamount" value="0" class="form-control">
                                                    <input type="hidden" name="hidden_total_dueamount" value="">
                                                <br></td>
                                                
                                                <td colspan="1"> Total Discount : <input type="text" disabled name="total_discount" value="0" class="form-control">
                                                    <input type="hidden" name="hidden_total_discount" value="">
                                                <br></td>
                                                <!-- <input type="text" name="fee_commitment_remarks" class="form-control"> -->
                                            
                                            </tr>
                                            <tr>
                                                <td class="uperletter"> Received Amount Details :- Remarks :- </td>
                                                <td colspan="2" class="uperletter"> <input class="uperletter" name="received_amount_details_remarks"  placeholder="Received Amount Details :- Remarks :-" type="text" class="form-control"> </td>
                                                <td colspan="1" ><br><br> </td>

                                            </tr>
                                            <tr>
                                            <td> <label class="form-label" for="form1">By Cash : <br> 
                                                    <label class="text-success" for="advance_received_prev">Already have Advance</label>
                                                    <input type="text" class="form-control uperletter" value="" placeholder="Already have Advance" id="advance_received_prew" name="advance_received_prew">
                                                </td>
                                                <td>
                                                    <label class="form-label" for="form1"><br><br></label>
                                                    <input type="text" id="by_cash" placeholder="Type Your Amount" name="by_cash" class="form-control" required>
                                                  </td>
                                                  
                                                <td colspan="1"> <label class="form-label" for="form1"><br><br><br><br></label> <!-- <input name="pdc" type="checkbox"> PDC</td> -->
                                            </tr>
                                            <tr>
                                            <td> <label class="form-label" for="form1">Payment By :   </label>
                                                    <select  class="uperletter" id="" name="payment_by_select"> 
                                                        <option value=""> -- Select -- </option> 
                                                        <option value="Cheque">Cheque</option>
                                                        <option value="NEFT">NEFT</option>
                                                        <option value="Debit/Credit Card">Debit/Credit Card</option>
                                                        <option value="PAYTM">PAYTM</option>
                                                        <option value="Online Pay">Online Pay</option>
                                                        <option value="DD">DD</option>
                                                        <option value="Cash in Bank">Cash in Bank</option>
                                                        <option value="Paid on Other Applications">Paid on Other Applications</option>
                                                    </select> 
                                                </td>
                                                <td><br> <input type="text" placeholder="Nuber or code" name="payment_by" class="form-control uperletter"> </td>
                                                <td colspan="1"><br>
                                                <input type="date" name="payment_date" class="form-control"> </td>
                                            </tr>
                                            <tr>
                                                <td> <label class="form-label" for="form1">Total Received : </label></td>
                                                <td> <input type="text" disabled id="total_received" name="total_received" class="form-control"> </td>
							                        <input type="hidden" id="hidden_total_received" name="hidden_total_received" class="form-control"> 
                                                <td colspan="1" ><label class="form-label" for="form1">Bank A/c Post : </label> <select name="" id=""><option value="Axis Bank">Axis Bank</option></select></td>
						                            <input type="hidden" name="hidden_advance_received" value="">
                                            </tr>
                                            <tr>
                                                <td> <label class="form-label" for="form1">Balance : </label></td>
                                                <td> <input type="text" disabled id="balance_f" name="balance_f" class="form-control"> </td>
                                                    <input type="hidden" id="hidden_balance_f" name="hidden_balance_f" class="form-control"> 
                                                <td colspan="1" ></td>
                                                    <input type="hidden" id="hidden_grand_total_s" name="hidden_grand_total_due" class="form-control"> 
                                                    <input type="hidden" id="hidden_grand_total_s1" name="hidden_grand_total_received" class="form-control"> 
                                            </tr>
                                            <tr>
                                                <td> <label class="form-label" for="form1">Posted V. No : __ : Date :</label></td>
                                                <td colspan="2"> </td>
                                                    <input type="hidden" id="hidden_balance_s" name="hidden_balance_due" class="form-control"> 
                                            </tr>
                                        </tbody>
                                    </table>


                                    
                                    </div>
                                            </div>
                                            </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card text-start">
                                    <div class="card-body">
                                <div class="table-responsive">
                                     <table class="table table-borderless">
                                        <tbody>
                                            <tr class="uperletter" >
						<input  class="uperletter"  type="hidden" name="hidden_total_dueamount" value="">
                                            <td colspan="3" class="uperletter" > Fee Commitment / Remarks : 
                                            <textarea name="fee_commitment_remarks" placeholder="Fee Commitment / Remarks" id="fee_commitment_remarks" class="form-control uperletter" ></textarea>     
                                                <!-- <input type="text" name="fee_commitment_remarks" class="form-control"> -->
                                            </td>
                                            </tr>
                                            <tr>
                                                <td> Totals :- Particulars <br><br></td>
                                                <td> I Due Amt. <br><br></td>
                                                <td> I Received Amt. <br><br></td>
                                                
                                            </tr>
                                            <tr>
                                            <td> 
                                                    <label class="form-label uperletter" for="form1">Late Fees Rate : </label> 
                                                </td>
                                                <td> <label class="form-label uperletter" for="form1"><br></label><input placeholder="Late Fees Due" type="text" name="late_fees_rate_due" class="form-control uperletter"> </td>
                                                <td> <label class="form-label uperletter" for="form1"><br></label><input placeholder="Late Fees Received" type="text" name="late_fees_rate_received" class="form-control uperletter"> </td>
                                            </tr>
                                            <!-- <tr>
                                                <td> <label class="form-label" for="form1">Late Fees Waive Off : </label> <br> </td>
                                                <td> <input type="text" placeholder="Late Fees Waive Off" name="late_fees_waive_off_due" class="form-control"><br> </td>
                                                <td><br></td>
                                            </tr> -->
                                            <tr>
                                            <!-- <td> <label class="form-label" for="form1">Less : Old Advance Fees : </label> </td>
                                                <td> <input type="text" placeholder="Less : Old Advance Fees Due" name="old_advance_fees_due" class="form-control uperletter"> </td>
                                                <td> <input type="text" name="old_advance_fees_received" placeholder="Less : Old Advance Fees Received" class="form-control uperletter"> </td>
                                            </tr> -->
                                            <tr>
                                            <td> <label class="form-label" for="form1">Sub Total : </label> </td>
                                                <td> <input type="text" disabled name="sub_total_due" id="sub_total_s" name="sub_total_s" class="form-control"> </td>
							<input type="hidden" name="hidden_sub_total_due" id="hidden_sub_total_due" value="" class="form-control">
                                                <td> <input type="text" disabled name="sub_total_received" id="sub_total_s1" name="sub_total_s1" class="form-control"> </td>
							<input type="hidden" name="hidden_sub_total_received" id="hidden_sub_total_s1" name="sub_total_s1" class="form-control"> 
                                            </tr>
                                            <!-- <tr>
                                                <td> <label class="form-label" for="form1">Late Fees On Posting : </label> </td>
                                                <td> <input type="text" name="late_fees_on_posting_due" placeholder="Late Fees On Posting Due" class="form-control uperletter"> </td>
                                                <td> <input type="text" name="late_fees_on_posting_received" placeholder="Late Fees On Posting Received" class="form-control uperletter"> </td>
                                            </tr> -->
                                            <tr>
                                                <td> <label class="form-label" for="form1">Advance : </label> </td>
                                                <td> 
						<input type="hidden" id="hidden_total_received" name="hidden_total_received" class="form-control"> 
                                                    <!-- <input type="text" name="advance_due" class="form-control">  -->
							<input type="hidden" name="hidden_advance_received" value="">
                                                </td>
                                                <td> <input type="text" disabled name="advance_received" class="form-control"> </td>
                                            </tr>
                                            <tr>
						<input type="hidden" id="hidden_balance_f" name="hidden_balance_f" class="form-control">  
                                                <td> <label class="form-label" for="form1">Grand Total : </label> </td>
                                                <td> <input type="text" disabled id="grand_total_s" name="grand_total_due" class="form-control"> </td>
							<input type="hidden" id="hidden_grand_total_s" name="hidden_grand_total_due" class="form-control"> 
                                                <td> <input type="text" disabled id="grand_total_s1" name="grand_total_received" class="form-control"> </td>
							<input type="hidden" id="hidden_grand_total_s1" name="hidden_grand_total_received" class="form-control"> 
                                            </tr>
                                            <tr>
                                                <td> <label class="form-label" for="form1">Balance : </label> </td>
                                                <td> <input type="text" disabled id="balance_s" name="balance_due" class="form-control"> </td>
							<input type="hidden" id="hidden_balance_s" name="hidden_balance_due" class="form-control"> 
                                                <td> </td>
                                            </tr>
                                            <tr>
                                                <td> <label class="form-label" for="form1">Late Fees Account : </label> </td>
                                                <td colspan="2"> 
                                                    <select name="late_fees_account" id="">
                                                        <option value=""> -- Select -- </option>
                                                        <option value="ACTIVITY FEES">ACTIVITY FEES</option>
                                                        <option value="ADMISSION FEES">ADMISSION FEES</option>
                                                    </select> 
                                                </td>
                                            </tr>
                                            <!-- <tr>
                                            <td> <label class="form-label" for="form1">Advance Fees Account : </label> </td>
                                                <td colspan="2"> 
                                                    <select name="advance_fees_account" id="">
                                                        <option value=""> -- Select -- </option>
                                                        <option value="ACTIVITY FEES">ACTIVITY FEES</option>
                                                        <option value="ADMISSION FEES">ADMISSION FEES</option>
                                                    </select> 
                                                </td>
                                            </tr> -->
                                        </tbody>
                                    </table>
                                    </div>
                                            </div>
                                            </div>
                                </div>
                                <div class="card-title mb-3 text-end">
                                    <button class="btn btn-success" id="saveButton" disabled>Save</button>
                                  </div>
                                </form>
                                <!-- end separator -->
                                   
                                </div>


                           
                        </div>
                    </div>
            </div>
            <form id="searchForm" action="{{url('search-student-ledger')}}" method="POST" target="_blank">
                {{ csrf_field() }}
                <!-- Add your form fields here -->
                <input type="hidden" name="student_id" id="student_id" value="">
                <!-- <button type="submit" id="ledger">Search Ledger</button> -->
            </form>
          </div>
          <!-- end of main-content -->
          <!-- Footer Start -->
          <div class="flex-grow-1"></div>
          <!-- fotter end -->
        </div>
<!-- 
<script>
  $(document).ready(function() {
    // Function to handle the date input
    $("#student_dob").on("input", function() {
      // Do something with the date value
      const dateValue = $(this).val();
    //   console.log("Selected Date:", dateValue);
    });

    // $("#lumpsum_fees").click(function() {
    //     var i = 0;
    //     var sumfees = 0;
    //     var num10 = $("#lumpsum_fees").val();
    //     $("input[name='head_due_amount[]']").each(function(){
    //         var fees = $(this).val();
    //         hold_tution = $("#head_due_amount_hold_"+i).val();
    //         var headname = $("#head_name_"+i).val();
    //         // console.log(headname);
    //         if (fees !== '') {
    //             var number = parseInt(fees);
    //             var lumpsum_fees = 0;
                
    //             if ($('#lumpsum_fees').is(':checked')) {
    //                 lumpsum_fees = $('#lumpsum_fees').val();
                    
    //                 if(('TUITION FEES' == headname && lumpsum_fees != 0)){
    //                     // value_tuition
                        
    //                     sumfees = sumfees + number;
    //                     value_tuition_total = parseInt(sumfees) * ( parseInt(num10) / 100);
    //                     value_tuition = (parseInt(sumfees) - value_tuition_total);
    //                     text_tuition = '<span class="text-danger">' + sumfees + '</span>';
    //                     description_text = '<span class="text-danger">Origin Amount of TUITION FEES ' + sumfees + ' After lumpsum add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total +'</span>';
                        
    //                     value_tuition_total_text = parseInt(fees) * ( parseInt(num10) / 100);
    //                     value_tuition_text = (parseInt(fees) - value_tuition_total_text);
    //                     $("#value_tuition"+i).removeClass('').text(value_tuition_text).addClass('text-danger');
    //                     $("#head_due_amount_"+i).val(value_tuition_text);
    //                     $('#description_text').html(description_text);
    //                 }
                    
    //             } else {
    //                 $('#description_text').html("");
    //                 $("#value_tuition"+i).text(hold_tution);
    //                 $("#head_due_amount_"+i).val(hold_tution);
    //             }

    //             // if (by_cash > 0) { // Check if by_cash is available for subtraction
    //             //     if (number <= by_cash) {
    //             //         by_cash -= number;
    //             //         $("input[name='head_rec_ammount[]']").eq(index).val(number);
    //             //     } else {
    //             //         $("input[name='head_rec_ammount[]']").eq(index).val(by_cash);
    //             //         number -= by_cash;
    //             //         by_cash = 0;
    //             //         // console.log("Subtracted value:", number);
    //             //     }
    //             // } else {
    //             //     // console.log("Insufficient by_cash for subtraction");
    //             // }

    //             // index++;
    //         }
    //         i++
    //     });
        
    // });

  });
</script>


<script>

document.addEventListener('DOMContentLoaded', function() {
        var today = new Date().toISOString().split('T')[0];
        document.getElementById('student_dob').value = today;
    });

function select_due_upto(ele){
    var date = ele.value;
    var lumpsum_fees = 0;
    var siblings = 0;
    var staff_ward = 0;
    var lvn = 0;
    var final_total = 0;
    var value_tuition_total = 0;
    var value_tuition_total_m = 0;
    var text_tuition_ = 0;
    if ($('#lumpsum_fees').is(":checked")) {
        lumpsum_fees = $('#lumpsum_fees').val();
    }
    if ($('#siblings').is(":checked")) {
        siblings = $('#siblings').val();
    }
    if ($('#staff_ward').is(":checked")) {
        staff_ward = $('#staff_ward').val();
    }
    if ($('#lvn').is(":checked")) {
        lvn = $('#lvn').val();
    }

    var student_id = $('#search_student').val();
    $(".head_table_body").empty();
    $.ajax({
        type : "POST",
        data : {student_id:student_id,date:date},
        url : "{{ url('get_student_fees_struct') }}",
        headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        dataType : 'json',
        success: function(data){
            var head_rec_ammount = '';
            // console.log(data.generate_due_chat);
            // console.log(data.tuition_amount_);
            $("input[name='advance_received_prew']").val(data.advance_received);
            var text_tuition_ = 0;
            if (data.due_chart_data != 0) {
                var i = 0;
                var totalSum = 0; // Initialize a variable to hold the total sum

                $.each(data.discount, function(key, value){
                    if (value !== null) { // Check if value is not null
                        var num = parseFloat(value.match(/\d+/)[0]); // Extracts the first numerical value from the string
                        totalSum += num; // Add the numerical value to the total sum
                    }
                });

                console.log("Total sum:", totalSum); // Output the total sum

                $.each(data.due_chart_data, function(key, value) {
                    var headName = value.head_name;
                    // if(('TUITION FEES' == headName)){
                    //     value.head_rec_ammount =  (parseInt(value.head_rec_ammount) + parseInt(data.tuition_amount_)); //tuition_amount_
                    // }
                    
                    // const dbDate = new Date(value.head_due_date.split("-").reverse().join("-"));
                    // const currentDate = new Date(date.split("-").reverse().join("-"));
                    const dbDate = value.head_due_date;
                    const currentDate = $("#due_upto_date").val(); //new Date(date.split("-").reverse().join("-"));

                    const dbDateParts = dbDate.split('-').map(part => parseInt(part)); // Parse the parts as integers
                    const currentDateParts = currentDate.split('-').map(part => parseInt(part)); // Parse the parts as integers

                    // Create Date objects
                    const dbDateObject = new Date(dbDateParts[2], dbDateParts[1] - 1, dbDateParts[0]); // year, monthIndex (0-11), day
                    const currentDateObject = new Date(currentDateParts[2], currentDateParts[1] - 1, currentDateParts[0]); // year, monthIndex (0-11), day

                    if (dbDateObject <= currentDateObject){
                        
                        if(('TUITION FEES' == headName) && (value.head_rec_ammount !== 0)){
                            var discount = value.head_due_amount * (totalSum / 100);
                            var discountedPrice = value.head_rec_ammount + discount
                            value.head_rec_ammount = discountedPrice;
                            console.log("after " + value.term_str);
                        }
                        if (value.head_due_amount != value.head_rec_ammount){
                            console.log("He " + value.head_due_amount);
                            console.log("rec " + value.head_rec_ammount);
                            
                            var value_tuition = data.generate_due_chat.fees[i];
                            var text_tuition = '';
                            text_tuition_ += parseInt(data.generate_due_chat.fees[i]);
                            if(('TUITION FEES' == data.generate_due_chat.account_name[i] && lumpsum_fees != 0 && siblings != 0)){
                                // 3000 * (10 / 100) = 300
                                var num8 = $("#lumpsum_fees").val();
                                var num9 = $("#siblings").val();
                                // var num11 = $("#staff_ward").val();
                                var num12 = 0;
                                if ($('#staff_ward').is(":checked")) {
                                    num12 = $('#staff_ward').val();
                                }
                                var num13 = 0;
                                if ($('#lvn').is(":checked")) {
                                    num13 = $('#lvn').val();
                                }
                                var num10 = (parseInt(num8) + parseInt(num9) + parseInt(num12) + parseInt(num13));
                                // var num10 = (parseInt(num8) + parseInt(num9) + pareseInt(num11));
                                value_tuition_total = parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition_total_m += parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition = (parseInt(data.generate_due_chat.fees[i]) - value_tuition_total);
                                text_tuition = '<span class="text-danger">' + data.generate_due_chat.fees[i] + '</span>';
                                final_total += parseInt(data.generate_due_chat.fees[i]);
                                description_text = '<span class="text-danger">Origin Amount of TUITION FEES ' + final_total + ' After lumpsum and siblings add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total_m +'</span>';
                            } else if(('TUITION FEES' == data.generate_due_chat.account_name[i] && lumpsum_fees != 0)){
                                var num8 = $("#lumpsum_fees").val();
                                var num12 = 0;
                                if ($('#staff_ward').is(":checked")) {
                                    num12 = $('#staff_ward').val();
                                }
                                var num13 = 0;
                                if ($('#lvn').is(":checked")) {
                                    num13 = $('#lvn').val();
                                }
                                var num10 = (parseInt(num8) + parseInt(num12) + parseInt(num13));
                                value_tuition_total = parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition_total_m += parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition = (parseInt(data.generate_due_chat.fees[i]) - value_tuition_total);
                                text_tuition = '<span class="text-danger">' + data.generate_due_chat.fees[i] + '</span>';
                                final_total += parseInt(data.generate_due_chat.fees[i]);
                                description_text = '<span class="text-danger">Origin Amount of TUITION FEES ' + final_total + ' After lumpsum add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total_m +'</span>';
                            } else if(('TUITION FEES' == data.generate_due_chat.account_name[i] && siblings != 0)){
                                var num8 = $("#siblings").val();
                                var num12 = 0;
                                if ($('#staff_ward').is(":checked")) {
                                    num12 = $('#staff_ward').val();
                                }
                                var num13 = 0;
                                if ($('#lvn').is(":checked")) {
                                    num13 = $('#lvn').val();
                                }
                                var num10 = (parseInt(num8) + parseInt(num12) + parseInt(num13));
                                value_tuition_total = parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition_total_m += parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition = (parseInt(data.generate_due_chat.fees[i]) - value_tuition_total);
                                text_tuition = '<span class="text-danger">' + data.generate_due_chat.fees[i] + '</span>';
                                final_total += parseInt(data.generate_due_chat.fees[i]);
                                description_text = '<span class="text-danger">Origin Amount of TUITION FEES ' + final_total + ' After siblings add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total_m +'</span>';
                            } else if(('TUITION FEES' == data.generate_due_chat.account_name[i] && staff_ward != 0)){
                                var num8 = $("#staff_ward").val();
                                var num13 = 0;
                                if ($('#lvn').is(":checked")) {
                                    num13 = $('#lvn').val();
                                }
                                var num10 = (parseInt(num8) + parseInt(num13));
                                value_tuition_total = parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition_total_m += parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition = (parseInt(data.generate_due_chat.fees[i]) - value_tuition_total);
                                text_tuition = '<span class="text-danger">' + data.generate_due_chat.fees[i] + '</span>';
                                final_total += parseInt(data.generate_due_chat.fees[i]);
                                description_text = '<span class="text-danger">Origin Amount of TUITION FEES ' + final_total + ' After siblings add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total_m +'</span>';
                            }
                            else if(('TUITION FEES' == data.generate_due_chat.account_name[i] && lvn != 0)){
                                var num8 = $("#lvn").val();
                                var num10 = (parseInt(num8));
                                value_tuition_total = parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition_total_m += parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition = (parseInt(data.generate_due_chat.fees[i]) - value_tuition_total);
                                text_tuition = '<span class="text-danger">' + data.generate_due_chat.fees[i] + '</span>';
                                final_total += parseInt(data.generate_due_chat.fees[i]);
                                description_text = '<span class="text-danger">Origin Amount of TUITION FEES ' + final_total + ' After siblings add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total_m +'</span>';
                            }
                            var row = $('<tr>');
                            row.append(
                            $('<td>').text(value.head_name)
                                .append('<input type="hidden" value="' + value.head_name + '" class="form-control head_name" id="head_name_'+i+'" name="head_name[]">')
                                .append('<input type="hidden" value="' + (value_tuition - value.head_rec_ammount) + '" class="form-control head_due_amount_" id="head_due_amount_'+i+'" name="head_due_amount[]">')
                                .append('<input type="hidden" value="' + value.head_due_date + '" class="form-control head_to_date" name="head_to_date[]">')
                                .append('<input type="hidden" value="' + value.term_str + '" class="form-control term_str" name="term_str[]">')
                                .append('<input type="hidden" value="' + value.head_to_date + '" class="form-control head_due_date" name="head_due_date[]">')
                            );
                            // row.append($('<td>').html('<span class="text-success" id="value_tuition' + i + '">' + value_tuition - value.head_rec_ammount));
                            row.append($('<td>').text(value_tuition - value.head_rec_ammount));
                            row.append($('<td>').html('<input type="text" id="head_rec_ammount_' + i + '" placeholder="received amount" name="head_rec_ammount[]" value="" class="form-control head_rec_ammount">'));
                            row.append($('<td>').text(value.head_due_date));
                            row.append($('<td>').text(value.term_str));
                            row.append($('<td>').text(value.head_to_date));
                            $('.head_table_body').append(row);
                        }
                    }
                    
                    
                    i++;
                });
                $('#description_text').html(description_text);

                var match;
                if (description_text !== undefined) {
                    match = description_text.match(/\b\d+\b/g);
                }

                var lastAmount = match ? match[match.length - 1] : null;
                if (lastAmount !== null) {
                    $("#tuition_amount_input").val(lastAmount);
                }
                $("input[name='after_discount']").val(text_tuition_);
                // for (var i = 0; i < data.due_chart_data[0].head_name.length; i++) {
                //     var row = $('<tr>');
                //     row.append(
                
                //     $('<td>').text(data.due_chart_data.head_name[i])
                //         .append('<input type="hidden" value="' + data.due_chart_data.head_name[i] + '" class="form-control head_name" name="head_name[]">')
                //         .append('<input type="hidden" value="' + data.due_chart_data.head_due_amount[i] + '" class="form-control head_due_amount_" id="head_due_amount_'+i+'" name="head_due_amount[]">')
                //         .append('<input type="hidden" value="' + data.due_chart_data.head_due_date[i] + '" class="form-control head_to_date" name="head_to_date[]">')
                //         .append('<input type="hidden" value="' + data.due_chart_data.term_str[i] + '" class="form-control term_str" name="term_str[]">')
                //         .append('<input type="hidden" value="' + data.due_chart_data.head_to_date[i] + '" class="form-control head_due_date" name="head_due_date[]">')
                //     );

                //     row.append($('<td>').text(data.due_chart_data.head_due_amount[i]));
                //     row.append($('<td>').html('<input type="text" id="head_rec_ammount_' + i + '" placeholder="received amount" name="head_rec_ammount[]" value="" class="form-control head_rec_ammount">'));
                //     row.append($('<td>').text(data.due_chart_data.head_due_date[i]));
                //     row.append($('<td>').text(data.due_chart_data.term_str[i]));
                //     row.append($('<td>').text(data.due_chart_data.head_to_date[i]));
                // }
            } else {
                var description_text = '';
                for (var i = 0; i < data.generate_due_chat.account_name.length; i++) {
                    // console.log(data.generate_due_chat.due_date[i]);
                    const dbDate = new Date(data.generate_due_chat.due_date[i]);
                    const currentDate = new Date($("#due_upto_date").val()); //new Date(date.split("-").reverse().join("-"));
                    
                    var skipRow = false;
                    if (dbDate <= currentDate){
                        // console.log(currentDate);
                        text_tuition_ += parseInt(data.generate_due_chat.fees[i]);
                        var matchedEntryFound = false;

                        var val_rec = 0;
                        $.each(data.due_chart_data, function(key, value) {
                            var head_name = value.head_name;
                            if (data.generate_due_chat.account_name[i] == head_name && data.generate_due_chat.term[i] == value.term_str){
                                head_rec_ammount = value.head_rec_ammount;
                                // row.append($('<td>').html('<input type="text" id="head_rec_ammount_' + i + '" placeholder="received amount" name="head_rec_ammount[]" value="' + head_rec_ammount + '" class="form-control head_rec_ammount">'));
                                matchedEntryFound = true;
                                skipRow = true;
                                val_rec = value.head_rec_ammount
                                // console.log(data.generate_due_chat.fees[i]);
                            }
                            
                        });
                        
                        if (!skipRow) {
                            
                            var value_tuition = data.generate_due_chat.fees[i];
                            
                            var text_tuition = '';
                            if(('TUITION FEES' == data.generate_due_chat.account_name[i] && lumpsum_fees != 0 && siblings != 0)){
                                // 3000 * (10 / 100) = 300
                                var num9 = $("#siblings").val();
                                var num8 = $("#lumpsum_fees").val();
                                var num12 = 0;
                                if ($('#staff_ward').is(":checked")) {
                                    num12 = $('#staff_ward').val();
                                }
                                var num13 = 0;
                                if ($('#lvn').is(":checked")) {
                                    num13 = $('#lvn').val();
                                }

                                var num10 = (parseInt(num8) + parseInt(num9) + parseInt(num12) + parseInt(num13));
                                value_tuition_total = parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition_total_m += parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition = (parseInt(data.generate_due_chat.fees[i]) - value_tuition_total);
                                text_tuition = '<span class="text-danger">' + data.generate_due_chat.fees[i] + '</span>';
                                final_total += parseInt(data.generate_due_chat.fees[i]);
                                description_text = '<span class="text-danger">A Origin Amount of TUITION FEES ' + final_total + ' After lumpsum and siblings add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total +'</span>';
                            } else if(('TUITION FEES' == data.generate_due_chat.account_name[i] && lumpsum_fees != 0)){
                                var num8 = $("#lumpsum_fees").val(); //alert("test");
                                var num12 = 0;
                                if ($('#staff_ward').is(":checked")) {
                                    num12 = $('#staff_ward').val();
                                }
                                var num13 = 0;
                                if ($('#lvn').is(":checked")) {
                                    num13 = $('#lvn').val();
                                }
                                $(document).ready(function(){    
// function round(num, decimalPlaces) {
//     num = Math.round(Math.abs(num) + "e" + decimalPlaces) * Math.sign(num);
//     return Number(num + "e" + -decimalPlaces);
// }
});
                                var num10 = (parseInt(num8) + parseInt(num12) + parseInt(num13));
                                var value_tuition_total; var value_tuition_total_m1;
                                value_tuition_total1 = parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition_total_m1 += parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition_total = value_tuition_total1;
                                value_tuition_total_m = value_tuition_total_m1;
                                value_tuition = (parseInt(data.generate_due_chat.fees[i]) - value_tuition_total);
                                text_tuition = '<span class="text-danger">' + data.generate_due_chat.fees[i] + '</span>';
                                final_total += parseInt(data.generate_due_chat.fees[i]);
                                description_text = '<span class="text-danger">Origin Amount of TUITION FEES ' + final_total + ' After lumpsum add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total_m +'</span>';
                            } else if(('TUITION FEES' == data.generate_due_chat.account_name[i] && siblings != 0)){
                                // var num10 = $("#siblings").val();
                                var num8 = $("#siblings").val();
                                var num12 = 0;
                                if ($('#staff_ward').is(":checked")) {
                                    num12 = $('#staff_ward').val();
                                }
                                var num13 = 0;
                                if ($('#lvn').is(":checked")) {
                                    num13 = $('#lvn').val();
                                }
                                var num10 = (parseInt(num8) + parseInt(num12) + parseInt(num13));
                                value_tuition_total = parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition_total_m += parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition = (parseInt(data.generate_due_chat.fees[i]) - value_tuition_total);
                                text_tuition = '<span class="text-danger">' + data.generate_due_chat.fees[i] + '</span>';
                                final_total += parseInt(data.generate_due_chat.fees[i]);
                                description_text = '<span class="text-danger">Origin Amount of TUITION FEES ' + final_total + ' After siblings add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total_m +'</span>';
                            } else if (('TUITION FEES' == data.generate_due_chat.account_name[i] && staff_ward != 0)){
                                var num8 = $("#staff_ward").val();
                                // var num12 = 0;
                                var num13 = 0;
                                if ($('#lvn').is(":checked")) {
                                    num13 = $('#lvn').val();
                                }
                                var num10 = (parseInt(num8) + parseInt(num13));
                                // var num10 = (parseInt(num8) + parseInt(num12));
                                value_tuition_total = parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition_total_m += parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition = (parseInt(data.generate_due_chat.fees[i]) - value_tuition_total);
                                text_tuition = '<span class="text-danger">' + data.generate_due_chat.fees[i] + '</span>';
                                final_total += parseInt(data.generate_due_chat.fees[i]);
                                description_text = '<span class="text-danger">AOrigin Amount of TUITION FEES ' + final_total + ' After staff_ward add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total_m +'</span>';
                            } else if (('TUITION FEES' == data.generate_due_chat.account_name[i] && lvn != 0)){
                                var num8 = $("#lvn").val();
                                // var num12 = 0;
                                var num10 = (parseInt(num8));
                                // var num10 = (parseInt(num8) + parseInt(num12));
                                value_tuition_total = parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition_total_m += parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition = (parseInt(data.generate_due_chat.fees[i]) - value_tuition_total);
                                text_tuition = '<span class="text-danger">' + data.generate_due_chat.fees[i] + '</span>';
                                final_total += parseInt(data.generate_due_chat.fees[i]);
                                description_text = '<span class="text-danger">Origin Amount of TUITION FEES ' + final_total + ' After staff_ward add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total_m +'</span>';
                            }
                            
                            var row = $('<tr>');
                            
                            row.append(
                        
                            $('<td>').text(data.generate_due_chat.account_name[i])
                                .append('<input type="hidden" value="' + data.generate_due_chat.account_name[i] + '" class="form-control head_name" id="head_name_'+i+'" name="head_name[]">')
                                .append('<input type="hidden" value="' + value_tuition + '" class="form-control head_due_amount_" id="head_due_amount_'+i+'" name="head_due_amount[]">')
                                .append('<input type="hidden" value="' + value_tuition + '" class="form-control head_due_amount_hold_" id="head_due_amount_hold_'+i+'" name="head_due_amount_hold[]">')
                                .append('<input type="hidden" value="' + data.generate_due_chat.fees_date[i] + '" class="form-control head_to_date" name="head_to_date[]">')
                                .append('<input type="hidden" value="' + data.generate_due_chat.term[i] + '" class="form-control term_str" name="term_str[]">')
                                .append('<input type="hidden" value="' + data.generate_due_chat.due_date[i] + '" class="form-control head_due_date" name="head_due_date[]">')
                            );
                            // console.log(val_rec);
                            row.append($('<td>').html('<span class="text-success" id="value_tuition' + i + '">' + value_tuition + ' </span> ' + text_tuition));
                            row.append($('<td>').html('<input type="text" id="head_rec_ammount_' + i + '" placeholder="received amount" name="head_rec_ammount[]" value="" class="form-control head_rec_ammount">'));
                            row.append($('<td>').text(data.generate_due_chat.fees_date[i]));
                            row.append($('<td>').text(data.generate_due_chat.term[i]));
                            row.append($('<td>').text(data.generate_due_chat.due_date[i]));
                        }
                        row.append(
                    
                        $('<td>')
                            .append('<input type="hidden" value="' + data.generate_due_chat.account_name[i] + '" class="form-control head_name" name="head_name1[]">')
                            .append('<input type="hidden" value="' + data.generate_due_chat.fees[i] + '" class="form-control head_due_amount_" id="head_due_amount_'+i+'" name="head_due_amount1[]">')
                            .append('<input type="hidden" value="' + data.generate_due_chat.fees_date[i] + '" class="form-control head_to_date" name="head_to_date1[]">')
                            .append('<input type="hidden" value="' + data.generate_due_chat.term[i] + '" class="form-control term_str" name="term_str1[]">')
                            .append('<input type="hidden" value="' + data.generate_due_chat.due_date[i] + '" class="form-control head_due_date" name="head_due_date1[]">')
                            .append($('<td>').html('<input type="hidden" id="head_rec_ammount_' + i + '" placeholder="received amount" name="head_rec_ammount1[]" value="" class="form-control head_rec_ammount">'))
                        );
                        // console.log(data.generate_due_chat.fees[i]);
                       
                        
                        $('.head_table_body').append(row);
                        // console.log(row);
                    }
                    // var row1 = $('<tr>');
                    $('#description_text').html(description_text);
                    var match
                    if (description_text !== undefined) {
                        match = description_text.match(/\b\d+\b/g);
                    }

                    // var match = description_text.match(/\b\d+\b/g);
                    var lastAmount = match ? match[match.length - 1] : null;
                    if (lastAmount !== null) {
                        $("#tuition_amount_input").val(lastAmount);
                    }
                    row.append(
                    
                    $('<td>')
                        .append('<input type="hidden" value="' + data.generate_due_chat.account_name[i] + '" class="form-control head_name" name="head_name1[]">')
                        .append('<input type="hidden" value="' + data.generate_due_chat.fees[i] + '" class="form-control head_due_amount_" id="head_due_amount_'+i+'" name="head_due_amount1[]">')
                        .append('<input type="hidden" value="' + data.generate_due_chat.fees_date[i] + '" class="form-control head_to_date" name="head_to_date1[]">')
                        .append('<input type="hidden" value="' + data.generate_due_chat.term[i] + '" class="form-control term_str" name="term_str1[]">')
                        .append('<input type="hidden" value="' + data.generate_due_chat.due_date[i] + '" class="form-control head_due_date" name="head_due_date1[]">')
                        .append($('<td>').html('<input type="hidden" id="head_rec_ammount_' + i + '" placeholder="received amount" name="head_rec_ammount1[]" value="" class="form-control head_rec_ammount">'))
                    );
                    // console.log(data.generate_due_chat.fees[i]);
                   
                    $('.head_table_body').append(row);
                    // $('.head_table_body').append(row1);
                    
                }
            }
            

            var sum_main = 0;
            var sum_after_discount = 0;
            var discount_amount = 0;
            $("input[name='head_due_amount[]']").each(function(){
                if ($(this).val() !== '') {
                    sum_main += parseInt($(this).val());
                    // console.log(parseInt($(this).val()));
                }
            });
            // alert(text_tuition_);
            $("input[name='total_dueamount']").val(sum_main);
            $("input[name='after_discount']").val(text_tuition_);
            discount_amount = parseInt(text_tuition_) - parseInt(sum_main);
            $("input[name='total_discount']").val(discount_amount);
            $("input[name='hidden_total_dueamount']").val(sum_main);
            $("input[name='grand_total_due']").val(sum_main);
            $("input[name='hidden_grand_total_due']").val(sum_main);
            $("input[name='sub_total_due']").val(sum_main);
            $("input[name='hidden_sub_total_due']").val(sum_main);
            $("input[name='grand_total_received']").val(sum_main);
            var ab = $('.head_due_amount_').length;
            // console.log(ab);

            $('#count_class').val(ab);
        }
    });
}

function select_data(ele){
    student_id = ele.value;
    if (student_id === 'Select The Student'){
        $("#ledger").addClass("disabled");
    } else {
        $("#ledger").removeClass("disabled");
        $("#student_id").val(student_id);
        $(".head_table_body").empty();
        $.ajax({
            type : "POST",
            data : {student_id:student_id},
            url : "{{ url('get_student_info') }}",
            headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            dataType : 'json',
            success: function(data){
                // console.log(data.siblings);
                // return;
                $("#lumpsum_fees").prop("disabled", false);
                $("#lumpsum_fees_text").addClass("text-success");
                
                if (data.siblings != '0'){
                    $("#siblings_text").removeClass("text-primary");
                    $("#siblings").prop("disabled", false);
                    $("#siblings_text").addClass("text-success");
                } else {
                    $("#siblings_text").removeClass("text-success");
                    $("#siblings").prop("disabled", true);
                    $("#siblings_text").addClass("text-primary");
                }
                
                $("#name_student_get").text(data.student_name);
                $("#name_father_get").text(data.fathername);
                $("#name_formno_get").text(data.formno);
                $("#name_adm_get").text(data.adm_dt);
                $("#name_class_get").text(data.class_name);
                $("#name_student_geti").val(data.student_name);
                $("#name_father_geti").val(data.fathername);
                $("#name_formno_geti").val(data.formno);
                $("#name_adm_geti").val(data.adm_dt);
                $("#name_classsection_geti").val(data.class_name);

                $("#due_upto_date").empty();
                var selectTag = $("#due_upto_date");

                selectTag.append($('<option>', {
                        value: 'select',
                        text: '-- select --'
                    }));
                for (var key in data.due_upto) {
                    var date = data.due_upto[key];
                    selectTag.append($('<option>', {
                        value: date,
                        text: date
                    }));
                }

                // append rows

                // console.log(data.generate_due_chat.account_name);

                // return;
                // var tableBody = $('.head_table');

                // for (var i = 0; i < data.generate_due_chat.account_name.length; i++) {
                //     // console.log(row);
                //     var row = $('<tr>');
                //         row.append(
                //             $('<td>').text(data.generate_due_chat.account_name[i])
                //                 .append('<input type="hidden" value="' + data.generate_due_chat.account_name[i] + '" class="form-control head_name" name="head_name[]">')
                //                 .append('<input type="text" value="' + data.generate_due_chat.fees[i] + '" class="form-control head_due_amount_" id="head_due_amount_'+i+'" name="head_due_amount[]">')
                //                 .append('<input type="hidden" value="' + data.generate_due_chat.fees_date[i] + '" class="form-control head_to_date" name="head_to_date[]">')
                //                 .append('<input type="hidden" value="' + data.generate_due_chat.term[i] + '" class="form-control term_str" name="term_str[]">')
                //                 .append('<input type="hidden" value="' + data.generate_due_chat.due_date[i] + '" class="form-control head_due_date" name="head_due_date[]">')
                        
                //         );

                //         row.append($('<td>').text(data.generate_due_chat.fees[i]));
                //         row.append($('<td>').html('<input type="text" id="head_rec_ammount_'+[i]+'" placeholder="recieved ammount" name="head_rec_ammount[]" value="" class="form-control head_rec_ammount">')); // You can populate this with received amount data
                //         row.append($('<td>').text(data.generate_due_chat.fees_date[i]));
                //         row.append($('<td>').text(data.generate_due_chat.term[i]));
                //         row.append($('<td>').text(data.generate_due_chat.due_date[i]));
                        
                //         $('.head_table_body').append(row);
                //         console.log(row);
                        

                // }
                // var sum_main = 0;
                // $("input[name='head_due_amount[]']").each(function(){
                //     if ($(this).val() !== '') {
                //         sum_main += parseInt($(this).val());
                //     }
                // });
        
                // $("input[name='total_dueamount']").val(sum_main);
                // $("input[name='grand_total_due']").val(sum_main);
                // $("input[name='grand_total_received']").val(sum_main);
                // var ab = $('.head_due_amount_').length;
                // console.log(ab);

                // $('#count_class').val(ab);


                // $.each(data.generate_due_chat, function (a1,b1) {
                //     var tbl_row ='<tr><td>';

                //     tbl_row += '</td></tr>';
                //     $('.head_table').append(tbl_row);
                // });

                // var total_due_ammount = 0;
                // var i = 0; 
                // $.each(data.due_chart_data, function (k1,v1) {
                //     total_due_ammount += parseInt(v1.fees);
                //     var tbl_row ='<tr><td><select class="form-control" name="head_name"><option>Select Head</option>';

                //     $.each(data.course_fees_head_orders_list_arr, function (k2,v2) { 
                //         if(v2.ac_head_name.trim().toUpperCase()==v1.account_name.trim().toUpperCase()){
                //             tbl_row += '<option selected>'+v2.ac_head_name.trim()+'</option>';
                //         }else{
                //             tbl_row += '<option>'+v2.ac_head_name.trim()+'</option>';
                //         }
                //     });

                //     // tbl_row += '</select> </td> </td><td><input type="text" name="head_due_amount[]" value="'+v1.fees+'" class="form-control head_due_amount"></td><td><input type="text" name="head_rec_ammount[]" value="" class="form-control" placeholder="recieved ammount"></td><td><input type="date" name="head_from_ammount[]" value="" class="form-control" placeholder="from ammount"></td><td><input type="date" name="head_to_date[]" value="" class="form-control head_to_date"></td><td><input type="date" name="head_due_date[]" value="'+v1.due_date+'" class="form-control head_due_date"></td><td><a href="javascript:void(0)" class="remove_head_row_button text-danger remove_btn">remove</a></td></tr>';

                //     $('.head_table_body').append(tbl_row);

                // });

                // $("input[name='total_dueamount']").val(total_due_ammount);
                // $("input[name='grand_total_due']").val(total_due_ammount);
                // $("input[name='grand_total_received']").val(total_due_ammount);
                // return;



                // console.log(data.due_chart_data);
                // return;

                // $.each(data.due_chart_data, function (data1,d1) {

                //     console.log(data);

                //     $.each(d1, function (data2) {
                //     var tbl_row = '';

                //          tbl_row += '<tr>';

                //         tbl_row += '<td>';
                //              tbl_row +='<select class="form-control" name="head_name"><option>Select Head</option><?php if(!empty($course_fees_head_orders_list_arr)){ ?><?php foreach($course_fees_head_orders_list_arr as $each){ ?><option><?php echo trim($each->ac_head_name); ?> </option> <?php } } ?> </select> </td> </td><td><input type="text" name="head_due_amount" value="" class="form-control head_due_amount">'+data2.account_name+'</td><td><input type="text" name="head_rec_ammount[]" value="" class="form-control" placeholder="recieved ammount"></td><td><input type="date" name="head_from_ammount[]" value="" class="form-control" placeholder="from ammount"></td><td><input type="date" name="head_to_date" value="" class="form-control head_to_date"></td><td><input type="date" name="head_due_date" value="" class="form-control head_due_date"></td><td><a href="javascript:void(0)" class="remove_head_row_button text-danger">remove</a>';


                //         tbl_row += '<td/>';

                //           tbl_row +='</tr>';
                //         });
                //         $('.head_table').append(tbl_row);
                // });

                // console.log(data.due_chart_data);

                // $('.head_table').append();
                
            }
        });
    }
}

$("input[name='late_fees_waive_off_due']").keyup(function() {
    var waive_off = $("input[name='late_fees_waive_off_due']").val();
    var late_num = $("input[name='late_fees_rate_due']").val();
    var by_cash = parseInt($("#by_cash").val());
    var advance_received_prew = parseInt($("#advance_received_prew").val());
    if (advance_received_prew != null) {
        by_cash += advance_received_prew;
    }
    var sum_main = $("input[name='total_dueamount']").val();
    var num = (parseInt(by_cash) - (parseInt(sum_main) + (parseInt(late_num) - parseInt(waive_off)) ));

    $("#balance_s").val((parseInt(sum_main) + (parseInt(late_num) - parseInt(waive_off)) ) - parseInt(by_cash));
    $("#hidden_balance_s").val((parseInt(sum_main) + (parseInt(late_num) - parseInt(waive_off)) ) - parseInt(by_cash));
    $("#balance_f").val((parseInt(sum_main) + (parseInt(late_num) - parseInt(waive_off)) ) - parseInt(by_cash));
    $("#hidden_balance_f").val((parseInt(sum_main) + (parseInt(late_num) - parseInt(waive_off)) ) - parseInt(by_cash));
    $("#sub_total_s").val(parseInt(sum_main) + (parseInt(late_num) - parseInt(waive_off)));
    $("#hidden_sub_total_due").val(parseInt(sum_main) + (parseInt(late_num) - parseInt(waive_off)));
    $("#grand_total_s").val(parseInt(sum_main) + (parseInt(late_num) - parseInt(waive_off)));
    $("#hidden_grand_total_s").val(parseInt(sum_main) + (parseInt(late_num) - parseInt(waive_off)));

    if (parseInt(num) > 0){
        $("input[name='advance_received']").val(num);
        $("input[name='hidden_advance_received']").val(num);
    }
    $("input[name='late_fees_rate_received']").val(parseInt(late_num) - parseInt(waive_off));
});

$("input[name='late_fees_rate_due']").keyup(function(){
    var late_num = $("input[name='late_fees_rate_due']").val();
    var by_cash = parseInt($("#by_cash").val());
    var advance_received_prew = parseInt($("#advance_received_prew").val());
    if (advance_received_prew != null) {
        by_cash += advance_received_prew;
    }
    var sum_main = $("input[name='total_dueamount']").val();
    var num = (parseInt(by_cash) - (parseInt(sum_main) + parseInt(late_num)));
    $("input[name='advance_received']").val('');
    $("input[name='hidden_advance_received'").val('');

    $("input[name='late_fees_rate_received']").val(late_num);
    $("#sub_total_s").val(parseInt(sum_main) + parseInt(late_num));
    $("#hidden_sub_total_due").val(parseInt(sum_main) + parseInt(late_num));
    $("#grand_total_s").val(parseInt(sum_main) + parseInt(late_num));
    $("#hidden_grand_total_s").val(parseInt(sum_main) + parseInt(late_num));
    $("#sub_total_s1").val(parseInt(sum_main) + parseInt(late_num));
    $("#hidden_sub_total_s1").val(parseInt(sum_main) + parseInt(late_num));
    // var total_due = $("input[name='total_dueamount']").val();
    // $("input[name='total_dueamount']").val('');
    // $("input[name='total_dueamount']").val(parseInt(late_num) + parseInt(total_due));

    $("#balance_s").val((parseInt(sum_main) + parseInt(late_num)) - parseInt(by_cash));
    $("#hidden_balance_s").val((parseInt(sum_main) + parseInt(late_num)) - parseInt(by_cash));
    $("#balance_f").val((parseInt(sum_main) + parseInt(late_num)) - parseInt(by_cash));
    $("#hidden_balance_f").val((parseInt(sum_main) + parseInt(late_num)) - parseInt(by_cash));

    if (parseInt(num) > 0){
        $("input[name='advance_received']").val(num);
        $("input[name='hidden_advance_received'").val(num);
    }
});

$("#by_cash").keyup(function(){
    var by_cash = parseInt($("#by_cash").val());
    var advance_received_prew = parseInt($("#advance_received_prew").val());
    if (!isNaN(advance_received_prew)) {
        by_cash += advance_received_prew;
    }
    $("#total_received").val(by_cash);
    $("#hidden_total_received").val(by_cash);
    // $("#sub_total_s").val(by_cash);
    $("#sub_total_s1").val(by_cash);
    $("#hidden_sub_total_s1").val(by_cash);
    // $("#grand_total_s").val(by_cash);
    $("#grand_total_s1").val(by_cash);
    $("#hidden_grand_total_s1").val(by_cash);
    // $("#balance_s").val(by_cash);
    // console.log(by_cash);
    $("input[name='head_rec_ammount[]']").val('');
    $("input[name='advance_received']").val('');
    $("input[name='hidden_advance_received']").val('');
    
    var sum_main = $("input[name='total_dueamount']").val();
    $("#balance_s").val(parseInt(sum_main) - parseInt(by_cash));
    $("#hidden_balance_s").val(parseInt(sum_main) - parseInt(by_cash));
    $("#balance_f").val(parseInt(sum_main) - parseInt(by_cash));
    $("#hidden_balance_f").val(parseInt(sum_main) - parseInt(by_cash));
    var num = (parseInt(by_cash) - parseInt(sum_main));
    
    if (parseInt(num) > 0){
        $("input[name='advance_received']").val(num);
        $("input[name='hidden_advance_received']").val(num);
    }

    var index = 0;
    $("input[name='head_due_amount[]']").each(function(){
        
        var inputValue = $(this).val();

        if (inputValue !== '') {
            var number = parseInt(inputValue);
            // console.log("Original value:", number);

            if (by_cash > 0) { // Check if by_cash is available for subtraction
                if (number <= by_cash) {
                    by_cash -= number;
                    $("input[name='head_rec_ammount[]']").eq(index).val(number);
                } else {
                    $("input[name='head_rec_ammount[]']").eq(index).val(by_cash);
                    number -= by_cash;
                    by_cash = 0;
                    // console.log("Subtracted value:", number);
                }
            } else {
                // console.log("Insufficient by_cash for subtraction");
            }

            index++;
        }

    });

});

    $("#search_student").keyup(function() {
        var student_name = $("#search_student").val();
        // console.log(student_name);

        if (student_name == "") {
           //Assigning empty value to "display" div in "search.php" file.
        //    $("#display").html("");
        } else {
            $('#display').empty();
            $.ajax({
                type:"POST",
                data: {student_name:student_name},
                url: "{{url('search_student_name')}}",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                dataType: 'json',
                success: function(data){
                    $.each(data , function(key , val){
                        // list ="<li>"+val.student_name+"</li>";
                        $('#search_student')
                        .append($("<option></option>")
                        .attr("value", val.student_name)
                        .text(val.student_name)); 
                        // $("#search_student").append(list);
                        // console.log(val.student_name)
                    });
                }
            })
        }
    });


    // $('.add_new_head_btn').click(function(){

    //     // $('.add_new_head_div').show();

    //     // setTimeout(function(){
    //     //    $(".add_new_head_div").load(location.href + " #mydiv");
    //     // }, 1000);

    // });
    
    // var cd = 0;
    $('.add_row_head_btn').click(function(){
        var ab = $('#count_class').val();
        var cd = parseInt(ab);
        cd++
        // console.log(cd);
        // $('.head_table').append($(this).closest('tr'));

        // $("#mydiv").load(location.href + " #mydiv");

        // var head_due_amount = $('.head_due_amount').val();
        // var head_from_date = $('.head_from_date').val();
        // var head_to_date = $('.head_to_date').val();
        // var head_due_date = $('.head_due_date').val();

        var tbl_row = '<tr><td><select class="form-control" name="head_name[]"><option>Select Head</option><?php if(!empty($course_fees_head_orders_list_arr)){ ?><?php foreach($course_fees_head_orders_list_arr as $each){ ?><option><?php echo trim($each->ac_head_name); ?> </option> <?php } } ?> </select> </td> </td><td><input type="text" name="head_due_amount[]" value="" id="head_due_amount_'+cd+'" class="form-control head_due_amount"></td><td><input type="text" name="head_rec_ammount[]" value="" class="form-control" placeholder="recieved ammount"></td><td><input type="date" name="head_to_date[]" value="" class="form-control" placeholder="from ammount"></td><td><select id="term" class="form-control" name="term_str" autocomplete="shipping address-level1" required=""><option value="" disabled="" selected="">Please select</option><option value="1st">1st</option><option value="2nd">2nd</option><option value="3rd">3rd</option><option value="4th">4th</option></select></td><td><input type="date" name="head_due_date[]" value="" class="form-control head_due_date"></td><td><a href="javascript:void(0)" class="remove_head_row_button text-danger remove_btn">remove</a><td/></tr>';

        $('.head_table_body').append(tbl_row);
        
        $('#count_class').val(cd);
    });

    // $('.remove_head_row_buttun').click(function(){
       // $('.remove_head_row_button').on('click', function () {  
         // var whichtr = $(this).closest("tr");
        //   $(this).parents("tr").remove()      
        // alert('worked'); // Alert does not work
        // whichtr.remove();   
    // });

    function sum_main() {
       var sum_main = 0;
       $("input[name='head_due_amount[]']").each(function(){
             if($(this).val()!==''){
                sum_main += parseInt($(this).val());
             }
       });
        $("input[name='total_dueamount']").val(sum_main);
        $("input[name='hidden_total_dueamount']").val(sum_main);
        $("input[name='grand_total_due']").val(sum_main);
        $("input[name='hidden_grand_total_due']").val(sum_main);
        // $("input[name='grand_total_received']").val(sum_main);
    }

    $(document).on('keyup change', "input[name='head_due_amount[]']", function() {
       setTimeout(sum_main, 100);
    });

    // function sum_main1() {
    //    var sum_main1 = $("input[name='total_dueamount']").val();
    //     // alert(sum_main1);
    //     // sum_main2 = parseInt(sum_main1) - parseInt($(this).val());
       
    //     // $("input[name='total_dueamount']").val(sum_main2);
    // }
    
    // $(document).on('keyup change', "input[name='head_rec_ammount[]']", function() {
    //    setTimeout(sum_main1, 100);
    // });

    $("table").on("click", ".remove_btn", function () {
        var removedValue = parseInt($(this).closest("tr").find("input[name='head_due_amount[]']").val());

        var sum_main = 0;
        $("input[name='head_due_amount[]']").each(function () {
            if ($(this).val() !== '') {
                sum_main += parseInt($(this).val());
            }
        });

        sum_main -= removedValue; 
    
        $("input[name='total_dueamount']").val(sum_main);
        $("input[name='hidden_total_dueamount']").val(sum_main);
        $("input[name='grand_total_due']").val(sum_main);
        $("input[name='hidden_grand_total_due']").val(sum_main);
        $("input[name='sub_total_due']").val(sum_main);
        $("input[name='hidden_sub_total_due']").val(sum_main);
        // $("input[name='grand_total_received']").val(sum_main);
        $(this).closest("tr").remove();
    });

    $("#ledger").on("click", function() {
        $("#searchForm").submit();
    })

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

  // Get the current year
//   var currentYear = new Date().getFullYear();

// Define the number of years for sessions in the future
// var numberOfFutureYears = 5; // You can adjust this as needed

// Get the select element
// var selectElement = document.getElementById("due_upto_date");

// Generate and append option elements for future sessions
// for (var i = 0; i < numberOfFutureYears; i++) {
//     var sessionStartYear = currentYear + i;
//     var sessionEndYear = sessionStartYear + 1;
//     var session = sessionStartYear + "-" + sessionEndYear;
//     var option = document.createElement("option");
//     option.value = session;
//     option.textContent = session;
//     selectElement.appendChild(option);
// }

// Set the default selected option to the current session (e.g., "2023-2024")
// selectElement.value = currentYear + "-" + (currentYear + 1);

const inputField = document.getElementById('by_cash');
  const saveButton = document.getElementById('saveButton');

  function toggleSaveButton() {
    if (inputField.value.trim() !== '') {
      saveButton.removeAttribute('disabled');
    } else {
      saveButton.setAttribute('disabled', 'true');
    }
  }

  inputField.addEventListener('input', toggleSaveButton);

  toggleSaveButton();

</script>

{{-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('search_student');
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

  </script> --}} -->

<!-- </div> -->
@endsection