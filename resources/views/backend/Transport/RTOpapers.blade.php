@extends('backend.layouts.main')
@section('main-container')

<style>

.uperletter{
  text-transform: capitalize;
} 

</style>
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Party Master Registration</h1>
        </div>
        
        @if(!empty($listrtopaper))
                  <form action="{{url('rtopaper-store')}}" novalidate enctype='multipart/form-data' method="post">
                  <input type="hidden" 
                    @if(!empty($listrtopaper))
                      @foreach($listrtopaper as $listrto)
                        value=" {{ $listrto->id }}"
                      @endforeach
                    @else
                      value=""
                    @endif
                    name="id"
                  >
                  @else
                  <!-- <form id="progress-form" class="p-4 progress-form" action="{{url('bus-stop-create')}}"  novalidate method="post"> -->
                  <form action="{{url('save-rto-paper')}}" enctype='multipart/form-data' method="post">
                  @endif
        
            @csrf
            <div class="row">
            <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Renewal Date:</label>
                    <input name="Renewal_Date" class="form-control" id="Challan" type="date" 
                    @if(!empty($listrtopaper))
                      @foreach($listrtopaper as $listrto)
                        value=" {{ $listrto->Renewal_Date }}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Next Renewal Date:</label>
                    <input name="Next_Renewal_Date" class="form-control" id="Challan" type="date" 
                    @if(!empty($listrtopaper))
                      @foreach($listrtopaper as $listrto)
                        value=" {{ $listrto->Next_Renewal_Date }}"
                      @endforeach
                    @else
                      value=""
                    @endif
                    placeholder="Next_Renewal_Date" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Registration Date:</label>
                    <input name="Registration_Date" class="form-control" id="Challan" type="date" 
                    @if(!empty($listrtopaper))
                      @foreach($listrtopaper as $listrto)
                        value=" {{ $listrto->Registration_Date }}"
                      @endforeach
                    @else
                      value=""
                    @endif
                    placeholder="Registration_Date" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Vehicle NO.</label>
                    <select id="Text" class="form-control" name="Vehicle" autocomplete="shipping address-level1" required>
                    @if(!empty($listrtopaper))
                            @foreach($listrtopaper as $listrto)
                              value=" {{ $listrto->Vehicle }}"
                              <option value=" {{ $listrto->Vehicle }}">{{ $listrto->Vehicle }}</option>
                            @endforeach
                          @else
                          <option>select option</option>
                          @endif
                              <option value="op1">option 1</option>
                              <option value="op2">option 2</option>
                              <option value="op3">option 3</option>
                           </select>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">RTO Paper Name</label>
                    <select id="Text" class="form-control uperletter" name="RTO_Paper_Name" autocomplete="shipping address-level1" required>
                    @if(!empty($listrtopaper))
                            @foreach($listrtopaper as $listrto)
                              value=" {{ $listrto->RTO_Paper_Name }}"
                              <option value=" {{ $listrto->RTO_Paper_Name }}">{{ $listrto->Vehicle }}</option>
                            @endforeach
                          @else
                          <option>select option</option>
                          @endif
                              <option value="op1">option 1</option>
                              <option value="op2">option 2</option>
                              <option value="op3">option 3</option>
                           </select>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Transfer date </label>
                    <input name="Transfer_date" class="form-control" id="Amount" type="date"
                    @if(!empty($listrtopaper))
                      @foreach($listrtopaper as $listrto)
                        value=" {{ $listrto->Transfer_date }}"
                      @endforeach
                    @else
                      value=""
                    @endif
                        placeholder="Transfer date" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Document No. </label>
                    <input name="Document" class="form-control" id="Amount" type="text" 
                    @if(!empty($listrtopaper))
                      @foreach($listrtopaper as $listrto)
                        value=" {{ $listrto->Document}}"
                      @endforeach
                    @else
                      value=""
                    @endif
                        placeholder="Document" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Reminder Frequency </label>
                    <input name="Reminder_Frequency" class="form-control" id="Amount" type="text"
                    @if(!empty($listrtopaper))
                      @foreach($listrtopaper as $listrto)
                        value=" {{ $listrto->Reminder_Frequency}}"
                      @endforeach
                    @else
                      value=""
                    @endif
                        placeholder="Reminder Frequency" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Upload Document</label>
                    <input name="image" class="form-control" 
                    @if(!empty($listrtopaper))
                      @foreach($listrtopaper as $listrto)
                        value=" {{ $listrto->image}}"
                      @endforeach
                    @else
                      value=""
                    @endif
                    id="Amount" type="file" />
                </div> 
                <div class="col-md-42">
                    <button class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
    <!-- end of main-content -->
</div>
@endsection