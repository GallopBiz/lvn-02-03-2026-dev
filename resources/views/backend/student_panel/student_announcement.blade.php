@extends('backend.layouts.main')
@section('main-container')
<!-- ============ Body content start ============= -->
<div class="main-content">
          <div class="breadcrumb">
            <h1>Announcement</h1>
            <ul>
              <!-- <li><a href="href">Apps</a></li> -->
              <li>New Announcement</li>
            </ul>
          </div>
          <div class="separator-breadcrumb border-top"></div>

        <div class="main-content">
          <!-- MAIN SIDEBAR CONTAINER-->
          <div
            class="inbox-main-sidebar-container"
            data-sidebar-container="main"
          >
            <div class="inbox-main-content" data-sidebar-content="main">
              <!-- SECONDARY SIDEBAR CONTAINER-->
              <div
                class="inbox-secondary-sidebar-container box-shadow-1"
                data-sidebar-container="secondary"
              >
                <div data-sidebar-content="secondary">
                  <div
                    class="inbox-secondary-sidebar-content position-relative"
                    style="min-height: 500px"
                  >
                    <div
                      class="inbox-topbar box-shadow-1 perfect-scrollbar rtl-ps-none ps-3"
                      data-suppress-scroll-y="true"
                    >
                      <!-- <span class="d-sm-none">Test</span>--><a
                        class="link-icon d-md-none"
                        data-sidebar-toggle="main"
                        ><i class="icon-regular i-Arrow-Turn-Left"></i></a
                      ><a
                        class="link-icon me-3 d-md-none"
                        data-sidebar-toggle="secondary"
                        ><i class="icon-regular me-1 i-Left-3"></i> Inbox</a
                      >
                      <div class="d-flex">
                        <a class="link-icon me-3" href=""
                          ><i class="icon-regular i-Mail-Reply"></i> Reply</a
                        ><a class="link-icon me-3" href=""
                          ><i class="icon-regular i-Mail-Reply-All"></i>
                          Forward</a
                        ><a class="link-icon me-3" href=""
                          ><i class="icon-regular i-Mail-Reply-All"></i>
                          Delete</a
                        >
                      </div>
                    </div>
                    <!-- EMAIL DETAILS-->
                    <div
                      class="inbox-details perfect-scrollbar rtl-ps-none"
                      data-suppress-scroll-x="true"
                    >
                      <div class="d-flex align-items-center mb-3">
                        <div class="me-2" style="width: 36px">
                          <img
                            class="rounded-circle"
                            src="{{url('assets/backend')}}/images/faces/1.jpg"
                            alt=""
                          />
                        </div>
                        <div class="">
                          <p class="m-0">Lvn Admin</p>
                          <p class="text-12 text-muted m-0">20 Dec, 2022</p>
                        </div>
                      </div>
                      <h4 class="mb-3">New Announcement</h4>
                      <div>
                        <p>
                          Natus consequuntur perspiciatis esse beatae illo quos
                          eaque.
                        </p>
                        <p>
                          Earum, quisquam, fugit? Numquam dolor magni nisi?
                          Suscipit odit, ipsam iusto enim culpa, temporibus vero
                          possimus error voluptates sequi. Iusto ipsam, nihil?
                          Eveniet modi maxime animi excepturi a dignissimos
                          doloribus, inventore sed ratione, ducimus atque earum
                          maiores tenetur officia commodi dicta tempora
                          consequatur non nesciunt ipsam, consequuntur quia fuga
                          aspernatur impedit et? Natus, earum.
                        </p>
                        <blockquote class="blockquote">
                          Earum, quisquam, fugit? Numquam dolor magni nisi?
                          Suscipit odit, ipsam iusto enim culpa, temporibus vero
                          possimus error voluptates sequi.
                        </blockquote>
                        <p>
                          Earum, quisquam, fugit? Numquam dolor magni nisi?
                          Suscipit odit, ipsam iusto enim culpa, temporibus vero
                          possimus error voluptates sequi. Iusto ipsam, nihil?
                          Eveniet modi maxime animi excepturi a dignissimos
                          doloribus, inventore sed ratione, ducimus atque earum
                          maiores tenetur officia commodi dicta tempora
                          consequatur non nesciunt ipsam, consequuntur quia fuga
                          aspernatur impedit et? Natus, earum.
                        </p>
                        <br />
                        Thanks<br />
                        Jhone
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Secondary Inbox sidebar-->
                <div
                  class="inbox-secondary-sidebar perfect-scrollbar rtl-ps-none"
                  data-sidebar="secondary"
                >
                  <i
                    class="sidebar-close i-Close"
                    data-sidebar-toggle="secondary"
                  ></i>
                  <div class="mail-item">
                    <div class="avatar">
                      <img src="{{url('assets/backend')}}/images/faces/1.jpg" alt="" />
                    </div>
                    <div class="col-xs-6 details">
                      <span class="name text-muted">Lvn Admin</span>
                      <p class="m-0">New annoucement</p>
                    </div>
                    <div class="col-xs-3 date">
                      <span class="text-muted">20 Jan 2023</span>
                    </div>
                  </div>
                  <div class="mail-item">
                    <div class="avatar">
                      <img src="{{url('assets/backend')}}/images/faces/1.jpg" alt="" />
                    </div>
                    <div class="col-xs-6 details">
                      <span class="name text-muted">Lvn Super Admin</span>
                      <p class="m-0">Confirm your email</p>
                    </div>
                    <div class="col-xs-3 date">
                      <span class="text-muted">12 Mar 2023</span>
                    </div>
                  </div>
                  <div class="mail-item">
                    <div class="avatar">
                      <img src="{{url('assets/backend')}}/images/faces/1.jpg" alt="" />
                    </div>
                    <div class="col-xs-6 details">
                      <span class="name text-muted">Lvn Admin</span>
                      <p class="m-0">Confirm your Exam Date</p>
                    </div>
                    <div class="col-xs-3 date">
                      <span class="text-muted">20 Dec 2022</span>
                    </div>
                  </div>
                  <div class="mail-item">
                    <div class="avatar">
                      <img src="{{url('assets/backend')}}/images/faces/1.jpg" alt="" />
                    </div>
                    <div class="col-xs-6 details">
                      <span class="name text-muted">Lvn Admin</span>
                      <p class="m-0">Result - 2022=2023</p>
                    </div>
                    <div class="col-xs-3 date">
                      <span class="text-muted">20 june 2023</span>
                    </div>
                  </div>
                  <div class="mail-item">
                    <div class="avatar">
                      <img src="{{url('assets/backend')}}/images/faces/1.jpg" alt="" />
                    </div>
                    <div class="col-xs-6 details">
                      <span class="name text-muted">Lvn Admin</span>
                      <p class="m-0">Confirm your email</p>
                    </div>
                    <div class="col-xs-3 date">
                      <span class="text-muted">20 Dec 2018</span>
                    </div>
                  </div>
                  <div class="mail-item">
                    <div class="avatar">
                      <img src="{{url('assets/backend')}}/images/faces/1.jpg" alt="" />
                    </div>
                    <div class="col-xs-6 details">
                      <span class="name text-muted">Lvn Admin</span>
                      <p class="m-0">New Announcement</p>
                    </div>
                    <div class="col-xs-3 date">
                      <span class="text-muted">20 Dec 2018</span>
                    </div>
                  </div>
                  <div class="mail-item">
                    <div class="avatar">
                      <img src="{{url('assets/backend')}}/images/faces/1.jpg" alt="" />
                    </div>
                    <div class="col-xs-6 details">
                      <span class="name text-muted">Lvn Super Admin</span>
                      <p class="m-0">New Announcement</p>
                    </div>
                    <div class="col-xs-3 date">
                      <span class="text-muted">20 Dec 2018</span>
                    </div>
                  </div>
                  <div class="mail-item">
                    <div class="avatar">
                      <img src="{{url('assets/backend')}}/images/faces/1.jpg" alt="" />
                    </div>
                    <div class="col-xs-6 details">
                      <span class="name text-muted">Lvn Admin</span>
                      <p class="m-0">New Announcement</p>
                    </div>
                    <div class="col-xs-3 date">
                      <span class="text-muted">20 Dec 2018</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- MAIN INBOX SIDEBAR-->
            
          
          <!-- end of main-content -->
@endsection
   
 
