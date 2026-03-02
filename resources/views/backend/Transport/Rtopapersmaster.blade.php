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
            <h1 class="me-2">RTO Paper Master</h1>
        </div>
        <div class="separator-breadcrumb border-top"></div>
            @if(!empty($rto_papers))
                    <form id="progress-form" class="p-4 progress-form" action="{{url('store-rtopaper')}}" method="post">
                    <input type="hidden" 
                @if(!empty($rto_papers))
                    @foreach($rto_papers as $rto_paper)
                    value=" {{ $rto_paper->id }}"
                    @endforeach
                @else
                    value=""
                @endif
                name="id"
            >
            @else
                <form id="progress-form" class="p-4 progress-form" action="{{url('save-rtopaper')}}" method="post">
            @endif
            @csrf
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">RTO Paper Name:</label>
                    <input required name="rto_paper_name" 
                    class="form-control uperletter" id="rto_paper_name" 
                    @if(!empty($rto_papers))
                        @foreach($rto_papers as $rto_paper)
                            value="{{ $rto_paper->rto_paper_name }}"
                        @endforeach
                    @else
                        value=""
                    @endif
                    type="text" placeholder="RTO Paper Name" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Remarks:</label>
                    <textarea id="r" name="remark" class="form-control uperletter"  
                    placeholder="Remarks"><?php 
                    if(!empty($rto_papers)){ 
                        foreach($rto_papers as $rto_paper){
                            echo $rto_paper->remark; 
                        }
                    } 
                    else{} 
                    ?></textarea>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <br>
                    <label for="lastName1">Is Permit:</label>
                    <input type="checkbox" 
                    class="" id="is_permit" 
                    @if(!empty($rto_papers))
                        @foreach($rto_papers as $rto_paper)
                            <?php 
                                if (!empty($rto_paper->is_permit)){
                                    echo 'checked="checked"';
                                    echo 'value="'.$rto_paper->is_permit.'"';
                                }
                            ?>
                        @endforeach
                    @else
                        value="yes"
                    @endif
                    name="is_permit" />
                </div>
                <div class="col-md-42">
                    <button class="btn btn-primary">Submit</button>
                    <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>


                </div>
            </div>
        </form>
    </div>
    <br>
    <div class="separator-breadcrumb border-top"></div>
    <div class="breadcrumb">
        <h1 class="me-2">List of RTO Paper Master saved Records</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card text-start">
            <div class="card-body">
            <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="rto_paper_name">
                      <input type="hidden" name="column_names[]" value="remark">
                      <input type="hidden" name="column_names[]" value="is_permit">
                      <input type="hidden" name="table_name" value="rtopaper">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div>
                <div class="table-responsive">
                <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                    <thead>
                    <tr>
                        <th>Sr.</th>
                        <th>RTO Paper Name</th>
                        <th>Remark</th>
                        <th>Is Permit</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i=1;  ?>
                    @if(!empty($rtopapers))
                    @foreach ($rtopapers as $rtopaper)
                    <tr>
                        <td>{{ $i }}</td>
                        <td>{{ $rtopaper->rto_paper_name }}</td>
                        <td>{{ $rtopaper->remark }}</td>
                        <td>{{ $rtopaper->is_permit }}</td>
                        <!-- <td> 
                            <a class="btn btn-raised ripple btn-raised-warning m-1" href="{{ url('view-rtopaper') .'/'.$rtopaper->id}}">Edit</a>
                            <a class="btn btn-raised ripple btn-raised-danger m-1" href="{{ url('delete-rtopaper') .'/'.$rtopaper->id}}">Delete</a>
                        </td> -->
                        <td class='d-flex'>
                              <a class="btn btn-primary m-1" href="{{ url('view-rtopaper') .'/'.$rtopaper->id}}">Edit</a>
                                <!-- <form id="deleteForm" method="post" action="{{url('rtopaper-delete')}}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="rtopaper">
                                    <input type="hidden" name="delete_id" value="{{ $rtopaper->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="confirmDelete(event)">Delete</button>
                                </form> -->
                                <br>
                                <?php $a = "rtopaper"."-".$rtopaper->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-driver-conductor-master').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
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
                        <th>RTO Paper Name</th>
                        <th>Remark</th>
                        <th>Is Permit</th>
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
        $("#rto_paper_name").val("");
        $("#r").val("");
        $("#is_permit").val("");
        $("input[type='checkbox']").prop('checked', false);
        $("input[type='radio']").prop('checked', false);

            
    });

})

</script>
@endsection