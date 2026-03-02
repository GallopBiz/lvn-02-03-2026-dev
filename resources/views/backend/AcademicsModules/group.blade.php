@extends('backend.layouts.main')
@section('main-container')
@php
                      $i = 0;
                    @endphp
<div class="main-content">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Group Master</h1>
        </div>
        <!-- <div class="separator-breadcrumb border-top"></div>
                  <form id="progress-form" class="p-4 progress-form" action="#"  novalidate method="">
                  -->
                  <div class="separator-breadcrumb border-top"></div>
                    @if(!empty($stream_master))
                            <form id="progress-form" class="p-4 progress-form" action="{{url('store-groupmaster')}}"  method="post">
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
                        <form id="progress-form" class="p-4 progress-form" action="{{url('save-groupmaster')}}" method="post">
                    @endif








            @csrf
            <div class="row">


            {{-- <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Class Group</label>
                    <select id="class_group" class="form-control" name="class_group" autocomplete="shipping address-level1" required>
                              <option value="">---Select---</option>
                              <option value="For-Student">For Student</option>
                              <option value="For-Staff">For Staff</option>
                              <option value="For-Worker">For Worker</option>
                           </select>
                         </div> --}}


{{-- 
                         <div class="col-md-3 form-group mb-3">
                          <label for="firstName1">Class Name</label>
                          <select id="class_name" class="form-control" name="class_name" autocomplete="" required>
                              @if (!empty($teacher_subject))
                                  <option value="" disabled hidden>Please select</option>
                                  @foreach($classlist as $each)
                                      @if(in_array($each->class_name, ['Nursery', 'Kg1', 'Kg2']))
                                          <option value="{{$each->class_name}}" {{ ($teacher_subject->class_name == $each->class_name) ? 'selected' : ''}}>{{$each->class_name}}</option>
                                      @endif
                                  @endforeach
                              @else
                                  <option value="" disabled selected>Please select</option>
                                  @foreach($classlist as $each)
                                      @if(in_array($each->class_name, ['Nursery', 'Kg1', 'Kg2']))
                                          <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                                      @endif
                                  @endforeach
                              @endif
                          </select>
                          <span class="classname_msg validation_err"></span>
                      </div> --}}
                      


                      <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Class Name</label>
                        <select required id="class_name" class="form-control" name="class_name" autocomplete="" required>
                          <option value="">-- Please select --</option>
                                @if (!empty($stream_master))
                                @foreach ($stream_master as $s_item)
                                {{ $class_stream = $s_item->class_name }}
                                @endforeach
                                @endif
                                @foreach ($classlist as $each)
                                <option {{( (!empty($class_stream)) && ($stream_master[0]->class_name==$each->class_name)) ? 'selected' :
                                            '' }} value="{{ $each->class_name }}">{{ $each->class_name }}</option>
                                @endforeach
                          
                        </select>
                        <span class="classname_msg validation_err"></span>
                    </div>
                    















                       {{-- <div class="col-md-3 form-group mb-3">
                         <label for="firstName1">Primary Group </label>
                         <select id="primary_group_name" class="form-control" name="primary_group_name" autocomplete="shipping address-level1" required>
                              <option value="">---Select---</option>
                              <option value="For-Student">For Student</option>
                              <option value="For-Staff">For Staff</option>
                              <option value="For-Worker">For Worker</option>
                         </select>
                          </div> --}}
                          

                          <div class="col-md-3 form-group mb-3">
                            <label for="primary_group_name">Primary Group</label>
                            <select required name="primary_group_name" class="form-control" id="primary_group_name">
                                <option value="">-- Please select --</option>
                                @if (!empty($stream_master))
                                @foreach ($stream_master as $s_item)
                                {{ $c_stream = $s_item->primary_group_name }}
                                @endforeach
                                @endif
                                @foreach ($primarylist as $primarylist)
                                <option {{( (!empty($c_stream)) && ($c_stream==$primarylist->primary_group_name)) ? 'selected' :
                                            '' }} value="{{ $primarylist->primary_group_name }}">{{ $primarylist->primary_group_name }}</option>
                                @endforeach
                            </select>
                        </div>
























                <div class="col-md-3 form-group mb-3">
                <label for="group_name">Group Name </label>
                <input
                required
                  class="form-control"
                  id="group_name"
                  name="group_name"
                  type="text"
                  @if(!empty($stream_master))
                    @foreach($stream_master as $streammaster)
                      value=" {{ $streammaster->group_name }}"
                    @endforeach
                  @else
                    value=""
                  @endif
                  placeholder="group_name"
                />
              </div>









              <div class="col-md-3 form-group mb-3">
                <label for="display_order">Display Order  </label>
                <input
                required
                  class="form-control"
                  id="display_order"
                  name="display_order"
                  type="number"
                  @if(!empty($stream_master))
                    @foreach($stream_master as $streammaster)
                      value="{{ $streammaster->display_order }}"
                    @endforeach
                  @else
                    value=""
                  @endif
                  placeholder="display_order"
                />
              </div>
              <div class="col-md-3 form-group mb-3">
    <label for="health_group">Health Group</label>
    <input
        id="health_group"
        name="health_group"
        type="checkbox"
        value="Yes" {{-- Default value when checked --}}
        @if(!empty($stream_master))
            @foreach($stream_master as $streammaster)
                @if($streammaster->health_group == 'Yes')
                    checked
                @endif
            @endforeach
        @endif
        placeholder="health_group"
    />
</div>


                
                <div class="col-md-3 form-group mb-3">
    <br>
    <label for="lastName1">Entry Type</label><br>
    <label class="radio-inline">
        <input type="radio" name="entry_type" value="Option 1" checked>Option 1
    </label>
    <label class="radio-inline">
        <input type="radio" name="entry_type" value="Option 2">Option 2
    </label>
    <label class="radio-inline">
        <input type="radio" name="entry_type" value="Option 3">Option 3
    </label>
</div>
                <div class="col-md-12">
                    <button class="btn btn-primary">Submit</button>

                    <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>

                    @if(request()->route()->getName() !== 'groupmaster')
                    <a href="{{ url('groupmaster') }}" class="btn btn-primary">Add New</a>
             @endif

                </div>
            </div>
        </form><br>
    </div>

    <div class="breadcrumb">
            <h1 class="me-2">list Group Master</h1>
          </div>
        <div class="separator-breadcrumb border-top"></div>

    <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Below First Group Name</th>
                          <th>Group Name</th>
                          <th>Display Order</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                      @if(!empty($stream))
                        @foreach($stream as $streams)
                        <tr>
                        <td>{{++$i}}</td>
                          <td class= "uperletter">{{$streams->primary_group_name}}</td> 
                          <td class= "uperletter">{{$streams->group_name}}</td>  
                          <td>{{$streams->display_order}}</td>                                           

                          <td class='d-flex'>
                              <a class="btn btn-primary m-1" href="{{ url('view-groupmaster') .'/'.$streams->id}}">Edit</a>
                                <!-- <form id="deleteForm" method="post" action="{{url('delete-streammaster')}}">                                
                                    @csrf
                                    <input type="hidden" name="table_name" value="streams">
                                    <input type="hidden" name="delete_id" value="{{ $streams->id }}">
                                    <button type="button" class="btn btn-danger m-1" onclick="confirmDelete(event)">Delete</button>
                                </form> -->
                                <?php $a = "groupmaster"."-".$streams->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-groupmaster').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
                            </td>
                        </tr>
                        <!-- </?php $i++; ?> -->
                        @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif                                    
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>Sr.</th>
                          <th>Class Group</th>
                          <th>Below First Group Name</th>
                          <th>Display Order</th>
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
            $("#class_name").val("");     
            $("#primary_group_name").val("");     
            $("#group_name").val("");     
            $("#display_order").val(""); 
            $("input[type='checkbox']").prop('checked', false);
            $("input[type='radio']").prop('checked', false);                     
        });
    })


</script>





@endsection