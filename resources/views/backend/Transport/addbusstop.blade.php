@extends('backend.layouts.main')

@section('main-container')
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
                  <form id="progress-form" class="p-4 progress-form" action="{{url('save-fees-type-master')}}"  novalidate method="post">
                  @endif
                  {{ csrf_field() }}
                    <div class="row">
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    
                      <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Fess Type Name : </label>
                        <input
                          class="form-control"
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
                      <div class="col-md-3 form-group mb-3">
                        <label for="lastName1">Remarks : </label>
                        <input
                          class="form-control"
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
                            <td> 
                              <a class="btn btn-raised ripple btn-raised-warning m-1" href="{{ url('view') .'/'.$feestypes->id}}">Edit</a>
                              <a class="btn btn-raised ripple btn-raised-danger m-1" href="{{ url('delete') .'/'.$feestypes->id}}">Delete</a>
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

@endsection 
