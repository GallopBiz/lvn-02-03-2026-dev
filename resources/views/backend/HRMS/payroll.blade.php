@extends('backend.layouts.main')
@section('main-container')
    @php
        $i = 0;
    @endphp
    <div class="main-content">

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="breadcrumb">
                    <h1 class="me-2">Generate Payroll</h1>
                </div>
                <div class="container-flex mt-3">
                    <div class="row align-items-center mb-4">
                        <!-- Month and Year selection (Left side) -->
                        <div class="col-md-6 d-flex align-items-center mb-3 mb-md-0">
                            <div class="me-3">
                                <label for="month" class="form-label">Month:</label>
                                <select id="month" name="month" class="form-select">
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>

                            <div>
                                <label for="year" class="form-label">Year:</label>
                                <select id="payroll_year" name="year" class="form-select    ">
                                    <option value="2023">2023</option>
                                    <option value="2024">2024</option>
                                    <option value="2025">2025</option>
                                    <option value="2026">2026</option>
                                    <!-- Add more years as needed -->
                                </select>
                            </div>
							<!-- ✅ ADD HERE -->
							<div class="ms-3 mt-4">
								<label>
									<input type="checkbox" id="skip_late_coming" value="1">
									Skip Late Coming
								</label>
							</div>
                        </div>

                        <!-- Generate Payroll button (Right side) -->
                        <div class="col-md-6 d-flex justify-content-end">
                            <a href="{{ route('payroll.attendance.configuration') }}" class="btn btn-outline-secondary me-2">
                                Attendance Settings
                            </a>
                            <button id="generatePayrollButton" class="btn btn-primary" style="display: none;">Generate
                                Payroll</button>
                        </div>
                    </div>
                </div>
                <div id="ErrorAlert" class="alert alert-danger mt-2 d-none error-alert"></div>
                <div class="separator-breadcrumb border-top"></div>

                <div class="card text-start">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display table table-striped table-bordered" id="zero_configuration_table"
                                style="width: 100%">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select_all_employees"></th>
                                        <th>Sr.</th>
                                        <th>Employee ID</th>
                                        <th>Employee Name</th>
                                        <th>Gross Amount</th>
                                        {{-- <th>Action </th> --}}
                                    </tr>
                                </thead>
                                <tbody>


                                    @if (!empty($stream))
                                        @foreach ($stream as $streams)
                                            <tr>
                                                <td><input type="checkbox" class="employee-checkbox"
                                                        data-id="{{ $streams->id }}"></td>
                                                <td>{{ ++$i }}</td>
                                                <td>{{ $streams->id }}</td>
                                                <td class= "uperletter">{{ $streams->first_name }}
                                                    {{ $streams->last_name }}</td>
                                                <td>
                                                    @if ($streams->salaries->isNotEmpty())
                                                        {{ $streams->salaries[0]->basic_salary }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td class='d-flex'>
                                                    {{-- <form action="{{ route('payroll.calculate', $streams->id) }}"
                                                        class="payroll-form" method="POST">
                                                        @csrf
                                                        <div>
                                                            <input type="number" value="" hidden name="month">
                                                            <input type="text" value="" hidden name="year">
                                                            <input type="text" value="{{ $streams->id }}" hidden
                                                                name="employee_ids[]">
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Generate
                                                            Payroll</button>
                                                    </form> --}}
                                                    <form class="payroll-form"
                                                        data-action="{{ route('payroll.calculate', $streams->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <input type="hidden" name="month" value="">
                                                        <input type="hidden" name="year" value="">
                                                        <input type="hidden" name="employee_ids[]"
                                                            value="{{ $streams->id }}">

                                                        <button type="submit" class="btn btn-primary generate-payroll-btn">
                                                            Generate Payroll
                                                        </button>

                                                        <!-- Loader (Initially Hidden) -->
                                                        <div class="spinner-border text-primary d-none" role="status">
                                                            <span class="sr-only">Loading...</span>
                                                        </div>
                                                    </form>
                                                    
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script>
        jQuery(document).ready(function($) {
            // Initialize Select2
            // $('#inq-form-nomenu').select2();
            $('.deductions').select2();
        });

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

        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#employeeTable').DataTable({
                paging: true, // Enable pagination
                select: {
                    style: 'multi' // Enable multi-selection of employees
                }
            });

            // Handle the "Select All" checkbox for pagination
            $('#select_all_employees').on('click', function() {
                var isChecked = this.checked;
                $('.employee-checkbox').prop('checked',
                    isChecked); // Select/Deselect all checkboxes on the page
                toggleGenerateButton();
            });

            // Update the "Generate Payroll" button visibility based on selected employees
            $('.employee-checkbox').on('change', function() {
                toggleGenerateButton();
            });

            // Function to toggle the visibility of the "Generate Payroll" button
            function toggleGenerateButton() {
                var selectedCount = $('.employee-checkbox:checked').length;
                if (selectedCount > 0) {
                    $('#generatePayrollButton').show();
                } else {
                    $('#generatePayrollButton').hide();
                }
            }

            // When the "Generate Payroll" button is clicked
            $('#generatePayrollButton').on('click', function() {
                var selectedEmployeeIds = [];
                $('.employee-checkbox:checked').each(function() {
                    selectedEmployeeIds.push($(this).data(
                        'id')); // Get the employee id from the data-id attribute
                });

                // Fetch month and year
                var month = $('#month').val();
                var year = $('#payroll_year').val();
				var skipLateComing = $('#skip_late_coming').is(':checked') ? 1 : 0;
                // Send the selected employee IDs, month, and year to the server
                $.ajax({
                    url: '{{ route('payroll.generate') }}', // Replace with the actual route
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        employee_ids: selectedEmployeeIds,
                        month: month,
                        year: year,
						skip_late_coming: skipLateComing   // ✅ added
                    },
                    success: function (response, status, xhr) {
                // Extract filename from Content-Disposition header
                let filename = xhr.getResponseHeader("Content-Disposition")
                    .split("filename=")[1]
                    .replaceAll('"', ''); // Remove quotes if present

                let blob = new Blob([response], { type: "text/csv" });
                let link = document.createElement("a");

                link.href = window.URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                // Reset UI after download
                button.prop("disabled", false);
                loader.addClass("d-none");
            },
                    error: function(xhr, status, error) {
                        let errorMessage = 'Error generating payroll!';
                        
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        
                        // Show error in the alert div
                        $('#ErrorAlert').removeClass('d-none').html(errorMessage);
                        
                        // Scroll to the alert
                        $('html, body').animate({
                            scrollTop: $("#ErrorAlert").offset().top - 100
                        }, 500);
                    }
                });
            });

            $(".payroll-form").submit(function(e) {
                e.preventDefault(); // Prevent default form submission

                let form = $(this);
                let formData = form.serialize();
                let actionUrl = form.data("action");

                let button = form.find(".generate-payroll-btn");
                let loader = form.find(".spinner-border");
                let errorAlert =    $("#ErrorAlert");// form.siblings(".error-alert");

                // Show loading spinner & disable button
                button.prop("disabled", true);
                loader.removeClass("d-none");

                $.ajax({
            url: actionUrl,
            type: "POST",
            data: formData,
            // xhrFields: {
            //     responseType: 'blob' // Important for handling binary files
            // },
            success: function (response, status, xhr) {
                // Extract filename from Content-Disposition header
                let filename = xhr.getResponseHeader("Content-Disposition")
                    .split("filename=")[1]
                    .replaceAll('"', ''); // Remove quotes if present

                let blob = new Blob([response], { type: "text/csv" });
                let link = document.createElement("a");

                link.href = window.URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                // Reset UI after download
                button.prop("disabled", false);
                loader.addClass("d-none");
            },
            error: function (xhr, status, error) {
                button.prop("disabled", false);
                loader.addClass("d-none");
                
                let errorMessage = "An error occurred while generating payroll. Please try again.";
                
                // Get error message from response
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                // Display error in alert
                errorAlert.removeClass("d-none").html(errorMessage);
                $('html, body').animate({ scrollTop: errorAlert.offset().top - 100 }, 500);
            }
        });
            });


        });

        document.addEventListener('DOMContentLoaded', function() {
            const monthDropdown = document.getElementById('month');
            const yearDropdown = document.getElementById('payroll_year');
            const payrollForms = document.querySelectorAll('.payroll-form');

            payrollForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    // Set the month and year values dynamically
                    const month = monthDropdown.value;
                    const year = yearDropdown.value;

                    form.querySelector('input[name="month"]').value = month;
                    form.querySelector('input[name="year"]').value = year;
                });
            });
        });
    </script>



@endsection
