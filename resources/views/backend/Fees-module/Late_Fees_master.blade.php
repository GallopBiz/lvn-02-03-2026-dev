@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Late Fees Master</h1>
        </div>
        <form action="@if(!empty($edit_data)) {{url('late-fees-master-update/'.$edit_data->id)}} @else {{url('save-late-fees-master')}} @endif" method="post">
            @csrf
            @if(!empty($edit_data))
                @method('PUT')
            @endif
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label for="lastName1">Late Fees Amount <span class="text-danger">(Rs. Per Day)*</span> </label>
                    <input name="late_fees_amount" class="form-control" id="late_fees_amount" type="text"
                        placeholder="Late Fees Amount" required value="@if(!empty($edit_data) && isset($edit_data->late_fees_amount)){{$edit_data->late_fees_amount}}@endif" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');" />
                    <div id="error" style="color: red; display: none;">Please enter only numeric values.</div>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label for="picker2">Applicable From Amount</label>
                    <input type="text" class="form-control" id="from_amount" name="from_amount" placeholder="From Amount" value="@if(!empty($edit_data) && isset($edit_data->from_amount)){{$edit_data->from_amount}}@endif" required oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');" />
                    <div id="from_amount_error" style="color: red; display: none;">Please enter only numeric values.</div>
                  </div>
                <div class="col-md-6 form-group mb-3">
                    <label for="picker2">Applicable To Amount</label>
                    <input type="text" class="form-control" id="to_amount" name="to_amount" value="@if(!empty($edit_data) && isset($edit_data->to_amount)){{$edit_data->to_amount}}@endif" placeholder="To Amount" required oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');" />
                    <div id="to_amount_error" style="color: red; display: none;">Please enter only numeric values.</div>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label for="picker2">UPto</label>
                    <input type="text" class="form-control" id="upto" name="upto" value="@if(!empty($edit_data) && isset($edit_data->upto)){{$edit_data->upto}}@endif"  placeholder="UPto" required oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');" />
                    <div id="upto_error" style="color: red; display: none;">Please enter only numeric values.</div>
                </div>
                <div class="col-md-12 m-2">
                    <button class="btn btn-primary">@if(!empty($edit_data)) Update @else Submit @endif</button>
                    @if(!empty($edit_data))
                    <a href='{{url("late-fees-master")}}' class="btn btn-primary">Go-Back</a>
                    @endif
                    
                </div>
            </div>
        </form>
    @if(empty($edit_data))
        <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  <!-- <h4 class="card-title mb-3 text-end"><a href="{{url('add-student-registrations')}}"><button class="btn btn-outline-primary" type="button">Create Registration</button></a></h4> -->
                  <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="late_fees_amount">
                      <input type="hidden" name="column_names[]" value="from_amount">
                      <input type="hidden" name="column_names[]" value="to_amount">
                      <input type="hidden" name="column_names[]" value="upto">
                      <input type="hidden" name="table_name" value="late_fees_master">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div>
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                          <th>SNO</th>
                          <th>Late Fees Amount</th>
                          <th>Applicable From Amount</th>
                          <th>Applicable To Amount</th>
                          <th>UPto</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(!empty($late_fees_master_arr))
                        @foreach ($late_fees_master_arr as $each_data)
                        <tr>
                            <td>{{ $each_data->id }}</td>
                            <td>{{ $each_data->late_fees_amount }}</td>
                            <td>{{ $each_data->from_amount }}</td>
                            <td>{{ $each_data->to_amount }}</td>
                            <td>{{ $each_data->upto }}</td>

                            <td class='d-flex'> 
                              <a class="btn btn-primary m-1" href="{{ url('late-fees-master-edit') .'/'.$each_data->id}}">Edit</a>
                               
                              <!-- <form id="deleteForm" method="post" action="{{url('late-fees-master-delete')}}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="late_fees_master">
                                    <input type="hidden" name="delete_id" value="{{ $each_data->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="confirmDelete(event)">Delete</button>
                                </form> -->
                                <?php $a = "late_fees_master"."-".$each_data->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('late-fees-master-delete').'/'.$a}}" onclick="confirmDelete(event)">delete</a>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>SNO</th>
                          <th>Late Fees Amount</th>
                          <th>From Amount</th>
                          <th>To Amount</th>
                          <th>UPto</th>
                          <th>Action</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endif

    </div>
    <!-- end of main-content -->
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
     function confirmDelete(event) {
        event.preventDefault(); // Prevents the default link navigation

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // If the user clicks on "Yes, delete it!", navigate to the delete URL
                window.location.href = event.target.href;
            }
        });
    }

    $("#late_fees_amount").on("keypress", function(event) {
      var charCode = (event.which) ? event.which : event.keyCode;
      if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        event.preventDefault();
        $("#error").show();
      } else {
        $("#error").hide();
      }
    });
    $("#from_amount").on("keypress", function(event) {
      var charCode = (event.which) ? event.which : event.keyCode;
      if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        event.preventDefault();
        $("#from_amount_error").show();
      } else {
        $("#from_amount_error").hide();
      }
    });
    $("#to_amount").on("keypress", function(event) {
      var charCode = (event.which) ? event.which : event.keyCode;
      if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        event.preventDefault();
        $("#to_amount_error").show();
      } else {
        $("#to_amount_error").hide();
      }
    });
    $("#upto").on("keypress", function(event) {
      var charCode = (event.which) ? event.which : event.keyCode;
      if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        event.preventDefault();
        $("#upto_error").show();
      } else {
        $("#upto_error").hide();
      }
    });
</script>

@endsection

