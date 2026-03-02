<!DOCTYPE html>
<html lang="en" dir="">
  <head>
       <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>LVN-School</title>
    @if(Request::segment(1)=='add-student-registrations' )
    @endif
    @if(Request::segment(1)=='inquiry-data-show' || Request::segment(1)=='users')
    @endif
    @if(Request::segment(1)=='add-student-registrations' || Request::segment(1)=='users' || 
    Request::segment(1)=='fees-type-master' || Request::segment(1)=='bus-fees-master' || Request::segment(1)=='view'  || 
    Request::segment(1)=='selection-process' || Request::segment(1)=='adminenquirylist' || Request::segment(1)=='followupdate'
    || Request::segment(1)=='view'  || Request::segment(1)=='selection-process' || Request::segment(1)=='filter-student-registration' 
    || Request::segment(1)=='addvehical' || Request::segment(1)=='Student-master' 
    || Request::segment(1)=='bus-stop' || Request::segment(1)=='bus-stop-view' || Request::segment(1)=='course-fees-head-orders' 
    || Request::segment(1)=='late-fees-master' || Request::segment(1)=='fees-types-master' || Request::segment(1)=='filter-allenquiry'|| Request::segment(1)=='gread')  
<link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/datatables.min.css"/>
    @endif 
</head>