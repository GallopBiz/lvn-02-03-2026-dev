@extends('backend.layouts.main')
@section('main-container')

<style>
    .uperletter {
        text-transform: capitalize;
    }
</style>

<div class="main-content">
    <div class="separator-breadcrumb border-top"></div>
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="breadcrumb">
                <h1 class="me-2">Comp Off Requests</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>

            <!-- Comp Off Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" action="{{ route('compoff.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label>Employee</label>
                                <select name="employee_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Leave Type</label>
                                <select name="leave_type_id" id="leave_type_id" class="form-control">
									<option value="">Select Leave Type</option>
									@php
										$compOff = \App\Models\HrmsLeaveType::where('name', 'like', '%comp%')->first();
									@endphp
									@if ($compOff)
										<option value="{{ $compOff->id }}"
											{{ old('leave_type_id', $stream_master->leave_type_id ?? '') == $compOff->id ? 'selected' : '' }}>
											{{ $compOff->name }}
										</option>
									@endif
								</select>

                            </div>

                            <div class="col-md-3 mb-3">
                                <label> Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>

                            <!--<div class="col-md-3 mb-3">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>-->

                            <div class="col-md-6 mb-3">
                                <label>Reason</label>
                                <textarea name="reason" class="form-control" rows="2" required></textarea>
                            </div>

                            <!--<div class="col-md-3 mb-3">
                                <label>
                                    <input type="checkbox" name="is_half_day" value="1" id="half_day_checkbox">
                                    Is Half Day?
                                </label>
                                <select name="half_day_type" id="half_day_type" class="form-control mt-2" style="display: none;">
                                    <option value="">Select Half</option>
                                    <option value="morning">Morning</option>
                                    <option value="afternoon">Afternoon</option>
                                </select>
                            </div>-->

                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Submit Request</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Comp Off Requests Table -->
            <div class="card text-start">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Employee</th>
                                    <th>Comp Off Date</th>
                                    <!--<th>End Date</th>-->
                                    <th>Leave Type</th>
                                    <th>Reason</th>
                                    <!--<th>Half Day</th>-->
                                    <th>Status</th>
                                    <th>Approved By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requests as $index => $req)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $req->employee->first_name }} {{ $req->employee->last_name }}</td>
                                        <td>{{ date('d-m-Y', strtotime($req->start_date)) }}</td>
                                        <!--<td>{{ date('d-m-Y', strtotime($req->end_date)) }}</td>-->
                                        <td>{{ $req->leaveType->name ?? '-' }}</td>
                                        <td>{{ $req->reason }}</td>
                                        <!--<td>
                                            @if($req->is_half_day)
                                                {{ ucfirst($req->half_day_type) }} Half
                                            @else
                                                -
                                            @endif
                                        </td>-->
                                        <td class="text-capitalize">{{ $req->status }}</td>
                                        <td>{{ $req->approver->first_name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No Comp Off Requests Found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Half Day Toggle Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const halfDayCheckbox = document.getElementById('half_day_checkbox');
        const halfDayType = document.getElementById('half_day_type');

        halfDayCheckbox.addEventListener('change', function () {
            halfDayType.style.display = this.checked ? 'block' : 'none';
        });
    });
</script>

@endsection
