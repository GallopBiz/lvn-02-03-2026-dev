@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h3 class="me-2">Employee Leave Balance</h3>
        </div>
    </div>
    <div class="separator-breadcrumb border-top mb-3"></div>

    <div class="card text-start">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Search Form --}}
            <form id="searchForm" method="GET" action="{{ route('employee.leave.balances') }}">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Search by employee name" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="{{ route('employee.leave.balances') }}" class="btn btn-secondary ms-2">Clear</a>
                    </div>
                </div>
            </form>

            <form method="POST" action="{{ route('employee.leave.balances.import') }}" enctype="multipart/form-data" class="mb-3">
                @csrf
                <div class="row align-items-end g-2">
                    <div class="col-md-6">
                        <label for="leave_balance_file">Bulk Import Leave Balances</label>
                        <input type="file" name="leave_balance_file" id="leave_balance_file" class="form-control" accept=".csv,.txt" required>
                        <small class="form-text text-muted">Download the sample, update the balance column, then import the same file.</small>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2 justify-content-md-end">
                            <a href="{{ route('employee.leave.balances.sample') }}" class="btn btn-secondary">Download Sample CSV</a>
                            <button type="submit" class="btn btn-warning">Import CSV</button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div id="leave-balance-table">
                @include('backend.HRMS.employee_leave_balance_table')
            </div>

        </div>
    </div>
</div>
@endsection
