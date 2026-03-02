@extends('backend.layouts.main')

@section('main-container')

    <style>
        .uperletter {
            text-transform: capitalize;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Freeze first column (Employee Name) */
        table.table-bordered th:first-child,
        table.table-bordered td:first-child {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 5;
            border-right: 2px solid #dee2e6;
        }
    </style>

    @php
        $i = 0;
        $months = [
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
        $selectedMonth = request('month', date('m')); // Default to current month
    @endphp

    <div class="main-content">
        <div id="loader" style="display: none; text-align: center;">
            <div class="spinner"></div>
        </div>
        
        <h2 class="mb-4">Attendance Management</h2>
        <button id="syncAttendance" class="btn btn-primary mb-3">🔄 Sync Attendance</button>
        @php
            $selectedMonthName = $months[$selectedMonth] ?? 'Unknown';
        @endphp

        <button id="lockAttendanceBtn" class="btn btn-primary mb-3">🔒 Lock {{ $months[$selectedMonth] }} Attendance</button>

        <!-- Filters Section -->
        <form method="GET" action="{{ route('employeeattendance') }}" class="mb-4">
            <div class="row">
                {{-- <div class="col-md-4">
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control"
                        value="{{ request('start_date', $startDate) }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control"
                        value="{{ request('end_date', $endDate) }}">
                </div> --}}
                <div class="col-md-4">
                    <label for="month">Month:</label>
                    <select name="month" id="month" class="form-control">

                        @foreach ($months as $key => $value)
                            <option value="{{ $key }}" {{ $selectedMonth == $key ? 'selected' : '' }}>
                                {{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="Attendanceyear">Year:</label>
                    <select name="Attendanceyear" id="Attendanceyear" class="form-control">
                        @php
                            $startYear = 2021;
                            $endYear = 2030;
                            $selectedYear = request('year', date('Y')); // Default to current year
                        @endphp
                        @for ($year = $startYear; $year <= $endYear; $year++)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                {{ $year }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="employee_id">Select Employee:</label>
                    <select name="employee_id" id="employee_id" class="form-control">
                        <option value="">All Employees</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}"
                                {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3" >Filter</button>
        </form>

        <!-- Attendance Table -->
        <div id="attendanceTable">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Employee Name</th>
                            @foreach (Carbon\CarbonPeriod::create($startDate, $endDate) as $date)
                                <th>{{ $date->format('d-M') }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @if ($attendanceData->isEmpty())
                            <tr>
                                <td colspan="{{ \Carbon\CarbonPeriod::create($startDate, $endDate)->count() + 1 }}"
                                    class="text-center text-muted">
                                    No attendance records found for the selected filters.
                                </td>
                            </tr>
                        @else
                            @foreach ($attendanceData as $employeeId => $records)
                                <tr>
                                    <td>{{ optional($employees->firstWhere('id', $employeeId))->first_name ?? '--' }}</td>
                                    @foreach (Carbon\CarbonPeriod::create($startDate, $endDate) as $date)
                                        @php
                                            $record = $records->firstWhere('log_date', $date->toDateString());
                                            $holiday = \App\Models\Holidays::whereDate('HolidayStartDate', '<=', $date)
                                                ->whereDate('HolidayEndDate', '>=', $date)
                                                ->first();
                                            $isWeekendOff = $date->isSunday();
                                        @endphp
                                        <td>
                                            @if ($record)
                                                In: {{ $record->in_time ?? '--' }} <br>
                                                Out: {{ $record->out_time ?? '--' }} <br>
                                                Status: <span
                                                    class="badge badge-{{ $record->status == 'present' ? 'success' : ($record->status == 'on-official-work' ? 'info' : 'danger') }}">
                                                    {{ ucfirst($record->status) }}
                                                </span>
                                            @elseif($holiday)
                                                <span>Holiday</span>
                                            @elseif($isWeekendOff)
                                                <span>Sunday</span>
                                            @else
                                                <span style="color: red">Absent</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>

    @if (session('success'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: true
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var select = document.getElementById('employee_id');
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

        document.addEventListener('DOMContentLoaded', function() {
            $("#reset").on("click", function() {
                $("#monthYear").val("");
                $("#employee_id").val("");
            });
        });

        $(document).ready(function() {
            $("#syncAttendance").click(function() {
                if (confirm("Are you sure you want to sync attendance from ESSL?")) {
                    let $btn = $(this); // Store reference to the button
                    let originalText = $btn.html(); // Store the original text

                    $btn.html("⏳ Syncing...").prop("disabled", true); // Change text & disable button
                    let Attendanceyear = $('#Attendanceyear').val();
                    let month = $('#month').val();
                    $.ajax({
                        url: "{{ route('employeeattendance.sync') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        data: {
                            Attendanceyear: Attendanceyear,
                            month: month
                        },
                        success: function(response) {
                            alert(response.message);
                            location.reload(); // Reload the page to update attendance data
                        },
                        error: function(xhr) {
                            alert(xhr.responseJSON.message ||
                                "An error occurred while syncing attendance.");
                            $btn.html(originalText).prop("disabled", false);
                        }
                    }).always(function() {
                        $btn.html(originalText).prop("disabled",
                        false); // Ensure button resets after AJAX call
                    });
                }
            });
            $('#lockAttendanceBtn').click(function() {
                let Attendanceyear = $('#Attendanceyear').val();
                let month = $('#month').val();

                $.post('/attendance/lock', {
                        Attendanceyear,
                        month,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(response => alert(response.success))
                    .fail(error => alert(error.responseJSON.error));
            });

        });
        document.addEventListener("DOMContentLoaded", function() {
            let monthDropdown = document.getElementById("month");
            let lockButton = document.getElementById("lockAttendanceBtn");

            let monthNames = @json($months);

            monthDropdown.addEventListener("change", function() {
                let selectedMonth = monthDropdown.value;
                lockButton.innerHTML = "🔒 Lock " + monthNames[selectedMonth] + " Attendance";
            });
        });

        $(document).ready(function () {
        $("form").on("submit", function () {
            $("#loader").show();
            $("#attendanceTable").hide();
        });
    });
    </script>

@endsection
