@extends('backend.layouts.main')
@section('main-container')

<script type="text/javascript">jQuery.noConflict();</script>

<div class="main-content">
    <div class="breadcrumb">
        <h1 class="me-2">Inquiry Report</h1>
        <ul>
            <li><a href="">Dashboard</a></li>
        </ul>
    </div>
    <div class="separator-breadcrumb border-top"></div>


<!-- Filter + Export Section -->
<div class="row mb-4">
    <div class="col-8">
        <form method="GET" action="{{ route('inquiry.report') }}">
            <div class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label for="fromdate">Start Date</label>
                    <input type="date" name="fromdate" value="{{ $startDate }}" class="form-control">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="todate">End Date</label>
                    <input type="date" name="todate" value="{{ $endDate }}" class="form-control">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="status">Status</label>
                    <select name="status" class="form-control">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All</option>
                        <option value="i" {{ request('status') == 'i' ? 'selected' : '' }}>Enquiry</option>
                        <option value="p" {{ request('status') == 'p' ? 'selected' : '' }}>Pre Enquiry</option>
                        <option value="r" {{ request('status') == 'r' ? 'selected' : '' }}>Registered</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-1 mb-2 text-end">
                    <a href="{{ route('inquiry.report.export', ['fromdate' => $startDate, 'todate' => $endDate, 'status' => request('status')]) }}" class="btn btn-success">Export</a>
                </div>
            </div>
        </form>
    </div>
</div>



    <!-- Card Layout Section -->
    <div class="row">
        <!-- Total All Card -->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                <div class="card-body text-center">
                    <i class="i-Checked-User"></i>
                    <p class="text-muted mt-2 mb-0">Total All</p>
                    <p class="text-primary text-24 line-height-1 mb-2">{{ $totalAll }}</p>
                </div>
            </div>
        </div>

        <!-- Pre Inquiries Card -->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-icon-bg card-icon-bg-warning o-hidden mb-4">
                <div class="card-body text-center">
                    <i class="i-Pen-3"></i>
                    <p class="text-muted mt-2 mb-0">Pre Inquiries</p>
                    <p class="text-warning text-24 line-height-1 mb-2">{{ $totalPreInquiry }}</p>
                </div>
            </div>
        </div>

        <!-- Inquiries Card -->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-icon-bg card-icon-bg-info o-hidden mb-4">
                <div class="card-body text-center">
                    <i class="i-Diploma-2"></i>
                    <p class="text-muted mt-2 mb-0">Inquiries</p>
                    <p class="text-info text-24 line-height-1 mb-2">{{ $totalInquiry }}</p>
                </div>
            </div>
        </div>

        <!-- Form Selected Card -->
<div class="col-lg-3 col-md-6 col-sm-6">
    <div class="card card-icon-bg card-icon-bg-success o-hidden mb-4">
        <div class="card-body text-center">
            <i class="i-Add-User"></i>
            <p class="text-muted mt-2 mb-0">Form Selected</p>
            <p class="text-success text-24 line-height-1 mb-2">{{ $totalFormSelected }}</p>
        </div>
    </div>
</div>

    </div>

    <!-- Inquiry Report Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Form Number</th>
                            <th>Student Name</th>
                            <th>DOB</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allData as $row)
                            <tr>
                                <td>{{ $row->id }}</td>
                                <td>{{ $row->form_number }}</td>
                                <td>{{ $row->student_name }}</td>
                                <td>{{ $row->date_of_birth }}</td>
                                <td>{{ $row->class_name }}</td>
                                <td>{{ strtoupper($row->save_status) }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->created_at)->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Export Button -->
    <div class="col-md-12 text-right mt-4">
        <a href="{{ route('inquiry.report.export', ['fromdate' => $startDate, 'todate' => $endDate, 'status' => request('status')]) }}" class="btn btn-success">Export CSV</a>
    </div>
</div>

<script type="text/javascript">
    // Simple Search Functionality for Table
    document.getElementById('search').addEventListener('keyup', function() {
        var value = this.value.toLowerCase();
        var rows = document.querySelectorAll('tbody tr');
        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });
</script>
@endsection
