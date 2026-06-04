@if(count($employees) > 0)
<form method="POST" action="{{ route('employee.leave.balances.update') }}">
    @csrf
    @if(request('search'))
        <input type="hidden" name="search" value="{{ request('search') }}">
    @endif
<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Staff Type</th>
                @foreach($leaveTypes as $leaveType)
                    <th>{{ $leaveType->name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $emp)
            <tr>
                <td>{{ $emp['employee_id'] }}</td>
                <td>{{ $emp['employee_name'] }}</td>
                <td>{{ $emp['staff_type_name'] }}</td>
                @foreach($leaveTypes as $leaveType)
                    <td style="min-width: 120px;">
                        <input
                            type="number"
                            name="balances[{{ $emp['employee_id'] }}][{{ $leaveType->id }}]"
                            class="form-control"
                            value="{{ $emp['leaves'][$leaveType->id] ?? 0 }}"
                            min="0"
                            step="0.5"
                        >
                    </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
    <button type="submit" class="btn btn-primary">Update Leave Balances</button>
</form>
@else
<p>No employee leave balance data found.</p>
@endif
