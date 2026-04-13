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
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Comp Off Requests (Staff)</h1>
        </div>

        <div class="separator-breadcrumb border-top"></div>

        <form id="progress-form" class="p-4 progress-form"
            action="{{ url('save-compoffrequests-staff') }}" method="post">
            @if (!empty($stream_master))
                <input type="hidden" name="id" value="{{ $stream_master->id }}">
            @endif
            @csrf

            <div class="row">
                <div class="col-md-3 form-group mb-3">
                    <label for="start_date">Comp Off Date</label>
                    <input required class="form-control" id="start_date" name="start_date" type="date"
                        value="{{ $stream_master->start_date ?? '' }}" />
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="employee_id">Employee</label>
                    <select name="employee_id" id="employee_id" class="form-control" readonly>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" selected>
                                {{ $employee->first_name }} {{ $employee->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="leave_type_id">Comp Off Type</label>
                    <select name="leave_type_id" id="leave_type_id" class="form-control">
                        <option value="">Select Comp Off Type</option>
                        @foreach ($leaveTypes as $leaveType)
                            <option value="{{ $leaveType->id }}" {{ (isset($stream_master) && $stream_master->leave_type_id == $leaveType->id) ? 'selected' : '' }}>{{ $leaveType->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 form-group mb-3">
                    <label for="reason">Note</label>
                    <textarea class="form-control" id="reason" name="reason" rows="3">{{ $stream_master->reason ?? '' }}</textarea>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-secondary" id="reset-button">Reset</button>
                </div>
            </div>
        </form>
        <br>
    </div>

    <div class="separator-breadcrumb border-top"></div>

    {{-- TABLE --}}
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="breadcrumb">
                <h1 class="me-2">List of Saved Comp Off Requests</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>

            <div class="card text-start">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="compoffTable" class="display table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Comp Off Date</th>
                                    <th>Comp Off Type</th>
                                    <th>Note</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stream as $streams)
                                    <tr>
                                        <td>{{ ++$i }}</td>
                                        <td>{{ date('d-m-Y', strtotime($streams->start_date)) }}</td>
                                        <td class="uperletter">{{ $streams->leaveType->name ?? 'N/A' }}</td>
                                        <td>{{ Str::limit($streams->reason, 60) }}</td>
                                        <td class="uperletter">{{ $streams->status }}</td>
                                    </tr>
                                @endforeach
                                @if ($stream->isEmpty())
                                    <tr>
                                        <td colspan="5" class="text-center">No Data Found</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination links --}}
                    <div class="d-flex justify-content-center mt-3">
                        {!! $stream->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    function confirmDelete(event) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently delete the comp off request.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = event.target.href;
            }
        });
    }
</script>
@endsection
