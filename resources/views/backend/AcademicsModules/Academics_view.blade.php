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
            <h1>Academics Section</h1>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <!-- left-side-->
                    <div class="col-lg-4 col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body">
                              <div class="card-title"> Attandence </div>
                                <div class="separator-breadcrumb border-top"></div>
                                  <div class="ul-widget-app__browser-list">
                                    <a href="{{ 'student-attandence-report' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Student Attandence Report</span></div></a>
                                    <a href="{{ 'Attandencereports' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Attandence Reports</span></div></a>
                                    <a href="{{ 'dailyattandence' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Daily Attandence</span></div></a>
                                  </div>
                              </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body">
                              <div class="card-title"> Groups </div>
                                <div class="separator-breadcrumb border-top"></div>
                                  <div class="ul-widget-app__browser-list">
                                    <a href="{{ 'primarygroup' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Primary Group</span></div></a>
                                    <a href="{{ 'groupmaster' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Group</span></div></a>
                                    <a href="{{ 'headmaster' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Head</span></div></a>
                                    <a href="{{ 'subheadmaster' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Sub Head</span></div></a>
                                  </div>
                              </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body">
                              <div class="card-title">Academic Work</div>
                                <div class="separator-breadcrumb border-top"></div>
                                  <div class="ul-widget-app__browser-list">
                                    <a href="{{ 'AssignSubject' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Assign Subject</span></div></a>
                                    <a href="{{ 'exammaster' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Exam</span></div></a>
                                    <a href="{{ 'calssese-assigne-to-teacher' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Calssese Assigne To Teacher</span></div></a>
                                    <a href="{{ 'subjectmaster' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Subject</span></div></a>
                                    <a href="{{ 'greadingmaster' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Greading</span></div></a>
                                    <a href="{{ 'streammaster' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Stream</span></div></a>
                                    <a href="{{ 'sectionmaster' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Section</span></div></a>
                                    <a href="{{ 'remarkmaster' }}"><div class="ul-widget-app__browser-list-1 mb-2"><span class="text-18">Remark</span></div></a>
                                  </div>
                              </div>
                        </div>
                    </div>
            <!-- right-side-->
          <!-- end of main-content -->
        </div>
</div>
@endsection