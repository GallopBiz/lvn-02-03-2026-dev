@extends('backend.layouts.main')

@section('main-container')
    <div class="main-content">
        <div class="container-fluid">
            <div class="breadcrumb">
                <h1 class="me-2">Shift History Assignment</h1>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card text-start mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('shifts.history') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="department_id">Department</label>
                                <select name="department_id" id="department_id" class="form-control">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
                                            {{ $department->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="position_id">Designation</label>
                                <select name="position_id" id="position_id" class="form-control">
                                    <option value="">All Designations</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}" @selected(request('position_id') == $position->id)>
                                            {{ $position->position_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="staff_type_id">Staff Type</label>
                                <select name="staff_type_id" id="staff_type_id" class="form-control">
                                    <option value="">All Staff Types</option>
                                    @foreach ($staffTypes as $staffType)
                                        <option value="{{ $staffType->id }}" @selected(request('staff_type_id') == $staffType->id)>
                                            {{ $staffType->staff_type_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="employee_status">Employment Status</label>
                                <select name="employee_status" id="employee_status" class="form-control">
                                    <option value="">Active / Blank</option>
                                    <option value="active" @selected(request('employee_status') === 'active')>Active</option>
                                    <option value="inactive" @selected(request('employee_status') === 'inactive')>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('shifts.history') }}" class="btn btn-secondary">Reset</a>
                            <button type="submit" class="btn btn-primary">Filter Employees</button>
                        </div>
                    </form>
                </div>
            </div>

            <form method="POST" action="{{ route('shifts.history.store') }}">
                @csrf
                <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                <input type="hidden" name="position_id" value="{{ request('position_id') }}">
                <input type="hidden" name="staff_type_id" value="{{ request('staff_type_id') }}">
                <input type="hidden" name="employee_status" value="{{ request('employee_status') }}">

                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card text-start">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Assign Shift</h5>
                                <div class="form-group mb-3">
                                    <label for="shift_id">Shift</label>
                                    <select name="shift_id" id="shift_id" class="form-control" required>
                                        <option value="">Select Shift</option>
                                        @foreach ($shifts as $shift)
                                            <option value="{{ $shift->id }}" @selected(old('shift_id') == $shift->id)>
                                                {{ optional($shift->shiftType)->shift_type_name ?? 'Shift '.$shift->id }}
                                                ({{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} -
                                                {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="effective_from">Effective From</label>
                                    <input type="date" name="effective_from" id="effective_from" class="form-control"
                                        value="{{ old('effective_from') }}" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="effective_to">Effective To</label>
                                    <input type="date" name="effective_to" id="effective_to" class="form-control"
                                        value="{{ old('effective_to') }}" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="remarks">Remarks</label>
                                    <textarea name="remarks" id="remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success">Assign Selected Employees</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8 mb-4">
                        <div class="card text-start">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Employees</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="select_all_shift_employees"></th>
                                                <th>Employee</th>
                                                <th>Department</th>
                                                <th>Designation</th>
                                                <th>Staff Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($employees as $employee)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="shift-employee-checkbox"
                                                            name="employee_ids[]" value="{{ $employee->id }}">
                                                    </td>
                                                    <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                                                    <td>{{ optional($employee->department)->department_name }}</td>
                                                    <td>{{ optional($employee->position)->position_name }}</td>
                                                    <td>{{ optional($employee->staffType)->staff_type_name }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">No employees found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="card text-start">
                <div class="card-body">
                    <h5 class="card-title mb-3">Recent Shift Assignments</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Shift</th>
                                    <th>Effective From</th>
                                    <th>Effective To</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentHistories as $history)
                                    <tr>
                                        <td>{{ optional($history->employee)->first_name }} {{ optional($history->employee)->last_name }}</td>
                                        <td>{{ optional(optional($history->shift)->shiftType)->shift_type_name }}</td>
                                        <td>{{ optional($history->effective_from)->format('d-m-Y') }}</td>
                                        <td>{{ optional($history->effective_to)->format('d-m-Y') }}</td>
                                        <td>{{ $history->remarks }}</td>
                                        <td>
                                            <a href="{{ route('shifts.history.edit', $history->id) }}"
                                                class="btn btn-warning btn-sm">Edit</a>
                                            <form action="{{ route('shifts.history.destroy', $history->id) }}" method="POST"
                                                style="display:inline-block;" onsubmit="confirmDelete(event)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No shift assignments found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('shifts.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('select_all_shift_employees')?.addEventListener('change', function () {
            document.querySelectorAll('.shift-employee-checkbox').forEach(function (checkbox) {
                checkbox.checked = this.checked;
            }, this);
        });

        function confirmDelete(event) {
            if (!confirm('Are you sure you want to delete this shift assignment?')) {
                event.preventDefault();
            }
        }
    </script>
@endsection
