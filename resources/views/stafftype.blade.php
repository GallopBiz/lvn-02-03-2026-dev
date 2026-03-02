@extends('backend.layouts.main')
@section('main-container')

<style>
    .uperletter {
        text-transform: capitalize;
    }
</style>

<div class="main-content">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Staff Type</h1>
        </div>

        <div class="separator-breadcrumb border-top"></div>

        <form id="progress-form" class="p-4 progress-form" 
            action="{{ !empty($stream_master) ? url('store-stafftype') : url('save-stafftype') }}"  
            method="post">
            @csrf

            @if(!empty($stream_master))
                <input type="hidden" name="id" value="{{ $stream_master->Staff_Type_ID }}">
            @endif

            <div class="row">
                <div class="col-md-3 form-group mb-3">
                    <label for="stafftype">Staff Type</label>
                    <input 
                        required
                        class="form-control uperletter"
                        id="Type"
                        name="Type"
                        type="text"
                        value="{{ $stream_master->Type ?? '' }}"
                        placeholder="Staff Type"
                    />
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" id="reset" class="btn btn-secondary">Reset</button>
                </div>
            </div>
        </form><br>
    </div>

    <div class="separator-breadcrumb border-top"></div>
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="breadcrumb">
                <h1 class="me-2">List of Saved Staff Types:</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>

            <div class="card text-start">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Staff Types</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($stream))
                                    @foreach($stream as $index => $streams)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="uperletter">{{ $streams->Type }}</td> 
                                            <td class='d-flex'>
                                                <a class="btn btn-primary m-1" href="{{ url('view-stafftype/' . $streams->Staff_Type_ID) }}">Edit</a>
                                                <?php $a = "staff_type-" . $streams->Staff_Type_ID; ?>
                                                <a class="btn btn-raised ripple btn-danger m-1" href="{{ url('delete-stafftype/' . $a) }}" onclick="confirmDelete(event)">Delete</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="3" class="text-center">No Data Found</td></tr>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    function confirmDelete(event) {
        event.preventDefault();

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
                window.location.href = event.target.href;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        $("#reset").on("click", function () {                
            $("#Type").val("");      
        });
    });
</script>

@endsection
