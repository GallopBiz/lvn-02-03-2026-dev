@extends('backend.layouts.main')

@section('main-container')
<div class="main-content">
    <div class="breadcrumb">
        <h1>Consolidated Salary Report</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ url('consolidated-salary-report') }}" class="row mb-3">
                <div class="col-md-3">
                    <label for="month">Month</label>
                    <select name="month" class="form-control">
                        <option value="">All</option>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year">Year</label>
                    <select name="year" class="form-control">
                        <option value="">All</option>
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            @if($consolidatedReports->count())
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr class="table-primary">
                                <th>#</th>
                                <th>Month-Year</th>
                                <th>Total Gross Salary</th>
                                <th>Total Net Salary</th>
                                <th>Bank Transferred Date</th>
                                <th>Disbursement Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($consolidatedReports as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ date('F Y', mktime(0, 0, 0, $row->month, 1, $row->year)) }}</td>
                                    <td>{{ number_format($row->total_gross, 2) }}</td>
                                    <td>{{ number_format($row->total_net, 2) }}</td>
                                    <td>{{ $row->bank_transferred_date ? \Carbon\Carbon::parse($row->bank_transferred_date)->format('d M Y') : 'N/A' }}</td>
                                    <td>{{ $row->disbursement_date ? \Carbon\Carbon::parse($row->disbursement_date)->format('d M Y') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-danger">No data found for selected filters.</p>
            @endif
        </div>
    </div>
</div>
@endsection
