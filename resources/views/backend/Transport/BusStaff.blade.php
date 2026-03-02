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


.uperletter{
  text-transform: capitalize;
} 

</style>
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Driver / Conductor Master</h1>
        </div>
		<form action="{{ route('busstaff.import') }}" method="POST" enctype="multipart/form-data">
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
                @if(!empty($bus_stafs))
                  <form action="{{url('store-driver-conductor-master')}}" method="post">                  
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
                    <form id="myform" action="{{url('save-driver-conductor-master')}}" method="post">
                @endif
            @csrf
            <div class="row">
            <div class="col-md-12 form-group mb-3">
                    <label for="phone">Driver/Conductor</label><br>
                    <label class="radio-inline">
                    </label>
                    <label class="radio-inline">
                    <?php 
                        if(!empty($bus_stafs)){
                            foreach($bus_stafs as $bus_staf){
                                if ($bus_staf->role == "Driver") {
                                    echo '<input type="radio" name="role" checked value="Driver"> Driver ';
                                    echo '<input type="radio" name="role" value="Conductor"> Conductor ';
                                } else {
                                    echo '<input type="radio" name="role" value="Driver"> Driver ';
                                    echo '<input type="radio" name="role" checked value="Conductor"> Conductor ';
                                }
                            }
                        }else{
                            echo '<input type="radio" name="role" checked value="Driver"> Driver ';
                            echo '<input type="radio" name="role" value="Conductor"> Conductor ';
                        }
                    
                    ?>    
                    

                    
                    </label>
                    
                </div>
                <h4>Personal info : </h4>
            <div class="col-md-3 form-group mb-3">
                    <label for="firstName1">Employee Name</label>
                    <input name="ename" class="form-control uperletter" id="ename" 
                    type="text" 
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            value=" {{ $bus_staf->ename }}"
                        @endforeach
                    @else
                        value=""
                    @endif
                    placeholder="Employee Name" />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="picker2">Mobile no.</label>
                    <input type="text" class="form-control" id="mobile_number" 
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            value=" {{ $bus_staf->mobile_number }}"
                        @endforeach
                    @else
                        value=""
                    @endif
                    name="mobile_number" />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="firstName1">Aadhar No.</label>
                    <input name="aadhar_number" class="form-control" id="aadhar_number" 
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            value="{{ $bus_staf->aadhar_number }}"
                        @endforeach
                    @else
                        value=""
                    @endif
                    type="text" placeholder="Aadhar No." />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">SSSMID</label>
                    <input name="sssmid" class="form-control" id="sssmid" 
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            value=" {{ $bus_staf->sssmid }}"
                        @endforeach
                    @else
                        value=""
                    @endif
                    type="text"
                        placeholder="SSSMID" />
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label for="lastName1">Current Address</label>
                    <!-- <input name="Cadd" class="form-control" id="Cadd" type="text" placeholder="Current Address" /> -->
                    <br>
                    <textarea id="current_address" class="form-control uperletter" 
                    name="current_address" rows="3" cols="75" 
                    placeholder="Current Address" ><?php 
                    if(!empty($bus_stafs)){ 
                        foreach($bus_stafs as $bus_staf){
                            echo $bus_staf->current_address; 
                        }
                    } 
                    else{} 
                    ?></textarea>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label for="lastName1">Parmanent Address</label>
                    <!-- <input name="Padd" class="form-control" id="Padd" type="textarea" placeholder="Parmanent Address" /> -->
                    <br>
                    <textarea id="parmanent_address" name="parmanent_address" 
                    rows="3" cols="75" class="form-control uperletter"
                    placeholder="Parmanent Address" ><?php 
                    if(!empty($bus_stafs)){ 
                        foreach($bus_stafs as $bus_staf){
                            echo $bus_staf->parmanent_address; 
                        }
                    } 
                    else{} 
                    ?></textarea>
                </div>        
                <h4>Document info : </h4>     
                <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">License NO.</label>
                    <input name="license_no" class="form-control" id="license_no" 
                    type="text"
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            value="{{ $bus_staf->license_no }}"
                        @endforeach
                    @else
                        value=""
                    @endif
                    placeholder="License NO." />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="picker2">License Expire</label>
                    <input type="date" class="form-control" id="license_expire" 
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            value="{{ $bus_staf->license_expire }}"
                        @endforeach
                    @else
                        value=""
                    @endif
                    name="license_expire" />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="picker2">License Issue</label>
                    <input type="date" class="form-control" id="license_lssue" 
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            value="{{ $bus_staf->license_lssue }}"
                        @endforeach
                    @else
                        value=""
                    @endif
                    name="license_lssue" />
                </div>
               
                <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">PAN Card No.</label>
                    <input name="voter_id_no" class="form-control" 
                    id="voter_id_no" type="text"
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            value="{{ $bus_staf->voter_id_no }}"
                        @endforeach
                    @else
                        value=""
                    @endif
                        placeholder="PAN Card No." />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="picker2">Joining Date</label>
                    <input type="date" class="form-control" id="joining_ate" 
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            value="{{ $bus_staf->joining_date }}"
                        @endforeach
                    @else
                        value=""
                    @endif
                    name="joining_date" />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="picker2">Left ?/ Leaving Date</label>
                    <input type="checkbox" id="leaving_date1" 
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            <?php 
                                if (!empty($bus_staf->leaving_date1)){
                                    echo 'checked="checked"';
                                    echo 'value="'.$bus_staf->leaving_date1.'"';
                                }
                            ?>
                        @endforeach
                    @else
                        value="yes"
                    @endif
                    name="leaving_date1" /> 
                    <input type="date" class="form-control" id="leaving_date" 
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            value="{{ $bus_staf->leaving_date }}"
                        @endforeach
                    @else
                        value=""
                    @endif
                    name="leaving_date" />
                </div>
               
                
             <!--<div class="col-md-3 form-group mb-3">
                    <label for="callno">call no.</label>
                    <select name="callno" class="form-control uperletter" id="call_no">
                        <option value="">-- Please select --</option>
                        @if (!empty($bus_stafs))
                        @foreach ($bus_stafs as $s_item)
                        {{ $c_stream = $s_item->callno }}
                        @endforeach
                        @endif
                        @if(!empty($callno))
                            @foreach ($callno as $callno_)
                            <option {{( (!empty($c_stream)) && ($c_stream==$callno_->callno)) ? 'selected' :
                                    '' }} value="{{ $callno_->callno }}">{{ $callno_->callno }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>-->
                 
                <div class="col-md-3 form-group mb-3">
                    <label for="picker2"> Offical Mobile no.</label>
                    <input type="number" class="form-control" id="offical_mobile_no" name="offical_mobile_no" @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            value="{{ $bus_staf->offical_mobile_no }}"
                        @endforeach
                    @else
                        value=""
                    @endif/>
                </div>
				<div class="col-md-3 form-group mb-3">
					<label for="emergency_contact_no">Emergency Contact No.</label>
					<input type="number" class="form-control" id="emergency_contact_no" name="emergency_contact_no"
						@if(!empty($bus_stafs))
							@foreach($bus_stafs as $bus_staf)
								value="{{ $bus_staf->emergency_contact_no }}"
							@endforeach
						@else
							value=""
						@endif
					/>
				</div>


                <div class="col-md-6 form-group mb-3">
                    <label for="lastName1">Remarks</label>
                    <!-- <input name="Remarks" class="form-control" id="Remarks" type="text" placeholder="Remarks" /> -->
                    <br>
                    <textarea id="remarks" class="form-control uperletter" name="remarks" rows="3" cols="75" 
                    placeholder="Remarks" ><?php 
                    if(!empty($bus_stafs)){ 
                        foreach($bus_stafs as $bus_staf){
                            echo $bus_staf->remarks; 
                        }
                    } 
                    else{} 
                    ?></textarea>
                    
                </div>
                <!--<div class="col-md-3 form-group mb-3">
                    <br>
                    <label for="lastName1">Health Status </label>
                    <input type="checkbox" id="healthstatus" 
                    @if(!empty($bus_stafs))
                        @foreach($bus_stafs as $bus_staf)
                            <?php 
                                if (!empty($bus_staf->healthstatus)){
                                    echo 'checked="checked"';
                                    'value="'.$bus_staf->healthstatus.'"';
                                }
                            ?>
                        @endforeach
                    @else
                        value="yes"
                    @endif
                    name="healthstatus" />
                </div>   -->            
                <div class="col-md-12">
                    <button class="btn btn-primary">Submit</button>

                    <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>

                </div>
            </div>
        </form>
    </div>
    <!-- end of main-content -->
    <br>
    <h1 class="me-2">List of Driver / Conductor saved Records</h1>
          </div>
        <div class="separator-breadcrumb border-top"></div>

        <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
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
                  </form></div>
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>Sr.</th>
                            <th>Driver / Conductor Name</th>
                            <th>Is Driver</th>
                            <!-- <th>Link with Employee</th> -->
                            <!-- <th>Employee Name</th> -->
                            <th>Address</th>
                            <th>Mobile No</th>
                            <th>Joining Date</th>
                            <!-- <th>Is Left</th> -->
                            <th>Leaving Date</th>
                            <!--<th>Remarks</th>
                            <th>CellNo</th>-->
                            <th>Official Mobile No</th>
							<th>Emergency Contact No.</th>
                            <!--<th>Health Status</th>-->
                            <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $i=1;  ?>
                        @if(!empty($busStafs))
                        @foreach ($busStafs as $busStaf)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $busStaf->ename }}</td>
                            <td>{{ $busStaf->role }}</td>
                            <td>{{ $busStaf->current_address }}</td>
                            <td>{{ $busStaf->mobile_number }}</td>
                            <td>{{ date("d-m-Y", strtotime($busStaf->joining_date)) }}</td>
                            <td>{{ date("d-m-Y", strtotime($busStaf->leaving_date)) }}</td>
                           <!-- <td>{{ $busStaf->remarks }}</td>
                            <td>{{ $busStaf->call_no }}</td>-->
                            <td>{{ $busStaf->offical_mobile_no }}</td>
							<td>{{ $busStaf->emergency_contact_no }}</td>
                            <!--<td>{{ $busStaf->healthstatus }}</td>-->
                            <!-- <td> 
                              <a class="btn btn-raised ripple btn-raised-warning m-1" href="{{ url('view-driver-conductor-master') .'/'.$busStaf->id}}">Edit</a>
                              <a class="btn btn-raised ripple btn-raised-danger m-1" href="{{ url('delete-driver-conductor-master') .'/'.$busStaf->id}}">Delete</a>
                            </td> -->
                            <td class='d-flex'>
                              <a class="btn btn-primary m-1" href="{{ url('view-driver-conductor-master') .'/'.$busStaf->id}}">Edit</a>
                                <!-- <form id="deleteForm" method="post" action="{{url('delete-driver-conductor-master')}}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="busstaff">
                                    <input type="hidden" name="delete_id" value="{{ $busStaf->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="confirmDelete(event)">Delete</button>
                                </form> -->
                                <br>
                                <?php $a = "busstaff"."-".$busStaf->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-driver-conductor-master').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
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
                            <th>Driver / Conductor Name</th>
                            <th>Is Driver</th>
                            <!-- <th>Link with Employee</th> -->
                            <!-- <th>Employee Name</th> -->
                            <th>Address</th>
                            <th>Mobile No</th>
                            <th>Joining Date</th>
                            <!-- <th>Is Left</th> -->
                            <th>Leaving Date</th>
                            <th>Remarks</th>
                            <th>CellNo</th>
                            <th>Official Mobile No</th>
                            <th>Health Status</th>
                            <th>Action</th>
                        </tr>
                      </tfoot>
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
        $("#ename").val("");
        $("#mobile_number").val("");
        $("#aadhar_number").val("");
        $("#sssmid").val(""); 
        $("#current_address-").val(""); 
        $("#parmanent_address").val("");
        $("#license_no").val("");
        $("#license_expire").val("");
        $("#license_lssue").val("");
        $("#joining_ate").val("");  
        $("#leaving_date").val("");  
        $("#call_no").val(""); 
        $("#offical_mobile_no").val(""); 
        $("#remarks").val(""); 
        $("#healthstatus").val("");
        $("input[type='checkbox']").prop('checked', false);
        $("input[type='radio']").prop('checked', false);     
    });

})
</script>
<script>
    document.getElementById('reset').addEventListener('click', function() {
        document.getElementById('myform').reset();
    });
</script>

@endsection