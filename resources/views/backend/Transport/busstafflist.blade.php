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
                  <h4 class="card-title mb-3 text-end"><a href="{{url('busstaff')}}"><button class="btn btn-outline-primary" type="button">Create Vehical Member</button></a></h4>
                  @php
                      $i = 0;
                    @endphp
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                      <tr>
                            <th>No</th>
                            <th>Role</th>
                            <th>Employee</th>
                            <th>Current Address</th>
                            <th>Parmanent Address</th>
                            <th>SSSMID</th>
                            <th>Mobile no.</th>
                            <th>License no</th>
                            <th>License Exp.</th>
                            <th>License Issue</th>
                            <th>Aadhar no.</th>
                            <th>Pen Cart No.</th>
                            <th>Joining Date</th>
                            <th>Leaving Date</th>
                            <th>Remarks</th>
                            <th>Call no.</th>
                            <th>Offical no.</th>
                            <th>HealthStatus</th>
                        </tr>
                      </thead>
                      
                      <tbody>
                      @if(!empty($BusStaff))
                        @foreach($BusStaff as $cum)
                        <tr>
                        <td>{{++$i}}</td>
                        <td>{{$cum->Role}}</td>
                            <td>{{$cum->Ename}}</td>
                            <td>{{$cum->Cadd}}</td>
                            <td>{{$cum->Padd}}</td>
                            <td>{{$cum->SSSMID}}</td>
                            <td>{{$cum->Mobile}}</td>
                            <td>{{$cum->LNO}}</td>
                            <td>{{$cum->LE}}</td>
                            <td>{{$cum->LI}}</td>
                            <td>{{$cum->Aadhar}}</td>    
                            <td>{{$cum->Vid}}</td>
                            <td>{{$cum->JD}}</td>
                            <td>{{$cum->LD}}</td>
                            <td>{{$cum->Remarks}}</td>
                            <td>{{$cum->Application}}</td>
                            <td>{{$cum->OfMobile}}</td>
                            <!-- <td>{{$cum->HealthStatus}}</td> -->
                            <td>@if($cum->HealthStatus==true)
                                <span style="color:green">Fine</span>
                                <!-- @else
                                <span style="color:red">Null</span> -->
                                @endif
                            </td>
                            <!-- <td> <a href="{{url('/customer/delete/')}}/{{$cum->customer_id}}">
                            <button  class="btn btn-danger">Delete</button>
                            </a>
                            <a href="{{url('/customer/edit/')}}/{{$cum->customer_id}}">
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
                            <th>Role</th>
                            <th>Employee</th>
                            <th>Current Address</th>
                            <th>Parmanent Address</th>
                            <th>SSSMID</th>
                            <th>Mobile no.</th>
                            <th>License no</th>
                            <th>License Exp.</th>
                            <th>License Issue</th>
                            <th>Aadhar no.</th>
                            <th>Pen Cart No.</th>
                            <th>Joining Date</th>
                            <th>Leaving Date</th>
                            <th>Remarks</th>
                            <th>Call no.</th>
                            <th>Offical no.</th>
                            <th>HealthStatus</th>
                      
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