@extends('backend.layouts.main')
@section('main-container')

<style>
    .uperletter {
        text-transform: capitalize;
    }
</style>

<meta name="csrf-token" content="{{ csrf_token() }}">

@php $i = 0; @endphp

<div class="main-content">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Leave Requests (Staff)</h1>
        </div>

        <div class="separator-breadcrumb border-top"></div>

        <form id="progress-form" class="p-4 progress-form"
            action="{{ url('save-leaverequests-staff') }}" method="post">
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
                    <select name="employee_id" id="employee_id" class="form-control" readonly>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" selected>
                                {{ $employee->first_name }} {{ $employee->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="leave_type_id">Leave Type</label>
                    <select name="leave_type_id" id="leave_type_id" class="form-control">
                        <option value="">Select Leave Type</option>
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
                    <button type="button" class="btn btn-secondary" id="reset-button">Reset</button>
                </div>
            </div>
        </form>
        <br>
    </div>

    <div class="separator-breadcrumb border-top"></div>

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
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Total Days</th>
                                    <th>Leave Type</th>
                                    <th>Half Day Type</th>
                                    <th>Note</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stream as $streams)
                                    <tr>
                                        <td>{{ ++$i }}</td>
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
                                        <td colspan="9" class="text-center">No Data Found</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
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

    // Reset form
    document.getElementById('reset-button')?.addEventListener('click', function () {
        document.getElementById("reason").value = "";
        document.getElementById("start_date").value = "";
        document.getElementById("end_date").value = "";
        document.getElementById("leave_type_id").innerHTML = '<option value="">Select Leave Type</option>';
    });

    // Load leave types for logged-in staff

    function loadStaffLeaveTypes() {
        const leaveTypeSelect = document.getElementById('leave_type_id');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch("{{ route('getLeaveTypes.staff') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({}),
        })
            .then(response => response.json())
            .then(data => {
                leaveTypeSelect.innerHTML = '<option value="">Select Leave Type</option>';
                data.forEach(leaveType => {
                    const option = document.createElement('option');
                    option.value = leaveType.id;
                    option.text = leaveType.name; // Already includes balance
                    leaveTypeSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching leave types:', error));
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadStaffLeaveTypes();
    });
</script>
@endsection
