@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Bus Fees Master</h1>
        </div>
        <form action="" method="post">
            @csrf
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Select Batch</label>
                    <select id="Batch" class="form-control" name="Batch" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              <option value="2023-2024">2023-2024</option>
                              <option value="2022-2023">2022-2023</option>
                              <option value="2021-2022">2021-2022</option>
                           </select>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Bus Fees Type Name </label>
                    <input name="Bus_Fees_Type_Name" class="form-control" id="Bus Fees Type Name" type="text"
                        placeholder="Bus Fees Type Name" />
                </div>
            <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Date</label>
                    <input type="date" class="form-control" id="picker2" name="Date" />
                </div>
                
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Amount</label>
                    <input type="number" class="form-control" id="picker2" name="Amount" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Year</label>
                    <select id="Year" class="form-control" name="Year" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              <option value="1st">1st</option>
                              <option value="2nd">2nd</option>
                              <option value="3rd">3rd</option>
                           </select>
                </div>
                <div class="col-md-12">
                    <button class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
    <!-- end of main-content -->
</div>
@endsection