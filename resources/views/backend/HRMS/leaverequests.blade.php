@extends('backend.layouts.main')
@section('main-container')

<style>
    .uperletter {
        text-transform: capitalize;
    }
    .select2-container .select2-selection--single {
        height: 34px !important;
        border: 1px solid #ced4da !important;
        border-radius: 4px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 32px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
        padding-left: 8px !important;
    }
</style>

<meta name="csrf-token" content="{{ csrf_token() }}">

@php $i = 0; @endphp
@php
    $selectedEmployeeId = old(
        'employee_id',
        $stream_master->employee_id ?? ($employees->count() === 1 ? optional($employees->first())->id : '')
    );
@endphp

<div class="main-content">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Leave Requests</h1>
        </div>

        <div class="separator-breadcrumb border-top"></div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Attention:</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form id="progress-form" class="p-4 progress-form"
            action="{{ !empty($stream_master) ? url('store-leaverequests') : url('save-leaverequests') }}" method="post">
            @if (!empty($stream_master))
                <input type="hidden" name="id" value="{{ $stream_master->id }}">
            @endif
            @csrf

            <div class="row">
                <div class="col-md-3 form-group mb-3">
                    <label for="start_date">Leave Start Date</label>
                    <input required class="form-control" id="start_date" name="start_date" type="date"
                        value="{{ $stream_master->start_date ?? '' }}" />
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="end_date">Leave End Date</label>
                    <input required class="form-control" id="end_date" name="end_date" type="date"
                        value="{{ $stream_master->end_date ?? '' }}" />
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="employee_id">Employee</label>
                    <select name="employee_id" id="employee_id" class="form-control select2" data-placeholder="Select Employee">
                        <option value="">Select Employee</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}"
                                @selected((string) $selectedEmployeeId === (string) $employee->id)>
                                {{ $employee->first_name }} {{ $employee->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="leave_type_id">Leave Type</label>
                    <select name="leave_type_id" id="leave_type_id" class="form-control">
                        <option value="">Select Leave Type</option>
                        @if (isset($stream_master))
                            @foreach ($leaveTypes as $leaveType)
                                @php
                                    $empBalance = \App\Models\HrmsEmployeeLeaveBalance::where('employee_id', $stream_master->employee_id)
                                        ->where('leave_type_id', $leaveType->id)
                                        ->first();
                                    $balance = $empBalance ? $empBalance->balance : 0;

                                    if ($stream_master->leave_type_id == $leaveType->id && $stream_master->is_half_day) {
                                        $balance -= 0.5;
                                    }

                                    $formattedBalance = number_format(max($balance, 0), 1, '.', '');
                                @endphp

                                <option value="{{ $leaveType->id }}"
                                    {{ $stream_master->leave_type_id == $leaveType->id ? 'selected' : '' }}>
                                    {{ $leaveType->name }}{{ $leaveType->is_paid ? " ({$formattedBalance} available)" : '' }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-6 form-group mb-3">
                    <label for="reason">Note</label>
                    <textarea class="form-control" id="reason" name="reason" rows="3">{{ $stream_master->reason ?? '' }}</textarea>
                </div>

                <div class="col-md-3 form-group mb-3">
                    <div class="form-check d-flex align-items-center">
                        <input type="checkbox" class="form-check-input me-2" id="is_half_day" name="is_half_day"
                            {{ isset($stream_master) && $stream_master->is_half_day ? 'checked' : '' }}>
                        <label class="form-check-label me-3" for="is_half_day">Half Day</label>

                        <select name="half_day_type" id="half_day_type" class="form-control">
                            <option value="">Select Half</option>
                            <option value="first" {{ isset($stream_master) && $stream_master->half_day_type == 'first' ? 'selected' : '' }}>
                                First Half
                            </option>
                            <option value="second" {{ isset($stream_master) && $stream_master->half_day_type == 'second' ? 'selected' : '' }}>
                                Second Half
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    @if (request()->route()->getName() == 'leaverequests')
                        <button type="button" class="btn btn-secondary" id="reset-button">Reset</button>
                    @endif
                    @if (request()->route()->getName() !== 'leaverequests')
                        <a href="{{ url('leaverequests') }}" class="btn btn-primary">Add New</a>
                    @endif
                </div>
            </div>
        </form>
        <br>
    </div>

    <div class="separator-breadcrumb border-top"></div>

    {{-- FILTERS --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <input type="text" id="searchInput" class="form-control" placeholder="Search by employee, note, leave type">
        </div>
        <div class="col-md-3">
            <select id="statusFilter" class="form-control">
                <option value="">Filter by Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="breadcrumb">
                <h1 class="me-2">List of Saved Leave Requests</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>

            <div class="card text-start">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="leaveTable" class="display table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Employee Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Total Days</th>
                                    <th>Leave Type</th>
                                    <th>Half Day Type</th>
                                    <th>Note</th>
                                    <th>Status</th>
                                    <th>Request Submitted Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($stream as $streams)
                                    <tr>
                                        <td>{{ ++$i }}</td>
                                        <td class="uperletter">
                                            {{ $streams->employee->first_name ?? '' }} {{ $streams->employee->last_name ?? '' }}
                                        </td>
                                        <td>{{ date('d-m-Y', strtotime($streams->start_date)) }}</td>
                                        <td>{{ date('d-m-Y', strtotime($streams->end_date)) }}</td>
                                        <td>
                                            {{ $streams->is_half_day ? '0.5' : (new DateTime($streams->start_date))->diff(new DateTime($streams->end_date))->days + 1 }}
                                        </td>
                                        <td class="uperletter">{{ $streams->leaveType->name ?? 'N/A' }}</td>
                                        <td>
                                            @if ($streams->is_half_day)
                                                {{ ucfirst($streams->half_day_type) }} Half
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($streams->reason, 60) }}</td>
                                        <td class="uperletter">{{ $streams->status }}</td>
                                        <td>{{ $streams->created_at ? $streams->created_at->format('d-m-Y') : '-' }}</td>

                                        <td class="d-flex">
                                            @if ($streams->status === 'Pending')
                                                <a class="btn btn-primary m-1" href="{{ url('view-leaverequests/' . $streams->id) }}">Edit</a>
                                                <a class="btn btn-danger m-1" href="{{ url('delete-leaverequests/' . $streams->id) }}" onclick="confirmDelete(event)">Delete</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($stream->isEmpty())
                                    <tr>
                                        <td colspan="11" class="text-center">No Data Found</td>
                                    </tr>
                                @endif
                            </tbody>

                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {!! $stream->appends(request()->except('page'))->links('pagination::bootstrap-4') !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<script>
    function confirmDelete(event) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently delete the leave request.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = event.target.href;
            }
        });
    }

    // Search + Filter
    document.getElementById("searchInput").addEventListener("keyup", filterTable);
    document.getElementById("statusFilter").addEventListener("change", filterTable);

    function filterTable() {
        let searchValue = document.getElementById("searchInput").value.toLowerCase();
        let statusValue = document.getElementById("statusFilter").value.toLowerCase();

        let rows = document.querySelectorAll("#leaveTable tbody tr");

        rows.forEach(row => {
            let rowText = row.innerText.toLowerCase();
            let statusText = row.querySelector("td:nth-child(9)")?.innerText.toLowerCase();

            let matchesSearch = rowText.includes(searchValue);
            let matchesStatus = statusValue ? statusText === statusValue : true;

            row.style.display = (matchesSearch && matchesStatus) ? "" : "none";
        });
    }

    // Reset form
    document.getElementById('reset-button')?.addEventListener('click', function () {
        document.getElementById("reason").value = "";
        document.getElementById("start_date").value = "";
        document.getElementById("end_date").value = "";
        if (window.jQuery && $('#employee_id').data('select2')) {
            $('#employee_id').val('').trigger('change');
        } else {
            const empEl = document.getElementById("employee_id");
            if (empEl) empEl.value = "";
        }
        document.getElementById("leave_type_id").innerHTML = '<option value="">Select Leave Type</option>';
    });

    // Dynamic leave types
    function setupDynamicLeaveTypeUpdate(employeeSelectId, leaveTypeSelectId) {
        const employeeSelect = document.getElementById(employeeSelectId);
        const leaveTypeSelect = document.getElementById(leaveTypeSelectId);

        function loadLeaveTypes(employeeId) {
            leaveTypeSelect.innerHTML = '<option value="">Select Leave Type</option>';

            if (!employeeId) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch("{{ route('getLeaveTypes') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ employee_id: employeeId }),
            })
                .then(response => {
                    if (!response.ok) throw new Error(`Request failed with status ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (!Array.isArray(data)) throw new Error('Invalid leave type response');

                    data.forEach(leaveType => {
                        const option = document.createElement('option');
                        option.value = leaveType.id;
                        option.text = leaveType.name;
                        option.setAttribute('data-is-paid', leaveType.is_paid ? '1' : '0');
                        option.setAttribute('data-balance', leaveType.available_balance !== null && leaveType.available_balance !== undefined ? leaveType.available_balance : '');
                        option.setAttribute('data-has-cl-balance', leaveType.has_cl_balance ? '1' : '0');
                        leaveTypeSelect.appendChild(option);
                    });

                    const selectedLeaveType = @json((string) old('leave_type_id', $stream_master->leave_type_id ?? ''));
                    if (selectedLeaveType) leaveTypeSelect.value = selectedLeaveType;
                })
                .catch(error => {
                    console.error('Error fetching leave types:', error);
                    leaveTypeSelect.innerHTML = '<option value="">Unable to load leave types</option>';
                });
        }

        employeeSelect.addEventListener('change', function () {
            loadLeaveTypes(this.value);
        });

        if (window.jQuery) {
            $(employeeSelect).on('change', function () {
                loadLeaveTypes(this.value);
            });
        }

        loadLeaveTypes(employeeSelect.value);
    }

    setupDynamicLeaveTypeUpdate('employee_id', 'leave_type_id');

    function checkLeaveBalanceAlert(selectElement, showAlert = true) {
        if (!selectElement) return true;
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        if (!selectedOption || !selectedOption.value) return true;

        const isPaid = selectedOption.getAttribute('data-is-paid');
        const balanceAttr = selectedOption.getAttribute('data-balance');
        const balance = balanceAttr !== '' && balanceAttr !== null ? parseFloat(balanceAttr) : null;
        const hasClBalance = selectedOption.getAttribute('data-has-cl-balance') === '1';
        const isLeaveApplication = (selectedOption.value == '6' || isPaid === '0' || selectedOption.text.toLowerCase().includes('leave application'));

        // Validation for Leave Application:
        // If staff member has an available balance in CL-T or Casual Leave, they should not be allowed to select or submit a Leave Application.
        if (isLeaveApplication && hasClBalance) {
            if (showAlert) {
                const clAlertMsg = 'You still have available leave balance (CL-T / Casual Leave). Please use your available leave balance before applying for Leave Application.';
                const clAlertHtml = 'You still have available leave balance (CL-T / Casual Leave).<br><br>Please use your available leave balance before applying for <strong style="color: #0d6efd; font-weight: 700; background: #e7f1ff; padding: 3px 8px; border-radius: 4px; border: 1px solid #b6d4fe; display: inline-block;">Leave Application</strong>.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Available Leave Balance',
                        html: clAlertHtml,
                        confirmButtonColor: '#0d6efd',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert(clAlertMsg);
                }
            }
            selectElement.value = '';
            return false;
        }

        // Validation for paid leave types:
        if (isPaid === '1' && balance !== null && balance <= 0) {
            if (showAlert) {
                const alertHtml = 'You do not have any available leave balance for this leave type.<br><br>Please submit a <strong style="color: #0d6efd; font-weight: 700; background: #e7f1ff; padding: 3px 8px; border-radius: 4px; border: 1px solid #b6d4fe; display: inline-block;">Leave Application</strong> instead.';
                const alertMsg = 'You do not have any available leave balance for this leave type. Please submit a Leave Application instead.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Available Leave Balance',
                        html: alertHtml,
                        confirmButtonColor: '#0d6efd',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert(alertMsg);
                }
            }
            return false;
        }
        return true;
    }

    document.getElementById('progress-form')?.addEventListener('submit', function (e) {
        const leaveTypeSelect = document.getElementById('leave_type_id');
        if (leaveTypeSelect && !checkLeaveBalanceAlert(leaveTypeSelect, true)) {
            e.preventDefault();
            return false;
        }
    });

    document.getElementById('leave_type_id')?.addEventListener('change', function () {
        checkLeaveBalanceAlert(this, true);
    });

    if (window.jQuery && $.fn.select2) {
        if (!$('#employee_id').data('select2')) {
            $('#employee_id').select2({
                width: '100%',
                placeholder: 'Select Employee',
                allowClear: true
            });
        }
    }
</script>

@endsection
