@extends('backend.layouts.main')

@section('main-container')
<div class="main-content">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
				<a href="{{ route('shifts.index') }}" class="btn btn-primary">Back</a>
                <h1>Edit Shift</h1>

                <!-- Display Validation Errors -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('shifts.update', $shift->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="shift_type_id">Shift Type</label>
                        <p class="form-control-static">{{ $shift->shiftType->shift_type_name }}</p>
                        <input type="hidden" name="shift_type_id" value="{{ $shift->shift_type_id }}">
                        @error('shift_type_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $shift->start_time) }}" required>
                        @error('start_time')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $shift->end_time) }}" required>
                        @error('end_time')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="late_coming_threshold">Late Coming Threshold (in minutes)</label>
                        <input type="number" name="late_coming_threshold" id="late_coming_threshold" class="form-control" value="{{ old('late_coming_threshold', $shift->late_coming_threshold) }}" min="0" max="60" required>
                        @error('late_coming_threshold')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success">Update Shift</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
