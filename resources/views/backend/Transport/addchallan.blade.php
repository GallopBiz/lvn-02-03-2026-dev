@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Vehicel Registration</h1>
        </div>
        <form action="" method="post">
            @csrf
            <div class="row">
            <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Challan NO.</label>
                    <input name="Challan" class="form-control" id="Challan" type="text" placeholder="Challan NO" />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Vehicle NO.</label>
                    <select id="vehicle" class="form-control" name="vehicle" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              <option value="op1">option 1</option>
                              <option value="op2">option 2</option>
                              <option value="op3">option 3</option>
                           </select>
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Amount </label>
                    <input name="Amount" class="form-control" id="Amount" type="number"
                        placeholder="Amount" />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="picker2">vehicle Type</label>
                    <input type="text" class="form-control" id="vehicle" name="VType" />
                </div>
            <div class="col-md-3 form-group mb-3">
                    <label for="picker2">Entry Date</label>
                    <input type="date" class="form-control" id="picker2" name="EDate" />
                </div>
                
                <div class="col-md-3 form-group mb-3">
                    <label for="picker2">Challan Date</label>
                    <input type="date" class="form-control" id="picker2" name="CDate" />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="picker2">Date of Detechion</label>
                    <input type="datetime-local" class="form-control" id="picker2" name="Detechion" />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="picker2">Date of Generation</label>
                    <input type="datetime-local" class="form-control" id="picker2" name="Generation"/>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Reason :</label>
                    <!-- <input name="Cadd" class="form-control" id="Cadd" type="text" placeholder="Current Address" /> -->
                    <br>
                    <textarea id="Reason" name="Reason" rows="3" cols="50" placeholder="Reason" ></textarea>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Remark :</label>
                    <!-- <input name="Cadd" class="form-control" id="Cadd" type="text" placeholder="Current Address" /> -->
                    <br>
                    <textarea id="Remark" name="Remark" rows="3" cols="50" placeholder="Remark" ></textarea>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Action Teken :</label>
                    <!-- <input name="Cadd" class="form-control" id="Cadd" type="text" placeholder="Current Address" /> -->
                    <br>
                    <textarea id="Action" name="Action" rows="3" cols="50" placeholder="Action Teken" ></textarea>
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