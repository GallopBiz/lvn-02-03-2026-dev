@extends('backend.layouts.main')

@section('main-container')
<style>
    .uperletter {
        text-transform: capitalize;
    }
</style>

<div class="main-content">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Leave Master</h1>
        </div>

        <div class="separator-breadcrumb border-top"></div>

        <form id="progress-form" class="p-4 progress-form"
            action="{{ !empty($leaveAllocation) ? url('store-leave_allocation') : url('save-leave_allocation') }}"
            method="post">
            @csrf

            @if(!empty($leaveAllocation))
                <input type="hidden" name="id" value="{{ $leaveAllocation->Leave_Allocation_ID }}">
            @endif

            <div class="row">
                <div class="col-md-3 form-group mb-3">
                    <label for="Staff_Type_ID">Staff Type</label>
                    <select required name="Staff_Type_ID" id="Staff_Type_ID" class="form-control" onchange="updateLeaveFields()">
                        <option value="">Select Staff Type</option>
                        @foreach($staffTypes as $staffType)
                            <option value="{{ $staffType->Staff_Type_ID }}"
                                {{ isset($leaveAllocation) && $leaveAllocation->Staff_Type_ID == $staffType->Staff_Type_ID ? 'selected' : '' }}>
                                {{ $staffType->Type }}
                            </option>
                        @endforeach
                    </select>
                    @error('Staff_Type_ID')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="PositionName">Positions</label>
                    <select required name="PositionName" id="PositionName" class="form-control">
                        <option value="">Select Position</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->PositionName }}"
                                {{ isset($leaveAllocation) && $leaveAllocation->PositionName == $pos->PositionName ? 'selected' : '' }}>
                                {{ $pos->PositionName }}
                            </option>
                        @endforeach
                    </select>
                    @error('PositionName')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="DepartmentName">Department</label>
                    <select required name="DepartmentName" id="DepartmentName" class="form-control">
                        <option value="">Select Department</option>
                        @foreach($departments as $dep)
                            <option value="{{ $dep->DepartmentName }}"
                                {{ isset($leaveAllocation) && $leaveAllocation->DepartmentName == $dep->DepartmentName ? 'selected' : '' }}>
                                {{ $dep->DepartmentName }}
                            </option>
                        @endforeach
                    </select>
                    @error('DepartmentName')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="leave_name">Leave Name</label>
                    <input
                        required
                        class="form-control"
                        id="leave_name"
                        name="leave_name"
                        type="text"
                        value="{{ $leaveAllocation->leave_name ?? '' }}"
                        placeholder="Leave Name"
                    />
                    @error('leave_name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="short_name">Short Name</label>
                    <input
                        required
                        class="form-control"
                        id="short_name"
                        name="short_name"
                        type="text"
                        value="{{ $leaveAllocation->short_name ?? '' }}"
                        placeholder="Short Name"
                    />
                    @error('short_name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="CL_Allocation">CL Allocation</label>
                    <input
                        required
                        class="form-control uperletter"
                        id="CL_Allocation"
                        name="CL_Allocation"
                        type="number"
                        value="{{ $leaveAllocation->CL_Allocation ?? '' }}"
                        placeholder="CL Allocation"
                        min="0"
                        step="any"
                    />
                    <small class="form-text text-muted">Enter only numbers.</small>
                    @error('CL_Allocation')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="ML_Allocation">ML Allocation</label>
                    <input
                        required
                        class="form-control uperletter"
                        id="ML_Allocation"
                        name="ML_Allocation"
                        type="number"
                        value="{{ $leaveAllocation->ML_Allocation ?? '' }}"
                        placeholder="ML Allocation"
                        min="0"
                        step="any"
                    />
                    <small class="form-text text-muted">Enter only numbers.</small>
                    @error('ML_Allocation')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="EL_Allocation">EL Allocation</label>
                    <input
                        required
                        class="form-control uperletter"
                        id="EL_Allocation"
                        name="EL_Allocation"
                        type="number"
                        value="{{ $leaveAllocation->EL_Allocation ?? '' }}"
                        placeholder="EL Allocation"
                        min="0"
                        step="any"
                    />
                    <small class="form-text text-muted">Enter only numbers.</small>
                    @error('EL_Allocation')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="Max_CL_At_Time">Can enjoy at once CL</label>
                    <input
                        required
                        class="form-control uperletter"
                        id="Max_CL_At_Time"
                        name="Max_CL_At_Time"
                        type="number"
                        value="{{ $leaveAllocation->Max_CL_At_Time ?? '' }}"
                        placeholder="Can enjoy at once CL"
                        min="0"
                        step="any"
                    />
                    <small class="form-text text-muted">Enter only numbers.</small>
                    @error('Max_CL_At_Time')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="Max_EL_At_Time">Can enjoy at once EL</label>
                    <input
                        required
                        class="form-control uperletter"
                        id="Max_EL_At_Time"
                        name="Max_EL_At_Time"
                        type="number"
                        value="{{ $leaveAllocation->Max_EL_At_Time ?? '' }}"
                        placeholder="Can enjoy at once EL"
                        min="0"
                        step="any"
                    />
                    <small class="form-text text-muted">Enter only numbers.</small>
                    @error('Max_EL_At_Time')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="Max_ML_At_Time">Can enjoy at once ML</label>
                    <input
                        required
                        class="form-control uperletter"
                        id="Max_ML_At_Time"
                        name="Max_ML_At_Time"
                        type="number"
                        value="{{ $leaveAllocation->Max_ML_At_Time ?? '' }}"
                        placeholder="Can enjoy at once ML"
                        min="0"
                        step="any"
                    />
                    <small class="form-text text-muted">Enter only numbers.</small>
                    @error('Max_ML_At_Time')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="is_paid">Leave Type</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_paid" id="paid_yes" value="1"
                        {{ isset($leaveAllocation) && $leaveAllocation->is_paid == 1 ? 'checked' : '' }} required>
                        <label class="form-check-label" for="paid_yes">Paid</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_paid" id="paid_no" value="0"
                        {{ isset($leaveAllocation) && $leaveAllocation->is_paid == 0 ? 'checked' : '' }} required>
                        <label class="form-check-label" for="paid_no">Unpaid</label>
                    </div>
                    @error('is_paid')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
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
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Submit</button>
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
                        <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Staff Type</th>
                                    <th>CL Allocation</th>
                                    <th>ML Allocation</th>
                                    <th>EL Allocation</th>
                                    <th>Can enjoy at once CL</th>
                                    <th>Can enjoy at once EL</th>
                                    <th>Can enjoy at once ML</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($leaveAllocations))
                                    @foreach($leaveAllocations as $index => $allocation)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $allocation->staffType ? $allocation->staffType->Type : 'Staff Type Not Found' }}</td>
                                            <td>{{ $allocation->CL_Allocation }}</td>
                                            <td>{{ $allocation->ML_Allocation }}</td>
                                            <td>{{ $allocation->EL_Allocation }}</td>
                                            <td>{{ $allocation->Max_CL_At_Time }}</td>
                                            <td>{{ $allocation->Max_EL_At_Time }}</td>
                                            <td>{{ $allocation->Max_ML_At_Time }}</td>

                                            <td class='d-flex'>
                                                <a class="btn btn-primary m-1" href="{{ url('view-leave_allocation/' . $allocation->Leave_Allocation_ID) }}">Edit</a>
                                                <a class="btn btn-raised ripple btn-danger m-1" href="{{ url('delete-leave_allocation/' . $allocation->Leave_Allocation_ID) }}" onclick="confirmDelete(event)">Delete</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="9" class="text-center">No Data Found</td></tr>
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
    
    function updateLeaveFields() {
        const staffType = document.getElementById('Staff_Type_ID').value;
        const isVacation = document.querySelector('input[name="is_vacation"]:checked') ? document.querySelector('input[name="is_vacation"]:checked').value : 'no';

        if (staffType == 1) { // Assuming ID 1 corresponds to 'Permanent'
            document.getElementById('CL_Allocation').value = 13;
            document.getElementById('ML_Allocation').value = 10;
            document.getElementById('Max_CL_At_Time').value = 5;
            document.getElementById('EL_Allocation').value = isVacation === 'yes' ? 30 : 0;
        } else if (staffType == 2) { // Assuming ID 2 corresponds to 'Temporary'
            document.getElementById('CL_Allocation').value = 1;
            document.getElementById('ML_Allocation').value = 0;
            document.getElementById('Max_CL_At_Time').value = 3;
            document.getElementById('EL_Allocation').value = 0;
        } else if (staffType == 3) { // Assuming ID 3 corresponds to 'Guest Faculty'
            document.getElementById('CL_Allocation').value = 0;
            document.getElementById('ML_Allocation').value = 0;
            document.getElementById('EL_Allocation').value = 0;
            document.getElementById('Max_CL_At_Time').value = 0;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('Staff_Type_ID').addEventListener('change', updateLeaveFields);
        document.querySelectorAll('input[name="is_vacation"]').forEach((radio) => {
            radio.addEventListener('change', updateLeaveFields);
        });
    });
</script>

@endsection