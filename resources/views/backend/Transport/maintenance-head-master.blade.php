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
                    <h1 class="me-2">Maintenance Group Master</h1>
                </div>
                <div class="separator-breadcrumb border-top"></div>
                    @if(!empty($maintenance_groups))
                        <form id="progress-form" class="p-4 progress-form" action="{{url('storeg-maintenance-group-master')}}"  method="post">
                        <input type="hidden"
                        required 
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
                        <form id="progress-form" class="p-4 progress-form" action="{{url('storeg-maintenance-group-master')}}" method="post">
                    @endif
                    @csrf
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <label for="lastName1">Maintenance Head Name:</label>
                            <input required type="text" class="form-control uperletter" placeholder="Maintenance Group Name" 
                            name="maintenance_group_name" id="maintenance_group_name" <?php 
                            if(!empty($maintenance_groups)){ 
                                foreach($maintenance_groups as $maintenance_group){
                                    echo 'value="'.$maintenance_group->maintenance_group_name.'"'; 
                                }
                            } 
                            else{
                                echo 'value=""';
                            } 
                            ?> >
                        </div>
                        <div class="col-md-42">
                            <button class="btn btn-primary">Submit</button>
                            <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>

                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-8 mb-4">
            <div class="form_section1_div">
                <div class="breadcrumb">
                    <h1 class="me-2">Maintenance Head Master</h1>
                </div>
                <div class="separator-breadcrumb border-top"></div>
                    @if(!empty($maintenance_s))
                        <form id="progress-form" class="p-4 progress-form uperletter" action="{{url('maintenance-head-master')}}" method="post">
                        <input required type="hidden" 
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
                        <form id="progress-form" class="p-4 progress-form" action="{{url('maintenance-head-master')}}"  novalidate method="post">
                    @endif
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="lastName1">Maintenance Group Name:</label>
                            <select name="maintenance_group_name" class="form-control" id="maintenance_group_name1">
                                <?php 
                                    if(!empty($select_main)){
                                        foreach($select_main as $select){
                                            if(!empty($maintenance_s)){ 
                                                foreach($maintenance_s as $maintenance){
                                                    if($maintenance->maintenance_group_name == $select->maintenance_group_name){
                                                        echo '<option value="'.$maintenance->maintenance_group_name.'" selected>'.$select->maintenance_group_name.'</option>';
                                                    }else {
                                                        echo '<option value="'.$select->maintenance_group_name.'">'.$select->maintenance_group_name.'</option>';
                                                    }
                                                }
                                            } else {
                                                echo '<option value="'.$select->maintenance_group_name.'">'.$select->maintenance_group_name.'</option>';
                                            }
                                        }
                                    }
                                ?>

                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="lastName1">Maintenance Head Name:</label>
                            <input type="text" class="form-control uperletter" placeholder="Maintenance Head Name" 
                            name="maintenance_head_name" id="maintenance_head" <?php 
                            if(!empty($maintenance_s)){ 
                                foreach($maintenance_s as $maintenance){
                                    echo 'value="'.$maintenance->maintenance_head_name.'"'; 
                                }
                            } 
                            else{
                                echo 'value=""';
                            } 
                            ?> >
                        </div>
                        <div class="col-md-42">
                            <button class="btn btn-primary">Submit</button>
                            {{-- <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button> --}}

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
                <h1 class="me-2">Maintenance Group Master</h1>
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
                                    <th>Maintenance Group Name</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $i=1;  ?>
                                @if(!empty($maintenancegs))
                                @foreach ($maintenancegs as $maintenancg)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $maintenancg->maintenance_group_name }}</td>
                                    <!-- <td> 
                                        <a class="btn btn-raised ripple btn-raised-warning m-1" href="{{ url('editg-maintenance-group-master') .'/'.$maintenancg->id}}">Edit</a>
                                        <a class="btn btn-raised ripple btn-raised-danger m-1" href="{{ url('deleteg-maintenance-group-master') .'/'.$maintenancg->id}}">Delete</a>
                                    </td> -->
                                    <td class='d-flex'>
                              <a class="btn btn-primary m-1" href="{{ url('editg-maintenance-group-master') .'/'.$maintenancg->id}}">Edit</a>
                                    <!-- <form id="deletegmaintenancegroupmasterform" method="post" action="{{ url('deleteg-maintenance-group-master') }}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="maintenancegroupmaster">
                                    <input type="hidden" name="delete_id" value="{{ $maintenancg->id }}">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('deletegMaintenancegroupMaster')">Delete</button>
                                </form> -->
                                <br>
                                <?php $a = "maintenancegroupmaster"."-".$maintenancg->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('deleteg-maintenance-group-master').'/'.$a}}" onclick="confirmDelete2(event)">Delete</a>

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
                                    <th>Maintenance Group Name</th>
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
                <h1 class="me-2">Maintenance Head Master</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card text-start">
                    <div class="card-body">
                    <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="maintenance_group_name">
                      <input type="hidden" name="column_names[]" value="maintenance_head_name">
                      <input type="hidden" name="table_name" value="maintenanceheadmaster">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div>
                        <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                            <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Maintenance Group Name</th>
                                <th>Maintenance Group Head</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i=1;  ?>
                            @if(!empty($maintenances))
                            @foreach ($maintenances as $maintenanc)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $maintenanc->maintenance_group_name }}</td>
                                <td>{{ $maintenanc->maintenance_head_name }}</td>
                                <!-- <td> 
                                    <a class="btn btn-raised ripple btn-raised-warning m-1" href="{{ url('view-maintenance-head-master') .'/'.$maintenanc->id}}">Edit</a>
                                    <a class="btn btn-raised ripple btn-raised-danger m-1" href="{{ url('delete-maintenance-head-master') .'/'.$maintenanc->id}}">Delete</a>
                                </td> -->
                                <td class='d-flex'>
                              <a class="btn btn-primary m-1" href="{{ url('view-maintenance-head-master') .'/'.$maintenanc->id}}">Edit</a>
                                <!-- <form id="deletehmaintenanceheadmasterform" method="post" action="{{url('deleteh-maintenance-head-master')}}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="maintenanceheadmaster">
                                    <input type="hidden" name="delete_id" value="{{ $maintenanc->id }}">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('deletehMaintenanceHeadMaster')">Delete</button>
                                </form> -->
                                <br>
                                <?php $a = "maintenanceheadmaster"."-".$maintenanc->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-driver-conductor-master').'/'.$a}}" onclick="confirmDelete1(event)">Delete</a>
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
                                <th>Maintenance Group Name</th>
                                <th>Maintenance Head Name</th>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('maintenance_group_name');
        for (var i = 0; i < select.options.length; i++) {
            select.options[i].innerText = capitalizeFirstLetter(select.options[i].innerText);
        }
    });
  
    function capitalizeFirstLetter(str) {
        var words = str.toLowerCase().split(' ');
        for (var i = 0; i < words.length; i++) {
            words[i] = words[i].charAt(0).toUpperCase() + words[i].substring(1);
        }
        return words.join(' ');
    }

    document.addEventListener('DOMContentLoaded', function() {
    $("#reset").on("click", function () {                
        $("#maintenance_group_name").val("");
        $("#maintenance_group_name1").val("");
        $("#maintenance_head").val("");
            
    });

})

  </script>
  

@endsection