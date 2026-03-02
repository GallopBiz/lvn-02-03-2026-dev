@extends('backend.layouts.main')
@section('main-container')

<div class="main-content">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Leave Allocation for Employees</h1>
        </div>

        <div class="separator-breadcrumb border-top"></div>

        <form id="filter-leaves-form" class="p-4" action="{{ url('filter-leaves') }}" method="POST">
            @csrf
            <div class="row">
                <!-- Staff Type Selection -->
                <div class="col-md-3 form-group mb-3">
                    <label for="staff_name">Staff Type</label>
                    <select required name="staff_name" id="staff_name" class="form-control">
                        <option value="">Select Staff Type</option>
                        @foreach($staffTypes as $staffType)
                            <option value="{{ $staffType->Staff_Type_ID }}"
                                {{ isset($staff_name) && $staff_name == $staffType->Staff_Type_ID ? 'selected' : '' }}>
                                {{ $staffType->Type }}
                            </option>
                        @endforeach
                    </select>
                </div>
        
                <!-- Leave Name Selection -->
                <div class="col-md-3 form-group mb-3">
                    <label for="leave_name">Leave Name</label>
                    <select required name="leave_name" id="leave_name" class="form-control">
                        <option value="">Select Leave Name</option>
                        @foreach($leaveAllocations as $leave)
                            <option value="{{ $leave->Leave_Allocation_ID }}"
                                {{ isset($leave_name) && $leave_name == $leave->Leave_Allocation_ID ? 'selected' : '' }}>
                                {{ $leave->leave_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
        
                <!-- Submit Button -->
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
        
        <!-- Separator -->
        <div class="separator-breadcrumb border-top"></div>

        <!-- Table to Display Leave Allocation -->
        @if(!empty($filteredLeaveAllocations))
            <div class="card text-start">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="leave_allocation_table">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Staff Type</th>
                                    <th>Leave Type</th>
                                    <th>CL Allocation</th>
                                    <th>ML Allocation</th>
                                    <th>EL Allocation</th>
                                    <th>Assign to Employee (A)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($filteredLeaveAllocations as $index => $leave)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $leave->staffType->Type ?? 'N/A' }}</td>
                                        <td>{{ $leave->leave_name }}</td>
                                        <td>{{ $leave->CL_Allocation }}</td>
                                        <td>{{ $leave->ML_Allocation }}</td>
                                        <td>{{ $leave->EL_Allocation }}</td>
                                        <td>
                                            <input type="number" name="assigned_leave" class="form-control assign-leave"
                                                data-leave-id="{{ $leave->id }}" placeholder="Assign leave">
                                        </td>
                                        <td>
                                            <button class="btn btn-primary save-leave" data-leave-id="{{ $leave->id }}">Save</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info">No leave allocation data found for the selected criteria.</div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.save-leave').forEach(function(button) {
            button.addEventListener('click', function() {
                const leaveId = this.getAttribute('data-leave-id');
                const assignedLeave = document.querySelector(`input[data-leave-id="${leaveId}"]`).value;

                fetch("{{ url('save-assigned-leave') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        leave_id: leaveId,
                        assigned_leave: assignedLeave
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Assigned leave saved successfully.');
                        location.reload();  // Reload to reflect changes
                    } else {
                        alert('Failed to save assigned leave.');
                    }
                })
                .catch(error => {
                    alert('An error occurred. Please try again.');
                    console.error('Error:', error);
                });
            });
        });
    });
</script>

@endsection
