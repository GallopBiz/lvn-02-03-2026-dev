@extends('backend.layouts.main')
@section('main-container')

<div class="container">
    <h2>Daily Online Fees Settlements</h2>

    <!-- Filter Form -->
    <form method="GET" action="{{ url('student-daily-online-fees-settlement') }}">
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="from_date" class="form-label">From Date</label>
                <input type="date" class="form-control" id="from_date" name="from_date" value="{{ old('from_date', $fromDate ?? '') }}" required>
            </div>
            <div class="col-md-3">
                <label for="to_date" class="form-label">To Date</label>
                <input type="date" class="form-control" id="to_date" name="to_date" value="{{ old('to_date', $toDate ?? '') }}" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </div>
    </form>

    <!-- Display Error Message -->
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Display Settlements Table -->
    @if (!empty($settlements))
        <div class="table-responsive">
            <div class="form-group mb-3 float-end">
                <a href="{{ route('student-online-fees.export-settlement') . '?' . http_build_query(request()->query()) }}" class="btn btn-secondary">Export Settlements</a>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Fees</th>
                        <th>Tax</th>
                        <th>UTR</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($settlements as $index => $settlement)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $settlement['id'] }}</td>
                            <td>{{ number_format($settlement['amount'] / 100, 2) }}</td>
                            <td>{{ $settlement['status'] }}</td>
                            <td>{{ number_format($settlement['fees'] / 100, 2) }}</td>
                            <td>{{ number_format($settlement['tax'] / 100, 2) }}</td>
                            <td>{{ $settlement['utr'] }}</td>
                            <td>{{ date('d-m-Y H:i:s', $settlement['created_at']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p>No settlements found for the selected date range.</p>
    @endif
</div>
@endsection

