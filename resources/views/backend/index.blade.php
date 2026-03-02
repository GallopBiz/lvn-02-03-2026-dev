@extends('backend.layouts.main')
@section('main-container')
  <script type="text/javascript">jQuery.noConflict();</script> 

    <div class="main-content">
		<div class="breadcrumb">
			@role('Student')
			<h1 class="me-2">Student Panel</h1>
			@endrole
			
			@role('Admin')
			<h1 class="me-2">Admin Panel</h1>
			@endrole
			<ul>
				<li><a href="">Dashboard</a></li>
			</ul>
		</div>
          <div class="separator-breadcrumb border-top"></div>
		  @role('Admin') 
		<div class="row">          
            <div class="col-lg-12 col-md-12">
              <!-- CARD ICON-->
              <div class="row"> 
                
                <div class="col-lg-3 col-md-6 col-sm-6">
					<div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
						<div class="card-body text-center">
							<i class="i-Pen-3"></i>
							<div class="w-100 text-center">
								<p class="text-muted mt-2 mb-0">Total Pre Enquiries</p>
								<p class="text-primary text-24 line-height-1 mb-2">{{ $preenquirecount }}</p>
								<!-- Tag Always Visible -->
								<a href="https://school.lvnindore.org.in/admin-pre-enquiryform" class="badge badge-primary">View Pre Enquiries</a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-md-6 col-sm-6">
					<div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
						<div class="card-body text-center">
							<i class="i-Diploma-2"></i>
							<div class="w-100 text-center">
								<p class="text-muted mt-2 mb-0">Total Enquiries</p>
								<p class="text-primary text-24 line-height-1 mb-2">{{ $enquirecount }}</p>
								<!-- Tag Always Visible -->
								<a href="https://school.lvnindore.org.in/adminenquirylist" class="badge badge-primary">View Enquiries</a>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-3 col-md-6 col-sm-6">
					<div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
						<div class="card-body text-center">
							<i class="i-Student-Hat-2"></i>
							<div class="w-100 text-center">
								<p class="text-muted mt-2 mb-0">Total Registrations</p>
								<p class="text-primary text-24 line-height-1 mb-2">{{ $totalRegistrations }}</p>
								<!-- Tag Always Visible -->
								<a href="https://school.lvnindore.org.in/student-registrations" class="badge badge-primary">View Registrations</a>
							</div>
						</div>
					</div>
				</div>

               <div class="col-lg-3 col-md-6 col-sm-6">
					<div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
						<div class="card-body text-center">
							<i class="i-Jeep"></i>
							<div class="w-100 text-center">
								<p class="text-muted mt-2 mb-0">Total Buses</p>
								<p class="text-primary text-24 line-height-1 mb-2">{{ $totalBuses }}</p>
								<!-- View Button Always Visible -->
								<a href="https://school.lvnindore.org.in/bus_data" class="badge badge-primary">View Buses</a>
							</div>
						</div>
					</div>
				</div>
				
        
              </div>
            </div>
			
			<div class="row">
				<!-- First Column with Payments Summary -->
				<div class="col-lg-6 col-md-12">
					<div class="row">
						<div class="row w-100">
							<a href="{{url('enquiry-data')}}" class="col-lg-3 col-md-6 col-sm-6" style="width: 50%">
								<div class="card card-icon mb-2" >
									<div class="card-body text-center">
												<i class="i-Money-2"></i>
											<p class="text-muted mt-2 mb-2">Enquiry List</p>
										<p class="text-primary text-24 line-height-1 m-0">{{$enquirecount * 500}}</p>
									</div>
								</div>
							</a>
							<a href="{{url('duestuamount')}}" class="col-lg-3 col-md-6 col-sm-6" style="width: 50%">
								<div class="card card-icon mb-2">
									<div class="card-body text-center">
											<i class="i-Money-Bag"></i>
										<p class="text-muted mt-2 mb-2">Registration list</p>
										<p class="text-primary text-24 line-height-1 m-0">{{ $uniqueScholarCount * 19000 }}</p>
									</div>
								</div>
							</a>
						</div>		
					</div>
				</div>
				<!-- Second Column with Latest 10 Records -->
				<div class="col-lg-6 col-md-12">
					<div class="card o-hidden mb-4">
						<div class="card-header d-flex align-items-center border-0">
							<h3 class="w-50 float-start card-title m-0">Latest Payments Summary</h3>
						</div>
						<div>
							<div class="table-responsive">
								<table class="table text-center" id="payment_summary_table">
									<thead>
										<tr>
											<th scope="col">Sr. No.</th>
											<th scope="col">Payment Date</th>
											<th scope="col">Total Received (₹)</th>
										</tr>
									</thead>
									<tbody>
										@forelse ($records as $index => $row)
											<tr>
												<th scope="row">{{ $index + 1 }}</th>
												<td>{{ \Carbon\Carbon::parse($row->payment_day)->format('d-m-Y') }}</td>
												<td>₹{{ number_format($row->total_received, 2) }}</td>
											</tr>
										@empty
											<tr>
												<td colspan="3" class="text-center">No payment records available.</td>
											</tr>
										@endforelse
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

			</div>
			<div class="row">
				<!-- First Column with Payments Summary -->
				<div class="col-lg-6 col-md-12">
					<div class="row">
						<!-- First Card: Total Received Amount -->
						<div class="col-lg-6 col-md-12">
							<div class="card card-chart-bottom o-hidden mb-4">
								<div class="card-body">
									<div class="text-muted">Total Received Amount</div>
									<p class="mb-4 text-primary text-24">
										₹{{ number_format($totalSubTotalReceived, 2) }}
									</p>
								</div>
								<div id="echart1" style="height: 260px"></div>
							</div>
						</div>

						<!-- Second Card: Last Week Sales -->
						<!--<div class="col-lg-6 col-md-12">
							<div class="card card-chart-bottom o-hidden mb-4">
								<div class="card-body">
									<div class="text-muted">Last Week Received Amount</div>
									<p class="mb-4 text-warning text-24">₹0</p>
								</div>
								<div id="echart2" style="height: 260px"></div>
							</div>
						</div>-->
					</div>
				</div>
				<!-- Second Column with Payment Summary Table -->
				<div class="col-lg-6 col-md-12">
					<!--<div class="card o-hidden mb-4">
						<div class="card-header d-flex align-items-center border-0">
							<h3 class="w-50 float-start card-title m-0">Latest Payments Summary</h3>
						</div>
						<div>
							<div class="table-responsive">
								<table class="table text-center" id="payment_summary_table">
									<thead>
										<tr>
											<th scope="col">Sr. No.</th>
											<th scope="col">Payment Date</th>
											<th scope="col">Total Received (₹)</th>
										</tr>
									</thead>
									<tbody>
										@forelse ($records as $index => $row)
											<tr>
												<th scope="row">{{ $index + 1 }}</th>
												<td>{{ \Carbon\Carbon::parse($row->payment_day)->format('d-m-Y') }}</td>
												<td>₹{{ number_format($row->total_received, 2) }}</td>
											</tr>
										@empty
											<tr>
												<td colspan="3" class="text-center">No payment records available.</td>
											</tr>
										@endforelse
									</tbody>
								</table>
							</div>
						</div>
					</div>-->
				</div>
			</div>
			<div class="row mt-4">
            <!-- notification-->
            <div class="col-lg-4 col-md-4 mb-4">
              <div class="card">
                <div class="card-body">
                  <div class="card-title">Notification</div>
                  {{--	<div class="ul-widget-app__browser-list">
							<div class="ul-widget-app__browser-list-1 mb-4">
							<i
							class="i-Bell1 text-white bg-warning rounded-circle p-2 me-3"></i
							  ><span class="text-15">You have 9 pending Tasks</span
							  ><span class="text-mute">in a sec</span>
							</div>
							<div class="ul-widget-app__browser-list-1 mb-4">
							  <i
								class="i-Internet text-white green-500 rounded-circle p-2 me-3"
							  ></i
							  ><span class="text-15">Traffic Overloaded</span
							  ><span class="text-mute">4 Hours ago</span>
							</div>
						</div>--}}
                </div>
              </div>
            </div>
            <!-- best-sellers-->
            <div class="col-xl-4 col-md-12 mb-4">
              <div class="card">
                <div class="card-body">
                  <div class="ul-widget__head">
                    <div class="ul-widget__head-label">
                      <h3 class="ul-widget__head-title">Calender</h3>
                    </div>
                    <div class="ul-widget__head-toolbar">
                      <ul
                        class="nav nav-tabs nav-tabs-line nav-tabs-bold ul-widget-nav-tabs-line"
                        role="tablist"
                      >
                        <li class="nav-item">
                          <a
                            class="nav-link active show"
                            data-bs-toggle="tab"
                            href="#ul-widget5-tab1-content"
                            role="tab"
                            aria-selected="true"
                            >Latest</a
                          >
                        </li>
                        <li class="nav-item">
                          <a
                            class="nav-link"
                            data-bs-toggle="tab"
                            href="#ul-widget5-tab2-content"
                            role="tab"
                            aria-selected="false"
                            >Month</a
                          >
                        </li>
                      </ul>
                    </div>
                  </div>
				  {{-- <div class="ul-widget__body">
                    <div class="tab-content">
					<div class="tab-pane active show"
							id="ul-widget5-tab1-content">
						<div class="ul-widget5">
							<div class="ul-widget5__item">
								<div class="ul-widget5__content">
								  <!-- <div class="ul-widget5__pic">
									<img
									  src="../../dist-assets/images/products/iphone-1.jpg"
									  alt="Third slide"
									/>
								  </div> -->
								  <div class="ul-widget5__section">
									<a class="ul-widget4__title" href="#"
									  >Great Logo Designn</a
									>
									<p class="ul-widget5__desc">
									  UI lib admin themes.
									</p>
									<div class="ul-widget5__info">
									  <span>Author:</span
									  ><span class="text-primary">Jon Snow</span
									  ><span>Released:</span
									  ><span class="text-primary">23.08.17</span>
									</div>
								  </div>
								</div>
							  
							</div>
						</div>
                    </div>
                    <div class="tab-pane" id="ul-widget5-tab2-content">
                        <div class="ul-widget5">
                          <div class="ul-widget5__item">
                            <div class="ul-widget5__content"> 
                              <div class="ul-widget5__section">
                                <a class="ul-widget4__title" href="#"
                                  >Great Logo Designn</a
                                >
                                <p class="ul-widget5__desc">
                                  UI lib admin themes.
                                </p>
                                <div class="ul-widget5__info">
                                  <span>Author:</span
                                  ><span class="text-primary">Jon Snow</span
                                  ><span>Released:</span
                                  ><span class="text-primary">23.08.17</span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="ul-widget5__item">
                            <div class="ul-widget5__content">
                              <div class="ul-widget5__section">
                                <a class="ul-widget4__title" href="#"
                                  >Great Logo Designn</a
                                >
                                <p class="ul-widget5__desc">
                                  UI lib admin themes.
                                </p>
                                <div class="ul-widget5__info">
                                  <span>Author:</span
                                  ><span class="text-primary">Jon Snow</span
                                  ><span>Released:</span
                                  ><span class="text-primary">23.08.17</span>
                                </div>
                              </div>
                            </div>
                            <!-- <div class="ul-widget5__content">
                              <div class="ul-widget5__stats">
                                <span class="ul-widget5__number">29,200</span
                                ><span class="ul-widget5__sales text-mute"
                                  >sales</span
                                >
                              </div>
                              <div class="ul-widget5__stats">
                                <span class="ul-widget5__number">4500</span
                                ><span class="ul-widget5__sales text-mute"
                                  >votes</span
                                >
                              </div>
                            </div> -->
                          </div>
                          <div class="ul-widget5__item">
                            <div class="ul-widget5__content">
                              <!-- <div class="ul-widget5__pic">
                                <img
                                  src="../../dist-assets/images/products/watch-1.jpg"
                                  alt="Third slide"
                                />
                              </div> -->
                              <div class="ul-widget5__section">
                                <a class="ul-widget4__title" href="#"
                                  >Great Logo Designn</a
                                >
                                <p class="ul-widget5__desc">
                                  UI lib admin themes.
                                </p>
                                <div class="ul-widget5__info">
                                  <span>Author:</span
                                  ><span class="text-primary">Jon Snow</span
                                  ><span>Released:</span
                                  ><span class="text-primary">23.08.17</span>
                                </div>
                              </div>
                            </div>
                            <!-- <div class="ul-widget5__content">
                              <div class="ul-widget5__stats">
                                <span class="ul-widget5__number">23,200</span
                                ><span class="ul-widget5__sales text-mute"
                                  >sales</span
                                >
                              </div>
                              <div class="ul-widget5__stats">
                                <span class="ul-widget5__number">2046</span
                                ><span class="ul-widget5__sales text-mute"
                                  >votes</span
                                >
                              </div>
                            </div> -->
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>--}}
                </div>
              </div>
            </div>
            <!-- latest-log-->
            <div class="col-lg-4 col-xl-4 mb-4">
              <div class="card">
                <div class="card-body">
                  <div class="ul-widget__head">
                    <div class="ul-widget__head-label">
                      <h3 class="ul-widget__head-title">Annoucement</h3>
                    </div>
                    <div class="ul-widget__head-toolbar">
                      <ul
                        class="nav nav-tabs nav-tabs-line nav-tabs-bold ul-widget-nav-tabs-line"
                        role="tablist"
                      >
                        <li class="nav-item">
                          <a
                            class="nav-link active show"
                            data-bs-toggle="tab"
                            href="#__g-widget-s6-tab1-content"
                            role="tab"
                            aria-selected="true"
                            >Today</a
                          >
                        </li>
                        <!-- <li class="nav-item">
                          <a
                            class="nav-link"
                            data-bs-toggle="tab"
                            href="#__g-widget-s6-tab2-content"
                            role="tab"
                            aria-selected="false"
                            >Month</a
                          >
                        </li> -->
                      </ul>
                    </div>
                  </div>
				  {{-- <div class="ul-widget__body">
                    <div class="tab-content">
                      <div
                        class="tab-pane active show"
                        id="__g-widget-s6-tab1-content"
                      >
                        <div class="ul-widget-s6__items">
                          <div class="ul-widget-s6__item">
                            <span class="ul-widget-s6__badge">
                              <p
                                class="badge-dot-primary ul-widget6__dot"
                              ></p> </span
                            ><span class="ul-widget-s6__text"
                              >12 new users registered</span
                            ><span class="ul-widget-s6__time">Just Now</span>
                          </div>
                          <div class="ul-widget-s6__item">
                            <span class="ul-widget-s6__badge">
                              <p class="badge-dot-success ul-widget6__dot"></p>
                            </span>
                            <p class="ul-widget-s6__text">
                              System shutdown<span
                                class="badge rounded-pill text-bg-primary m-2"
                                >Primary</span
                              >
                            </p>
                            <span class="ul-widget-s6__time">14 mins</span>
                          </div>
                        </div>
                      </div>
                      <div class="tab-pane" id="__g-widget-s6-tab2-content">
                        <div class="ul-widget2">
                          <div class="ul-widget-s6__items">
                            <div class="ul-widget-s6__item">
                              <span class="ul-widget-s6__badge">
                                <p
                                  class="badge-dot-danger ul-widget6__dot"
                                ></p> </span
                              ><span class="ul-widget-s6__text"
                                >44 new users registered</span
                              ><span class="ul-widget-s6__time">Just Now</span>
                            </div>
                            <div class="ul-widget-s6__item">
                              <span class="ul-widget-s6__badge">
                                <p
                                  class="badge-dot-warning ul-widget6__dot"
                                ></p>
                              </span>
                              <p class="ul-widget-s6__text">
                                System shutdown<span
                                  class="badge rounded-pill text-bg-primary m-2"
                                  >Primary</span
                                >
                              </p>
                              <span class="ul-widget-s6__time">14 mins</span>
                            </div>
                            <div class="ul-widget-s6__item">
                              <span class="ul-widget-s6__badge">
                                <p
                                  class="badge-dot-primary ul-widget6__dot"
                                ></p> </span
                              ><span class="ul-widget-s6__text"
                                >System error -<a
                                  class="typo_link text-danger"
                                  href=""
                                  >Danger state text</a
                                ></span
                              ><span class="ul-widget-s6__time">2 hrs </span>
                            </div>
                            <div class="ul-widget-s6__item">
                              <span class="ul-widget-s6__badge">
                                <p
                                  class="badge-dot-danger ul-widget6__dot"
                                ></p> </span
                              ><span class="ul-widget-s6__text"
                                >12 new users registered</span
                              ><span class="ul-widget-s6__time">Just Now</span>
                            </div>
                            <div class="ul-widget-s6__item">
                              <span class="ul-widget-s6__badge">
                                <p class="badge-dot-info ul-widget6__dot"></p>
                              </span>
                              <p class="ul-widget-s6__text">
                                System shutdown<span
                                  class="badge rounded-pill text-bg-success m-2"
                                  >Primary</span
                                >
                              </p>
                              <span class="ul-widget-s6__time">14 mins</span>
                            </div>
                            <div class="ul-widget-s6__item">
                              <span class="ul-widget-s6__badge">
                                <p
                                  class="badge-dot-dark ul-widget6__dot"
                                ></p> </span
                              ><span class="ul-widget-s6__text"
                                >System error -<a
                                  class="typo_link text-danger"
                                  href=""
                                  >Danger state text</a
                                ></span
                              ><span class="ul-widget-s6__time">2 hrs </span>
                            </div>
                            <div class="ul-widget-s6__item">
                              <span class="ul-widget-s6__badge">
                                <p
                                  class="badge-dot-primary ul-widget6__dot"
                                ></p> </span
                              ><span class="ul-widget-s6__text"
                                >12 new users registered</span
                              ><span class="ul-widget-s6__time">Just Now</span>
                            </div>
                            <div class="ul-widget-s6__item">
                              <span class="ul-widget-s6__badge">
                                <p
                                  class="badge-dot-success ul-widget6__dot"
                                ></p> </span
                              ><span class="ul-widget-s6__text"
                                >System shutdown<span
                                  class="badge rounded-pill text-bg-danger m-2"
                                  >Primary</span
                                ></span
                              ><span class="ul-widget-s6__time">14 mins</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>--}}
                </div>
              </div>
            </div>
          </div>
          <!-- end of row-->
          <!-- end of main-content -->
          <!-- end of main-content -->
          <!-- Footer Start -->
          <div class="flex-grow-1"></div>
          <!-- fotter end -->
        </div>
		@endrole
		
@role('Student')
<div class="row">          
    <div class="col-lg-12 col-md-12">
        <div class="row"> 
			<div class="col-lg-3 col-md-6 col-sm-6">
                <a href="{{ url('/public-page') }}">
                    <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                        <div class="card-body text-center"><i class="i-Money1"></i>
                            <div class="w-100 text-center">
                                <p class="text-primary text-16 line-height-1 m-0">Pay Fees Online</p>
								<p class="text-muted mt-2 mb-2">View More</p>                                
                            </div>
                        </div>
                    </div>
                </a>
            </div>
		
            <div class="col-lg-3 col-md-6 col-sm-6">
                <a href="{{ url('/student_fees_leadger') }}">
                    <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                        <div class="card-body text-center"><i class="i-Money-Bag"></i>
                            <div class="w-100 text-center">
								<p class="text-primary text-16 line-height-1 m-0">Fees Details</p>	
								<p class="text-muted mt-2 mb-2">View More</p>	
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6">
                <a href="{{ url('/route-vehicle-map') }}">
                    <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                        <div class="card-body text-center"><i class="i-Jeep"></i>
                            <div class="w-100 text-center">
                                <p class="text-primary text-16 line-height-1 m-0">Bus Facility</p>
								<p class="text-muted mt-2 mb-2">View More</p>  
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endrole


		
		
		
        <script>
          "use strict";

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }


document.addEventListener('DOMContentLoaded', function() {
    
  var echartElem1 = document.getElementById('echart1');

  if (echartElem1) {
    var echart1 = echarts.init(echartElem1);
    echart1.setOption(_objectSpread({}, echartOptions.defaultOptions, {}, {
      grid: echartOptions.gridAlignLeft,
      series: [_objectSpread({
        data: [30, 40, 20, 50, 40, 80, 90, 40]
      }, echartOptions.smoothLine, {
        lineStyle: _objectSpread({
          color: '#4CAF50'
        }, echartOptions.lineShadow),
        itemStyle: {
          color: '#4CAF50'
        }
      })]
    }));
    
  }

  
}, false);
        </script>

<script>
  function redirectToEnquiryData() {
      window.location.href = "{{ route('enquiry-data') }}"; // Replace 'enquiry-data' with your actual route name
  }
</script>

@endsection 
