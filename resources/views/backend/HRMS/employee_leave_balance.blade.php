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

            {{-- Table --}}
            <div id="leave-balance-table">
                @include('backend.HRMS.employee_leave_balance_table')
            </div>

        </div>
    </div>
</div>
@endsection
