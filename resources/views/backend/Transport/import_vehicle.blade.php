@extends('backend.layouts.main')
@section('main-container')

<!-- <div class="main-content-wrap sidenav-open d-flex flex-column"> -->
        <!-- ============ Body content start ============= -->
        <div class="main-content">
			<div class="breadcrumb">
				<h1>Transport Section</h1>
				<a href="{{ route('addvehical') }}" class="btn btn-raised ripple btn-raised-warning m-1">Back to Vehicle Master</a>
			  </div>
			  <div class="separator-breadcrumb border-top"></div>
	<div class="container">
		<h2>Import Vehicles</h2>
		
		@if(session('success'))
			<div class="alert alert-success">{{ session('success') }}</div>
		@endif
		@if(session('error'))
			<div class="alert alert-danger">{{ session('error') }}</div>
		@endif

	<form action="{{ route('import-vehicle') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <label for="file">Upload Vehicle File (CSV/Excel)</label>
        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" required>
        
        @error('file')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <button type="submit" class="btn btn-primary mt-2">Import</button>
	</form>


	</div>
</div>
@endsection
