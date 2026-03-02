@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h3 class="me-2">Collection Report</h3>
        </div>
    </div>
    <br>
    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card text-start">
                <div class="card-body">

                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('backend.dailycollection.index') }}" class="mb-4">
                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <label for="from_date">From Date:</label>
                                <input type="date" name="from_date" id="from_date" class="form-control"
                                    value="{{ old('from_date', $fromDate ?? '') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="to_date">To Date:</label>
                                <input type="date" name="to_date" id="to_date" class="form-control"
                                    value="{{ old('to_date', $toDate ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="year">Financial Year Start:</label>
                                <input type="number" name="year" id="year" class="form-control" placeholder="e.g. 2024"
                                    value="{{ old('year', $year ?? '') }}" min="2000" max="2099" step="1">
                                <small class="form-text text-muted">Enter start year (April 1 - March 31)</small>
                            </div>
                            <div class="col-md-4 d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('backend.dailycollection.index') }}" class="btn btn-secondary">Clear</a>
                                <a href="{{ route('backend.dailycollection.export', ['from_date' => $fromDate, 'to_date' => $toDate]) }}"
                                    class="btn btn-success">Export Daily CSV</a>
                            </div>
                        </div>
                    </form>

                    {{-- Daily Collection Table --}}
                    <h4>Daily Collection Report</h4>
                    @if($collections->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Sr. No</th>
										<th>Voucher No.</th>
                                        <th>Scholar No</th>
                                        <th>Class Section</th>
                                        <th>Student Name</th>
                                        <th>Sub Total Received</th>
                                        <th>Payment Date</th>
                                        <th>Payment By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($collections as $item)
                                    <tr>
                                        <td>{{ ($collections->currentPage() - 1) * $collections->perPage() + $loop->iteration }}</td>
										<td>{{ $item->id }}</td>
                                        <td>{{ $item->name_scholarno ?? '-' }}</td>
                                        <td>{{ $item->name_classsection ?? '-' }}</td>
                                        <td>{{ $item->name_student ?? '-' }}</td>
                                        <td>{{ $item->sub_total_received ?? '-' }}</td>
                                        <td>{{ $item->payment_date ?? '-' }}</td>
                                        <td>{{ $item->payment_by_select ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Daily Pagination --}}
                        {{ $collections->appends(request()->except('page'))->links('pagination::bootstrap-4') }}

                    @else
                        <p>No daily collection records found for the selected date range.</p>
                    @endif

                    {{-- Yearly Collection Table --}}
                    @if($year)
                        <hr>
                        <h4>Yearly Collection Report: April 1, {{ $year }} - March 31, {{ $year + 1 }}</h4>
                        @if($yearlyCollections && $yearlyCollections->count() > 0)
							<a href="{{ route('backend.dailycollection.exportYearly', ['year' => $year]) }}" class="btn btn-success mb-3">
								Export Yearly CSV
							</a>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Sr. No</th>
											<th>Voucher No.</th>
                                            <th>Scholar No</th>
                                            <th>Class Section</th>
                                            <th>Student Name</th>
                                            <th>Sub Total Received</th>
                                            <th>Payment Date</th>
                                            <th>Payment By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($yearlyCollections as $item)
                                        <tr>
                                            <td>{{ ($yearlyCollections->currentPage() - 1) * $yearlyCollections->perPage() + $loop->iteration }}</td>
											<td>{{ $item->id }}</td>
                                            <td>{{ $item->name_scholarno ?? '-' }}</td>
                                            <td>{{ $item->name_classsection ?? '-' }}</td>
                                            <td>{{ $item->name_student ?? '-' }}</td>
                                            <td>{{ $item->sub_total_received ?? '-' }}</td>
                                            <td>{{ $item->payment_date ?? '-' }}</td>
                                            <td>{{ $item->payment_by_select ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Yearly Pagination --}}
                            {{ $yearlyCollections->appends(request()->except('pageYearly'))->links('pagination::bootstrap-4') }}

                        @else
                            <p>No yearly collection records found for the financial year.</p>
                        @endif
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
