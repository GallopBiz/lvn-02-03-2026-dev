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
          <h2>Party Master </h2>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
          <div class="col-md-12 mb-4">
              <div class="form_section1_div">
                <form class="needs-validation" novalidate="novalidate" method="post" action="{{url('rto-paper-filter')}}">
                    @csrf
                    <div class="row">
                       <div class="col-md-2 form-group mb-3">
                          <label for="firstName1">RTO Paper Name :</label>
                          <select id="RTO_Paper_Name" class="form-control" name="RTO_Paper_Name" autocomplete="shipping address-level1" required>
                                <option>-- Select -- </option>
                                <option value="option 1" >option 1</option>
                                <option value="option 2" >option 2</option>
                                <option value="option 3" >option 3</option>
                          </select>
                       </div>
                       <div class="col-md-2 form-group mb-3">
                          <label for="firstName1">Vehicle No. :</label>
                          <select id="class" class="form-control" name="Vehicle_No." autocomplete="shipping address-level1" required>
                                <option> -- Select -- </option>
                                <option value="op1">option 1</option>
                              <option value="op2">option 2</option>
                              <option value="op3">option 3</option>
                          </select>
                       </div>
                        <div class="col-md-6">
                          <br>
                            <button class="btn btn-primary">Search</button>
                        </div>
                    </div>
                </form>
            </div>
            </div>
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  <h4 class="card-title mb-3 text-end"><a href="{{url('RTOpaper')}}"><button class="btn btn-outline-primary" type="button">Create RTO Paper</button></a></h4>
                  @php
                      $i = 0;
                    @endphp
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>No</th>
                            <th>Renewal Date</th>
                            <th>Next Renewal Date</th>
                            <th>Registration Date</th>
                            <th>Vehicle</th>
                            <th>Transfer date</th>
                            <th>RTO paper Name</th>
                            <th>Document</th>
                            <th>Reminder_Frequency</th>
                            <th>	image</th>
                            <!-- <th>GST no.</th> -->
                            <th>Action Teken</th>
                            
                        </tr>
                      </thead>
                      
                      <tbody>
                        @if(!empty($listrtopaper))
                        @foreach($listrtopaper as $listrto)
                        <tr>
                            <td>{{++$i}}</td>
                            <td>{{$listrto->Renewal_Date}}</td>
                            <td>{{$listrto->Next_Renewal_Date}}</td>
                            <td>{{$listrto->Registration_Date}}</td>
                            <td>{{$listrto->Vehicle}}</td>
                            <td>{{$listrto->Transfer_date}}</td>
                            <td>{{$listrto->RTO_paper_Name}}</td>
                            <td>{{$listrto->Document}}</td>
                            <td>{{$listrto->Reminder_Frequency}}</td>
                            <td>{{$listrto->	image}}</td>
                            <!-- <td>{{$listrto->GST_no_}}</td> -->
                            <!-- <td>{{$listrto->Remark}}</td>    -->
                            <!-- <td>{{$listrto->Action}}</td>     -->
                            <!-- <td> <a href="{{url('rtopaper-delete')}}/{{$listrto->id}}">
                            <button  class="btn btn-danger">Delete</button>
                            </a>
                            <a  href="{{ url('rtopaper-view') .'/'.$listrto->id}}">
                            <button  class="btn btn-success">Edit</button>
                            </a></td> -->
                            <td>
                              <a class="btn btn-primary m-1" href="{{ url('rtopaper-view') .'/'.$listrto->id}}">Edit</a>
                                <form method="post" action="{{url('rtopaper-delete')}}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="rto_paper">
                                    <input type="hidden" name="delete_id" value="{{ $listrto->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                      <th>No</th>
                            <th>Renewal Date</th>
                            <th>Next Renewal Date</th>
                            <th>Registration Date</th>
                            <th>Vehicle</th>
                            <th>Transfer date</th>
                            <th>RTO paper Name</th>
                            <th>Document</th>
                            <th>Reminder_Frequency</th>
                            <th>	image</th>
                            <!-- <th>GST no.</th> -->
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