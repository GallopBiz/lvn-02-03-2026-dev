@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
   <div class="breadcrumb">
      <h1 class="me-2">Edit Fess Type Master</h1>
   </div>
   <div class="separator-breadcrumb border-top"></div>
   <div class="row">
      <div class="col-md-12 mb-4">
         <div class="card text-start">
            <div class="card-body">
               <form method="post" action="{{url('fees-types-master-update')}}">
                @csrf
                <input type="hidden" name="update_id" value="{{$fees_types_masterArr->id}}">
                  <div class="row">

                     <div class="col-md-12 form-group mb-3">
                        <label for="lastName1">Fees Type Title <span class="text-danger">(non editable field*)</span></label>
                        <input class="form-control" name="fees_type" value="{{$fees_types_masterArr->fees_type}}" id="lastName1" type="text" placeholder="Fess Type Name" readonly />
                     </div>
                     <div class="col-md-12 form-group mb-3">
                        <label for="exampleInputEmail1">Session</label>
                        <input type="text" readonly id="session" class="form-control" value="" name="session">
                        <!-- <select id="session" class="form-control" name="session" autocomplete="" required>
                            <option disabled selected>Please select</option>
                            @foreach(config('global.session_name') as $each)
                            <option value="{{$each}}" @if($fees_types_masterArr->session==$each) selected @endif>{{$each}}</option>
                            @endforeach
                        </select> -->
                     </div>
                     <div class="col-md-12 form-group mb-3">
                        <label for="exampleInputEmail1">Percentage number / Remark</label>
                        <input class="form-control" name="remark" value="{{$fees_types_masterArr->remark}}" id="exampleInputEmail1" type="text" placeholder="Fees type remarks" />
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function () {
      setTimeout(function() {
        var year = $("#year").val()
        $("#session").val(year);
      }, 1000);
    });
</script>
@endsection