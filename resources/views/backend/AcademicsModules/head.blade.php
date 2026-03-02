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
            <h1 class="me-2">Head Master</h1>
        </div>
        <!-- <div class="separator-breadcrumb border-top"></div>
                  <form id="progress-form" class="p-4 progress-form" action="#"  novalidate method=""> -->
                 

                  <div class="separator-breadcrumb border-top"></div>
                    @if(!empty($stream_master))
                            <form id="progress-form" class="p-4 progress-form" action="{{url('store-headmaster')}}" method="post">
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
                        <form id="progress-form" class="p-4 progress-form" action="{{url('save-headmaster')}}"   method="post">
                    @endif
  
            @csrf
            <div class="row">

            {{-- <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Group Name</label>
                    <select id="group_name" class="form-control" name="group_name" autocomplete="shipping address-level1" required>
                              <option value="">---Select---</option>
                              <option value="For-Student">For Student</option>
                              <option value="For-Staff">For Staff</option>
                              <option value="For-Worker">For Worker</option>
                           </select>
                </div> --}}



                {{-- <div class="col-md-3 form-group mb-3">
                            <label for="firstName1">Class Name</label>
                            <select id="classname" class="form-control" name="classname" autocomplete="" required>
                               <option value="" disabled selected>Please select</option>
                               @foreach($classlist as $each)
                               <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                               @endforeach
                                {{-- @foreach(config('global.class_name') as $each)
                               <option value="{{$each}}">{{$each}}</option>
                               @endforeach  --}}
                            {{-- </select>
                            <span class="classname_msg validation_err"></span>
                          </div>  --}} 

                          {{-- <div class="col-md-3 form-group mb-3">
                            <label for="firstName1">Class Name</label>
                            <select id="class_name" class="form-control" name="class_name" autocomplete="" required>
                               <option value="" disabled selected>Please select</option>
                               @foreach($classlist as $each)
                               
                               <option {{( (!empty($stream_master)) && ($stream_master[0]->class_name==$each->class_name)) ? 'selected' : '' }}

                                value="{{$each->class_name}}">{{$each->class_name}}</option>


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
                                @foreach ($classlist as $each)
                                <option {{( (!empty($stream_master)) && ($stream_master[0]->class_name==$each->class_name)) ? 'selected' :
                                            '' }} value="{{ $each->class_name }}">{{ $each->class_name }}</option>
                                @endforeach
                            </select>
                            <span class="classname_msg validation_err"></span>
                        </div>
                        

                 <div class="col-md-3 form-group mb-3">
                  <label for="group_name">Group Name</label>
                  <select name="group_name" class="form-control uperletter" id="group_name" required>
                      <option value="">-- Please select --</option>
                      @if (!empty($stream_master))
                      @foreach ($stream_master as $s_item)
                      {{ $c_stream = $s_item->group_name }}
                      @endforeach
                      @endif
                      @foreach ($grouplist as $grouplist)
                      <option {{( (!empty($c_stream)) && ($c_stream==$grouplist->group_name)) ? 'selected' :
                                  '' }} value="{{ $grouplist->group_name }}">{{ $grouplist->group_name }}</option>
                      @endforeach
                   </select>
                   </div>


                   {{-- <div class="col-md-3 form-group mb-3">
                    <label for="head_name">Head Name</label>
                    <select name="head_name" class="form-control" id="head_name">
                        <option value="">-- Please select --</option>
                        @if (!empty($stream_master))
                        @foreach ($stream_master as $s_item)
                        {{ $c_stream = $s_item->head_name }}
                        @endforeach
                        @endif
                        @foreach ($subheadlist as $subheadlist)
                        <option {{( (!empty($c_stream)) && ($c_stream==$subheadlist->head_name)) ? 'selected' :
                                    '' }} value="{{ $subheadlist->head_name }}">{{ $subheadlist->head_name }}</option>
                        @endforeach
                     </select>
                     </div> --}}

                <div class="col-md-3 form-group mb-3">
                <label for="head_name">Head Name </label>
                <input
                required
                  class="form-control uperletter"
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

                <!-- <div class="col-md-3 form-group mb-3">
                    <label for="firstName1">Head Name </label>
                    <input name="head_name" class="form-control" id="head_name" type="text" 
                                                placeholder="Head_Name" />
                </div>   -->

                <!-- <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Display Order</label>
                    <input name="Display_Order" class="form-control" id="DisplayOrder" type="number"/>
                </div> -->
                




               <div class="col-md-3 form-group mb-3">
        <label>Applicable To</label>
                      
            <input type="checkbox" id="e1" name="E1" class="class_E1"            
            {{ (!empty($stream_master)) &&  str_contains($stream_master[0]->applicable_to, "E1") ? 'checked' : '' }} 
            > E1

            <input type="checkbox"  id= "e2" name="E2" class="class_E2" 
            {{ (!empty($stream_master)) &&  str_contains($stream_master[0]->applicable_to, "E2") ? 'checked' : '' }} 
            > E2        
            <input type="checkbox" id= "e3" name="E3" class="class_E3"
            
            {{ (!empty($stream_master)) &&  str_contains($stream_master[0]->applicable_to, "E3") ? 'checked' : '' }} 
            > E3
            <input type="checkbox" id="e4" name="E4" class="class_E4"
            {{ (!empty($stream_master)) &&  str_contains($stream_master[0]->applicable_to, "E4") ? 'checked' : '' }}             
            > E4        
    </div>





                {{-- <div class="col-md-3 form-group mb-3">
                    <br>
                    <label for="is_elective">is Elective</label> 
                    <!-- <input type="checkbox" id="StudentRelated" name="Health_Group" /> -->
                    <input type="checkbox" id="is_elective" name="is_elective" autocomplete="off"/>
                </div> --}}


                <div class="col-md-3 form-group mb-3">
                  <label for="is_elective">is Elective</label>
                  <input
                      id="is_elective"
                      name="is_elective"
                      type="checkbox"
                      value="Yes" 
                      @if(!empty($stream_master))
                          @foreach($stream_master as $streammaster)
                              @if($streammaster->is_elective == 'Yes')
                                  checked
                              @endif
                          @endforeach
                      @endif
                      placeholder="is_elective"
                  />
              </div>





                
                <div class="col-md-12">
                    <button class="btn btn-primary">Submit</button>

                    <button type="button"  id="reset"  class="btn btn-primary" name="btn" value="Reset Form">Reset</button>

                    @if(request()->route()->getName() !== 'headmaster')
                    <a href="{{ url('headmaster') }}" class="btn btn-primary">Add New</a>
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
                          <th>Group Name</th>
                          <th>Head Name</th>
                          <th>Display Order</th>
                          <th>Elective</th>
                          <th>Applicable to</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                      @if(!empty($stream))
                        @foreach($stream as $streams)
                        <tr>
                        <td>{{++$i}}</td>
                          <td class= "uperletter">{{$streams->head_name}}</td> 
                          <td class= "uperletter">{{$streams->group_name}}</td>  
                          <td>{{$streams->display_order}}</td>
                          <td>{{$streams->is_elective}}</td>                                           
                          <td class= "uperletter">{{$streams->applicable_to}}</td>                                           

                          <td class='d-flex'>
                              <a class="btn btn-primary m-1" href="{{ url('view-headmaster') .'/'.$streams->id}}">Edit</a>
                                <!-- <form id="deleteForm" method="post" action="{{url('delete-streammaster')}}">                                
                                    @csrf
                                    <input type="hidden" name="table_name" value="streams">
                                    <input type="hidden" name="delete_id" value="{{ $streams->id }}">
                                    <button type="button" class="btn btn-danger m-1" onclick="confirmDelete(event)">Delete</button>
                                </form> -->
                                <?php $a = "headmaster"."-".$streams->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-headmaster').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
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
                          <th>Group Name</th>
                          <th>Head Name</th>
                          <th>Display Order</th>
                          
                          <th>Elective</th>
                          <th>Applicable to</th>
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

    document.addEventListener("DOMContentLoaded", function() {
      
      $("input.class_E1").attr("disabled", true);
      $("input.class_E2").attr("disabled", true);
      $("input.class_E3").attr("disabled", true);
      $("input.class_E4").attr("disabled", true);
           

      document.getElementById("class_name").addEventListener("click", function(e) {
        let classvalue = e.target.value;

        if(!classvalue || classvalue==""){
          return
        }
        
        let classBelowFirst = ['Nursery', 'Kg1', 'Kg2']
        if(classBelowFirst.includes(classvalue)){
          $("input.class_E1").attr("disabled", false);
          $("input.class_E2").attr("disabled", false);
          $("input.class_E3").attr("disabled", true);
          $("input.class_E4").attr("disabled", true);
        }
        else{
          $("input.class_E1").attr("disabled", false);
          $("input.class_E2").attr("disabled", false);
          $("input.class_E3").attr("disabled", false);
          $("input.class_E4").attr("disabled", false);
        }
      })


    })

    document.addEventListener('DOMContentLoaded', function() {
        $("#reset").on("click", function () {
            $("#class_name").val("");     
            $("#head_name").val("");     
            $("#group_name").val("");     
            $("#display_order").val("");
            $("#e1").val("");
            $("#e2").val("");
            $("#e3").val("");
            $("#e4").val(""); 
            $("#is_elective").val("");                     
        });
    })

    document.addEventListener('DOMContentLoaded', function() {
        $("#reset").on("click", function () {
            $("#class_name").val("");     
            $("#group_name").val("");     
            $("#head_name").val("");                      
            $("#display_order").val("");
            $("input[type='checkbox']").prop('checked', false);
            $("input[type='radio']").prop('checked', false);
        });
    })


</script>











@endsection