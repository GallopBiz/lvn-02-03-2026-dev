@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Student Fee Exemptions</h2>
    <a href="{{ route('exemptions.export') }}" class="btn btn-success mb-3">Export to CSV</a>

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
            @foreach($exempted_students as $student)
                @if ($student['total_discount_amount'] > 0)
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
                @endif
            @endforeach
        </tbody>
    </table>
</div>
@endsection
