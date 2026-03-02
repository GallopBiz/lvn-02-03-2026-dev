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
            <h1>Fess Type Master</h1>
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
                  @if(!empty($fees_type))
                  <form id="progress-form" class="p-4 progress-form" action="{{url('store')}}"  novalidate method="post">
                  <input type="hidden" 
                    @if(!empty($role))
                      @foreach($role as $roles)
                        value=" {{ $roles->id }}"
                      @endforeach
                    @else
                      value=""
                    @endif
                    name="id" 
                  >
                  @else
                  <form id="progress-form" class="p-4 progress-form" action="{{url('save-fees-types-master')}}"  novalidate method="post">
                  @endif
                  {{ csrf_field() }}
                    <div class="row">

                      <div class="col-md-6 form-group mb-3">
                        <label for="firstName1">Fess Type Name : </label>
                        <input
                          class="form-control uperletter"
                          id="fees_type"
                          name="fees_type"
                          type="text" placeholder="Fess Type Name" value="{{ old('fees_type') }}"  
                        />
                        @if($errors->has('fees_type'))
                          <span class="text text-danger">
                                {{ $errors->first('fees_type') }}
                          </span>
                        @endif
                      </div>
                      <div class="col-md-6 form-group mb-3">
                        <label for="lastName1">Percentage number / Remark : </label>
                        <input
                          class="form-control uperletter"
                          id="lastName1"
                          name="remark"
                          type="text"
                          placeholder="Percentage Number / Remark"
                        />
                      </div>
                      <div class="col-md-6 form-group mb-3">
                        <label for="session">Session : </label>
                        <!-- <select id="session" class="form-control" name="session" autocomplete="" required>
                            <option disabled selected>Please select</option>
                            @foreach(config('global.session_name') as $each)
                            <option value="{{$each}}">{{$each}}</option>
                            @endforeach
                        </select> -->
                        <input type="text" readonly id="session" class="form-control" value="" name="session">
                        @if($errors->has('session'))
                          <span class="text text-danger">
                                {{ $errors->first('session') }}
                          </span>
                        @endif
                      </div>
                      </div>

                      <div class="col-md-12">
                        <button class="btn btn-primary">Save</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
              <div class="breadcrumb">
            <h1 class="me-2">List of Fees saved Records</h1>
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
                          <th>SNO</th>
                          <th>Fees Type Title</th>
                          <th>Session</th>
                          <th>Remarks</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        
                        @if(!empty($fees_types_masterArr))
                        @foreach ($fees_types_masterArr as $fees_type)
                        <tr>
                            <td>{{ $fees_type->id }}</td>
                            <td>{{ $fees_type->fees_type }}</td>
                            <td>{{ $fees_type->session }}</td>
                            <td>{{ $fees_type->remark }}</td>
                            <td class="d-flex">  
                              <a href="{{ url('fees-types-master-edit') .'/'.$fees_type->id}}"><button type="submit" class="btn btn-primary m-1">Edit</button></a>
                                <!-- <form method="post" action="{{url('fees-types-master-delete')}}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="fees_types_master">
                                    <input type="hidden" name="delete_id" value="{{ $fees_type->id }}">
                                    <button type="submit" class="btn btn-primary btn-sm removeItem">Delete</button>
                                </form> -->
                                <?php $a = "fees_types_master"."-".$fees_type->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('fees-type-delete').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>SNO</th>
                          <th>Fees Type Title</th>
                          <th>Session</th>
                          <th>Remarks</th>
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
          <!-- Footer Start -->
          <div class="flex-grow-1"></div>
          <!-- fotter end -->
        </div>

<!-- <link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/sweetalert2.min.css" /> -->

<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script> -->
<!-- <script src="{{url('assets/backend')}}/js/plugins/sweetalert2.min.js"></script>
<script src="{{url('assets/backend')}}/js/scripts/sweetalert.script.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
    $(document).ready(function () {
      setTimeout(function() {
        var year = $("#year").val()
        $("#session").val(year);
      }, 1000);
    });
    
</script>

@endsection 
