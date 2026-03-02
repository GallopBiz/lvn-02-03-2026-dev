@extends('backend.layouts.main')
@section('main-container')

{{-- <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" /> --}}
{{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css" rel="stylesheet" /> --}}

<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- <div class="main-content"> -->

    <!-- ============ Body content start ============= -->
    <div class="main-content">

          <!-- ============ Body content start ============= -->
        <div class="main-content">
          <div class="breadcrumb">
            <h1>Fees Details</h1>
            <ul>
              <!-- <li><a href="href">Form</a></li> -->
              <!-- <li>Basic</li> -->
              <input type="hidden" id="student_id_led" class="disabled" value="<?php echo $data['student_id']; ?>">
              <input type="hidden" id="class_name_led" class="disabled" value="<?php echo $data['class_name']; ?>">
              <input type="hidden" id="due_upto_led" class="disabled" value="<?php echo $data['due_upto']; ?>">
              <?php //print_r($data); ?>
            </ul>
          </div>
          <div class="separator-breadcrumb border-top"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-body">
                        <!-- <div class="card-title mb-3">Form Inputs</div> -->
                        <!-- <form> -->
                            <form action="{{url('save_feesreceipt_challan')}}" class="p-4 progress-form" novalidate method="POST">
                            {{ csrf_field() }}
                                <div class="row">
                                    <p id="description_text"></p>
                                </div>

                                <div class="row">

                                    <div class="col-md-12 form-group mb-12">
                                    <table class="table table-bordered head_table">
                                        <tr>
                                            <!-- <td>Sr</td> -->
                                            <td>Head Name</td>
                                            <td>Due Ammount</td>
                                            <td>Rec. Ammount</td>
                                            <td>From Date</td>
                                            <td>To Date</td>
                                            <td>Due Date</td>
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
                                    <div>

                                            <a href="javascript:void(0)" class="btn-sm btn-success mb-4 add_new_head_btn add_row_head_btn" >Add New Head</a>
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


                                <div class="row">
                                    <!-- separator table of single form  -->
                                 <div class="col-md-6 mb-4">
                                    <div class="card text-start">
                                    <div class="card-body">
                                <div class="table-responsive">
                                     <table class="table table-borderless.">
                                        <tbody>
                                        <tr>
                                                <td colspan="3"> total Due Amount : <input type="text" disabled name="total_dueamount" value="0" class="form-control"><br></td>   
                                                <!-- <input type="text" name="fee_commitment_remarks" class="form-control"> -->
                                            </td>
                                            </tr>
                                            <tr>
                                                <td> Received Amount Details :- Remarks :- </td>
                                                <td colspan="2"> <input name="received_amount_details_remarks"  placeholder="Received Amount Details :- Remarks :-" type="text" class="form-control"> </td>
                                                <td colspan="1" ><br><br> </td>

                                            </tr>
                                            <tr>
                                            <td> <label class="form-label" for="form1">By Cash : <br> 
                                                    <label class="text-success" for="advance_received_prev">Already have Advance</label>
                                                    <input type="text" class="form-control" value="" placeholder="Already have Advance" id="advance_received_prew" name="advance_received_prew">
                                                </td>
                                                <td> <label class="form-label" for="form1"><br><br></label><input type="text" id="by_cash" placeholder="Type Your Amount" name="by_cash" class="form-control"> </td>
                                                <td colspan="1"> <label class="form-label" for="form1"><br><br><br><br></label><input name="pdc" type="checkbox"> PDC</td>
                                            </tr>
                                            <tr>
                                            <td> <label class="form-label" for="form1">Payment By :   </label>
                                                    <select id="" name="payment_by_select"> 
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
                                                <td><br> <input type="text" placeholder="Nuber or code" name="payment_by" class="form-control"> </td>
                                                <td colspan="1"> </td>
                                            </tr>
                                            <tr>
                                                <td> <label class="form-label" for="form1">Total Received : </label></td>
                                                <td> <input type="text" disabled id="total_received" name="total_received" class="form-control"> </td>
                                                <td colspan="1" ><label class="form-label" for="form1">Bank A/c Post : </label> <select name="" id=""><option value=""> -- Select -- </option><option value="">Axis Bank</option></select></td>
                                            </tr>
                                            <tr>
                                                <td> <label class="form-label" for="form1">Balance : </label></td>
                                                <td> <input type="text" disabled id="balance_f" name="balance_f" class="form-control"> </td>
                                                <td colspan="1" ></td>
                                            </tr>
                                            <tr>
                                                <td> <label class="form-label" for="form1">Posted V. No : __ : Date :</label></td>
                                                <td colspan="2"> </td>
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
                                            <tr>
                                            <td colspan="3"> Fee Commitment / Remarks : 
                                            <textarea name="fee_commitment_remarks" placeholder="Fee Commitment / Remarks" id="fee_commitment_remarks" class="form-control" ></textarea>     
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
                                                    <label class="form-label" for="form1">Late Fees Rate : </label> 
                                                    <!-- <input name="late_fees_rate" type="text" class="form-control" value="{{$late_fees_master->late_fees_amount}}">  -->
                                                </td>
                                                <td> <label class="form-label" for="form1"><br></label><input placeholder="Late Fees Due" type="text" name="late_fees_rate_due" class="form-control"> </td>
                                                <td> <label class="form-label" for="form1"><br></label><input placeholder="Late Fees Received" type="text" name="late_fees_rate_received" class="form-control"> </td>
                                            </tr>
                                            <tr>
                                                <td> <label class="form-label" for="form1">Late Fees Waive Off : </label> <br> </td>
                                                <td> <input type="text" placeholder="Late Fees Waive Off" name="late_fees_waive_off_due" class="form-control"><br> </td>
                                                <td><br></td>
                                            </tr>
                                            <tr>
                                            <td> <label class="form-label" for="form1">Less : Old Advance Fees : </label> </td>
                                                <td> <input type="text" placeholder="Less : Old Advance Fees Due" name="old_advance_fees_due" class="form-control"> </td>
                                                <td> <input type="text" name="old_advance_fees_received" placeholder="Less : Old Advance Fees Received" class="form-control"> </td>
                                            </tr>
                                            <tr>
                                            <td> <label class="form-label" for="form1">Sub Total : </label> </td>
                                                <td> <input type="text" disabled name="sub_total_due" id="sub_total_s" name="sub_total_s" class="form-control"> </td>
                                                <td> <input type="text" disabled name="sub_total_received" id="sub_total_s1" name="sub_total_s1" class="form-control"> </td>
                                            </tr>
                                            <tr>
                                                <td> <label class="form-label" for="form1">Late Fees On Posting : </label> </td>
                                                <td> <input type="text" name="late_fees_on_posting_due" placeholder="Late Fees On Posting Due" class="form-control"> </td>
                                                <td> <input type="text" name="late_fees_on_posting_received" placeholder="Late Fees On Posting Received" class="form-control"> </td>
                                            </tr>
                                            <tr>
                                                <td> <label class="form-label" for="form1">Advance : </label> </td>
                                                <td> 
                                                    <!-- <input type="text" name="advance_due" class="form-control">  -->
                                                </td>
                                                <td> <input type="text" disabled name="advance_received" class="form-control"> </td>
                                            </tr>
                                            <tr>
                                                <td> <label class="form-label" for="form1">Grand Total : </label> </td>
                                                <td> <input type="text" disabled id="grand_total_s" name="grand_total_due" class="form-control"> </td>
                                                <td> <input type="text" disabled id="grand_total_s1" name="grand_total_received" class="form-control"> </td>
                                            </tr>
                                            <tr>
                                                <td> <label class="form-label" for="form1">Balance : </label> </td>
                                                <td> <input type="text" disabled id="balance_s" name="balance_due" class="form-control"> </td>
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
                                            <tr>
                                            <td> <label class="form-label" for="form1">Advance Fees Account : </label> </td>
                                                <td colspan="2"> 
                                                    <select name="advance_fees_account" id="">
                                                        <option value=""> -- Select -- </option>
                                                        <option value="ACTIVITY FEES">ACTIVITY FEES</option>
                                                        <option value="ADMISSION FEES">ADMISSION FEES</option>
                                                    </select> 
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    </div>
                                            </div>
                                            </div>
                                </div>
                                <!-- <div class="card-title mb-3 text-end"> <button class="btn btn-success"> Save </button></div> -->
                                </form>
                                <!-- end separator -->
                                   
                                </div>


                            </form>
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>

<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script> -->
<script>

$(document).ready(function(){
    var id = $("#student_id_led").val();
    select_data(id);
});

function select_due_upto(ele){
    var date = ele;
    var lumpsum_fees = 0;
    var siblings = 0;
    if ($('#lumpsum_fees').is(":checked")) {
        lumpsum_fees = $('#lumpsum_fees').val();
    }
    if ($('#siblings').is(":checked")) {
        siblings = $('#siblings').val();
    }
    
    var student_id = $('#student_id_led').val();
    $(".head_table_body").empty();
    $.ajax({
        type : "POST",
        data : {student_id:student_id,date:date},
        url : "{{ url('get_student_fees_struct_view') }}",
        headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        dataType : 'json',
        success: function(data){
            var head_rec_ammount = '';
            // console.log(data.generate_due_chat);
            console.log(data.all_value.by_cash);
            $("input[name='by_cash']").val(data.all_value.by_cash);
            $("input[name='advance_received_prew']").val(data.all_value.advance_received);
            $("input[name='payment_by_select']").val(data.all_value.payment_by_select);
            $("input[name='total_received']").val(data.all_value.total_received);
            $("input[name='balance_f']").val(data.all_value.balance_f);
            $("input[name='late_fees_rate_due']").val(data.all_value.late_fees_rate_due);
            $("input[name='late_fees_rate_received']").val(data.all_value.late_fees_rate_received);
            $("input[name='late_fees_waive_off_due']").val(data.all_value.late_fees_waive_off_due);
            $("input[name='old_advance_fees_due']").val(data.all_value.old_advance_fees_due);
            $("input[name='old_advance_fees_received']").val(data.all_value.old_advance_fees_received);
            $("input[name='sub_total_due']").val(data.all_value.sub_total_due);
            $("input[name='sub_total_received']").val(data.all_value.sub_total_received);
            $("input[name='late_fees_on_posting_due']").val(data.all_value.late_fees_on_posting_due);
            $("input[name='late_fees_on_posting_received']").val(data.all_value.late_fees_on_posting_received);
            $("input[name='advance_received']").val(data.all_value.advance_received);
            $("input[name='grand_total_due']").val(data.all_value.grand_total_due);
            $("input[name='grand_total_received']").val(data.all_value.grand_total_received);
            $("input[name='balance_due']").val(data.all_value.balance_due);
            $("input[name='late_fees_account']").val(data.all_value.late_fees_account);
            $("input[name='advance_fees_account']").val(data.all_value.advance_fees_account);

            if (data.due_chart_data != 0) {
                var i = 0;
                $.each(data.due_chart_data, function(key, value) {
                    var headName = value.head_name;
                    console.log(value.head_due_date);

                    if (value.head_due_date){
                        if (value.head_due_amount == value.head_rec_ammount){
                            var value_tuition = data.generate_due_chat.fees[i];
                            var text_tuition = '';
                            
                            var row = $('<tr>');
                            row.append(
                            $('<td>').text(value.head_name)
                                .append('<input type="hidden" value="' + value.head_name + '" class="form-control head_name" name="head_name[]">')
                                .append('<input type="hidden" value="' + (value_tuition) + '" class="form-control head_due_amount_" id="head_due_amount_'+i+'" name="head_due_amount[]">')
                                .append('<input type="hidden" value="' + value.head_due_date + '" class="form-control head_to_date" name="head_to_date[]">')
                                .append('<input type="hidden" value="' + value.term_str + '" class="form-control term_str" name="term_str[]">')
                                .append('<input type="hidden" value="' + value.head_to_date + '" class="form-control head_due_date" name="head_due_date[]">')
                            );

                            row.append($('<td>').text(value_tuition));
                            row.append($('<td>').html('<input type="text" id="head_rec_ammount_' + i + '" placeholder="received amount" name="head_rec_ammount[]" value="'+value.head_rec_ammount+'" class="form-control head_rec_ammount" disabled="true">'));
                            row.append($('<td>').text(value.head_due_date));
                            row.append($('<td>').text(value.term_str));
                            row.append($('<td>').text(value.head_to_date));
                            $('.head_table_body').append(row);
                        }
                    }
                    
                    
                    i++;
                });
                
                // for (var i = 0; i < data.due_chart_data.head_name.length; i++) {
                    // var row = $('<tr>');
                    // row.append(
                
                    // $('<td>').text(data.due_chart_data.head_name[i])
                    //     .append('<input type="hidden" value="' + data.due_chart_data.head_name[i] + '" class="form-control head_name" name="head_name[]">')
                    //     .append('<input type="hidden" value="' + data.due_chart_data.head_due_amount[i] + '" class="form-control head_due_amount_" id="head_due_amount_'+i+'" name="head_due_amount[]">')
                    //     .append('<input type="hidden" value="' + data.due_chart_data.head_due_date[i] + '" class="form-control head_to_date" name="head_to_date[]">')
                    //     .append('<input type="hidden" value="' + data.due_chart_data.term_str[i] + '" class="form-control term_str" name="term_str[]">')
                    //     .append('<input type="hidden" value="' + data.due_chart_data.head_to_date[i] + '" class="form-control head_due_date" name="head_due_date[]">')
                    // );

                    // row.append($('<td>').text(data.due_chart_data.head_due_amount[i]));
                    // row.append($('<td>').html('<input type="text" id="head_rec_ammount_' + i + '" placeholder="received amount" name="head_rec_ammount[]" value="" class="form-control head_rec_ammount">'));
                    // row.append($('<td>').text(data.due_chart_data.head_due_date[i]));
                    // row.append($('<td>').text(data.due_chart_data.term_str[i]));
                    // row.append($('<td>').text(data.due_chart_data.head_to_date[i]));
                // }
            } else {
                var description_text = '';
                for (var i = 0; i < data.generate_due_chat.account_name.length; i++) {
                    
                    var skipRow = false;
                    if (data.generate_due_chat.due_date[i] <= date){
                        
                        
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
                                var num8 = $("#lumpsum_fees").val();
                                var num9 = $("#siblings").val();
                                var num10 = (parseInt(num8) + parseInt(num9));
                                value_tuition_total = parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition = (parseInt(data.generate_due_chat.fees[i]) - value_tuition_total);
                                text_tuition = '<span class="text-danger">' + data.generate_due_chat.fees[i] + '</span>';
                                description_text = '<span class="text-danger">Origin Amount of TUITION FEES ' + data.generate_due_chat.fees[i] + ' After lumpsum and siblings add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total +'</span>';
                            } else if(('TUITION FEES' == data.generate_due_chat.account_name[i] && lumpsum_fees != 0)){
                                var num10 = $("#lumpsum_fees").val();
                                value_tuition_total = parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition = (parseInt(data.generate_due_chat.fees[i]) - value_tuition_total);
                                text_tuition = '<span class="text-danger">' + data.generate_due_chat.fees[i] + '</span>';
                                description_text = '<span class="text-danger">Origin Amount of TUITION FEES ' + data.generate_due_chat.fees[i] + ' After lumpsum add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total +'</span>';
                            } else if(('TUITION FEES' == data.generate_due_chat.account_name[i] && siblings != 0)){
                                var num10 = $("#siblings").val();
                                value_tuition_total = parseInt(data.generate_due_chat.fees[i]) * ( parseInt(num10) / 100);
                                value_tuition = (parseInt(data.generate_due_chat.fees[i]) - value_tuition_total);
                                text_tuition = '<span class="text-danger">' + data.generate_due_chat.fees[i] + '</span>';
                                description_text = '<span class="text-danger">Origin Amount of TUITION FEES ' + data.generate_due_chat.fees[i] + ' After siblings add the descount '+num10+ '% ' + 'subtraction the amount '+ value_tuition_total +'</span>';
                            }
                            
                            var row = $('<tr>');
                            
                            row.append(
                        
                            $('<td>').text(data.generate_due_chat.account_name[i])
                                .append('<input type="hidden" value="' + data.generate_due_chat.account_name[i] + '" class="form-control head_name" name="head_name[]">')
                                .append('<input type="hidden" value="' + value_tuition + '" class="form-control head_due_amount_" id="head_due_amount_'+i+'" name="head_due_amount[]">')
                                .append('<input type="hidden" value="' + data.generate_due_chat.fees_date[i] + '" class="form-control head_to_date" name="head_to_date[]">')
                                .append('<input type="hidden" value="' + data.generate_due_chat.term[i] + '" class="form-control term_str" name="term_str[]">')
                                .append('<input type="hidden" value="' + data.generate_due_chat.due_date[i] + '" class="form-control head_due_date" name="head_due_date[]">')
                            );
                            // console.log(val_rec);
                            row.append($('<td>').html('<span class="text-success">' + value_tuition + ' </span> ' + text_tuition));
                            row.append($('<td>').html('<input type="text" id="head_rec_ammount_' + i + '" placeholder="received amount" name="head_rec_ammount[]" value="" class="form-control head_rec_ammount" disabled="true">'));
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
            $("input[name='head_due_amount[]']").each(function(){
                if ($(this).val() !== '') {
                    sum_main += parseInt($(this).val());
                    // console.log(parseInt($(this).val()));
                }
            });
    
            $("input[name='total_dueamount']").val(sum_main);
            $("input[name='hidden_total_dueamount']").val(sum_main);
            $("input[name='grand_total_due']").val(sum_main);
            $("input[name='hidden_grand_total_due']").val(sum_main);
            $("input[name='sub_total_due']").val(sum_main);
            $("input[name='hidden_sub_total_due']").val(sum_main);
            // $("input[name='grand_total_received']").val(sum_main);
            var ab = $('.head_due_amount_').length;
            // console.log(ab);

            $('#count_class').val(ab);
        }
    });
}

function select_data(ele){
    student_id = ele;
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
                console.log(data);
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



                var selectTag = $("#due_upto_date");
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
    var date = $("#due_upto_led").val();
    select_due_upto(date);
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
    if (advance_received_prew != null) {
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
                console.log("Insufficient by_cash for subtraction");
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

        var tbl_row = '<tr><td><select class="form-control" name="head_name[]"><option>Select Head</option><?php if(!empty($course_fees_head_orders_list_arr)){ ?><?php foreach($course_fees_head_orders_list_arr as $each){ ?><option><?php echo trim($each->ac_head_name); ?> </option> <?php } } ?> </select> </td> </td><td><input type="text" name="head_due_amount[]" value="" id="head_due_amount_'+cd+'" class="form-control head_due_amount" disabled="true"></td><td><input type="text" name="head_rec_ammount[]" value="" class="form-control disabled" placeholder="recieved ammount"></td><td><input type="date" name="head_to_date[]" value="" class="form-control disabled" placeholder="from ammount"></td><td><select id="term" class="form-control disabled" name="term_str" autocomplete="shipping address-level1" required=""><option value="" disabled="" selected="">Please select</option><option value="1st">1st</option><option value="2nd">2nd</option><option value="3rd">3rd</option><option value="4th">4th</option></select></td><td><input type="date" name="head_due_date[]" value="" class="form-control head_due_date disabled"></td><td><a href="javascript:void(0)" class="remove_head_row_button text-danger remove_btn disabled">remove</a><td/></tr>';

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



</script>
<!-- </div> -->
@endsection