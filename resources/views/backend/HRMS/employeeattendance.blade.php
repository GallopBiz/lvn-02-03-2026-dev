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
                return $hours . 'h ' . $remMinutes . 'm';
            }
            return $hours . 'h';
        }
    }
@endphp

    <style>
        .uperletter {
            text-transform: capitalize;
        }
        .spinner {
            width: 44px;
            height: 44px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Container & Table Structure */
        .att-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        #attendanceTable .table-responsive {
            max-height: 72vh;
            overflow: auto;
        }
        #attendanceTable table {
            min-width: max-content;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
        }
        #attendanceTable th,
        #attendanceTable td {
            border-bottom: 1px solid #cbd5e1 !important;
            border-right: 1px solid #e2e8f0 !important;
            vertical-align: middle;
        }

        /* Sticky Left Column: Employee Name */
        #attendanceTable th:first-child,
        #attendanceTable td:first-child {
            position: sticky;
            left: 0;
            background-color: #ffffff;
            z-index: 6;
            border-right: 2px solid #94a3b8 !important;
            border-bottom: 1px solid #cbd5e1 !important;
            min-width: 190px;
            max-width: 240px;
            white-space: normal;
            box-shadow: 3px 0 6px rgba(0, 0, 0, 0.03);
        }

        /* Sticky Header Row */
        #attendanceTable thead th {
            position: sticky;
            top: 0;
            z-index: 8;
            background: #1e293b;
            color: #f8fafc;
            border-bottom: 2px solid #0f172a !important;
            padding: 8px 4px;
            text-align: center;
            font-weight: 600;
        }

        #attendanceTable thead th:first-child {
            background: #0f172a;
            color: #ffffff;
            z-index: 10;
            text-align: left;
            padding-left: 14px;
        }

        /* Weekend Header */
        #attendanceTable thead th.header-weekend {
            background: #334155;
            color: #f87171;
        }

        /* Matrix Cell Width & Layout */
        .att-cell {
            min-width: 86px;
            max-width: 96px;
            width: 88px;
            padding: 5px 3px !important;
            text-align: center;
            transition: background-color 0.15s ease;
            font-size: 11px;
            background-color: #ffffff;
        }

        .att-cell:hover {
            background-color: #f1f5f9 !important;
        }

        .cell-weekend {
            background-color: #f8fafc;
        }

        .cell-absent {
            background-color: #fff5f5;
        }

        .cell-holiday {
            background-color: #f0fdf4;
        }

        /* Badge Chips Styling */
        .badge-chip {
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            padding: 1.5px 5px;
            border-radius: 4px;
            line-height: 1.25;
            white-space: nowrap;
            text-transform: capitalize;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badge-chip-present { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-chip-late { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-chip-early { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-chip-extra { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-chip-noout { background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
        .badge-chip-absent { background-color: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
        .badge-chip-leave { background-color: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-chip-holiday { background-color: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }
        .badge-chip-sunday { background-color: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; }

        /* Punch Timing Formatting */
        .punch-times {
            margin-top: 3px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 10.5px;
            line-height: 1.35;
        }
        .punch-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2px;
            white-space: nowrap;
        }
        .punch-in { color: #166534; font-weight: 600; }
        .punch-out { color: #991b1b; font-weight: 600; }
        .lbl-in { color: #16a34a; font-size: 9.5px; font-weight: 700; }
        .lbl-out { color: #dc2626; font-size: 9.5px; font-weight: 700; }
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
        $selectedMonthName = $months[$selectedMonth] ?? 'Unknown';
    @endphp

    <div class="main-content">
        <div id="loader" style="display: none; text-align: center; padding: 20px;">
            <div class="spinner mx-auto"></div>
            <p class="mt-2 text-muted small">Loading attendance records...</p>
        </div>
        
        <!-- Header & Action Row -->
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
            <div>
                <h3 class="mb-1 text-dark font-weight-bold">Biometric Attendance Management</h3>
                <p class="text-muted mb-0 small">Monthly employee attendance matrix and ESSL sync log</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button id="syncAttendance" class="btn btn-primary btn-sm px-3 shadow-sm rounded-2">
                    <i class="fa fa-sync-alt me-1"></i> Sync Attendance
                </button>
                <button id="lockAttendanceBtn" class="btn btn-outline-secondary btn-sm px-3 shadow-sm rounded-2">
                    <i class="fa fa-lock me-1"></i> Lock {{ $selectedMonthName }} Attendance
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card border-0 shadow-sm mb-3 rounded-3" style="background: #ffffff;">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('employeeattendance') }}" class="mb-0">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label for="month" class="form-label font-weight-bold small text-secondary">Month</label>
                            <select name="month" id="month" class="form-control form-control-sm">
                                @foreach ($months as $key => $value)
                                    <option value="{{ $key }}" {{ $selectedMonth == $key ? 'selected' : '' }}>
                                        {{ $value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="Attendanceyear" class="form-label font-weight-bold small text-secondary">Year</label>
                            <select name="Attendanceyear" id="Attendanceyear" class="form-control form-control-sm">
                                @php
                                    $startYear = 2021;
                                    $endYear = 2030;
                                    $selectedYear = request('Attendanceyear', request('year', date('Y')));
                                @endphp
                                @for ($year = $startYear; $year <= $endYear; $year++)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                        {{ $year }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="employee_id" class="form-label font-weight-bold small text-secondary">Employee</label>
                            <select name="employee_id" id="employee_id" class="form-control form-control-sm">
                                <option value="">All Employees</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->first_name }} {{ $employee->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100 font-weight-bold">
                                <i class="fa fa-filter me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Legend Badge Bar -->
        <div class="card mb-3 border-0 shadow-sm rounded-3">
            <div class="card-body py-2 px-3 bg-white rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="font-size: 12px;">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <strong class="text-secondary me-1"><i class="fa fa-info-circle me-1"></i> Legend:</strong>
                    <span class="badge-chip badge-chip-present">Present</span>
                    <span class="badge-chip badge-chip-late">Late</span>
                    <span class="badge-chip badge-chip-early">Early</span>
                    <span class="badge-chip badge-chip-extra">Extra</span>
                    <span class="badge-chip badge-chip-noout">No Out Punch</span>
                    <span class="badge-chip badge-chip-leave">Leave / Half Day</span>
                    <span class="badge-chip badge-chip-holiday">Holiday</span>
                    <span class="badge-chip badge-chip-sunday">Sunday</span>
                    <span class="badge-chip badge-chip-absent">Absent</span>
                </div>
                <div class="text-muted small d-none d-md-block">
                    <i class="fa fa-mouse-pointer me-1"></i> Hover over cell for details
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div id="attendanceTable" class="att-card">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            @foreach (Carbon\CarbonPeriod::create($startDate, $endDate) as $date)
                                <th class="{{ $date->isSunday() ? 'header-weekend' : '' }}">
                                    <div style="font-size: 13px; font-weight: 700; line-height: 1.1;">{{ $date->format('d') }}</div>
                                    <div style="font-size: 10px; opacity: 0.85; text-transform: uppercase;">{{ $date->format('D') }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @if ($attendanceData->isEmpty())
                            <tr>
                                <td colspan="{{ \Carbon\CarbonPeriod::create($startDate, $endDate)->count() + 1 }}"
                                    class="text-center text-muted py-4">
                                    No attendance records found for the selected month & filters.
                                </td>
                            </tr>
                        @else
                            @foreach ($attendanceData as $employeeId => $records)
                                @php
                                    $employee = $employees->firstWhere('id', $employeeId) ?? ($records->first() ? $records->first()->employee : null);
                                    $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
                                    if (!$employee || !$employeeName) {
                                        continue;
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 26px; height: 26px; font-size: 11px; font-weight: 700; flex-shrink: 0;">
                                                {{ strtoupper(substr($employeeName ?: 'E', 0, 1)) }}
                                            </div>
                                            <div style="font-weight: 600; font-size: 12.5px; color: #1e293b; line-height: 1.2;">
                                                {{ $employeeName ?: '--' }}
                                            </div>
                                        </div>
                                    </td>
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

                                            // Cell class & tooltip
                                            $cellClass = 'att-cell';
                                            if ($isWeekendOff) $cellClass .= ' cell-weekend';
                                            elseif ($holiday) $cellClass .= ' cell-holiday';
                                            elseif (!$record && !$approvedLeave) $cellClass .= ' cell-absent';

                                            $tooltipText = "Employee: " . ($employeeName ?: '--') . " | Date: " . $date->format('d M Y (D)');
                                            if ($record) {
                                                $tooltipText .= "\nStatus: " . ucfirst($record->status);
                                                if ($shift && $shift->start_time && $shift->end_time) {
                                                    $tooltipText .= "\nShift: " . $shift->start_time . ' - ' . $shift->end_time;
                                                }
                                                $tooltipText .= "\nIn: " . ($record->in_time ? \Carbon\Carbon::parse($record->in_time)->format('h:i A') : 'N/A');
                                                if ($isLateIn) $tooltipText .= " (Late by " . formatDurationInHoursAndMinutes($lateMinutes) . ")";
                                                $tooltipText .= "\nOut: " . ($record->out_time ? \Carbon\Carbon::parse($record->out_time)->format('h:i A') : 'N/A');
                                                if ($isEarlyOut) $tooltipText .= " (Early by " . formatDurationInHoursAndMinutes($earlyMinutes) . ")";
                                                if ($isExtraOut) $tooltipText .= " (Extra " . formatDurationInHoursAndMinutes($extraMinutes) . ")";
                                                if ($missingOut) $tooltipText .= " (No Out Punch)";
                                            } elseif ($holiday) {
                                                $tooltipText .= "\nHoliday: " . ($holiday->HolidayName ?? 'Public Holiday');
                                            } elseif ($isWeekendOff) {
                                                $tooltipText .= "\nSunday Off";
                                            } else {
                                                $tooltipText .= "\nStatus: Absent";
                                            }
                                        @endphp
                                        <td class="{{ $cellClass }}" title="{{ $tooltipText }}">
                                            @if ($record)
                                                <div class="d-flex flex-column align-items-center justify-content-center gap-1">
                                                    <!-- Exception / Status Badge Chips -->
                                                    @php $hasException = false; @endphp

                                                    @if ($isLateIn)
                                                        @php $hasException = true; @endphp
                                                        <span class="badge-chip badge-chip-late" title="Late by {{ formatDurationInHoursAndMinutes($lateMinutes) }}">
                                                            Late
                                                        </span>
                                                    @endif

                                                    @if ($isEarlyOut)
                                                        @php $hasException = true; @endphp
                                                        <span class="badge-chip badge-chip-early" title="Left {{ formatDurationInHoursAndMinutes($earlyMinutes) }} early">
                                                            Early
                                                        </span>
                                                    @elseif ($isExtraOut)
                                                        @php $hasException = true; @endphp
                                                        <span class="badge-chip badge-chip-extra" title="Extra {{ formatDurationInHoursAndMinutes($extraMinutes) }}">
                                                            Extra
                                                        </span>
                                                    @elseif ($missingOut)
                                                        @php $hasException = true; @endphp
                                                        <span class="badge-chip badge-chip-noout" title="No Out Punch Recorded">
                                                            No Out
                                                        </span>
                                                    @endif

                                                    @if ($approvedLeave)
                                                        @php $hasException = true; @endphp
                                                        <span class="badge-chip badge-chip-leave">
                                                            {{ $approvedLeave->is_half_day ? 'Half Day' : 'Leave' }}
                                                        </span>
                                                    @endif

                                                    @if (!$hasException)
                                                        <span class="badge-chip badge-chip-present">
                                                            {{ $record->status == 'on-official-work' ? 'Official' : 'Present' }}
                                                        </span>
                                                    @endif

                                                    <!-- Timings -->
                                                    <div class="punch-times">
                                                        <div class="punch-row punch-in">
                                                            <span class="lbl-in">I:</span> {{ $record->in_time ? \Carbon\Carbon::parse($record->in_time)->format('h:i A') : '--' }}
                                                        </div>
                                                        <div class="punch-row punch-out">
                                                            <span class="lbl-out">O:</span> {{ $record->out_time ? \Carbon\Carbon::parse($record->out_time)->format('h:i A') : '--' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($holiday)
                                                <span class="badge-chip badge-chip-holiday" title="{{ $holiday->HolidayName ?? 'Holiday' }}">Holiday</span>
                                            @elseif($isWeekendOff)
                                                <span class="badge-chip badge-chip-sunday">Sunday</span>
                                            @else
                                                <span class="badge-chip badge-chip-absent">Absent</span>
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
            if (select) {
                for (var i = 0; i < select.options.length; i++) {
                    select.options[i].innerText = capitalizeFirstLetter(select.options[i].innerText);
                }
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
                    let $btn = $(this);
                    let originalText = $btn.html();

                    $btn.html("⏳ Syncing...").prop("disabled", true);
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
                            location.reload();
                        },
                        error: function(xhr) {
                            alert(xhr.responseJSON.message ||
                                "An error occurred while syncing attendance.");
                            $btn.html(originalText).prop("disabled", false);
                        }
                    }).always(function() {
                        $btn.html(originalText).prop("disabled", false);
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

            if (monthDropdown && lockButton) {
                monthDropdown.addEventListener("change", function() {
                    let selectedMonth = monthDropdown.value;
                    lockButton.innerHTML = "🔒 Lock " + monthNames[selectedMonth] + " Attendance";
                });
            }
        });

        $(document).ready(function () {
            $("form").on("submit", function () {
                $("#loader").show();
                $("#attendanceTable").hide();
            });
        });
    </script>

@endsection

