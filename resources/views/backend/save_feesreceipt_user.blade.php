@extends('backend.layouts.main')

{{-- <//?php echo $todate ?> --}}
{{-- </?php echo $fromdate ?> --}}

@section('main-container')

<style type="text/css">
    .validation_err {
        color: red !important;
    }

    input[type="number"] {
        appearance: textfield;
        -webkit-appearance: textfield;
        -moz-appearance: textfield;
    }

    input {
        position: relative;
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

    tr.disabled {
        background-color: #8080806b;
    }

    .badge {
        display: inline-block;
        min-width: 10px;
        padding: 3px 7px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        background-color: #777;
        border-radius: 10px;
    }

</style>
<meta name="csrf-token" content="{{ csrf_token() }}" />

<!-- {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}} -->

<div class="main-content pt-4">

    <div class="breadcrumb">
        <h1>Fees Details</h1>
        <ul>
        </ul>
    </div>
    <div class="separator-breadcrumb border-top"></div>
    <div class="row">
        <div class="col-md-12">


            <div class="card mb-4">
                <div class="card-body p-0">


                    <input id="sform_number" class="d-none" type="text" value="{{Auth::user()->form_number}}">
                    

                    <div class="row">

                        <div class="separator-breadcrumb"></div>
                        <div class="col-lg-3">
                            <!-- <div class="ul-product-detail__image"><img src="http://localhost/lvn-school/public/assets/frontend/images/logo.png" alt="" /></div> -->
                            <div class="card-body text-center">
                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="avatar box-shadow-2 mb-3" style="border-radius:50%; height:150px; width:150px"><img src="http://localhost/lvn-school/public/assets/backend/images/student.png" alt=""></div>
                                </div>
                                <h5 class="m-0" id="name_student_get1"></h5>
                                <p class="mt-0">Class - <span id="name_class_get1"></span></p>
                                <!-- <button class="btn btn-primary btn-rounded">Contact Jassica</button> -->
                                <div class="card-socials-simple mt-4">
                                    <div class="table-responsive">
                                        <table class="table table-striped" style="font-size: small;">
                                            <thead>
                                            </thead>
                                            <tbody>
                                                <input type="hidden" name="mobile_number" id="mobile_number" value="">
                                                <tr>
                                                    <th>Name : </th>
                                                    <td><span name="student_name" id="name_student_get"></span></td>
                                                </tr>
                                                {{-- <tr>
                                                    <th>Father's Name : </th>
                                                    <td><span name="" id="name_father_get"></span></td>
                                                </tr> --}}
                                                <tr>
                                                    <th>Class Name : </th>
                                                    <td><span name="class_name" id="name_class_get"></span></td>
                                                </tr>
                                                {{-- <tr>
                                                    <th>Adm Dt : </th>
                                                    <td><span id="name_adm_get"></span></td>
                                                </tr> --}}
                                                {{-- <tr>
                                                    <th>Serial No : </th>
                                                    <td><span id="name_formno_get"></span></td>
                                                </tr> --}}
                                                <tr>
                                                    <th>Scholar No : </th>
                                                    <td><span name="scholar_no" id="name_scholarno_geti"></span></td>
                                                </tr>

                                                <tr>
                                                    <th>Session : </th>
                                                    <td><span name="session_name" id="session_name"></span></td>
                                                </tr>


                                                <tr>
                                                    <th>Form number : </th>
                                                    <td><span name="form_number" id="form_number"></span></td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-9">


                            <div class="card text-left">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-3">Fee Information</h4>

                                        <!-- <form action="{{url('save_feesreceipt_user')}}" class="progress-form" novalidate method="POST"> -->
                                            <!-- {{ csrf_field() }} -->
                                            <!-- <input type="hidden" id="_token" name="_token" value="{{ csrf_token() }}"> -->
                                            <input type="hidden" name="student_id" value="{{ $data['id'] }}">
                                            <input type="hidden" id="class_name" name="class_name" value="">
                                            <input type="hidden" name="due_upto" value="{{ $data['id'] }}">
                                            <div class="row col-12 p-4">
                                                <div class="term-dropdown col-6">
                                                    <label for="termSelect">Select Term:</label>
                                                    <select id="termSelect" class="form-control" name="term">
                                                        <option value="" selected><?php print_r($data['result']['termSelect_select'])?></option>
                                                        
                                                    </select>
                                                    <div class="mt-3">
                                                        <div>Pay Amount :
                                                            <input type="text" name="total_dueamount" id="total_dueamount" value="<?php print_r($data['result']['hidden_total_dueamount'])?>" class="form-control">
                                                            <!-- <input type="hidden" name="hidden_total_dueamount" id="hidden_total_dueamount" value=""><br> -->
                                                            <!-- <input type="submit" value="Order" class="btn btn-success"> -->
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="table-responsive mt-3 col-6">
                                                    <table class="table table-bordered head_table">
                                                        <tr>
                                                            <!-- <td>Sr</td> -->
                                                            <th>Head Name</th>
                                                            <th>Due Ammount</th>
                                                        </tr>
                                                        <tbody class="head_table_bodya">
                                                            <?php 
                                                            if(!empty($data['result'])) { 
                                                                for($i=0; $i < count($data['result']['headname']); $i++ ) { ?>
                                                                <tr>
                                                                    <td><?php print_r($data['result']['headname'][$i])?></td>
                                                                    <td><?php print_r($data['result']['headfees'][$i])?></td>
                                                                </tr>
                                                            <?php } ?>
                                                            <tr>
                                                            <td>Total</td>
                                                            <td><?php print_r($data['result']['hidden_total_dueamount'])?></td>
                                                            </tr>
                                                        <?php } ?>
                                                        </tbody>

                                                    </table>
                                                </div>


                                            </div>
                                        <!-- </form> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @endsection
    <script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script><script>
    </script>
            <script>
                $(document).ready(function() {
                    
                    let isContentVisible = false;

                    $('#moreBtn').click(function() {
                        $('#head_names').show();
                        $('#moreBtn').hide();
                        $('#lessBtn').show();
                    });

                    $('#lessBtn').click(function() {
                        $('#head_names').hide();
                        $('#moreBtn').show();
                        $('#lessBtn').hide();
                    });

                    function changeTerm() {
                        var selectedTerm = $("#termSelect").val();
                        // var student_id = '1324';
                        var form_number = $("#sform_number").val();
                        // var student_id = '1324';
                        let _token = $("_token").val();


                        $.ajax({
                            type: "POST"
                            , data: {
                                form_number: form_number
                            }
                            , url: "{{ url('search-due-fee-student') }}"
                            , headers: {
                                'X-CSRF-TOKEN': _token
                            }
                            , dataType: 'json'
                            , success: function(data) {
                                if (data && data.length > 0) {
                                    let data_json = JSON.parse(data[0].json_str)
                                    let json_str = JSON.parse(data_json[0].json_str)

                                    // console.log(data);
                                    // console.log(data_json);
                                    // console.log(json_str);


                                    const headNames = json_str.account_name;
                                    const headFees = json_str.fees;
                                    const headTerms = json_str.term;
                                    
                                    // headNames.forEach(elem => {
                                    //     $("#head_names").append(`<span class="badge badge-secondary mt-1 ms-1">${elem}</span>`);
                                    // });

                                    let tbodystr = '';
                                    let totalAmount = 0;
                                    for (let i = 0; i < headNames.length; i++) {
                                        if(headTerms[i]<=selectedTerm){
                                            totalAmount += parseInt(headFees[i]);
                                            tbodystr += `<tr>
                                                    <td><input type="hidden" name="headname[]" value="${headNames[i]}" >${headNames[i]}</td>
                                                    <td><input type="hidden" name="headfees[]" value="${headFees[i]}" >${headFees[i]}</td>
                                                </tr>`;
                                        }
                                    }

                                    $("#total_dueamount").val(totalAmount);
                                    $("#hidden_total_dueamount").val(totalAmount);


                                    var initialAmount = 1000; // Initial amount
                                    var newAmount = parseInt(totalAmount); // New amount
                                    var rzp = createRazorpayInstance(initialAmount); // Initialize with the initial amount

                                    // Function to create the Razorpay instance
                                    function createRazorpayInstance(amount) {
                                        return new Razorpay({
                                            key: 'rzp_test_JOC0wRKpLH1cVW',
                                            amount: amount * 100, // Amount in paise (convert to the smallest currency unit)
                                            name: 'LVN Schoole',
                                            prefill: {
                                                name: 'name',
                                                email: 'rohit@gmail.com'
                                            },
                                            handler: function (response) {
                                                // Handle the Razorpay success callback here
                                                console.log("Payment successful:", response.razorpay_payment_id);
                                                $("#razorpay_payment_id").val(response.razorpay_payment_id);

                                                $("form").submit();
                                                // You can redirect or perform other actions after successful payment
                                            }
                                        });
                                    }

                                    // Attach a click event listener to the "Change Amount" button
                                    $("#changeAmountButton").on("click", function () {
                                        // Open Razorpay payment form with the new amount
                                        rzp = createRazorpayInstance(newAmount);
                                        rzp.open();
                                    });

                                    let tableFooter = (tbodystr!='') ? `<tr>
                                        <th>Total amount</th>
                                        <th><input type="hidden" name="totalamount" value="${totalAmount}" >${totalAmount}</th>
                                        </tr>` : '';
                                                                        
                                    $(".head_table_body").html(`${tbodystr} ${tableFooter}`);                                  

                                }
                            }
                        });
                    }

                    var form_number = $("#sform_number").val();
                    var _token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        type: "POST", 
                        url: "{{ url('student-details') }}",
                        data: {
                            student_id: form_number,
                             _token: _token
                        }, 
                        success: function(data) {
                           if(data){
                            var json_str = JSON.parse(data.json_str);
                                console.log(json_str);
                                $("#mobile_number").val(json_str.mobile_number);
                                $("#name_student_get1").html(data.student_name);
                                $("#name_class_get1").html(data.class_name);
                                $("#name_student_get").html(data.student_name);
                                $("#name_class_get").html(data.class_name);
                                $("#session_name").html(data.session_name);
                                $("#form_number").html(data.form_number);
                                $("#name_scholarno_geti").html(data.scholar_no);
                                $("#class_name").val(data.class_name);
                                // $("#name_adm_get").html(data.adm_dt);
                                $("#name_scholarno_geti").html(data.formno);
                           }
                        }
                        , error: function(xhr, status, error) {
                            console.log(xhr.responseText);
                        }
                    });

                    $('#termSelect').change(function() {
                        const selectedTerm = $('#termSelect').val();
                        // alert("Selected Term: " + selectedTerm);
                        changeTerm();
                        // You can perform further actions with the selected term here.
                    });

                });

            </script>
