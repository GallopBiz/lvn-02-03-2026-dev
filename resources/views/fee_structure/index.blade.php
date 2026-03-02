<!DOCTYPE html>
<html>
<head>
    <title>Fee Structure</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<style>
    /* General Page Styling */
    body {
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f4f4f9;
        color: #333;
    }

    h1, h2, h3 {
        color: #2c3e50;
        margin-bottom: 10px;
        margin-top: 10px;
    }

    h1 {
        text-align: center;
    }

    form {
        margin-bottom: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
    }

    form input[type="text"] {
        padding: 10px;
        font-size: 16px;
        border-radius: 8px;
        border: 1px solid #ccc;
        width: 100%;
        max-width: 400px;
        margin-bottom: 10px;
    }

    form button {
        padding: 10px 20px;
        font-size: 16px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
        max-width: 200px;
    }

    form button:hover {
        background-color: #2980b9;
    }

    /* Error Message Styling */
    p {
        text-align: center;
        font-size: 16px;
    }

    /* Table Styling */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        background-color: white;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    thead {
        background-color: #3498db;
        color: white;
    }

    thead th {
        padding: 15px;
        text-align: left;
        font-size: 16px;
        border-bottom: 1px solid #ddd;
    }

    tbody tr {
        border-bottom: 1px solid #ddd;
    }

    tbody td {
        padding: 15px;
        text-align: left;
        font-size: 14px;
    }

    tbody tr:nth-child(even) {
        background-color: #f4f4f9;
    }

    /* Highlighted Columns */
    .highlight-1 {
        background-color: #b6a61c;
        color: #fff;
        font-weight: bold;
        text-align: center;
    }

    .highlight-2 {
        background-color: #f9f9f9;
    }

    .highlight-3 {
        background-color: #f1c40f;
        color: white;
    }

    .highlight-4 {
        background-color: #e74c3c;
        color: white;
    }

    /* Checkbox Styling */
    .fee-checkbox {
        transform: scale(1.2);
        cursor: pointer;
    }

    /* Button Styling */
    button#submitFeesButton {
        display: block;
        margin: 20px auto;
        padding: 15px 30px;
        background-color: #27ae60;
        color: white;
        font-size: 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
        max-width: 250px;
    }

    button#submitFeesButton:hover {
        background-color: #2ecc71;
    }

    /* Total Section */
    #totalAmount {
        color: #27ae60;
        font-size: 18px;
        font-weight: bold;
        text-align: center;
        display: block;
        margin: 10px 0;
    }

    .pd-left {
        display: inline-block;
        padding-left: 10px;
    }

    .remaining_cls {
        display: flex;
        align-items: center;
    }

    .remaining_cls span {
        display: inline-block;
        padding-left: 10px;
    }
    .index_page {
    max-width: 1000px;
    margin: auto;
}
.index_page form button {
    margin-left: 10px;
    margin-bottom: 10px;
}

.lum-button{
	text-align: center;
}
.lum-button1{
    color: #fff;
    font-size: 14px;
    margin: 10px;
	padding: 10px;
	background-color: green;
}

.reset-lump{ 
	color: #fff;
    font-size: 14px;
    margin: 10px;
	padding: 10px;
	background-color: #e74c3c;
	border-radius: 10px;
    border: 0px;
	}


@keyframes blink {
    0% { opacity: 1; }
    50% { opacity: 0; }
    100% { opacity: 1; }
}

.blink-text {
    color: red; /* Adjust color as needed */
    font-weight: bold;
    animation: blink 1s infinite;
}

/* Media Query for Smaller Screens */
@media (max-width: 768px) {
    th, td {
        font-size: 14px; /* Adjust font size for smaller screens */
        padding: 8px;
    }
    .table-responsive {
    overflow-x: auto;
}
}
</style>

<body>
    <section class="index_page">
    <h1>Fee Structure</h1>
    <!-- Search form -->
    <form action="{{ route('fee-structure.search') }}" method="POST">
        @csrf
        <input type="text" name="scholar_id" placeholder="Enter Scholar ID" value="{{ $student->scholar_no ?? '' }}" required>
		<button type="submit">Search</button>
    </form>
    
    @if(isset($remainingAmount) && $remainingAmount>0)
    <p><strong>Remaining Amount From Previous Session:</strong> ₹{{ number_format($remainingAmount, 2) }}</p>
    <p> <a href="https://school.lvnindore.org.in/custom-payment?previouspending=true&scholar_id={{ $student->scholar_no }}" />PAY NOW</a></p>
    @else 
        @if(session('error'))
            <p style="color:red;">{{ session('error') }}</p>
        @endif

        @if(isset($fee_structure))
            <h2>Student Name: {{ $student->student_name }} </h2>
            <h3> Scholar No. : {{ $student->scholar_no }}</h3>
            @foreach($fee_structure as $fee)
                <h3>Class: {{ $fee->class_name }} </h3>
            @endforeach
			
			 @php
				$currentDate = \Carbon\Carbon::now();
				$cutOffDate = \Carbon\Carbon::createFromDate($currentDate->year, 6, 30)->endOfDay();
			@endphp

			@if($currentDate->lessThanOrEqualTo($cutOffDate))
				@if($record_found_feesreciept_challan == 0)
					<p class="blink-text">Get 10% Off on Tuition Fees When You Pay Lump-Sum!</p>
					<div class="lum-button" style="align-items: center;">
						<form action="{{ route('fee-structure.search') }}" method="POST" style="display: inline;">
							@csrf
							<input type="hidden" name="scholar_id" value="{{ $student->scholar_no ?? '' }}" required>
							<input type="hidden" name="lum" value="1" required>
							<button type="submit" class="lum-button1">Pay Lump-Sum</button>
						</form>
						<form action="{{ route('fee-structure.search') }}" method="POST" style="display: inline;">
							@csrf
							<input type="hidden" name="scholar_id" value="{{ $student->scholar_no ?? '' }}" required>
							<button type="submit" class="lum-button1">Reset</button>
						</form>
					</div>
				@endif
			@endif


            
			<div class="table-responsive">
				<table>
					<thead>
						<tr>
							<th>Select Term</th>
							<th>Account Name</th>
							<th>Term</th>
							<th>Fees</th>
							<th class="highlight-4">Due Date</th>
							<th class="highlight-3">Fees Paid</th>
							<th class="highlight-1">Remaining Amount</th>
						</tr>
					</thead>
					<tbody>
						@foreach($fee_final_data as $key => $fee)
							@if(($fee['remaining_amount'] ?? $fee['fees']) > 0 && stripos($fee['account_name'] ?? '', 'bus') === false)
							<tr>
								<td>
									@if(($fee['remaining_amount'] ?? $fee['fees']) > 0)
										<input type="checkbox" class="fee-checkbox" data-term="{{ $fee['term'] }}" 
											data-account-name="{{ $fee['account_name'] }}" 
											data-allocated-amount="{{ $fee['allocatedAmount'] ?? 0 }}" 
											data-remaining-amount="{{ $fee['remaining_amount'] ?? $fee['fees'] }}" 
											data-late-fee="{{ $fee['late_fee'] ?? 0 }}"
											data-discount="{{ $fee['percentage'] ?? 0 }}"
											value="{{ $fee['remaining_amount'] ?? $fee['fees'] }}"
                                            @if($lum_checked ==1 && $record_found_feesreciept_challan==0)
                                                checked="true"
                                                disabled="disabled"
                                            @endif
                                            >
									@endif
								</td>
								<td>{{ $fee['account_name'] ?? '' }}</td>
								<td>{{ $fee['term'] ?? '' }} </td>
								@if(($fee['dis_applies_flag'] ?? -1) > 0)
								<td class="highlight-2"><s>{{ $fee['orig_fees'] ?? "" }}</s> {{ $fee['fees'] ?? "" }} <span>({{ $fee['percentage'] }}% Discount)</span></td>
								@elseif(($fee['late_fee_flag'] ?? -1) > 0)
								<td class="highlight-2"><s>{{ $fee['orig_fees'] ?? "" }}</s> {{ $fee['fees'] ?? "" }} <span>({{ $fee['late_fee'] }} Late Fee )</span></td>
								@else
								<td class="highlight-2">{{ $fee['fees'] ?? "" }}</td>
								@endif
								<td class="highlight-4">{{ $fee['due_date'] ?? '' }}</td>
								<td class="highlight-3">{{ $fee['allocatedAmount'] ?? '0' }}</td>
								<td class="highlight-1">{{ $fee['remaining_amount'] ?? $fee['fees'] }}</td>
							</tr>
							@endif
						@endforeach
					</tbody>
				</table>
			</div>
			
			<!-- Separate Table for BUS FEES -->
			
			
	@php
    $busFees = collect($fee_final_data)->firstWhere('account_name', 'BUS FEES');
    $hasBusFees = $busFees && ($busFees['remaining_amount'] ?? $busFees['fees']) > 0;
@endphp

@if($hasBusFees)
    @php
        $totalBusFees = $busFees['fees']; // Total bus fee
        $remainingAmount = $busFees['remaining_amount'] ?? $totalBusFees; // Jo abhi tak bachi hai
        $paidAmount = $totalBusFees - $remainingAmount; // Jo pay ho chuki hai

        // Discount ke baad ka final amount calculate karein
        $discount = $busFees['percentage'] ?? 0;
        $totalBusFeesAfterDiscount = $totalBusFees - (($totalBusFees * $discount) / 100);
        $remainingAmountAfterDiscount = $busFees['remaining_amount'] ?? $totalBusFeesAfterDiscount;
        $paidAmountAfterDiscount = $totalBusFeesAfterDiscount - $remainingAmountAfterDiscount;

        // Divide fees into 3 equal installments
        $installmentAmount = round($totalBusFeesAfterDiscount / 3, 2);
        $installments = [$installmentAmount, $installmentAmount, $installmentAmount];

        // Remove paid installments dynamically
        $remainingInstallments = [];
        foreach ($installments as $installment) {
            if ($paidAmountAfterDiscount >= $installment) {
                $paidAmountAfterDiscount -= $installment;
            } else {
                $remainingInstallments[] = $installment;
            }
        }
    @endphp
    <h3>Bus Fees</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Select Term</th>
                    <th>Term</th>
                    <th>Fees</th>
                    <th class="highlight-4">Due Date</th>
                    <th class="highlight-3">Fees Paid</th>
                    <th class="highlight-1">Remaining Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($remainingInstallments as $index => $installment)
                    <tr>
                        <td>
                            <input type="checkbox" class="fee-checkbox" 
                                data-term="1st"
                                data-account-name="BUS FEES"
                                data-allocated-amount="{{ $busFees['allocatedAmount'] ?? 0 }}" 
                                data-remaining-amount="{{ $installment }}" 
                                data-late-fee="{{ $busFees['late_fee'] ?? 0 }}"
                                data-discount="{{ $busFees['percentage'] ?? 0 }}"
                                value="{{ $installment }}">
                        </td>
                        <td>Term</td>
                        <td class="highlight-2">{{ $installment }}</td>
                        <td class="highlight-4">{{ $busFees['due_date'] ?? '' }}</td>
                        <td class="highlight-3">{{ $busFees['allocatedAmount'] ?? '0' }}</td>
                        <td class="highlight-1">{{ $installment }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<!-- Total section -->
<h3 class="remaining_cls">Total Fees: <span id="totalAmount">0</span></h3>
<button id="submitFeesButton">Submit Fees</button>
<input type="hidden" id="studentId" value="{{ $student->id }}">
<input type="hidden" id="lumsum" value="{{ $lum_checked }}" />
        @endif
    @endif
</section>
    <!-- JavaScript to calculate total -->
    <script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
         $(document).ready(function() {
            $('.fee-checkbox').change(function() {
                let total = 0;
                $('.fee-checkbox:checked').each(function() {
                    total += parseFloat($(this).val());
                });
                $('#totalAmount').text(total.toFixed(2)); // Update total
            });
            checkTotal();
        });
        function checkTotal(){
                let total = 0;
                $('.fee-checkbox:checked').each(function() {
                    total += parseFloat($(this).val());
                });
                $('#totalAmount').text(total.toFixed(2)); // Update total
            }
    </script>



    <script>
        function createRazorpayInstance(amount,transaction_id) {
                if(amount<=0){
                    alert("Please select Term/Fee");
                }
                var scholar_id = jQuery("#scholar_id").val();
                return new Razorpay({
                    key: 'rzp_live_83HaVBhlz35Pwg', 
					//key: 'rzp_test_JOC0wRKpLH1cVW',
                    amount: amount * 100, // Amount in paise (convert to the smallest currency unit)
                    name: 'LVN School',
                    notes: {
                        "scholar_no": scholar_id,
                        "transaction_id": transaction_id
                    },
					method: {
						netbanking: false,
						card: false,
						wallet: false,
						upi: true
					},
                    prefill: {
                        name: 'name'
                        , email: ''
                    }
                    , handler: function(response) {
                        console.log(response);
                        $.ajax({
                            url: "{{ route('payment.success') }}",
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_signature: response.razorpay_signature,
                                transaction_id: transaction_id // Pass the unique transaction ID
                            },
                            success: function (response) {
                                alert(response.message);
                                // Redirect to a success page or update the UI
                                window.location.href = '/thank-you?txn_id='+transaction_id;
                            },
                            error: function (xhr) {
                                alert('Payment verification failed!');
                            }
                        });
                    }
                });
            }

        $(document).ready(function() {
            $('#submitFeesButton').click(function() {
                let selectedFees = {}; // To store the selected fees
                let studentId = $('#studentId').val(); // Assuming you have an input for student ID
                let total_fee = 0;
                let final_amount = document.getElementById("totalAmount").textContent;
                let lumsum =  $('#lumsum').val();
                // Loop through checked checkboxes and build the selected fees array
                var atLeastOneIsChecked = $('.fee-checkbox').eq(0).is(':checked');
                if(!atLeastOneIsChecked){
                    alert("Please select "+$('.fee-checkbox').eq(0).data("account-name")+" "+$('.fee-checkbox').eq(0).data("term"));
                    return;
                }
                
                $('.fee-checkbox:checked').each(function() {
                    let feeTerm = $(this).data('term');
                    let key = feeTerm + "_" + $(this).data('account-name');
					let accountName = $(this).data('account-name');
					let amount = parseFloat($(this).val());

					if (accountName === "BUS FEES") {
						if (selectedFees[key]) {
							// Add if BUS FEES already exists
							selectedFees[key].fees += amount;
							selectedFees[key].allocatedAmount += parseFloat($(this).data('allocated-amount')) || 0;
							selectedFees[key].remaining_amount += parseFloat($(this).data('remaining-amount')) || 0;
							selectedFees[key].discount += parseFloat($(this).data('discount')) || 0;
							selectedFees[key].late_fee += parseFloat($(this).data('late-fee')) || 0;
						} else {
							// Create if BUS FEES does not exist yet
							selectedFees[key] = {
								account_name: accountName,
								fee_term: feeTerm,
								fees: amount,
								allocatedAmount: parseFloat($(this).data('allocated-amount')) || 0,
								remaining_amount: parseFloat($(this).data('remaining-amount')) || 0,
								discount: parseFloat($(this).data('discount')) || 0,
								late_fee: parseFloat($(this).data('late-fee')) || 0
							};
						}
					} else {
						// For all other accounts → override
						selectedFees[key] = {
							account_name: accountName,
							fee_term: feeTerm,
							fees: amount,
							allocatedAmount: parseFloat($(this).data('allocated-amount')) || 0,
							remaining_amount: parseFloat($(this).data('remaining-amount')) || 0,
							discount: parseFloat($(this).data('discount')) || 0,
							late_fee: parseFloat($(this).data('late-fee')) || 0
						};
					}

                    total_fee += parseFloat($(this).val());
                });
                if(final_amount!=total_fee){
                    alert("Some error");
                    return;
                }
                // Send the data to the server via AJAX
                $.ajax({
                    url: "{{ route('store-fees-before-payment') }}", // Laravel route to handle the storing of fees
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}', // CSRF token
                        student_id: studentId,
                        fees: selectedFees,
                        lumsum: lumsum
                    },
                    success: function(response) {
                        // Optionally redirect to the payment gateway
                        rzp = createRazorpayInstance(final_amount,response.transaction_id);
                        rzp.open();
                    },
                    error: function(response) {
                        alert('Error: ' + response.responseJSON.message);
                    }
                });
            });
        });

    </script>
</body>
</html>
