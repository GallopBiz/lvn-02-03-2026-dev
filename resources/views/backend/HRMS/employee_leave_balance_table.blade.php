@if(count($employees) > 0)
<style>
    /* Leave balance table: sticky header + horizontal and vertical scrolling */
    .leave-table-container {
        max-height: 520px;
        overflow-x: auto;
        overflow-y: auto;
        border: 1px solid #e9ecef;
    }
    .leave-table {
        min-width: 100%;
        border-collapse: collapse;
    }
    .leave-table thead th {
        position: sticky;
        top: 0;
        background-color: #343a40 !important; /* dark header */
        color: #ffffff !important; /* light text */
        z-index: 3;
    }
    .leave-table th, .leave-table td { white-space: nowrap; }
</style>

<form method="POST" action="{{ route('employee.leave.balances.update') }}">
    @csrf
    @if(request('search'))
        <input type="hidden" name="search" value="{{ request('search') }}">
    @endif

    <div class="leave-table-container">
        <table class="table table-bordered leave-table">
        <thead class="table-dark">
            <tr>
                <th>Sr.</th>
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
                <td>{{ $loop->iteration }}</td>
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
