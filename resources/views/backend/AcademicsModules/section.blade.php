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
<div class="main-content">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Section Master</h1>
        </div>

        {{-- <div class="separator-breadcrumb border-top"></div>
                  <form id="progress-form" class="p-4 progress-form" action="#"  novalidate method=""> --}}

                    <div class="separator-breadcrumb border-top"></div>
                    @if(!empty($section_master))
                            <form id="progress-form" class="p-4 progress-form" action="{{url('store-sectionmaster')}}" method="post">
                            <input type="hidden" 
                        @if(!empty($section_master))
                        @foreach($section_master as $sectionmaster)
                            value=" {{ $sectionmaster->id }}"
                            @endforeach
                        @else
                            value=""
                        @endif
                        name="id"
                    >
                    @else
                        <form id="progress-form" class="p-4 progress-form" action="{{url('save-sectionmaster')}}" method="post">
                    @endif

            @csrf
            <div class="row">


              <div class="col-md-3 form-group mb-3">
                <label for="section">Section  </label>
                <input
                required
                  class="form-control uperletter"
                  id="section"
                  name="section"
                  type="text"
                  @if(!empty($section_master))
                    @foreach($section_master as $sectionmaster)
                      value=" {{ $sectionmaster->section }}"
                    @endforeach
                  @else
                    value=""
                  @endif
                  placeholder="Section"
                />
              </div>

              <div class="col-md-3 form-group mb-3">
                <label for="remark">Remarks  </label>
                <input
                  class="form-control uperletter"

                  id="remark"
                  name="remark"
                  type="text"
                  @if(!empty($section_master))
                    @foreach($section_master as $sectionmaster)
                      value=" {{ $sectionmaster->remark }}"
                    @endforeach
                  @else
                    value=""
                  @endif
                  placeholder="Remark"
                />
              </div>

                <div class="col-md-12">
                    <button class="btn btn-primary">Submit</button>
                    <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>
                    
                    @if(request()->route()->getName() !== 'sectionmaster')
                    <a href="{{ url('sectionmaster') }}" class="btn btn-primary">Add New</a>
             @endif

                </div>

            </div>
        </form><br>
    </div>



        <div class="separator-breadcrumb border-top"></div>
    <div class="row">


            <div class="col-md-12 mb-4">
            <div class="breadcrumb">
                <h1 class="me-2">List of Saved Section :-</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>

                    <div class="card text-start">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper" style="width: 100%">
                                <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Section</th>
                                    <th>Remark </th>
                                    <th>Action </th>
                                    <!-- <th>Section</th>
                                    <th>Class Strength</th> -->
                                </tr>
                                </thead>
                                <tbody>

                                
                        @if(!empty($section))
                        @foreach($section as $sections)
                        <tr>
                        <td>{{++$i}}</td>
                          <td class= "uperletter">{{$sections->section}}</td> 
                          <td class= "uperletter">{{$sections->remark}}</td>                                            

                          <td class='d-flex'>
                              <a class="btn btn-primary m-1" href="{{ url('view-sectionmaster') .'/'.$sections->id}}">Edit</a>
                                <!-- <form id="deleteForm" method="post" action="{{url('delete-sectionmaster')}}">                                
                                    @csrf
                                    <input type="hidden" name="table_name" value="sections">
                                    <input type="hidden" name="delete_id" value="{{ $sections->id }}">
                                    <button type="button" class="btn btn-danger m-1" onclick="confirmDelete(event)">Delete</button>
                                </form> -->
                                <?php $a = "sections"."-".$sections->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-sectionmaster').'/'.$a}}" onclick="confirmDelete(event)">delete</a>
                            </td>
                        </tr>
                        <!-- </?php $i++; ?> -->
                        @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif                                    
                                </tbody>
                                <tfoot>
                                
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
            $("#remark").val("");     
            $("#section").val("");     
                   
        });
    })

</script>



@endsection