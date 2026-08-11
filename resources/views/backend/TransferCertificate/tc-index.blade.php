@extends('backend.layouts.main')

@section('main-container')
<div class="main-content">
    <div class="breadcrumb d-flex justify-content-between align-items-center">
        <h1>Transfer Certificate Reports</h1>
        <div>
            <a href="{{ route('transfercertificate.create') }}" class="btn btn-success"># Create Transfer Certificate</a>
        </div>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('transfercertificate.index') }}" class="row">
                <div class="col-md-2 mb-2">
                    <label>Session</label>
                    <input type="text" name="session_name" class="form-control" value="{{ request('session_name') }}" placeholder="2025_2026">
                </div>
                <div class="col-md-2 mb-2">
                    <label>Class</label>
                    <select name="class_name" class="form-control">
                        <option value="">All</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->class_name }}" @selected(request('class_name') == $class->class_name)>{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label>From</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <label>To</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <label>Student / Scholar / T.C.</label>
                    <input type="text" name="student_search" class="form-control" value="{{ request('student_search') }}">
                </div>
                <div class="col-md-1 mb-2">
                    <label>Duplicate</label>
                    <div class="form-check">
                        <input type="checkbox" name="duplicates" value="1" class="form-check-input" @checked(request()->boolean('duplicates'))>
                    </div>
                </div>
                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="{{ route('transfercertificate.index') }}" class="btn btn-light">Reset</a>
                    <a href="{{ route('transfercertificate.create') }}" class="btn btn-success">Generate T.C.</a>
                    <button type="submit" name="export" value="csv" class="btn btn-outline-secondary">Export CSV</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>T.C. No.</th>
                            <th>Scholar No.</th>
                            <th>Student</th>
                            <th>Session</th>
                            <th>Class</th>
                            <th>Issue Date</th>
                            <th>Duplicate Count</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificates as $index => $certificate)
                            <tr>
                                <td>{{ $certificates->firstItem() + $index }}</td>
                                <td>{{ $certificate->certificate_no }}</td>
                                <td>{{ $certificate->scholar_no }}</td>
                                <td>{{ $certificate->student_name }}</td>
                                <td>{{ $certificate->session_name }}</td>
                                <td>{{ $certificate->class_name }} {{ $certificate->section_name ? ' / '.$certificate->section_name : '' }}</td>
                                <td>{{ optional($certificate->issue_date)->format('d-m-Y') }}</td>
                                <td>{{ $certificate->issues->where('is_duplicate', true)->count() }}</td>
                                <td>
                                    <a href="{{ route('transfercertificate.print', $certificate->id) }}" class="btn btn-sm btn-info">Print</a>
                                    <a href="{{ route('transfercertificate.print', [$certificate->id, 'download' => 'pdf']) }}" class="btn btn-sm btn-outline-info">PDF</a>
                                    <a href="{{ route('transfercertificate.duplicate', $certificate->id) }}" class="btn btn-sm btn-warning">Duplicate</a>
                                    <a href="{{ route('transfercertificate.edit', $certificate->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No Transfer Certificate records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $certificates->links() }}
        </div>
    </div>
</div>
@endsection
