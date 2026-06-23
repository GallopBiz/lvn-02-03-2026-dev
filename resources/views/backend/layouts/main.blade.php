<!DOCTYPE html>
<html lang="en" dir="">
   @include('backend.layouts.head')
   @if(Request::segment(1)=='fees_receipt_challan')

   <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
   @endif

   <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css" rel="stylesheet" />

   <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet"/>
   <link href="{{url('assets/backend')}}/css/themes/lite-purple.css" rel="stylesheet" />
   <link href="{{url('assets/backend')}}/css/plugins/perfect-scrollbar.css" rel="stylesheet"/>
   <link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/fontawesome-5.css"/>
   <link href="{{url('assets/backend')}}/css/plugins/metisMenu.min.css" rel="stylesheet"/>
   <link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/toastr.css" />
   <link rel="stylesheet"  href="{{url('assets/backend')}}/css/plugins/perfect-scrollbar.min.css" />

   @if(Request::segment(1)=='add-student-registrations'  || Request::segment(1)=='employee' || Request::segment(1)=='view-employee' || Request::segment(1)=='payroll' || Request::segment(1)=='admin-enquiryform'|| Request::segment(1)=='student-master' || Request::segment(1)=='follow' || Request::segment(1)=='enquiryeditlist')
   <meta name="csrf-token" content="{{ csrf_token() }}">
   <link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/smart.wizard/smart_wizard.min.css" />
   <link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/smart.wizard/smart_wizard_theme_arrows.min.css" />
   <link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/smart.wizard/smart_wizard_theme_circles.min.css" />
   <link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/smart.wizard/smart_wizard_theme_dots.min.css" />
   <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
@endif
   @if(Request::segment(1)=='add-student-registrations' || Request::segment(1)=='users' ||
   Request::segment(1)=='fees-type-master' || Request::segment(1)=='bus-fees-master'
   || Request::segment(1)=='view'  || Request::segment(1)=='selection-process' || Request::segment(1)=='student-registrations' || Request::segment(1)=='employee'  || Request::segment(1)=='view-employee' || Request::segment(1)=='payroll'
   ||  Request::segment(1)=='filter-student-registration' || Request::segment(1)=='addvehical'|| Request::segment(1)=='bus-attandence-list' ||
   Request::segment(1)=='bus-stop' || Request::segment(1)=='admin-enquiryform' || Request::segment(1)=='followupdate'
   || Request::segment(1)=='NatureOfWork' || Request::segment(1) == 'maintenance-head-master' ||
   Request::segment(1) == 'editg-maintenance-group-master' || Request::segment(1) == 'view-maintenance-head-master'
   || Request::segment(1) == 'area-master' || Request::segment(1) == 'route-master'
   || Request::segment(1) == 'view-bus-route-master' || Request::segment(1)=='course-fees-head-orders'
   || Request::segment(1)=='list-rto-paper'|| Request::segment(1)=='driver-conductor-master'
      || Request::segment(1)=='view-driver-conductor-master' || Request::segment(1)=='late-fees-master'
      || Request::segment(1)=='fees-types-master' || Request::segment(1)=='course-fees-structure-master-list'
      || Request::segment(1)=='bus_data' || Request::segment(1)=='roles' || Request::segment(1)=='scholarbusassign'
      || Request::segment(1)=='scholarbusassign_student' || Request::segment(1)=='view-area-master'
      || Request::segment(1)=='rtopaper'|| Request::segment(1)=='studentattandancereport'|| Request::segment(1)=='dailyattandence'
      || Request::segment(1)=='subjectmaster' || Request::segment(1)=='create-course-fees-structure-master'
      || Request::segment(1)=='teachersubject'|| Request::segment(1)=='calssese-assigne-to-teacher'
      || Request::segment(1)=='admin-pre-enquiryform'|| Request::segment(1)=='fees-master-student'|| Request::segment(1)=='permission'||
      Request::segment(1)=='duestuamount'|| Request::segment(1)=='filter-duestuamount' || Request::segment(1)=='enquiry-data'||
      Request::segment(1)=='filter-enquiry-list' || Request::segment(1)=='gread' ||
      Request::segment(1)=='show_report_markss' || Request::segment(1)=='show_report_marks' || Request::segment(1)=='exammaster'
      || Request::segment(1)=='internal-assessment-master')
   <link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/datatables.min.css"/>
   @endif
   <body class="text-start">
      <div class="app-admin-wrap layout-sidebar-large">
         @include('backend.layouts.header')
         @include('backend.layouts.sidebar')
         <div class="main-content-wrap sidenav-open d-flex flex-column">
            @include('backend.layouts.flash-message')
            @yield('main-container')
            @include('backend.layouts.footer')
         </div>
      </div>
         @include('backend.layouts.headerSearchBar')
         <!-- script js -->
         <script src="{{url('assets/backend')}}/js/plugins/jquery-3.3.1.min.js"></script>
         <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
         <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
         <script src="{{url('assets/backend')}}/js/plugins/bootstrap.bundle.min.js"></script>
         <script src="{{url('assets/backend')}}/js/plugins/perfect-scrollbar.min.js"></script>
         <script src="{{url('assets/backend')}}/js/scripts/script.min.js"></script>
         <script src="{{url('assets/backend')}}/js/scripts/sidebar.large.script.min.js"></script>
         <script src="{{url('assets/backend')}}/js/plugins/echarts.min.js"></script>
         <script src="{{url('assets/backend')}}/js/scripts/echart.options.min.js"></script>
         <script src="{{url('assets/backend')}}/js/scripts/dashboard.v1.script.min.js"></script>
         <script src="{{url('assets/backend')}}/js/scripts/customizer.script.min.js"></script>
         <script>
            $(document).ready(function() {
               $('select.select2, select.deductions').each(function() {
                  var select = $(this);
                  var isMultiple = select.prop('multiple');
                  var placeholder = select.data('placeholder') || select.attr('placeholder') || '';

                  if (!placeholder) {
                     var emptyOption = select.find('option[value=""], option[disabled]').first();
                     placeholder = emptyOption.length ? emptyOption.text().trim() : (isMultiple ? 'Select options' : 'Select option');
                  }

                  if (select.data('select2')) {
                     select.select2('destroy');
                  }

                  select.select2({
                     width: '100%',
                     placeholder: placeholder,
                     allowClear: !isMultiple,
                     closeOnSelect: !isMultiple
                  });
               });
            });
         </script>
      @if(Request::segment(1)=='add-student-registrations' || Request::segment(1)=='employee' || Request::segment(1)=='view-employee' || Request::segment(1)=='payroll' || Request::segment(1)=='admin-enquiryform'|| Request::segment(1)=='student-master' || Request::segment(1)=='enquiryeditlist')
      <script src="{{url('assets/backend')}}/js/scripts/tooltip.script.min.js"></script>
      <script src="{{url('assets/backend')}}/js/scripts/script_2.min.js"></script>
      <script src="{{url('assets/backend')}}/js/plugins/feather.min.js"></script>
      <script src="{{url('assets/backend')}}/js/plugins/metisMenu.min.js"></script>
      <script src="{{url('assets/backend')}}/js/scripts/layout-sidebar-vertical.min.js"></script>
    <script src="{{url('assets/backend')}}/js/scripts/sidebar.compact.script.min.js"></script>
    <script src="{{url('assets/backend')}}/js/scripts/customizer.script.min.js"></script>

    @endif

    @if(Request::segment(1)=='inquiry-data-show' || Request::segment(1)=='users' || Request::segment(1)=='fees-type-master'
    || Request::segment(1)=='bus-fees-master' || Request::segment(1)=='view' || Request::segment(1)=='selection-process' || Request::segment(1)=='adminenquirylist' || Request::segment(1)=='followupdate' || Request::segment(1)=='bus-fees-master' || Request::segment(1)=='view' || Request::segment(1)=='selection-process' ||
    Request::segment(1)=='student-registrations' || Request::segment(1)=='employee' || Request::segment(1)=='view-employee' || Request::segment(1)=='payroll'  || Request::segment(1)=='filter-student-registration'
    || Request::segment(1)=='addvehical'|| Request::segment(1)=='bus-attandence-list' || Request::segment(1)=='bus-stop' || Request::segment(1)=='bus-stop-view'
    || Request::segment(1)=='NatureOfWork' || Request::segment(1) == 'maintenance-head-master' ||
    Request::segment(1) == 'editg-maintenance-group-master' || Request::segment(1) == 'view-maintenance-head-master'
    || Request::segment(1) == 'area-master' || Request::segment(1) == 'route-master'
    || Request::segment(1) == 'view-bus-route-master'  || Request::segment(1)=='course-fees-head-orders'
    || Request::segment(1)=='late-fees-master' || Request::segment(1)=='list-rto-paper' ||
    Request::segment(1)=='fees-types-master' || Request::segment(1)=='filter-allenquiry' || Request::segment(1)=='driver-conductor-master'
      || Request::segment(1)=='view-driver-conductor-master'  || Request::segment(1)=='course-fees-structure-master-list'
      || Request::segment(1)=='bus_data' || Request::segment(1)=='roles' || Request::segment(1)=='scholarbusassign'
      || Request::segment(1)=='scholarbusassign_student' || Request::segment(1)=='view-area-master' || Request::segment(1)=='dailyattandence'
      || Request::segment(1)=='rtopaper'|| Request::segment(1)=='studentattandancereport'
      || Request::segment(1)=='subjectmaster' || Request::segment(1)=='AssignSubject'
      || Request::segment(1)=='create-course-fees-structure-master'
      || Request::segment(1)=='teachersubject'|| Request::segment(1)=='calssese-assigne-to-teacher'

      || Request::segment(1)=='subjectcombination'|| Request::segment(1)=='subjectcombinatiomaster'
      || Request::segment(1)=='sectionAssign' || Request::segment(1)=='exammaster'

      || Request::segment(1)=='admin-pre-enquiryform'|| Request::segment(1)=='fees-master-student' ||
      Request::segment(1)=='permission'|| Request::segment(1)=='duestuamount'||
      Request::segment(1)=='filter-duestuamount'|| Request::segment(1)=='enquiry-data' ||
      Request::segment(1)=='filter-enquiry-list' || Request::segment(1)=='gread' ||
      Request::segment(1)=='show_report_markss' || Request::segment(1)=='show_report_marks')


    <script src="{{url('assets/backend/')}}/js/plugins/datatables.min.js"></script>
    <script src="{{url('assets/backend/')}}/js/scripts/datatables.script.min.js"></script>

    @endif
      <script src="{{url('assets/backend')}}/js/plugins/jquery.smartWizard.min.js"></script>
      <script src="{{url('assets/backend')}}/js/scripts/smart.wizard.script.min.js"></script>
      <script src="{{url('assets/backend')}}/js/scripts/form.validation.script.min.js"></script>
      <script src="{{url('assets/backend')}}/js/scripts/sidebar.compact.script.min.js"></script>
      <script src="{{url('assets/backend')}}/js/scripts/customizer.script.min.js"></script>
      <script src="{{url('assets/backend')}}/js/plugins/toastr.min.js"></script>
      <script src="{{url('assets/backend')}}/js/scripts/toastr.script.min.js"></script>


<script>
    $(document).ready(function() {
        $('#classes').select2({
            tags: false,
            width: 'resolve'
        });
    });
    $(document).ready(function() {
        $('#stream').select2({
            tags: false,
            width: 'resolve'
        });
    });
</script>
   </body>
</html>
