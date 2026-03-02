<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Additional CSS for custom styling */
        
        section.payment-table-cls {
            background: #f8f9fa;
            margin-top: 30px;
            padding: 40px 0;
        }
        .msgprint{
            color: green;
        }
        
        .custom-table {
            display: none;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: -1px 10px 10px #e9ecef;
        }
        
        .custom-table.show {
            display: table;
        }
        
        .custom-table th,
        .custom-table td {
            padding: 10px;
            border: 1px solid #dee2e6;
        }
        
        .custom-table th {
            background-color: #f8f9fa;
            text-align: left;
            /* Removed display: none; */
        }
        
        .custom-table td:first-child,
        .custom-table th:first-child {
            display: table-cell;
        }
        
        .custom-table input[type="checkbox"] {
            margin-right: 5px;
        }
        
        .custom-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .custom-checkbox input[type="checkbox"] {
            display: none;
        }
        
        .custom-checkbox label {
            display: block;
            position: relative;
            padding-left: 25px;
            cursor: pointer;
        }
        
        .custom-checkbox label:before {
            content: "";
            display: block;
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            border: 1px solid #ccc;
            background-color: #fff;
        }
        
        .custom-checkbox input[type="checkbox"]:checked+label:before {
            content: "\2714";
            text-align: center;
            line-height: 18px;
            color: #007bff;
            background-color: #fff;
            border-color: #007bff;
        }
        
        span.danger {
            color: #ff0000;
            font-size: 12px;
        }
        
        .student-info {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            /* Align items to left and right */
        }
        
        .info-item {
            display: flex;
        }
        
        .label {
            font-weight: bold;
            color: #333;
			Font-Size: 22px;
        }
        
        .value {
            margin-left: 10px;
            color: #666;
			font-size:22px;
        }
        
        .payment-table-cls h4,
        .payment-table-cls h3 {
            background: rgb(230, 190, 30);
            padding: 5px 10px 7px;
            margin: 0;
            color: #fff;
            font-size: 20px;
        }
        
        tr.footer {
            background: rgb(255 193 7 / 50%);
			font-size:22px;
        }
        
        tr.footer strong {
            font-size: 22px;
        }
        
        .pay-sum h4 {
            background: #a6a3a3;
        }
        
        .pay-sum tr.footer {
            background: #d6d6d6;
        }
        
        .pay-sum button {
            background: rgb(41, 22, 111);
            width: 100%;
            border: 0;
            padding: 10px;
            font-size: 20px;
            color: #fff;
            box-shadow: 1px 1px 1px rgb(41, 22, 111);
            border-radius: 5px;
            transition: all 100ms;
        }
        
        .pay-sum button:hover {
            box-shadow: 2px 1px 5px rgb(41, 22, 111);
        }
        
        .center-content {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        .custom-input {
            margin-bottom: 10px;
        }
        
        /* Add margin to the top of the input field */
        .custom-input input[type="text"] {
            margin-top: 10px;
        }
		button#changeAmountButton {
			width: 265px;
			font-size: 20px;
		}
		h4.final-pay-section {
			background: #2c1a73;
		}
		table#table3 tr.footer {
			color: green;
			background: aliceblue;
			font-size: 22px;
			font-family: 'Montserrat';
			font-weight: 600;
		}
		td#TotelTermAmount {
			font-weight: 600;
		}
    </style>
     <style>
        /* CSS for receipt styling */
        body {
            /* font-family: Arial, sans-serif; */
            /* font-family: "Times New Roman", Times, serif; */
            margin: 0;
            padding: 0;
        }

        .receipt {
            border: 2px solid #333;
            padding: 10px;
            width: 19cm;
            height: 26.7cm;
            margin: 0 auto;
            overflow: hidden;
            font-size: 12px;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .receipt-header h2 {
            margin: 0;
        }

        .receipt-logo {
            text-align: center;
            margin-bottom: 10px;
        }

        .receipt-logo img {
            max-width: 80px;
        }

        .receipt-details {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .receipt-details p {
            margin: 5px 0;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-size: 14px;
        }

        .receipt-table th,
        .receipt-table td {
            border: 1px solid #333;
            /* padding: 2px; */
            text-align: center;
        }

        .receipt-table th {
            background-color: #f2f2f2;
        }

        .receipt-total {
            text-align: right;
            font-size: 14px;
        }

        .receipt-total p {
            margin: 5px 0;
        }

        .bullet-points {
            margin-top: 5px;
            font-size: 14px;
        }

        .bullet-points p {
            margin-bottom: 5px;
        }

        .bullet-points i {
            color: red;
            font-size: 10px;
        }

        .bus-fees {
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #333;
            width: 100%;
            box-sizing: border-box;
            font-size: 14px;
        }

        .bus-fees p {
            margin: 5px 0;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .receipt {
                width: 19cm;
                height: 26.7cm;
                overflow: hidden;
                page-break-after: always;
            }
        }

    </style>
</head>

<body>

<div class="receipt">
        <div class="d-flex justify-content-center align-items-center">
            <img height="60" src="https://gallopbiz.in/lvn-school/wp-content/uploads/2024/02/LVN-logo.png" alt="Logo">
        </div>

        <div class="receipt-details">
            <div class="d-flex justify-content-between" style="margin-top: 3rem;">
                <span><strong>Student Name:</strong> <span id="student_data_id"> John Doe </span></span>
                <span><strong>Class:</strong> <span id="student_data_class"> </span> </span>
                <span><strong>Payment Date:</strong> <span id="student_data_payment_date"> </span> </span>
            </div>
        </div>
        <table  id="receiptTable" class="receipt-table">
          <thead>
              <tr>
                  <th>S.no.</th>
                  <th>Account Name</th>
                  <th>Term 1</th>
                  <th>Term 2</th>
                  <th>Term 3</th>
                  <th>Term 4</th>
                  <th>Total Amount</th>
              </tr>
          </thead>
          <tbody id="tableBody">
              <tr>
                  <td colspan="6" style="text-align: right">Total</td>
                  <td>9000</td>
              </tr>
          </tbody>
      </table>

      <table class="receipt-table">
        <thead>
            <tr>
                <th>S.no.</th>
                <th>Name</th>
                <th>installment 1</th>
                <th>installment 2</th>
                <th>installment 3</th>                
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody id="bus_table">
            <!-- <tr>
                <td>1</td>
                <td>Bus Fee</td>
                <td>500</td>
                <td>500</td>
                <td>500</td>                
                <td>1500</td>
            </tr>                                             -->
        </tbody>
    </table>
        <div class="receipt-total">
            <p><strong id="total_received_">Total Amount (INR):</strong></p>
        </div>
        <div class="bullet-points">
            <ul>
              <li>The Fee is to be paid as per the schedule furnished in the fee card.</li>
              <li>Activity Fee/Computer Fee is to be paid with the I installment of the Fee.</li>
              <li>Fee once paid under head (other than Caution Money) <u>WILL NOT BE REFUNDABLE</u> in any circumstances.</li>
            </ul>           
        </div>
    </div>
    <div class="container">
        <div class="center-content">
            <form onsubmit="handleShowDetails(event)">
                <div class="custom-input">
                    <input type="text" class="form-control" placeholder="Enter Scholar No." required>
                </div>
                <button type="submit" class="btn btn-primary" id="showDetailsBtn">Show Details</button>
            </form>
        </div>
    </div>
    

    <section class="payment-table-cls" style="display: none;">
        <div class="container">
        <h3>Student Details <span class="divider"></span></h3>
            <div class="row">
                <div class="col-md-12">
                    <div class="student-info">
                        <div class="info-item">
                            <span class="label">Student Name:</span>
                            <span class="value" id="student_id">Jhone thomash</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Scholar No.:</span>
                            <span class="value" id="scholarNumber">231065478</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Class:</span>
                            <span class="value" id="classname">12th</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-5">
                            <h4>Term Payment</h4>
                            <table class="custom-table" id="table1">
                                <!-- Dummy data for Term Payment table -->
                                <thead>
                                    <tr>
                                        <th><span>Select Terms</span></th>
                                        <th>Terms</th>
                                        <th>Amount <span class="danger">Discount</span></th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody id="termbody">
                                    <tr>
                                        <td><input type="checkbox" class="rowCheckbox"></td>
                                        <td>1</td>
                                        <td>100 <span class="danger">+500</span></td>
                                        <td>13/03/2024</td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" class="rowCheckbox"></td>
                                        <td>2</td>
                                        <td>200 <span class="danger">+500</span></td>
                                        <td>14/03/2024</td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" class="rowCheckbox"></td>
                                        <td>3</td>
                                        <td>300 <span class="danger">+500</span></td>
                                        <td>15/03/2024</td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" class="rowCheckbox"></td>
                                        <td>4</td>
                                        <td>400 <span class="danger">+500</span></td>
                                        <td>16/03/2024</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="footer">
                                        <td colspan="2" style="text-align: left;"><strong>Total:</strong></td>
                                        <td colspan="2" style="text-align: right;" id="TotelTermAmount"> 0 </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <h4>Bus Fees</h4>
                            <table class="custom-table" id="table2">
                                <!-- Dummy data for Bus Install table -->
                                <thead>
                                    <tr>
                                        <th>Select Terms</th>
                                        <th>Installments&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                        <th>Amount&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                        {{-- <th>Column C</th> --}}
                                    </tr>
                                </thead>
                                <tbody id="busbody">
                                    <tr>
                                        <td><input type="checkbox" class="rowCheckbox"></td>
                                        <td>1</td>
                                        <td>100</td>
                                        {{-- <td>13/03/2024</td> --}}
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" class="rowCheckbox"></td>
                                        <td>2</td>
                                        <td>200</td>
                                        {{-- <td>14/03/2024</td> --}}
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" class="rowCheckbox"></td>
                                        <td>3</td>
                                        <td>300</td>
                                        {{-- <td>15/03/2024</td> --}}
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" class="rowCheckbox"></td>
                                        <td>4</td>
                                        <td>400</td>
                                        {{-- <td>16/03/2024</td> --}}
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="footer">
                                        <td colspan="2" style="text-align: left;"><strong>Total:</strong></td>
                                        <td colspan="2" style="text-align: right;" id="TotalBusAmount"> 0 </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="col-md-3">
                            <h4 class="final-pay-section">Payment Summary</h4>
                            <table class="custom-table" id="table3">
                                <!-- Dummy data for Payment Summary table -->
                                <thead>
                                    <tr>
                                        <th>Details</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Term Fee</td>
                                        <td id="subtotalTermFee">0</td>
                                    </tr>
                                    <tr>
                                        <td>Bus Fee</td>
                                        <td id="subtotalBusFee">0</td>
                                    </tr>
                                    <tr>
                                        <td>Late Fee <small>(Term fee)</small></td>
                                        <td id="subtotalLateFee">0</td>
                                    </tr>
                                    <tr>
                                        <td id="subtotalDicounttext">Discount <small>(10% only for all tution fees of terms.)</small></td>
                                        <td id="subtotalDicount">0</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="footer">
                                        <td style="text-align: left;"><strong>Total:</strong> </td>
                                        <td style="text-align: right;" id="subTotalAmount"> 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="center-content">
                                <button id="changeAmountButton" class="btn btn-primary pay-sum">Pay</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        
    </section>
    
    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
{{-- <script>
    document.getElementById('showDetailsBtn').addEventListener('click', function() {

        var scholarNumber = document.querySelector('.custom-input input[type="text"]').value;

        // Send AJAX request to fetch student details
        $.ajax({
            // url: '/getStudentDetails',
            url: "{{ url('public-page') }}",

            method: 'get',
            data: { scholarNumber: scholarNumber },
            success: function(response) {
                if (response.error) {
                    alert(response.error);
                } else {
                    // Populate student info section
                    document.querySelector('.student-info .value:nth-of-type(1)').textContent = response.student_name;
                    document.querySelector('.student-info .value:nth-of-type(2)').textContent = response.id;

                    // Populate Term Payment table
                    populateTable('table1', response.term_payments);

                    // Populate Bus Install table
                    populateTable('table2', response.bus_installments);

                    // Populate Payment Summary table
                    populateTable('table3', response.payment_summary);

                    // Show the payment section
                    document.querySelector('.payment-table-cls').style.display = 'block';
                }
            },
            error: function(xhr, status, error) {
                alert('Error fetching student details');
            }
        });
    });

    function populateTable(tableId, rowData) {
        var tableBody = document.getElementById(tableId).querySelector('tbody');
        tableBody.innerHTML = ''; // Clear table body

        rowData.forEach(function(row) {
            var newRow = document.createElement('tr');
            Object.values(row).forEach(function(value) {
                var newCell = document.createElement('td');
                newCell.textContent = value;
                newRow.appendChild(newCell);
            });
            tableBody.appendChild(newRow);
        });
    }
</script> --}}

<script>
    // $('.payment-table-cls').hide();
        var jsonData = null;
        var term_json = null;
        var apidata = null;
        var termdata = null;
        var tutionfee = 0;
        

        var term_checked = [];
        var bus_checked = [false, false, false];
        var busFeeArr = [];

        function handleTerm(e, index){
            const termCheck = e.target.checked;
            term_checked[index] = termCheck;   
            // calculateTotelTerm();   
            // handleCalculateTotalAmount();      
        }

        function calculateTotelTerm(){
            let totalTermFee = 0;
            term_json?.fees.map((item, index)=>{
                if(term_checked[index]==true){
                    totalTermFee += parseInt(item);
                }
            })
            // $("#TotelTermAmount").html(totalTermFee);
            return totalTermFee;
        }


        function handleBusInstallment(e, index){
            const busCheck = e.target.checked;
            bus_checked[index] = busCheck;   
            calculateTotelBus();    
            handleCalculateTotalAmount();  
            
            const selectedValue = event.target.value;
            const hiddenInput = document.getElementById(`hiddenInput_${index}`);
            hiddenInput.value = selectedValue;
            console.log(hiddenInput.value);
        }

        function calculateTotelBus(){
            let totalBusFee = 0;
            busFeeArr.map((item, index)=>{
                if(bus_checked[index]==true){
                    totalBusFee += parseInt(item);
                }
            })
            $("#TotalBusAmount").html(totalBusFee);
            return totalBusFee;
        }



        function handleCalculateTotalAmount(){
            const termFee = parseInt($('#subtotalTermFee').text(), 10);

            const busFee = calculateTotelBus();
            const is_staff = $('#is_staff').val();
            const is_sibling = $('#is_sibling').val();
            
            var tuitionFees = $('#subtotalDicount').text();
            var tuitionFeess = 0;
            var tuitionFeessnext = 0;
            var allSelected = true;
            if (is_staff == "yes"){
                tuitionFeess = tuitionFeess * (50 / 100)
                
            }
            else if(is_sibling == "1"){ //alert("sibling");
                tuitionFeess = tuitionFeess * (10 / 100)
                $('#subtotalDicount').text(tuitionFeessnext);
                if (allSelected) { //alert("lumsum");
                    tuitionFees = tuitionFees * (10 / 100)
                    tuitionFeessnext += tuitionFees;
                    $('#subtotalDicount').text(tuitionFeessnext);
                     $("#subtotalDicounttext").html( "Discount <small>(10% (Siblings) for all tution fees of terms.)");
                   

                }

            } else {
                if (allSelected) { //alert("lumsum");
                    tuitionFees = tuitionFees * (10 / 100)
                    $('#subtotalDicount').text(tuitionFees);
                }
            }

            const totalAmount = (termFee + busFee);
            $('#subtotalDicount').text(tuitionFees);
            // $("#subtotalTermFee").html(termFee);
            $("#subtotalBusFee").html(busFee);
             $("#subtotalLateFee").html(lateFee);
            // $("#subtotalDicount").html(discount);
            if (tuitionFees){
                $("#subTotalAmount").html(totalAmount - tuitionFees);
            } else {
                $("#subTotalAmount").html(totalAmount);
            }
            
        }

        function updateTotal(json_data) {

            var totalAmount = 0;
            var tuitionFees = 0;
            var tuitionFeess = 0;
            var otherFees = 0;
            var checkboxes = document.querySelectorAll('.rowCheckbox_term'); 
            var allSelected = true;

            // Extract tuition fees from the json_data
            var fees = json_data.fees;
            var account_names = json_data.account_name;
            var discountlumsum = json_data.discountlumsum;
             var discountlumsum1 = json_data.discountlumsum1;
              var latefeeamount = 250;
              //alert(latefeeamount);
            var staff_is = $("#is_staff").val();
            var is_sibling = $("#is_sibling").val();
           // var latefeeamount = $("#subtotalLateFee").val();
            if ($('.rowCheckbox_term').is(":checked"))
{
  //alert($('#testa-1:checked').val());
}

            for (var i = 0; i < account_names.length; i++) {
                if (account_names[i] === "TUITION FEES") {
                    tuitionFees += parseInt(fees[i]);
                      //alert($('#testa-0:checked').val());
                } else {
                    otherFees += parseInt(fees[i]);
                }
            }
            var checkall = 1;
            checkboxes.forEach(function(checkbox , index) {
                if (checkbox.checked) { //alert("one");
                    var fee = parseInt(checkbox.dataset.fee);
                    var k = 0;
                    for (var i = 0; i < account_names.length; i++) {
                        if (staff_is == "yes"){
                            if (account_names[i] === "TUITION FEES") {
                                console.log("index: ",index);
                                console.log("k: ",k);
                                
                                if(index == k){
                                    tuitionFeess += parseInt(fees[i]);
                                    console.log("i: ",tuitionFeess);
                                }
                                k++;
                            }
                        }
                        if (is_sibling == "1"){
                            if (account_names[i] === "TUITION FEES") {
                                console.log("index: ",index);
                                console.log("k: ",k);
                                
                                if(index == k){
                                    tuitionFeess += parseInt(fees[i]);
                                    console.log("i: ",tuitionFeess);
                                }
                                k++;
                            }
                        }
                    }
                    totalAmount += fee;
                    if (checkall === 4){
                        allSelected = true;
                    } else {
                        allSelected = false;
                    }
                } else {
                    allSelected = false;
                }
                checkall++;
            });
            // alert(allSelected);
            // If all checkboxes are selected, apply a 10% discount to TUITION FEES
            // if (allSelected) {
            //     tuitionFees = tuitionFees * (10 / 100)

            //     // tuitionFees *= 0.9; // Apply 10% discount
            //     $('#subtotalDicount').text(tuitionFees);
            // }
            const is_staff = $('#is_staff').val();
            if (is_staff == "yes"){
                tuitionFeess = tuitionFeess * (50 / 100)
                $('#subtotalDicount').text(tuitionFeess);

            }else if(is_sibling == "1" && allSelected){ //alert("sibling1");
                $('#subtotalDicount').text(tuitionFeess);
                if (allSelected) { //alert("lumsum1");
                    tuitionFees = tuitionFees * (20 / 100)

                    $('#subtotalDicount').text(tuitionFees);
                      $("#subtotalDicounttext").html( "Discount <small>(20% (Lumsum+Siblings) for all tution fees of terms.)");
                   
                }

            }else if(is_sibling == "1"){ //alert("sibling2");
                  tuitionFees = tuitionFeess * (10 / 100)

                   // $('#subtotalDicount').text(tuitionFees);
                   $("#subtotalDicounttext").html( "Discount <small>(10% (Siblings) for all tution fees of terms.)");
                   
                     

                
            }else {
                if (allSelected) { //alert("lumsum1");
                    tuitionFees = tuitionFees * (10 / 100)
                    $('#subtotalDicount').text(tuitionFees);
                     $("#subtotalDicounttext").html( "Discount <small>(10% (Lumsum) for all tution fees of terms.)");
                }
            }

            
            // Update the total display for other fees
            $('#TotelTermAmount').text(totalAmount);
            $('#subtotalTermFee').text(totalAmount);
            
            const termFee = parseInt($('#subtotalTermFee').text(), 10);

            const busFee = parseInt($('#subtotalBusFee').text(), 10);
            const lateFee = latefeeamount;
            const discount = 0;

            const totalAmount_ = (termFee + busFee + lateFee) - discount;

            // $("#subtotalTermFee").html(termFee);
            $("#subtotalBusFee").html(busFee);
            $("#subtotalLateFee").html(lateFee);
            // $("#subtotalDicount").html(discount);
            if (allSelected) {
                $("#subTotalAmount").html(totalAmount_ - tuitionFees);  
            } else {
                if (is_staff == "yes"){
                    $("#subTotalAmount").html(totalAmount_ - tuitionFeess);
                }else if(is_sibling == "1"){
                    //tuitionFeess = tuitionFeess * (10 / 100)
                //$("#subTotalAmount").html(totalAmount_ - tuitionFees);
                $("#subTotalAmount").html(totalAmount);
                $("#subtotalDicounttext").html( "Discount <small>(10% (Siblings) for all tution fees of terms.)");
                //$("#subTotalAmount").html(totalAmount_);

            }  else {
                    $("#subTotalAmount").html(totalAmount_);
                }
             }

        }



        function handleShowDetails(event){
        event.preventDefault();
        
        var scholarNumber = document.querySelector('.custom-input input[type="text"]').value;
        fetch('{{ route("getStudentDetails") }}?scholarNumber=' + scholarNumber)
        .then(response => response.json())
        .then(data => {
            apidata = data;
            
            if (data.error != null) {
                alert(data.error);
                document.querySelectorAll('.custom-table').forEach(table => {
                    table.style.display = 'none';
                });
                document.querySelector('.payment-table-cls').style.display = 'none';
            } else {
                document.querySelectorAll('.custom-table').forEach(table => {
                    table.style.display = 'block';
                });
                document.querySelector('.payment-table-cls').style.display = 'block';
            }
            
            $('#student_id').text(data.student_name);
            $('#scholarNumber').text(scholarNumber);
            $('#classname').text(data.duechart_data.class_name);
            // var detailsContainer = document.getElementById("detailsContainer");
            // detailsContainer.innerHTML = ""; // Clear previous details
            // console.log(data);
            jsonData = JSON.parse(data.duechart_data.json_str);
            term_json = JSON.parse(jsonData[0].json_str);
            termdata = term_json; //alert(term_json.due_date);
            console.log(data);

            var termFees = {};

            // Iterate over the arrays and calculate total fees for each term
            term_json.term.forEach(function(term, index) {
                // Check if the term already exists in the termFees object
                if (!termFees[term]) {
                    termFees[term] = 0;
                }
                
                // Add fees for the current term to the total
                termFees[term] += parseInt(term_json.fees[index]);
                //alert(termFees[term]);
            });

            // Output total fees for each term
            // console.log("Total fees for each term:");
            let term_htmlstr = '';
            let i =0; let a =0;
            const tutionFeeArr = [];
            term_htmlstr += `<input type="hidden" id="is_staff" value="${data.is_staff}">` ;
            term_htmlstr += `<input type="hidden" id="is_sibling" value="${data.is_sibling}">` ;
            term_htmlstr += `<input type="hidden" id="is_sibling1" value="${data.discountlumsum1}">` ;
             $j=1; 
            Object.keys(termFees).forEach(function(term, i) {
                 if(data.is_sibling == 1){
                        var termFees2 = termFees[term] * (10 / 100);
                         var termFees3 = (termFees[term] - termFees2);
                    }else{
                        var termFees3 = termFees[term];
                        if($j == 1){
                            var termFees2 = 250;
                        }else{
                            var termFees2 = 0;
                        }
                    }
                 
                if(data.discountlumsum1 == "10 on Tuition fee"){ if($j == 1){
                    term_htmlstr += `
                    <tr>
                    <td colspan="4" class="msgprint">"Amount Not Displayed Due to Lump-Sum Payment"</td>
                    </tr>
                `;
                }}else{    

                term_htmlstr += `
                    <tr>
                    <td><div class="checkbox _term"><input type="checkbox" class="rowCheckbox_term" data-fee="${termFees3}" onchange="updateTotal(${JSON.stringify(term_json).replace(/"/g, '&quot;')})" id="testa-${i}" value="${i}"></div></td>

                    <td><input type="hidden" id="data_amount" value="${data.amount}"><input type="hidden" id="hiddenterms${i}" value="${term}">${term}</td>
                    <td><input type="hidden" id="hiddentermFees${i}" value="${termFees3} ">${termFees3} <span class="danger">-${termFees2}</span></td>`; 
                    if(a == 0){ term_htmlstr += `<td><input type="hidden" id="hiddendue_date${a}" value="${term_json.due_date[a]}">${term_json.due_date[a]}</td>`; }
                   else { 
                    term_htmlstr += `
                    <td><input type="hidden" id="hiddendue_date${a}" value="${term_json.due_date[a]}">${term_json.due_date[a]}</td>` 
                } a = (a + 2);
                
                    //<input type="hidden" id="hiddentermFees${i}" value="${termFees[term]}">${termFees3} <span class="danger">+500</span>
                    if(i == 0){
                    term_htmlstr += `
                   
                    </tr>
                `;  
            } 
           } $j++;  });
$(document).ready(function() {
let checkBoxTypes = ["_term","_term","_term"];

checkBoxTypes.forEach((item) => {
  var elements = document.getElementsByClassName("rowCheckbox" + item);
  for (var i = 0; i < elements.length; i++) {
    elements[i].onclick = function() {
      setCheckedBoxes(this.id, i);
    }
  }
});


function setCheckedBoxes(object, allChecks) {
  let objSplit = object.split('-');
  for (var i = 0; i <= allChecks; i++) { //alert(objSplit[1]);
    document.getElementById(objSplit[1] + '-' + i).checked = (i <= objSplit[1]);
    //document.getElementById(testa +'-'+ 1).checked = (iz <= 2);
    //testa-1
  }
}
});

            // tutionFeeArr.map((item, index)=>{
            //     term_checked.push(false);
                
            // })
            $("#termbody").html(term_htmlstr);


            let busFee = apidata.bus_fees || 0;
            let bus_htmlstr = '';
            let busInstallmentFee
            if (busFee === 18000 || busFee === 15000) {
                busInstallmentFee = parseInt(busFee) / 3;
                busFeeArr = [busInstallmentFee, busInstallmentFee, busInstallmentFee];
            } else if (busFee === 12000 || busFee === 10000) {
                busInstallmentFee = parseInt(busFee) / 2;
                busFeeArr = [busInstallmentFee, busInstallmentFee];
            } else {
                busInstallmentFee = parseInt(busFee) / 1;
                busFeeArr = [busInstallmentFee];
            }

            busFeeArr.map((item, index) => {
                bus_htmlstr += `
                    <tr>
                        <td><input type="checkbox" onchange="handleBusInstallment(event, ${index})" class="rowCheckbox"></td>
                        <td><input type="hidden" id="hiddenInput_indexbus${index}" value="${index + 1}">${index + 1}</td>
                        <td><input type="hidden" id="hiddenInput_busInstallmentFee${index}" value="${busInstallmentFee}">${busInstallmentFee}</td>
                    </tr>
                `;
            });

               
            $("#busbody").html(bus_htmlstr);

            // tutionfee = 0;
            // tutionFeeArr.map((item, index)=>{
            //     if(item == "TUITION FEES"){
            //         tutionfee += parseInt(termdata.fees[index]);
            //     }
            // })
            // console.log(tutionfee);

            // Display fetched details
            var detailsList = document.createElement("ul");
            for (var key in data) {
                if (key !== 'id' && key !== 'amount' && key !== 'duechart_data') { // Don't display ID and Amount
                    var listItem = document.createElement("li");
                    if (key == 'bus_fees') {
                        listItem.innerHTML = "<strong> Bus Fees :</strong> " + data[key];
                    } else {
                        listItem.innerHTML = data[key];
                    }
                    
                    detailsList.appendChild(listItem);
                }
            }
            detailsContainer.appendChild(detailsList);
            $("#changeAmountButton").show();
            $("#businstallmentcontainer").show();
            $("#busfee_payble_container").show();
            $("#terms_payble_container").show();
            $("#discount_container").show();

            // Set student ID in hidden input
            document.getElementById("studentId").value = data.id;

            // Show the checkboxes
            document.getElementById("checkboxContainer").classList.remove("hidden");

            // Calculate total amount minus bus fees
            // var totalAmount = parseFloat(data.amount) - parseFloat(data.bus_fees);
            var totalAmount = data.amount;
            // Display total amount
            document.getElementById("totalAmountContainer").innerHTML = "<strong>Total Amount:</strong> " + totalAmount;

            // Update term checkboxes and amounts
            updateTermCheckboxes(jsonData);

            // Show installment amounts
            handleChangeInstallmentSelect();
        })
        .catch(error => {
            console.error('Error:', error);
        });


        

        // Send AJAX request to fetch student details
        // $.ajax({
        //     url: '/getStudentDetails',
        //     method: 'GET',
        //     data: { scholarNumber: scholarNumber },
        //     success: function(response) {
        //         if (response.error) {
        //             alert(response.error);
        //         } else {
        //             // Populate student info section
        //             document.querySelector('.student-info .value:nth-of-type(1)').textContent = response.student_name;
        //             document.querySelector('.student-info .value:nth-of-type(2)').textContent = response.id;

        //             // Populate Term Payment table
        //             populateTable('table1', response.term_payments);

        //             // Populate Bus Install table
        //             populateTable('table2', response.bus_installments);

        //             // Populate Payment Summary table
        //             populateTable('table3', response.payment_summary);

        //             // Show the payment section
        //             document.querySelector('.payment-table-cls').style.display = 'block';
        //         }
        //     },
        //     error: function(xhr, status, error) {
        //         alert('Error fetching student details: ' + error);
        //     }
        // });
    }

    function populateTable(tableId, rowData) {
        var tableBody = document.getElementById(tableId).querySelector('tbody');
        tableBody.innerHTML = ''; // Clear table body

        rowData.forEach(function(row) {
            var newRow = document.createElement('tr');
            Object.values(row).forEach(function(value) {
                var newCell = document.createElement('td');
                newCell.textContent = value;
                newRow.appendChild(newCell);
            });
            tableBody.appendChild(newRow);
        });
    }


</script>

<script>
    $(document).ready(function() {
        $('.receipt').hide();
        function createRazorpayInstance(amount) {
            return new Razorpay({
                key: 'rzp_live_83HaVBhlz35Pwg', 
                // key: 'rzp_test_JOC0wRKpLH1cVW',
                amount: amount * 100, // Amount in paise (convert to the smallest currency unit)
                name: 'LVN School'
                , prefill: {
                    name: 'name'
                    , email: ''
                }
                , handler: function(response) {
                    // Handle the Razorpay success callback here
                    // console.log("Payment successful:", response);

                    // Declare arrays to store values
                    const hiddenInput_indexbusArray = [];
                    const hiddenInput_busInstallmentFeeArray = [];

                    // Loop through checkboxes
                    document.querySelectorAll('.rowCheckbox').forEach((checkbox, index) => {
                        if (checkbox.checked) {
                            // Get values
                            const hiddenInput_index = document.getElementById(`hiddenInput_indexbus${index}`).value;
                            const hiddenInput_busInstallmentFee = document.getElementById(`hiddenInput_busInstallmentFee${index}`).value;
                            
                            // Push values into arrays
                            hiddenInput_indexbusArray.push(hiddenInput_index);
                            hiddenInput_busInstallmentFeeArray.push(hiddenInput_busInstallmentFee);
                        }
                    });

                    // Log arrays outside the loop
                    console.log("hiddenInput_indexbus :", hiddenInput_indexbusArray);
                    console.log("hiddenInput_busInstallmentFee :", hiddenInput_busInstallmentFeeArray);

                    // Declare arrays to store values
                    const hiddentermsArray = [];
                    const hiddentermFeesArray = [];
                    const hiddendue_dateArray = [];

                    // Loop through checkboxes
                    document.querySelectorAll('.rowCheckbox_term').forEach((checkbox, index) => {
                        if (checkbox.checked) { //alert("all");
                            // Get values
                            const hiddenterms = document.getElementById(`hiddenterms${index}`).value;
                            const hiddentermFees = document.getElementById(`hiddentermFees${index}`).value;
                            const hiddendue_date = document.getElementById(`hiddendue_date${index}`).value;

                            // Push values into arrays
                            hiddentermsArray.push(hiddenterms);
                            hiddentermFeesArray.push(hiddentermFees);
                            hiddendue_dateArray.push(hiddendue_date);
                        }
                    });
                    const data_amount = document.getElementById(`data_amount`).value;

                    // Log arrays outside the loop
                    const TotalBusAmount = $('#TotalBusAmount').text();
                    const feestotalamount = $('#TotelTermAmount').text();
                    const student_id = $('#student_id').text();
                    const scholarNumber = $('#scholarNumber').text();
                    const classname = $('#classname').text();
                    const subTotalAmount_ = $('#subTotalAmount').text();
                    const subtotalTermFee = $('#subtotalTermFee').text();
                    const subtotalDicount = $('#subtotalDicount').text();
                    const payment_id = response.razorpay_payment_id;
                    $.ajax({
                        url: "{{url('save_feesreceipt_challan_public')}}",
                        type: "POST",
                        data: {
                            student_id: scholarNumber,
                            hiddenInput_indexbus: hiddenInput_indexbusArray,
                            hiddenInput_busInstallmentFee: hiddenInput_busInstallmentFeeArray,
                            hiddentermsArray:hiddentermsArray,
                            data_amount:data_amount,
                            TotelTermAmount:subTotalAmount_,
                            feestotalamount:feestotalamount,
                            TotalBusAmount:TotalBusAmount,
                            subtotalTermFee:subtotalTermFee,
                            subtotalDicount:subtotalDicount,
                            payment_id:payment_id,
                            _token: '{{csrf_token()}}'
                        },
                        dataType: 'json',
                        success: function (result) {

                            var bus_data = result.bus_data;
                            console.log(result.bus_data);
                            var data = JSON.parse(result.student_data.str_json);
                            // Function to calculate total amount
                            function calculateTotal(bus_data) {
                                var total = 0;
                                $.each(bus_data, function(index, bus) {
                                    total += parseInt(bus.busInstallmentFee);
                                });
                                return total;
                            }


                            // Populate tbody with rows
                            var tbody = $('#bus_table');
                            tbody.empty(); // Clear existing content if any
                            // Create a single row with total amount
                            var totalAmount = calculateTotal(bus_data);
                            var row = $('<tr>').append(
                                $('<td>').text('1'), // S.no.
                                $('<td>').text('Bus Fees'),
                                $('<td>').text(bus_data[0] ? bus_data[0].busInstallmentFee : ''),
                                $('<td>').text(bus_data[1] ? bus_data[1].busInstallmentFee : ''),
                                $('<td>').text(bus_data[2] ? bus_data[2].busInstallmentFee : ''),
                                $('<td>').text(data.TotalBusAmount)
                            );
                            tbody.append(row);

                            

                            // Parse the data
                            var accountNames = data.head_name.split(',');
                            var dueAmounts = data.head_due_amount.split(',');
                            var termStr = data.term_str.split(','); 

                            // Get the table body
                            var tableBody = document.getElementById('tableBody');


                            // Clear existing rows
                            tableBody.innerHTML = '';

                            // Populate the table
                            var processedAccountNames = [];

                            // Populate the table
                            for (var i = 0; i < accountNames.length; i++) {
                                // Check if the account name is already processed
                                if (processedAccountNames.includes(accountNames[i])) {
                                    continue; // Skip if already processed
                                }

                                // Add the account name to the processed list
                                processedAccountNames.push(accountNames[i]);

                                var tr = document.createElement('tr');
                                var sno = i + 1;
                                var accountName = accountNames[i];
                                var terms = ['', '', '', ''];
                                var totalAmount = 0;

                                // Loop through the data to find terms and calculate total amount
                                for (var j = 0; j < termStr.length; j++) {
                                    if (accountNames[j] === accountName) {
                                        var termIndex = terms.findIndex(term => term === ''); // Find empty term slot
                                        terms[termIndex] = termStr[j];
                                        totalAmount += dueAmounts[j] ? parseInt(dueAmounts[j]) : 0;
                                    }
                                }

                                tr.innerHTML = `
                                    <td>${sno}</td>
                                    <td>${accountName}</td>
                                    <td>${terms[0]}</td>
                                    <td>${terms[1]}</td>
                                    <td>${terms[2]}</td>
                                    <td>${terms[3]}</td>
                                    <td>${data.feestotalamount}</td>
                                `;

                                tableBody.appendChild(tr);
                            }





                            $('#total_received_').text('Total Amount (INR): '+data.total_received);
                            $('#student_data_id').text(result.student_data.name_student);
                            $('#student_data_class').text(result.student_data.class_name);
                            $('#student_data_payment_date').text(result.student_data.payment_date);
                            $('.payment-table-cls').hide();
                            $('.container').hide();
                            $('.receipt').show();
                            // payment-table-cls
                        }
                    });

                    $("#razorpay_payment_id").val(response.razorpay_payment_id);
                    // $("#termSelect_select").val(termSelect_);
                    // $("form").submit();
                    // You can redirect or perform other actions after successful payment
                }
            });
        }
        // Attach a click event listener to the "Change Amount" button
        $("#changeAmountButton").on("click", function() {
            var final_amount = document.getElementById("subTotalAmount").textContent;
            // Open Razorpay payment form with the new amount
            rzp = createRazorpayInstance(final_amount);
            rzp.open();
        });
    })

</script>


</body>

</html>
