@extends('backend.layouts.main')
@section('main-container')
<style>
  input {
    position: relative;
  }

  input[type="date"]::-webkit-calendar-picker-indicator {
    background-position: right;
    background-size: auto;
    cursor: pointer;
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    top: 7px;
    width: auto;
  }
</style>
<div class="main-content pt-4">
  <div class="modal fade" id="verifyModalContent" tabindex="-1" role="dialog" aria-labelledby="verifyModalContent" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">

          <h5 class="modal-title" id="verifyModalContent_title">
            Select Pick Up
          </h5>
          <button class="btn btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="progress-form" class="p-4 progress-form" action="{{url('scholarbusassign_post_pickup')}}" novalidate method="post">
            @csrf
            <input type="hidden" name="class_name" value="<?php echo (!empty($class_name)) ? $class_name : ''; ?>">
            <div class="form-check">
              <label class="form-check-label" for="disableFieldsCheckbox">Take student address </label>
              <input type="checkbox" class="form-check-input" name="studentaddcheck" id="studentaddcheck"><br>
              <span id="studentAddCheckMessage"></span>
              <span id="studentAddCheckMessage" class="text-danger"></span>
              <input type="hidden" id="latLngInput" name="latLngInput" placeholder="Latitude,Longitude">
              <input type="hidden" id="stu_bus_no" name="pickup_bus_no" placeholder="Latitude,Longitude">
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Select Pick Up Shedule Name :</label>
              <select class="form-control" onchange="pickup_shedule_name(this);" name="pick_shedule_name" id="pick_shedule_name">
                <?php
                echo '<option value=" -- Select Pick Up Shedule Name -- "> -- Select Pick Up Shedule Name -- </option>';
                foreach ($schedulemasters as $schedulemaster) {
                  echo '<option value="' . $schedulemaster->schedule_name . '"> ' . $schedulemaster->schedule_name . ' </option>';
                }
                ?>
              </select>
              <input class="form-control" id="student_id_select_p" type="hidden" name="student_id_select_p" />
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Pick Up Route :</label>
              <select class="form-control" name="pick_up_routes" onchange=pick_up_route(this) id="pick_up_routes">
                <?php
                echo '<option value=" -- Select Pick Up Route -- "> -- Select Pick Up Route -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Pick Up Area Name :</label>
              <select class="form-control" name="pickup_area_name" onclick="pickup_area_names(this)" id="pickup_area_name">
                <?php
                echo '<option value=" -- Select Pick Up Area Name -- "> -- Select Pick Up Area Name -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Pick Up Bus Stop Name :</label>
              <select class="form-control" name="pickup_bus_stop_names" onclick="pickup_bus_stop_name(this)" id="pickup_bus_stop_names">
                <?php
                echo '<option value=" -- Select Pick Up Bus Stop Name -- "> -- Select Pick Up Bus Stop Name -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Pick Up Bus No :</label>
              <select class="form-control" name="pickup_bus_no" id="pickup_bus_no">
                <?php
                echo '<option value=" -- Select Pick Up Bus No -- "> -- Select Pick Up Bus No -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">
            Close
          </button>
          <button class="btn btn-primary" type="submit">
            Send message
          </button>
        </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal fade" id="verifyModalContent_e" tabindex="-1" role="dialog" aria-labelledby="verifyModalContent_e" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">

          <h5 class="modal-title" id="verifyModalContent_title">
            Edit Pick Up
          </h5>
          <button class="btn btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <table>
            <tr>
              <td> Select Pick Up Shedule Name : <p id="Pickup_shedule_name"></p>
              </td>
              <td> | Pick Up Route : <p id="Pickup_uproute"></p>
              </td>
            </tr>
            <tr>
              <td> Pick Up Area Name : <p id="Pickup_areaname"></p>
              </td>
              <td> | Pick Up Bus Stop Name : <p id="Pickup_busstopname"></p>
              </td>
            </tr>
            <tr>
              <td> Pick Up Bus No : <p id="Pickup_busno"></p>
              </td>
            </tr>
          </table>
          <form id="progress-form" class="p-4 progress-form" action="{{url('scholarbusassign_post_pickup')}}" novalidate method="post">
            @csrf
            <input type="hidden" name="class_name" value="<?php echo (!empty($class_name)) ? $class_name : ''; ?>">
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Select Pick Up Shedule Name :</label>
              <select class="form-control" onchange="pickup_shedule_name(this);" name="pick_shedule_name" id="pick_shedule_name_e">
                <?php
                echo '<option value=" -- Select Pick Up Shedule Name -- "> -- Select Pick Up Shedule Name -- </option>';
                foreach ($schedulemasters as $schedulemaster) {
                  echo '<option value="' . $schedulemaster->schedule_name . '"> ' . $schedulemaster->schedule_name . ' </option>';
                }
                ?>
              </select>
              <input class="form-control" id="student_id_select_p_e" type="hidden" name="student_id_select_p" />
              <input class="form-control" id="type_to_create" type="hidden" value="edit" name="type_to_create" />
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Pick Up Route :</label>
              <select class="form-control" name="pick_up_routes" onchange=pick_up_route(this) id="pick_up_routes_e">
                <?php
                echo '<option value=" -- Select Pick Up Route -- "> -- Select Pick Up Route -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Pick Up Area Name :</label>
              <select class="form-control" name="pickup_area_name" onclick="pickup_area_names(this)" id="pickup_area_name_e">
                <?php
                echo '<option value=" -- Select Pick Up Area Name -- "> -- Select Pick Up Area Name -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Pick Up Bus Stop Name :</label>
              <select class="form-control" name="pickup_bus_stop_names" onclick="pickup_bus_stop_name(this)" id="pickup_bus_stop_names_e">
                <?php
                echo '<option value=" -- Select Pick Up Bus Stop Name -- "> -- Select Pick Up Bus Stop Name -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Pick Up Bus No :</label>
              <select class="form-control" name="pickup_bus_no" id="pickup_bus_no_e">
                <?php
                echo '<option value=" -- Select Pick Up Bus No -- "> -- Select Pick Up Bus No -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">
            Close
          </button>
          <button class="btn btn-primary" type="submit">
            Send message
          </button>
        </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal fade" id="verifyModalContent1" tabindex="-1" role="dialog" aria-labelledby="verifyModalContent1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="verifyModalContent_title">
            Select Drop
          </h5>
          <button class="btn btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="progress-form" class="p-4 progress-form" action="{{url('scholarbusassign_post_drop')}}" novalidate method="post">
            @csrf
            <input type="hidden" name="class_name" value="<?php echo (!empty($class_name)) ? $class_name : ''; ?>">
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Select Drop Shedule Name :</label>
              <select class="form-control" name="drop_shedule_name" onchange="drop_shedule_names(this);" id="drop_shedule_name">
                <?php
                echo '<option value=" -- Select Drop Shedule Name -- "> -- Select Drop Shedule Name -- </option>';
                foreach ($schedulemasters as $schedulemaster) {
                  echo '<option value="' . $schedulemaster->schedule_name . '"> ' . $schedulemaster->schedule_name . ' </option>';
                }
                ?>
              </select>
              <input class="form-control" id="student_id_select_d" type="hidden" name="student_id_select_d" />
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Drop Route :</label>
              <select class="form-control" onchange="drop_up_routes(this);" name="drop_up_route" id="drop_up_route">
                <?php
                echo '<option value=" -- Select Drop Route -- "> -- Select Drop Route -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Drop Area Name :</label>
              <select class="form-control" onchange="drop_area_names(this);" name="drop_area_name" id="drop_area_name">
                <?php
                echo '<option value=" -- Select Drop Area Name -- "> -- Select Drop Area Name -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Drop Bus Stop Name :</label>
              <select class="form-control" onchange="drop_bus_stop_names(this);" name="drop_bus_stop_name" id="drop_bus_stop_name">
                <?php
                echo '<option value=" -- Select Drop Bus Stop Name -- "> -- Select Drop Bus Stop Name -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
            <!-- <div class="form-group">
                      <label class="col-form-label" for="recipient-name-2"
                        >Drop Bus No :</label
                      >
                      <select class="form-control" name="shedule_name" id="shedule_name"> -->
            <?php
            // echo '<option value=" -- Select Drop Bus No -- "> -- Select Drop Bus No -- </option>';
            // foreach($schedulemasters as $schedulemaster){
            //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
            // }
            ?>
            <!-- </select>
                    </div> -->
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">
            Close
          </button>
          <button class="btn btn-primary" type="submit">
            Send message
          </button>
        </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="verifyModalContent1_e" tabindex="-1" role="dialog" aria-labelledby="verifyModalContent1_e" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="verifyModalContent_title">
            Edit Drop
          </h5>
          <button class="btn btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <table>
            <tr>
              <td> Select Drop Shedule Name : <p id="drop_shedule_name_e"></p>
              </td>
              <td> | Drop Route : <p id="drop_uproute_e"></p>
              </td>
            </tr>
            <tr>
              <td> Drop Area Name : <p id="drop_areaname_e"></p>
              </td>
              <td> | Drop Bus Stop Name : <p id="drop_busstopname_e"></p>
              </td>
            </tr>
            <tr>
              <td> Drop Bus No : <p id="drop_busno_e"></p>
              </td>
            </tr>
          </table>
          <form id="progress-form" class="p-4 progress-form" action="{{url('scholarbusassign_post_drop')}}" novalidate method="post">
            @csrf
            <input type="hidden" name="class_name" value="<?php echo (!empty($class_name)) ? $class_name : ''; ?>">
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Select Drop Shedule Name :</label>
              <select class="form-control" name="drop_shedule_name" onchange="drop_shedule_names(this);" id="drop_shedule_name_e">
                <?php
                echo '<option value=" -- Select Drop Shedule Name -- "> -- Select Drop Shedule Name -- </option>';
                foreach ($schedulemasters as $schedulemaster) {
                  echo '<option value="' . $schedulemaster->schedule_name . '"> ' . $schedulemaster->schedule_name . ' </option>';
                }
                ?>
              </select>
              <input class="form-control" id="student_id_select_d_e" type="hidden" name="student_id_select_d" />
              <input class="form-control" id="type_to_create" type="hidden" value="edit" name="type_to_create" />
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Drop Route :</label>
              <select class="form-control" onchange="drop_up_routes(this);" name="drop_up_route" id="drop_up_route_e">
                <?php
                echo '<option value=" -- Select Drop Route -- "> -- Select Drop Route -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Drop Area Name :</label>
              <select class="form-control" onchange="drop_area_names(this);" name="drop_area_name" id="drop_area_name_e">
                <?php
                echo '<option value=" -- Select Drop Area Name -- "> -- Select Drop Area Name -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label class="col-form-label" for="recipient-name-2">Drop Bus Stop Name :</label>
              <select class="form-control" onchange="drop_bus_stop_names(this);" name="drop_bus_stop_name" id="drop_bus_stop_name_e">
                <?php
                echo '<option value=" -- Select Drop Bus Stop Name -- "> -- Select Drop Bus Stop Name -- </option>';
                // foreach($schedulemasters as $schedulemaster){
                //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                // }
                ?>
              </select>
            </div>
            <!-- <div class="form-group">
                      <label class="col-form-label" for="recipient-name-2"
                        >Drop Bus No :</label
                      >
                      <select class="form-control" name="shedule_name" id="shedule_name"> -->
            <?php
            // echo '<option value=" -- Select Drop Bus No -- "> -- Select Drop Bus No -- </option>';
            // foreach($schedulemasters as $schedulemaster){
            //     echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
            // }
            ?>
            <!-- </select>
                    </div> -->
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">
            Close
          </button>
          <button class="btn btn-primary" type="submit">
            Send message
          </button>
        </div>
        </form>
      </div>
    </div>
  </div>

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <div class="form_section1_div">
    <div class="breadcrumb">
      <h1 class="me-2">Scholar Bus Assign</h1>
    </div>
    @if(!empty($bus_stafs))
    <!-- <form action="{{url('store-driver-conductor-master')}}" method="post">                   -->
    <input type="hidden" @if(!empty($bus_stafs)) @foreach($bus_stafs as $bus_staf) value=" {{ $bus_staf->id }}" @endforeach @else value="" @endif name="id">
    @else
    <!-- <form action="{{url('save-driver-conductor-master')}}" method="post"> -->
    @endif
    <form action="{{url('scholarbusassign_student')}}" method="GET">
      @csrf
      <input type="hidden" name="class_name" value="<?php echo (!empty($class_name)) ? $class_name : ''; ?>">
      <div class="row">
        <div class="col-md-3 form-group mb-3">
          <label for="picker2">Date</label>
          <input type="date" class="form-control" id="date" @if(!empty($bus_stafs)) @foreach($bus_stafs as $bus_staf) value=" {{ $bus_staf->license_lssue }}" @endforeach @else value="<?php echo (!empty($fdate)) ? $fdate : ''; ?>" @endif name="license_lssue" />
        </div>

        <div class="col-md-3 form-group mb-3">
          <label for="firstName1">Class Name</label>
          <select id="classname" class="form-control" name="classname" autocomplete="" required>
            @if(!empty($class_name))
            <option value=""> -- Please select -- </option>
            @foreach($classlist as $each)
            @if($class_name == $each->class_name)
            <option selected value="{{$each->class_name}}">{{$each->class_name}}</option>
            @else
            <option value="{{$each->class_name}}">{{$each->class_name}}</option>
            @endif
            @endforeach
            @else
            <option value="" selected> -- Please select -- </option>
            @foreach($classlist as $each)
            <option value="{{$each->class_name}}">{{$each->class_name}}</option>
            @endforeach
            @endif
          </select>
          <span class="classname_msg validation_err"></span>
        </div>

        {{-- <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Class Name</label>
                    <select id="class_name1" class="form-control" name="class_name" autocomplete="shipping address-level1" required>
                    @if(!empty($datas))
                        @foreach($datas as $bus_staf)
                            <?php //if( (!empty($class_name)) && $class_name == $bus_staf->class_name){
                            // echo '<option selected value="'.$bus_staf->class_name.'">'.$bus_staf->class_name.'</option>';
                            // }else{
                            // echo '<option value="'.$bus_staf->class_name.'">'.$bus_staf->class_name.'</option>';
                            // } 
                            ?>
                        
                         @endforeach
                        @else
                            <option value="">Please select option</option>
                        @endif
                    </select>
                </div> --}}

        <!-- <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Section Name</label>
                    <select id="section_name" class="form-control" name="section_name" autocomplete="shipping address-level1" required>
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            value=" {{ $bus_staf->call_no }}"
                            <option value=" {{ $bus_staf->call_no }}">{{ $bus_staf->call_no }}</option>
                        @endforeach
                        @else
                            <option value="">Please select option</option>
                        @endif          
                            <option value="option 1">option 1</option>
                    </select>
                </div> -->
        <div class="col-md-12">
          <button id="get_data" type="submit" class="btn btn-primary">Show</button>
          <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>

        </div>
      </div>
    </form>
  </div>
  <!-- end of main-content -->
  <br>
</div>

<div class="separator-breadcrumb border-top"></div>
<div class="row">
  <div class="col-md-12 mb-4">
    <div class="card text-start">
      <div class="card-body">
        <div class="card-title mb-3 text-end">
          <form method="POST" action="{{ route('export.csv') }}">
            @csrf
            <input type="hidden" name="class_name" value="<?php echo (!empty($class_name)) ? $class_name : ''; ?>">
            <input type="hidden" name="column_names[]" value="student_id_select_p">
            <input type="hidden" name="column_names[]" value="pick_shedule_name">
            <input type="hidden" name="column_names[]" value="pick_up_routes">
            <input type="hidden" name="column_names[]" value="pickup_area_name">
            <input type="hidden" name="column_names[]" value="pickup_bus_stop_names">
            <input type="hidden" name="column_names[]" value="pickup_bus_no">
            <input type="hidden" name="column_names[]" value="drop_shedule_name">
            <input type="hidden" name="column_names[]" value="drop_up_route">
            <input type="hidden" name="column_names[]" value="drop_area_name">
            <input type="hidden" name="column_names[]" value="drop_bus_stop_name">
            <input type="hidden" name="table_name" value="scholarbusassign">
            <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
          </form>
        </div>

        <div class="table-responsive">
          <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
            <thead>
              <tr>
                <th>Sr.</th>
                <th>Scholar Name</th>
                <th>Scholar Number</th>
                <th>Scholar Address</th>
                <th>Pick Bus Stop</th>
                <th>Pick up Schedule</th>
                <th>Pickup Bus No</th>
                <th>Pickup Route</th>
                <th>Drop Bus Stop</th>
                <th>Drop Schedule</th>
                <th>Drop Route</th>
                <th>Entry Date</th>
                <th>Stop Date</th>
                <th>Select PickUp</th>
                <th>Edit Pickup</th>
                <th>Select Drop</th>
                <th>Edit Drop</th>
              </tr>
            </thead>
            <tbody id="todo-list">
              <?php $id = 1; ?>
              @if(!empty($stor_data))
              <!-- var table = '<tr id="table_' + value.id + '"><td>' + id + '</td><td>' + value.student_name + '</td><td>' + value.form_number + '</td><td>' + 
  value.pickup_bus_stop_names + '</td><td>' + value.pick_shedule_name + '</td>
  <td>' + value.pickup_bus_no + '</td><td>' + value.pick_up_routes + '</td>
  <td>' + value.drop_bus_stop_name + '</td><td>' + value.drop_shedule_name + '</td>
  <td>' + value.drop_up_route + '</td><td>-</td><td>-</td><td> 
    <button onclick="form_p(' + value.id + ');" class="btn btn-raised ripple btn-raised-primary m-1" '+value.pickup_bool_s+'>Select PickUp</button> <button onclick="form_pe(' + value.id + ');" class="btn btn-raised ripple btn-raised-primary m-1" '+value.pickup_bool+'" >Edit Pickup</button> <button onclick="form_d(' + value.id + ');" class="btn btn-raised ripple btn-raised-warning m-1" '+ value.drop_bool_s +'>Select Drop</button> <button onclick="form_de(' + value.id + ');" class="btn btn-raised ripple btn-raised-warning m-1" '+value.drop_bool+'" >Edit Drop</button></tr>'; -->

              @foreach($stor_data as $stor_dat)
              <tr>
                <td>{{ $id }}</td>
                <td>{{ $stor_dat['student_name'] }}</td>
                <td>{{ $stor_dat['scholar_no'] }}</td>
                <td>{{ $stor_dat['student_add'] }}</td>
                <td>{{ $stor_dat['pickup_bus_stop_names'] }}</td>
                <td>{{ $stor_dat['pick_shedule_name'] }}</td>
                <td>{{ $stor_dat['pickup_bus_no'] }}</td>
                <td>{{ $stor_dat['pick_up_routes'] }}</td>
                <td>{{ $stor_dat['drop_bus_stop_name'] }}</td>
                <td>{{ $stor_dat['drop_shedule_name'] }}</td>
                <td>{{ $stor_dat['drop_up_route'] }}</td>
                <td> - </td>
                <td> - </td>
                <td><button onclick="form_p('{{ $stor_dat['id'] }}', '{{ $stor_dat['student_check_value'] }}','{{ $stor_dat['stu_driver'] }}');" class="btn btn-raised ripple btn-raised-primary m-1" <?php echo $stor_dat['pickup_bool_s']; ?>>Select PickUp</button>
                </td>
                <td><button onclick="form_pe('<?php echo $stor_dat['id']; ?>');" class="btn btn-raised ripple btn-raised-primary m-1" <?php echo $stor_dat['pickup_bool']; ?>>Edit Pickup</button>
                </td>
                <td><button onclick="form_d('<?php echo $stor_dat['id']; ?>');" class="btn btn-raised ripple btn-raised-warning m-1" <?php echo $stor_dat['drop_bool_s']; ?>>Select Drop</button>
                </td>
                <td><button onclick="form_de('<?php echo $stor_dat['id']; ?>');" class="btn btn-raised ripple btn-raised-warning m-1" <?php echo $stor_dat['drop_bool']; ?>>Edit Drop</button>
                </td>
              </tr>
              <?php $id++; ?>
              @endforeach

              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- </div> -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBa0_Zia458Lqzrwk7PzzpU7JIwJAkITdk&libraries=places"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script>
  const disableFieldsCheckbox = document.getElementById('studentaddcheck');
  const fieldsToDisable = document.querySelectorAll('.form-group select');
  const studentIdSelectP = document.getElementById('student_id_select_p');

  disableFieldsCheckbox.addEventListener('change', () => {
    const isDisabled = disableFieldsCheckbox.checked;

    fieldsToDisable.forEach(field => {
      field.disabled = isDisabled;
    });

    // Ensure student_id_select_p is never disabled
    studentIdSelectP.disabled = false;
  });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var latLngInput = document.getElementById('latLngInput');
  var stuBusNo = document.getElementById('stu_bus_no');
  var errorP = document.getElementById('studentAddCheckMessage');
  var form = document.getElementById('progress-form');

  form.addEventListener('submit', function (event) {
    if (stuBusNo.value === '') {
      event.preventDefault(); // Prevent form submission

      // Display error message in the <p> tag
      errorP.textContent = 'Please assign a bus to the student or ensure the present address is correct.';
      errorP.style.color = 'red';
    }
  });
});
</script>

<script>
  // get_data
  $(document).ready(function() {
    $("#get_data").click(function() {
      // $('tr:last-child').remove();
      $('#todo-list > tr > td').remove();
      var date = $("#date").val()
      var class_name = $("#class_name1").val()
      var section_name = $("#section_name").val()

      $.ajax({
        data: {
          date: date,
          class_name: class_name,
          section_name: section_name
        },
        url: "{{ url('scholarbusassign_student') }}",
        type: "POST",
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: "json",
        success: function(data) {
          var id = 1
          console.log(data);
          $.each(data, function(key, value) {
            var table = '<tr id="table_' + value.id + '"><td>' + id + '</td><td>' + value.student_name + '</td><td>' + value.form_number + '</td><td>' + value.pickup_bus_stop_names + '</td><td>' + value.pick_shedule_name + '</td><td>' + value.pickup_bus_no + '</td><td>' + value.pick_up_routes + '</td><td>' + value.drop_bus_stop_name + '</td><td>' + value.drop_shedule_name + '</td><td>' + value.drop_up_route + '</td><td>-</td><td>-</td><td> <button onclick="form_p(' + value.id + ');" class="btn btn-raised ripple btn-raised-primary m-1" ' + value.pickup_bool_s + '>Select PickUp</button> <button onclick="form_pe(' + value.id + ');" class="btn btn-raised ripple btn-raised-primary m-1" ' + value.pickup_bool + '" >Edit Pickup</button> <button onclick="form_d(' + value.id + ');" class="btn btn-raised ripple btn-raised-warning m-1" ' + value.drop_bool_s + '>Select Drop</button> <button onclick="form_de(' + value.id + ');" class="btn btn-raised ripple btn-raised-warning m-1" ' + value.drop_bool + '" >Edit Drop</button></tr>';
            jQuery('#todo-list').append(table);
            id++
          });
        }
      });
    });
  });


  function form_p(id, student_check_value, stu_driver) {
    console.log(id, student_check_value, stu_driver);

    $('#student_id_select_p').val(id);
    $('#studentaddcheck').val(student_check_value);
    $.ajax({
      data: {
        stu_driver: stu_driver
      },
      url: "{{ url('data_studata') }}",
      type: "get",
      header: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      dataType: "json",
      success: function(data) {
        console.log(data);
        $('#stu_bus_no').val(data);
      }
    })
    $('#verifyModalContent').modal('show');

    // Geocode the student address to obtain latitude and longitude
    geocodeAddress(student_check_value, function(lat, lng) {
      console.log('Latitude:', lat, 'Longitude:', lng);

      var latLngString = null; // Declare and initialize latLngString here

      if (lat !== undefined && lng !== undefined) {
        latLngString = lat + ',' + lng;
        if (latLngString === null) {
          $('#studentAddCheckMessage').text('Please assign a present address to this student.');
          return;
        } else {
          $('#studentAddCheckMessage').text(''); // Clear the message if there is a value
        }
        $('#latLngInput').val(latLngString);
        // You can now use lat and lng as needed
      }
    });

    $('#verifyModalContent').modal('show');
  }


  function geocodeAddress(address, callback) {
    var geocoder = new google.maps.Geocoder();
    geocoder.geocode({
      'address': address
    }, function(results, status) {
      if (status === 'OK') {
        var location = results[0].geometry.location;
        callback(location.lat(), location.lng());
      } else {
        console.error('Geocode was not successful for the following reason:', status);
        callback(null, null);
      }
    });
  }

  function form_pe(ele) {
    var number = ele;
    $('#student_id_select_p_e').val(ele)
    $.ajax({
      data: {
        number: number
      },
      url: "{{ url('data_foredit_pickup') }}",
      type: "get",
      header: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      dataType: "json",
      success: function(data) {
        console.log(data);
        $('#Pickup_shedule_name').text(data.pick_shedule_name);
        $('#Pickup_uproute').text(data.pick_up_routes);
        $('#Pickup_areaname').text(data.pickup_area_name);
        $('#Pickup_busstopname').text(data.pickup_bus_stop_names);
        $('#Pickup_busno').text(data.pickup_bus_no);
      }
    })
    $('#verifyModalContent_e').modal('show');
  }

  function form_d(ele) {
    $('#student_id_select_d').val(ele)
    $('#verifyModalContent1').modal('show');
  }

  function form_de(ele) {
    var number = ele;
    $('#student_id_select_d_e').val(ele)
    $.ajax({
      data: {
        number: number
      },
      url: "{{ url('data_foredit_pickup') }}",
      type: "get",
      header: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      dataType: "json",
      success: function(data) {
        console.log(data);
        $('#drop_shedule_name_e').text(data.drop_shedule_name);
        $('#drop_uproute_e').text(data.drop_up_route);
        $('#drop_areaname_e').text(data.drop_area_name);
        $('#drop_busstopname_e').text(data.drop_bus_stop_name);
        $('#drop_busno_e').text(data.pickup_bus_no);
      }
    })
    $('#verifyModalContent1_e').modal('show');
  }

  function pickup_shedule_name(val) {
    var shedule_name = val.value
    $('#pick_up_routes').find('option').remove();
    $('#pick_up_routes_e').find('option').remove();
    $.ajax({
      data: {
        shedule_name: shedule_name
      },
      url: "{{ url('scholarbusassign_student_shedule_name') }}",
      type: "get",
      header: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      dataType: "json",
      success: function(data) {
        $.each(data, function(key, value) {
          $('#pick_up_routes').append($("<option >" + value + "</option>").attr("value", value).text(value));
          $('#pick_up_routes_e').append($("<option >" + value + "</option>").attr("value", value).text(value));
        });
      }
    });
  }

  function pick_up_route(val) {
    var pickup_area_name = val.value
    $('#pickup_area_name').find('option').remove();
    $('#pickup_area_name_e').find('option').remove();
    $.ajax({
      data: {
        pickup_area_name: pickup_area_name
      },
      url: "{{ url('scholarbusassign_student_pick_up_routes') }}",
      type: "get",
      header: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      dataType: "json",
      success: function(data) {
        $.each(data, function(key, value) {
          $('#pickup_area_name').append($("<option >" + value + "</option>").attr("value", value).text(value));
          $('#pickup_area_name_e').append($("<option >" + value + "</option>").attr("value", value).text(value));
        });
      }
    });
  }

  function pickup_area_names(val) {
    var bus_stop_name = val.value
    $('#pickup_bus_stop_names').find('option').remove();
    $('#pickup_bus_stop_names_e').find('option').remove();

    $.ajax({
      data: {
        bus_stop_name: bus_stop_name
      },
      url: "{{ url('scholarbusassign_student_bus_stop_name') }}",
      type: "get",
      header: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      dataType: "json",
      success: function(data) {
        $.each(data, function(key, value) {
          $('#pickup_bus_stop_names').append($("<option >" + value + "</option>").attr("value", value).text(value));
          $('#pickup_bus_stop_names_e').append($("<option >" + value + "</option>").attr("value", value).text(value));
        });
      }
    });
  }

  function pickup_bus_stop_name(val) {
    // console.log("hyy");
    var shedule_name1 = $("#pick_shedule_name").val();
    // console.log(shedule_name);
    // var pick_up_routes = $("#pick_up_routes").val();
    if (shedule_name1 == " -- Select Pick Up Shedule Name -- ") {
      var shedule_name = $("#pick_shedule_name_e").val();
      var pick_up_routes = $("#pick_up_routes_e").val();
    } else {
      var shedule_name = $("#pick_shedule_name").val();
      var pick_up_routes = $("#pick_up_routes").val();
    }
    $('#pickup_bus_no').find('option').remove();
    $('#pickup_bus_no_e').find('option').remove();

    $.ajax({
      data: {
        shedule_name: shedule_name,
        pick_up_routes: pick_up_routes
      },
      url: "{{ url('scholarbusassign_student_bus_no') }}",
      type: "get",
      header: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      dataType: "json",
      success: function(data) {
        $.each(data, function(key, value) {
          console.log(value)
          $('#pickup_bus_no').append($("<option >" + value + "</option>").attr("value", value).text(value));
          $('#pickup_bus_no_e').append($("<option >" + value + "</option>").attr("value", value).text(value));
        });
      }
    });
  }

  // for drop

  function drop_shedule_names(val) {
    var shedule_name = val.value
    console.log(shedule_name)
    $('#drop_up_route').find('option').remove();
    $('#drop_up_route_e').find('option').remove();

    $.ajax({
      data: {
        shedule_name: shedule_name
      },
      url: "{{ url('scholarbusassign_student_shedule_name_drop') }}",
      type: "get",
      header: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      dataType: "json",
      success: function(data) {
        $.each(data, function(key, value) {
          $('#drop_up_route').append($("<option >" + value + "</option>").attr("value", value).text(value));
          $('#drop_up_route_e').append($("<option >" + value + "</option>").attr("value", value).text(value));
        });
      }
    });
  }

  function drop_up_routes(val) {
    var pickup_area_name = val.value
    $('#drop_area_name').find('option').remove();
    $('#drop_area_name_e').find('option').remove();

    $.ajax({
      data: {
        pickup_area_name: pickup_area_name
      },
      url: "{{ url('scholarbusassign_student_drop_routes') }}",
      type: "get",
      header: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      dataType: "json",
      success: function(data) {
        $.each(data, function(key, value) {
          $('#drop_area_name').append($("<option >" + value + "</option>").attr("value", value).text(value));
          $('#drop_area_name_e').append($("<option >" + value + "</option>").attr("value", value).text(value));
        });
      }
    });
  }

  function drop_area_names(val) {
    var bus_stop_name = val.value
    $('#drop_bus_stop_name').find('option').remove();
    $('#drop_bus_stop_name_e').find('option').remove();

    $.ajax({
      data: {
        bus_stop_name: bus_stop_name
      },
      url: "{{ url('scholarbusassign_student_bus_stop_name_drop') }}",
      type: "get",
      header: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      dataType: "json",
      success: function(data) {
        $.each(data, function(key, value) {
          $('#drop_bus_stop_name').append($("<option >" + value + "</option>").attr("value", value).text(value));
          $('#drop_bus_stop_name_e').append($("<option >" + value + "</option>").attr("value", value).text(value));
        });
      }
    });
  }

  // $.("#get_data").click(function(){
  //     alert("Hello !")

  // }); 
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function() {
    // Function to handle the date input
    $("#picker2").on("input", function() {
      // Do something with the date value
      const dateValue = $(this).val();
      console.log("Selected Date:", dateValue);
    });
  });

  document.addEventListener('DOMContentLoaded', function() {
    $("#reset").on("click", function () {                
        $("#date").val("");
        $("#classname").val("");
           
    });

})

</script>
@endsection