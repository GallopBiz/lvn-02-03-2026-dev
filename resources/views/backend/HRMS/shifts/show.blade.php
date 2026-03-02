@extends('backend.layouts.main')
@section('main-container')
<div class="main-content">
	<div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Shift Details</h1>
                <ul>
                    <li>ID: {{ $shift->id }}</li>
                    <li>Shift Type: {{ $shift->shiftType->shift_type_name }}</li>
                    <li>Start Time: {{ $shift->start_time }}</li>
                    <li>End Time: {{ $shift->end_time }}</li>
                    <li>Late Coming Threshold: {{ $shift->late_coming_threshold }}</li>
                </ul>
                <a href="{{ route('shifts.edit', $shift->id) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('shifts.destroy', $shift->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
                <a href="{{ route('shifts.index') }}" class="btn btn-primary">Back to List</a>
            </div>
        </div>
    </div>
</div>
@endsection
