@extends('backend.layouts.main')
@section('main-container')
<style type="text/css">
  .validation_err {
    color: red !important;
  }

  input[type="number"] {
    appearance: textfield;
    -webkit-appearance: textfield;
    -moz-appearance: textfield;
  }

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

  .s1 {
    background-color: #ff4c51;
  }

  .uperletter {
    text-transform: capitalize;
  }

  .sw-theme-default .sw-toolbar-top {
    display: none;
  }

  .mb-3 {
    margin-bottom: 0.4rem !important;
  }
</style>
<div class="main-content pt-4">
  <!-- <h2>hyy</h2> -->
  <div class="breadcrumb">
    <h2>Next Year Fees</h2>
  </div>
  <div class="separator-breadcrumb border-top"></div>
  <div class="row">
    <!-- student information -->
    <section class="ul-product-detail">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-lg-3">
                  <div class="avatar box-shadow-2 mb-3 uperletter" style="border-radius:50%;"><img src="{{url('assets/backend/')}}/images/student.png" alt="" /></div>
                  <table class="table table-striped">
                    <thead>
                    </thead>
                    <tbody>
                      <tr>
                        <th>Name </th>
                        <td>{{$all_inquiry[0]->student_name}}</td>
                      </tr>
                      <tr>
                        <th>Class </th>
                        <td>{{$all_inquiry[0]->class_name}}</td>

                      </tr>
                      <tr>
                        <th>Scholar No. </th>
                        <td>{{$all_inquiry[0]->scholar_no}}</td>

                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="col-lg-9">


                  <div class="card text-left">
                    <div class="card">
                      <div class="card-body">
                        <h4 class="card-title mb-3">Student Information
                          <!-- <input type="checkbox" id="edit_checkbox" /> -->
                          <!-- <label for="edit_checkbox">Edit</label> -->
                        </h4>

                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                          <li class="nav-item">
                            <a class="nav-link active" id="home-basic-tab" data-bs-toggle="tab" href="#homeBasic" role="tab" aria-controls="homeBasic" aria-selected="true">Basic Information</a>
                          </li>
                          <!-- <li class="nav-item">
                      <a
                        class="nav-link"
                        id="contact-basic-tab"
                        data-bs-toggle="tab"
                        href="#contactBasic"
                        role="tab"
                        aria-controls="contactBasic"
                        aria-selected="false"
                        >Contact</a
                      >
                    </li>
                    <li class="nav-item">
                      <a
                        class="nav-link"
                        id="profile-basic-tab"
                        data-bs-toggle="tab"
                        href="#profileBasic"
                        role="tab"
                        aria-controls="profileBasic"
                        aria-selected="false"
                        >Siblings Details</a
                      >
                    </li> -->
                        </ul>
                        <div class="tab-content" id="myTabContent">
                          <div class="tab-pane fade show active" id="homeBasic" role="tabpanel" aria-labelledby="home-basic-tab">
                            <div class="table-responsive">
                              <table class="table">
                                <thead>
                                  <tr>
                                    <th>S No.</th>
                                    <th>Fees date</th>
                                    <th>Due date</th>
                                    <th>Account Name</th>
                                    <th>Receipt Number</th>
                                    <th>Amount<th>                                               
                                  </tr>
                                </thead>
                                <tbody>
                                      @if(!empty($all_inquiry))
                                            <?php $i=1; $total = 0;?>
                                      @foreach($all_inquiry as $each_inq)   
                                      <?php $total += $each_inq->fees; ?>                           
                                      <tr>
                                        <td><?php echo $i; ?></td>
                                        <td>{{$each_inq->fees_date}}</td>
                                        <td>{{$each_inq->due_date}}</td>
                                        <td>{{$each_inq->account_name}}</td>
                                        <td>{{$each_inq->receipt_number}}</td>
                                        <td>{{$each_inq->fees}}</td>
                                      </tr>
                                      <?php $i++;?>
                                      @endforeach
                                      <input type="hidden" id="select_count" name="select_count" value="<?php echo $i-1; ?>">
                                      @else
                                      <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                                      @endif
                                </tbody>
                              </table>
                                <h4>Total Amount: {{$total}}</h4>
                            </div>
                          </div>


                        </div>
                      </div>



                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
    </section>
    <!-- <Add>To Cart</Add> -->
    <!-- end student information -->



  </div>
  <!-- end of main-content -->
</div>
@endsection