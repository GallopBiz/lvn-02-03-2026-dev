@extends('backend.layouts.main')

@section('main-container')

<style>
  .card-title {
    margin-bottom: 10px;
    color: #7074b3;
    font-size: 25px;
  }

  .form-inline {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 20px;
  }

  .search-bar {
    float: right;
    margin-top: 5px;
  }

  .generate-btn {
    margin-top: 10px;
  }
</style>

<div class="main-content">
  <div class="breadcrumb">
    <h1>Tuition Fees Certificate</h1>
  </div>
  <div class="separator-breadcrumb border-top"></div>

  <!-- Form Section -->
  <div class="row mb-3">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="card-title">Search Student by Scholar No</div>
          <form class="form-inline" method="GET" action="{{ route('fees.certificate.search') }}">
            <input type="text" name="scholar_no" class="form-control" placeholder="Enter Scholar No" required>
            
            <!-- Date Range Filters -->
            <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
            <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
            
            <button type="submit" class="btn btn-primary">Search</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Student List Section -->
  @if(isset($students) && count($students) > 0)
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <div class="card-title">Student Details</div>
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Scholar No</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Father's Name</th>
                    <th>Fees Submitted</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($students as $index => $student)
                    @php
                      // Access the decoded data
                      $studentData = $student->data;
                    @endphp
                    <tr>
                      <td>{{ $index + 1 }}</td>
                      <td>{{ $studentData->scholar_no }}</td>
                      <td>{{ $studentData->studentname }}</td>
                      <td>{{ $studentData->classname }}</td>
                      <td>{{ $studentData->fathername }}</td>
                      <td>
                        @if(isset($student->fees) && count($student->fees) > 0)
                          @foreach($student->fees as $fee)
                            <p>{{ $fee->sub_total_received }} ({{ \Carbon\Carbon::parse($fee->created_at)->format('d-m-Y') }})</p>
                          @endforeach
                        @else
                          <p>No fees found in the selected date range.</p>
                        @endif
                      </td>
                      <td>
                        <a href="{{ route('fees.certificate.generate', $student->id) }}" class="btn btn-success btn-sm generate-btn">
                          Generate Certificate
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  @elseif(request()->has('scholar_no'))
    <div class="alert alert-warning">No student found with Scholar No: <strong>{{ request('scholar_no') }}</strong></div>
  @endif
</div>

@endsection
