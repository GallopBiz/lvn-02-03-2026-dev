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
            <h1 class="me-2">Assign Class to Teacher</h1>
        </div>
                @if(!empty($updateclassteacher))
                  <form action="{{url('store-update-classassignteacher')}}" method="post">                  
                  <input type="hidden" 
                    @if(!empty($updateclassteacher))
                      @foreach($updateclassteacher as $data)
                      <?php print_r($data->id); ?>
                        value=" {{ $data->id }}"
                      @endforeach
                    @else
                      value=""
                    @endif
                    name="id"
                  >
                @else
                    <form action="{{url('save-class-assign-teacher')}}" method="post">
                @endif
            @csrf
                     <div class="row">                        
                       {{--  <div class="col-md-3 form-group mb-3">
                            <label for="lastName1">Class</label>
                            <select id="class_name" class="form-control" name="class_name" autocomplete="shipping address-level1" required>                              
                              @if(!empty($classlist))
                              <option value="" hidden>--select--</option>
                              @foreach($classlist as $each)
                              <option 
                              @if(!empty($updateclassteacher))
                              {{($updateclassteacher[0]->class_name == $each->class_name) ? 'selected' : ''}}
                              @endif                              
                              value="{{$each->class_name}}">{{$each->class_name}}</option>
                              @endforeach
                              @endif
                            </select>
                        </div> --}}


                        {{-- <div class="col-md-3 form-group mb-3">
                          <label for="firstName1">Class Name</label>
                          <select id="class_name" class="form-control" name="class_name" autocomplete="" required>
                             <option value="" disabled selected>Please select</option>
                             @foreach($classlist as $each)
                             <option {{( (!empty($updateclassteacher)) && ($updateclassteacher[0]->class_name==$each->class_name)) ? 'selected' : '' }}
  
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
                                  @if (!empty($updateclassteacher))
                                  @foreach ($updateclassteacher as $s_item)
                                  {{ $class_stream = $s_item->class_name }}
                                  @endforeach
                                  @endif
                                  @foreach ($classlist as $each)
                                  <option {{( (!empty($updateclassteacher)) && ($updateclassteacher[0]->Class==$each->class_name)) ? 'selected' :
                                              '' }} value="{{ $each->class_name }}">{{ $each->class_name }}</option>
                                  @endforeach
                            
                          </select>
                          <span class="classname_msg validation_err"></span>
                      </div>





                      <div class="col-md-3 form-group mb-3">
                        <label for="lastName1">Section</label>
                        <select id="section_name" class="form-control uperletter" name="section_name" autocomplete="shipping address-level1" required>                                                       
                            <option value="">-- Please select --</option>
                            @if(!empty($updateclassteacher))
                                @foreach($updateclassteacher as $s_item)
                                    {{ $selectedSection = $s_item->Section }}
                                @endforeach
                                <option {{ (!empty($selectedSection)) ? 'selected' : '' }} value="{{ $selectedSection }}">{{ $selectedSection }}</option>
                            @endif
                            {{-- @foreach($sectionlist as $section)
                                <option {{ (!empty($selectedSection) && $selectedSection == $section->section_name) ? 'selected' : '' }} value="{{ $section->section_name }}">{{ $section->section_name }}</option>
                            @endforeach --}}
                        </select>
                    </div>
                    


                        {{-- <div class="col-md-3 form-group mb-3">
                          <label for="section_name">Section</label>
                          <select id="section_name" class="form-control" name="section_name" autocomplete="shipping address-level1" required>
                              </?php //print_r($sectionname);die(); 
                              ?>
                      
                              @if (!empty($updateclassteacher))
                              <option value="{{ $updateclassteacher->Section}}" selected>{{ $updateclassteacher->Section}}</option>
                              @else
                      
                              @endif
                          </select>
                      </div> --}}


                      <div class="col-md-3 form-group mb-3">
                        <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                        <label for="lastName1">Teacher Name </label>
                        <!-- <option value="11th SC(BPSY)">Teacher name 1</option> -->
                        <!-- <option value="11th SC(MPSY)">Teacher name 2</option> -->
                        <select name="teacher_name" class="form-control" id="teacher_name">
                          <option value="">-- Please select --</option>
                          @if (!empty($updateclassteacher))
                          @foreach ($updateclassteacher  as $s_item)
                          {{ $c_stream = $s_item->teacher_name }}
                          @endforeach
                          @endif
                          @foreach ($teacherlist as $teacherlist)
                          <option {{( (!empty($c_stream)) && ($c_stream==$teacherlist->teacher_name)) ? 'selected' :
                                      '' }} value="{{ $teacherlist->teacher_name }}">{{ $teacherlist->teacher_name }}</option>
                          @endforeach
                       </select>
                    </div>

                     {{-- <div class="col-md-3 form-group mb-3">
                      <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                      <label for="lastName1">Teacher Name 2</label>
                      <!-- <option value="11th SC(BPSY)">Teacher name 1</option> -->
                      <!-- <option value="11th SC(MPSY)">Teacher name 2</option> -->
                      <select name="teacher_namee" class="form-control" id="teacher_namee">
                        <option value="">-- Please select --</option>
                        @if (!empty($updateclassteacher))
                        @foreach ($updateclassteacher as $s_item)
                        {{ $c_stream = $s_item->teacher_namee }}
                        @endforeach
                        @endif
                        @foreach ($teacherlist as $teacherlist)
                        <option {{( (!empty($c_stream)) && ($c_stream==$teacherlist->teacher_namee)) ? 'selected' :
                                    '' }} value="{{ $teacherlist->teacher_namee }}">{{ $teacherlist->teacher_namee }}</option>
                        @endforeach
                     </select>
                  </div>  --}}


                        <div class="col-md-42">
                            <button class="btn btn-primary">Submit</button>
                            <button type="button" id= "reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>

                            @if(request()->route()->getName() !== 'calssese-assigne-to-teacher')
                            <a href="{{ url('calssese-assigne-to-teacher') }}" class="btn btn-primary">Add New</a>
                        @endif
                        </div>
                    </div>
        </form>
    </div>
    <!-- end of main-content -->
    <br>
    <h1 class="me-2">List of Assign Class to Teacher</h1>
          </div>
        <div class="separator-breadcrumb border-top"></div>

        <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                <!-- <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="role">
                      <input type="hidden" name="column_names[]" value="ename">
                      <input type="hidden" name="column_names[]" value="current_address">
                      <input type="hidden" name="column_names[]" value="mobile_number">
                      <input type="hidden" name="column_names[]" value="joining_date">
                      <input type="hidden" name="column_names[]" value="leaving_date">
                      <input type="hidden" name="column_names[]" value="remarks">
                      <input type="hidden" name="column_names[]" value="call_no">
                      <input type="hidden" name="column_names[]" value="offical_mobile_no">
                      <input type="hidden" name="column_names[]" value="healthstatus">
                      <input type="hidden" name="table_name" value="busstaff">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div> -->
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>Sr.</th>
                            <th>class</th>
                            <th>Section</th>
                            <th>Teacher </th>
                            {{-- <th>Teacher 2</th> --}}
                            <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $i=1;  ?> 
                        @if(!empty($ClassTeacher))
                        @foreach ($ClassTeacher as $data)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $data->Class }}</td>
                            <td>{{ $data->Section }}</td>
                            <td>{{ $data->teacher_name }}</td>
                            {{-- <td>{{ $data->Teacher_2 }}</td> --}}
                            <!-- <td>{{ $data->id }}</td> -->
                            <td class="d-flex"> 
                              <a class="btn btn-raised ripple btn-primary m-1" href="{{ url('classteacher-view') .'/'.$data->id}}">Edit</a><br>
                              <!-- <a class="btn btn-raised ripple btn-raised-danger m-1" href="{{ url('delete') .'/'.$data->id}}">Delete</a> -->
                              <!-- <form id="deleteForm" method="post" action="{{url('classteacher-delete')}}">
                                    @csrf
                            
                                    <input type="hidden" name="table_name{{ $i }}" value="{{ $data->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $data->id }})">Delete</button>
                                </form> -->
                                <?php $a = "classasigntoteacher"."-".$data->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('classteacher-delete').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
                            </td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
<!-- </div> -->
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

function showSections() {
    var iso2 = $("#class_name").val();
    let token = document.getElementsByName("_token")[0].value   
    console.log(iso2);
    if (iso2) {
        $.ajax({
            data: {
                id: iso2
            }
            , url: "{{url('classsection-view')}}/" + iso2
            , headers: {
                'X-CSRF-TOKEN': token
            }
            , method: "POST"
            , dataType: 'json'
            , success: function(data) {
                // console.log(data);
                $('#section_name').html('<option value=""> -- Select All -- </option>');
                for (var i = 0; i < data.length; i++) {
                    var studentData = data[i].section_name;
                    // console.log(studentData, ' ', selected_section);                            
                    $('#section_name').append('<option value="' + studentData + '">' + studentData + '</option>');
                }
            }
            , error: function(xhr, status, error) {
                console.error(error);
            }
        });
    } else {
        $('#section_name').html('<option value="">Select class first</option>');
    }
}

// showSections();

$('#class_name').on('change', showSections)

})


document.addEventListener('DOMContentLoaded', function() {
        $("#reset").on("click", function () {
            $("#class_name").val("");     
            $("#section_name").val("");     
            $("#teacher_name").val("");                      
        });
    })



</script>
@endsection