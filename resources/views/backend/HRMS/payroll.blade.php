@extends('backend.layouts.main')
@section('main-container')
    @php
        $i = 0;
    @endphp
    <style>
        #skipLateComingPanel {
            background: #ffffff;
            border: 1px solid #e2e8f0 !important;
            border-left: 4px solid #663399 !important;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .exemption-radio-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            transition: all 0.2s ease;
            user-select: none;
        }
        .exemption-radio-pill:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }
        .exemption-radio-pill input[type="radio"]:checked + span {
            color: #663399;
        }
        .exemption-radio-pill:has(input[type="radio"]:checked) {
            background: #f3e8ff;
            border-color: #a855f7;
            color: #6b21a8;
            box-shadow: 0 1px 3px rgba(168, 85, 247, 0.2);
        }
        /* Select2 tags inside skipLateComingPanel */
        #skipLateComingPanel .select2-container--default .select2-selection--multiple {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            min-height: 38px;
            background-color: #ffffff;
            padding: 2px 4px;
        }
        #skipLateComingPanel .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #663399;
            box-shadow: 0 0 0 3px rgba(102, 51, 153, 0.12);
        }
        #skipLateComingPanel .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #f3e8ff !important;
            color: #6b21a8 !important;
            border: 1px solid #d8b4fe !important;
            border-radius: 4px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            padding: 2px 8px !important;
            margin-top: 4px !important;
        }
        #skipLateComingPanel .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #9333ea !important;
            margin-right: 4px !important;
            font-weight: bold !important;
        }
        #skipLateComingPanel .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #dc2626 !important;
        }
    </style>
    <div class="main-content">

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="breadcrumb">
                    <h1 class="me-2">Generate Payroll</h1>
                </div>
                <div class="container-flex mt-3">
                    <div class="row align-items-center mb-3">
                        <!-- Month and Year selection (Left side) -->
                        <div class="col-md-7 d-flex align-items-center flex-wrap gap-3">
                            <div>
                                <label for="month" class="form-label mb-1 font-weight-bold small text-muted">Month:</label>
                                <select id="month" name="month" class="form-select form-select-sm">
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
                                <label for="year" class="form-label mb-1 font-weight-bold small text-muted">Year:</label>
                                <select id="payroll_year" name="year" class="form-select form-select-sm">
                                    <option value="2023">2023</option>
                                    <option value="2024">2024</option>
                                    <option value="2025">2025</option>
                                    <option value="2026">2026</option>
                                    <!-- Add more years as needed -->
                                </select>
                            </div>

                            <div class="pt-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="skip_late_coming" value="1">
                                    <label class="form-check-label font-weight-bold text-dark small" for="skip_late_coming">
                                        <i class="fa fa-clock-o text-warning me-1"></i> Skip Late Coming
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Generate Payroll button (Right side) -->
                        <div class="col-md-5 d-flex justify-content-end align-items-center">
                            <a href="{{ route('payroll.attendance.configuration') }}" class="btn btn-outline-secondary btn-sm me-2">
                                Attendance Settings
                            </a>
                            <button id="generatePayrollButton" class="btn btn-primary btn-sm" style="display: none;">Generate
                                Payroll</button>
                        </div>
                    </div>

                    <!-- Skip Late Coming Options Card -->
                    <div id="skipLateComingPanel" class="card mb-3" style="display: none;">
                        <div class="card-body py-3 px-3">
                            <div class="row align-items-center g-3">
                                <div class="col-md-5">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge px-2 py-1 font-weight-bold me-2" style="background-color: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; border-radius: 4px; font-size: 11px;">
                                            <i class="fa fa-sliders me-1"></i> Exemption Scope
                                        </span>
                                        <span class="small font-weight-bold text-dark">Late Coming Rule</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <label class="exemption-radio-pill mb-0" for="skip_type_entire_month">
                                            <input class="form-check-input me-1" type="radio" name="skip_late_coming_type" id="skip_type_entire_month" value="entire_month" checked>
                                            <span>Entire Month</span>
                                        </label>
                                        <label class="exemption-radio-pill mb-0" for="skip_type_specific_dates">
                                            <input class="form-check-input me-1" type="radio" name="skip_late_coming_type" id="skip_type_specific_dates" value="specific_dates">
                                            <span>Particular Dates</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-7" id="specificDatesContainer" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="skip_late_coming_dates" class="form-label small font-weight-bold text-dark mb-0">
                                            <i class="fa fa-calendar me-1" style="color: #663399;"></i> Select Dates to Exempt:
                                        </label>
                                        <div>
                                            <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 me-1" id="selectAllDatesBtn" style="font-size: 11px; border-radius: 4px;">
                                                <i class="fa fa-check-square-o me-1"></i>Select All
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2" id="clearAllDatesBtn" style="font-size: 11px; border-radius: 4px;">
                                                <i class="fa fa-times me-1"></i>Clear
                                            </button>
                                        </div>
                                    </div>
                                    <select id="skip_late_coming_dates" name="skip_late_coming_dates[]" class="form-control form-control-sm select2" multiple="multiple" style="width: 100%;" data-placeholder="Choose specific dates in selected month...">
                                    </select>
                                    <div class="text-muted d-flex align-items-center mt-1" style="font-size: 11px;">
                                        <i class="fa fa-info-circle text-info me-1"></i>
                                        <span>Only late-coming penalties on these selected dates will be waived. All other rules remain active.</span>
                                    </div>
                                </div>
                            </div>
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

            function updateMonthDates() {
                var month = parseInt($('#month').val(), 10);
                var year = parseInt($('#payroll_year').val(), 10);
                if (!month || !year) return;

                var daysInMonth = new Date(year, month, 0).getDate();
                var select = $('#skip_late_coming_dates');
                var currentVals = select.val() || [];

                select.empty();
                var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                var dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

                for (var d = 1; d <= daysInMonth; d++) {
                    var dateObj = new Date(year, month - 1, d);
                    var dayStr = d < 10 ? '0' + d : d;
                    var monthStr = month < 10 ? '0' + month : month;
                    var dateVal = year + '-' + monthStr + '-' + dayStr;
                    var dayName = dayNames[dateObj.getDay()];
                    var text = dayStr + ' ' + monthNames[month - 1] + ' ' + year + ' (' + dayName + ')';

                    var isSelected = currentVals.indexOf(dateVal) !== -1;
                    var option = new Option(text, dateVal, isSelected, isSelected);
                    select.append(option);
                }

                if ($.fn.select2) {
                    select.select2({
                        placeholder: 'Choose specific dates in selected month...',
                        allowClear: true,
                        width: '100%'
                    });
                }
            }

            $('#skip_late_coming').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#skipLateComingPanel').slideDown(200);
                    updateMonthDates();
                } else {
                    $('#skipLateComingPanel').slideUp(200);
                }
            });

            $('input[name="skip_late_coming_type"]').on('change', function() {
                if ($(this).val() === 'specific_dates') {
                    $('#specificDatesContainer').slideDown(200);
                    if ($.fn.select2) {
                        $('#skip_late_coming_dates').select2({
                            placeholder: 'Choose specific dates in selected month...',
                            allowClear: true,
                            width: '100%'
                        });
                    }
                } else {
                    $('#specificDatesContainer').slideUp(200);
                }
            });

            $('#month, #payroll_year').on('change', function() {
                updateMonthDates();
            });

            $('#selectAllDatesBtn').on('click', function(e) {
                e.preventDefault();
                $('#skip_late_coming_dates > option').prop('selected', true);
                $('#skip_late_coming_dates').trigger('change');
            });

            $('#clearAllDatesBtn').on('click', function(e) {
                e.preventDefault();
                $('#skip_late_coming_dates > option').prop('selected', false);
                $('#skip_late_coming_dates').trigger('change');
            });

            // When the "Generate Payroll" button is clicked
            $('#generatePayrollButton').on('click', function() {
                var selectedEmployeeIds = [];
                $('.employee-checkbox:checked').each(function() {
                    selectedEmployeeIds.push($(this).data('id'));
                });

                // Fetch month, year, and skip late coming settings
                var month = $('#month').val();
                var year = $('#payroll_year').val();
                var skipLateComing = $('#skip_late_coming').is(':checked') ? 1 : 0;
                var skipLateComingType = $('input[name="skip_late_coming_type"]:checked').val() || 'entire_month';
                var skipLateComingDates = $('#skip_late_coming_dates').val() || [];

                var button = $(this);
                button.prop("disabled", true);

                // Send the selected employee IDs, month, and year to the server
                $.ajax({
                    url: '{{ route('payroll.generate') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        employee_ids: selectedEmployeeIds,
                        month: month,
                        year: year,
                        skip_late_coming: skipLateComing,
                        skip_late_coming_type: skipLateComingType,
                        skip_late_coming_dates: skipLateComingDates
                    },
                    success: function (response, status, xhr) {
                        button.prop("disabled", false);
                        let disposition = xhr.getResponseHeader("Content-Disposition");
                        let filename = (disposition && disposition.indexOf("filename=") !== -1)
                            ? disposition.split("filename=")[1].replaceAll('"', '')
                            : 'payroll_' + year + '_' + month + '.csv';

                        let blob = new Blob([response], { type: "text/csv" });
                        let link = document.createElement("a");

                        link.href = window.URL.createObjectURL(blob);
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    },
                    error: function(xhr, status, error) {
                        button.prop("disabled", false);
                        let errorMessage = 'Error generating payroll!';
                        
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        
                        $('#ErrorAlert').removeClass('d-none').html(errorMessage);
                        $('html, body').animate({
                            scrollTop: $("#ErrorAlert").offset().top - 100
                        }, 500);
                    }
                });
            });

            $(".payroll-form").submit(function(e) {
                e.preventDefault(); // Prevent default form submission

                let form = $(this);
                let actionUrl = form.data("action");

                let button = form.find(".generate-payroll-btn");
                let loader = form.find(".spinner-border");
                let errorAlert = $("#ErrorAlert");

                let month = $('#month').val();
                let year = $('#payroll_year').val();
                let skipLateComing = $('#skip_late_coming').is(':checked') ? 1 : 0;
                let skipLateComingType = $('input[name="skip_late_coming_type"]:checked').val() || 'entire_month';
                let skipLateComingDates = $('#skip_late_coming_dates').val() || [];

                form.find('input[name="month"]').val(month);
                form.find('input[name="year"]').val(year);

                let data = form.serializeArray();
                data.push({ name: 'skip_late_coming', value: skipLateComing });
                data.push({ name: 'skip_late_coming_type', value: skipLateComingType });
                if (skipLateComingDates && skipLateComingDates.length > 0) {
                    skipLateComingDates.forEach(function(d) {
                        data.push({ name: 'skip_late_coming_dates[]', value: d });
                    });
                }

                // Show loading spinner & disable button
                button.prop("disabled", true);
                loader.removeClass("d-none");

                $.ajax({
                    url: actionUrl,
                    type: "POST",
                    data: $.param(data),
                    success: function (response, status, xhr) {
                        let disposition = xhr.getResponseHeader("Content-Disposition");
                        let filename = (disposition && disposition.indexOf("filename=") !== -1)
                            ? disposition.split("filename=")[1].replaceAll('"', '')
                            : 'payroll_single.csv';

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
                        
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        
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
