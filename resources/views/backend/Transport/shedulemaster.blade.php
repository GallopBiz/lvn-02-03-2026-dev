@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="row">
<div class="col-md-3 form-group mb-3">
        <div class="breadcrumb">
                <h1 class="me-2">Schedule Master</h1>
        </div>
		<form action="{{ route('schedule-master.import') }}" method="POST" enctype="multipart/form-data">
			@csrf
			<div class="form-group">
				<label for="csv_file">Upload CSV File for Schedule Master:</label>
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
        @if(!empty($bus_s))
            <form id="progress-form" class="p-4 progress-form" action="{{url('schedulemaster')}}" method="post">
            <input type="hidden" 
                @if(!empty($bus_s))
                    value=" {{ $bus_s->id }}"
                @else
                    value=""
                @endif
                name="id"
        >
        @else
            <form id="progress-form" class="p-4 progress-form" action="{{url('schedulemaster')}}" method="post">
        @endif
        @csrf
            <div class="row">
            <div class="col-md-12 form-group mb-3">
                    <label for="lastName1">Enter new Schedule Name:</label>
                    <input required name="schedule_name" class="form-control uperletter" id="schedule_name" type="text" placeholder="Enter new Schedule Name" />
                </div>
                <!-- <div class="col-md-12 form-group mb-3">
                    <label for="picker2">Print Options</label>
                    <br>
                    <input type="radio" id="css" name="Class&Buss" value="Class&Buss">
                    <label for="css">Class & Buss</label>
                    <input type="radio" id="css" name="AllBusStop" value="AllBusStop">
                    <label for="css">All Bus Stop</label>
                    <input type="radio" id="css" name="BusStop" value="BusStop">
                    <label for="css">Bus Stop</label>
                </div> -->
                <!-- <div class="col-md-4 form-group mb-3">
                    <label for="picker2">points</label>
                    <br>
                    <input type="radio" id="css" name="Pickup" value="Pickup">
                    <label for="css">Pick up</label>
                    <input type="radio" id="css" name="drop" value="drop">
                    <label for="css">Drops</label>
                </div> -->
                <div class="col-md-42">
                    <button class="btn btn-primary">Submit</button>
                    <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>


                </div>
            </div>
        </form>
    </div>

<div class="col-md-8 form-group mb-3">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Add Schedule </h1>		
        </div>
		<form action="{{ route('schedule-master-all.import') }}" method="POST" enctype="multipart/form-data">
			@csrf
				<div class="form-group">
					<label for="csv_file">Upload CSV File for Schedule Master:</label>
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
        @if(!empty($bus_s))
            <form id="progress-form" class="p-4 progress-form" action="{{url('save-schedulemaster')}}" method="post">
            <input required type="hidden" 
                @if(!empty($bus_s))
                    value=" {{ $bus_s->id }}"
                @else
                    value=""
                @endif
                name="id"
        >
        @else
            <form id="progress-form" class="p-4 progress-form" action="{{url('save-schedulemaster')}}" method="post">
            <input type="hidden" value="" id="id" name="id">
        @endif
        @csrf
            <div class="row">
            <div class="col-md-3 form-group mb-3">
                <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                    <label for="lastName1">Schedule Name</label>
                    <select required name="schedule_namea" class="form-control" onchange="schedule_names(this);" id="schedule_namea">
                    <!-- <select id="schedule_namea" onchange="schedule_namea(this);" class="form-control" name="schedule_name" autocomplete="shipping address-level1" > -->
                        <option value="" selected> -- Please select -- </option>
                       <?php 
                            if(!empty($schedule_names)){
                                foreach($schedule_names as $schedule_name){
                                    echo '<option value="'.$schedule_name->schedule_name.'" >'.$schedule_name->schedule_name.'</option>';
                                }
                            }
                       ?>
                    </select>
                </div>
                
                <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Timing From Date </label>
                    <input name="schedule_date" class="form-control" id="schedule_date" type="date"
                        placeholder="Timing From Date" />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Timing From  </label>
                    <input name="schedule_time_from" class="form-control" id="schedule_time_from" type="time"
                        placeholder="Timing From from" />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">To  </label>
                    <input name="schedule_time_to" class="form-control" id="schedule_time_to" type="time"
                        placeholder="to" />
                </div>
                <div class="col-md-5 form-group mb-3">
                    <br>
                    <label for="picker2">Print Options</label>
                    <br>
                    <input type="radio" id="schedule_print_option" name="schedule_print_option" value="Class&Buss">
                    <label for="css">Class & Buss</label>
                    <input type="radio" id="schedule_print_option" name="schedule_print_option" value="AllBusStop">
                    <label for="css">All Bus Stop</label>
                    <input type="radio" id="schedule_print_option" name="schedule_print_option" value="BusStop">
                    <label for="css">Bus Stop</label>
                </div>
                <div class="col-md-3 form-group mb-3">
                    <br>
                    <label for="picker2">points</label>
                    <br>
                    <input type="radio" id="schedule_point" name="schedule_point" value="Pickup">
                    <label for="css">Pick up</label>
                    <input type="radio" id="schedule_point" name="schedule_point" value="drop">
                    <label for="css">Drops</label>
                </div>
                <div class="col-md-2 form-group mb-3">
                <br>
                    <!-- <label for="picker2">Display Order</label> -->
                    <input type="hidden" class="form-control" value="0" id="schedule_order" name="schedule_order" />
                </div> 
                <div class="col-md-42">
                    <button class="btn btn-primary">Submit</button>
                    {{-- <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button> --}}

                </div>
            </div>
    </div>
</div>
</div>


<div class="row">
<br>
    <div class="separator-breadcrumb border-top"></div>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="breadcrumb">
                <h1 class="me-2">Classes To Pickup and Drop</h1>
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
                                    <th>Select</th>
                                    <th>Class Name</th>
                                    <!-- <th>Section</th> -->
                                    <th>Class Strength</th>
                                    <th>Need To Take</th>
                                    <th>Taken</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $i=1;  ?>
                                <!-- @if(!empty($schedule_names)) -->
                                @if(!empty($student_data))
                                    @foreach ($student_data as $schedule_name)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>
                                        <?php 
                                            echo '<input type="checkbox" id="check_two_'.$i.'" name="check_two_'.$i.'" value="1">';
                                            echo '<input type="hidden" name="count" value="'.$i.'">';
                                        ?> 
                                        </td>
                                        <td> <?php echo '<input type="hidden" value="'.$schedule_name->class_name.'" id="class_name_'.$i.'" name="class_name_'.$i.'">' ;echo ($schedule_name->class_name); ?> </td>
                                        <!-- <td> <?php //echo '<input type="hidden" value="'.$schedule_name['section'].'" id="section_'.$i.'" name="section_'.$i.'">'; echo ($schedule_name['section']); ?> </td> -->
                                        <td> <?php echo '<input type="hidden" value="'.$schedule_name->count.'" id="class_strength_'.$i.'" name="class_strength_'.$i.'">'; echo ($schedule_name->count); ?> </td>
                                        <td> <?php echo '<input type="hidden" value="'.$schedule_name->transport_count.'" id="class_strength_'.$i.'" name="class_strength_'.$i.'">'; echo ($schedule_name->transport_count); ?> </td>
                                        <td> 
                                            <?php 
                                                if(!empty($count_taken_tranport)){
                                                    foreach($count_taken_tranport as $transport){
                                                        if($transport->class_name == $schedule_name->class_name){
                                                            print_r($transport->count);
                                                        }
                                                        
                                                    }
                                                } else {

                                                }
                                            ?> 
                                        </td>
                                    </tr>
                                    <?php $i++; ?>
                                    @endforeach
                                @endif
                                <!-- @else -->
                                <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                                <!-- @endif -->
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Select</th>
                                    <th>Class Name</th>
                                    <!-- <th>Section</th> -->
                                    <th>Class Strength</th>
                                    <th>Need To Take</th>
                                    <th>Taken</th>
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
                        <!-- <h4 class="card-title mb-3 text-end"><a href="{{url('add-student-registrations')}}"><button class="btn btn-outline-primary" type="button">Create Registration</button></a></h4> -->
                        <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                            <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Select</th>
                                <th>Call No.</th>
                                <th>Vechicle No.</th>
                                <th>Capacity</th>
                                <th>In Bus</th>
                                <th>Route</th>
                                <th>Driver</th>
                                <th>Conductor</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i=1;  ?>
                            @if(!empty($vehicels))
                            @foreach ($vehicels as $vehicel)
                            <tr>
                                <td>{{ $i }}</td>
                                
                                <td> 
                                    <?php 
                                        echo '<input type="checkbox" id="check_one_'.$i.'" name="check_one_'.$i.'" value="1">';
                                        echo '<input type="hidden" class="check_one_count" name="check_one_count" value="'.$i.'">';
                                    ?>
                                </td>
                                <td><?php echo '<input type="hidden" value="'.$vehicel->callno.'" id="callno_'.$i.'" name="callno_'.$i.'">' ;?>{{ $vehicel->callno }}</td>
                                <td><?php echo '<input type="hidden" value="'.$vehicel->vehicelno.'" id="vehicelno_'.$i.'" name="vehicelno_'.$i.'">' ;?>{{ $vehicel->vehicelno }}</td> 
                                <td><?php echo '<input type="hidden" value="'.$vehicel->capacity.'" id="capacity_'.$i.'" name="capacity_'.$i.'">' ;?>{{ $vehicel->capacity }}</td> 
                                <td><?php echo '<p id="counttrasport_'.$i.'"> 0 </p>'; ?></td>
                                <td>
                                    <select name="route_name_{{$i}}" id="route_name_{{$i}}" class="form-control route_name_name">
                                        <?php 
                                            echo '<option value="-- select Route --">-- select Route --</option>';
                                            if(!empty($routes)){
                                                foreach($routes as $route){
                                                    echo '<option value="'.$route->route_name.'">'.$route->route_name.'</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </td> 
                                <td>
                                    <select name="driver_name_{{$i}}" class="form-control driver_name_name" id="driver_name_{{$i}}">
                                        <?php 
                                            echo '<option value="-- select driver --">-- select driver --</option>';
                                            if(!empty($drivers)){
                                                foreach($drivers as $driver){
                                                    echo '<option value="'.$driver->ename.'">'.$driver->ename.'</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </td> 
                                <td>
                                    <select name="conductor_name_{{$i}}" class="form-control conductor_name_name" id="conductor_name_{{$i}}">
                                        <?php 
                                            echo '<option value="-- select conductor --">-- select conductor --</option>';
                                            if(!empty($conductors)){
                                                foreach($conductors as $conductor){
                                                    echo '<option value="'.$conductor->ename.'">'.$conductor->ename.'</option>';
                                                }
                                            }
                                        ?>
                                    </select>
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
                                <th>Select</th>
                                <th>Call No.</th>
                                <th>Vechicle No.</th>
                                <th>Capacity</th>
                                <th>In Bus</th>
                                <th>Route</th>
                                <th>Driver</th>
                                <th>Conductor</th>
                            </tr>
                            </tfoot>
                        </table>
                        </div>
                    </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<script>
    function schedule_names(sel){
        schedule_name = sel.value
        $('.route_name_name').prop('selectedIndex', 0);
        $('.driver_name_name').prop('selectedIndex', 0);
        $('.conductor_name_name').prop('selectedIndex', 0); 

        $.ajax({
            data: {schedule_name:schedule_name},
            url: "{{ url('route-schedulemaster') }}",
            type: "POST",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            dataType: 'json',
            success: function (data) {
                var i = 1
                // console.log(data.schedule_names);
                $.each(data.schedule_names, function(key, value) {
                    $('#schedule_point').prop('checked', false);
                    $('#schedule_print_option').prop('checked', false);
                    $('#schedule_date').val(value.schedule_date);
                    $('#schedule_time_from').val(value.schedule_time_from);
                    $('#schedule_time_to').val(value.schedule_time_to);
                    $('#schedule_order').val(value.schedule_order);
                    // console.log(value.schedule_print_option);
                    $("input[name=schedule_print_option][value='"+value.schedule_print_option+"']").prop("checked",true);
                    // $('#schedule_print_option').filter("[value="+value.schedule_print_option+"]").prop('checked', true);
                    $('#schedule_point').filter("[value="+value.schedule_point+"]").prop('checked', true);
                    $("input[name=schedule_point][value='"+value.schedule_point+"']").prop("checked",true);
                    // $('#check_two_'+i+'').empty();
                    $('#check_two_'+i+'').prop('checked', false);
                    $('#check_one_'+i+'').prop('checked', false);

                    var json = $.parseJSON(value.schedule_check_two);
                    $(json).each(function (i, val) {
                        $.each(val, function (k, v) {                                 
                            $('#check_two_'+k+'').prop('checked', true);
                            // console.log(k + " : " + v);
                        });
                    });
                    // $('#route_name_'+i+'').val('1');
                    // $('#route_name_'+i+'').find('option:selected').empty();
                    // $('#route_name_'+i+'').append($("<option selected> Please select </option>").attr("value", ""); 

                    var json1 = $.parseJSON(value.schedule_check_one);
                    $(json1).each(function (i, val) {
                        
                        $.each(val, function (k, v) {
                            console.log('counttrasport_'+k);
                            // ${foundItem.pickup_bus_no_count}
                            const foundItem = data.pickupBusNoCounts.find(item => item.pickup_bus_no === v.vehicelno);
                            $('#counttrasport_'+k).text('');
                            if (foundItem) {
                                $('#counttrasport_'+k).text(`${foundItem.pickup_bus_no_count}`);
                                console.log(`${foundItem.pickup_bus_no_count}.`);
                            } else {
                                console.log(`0`);
                                $('#counttrasport_'+k).text(`0`);
                            }

                            

                            // counttrasport
                            // $('#route_name_'+k+' option:selected').empty();
                            // $('#driver_name_'+k+' option:selected').empty();
                            // $('#conductor_name_'+k+' option:selected').empty();

                            $('#check_one_'+k+'').prop('checked', true);
                            // $('#route_name_'+k+'').find('option:selected').empty();

                            // var route_name_select = $('#route_name_'+i+'').val()
                            // console.log(route_name_select)

                            var route_name = $('#route_name_'+k+' option');
                            $('#route_name_'+k+'').find('option').remove();
                            
                            var values = $.map(route_name ,function(option) {
                                if(option.value == v.route_name){
                                    $('#route_name_'+k+'').append($("<option selected>"+ v.route_name+"</option>").attr("value", v.route_name).text(v.route_name)); 
                                } 
                                if(option.value != v.route_name){
                                    $('#route_name_'+k+'').append($("<option >"+ option.value+"</option>").attr("value", option.value).text(option.value)); 
                                }
                            });

                            var driver_name = $('#driver_name_'+k+' option');
                            $('#driver_name_'+k+'').find('option').remove();
                            
                            var values = $.map(driver_name ,function(option) {
                                if(option.value == v.driver_name){
                                    $('#driver_name_'+k+'').append($("<option selected>"+ v.driver_name+"</option>").attr("value", v.driver_name).text(v.driver_name)); 
                                } 
                                if(option.value != v.driver_name){
                                    $('#driver_name_'+k+'').append($("<option >"+ option.value+"</option>").attr("value", option.value).text(option.value)); 
                                }
                            });

                            var conductor_name = $('#conductor_name_'+k+' option');
                            $('#conductor_name_'+k+'').find('option').remove();
                            
                            var values = $.map(conductor_name ,function(option) {
                                if(option.value == v.conductor_name){
                                    $('#conductor_name_'+k+'').append($("<option selected>"+ v.conductor_name+"</option>").attr("value", v.conductor_name).text(v.conductor_name)); 
                                } 
                                if(option.value != v.conductor_name){
                                    $('#conductor_name_'+k+'').append($("<option >"+ option.value+"</option>").attr("value", option.value).text(option.value)); 
                                }
                            });

                            // $('#driver_name_'+k+'').append($("<option selected>"+ v.driver_name+"</option>").attr("value", v.driver_name).text(v.driver_name)); 
                            // $('#conductor_name_'+k+'').append($("<option selected>"+ v.conductor_name+"</option>").attr("value", v.conductor_name).text(v.conductor_name)); 
                        
                        });
                    });
                    $("input[type='text'].id").val()
                    $("#id").val(value.id)

                    i++
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


    document.addEventListener('DOMContentLoaded', function() {
    $("#reset").on("click", function () {                
        $("#schedule_name").val("");
        $("#schedule_namea").val("");
        $("#schedule_date").val("");
        $("#schedule_time_from").val("");
        $("#schedule_time_to").val("");
        $("#schedule_print_option").val("");
        $("#schedule_point").val("");
        $("input[type='checkbox']").prop('checked', false);
        $("input[type='radio']").prop('checked', false);

    });

})

</script>
    <!-- end of main-content -->
</div>
@endsection