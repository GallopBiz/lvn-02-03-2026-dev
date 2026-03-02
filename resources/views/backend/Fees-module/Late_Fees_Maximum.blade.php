@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Late Fees Maximum Limit</h1>
        </div>
        <form action="{{url('update-late-fees-limit')}}" method="post">
            @csrf
            <input type="hidden" name="update_id" value="@if($data!=='FALSE'){{$data->id}}@else 1 @endif">
            <div class="row">
               
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">From these no. of days </label>
                    <input name="from_this_no_of_days" value="@if($data!=='FALSE'){{$data->from_this_no_of_days}}@endif" class="form-control" id="From these no. of days" type="number"
                        placeholder="From these no. of days" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">To these no. of days </label>
                    <input name="to_this_no_of_days"  value="@if($data!=='FALSE'){{$data->to_this_no_of_days}}@endif" class="form-control" id="To these no. of days" type="number"
                        placeholder="To these no. of days" />
                </div>
                
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Maximum Late fees can be</label>
                    <input type="number" class="form-control" id="picker2" name="max_late_fees" value="@if($data!=='FALSE'){{$data->max_late_fees}}@endif"  placeholder="Maximum Late fees can be" />
                </div>
                <div class="col-md-12">
                    <button class="btn btn-primary">Submit</button>
                    <a href="{{url('late-fees-master')}}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </form>
    </div>
    <!-- end of main-content -->
</div> 
@endsection