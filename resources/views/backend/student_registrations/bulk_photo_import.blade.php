@extends('backend.layouts.main')
@section('main-container')

<div class="main-content">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Bulk Upload Student Photos</h1>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ url('upload-student-photos') }}" method="POST" enctype="multipart/form-data" class="p-4">
            @csrf
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label>Upload ZIP File (Photos must be named by Scholar No)</label>
                    <input type="file" name="photos_zip" class="form-control" accept=".zip" required>
                </div>
                <div class="col-md-12">
                    <button class="btn btn-primary">Upload & Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
