@extends('backend.layouts.main')

@section('main-container')
    <style>
        .uperletter {
            text-transform: capitalize;
        }

        .date-list {
            list-style: none;
            padding: 0;
        }

        .date-list li {
            background: #f8f9fa;
            /* Light gray for the list items */
            border: 1px solid #ddd;
            padding: 5px 10px;
            margin-bottom: 2px;
            border-radius: 4px;
        }
    </style>
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <div class="main-content">
        <div class="form_section1_div">
            <div class="breadcrumb">
                <h1 class="me-2">Leave Staff Allocation</h1>
            </div>

            <div class="separator-breadcrumb border-top"></div>

            <form id="progress-form" class="p-4 progress-form"
                action="{{ !empty($leaveAllocation) ? url('store-leave_staff_allocation') : url('save-leave_staff_allocation') }}"
                method="post">
                @csrf

                @if (!empty($leaveAllocation))
                    <input type="hidden" name="id" value="{{ $leaveAllocation->id }}">
                @endif

                <div class="row">
                    <div class="col-md-3 form-group mb-3">
                        <label for="hrms_staff_type_id">Staff Type</label>
                        <select name="hrms_staff_type_id" id="hrms_staff_type_id" class="form-control">
                            <option value="">Select Staff Type</option>
                            @foreach ($staffTypes as $staffType)
                                <option value="{{ $staffType->id }}"
                                    {{ isset($leaveAllocation) && $leaveAllocation->hrms_staff_type_id == $staffType->id ? 'selected' : '' }}>
                                    {{ $staffType->staff_type_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('hrms_staff_type_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 form-group mb-3">
                        <label for="leave_type_id">Leave Type</label>
                        <select name="leave_type_id" id="leave_type_id" class="form-control">
                            <option value="">Select Leave Type</option>
                            @foreach ($leaveTypes as $leaveType)
                                <option value="{{ $leaveType->id }}"
                                    {{ isset($leaveAllocation) && $leaveAllocation->leave_type_id == $leaveType->id ? 'selected' : '' }}>
                                    {{ $leaveType->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 form-group mb-3">
                        <label for="max_allowed">Max. Leaves Allowed</label>
                        <input class="form-control uperletter" id="max_allowed" name="max_allowed" type="number"
                            value="{{ $leaveAllocation->max_allowed ?? '' }}" placeholder="Max. Leaves Allowed"
                            min="0" step="any" />
                        <small class="form-text text-muted">Enter only numbers.</small>
                        @error('max_allowed')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 form-group mb-3">
                        <label for="max_per_instance">Maximum Leave Allowed at a Time</label>
                        <input class="form-control uperletter" id="max_per_instance" name="max_per_instance" type="number"
                            value="{{ $leaveAllocation->max_per_instance ?? '' }}"
                            placeholder="Maximum Leave Allowed at a Time" min="0" step="any" />
                        <small class="form-text text-muted">Enter only numbers.</small>
                        @error('max_per_instance')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- <div class="col-md-3 form-group mb-3">
                    <label for="is_vacation">Vacation Staff</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_vacation" id="vacation_yes" value="yes"
                        {{ isset($leaveAllocation) && $leaveAllocation->is_vacation == 'yes' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="vacation_yes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_vacation" id="vacation_no" value="no"
                        {{ isset($leaveAllocation) && $leaveAllocation->is_vacation == 'no' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="vacation_no">No</label>
                    </div>
                    @error('is_vacation')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div> --}}

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-secondary" id="reset-button">Reset</button>
                        @if (request()->route()->getName() !== 'leave_staff_allocation')
                            <a href="{{ url('leave_staff_allocation') }}" class="btn btn-primary">Add New</a>
                        @endif

                    </div>
                </div>
            </form><br>
        </div>

        <div class="separator-breadcrumb border-top"></div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="breadcrumb">
                    <h1 class="me-2">List of Leave Allocations:</h1>
                </div>
                <div class="separator-breadcrumb border-top"></div>

                <div class="card text-start">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper"
                                style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Staff Type</th>
                                        <th>Leave Type</th>
                                        <th>Max. Leaves Allowed</th>
                                        <th>Maximum Leave Allowed at a Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($leaveAllocations))
                                        @foreach ($leaveAllocations as $index => $allocation)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $allocation->staffType ? $allocation->staffType->staff_type_name : 'Staff Type Not Found' }}
                                                </td>
                                                <td>{{ $allocation->leaveType ? $allocation->leaveType->name : 'Leave Type Not Found' }}
                                                </td>
                                                <td>{{ $allocation->max_allowed }}</td>
                                                <td>{{ $allocation->max_per_instance }}</td>

                                                <td class='d-flex'>
                                                    <a class="btn btn-primary m-1"
                                                        href="{{ url('view-leave_staff_allocation/' . $allocation->id) }}">Edit</a>
                                                    <a class="btn btn-raised ripple btn-danger m-1"
                                                        href="{{ url('delete-leave_staff_allocation/' . $allocation->id) }}"
                                                        onclick="confirmDelete(event)">Delete</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
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

    <!-- SweetAlert2 Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        function confirmDelete(event) {
            event.preventDefault();

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
                    window.location.href = event.target.href;
                }
            });
        }

        function resetForm() {
                $("#hrms_staff_type_id").val("");
                $("#leave_type_id").val("");
                $("#max_allowed").val("");
                $("#max_per_instance").val("");
        }
        document.addEventListener('DOMContentLoaded', () => {
            // Attach event listener to the reset button
            document.getElementById('reset-button').addEventListener('click', resetForm);
        });
    </script>

@endsection
