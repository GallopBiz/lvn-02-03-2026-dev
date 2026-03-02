@extends('backend.layouts.main')
@section('main-container')

    <style>
        .uperletter {
            text-transform: capitalize;
        }
    </style>

    @php
        $i = 0;
    @endphp
    <div class="main-content">
        <div class="form_section1_div">
            <div class="breadcrumb">
                <h1 class="me-2">Employee Security Deposit Management</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>
            <div class="card-header">
                <h4>{{ isset($stream_master) ? 'Edit Loan' : 'Create Loan' }}</h4>
            </div>
            @if (!empty($stream_master))
                <form id="progress-form" class="p-4 progress-form" action="{{ url('store-loan') }}" method="post">
                    <input type="hidden"
                        @if (!empty($stream_master)) value=" {{ $stream_master->id }}"
                        @else
                            value="" @endif
                        name="id">
                @else
                    <form id="progress-form" class="p-4 progress-form" action="{{ url('save-loan') }}" method="post">
            @endif

            @csrf
            <div class="row">


                <div class="col-md-3 form-group mb-3">
                    <label for="DepartmentID">Employees </label>

                    <select name="employee_id" class="form-control" id="EmployeeID1">
						<option value=""> -- Please select -- </option>
						@foreach ($employeeName as $employee)
							<option value="{{ $employee->id }}"
								{{ isset($stream_master) && $stream_master->employee_id == $employee->id ? 'selected' : '' }}>
								{{ $employee->first_name }} {{ $employee->last_name }}
							</option>
						@endforeach
					</select>

                    @error('employee_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
				<div class="col-md-3 form-group mb-3">
					<label for="status">Status</label>
					<select class="form-control" name="status" id="status">
						<option value="">-- Select Status --</option>
						<option value="pending" 
							{{ old('status', $stream_master->status ?? '') == 'pending' ? 'selected' : '' }}>
							Pending
						</option>
						<option value="completed" 
							{{ old('status', $stream_master->status ?? '') == 'completed' ? 'selected' : '' }}>
							Completed
						</option>
					</select>
					@error('status')
						<div class="text-danger">{{ $message }}</div>
					@enderror
				</div>



                <div class="col-md-3 form-group mb-3">
                    <label for="loan_amount">Security Deposit Amount</label>
                    <input class="form-control" id="loan_amount" name="loan_amount" type="text"
                        value="{{ isset($stream_master) ? $stream_master->loan_amount : old('loan_amount') }}"
                        placeholder="Loan Amount" onchange="validateAmount(this,'amountError')" />
                    @error('loan_amount')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    <span class="text-danger" id="amountError"></span>
                </div>

                <div class="col-md-3 form-group mb-3" id="duration_field">
					<label for="duration_months">Duration (Months)</label>
					<input class="form-control" id="duration_months" name="duration_months" type="number"
						value="{{ isset($stream_master) ? $stream_master->duration_months : old('duration_months') }}"
						placeholder="Duration (Months)" />
					@error('duration_months')
						<div class="text-danger">{{ $message }}</div>
					@enderror
				</div>

                {{-- <div class="col-md-3 form-group mb-3">
                    <label for="interest_rate">Interest Rate</label>
                    <input class="form-control" id="interest_rate" name="interest_rate" type="text"
                        value="{{ isset($stream_master) ? $stream_master->interest_rate : old('interest_rate') }}"
                        placeholder="Interest Rate" onchange="validateAmount(this,'intRateAmountError')" />
                    @error('interest_rate')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    <span class="text-danger" id="intRateAmountError"></span>
                </div> --}}

                <div class="col-md-3 form-group mb-3">
                    <label for="start_date">Start Date</label>
                    <input class="form-control" id="start_date" name="start_date" type="date"
                        value="{{ isset($stream_master) ? $stream_master->start_date : old('start_date') }}"
                        placeholder="Start Date" />
                    @error('start_date')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3">{{ isset($stream_master) ? $stream_master->description : old('description') }}</textarea>
                    @error('description')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2 form-group mb-3" id="is_active_field">
					<label for="is_active">Is Active Loan</label><br>
					<input id="is_active" type="checkbox" name="is_active" value="1"
						{{ isset($stream_master) && $stream_master->is_active ? 'checked' : '' }} />
					@error('is_active')
						<div class="text-danger">{{ $message }}</div>
					@enderror
				</div>

                <div class="col-md-12">
                    <button type="submit"
                        class="btn btn-primary">{{ isset($stream_master) ? 'Update Loan' : 'Create Loan' }}</button>
                    <button type="button" id="reset" class="btn btn-primary" name="btn"
                        value="Reset Form">Reset</button>

                    @if (request()->route()->getName() !== 'employeeloan')
                        <a href="{{ url('employeeloan') }}" class="btn btn-primary">Add New</a>
                    @endif

                </div>
            </div>
            </form>
            <br>
        </div>

        @if (isset($stream_master))
            <div class="card mt-4">
                <div class="card-header">
                    <h4>EMI Details</h4>
                </div>
                <div class="card-body">
                    @if ($stream_master->emis->isEmpty())
                        <p>No EMIs available for this loan.</p>
                    @else
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>EMI Date</th>
                                    <th>EMI Amount</th>
                                    <th>Principal</th>
                                    {{-- <th>Interest</th> --}}
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stream_master->emis as $emi)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $emi->emi_date }}</td>
                                        <td>{{ number_format($emi->emi_amount, 2) }}</td>
                                        <td>{{ number_format($emi->principal_component, 2) }}</td>
                                        {{-- <td>{{ number_format($emi->interest_component, 2) }}</td> --}}
                                        <td>
                                            <span
                                                class="badge bg-{{ $emi->status == 'paid' ? 'success' : ($emi->status == 'paused' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($emi->status) }}
                                            </span>
                                        </td>
                                        {{-- <td>
                                        @if ($emi->status == 'pending')
                                            <form action="{{ route('pauseEmi', $emi->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-sm">Pause EMI</button>
                                            </form>
                                        @endif
                                    </td> --}}
                                    <td>
                                        @if ($emi->status == 'pending')
                                            <button type="button" class="btn btn-warning btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#pauseEmiModal"
                                                onclick="setEmiDetails({{ $emi->id }}, '{{ number_format($emi->emi_amount, 2, '.', '') }}')">
                                                Pause EMI
                                            </button>
                                        @endif
                                    </td>
                                    
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        @endif
       <!-- Modal for Pause EMI -->
		<div class="modal fade" id="pauseEmiModal" tabindex="-1" aria-labelledby="pauseEmiModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-body">
						<form id="pauseEmiForm" method="POST" action="{{ route('pauseEmi') }}">
							@csrf
							<input type="hidden" name="emi_id" id="emi_id">
						  
								<div class="modal-header">
									<h5 class="modal-title" id="pauseEmiModalLabel">Pause EMI</h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<div class="mb-3">
										<label for="cash_amount" class="form-label">Cash Amount (Optional)</label>
										<input type="text" class="form-control" id="cash_amount" name="cash_amount" placeholder="Enter cash amount if paying manually">
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
									<button type="submit" class="btn btn-warning">Pause EMI</button>
								</div>
						   
						</form>
					</div>
				</div>
			</div>
		</div>

        <div class="separator-breadcrumb border-top"></div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="breadcrumb">
                    <h1 class="me-2">List of Employees Security Deposit :-</h1>
                </div>
                <div class="separator-breadcrumb border-top"></div>

                <div class="card text-start">
                    <div class="card-body">
                        <div class="table-responsive">
							<div class="d-flex justify-content-end mb-3">
								<input type="text" id="searchByName" class="form-control w-25" placeholder="Search by Employee Name">
							</div>
                            <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper"
                                style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Employee Name</th>
                                        <th>Security Deposit Amount</th>
                                        <th>EMI Amount</th>
                                        {{-- <th>Interest Rate</th> --}}
                                        <th>Start Date</th>
                                        <th>Is Active</th>
										<th>Status</th>
                                        <th>Action </th>
                                        <!-- <th>Section</th>
                                                <th>Class Strength</th> -->
                                    </tr>
                                </thead>
                                <tbody>


                                    @if (!empty($stream))
                                        @foreach ($stream as $streams)
                                            <tr>
                                                <td>{{ ++$i }}</td>
                                                <td class= "uperletter">{{ $streams->employee->first_name }}
                                                    {{ $streams->employee->last_name }}</td>
                                                <td>{{ $streams->loan_amount }}</td>
                                                <td>{{ $streams->emi_amount }}</td>
                                                {{-- <td>{{ $streams->interest_rate }}</td> --}}
                                                <td>{{ $streams->start_date }}</td>
                                                <td>{{ $streams->is_active == 1 ? 'Yes' : 'No' }}</td>
												<td>
													@if ($streams->status == 'completed')
														<span class="badge bg-success">Completed</span>
													@else
														<span class="badge bg-warning text-dark">Pending</span>
													@endif
												</td>

                                                <td class='d-flex'>
                                                    <a class="btn btn-primary m-1"
                                                        href="{{ url('view-loan') . '/' . $streams->id }}">Edit</a>
                                                    @csrf
                                                    <a class="btn btn-raised ripple btn-danger m-1"
                                                        href="{{ url('delete-loan') . '/' . $streams->id }}"
                                                        onclick="confirmDelete(event)">Delete</a>
                                                </td>
                                            </tr>
                                            <!-- </?php $i++; ?> -->
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="9" class="text-center">No Data Found</td>
                                        </tr>
                                    @endif
                                </tbody>
                                <tfoot>

                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- end of main-content -->
    </div>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script>
        function confirmDelete(event) {
            event.preventDefault(); // Prevents the default link navigation

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If the user clicks on "Yes, delete it!", navigate to the delete URL
                    window.location.href = event.target.href;
                }
            });
        }

        function validateAmount(input, errorId) {


            // Allow only numbers, including decimal points (e.g. 123, 12.34, 0.56)
            const regex = /^[0-9]*(\.[0-9]{0,2})?$/;
            const value = input.value;
            if (!regex.test(value)) {
                console.log(">>>>>>>");
                // If the value doesn't match the regex, remove the last character
                $("#" + errorId).text("Please enter a valid number (int or decimal).");
                input.setCustomValidity("Please enter a valid number (int or decimal).");
            } else {
                console.log("<<<");
                $("#" + errorId).text("");
                input.setCustomValidity(""); // Reset validity
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            $("#reset").on("click", function() {
                $("#loan_amount").val("");
                $("#emi_amount").val("");
                $("#interest_rate").val("");
                $("#start_date").val("");
                $("#is_active").prop("checked", false);

            });

        })

        function setEmiDetails(emiId, emiAmount) {
        document.getElementById('emi_id').value = emiId;
        document.getElementById('cash_amount').value = emiAmount;
    }

    $(document).ready(function() {
    // When the "Pause EMI" button is clicked
    $('#pauseEmiModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var emiId = button.data('emi-id'); // Extract the EMI ID from data attributes

        // Update the modal's action and the EMI ID field
        var modal = $(this);
        modal.find('#emi_id').val(emiId);
    });

    // When the form is submitted
    $('#pauseEmiForm').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Check if the EMI was paused successfully
                if (response.success) {
                    // Update the EMI status in the table (optional)
                    $('tr').each(function() {
                        var emiId = $(this).data('emi-id');
                        if (emiId == response.emi_id) {
                            $(this).find('.badge').text('Paused').removeClass('bg-secondary').addClass('bg-warning');
                        }
                    });

                    // Close the modal
                    $('#pauseEmiModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr, status, error) {
                // Handle error
                alert('An error occurred while pausing the EMI.');
            }
        });
    });
});


document.addEventListener('DOMContentLoaded', function () {
    function toggleFields(status) {
        const durationField = document.getElementById('duration_field');
        const isActiveField = document.getElementById('is_active_field');
        
        if (status === 'completed') {
            durationField.style.display = 'none';
            isActiveField.style.display = 'none';

            // Remove required from hidden fields
            $('#duration_months').prop('required', false);
            $('#is_active').prop('required', false);

        } else {
            durationField.style.display = 'block';
            isActiveField.style.display = 'block';

            $('#duration_months').prop('required', true);
        }
    }

    const statusDropdown = document.getElementById('status');
    toggleFields(statusDropdown.value);

    statusDropdown.addEventListener('change', function () {
        toggleFields(this.value);
    });
});

document.getElementById('searchByName').addEventListener('keyup', function () {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('#deafult_ordering_table_wrapper tbody tr');

    rows.forEach(row => {
        const employeeName = row.cells[1]?.textContent.toLowerCase(); // Column 1 = Employee Name
        if (employeeName.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});


    </script>



@endsection
