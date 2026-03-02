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
            <h1 class="me-2"> Sub Head Master</h1>
        </div>
        <!-- <div class="separator-breadcrumb border-top"></div>
                  <form id="progress-form" class="p-4 progress-form" action="#"  novalidate method=""> -->
                 

                  <div class="separator-breadcrumb border-top"></div>
                  
                    @if(!empty($stream_master))
                            <form id="progress-form" class="p-4 progress-form" action="{{url('store-subheadmaster')}}"  method="post">
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
                        <form id="progress-form" class="p-4 progress-form" action="{{url('save-subheadmaster')}}"  method="post">
                    @endif
  
            @csrf
            <div class="row">


            {{-- <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Class Name</label>
                        <select id="class_name" class="form-control" name="class_name" autocomplete="" required>
                           <option value="" disabled selected>Please select</option>
                           @foreach($classlist as $each)
                           <option {{( (!empty($stream_master)) && ($stream_master[0]->class_name==$each->class_name)) ? 'selected' : '' }}

                           value="{{$each->class_name}}">{{$each->class_name}}</option>
                           @endforeach
                            {{-- @foreach(config('global.class_name') as $each)
                           <option value="{{$each}}">{{$each}}</option>
                           @endforeach  --}}
                        {{-- </select> --}}
                        {{-- <span class="classname_msg validation_err"></span> --}}
                      {{-- </div>  --}} 


                      <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Class Name</label>
                        <select id="class_name" class="form-control" name="class_name" autocomplete="" required>
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
                <label for="head_name">Head Name </label>
                <input
                  class="form-control"
                  id="head_name"
                  name="head_name"
                  type="text"
                  @if(!empty($stream_master))
                    @foreach($stream_master as $streammaster)
                      value=" {{ $streammaster->head_name }}"
                    @endforeach
                  @else
                    value=""
                  @endif
                  placeholder="head_name"
                />
              </div> --}}

              <div class="col-md-3 form-group mb-3">
                <label for="head_name">Head Name</label>
                <select name="head_name" class="form-control uperletter" id="head_name" required>
                    <option value="">-- Please select --</option>
                    @if (!empty($stream_master))
                    @foreach ($stream_master as $s_item)
                    {{ $c_stream = $s_item->head_name }}
                    @endforeach
                    @endif
                    @foreach ($headlist as $headlist)
                    <option {{( (!empty($c_stream)) && ($c_stream==$headlist->head_name)) ? 'selected' :
                                '' }} value="{{ $headlist->head_name }}">{{ $headlist->head_name }}</option>
                    @endforeach
                 </select>
                 </div>

              <div class="col-md-3 form-group mb-3">
                <label for="sub_head_name">Sub Head Name </label>
                <input
                required
                  class="form-control uperletter"
                  id="sub_head_name"
                  name="sub_head_name"
                  type="text"
                  @if(!empty($stream_master))
                    @foreach($stream_master as $streammaster)
                      value=" {{ $streammaster->sub_head_name }}"
                    @endforeach
                  @else
                    value=""
                  @endif
                  placeholder="sub_head_name"
                />
              </div>

              <div class="col-md-3 form-group mb-3">
                <label for="display_order">Display Order  </label>
                <input
                  class="form-control uperletter"
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
                <label for="remark">Visibility  </label>
                <input
                  
                  id="visibility"
                  name="visibility"
                  autocomplete="off"
                  type="checkbox"
                   value="Yes"
                  @if(!empty($stream_master))
                    @foreach($stream_master as $streammaster)
                      value=" {{ $streammaster->visibility == 'Yes' }}"
                    @endforeach
                  @else
                    value=""
                  @endif
                  placeholder="visibility"
                  {{ (!empty($stream_master)) &&  ($stream_master[0]->visibility == "on") ? 'checked' : '' }}  
                />
                <label for="lastName1"> Dont show in this session </label>
              </div> 
              
               

              {{-- <div class="col-md-3 form-group mb-3">
                <label for="visibility">Visibility</label>
                <input
                    id="visibility"
                    name="visibility"
                    type="checkbox"
                    value="Yes" 
                    @if(!empty($stream_master))
                        @foreach($stream_master as $streammaster)
                            @if($streammaster->visibility == 'Yes')
                                checked
                            @endif
                        @endforeach
                    @endif
                    placeholder="visibility"
                />
            </div> --}}











            <div class="col-md-3 form-group mb-3">
    <br>
    <label for="lastName1">E-1 Entry Type</label><br>
    <label class="radio-inline">
        <input type="radio" name="entry_type_e1" value="Number" {{ (!empty($stream_master) && $stream_master[0]->entry_type_e1 == "Number") ? 'checked' : '' }}>
        Number
    </label>
    <label class="radio-inline">
        <input type="radio" name="entry_type_e1" value="Grade" {{ (!empty($stream_master) && $stream_master[0]->entry_type_e1 == "Grade") ? 'checked' : '' }}>
        Grade
    </label>
</div>

<div class="col-md-3 form-group mb-3">
  <br>
  <label for="lastName1">E-2 Entry Type</label><br>
  <label class="radio-inline">
      <input type="radio" name="entry_type_e2" value="Number" {{ (!empty($stream_master) && $stream_master[0]->entry_type_e2 == "Number") ? 'checked' : '' }}>
      Number
  </label>
  <label class="radio-inline">
      <input type="radio" name="entry_type_e2" value="Grade" {{ (!empty($stream_master) && $stream_master[0]->entry_type_e2 == "Grade") ? 'checked' : '' }}>
      Grade
  </label>
</div>

<div class="col-md-3 form-group mb-3">
  <br>
  <label for="lastName1">E-3 Entry Type</label><br>
  <label class="radio-inline">
      <input type="radio" name="entry_type_e3" value="Number" {{ (!empty($stream_master) && $stream_master[0]->entry_type_e3 == "Number") ? 'checked' : '' }}>
      Number
  </label>
  <label class="radio-inline">
      <input type="radio" name="entry_type_e3" value="Grade" {{ (!empty($stream_master) && $stream_master[0]->entry_type_e3 == "Grade") ? 'checked' : '' }}>
      Grade
  </label>
</div>

<div class="col-md-3 form-group mb-3">
  <br>
  <label for="lastName1">E-4 Entry Type</label><br>
  <label class="radio-inline">
      <input type="radio" name="entry_type_e4" value="Number" {{ (!empty($stream_master) && $stream_master[0]->entry_type_e4 == "Number") ? 'checked' : '' }}>
      Number
  </label>
  <label class="radio-inline">
      <input type="radio" name="entry_type_e4" value="Grade" {{ (!empty($stream_master) && $stream_master[0]->entry_type_e4 == "Grade") ? 'checked' : '' }}>
      Grade
  </label>
</div>


               
                <div class="col-md-12">
                    <button class="btn btn-primary">Submit</button>
                    <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>

                    @if(request()->route()->getName() !== 'subheadmaster')
                    <a href="{{ url('subheadmaster') }}" class="btn btn-primary">Add New</a>
                @endif



                </div>
            </div>
        </form><br>
    </div>
    <div class="breadcrumb">
            <h1 class="me-2">list Head Master</h1>
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
            <th>Head Name</th>
            <th>Sub Head Name</th>
            <th>Display Order</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @if(!empty($stream))
        @foreach($stream as $streams)
        <tr>
            <td>{{++$i}}</td>
            <td class= "uperletter">{{$streams->head_name}}</td>
            <td class= "uperletter">{{$streams->sub_head_name}}</td>
            <td>{{$streams->display_order}}</td>

            <td class='d-flex'>
                <a class="btn btn-primary m-1" href="{{ url('view-subheadmaster') .'/'.$streams->id }}">Edit</a>
                <?php $a = "subheads"."-".$streams->id ; ?>
                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-subheadmaster').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
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
            <th>Head Name</th>
            <th>Sub Head Name</th>
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
            $("#head_name").val(""); 
            $("#sub_head_name").val(""); 
            $("#display_order").val("");     
            $("input[type='checkbox']").prop('checked', false);
            $("input[type='radio']").prop('checked', false);
            
        });
    })

</script>











@endsection