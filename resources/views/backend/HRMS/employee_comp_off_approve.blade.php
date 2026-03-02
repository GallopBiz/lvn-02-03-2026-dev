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
                    <h1 class="me-2">Employees Comp Off Leave Requests</h1>
                </div>
                <div class="separator-breadcrumb border-top"></div>

                <div class="card text-start">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Employee Name</th>
                                        <th>Requested Date</th>
                                        <th>Leave Type</th>
                                        <th>Note</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stream as $streams)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            <td class="uperletter">{{ $streams->employee->first_name }} {{ $streams->employee->last_name }}</td>
                                            <td>{{ date('d-m-Y', strtotime($streams->start_date)) }}</td>
                                            <td class="uperletter">{{ $streams->leaveType->name ?? '-' }}</td>
                                            <td>{{ strlen($streams->reason) > 60 ? substr($streams->reason, 0, 60) . '...' : $streams->reason }}</td>
                                            <td class="uperletter">{{ $streams->status }}</td>
                                            <td>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal{{ $streams->id }}">
                                                    View
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal -->
                                        <div class="modal fade" id="viewModal{{ $streams->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $streams->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="viewModalLabel{{ $streams->id }}">Comp Off Request Details</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Employee Name:</strong> {{ $streams->employee->first_name }} {{ $streams->employee->last_name }}</p>
                                                        <p><strong>Leave Type:</strong> {{ $streams->leaveType->name ?? '-' }}</p>
                                                        <p><strong>Requested Date:</strong> {{ \Carbon\Carbon::parse($streams->start_date)->format('d M, Y') }}</p>
                                                        <p><strong>Reason:</strong> {{ $streams->reason }}</p>
                                                        <p><strong>Status:</strong> {{ ucfirst($streams->status) }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <form method="POST" action="{{ route('employeecompoff.update', $streams->id) }}">
                                                            @csrf
                                                            <input type="hidden" name="status" value="approved">
                                                            <button type="submit" class="btn btn-success">Approve</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('employeecompoff.update', $streams->id) }}">
                                                            @csrf
                                                            <input type="hidden" name="status" value="rejected">
                                                            <button type="submit" class="btn btn-danger">Reject</button>
                                                        </form>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    @if ($stream->isEmpty())
                                        <tr>
                                            <td colspan="7" class="text-center">No Comp Off Requests Found</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ✅ REQUIRED Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@endsection
