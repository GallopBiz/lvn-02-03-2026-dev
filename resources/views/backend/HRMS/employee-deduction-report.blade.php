@extends('backend.layouts.main')

@section('main-container')
<div class="main-content">
    <div class="breadcrumb">
        <h1 class="me-2">Employee Deduction Report</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="card text-start">
        <div class="card-body">
            <form action="{{ route('employee_deduction_report') }}" method="GET" class="row mb-4">
                <div class="col-md-2">
                    <label for="month">Month</label>
                    <select name="month" class="form-control">
                        <option value="">-- All --</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="year">Year</label>
                    <select name="year" class="form-control">
                        <option value="">-- All --</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="employee_id">Employee</label>
                    <select name="employee_id" class="form-control">
                        <option value="">-- All --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('employee_deduction_report') }}" class="btn btn-secondary me-2">Reset</a>
                </div>
                <div class="col-md-2 mt-2 d-flex align-items-end">
                    <button type="submit" name="export" value="1" class="btn btn-success">Export CSV</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="display table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Deduction Type</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deductionRows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row['employee_name'] }}</td>
                                <td>{{ $row['month'] }}</td>
                                <td>{{ $row['year'] }}</td>
                                <td>{{ $row['deduction_type'] }}</td>
                                <td>{{ number_format($row['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
