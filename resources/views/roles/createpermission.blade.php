@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Permission</h1>
        </div>
        <div class="separator-breadcrumb border-top"></div>
            @if(!empty($fdata))
                    <form id="myForm" class="p-4 progress-form" action="{{url('store-permission')}}"  novalidate method="post">
                    <input type="hidden" 
                @if(!empty($fdata))
                    @foreach($fdata as $fdatas)
                    value=" {{ $fdatas->id }}"
                    @endforeach
                @else
                    value=""
                @endif
                name="id"
            >
            @else
                <form id="myForm" class="p-4 progress-form" action="{{url('save-permission')}}"  novalidate method="post">
            @endif
            @csrf
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Permission:</label>
                    <input name="permission" class="form-control" id="permission" 
                        @if(!empty($fdata))
                            @foreach($fdata as $fdatas)
                                value="{{ $fdatas->name }}"
                            @endforeach
                        @else
                            value=""
                        @endif
                        type="text" placeholder="Permission" required />
                    <span id="permissionError" class="text-danger"></span>
                </div>
                <div class="col-md-2">
                    <br>
                    <button class="btn btn-primary" onclick="validateAndSubmit(event)">Submit</button>
                </div>
            </div>
        </form>
    </div>
    <br>
    <div class="separator-breadcrumb border-top"></div>
    <div class="breadcrumb">
        <h1 class="me-2">List of Permission</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card text-start">
            <div class="card-body">
            <!-- <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="rto_paper_name">
                      <input type="hidden" name="column_names[]" value="remark">
                      <input type="hidden" name="column_names[]" value="is_permit">
                      <input type="hidden" name="table_name" value="rtopaper">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div> -->
                <div class="table-responsive">
                <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                    <thead>
                    <tr>
                        <th>Sr.</th>
                        <th>Permission</th>
                        <!-- <th>Remark</th> -->
                        <!-- <th>Is Permit</th> -->
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i=1; ?>
                    @if(!empty($pdata)) 
                    @foreach ($pdata as $rtopaper)
                    <tr>
                        <td>{{ $i }}</td>
                        <td>{{ $rtopaper->name }}</td>
                        <!-- <td> 
                            <a class="btn btn-raised ripple btn-raised-warning m-1" href="{{ url('view-rtopaper') .'/'.$rtopaper->id}}">Edit</a>
                            <a class="btn btn-raised ripple btn-raised-danger m-1" href="{{ url('delete-rtopaper') .'/'.$rtopaper->id}}">Delete</a>
                        </td> -->
                        <td class='d-flex'>
                              <a class="btn btn-primary m-1" href="{{ url('view-permission') .'/'.$rtopaper->id}}">Edit</a>
                                <!-- <form id="deleteForm" method="post" action="{{url('rtopaper-delete')}}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="rtopaper">
                                    <input type="hidden" name="delete_id" value="{{ $rtopaper->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="confirmDelete(event)">Delete</button>
                                </form> -->
                                <br>
                                <?php $a = $rtopaper->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-permission').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
                            </td>
                    </tr>
                    <?php $i++; ?>
                    @endforeach
                    @else
                    <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                    @endif
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>Sr.</th>
                        <th>Permission</th>
                        <!-- <th>Remark</th> -->
                        <!-- <th>Is Permit</th> -->
                        <th>Action</th>
                    </tr>
                    </tfoot>
                </table>
                </div>
            </div>
            </div>
        </div>
    </div>
    <!-- end of main-content -->
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
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
</script>
<script>
    function validateAndSubmit(event) {
        var permissionInput = document.getElementById('permission');
        var permissionError = document.getElementById('permissionError');

        // Trim leading and trailing whitespaces
        var permissionValue = permissionInput.value.trim();

        if (permissionValue === '') {
            permissionError.innerText = 'Permission field is required.';
            event.preventDefault(); // Prevent form submission
        } else {
            // If the validation passes, set the error message to an empty string
            permissionError.innerText = '';
            // Continue with form submission
        }
    }
</script>
@endsection