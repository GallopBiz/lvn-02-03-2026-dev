@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
    <!-- <h2>hyy</h2> -->
    @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
          <div class="breadcrumb">
          <h2>Vehical Management</h2>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  <h4 class="card-title mb-3 text-end"><a href="{{url('addvehical')}}"><button class="btn btn-outline-primary" type="button">Create Vehical</button></a></h4>
                  @php
                      $i = 0;
                    @endphp
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>No</th>
                            <th>Call no.</th>
                            <th>Vehical no.</th>
                            <th>Vehical Type</th>
                            <th>Services</th>
                            <th>Model</th>
                            <th>Purchase Date</th>
                            <th>Capacity</th>
                            <th>Standard Avg.</th>
                            <th>IMEI</th>
                            <th>Machine</th>
                            <th>Student Related</th>
                            <th>Scrapped</th>
                            <th>Action</th>
                        </tr>
                      </thead>
                      
                      <tbody>
                        @if(!empty($Vehicallist))
                        @foreach($Vehicallist as $listV)
                        <tr>
                        <td>{{++$i}}</td>
                            <td>{{$listV->callno}}</td>
                            <td>{{$listV->vehicelno}}</td>
                            <td>{{$listV->vehiceltype}}</td>
                            <td>{{$listV->nature}}</td>
                            <td>{{$listV->model}}</td>
                            <td>{{$listV->purchase}}</td>
                            <td>{{$listV->capacity}}</td>
                            <td>{{$listV->standard}}</td>
                            <td>{{$listV->IMEI}}</td>
                            <td>{{$listV->machine}}</td>    
                            <td>@if($listV->studentrelated==true)
                                <span style="color:green">For Student</span>
                                <!-- @else
                                <span style="color:red">Null</span> -->
                                @endif
                            </td>
                            <td>@if($listV->scrapped==true)
                                <span style="color:green">Scrapped</span>
                                <!-- @else
                                <span style="color:red">Null</span> -->
                                @endif
                            </td>
                            <!-- <td> <a href="{{url('/customer/delete/')}}/{{$listV->customer_id}}">
                            <button  class="btn btn-danger">Delete</button>
                            </a>
                            <a href="{{url('/customer/edit/')}}/{{$listV->customer_id}}">
                            <button  class="btn btn-success">Edit</button>
                            </a></td> -->
                        </tr>
                    @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                      <th>No</th>
                            <th>Call no.</th>
                            <th>Vehical no.</th>
                            <th>Vehical Type</th>
                            <th>Services</th>
                            <th>Model</th>
                            <th>Purchase Date</th>
                            <th>Capacity</th>
                            <th>Standard Avg.</th>
                            <th>IMEI</th>
                            <th>Machine</th>
                            <th>Student Related</th>
                            <th>Scrapped</th>
                            <th>Action</th>
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