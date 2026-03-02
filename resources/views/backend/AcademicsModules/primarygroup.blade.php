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
            <h1 class="me-2">Below First Group Master</h1>
        </div>
        {{-- <div class="separator-breadcrumb border-top"></div>  --}}
      
                  <!-- <form id="progress-form" class="p-4 progress-form" action="{{url('save-primarygroupmaster')}}"   novalidate method="post"> -->
                 




    <div class="separator-breadcrumb border-top"></div>
    @if(!empty($stream_master))
    <form id="progress-form" class="p-4 progress-form" action="{{url('store-primarygroupmaster')}}" method="post">
      <input type="hidden" value="{{$stream_master[0]->id}}" name="id">



                    @else
                        <form id="progress-form" class="p-4 progress-form" action="{{url('save-primarygroupmaster')}}"  method="post">
                    @endif

        @csrf
        <div class="row">
          <div class="col-md-3 form-group mb-3">
            <label for="lastName1">Class Group</label>
            <select required id="class_name" class="form-control" name="class_group" autocomplete="" required>
              <option value="">-- Please select --</option>
                    @if (!empty($stream_master))
                    @foreach ($stream_master as $s_item)
                    {{ $class_stream = $s_item->class_group }}
                    @endforeach
                    @endif
                    @foreach ($classlist as $each)
                    <option {{( (!empty($class_stream)) && ($stream_master[0]->class_group==$each->class_name)) ? 'selected' :
                                '' }} value="{{ $each->class_name }}">{{ $each->class_name }}</option>
                    @endforeach
              
            </select>
          </div>
          <div class="col-md-3 form-group mb-3">
            <label for="remark">Below First Group Name </label>
            <input required class="form-control uperletter" id="primary_group_name" name="primary_group_name" type="text" @if(!empty($stream_master)) @foreach($stream_master as $streammaster) value=" {{ $streammaster->primary_group_name }}" @endforeach @else value="" @endif placeholder="primary group name" />
          </div>

          <div class="col-md-3 form-group mb-3">
            <label for="remark">Display Order </label>
            <input required class="form-control" id="display_order" name="display_order" type="number" value="<?php if (!empty($stream_master[0]->display_order)) {echo $stream_master[0]->display_order; } ?>" placeholder="display_order" />
          </div>
          {{-- <div class="col-md-3 form-group mb-3">
            <label for="remark">Visibility </label>
            <input id="visibility" name="visibility" autocomplete="off" type="checkbox" value="No"  <?php if(!empty($stream_master[0]->visibility)){echo "checked";?> value="Yes"<?php }else{?> <?php } ?> placeholder="visibility" />                
            @csrf
          </div> --}}
          
            <div class="row">
              {{-- <div class="col-md-3 form-group mb-3">
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
                       

                      
                {{-- <div class="col-md-3 form-group mb-3">
                <label for="remark">Below First Group Name  </label>
                <input
                  class="form-control"
                  id="primary_group_name"
                  name="primary_group_name"
                  type="text"
                  @if(!empty($stream_master))
                    @foreach($stream_master as $streammaster)
                      value=" {{ $streammaster->primary_group_name }}"
                    @endforeach
                  @else
                    value=""
                  @endif
                  placeholder="primary group name"
                />
              </div> --}}

              {{-- <div class="col-md-3 form-group mb-3">
                <label for="remark">Display Order  </label>
                <input
                  class="form-control"
                  id="display_order"
                  name="display_order"
                  type="number"
                  @if(!empty($stream_master))
                    @foreach($stream_master as $streammaster)
                      value=" {{ $streammaster->display_order }}"
                    @endforeach
                  @else
                    value=""
                  @endif
                  placeholder="display_order"
                />
              </div> --}}

              <div class="col-md-3 form-group mb-3">
                <label for="remark">Visibility  </label>                
                <input                  
                  id="visibility"
                  name="visibility"
                  autocomplete="off"
                  type="checkbox"
                  {{ (!empty($stream_master) && $stream_master[0]->visibility) ? 'checked' : ''}}                 
                  placeholder="visibility"
                />
                
                <label for="lastName1"> Dont show in this session </label>
              </div>  


                <!-- <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Display Order</label>
                    <input name="DisplayOrder" class="form-control" id="DisplayOrder" type="number"/>
                </div>
                <div class="col-md-3 form-group mb-3">
                    <br>
                    <label for="lastName1">Visibility </label><br>
                    <input type="checkbox" id="StudentRelated" name="Visibility" id="Visibility" />  <label for="lastName1"> Dont show in this session </label>
                </div> -->


                <div class="col-md-12">
                    <button class="btn btn-primary">Submit</button>
                    <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>


                          @if(request()->route()->getName() !== 'primarygroup')
                         <a href="{{ url('primarygroup') }}" class="btn btn-primary">Add New</a>
                         @endif
                         
                </div>
            </div>
        </form><br>
    </div>
    <div class="breadcrumb">
            <h1 class="me-2">Below First Group Master</h1>
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
            <th>Class Group</th>
            <th>Below First Group Name</th>
            <th>Display Order</th>
            <th>Action</th>
        </tr>
    </thead>

              <tbody>
                @if(!empty($stream))
                @foreach($stream as $streams)
                <tr>
                  <td>{{++$i}}</td>
                  <td class= "uperletter">{{$streams->class_group}}</td>
                  <td class= "uperletter">{{$streams->primary_group_name}}</td>
                  <td>{{$streams->display_order}}</td>
                  <!-- <td>{{$streams->visibility}}</td> -->
                  <td class='d-flex'>
                    <a class="btn btn-primary m-1" href="{{ url('view-primarygroupmaster') .'/'.$streams->id }}">Edit</a>
                    <?php $a = "primarygroup" . "-" . $streams->id; ?>
                    <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-primarygroupmaster').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
                  </td>
                </tr>
                @endforeach
                @else
                <tr>
                  <td colspan="5" class="text-center">No Data Found</td>
                </tr>
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
            $("#display_order").val("");    
            $("input[type='checkbox']").prop('checked', false);
            $("input[type='radio']").prop('checked', false);                  
        });
    })

</script>




@endsection