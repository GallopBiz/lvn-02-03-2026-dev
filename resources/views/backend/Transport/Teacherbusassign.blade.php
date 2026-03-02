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
        <div class="modal fade" id="verifyModalContent" tabindex="-1" role="dialog" aria-labelledby="verifyModalContent" aria-hidden="true" >
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                
                  <h5 class="modal-title" id="verifyModalContent_title">
                    Select Pick Up
                  </h5>
                  <button
                    class="btn btn-close"
                    type="button"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                  ></button>
                </div>
                <div class="modal-body">
                  <form id="progress-form" class="p-4 progress-form" action="{{url('teacherbusassign_post_pickup')}}"  novalidate method="post">
                    @csrf
                    <div class="form-group">
                      <label class="col-form-label" for="recipient-name-2"
                        >Select Pick Up Shedule Name :</label
                      >
                      <select class="form-control" onchange="pickup_shedule_name(this);" name="pick_shedule_name" id="pick_shedule_name">
                        <?php 
                        echo '<option value=" -- Select Pick Up Shedule Name -- "> -- Select Pick Up Shedule Name -- </option>';
                            foreach($schedulemasters as $schedulemaster){
                                echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                            }
                        ?>
                      </select>
                      <input
                        class="form-control"
                        id="student_id_select_p"
                        type="hidden"
                        name="student_id_select_p"
                      />
                    </div>
                    <div class="form-group">
                      <label class="col-form-label" for="recipient-name-2"
                        >Pick Up Route :</label
                      >
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
                      <label class="col-form-label" for="recipient-name-2"
                        >Pick Up Area Name :</label
                      >
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
                      <label class="col-form-label" for="recipient-name-2"
                        >Pick Up Bus Stop Name :</label
                      >
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
                      <label class="col-form-label" for="recipient-name-2"
                        >Pick Up Bus No :</label
                      >
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
                  <button
                    class="btn btn-secondary"
                    type="button"
                    data-bs-dismiss="modal"
                  >
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
          <div class="modal fade" id="verifyModalContent_e" tabindex="-1" role="dialog" aria-labelledby="verifyModalContent_e" aria-hidden="true" >
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                
                  <h5 class="modal-title" id="verifyModalContent_title">
                    Edit Pick Up
                  </h5>
                  <button
                    class="btn btn-close"
                    type="button"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                  ></button>
                </div>
                <div class="modal-body">
                  <table>
                    <tr>
                      <td> Select Pick Up Shedule Name : <p id="Pickup_shedule_name"></p></td>
                      <td> | Pick Up Route : <p id="Pickup_uproute"></p></td>
                    </tr>
                    <tr>
                      <td> Pick Up Area Name : <p id="Pickup_areaname"></p></td>
                      <td> | Pick Up Bus Stop Name : <p id="Pickup_busstopname"></p></td>
                    </tr>
                    <tr>
                      <td> Pick Up Bus No : <p id="Pickup_busno"></p></td>
                    </tr>
                  </table>
                  <form id="progress-form" class="p-4 progress-form" action="{{url('teacherbusassign_post_pickup')}}"  novalidate method="post">
                    @csrf
                    <div class="form-group">
                      <label class="col-form-label" for="recipient-name-2"
                        >Select Pick Up Shedule Name :</label
                      >
                      <select class="form-control" onchange="pickup_shedule_name(this);" name="pick_shedule_name" id="pick_shedule_name_e">
                        <?php 
                        echo '<option value=" -- Select Pick Up Shedule Name -- "> -- Select Pick Up Shedule Name -- </option>';
                            foreach($schedulemasters as $schedulemaster){
                                echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                            }
                        ?>
                      </select>
                      <input
                        class="form-control"
                        id="student_id_select_p_e"
                        type="hidden"
                        name="student_id_select_p"
                      />
                      <input
                        class="form-control"
                        id="type_to_create"
                        type="hidden"
                        value="edit"
                        name="type_to_create"
                      />
                    </div>
                    <div class="form-group">
                      <label class="col-form-label" for="recipient-name-2"
                        >Pick Up Route :</label
                      >
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
                      <label class="col-form-label" for="recipient-name-2"
                        >Pick Up Area Name :</label
                      >
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
                      <label class="col-form-label" for="recipient-name-2"
                        >Pick Up Bus Stop Name :</label
                      >
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
                      <label class="col-form-label" for="recipient-name-2"
                        >Pick Up Bus No :</label
                      >
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
                  <button
                    class="btn btn-secondary"
                    type="button"
                    data-bs-dismiss="modal"
                  >
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
          <div class="modal fade" id="verifyModalContent1" tabindex="-1" role="dialog" aria-labelledby="verifyModalContent1" aria-hidden="true" >
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="verifyModalContent_title">
                    Select Drop
                  </h5>
                  <button
                    class="btn btn-close"
                    type="button"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                  ></button>
                </div>
                <div class="modal-body">
                <form id="progress-form" class="p-4 progress-form" action="{{url('teacherbusassign_post_drop')}}"  novalidate method="post">
                    @csrf
                    <div class="form-group">
                      <label class="col-form-label" for="recipient-name-2"
                        >Select Drop Shedule Name :</label
                      >
                      <select class="form-control" name="drop_shedule_name" onchange="drop_shedule_names(this);" id="drop_shedule_name">
                        <?php 
                        echo '<option value=" -- Select Drop Shedule Name -- "> -- Select Drop Shedule Name -- </option>';
                            foreach($schedulemasters as $schedulemaster){
                                echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                            }
                        ?>
                      </select>
                      <input
                        class="form-control"
                        id="student_id_select_d"
                        type="hidden"
                        name="student_id_select_d"
                      />
                    </div>
                    <div class="form-group">
                      <label class="col-form-label" for="recipient-name-2"
                        >Drop Route :</label
                      >
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
                      <label class="col-form-label" for="recipient-name-2"
                        >Drop Area Name :</label
                      >
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
                      <label class="col-form-label" for="recipient-name-2"
                        >Drop Bus Stop Name :</label
                      >
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
                  <button
                    class="btn btn-secondary"
                    type="button"
                    data-bs-dismiss="modal"
                  >
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

          <div class="modal fade" id="verifyModalContent1_e" tabindex="-1" role="dialog" aria-labelledby="verifyModalContent1_e" aria-hidden="true" >
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="verifyModalContent_title">
                    Edit Drop
                  </h5>
                  <button
                    class="btn btn-close"
                    type="button"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                  ></button>
                </div>
                <div class="modal-body">
                <table>
                    <tr>
                      <td> Select Drop Shedule Name : <p id="drop_shedule_name_e"></p></td>
                      <td> | Drop Route : <p id="drop_uproute_e"></p></td>
                    </tr>
                    <tr>
                      <td> Drop Area Name : <p id="drop_areaname_e"></p></td>
                      <td> | Drop Bus Stop Name : <p id="drop_busstopname_e"></p></td>
                    </tr>
                    <tr>
                      <td> Drop Bus No : <p id="drop_busno_e"></p></td>
                    </tr>
                  </table>
                <form id="progress-form" class="p-4 progress-form" action="{{url('teacherbusassign_post_drop')}}"  novalidate method="post">
                    @csrf
                    <div class="form-group">
                      <label class="col-form-label" for="recipient-name-2"
                        >Select Drop Shedule Name :</label
                      >
                      <select class="form-control" name="drop_shedule_name" onchange="drop_shedule_names(this);" id="drop_shedule_name_e">
                        <?php 
                        echo '<option value=" -- Select Drop Shedule Name -- "> -- Select Drop Shedule Name -- </option>';
                            foreach($schedulemasters as $schedulemaster){
                                echo '<option value="'.$schedulemaster->schedule_name.'"> '.$schedulemaster->schedule_name.' </option>';
                            }
                        ?>
                      </select>
                      <input
                        class="form-control"
                        id="student_id_select_d_e"
                        type="hidden"
                        name="student_id_select_d"
                      />
                      <input
                        class="form-control"
                        id="type_to_create"
                        type="hidden"
                        value="edit"
                        name="type_to_create"
                      />
                    </div>
                    <div class="form-group">
                      <label class="col-form-label" for="recipient-name-2"
                        >Drop Route :</label
                      >
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
                      <label class="col-form-label" for="recipient-name-2"
                        >Drop Area Name :</label
                      >
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
                      <label class="col-form-label" for="recipient-name-2"
                        >Drop Bus Stop Name :</label
                      >
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
                  <button
                    class="btn btn-secondary"
                    type="button"
                    data-bs-dismiss="modal"
                  >
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
            <h1 class="me-2">Teacher Bus Assign</h1>
        </div>
                @if(!empty($bus_stafs))
                  <!-- <form action="{{url('store-driver-conductor-master')}}" method="post">                   -->
                  <input type="hidden" 
                    @if(!empty($bus_stafs))
                      @foreach($bus_stafs as $bus_staf)
                        value=" {{ $bus_staf->id }}"
                      @endforeach
                    @else
                      value=""
                    @endif
                    name="id"
                  >
                @else
                    <!-- <form action="{{url('save-driver-conductor-master')}}" method="post"> -->
                @endif
            @csrf
            <div class="row">
                <div class="col-md-3 form-group mb-3">
                    <label for="picker2">Date</label>
                    <input type="date" class="form-control" id="date" 
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            value=" {{ $bus_staf->license_lssue }}"
                        @endforeach
                    @else
                        value=""
                    @endif
                    name="license_lssue" />
                </div>
               
                <div class="col-md-3 form-group mb-3">
                  <label for="firstName1">Class Name</label>
                  <select id="classname" class="form-control" name="classname" autocomplete="" required>
                     <option value="" disabled selected>Please select</option>
                     @foreach($classlist as $each)
                     <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                     @endforeach
                      {{-- @foreach(config('global.class_name') as $each)
                     <option value="{{$each}}">{{$each}}</option>
                     @endforeach  --}}
                  </select>
                  <span class="classname_msg validation_err"></span>
                </div> 
                
                {{-- <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Class Name</label>
                    <select id="class_name" class="form-control" name="class_name" autocomplete="shipping address-level1" required>
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
                </div> --}}

                <div class="col-md-3 form-group mb-3">
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
                </div>
                <div class="col-md-12">
                    <button id="get_data" onchange="get_data();" class="btn btn-primary">Show</button>
                    <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>


                </div>
            </div>
        <!-- </form> -->
    </div>
    <!-- end of main-content -->
    <br>
    </div>
        <div class="separator-breadcrumb border-top"></div>

        <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
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
                      <input type="hidden" name="table_name" value="teacherbusassign">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div>
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Teacher Name</th>
                                <th>Teacher Number</th>
                                <th>Pick Bus Stop</th>
                                <th>Pick up Schedule</th>
                                <th>Pickup Bus No</th>
                                <th>Pickup Route</th>
                                <th>Drop Bus Stop</th>
                                <th>Drop Schedule</th>
                                <th>Drop Route</th>
                                <th>Entry Date</th>
                                <th>Stop Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="todo-list">
                        </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
<!-- </div> -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<script>
    // get_data
    $(document).ready(function(){
        $("#get_data").click(function(){
            var date = $("#date").val()
            var class_name = $("#class_name").val()
            var section_name = $("#section_name").val()

            $.ajax({
                data : {date:date,class_name:class_name,section_name:section_name},
                url : "{{ url('teacherbusassign_student') }}",
                type : "get",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                dataType : "json",
                success: function(data){
                    var id = 1
                    console.log(data);
                    $.each( data ,function(key , value) {
                    var nameArray = ["Rahul", "Tina", "Ajay", "Atul","Rahul", "Tina", "Ajay", "Atul","Rahul", "Tina", "Ajay", "Atul","Rahul", "Tina"]; // Example name array
                    var TeacherName = nameArray[id - 1]; // Get the name from the array based on the index
                    var table = '<tr id="table_' + value.id + '"><td>' + id + '</td><td>' + TeacherName + '</td><td>' + value.id + '</td><td>' + value.pickup_bus_stop_names + '</td><td>' + value.pick_shedule_name + '</td><td>' + value.pickup_bus_no + '</td><td>' + value.pick_up_routes + '</td><td>' + value.drop_bus_stop_name + '</td><td>' + value.drop_shedule_name + '</td><td>' + value.drop_up_route + '</td><td>-</td><td>-</td><td> <button onclick="form_p(' + value.id + ');" class="btn btn-raised ripple btn-raised-primary m-1" '+value.pickup_bool_s+'>Select PickUp</button> <button onclick="form_pe(' + value.id + ');" class="btn btn-raised ripple btn-raised-primary m-1" '+value.pickup_bool+'" >Edit Pickup</button> <button onclick="form_d(' + value.id + ');" class="btn btn-raised ripple btn-raised-warning m-1" '+ value.drop_bool_s +'>Select Drop</button> <button onclick="form_de(' + value.id + ');" class="btn btn-raised ripple btn-raised-warning m-1" '+value.drop_bool+'" >Edit Drop</button></tr>';
                    jQuery('#todo-list').append(table);
                    id++
                    });
                }
            });
        });
    });

    function form_p(ele){
        console.log(ele);
        $('#student_id_select_p').val(ele)
        $('#verifyModalContent').modal('show');
        
    }

    function form_pe(ele){
      var number = ele;
      $('#student_id_select_p_e').val(ele)
      $.ajax({
        data : {number:number},
        url : "{{ url('data_foredit_pickup') }}",
        type : "get",
        header : {'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')},
        dataType : "json",
        success: function(data){
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

    function form_d(ele){
        $('#student_id_select_d').val(ele)
        $('#verifyModalContent1').modal('show');
    }

    function form_de(ele){
      var number = ele;
      $('#student_id_select_d_e').val(ele)
      $.ajax({
        data : {number:number},
        url : "{{ url('data_foredit_pickup') }}",
        type : "get",
        header : {'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')},
        dataType : "json",
        success: function(data){
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

    function pickup_shedule_name(val){
      var shedule_name = val.value
      $('#pick_up_routes').find('option').remove();
      $('#pick_up_routes_e').find('option').remove();
      $.ajax({
        data : {shedule_name:shedule_name},
        url : "{{ url('teacherbusassign_student_shedule_name') }}",
        type : "get",
        header : {'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')},
        dataType : "json",
        success: function(data){
          $.each(data ,function(key,value){
            $('#pick_up_routes').append($("<option >"+ value+"</option>").attr("value", value).text(value)); 
            $('#pick_up_routes_e').append($("<option >"+ value+"</option>").attr("value", value).text(value));
          });
        }
      });
    }

    function pick_up_route(val){
      var pickup_area_name = val.value
      $('#pickup_area_name').find('option').remove();
      $('#pickup_area_name_e').find('option').remove();
      $.ajax({
        data : {pickup_area_name:pickup_area_name},
        url : "{{ url('scholarbusassign_student_pick_up_routes') }}",
        type : "get",
        header : {'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')},
        dataType : "json",
        success: function(data){
          $.each(data ,function(key,value){
            $('#pickup_area_name').append($("<option >"+ value+"</option>").attr("value", value).text(value));
            $('#pickup_area_name_e').append($("<option >"+ value+"</option>").attr("value", value).text(value)); 
          });
        }
      });
    }

    function pickup_area_names(val){
      var bus_stop_name = val.value
      $('#pickup_bus_stop_names').find('option').remove();
      $('#pickup_bus_stop_names_e').find('option').remove();

      $.ajax({
        data : {bus_stop_name:bus_stop_name},
        url : "{{ url('teacherbusassign_student_bus_stop_name') }}",
        type : "get",
        header : {'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')},
        dataType : "json",
        success: function(data){
          $.each(data ,function(key,value){
            $('#pickup_bus_stop_names').append($("<option >"+ value+"</option>").attr("value", value).text(value));
            $('#pickup_bus_stop_names_e').append($("<option >"+ value+"</option>").attr("value", value).text(value)); 
          });
        }
      });
    }

    function pickup_bus_stop_name(val){
      var shedule_name1 = $("#pick_shedule_name").val();
      // var pick_up_routes = $("#pick_up_routes").val();
      if(shedule_name1 == " -- Select Pick Up Shedule Name -- "){
        var shedule_name = $("#pick_shedule_name_e").val();
        var pick_up_routes = $("#pick_up_routes_e").val();
      } else {
        var shedule_name = $("#pick_shedule_name").val();
        var pick_up_routes = $("#pick_up_routes").val();
      }
      $('#pickup_bus_no').find('option').remove();
      $('#pickup_bus_no_e').find('option').remove();
      
      $.ajax({
        data : {shedule_name:shedule_name,pick_up_routes:pick_up_routes},
        url : "{{ url('teacherbusassign_student_bus_no') }}",
        type : "get",
        header : {'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')},
        dataType : "json",
        success: function(data){
          $.each(data ,function(key,value){
            console.log(value)
            $('#pickup_bus_no').append($("<option >"+ value+"</option>").attr("value", value).text(value)); 
            $('#pickup_bus_no_e').append($("<option >"+ value+"</option>").attr("value", value).text(value)); 
          });
        }
      });
    }

    // for drop

    function drop_shedule_names(val){
      var shedule_name = val.value
      console.log(shedule_name)
      $('#drop_up_route').find('option').remove();
      $('#drop_up_route_e').find('option').remove();

      $.ajax({
        data : {shedule_name:shedule_name},
        url : "{{ url('teacherbusassign_student_shedule_name_drop') }}",
        type : "get",
        header : {'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')},
        dataType : "json",
        success: function(data){
          $.each(data ,function(key,value){
            $('#drop_up_route').append($("<option >"+ value+"</option>").attr("value", value).text(value)); 
            $('#drop_up_route_e').append($("<option >"+ value+"</option>").attr("value", value).text(value)); 
          });
        }
      });
    }

    function drop_up_routes(val){
      var pickup_area_name = val.value
      $('#drop_area_name').find('option').remove();
      $('#drop_area_name_e').find('option').remove();

      $.ajax({
        data : {pickup_area_name:pickup_area_name},
        url : "{{ url('teacherbusassign_student_drop_routes') }}",
        type : "get",
        header : {'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')},
        dataType : "json",
        success: function(data){
          $.each(data ,function(key,value){
            $('#drop_area_name').append($("<option >"+ value+"</option>").attr("value", value).text(value)); 
            $('#drop_area_name_e').append($("<option >"+ value+"</option>").attr("value", value).text(value)); 
          });
        }
      });
    }

    function drop_area_names(val){
      var bus_stop_name = val.value
      $('#drop_bus_stop_name').find('option').remove();
      $('#drop_bus_stop_name_e').find('option').remove();

      $.ajax({
        data : {bus_stop_name:bus_stop_name},
        url : "{{ url('teacherbusassign_student_bus_stop_name_drop') }}",
        type : "get",
        header : {'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')},
        dataType : "json",
        success: function(data){
          $.each(data ,function(key,value){
            $('#drop_bus_stop_name').append($("<option >"+ value+"</option>").attr("value", value).text(value)); 
            $('#drop_bus_stop_name_e').append($("<option >"+ value+"</option>").attr("value", value).text(value)); 
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
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
      var select = document.getElementById('section_name');
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
        $("#date").val("");
        $("#classname").val("");
        $("#section_name").val("");
        $("#schedule_time_from").val("");
        $("#schedule_time_to").val("");
        $("#schedule_print_option").val("");
        $("#schedule_point").val("");
            
    });

})

</script>

@endsection