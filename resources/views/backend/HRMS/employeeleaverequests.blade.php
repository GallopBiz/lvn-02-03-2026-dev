@extends('backend.layouts.main')
@section('main-container')

<style>
    .uperletter {
        text-transform: capitalize;
    }
</style>
<meta name="csrf-token" content="{{ csrf_token() }}">

@php $i = 0; @endphp
<div class="main-content">
    <div class="separator-breadcrumb border-top"></div>
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="breadcrumb">
                <h1 class="me-2">Employees Leave Requests :-</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>

            <div class="card text-start">
                <div class="card-body">
					<form method="GET" action="{{ url()->current() }}" class="row g-2 mb-3">
						<div class="col-md-3">
							<select name="employee_id" class="form-select">
								<option value="">Employee Wise</option>
								@foreach($employees as $emp)
									<option value="{{ $emp->id }}"
										{{ request('employee_id') == $emp->id ? 'selected' : '' }}>
										{{ $emp->first_name }} {{ $emp->last_name }}
									</option>
								@endforeach
							</select>
						</div>

						<div class="col-md-2">
							<select name="status" class="form-select">
								<option value="">Leave Status</option>
								<option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
								<option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
								<option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
							</select>
						</div>

						<div class="col-md-2">
							<input type="date" name="from_date" class="form-control"
								   value="{{ request('from_date') }}">
						</div>

						<div class="col-md-2">
							<input type="date" name="to_date" class="form-control"
								   value="{{ request('to_date') }}">
						</div>

						<div class="col-md-2">
							<input type="text" name="search" class="form-control"
								   placeholder="Search..."
								   value="{{ request('search') }}">
						</div>

						<div class="col-md-2 d-flex gap-2 align-items-end">
							<button type="submit" class="btn btn-primary w-100">
								Filter
							</button>

							<a href="{{ url()->current() }}" class="btn btn-outline-secondary w-100">
								Reset
							</a>
						</div>
					</form>

                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Employee Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Total Applied Leaves</th>
                                    <th>Leave Type</th>
                                    <th>Half Day Type</th>
                                    <th>Note</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stream as $streams)
                                    <tr>
                                        <td>
											{{ ($stream->currentPage() - 1) * $stream->perPage() + $loop->iteration }}
										</td>
                                        <td class="uperletter">{{ $streams->employee->first_name }} {{ $streams->employee->last_name }}</td>
                                        <td>{{ date('d-m-Y', strtotime($streams->start_date)) }}</td>
                                        <td>{{ date('d-m-Y', strtotime($streams->end_date)) }}</td>
                                        <td>
                                            @if($streams->is_half_day)
                                                0.5
                                            @else
                                                @php
                                                    $start_date = new DateTime($streams->start_date);
                                                    $end_date = new DateTime($streams->end_date);
                                                    $interval = $start_date->diff($end_date);
                                                    echo $interval->days + 1;
                                                @endphp
                                            @endif
                                        </td>
                                        <td class="uperletter">{{ $streams->leaveType->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($streams->is_half_day)
                                                {{ ucfirst($streams->half_day_type) }} Half
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ strlen($streams->reason) > 60 ? substr($streams->reason, 0, 60) . '...' : $streams->reason }}</td>
                                        <td class="uperletter">{{ $streams->status }}</td>
                                        <td class='d-flex'>
                                            <button class="btn btn-primary m-1" data-bs-toggle="modal" data-bs-target="#viewModal{{ $streams->id }}">View</button>
                                            <!-- Modal -->
                                            <div class="modal fade" id="viewModal{{ $streams->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $streams->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('employeeleaves.update', $streams->id) }}">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="viewModalLabel{{ $streams->id }}">
                                                                    Leave Request - {{ $streams->employee->first_name }} {{ $streams->employee->last_name }}
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Leave Period:</strong>
                                                                    {{ \Carbon\Carbon::parse($streams->start_date)->format('d-m-Y') }} to
                                                                    {{ \Carbon\Carbon::parse($streams->end_date)->format('d-m-Y') }}
                                                                </p>
                                                                <p><strong>Reason:</strong> {{ $streams->reason }}</p>

                                                                @php
                                                                    $period = \Carbon\CarbonPeriod::create($streams->start_date, $streams->end_date);
                                                                @endphp

                                                                <div class="table-responsive mt-3">
                                                                    <table class="table table-bordered">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Date</th>
                                                                                <th>Leave Type</th>
                                                                                <th>LWP</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($period as $date)
                                                                                <tr>
                                                                                    <td>{{ $date->format('d-m-Y') }}</td>
                                                                                    <td>
                                                                                        <select name="leave_types[{{ $date->format('Y-m-d') }}]" class="form-select" required>
                                                                                            @foreach ($leaveTypes as $type)
                                                                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                    </td>
                                                                                    <td class="text-center">
                                                                                        <input type="checkbox" name="lwp_dates[]" value="{{ $date->format('Y-m-d') }}">
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <input type="hidden" name="status" value="approved">
                                                                <button type="submit" class="btn btn-success">Approve</button>
                                                                <button type="submit" name="status" value="rejected" class="btn btn-danger">Reject</button>
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>                                    
                                @endforeach
                            </tbody>
                        </table>
						<div class="d-flex justify-content-between align-items-center mt-3">
							<div class="text-muted">
								Showing {{ $stream->firstItem() }} to {{ $stream->lastItem() }} of {{ $stream->total() }} entries
							</div>

							<div>
								{{ $stream->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
							</div>
						</div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    function capitalizeSelectOptions(selectId) {
        const select = document.getElementById(selectId);
        if (select) {
            Array.from(select.options).forEach(option => {
                option.innerText = capitalizeWords(option.innerText);
            });
        }
    }
    function capitalizeWords(str) {
        return str
            .toLowerCase()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.substring(1))
            .join(' ');
    }
    document.addEventListener('DOMContentLoaded', function() {
        capitalizeSelectOptions('employee_id');
    });
</script>
@endsection
