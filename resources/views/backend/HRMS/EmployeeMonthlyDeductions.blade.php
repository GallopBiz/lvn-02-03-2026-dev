@extends('backend.layouts.main')
@section('main-container')

    <style>
        .uperletter {
            text-transform: capitalize;
        }
		.bg-light {
    --bs-bg-opacity: 1;
    background-color: rgb(169 190 233) !important;
}
    </style>

    @php
        $i = 0;
    @endphp
    <div class="main-content">
        <div class="breadcrumb d-flex justify-content-between">
            <h1 class="me-2">Manage Employee Deductions</h1>
            <a href="{{ route('basicDeduction') }}" class="btn btn-primary">
                Back
            </a>
        </div>
        <form method="GET" action="">
            <div class="form-group d-flex align-items-end gap-2">
                <div>
                    <label for="month">Select Month:</label>
                    <select name="month" id="month" class="form-control mb-2" required>
                        @php
                            $monthNames = [
                                '01' => 'January',
                                '02' => 'February',
                                '03' => 'March',
                                '04' => 'April',
                                '05' => 'May',
                                '06' => 'June',
                                '07' => 'July',
                                '08' => 'August',
                                '09' => 'September',
                                '10' => 'October',
                                '11' => 'November',
                                '12' => 'December',
                            ];
                        @endphp
                        @foreach ($years as $year)
                            @foreach ($monthNames as $num => $name)
                                <option value="{{ $year }}-{{ $num }}" {{ $selectedMonth == $year.'-'.$num ? 'selected' : '' }}>{{ $name }} {{ $year }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary mb-2">Show</button>
                </div>
            </div>
        </form>
    
        {{-- <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Employee</th>
                    @foreach ($employees->first()->employeeDeductions as $deduction)
                        <th>{{ $deduction->basicDeduction->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $employee)
                    <tr>
                        <td class="uperletter">{{ $employee->first_name }} {{ $employee->last_name }}</td>
                        @foreach ($employee->employeeDeductions as $deduction)
                            <td>
                                <input type="number" 
                                    class="form-control deduction-input"
                                    data-employee-id="{{ $employee->id }}" 
                                    data-deduction-id="{{ $deduction->basicDeduction->id }}" 
                                    value="{{ old('deductions.' . $employee->id . '.' . $deduction->basicDeduction->id, $deduction->manual_amount ?? $deduction->basicDeduction->amount) }}">
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table> --}}
		   @php
			// Initialize totals array
			$totals = [];
			foreach ($uniqueDeductions as $deductionId => $deductionName) {
				$totals[$deductionId] = 0;
			}
			@endphp
		<table class="table table-bordered">
				<thead>
					<tr>
						<th>Employee</th>
						@foreach ($uniqueDeductions as $deductionId => $deductionName)
							<th>{{ $deductionName }}</th>
						@endforeach
					</tr>
				</thead>
				<tbody>
					@foreach ($employees as $employee)
						<tr>
							<td class="uperletter">{{ $employee->first_name }} {{ $employee->last_name }}</td>

							@foreach ($uniqueDeductions as $deductionId => $deductionName)
								@php
									$deduction = $employee->employeeDeductions->firstWhere('basicDeduction.id', $deductionId);
									$amount = $deduction ? ($deduction->manual_amount ?? $deduction->basicDeduction->amount) : 0;

									// Add to total
									$totals[$deductionId] += $amount;
								@endphp

								<td>
									<input type="number" 
										class="form-control deduction-input"
										data-employee-id="{{ $employee->id }}" 
										data-deduction-id="{{ $deductionId }}" 
										value="{{ old('deductions.' . $employee->id . '.' . $deductionId, $amount) }}">
								</td>
							@endforeach
						</tr>
					@endforeach
				</tbody>

				<tfoot class="bg-light font-weight-bold">
					<tr>
						<td>Total</td>
						@foreach ($uniqueDeductions as $deductionId => $deductionName)
							<td>₹{{ number_format($totals[$deductionId], 2) }}</td>
						@endforeach
					</tr>
				</tfoot>
			</table>
	</div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Set CSRF token for all AJAX requests (required for Laravel POST)
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        function validateAmount(input) {
        const regex = /^[0-9]*(\.[0-9]{0,2})?$/;
        const value = input.value;
        if (!regex.test(value)) {
            console.log(">>>>>>>");
            $("#amountError").text("Please enter a valid number (int or decimal).");
            input.setCustomValidity("Please enter a valid number (int or decimal).");
        } else {
            console.log("<<<");
            $("#amountError").text("");
            input.setCustomValidity(""); 
        }
    }
    

$(document).ready(function() {
    $("#month").on("change", function() {
        var selectedMonth = $(this).val();

        $.ajax({
            url: "{{ route('deductions.fetch') }}",
            type: "GET",
            data: { month: selectedMonth },
            success: function(response) {
                $("tbody").html(response.html);
            }
        });
    });

    
    $(document).on("change", ".deduction-input", function() {
        var employeeId = $(this).data("employee-id");
        var deductionId = $(this).data("deduction-id");
        var amount = $(this).val();
        var month = $("#month").val();

        $.ajax({
            url: "{{ route('deductions.update') }}",
            type: "POST",
            data: {
                employee_id: employeeId,
                deduction_id: deductionId,
                amount: amount,
                month: month,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (!response.success) {
                    alert(response.message || "Failed to update deduction.");
                }
            },
            error: function(xhr, status, error) {
                let msg = "Failed to update deduction.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    });
});
</script>




@endsection
