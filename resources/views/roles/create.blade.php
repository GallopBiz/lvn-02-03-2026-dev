@extends('layouts.app')
@section('main-container')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Create New Role</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('roles.index') }}"> Back</a>
        </div>
    </div>
</div>
@if (count($errors) > 0)
    <div class="alert alert-danger mt-2">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        </ul>
    </div>
@endif
{!! Form::open(array('route' => 'roles.store','method'=>'POST')) !!}
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group mb-3">
            <strong>Role Name:</strong>
            {!! Form::text('name', null, array('placeholder' => 'Role Name (e.g. Teacher, Accounts, Receptionist)','class' => 'form-control')) !!}
        </div>
    </div>

    <!-- Sidebar Menu & Action Permissions -->
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group col-md-12 mt-2">
            <h4 class="mb-3 text-primary fw-bold">Menu & Action Permissions (View, Add, Edit, Delete)</h4>
            @include('backend.layouts.menu_columns_checkboxes', [
                'selectedMenus' => $selectedMenu ?? []
            ])
        </div>
    </div>

    <div class="col-xs-12 col-sm-12 col-md-12 text-center my-4">
        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold fs-6">Create Role</button>
    </div>
</div>
{!! Form::close() !!}
@endsection
