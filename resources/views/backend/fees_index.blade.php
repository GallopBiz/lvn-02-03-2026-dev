@extends('backend.layouts.main')

@section('main-container')

<style>

.uperletter{
  text-transform: capitalize;
}  


    /* .capitalize-text {
        text-transform: capitalize;
    } */


</style>
  <!-- <div class="main-content"> -->


          <!-- ============ Body content start ============= -->
        <div class="main-content">
          <div class="breadcrumb">
            <h1>Fess Type Master</h1>
            <ul>
              <li><a href="href">Form</a></li>
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
                  @if(!empty($feestype))
                  <form id="progress-form" class="p-4 progress-form" action="{{url('feestype-update')}}"  novalidate method="post">
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
                  <form id="progress-form" class="p-4 progress-form" action="{{url('save-fees-type-master')}}"  novalidate method="post">
                  @endif
                  {{ csrf_field() }}
                    <div class="row">
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    
                       <div class="col-md-3 form-group mb-3 uperletter ">
                        <label for="firstName1">Fess Type Name : </label>
                        <input style="text-transform: capitalize;"
                          class="form-control uperletter"
                          id="feestype"
                          name="feestype"
                          type="text"
                          @if(!empty($role))
                            @foreach($role as $roles)
                              value=" {{ $roles->feestype }}"
                            @endforeach
                          @else
                            value=""
                          @endif

                          placeholder="Fess Type Name"
                        />
                      </div> 



                      {{-- <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Fees Type Name :</label>
                        <input
                            class="form-control uperletter"
                            id="feestype"
                            name="feestype"
                            type="text"
                            @if(!empty($role))
                                @foreach($role as $roles)
                                    value="{{ $roles->feestype }}"
                                @endforeach
                            @else
                                value=""
                            @endif
                            placeholder="Fees Type Name"
                            style="text-transform: capitalize;" <!-- Apply the style directly -->
                        />
                    </div> --}}
                    



                      <div class="col-md-3 form-group mb-3 uperletter">
                        <label for="lastName1">Remarks : </label>
                        <input
                          class="form-control uperletter"
                          id="lastName1"
                          name="remarks"
                          type="text"
                          @if(!empty($role))
                            @foreach($role as $roles)
                              value=" {{ $roles->remarks }}"
                            @endforeach
                          @else
                            value=""
                          @endif
                          placeholder="Remarks"
                        />
                      </div>

                      <div class="col-md-12">
                        <button class="btn btn-success">Save</button>
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
                  <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="fees_type">
                      <input type="hidden" name="column_names[]" value="remark">
                      <input type="hidden" name="column_names[]" value="session">
                      <input type="hidden" name="table_name" value="fees_types_master">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div>
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                          <th>SNO</th>
                          <th>Name</th>
                          <th>Remarks</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        
                        @if(!empty($feestype))
                        @foreach ($feestype as $feestypes)
                        <tr>
                            <td>{{ $feestypes->id }}</td>
                            <td>{{ $feestypes->feestype }}</td>
                            <td>{{ $feestypes->remarks }}</td>
                            <td class="d-flex"> 
                              <!-- <a class="btn btn-raised ripple btn-raised-warning m-1" href="{{ url('edit') .'/'.$feestypes->id}}">Edit</a> -->
                              <a class="btn btn-primary m-1" href="{{ url('edit') .'/'.$feestypes->id}}">Edit</a>
                                <form id="deleteForm" method="post" action="{{url('feestype-delete')}}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="feestypes">
                                    <input type="hidden" name="delete_id" value="{{ $feestypes->id }}">
                                    <button type="submit" class="btn btn-raised ripple btn-danger m-1">Delete</button>
                                </form>
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
                            <th>Name</th>
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
           
            
            
      <!-- </div> -->
          <!-- end of main-content -->
          <!-- Footer Start -->
      <div class="flex-grow-1"></div>
          <!-- fotter end -->
        <!-- </div> -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    function confirmDelete(event) {
        event.preventDefault(); // Prevents the default form submission

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
                // If the user clicks on "Yes, delete it!", manually submit the form
                document.getElementById('deleteForm').submit();
            }
        });
    }
</script>
@endsection 
