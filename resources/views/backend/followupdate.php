@extends('backend.layouts.main')

@section('main-container')
​
<div class="main-content pt-4">
          <div class="breadcrumb">
            <h1 class="me-2">Follow Up Date</h1>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  <h4 class="card-title mb-3 text-end">
                    <a href="{{url('admin-enquiryform')}}"><button class="btn btn-outline-primary" type="button">Create Enquiry</button></a>
                  </h4>
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                         <!--  <th>Application For</th> -->
                          <th>Class Name</th>                                               
                          <th>Student Name</th>
                          <th>Session Name</th>
                          <th>DOB</th> 
                          <th>Phone Number</th>
                          <th>Mobile Number</th>
                          <th>Mode</th>
                          <th>Created At</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(!empty($all_inquiry))
                        @foreach($all_inquiry as $each_inq)
                        <tr>
                         <!--  <td>{{$each_inq->application_for}}</td> -->
                          
                          <td>{{$each_inq->class_name}}</td>
                          <td>{{$each_inq->student_name}}</td>
                          <td>{{$each_inq->session_name}}</td>
                          <td>{{$each_inq->date_of_birth}}</td>
                          <td>{{$each_inq->phone_number}}</td>
                          <td>{{$each_inq->mobile_number}}</td>
                          <td>@if($each_inq->inq_mode=='off') offline @elseif($each_inq->inq_mode=='on') Online @endif</td>
                          <td>{{ date('d-m-Y', strtotime($each_inq->created_at)) }}</td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-primary dropdown-toggle" id="dropdownMenuButton" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Actions
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"><a class="dropdown-item" href="">Edit</a>
                              </div>
                            </div>
                          </td>
                        </tr>
                        @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <!-- <th>Application For</th> -->
                          
                          <th>Class Name</th>
                          <th>Student Name</th>
                          <th>Session Name</th>
                          <th>DOB</th>
                          <th>Phone Number</th>
                          <th>Mobile Number</th>
                          <th>Mode</th>
                          <th>Created At</th>
                          <th>Status</th>
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
​
@endsection 