@if(count($employees) > 0)
<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
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
                <td>{{ $emp['employee_name'] }}</td>
                <td>{{ $emp['staff_type_name'] }}</td>
                @foreach($leaveTypes as $leaveType)
                    <td>{{ $emp['leaves'][$leaveType->name] ?? 0 }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<p>No employee leave balance data found.</p>
@endif
