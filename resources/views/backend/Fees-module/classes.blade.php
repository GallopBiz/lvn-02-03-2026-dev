@extends('backend.layouts.main')
@section('main-container')


<style>

.uperletter{
  text-transform: capitalize;
}  

</style>
<div class="main-content pt-4">


    <div class="row">
        <div class="col-md-4 form-group mb-3">
            <div class="form_section1_div">
                <div class="breadcrumb">
                    <h1 class="me-2">Class Name</h1>
                </div>
                <div class="separator-breadcrumb border-top"></div>
                    @if(!empty($maintenance_groups))
                        <form id="progress-form" class="p-4 progress-form" action="{{url('storeg-classname')}}"  novalidate method="post">
                        <input type="hidden" 
                            @if(!empty($maintenance_groups))
                                @foreach($maintenance_groups as $maintenance_group)
                                value=" {{ $maintenance_group->id }}"
                                @endforeach
                            @else
                                value=""
                            @endif
                            name="id"
                    >
                    @else
                        <form id="progress-form" class="p-4 progress-form" action="{{url('storeg-classname')}}"  novalidate method="post">
                    @endif
                    @csrf
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <label for="lastName1">Class Name:</label>
                            <input type="text" class="form-control uperletter" placeholder="class name" 
                            name="class_name" required <?php 
                            if(!empty($maintenance_groups)){ 
                                foreach($maintenance_groups as $maintenance_group){
                                    echo 'value="'.$maintenance_group->class_name.'"'; 
                                }
                            } 
                            else{
                                echo 'value=""';
                            } 
                            ?> > 
                            <span class="text-danger" id="error-message"></span>
                            <span class="text-danger">                                
                                 @if (!empty(Session::has('class_error')))
                                  {{ Session::get('class_error') }}
                                 @endif                                             
                             </span>
                        </div>
                        <div class="col-md-42">
                            <button class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-8 mb-4">
            <div class="form_section1_div">
                <div class="breadcrumb">
                    <h1 class="me-2">Classes And Section</h1>
                </div>
                
                <div class="separator-breadcrumb border-top"></div>
                    @if(!empty($maintenance_s))
                        <form id="myForm" class="p-4 progress-form" action="{{url('updateclasses')}}"  novalidate method="post">
                        <input type="hidden" 
                            @if(!empty($maintenance_s))
                                @foreach($maintenance_s as $maintenance)
                                value=" {{ $maintenance->id }}"
                                @endforeach
                            @else
                                value=""
                            @endif
                            name="id"
                    >
                    @else
                        <form id="myForm" class="p-4 progress-form" action="{{url('classes')}}"  novalidate method="post">
                    @endif
                    @csrf
                    <div class="row">
                        <div class="col-md-3 form-group mb-3">
                            <label for="lastName1">Classes:</label>
                            <select name="class_name" class="form-control" id="class_name">
                                <?php 
                                    if(!empty($select_main)){
                                        foreach($select_main as $select){
                                            if(!empty($maintenance_s)){ 
                                                foreach($maintenance_s as $maintenance){
                                                    if($maintenance->class_name == $select->class_name){
                                                        echo '<option value="'.$maintenance->class_name.'" selected>'.$select->class_name.'</option>';
                                                    }else {
                                                        echo '<option value="'.$select->class_name.'">'.$select->class_name.'</option>';
                                                    }
                                                }
                                            } else {
                                                echo '<option value="'.$select->class_name.'">'.$select->class_name.'</option>';
                                            }
                                        }
                                    }
                                ?>

                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="lastName1">Section Name:</label>
                            <input type="text" class="form-control uperletter" placeholder="section Name" 
                            name="section_name" <?php 
                            if(!empty($maintenance_s)){ 
                                foreach($maintenance_s as $maintenance){
                                    echo 'value="'.$maintenance->section_name.'"'; 
                                }
                            } 
                            else{
                                echo 'value=""';
                            } 
                            ?> >
                            <span id="sectionNameError" class="text-danger"></span>
                        </div>  
                        <div class="col-md-3 form-group mb-3">
                            <label for="lastName1">Start Time:</label>
                            
                            <input type="text" class="form-control" required name="start_time" placeholder="Enter Time" id="start_time" onclick="showTimePicker('start_time', '<?php echo !empty($maintenance_s) ? 'H:i' : ''; ?>')" <?php 
    if(!empty($maintenance_s)){ 
        foreach($maintenance_s as $maintenance){
            echo 'value="'.date("h:i A", strtotime($maintenance->start_time)).'"'; 
        }
    } 
    else{
        echo 'value=""';
    } 
?>>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="lastName1">End Time:</label>
                            <input type="text" class="form-control" required name="end_time" placeholder="Enter Time" id="end_time" onclick="showTimePicker('end_time', '<?php echo !empty($maintenance_s) ? 'H:i' : ''; ?>')" <?php 
    if(!empty($maintenance_s)){ 
        foreach($maintenance_s as $maintenance){
            echo 'value="'.date("h:i A", strtotime($maintenance->end_time)).'"'; 
        }
    } 
    else{
        echo 'value=""';
    } 
?>>
                        </div>
                        <div class="col-md-42">
                            <button class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


















    
    <br>

    <div class="separator-breadcrumb border-top"></div>
    <div class="row">
        <div class="col-md-4 mb-4">

            <div class="breadcrumb">
                <h1 class="me-2">Class Name</h1>
            </div>

            <div class="separator-breadcrumb border-top"></div>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card text-start">
                    <div class="card-body">
                        <!-- <h4 class="card-title mb-3 text-end"><a href="{{url('add-student-registrations')}}"><button class="btn btn-outline-primary" type="button">Create Registration</button></a></h4> -->
                        <div class="table-responsive">
                            <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper" style="width: 100%">
                                
                                <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Class Name</th>
                                    <th>Action</th>
                                </tr>
                                </thead>

                                <tbody>
                                <?php $i=1;  ?>
                                @if(!empty($maintenancegs))
                                @foreach ($maintenancegs as $maintenancg)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $maintenancg->class_name }}</td>
                                    <!-- <td> 
                                        <a class="btn btn-raised ripple btn-raised-warning m-1" href="{{ url('editg-classname') .'/'.$maintenancg->id}}">Edit</a>
                                        <a class="btn btn-raised ripple btn-raised-danger m-1" href="{{ url('deleteg-maintenance-group-master') .'/'.$maintenancg->id}}">Delete</a>
                                    </td> -->
                                    <td class='d-flex'>
                              <a class="btn btn-primary m-1" href="{{ url('editg-classname') .'/'.$maintenancg->id}}">Edit</a>
                                     {{-- <form id="deletegmaintenancegroupmasterform" method="post" action="{{ url('deleteg-maintenance-group-master') }}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="maintenancegroupmaster">
                                    <input type="hidden" name="delete_id" value="{{ $maintenancg->id }}">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('deletegMaintenancegroupMaster')">Delete</button>
                                </form>  --}}
                                <br>
                                <?php $a = "class_name"."-".$maintenancg->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" id="classN" href="{{url('deleteg-classname').'/'.$a}}" onclick="confirmDelete2(event)">Delete</a>

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
                                    <th>Class Name</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    </div>
                </div>
                
            </div>
        </div>







        <div class="col-md-8 mb-4">
            <div class="breadcrumb">
                <h1 class="me-2">classes And Section Name</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card text-start">
                    <div class="card-body">
                    <div class="card-title mb-3 text-end">

                </div>
                        <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                            <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Class Name</th>
                                <th>Section Name</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i=1; ?>
                            @if(!empty($maintenances))
                            @foreach ($maintenances as $maintenanc)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $maintenanc->class_name }}</td>
                                <td>{{ $maintenanc->section_name }}</td>
                                <td>{{ $maintenanc->start_time }}</td>
                                <td>{{ $maintenanc->end_time }}</td>

                                <!-- <td> 
                                    <a class="btn btn-raised ripple btn-raised-warning m-1" href="{{ url('view-maintenance-head-master') .'/'.$maintenanc->id}}">Edit</a>
                                    <a class="btn btn-raised ripple btn-raised-danger m-1" href="{{ url('delete-maintenance-head-master') .'/'.$maintenanc->id}}">Delete</a>
                                </td> -->
                                <td class='d-flex'>
                              <a class="btn btn-primary m-1" href="{{ url('view-classes') .'/'.$maintenanc->id}}">Edit</a>
                                <!-- <form id="deletehmaintenanceheadmasterform" method="post" action="{{url('deleteh-maintenance-head-master')}}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="maintenanceheadmaster">
                                    <input type="hidden" name="delete_id" value="{{ $maintenanc->id }}">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('deletehMaintenanceHeadMaster')">Delete</button>
                                </form> -->
                                <br>
                                <?php $a = "classes"."-".$maintenanc->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" id="classes" href="{{url('deleteh-classes').'/'.$a}}" onclick="confirmDelete1(event)">Delete</a>
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
                                <th>Class Name</th>
                                <th>Section Name</th>
                                <th>Action</th>
                            </tr>
                            </tfoot>
                        </table>
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
function showTimePicker(inputId, timeFormat) {
    const timeInput = document.getElementById(inputId);
    
    // Create a new time input element
    const timePicker = document.createElement('input');
    timePicker.setAttribute('type', 'time');
    timePicker.setAttribute('class', 'form-control');
    timePicker.setAttribute('name', inputId); // Set the name attribute dynamically
    timePicker.value = timeInput.value;

    if (timeFormat) {
        timePicker.value = timeInput.value;
    }

    // Replace the text input with the time picker
    timeInput.parentNode.replaceChild(timePicker, timeInput);

    // Focus on the time picker element
    timePicker.focus();
}
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('#progress-form').addEventListener('submit', function(event) {
            var classNameInput = document.querySelector('input[name="class_name"]');
            var errorMessage = document.querySelector('#error-message');
            
            if (classNameInput.value.trim() === '') {
                errorMessage.textContent = 'Class Name is required.';
                event.preventDefault();
            } else {
                errorMessage.textContent = ''; // Clear any previous error messages
            }
        });
    });
</script>
<script>
    document.getElementById('myForm').addEventListener('submit', function(event) {
        var sectionNameInput = document.querySelector('input[name="section_name"]');
        var sectionNameError = document.getElementById('sectionNameError');
        
        if (sectionNameInput.value.trim() === '') {
            event.preventDefault(); // Prevent form submission
            sectionNameError.textContent = 'Section Name cannot be blank';
        }
    });
</script>
<script>
     function confirmDelete1(event) {
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
</script>


@endsection