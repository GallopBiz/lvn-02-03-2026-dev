@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
   <div class="breadcrumb">
      <h1 class="me-2">Edit Inquiry</h1>
   </div>
   <div class="separator-breadcrumb border-top"></div>
   <div class="row">
      <div class="col-md-12 mb-4">
         <div class="card text-start">
            <div class="card-body">
               <form method="post" action="{{url('update-inquiry')}}">
                @csrf
                <input type="hidden" name="update_id" value="{{$inquiry_data->id}}">
                  <div class="row">
                     <div class="col-md-6 form-group mb-3">
                        <label for="firstName1">Application For</label>
                          <div class="dropdown">
                            <select class="form-control" name="application_for">
                              <option value="cbse">CBSE</option>
                              <option value="rte">RTE</option>
                              <option value="saff">SAFF</option>
                            </select>
                          </div>
                     </div>
                     <div class="col-md-6 form-group mb-3">
                        <label for="lastName1">DOB</label>
                        <input class="form-control" name="dob" id="lastName1" type="date" placeholder="Enter your last name" value="{{ date('Y-m-d', strtotime($inquiry_data->date_of_birth)) }}" />
                     </div>
                     <div class="col-md-6 form-group mb-3">
                        <label for="exampleInputEmail1">Class Name</label>
                        <input class="form-control" name="class_name" value="{{$inquiry_data->class_name}}" id="exampleInputEmail1" type="text" placeholder="Class Name" />
                     </div>
                     <div class="col-md-6 form-group mb-3">
                        <label for="phone">Student Name</label>
                        <input class="form-control" name="student_name" value="{{$inquiry_data->class_name}}" id="phone" placeholder="Student Name"/>
                     </div>
                     <div class="col-md-6 form-group mb-3">
                        <label for="credit1">Session Name</label>
                        <input class="form-control" name="session_name" value="{{$inquiry_data->session_name}}" id="credit1" placeholder="Session Name"/>
                     </div>
                     <div class="col-md-6 form-group mb-3">
                        <label for="website">Phone Number</label>
                        <input class="form-control" name="phone_number" value="{{$inquiry_data->phone_number}}" id="website" placeholder="Phone Number"/>
                     </div>
                     <div class="col-md-6 form-group mb-3">
                        <label for="picker2">Mobile Number</label>
                        <input class="form-control" name="mobile_number" value="{{$inquiry_data->mobile_number}}" type="text" id="picker2" />
                     </div>
                     <div class="col-md-12">
                        <button class="btn btn-primary">Update</button>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
   <!-- end of main-content -->
</div>
@endsection