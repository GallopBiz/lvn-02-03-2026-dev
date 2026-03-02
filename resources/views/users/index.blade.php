@extends('layouts.app')
@section('main-container')
    <div class="main-content pt-4">
        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
          <div class="breadcrumb">
          <h2>Users Management</h2>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  @role('Admin') 
                  <h4 class="card-title mb-3 text-end"><a href="{{route('users.create')}}"><button class="btn btn-outline-primary" type="button">Create New User</button></a></h4>
                  @endrole
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>No</th>
                            <th>Student Name</th>
                            <th>Form Number</th>
                            <th>Roles</th>
                            <th width="280px">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $i=0; ?>
                        @if(!empty($data))
                          @foreach($data as $key => $user)
                          <tr>
                              <td>{{ ++$i }}</td>
                              <td>{{ $user->student_name }}</td>
                              <td>{{ $user->form_number }}</td>
                              <td>
                                  @if(!empty($user->getRoleNames()))
                                      @foreach($user->getRoleNames() as $v)
                                          <span class="badge rounded-pill bg-dark">{{ $v }}</span>
                                      @endforeach
                                  @endif
                              </td>
                              <td>
                                  <!-- <a class="btn btn-info" href="{{ route('users.show',$user->id) }}">Show</a> -->
                                  <a class="btn btn-primary" href="{{ route('users.edit',$user->id) }}">Edit</a>
                                  @role('Admin')                                    
                                    {!! Form::open(['method' => 'DELETE','route' => ['users.destroy', $user->id],'style'=>'display:inline']) !!}
                                    {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                                    {!! Form::close() !!}
                                  @endrole
                              </td>
                          </tr>
                          @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                        <th>No</th>
                        <th>Student Name</th>
                        <th>Form Number</th>
                        <th>Roles</th>
                         <th width="280px">Action</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- end of main-content -->
        </div>

@endsection
