<div class="side-content-wrap">
    <div class="sidebar-left open rtl-ps-none" data-perfect-scrollbar="" data-suppress-scroll-x="true">
        <ul class="navigation-left">
            @role('Student')
           <li class="nav-item" data-item="">
                <a class="nav-item-hold" href="{{url('admin-dashboard')}}"><i class="nav-icon i-Bar-Chart"></i><span class="nav-text">DASHBOARD</span></a>
                <!-- <div class="triangle"></div> -->
		   </li>

            {{-- <ul class="navigation-left">
              @role('Admin')
             <li class="nav-item" data-item="">
               <a class="nav-item-hold" href="{{url('admin-dashboard')}}"
            ><i class="nav-icon i-Bar-Chart"></i><span class="nav-text">DASHBOARD</span></a>
            <!-- <div class="triangle"></div> -->
            </li> --}}


			{{-- <li class="nav-item" data-item="setting">
                <a class="nav-item-hold" href="#"><i class="nav-icon i-Administrator"></i><span class="nav-text">PROFILE</span></a>
                <!-- <div class="triangle"></div> -->
            </li>--}}
            @endrole
            @role('Student')

            <li class="nav-item" data-item="transport">
                <a class="nav-item-hold" href="#"><i class="nav-icon i-Jeep"></i><span class="nav-text">TRANSPORT</span></a>
                <!-- <div class="triangle"></div> -->
            </li>
            @endrole

            @role('Student')
            <li class="nav-item" data-item="feesdetail">
                <a class="nav-item-hold" href="#"><i class="nav-icon i-Money1"></i><span class="nav-text">FEES DETAIL</span></a>
                <!-- <div class="triangle"></div> -->
            </li>
            @endrole

            @role('Student')
            {{--<li class="nav-item" data-item="">
                <a class="nav-item-hold" href="{{url('student_calender')}}"><i class="nav-icon i-File-Clipboard-File--Text"></i><span class="nav-text">CALENDER</span></a>
                <!-- <div class="triangle"></div> -->
            </li>--}}
            {{--<li class="nav-item" data-item="">
                <a class="nav-item-hold" href="{{url('student_announcement')}}"><i class="nav-icon i-Double-Tap"></i><span class="nav-text">ANNOUNCEMENT</span></a>
                <!-- <div class="triangle"></div> -->
            </li>--}}
            @endrole

            @role('Admin')
            <li class="nav-item" data-item="dashboard">
                <a class="nav-item-hold" href="#"><i class="nav-icon i-Dashboard"></i><span class="nav-text">Dashboard</span></a>
                <div class="triangle"></div>
            </li>
            @endrole

					@can('dashboard')
					<li class="nav-item" data-item="dashboard">
						<a class="nav-item-hold" href="#"><i class="nav-icon i-Dashboard"></i><span class="nav-text">Dashboard</span></a>
						<div class="triangle"></div>
					</li>
					@endcan
					@can('scholars')
					<li class="nav-item" data-item="scholars">
						<a class="nav-item-hold" href="#"><i class="nav-icon i-Student-Hat-2"></i><span class="nav-text">Scholars</span></a>
						<div class="triangle"></div>
					</li>
					@endcan
					@can('fees')
					<li class="nav-item" data-item="fees">
						<a class="nav-item-hold" href="#"><i class="nav-icon i-Money-2"></i><span class="nav-text">Fees</span></a>
						<div class="triangle"></div>
					</li>
					@endcan
					@can('transport')
					<li class="nav-item" data-item="transport">
						<a class="nav-item-hold" href="#"><i class="nav-icon i-Jeep"></i><span class="nav-text">Transport</span></a>
						<div class="triangle"></div>
					</li>
					@endcan
					@can('academic')
					<li class="nav-item" data-item="Academic">
						<a class="nav-item-hold" href="#"><i class="nav-icon i-Book"></i><span class="nav-text">Academic</span></a>
						<div class="triangle"></div>
					</li>
					@endcan
					@can('hrms')
					<li class="nav-item" data-item="hrms">
						<a class="nav-item-hold" href="#"><i class="nav-icon i-Add-UserStar"></i><span class="nav-text">HRMS</span></a>
						<div class="triangle"></div>
					</li>
					@endcan
					@can('setting')
					<li class="nav-item" data-item="setting">
						<a class="nav-item-hold" href="#"><i class="nav-icon i-Gear"></i><span class="nav-text">Setting</span></a>
						<div class="triangle"></div>
					</li>
					@endcan






            @role('Admin')
            <li class="nav-item" data-item="scholars">
                <a class="nav-item-hold" href="#"><i class="nav-icon i-Student-Hat-2"></i><span class="nav-text">Scholars</span></a>
                <div class="triangle"></div>
            </li>
            @endrole
            <!-- Fees -->
            @role('Admin')
            <li class="nav-item" data-item="fees">
                <a class="nav-item-hold" href="#"><i class="nav-icon i-Money-2"></i><span class="nav-text">Fees</span></a>
                <div class="triangle"></div>
            </li>
            @endrole
            <!-- Transport  -->
            @role('Admin')
            <li class="nav-item" data-item="transport">
                <a class="nav-item-hold" href="#"><i class="nav-icon i-Jeep"></i><span class="nav-text">Transport</span></a>
                <div class="triangle"></div>
            </li>
            @endrole
            <!-- Academic Module  -->
            @role('Admin')
            <li class="nav-item" data-item="Academic">
                <a class="nav-item-hold" href="#"><i class="nav-icon i-Book"></i><span class="nav-text">Academic</span></a>
                <div class="triangle"></div>
            </li>
            @endrole

            @role('Admin')
			{{-- HRMS --}}

            <li class="nav-item" data-item="hrms">
                <a class="nav-item-hold" href="#"><i class="nav-icon i-Add-UserStar"></i><span class="nav-text">HRMS</span></a>
                <div class="triangle"></div>
            </li>

            <li class="nav-item" data-item="setting">
                <a class="nav-item-hold" href="#"><i class="nav-icon i-Gear"></i><span class="nav-text">Setting</span></a>
                <div class="triangle"></div>
            </li>
            @endrole

            </li>
        </ul>
    </div>
    <div class="sidebar-left-secondary rtl-ps-none" data-perfect-scrollbar="" data-suppress-scroll-x="true">
        <!-- schalars-->
        <ul class="childNav" data-parent="scholars">
            <!-- Scholar Module Menu - Structured View -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-File-Clipboard-Text--Image"></i>
					<span class="item-name">Pre-Admission</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<li><a href="{{url('admin-preenquiryform')}}">Pre-Enquiry Entry</a></li>
					<li><a href="{{url('admin-pre-enquiryform')}}">Pre-Enquiry List</a></li>
					<li><a href="{{url('admin-enquiryform')}}">Enquiry Entry</a></li>
					<li><a href="{{url('adminenquirylist')}}">Enquiry List</a></li>
					<li><a href="{{url('followupdate')}}">Follow-Up Scheduling</a></li>
					<li><a href="{{url('selection-process')}}">Selection Process</a></li>
				</ul>
			</li>

			<li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-Add-User"></i>
					<span class="item-name">Admission & Registration</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<li><a href="{{url('add-student-registrations')}}">Student Registration</a></li>
					<li><a href="{{url('student-registrations')}}">Registration List</a></li>
				</ul>
			</li>

			<li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-Certificate"></i>
					<span class="item-name">Certificates</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<li><a href="{{url('bonafide-certificate')}}">Bonafide Certificate</a></li>
				</ul>
			</li>

			<li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-Bar-Chart"></i>
					<span class="item-name">Reports</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<li><a href="{{url('inquiry-report')}}">Inquiry Reports</a></li>
					<li><a href="{{url('admin-pre-enquiryfeeslist')}}">Form Fee Reports</a></li>
					<li><a href="{{url('duestuamount')}}">Admission Fee Reports</a></li>
				</ul>
			</li>
        </ul>
        {{-- Dashboard  --}}

        <ul class="childNav" data-parent="dashboard">
            <li class="nav-item">
                <a href="{{url('admin-dashboard')}}"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">Dashboard</span></a>
            </li>            {{-- <li class="nav-item dropdown-sidemenu">
              <a href="#"
                ><i class="nav-icon i-File-Clipboard-Text--Image"></i
                {{-- ><span class="item-name">Admission Process</span --}}
            {{-- ><i class="dd-arrow i-Arrow-Down"></i --}}
            {{-- ></a> --}}
            {{-- <ul class="submenu"> --}}
            {{-- <li><a href="{{url('admin-preenquiryform')}}">Dashboard</a></li> --}}

            {{-- </ul> --}}
            {{-- </li>  --}}
        </ul>
        <!-- end scholars-->
        <!-- Fees-->
        <ul class="childNav" data-parent="fees">

			<!-- Collection -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#"><i class="nav-icon i-Money-Bag"></i><span class="item-name">Collection</span><i class="dd-arrow i-Arrow-Down"></i></a>
				<ul class="submenu">
					<li><a href="{{ url('fees_receipt_challan') }}">Fees Receipt Challan</a></li>
					<li><a href="{{ url('student-online-fees') }}">Online Collection</a></li>
					<li><a href="{{ url('student-daily-online-fees-reconcile') }}">Daily Online Fees Reconciliation</a></li>
					<li><a href="{{ url('student-daily-online-fees-settlement') }}">Daily Online Fees Settlements</a></li>
					<li><a href="{{ url('daily-collection') }}">Daily / Yearly Collection</a></li>
					<li><a href="{{ url('student_ledger') }}">Student Ledger</a></li>
				</ul>
			</li>

			<!-- Fee Master -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#"><i class="nav-icon i-Receipt-4"></i><span class="item-name">Fee Master</span><i class="dd-arrow i-Arrow-Down"></i></a>
				<ul class="submenu">
					<li><a href="{{ url('fees-master-student') }}">Student Fees Master</a></li>
					<li><a href="{{ url('fees-types-master') }}">Fees Type Master</a></li>
					<li><a href="{{ url('bus-fees-master') }}">Bus Fees Master</a></li>
					<li><a href="{{ url('late-fees-master') }}">Late Fees Master</a></li>
					<li><a href="{{ url('advance-next-year-fees') }}">Advance Next Year Fees</a></li>
					<li><a href="{{ url('course-fees-head-orders') }}">Course Fees Head Order</a></li>
					<li><a href="{{url('generate-due-chart')}}">Generate Fees Due Chart</a></li>
				</ul>
			</li>

			<!-- Structure -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#"><i class="nav-icon i-Checked-User"></i><span class="item-name">Structure</span><i class="dd-arrow i-Arrow-Down"></i></a>
				<ul class="submenu">
					<li><a href="{{ url('create-course-fees-structure-master') }}">Create Course Fees Structure</a></li>
					<li><a href="{{ url('course-fees-structure-master-list') }}">Course Fees Structure List</a></li>
					<li><a href="{{ url('terms') }}">Terms Master</a></li>
				</ul>
			</li>

			<!-- Reports -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#"><i class="nav-icon i-Bar-Chart"></i><span class="item-name">Reports</span><i class="dd-arrow i-Arrow-Down"></i></a>
				<ul class="submenu">
					<li><a href="{{ url('defaulters') }}">Defaulters List</a></li>
					<li><a href="{{ url('exemptions') }}">Exemption Report</a></li>
				</ul>
			</li>

		</ul>

        <!-- End Fees-->

        <!-- Transport -->
        <ul class="childNav" data-parent="transport">
            <!-- <li class="nav-item dropdown-sidemenu">
              <a href="{{url('all-transport-view')}}"
                ><i class="nav-icon i-File-Clipboard-Text--Image"></i
                ><span class="item-name">All</span
                ></a>
            </li> -->
            @role('Admin')
			   <li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-Settings"></i>
					<span class="item-name">Configuration</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<li><a href="{{url('route-vehicle-map')}}">Route List</a></li>
					<li><a href="{{url('addvehical')}}">Vehicle</a></li>
					<li><a href="{{url('area-master')}}">Area Master</a></li>
					<li><a href="{{url('bus-stop')}}">Bus Stops</a></li>
					<li><a href="{{url('driver-conductor-master')}}">Driver / Conductor Master</a></li>
					<!--<li><a href="{{url('list-party-master')}}">Party Master</a></li>-->
				</ul>
			</li>

			<li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-Wrench"></i>
					<span class="item-name">Maintenance</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<li><a href="{{url('maintenance-head-master')}}">Maintenance Head Master</a></li>
					<li><a href="{{url('bus-maintenance-entry')}}">Vehicle Maintenance</a></li>
				</ul>
			</li>

			<li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-Clock"></i>
					<span class="item-name">Schedule</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<li><a href="{{url('route-master')}}">Route Master</a></li>
					<li><a href="{{url('schedulemaster')}}">Schedule Master</a></li>
				</ul>
			</li>

			<li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-Checked-User"></i>
					<span class="item-name">Assignments</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<li><a href="{{url('scholarbusassign')}}">Scholar Bus Assign</a></li>
					<li><a href="{{url('teacherbusassign')}}">Teacher Bus Assign</a></li>
				</ul>
			</li>

			<li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-Bus"></i>
					<span class="item-name">Bus Live Location</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<!--<li><a href="{{url('bus-attandence-list')}}">Bus Attendance</a></li>-->
					<li><a href="{{url('bus_data')}}">Bus Data</a></li>
				</ul>
			</li>

            @endrole

            @role('Student')
            <!--<li class="nav-item">
                <a href="{{url('bus_data')}}"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">Bus Status</span></a>
            </li>-->
			<li class="nav-item">
                <a href="{{url('route-vehicle-map')}}"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">Route List</span></a>
            </li>
            @endrole

            <!-- <li class="nav-item dropdown-sidemenu">
              <a href="#"
                ><i class="nav-icon i-File-Clipboard-Text--Image"></i
                ><span class="item-name">Transactions</span
                ><i class="dd-arrow i-Arrow-Down"></i
              ></a>
              <ul class="submenu">
                <li><a href="{{url('student-registrations')}}">Registration</a></li>
              </ul>
            </li> -->
            <!-- <li class="nav-item dropdown-sidemenu">
              <a href="#"
                ><i class="nav-icon i-File-Clipboard-Text--Image"></i
                ><span class="item-name">Reports</span
                ><i class="dd-arrow i-Arrow-Down"></i
              ></a>
              <ul class="submenu">
                <li><a href="{{url('student-registrations')}}">Registration</a></li>
              </ul>
            </li> -->
        </ul>
        <!-- End Transport -->
        <!-- Setting -->
        <ul class="childNav" data-parent="setting">
            @role('Admin')
            <li class="nav-item">
                <a href="{{url('users')}}"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">Users</span></a>
            </li>
            <li class="nav-item">
                <a href="{{ url('roles') }}"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">Roles</span></a>
            </li>
            <li class="nav-item">
                <a href="{{ url('roles') }}"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">Permission</span></a>
            </li>
            @endrole
            @role('Student')
            <li class="nav-item">
                <a href="{{url('scholars_profile/')}}/{{Auth::user()->id}}"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">Scholars Profile</span></a>
            </li>
            @endrole
        </ul>
        <!-- End Setting -->

        <!-- feesdetail -->
        <ul class="childNav" data-parent="feesdetail">
            @role('Student')
            <li class="nav-item">
                <a href="{{url('fees_payments/'.Auth::user()->id)}}"><i class="nav-icon i-Money-2"></i><span class="item-name">Fees Payments</span></a>
            </li>
            <li class="nav-item">
                <a href="{{url('student_fees_leadger')}}"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">Fees Details</span></a>
            </li>
            @endrole
        </ul>
        <!-- End feesdetail -->

        <!-- Academics -->
        <ul class="childNav" data-parent="Academic">
			<!-- SESSION & CLASS STRUCTURE -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-Calendar-4"></i>
					<span class="item-name">Session & Class Setup</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<li><a href="{{url('session')}}">Make Session</a></li>
					<li><a href="{{url('classes')}}">Classes</a></li>
					<!--<li><a href="{{url('sectionmaster')}}">Section Master</a></li>-->
					<li><a href="{{url('streammaster')}}">Stream Master</a></li>
					<li><a href="{{url('student-transfer')}}">Student Transfer</a></li>
					<li><a href="{{url('sectionAssign')}}">Assign Section to Student</a></li>
				</ul>
			</li>

			<!-- SUBJECT & TEACHER MANAGEMENT -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-Book"></i>
					<span class="item-name">Subjects & Teachers</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<li><a href="{{url('subjectmaster')}}">Subject Master</a></li>
					<li><a href="{{url('subjectcombinatiomaster')}}">Subject Combination</a></li>
					<li><a href="{{url('AssignSubject')}}">Assign Subject to Student</a></li>
					<li><a href="{{url('teachers')}}">Teachers</a></li>
					<li><a href="{{url('teachersubject')}}">Teacher Subject Mapping</a></li>
					<li><a href="{{url('calssese-assigne-to-teacher')}}">Assign Class to Teacher</a></li>
				</ul>
			</li>

			<!-- EXAMINATION & GRADING -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-Check"></i>
					<span class="item-name">Examination & Grading</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<li><a href="{{url('examtype')}}">Exam Type</a></li>
					<li><a href="{{url('exammaster')}}">Exam Master</a></li>
					<li><a href="{{url('marks')}}">Enter Marks</a></li>
					<li><a href="{{url('show_report_marks')}}">Report Marks</a></li>
					<li><a href="{{url('marksheet')}}">Marksheet</a></li>
					<li><a href="{{url('greadingmaster')}}">Grading Master</a></li>
					<li><a href="{{url('gread')}}">Grades</a></li>
					<li><a href="{{url('remarkmaster')}}">Remark Master</a></li>
				</ul>
			</li>

			<!-- ATTENDANCE MANAGEMENT -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-Checked-User"></i>
					<span class="item-name">Attendance</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<li><a href="{{url('dailyattandence')}}">Daily Attendance</a></li>
					<li><a href="{{url('student-attandence-report')}}">Student-wise Attendance</a></li>
					<li><a href="{{url('Attandencereports')}}">Attendance Reports</a></li>
				</ul>
			</li>

			<!-- ACCOUNTING HEADS & GROUPS -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#">
					<i class="nav-icon i-Structure"></i>
					<span class="item-name">Heads & Groups</span>
					<i class="dd-arrow i-Arrow-Down"></i>
				</a>
				<ul class="submenu">
					<li><a href="{{url('primarygroup')}}">Primary Group</a></li>
					<li><a href="{{url('groupmaster')}}">Group</a></li>
					<li><a href="{{url('headmaster')}}">Head</a></li>
					<li><a href="{{url('subheadmaster')}}">Sub Head</a></li>
				</ul>
			</li>

            {{-- @endrole  --}}

            {{-- <ul class="childNav" data-parent="Academic">
              <li class="nav-item">
                  <a href="{{url('Academics')}}"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">All</span></a>
            </li>

            <li class="nav-item dropdown-sidemenu">
                <a href="#"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">Academic Management</span><i class="dd-arrow i-Arrow-Down"></i></a>

                <ul class="submenu">
                    <li><a href="{{url('session')}}">Make Session</a></li>
                    <li><a href="{{url('exammaster')}}">Exam</a></li>
                    <li><a href="{{url('examtype')}}">Exam Type</a></li>
                    <li><a href="{{url('teachers')}}">Teachers</a></li>
                    <li><a href="{{url('marksheet')}}">Marksheet</a></li>
                    <li><a href="{{url('marks')}}">Marks</a></li>
                    <li><a href="{{url('AssignSubject')}}">Assigning Subject To Student</a></li>
                    <li><a href="{{url('student-attandence-report')}}">Student Wise Attendance</a></li>
                    <li><a href="{{url('subjectmaster')}}">Subject</a></li>
                    <li><a href="{{url('teachersubject')}}">Teachers subject</a></li>
                    <li><a href="{{url('Attandencelist')}}">Attendance list</a></li>
                    <li><a href="{{url('Attandencereports')}}">Attendance Reports</a></li>
                    <li><a href="{{url('dailyattandence')}}">Daily Attendance</a></li>
                    <li><a href="{{url('primarygroup')}}">Primary Group</a></li>
                    <li><a href="{{url('groupmaster')}}">Group</a></li>
                    <li><a href="{{url('headmaster')}}">Head</a></li>
                    <li><a href="{{url('subheadmaster')}}">Sub Head</a></li>
                    <li><a href="{{url('greadingmaster')}}">Grading</a></li>
                    <li><a href="{{url('calssese-assigne-to-teacher')}}">Assign Class To Teacher</a></li>
                    <li><a href="{{url('streammaster')}}">stream Master</a></li>
                    <li><a href="{{url('sectionmaster')}}">Section Master</a></li>
                    <li><a href="{{url('remarkmaster')}}">Remark Master</a></li>
                    <li><a href="{{url('subjectcombinatiomaster')}}">Subject Combination</a></li>
                    <li class="hidden-after-subject-combination"><a href="#">Hidden Item 1</a></li>
                    <li class="hidden-after-subject-combination"><a href="#">Hidden Item 2</a></li>
                    <!-- Add more hidden items as needed -->
                </ul>
            </li>
        </ul>

        <style>
            .hidden-after-subject-combination {
                display: none;
            }

        </style> --}}

        <!-- <li class="nav-item dropdown-sidemenu">
              <a href=""
                ><i class="nav-icon i-File-Clipboard-Text--Image"></i
                ><span class="item-name">Transactions</span
                ><i class="dd-arrow i-Arrow-Down"></i
              ></a>
              <ul class="submenu">
                <li><a href="{{url('student-registrations')}}">Registration</a></li>
              </ul>
            </li> -->
        <!-- <li class="nav-item dropdown-sidemenu">
              <a href=""
                ><i class="nav-icon i-File-Clipboard-Text--Image"></i
                ><span class="item-name">Reports</span
                ><i class="dd-arrow i-Arrow-Down"></i
              ></a>
              <ul class="submenu">
                <li><a href="{{url('student-registrations')}}">Registration</a></li>
              </ul>
            </li> -->
        </ul>
        <!-- End Academic -->
        {{-- HRMS --}}


        <ul class="childNav" data-parent="hrms">
            {{-- <li class="nav-item">
     <a href="{{url('hrms')}}"
            ><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">All</span></a>
            </li> --}}
            <!-- EMPLOYEE MANAGEMENT -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#"><i class="nav-icon i-Add-User"></i><span class="item-name">Employee Management</span><i class="dd-arrow i-Arrow-Down"></i></a>
				<ul class="submenu">
					<li><a href="{{ url('employee') }}">Employees</a></li>
					<li><a href="{{ url('department') }}">Departments</a></li>
					<li><a href="{{ url('position') }}">Position</a></li>
					<li><a href="{{ url('stafftype') }}">Staff Type</a></li>
				</ul>
			</li>

			<!-- SHIFT MANAGEMENT -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#"><i class="nav-icon i-Clock-Back"></i><span class="item-name">Shift Management</span><i class="dd-arrow i-Arrow-Down"></i></a>
				<ul class="submenu">
					<li><a href="{{ url('shifttype') }}">Shift Type</a></li>
					<li><a href="{{ url('shifts') }}">Create Shift</a></li>
				</ul>
			</li>

			<!-- ATTENDANCE -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#"><i class="nav-icon i-Checked-User"></i><span class="item-name">Attendance</span><i class="dd-arrow i-Arrow-Down"></i></a>
				<ul class="submenu">
					<li><a href="{{ url('employeeattendance') }}">Biometric Attendance</a></li>
					<li><a href="{{ url('manual-attendance') }}">Manual Attendance</a></li>
				</ul>
			</li>

			<!-- LEAVE MANAGEMENT -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#"><i class="nav-icon i-Calendar"></i><span class="item-name">Leave Management</span><i class="dd-arrow i-Arrow-Down"></i></a>
				<ul class="submenu">
					<li><a href="{{ url('leave_Types') }}">Leave Type Master</a></li>
					<li><a href="{{ url('leave_staff_allocation') }}">Leave Staff Allocation</a></li>
					<li><a href="{{ url('employee-leave-balances') }}">Employee Leave Balance</a></li>
					<li><a href="{{ url('leaverequests') }}">Leave Requests</a></li>
					<li><a href="{{ url('employeeleaves') }}">Admin Leave Requests</a></li>
					<li><a href="{{ url('employee-leave-report') }}">Employee Wise Leave Report</a></li>
				</ul>
			</li>

			<!-- COMP OFF -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#"><i class="nav-icon i-Add-File"></i><span class="item-name">Comp Off</span><i class="dd-arrow i-Arrow-Down"></i></a>
				<ul class="submenu">
					<li><a href="{{ url('compoff') }}">Comp Off Requests</a></li>
					<li><a href="{{ url('employee-compoff-requests') }}">Admin Comp Off Requests</a></li>
				</ul>
			</li>
			<!-- SECURITY DEPOSIT -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#"><i class="nav-icon i-Money-2"></i><span class="item-name">Manage Security Deposit</span><i class="dd-arrow i-Arrow-Down"></i></a>
				<ul class="submenu">
					<li><a href="{{ url('employeeloan') }}">Manage Security Deposit</a></li>
					<li><a href="{{ url('security-deposit-refund') }}">Refund Security Deposit</a></li>
				</ul>
			</li>
			<!-- PAYROLL -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#"><i class="nav-icon i-Money-2"></i><span class="item-name">Payroll</span><i class="dd-arrow i-Arrow-Down"></i></a>
				<ul class="submenu">
					<li><a href="{{ url('basicDeduction') }}">Manage Deductions</a></li>
					<li><a href="{{ url('employeeloan') }}">Manage Security Deposit</a></li>
					<li><a href="{{ url('security-deposit-refund') }}">Refund Security Deposit</a></li>
					<li><a href="{{ url('salaries') }}">Salaries</a></li>
					<li><a href="{{ url('payroll') }}">Generate Payroll</a></li>
					<li><a href="{{ url('employee-salary-report') }}">Salary Report</a></li>
				</ul>
			</li>

			<!-- OTHER -->
			<li class="nav-item">
				<a href="{{ url('holidays') }}"><i class="nav-icon i-Calendar-3"></i><span class="item-name">Holidays</span></a>
			</li>
			<!-- REPORT -->
			<li class="nav-item dropdown-sidemenu">
				<a href="#"><i class="nav-icon i-Money-2"></i><span class="item-name">Reports</span><i class="dd-arrow i-Arrow-Down"></i></a>
				<ul class="submenu">
					<li><a href="{{ url('apply-report') }}">Employee Deduction Overview</a></li>
					<li><a href="{{ url('employee-deduction-report') }}">Employee Wise Deductions Report</a></li>
					<li><a href="{{ url('employee-leave-report') }}">Employee Wise Leave Report</a></li>
					<li><a href="{{ url('employee-leave-report-date-wise') }}">Date Wise Employee Leave Report</a></li>
				</ul>
			</li>



            <!-- schalars-->
            <ul class="childNav" data-parent="setting">
                @role('Student')
                <li class="nav-item">
                    <a href="{{url('scholars_profile/')}}/{{Auth::user()->id}}"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">Scholars Profile</span></a>
                </li>
                @endrole
                <!-- @role('Admin')
            <li class="nav-item">
              <a href="{{url('users')}}"
                ><i class="nav-icon i-File-Clipboard-Text--Image"></i
                ><span class="item-name">Users</span></a
              >
            </li>
            <li class="nav-item">
              <a href="{{ url('roles') }}"
                ><i class="nav-icon i-File-Clipboard-Text--Image"></i
                ><span class="item-name">Roles</span></a
              >
            </li>
            @endrole -->
            </ul>
            <!-- End schalars-->

    </div>
    <div class="sidebar-overlay"></div>
</div>
