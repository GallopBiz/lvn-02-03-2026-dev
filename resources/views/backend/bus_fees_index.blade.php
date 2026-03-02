@extends('backend.layouts.main')

@section('main-container')
<style>
      input {
        position: relative;
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        background-position: right;
        background-size: auto;
        cursor: pointer;
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        top: 7px;
        width: auto;
    }

    .error-msg {
        color: red;
        margin-top: 5px;
        font-size: 12px;
    }

    .uperletter{
  text-transform: capitalize;
}  


</style>
  <div class="main-content">


          <!-- ============ Body content start ============= -->
        <div class="main-content">
          <div class="breadcrumb">
            <h1>Bus Fees Type</h1>
            <ul>
              <!-- <li><a href="href">Form</a></li> -->
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
                   
                  @if(!empty($bus_feess))
                  <form id="progress-form" class="p-4 progress-form" action="{{url('bus-store')}}"  novalidate method="post">
                  <input type="hidden" 
                    @if(!empty($bus_feess))
                      @foreach($bus_feess as $bus_fees)
                        value=" {{ $bus_fees->id }}"
                      @endforeach
                    @else
                      value=""
                    @endif
                    name="id"
                  >
                  @else
                  <form id="progress-form" onsubmit="return validateForm()" class="p-4 progress-form" action="{{url('save-bus-fees-type')}}"  novalidate method="post">
                  @endif
                    <div class="row">
                      {{ csrf_field() }}
                      <!-- <div class="row"> -->
                      <div class="col-md-3 form-group mb-3">
                        <label for="picker1">Select Session : </label>
                        <!-- <select class="form-control" name="select_batch">
                          <option value="" disabled selected>Please Select</option>
                          @foreach(config('global.session_name') as $each)
                            @if($each == '2023-2024')
                              <option selected value="{{$each}}">{{$each}}</option>
                            @else
                              <option value="{{$each}}">{{$each}}</option>
                            @endif
                          @endforeach
                        </select> -->
                        <input type="text" readonly id="select_batch" class="form-control" value="" name="select_batch">
                        <p class="error-msg" id="session-error"></p>
                      </div>
                      <div class="col-md-3 form-group mb-3 uperletter ">
                        <label for="firstName1">Bus Fees Type Name : </label>
                        <input
                          class="form-control uperletter"
                          id="firstName1"
                          name="busfeestypename"
                          type="text"
                          @if(!empty($bus_feess))
                            @foreach($bus_feess as $bus_fees)
                              value=" {{ $bus_fees->busfeestypename }}"
                            @endforeach
                          @else
                            value=""
                          @endif
                          placeholder="Fess Type Name"
                        />
                        <p class="error-msg" id="fees-error"></p>
                      </div>
                      
                      {{-- <div class="col-md-3 form-group mb-3">
                        <label for="lastName1">Date : </label>
                        <input
                          class="form-control"
                          id="lastName1"
                          type="date"
                          name="date"
                           @if(!empty($bus_fees) && isset($bus_fees->date)) value="{{$bus_fees->date}}" @else value='' @endif
                          placeholder="date"
                        />
                      </div> --}}


                      <div class="col-md-3 form-group mb-3">
                        <label for="lastName1">Date : </label>
                        <input
                            class="form-control"
                            id="lastName1"
                            type="date"
                            name="date"
                            value="<?php if (!empty($bus_fees->date)) {echo $bus_fees->date;} ?>"
                            placeholder="dd-mm-yyyy"
                        />
                        <p class="error-msg" id="date-error"></p>
                    </div>
                  

                      <div class="col-md-1 form-group mb-3">
                        <label for="amount">Amount : </label>
                        <input
                          class="form-control"
                          id="amount"
                          type="text"
                          name="amount"
                           @if(!empty($bus_fees) && isset($bus_fees->amount)) value="{{ $bus_fees->amount }}" @else value='' @endif
                          placeholder="amount"
                        />
                        <p class="error-msg" id="amount-error"></p>
                      </div>
                      <!-- <div class="col-md-2 form-group mb-3">
                        <label for="picker1"> <br></label>
                        <select class="form-control" name="select_option">
                          <option selected disabled>Select Batch</option>
                          <option value="Option 1" @if(!empty($bus_fees) && $bus_fees->select_option=="Option 1") selected @endif>Option 1</option>
                          <option value="Option 2" @if(!empty($bus_fees) && $bus_fees->select_option=="Option 2") selected @endif>Option 2</option>
                          <option value="Option 3" @if(!empty($bus_fees) && $bus_fees->select_option=="Option 3") selected @endif>Option 3</option>
                        </select>
                      </div> -->
                      <div class="col-md-12">
                        <button class="btn btn-primary">@if(!empty($bus_fees)) Update @else Save @endif </button>

                        @if(!empty($bus_fees))<button class="btn btn-primary" onclick="history.back()" > Back </button>@endif 
                        
                      </div>
                    </div>
                  </form>
                </div>
              </div>

              <div class="breadcrumb">
            @if(empty($bus_feess))<h1 class="me-2">List of Bus Fees saved Records</h1> @endif
          </div>
          <div class="separator-breadcrumb border-top"></div>
          @if(empty($bus_feess))
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  <!-- <h4 class="card-title mb-3 text-end"><a href="{{url('add-student-registrations')}}"><button class="btn btn-outline-primary" type="button">Create Registration</button></a></h4> -->
                  <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="select_batch">
                      <input type="hidden" name="column_names[]" value="busfeestypename">
                      <input type="hidden" name="column_names[]" value="date">
                      <input type="hidden" name="column_names[]" value="amount">
                      <input type="hidden" name="table_name" value="busfees">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div>
                  @php
                      $i = 0;
                    @endphp
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                          <th>SNO</th>
                          <th>Select Batch</th>
                          <th>Bus Fees Type Name</th>
                          <th>Date</th>
                          <th>Amount</th>
                          <th>Action</th>
                          <!-- <th>Action</th> -->
                        </tr>
                      </thead>
                      <tbody>
                        @if(!empty($busfees))
                        @foreach($busfees as $busfeess)
                        <tr>
                        <td>{{++$i}}</td>
                          <td>{{$busfeess->select_batch}}</td>
                          <td>{{$busfeess->busfeestypename}}</td>
                          <td>{{date("d-m-Y",strtotime($busfeess->date))}}</td>

                          <td>{{$busfeess->amount}}</td>
                          <!-- <td> 
                            <a class="btn btn-raised ripple btn-raised-warning m-1" href="{{ url('bus-fees-master-edit') .'/'.$busfeess->id}}">Edit</a>
                            <a class="btn btn-raised ripple btn-raised-danger m-1" href="{{ url('bus-fees-master-delete') .'/'.$busfeess->id}}">Delete</a>
                          </td> -->
                          <td class='d-flex'>

                            <a href="{{ url('bus-fees-master-edit') .'/'.$busfeess->id}}"><button type="submit" class="btn btn-primary m-1">Edit</button></a>

                            <!-- <form method="post" action="{{url('Busfees-delete')}}">
                                @csrf
                                <input type="hidden" name="table_name" value="busfees">
                                <input type="hidden" name="delete_id" value="{{ $busfeess->id }}">
                                <button type="submit" class="btn btn-raised ripple btn-danger m-1">Delete</button>
                            </form> -->
                            <?php $a = "busfees"."-".$busfeess->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('Busfees-delete').'/'.$a}}" onclick="confirmDelete(event)">delete</a>
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
                          <th>Select Batch</th>
                          <th>Bus Fees Type Name</th>
                          <th>Date</th>
                          <th>Amount</th>
                          <th>Action</th>
                          <!-- <th>Action</th> -->
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endif
        </div>
           
            
            
        </div>
        <!-- end of main-content -->
        <!-- Footer Start -->
        <div class="flex-grow-1"></div>
        <!-- fotter end -->
    </div>


<!-- <link rel="stylesheet" href="{//{url('assets/backend')}}/css/plugins/sweetalert2.min.css" /> -->


<!-- Include datepicker.js library -->
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datepicker/1.0.10/datepicker.min.css"> --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/datepicker/1.0.10/datepicker.min.js"></script> --}}


<!-- Include jQuery library -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include jQuery UI library -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
  <!-- <script src="{{url('assets/backend')}}/js/plugins/sweetalert2.min.js"></script> -->
  <!-- <script src="{{url('assets/backend')}}/js/scripts/sweetalert.script.min.js"></script> -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

  <script>
    function validateForm() {
        var selectBatch = document.getElementById('select_batch').value;
        var busFeeTypeName = document.getElementById('firstName1').value;
        var date = document.getElementById('lastName1').value;
        var amount = document.getElementById('amount').value;
        
        // Reset previous error messages
        document.getElementById('session-error').innerText = '';
        document.getElementById('fees-error').innerText = '';
        document.getElementById('date-error').innerText = '';
        document.getElementById('amount-error').innerText = '';

        var isValid = true;

        if (selectBatch === '') {
            document.getElementById('session-error').innerText = 'Please select a session.';
            isValid = false;
        }

        if (busFeeTypeName === '') {
            document.getElementById('fees-error').innerText = 'Please enter the fees type name.';
            isValid = false;
        }

        if (date === '') {
            document.getElementById('date-error').innerText = 'Please enter a date.';
            isValid = false;
        }

        if (amount === '') {
            document.getElementById('amount-error').innerText = 'Please enter the amount.';
            isValid = false;
        }

        return isValid;
    }
</script>



<script>
$(document).ready(function () {
  setTimeout(function() {
    var year = $("#year").val()
    $("#select_batch").val(year);
  }, 1000);
});
$(function () {

  $("#lastName1").datepicker({
  dateFormat: 'dd/mm/y', //check change
  changeMonth: true,
  changeYear: true
});

  // var jQuery3 = $.noConflict(true);
  //       $("#lastName1").datepicker({
  //           dateFormat: 'dd-mm-yy', 
  //       });
    });





     function confirmDelete(event) {
        event.preventDefault(); // Prevents the default link navigation

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
                // If the user clicks on "Yes, delete it!", navigate to the delete URL
                window.location.href = event.target.href;
            }
        });
    }
</script>

  <script type="text/javascript">

    $('.removeItem').click(function (event) {
      event.preventDefault();

      swal({
        title: 'Are you sure?',
        text: "It will permanently deleted !",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'success',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then(function() {

        var myUrl = "{{url('Busfees-delete')}}";
        var table_name = $("input[name=table_name]").val();
        var delete_id = $("input[name=delete_id]").val();

        $.ajax({
            url: myUrl,
            type: "POST",
            data: { 
                "_token": "{{ csrf_token() }}",
                table_name : table_name,
                delete_id : delete_id
            },
            success: function () {
              swal(
                'Deleted!',
                'Your file has been deleted.',
                'success'
              );

             setTimeout(function() {
                  location.reload();
              }, 3000);
            }
        });

      })
       
    });



















  </script>

@endsection 

