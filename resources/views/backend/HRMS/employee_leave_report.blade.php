@extends('backend.layouts.main')
@section('main-container')

<div class="main-content">

    <div class="breadcrumb">
        <h1>Employee Leave Report</h1>
    </div>

    <div class="separator-breadcrumb border-top"></div>

    {{-- ================= FILTER FORM ================= --}}
    <form method="GET" action="{{ route('employee.leave.report') }}" class="row mb-4">

        <div class="col-md-3">
            <label>From Date</label>
            <input type="date"
                   name="from_date"
                   class="form-control"
                   value="{{ request('from_date') }}">
        </div>

        <div class="col-md-3">
            <label>To Date</label>
            <input type="date"
                   name="to_date"
                   class="form-control"
                   value="{{ request('to_date') }}">
        </div>

        <div class="col-md-3">
            <label>Employee</label>
            <select name="employee_id" class="form-control">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}"
                        {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->first_name }} {{ $emp->last_name }}
                        ({{ optional($emp->biometricDetail)->ess_emp_code ?? 'NA' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>Leave Type</label>
            <select name="leave_type_id" class="form-control">
                <option value="">All Leave Types</option>
                @foreach($leaveTypes as $lt)
                    <option value="{{ $lt->id }}"
                        {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>
                        {{ $lt->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 mt-3">
            <button type="submit" class="btn btn-primary w-100">
                View
            </button>
        </div>

        <div class="col-md-2 mt-3">
            <a href="{{ route('employee.leave.report') }}"
               class="btn btn-secondary w-100">
                Reset
            </a>
        </div>

        {{-- Export CSV Button --}}
        @if(request()->filled('from_date') || request()->filled('to_date') || request()->filled('employee_id') || request()->filled('leave_type_id'))
    <div class="col-md-2 mt-3">
        <a href="{{ route('employee.leave.export', request()->query()) }}"
           class="btn btn-success w-100">
           Export CSV
        </a>
    </div>
@endif


    </form>

    {{-- ================= TABLE ================= --}}
    @if(request()->filled('from_date') || request()->filled('to_date') || request()->filled('employee_id') || request()->filled('leave_type_id'))

        <div class="table-responsive">
            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>ESS Code</th>
                        @foreach($months as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse($employees as $emp)
                        <tr>
                            <td>{{ $emp->first_name }} {{ $emp->last_name }}</td>
                            <td>{{ optional($emp->biometricDetail)->ess_emp_code ?? '-' }}</td>

                            @foreach($months as $ym => $label)
                                <td class="text-center">
                                    {{ $matrix[$emp->id][$ym] ?? 0 }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($months) + 2 }}" class="text-center">
                                No records found
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    @else
        {{-- Default state --}}
        <div class="alert alert-info">
            Please apply filter to view leave records.
        </div>
    @endif

</div>

@endsection
