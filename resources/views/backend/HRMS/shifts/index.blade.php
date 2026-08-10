@extends('backend.layouts.main')
@section('main-container')
<div class="main-content">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <h1 class="m-0">Shifts</h1>
                    <a href="{{ route('shifts.create') }}" class="btn btn-primary">Create Shift</a>
                    <a href="{{ route('shifts.history') }}" class="btn btn-outline-secondary">Shift History</a>
                    <form action="{{ route('shifts.index') }}" method="GET" class="d-flex align-items-center ms-auto">
                        <input type="search" name="search" id="searchInput" class="form-control me-2" placeholder="Search shift types" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-outline-primary me-2">Search</button>
                        <a href="{{ route('shifts.index') }}" class="btn btn-secondary">Reset</a>
                    </form>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>SR No.</th>
                            <th>Shift Type</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Late Coming Threshold</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($shifts as $shift)
                            <tr>
                                <td>{{ $shifts->firstItem() + $loop->index }}</td>
                                <td>
                                    @if($shift->shiftType)
                                        {{ $shift->shiftType->shift_type_name }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                
                                {{-- <td>{{ $shift->shiftType->Type }}</td> --}}
                                <td>{{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}</td>
                                <td>{{ $shift->late_coming_threshold }} minutes</td>
                                <td>
                                    <a href="{{ route('shifts.edit', $shift->id) }}" class="btn btn-warning">Edit</a>
                                    <form action="{{ route('shifts.destroy', $shift->id) }}" method="POST" style="display:inline-block;" onsubmit="confirmDelete(event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center">
                    {!! $shifts->links('pagination::bootstrap-4') !!}
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    function confirmDelete(event) {
        event.preventDefault(); // Prevents the default form submission
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085D6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // If the user clicks on "Yes, delete it!", submit the form
                event.target.submit();
            }
        });
    }
</script>
@endsection
