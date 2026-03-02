@extends('backend.layouts.main')

@section('main-container')

<style>
  .uperletter {
    text-transform: capitalize;
  }
</style>

@php
  $i = 0;
@endphp

<div class="main-content">
  <div class="form_section1_div">
    <div class="breadcrumb">
      <h1 class="me-2">Attendance</h1>
    </div>

    <div class="separator-breadcrumb border-top"></div>

    <form id="progress-form" class="p-4 progress-form" action="{{ route('filter-attendance') }}" method="get">
      @csrf
      <div class="row">
        <div class="col-md-3 form-group mb-3">
          <label for="monthYear">Month</label>
          <input required
            class="form-control"
            id="monthYear"
            name="Date"
            type="month"
            value="{{ request('Date') }}"
            placeholder="YYYY-MM"
          />
        </div>

        <div class="col-md-3 form-group mb-3">
          <label for="EmployeeID">Employees</label>
          <select required name="EmployeeID" class="form-control" id="EmployeeID">
            <option value="" selected>-- Please select --</option>
            @foreach($employeeName as $employee)
              <option value="{{ $employee->ess_emp_code }}" {{ request('EmployeeID') == $employee->ess_emp_code ? 'selected' : '' }}>
                {{ $employee->FirstName }} {{ $employee->LastName }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-12">
          <button type="submit" class="btn btn-primary">Submit</button>
          <button type="button" id="reset" class="btn btn-primary">Reset</button>
        </div>
      </div>
    </form><br>
  </div>

  <div class="separator-breadcrumb border-top"></div>
  <div class="row">
    <div class="col-md-12 mb-4">
      <div class="breadcrumb">
        <h1 class="me-2">List of Saved Attendance:</h1>
      </div>
      <div class="separator-breadcrumb border-top"></div>

      <div class="card text-start">
        <div class="card-body">
          <div class="table-responsive">
            <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper" style="width: 100%">
              <thead>
                <tr>
                  <th>Sr.</th>
                  <th>Log Date</th>
                  <th>In Time</th>
                  <th>Out Time</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $i = 0;
                @endphp
                @if(!empty($attendanceData))
                  @foreach($attendanceData as $data)
                    <tr>
                      <td>{{ ++$i }}</td>
                      <td>{{ $data['log_date'] ?? 'N/A' }}</td>
                      <td>{{ $data['in_time'] ?? '-' }}</td>
                      <td>{{ $data['out_time'] ?? '-' }}</td>
                      <td>{{ $data['status'] ?? 'N/A' }}</td>
                    </tr>
                  @endforeach
                @else
                  <tr>
                    <td colspan="5" class="text-center">No Data Found</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- end of main-content -->
</div>

@if(session('success'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
  Swal.fire({
    icon: 'success',
    title: '{{ session('success') }}',
    showConfirmButton: true
  });
</script>
@endif

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var select = document.getElementById('EmployeeID');
    for (var i = 0; i < select.options.length; i++) {
      select.options[i].innerText = capitalizeFirstLetter(select.options[i].innerText);
    }
  });

  function capitalizeFirstLetter(str) {
    var words = str.toLowerCase().split(' ');
    for (var i = 0; i < words.length; i++) {
      words[i] = words[i].charAt(0).toUpperCase() + words[i].substring(1);
    }
    return words.join(' ');
  }

  document.addEventListener('DOMContentLoaded', function() {
    $("#reset").on("click", function () {                
      $("#monthYear").val(""); 
      $("#EmployeeID").val("");      
    });
  });
</script>

@endsection
