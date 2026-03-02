@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Course Fees Master</h1>
        </div>
        <form action="" method="post">
            @csrf
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Class Name</label>
                    <select id="Batch" class="form-control" name="Class_Name" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              <option value="Opt1">Opt1</option>
                              <option value="Opt2">Opt2</option>
                              <option value="Opt3">Opt3</option>
                           </select>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Session Name</label>
                    <select id="Session_Name" class="form-control" name="Session_Name" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              <option value="2023-2024">2023-2024</option>
                              <option value="2022-2023">2022-2023</option>
                              <option value="2021-2022">2021-2022</option>
                           </select>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Fees Type Name</label>
                    <select id="Fees_Type_Name" class="form-control" name="Fees_Type_Name" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              <option value="Opt1">Opt1</option>
                              <option value="Opt2">Opt2</option>
                              <option value="Opt3">Opt3</option>
                           </select>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Caste /  Category </label><br>
                    <input type="checkbox" id="vehicle1" name="Cast/category" value="Bike">
                    <label for="vehicle1"> Same for all</label><br>
                    <select id="Fees_Type_Name" class="form-control" name="Cast_/_category" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              <option value="Opt1">Opt1</option>
                              <option value="Opt2">Opt2</option>
                              <option value="Opt3">Opt3</option>
                           </select>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Batch </label><br>
                    <input type="checkbox" id="vehicle1" name="BCast/category" value="Bike">
                    <label for="vehicle1"> Same for all</label><br>
                    <select id="Fees_Type_Name" class="form-control" name="BCast_/_category" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              <option value="Opt1">Opt1</option>
                              <option value="Opt2">Opt2</option>
                              <option value="Opt3">Opt3</option>
                           </select>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-9 form-group mb-3">
                    <h5>Copy same structure from  previous years with  dates adjustments :-</h5>
                </div>
                <div class="col-md-3 form-group mb-3">
                <button class="btn btn-success m-1" type="button">Copy Previous</button></div>

                <div class="col-md-6 form-group mb-3">
                    <h5>Copy same structure as the following structure :-</h5>
                </div>
                <div class="col-md-3 form-group mb-3">
                <select id="Fees_Type_Name" class="form-control" name="BCast_/_category" autocomplete="shipping address-level1" required>
                              <option value="" disabled selected>Please select</option>
                              <option value="Opt1">Opt1</option>
                              <option value="Opt2">Opt2</option>
                              <option value="Opt3">Opt3</option>
                           </select></div>
                <div class="col-md-3 form-group mb-3">
                <button class="btn btn-success m-1" type="button">Copy Selected</button></div>
            </div>
            <br>
            <br>
            <div class="row">
            <div class="col-md-5 form-group mb-3">
            <table class="table table-borderless">
                                        <thead>
                                            <tr>
                                                <!-- <th scope="col">#</th> -->
                                                <th scope="col">Fees Date</th>
                                                <th scope="col">Date</th>
                                                <th scope="col">Term</th>
                                                <!-- <th scope="col">Status</th> -->
                                                <!-- <th scope="col">Action</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <!-- <th scope="row">1</th> -->
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="date" placeholder="IMEI No" /></td>
                                                <td><select id="Fees_Type_Name" class="form-control" name="BCast_/_category" autocomplete="shipping address-level1" required>
                                                    <option value="" disabled selected>Please select</option>
                                                    <option value="Opt1">Opt1</option>
                                                    <option value="Opt2">Opt2</option>
                                                    <option value="Opt3">Opt3</option>
                                                </select></td>
                                                <!-- <td>Smith@gmail.com</td> -->
                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button class="btn btn-danger m-1" type="button">Clear Fees</button>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">Sr.</th>
                                                <th scope="col">Account Name</th>
                                                <th scope="col">Fees</th>
                                                <th scope="col">Select</th>
                                                <!-- <th scope="col">Status</th> -->
                                                <!-- <th scope="col">Action</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row">1</th>
                                                <td>Admission Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                            <tr>
                                                <th scope="row">2</th>
                                                <td>Alumini Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                            <tr>
                                                <th scope="row">3</th>
                                                <td>Caution Money</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                            <tr>
                                                <th scope="row">4</th>
                                                <td>Activity Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                            <tr>
                                                <th scope="row">5</th>
                                                <td>Bus Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                            <tr>
                                                <th scope="row">6</th>
                                                <td>Computer Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                            <tr>
                                                <th scope="row">7</th>
                                                <td>Lunch Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                            <tr>
                                                <th scope="row">8</th>
                                                <td>Practical Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                            <tr>
                                                <th scope="row">9</th>
                                                <td>Sports Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                            <tr>
                                                <th scope="row">10</th>
                                                <td>Tuition Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                        </tbody>
                                    </table>
            </div>
            <div class="col-md-1 form-group mb-3" style="margin-top:30%;">
            <button class="btn btn-info m-1"  type="button">>>></button><br>
            <button class="btn btn-info m-1"  type="button"><<<</button>
            </div>
            <div class="col-md-6 form-group mb-3">
                <!-- right side table -->
                <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">Sr.</th>
                                                <th scope="col">Fees Date</th>
                                                <th scope="col">Account Name</th>
                                                <th scope="col">Fees</th>
                                                <th scope="col">Due Date</th>
                                                <th scope="col">Term</th>
                                                <th scope="col">Select</th>
                                                <th scope="col">Action</th>
                                                <!-- <th scope="col">Status</th> -->
                                                <!-- <th scope="col">Action</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row">1</th>
                                                <td>12-06-2023</td>
                                                <td>Admission Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td>12-08-2023</td>
                                                <td><select id="Fees_Type_Name" class="form-control" name="BCast_/_category" autocomplete="shipping address-level1" required>
                                                    <option value="" disabled selected>Please select</option>
                                                    <option value="Opt1">Opt1</option>
                                                    <option value="Opt2">Opt2</option>
                                                    <option value="Opt3">Opt3</option>
                                                </select></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <td><button class="btn btn-danger m-1" type="button">Delete</button></td>

                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                            <tr>
                                                <th scope="row">2</th>
                                                <td>12-06-2023</td>
                                                <td>Tuition Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td>12-08-2023</td>
                                                <td><select id="Fees_Type_Name" class="form-control" name="BCast_/_category" autocomplete="shipping address-level1" required>
                                                    <option value="" disabled selected>Please select</option>
                                                    <option value="Opt1">Opt1</option>
                                                    <option value="Opt2">Opt2</option>
                                                    <option value="Opt3">Opt3</option>
                                                </select></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <td><button class="btn btn-danger m-1" type="button">Delete</button></td>

                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                            <tr>
                                                <th scope="row">3</th>
                                                <td>12-06-2023</td>
                                                <td>Caution Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td>12-08-2023</td>
                                                <td><select id="Fees_Type_Name" class="form-control" name="BCast_/_category" autocomplete="shipping address-level1" required>
                                                    <option value="" disabled selected>Please select</option>
                                                    <option value="Opt1">Opt1</option>
                                                    <option value="Opt2">Opt2</option>
                                                    <option value="Opt3">Opt3</option>
                                                </select></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <td><button class="btn btn-danger m-1" type="button">Delete</button></td>

                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                            <tr>
                                                <th scope="row">4</th>
                                                <td>12-06-2023</td>
                                                <td>Bus Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td>12-08-2023</td>
                                                <td><select id="Fees_Type_Name" class="form-control" name="BCast_/_category" autocomplete="shipping address-level1" required>
                                                    <option value="" disabled selected>Please select</option>
                                                    <option value="Opt1">Opt1</option>
                                                    <option value="Opt2">Opt2</option>
                                                    <option value="Opt3">Opt3</option>
                                                </select></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <td><button class="btn btn-danger m-1" type="button">Delete</button></td>

                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                            <tr>
                                                <th scope="row">5</th>
                                                <td>12-06-2023</td>
                                                <td>Sports Fees</td>
                                                <td><input name="imei" class="form-control" id="IMEI" type="text" placeholder="IMEI No" /></td>
                                                <td>12-08-2023</td>
                                                <td><select id="Fees_Type_Name" class="form-control" name="BCast_/_category" autocomplete="shipping address-level1" required>
                                                    <option value="" disabled selected>Please select</option>
                                                    <option value="Opt1">Opt1</option>
                                                    <option value="Opt2">Opt2</option>
                                                    <option value="Opt3">Opt3</option>
                                                </select></td>
                                                <td><input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"></td>
                                                <td><button class="btn btn-danger m-1" type="button">Delete</button></td>

                                                <!-- <td><span class="badge badge-success">Active</span></td> -->
                                                <!-- <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td> -->
                                            </tr>
                                        </tbody>
                                    </table>
            </div>
            </div>
            <div class="row">
                
                <div class="col-md-4 form-group mb-3">
                    <h5>Total above fees :</h5>
                </div>
                <div class="col-md-2 form-group mb-3">
                <input name="imei" class="form-control" id="IMEI" 
                     type="text" placeholder="IMEI No" /></div>
                     <div class="col-md-4 form-group mb-3">
                    <h5>Total above fees :</h5>
                </div>
                <div class="col-md-2 form-group mb-3">
                <input name="imei" class="form-control" id="IMEI" 
                     type="text" placeholder="IMEI No" /></div>
            </div>
            <div class="row"><div class="col-md-12">
                    <button class="btn btn-primary">Submit</button>
                </div></div>
        </form>
    </div>
    <!-- end of main-content -->
</div>
@endsection