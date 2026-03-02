@extends('backend.layouts.main')
@section('main-container')
<style>
.uperletter{
    text-transform: capitalize;
  } 
  </style>
<div class="main-content pt-4">
<meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="row">
        <div class="col-md-4 form-group mb-3">
            <div class="form_section1_div">
                <div class="breadcrumb">
                    <h1 class="me-2">Route Master</h1>
                </div>
				<form action="{{ route('route-master.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <label for="csv_file">Upload CSV File:</label>
        <input type="file" name="file" class="form-control" required>

        @if ($errors->any())
            <div class="alert alert-danger mt-2">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    <button type="submit" class="btn btn-raised ripple btn-raised-success m-1">Import CSV</button>
</form>

                <div class="separator-breadcrumb border-top"></div>
                    @if(!empty($route_s))
                        <form id="progress-form" class="p-4 progress-form" action="{{url('route-master')}}"  novalidate method="post">
                        <input type="hidden" 
                            @if(!empty($route_s))
                                @foreach($route_s as $route)
                                value=" {{ $route->id }}"
                                @endforeach
                            @else
                                value=""
                            @endif
                            name="id"
                    >
                    @else
                        <form id="progress-form" class="p-4 progress-form" action="{{url('route-master')}}" method="post">
                    @endif
                    @csrf
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <label for="lastName1">Enter New Route Name:</label>
                            <input required type="text" class="form-control uperletter" placeholder="Route Name" 
                            name="route_name" id="route_name" <?php 
                            if(!empty($route_s)){ 
                                foreach($route_s as $route){
                                    echo 'value="'.$route->route_name.'"'; 
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
                    <h1 class="me-2">Route Area Bus Master</h1>
                </div>
				
				<form action="{{ route('route-area-bus.import') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="form-group">
						<label for="csv_file">Upload CSV File for Route Area Bus:</label>
						<input type="file" name="file" class="form-control" required>

						@if ($errors->any())
							<div class="alert alert-danger mt-2">
								<ul>
									@foreach ($errors->all() as $error)
										<li>{{ $error }}</li>
									@endforeach
								</ul>
							</div>
						@endif
					</div>
					<button type="submit" class="btn btn-raised ripple btn-raised-success m-1">Import CSV</button>
				</form>
                <div class="separator-breadcrumb border-top"></div>
                    @if(!empty($bus_s))
                        <form id="progress-form" class="p-4 progress-form" action="{{url('save-route-area-bus')}}" method="post">
                        <input type="hidden" 
                            @if(!empty($bus_s))
                                value=" {{ $bus_s->id }}"
                            @else
                                value=""
                            @endif
                            name="id"
                    >
                    @else
                        <form id="progress-form" class="p-4 progress-form" action="{{url('save-route-area-bus')}}" method="post">
                    @endif
                    @csrf
                    <div class="row">
                        <div class="col-md-3 form-group mb-3">
                            <label for="lastName1">Route Name:</label>
                            <select name="route_name" class="form-control" id="route_name1">
                                <?php 
                                    if(!empty($select_main)){
                                        foreach($select_main as $select){
                                            if(!empty($bus_s)){ 
                                                
                                                // foreach($bus_s as $bus){
                                                    // echo $bus_s->id;die();
                                                    // print_r($bus->id);die;
                                                    if($bus_s->route_name == $select->route_name){
                                                        echo '<option value="'.$bus_s->route_name.'" selected>'.$bus_s->route_name.'</option>';
                                                    }else {
                                                        echo '<option value="'.$select->route_name.'">'.$select->route_name.'</option>';
                                                    }
                                                // }
                                            } else {
                                                echo '<option value="'.$select->route_name.'">'.$select->route_name.'</option>';
                                            }
                                        }
                                    }
                                ?>

                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="lastName1">Area Name:</label>
                            <select name="area_name" class="form-control" onchange="maintenance_area_name_id(this);" id="maintenance_area_name_d">
                                <?php 
                                    if(!empty($select_main_area)){
                                        echo '<option value="0" selected> ---- Select The Area ---- </option>';
                                        foreach($select_main_area as $select){
                                            if(!empty($bus_s)){ 
                                                // foreach($maintenance_s as $maintenance){
                                                    if($bus_s->area_name == $select->area_name){
                                                        echo '<option value="'.$bus_s->area_name.'" selected>'.$bus_s->area_name.'</option>';
                                                    }else {
                                                        echo '<option value="'.$select->area_name.'">'.$select->area_name.'</option>';
                                                    }
                                                // }
                                            } else {
                                                echo '<option value="'.$select->area_name.'">'.$select->area_name.'</option>';
                                            }
                                        }
                                    }
                                ?>

                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3" id="bus_top">
                            <label for="lastName1">Bus Stop Name:</label>
                                <select name="bus_stop_name" class="form-control uperletter" id="maintenance_bus_stop_name">
                                    <?php 
                                        if(!empty($bus_s)){ 
                                            // if(!empty($select_main_area)){
                                                foreach($select_main_bus as $select){
                                                    if(!empty($bus_s)){ 
                                                        echo $select->bus_stop_name;
                                                        // foreach($maintenance_s as $maintenance){
                                                            if($bus_s->bus_stop_name == $select->bus_stop_name){
                                                                echo 'Hello';
                                                                echo '<option value="'.$bus_s->bus_stop_name.'" selected>'.$bus_s->bus_stop_name.'</option>';
                                                            }else {
                                                                echo '<option value="'.$select->bus_stop_name.'">'.$select->bus_stop_name.'</option>';
                                                            }
                                                        // }
                                                    } else {
                                                        echo '<option>--Select Bus Stop--</option>';
                                                        echo '<option value="'.$select->bus_stop_name.'">'.$select->bus_stop_name.'</option>';
                                                    }
                                                }
                                            // }
                                        }
                                    ?>

                                </select>
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
                <h1 class="me-2">Route Master</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card text-start">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper" style="width: 100%">
                                <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Route Name</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $i=1;  ?>
                                @if(!empty($routemasters))
                                @foreach ($routemasters as $routemaster)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $routemaster->route_name }}</td>
                                    <td class='d-flex'>
                                        <a class="btn btn-primary m-1" href="{{ url('view-route-master') .'/'.$routemaster->id}}">Edit</a>
                                        <!-- <form id="routeMasterForm" method="post" action="{{ url('delete-route-name') }}">
    @csrf
    <input type="hidden" name="table_name" value="routemaster">
    <input type="hidden" name="delete_id" value="{{ $routemaster->id }}">
    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('routeMaster')">Delete</button>
</form> -->
                                <?php $a = "routemaster"."-".$routemaster->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-route-name').'/'.$a}}" onclick="confirmDelete2(event)">Delete</a>

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
                <h1 class="me-2">Route Area Bus Master</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card text-start">
                    <div class="card-body">
                    <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="route_name">
                      <input type="hidden" name="column_names[]" value="area_name">
                      <input type="hidden" name="column_names[]" value="bus_stop_name">
                      <input type="hidden" name="table_name" value="routeareabusmaster">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div>
                        <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                            <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Route Name</th>
                                <th>Area Name</th>
                                <th>Bus Name</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i=1;  ?>
                            @if(!empty($routeareabuss))
                            @foreach ($routeareabuss as $routeareabus)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $routeareabus->route_name }}</td>
                                <td>{{ $routeareabus->area_name }}</td>
                                <td>{{ $routeareabus->bus_stop_name }}</td>

                                <td class='d-flex'>
                                    <a class="btn btn-primary m-1" href="{{ url('view-bus-route-master') .'/'.$routeareabus->id}}">Edit</a>
                                    <!-- <form id="routeAreaBusMasterForm" method="post" action="{{ url('delete-route') }}">
    @csrf
    <input type="hidden" name="table_name" value="routeareabusmaster">
    <input type="hidden" name="delete_id" value="{{ $routeareabus->id }}">
    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('routeAreaBusMaster')">Delete</button>
</form> -->
<br>
                                <?php $a = "routeareabusmaster"."-".$routeareabus->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-route').'/'.$a}}" onclick="confirmDelete1(event)">Delete</a>

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
                                <th>Route Name</th>
                                <th>Area Name</th>
                                <th>Bus Name</th>
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
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
       function maintenance_area_name_id(sel){
            var area_name = sel.value;
            // alert(area_name);

            $.ajax({
                data: {area_name:area_name},
                url: "{{ url('route-data') }}",
                type: "POST",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                dataType: 'json',
                success: function (data) {
                    $('#maintenance_bus_stop_name').empty();
                    $.each(data, function(key, value) {
                        $('#maintenance_bus_stop_name')
                        .append($("<option></option>")
                        .attr("value", value.bus_stop_name)
                        .text(value.bus_stop_name)); 
                    });                
                },
                error: function (data) {
                    console.log('Error:', data);
                    $('#maintenance_bus_stop_name')
                    .append($("<option></option>")
                    .attr("value", '404')
                    .text('Please check the error')); 
                }
            });
        }

        $('#maintenance_area_name_i').click(function (e) {
            // alert("Hello")
        e.preventDefault();
        $(this).html('Sending..');
        $.ajax({
          data: $('#productForm1').serialize(),
          url: "{{ route('inquiry-data.store') }}",
          type: "POST",
          dataType: 'json',
          success: function (data) {
       
              $('#productForm1').trigger("reset");
              $('#callModel').modal('hide');
              table.draw();
           
          },
          error: function (data) {
              console.log('Error:', data);
              $('#saveBtn').html('Save Changes');
          }
      });
    });

    document.addEventListener('DOMContentLoaded', function() {
    $("#reset").on("click", function () {                
        $("#route_name").val("");
        $("#route_name1").val("");
        $("#maintenance_area_name_d").val("");
        $("#maintenance_bus_stop_name").val("");


            
    });

})

    </script>
</div>
@endsection