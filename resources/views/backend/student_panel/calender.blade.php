@extends('backend.layouts.main')
@section('main-container')
        <!-- ============ Body content start ============= -->
        <div class="main-content">
          <div class="breadcrumb">
            <h1>Calendar</h1>
            <ul>
              <!-- <li><a href="href">Apps</a></li> -->
              <li>My Calendar</li>
            </ul>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-3">
              <div class="card mb-4">
                <div class="card-body">
                  <div class="create_event_wrap">
                    <form class="js-form-add-event">
                      <div class="form-group">
                        <label for="newEvent">Create new Event</label>
                        <input
                          class="form-control"
                          id="newEvent"
                          type="text"
                          name="newEvent"
                          placeholder="new Event"
                          aria-describedby="helpId"
                        />
                      </div>
                    </form>
                    <ul class="list-group" id="external-events">
                      <li class="list-group-item bg-success fc-event">
                        Hello World
                      </li>
                      <li class="list-group-item bg-primary fc-event">
                        Go to Shopping
                      </li>
                      <li class="list-group-item bg-warning fc-event">
                        Payment schedule
                      </li>
                      <li class="list-group-item bg-danger fc-event">
                        Rent Due
                      </li>
                    </ul>
                    <p>
                      <input id="drop-remove" type="checkbox" />
                      <label for="drop-remove">remove after drop</label>
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-9">
              <div class="card mb-4 o-hidden">
                <div class="card-body">
                  <div id="calendar"></div>
                </div>
              </div>
            </div>
          </div>
          <!-- end of main-content -->
         
        </div>
      </div>
    </div>
    <!-- ============ Search UI Start ============= -->
    <div class="search-ui">
      <div class="search-header">
        <img src="../../dist-assets/images/logo.png" alt="" class="logo" />
        <button class="search-close btn btn-icon bg-transparent float-end mt-2">
          <i class="i-Close-Window text-22 text-muted"></i>
        </button>
      </div>
      <input
        type="text"
        placeholder="Type here"
        class="search-input"
        autofocus
      />
      <div class="search-title">
        <span class="text-muted">Search results</span>
      </div>
      <div class="search-results list-horizontal">
        <div class="list-item col-md-12 p-0">
          <div class="card o-hidden flex-row mb-4 d-flex">
            <div class="list-thumb d-flex">
              <!-- TUMBNAIL -->
              <img
                src="../../dist-assets/images/products/headphone-1.jpg"
                alt=""
              />
            </div>
            <div class="flex-grow-1 ps-2 d-flex">
              <div
                class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center flex-lg-row"
              >
                <!-- OTHER DATA -->
                <a href="" class="w-40 w-sm-100">
                  <div class="item-title">Headphone 1</div>
                </a>
                <p class="m-0 text-muted text-small w-15 w-sm-100">Gadget</p>
                <p class="m-0 text-muted text-small w-15 w-sm-100">
                  $300
                  <del class="text-secondary">$400</del>
                </p>
                <p
                  class="m-0 text-muted text-small w-15 w-sm-100 d-none d-lg-block item-badges"
                >
                  <span class="badge bg-danger">Sale</span>
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="list-item col-md-12 p-0">
          <div class="card o-hidden flex-row mb-4 d-flex">
            <div class="list-thumb d-flex">
              <!-- TUMBNAIL -->
              <img
                src="../../dist-assets/images/products/headphone-2.jpg"
                alt=""
              />
            </div>
            <div class="flex-grow-1 ps-2 d-flex">
              <div
                class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center flex-lg-row"
              >
                <!-- OTHER DATA -->
                <a href="" class="w-40 w-sm-100">
                  <div class="item-title">Headphone 1</div>
                </a>
                <p class="m-0 text-muted text-small w-15 w-sm-100">Gadget</p>
                <p class="m-0 text-muted text-small w-15 w-sm-100">
                  $300
                  <del class="text-secondary">$400</del>
                </p>
                <p
                  class="m-0 text-muted text-small w-15 w-sm-100 d-none d-lg-block item-badges"
                >
                  <span class="badge bg-primary">New</span>
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="list-item col-md-12 p-0">
          <div class="card o-hidden flex-row mb-4 d-flex">
            <div class="list-thumb d-flex">
              <!-- TUMBNAIL -->
              <img
                src="../../dist-assets/images/products/headphone-3.jpg"
                alt=""
              />
            </div>
            <div class="flex-grow-1 ps-2 d-flex">
              <div
                class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center flex-lg-row"
              >
                <!-- OTHER DATA -->
                <a href="" class="w-40 w-sm-100">
                  <div class="item-title">Headphone 1</div>
                </a>
                <p class="m-0 text-muted text-small w-15 w-sm-100">Gadget</p>
                <p class="m-0 text-muted text-small w-15 w-sm-100">
                  $300
                  <del class="text-secondary">$400</del>
                </p>
                <p
                  class="m-0 text-muted text-small w-15 w-sm-100 d-none d-lg-block item-badges"
                >
                  <span class="badge bg-primary">New</span>
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="list-item col-md-12 p-0">
          <div class="card o-hidden flex-row mb-4 d-flex">
            <div class="list-thumb d-flex">
              <!-- TUMBNAIL -->
              <img
                src="../../dist-assets/images/products/headphone-4.jpg"
                alt=""
              />
            </div>
            <div class="flex-grow-1 ps-2 d-flex">
              <div
                class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center flex-lg-row"
              >
                <!-- OTHER DATA -->
                <a href="" class="w-40 w-sm-100">
                  <div class="item-title">Headphone 1</div>
                </a>
                <p class="m-0 text-muted text-small w-15 w-sm-100">Gadget</p>
                <p class="m-0 text-muted text-small w-15 w-sm-100">
                  $300
                  <del class="text-secondary">$400</del>
                </p>
                <p
                  class="m-0 text-muted text-small w-15 w-sm-100 d-none d-lg-block item-badges"
                >
                  <span class="badge bg-primary">New</span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- PAGINATION CONTROL -->
      <div class="col-md-12 mt-5 text-center">
        <nav aria-label="Page navigation example">
          <ul class="pagination d-inline-flex">
            <li class="page-item">
              <a class="page-link" href="#" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
                <span class="sr-only">Previous</span>
              </a>
            </li>
            <li class="page-item"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
              <a class="page-link" href="#" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                <span class="sr-only">Next</span>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
    <!-- ============ Search UI End ============= -->
    <div class="customizer">
      <div class="handle"><i class="i-Gear spin"></i></div>
      <div
        class="customizer-body"
        data-perfect-scrollbar=""
        data-suppress-scroll-x="true"
      >
        <div class="accordion" id="accordionCustomizer">
          <div class="card">
            <div class="card-header" id="headingOne">
              <p class="mb-0">Sidebar Colors</p>
            </div>
            <div
              class="collapse show"
              id="collapseOne"
              aria-labelledby="headingOne"
              data-parent="#accordionCustomizer"
            >
              <div class="card-body">
                <div class="colors sidebar-colors">
                  <a
                    class="color gradient-purple-indigo"
                    data-sidebar-class="sidebar-gradient-purple-indigo"
                    ><i class="i-Eye"></i></a
                  ><a
                    class="color gradient-black-blue"
                    data-sidebar-class="sidebar-gradient-black-blue"
                    ><i class="i-Eye"></i></a
                  ><a
                    class="color gradient-black-gray"
                    data-sidebar-class="sidebar-gradient-black-gray"
                    ><i class="i-Eye"></i></a
                  ><a
                    class="color gradient-steel-gray"
                    data-sidebar-class="sidebar-gradient-steel-gray"
                    ><i class="i-Eye"></i></a
                  ><a
                    class="color dark-purple active"
                    data-sidebar-class="sidebar-dark-purple"
                    ><i class="i-Eye"></i></a
                  ><a
                    class="color slate-gray"
                    data-sidebar-class="sidebar-slate-gray"
                    ><i class="i-Eye"></i></a
                  ><a
                    class="color midnight-blue"
                    data-sidebar-class="sidebar-midnight-blue"
                    ><i class="i-Eye"></i></a
                  ><a class="color blue" data-sidebar-class="sidebar-blue"
                    ><i class="i-Eye"></i></a
                  ><a class="color indigo" data-sidebar-class="sidebar-indigo"
                    ><i class="i-Eye"></i></a
                  ><a class="color pink" data-sidebar-class="sidebar-pink"
                    ><i class="i-Eye"></i></a
                  ><a class="color red" data-sidebar-class="sidebar-red"
                    ><i class="i-Eye"></i></a
                  ><a class="color purple" data-sidebar-class="sidebar-purple"
                    ><i class="i-Eye"></i
                  ></a>
                </div>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header" id="headingOne">
              <p class="mb-0">RTL</p>
            </div>
            <div
              class="collapse show"
              id="collapseTwo"
              aria-labelledby="headingTwo"
              data-parent="#accordionCustomizer"
            >
              <div class="card-body">
                <label class="checkbox checkbox-primary">
                  <input id="rtl-checkbox" type="checkbox" /><span
                    >Enable RTL</span
                  ><span class="checkmark"></span>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    
    <script src="{{url('assets/backend')}}/js/plugins/jquery-3.3.1.min.js"></script>
    <script src="{{url('assets/backend')}}/js/plugins/bootstrap.bundle.min.js"></script>
    <script src="{{url('assets/backend')}}/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="{{url('assets/backend')}}/js/scripts/script.min.js"></script>
    <script src="{{url('assets/backend')}}/js/scripts/sidebar.compact.script.min.js"></script>
    <script src="{{url('assets/backend')}}/js/scripts/customizer.script.min.js"></script>
    <script src="{{url('assets/backend')}}/js/plugins/calendar/jquery-ui.min.js"></script>
    <script src="{{url('assets/backend')}}/js/plugins/calendar/moment.min.js"></script>
    <script src="{{url('assets/backend')}}/js/plugins/calendar/fullcalendar.min.js"></script>
    <script src="{{url('assets/backend')}}/js/scripts/calendar.script.min.js"></script>
  </body>
</html>
@endsection
   <!--  <script src="{{url('assets/backend')}}/js/plugins/calendar/fullcalendar.min.js"></script>
    <script src="{{url('assets/backend')}}/js/scripts/calendar.script.min.js"></script> -->

 
