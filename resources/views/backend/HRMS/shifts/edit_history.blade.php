@extends('backend.layouts.main')

@section('main-container')
    <div class="main-content">
        <div class="container-fluid">
            <div class="breadcrumb">
                <h1 class="me-2">Edit Shift Assignment</h1>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card text-start">
                <div class="card-body">
                    <form method="POST" action="{{ route('shifts.history.update', $history->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label>Employee</label>
                            <input type="text" class="form-control"
                                value="{{ optional($history->employee)->first_name }} {{ optional($history->employee)->last_name }}"
                                readonly>
                        </div>

                        <div class="form-group mb-3">
                            <label for="shift_id">Shift</label>
                            <select name="shift_id" id="shift_id" class="form-control" required>
                                <option value="">Select Shift</option>
                                @foreach ($shifts as $shift)
                                    <option value="{{ $shift->id }}" @selected(old('shift_id', $history->shift_id) == $shift->id)>
                                        {{ optional($shift->shiftType)->shift_type_name ?? 'Shift '.$shift->id }}
                                        ({{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} -
                                        {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="effective_from">Effective From</label>
                                    <input type="date" name="effective_from" id="effective_from" class="form-control"
                                        value="{{ old('effective_from', optional($history->effective_from)->format('Y-m-d')) }}"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="effective_to">Effective To</label>
                                    <input type="date" name="effective_to" id="effective_to" class="form-control"
                                        value="{{ old('effective_to', optional($history->effective_to)->format('Y-m-d')) }}"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="remarks">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="3">{{ old('remarks', $history->remarks) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success">Update Assignment</button>
                        <a href="{{ route('shifts.history') }}" class="btn btn-secondary">Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
