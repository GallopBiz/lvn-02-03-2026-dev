@extends('backend.layouts.main')

@section('main-container')
<div class="main-content">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
				<a href="{{ route('shifts.index') }}" class="btn btn-primary">Back</a>
                <h1>Create Shift</h1>
                <form action="{{ route('shifts.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="shift_type_id">Shift Type</label>
                        <select name="shift_type_id" class="form-control">
                            @foreach($shiftTypes as $shiftType)
                                <option value="{{ $shiftType->id }}" {{ old('id') == $shiftType->id ? 'selected' : '' }}>
                                    {{ $shiftType->shift_type_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('shift_type_id')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}" required>
                        @error('start_time')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}" required>
                        @error('end_time')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="late_coming_threshold">Late Coming Threshold</label>
                        <input type="number" name="late_coming_threshold" id="late_coming_threshold" class="form-control" value="{{ old('late_coming_threshold') }}" min="0" max="60" required>
                        @error('late_coming_threshold')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-success">Create Shift</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
