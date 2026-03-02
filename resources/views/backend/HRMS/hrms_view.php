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
            <h1>HRMS</h1>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <!-- left-side-->
                    <div class="col-lg-4 col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body">
                              <div class="card-title"> Employees </div>
                                <div class="separator-breadcrumb border-top"></div>
                                  <div class="ul-widget-app__browser-list">
                                    <a href="{{ 'employee' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18"></span></div></a>
                                    <a href="{{ 'Attandencereports' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Attandence Reports</span></div></a>
                                    <a href="{{ 'dailyattandence' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Daily Attandence</span></div></a>
                                  </div>
                              </div>
                        </div>
                    </div>
                   
            <!-- right-side-->
          <!-- end of main-content -->
        </div>
</div>
@endsection