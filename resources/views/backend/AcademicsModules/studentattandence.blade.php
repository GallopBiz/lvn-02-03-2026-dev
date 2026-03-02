@extends('backend.layouts.main')
@section('main-container')
<!-- <style>
    #trackCont{
        background-color: #eef3f3;
        border-radius: 25px;
        display: flex;
        /* align-items: center; */
        justify-content: center;
        /* padding: 10px; */
        /* margin:5px; */
    }
    #imgdiv{
        display: flex;
  align-items: center;
    }
    #AddLoc{
        background-color: #e4f0fd;
        border-radius: 25px;
        padding: 10px;
        /* margin-right: 17px;  */
        /* margin:5px; */
    }
    #BusInfo{
        background-color: #ddebda;
        border-radius: 25px;
        padding: 10px;
        /* margin-right: 2px;  */
    }
</style> -->
<div class="main-content">
    <div class="breadcrumb">
            <h1 class="me-2">Single Student Attandence Report :-</h1>
          </div>
        <div class="separator-breadcrumb border-top"></div>
    <div class="row">
    <form id="progress-form" class="p-4 progress-form" action="#"  novalidate method="">
                 
                 @csrf
                 <div class="row">
                     <div class="col-md-3 form-group mb-3">
                         <label for="firstName1">Student Name</label>
                         <input name="Student_Name" class="form-control" id="GN" type="text" placeholder="Student Name"/>
                     </div>
                     <div class="col-md-3 form-group mb-3">
                         <label for="lastName1">From Date</label>
                         <input name="From_Date" class="form-control" id="From_Date" type="date"/>
                     </div>
                     <div class="col-md-3 form-group mb-3">
                         <label for="lastName1">To Date</label>
                         <input name="To_Date" class="form-control" id="From_Date" type="date"/>  
                     </div>
                     <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Session </label><br>
                    <select id="PrimaryGroup" class="form-control" name="Session" autocomplete="shipping address-level1" required>
                              <option value="">---Select---</option>
                              <option value="2023-2024">2023-2024</option>
                              <option value="2022-2023">2022-2023</option>
                              <option value="2021-2022">2021-2022</option>
                           </select>
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="lastName1">Exam Session </label><br>
                    <select id="PrimaryGroup" class="form-control" name="Exam_Session" autocomplete="shipping address-level1" required>
                              <option value="">---Select---</option>
                              <option value="For-Student">For Student</option>
                              <option value="For-Staff">For Staff</option>
                              <option value="For-Worker">For Worker</option>
                           </select>
                </div>
                     <!-- <div class="col-md-3 form-group mb-3">
                         <br>
                         <label for="lastName1">Health Group </label> 
                         <input type="checkbox" id="StudentRelated" name="Health_Group" />
                     </div> -->
                     <div class="col-md-3 form-group mb-3">
                      <br>
                         <!-- <label for="lastName1">Entry Type </label><br> -->
                         <label class="radio-inline">
                             <input type="radio" name="optradio" checked>Attandence Status Waise
                           </label>
                           <label class="radio-inline">
                             <input type="radio" name="optradio">Subject Waise 
                           </label>
                     </div>
                     <div class="col-md-12">
                         <button class="btn btn-primary">Submit</button>
                     </div>
                 </div>
             </form>
    </div>
    <div class="separator-breadcrumb border-top"></div>
    <div class="row">
        <div class="col-md-6 mb-4">
            <h3>Attandence Reports</h3>
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th scope="col">S.no.</th>
                                                <th scope="col">Teacher Name</th> 
                                                <th scope="col">Subject</th>
                                                <th scope="col">Attendance Status</th>
                                                <th scope="col">Period no.</th>
                                                <th scope="col">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row">1</th>
                                                <td>Teacher</td>
                                                <td>Maths</td>
                                                <td>P</td>
                                                <td>1</td>
                                                <td>12-03-2023</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">1</th>
                                                <td>Teacher</td>
                                                <td>Science</td>
                                                <td>A</td>
                                                <td>1</td>
                                                <td>12-03-2023</td>
                                            </tr>
                                        </tbody>
                                  </table>
        </div>
        <div class="col-md-6 mb-4">
            <h3>Summary</h3>
            <table class="table">
                                          <tbody>
                                            <tr>
                                                <th>Student Name </th>
                                                <td>Name of the Student</td>
                                            </tr>
                                            <tr>
                                                <th>Class</th>
                                                <td>7</td>
                                            </tr>
                                            <tr>
                                                <th>Total Present</th>
                                                <td>85</td>
                                            </tr>
                                            <tr>
                                                <th>Total Absent</th>
                                                <td>12</td>
                                            </tr>
                                            <tr>
                                                <th>Over All Attandence</th>
                                                <td>85%</td>
                                            </tr>
                                        </tbody>
                                  </table>
        </div>
    </div>
    <!-- this div on rohit sir requirement not for this page -->
    <!-- <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8"> -->
            <!-- put here -->
            <!-- <div class="row" id="trackCont">
    <div class="col-md-4" id="imgdiv"><img src="{{url('assets/backend/')}}/images/photo-wide-5.jpeg" alt="" /></div>
    <div class="col-md-4" id="BusInfo">
      <h4>MP09EB3121</h4>
      <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <th >Running</th>
                                                <td>since 03min</td>
                                            </tr>
                                            <tr>
                                                <th >Speed</th>
                                                <td>35Km/h</td>
                                            </tr>
                                            <tr>
                                                <th >Driver</th>
                                                <td>Name of Driver</td>
                                            </tr>
                                            <tr>
                                                <th >Mobile No.</th>
                                                <td>123465687</td>
                                            </tr>
                                        </tbody>
                                  </table>
    </div>
    <div class="col-md-4" id="AddLoc">
    <h4>Location</h4>
    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsam repudiandae officiis et eos fugit.</p>
    <h6>Last Update At 31-07-2023 16:31:45</h6> 
    </div>
    </div>
        </div>
        <div class="col-md-2"></div>
    </div> -->
   
    <!-- this div on rohit sir requirement not for this page -->
    <!-- end of main-content -->
</div>
@endsection