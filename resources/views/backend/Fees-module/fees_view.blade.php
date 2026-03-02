@extends('backend.layouts.main')
@section('main-container')
<style>
  /* Style for WebKit-based browsers (Chrome, Safari) */
  .ul-widget-app__browser-list::-webkit-scrollbar {
    width: 5px; /* Width of the scrollbar */
    max-height: 200px;
    overflow: auto;
  }

  .ul-widget-app__browser-list::-webkit-scrollbar-thumb {
    background-color: #7074b3; /* Color of the scrollbar thumb */
    border-radius: 6px; /* Round the corners of the thumb */
    max-height: 200px;
    overflow: auto;
  }

  /* Style for Firefox */
  .ul-widget-app__browser-list {
    scrollbar-color: #7074b3 transparent; /* Color of the scrollbar thumb and track */
    max-height: 200px;
    overflow: auto;
  }
  .card-title{
        margin-bottom:5px;
        color:#7074b3;
        font-size:25px;
  }
</style>

<!-- <div class="main-content-wrap sidenav-open d-flex flex-column"> -->
        <!-- ============ Body content start ============= -->
        <div class="main-content">
        <div class="breadcrumb">
            <h1>Fees Section</h1>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <!-- left-side-->
                    <!-- notification-->
                     <div class="col-lg-4 col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title"> Fees Type </div>
                                <div class="separator-breadcrumb border-top"></div>
                                <div class="ul-widget-app__browser-list">
                                    <a href="{{ 'fees-types-master' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Fees Type Master</span></div></a>
                                    <a href="{{ 'bus-fees-master' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Bus Fees Master</span></div></a>
                                    <a href="{{ 'late-fees-master' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Late Fees Master</span></div></a>
                                    <a href="{{ 'create-course-fees-structure-master' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Course Fees Structure Master</span></div></a>
                                    <a href="{{ 'terms' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Terms Master</span></div></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title"> Fees List </div>
                                <div class="separator-breadcrumb border-top"></div>
                                <div class="ul-widget-app__browser-list">
                                    <a href="{{ 'course-fees-head-orders' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Course Fees Head Orders</span></div></a>
                                    <a href="{{ 'course-fees-structure-master-list' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Course Fees Structure Master List</span></div></a>
                                    <a href="{{ 'defaulters_list' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Defaulters List</span></div></a>
                                    <a href="{{ 'cancle_student_ledger' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">cancle_student_ledger</span></div></a>
                                    <a href="{{ 'fees-master-student' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Student Fees Master</span></div></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title"> Other</div>
                                <div class="separator-breadcrumb border-top"></div>
                                <div class="ul-widget-app__browser-list">
                                    <a href="{{ 'fees_receipt_challan' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Fees Receipt Challan</span></div></a>
                                    <a href="{{ 'generate-due-chart' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Generate Due Chart</span></div></a>
                                    <a href="{{ 'student_ledger' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Student Ledger</span></div></a>
                                </div>
                            </div>
                        </div>
                    </div>
            <!-- right-side-->
          <!-- end of main-content -->
        </div>
</div>
@endsection