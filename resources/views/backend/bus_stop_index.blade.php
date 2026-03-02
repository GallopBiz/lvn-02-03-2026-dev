@extends('backend.layouts.main')

@section('main-container')

<style>

.uperletter{
  text-transform: capitalize;
} 


</style>
  <div class="main-content">


          <!-- ============ Body content start ============= -->
        <div class="main-content">
          <div class="breadcrumb">
            <h1>Bus Stop Master </h1>
            <ul>
              <!-- <li><a href="href">Form</a></li> -->
              <!-- <li>Basic</li> -->
            </ul>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12">
              <div class="card mb-4">
                <div class="card-body">
                  <!-- <div class="card-title mb-3">Form Inputs</div> -->
                  <!-- <form> -->
                    
                  @if(!empty($areas))
                  <form id="progress-form" class="p-4 progress-form" action="{{url('bus-stop-store')}}" method="post">
                  <input type="hidden" 
                    @if(!empty($areas))
                      @foreach($areas as $bus_stop)
                        value=" {{ $bus_stop->id }}"
                      @endforeach
                    @else
                      value=""
                    @endif
                    name="id"
                  >
                  @else
                  <form id="progress-form" class="p-4 progress-form" action="{{url('bus-stop-create')}}" method="post">
                  @endif
                    <div class="row">
                      {{ csrf_field() }}
                      <!-- <div class="row"> -->
                      <div class="col-md-3 form-group mb-3">
                        <label for="picker1">Area Name : </label>
                        <select required name="area_name" class="form-control uperletter" id="area_name">
                        <?php 
                            if(!empty($select_main)){
                                foreach($select_main as $select){
                                    if(!empty($areas)){
                                        foreach($areas as $area){
                                            if($area->area_name == $select->area_name){
                                                echo '<option value="'.$area->area_name.'" selected>'.$select->area_name.'</option>';
                                            }else {
                                                echo '<option value="'.$select->area_name.'">'.$select->area_name.'</option>';
                                            }
                                        }
                                    } else {
                                        echo '<option value="'.$select->area_name.'">'.$select->area_name.'</option>';
                                    }
                                }
                            }
                        ?>
                        </select>
                      </div>
                      <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Bus Stop Name : </label>
                        <input
                          class="form-control uperletter"
                          id="bus_stop_name"
                          name="bus_stop_name"
                          type="text"
                          @if(!empty($areas))
                            @foreach($areas as $bus_stop)
                              value=" {{ $bus_stop->bus_stop_name }}"
                            @endforeach
                          @else
                            value=""
                          @endif
                          placeholder="Fess Type Name"
                        />
                      </div>
                      <div class="col-md-3 form-group mb-3">
                        <label for="lastName1">Langitude : </label>
                        <input
                          class="form-control uperletter"
                          id="latitude"
                          type="text"
                          name="latitude"
                          @if(!empty($areas))
                            @foreach($areas as $bus_stop)
                              value=" {{ $bus_stop->latitude }}"
                            @endforeach
                          @else
                            value=""
                          @endif
                          placeholder="latitude"
                        />
                      </div>
                      <div class="col-md-3 form-group mb-3">
                        <label for="lastName1">Langitude : </label>
                        <input
                          class="form-control uperletter"
                          id="langitude"
                          type="text"
                          name="langitude"
                          @if(!empty($areas))
                            @foreach($areas as $bus_stop)
                              value=" {{ $bus_stop->langitude }}"
                            @endforeach
                          @else
                            value=""
                          @endif
                          placeholder="latitude"
                        />
                      </div>
                      <div class="col-md-12">
                        <button class="btn btn-success">Save</button>

                        <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>

                        
                      </div>
                    </div>
                  </form>
                </div>
				<div class="col-md-6 form-group mb-3">
					<div class="card">
						<div class="card-body">
							<h4 class="card-title mb-3">Import Bus Stop Master</h4>
							<form action="{{ route('bus-stop.import') }}" method="POST" enctype="multipart/form-data">
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
						</div>
					</div>
				</div>
              </div>

              <div class="breadcrumb">
            <h1 class="me-2">List of Bus Stop Master saved Records</h1>
            @php
                      $i = 0;
                    @endphp
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="area_name">
                      <input type="hidden" name="column_names[]" value="bus_stop_name">
                      <input type="hidden" name="column_names[]" value="latitude">
                      <input type="hidden" name="column_names[]" value="langitude">
                      <input type="hidden" name="table_name" value="busstop">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div>
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>S no</th>
                            <th>Area Name</th>
                            <th>Bus Stop Name</th>
                            <th>Langitude</th>
                            <th>Langitude</th>
                            <th>Action</th>
                          <!-- <th>Action</th> -->
                        </tr>
                      </thead>
                      <tbody>

                        @if(!empty($busstops))
                        @foreach($busstops as $busstop)
                        <tr>
                        <td>{{++$i}}</td>
                          <td>{{$busstop->area_name}}</td>
                          <td>{{$busstop->bus_stop_name}}</td>
                          <td>{{$busstop->latitude}}</td>
                          <td>{{$busstop->langitude}}</td>
                          <!-- <td> 
                            <a class="btn btn-raised ripple btn-raised-warning m-1" href="{{ url('bus-stop-view') .'/'.$busstop->id}}">Edit</a>
                            <a class="btn btn-raised ripple btn-raised-danger m-1" href="{{ url('bus-stop-delete') .'/'.$busstop->id}}">Delete</a>
                          </td> -->

                          <td class='d-flex'>
                              <a class="btn btn-primary m-1" href="{{ url('bus-stop-view') .'/'.$busstop->id}}">Edit</a>
                                <!-- <form id="deleteForm" method="post" action="{{url('busstop-delete')}}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="busstop">
                                    <input type="hidden" name="delete_id" value="{{ $busstop->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="confirmDelete(event)">Delete</button>
                                </form> -->
                                <br>
                                <?php $a = "busstop"."-".$busstop->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-busstop').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
                            </td>
                        </tr>

                        @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                            <th>S no</th>
                            <th>Area Name</th>
                            <th>Bus Stop Name</th>
                            <th>Langitude</th>
                            <th>Langitude</th>
                            <th>Action</th>
                          <!-- <th>Action</th> -->
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
        <!-- Footer Start -->
        <div class="flex-grow-1"></div>
        <!-- fotter end -->
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
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
      var select = document.getElementById('area_name');
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
        $("#area_name").val("");
        $("#bus_stop_name").val("");
        $("#latitude").val("");
        $("#langitude").val(""); 
           
    });

})

</script>

@endsection 
