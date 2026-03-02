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
          <h2>Challan Management</h2>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  <h4 class="card-title mb-3 text-end"><a href="{{url('Challa')}}"><button class="btn btn-outline-primary" type="button">Create Challan</button></a></h4>
                  @php
                      $i = 0;
                    @endphp
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>No</th>
                            <th>Callan no.</th>
                            <th>Vehical no.</th>
                            <th>Vehical Type</th>
                            <th>Amount</th>
                            <th>Entry Date</th>
                            <th>Challan Date</th>
                            <th>Date of Detechion</th>
                            <th>Date of Generation</th>
                            <th>Reason</th>
                            <th>Remark</th>
                            <th>Action Teken</th>
                            
                        </tr>
                      </thead>
                      
                      <tbody>
                        @if(!empty($listofchallan))
                        @foreach($listofchallan as $listC)
                        <tr>
                            <td>{{++$i}}</td>
                            <td>{{$listC->Challan}}</td>
                            <td>{{$listC->vehicle}}</td>
                            <td>{{$listC->VType}}</td>
                            <td>{{$listC->Amount}}</td>
                            <td>{{$listC->EntryDate}}</td>
                            <td>{{$listC->ChallanDate}}</td>
                            <td>{{$listC->Detechion}}</td>
                            <td>{{$listC->Generation}}</td>
                            <td>{{$listC->Reason}}</td>
                            <td>{{$listC->Remark}}</td>   
                            <td>{{$listC->Action}}</td>    
                            <!-- <td> <a href="{{url('/customer/delete/')}}/{{$listC->customer_id}}">
                            <button  class="btn btn-danger">Delete</button>
                            </a>
                            <a href="{{url('/customer/edit/')}}/{{$listC->customer_id}}">
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
                            <th>Callan no.</th>
                            <th>Vehical no.</th>
                            <th>Vehical Type</th>
                            <th>Amount</th>
                            <th>Entry Date</th>
                            <th>Challan Date</th>
                            <th>Date of Detechion</th>
                            <th>Date of Generation</th>
                            <th>Reason</th>
                            <th>Remark</th>
                            <th>Action Teken</th>
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