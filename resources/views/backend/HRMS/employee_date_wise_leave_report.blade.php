@extends('backend.layouts.main')
@section('main-container')

<div class="main-content">

    <div class="breadcrumb">
        <h1>Employee Leave Report Date Wise</h1>
    </div>

    <div class="separator-breadcrumb border-top"></div>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('employee.leave.report.datewise') }}" class="row mb-3" id="filterForm">

        <div class="col-md-3">
            <label>From Date</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>

        <div class="col-md-3">
            <label>To Date</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
        </div>

        <div class="col-md-4">
            <label>Employee</label>
            <select name="employee_id" class="form-control">
    <option value="">All Employees</option>
    @foreach($employees as $emp)
        <option value="{{ $emp->id }}" {{ (string) request('employee_id') === (string) $emp->id ? 'selected' : '' }}>
            {{ $emp->first_name }} {{ $emp->last_name }}
        </option>
    @endforeach
</select>

        </div>

        <div class="col-md-2 mt-3">
            <button type="submit" class="btn btn-primary w-100">View</button>
        </div>

        <div class="col-md-2 mt-3">
            <a href="{{ route('employee.leave.report.datewise') }}" class="btn btn-secondary w-100">Reset</a>
        </div>

    </form>

    {{-- Export CSV --}}
    <button class="btn btn-success mb-3" id="exportCsv">Export CSV</button>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-bordered table-striped">

            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Day</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['date'] }}</td>
                        <td>{{ $row['employee'] }}</td>
                        <td>{{ $row['leave_type'] }}</td>
                        <td>{{ $row['day_value'] }}</td>
                        <td>{{ $row['status'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No records found</td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

</div>

{{-- Export CSV Script --}}
<script>
    document.getElementById('exportCsv').addEventListener('click', function() {
        const form = document.getElementById('filterForm');
        const from_date = form.querySelector('input[name="from_date"]').value;
        const to_date = form.querySelector('input[name="to_date"]').value;
        const employee_id = form.querySelector('select[name="employee_id"]').value;

        const url = new URL("{{ route('employee.leave.report.datewise.csv') }}", window.location.origin);
        url.searchParams.set('from_date', from_date);
        url.searchParams.set('to_date', to_date);
        url.searchParams.set('employee_id', employee_id);

        window.location.href = url.toString();
    });
</script>

@endsection
