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
            <h1>Admission Process</h1>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <!-- left-side-->
                    <div class="col-lg-4 col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title"> Registration </div>
                                <div class="separator-breadcrumb border-top"></div>
                                <div class="ul-widget-app__browser-list">
                                    <a href="{{ 'admin-preenquiryform' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Pre Enquiry Entry</span></div></a>
                                    <a href="{{ 'admin-enquiryform' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Enquiry Entry</span></div></a>
                                    <a href="{{ 'add-student-registrations' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Student Registration</span></div></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title"> Registration List</div>
                                <div class="separator-breadcrumb border-top"></div>
                                <div class="ul-widget-app__browser-list">
                                    <a href="{{ 'admin-pre-enquiryform' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Pre Enquiry List</span></div></a>
                                    <a href="{{ 'adminenquirylist' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Enquiry List</span></div></a>
                                    <a href="{{ 'student-registrations' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Student List</span></div></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title"> Selection</div>
                                <div class="separator-breadcrumb border-top"></div>
                                <div class="ul-widget-app__browser-list">
                                    <a href="{{ 'selection-process' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Selection Process</span></div></a>
                                    <a href="{{ 'followupdate' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Follow Up Date</span></div></a>
                                </div>
                            </div>
                        </div>
                    </div>            
            <!-- right-side-->
          <!-- end of main-content -->
        </div>
</div>
@endsection