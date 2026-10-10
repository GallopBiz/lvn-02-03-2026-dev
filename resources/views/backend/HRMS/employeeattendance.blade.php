@extends('backend.layouts.main')

@section('main-container')

@php
    if (!function_exists('formatDurationInHoursAndMinutes')) {
        function formatDurationInHoursAndMinutes($minutes) {
            $minutes = (int) $minutes;
            if ($minutes < 60) {
                return $minutes . 'm';
            }
            $hours = floor($minutes / 60);
            $remMinutes = $minutes % 60;
            if ($remMinutes > 0) {
                return $hours . 'hr ' . $remMinutes . 'm';
            }
            return $hours . 'hr';
        }
    }
@endphp

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

        #attendanceTable .table-responsive {
            max-height: 70vh;
            overflow: auto;
        }
        #attendanceTable table {
            min-width: max-content;
        }
        #attendanceTable th,
        #attendanceTable td {
            white-space: nowrap;
        }
        #attendanceTable th:first-child,
        #attendanceTable td:first-child {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 5;
            border-right: 2px solid #dee2e6;
            min-width: 180px;
            max-width: 280px;
            white-space: normal;
        }
        #attendanceTable thead th {
            position: sticky;
            top: 0;
            z-index: 7;
            background-color: #afa4a4;
        }
        #attendanceTable th:first-child {
            background-color: #e5e5e5;
            z-index: 8;
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

        <!-- Legend Badge Bar -->
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-body py-2 px-3 bg-light rounded d-flex flex-wrap align-items-center gap-2" style="font-size: 13px;">
                <strong class="me-2">Attendance Badges Legend:</strong>
                <span class="badge badge-success" style="padding: 4px 6px; background-color: #28a745; color: #fff;">On Time In / Out</span>
                <span class="badge badge-danger" style="padding: 4px 6px; background-color: #dc3545; color: #fff;">Late (Xm)</span>
                <span class="badge badge-warning" style="padding: 4px 6px; background-color: #ffc107; color: #212529;">Early Out (Xm)</span>
                <span class="badge badge-info" style="padding: 4px 6px; background-color: #17a2b8; color: #fff;">Extra (Xm) [After Shift End]</span>
                <span class="badge badge-secondary" style="padding: 4px 6px; background-color: #6c757d; color: #fff;">No Out Punch</span>
                <span class="badge badge-primary" style="padding: 4px 6px; background-color: #007bff; color: #fff;">Half Day / Leave</span>
            </div>
        </div>

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
                                @php
                                    $employee = $employees->firstWhere('id', $employeeId);
                                    $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
                                @endphp
                                <tr>
                                    <td>{{ $employeeName ?: '--' }}</td>
                                    @foreach (Carbon\CarbonPeriod::create($startDate, $endDate) as $date)
                                        @php
                                            $record = $records->firstWhere('log_date', $date->toDateString());
                                            $holiday = \App\Models\Holidays::whereDate('HolidayStartDate', '<=', $date)
                                                ->whereDate('HolidayEndDate', '>=', $date)
                                                ->first();
                                            $isWeekendOff = $date->isSunday();

                                            $shift = $employee ? \App\Services\Hrms\ShiftResolver::getApplicableShift($employee, $date->toDateString()) : null;

                                            $approvedLeave = isset($leaveRequests) ? $leaveRequests->first(function ($lr) use ($employeeId, $date) {
                                                return $lr->employee_id == $employeeId
                                                    && \Carbon\Carbon::parse($lr->start_date)->lte($date)
                                                    && \Carbon\Carbon::parse($lr->end_date)->gte($date);
                                            }) : null;

                                            $isLateIn = false;
                                            $isOnTimeIn = false;
                                            $lateMinutes = 0;

                                            $isEarlyOut = false;
                                            $earlyMinutes = 0;
                                            $isExtraOut = false;
                                            $extraMinutes = 0;
                                            $isOnTimeOut = false;
                                            $missingOut = false;

                                            if ($record && $shift && $shift->start_time && $shift->end_time) {
                                                $dateStr = $date->toDateString();
                                                $shiftStart = strtotime($dateStr . ' ' . $shift->start_time);
                                                $shiftEnd = strtotime($dateStr . ' ' . $shift->end_time);
                                                $threshold = (int) ($shift->late_coming_threshold ?? 0);

                                                if ($record->in_time) {
                                                    $inTime = strtotime($dateStr . ' ' . $record->in_time);
                                                    if ($inTime > ($shiftStart + ($threshold * 60))) {
                                                        $isLateIn = true;
                                                        $lateMinutes = (int) round(($inTime - $shiftStart) / 60);
                                                    } else {
                                                        $isOnTimeIn = true;
                                                    }
                                                }

                                                if ($record->out_time) {
                                                    $outTime = strtotime($dateStr . ' ' . $record->out_time);
                                                    if ($outTime < $shiftEnd) {
                                                        $isEarlyOut = true;
                                                        $earlyMinutes = (int) round(($shiftEnd - $outTime) / 60);
                                                    } else {
                                                        $extraDiff = (int) round(($outTime - $shiftEnd) / 60);
                                                        if ($extraDiff >= 15) {
                                                            $isExtraOut = true;
                                                            $extraMinutes = $extraDiff;
                                                        } else {
                                                            $isOnTimeOut = true;
                                                        }
                                                    }
                                                } elseif ($record->in_time && !$record->out_time) {
                                                    $missingOut = true;
                                                }
                                            }
                                        @endphp
                                        <td style="min-width: 145px; padding: 6px 8px;">
                                            @if ($record)
                                                <!-- STATUS BADGES FIRST IN FRONT -->
                                                <div class="mb-1" style="display: flex; flex-wrap: wrap; gap: 3px; align-items: center;">
                                                    <span class="badge {{ $record->status == 'present' ? 'badge-success' : ($record->status == 'on-official-work' ? 'badge-info' : 'badge-danger') }}" style="font-size: 11px; padding: 4px 6px;">
                                                        {{ ucfirst($record->status) }}
                                                    </span>

                                                    @if ($isLateIn)
                                                        <span class="badge badge-danger" style="font-size: 11px; padding: 4px 6px; background-color: #dc3545; color: #fff;" title="Late In by {{ formatDurationInHoursAndMinutes($lateMinutes) }}">
                                                            Late ({{ formatDurationInHoursAndMinutes($lateMinutes) }})
                                                        </span>
                                                    @elseif ($isOnTimeIn)
                                                        <span class="badge badge-success" style="font-size: 11px; padding: 4px 6px; background-color: #28a745; color: #fff;" title="On Time Entry">
                                                            On Time In
                                                        </span>
                                                    @endif

                                                    @if ($isEarlyOut)
                                                        <span class="badge badge-warning" style="font-size: 11px; padding: 4px 6px; background-color: #ffc107; color: #212529;" title="Left {{ formatDurationInHoursAndMinutes($earlyMinutes) }} before shift end">
                                                            Early Out ({{ formatDurationInHoursAndMinutes($earlyMinutes) }})
                                                        </span>
                                                    @elseif ($isExtraOut)
                                                        <span class="badge badge-info" style="font-size: 11px; padding: 4px 6px; background-color: #17a2b8; color: #fff;" title="Punched Out {{ formatDurationInHoursAndMinutes($extraMinutes) }} after shift end">
                                                            Extra ({{ formatDurationInHoursAndMinutes($extraMinutes) }})
                                                        </span>
                                                    @elseif ($isOnTimeOut)
                                                        <span class="badge badge-success" style="font-size: 11px; padding: 4px 6px; background-color: #28a745; color: #fff;" title="Punched Out On Time">
                                                            On Time Out
                                                        </span>
                                                    @elseif ($missingOut)
                                                        <span class="badge badge-secondary" style="font-size: 11px; padding: 4px 6px; background-color: #6c757d; color: #fff;" title="No Out Punch Recorded">
                                                            No Out Punch
                                                        </span>
                                                    @endif

                                                    @if ($approvedLeave)
                                                        <span class="badge badge-primary" style="font-size: 11px; padding: 4px 6px; background-color: #007bff; color: #fff;">
                                                            {{ $approvedLeave->is_half_day ? 'Half Day Leave' : 'Leave' }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- TIMINGS DISPLAY -->
                                                <div style="font-size: 12px; line-height: 1.4;">
                                                    <span style="color: #28a745; font-weight: 600;">In:</span> {{ $record->in_time ? \Carbon\Carbon::parse($record->in_time)->format('h:i A') : '--' }}
                                                    <br>
                                                    <span style="color: #dc3545; font-weight: 600;">Out:</span> {{ $record->out_time ? \Carbon\Carbon::parse($record->out_time)->format('h:i A') : '--' }}
                                                </div>
                                            @elseif($holiday)
                                                <span class="badge badge-info" style="font-size: 11px; padding: 4px 6px;">Holiday</span>
                                            @elseif($isWeekendOff)
                                                <span class="badge badge-secondary" style="font-size: 11px; padding: 4px 6px;">Sunday</span>
                                            @else
                                                <span class="badge badge-danger" style="font-size: 11px; padding: 4px 6px;">Absent</span>
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
