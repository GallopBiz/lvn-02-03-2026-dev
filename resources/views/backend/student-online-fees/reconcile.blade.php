@extends('backend.layouts.main')
@section('main-container')

<div class="container">
    <h2>Daily Online Fees Reconciliation</h2>

    <!-- Filter Form -->
    <form method="GET" action="">
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="year" class="form-label">Year</label>
                <input type="number" class="form-control" id="year" name="year" value="{{ old('year', $year ?? '') }}" required>
            </div>
            <div class="col-md-3">
                <label for="month" class="form-label">Month</label>
                <input type="number" class="form-control" id="month" name="month" value="{{ old('month', $month ?? '') }}" required min="1" max="12">
            </div>
            <div class="col-md-3">
                <label for="day" class="form-label">Day (Optional)</label>
                <input type="number" class="form-control" id="day" name="day" value="{{ old('day', $day ?? '') }}" min="1" max="31">
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

    <!-- Display Reconciliation Data -->
    @if (isset($data) && !empty($data) && $data['count'] > 0)
        @php
            // Calculate totals for each settlement_id
            $totals = [];
            foreach ($data['items'] as $item) {
                $settlement_id = $item['settlement_id'];
                if (!isset($totals[$settlement_id])) {
                    $totals[$settlement_id] = 0;
                }
                $totals[$settlement_id] += $item['amount'];
            }
        @endphp
        <div class="card text-start">
            <div class="card-body">
                <div class="table-responsive">
                    <div class="form-group mb-3 float-end">
                        <a href="{{ route('student-daily-online-fees-reconcile.export-reconciliation') . '?' . http_build_query(request()->query()) }}" class="btn btn-success">Export Reconciliation</a>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>S No.</th>
                                <th>Entity ID</th>
                                <th>Settlement ID</th>
                                <th>Amount</th>
                                <th>Fee</th>
                                <th>Tax</th>
                                <th>On Hold</th>
                                <th>Settled</th>
                                <th>Created At</th>
                                <th>Settled At</th>
                                <th>Total (for Settlement ID)</th>
                                <th>Settlement UTR</th>
                                <th>Method</th>
                                <th>Card Network</th>
                                <th>Card Issuer</th>
                                <th>Card Type</th>
                                <th>Dispute ID</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['items'] as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item['entity_id'] }}</td>
                                    <td>{{ $item['settlement_id'] }}</td>
                                    <td>{{ number_format($item['amount'] / 100, 2) }}</td>
                                    <td>{{ number_format($item['fee'] / 100, 2) }}</td>
                                    <td>{{ number_format($item['tax'] / 100, 2) }}</td>
                                    <td>{{ $item['on_hold'] ? 'Yes' : 'No' }}</td>
                                    <td>{{ $item['settled'] ? 'Yes' : 'No' }}</td>
                                    <td>{{ date('d-m-Y H:i:s', $item['created_at']) }}</td>
                                    <td>{{ date('d-m-Y H:i:s', $item['settled_at']) }}</td>
                                    <td>{{ number_format($totals[$item['settlement_id']] / 100, 2) }}</td>
                                    <td>{{ $item['settlement_utr'] }}</td>
                                    <td>{{ $item['method'] }}</td>
                                    <td>{{ $item['card_network'] ?: 'N/A' }}</td>
                                    <td>{{ $item['card_issuer'] ?: 'N/A' }}</td>
                                    <td>{{ $item['card_type'] ?: 'N/A' }}</td>
                                    <td>{{ $item['dispute_id'] ?: 'N/A' }}</td>
                                    <td>{{ $item['notes'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <p>No reconciliation data found for the selected date range.</p>
    @endif
</div>

@endsection
