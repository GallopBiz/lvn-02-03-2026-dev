@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
    <!-- <h2>hyy</h2> -->
    @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
          <div class="breadcrumb">
          <h2>Bus Per Student</h2>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">

                  <h4 class="card-title mb-3 text-end"><a href="{{url('bus-attandence-list')}}"><button class="btn btn-outline-primary" type="button">Back</button></a></h4>
                  <!-- <div class="card-title mb-3 text-end"><form method="" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="Party_Name">
                      <input type="hidden" name="column_names[]" value="Address">
                      <input type="hidden" name="column_names[]" value="emailId">
                      <input type="hidden" name="column_names[]" value="City">
                      <input type="hidden" name="column_names[]" value="STDCode">
                      <input type="hidden" name="column_names[]" value="Office_ph_no_1">
                      <input type="hidden" name="column_names[]" value="Mobile">
                      <input type="hidden" name="column_names[]" value="PAN_no_">
                      <input type="hidden" name="column_names[]" value="TIN_no_">
                      <input type="hidden" name="column_names[]" value="GST_no_">
                      <input type="hidden" name="table_name" value="partymaster">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div> -->
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>No</th>
                            <th>Student Name</th>
                            <th>Address</th>
                            <th>Class Name</th>
                            <th>Attendance</th>
                            
                        </tr>
                      </thead>
                        <tbody>
                            @foreach($listbusstudent as $item)
                                <?php $jsonData = json_decode($item->json_str, true); ?>
                                <?php
                                $studentNames = $jsonData['Student_name'] ?? [];
                                $classNames = $jsonData['Class_name'] ?? [];
                                $addresses = $jsonData['Address'] ?? [];
                                $studentIds = $jsonData['student_id'] ?? [];
                                $count = count($studentNames);
                                ?>
                                @for($index = 0; $index < $count; $index++)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $studentNames[$index] ?? '' }}</td>
                                        <td>{{ $classNames[$index] ?? '' }}</td>
                                        <td>{{ $addresses[$index] ?? '' }}</td>
                                        <td>{{ $studentIds[$index] ?? '' }}</td>
                                    </tr>
                                @endfor
                            @endforeach
                        </tbody>
                      <tfoot>
                            <th>No</th>
                            <th>Student Name</th>
                            <th>Address</th>
                            <th>Class Name</th>
                            <th>Attendance</th>
                            
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
    <!-- end of main-content -->
</div>

@endsection