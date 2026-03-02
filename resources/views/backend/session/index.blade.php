@extends('backend.layouts.main')
@section('main-container')
<div class="main-content">
    <div class="breadcrumb">
        <h1>Session</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>
</div>
<div class="col-md-4 form-group mb-3">
    

    <iframe src="{{url('')}}/session.php" height="500" width="500" title="Iframe Example"></iframe>

</div>
<!-- end of main-content -->
@endsection
   