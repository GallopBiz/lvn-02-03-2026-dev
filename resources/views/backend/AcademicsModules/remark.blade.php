
@extends('backend.layouts.main')       
@section('main-container')

<style>

.uperletter{
  text-transform: capitalize;
} 


</style>
@php
                      $i = 0;
                    @endphp
<div class="main-content pt-4">
    <div class="row">
    <div class="breadcrumb">
                    <h1 class="me-2">Remarks Master</h1>
                </div>
        <div class="col-md-6 form-group mb-3">
            <div class="form_section1_div">
               

                <div class="separator-breadcrumb border-top"></div>
                @if(!empty($stream_master))
                            <form id="progress-form" class="p-4 progress-form" action="{{url('store-remarksmaster')}}"  method="post">
                            <input type="hidden" 
                        @if(!empty($stream_master))
                        @foreach($stream_master as $streammaster)
                            value=" {{ $streammaster->id }}"
                            @endforeach
                        @else
                            value=""
                        @endif
                        name="id"
                    >

                    
                    @else
                        <form id="progress-form" class="p-4 progress-form" action="{{url('save-remarksmaster')}}" method="post">
                    @endif
                    @csrf

                    
                    <div class="row">
                    <div class="col-md-12 form-group mb-3">
                <label for="remark">Enter Remark Below</label>
                <input
                required
                  class="form-control uperletter"
                  id="remark"
                  name="remark"
                  type="text"
                  @if(!empty($stream_master))
                    @foreach($stream_master as $streammaster)
                      value=" {{ $streammaster->remark }}"
                    @endforeach
                  @else
                    value=""
                  @endif
                  placeholder="remark"
                />
              </div>


                        <div class="col-md-42">
                            <button class="btn btn-primary">Submit</button>
                            <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>

                        </div>


                    </div>
                </form>
            </div>
        </div>
    </div>
    <br>
    <div class="separator-breadcrumb border-top"></div>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="breadcrumb">
                <h5 class="me-2">List Of Saved Remarks</h5>
            </div>
            <div class="separator-breadcrumb border-top"></div>

            <div class="row">
                <div class="col-md-12 mb-6">
                    <div class="card text-start">
                    <div class="card-body">
                        <!-- <h4 class="card-title mb-3 text-end"><a href="{{url('add-student-registrations')}}"><button class="btn btn-outline-primary" type="button">Create Registration</button></a></h4> -->
                        <div class="table-responsive">
                            <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper" style="width: 100%">
                                <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
        @if(!empty($stream))
        @foreach($stream as $streams)
        <tr>
            <td>{{++$i}}</td>
            <td>{{$streams->remark}}</td>
            

            <td class='d-flex'>
                <a class="btn btn-primary m-1" href="{{ url('view-remarkmaster') .'/'.$streams->id }}">Edit</a>
                <?php $a = "academic_remarksmaster"."-".$streams->id ; ?>
                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-remarkmaster').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
            </td>
          
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="9" class="text-center">No Data Found</td>
        </tr>
        @endif        
    </tbody>
                                <tfoot>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>

                              </tfoot>
                              
                            </table>  
                            {{ $stream->links('pagination::bootstrap-4') }}

                            
                        </div>
                    </div>
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
        console.log('confirmDelete function called');
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

    function confirmDelete2(event) {
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
            $("#remark").val(""); 
            $("input[type='checkbox']").prop('checked', false);
            $("input[type='radio']").prop('checked', false);    
                   
        });
    })

</script>


@endsection
