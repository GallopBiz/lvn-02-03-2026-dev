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
            <h1>Transport Section</h1>
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
                                    <a href="{{ 'addvehical' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Add Vehical</span></div></a>
                                    <a href="{{ 'driver-conductor-master' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Driver Conductor </span></div></a>
                                    <a href="{{ 'list-party-master' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18"> Party Master</span></div></a>
                                    <a href="{{ 'rtopaper' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18"> R.T.O. Paper</span></div></a>
                                  </div>
                              </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body">
                              <div class="card-title"> Routes </div>
                                <div class="separator-breadcrumb border-top"></div>
                                  <div class="ul-widget-app__browser-list">
                                    <a href="{{ 'bus_data' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Bus Data</span></div></a>
                                    <a href="{{ 'route-master' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Route Master </span></div></a>
                                    <a href="{{ 'list-party-master' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18"> Party Master</span></div></a>
                                    <a href="{{ 'bus-stop' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18"> Bus Stop</span></div></a>
                                    <a href="{{ 'area-master' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18"> Area Master</span></div></a>
                                  </div>
                              </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body">
                              <div class="card-title"> Other </div>
                                <div class="separator-breadcrumb border-top"></div>
                                  <div class="ul-widget-app__browser-list">
                                    <a href="{{ 'NatureOfWork' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Nature Of Work</span></div></a>
                                    <a href="{{ 'bus-attandence-list' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Bus Attandence List </span></div></a>
                                    <a href="{{ 'maintenance-head-master' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18"> Maintenance Head Master</span></div></a>
                                    <a href="{{ 'schedulemaster' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18"> Schedule Master</span></div></a>
                                    <a href="{{ 'scholarbusassign' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18"> Scholar Bus Assign</span></div></a>
                                    <a href="{{ 'teacherbusassign' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18"> Teacher Bus Assign</span></div></a>
                                  </div>
                              </div>
                        </div>
                    </div>
            <!-- right-side-->
          <!-- end of main-content -->
        </div>
</div>
@endsection