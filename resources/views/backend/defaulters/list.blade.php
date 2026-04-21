@extends('backend.layouts.main')

@section('main-container')
    @php use Carbon\Carbon; @endphp

    <div class="main-content">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="breadcrumb">
                    <h1 class="me-2">Defaulter List</h1>
                    <form method="GET" action="{{ route('defaulters.export') }}" class="float-end">
						<input type="hidden" name="transport_filter" value="{{ request('transport_filter') }}">
						<input type="hidden" name="advance_filter" value="{{ request('advance_filter') }}">
						<input type="hidden" name="class_filter" value="{{ request('class_filter') }}">
                        <input type="hidden" name="batch_filter" value="{{ request('batch_filter') }}">
						<button type="submit" class="btn btn-success btn-sm">Export CSV</button>
					</form>

                </div>

                <form method="GET" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="transport_filter" class="form-control">
                                <option value="">-- Please Select --</option>
                                <option value="with" {{ request('transport_filter') == 'with' ? 'selected' : '' }}>With Bus</option>
                                <option value="without" {{ request('transport_filter') == 'without' ? 'selected' : '' }}>Without Bus</option>
                            </select>
                        </div>
                        <!--<div class="col-md-3">
                            <select name="advance_filter" class="form-control">
                                <option value="">-- Please Select --</option>
                                <option value="with" {{ request('advance_filter') == 'with' ? 'selected' : '' }}>With Advance</option>
                                <option value="without" {{ request('advance_filter') == 'without' ? 'selected' : '' }}>Without Advance</option>
                            </select>
                        </div>-->
						<div class="col-md-3">
							<select name="class_filter" class="form-control">
								<option value="">-- All Classes --</option>
								@foreach($allClasses as $class)
									<option value="{{ $class }}" {{ request('class_filter') == $class ? 'selected' : '' }}>
										{{ $class }}
									</option>
								@endforeach
							</select>
						</div>
                        <div class="col-md-3">
                            <select name="batch_filter" class="form-control">
                                <option value="">-- All Batches --</option>
                                @foreach($allBatches as $batch)
                                    <option value="{{ $batch }}" {{ request('batch_filter') == $batch ? 'selected' : '' }}>
                                        {{ $batch }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-primary" type="submit">Filter</button>
                            <a href="{{ route('defaulters.index') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            @forelse ($defaulters as $studentId => $data)
                                <div class="card mb-4 p-3 border rounded">
                                    <h5>
                                        Name: {{ $data['info']['name'] }} | Class: {{ $data['info']['classname'] }} | Batch: {{ $data['info']['batch'] ?? 'N/A' }} | Scholar No: {{ $data['info']['scholar_no'] }}
                                    </h5>

                                    <table class="table table-bordered mt-2">
                                        <thead>
                                            <tr>
                                                <th>Account Name</th>
                                                <th>Term</th>
                                                <th>Due Date</th>
                                                <th class="text-end">Remaining Amount (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data['dues'] as $row)
                                                @php
                                                    try {
                                                        $parsedDate = Carbon::parse($row['due_date']);
                                                    } catch (\Exception $e) {
                                                        $parsedDate = null;
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>{{ $row['account_name'] }}</td>
                                                    <td>{{ $row['term'] }}</td>
                                                    <td class="{{ $parsedDate && $parsedDate->lt(now()) ? 'text-danger' : '' }}">
                                                        {{ $row['due_date'] }}
                                                    </td>
                                                    <td class="text-end">₹{{ number_format($row['amount'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Total Remaining</th>
                                                <th class="text-end">
                                                    ₹{{ number_format(collect($data['dues'])->sum('amount'), 2) }}
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @empty
                                <p>No defaulters found.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
