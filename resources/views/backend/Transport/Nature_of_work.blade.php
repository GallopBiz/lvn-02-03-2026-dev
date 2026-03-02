@extends('backend.layouts.main')
@section('main-container')
<style>

.uperletter{
  text-transform: capitalize;
} 

</style>

<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Nature of Work Master</h1>
        </div>
        <div class="separator-breadcrumb border-top"></div>
            @if(!empty($natureof_works))
                    <form id="progress-form" class="p-4 progress-form" action="{{url('store-nature-of-work')}}" method="post">
                    <input type="hidden" 
                @if(!empty($natureof_works))
                    @foreach($natureof_works as $natureof_work)
                    value=" {{ $natureof_work->id }}"
                    @endforeach
                @else
                    value=""
                @endif
                name="id"
            >
            @else
                <form id="progress-form" class="p-4 progress-form" action="{{url('save-nature-of-work')}}" method="post">
            @endif
            @csrf
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Nature of Work </label>
                    <input required name="nature_of_work_name" class="form-control uperletter" 
                    id="nature_of_work_name" 
                    @if(!empty($natureof_works))
                        @foreach($natureof_works as $natureof_work)
                            value=" {{ $natureof_work->nature_of_work_name }}"
                        @endforeach
                    @else
                        value=""
                    @endif
                    type="text"
                    placeholder="Bus Fees Type Name" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Remarks:</label>
                    <textarea id="Remarks" name="nature_of_work_remarks" class="form-control uperletter" rows="2" cols="75" 
                    placeholder="Remarks"><?php if(!empty($natureof_works)){foreach($natureof_works as $natureof_work){ echo $natureof_work->nature_of_work_remarks; }}else{}?></textarea>
                </div>
                <div class="col-md-12">
                    <button class="btn btn-primary">Submit</button>
                    <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>


                </div>
            </div>
        </form>
    </div>
    <br>
    <div class="separator-breadcrumb border-top"></div>
    <div class="breadcrumb">
        <h1 class="me-2">List of Nature of Work Master saved Records</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card text-start">
            <div class="card-body">
            <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="nature_of_work_name">
                      <input type="hidden" name="column_names[]" value="nature_of_work_remarks">
                      <input type="hidden" name="table_name" value="natureofwork">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div>
                <div class="table-responsive">
                <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                    <thead>
                    <tr>
                        <th>Sr.</th>
                        <th>Nature of Work</th>
                        <th>Remark</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i=1;  ?>
                    @if(!empty($natureofworks))
                    @foreach ($natureofworks as $natureofwork)
                    <tr>
                        <td>{{ $i }}</td>
                        <td>{{ $natureofwork->nature_of_work_name }}</td>
                        <td>{{ $natureofwork->nature_of_work_remarks }}</td>
                        <!-- <td> 
                            <a class="btn btn-raised ripple btn-raised-warning m-1" href="{{ url('view-nature-of-work') .'/'.$natureofwork->id}}">Edit</a>
                            <a class="btn btn-raised ripple btn-raised-danger m-1" href="{{ url('delete-nature-of-work') .'/'.$natureofwork->id}}">Delete</a>
                        </td> -->
                        <td class='d-flex'>
                              <a class="btn btn-primary m-1" href="{{ url('view-nature-of-work') .'/'.$natureofwork->id}}">Edit</a>
                                <!-- <form id="deleteForm" method="post" action="{{url('delete-nature-of-work')}}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="natureofwork">
                                    <input type="hidden" name="delete_id" value="{{ $natureofwork->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="confirmDelete(event)">Delete</button>
                                </form> -->
                                <br>
                                <?php $a = "natureofwork"."-".$natureofwork->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-nature-of-work').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
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
                        <th>Nature of Work</th>
                        <th>Remark</th>
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

    document.addEventListener('DOMContentLoaded', function() {
    $("#reset").on("click", function () {                
        $("#nature_of_work_name").val("");
        $("#Remarks").val("");
            
    });

})

</script>
@endsection