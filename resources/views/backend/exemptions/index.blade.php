@extends('layouts.app')

@section('main-container')

<div class="container">
    <h2 class="mb-4">Student Fee Exemptions</h2>
    <a href="{{ route('exemptions.export') }}" class="btn btn-success mb-3">Export to CSV</a>

    <form method="get" class="mb-3" action="">
        <div class="row">
            <div class="col-md-4 mb-2 mb-md-0">
                <input type="text" name="scholar_no" class="form-control" placeholder="Search Scholar No" value="{{ request('scholar_no', $scholar_no ?? '') }}">
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <input type="text" name="student_name" class="form-control" placeholder="Search Student Name" value="{{ request('student_name', $student_name ?? '') }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary me-2">Search</button>
                <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Scholar No</th>
                <th>Student Name</th>
                <th>Class</th>
                <th>Section</th>
                <th>Head</th>
                <th>Exempt %</th>
                <th>Discount Type</th>
                <th>Total Fees</th>
                <th>Discount Amount</th>
                <th>After Discount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($exempted_students as $student)
                <tr>
                    <td>{{ $student['scholar_no'] }}</td>
                    <td>{{ $student['student_name'] }}</td>
                    <td>{{ $student['class_name'] }}</td>
                    <td>{{ $student['section'] }}</td>
                    <td>{{ $student['head_name'] }}</td>
                    <td>{{ $student['exempt_per'] }}%</td>
                    <td>
                        @php
                            $types = [];
                            if ($student['lum']) $types[] = 'Lum';
                            if ($student['lssm']) $types[] = 'LSSM';
                            if ($student['lunch']) $types[] = 'Lunch';
                            if ($student['staff']) $types[] = 'Staff';
                            if ($student['sibling']) $types[] = 'Sibling';
                            if ($student['last_year_lssm']) $types[] = 'Last Year LSSM';
                        @endphp
                        {{ implode(', ', $types) }}
                    </td>
                    <td>₹{{ number_format($student['total_fees'], 2) }}</td>
                    <td>₹{{ number_format($student['total_discount_amount'], 2) }}</td>
                    <td>₹{{ number_format($student['total_fees_after_discount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No exemption data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="row mt-3">
        <div class="col-12 d-flex justify-content-center">
            {!! $exempted_students->links('pagination::bootstrap-4') !!}
        </div>
    </div>
</div>
@endsection
